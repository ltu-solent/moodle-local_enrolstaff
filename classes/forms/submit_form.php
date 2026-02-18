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
 * Class submit_form
 *
 * @package    local_enrolstaff
 * @copyright  2025 Southampton Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
/**
 * Submit enrolment request.
 */
class submit_form extends moodleform {
    /**
     * {@inheritDoc}
     *
     * @return void
     */
    public function definition() {
        $mform = $this->_form;
        $data = $this->_customdata;

        $mform->addElement('hidden', 'course', $data['course']);
        $mform->setType('course', PARAM_INT);
        $mform->addElement('hidden', 'role', $data['role']);
        $mform->setType('role', PARAM_INT);
        $mform->addElement('hidden', 'action', 'confirm_select');
        $mform->setType('action', PARAM_ALPHANUMEXT);
        $this->add_action_buttons(false, get_string('confirm'));
    }
}
