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

use core\context\system;
use core\lang_string;
use core\url;
use core\user;
use local_enrolstaff\event\rule_deleted;
use local_enrolstaff\event\rule_edited;
use local_enrolstaff\local\rule;
use local_enrolstaff\persistent\authorise;

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
        // Users must be Moodle users.
        email_to_user($to, $from, $subject, $message, text_to_html($message));
    }

    /**
     * Returns list of module leaders for the given course
     *
     * @param int $courseid
     * @param array $roleids
     * @return array Array of user objects
     */
    public static function get_users_with_roles($courseid, array $roleids) {
        global $DB;
        [$insql, $inparams] = $DB->get_in_or_equal($roleids, SQL_PARAMS_NAMED);
        $sql = "SELECT u.*
            FROM {course} c
            JOIN {context} cx ON cx.instanceid = c.id AND cx.contextlevel = 50
            JOIN {role_assignments} ra ON ra.contextid = cx.id
            JOIN {role} r ON r.id = ra.roleid AND ra.roleid $insql
            JOIN {user} u ON u.id = ra.userid
            WHERE c.id = :courseid
            GROUP BY u.id";
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

    /**
     * Generate authorisation link
     *
     * @param int $ruleid
     * @param int $courseid
     * @param int $userid
     * @param int $roleid
     * @param int $authoriserid
     * @param int $validitydays
     * @return string
     */
    public static function generate_authorisation_link(
        int $ruleid,
        int $courseid,
        int $userid,
        int $roleid,
        int $authoriserid,
        int $validitydays = 7
    ): string {
        $input = (object)[
            'ruleid' => $ruleid,
            'courseid' => $courseid,
            'requestorid' => $userid,
            'roleid' => $roleid,
            'authoriserid' => $authoriserid,
            'validuntil' => time() + ($validitydays * DAYSECS),
        ];
        $authorise = new authorise(0, $input);
        $authorise->save();
        $url = new url('/local/enrolstaff/authorise.php', $authorise->get_url_params());
        return $url->out();
    }

    /**
     * To be used on the main settings page to limit the roles available for each rule.
     *
     * @param array $archetypes Specify archetypes, if required.
     * @return array
     */
    public static function get_course_level_roles_menu($archetypes = []): array {
        global $DB;
        // We'll leave student archetype in because there may be some student derived roles, but we'll explicitly remove student.
        [$notinsql, $notinparams] = $DB->get_in_or_equal(
            ['coursecreator', 'frontpage', 'guest', 'manager', 'user'],
            SQL_PARAMS_NAMED,
            'archetypeout',
            false
        );
        $insql = '';
        $inparams = [];
        if (!empty($archetypes)) {
            [$insql, $inparams] = $DB->get_in_or_equal(
                $archetypes,
                SQL_PARAMS_NAMED,
                'archetypein'
            );
            if (!empty($insql)) {
                $insql = " AND r.archetype {$insql} ";
            }
        }
        $roles = $DB->get_records_sql(
            "SELECT r.*
            FROM {role} r
            JOIN {role_context_levels} rcl ON rcl.roleid = r.id AND rcl.contextlevel = :contextlevel
            WHERE r.archetype {$notinsql} {$insql} AND r.shortname != 'student'",
            ['contextlevel' => CONTEXT_COURSE] + $notinparams + $inparams
        );
        $roles = role_fix_names($roles, system::instance(), ROLENAME_ALIAS, true);
        return $roles;
    }

    /**
     * Get roles menu
     *
     * @return array
     */
    public static function get_roles_menu(): array {
        global $DB;
        $roleids = static::clean_csv(get_config('local_enrolstaff', 'availableroles'));
        if (empty($roleids)) {
            return [];
        }
        [$insql, $inparams] = $DB->get_in_or_equal($roleids, SQL_PARAMS_NAMED);
        $roles = $DB->get_records_select_menu('role', "id {$insql}", $inparams);
        return role_fix_names($roles, system::instance(), ROLENAME_ALIAS, true);
    }

    /**
     * Get duration menu
     *
     * @return array
     */
    public static function get_duration_menu(): array {
        return [
            0   => new lang_string('neverexpire', 'local_enrolstaff'),
            182 => new lang_string('numdays', '', 182),
            365 => new lang_string('numdays', '', 365),
            547 => new lang_string('numdays', '', 547),
            730 => new lang_string('numdays', '', 730),
        ];
    }

    /**
     * Get authentication methods menu
     *
     * @return array
     */
    public static function get_auth_menu(): array {
        global $CFG;
        $values = array_merge(['manual'], explode(',', $CFG->auth));
        return array_combine($values, $values);
    }

    /**
     * Get departments menu
     *
     * @return array
     */
    public static function get_depts_menu(): array {
        $config = get_config('local_enrolstaff', 'availabledepartments');
        if (!$config) {
            return [];
        }
        $validdepts = explode(',', $config);
        return array_combine($validdepts, $validdepts);
    }

    /**
     * Get Cohorts menu for rules and validation
     *
     * @return array
     */
    public static function get_cohorts_menu(): array {
        global $DB;
        $cohortids = static::clean_csv(get_config('local_enrolstaff', 'availablecohorts'));
        [$insql, $inparams] = $DB->get_in_or_equal($cohortids, SQL_PARAMS_NAMED);
        $cohorts = $DB->get_records_select_menu('cohort', "id {$insql}", $inparams, 'name', 'id,name');
        return $cohorts;
    }

    /**
     * Get all system level cohorts for availability.
     *
     * @return array
     */
    public static function get_system_cohorts_menu(): array {
        global $DB;
        return $DB->get_records_menu('cohort', ['contextid' => system::instance()->id], 'name', 'id,name');
    }

    /**
     * Get sendas notification menu
     *
     * @return array
     */
    public static function get_sendas_menu(): array {
        return [
            'authorisation' => new lang_string('sendas:authorisation', 'local_enrolstaff'),
            'notification' => new lang_string('sendas:notification', 'local_enrolstaff'),
            'nonotification' => new lang_string('sendas:nonotification', 'local_enrolstaff'),
            'registryrequest' => new lang_string('sendas:registryrequest', 'local_enrolstaff'),
        ];
    }

    /**
     * Get roles for who to notify if a notification is required.
     *
     * @return array
     */
    public static function get_notify_menu(): array {
        global $DB;
        $notifyroles = get_config('local_enrolstaff', 'availablenotifyroles');
        $roleids = static::clean_csv($notifyroles);
        $context = system::instance();
        $choices = [];
        if (!empty($roleids)) {
            [$insql, $inparams] = $DB->get_in_or_equal($roleids, SQL_PARAMS_NAMED);
            $roles = $DB->get_records_select('role', "id {$insql}", $inparams);
            $roles = role_fix_names($roles, $context, ROLENAME_ALIAS, true);
            foreach ($roles as $rid => $role) {
                $choices["r:" . $rid] = $role;
            }
        }

        $choices += static::get_backupnotify_menu();
        $choices += static::get_registryemail_menu();
        return $choices;
    }

    /**
     * Backup notify menu
     *
     * @return array
     */
    public static function get_backupnotify_menu(): array {
        global $CFG;
        $backupnotifyemails = get_config('local_enrolstaff', 'availablebackupnotifyemails');
        $emails = static::clean_csv($backupnotifyemails);
        $choices = [];
        foreach ($emails as $email) {
            if (clean_param($email, PARAM_EMAIL) !== '') {
                $choices["e:" . $email] = $email;
            }
        }
        if (empty($choices)) {
            $support = user::get_support_user();
            $choices["e:" . $support->email] = $support->email;
        }
        return $choices;
    }

    /**
     * Get list of registry emails menu options
     *
     * @return array
     */
    public static function get_registryemail_menu(): array {
        $registryemails = get_config('local_enrolstaff', 'availableregistryemails');
        $registryemails = static::clean_csv($registryemails);
        $registryemails = array_filter($registryemails, function ($email) {
            // Does this email need to be a user in Moodle?
            return clean_param($email, PARAM_EMAIL) !== '';
        });
        $choices = [];
        foreach ($registryemails as $key => $email) {
            $choices["e:" . $email] = $email;
        }
        return $choices;
    }

    /**
     * Trigger the appropriate event for the action
     *
     * @param rule $rule
     * @param string $action
     * @return void
     */
    public static function rule_event($rule, $action = 'new') {
        $eventdata = [];
        $eventdata['objectid'] = $rule->get('id');
        $eventdata['context'] = system::instance();

        $event = match ($action) {
            'new', 'edit', 'enable', 'disable' => rule_edited::create($eventdata),
            'delete' => rule_deleted::create($eventdata),
        };

        $event->trigger();
    }

    /**
     * Take a csv string and return a clean array
     *
     * @param string $csv
     * @return array
     */
    public static function clean_csv(string $csv): array {
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
     * SQL fragment for arrays
     *
     * @param string $field
     * @param array $values
     * @param string $prefix
     * @return array (select, params, conditions)
     */
    public static function get_sql_for_field_in_array(string $field, array $values, string $prefix = 'param'): array {
        global $DB;
        $conditions = [];
        $params = [];
        $select = '';
        if (count($values) > 0) {
            [$insql, $inparams] = $DB->get_in_or_equal(
                $values,
                SQL_PARAMS_NAMED,
                $prefix
            );
            $select = "{$field} {$insql}";
            $params = $inparams;
        }
        return [$select, $params, $conditions];
    }

    /**
     * Get SQL fragment for cohort membership
     *
     * @param string $field
     * @param array $cohortids
     * @param string $prefix
     * @return array (select, params, conditions)
     */
    public static function get_sql_for_cohort_membership(string $field, array $cohortids, string $prefix = 'cohort'): array {
        global $DB;
        $conditions = [];
        $params = [];
        $select = '';
        if (count($cohortids) > 0) {
            [$insql, $inparams] = $DB->get_in_or_equal(
                $cohortids,
                SQL_PARAMS_NAMED,
                $prefix
            );
            $select = "{$field} IN (
                SELECT userid
                FROM {cohort_members}
                WHERE cohortid {$insql}
            )";
            $params = $inparams;
        }
        return [$select, $params, $conditions];
    }
}
