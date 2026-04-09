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

use core\form\persistent;
use core\lang_string;
use local_enrolstaff\local\api;
use local_enrolstaff\persistent\rule;

/**
 * Class rule_form
 *
 * @package    local_enrolstaff
 * @copyright  2025 Southampton Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rule_form extends persistent {
    /**
     * Persistent class for this form
     *
     * @var string
     */
    protected static $persistentclass = \local_enrolstaff\persistent\rule::class;

    /**
     * Form definition
     *
     * @return void
     */
    public function definition() {
        global $CFG;
        $mform = $this->_form;
        $formdata = $this->get_persistent();

        $required = new lang_string('required');
        $size = ['size' => 100];
        $multiple = ['multiple' => true];
        $multiplewithtags = ['multiple' => true, 'tags' => true];

        // Field: Title.
        $mform->addElement('text', 'title', new lang_string('title', 'local_enrolstaff'), $size);
        $mform->addRule('title', $required, 'required', null, 'client');

        // Field: Enabled.
        $mform->addElement('selectyesno', 'enabled', new lang_string('enabled', 'local_enrolstaff'));

        // Heading: Filters.
        $mform->addElement('header', 'filter', new lang_string('filter', 'local_enrolstaff'));

        // Filter help.
        $mform->addElement('static', 'filterhelp', '', new lang_string('filterhelp', 'local_enrolstaff'));

        // Filter validation. At least one filter must be chosen.
        $chosen = array_filter(rule::ONEOF, function ($item) use ($formdata) {
            $value = $formdata->get($item);
            return !empty($value);
        });
        if (count($chosen) == 0) {
            $mform->addElement('static', 'chooseone', '', new lang_string('chooseone', 'local_enrolstaff'));
            $mform->addElement('hidden', 'chooseoneerror', 1);
            $mform->setType('chooseoneerror', PARAM_BOOL);
            // Set Enabled field to false if no Filters are active.
            $mform->setDefault('enabled', false);
            $mform->disabledIf('enabled', 'chooseoneerror', 'eq', 1);
        }

        // Field: Email pattern.
        $mform->addElement('text', 'email', new lang_string('field:emailpattern', 'local_enrolstaff'), $size);
        $mform->addHelpButton('email', 'field:emailpattern', 'local_enrolstaff');
        $defaultemail = get_config('local_enrolstaff', 'defaultemailpattern');
        $mform->setDefault('email', $defaultemail);
        $mform->setType('email', PARAM_RAW);

        // Field: Username pattern.
        $mform->addElement('text', 'username', new lang_string('field:usernamepattern', 'local_enrolstaff'), $size);
        $mform->addHelpButton('username', 'field:usernamepattern', 'local_enrolstaff');
        $mform->setDefault('username', get_config('local_enrolstaff', 'defaultusernamepattern'));
        $mform->setType('username', PARAM_RAW);

        // Field: Exclude email pattern.
        $mform->addElement('text', 'exemail', new lang_string('field:exemailpattern', 'local_enrolstaff'), $size);
        $mform->addHelpButton('exemail', 'field:exemailpattern', 'local_enrolstaff');
        $mform->setDefault('exemail', get_config('local_enrolstaff', 'defaultexemailpattern'));
        $mform->setType('exemail', PARAM_RAW);

        // Field: Exclude username pattern.
        $mform->addElement('text', 'exusername', new lang_string('field:exusernamepattern', 'local_enrolstaff'), $size);
        $mform->addHelpButton('exusername', 'field:exusernamepattern', 'local_enrolstaff');
        $mform->setDefault('exusername', get_config('local_enrolstaff', 'defaultexusernamepattern'));
        $mform->setType('exusername', PARAM_RAW);

        // Field: Departments.
        $mform->addElement(
            'autocomplete',
            'departments',
            new lang_string('departments', 'local_enrolstaff'),
            api::get_depts_menu(),
            $multiple
        );
        $mform->setType('departments', PARAM_TAGLIST);
        $mform->addHelpButton('departments', 'field:departments', 'local_enrolstaff');

        // Field: Cohorts.
        $mform->addElement(
            'autocomplete',
            'cohortids',
            new lang_string('cohorts', 'local_enrolstaff'),
            api::get_cohorts_menu(),
            $multiple
        );
        $mform->addHelpButton('cohortids', 'field:cohortids', 'local_enrolstaff');

        // Field: Institution.
        $mform->addElement('text', 'institution', new lang_string('institution', 'local_enrolstaff'), $size);
        $mform->addHelpButton('institution', 'field:institution', 'local_enrolstaff');

        // Field: Authentication methods.
        $choices = api::get_auth_menu();
        $mform->addElement(
            'autocomplete',
            'auths',
            new lang_string('field:authenticationmethods', 'local_enrolstaff'),
            $choices,
            $multiple
        );
        $default = get_config('local_enrolstaff', 'defaultauths');
        $mform->setDefault('auths', $default);
        $mform->addHelpButton('auths', 'field:authenticationmethods', 'local_enrolstaff');

        // Header: Permissions.
        $mform->addElement('header', 'permissions', new lang_string('permissions', 'local_enrolstaff'));

        // Field: Roles.
        $roles = api::get_roles_menu();
        $mform->addElement('autocomplete', 'roleids', new lang_string('field:roleids', 'local_enrolstaff'), $roles, $multiple);
        $mform->addRule('roleids', $required, 'required', null, 'client');
        $mform->addHelpButton('roleids', 'field:roleids', 'local_enrolstaff');

        // Field: Enrolment duration.
        $choices = api::get_duration_menu();
        $mform->addElement('select', 'duration', new lang_string('field:duration', 'local_enrolstaff'), $choices);
        $mform->addRule('duration', $required, 'required', null, 'client');
        $mform->addHelpButton('duration', 'field:duration', 'local_enrolstaff');

        // Field: Module codes.
        $mform->addElement('autocomplete', 'codes', new lang_string('field:codes', 'local_enrolstaff'), [], $multiplewithtags);
        $mform->addHelpButton('codes', 'field:codes', 'local_enrolstaff');

        // Field: Excluded Module codes.
        $mform->addElement('autocomplete', 'excodes', new lang_string('field:excodes', 'local_enrolstaff'), [], $multiplewithtags);
        $mform->addHelpButton('excodes', 'field:excodes', 'local_enrolstaff');

        // Header: Actions.
        $mform->addElement('header', 'actions', new lang_string('actions', 'local_enrolstaff'));

        // Field: Send as.
        $choices = api::get_sendas_menu();
        $mform->addElement('select', 'sendas', new lang_string('field:sendas', 'local_enrolstaff'), $choices);
        $mform->addHelpButton('sendas', 'field:sendas', 'local_enrolstaff');
        $mform->addRule('sendas', $required, 'required', 'client');

        $mform->addElement('static', 'notificationhelp', '', new lang_string('notificationhelp', 'local_enrolstaff'));
        $mform->hideIf('notificationhelp', 'sendas', 'eq', 'nonotification');
        $mform->hideIf('notificationhelp', 'sendas', 'eq', 'registryrequest');

        $choices = api::get_registryemail_menu();
        $mform->addElement('select', 'registrycontact', new lang_string('field:registrycontact', 'local_enrolstaff'), $choices);
        $mform->addHelpButton('registrycontact', 'field:registrycontact', 'local_enrolstaff');
        $mform->hideIf('registrycontact', 'sendas', 'neq', 'registryrequest');

        $this->add_action_buttons();
    }
}
