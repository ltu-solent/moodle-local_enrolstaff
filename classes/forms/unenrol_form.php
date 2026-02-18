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
 * Class unenrol_form
 *
 * @package    local_enrolstaff
 * @copyright  2025 Southampton Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
/**
 * Unenrol form
 */
class unenrol_form extends moodleform {
    /**
     * {@inheritDoc}
     *
     * @return void
     */
    public function definition() {
        $mform = $this->_form;
        $customdata = $this->_customdata;
        $enrolments = $customdata['enrolments'];

        echo get_string('unenrolselect', 'local_enrolstaff');
        $ccount = 0;
        foreach ($enrolments as $course) {
            $odd = $ccount & 1;
            $style = $odd ? 'background-color: var(--gray-200)' : 'background-color: var(--gray-100)';
            $enddate = ($course->enddate > 0) ? date('d/m/Y', $course->enddate) : '';
            $label = get_string('courselabel', 'local_enrolstaff', [
                'fullname' => $course->fullname,
                'idnumber' => $course->shortname,
                'startunix' => date('d/m/Y', $course->startdate),
                'endunix' => $enddate,
            ]);
            $label .= '<br />' . get_string('existingroles', 'local_enrolstaff', $course->roles);

            $mform->addElement("html", '
                <div class="w-100 p1" style="' . $style . '">
                            <input name="courses[]" type="checkbox" value="' . $course->course_id . '" id="id_courses">
                            <label for="id_courses">' . $label . '</label>
                </div>');
            $ccount++;
        }

        $mform->addElement('hidden', 'action', 'unenrol_select');
        $mform->setType('action', PARAM_ALPHANUMEXT);
        $this->add_action_buttons(false, get_string('unenrol', 'local_enrolstaff'));
    }
}
