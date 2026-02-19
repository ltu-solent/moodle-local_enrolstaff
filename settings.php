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
 * This file defines the admin settings for this plugin
 *
 * @package   local_enrolstaff
 * @copyright 2018 Southampton Solent University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\url;
use local_adminsettingsconfig\admin_setting_configregex;
use local_enrolstaff\local\api;

defined('MOODLE_INTERNAL') || die;

$parent = new admin_category('local_enrolstaffcat', new lang_string('pluginname', 'local_enrolstaff'));

if ($hassiteconfig) {
    $ADMIN->add('localplugins', $parent);
    // External page: Manage rules.
    $ADMIN->add(
        'local_enrolstaffcat',
        new admin_externalpage(
            'local_enrolstaff/managerules',
            new lang_string('managerules', 'local_enrolstaff'),
            new url('/local/enrolstaff/manage.php')
        )
    );

    // Settings page.
    $settings = new admin_settingpage('local_enrolstaff_settings', new lang_string('adminsettings', 'local_enrolstaff'));

    $commaseparated = new lang_string('commaseparatedlist', 'local_enrolstaff');
    // Heading: Restricted sets.
    $settings->add(
        new admin_setting_heading(
            'local_enrolstaff/restrictedsets',
            new lang_string('restrictedsets', 'local_enrolstaff'),
            new lang_string('restrictedsets_desc', 'local_enrolstaff')
        )
    );

    // Available departments.
    $settings->add(
        new admin_setting_configtext(
            'local_enrolstaff/availabledepartments',
            new lang_string('availabledepartments', 'local_enrolstaff'),
            new lang_string('availabledepartments_desc', 'local_enrolstaff'),
            'academic,management,support',
            PARAM_TEXT,
            100
        )
    );

    // Available roles (for enrolment).
    $choices = api::get_course_level_roles_menu();
    $settings->add(
        new admin_setting_configmultiselect(
            'local_enrolstaff/availableroles',
            new lang_string('availableroles', 'local_enrolstaff'),
            new lang_string('availableroles_desc', 'local_enrolstaff'),
            null,
            $choices
        )
    );

    // Available Cohorts.
    $settings->add(
        new admin_setting_configmultiselect(
            'local_enrolstaff/availablecohorts',
            new lang_string('availablecohorts', 'local_enrolstaff'),
            new lang_string('availablecohorts_desc', 'local_enrolstaff'),
            null,
            api::get_system_cohorts_menu()
        )
    );

    // Available Notify roles.
    $choices = api::get_course_level_roles_menu(['editingteacher']);
    $settings->add(
        new admin_setting_configmultiselect(
            'local_enrolstaff/availablenotifyroles',
            new lang_string('availablenotifyroles', 'local_enrolstaff'),
            new lang_string('availablenotifyroles_desc', 'local_enrolstaff'),
            null,
            $choices
        )
    );

    // Available Backup notify email addresses.
    $settings->add(
        new admin_setting_configtext(
            'local_enrolstaff/availablebackupnotify',
            new lang_string('availablebackupnotify', 'local_enrolstaff'),
            new lang_string('availablebackupnotify_desc', 'local_enrolstaff'),
            '',
            PARAM_TAGLIST,
            100
        )
    );

    // Available Registry email addresses.
    $settings->add(
        new admin_setting_configtext(
            'local_enrolstaff/availableregistryemails',
            new lang_string('availableregistryemails', 'local_enrolstaff'),
            new lang_string('availableregistryemails_desc', 'local_enrolstaff'),
            '',
            PARAM_TAGLIST,
            100
        )
    );

    // Heading: Default settings.
    $settings->add(
        new admin_setting_heading(
            'local_enrolstaff/defaultsettings',
            new lang_string('defaultsettings', 'local_enrolstaff'),
            new lang_string('defaultsettings_desc', 'local_enrolstaff')
        )
    );

    // Default enrolment duration.
    $settings->add(
        new admin_setting_configselect(
            'local_enrolstaff/defaultexpireenrolments',
            new lang_string('defaultexpireenrolments', 'local_enrolstaff'),
            new lang_string('defaultexpireenrolments_desc', 'local_enrolstaff'),
            0,
            api::get_duration_menu()
        )
    );

    // Regular expression help.
    $settings->add(
        new admin_setting_description(
            'local_enrolstaff/regexhelp',
            '',
            new lang_string('regexhelp', 'local_enrolstaff')
        )
    );

    // Default email pattern.
    $settings->add(
        new admin_setting_configregex(
            'local_enrolstaff/defaultemailpattern',
            new lang_string('defaultemailpattern', 'local_enrolstaff'),
            new lang_string('defaultemailpattern_desc', 'local_enrolstaff'),
            '@solent.ac.uk',
            PARAM_RAW,
            '100',
            '1'
        )
    );

    // Default username pattern.
    $settings->add(
        new admin_setting_configregex(
            'local_enrolstaff/defaultusernamepattern',
            new lang_string('defaultusernamepattern', 'local_enrolstaff'),
            new lang_string('defaultusernamepattern_desc', 'local_enrolstaff'),
            '',
            PARAM_RAW,
            '100',
            '1'
        )
    );

    // Default exclude email pattern.
    $settings->add(
        new admin_setting_configregex(
            'local_enrolstaff/defaultexemailpattern',
            new lang_string('defaultexemailpattern', 'local_enrolstaff'),
            new lang_string('defaultexemailpattern_desc', 'local_enrolstaff'),
            '',
            PARAM_RAW,
            '100',
            '1'
        )
    );

    // Default exclude username pattern.
    $settings->add(
        new admin_setting_configregex(
            'local_enrolstaff/defaultexusernamepattern',
            new lang_string('defaultexusernamepattern', 'local_enrolstaff'),
            new lang_string('defaultexusernamepattern_desc', 'local_enrolstaff'),
            '',
            PARAM_RAW,
            '100',
            '1'
        )
    );

    // Default departments.
    $settings->add(
        new admin_setting_configtext(
            'local_enrolstaff/defaultdepartments',
            new lang_string('defaultdepartments', 'local_enrolstaff'),
            new lang_string('defaultdepartments_desc', 'local_enrolstaff'),
            'academic',
            PARAM_TEXT,
            100
        )
    );

    // Default auth.
    $settings->add(
        new admin_setting_configselect(
            'local_enrolstaff/defaultauths',
            new lang_string('defaultauths', 'local_enrolstaff'),
            new lang_string('defaultauths_desc', 'local_enrolstaff'),
            '',
            api::get_auth_menu(),
        )
    );

    // Default notify role.
    $settings->add(
        new admin_setting_configtext(
            'local_enrolstaff/defaultnotifyroles',
            new lang_string('defaultnotifyroles', 'local_enrolstaff'),
            new lang_string('defaultnotifyroles_desc', 'local_enrolstaff'),
            '',
            PARAM_TEXT,
            100
        )
    );

    // Default backup notify, if no-one is an 'editingteacher' on the page.
    $supportemail = (isset($CFG->supportemail)) ? clean_param($CFG->supportemail, PARAM_EMAIL) : '';
    $settings->add(
        new admin_setting_configtext(
            'local_enrolstaff/defaultbackupnotify',
            new lang_string('defaultbackupnotify', 'local_enrolstaff'),
            new lang_string('defaultbackupnotify_desc', 'local_enrolstaff'),
            $supportemail,
            PARAM_EMAIL,
            100
        )
    );

    // Default Registry email, if the role is an 'editingteacher'. Registry will process.
    $settings->add(
        new admin_setting_configtext(
            'local_enrolstaff/defaultregistryemail',
            new lang_string('defaultregistryemail', 'local_enrolstaff'),
            new lang_string('defaultregistryemail_desc', 'local_enrolstaff'),
            '',
            PARAM_EMAIL,
            100
        )
    );

    // Heading: Hard rules.
    $settings->add(
        new admin_setting_heading(
            'local_enrolstaff/hardsettings',
            new lang_string('hardsettings', 'local_enrolstaff'),
            new lang_string('hardsettings_desc', 'local_enrolstaff')
        )
    );
    // Exclude course shortname.
    $settings->add(
        new admin_setting_configtextarea(
            'local_enrolstaff/excludeshortname',
            new lang_string('excludeshortname', 'local_enrolstaff'),
            new lang_string('excludeshortname_desc', 'local_enrolstaff'),
            ''
        )
    );
    // Exclude fullname.
    $settings->add(new admin_setting_configtextarea(
        'local_enrolstaff/excludefullname',
        new lang_string('excludefullname', 'local_enrolstaff'),
        new lang_string('excludefullname_desc', 'local_enrolstaff'),
        ''
    ));

    // Exclude course IDs.
    $settings->add(
        new admin_setting_configtext(
            'local_enrolstaff/excludeid',
            get_string('excludeid', 'local_enrolstaff'),
            $commaseparated,
            ''
        )
    );

    $settings->add(
        new admin_setting_heading(
            'local_enrolstaff/notificationtemplates',
            new lang_string('notificationtemplates', 'local_enrolstaff'),
            new lang_string('notificationtemplates_desc', 'local_enrolstaff')
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'local_enrolstaff/enrolmentauthorisationsubject',
            new lang_string('enrolmentauthorisationsubject', 'local_enrolstaff'),
            new lang_string('enrolmentauthorisationsubject_desc', 'local_enrolstaff'),
            new lang_string('enrolmentauthorisationsubject_default', 'local_enrolstaff'),
            PARAM_TEXT,
            100
        )
    );

    $settings->add(
        new admin_setting_configtextarea(
            'local_enrolstaff/enrolmentauthorisationmessage',
            new lang_string('enrolmentauthorisationmessage', 'local_enrolstaff'),
            new lang_string('enrolmentauthorisationmessage_desc', 'local_enrolstaff'),
            new lang_string('enrolmentauthorisationmessage_default', 'local_enrolstaff'),
            PARAM_RAW,
            120,
            15
        )
    );

    $settings->add(
        new admin_setting_configtextarea(
            'local_enrolstaff/enrolmentauthorisationmessageconfirmation',
            new lang_string('enrolmentauthorisationmessageconfirmation', 'local_enrolstaff'),
            new lang_string('enrolmentauthorisationmessageconfirmation_desc', 'local_enrolstaff'),
            new lang_string('enrolmentauthorisationmessageconfirmation_default', 'local_enrolstaff'),
            PARAM_RAW,
            120,
            15
        )
    );

    $choice = [
        7   => new lang_string('numdays', '', 7),
        14  => new lang_string('numdays', '', 14),
        21  => new lang_string('numdays', '', 21),
        28  => new lang_string('numdays', '', 28),

    ];
    $settings->add(
        new admin_setting_configselect(
            'local_enrolstaff/enrolmentauthorisationvalidity',
            new lang_string('enrolmentauthorisationvalidity', 'local_enrolstaff'),
            new lang_string('enrolmentauthorisationvalidity_desc', 'local_enrolstaff'),
            7,
            $choice
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'local_enrolstaff/enrolmentnotificationsubject',
            new lang_string('enrolmentnotificationsubject', 'local_enrolstaff'),
            new lang_string('enrolmentnotificationsubject_desc', 'local_enrolstaff'),
            new lang_string('enrolmentnotificationsubject_default', 'local_enrolstaff'),
            PARAM_TEXT,
            100
        )
    );

    $settings->add(
        new admin_setting_configtextarea(
            'local_enrolstaff/enrolmentnotificationmessage',
            new lang_string('enrolmentnotificationmessage', 'local_enrolstaff'),
            new lang_string('enrolmentnotificationmessage_desc', 'local_enrolstaff'),
            new lang_string('enrolmentnotificationmessage_default', 'local_enrolstaff'),
            PARAM_RAW,
            120,
            15
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'local_enrolment/enrolmentregistryrequestsubject',
            new lang_string('enrolmentregistryrequestsubject', 'local_enrolstaff'),
            new lang_string('enrolmentregistryrequestsubject_desc', 'local_enrolstaff'),
            new lang_string('enrolmentregistryrequestsubject_default', 'local_enrolstaff'),
            PARAM_TEXT,
            100
        )
    );

    $settings->add(
        new admin_setting_configtextarea(
            'local_enrolment/enrolmentregistryrequestmessage',
            new lang_string('enrolmentregistryrequestmessage', 'local_enrolstaff'),
            new lang_string('enrolmentregistryrequestmessage_desc', 'local_enrolstaff'),
            new lang_string('enrolmentregistryrequestmessage_default', 'local_enrolstaff'),
            PARAM_RAW,
            120,
            15
        )
    );

    // Heading: Old settings.
    $settings->add(
        new admin_setting_heading(
            'local_enrolstaff/oldsettings',
            new lang_string('oldsettings', 'local_enrolstaff'),
            new lang_string('oldsettings_desc', 'local_enrolstaff')
        )
    );


    $settings->add(
        new admin_setting_configtext(
            'local_enrolstaff/unitleaderid',
            get_string('unitleaderid', 'local_enrolstaff'),
            $commaseparated,
            '19'
        )
    );
    $settings->add(
        new admin_setting_configtext(
            'local_enrolstaff/roleids',
            get_string('roleidssolent', 'local_enrolstaff'),
            $commaseparated,
            '0'
        )
    );
    $settings->add(
        new admin_setting_configtext(
            'local_enrolstaff/qaheroleids',
            get_string('roleidsqahe', 'local_enrolstaff'),
            $commaseparated,
            '0'
        )
    );
    $settings->add(
        new admin_setting_configtextarea(
            'local_enrolstaff/qahecodes',
            get_string('codesqahe', 'local_enrolstaff'),
            $commaseparated,
            ''
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'local_enrolstaff/studentrecords',
            get_string('studentrecords', 'local_enrolstaff'),
            '',
            ''
        )
    );

    $name = new lang_string('qahecontact', 'local_enrolstaff');
    $desc = new lang_string('qahecontact_desc', 'local_enrolstaff');
    $settings->add(new admin_setting_configtext('local_enrolstaff/qahecontact', $name, $desc, ''));

    $name = new lang_string('enrolmentnotificationmessageenable', 'local_enrolstaff');
    $desc = new lang_string('enrolmentnotificationmessageenable_desc', 'local_enrolstaff');
    $settings->add(new admin_setting_configcheckbox('local_enrolstaff/enrolmentnotificationmessageenable', $name, $desc, 1));

    $ADMIN->add('local_enrolstaffcat', $settings);
}
