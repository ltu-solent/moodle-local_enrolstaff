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
 * TODO describe file authorise
 *
 * @package    local_enrolstaff
 * @copyright  2025 Southampton Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\context\course;
use core\context\system;
use core\url;
use local_enrolstaff\local\api;
use local_enrolstaff\persistent\authorise;
use local_enrolstaff\persistent\rule;

require('../../config.php');
$action = optional_param('action', '', PARAM_ALPHA);
$courseid = required_param('courseid', PARAM_INT);
$signature = required_param('signature', PARAM_ALPHANUMEXT);
$token = required_param('token', PARAM_ALPHANUMEXT);

// Ensure can login to course.
require_login($courseid);
// Ensure user has permission to authorise enrolments.
$context = course::instance($courseid);
require_capability('local/enrolstaff:authoriseenrolments', $context);

$authorise = authorise::get_record(['courseid' => $courseid, 'token' => $token]);
if (!$authorise) {
    throw new moodle_exception('authorise:' . authorise::TOKEN_INVALID, 'local_enrolstaff');
}
if (($result = $authorise->check_request_token($token, $signature)) !== true) {
    throw new moodle_exception('authorise:' . $result, 'local_enrolstaff');
}
$params = [
    'action' => $action,
    'courseid' => $courseid,
    'signature' => $signature,
    'token' => $token,
];
$url = new url('/local/enrolstaff/authorise.php', $params);
$PAGE->set_url($url);

$rule = rule::get_record(['id' => $authorise->get('ruleid')]);
$c = $DB->get_record('course', ['id' => $authorise->get('courseid')], '*', MUST_EXIST);
$r = $DB->get_record('role', ['id' => $authorise->get('roleid')], '*', MUST_EXIST);
$rolename = role_get_name($r, $context);
$coursefullname = $c->fullname . " " . userdate($c->startdate, '%d/%m/%Y') . " - " . userdate($c->enddate, '%d/%m/%Y');
$requestor = core_user::get_user($authorise->get('requestorid'));
$PAGE->set_context($context);

$heading = get_string('enrolmentauthorisation', 'local_enrolstaff');
$PAGE->set_heading($heading);
echo $OUTPUT->header();

// Email receipt to user of requested.
$emailfields = [
    'recipientfirstname' => $requestor->firstname,
    'recipientlastname' => $requestor->lastname,
    'coursefullname' => format_string($coursefullname),
    'rolename' => format_string($rolename),
    'authoriserfirstname' => $USER->firstname,
    'authoriserlastname' => $USER->lastname,
    'shortname' => $c->shortname,
    'courseid' => $c->id,
];

if ($action == 'confirm') {
    $authorise->authorise_enrolment($rule);
    echo $OUTPUT->notification(get_string('enrolmentauthorisationconfirmed', 'local_enrolstaff'), 'notifysuccess');
    $subject = api::prepare_message('enrolmentauthorisationsubject', $emailfields);
    $message = api::prepare_message('enrolmentauthorisationmessageconfirmation', $emailfields);
    api::send_message($requestor, $USER, $subject, $message);
} else if ($action == 'reject') {
    // Delete the authorisation request.
    $authorise->delete();
    echo $OUTPUT->notification(get_string('enrolmentauthorisationrejected', 'local_enrolstaff'), 'notifymessage');
    $subject = api::prepare_message('enrolmentauthorisationsubject', $emailfields);
    $message = api::prepare_message('enrolmentauthorisationmessagereject', $emailfields);
    api::send_message($requestor, $USER, $subject, $message);
} else {
    // Show confirmation message.
    $confirmurl = new url('/local/enrolstaff/authorise.php', array_merge($params, ['action' => 'confirm']));
    $cancelurl = new url('/local/enrolstaff/authorise.php', array_merge($params, ['action' => 'reject']));
    $message = get_string('enrolmentauthorisationconfirmmessage', 'local_enrolstaff', [
        'course' => format_string($coursefullname),
        'role' => format_string($rolename),
        'requestor' => fullname($requestor),
    ]);
    echo $OUTPUT->confirm($message, $confirmurl, $cancelurl, [
        'continuestr' => get_string('confirm'),
        'cancelstr' => get_string('reject'),
    ]);
}

echo $OUTPUT->footer();
