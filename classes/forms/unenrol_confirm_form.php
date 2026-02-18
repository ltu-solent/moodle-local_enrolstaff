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

use core\output\html_writer;
use moodleform;

/**
 * Class unenrol_confirm_form
 *
 * @package    local_enrolstaff
 * @copyright  2025 Southampton Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class unenrol_confirm_form extends moodleform {
    /**
     * {@inheritDoc}
     *
     * @return void
     */
    public function definition() {
        global $DB;

        $mform = $this->_form;
        $customdata = $this->_customdata;
        $courses = $customdata['courses'];

        [$wheresql, $whereparams] = $DB->get_in_or_equal($courses);
        $enroledcourses = $DB->get_records_sql("SELECT id, fullname, shortname
                                FROM {course}
                                WHERE id {$wheresql}", $whereparams);

        $mform->addElement("html", get_string('unenrolwarning', 'local_enrolstaff'));
        $courselist = '';
        foreach ($enroledcourses as $course) {
            $courselist .= html_writer::tag('li', $course->shortname . ' - ' . $course->fullname);
        }
        $courselist = html_writer::tag('ul', $courselist);
        $mform->addElement("html", $courselist);

        $mform->addElement("html", "<br />");
        $mform->addElement('hidden', 'action', 'unenrol_confirm');
        $mform->setType('action', PARAM_ALPHANUMEXT);
        $mform->addElement('hidden', 'courses', join(',', $courses));
        $mform->setType('courses', PARAM_SEQUENCE);
        $this->add_action_buttons(true, get_string('confirm'));
    }
}
