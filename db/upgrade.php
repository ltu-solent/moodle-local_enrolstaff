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
 * Upgrade steps for Staff Enrolment
 *
 * Documentation: {@link https://moodledev.io/docs/guides/upgrade}
 *
 * @package    local_enrolstaff
 * @category   upgrade
 * @copyright  2025 Southampton Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_enrolstaff\local\api;

/**
 * Execute the plugin upgrade steps from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_local_enrolstaff_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2025112100) {
        $table = new xmldb_table('local_enrolstaff');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
        $table->add_field('title', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('email', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('username', XMLDB_TYPE_CHAR, '255', null, null, null, null, null);
        $table->add_field('exemail', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('exusername', XMLDB_TYPE_CHAR, '255', null, null, null, null, null);
        $table->add_field('departments', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('cohortids', XMLDB_TYPE_CHAR, '255', null, null, null, null, null);
        $table->add_field('institution', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('auths', XMLDB_TYPE_CHAR, '255', null, null, null, null, null);
        $table->add_field('roleids', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('duration', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null);
        $table->add_field('codes', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('excodes', XMLDB_TYPE_CHAR, '255', null, null, null, null, null);
        $table->add_field('sendas', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('notify', XMLDB_TYPE_CHAR, '1000', null, null, null, null);
        $table->add_field('enabled', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        $table = new xmldb_table('local_enrolstaff_auth');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
        $table->add_field('ruleid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null);
        $table->add_field('requestorid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null);
        $table->add_field('roleid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null);
        $table->add_field('authoriserid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null);
        $table->add_field('token', XMLDB_TYPE_CHAR, '32', null, XMLDB_NOTNULL, null, null, null);
        $table->add_field('validuntil', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('ruleid', XMLDB_KEY_FOREIGN, ['ruleid'], 'local_enrolstaff', ['id']);
        $table->add_key('requestorid', XMLDB_KEY_FOREIGN, ['requestorid'], 'user', ['id']);
        $table->add_key('authoriserid', XMLDB_KEY_FOREIGN, ['authoriserid'], 'user', ['id']);
        $table->add_key('courseid', XMLDB_KEY_FOREIGN, ['courseid'], 'course', ['id']);
        $table->add_key('roleid', XMLDB_KEY_FOREIGN, ['roleid'], 'role', ['id']);
        $table->add_index('token', XMLDB_INDEX_UNIQUE, ['token']);
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Copy old settings to new and new defaults.
        $availableroles = [];
        foreach (['unitleaderid', 'roleids', 'qaheroleids'] as $name) {
            $setting = get_config('local_enrolstaff', $name);
            if ($setting && !empty($setting)) {
                $roles = api::clean_csv($setting);
                $availableroles = array_merge($availableroles, $roles);
            }
        }
        if (count($availableroles) > 0) {
            set_config('availableroles', join(',', $availableroles));
        }
        $availablenotifyroles = get_config('local_enrolstaff', 'unitleaderid');
        set_config('availablenotifyroles', $availablenotifyroles, 'local_enrolstaff');
        $availableregistryemails = [];
        foreach (['studentrecords', 'qahecontact'] as $name) {
            $setting = get_config('local_enrolstaff', $name);
            if ($setting && !empty($setting)) {
                $emails = api::clean_csv($setting);
                $availableregistryemails = array_merge($availableregistryemails, $emails);
            }
        }
        if (count($availableregistryemails) > 0) {
            set_config('availableregistryemails', $availableregistryemails, 'local_enrolstaff');
        }

        $defaultexpireenrolments = get_config('local_enrolstaff', 'expireenrolment');
        set_config('defaultexpireenrolments', $defaultexpireenrolments, 'local_enrolstaff');

        $unitleaderids = get_config('local_enrolstaff', 'unitleaderid');
        // Convert roleid to roleshortname, if available.
        $unitleaders = api::clean_csv($unitleaderids);
        [$insql, $inparams] = $DB->get_in_or_equal($unitleaders, SQL_PARAMS_NAMED);
        $roleshortnames = $DB->get_field_select('role', 'shortname', "id {$insql}", $inparams);
        $defaultnotifyroles = [];
        foreach ($roleshortnames as $shortname) {
            if (!empty($shortname)) {
                $defaultnotifyroles[] = $shortname;
            }
        }
        set_config('defaultnotifyroles', join(',', $defaultnotifyroles), 'local_enrolstaff');

        upgrade_plugin_savepoint(true, 2025112100, 'local', 'enrolstaff');
    }

    return true;
}
