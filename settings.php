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


$settings->add(new admin_setting_configtext('local_enrolstaff/unitleaderid', 'Unit leader role id', '', '19'));
$settings->add(new admin_setting_configtext('local_enrolstaff/excludeshortname', 'Exclude from search by shortname (comma separated)', '', ''));
$settings->add(new admin_setting_configtext('local_enrolstaff/excludefullname', 'Exclude from search by fullname (comma separated)', '', 'counselling,social work'));
$settings->add(new admin_setting_configtext('local_enrolstaff/excludeid', 'Exclude from search by id (comma separated)', '', '328, 22679, 6432'));
//$settings->add(new admin_setting_configtext('local_enrolstaff/excludecategory', 'Exclude from search by category (comma separated)', '', 'module pages,course pages'));
$settings->add(new admin_setting_configtext('local_enrolstaff/studentrecords', 'Student records email', '', ''));
// $settings->add(new admin_setting_configtext('local_enrolstaff/sadf', 'Art, Design & Fashion email', '', ''));
// $settings->add(new admin_setting_configtext('local_enrolstaff/sadfid', 'Art, Design & Fashion category id', '', ''));
// $settings->add(new admin_setting_configtext('local_enrolstaff/sblc', 'Business, Law and Communications email', '', ''));
// $settings->add(new admin_setting_configtext('local_enrolstaff/sblcid', 'Business, Law and Communications category id', '', ''));
// $settings->add(new admin_setting_configtext('local_enrolstaff/smse', 'Maritime, Science & Engineering email', '', ''));
// $settings->add(new admin_setting_configtext('local_enrolstaff/smseid', 'Maritime, Science & Engineering category id', '', ''));
// $settings->add(new admin_setting_configtext('local_enrolstaff/smat', 'Media, Arts & Technology email', '', ''));
// $settings->add(new admin_setting_configtext('local_enrolstaff/smatid', 'Media, Arts & Technology category id', '', ''));
// $settings->add(new admin_setting_configtext('local_enrolstaff/sshss', 'Sport, Health & Social Sciences email', '', ''));
// $settings->add(new admin_setting_configtext('local_enrolstaff/sshssid', 'Sport, Health & Social Sciences category id', '', ''));
// $settings->add(new admin_setting_configtext('local_enrolstaff/bcc', 'Bcc email to', '', ''));
// $settings->add(new admin_setting_configtext('local_enrolstaff/emailfrom', 'Email from', '', ''));
// $settings->add(new admin_setting_configtext('local_enrolstaff/replytoltu', 'Reply to', '', ''));
$ADMIN->add('localplugins', $settings);
