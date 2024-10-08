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

/**
 * User class
 */
class user {
    /**
     * List of valid roles this person can enrol with
     *
     * @var array id list
     */
    public $validroles;

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
     * Is this user working in the jobshop
     *
     * @var bool
     */
    private $isjobshopuser;

    /**
     * Departments that are valid for the enrol staff features
     *
     * @var array
     */
    private $validdepts;

    /**
     * Sets up variables that are used in subsequent queries
     *
     * @param object $user
     */
    public function __construct($user) {
        $this->user = $user;
        $emailparts = explode('@', $user->email);
        $this->domain = $emailparts[1];
        $this->config = get_config('local_enrolstaff');
        // This should be a setting.
        $this->validdepts = ['academic', 'management', 'support'];
        $this->department = strtolower($user->department);

        if ($this->domain == 'qa.com') {
            $this->validroles = explode(',', $this->config->qaheroleids);
        } else if ($this->domain == 'solent.ac.uk') {
            $this->validroles = explode(',', $this->config->roleids);
        } else if (is_siteadmin($user)) {
            $this->validroles = explode(',', $this->config->roleids);
            return;
        } else {
            $this->validroles = [];
        }

        $this->isjobshopuser = strpos($user->email, 'jobshop') === 0;
        if ($this->isjobshopuser) {
            $this->validroles = [];
        }
        if (!in_array($this->department, $this->validdepts)) {
            $this->validroles = [];
        }
    }

    /**
     * Gets available roles for given email address domain.
     *
     * @return array Menu select items
     */
    public function get_roles_menu() {
        global $DB;
        if (empty($this->validroles)) {
            return [];
        }
        list($inorequalsql, $params) = $DB->get_in_or_equal($this->validroles, SQL_PARAMS_NAMED, '', true);

        $sql = "SELECT id, name
                FROM {role}
                WHERE id {$inorequalsql}
                ORDER BY name";

        $roles = $DB->get_records_sql_menu($sql, $params);

        return $roles;
    }

    /**
     * Check the selected roleid is valid for this user.
     *
     * @param int $roleid
     * @return boolean
     */
    public function is_role_valid($roleid) {
        global $DB;
        if (empty($this->validroles)) {
            return false;
        }
        if (!$DB->record_exists('role', ['id' => $roleid])) {
            return false;
        }
        return in_array($roleid, $this->validroles);
    }

    /**
     * Is this user allowed to enrol themselves?
     *
     * @return bool
     */
    public function user_can_enrolself(): bool {
        if (is_siteadmin()) {
            return true;
        }

        if (empty($this->validroles)) {
            return false;
        }
        if ($this->isjobshopuser) {
            return false;
        }

        if (in_array($this->department, $this->validdepts)) {
            return true;
        }
        return false;
    }

    /**
     * Can user enrol self on a particular course?
     *
     * @param int $courseid
     * @return boolean
     */
    public function can_enrolselfon($courseid): bool {
        global $DB;
        $canenrolself = $this->user_can_enrolself();
        if (!$canenrolself) {
            return false;
        }

        $excludecourses = explode(',', $this->config->excludeid);
        if (in_array($courseid, $excludecourses)) {
            return false;
        }

        $moduleslike = $DB->sql_like('cc.idnumber', ':moduleslike', false, false);
        $courseslike = $DB->sql_like('cc.idnumber', ':courseslike', false, false);
        $params = [
            'courseid' => $courseid,
            'moduleslike' => 'modules_%',
            'courseslike' => 'courses_%',
        ];
        [$andsql, $andparams] = $this->get_course_filter();
        $params += $andparams;
        $sql = "SELECT c.id
            FROM {course} c
            JOIN {course_categories} cc on c.category = cc.id
            WHERE c.id = :courseid
            $andsql
            AND ({$moduleslike} OR {$courseslike})
            AND c.visible = 1";

        $courses = $DB->get_records_sql($sql, $params);
        if (count($courses) == 1) {
            return true;
        }
        return false;
    }

    /**
     * Returns a list of courses this user can enrol themselves on.
     * Searches both the shortcode and fullname
     * Scope of search is limited to category idnumbers that start with "modules_" or "courses_"
     *
     * @param string $coursesearch
     * @return array List of valid courses
     */
    public function course_search($coursesearch) {
        global $DB;
        $canenrolself = $this->user_can_enrolself();
        if (!$canenrolself) {
            return [];
        }

        $excludecourses = $this->config->excludeid;
        $excludecourses = explode(',', $excludecourses);

        list($inorequalsql, $inparams) = $DB->get_in_or_equal($excludecourses, SQL_PARAMS_NAMED, '', false);
        $coursesearch1like = $DB->sql_like('c.shortname', ':coursesearch1', false, false);
        $coursesearch2like = $DB->sql_like('c.fullname', ':coursesearch2', false, false);
        $moduleslike = $DB->sql_like('cc.idnumber', ':moduleslike', false, false);
        $courseslike = $DB->sql_like('cc.idnumber', ':courseslike', false, false);
        $params = [
            'coursesearch1' => '%' . $DB->sql_like_escape($coursesearch) . '%',
            'coursesearch2' => '%' . $DB->sql_like_escape($coursesearch) . '%',
            'moduleslike' => 'modules_%',
            'courseslike' => 'courses_%',
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
                AND ({$moduleslike} OR {$courseslike})
                AND c.visible = 1
                ORDER BY c.startdate DESC, c.shortname ASC";
        $courses = $DB->get_records_sql($sql, $params);

        return $courses;
    }

    /**
     * Prepares an SQL snippet to limit the choice of courses available to the requesting user.
     *
     * @return array [SQL snippet, params]
     */
    private function get_course_filter(): array {
        global $DB;
        $excludename = explode(',', $this->config->excludeshortname);
        $excludeterm = explode(',', $this->config->excludefullname);
        $andsql = '';
        $andparams = [];
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

        // Limit QA accounts to only these courses.
        if ($this->domain == 'qa.com') {
            $validcodes = explode(',', $this->config->qahecodes);
        }
        if (isset($validcodes)) {
            foreach ($validcodes as $key => $value) {
                $andsql .= " AND " . $DB->sql_like('c.shortname', ":codes{$key}", false, false) . " ";
                $andparams["codes{$key}"] = '%' . $DB->sql_like_escape($value) . '%';
            }
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
        $roleassignments = $DB->get_records_sql("SELECT ra.id raid, c.id course_id, c.fullname, c.idnumber, c.shortname,
        c.startdate, c.enddate, ra.roleid
        FROM {course} c
            JOIN {context} ctx ON c.id = ctx.instanceid AND ctx.contextlevel = 50
            JOIN {role_assignments} ra ON ra.contextid = ctx.id
            JOIN {user} u ON u.id = ra.userid
        WHERE u.id = :userid
            AND ra.component != 'enrol_cohort'
            AND ra.component != 'enrol_meta'
            AND ra.component != 'enrol_solaissits'",
        ['userid' => $this->user->id]);
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
}
