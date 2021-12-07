<?php

namespace local_enrolstaff\local;

class api {

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