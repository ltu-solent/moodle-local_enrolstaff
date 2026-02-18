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
 * TODO describe file edit
 *
 * @package    local_enrolstaff
 * @copyright  2025 Southampton Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\context\system;
use core\lang_string;
use core\output\html_writer;
use core\output\notification;
use core\output\single_button;
use core\url;
use local_enrolstaff\event\rule_updated;
use local_enrolstaff\forms\rule_form;
use local_enrolstaff\local\api;
use local_enrolstaff\persistent\rule;

require('../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$id = optional_param('id', 0, PARAM_INT);
$action = optional_param('action', 'new', PARAM_ALPHA);
$confirmdelete = optional_param('confirmdelete', null, PARAM_BOOL);

$context = system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');

$PAGE->set_context($context);

if (!in_array($action, ['edit', 'delete', 'new', 'enable', 'disable'])) {
    $action = 'new';
}
$pageparams = [
    'action' => $action,
    'id' => $id,
];

$editurl = new url('/local/enrolstaff/rule.php', $pageparams);
$manageurl = new url('/local/enrolstaff/manage.php');
admin_externalpage_setup('local_enrolstaff/managerules', '', $pageparams, '/local/enrolstaff/rule.php');
$PAGE->set_url($editurl);
$PAGE->navbar->add(get_string('localplugins'), new url('/admin/category.php?category=localplugins'));
$PAGE->navbar->add(get_string('pluginname', 'local_enrolstaff'), new url('/admin/category.php?category=local_enrolstaffcat'));
$PAGE->navbar->add(get_string('managerules', 'local_enrolstaff'), $manageurl);

$staffrule = null;
$form = null;

if (in_array($action, ['edit', 'delete', 'enable', 'disable'])) {
    if ($id == 0) {
        throw new moodle_exception('invalidid', 'local_enrolstaff');
    }
}

$staffrule = new rule($id);

if (in_array($action, ['enable', 'disable'])) {
    $enabled = ($action == 'enable');
    $staffrule->set('enabled', $enabled);
    $staffrule->save();
    api::rule_event($staffrule, $action);
    redirect(
        $manageurl,
        get_string('updated', 'local_enrolstaff', $staffrule->get('title')),
        null,
        notification::NOTIFY_SUCCESS
    );
}

$customdata = [
    'persistent' => $staffrule,
    'userid' => $USER->id,
];

if ($confirmdelete && confirm_sesskey()) {
    $title = $staffrule->get('title');
    $staffrule->delete();
    api::rule_event($staffrule, $action);
    redirect(
        $manageurl,
        get_string('deleted', 'local_enrolstaff', $title),
        null,
        notification::NOTIFY_INFO
    );
}

$form = new rule_form($editurl, $customdata);
if ($form->is_cancelled()) {
    redirect($manageurl);
}

if ($formdata = $form->get_data()) {
    if (empty($formdata->id)) {
        $rule = new rule(0, $formdata);
        $rule->create();
        api::rule_event($rule, $action);
        redirect(
            $manageurl,
            get_string('newsaved', 'local_enrolstaff'),
            null,
            notification::NOTIFY_SUCCESS
        );
    } else {
        if ($action == 'edit') {
            $staffrule->from_record($formdata);
            $staffrule->update();
            api::rule_event($staffrule, $action);
            redirect(
                $manageurl,
                get_string('updated', 'local_enrolstaff', $formdata->title),
                null,
                notification::NOTIFY_SUCCESS
            );
        }
    }
}

$PAGE->set_heading($SITE->fullname);
echo $OUTPUT->header();

if ($action == 'delete') {
    $heading = new lang_string('confirmdelete', 'local_enrolstaff', $staffrule->get('title'));
    echo html_writer::tag('h3', $heading);
    $deleteurl = new url('/local/enrolstaff/rule.php', [
        'action' => 'delete',
        'confirmdelete' => 1,
        'id' => $id,
        'sesskey' => sesskey(),
    ]);
    $deletebutton = new single_button($deleteurl, get_string('delete'));
    echo $OUTPUT->confirm(
        $heading,
        $deletebutton,
        $manageurl
    );
} else {
    $heading = new lang_string('newrule', 'local_enrolstaff');
    if ($id > 0) {
        $heading = new lang_string('editstaffrule', 'local_enrolstaff');
    }
    echo html_writer::tag('h3', $heading);
    $form->display();
}

echo $OUTPUT->footer();
