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
 * Enrol staff landing page
 *
 * @package   local_enrolstaff
 * @author    Mark Sharp <mark.sharp@solent.ac.uk>
 * @copyright 2022 Solent University {@link https://www.solent.ac.uk}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\context\course;
use core\context\system;
use core\exception\moodle_exception;
use core\output\html_writer;
use core\url;
use core\user;
use local_enrolstaff\forms\course_form;
use local_enrolstaff\forms\role_form;
use local_enrolstaff\forms\search_form;
use local_enrolstaff\forms\submit_form;
use local_enrolstaff\local\api;

require_once('../../config.php');

require_login(true);
$enrolstaffurl = new url('/local/enrolstaff/enrolstaff.php');
$PAGE->set_context(system::instance());
$PAGE->set_url($enrolstaffurl);
$PAGE->set_title(get_string('enrol-selfservice', 'local_enrolstaff'));
$PAGE->set_heading(get_string('enrol-selfservice', 'local_enrolstaff'));
$PAGE->set_pagelayout('standard');
$PAGE->navbar->add(
    get_string('enrol-selfservice', 'local_enrolstaff'),
    $enrolstaffurl
);
$PAGE->navbar->add(new lang_string('enrolontocourse', 'local_enrolstaff'));
global $USER;

$action = optional_param('action', 'select_role', PARAM_ALPHANUMEXT);

if ($action == 'enrol_home') {
    redirect($enrolstaffurl);
}
if ($action == 'unenrol') {
        redirect(new url('/local/enrolstaff/unenrolstaff.php'));
}
echo $OUTPUT->header();
// This will populate the cache, if not done so already.
$activeuser = new \local_enrolstaff\local\user($USER);
if (!$activeuser->user_can_enrolself()) {
    throw new moodle_exception('cannotenrolself', 'local_enrolstaff');
}

$esconfig = get_config('local_enrolstaff');

$ruleids = $activeuser->usercache->get('ruleids');
if (count($ruleids) == 0) {
    echo html_writer::tag(
        'h2',
        get_string('norolesavailable', 'local_enrolstaff')
    ) . html_writer::tag(
        'p',
        get_string('staffselfenrolmentunavailable', 'local_enrolstaff')
    );
    echo $OUTPUT->footer();
    exit();
}

echo "<div class='maindiv'>";

// Role selection.
if ($action == 'select_role') {
    $rform = new role_form(null, ['activeuser' => $activeuser]);
    echo $OUTPUT->notification(get_string('enrolintro', 'local_enrolstaff'), 'notifymessage');
    if ($rform->is_cancelled()) {
        redirect($enrolstaffurl);
    } else if ($frorform = $rform->get_data()) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedIf
    } else {
        $rform->display();
    }

    echo get_string('unenrolheader', 'local_enrolstaff');
    echo get_string('unenrolintro', 'local_enrolstaff');
    echo $OUTPUT->single_button(
        new url('/local/enrolstaff/unenrolstaff.php'),
        get_string('unenrolfrommodules', 'local_enrolstaff')
    );
}

// Course search.
if ($action == 'unit_select') {
    $role = required_param('role', PARAM_INT);
    if (!$activeuser->is_role_valid($role)) {
        throw new moodle_exception('invalidrole', 'local_enrolstaff');
    }
    // Apart from core exclusions, most exclusions are handled by rules.
    $excludeshortname = trim($esconfig->excludeshortname);
    if ($excludeshortname == '') {
        $excludeshortname = [get_string('na', 'local_enrolstaff')];
    } else {
        $excludeshortname = explode(',', $excludeshortname);
    }
    core_collator::asort($excludeshortname, core_collator::SORT_NATURAL);

    $excludefullname = trim($esconfig->excludefullname);
    if ($excludefullname == '') {
        $excludefullname = [get_string('na', 'local_enrolstaff')];
    } else {
        $excludefullname = explode(',', $excludefullname);
    }
    core_collator::asort($excludefullname, core_collator::SORT_NATURAL);

    $qahecodes = trim($esconfig->qahecodes);
    if ($qahecodes == '') {
        $qahecodes = [get_string('na', 'local_enrolstaff')];
    } else {
        $qahecodes = explode(',', $qahecodes);
    }
    core_collator::asort($qahecodes, core_collator::SORT_NATURAL);

    echo get_string('intro', 'local_enrolstaff', [
        'excludeshortname' => join(', ', $excludeshortname),
        'excludefullname' => join(', ', $excludefullname),
        'qahecodes' => join(', ', $qahecodes)]);

    $sform = new search_form(null, ['role' => $role, 'activeuser' => $activeuser]);
    if ($sform->is_cancelled()) {
        redirect($enrolstaffurl);
    } else if ($frosform = $sform->get_data()) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedIf
    } else {
        $sform->display();
    }
}

// Course results list.
if ($action == 'search_select') {
    $coursesearch = required_param('coursesearch', PARAM_ALPHANUMEXT);
    $roleid = required_param('role', PARAM_INT);
    if (!$activeuser->is_role_valid($roleid)) {
        throw new moodle_exception('invalidrole', 'local_enrolstaff');
    }
    if ($coursesearch != '') {
        $courses = $activeuser->course_search($coursesearch, $roleid);
    }
    $r = $DB->get_record('role', ['id' => $roleid]);
    $rolename = role_get_name($r, system::instance());
    echo html_writer::tag(
        'p',
        get_string('currentsearch', 'local_enrolstaff', ['search' => $coursesearch, 'rolename' => $rolename])
    );

    if (count($courses) > 0) {
        echo get_string('unitselect', 'local_enrolstaff');

        $cform = new course_form(null, ['courses' => $courses, 'activeuser' => $activeuser, 'role' => $roleid]);

        if ($cform->is_cancelled()) {
            redirect($enrolstaffurl);
        } else if ($frocform = $cform->get_data()) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedIf
        } else {
            $cform->display();
        }
    } else {
        echo $OUTPUT->notification(get_string('nomatchingmodules', 'local_enrolstaff', ['coursesearch' => $coursesearch]));
        echo $OUTPUT->single_button(
            new url('/local/enrolstaff/enrolstaff.php'),
            get_string('enrolmenthome', 'local_enrolstaff')
        );
    }
}

// Confirmation.
if ($action == 'role_select') {
    $courseid = required_param('course', PARAM_INT);
    $roleid = required_param('role', PARAM_INT);
    $coursecontext = course::instance($courseid);
    if (!$activeuser->is_role_valid($roleid)) {
        throw new moodle_exception('invalidrole', 'local_enrolstaff');
    }
    if (!$activeuser->can_enrolselfon($courseid)) {
        throw new moodle_exception('invalidcourse', 'local_enrolstaff');
    }

    $c = $DB->get_record('course', ['id' => $courseid]);
    $r = $DB->get_record('role', ['id' => $roleid]);
    $rule = $activeuser->find_best_rule($courseid, $roleid);
    if (!$rule) {
        throw new moodle_exception('noruleapplies', 'local_enrolstaff');
    }
    $rolename = role_get_name($r, $coursecontext);

    if (in_array($rule->get('sendas'), ['authorisation', 'registryrequest'])) {
        echo get_string('requestforenrolment', 'local_enrolstaff', [
            'coursename' => $c->fullname,
            'rolename' => $rolename,
        ]);
    } else {
        echo get_string('abouttobeenrolled', 'local_enrolstaff', [
            'coursename' => $c->fullname,
            'rolename' => $rolename,
        ]);
    }

    echo $OUTPUT->notification(get_string('enrolwarning', 'local_enrolstaff'), 'notifymessage');

    if ($rule->get('duration') > 0) {
        echo html_writer::tag('p', get_string('enrolmentsexpireafter', 'local_enrolstaff', $rule->get('duration')));
    }

    $submitparams = [
        'role' => $r->id,
        'course' => $c->id,
    ];

    $sform = new submit_form(null, $submitparams);
    if ($sform->is_cancelled()) {
        redirect($enrolstaffurl);
    } else if ($frosform = $sform->get_data()) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedIf
    } else {
        $sform->display();
    }
}

if ($action == 'confirm_select') {
    $roleid = required_param('role', PARAM_INT);
    $courseid = required_param('course', PARAM_INT);
    $coursecontext = course::instance($courseid);
    if (!$activeuser->is_role_valid($roleid)) {
        throw new moodle_exception('invalidrole', 'local_enrolstaff');
    }
    if (!$activeuser->can_enrolselfon($courseid)) {
        throw new moodle_exception('invalidcourse', 'local_enrolstaff');
    }
    $c = $DB->get_record('course', ['id' => $courseid]);
    $r = $DB->get_record('role', ['id' => $roleid]);
    $rule = $activeuser->find_best_rule($courseid, $roleid);
    if (!$rule) {
        throw new moodle_exception('noruleapplies', 'local_enrolstaff');
    }
    $rolename = role_get_name($r, $coursecontext);
    $coursefullname = $c->fullname . " " . userdate($c->startdate, '%d/%m/%Y') . " - " . userdate($c->enddate, '%d/%m/%Y');
    $courseurl = new url('/course/view.php', ['id' => $c->id]);
    $contacts = $rule->get_contacts($c, $r);

    $defaultcontact = get_config('local_enrolstaff', 'defaultbackupnotify');
    $defaultcontact = user::get_user_by_email($defaultcontact);

    if (!$defaultcontact) {
        $defaultcontact = get_admin();
    }
    $backupcontact = $defaultcontact;
    if ($rule->get('sendas') != 'nonotification' && count($contacts) == 0) {
        // No module leaders or registry found, use backup contact.
        $contacts[] = $backupcontact;
    }
    $emailfields = [
        'coursefullname' => $coursefullname,
        'recipientfirstname' => '',
        'recipientfullname' => '',
        'rolename' => $rolename,
        'shortname' => $c->shortname,
        'userfullname' => fullname($USER),
        'courseurl' => $courseurl->out(),
    ];
    // Authorisation required - Enrolment not actioned until authorised.
    if ($rule->get('sendas') == 'authorisation') {
        $validitydays = get_config('local_enrolstaff', 'enrolmentauthorisationvalidity');
        $emailfields['validuntildays'] = $validitydays;
        // Validate that all email addresses exist as users.
        foreach ($contacts as $contact) {
            // Check they have permission to authorise enrolments. Maybe send to backup contact if not?
            if (!has_capability('local/enrolstaff:authoriseenrolments', course::instance($courseid), $contact->id)) {
                // It's a bit rude to send an exception to the requester here. It's not their fault.
                throw new moodle_exception('invalidnotifywithauthorisation', 'local_enrolstaff', '', $contact->email);
            }

            $emailfields['recipientfirstname'] = $contact->firstname;
            $emailfields['recipientfullname'] = fullname($contact);
            $emailfields['authorisationlink'] = api::generate_authorisation_link(
                $rule->get('id'),
                $courseid,
                $USER->id,
                $roleid,
                $contact->id,
                $validitydays
            );
            $subject = api::prepare_message('enrolmentauthorisationsubject', $emailfields);
            $message = api::prepare_message('enrolmentauthorisationmessage', $emailfields);
            api::send_message($contact, $USER, $subject, $message);
            unset($emailfields['authorisationlink']);
        }
        unset($emailfields['validuntildays']);

        // Inform user of request.
        echo $OUTPUT->notification(get_string('enrolrequestalertauthorisation', 'local_enrolstaff', [
            'shortname' => $c->shortname,
            'rolename' => $rolename,
        ]), 'notifysuccess');
    }

    // Send request to registry who will action via SRS.
    if ($rule->get('sendas') == 'registryrequest') {
        foreach ($contacts as $contact) {
            $emailfields['recipientfirstname'] = $contact->firstname;
            $emailfields['recipientfullname'] = fullname($contact);
            $subject = api::prepare_message('enrolmentregistryrequestsubject', $emailfields);
            $message = api::prepare_message('enrolmentregistryrequestmessage', $emailfields);
            api::send_message($contact, $USER, $subject, $message);
        }
        // Inform user of request.
        echo $OUTPUT->notification(get_string('enrolrequestalert', 'local_enrolstaff', [
            'schoolemail' => $studentrecordsemail,
            'shortname' => $c->shortname,
            'rolename' => $rolename,
        ]), 'notifysuccess');
        // Email receipt to user of requested.
        $subject = get_string('requestemailsubject', 'local_enrolstaff', ['shortname' => $c->shortname]);
        $message = get_string('enrolrequesteduser', 'local_enrolstaff', [
            'fullname' => $c->fullname,
            'rolename' => $rolename,
        ]);
        api::send_message($USER, $backupcontact, $subject, $message);
    }

    // Automatic enrolment, with optional notification.
    if (in_array($rule->get('sendas'), ['notification', 'nonotification'])) {
        $rule->enrol_user($USER->id, $courseid, $roleid);
        if ($rule->get('sendas') == 'notification') {
            foreach ($contacts as $contact) {
                $emailfields['recipientfirstname'] = $contact->firstname;
                $emailfields['recipientfullname'] = fullname($contact);
                $subject = api::prepare_message('enrolmentnotificationsubject', $emailfields);
                $message = api::prepare_message('enrolmentnotificationmessage', $emailfields);
                api::send_message($contact, $USER, $subject, $message);
            }
        }
        $courseurl = new url('/course/view.php', ['id' => $c->id]);
        echo $OUTPUT->notification(
            get_string('enrolconfirmation', 'local_enrolstaff', [
                'shortname' => $c->shortname,
                'rolename' => $rolename,
                'url' => $courseurl->out(),
            ]),
            'notifysuccess'
        );
    }

    echo $OUTPUT->single_button(
        new url('/local/enrolstaff/enrolstaff.php'),
        get_string('enrolmenthome', 'local_enrolstaff')
    );
}

echo "</div>";
echo $OUTPUT->footer();
