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
     * @var boolean
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
    public function user_can_enrolself():bool {
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

        $and = $this->get_course_filter();

        $sql = "SELECT c.id
            FROM {course} c
            JOIN {course_categories} cc on c.category = cc.id
            WHERE c.id = :courseid
            $and
            AND (cc.idnumber LIKE 'modules_%' OR cc.idnumber LIKE 'courses_%')
            AND c.visible = 1";

        $courses = $DB->get_records_sql($sql, ['courseid' => $courseid]);
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

        $params = [
            'coursesearch1' => '%'.$coursesearch.'%',
            'coursesearch2' => '%'.$coursesearch.'%'
        ];
        $params += $inparams;

        $and = $this->get_course_filter();

        $sql = "SELECT c.id, c.idnumber, c.shortname, c.fullname, DATE_FORMAT(FROM_UNIXTIME(c.startdate), '%d-%m-%Y') as startunix
                FROM {course} c
                JOIN {course_categories} cc on c.category = cc.id
                WHERE (c.shortname LIKE :coursesearch1
                OR c.fullname LIKE :coursesearch2)
                $and
                AND c.id {$inorequalsql}
                AND (cc.idnumber LIKE 'modules_%' OR cc.idnumber LIKE 'courses_%')
                AND c.visible = 1
                ORDER BY c.shortname DESC";
        $courses = $DB->get_records_sql($sql, $params);

        return $courses;
    }

    /**
     * Prepares an SQL snippet to limit the choice of courses available to the requesting user.
     *
     * @return string SQL snippet
     */
    private function get_course_filter(): string {
        $excludename = explode(',', $this->config->excludeshortname);
        $excludeterm = explode(',', $this->config->excludefullname);
        $and = '';

        foreach ($excludename as $value) {
            $and .= "AND (c.shortname NOT LIKE '$value%' OR c.fullname NOT LIKE '%$value%') ";
        }

        foreach ($excludeterm as $value) {
            $and .= "AND c.fullname NOT LIKE '%$value%' ";
        }

        // Limit QA accounts to only these courses.
        if ($this->domain == 'qa.com') {
            $validcodes = explode(',', $this->config->qahecodes);
        }
        if (isset($validcodes)) {
            foreach ($validcodes as $value) {
                $and .= "AND c.shortname LIKE '%$value%' ";
            }
        }
        return $and;
    }


    /**
     * Gets user's enrolments excluding cohort and meta enrolments.
     *
     * @return array
     */
    public function user_courses(): array {
        global $DB;
        $enrolledcourses = $DB->get_records_sql("SELECT FLOOR(RAND() * 401) + 100 as id, c.id course_id, c.fullname, c.idnumber,
            FROM_UNIXTIME(c.startdate, '%d-%m-%Y') startdate, r.id role_id, r.name,
            (SELECT GROUP_CONCAT(r.name SEPARATOR ', ')
                FROM {user} u1
                INNER JOIN {role_assignments} ra ON ra.userid = u1.id
                INNER JOIN {context} ct ON ct.id = ra.contextid
                INNER JOIN {course} c2 ON c2.id = ct.instanceid
                INNER JOIN {role} r ON r.id = ra.roleid
                WHERE c2.id = c.id
                AND u1.id = u.id
            ) AS roles
            FROM {course} c
                JOIN {context} ctx ON c.id = ctx.instanceid
                JOIN {role_assignments} ra ON ra.contextid = ctx.id
                JOIN {role} r ON ra.roleid = r.id
                JOIN {user} u ON u.id = ra.userid
            WHERE u.id = :userid
                AND ra.component != 'enrol_cohort'
                AND ra.component != 'enrol_meta'
            GROUP BY c.id", ['userid' => $this->user->id]);
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
