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
 * Language file
 *
 * @package   local_enrolstaff
 * @author    Mark Sharp <mark.sharp@solent.ac.uk>
 * @copyright 2022 Solent University {@link https://www.solent.ac.uk}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['abouttobeenrolled'] = 'You are about to be enrolled on <strong>{$a->coursename}</strong> with the role of <strong>{$a->rolename}</strong><br /><br />';
$string['addmore'] = "Enrol on another module";
$string['cannotenrolself'] = 'You are not permitted to do this action';
$string['codesbcas'] = "Comma separated list of BCAS module code prefixes";
$string['codesqahe'] = "Comma separated list of QAHE module code prefixes";
$string['commaseparatedlist'] = 'Comma separated list';
$string['courselabel'] = '{$a->idnumber} - {$a->fullname} - Start date: {$a->startunix}';
$string['coursesearch'] = "Module code";
$string['currentcourses'] = 'Current enrolments';

$string['enrolconfirmation'] = 'You have been enrolled on {$a->shortname} as {$a->rolename}';
$string['enrolintro'] = "<p>To remove yourself as module leader from a module page, <strong><a href='mailto:student.registry@solent.ac.uk?subject=Module%20Leader%20enrolment%20deletion%20request&amp;body=Dear%20Student%20Registry,%0D%0A%0D%0APlease%20remove%20me%20as%20module%20leader%20from%20module'>please click here to generate an email template to send to Student Registry</a></strong></p>";
$string['enrolmenthome'] = 'Enrolment home';
$string['enrolmentnotificationmessage'] = 'Notification message';
$string['enrolmentnotificationmessage_desc'] = 'You can include the following codes in the message [coursefullname], [recipientfirstname], [rolename], [shortname], [userfullname]';
$string['enrolmentnotificationmessage_default'] = "Dear [recipientfirstname],

[userfullname] has been enrolled on \"[coursefullname]\" with the \"[rolename]\" role.

Depending on the role, they may be able to edit your page, reuse the content on their page, and view participants' details.

Kind regards
";
$string['enrolmentnotificationmessageenable'] = 'Enable Module Leader notifications';
$string['enrolmentnotificationmessageenable_desc'] = 'Should emails be sent to the module leader when someone self-enrols?';
$string['enrolmentnotificationsubject'] = 'Subject of notification email';
$string['enrolmentnotificationsubject_default'] = 'New enrolment on [shortname]';
$string['enrolmentnotificationsubject_desc'] = 'You can include the following codes in the subject [coursefullname], [rolename], [shortname], [userfullname]';
$string['enrolmentsexpireafter'] = 'Enrolments expire after {$a} days. You may reenrol any time. We do this to help keep unnecessary enrolments down.';
$string['enrolrequestalert'] = '{$a->schoolemail} has been sent a request for you to be enrolled on {$a->shortname} as {$a->rolename}. <br /><br /> You will receive an email confirmation with further information.';
$string['enrolrequestedschool'] = 'Please enrol me on {$a->fullname} as {$a->rolename}.';
$string['enrolrequesteduser'] = 'This is confirmation of your request to be added to {$a->fullname} as {$a->rolename}. The turnaround time for processing module leader requests is 2-3 working days. If you do not have access to the module after that time, please let us know.';
$string['enrolwarning'] = "Please only enrol yourself on this module if you are teaching on it or have a specific operational requirement.";
$string['enrol-selfservice'] = "Staff enrolment self-service";
$string['enrolstaff:managestaffenrolments'] = "Manage staff enrolments";
$string['excludefullname'] = "Exclude from search by fullname (comma separated)";
$string['excludeid'] = "Exclude from search by id (comma separated)";
$string['excludeshortname'] = "Exclude from search by shortname (comma separated)";
$string['existingroles'] = ' <strong>(already enrolled as {$a})</strong>';
$string['expireenrolments'] = 'Expire enrolments';
$string['expireenrolments_desc'] = 'How long should the enrolment last. Expiry date is calculated when the enrolment is created, and can be altered after the fact.';

$string['intro'] = 'Please speak to the Module or Course Leader if you are unsure of the correct module or instance code.<br /><br /><strong>Modules that may contain sensitive data or have lecturers studying as students have been excluded from this service:</strong>
    <ul>
        <li>Module codes containing: {$a->excludeshortname}</li>
        <li>Module names containing: {$a->excludefullname}</li>
        <li>Modules removed from search results at the request of the module leader</li>
        <li>QAHE staff are restricted to searching for module codes with the prefixes: {$a->qahecodes}</li>
    </ul>
    If your module does not appear in the search results, or if you feel a module should be excluded from this service please contact guided.learning@solent.ac.uk<br /><br />';
$string['invalidcourse'] = 'You have selected an invalid course.';
$string['invalidrole'] = "You have selected an invalid role.";

$string['na'] = 'n/a';
$string['neverexpire'] = 'Never expire';
$string['nocourses'] = "You are not enrolled on any courses that can be un-enrolled from via this service.";
$string['nomatchingmodules'] = 'No modules match the term <strong>{$a->coursesearch}</strong>';
$string['nopermission'] = "You do not have permission to view this page (please check you are logged in).";
$string['otherunitleaders'] = "Other module leaders currently enrolled on this module are: ";
$string['pluginname'] = "Staff Enrolment";

$string['qahecontact'] = 'QAHE contact email';
$string['qahecontact_desc'] = 'This account will be emailed when someone requests to be enrolled on a QA module as Module leader';
$string['requestemailsubject'] = 'Module Leader enrolment request for {$a->shortname}';
$string['requestforenrolment'] = 'You are about to send a request for enrolment on <strong>{$a->coursename}</strong> with the role of <strong>{$a->rolename}</strong><br /><br />';
$string['role'] = "Select a role";
$string['roleidssolent'] = 'Comma separated list of Solent role IDs';
$string['roleidsbcas'] = 'Comma separated list of BCAS role IDs';
$string['roleidsqahe'] = 'Comma separated list of QAHE role IDs';
$string['selectmodule'] = 'Select module';
$string['selectamodule'] = 'Select a module';
$string['selectarole'] = 'Select a role';
$string['sendrequest'] = 'You are about to be enrolled on <strong>{$a->fullname}</strong> with the role of <strong>{$a->role}</strong><br /><br />';
$string['sendrequestleader'] = 'You are about to send a request for enrolment on <strong>{$a->fullname}</strong> with the role of <strong>{$a->role}</strong><br /><br />';
$string['studentrecords'] = 'Student records email';
$string['unenrolconfirm'] = "You have been unenrolled from your selected modules.";
$string['unenrol'] = "Unenrol from modules";
$string['unenrolfrommodules'] = 'Unenrol from modules';
$string['unenrolheader'] = "<br /><hr><h2>Staff unenrolment self-service</h2>";
$string['unenrolintro'] = "To un-enrol yourself from modules you no longer require access to as tutor, non-editing tutor or technician please use the unenrolment self-service:<br /><br />";
$string['unenrolselect'] = "Select the modules you wish to unenrol from:<br /><br />";
$string['unenrolwarning'] = "You have selected to unenrol from the following modules:<br /><br />";
$string['unitleaderid'] = "Module leader role id";
$string['unitselect'] = "Select a module from the list.<br /><br />If you are already enrolled on a module it will appear in the list but won't be available for selection.<br /><br />";
