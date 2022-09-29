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
 * API helper class file
 *
 * @package   local_enrolstaff
 * @author    Mark Sharp <mark.sharp@solent.ac.uk>
 * @copyright 2022 Solent University {@link https://www.solent.ac.uk}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_enrolstaff\local;

/**
 * API class
 */
class api {

    /**
     * Wrapper for send_to_user function
     *
     * @param object $to
     * @param object $from
     * @param string $subject
     * @param string $message
     * @return void
     */
    public static function send_message(object $to, object $from, string $subject, string $message) {
        email_to_user($to, $from, $subject, $message, text_to_html($message));
    }

    /**
     * Returns list of module leaders for the given course
     *
     * @param int $courseid
     * @return array Array of user objects
     */
    public static function moduleleader($courseid) {
        global $DB;
        $roleids = explode(',', get_config('local_enrolstaff', 'unitleaderid'));
        list($insql, $inparams) = $DB->get_in_or_equal($roleids, SQL_PARAMS_NAMED);
        $sql = "SELECT u.*
            FROM {course} c
            JOIN {context} cx ON cx.instanceid = c.id AND cx.contextlevel = 50
            JOIN {role_assignments} ra ON ra.contextid = cx.id
            JOIN {role} r ON r.id = ra.roleid AND ra.roleid $insql
            JOIN {user} u ON u.id = ra.userid
            WHERE c.id = :courseid";
        $params = ['courseid' => $courseid] + $inparams;
        $users = $DB->get_records_sql($sql, $params);
        return $users;
    }

    /**
     * Determines if a course is a partner course
     *
     * @param object $course
     * @return bool
     */
    public static function is_partner_course($course) {
        $qacodes = explode(',', get_config('local_enrolstaff', 'qahecodes'));
        $match = false;
        foreach ($qacodes as $qacode) {
            $ismatch = preg_match('#^' . $qacode . '#i', $course->shortname);
            if ($ismatch == 1) {
                $match = true;
            }
        }
        return $match;
    }

    /**
     * Replaces variables in the message template with values
     *
     * @param string $setting The setting/language string key
     * @param array $options array of fields to be replaced with values
     * @return string Message
     */
    public static function prepare_message(string $setting, array $options) {
        $message = get_config('local_enrolstaff', $setting);
        if (empty($message)) {
            $message = get_string($setting, 'local_enrolstaff');
        }

        foreach ($options as $key => $option) {
            $message = str_replace('[' . $key . ']', $option, $message);
        }
        return $message;
    }
}
