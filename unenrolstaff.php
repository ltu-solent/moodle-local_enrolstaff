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
 * Unenrol staff landing page
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
$PAGE->set_url('/local/enrolstaff/unenrolstaff.php');
$PAGE->set_title('Staff Unenrolment');
$PAGE->set_heading('Staff Unenrolment');
$PAGE->set_pagelayout('standard');
$PAGE->navbar->add(get_string('enrol-selfservice', 'local_enrolstaff'), new moodle_url('/local/enrolstaff/enrolstaff.php'));
$PAGE->navbar->add(get_string('unenrol', 'local_enrolstaff'));
global $USER;
$return = $CFG->wwwroot.'/local/enrolstaff/unenrolstaff.php';

$action = optional_param('action', 'unenrol', PARAM_ALPHANUMEXT);

if ($action == 'enrol_home') {
    redirect('/local/enrolstaff/enrolstaff.php');
}
echo $OUTPUT->header();

echo "<div class='maindiv'>";

$activeuser = new \local_enrolstaff\local\user($USER);
if (!$activeuser->user_can_enrolself()) {
    throw new moodle_exception('cannotenrolself', 'local_enrolstaff');
}

$enrolments = $activeuser->user_courses();
if (count($enrolments) == 0) {
    echo $OUTPUT->notification(get_string('nocourses', 'local_enrolstaff'));
    echo $OUTPUT->single_button(new moodle_url('/local/enrolstaff/enrolstaff.php'),
        get_string('enrolmenthome', 'local_enrolstaff'));
    $action = 'none';
}

if ($action == 'unenrol') {
    $uform = new unenrol_form(null, ['enrolments' => $enrolments]);
    if ($uform->is_cancelled()) {
        redirect('unenrolstaff.php');
    } else if ($frouform = $uform->get_data()) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedIf

    } else {
        $uform->display();
    }
}

if ($action == 'unenrol_select') {
    $courses = required_param_array('courses', PARAM_INT);
    foreach ($courses as $key => $courseid) {
        // What checks should there be that someone can unenrol themselves?
        $validcourse = $activeuser->is_enrolled_on($courseid);
        if (!$validcourse) {
            unset($courses[$key]);
        }
    }

    $cform = new unenrol_confirm(null, ['courses' => $courses]);

    if ($cform->is_cancelled()) {
        redirect('unenrolstaff.php');
    } else if ($frocform = $cform->get_data()) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedIf

    } else {
        $cform->display();
    }
}

if ($action == 'unenrol_confirm') {
    /** @var enrol_manual_plugin $pluginmanual */
    $pluginmanual = enrol_get_plugin('manual');
    /** @var enrol_flatfile_plugin $pluginflat */
    $pluginflat = enrol_get_plugin('flatfile');
    /** @var enrol_self_plugin $pluginself */
    $pluginself = enrol_get_plugin('self');
    $courses = required_param('courses', PARAM_SEQUENCE);
    $courses = explode(',', $courses);
    $enrolmentscourseids = array_column($enrolments, 'course_id');
    $listed = array_filter($courses, function($course) use ($enrolmentscourseids) {
        return in_array($course, $enrolmentscourseids);
    });
    list($insql, $inparams) = $DB->get_in_or_equal($listed, SQL_PARAMS_NAMED);
    $params = ['userid' => $USER->id] + $inparams;
    $enrolinstances = $DB->get_records_sql("SELECT ue.id ueid, e.*
        FROM {user_enrolments} ue
        JOIN {enrol} e ON e.id = ue.enrolid
        WHERE ue.userid = :userid
            AND e.courseid {$insql}", $params);

    foreach ($enrolinstances as $k => $v) {
        if ($v->enrol == 'manual') {
            $pluginmanual->unenrol_user($v, $USER->id);
        } else if ($v->enrol == 'flatfile') {
            $pluginflat->unenrol_user($v, $USER->id);
        } else if ($v->enrol == 'self') {
            $pluginself->unenrol_user($v, $USER->id);
        }
    }

    echo $OUTPUT->notification(get_string('unenrolconfirm', 'local_enrolstaff'), 'notifysuccess');
    echo $OUTPUT->single_button(new moodle_url('/local/enrolstaff/enrolstaff.php'),
        get_string('enrolmenthome', 'local_enrolstaff'));
}

echo "</div>";
echo $OUTPUT->footer();
