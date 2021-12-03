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
        $roleid = get_config('local_enrolstaff', 'unitleaderid');
        $sql = "SELECT u.*
            FROM {course} c
            JOIN {context} cx ON cx.instanceid = c.id AND cx.contextlevel = 50
            JOIN {role_assignments} ra ON ra.contextid = cx.id
            JOIN {role} r ON r.id = ra.roleid AND ra.roleid = :roleid
            JOIN {user} u ON u.id = ra.userid
            WHERE c.id = :courseid";
        
        $users = $DB->get_records_sql($sql, ['courseid' => $courseid, 'roleid' => $roleid]);
        return $users;
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