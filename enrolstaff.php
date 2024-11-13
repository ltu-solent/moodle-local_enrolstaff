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

require_once('../../config.php');
require_once('form.php');

require_login(true);
$PAGE->set_context(context_system::instance());
$PAGE->set_url($CFG->wwwroot. '/local/enrolstaff/enrolstaff.php');
$PAGE->set_title(get_string('enrol-selfservice', 'local_enrolstaff'));
$PAGE->set_heading(get_string('enrol-selfservice', 'local_enrolstaff'));
$PAGE->set_pagelayout('standard');
$PAGE->navbar->add(get_string('enrol-selfservice', 'local_enrolstaff'),
    new moodle_url($CFG->wwwroot. '/local/enrolstaff/enrolstaff.php'));
$PAGE->navbar->add('Enrol onto courses');
global $USER;
$return = $CFG->wwwroot.'/local/enrolstaff/enrolstaff.php';

$action = optional_param('action', 'select_role', PARAM_ALPHANUMEXT);

if ($action == 'enrol_home') {
    redirect($CFG->wwwroot. '/local/enrolstaff/enrolstaff.php');
}
if ($action == 'unenrol') {
        redirect($CFG->wwwroot. '/local/enrolstaff/unenrolstaff.php');
}
echo $OUTPUT->header();
$activeuser = new \local_enrolstaff\local\user($USER);
if (!$activeuser->user_can_enrolself()) {
    throw new moodle_exception('cannotenrolself', 'local_enrolstaff');
}

$esconfig = get_config('local_enrolstaff');

$unitleaderroleids = explode(',', $esconfig->unitleaderid);
echo "<div class='maindiv'>";

// Role selection.
if ($action == 'select_role') {
    $rform = new role_form(null, ['activeuser' => $activeuser]);
    echo $OUTPUT->notification(get_string('enrolintro', 'local_enrolstaff'), 'notifymessage');
    if ($rform->is_cancelled()) {
        redirect($CFG->wwwroot. '/local/enrolstaff/enrolstaff.php');
    } else if ($frorform = $rform->get_data()) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedIf

    } else {
        $rform->display();
    }

    echo get_string('unenrolheader', 'local_enrolstaff');
    echo get_string('unenrolintro', 'local_enrolstaff');
    echo $OUTPUT->single_button(new moodle_url('/local/enrolstaff/unenrolstaff.php'),
        get_string('unenrolfrommodules', 'local_enrolstaff'));
}

// Course search.
if ($action == 'unit_select') {
    $role = required_param('role', PARAM_INT);
    if (!$activeuser->is_role_valid($role)) {
        throw new moodle_exception('invalidrole', 'local_enrolstaff');
    }
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
        redirect($CFG->wwwroot. '/local/enrolstaff/enrolstaff.php');
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
        $courses = $activeuser->course_search($coursesearch);
    }

    if (count($courses) > 0) {
        echo get_string('unitselect', 'local_enrolstaff');

        $cform = new course_form(null, ['courses' => $courses, 'activeuser' => $activeuser, 'role' => $roleid]);

        if ($cform->is_cancelled()) {
            redirect($CFG->wwwroot. '/local/enrolstaff/enrolstaff.php');
        } else if ($frocform = $cform->get_data()) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedIf

        } else {
            $cform->display();
        }
    } else {
        echo $OUTPUT->notification(get_string('nomatchingmodules', 'local_enrolstaff', ['coursesearch' => $coursesearch]));
        echo $OUTPUT->single_button(new moodle_url('/local/enrolstaff/enrolstaff.php'),
            get_string('enrolmenthome', 'local_enrolstaff'));
    }
}

// Confirmation.
if ($action == 'role_select') {
    $courseid = required_param('course', PARAM_INT);
    $roleid = required_param('role', PARAM_INT);
    $coursecontext = context_course::instance($courseid);
    if (!$activeuser->is_role_valid($roleid)) {
        throw new moodle_exception('invalidrole', 'local_enrolstaff');
    }
    if (!$activeuser->can_enrolselfon($courseid)) {
        throw new moodle_exception('invalidcourse', 'local_enrolstaff');
    }
    $c = $DB->get_record('course', ['id' => $courseid]);
    $r = $DB->get_record('role', ['id' => $roleid]);
    $rolename = role_get_name($r, $coursecontext);
    if (in_array($roleid, $unitleaderroleids)) {
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

    if ($esconfig->expireenrolment > 0) {
        echo html_writer::tag('p', get_string('enrolmentsexpireafter', 'local_enrolstaff', $esconfig->expireenrolment));
    }
    $startdate = new DateTime();
    $startdate->setTimestamp($c->startdate);
    $startdate = userdate($startdate->getTimestamp(), '%d/%m/%Y');

    $enddate = new DateTime();
    $enddate->setTimestamp($c->enddate);
    $enddate = userdate($enddate->getTimestamp(), '%d/%m/%Y');

    $submitparams = [
        'role' => $r->id,
        'course' => $c->id,
    ];

    $sform = new submit_form(null, $submitparams);
    if ($sform->is_cancelled()) {
        redirect($CFG->wwwroot. '/local/enrolstaff/enrolstaff.php');
    } else if ($frosform = $sform->get_data()) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedIf

    } else {
        $sform->display();
    }
}

if ($action == 'confirm_select') {
    // Inform TAR of unit leader enrolment.
    $roleid = required_param('role', PARAM_INT);
    $courseid = required_param('course', PARAM_INT);
    $coursecontext = context_course::instance($courseid);
    if (!$activeuser->is_role_valid($roleid)) {
        throw new moodle_exception('invalidrole', 'local_enrolstaff');
    }
    if (!$activeuser->can_enrolselfon($courseid)) {
        throw new moodle_exception('invalidcourse', 'local_enrolstaff');
    }
    $c = $DB->get_record('course', ['id' => $courseid]);
    $r = $DB->get_record('role', ['id' => $roleid]);
    $rolename = role_get_name($r, $coursecontext);

    $moduleleaders = \local_enrolstaff\local\api::moduleleader($courseid);
    $studentrecordsemail = get_config('local_enrolstaff', 'studentrecords');
    $studentrecords = $DB->get_record('user', ['email' => $studentrecordsemail]);
    if (!$studentrecords) {
        $studentrecords = get_admin();
    }
    $qaheemail = get_config('local_enrolstaff', 'qaheemail');
    $qahe = $DB->get_record('user', ['email' => $qaheemail]);
    if (!$qahe) {
        $qahe = get_admin();
    }

    if (in_array($roleid, $unitleaderroleids)) {
        // Send request to student registery.
        $sql = "SELECT cc1.id, c.shortname, cc1.*
                        FROM {course} c
                        JOIN {course_categories} cc ON c.category = cc.id
                        JOIN {course_categories} cc1 ON cc.parent = cc1.id
                        WHERE c.id = :courseid";
        $category = $DB->get_record_sql($sql, ['courseid' => $courseid]);

        $subject = get_string('requestemailsubject', 'local_enrolstaff', ['shortname' => $c->shortname]);
        $message = get_string('enrolrequestedschool', 'local_enrolstaff', [
            'fullname' => $c->fullname . " " . userdate($c->startdate, '%d/%m/%Y') . " - " . userdate($c->enddate, '%d/%m/%Y'),
            'rolename' => $rolename,
        ]);
        $contact = $studentrecords;
        if (\local_enrolstaff\local\api::is_partner_course($c)) {
            $contact = $qahe;
        }
        \local_enrolstaff\local\api::send_message($contact, $USER, $subject, $message);

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
        \local_enrolstaff\local\api::send_message($USER, $contact, $subject, $message);

    } else {
        $plugin = enrol_get_plugin('manual');
        $instance = $DB->get_record('enrol', ['courseid' => $c->id, 'enrol' => 'manual']);
        if (!$instance) {
            $course = $DB->get_record('course', ['id' => $c->id]);
            $fields = [
                'status'          => '0',
                'roleid'          => '5',
                'enrolperiod'     => '0',
                'expirynotify'    => '0',
                'notifyall'       => '0',
                'expirythreshold' => '86400',
            ];
            $instance = $plugin->add_instance($course, $fields);
        }
        $expiry = get_config('local_enrolstaff', 'expireenrolment') ?? 0;
        if ($expiry > 0) {
            $expiry = time() + (DAYSECS * $expiry);
        }
        $instance = $DB->get_record('enrol', ['courseid' => $c->id, 'enrol' => 'manual']);
        $plugin->enrol_user($instance, $USER->id, $r->id, time(), $expiry, null, null);
        // Is there a module leader already enrolled? Are they active? If not, send request to Registry.
        if (count($moduleleaders) == 0) {
            // Send message to registry? Perhaps other editing roles? Or perhaps lt.systems - site admin email.
            $moduleleaders[] = get_admin();
        }
        $notificationenabled = get_config('local_enrolstaff', 'enrolmentnotificationmessageenable');
        if ($notificationenabled) {
            $options = [
                'coursefullname' => $c->fullname,
                'recipientfirstname' => '',
                'rolename' => $rolename,
                'shortname' => $c->shortname,
                'userfullname' => fullname($USER),
            ];
            foreach ($moduleleaders as $moduleleader) {
                $options['recipientfirstname'] = $moduleleader->firstname;
                $options['recipientfullname'] = fullname($moduleleader);
                $subject = \local_enrolstaff\local\api::prepare_message('enrolmentnotificationsubject', $options);
                $message = \local_enrolstaff\local\api::prepare_message('enrolmentnotificationmessage', $options);
                \local_enrolstaff\local\api::send_message($moduleleader, $USER, $subject, $message);
            }
        }

        echo $OUTPUT->notification(get_string('enrolconfirmation', 'local_enrolstaff',
            ['shortname' => $c->shortname, 'rolename' => $rolename]), 'notifysuccess');
    }

    echo $OUTPUT->single_button(new moodle_url('/local/enrolstaff/enrolstaff.php'),
        get_string('enrolmenthome', 'local_enrolstaff'));
}

echo "</div>";
echo $OUTPUT->footer();
