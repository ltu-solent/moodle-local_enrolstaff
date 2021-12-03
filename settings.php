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
 * This file defines the admin settings for this plugin
 *
 * @package   local_enrolstaff
 * @copyright 2018 Southampton Solent University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$settings = new admin_settingpage('local_enrolstaff', new lang_string('pluginname', 'local_enrolstaff'));

$settings->add(new admin_setting_configtext('local_enrolstaff/unitleaderid', get_string('unitleaderid', 'local_enrolstaff'), '', '19')); 
$settings->add(new admin_setting_configtext('local_enrolstaff/roleids', get_string('roleidssolent', 'local_enrolstaff'), '', '0'));
// $settings->add(new admin_setting_configtext('local_enrolstaff/bcasroleids', get_string('roleidsbcas', 'local_enrolstaff'), '', '0'));
// $settings->add(new admin_setting_configtext('local_enrolstaff/bcascodes', get_string('codesbcas', 'local_enrolstaff'), '', '0'));
$settings->add(new admin_setting_configtext('local_enrolstaff/qaheroleids', get_string('roleidsqahe', 'local_enrolstaff'), '', '0'));
$settings->add(new admin_setting_configtext('local_enrolstaff/qahecodes', get_string('codesqahe', 'local_enrolstaff'), '', '0'));
$settings->add(new admin_setting_configtext('local_enrolstaff/excludeshortname', get_string('excludeshortname', 'local_enrolstaff'), '', '0'));
$settings->add(new admin_setting_configtext('local_enrolstaff/excludefullname', get_string('excludeshortname', 'local_enrolstaff'), '', 'counselling,social work'));
$settings->add(new admin_setting_configtext('local_enrolstaff/excludeid', get_string('excludeid', 'local_enrolstaff'), '', '328, 22679, 6432'));
$settings->add(new admin_setting_configtext('local_enrolstaff/studentrecords', get_string('studentrecords', 'local_enrolstaff'), '', ''));

$name = new lang_string('enrolmentnotificationmessageenable', 'local_enrolstaff');
$desc = new lang_string('enrolmentnotificationmessageenable_desc', 'local_enrolstaff');
$settings->add(new admin_setting_configcheckbox('local_enrolstaff/enrolmentnotificationmessageenable', $name, $desc, 1));

$name = new lang_string('enrolmentnotificationsubject', 'local_enrolstaff');
$desc = new lang_string('enrolmentnotificationsubject_desc', 'local_enrolstaff');
$default = new lang_string('enrolmentnotificationsubject_default', 'local_enrolstaff');
$settings->add(new admin_setting_configtext('local_enrolstaff/enrolmentnotificationsubject', $name, $desc, $default, PARAM_TEXT));

$name = new lang_string('enrolmentnotificationmessage', 'local_enrolstaff');
$desc = new lang_string('enrolmentnotificationmessage_desc', 'local_enrolstaff');
$default = new lang_string('enrolmentnotificationmessage_default', 'local_enrolstaff');
$settings->add(new admin_setting_configtextarea('local_enrolstaff/enrolmentnotificationmessage', $name, $desc, $default, PARAM_TEXT, 120, 15));

$ADMIN->add('localplugins', $settings);
