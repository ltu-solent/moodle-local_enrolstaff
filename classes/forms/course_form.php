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

namespace local_enrolstaff\forms;

use moodleform;

/**
 * Class course_form
 *
 * @package    local_enrolstaff
 * @copyright  2025 Southampton Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
/**
 * Allow user to choose which course to enrol on from the searched courses.
 */
class course_form extends moodleform {
    /**
     * {@inheritDoc}
     *
     * @return void
     */
    public function definition() {
        global $DB, $OUTPUT;

        $mform = $this->_form;
        $customdata = $this->_customdata;
        // Courses will now be [$ruleid => $courses].
        $courses = [];
        $ruleids = array_keys($customdata['courses']);
        foreach ($customdata['courses'] as $ruleid => $rcourses) {
            $courses = array_merge($rcourses, $courses);
        }
        $activeuser = $customdata['activeuser'];
        $role = $customdata['role'];

        $enrolledon = $DB->get_records_sql("SELECT ra.id as id, r.id role_id, c.id course_id, r.name, r.shortname
            FROM {course} c
            JOIN {context} ctx ON c.id = ctx.instanceid
            JOIN {role_assignments} ra ON ra.contextid = ctx.id
            JOIN {role} r ON ra.roleid = r.id
            JOIN {user} u ON u.id = ra.userid
            WHERE u.id = :userid", ['userid' => $activeuser->user->id]);

        // Loop through and add role names string and id to array id=>2 roles=>student, teacher etc.
        $coursearray = [];
        $countenrolledon = count($enrolledon);
        // Initialise the arrays to avoid offsets.
        for ($x = 0; $x < $countenrolledon; $x++) {
            foreach ($enrolledon as $evalue) {
                if (!array_key_exists($evalue->course_id, $coursearray)) {
                    $coursearray[$evalue->course_id] = '';
                }
            }
        }

        // Fill arrays.
        if (!empty($enrolledon)) {
            foreach ($enrolledon as $evalue) {
                $rolename = !empty($evalue->name) ? $evalue->name : ucwords($evalue->shortname);
                $coursearray[$evalue->course_id] .= $rolename . ', ';
            }
        }

        // Then loop through that array and check for roles.
        $radioarray = [];
        $ccount = 0;

        foreach ($courses as $course) {
            $odd = $ccount & 1;
            $style = $odd ? 'background-color: var(--gray-200)' : 'background-color: var(--gray-100)';
            $fullname = explode('(Start', $course->fullname);
            $courselabel = get_string('courselabel', 'local_enrolstaff', [
                'idnumber' => $course->idnumber,
                'fullname' => $fullname[0],
                'startunix' => date('d/m/Y', $course->startunix),
                'endunix' => date('d/m/Y', $course->endunix),
            ]);
            if (array_key_exists($course->id, $coursearray)) {
                $style = 'background-color: var(--teal)';
                $radioarray[] =& $mform->createElement(
                    'radio',
                    'course',
                    '',
                    '<div class="w-100 p-1" style="' . $style . '">' . $courselabel .
                    '<br>' . get_string('existingroles', 'local_enrolstaff', rtrim($coursearray[$course->id], ", ")) .
                    '</div>',
                    $course->id,
                    'disabled'
                );
            } else {
                $radioarray[] =& $mform->createElement(
                    'radio',
                    'course',
                    '',
                    '<div class="w-100 p-1" style="' . $style . '">' . $courselabel . '</div>',
                    $course->id,
                    'required'
                );
            }
            $ccount++;
        }
        $action = 'role_select';
        $buttonlabel = get_string('selectmodule', 'local_enrolstaff');
        if (empty($radioarray)) {
            echo $OUTPUT->notification(get_string('nocoursesfound', 'local_enrolstaff'), 'notifyproblem');
            $action = 'unit_select';
            $buttonlabel = get_string('searchagain', 'local_enrolstaff');
        } else {
            $mform->addGroup(
                $radioarray,
                'radioar',
                get_string('selectamodule', 'local_enrolstaff'),
                '<br />',
                false
            );
            $mform->addGroupRule('radioar', get_string('required'), 'required');
        }

        $mform->addElement('hidden', 'action', $action);
        $mform->setType('action', PARAM_ALPHANUMEXT);
        $mform->addElement('hidden', 'role', $role);
        $mform->setType('role', PARAM_INT);
        $mform->addElement('hidden', 'ruleids', implode(',', $ruleids));
        $mform->setType('ruleids', PARAM_SEQUENCE);
        $this->add_action_buttons(false, $buttonlabel);
    }
}
