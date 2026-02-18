<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * User object with calculated properties and permissions for enrolment features
 *
 * @package   local_enrolstaff
 * @author    Mark Sharp <mark.sharp@solent.ac.uk>
 * @copyright 2022 Solent University {@link https://www.solent.ac.uk}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_enrolstaff\local;

use core_cache\cache;
use core_cache\session_cache;
use local_enrolstaff\persistent\rule;
use stdClass;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/cohort/lib.php');

/**
 * User class
 */
class user {
    /**
     * Domain of email address
     *
     * @var string
     */
    public $domain;

    /**
     * Moodle user object
     *
     * @var stdClass
     */
    public $user;

    /**
     * Plugin config settings
     *
     * @var stdClass
     */
    private $config;

    /**
     * Department field from user object
     *
     * @var string
     */
    private $department;

    /**
     * User details cache
     *
     * @var session_cache
     */
    public $usercache = null;

    /**
     * Cohorts this user belongs to
     *
     * @var array
     */
    private $cohorts = null;

    /**
     * The rules for this user
     *
     * @var array
     */
    private $rules = null;

    /**
     * Sets up variables that are used in subsequent queries
     *
     * @param object $user
     * @return null
     */
    public function __construct($user) {
        $this->user = $user;
        $emailparts = explode('@', $user->email);
        $this->domain = $emailparts[1];
        $this->config = get_config('local_enrolstaff');

        $this->department = strtolower($user->department);
        $this->get_cache();
    }

    /**
     * Gets available roles for given user.
     *
     * @return array Menu select items
     */
    public function get_roles_menu() {
        $this->get_roleids();
        if (empty($this->usercache->get('roleids'))) {
            return [];
        }
        $rolesmenu = api::get_roles_menu();
        $rolesmenu = array_filter($rolesmenu, function ($key) {
            return in_array($key, $this->usercache->get('roleids'));
        }, ARRAY_FILTER_USE_KEY);
        return $rolesmenu;
    }

    /**
     * Check the selected roleid is valid for this user.
     *
     * @param int $roleid
     * @return boolean
     */
    public function is_role_valid($roleid) {
        global $DB;
        $this->get_roleids();
        if (empty($this->usercache->get('roleids'))) {
            return false;
        }
        if (!$DB->record_exists('role', ['id' => $roleid])) {
            return false;
        }
        return in_array($roleid, $this->usercache->get('roleids'));
    }

    /**
     * Is this user allowed to enrol themselves?
     *
     * @return bool
     */
    public function user_can_enrolself(): bool {
        $this->get_cache();
        return $this->usercache->get('canenrolself');
    }

    /**
     * Can user enrol self on a particular course?
     *
     * This is limited to just the courses in the current search, and doesn't represent all available courses.
     *
     * @param int $courseid
     * @return boolean
     */
    public function can_enrolselfon($courseid): bool {
        $canenrolself = $this->user_can_enrolself();
        if (!$canenrolself) {
            return false;
        }

        // This is dependent on a course search having been done first.
        $rulecourses = $this->usercache->get('rulecourses');
        if (empty($rulecourses)) {
            return false;
        }
        foreach ($rulecourses as $ruleid => $courses) {
            foreach ($courses as $id => $course) {
                if ($id == $courseid) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Returns a list of courses this user can enrol themselves on.
     * Searches both the shortcode and fullname
     * Scope of search is limited to category idnumbers that start with "modules_" or "courses_"
     *
     * @param string $coursesearch
     * @param int $roleid
     * @return array List of valid courses
     */
    public function course_search($coursesearch, $roleid) {
        global $DB;
        $canenrolself = $this->user_can_enrolself();
        $this->usercache->delete('rulecourses');
        if (!$canenrolself) {
            return [];
        }
        // For the role, check which rules apply.
        $userrules = $this->usercache->get('ruleids');
        if (empty($userrules)) {
            return [];
        }
        $rules = $this->get_rules();
        $applicablerules = array_filter($userrules, function ($userruleid) use ($roleid, $rules) {
            $rule = $rules[$userruleid];
            $roleids = $rule->get('roleids');
            return in_array($roleid, $roleids);
        });
        // No rules for this roleid.
        if (count($applicablerules) == 0) {
            return [];
        }

        $excludecourses = $this->config->excludeid;
        $excludecourses = explode(',', $excludecourses);

        [$inorequalsql, $inparams] = $DB->get_in_or_equal($excludecourses, SQL_PARAMS_NAMED, 'excids', false);
        $coursesearch1like = $DB->sql_like('c.shortname', ':coursesearch1', false, false);
        $coursesearch2like = $DB->sql_like('c.fullname', ':coursesearch2', false, false);
        $modulecatlike = $DB->sql_like('cc.idnumber', ':modulecatlike', false, false);
        $coursecatlike = $DB->sql_like('cc.idnumber', ':coursecatlike', false, false);
        $params = [
            'coursesearch1' => '%' . $DB->sql_like_escape($coursesearch) . '%',
            'coursesearch2' => '%' . $DB->sql_like_escape($coursesearch) . '%',
            'modulecatlike' => 'modules_%',
            'coursecatlike' => 'courses_%',
        ];
        $params += $inparams;

        [$andsql, $andparams] = $this->get_course_filter();

        $params += $andparams;
        $sql = "SELECT c.id, c.idnumber, c.shortname, c.fullname, c.startdate as startunix, c.enddate as endunix
                FROM {course} c
                JOIN {course_categories} cc on c.category = cc.id
                WHERE ({$coursesearch1like} OR {$coursesearch2like})
                $andsql
                AND c.id {$inorequalsql}
                AND ({$modulecatlike} OR {$coursecatlike})
                AND c.visible = 1
                ORDER BY c.startdate DESC, c.shortname ASC";
        $courses = $DB->get_records_sql($sql, $params);
        if (count($courses) == 0) {
            // Any rules that apply after this will be irrelevant.
            return [
                0 => [],
            ];
        }
        // Rules-based exclusions/inclusions.
        $rules = $this->get_rules();
        $rulecourses = [];
        foreach ($applicablerules as $ruleid) {
            if (!isset($rules[$ruleid])) {
                continue;
            }
            /** @var rule $rule */
            $rule = $rules[$ruleid];
            $rulecourses[$ruleid] = array_filter($courses, function ($course) use ($rule) {
                return $rule->rule_applies_to_course($course);
            });
        }
        // This is only relevant for the current search. When a new search is started, this is cleared.
        $this->usercache->set('rulecourses', $rulecourses);
        return $rulecourses;
    }

    /**
     * Prepares an SQL snippet to limit the choice of courses available to the requesting user.
     *
     * @return array [SQL snippet, params]
     */
    private function get_course_filter(): array {
        global $DB;
        $excludename = $this->clean_csv($this->config->excludeshortname);
        $excludeterm = $this->clean_csv($this->config->excludefullname);
        $andsql = '';
        $andparams = [];
        // Universal exclusions.
        foreach ($excludename as $key => $value) {
            $andsql .= " AND (" . $DB->sql_like('c.shortname', ":eshortname{$key}", false, false, true) . " ";
            $andsql .= " OR " . $DB->sql_like('c.fullname', ":efullname{$key}", false, false, true) . ") ";
            $andparams["eshortname{$key}"] = $DB->sql_like_escape($value) . '%';
            $andparams["efullname{$key}"] = '%' . $DB->sql_like_escape($value) . '%';
        }

        foreach ($excludeterm as $key => $value) {
            $andsql .= " AND " . $DB->sql_like('c.fullname', ":eterm{$key}", false, false, true) . " ";
            $andparams["eterm{$key}"] = '%' . $DB->sql_like_escape($value) . '%';
        }

        return [$andsql, $andparams];
    }


    /**
     * Gets user's enrolments excluding cohort and meta enrolments.
     *
     * @return array
     */
    public function user_courses(): array {
        global $DB;
        $roleassignments = $DB->get_records_sql(
            "SELECT ra.id raid, c.id course_id, c.fullname, c.idnumber, c.shortname, c.startdate, c.enddate, ra.roleid
        FROM {course} c
            JOIN {context} ctx ON c.id = ctx.instanceid AND ctx.contextlevel = 50
            JOIN {role_assignments} ra ON ra.contextid = ctx.id
            JOIN {user} u ON u.id = ra.userid
        WHERE u.id = :userid
            AND ra.component != 'enrol_cohort'
            AND ra.component != 'enrol_meta'
            AND ra.component != 'enrol_solaissits'",
            ['userid' => $this->user->id]
        );
        $enrolledcourses = [];
        $roles = $DB->get_records('role');
        foreach ($roleassignments as $ra) {
            $rolename = role_get_name($roles[$ra->roleid]);
            unset($ra->roleid);
            if (isset($enrolledcourses[$ra->course_id])) {
                $enrolledcourses[$ra->course_id]->roles .= ', ' . $rolename;
            } else {
                $enrolledcourses[$ra->course_id] = $ra;
                $enrolledcourses[$ra->course_id]->roles = $rolename;
            }
        }
        return $enrolledcourses;
    }

    /**
     * Check to see if the user is already enrolled on a course.
     *
     * @param int $courseid
     * @return boolean
     */
    public function is_enrolled_on($courseid) {
        return true;
    }

    /**
     * Build the cache if not already built.
     * This will be a session cache, so will only be cleared when the session ends (logged out).
     *
     * @return void
     */
    private function get_cache() {
        global $DB;
        // Usercache is a session cache, so will load once per session for this user.
        if (!$this->usercache) {
            $this->usercache = cache::make('local_enrolstaff', 'user');
        }
        // Cache is already built. Nothing more to do.
        if ($this->usercache->has('canenrolself')) {
            return;
        }

        // Find all rules that apply to this user and make cache.
        $ruleids = [];
        $rules = rule::get_records(['enabled' => 1]);
        if (count($rules) == 0) {
            $this->usercache->set('canenrolself', false);
        }
        if (is_siteadmin()) {
            // Give site admins all the rules.
            foreach ($rules as $rule) {
                $ruleids[] = $rule->get('id');
            }
            $this->usercache->set('ruleids', $ruleids);
            if (count($rules) > 0) {
                $this->usercache->set('canenrolself', true);
            }
            return;
        }
        foreach ($rules as $rule) {
            // It only takes one failed value to fail the whole rule.
            $rulefails = [];
            if (!empty($rule->get('cohortids'))) {
                if (!$this->cohorts) {
                    // All this user's cohort membership.
                    $this->cohorts = array_keys(cohort_get_user_cohorts($this->user->id));
                }
                $cohortids = $rule->get('cohortids');
                $ismember = false;

                foreach ($cohortids as $cohortid) {
                    if ($ismember) {
                        continue;
                    }
                    $ismember = (in_array($cohortid, $this->cohorts));
                }
                $rulefails[] = !$ismember;
            }
            if (!empty($rule->get('departments'))) {
                $depts = $rule->get('departments');
                $rulefails[] = !in_array($this->department, $depts);
            }
            if (!empty($rule->get('institution'))) {
                $rulefails[] = !(strtolower($rule->get('institution')) == strtolower($this->user->institution));
            }
            if (!empty($rule->get('auths'))) {
                $auths = $rule->get('auths');
                $rulefails[] = !in_array($this->user->auth, $auths);
            }
            if (!empty($rule->get('exemail'))) {
                $match = preg_match('#' . $rule->get('exemail') . '#i', $this->user->email);
                if ($match !== 0) {
                    $rulefails[] = true;
                }
            }
            if (!empty($rule->get('exusername'))) {
                $match = preg_match('#' . $rule->get('exusername') . '#i', $this->user->username);
                if ($match !== 0) {
                    $rulefails[] = true;
                }
            }
            if (!empty($rule->get('email'))) {
                $match = preg_match('#' . $rule->get('email') . '#i', $this->user->email);
                if ($match === 0) {
                    $rulefails[] = true;
                }
            }
            if (!empty($rule->get('username'))) {
                $match = preg_match('#' . $rule->get('username') . '#', $this->user->username);
                if ($match === 0) {
                    $rulefails[] = true;
                }
            }

            $rulefails = array_filter($rulefails, function ($r) {
                return ($r == true);
            });

            if (count($rulefails) == 0) {
                $ruleids[] = $rule->get('id');
            }
        }
        if (count($ruleids) > 0) {
            $this->usercache->set('canenrolself', true);
            $this->usercache->set('ruleids', $ruleids);
        } else {
            $this->usercache->set('canenrolself', false);
        }
    }

    /**
     * We only build roles when we need to use them.
     *
     * @return array|false
     */
    private function get_roleids() {
        try {
            $this->get_cache();
        } catch (\Exception $e) {
            mtrace($this->user->username . ': Exception getting cache: ' . $e->getMessage());
            return false;
        }
        if ($this->usercache->has('roleids')) {
            return $this->usercache->get('roleids');
        }
        $roles = [];
        $rules = $this->get_rules();
        if (count($rules) == 0) {
            return [];
        }
        foreach ($this->usercache->get('ruleids') as $ruleid) {
            if (!isset($rules[$ruleid])) {
                continue;
            }
            $rule = $rules[$ruleid];
            $roleids = $rule->get('roleids');
            if (!empty($roleids)) {
                $roles = array_merge($roles, $roleids);
            }
        }
        $roles = array_unique($roles);
        $this->usercache->set('roleids', $roles);
        return $this->usercache->get('roleids');
    }

    /**
     * Sometimes, you just need to delete the cache if something has changed.
     *
     * @return void
     */
    public function delete_cache() {
        $this->usercache = null;
    }

    /**
     * Get rules for this user
     *
     * @return array
     */
    private function get_rules(): array {
        global $DB;
        if (!is_null($this->rules)) {
            return $this->rules;
        }
        $ruleids = $this->usercache->get('ruleids');
        if (empty($ruleids)) {
            $this->rules = [];
            return $this->rules;
        }
        [$insql, $inparams] = $DB->get_in_or_equal($ruleids, SQL_PARAMS_NAMED);
        $this->rules = rule::get_records_select("id {$insql}", $inparams);
        return $this->rules;
    }

    /**
     * Take a csv string and return a clean array
     *
     * @param string $csv
     * @return array
     */
    private function clean_csv(string $csv): array {
        $list = [];
        if (empty(trim($csv))) {
            return $list;
        }
        $items = explode(',', $csv);
        if (count($items) == 0) {
            return $list;
        }
        foreach ($items as $item) {
            if (!empty($item)) {
                $list[] = $item;
            }
        }
        return $list;
    }

    /**
     * Given the courseid and roleid, which is the best rule to apply
     *
     * @param int $courseid
     * @param int $roleid
     * @return rule|false
     */
    public function find_best_rule($courseid, $roleid) {
        $rulecourses = $this->usercache->get('rulecourses');
        if (empty($rulecourses)) {
            return false;
        }
        // It's possible more than one rule applies, but we're going to focus on returning the best fit.
        $rules = $this->get_rules();
        $matchingrules = [];
        foreach ($rulecourses as $ruleid => $courses) {
            /** @var rule $rule */
            $rule = $rules[$ruleid];
            $roleids = $rule->get('roleids');
            // If the role is an editing teacher archetype, should try to find who recipients are.
            foreach ($roleids as $rid) {
                if ($rid == $roleid) {
                    $matchingrules[$ruleid]['roleid'] = $rid;
                }
            }
            foreach ($courses as $course) {
                if ($courseid == $course->id) {
                    $matchingrules[$ruleid]['courseid'] = $course->id;
                }
            }
        }
        // Now find the best match.
        if (count($matchingrules) == 0) {
            // What are we even doing here?
            return false;
        }
        if (count($matchingrules) == 1) {
            $ruleids = array_keys($matchingrules);
            return $rules[$ruleids[0]];
        }
        $bestruleid = 0;
        foreach ($matchingrules as $ruleid => $matches) {
            if (isset($matches['roleid']) && isset($matches['courseid'])) {
                // Perfect match.
                $bestruleid = $ruleid;
                break;
            }
        }
        return $rules[$bestruleid];
    }
}
