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
$string['actions'] = 'Actions';
$string['addmore'] = "Enrol on another module";
$string['adminsettings'] = 'Settings';
$string['and'] = ' AND ';
$string['authorisation'] = 'Authorisation';
$string['authorise:invalidauthoriserid'] = 'The authoriser id is invalid.';
$string['authorise:invalidsignature'] = 'The signature is invalid.';
$string['authorise:invalidtoken'] = 'The token is invalid.';
$string['authorise:roleidnotinrule'] = 'The role id is not in the rule.';
$string['authorise:validutilexpired'] = 'The authorisation link has expired.';
$string['availablebackupnotify'] = 'Available backup notify';
$string['availablebackupnotify_desc'] = 'In the absence of someone to receive notification or authorise, who should recieve the email?
    This list will provide a menu on the rule form, but additional emails can be added.';
$string['availablecohorts'] = 'Available cohorts';
$string['availablecohorts_desc'] = 'Cohorts that can be used in a rule - make sure there are no student cohorts here.';
$string['availabledepartments'] = 'Available departments';
$string['availabledepartments_desc'] = 'User "department" fields that can be used in a rule';
$string['availablenotifyroles'] = 'Available notify roles';
$string['availablenotifyroles_desc'] = 'Roles available in a rule that are used to identify who can be notified or authorise about
    an enrolment';
$string['availableregistryemails'] = 'Available registry emails';
$string['availableregistryemails_desc'] = 'Email addresses of various "Registry" departments. Used in a rule when an "editingteacher"
    role has been chosen to be enrolled. This informs registry of a request, rather than enrolling someone automatically,
    so that they can be put through the system.';
$string['availableroles'] = 'Available roles';
$string['availableroles_desc'] = 'Roles available in rule creation';

$string['cachedef_rules'] = 'Enrolstaff rules cache';
$string['cachedef_user'] = 'Enrolstaff user session cache';
$string['cannotenrolself'] = 'You are not permitted to do this action';
$string['chooseone'] = '<div class="alert alert-warning">Choose at least one filter</div>';
$string['codes'] = 'Permitted module codes';
$string['codesbcas'] = "Comma separated list of BCAS module code prefixes";
$string['codesqahe'] = "Comma separated list of QAHE module code prefixes";
$string['cohorts'] = 'Cohorts';
$string['commaseparatedlist'] = 'Comma separated list';
$string['commaseparatedlistof'] = 'Commas separated list of {$a}';
$string['confirmdelete'] = 'Confirm deletion of "{$a}"';
$string['courselabel'] = '{$a->idnumber}<br />{$a->fullname}</br />Start/End: {$a->startunix} - {$a->endunix}';
$string['coursesearch'] = "Module code";
$string['currentcourses'] = 'Current enrolments';
$string['currentsearch'] = 'Current search: "<strong>{$a->search}</strong>" for role "<strong>{$a->rolename}</strong>"';

$string['defaultauths'] = 'Default authentication method';
$string['defaultauths_desc'] = 'Which authentication method is used as default for a rule?';
$string['defaultbackupnotify'] = 'Default Backup notify';
$string['defaultbackupnotify_desc'] = 'Email address of support in case no-one is available to authorise or receive the notification';
$string['defaultdepartments'] = 'Default departments';
$string['defaultdepartments_desc'] = 'In the user record, the field "department" indicates a university role. Comma separated list here.';
$string['defaultemailpattern'] = 'Default email pattern';
$string['defaultemailpattern_desc'] = 'Default email pattern to use when creating a new rule';
$string['defaultexemailpattern'] = 'Default exclude email pattern';
$string['defaultexemailpattern_desc'] = 'Default exclude email pattern to use when creating a new rule';
$string['defaultexpireenrolments'] = 'Default expire enrolments';
$string['defaultexpireenrolments_desc'] = 'How long should the enrolment last. Expiry date is calculated when the enrolment is created,
    and can be altered after the fact.';
$string['defaultexusernamepattern'] = 'Default exclude username pattern';
$string['defaultexusernamepattern_desc'] = 'Default exclude username pattern to use when creating a new rule';
$string['defaultnotifyroles'] = 'Default roles to notify';
$string['defaultnotifyroles_desc'] = 'Role shortname (as a csv) that will automatically be chosen in the rule form';
$string['defaultregistryemail'] = 'Default registry email';
$string['defaultregistryemail_desc'] = 'Email used if a user is trying to enrol as an "editingteacher" role.';
$string['defaultsettings'] = 'Default settings';
$string['defaultsettings_desc'] = 'These settings will be used as default settings when creating rules.';
$string['defaultusernamepattern'] = 'Default username pattern';
$string['defaultusernamepattern_desc'] = 'Default username pattern to use when creating a new rule';
$string['defaultvalidroles'] = 'Default valid roles';
$string['deleted'] = '"{$a}" has been deleted.';
$string['departments'] = 'Departments';

$string['editstaffrule'] = 'Edit Staff rule';
$string['emailpattern'] = 'Email pattern';
$string['enabled'] = 'Enabled';
$string['enrol-selfservice'] = "Staff enrolment self-service";
$string['enrolas'] = 'Can enrol as one of "{$a->list}" for {$a->period}';
$string['enrolconfirmation'] = 'You have been enrolled on <a href="{$a->url}">{$a->shortname}</a> as {$a->rolename}';
$string['enrolintro'] = "<p>To remove yourself as module leader from a module page, <strong><a href='mailto:student.registry@solent.ac.uk?subject=Module%20Leader%20enrolment%20deletion%20request&amp;body=Dear%20Student%20Registry,%0D%0A%0D%0APlease%20remove%20me%20as%20module%20leader%20from%20module'>please click here to generate an email template to send to Student Registry</a></strong></p>";
$string['enrolmentauthorisation'] = 'Enrolment authorisation';
$string['enrolmentauthorisationconfirmed'] = 'Enrolment authorisation confirmed. The user has been enrolled.';
$string['enrolmentauthorisationconfirmmessage'] = 'You have been requested to enrol {$a->requestor} on <strong>{$a->course}</strong> with the role of <strong>{$a->role}</strong>.<br /><br />Please confirm this request by clicking the "Confirm" button below.';
$string['enrolmentauthorisationmessage'] = 'Authorisation message';
$string['enrolmentauthorisationmessage_default'] = "Dear [recipientfirstname],

<strong>[userfullname]</strong> has asked to be enrolled on \"<a href=\"[courseurl]\">[coursefullname]</a>\" with the \"<strong>[rolename]</strong>\" role.

Please <a href=\"[authorisationlink]\">click here to authorise or deny this enrolment request</a>.

";
$string['enrolmentauthorisationmessage_desc'] = 'You can include the following codes in the message [coursefullname], [courseurl], [recipientfirstname], [rolename], [shortname], [userfullname], [authorisationlink], [validuntildays]';
$string['enrolmentauthorisationmessageconfirmation'] = "Enrolment authorisation confirmation message";
$string['enrolmentauthorisationmessageconfirmation_default'] = "Dear [recipientfirstname],
Your request to be enrolled on \"<a href=\"[courseurl]\">[coursefullname]</a>\" with the \"<strong>[rolename]</strong>\" role has been approved.
You can now access the module.
";
$string['enrolmentauthorisationmessageconfirmation_desc'] = 'You can include the following codes in the message [coursefullname], [courseurl], [recipientfirstname], [rolename], [shortname], [userfullname]';
$string['enrolmentauthorisationmessagereject'] = "Dear [recipientfirstname],
Your request to be enrolled on \"[coursefullname]\" with the \"[rolename]\" role has been <em>rejected</em>.
Kind regards
";
$string['enrolmentauthorisationmessageuser'] = 'Enrolment authorisation message to user';
$string['enrolmentauthorisationrejected'] = 'Enrolment authorisation rejected';
$string['enrolmentauthorisationsubject'] = 'Subject of authorisation email';
$string['enrolmentauthorisationsubject_default'] = 'Enrolment authorisation request for [shortname]';
$string['enrolmentauthorisationsubject_desc'] = 'You can include the following codes in the subject [coursefullname], [courseurl], [rolename], [shortname], [userfullname]';
$string['enrolmentauthorisationvalidity'] = 'Authorisation link validity (days)';
$string['enrolmentauthorisationvalidity_desc'] = 'How many days is the authorisation link valid for? After this time, the link will expire and a new request will need to be made.';
$string['enrolmenthome'] = 'Enrolment home';
$string['enrolmentnotificationmessage'] = 'Notification message';
$string['enrolmentnotificationmessage_default'] = "Dear [recipientfirstname],

[userfullname] has been enrolled on \"<a href=\"[courseurl]\">[coursefullname]</a>\" with the \"[rolename]\" role.

Depending on the role, they may be able to edit your page, reuse the content on their page, and view participants' details.

Kind regards
";
$string['enrolmentnotificationmessage_desc'] = 'You can include the following codes in the message [coursefullname], [courseurl], [recipientfirstname], [rolename], [shortname], [userfullname]';
$string['enrolmentnotificationmessageenable'] = 'Enable Module Leader notifications';
$string['enrolmentnotificationmessageenable_desc'] = 'Should emails be sent to the module leader when someone self-enrols?';
$string['enrolmentnotificationsubject'] = 'Subject of notification email';
$string['enrolmentnotificationsubject_default'] = 'New enrolment on [shortname]';
$string['enrolmentnotificationsubject_desc'] = 'You can include the following codes in the subject [coursefullname], [rolename], [shortname], [userfullname]';
$string['enrolmentregistryrequestmessage'] = 'Registry request message';
$string['enrolmentregistryrequestmessage_default'] = "Dear Registry,
<strong>[userfullname]</strong> has asked to be enrolled on \"<a href=\"[courseurl]\">[coursefullname]</a>\" with the \"[rolename]\" role.
Please process this request in the Student Records System.";
$string['enrolmentregistryrequestmessage_desc'] = 'You can include the following codes in the message [coursefullname], [courseurl], [rolename], [shortname], [userfullname]';
$string['enrolmentregistryrequestsubject'] = 'Subject of registry request email';
$string['enrolmentregistryrequestsubject_default'] = 'Enrolment request for [shortname]';
$string['enrolmentregistryrequestsubject_desc'] = 'You can include the following codes in the subject [coursefullname], [rolename], [shortname], [userfullname]';
$string['enrolmentsexpireafter'] = 'Enrolments expire after {$a} days. You may reenrol any time. We do this to help keep unnecessary enrolments down.';
$string['enrolontocourse'] = 'Enrol onto course';
$string['enrolrequestalert'] = 'Registry has been sent a request for you to be enrolled on {$a->shortname} as {$a->rolename}. <br /><br /> You will receive an email confirmation with further information.';
$string['enrolrequestalertauthorisation'] = 'An enrolment request for {$a->shortname} as {$a->rolename} has been sent to the page owner. <br /><br /> You will receive an email confirmation with further information once the request has been processed.';
$string['enrolrequestedschool'] = 'Please enrol me on {$a->fullname} as {$a->rolename}.';
$string['enrolrequesteduser'] = 'This is confirmation of your request to be added to {$a->fullname} as {$a->rolename}. The turnaround time for processing module leader requests is 2-3 working days. If you do not have access to the module after that time, please let us know.';
$string['enrolstaff:authoriseenrolments'] = "Authorise staff enrolments";
$string['enrolstaff:managestaffenrolments'] = "Manage staff enrolments";
$string['enrolwarning'] = "Please only enrol yourself on this module if you are teaching on it or have a specific operational requirement.";
$string['excludefullname'] = "Exclude course fullname";
$string['excludefullname_desc'] = 'Comma separated list of course fullnames (full or partial match) that users can never enrol on.';
$string['excludeid'] = "Exclude from search by id (comma separated)";
$string['excludeshortname'] = 'Exclude course shortname';
$string['excludeshortname_desc'] = 'Comma separated list of shortnames (full or partial match) that users can never enrol on.';
$string['excodes'] = 'Excluded module codes';

$string['existingroles'] = ' <strong>(already enrolled as {$a})</strong>';
$string['expireenrolments'] = 'Expire enrolments';
$string['expireenrolments_desc'] = 'How long should the enrolment last. Expiry date is calculated when the enrolment is created, and can be altered after the fact.';
$string['exusernamepattern'] = 'Exclude username pattern';

$string['field:authenticationmethods'] = 'Authentication methods';
$string['field:authenticationmethods_help'] = 'Select one or more authentication methods. Users must have at least one of these methods to match the rule.';
$string['field:auths'] = 'Authentication methods';
$string['field:backupnotify'] = 'Backup notify';
$string['field:backupnotify_help'] = 'If no-one with the role specified in \'Notify\' is available, who should receive requests?';
$string['field:codes'] = 'Module codes';
$string['field:codes_help'] = 'This will match against the start of any Module or Course code e.g. QHO101 or QHO if you want to explicity limit enrolments - you may need to create a separate rule for Content retrieval.';
$string['field:cohortids'] = 'Cohorts';
$string['field:cohortids_help'] = 'Select one or more cohorts. Users must be a member of at least one of these cohorts to match the rule.';
$string['field:departments'] = 'Departments';
$string['field:departments_help'] = 'This will match any part of the department field in the user profile.';
$string['field:duration'] = 'Enrolment duration';
$string['field:duration_help'] = 'When someone enrols themselves, they will be automatically unenroled after this time.';
$string['field:emailpattern'] = 'Email pattern';
$string['field:emailpattern_help'] = 'This will match any part of the email as a Regular expression';
$string['field:excodes'] = 'Excluded Module codes';
$string['field:excodes_help'] = 'This will explicitly prevent matching against the start of any Module or Course, in addition to any specified in the hard rules.';
$string['field:exemailpattern'] = 'Exclude email pattern';
$string['field:exemailpattern_help'] = 'This will exclude any email matching this Regular expression';
$string['field:exusernamepattern'] = 'Exclude username pattern';
$string['field:exusernamepattern_help'] = 'This will exclude any username matching this Regular expression';
$string['field:institution'] = 'Institution';
$string['field:institution_help'] = 'This will match any part of the institution field in the user profile. Consider using a Cohort instead.';
$string['field:notify'] = 'Notify';
$string['field:notify_help'] = '<p><strong>Required if a Notification or an Authorisation is required.</strong></p><p>Select a role for someone on the page to be notified or an email address</p>';
$string['field:registryemail'] = 'Registry email';
$string['field:registryemail_help'] = 'The relevant registry email for this user type. Any "editingteacher" roles will automatically be diverted to this address';
$string['field:roleids'] = 'Available roles';
$string['field:roleids_help'] = 'Select at least one role that a user matching the pattern will match against. If you require different actions, create separate rules for each role/action combination.';
$string['field:sendas'] = 'Authorisation';
$string['field:sendas_help'] = '* <strong>Notification:</strong> Automatic enrolment, but will send notification to Module leader
* <strong>Authorisation:</strong> Send authorisation request to Module leader who will need to confirm enrolment
* <strong>No notification:</strong> Automatic enrolment with no notification
* <strong>Registry request:</strong> Registry is notified of request and will follow up through SRS';
$string['field:usernamepattern'] = 'Username pattern';
$string['field:usernamepattern_help'] = 'This will match any part of the username as a Regular expression';
$string['fieldlookslikeoneof'] = '"{$a->field}" looks like one of "{$a->list}"';
$string['filter'] = 'Filter';
$string['filterhelp'] = '<p>The Filters section allows you to choose the conditions for selecting which users can enrol themselves.</p>
<p>Only select the filters you need. Imagine there\'s an "AND" between each element. If you leave a field blank, it will be ignored.</p>
<p>Users with department=student are automatically ignored. But beware of manual accounts where "department" might be blank. Be intentional.</p>
<p>Multi-select options will be treated as "OR" statements. e.g. department = academic OR management OR support.</p>
<div id="id_filtervalidation" style="display: none"></div>';
$string['forperiod'] = 'For {$a}';

$string['hardsettings'] = 'Hard settings';
$string['hardsettings_desc'] = 'These are settings that apply no matter the rule.';
$string['hasanemaillike'] = 'Has an "email" that looks like "{$a}"';
$string['hasausernamelike'] = 'Has a "username" that looks like "{$a}"';
$string['hasfield'] = 'Has "{$a->item}" "{$a->field}"';
$string['hasoneof'] = 'Has one of "{$a->list}" in "{$a->field}"';

$string['institution'] = 'Institution';
$string['intro'] = 'Please speak to the Module or Course Leader if you are unsure of the correct module or instance code.<br /><br /><strong>Modules that may contain sensitive data or have lecturers studying as students have been excluded from this service:</strong>
    <ul>
        <li>Module codes containing: {$a->excludeshortname}</li>
        <li>Module names containing: {$a->excludefullname}</li>
        <li>Modules removed from search results at the request of the module leader</li>
        <li>QAHE staff are restricted to searching for module codes with the prefixes: {$a->qahecodes}</li>
    </ul>
    If your module does not appear in the search results, or if you feel a module should be excluded from this service please contact guided.learning@solent.ac.uk<br /><br />';
$string['invalidbackupemail'] = 'You have entered an invalid backup notify email: {$a}';
$string['invalidcohortid'] = 'You have selected an invalid cohort.';
$string['invalidcourse'] = 'You have selected an invalid course.';
$string['invaliddepts'] = 'You have selected one or more invalid dept codes';
$string['invalidfield'] = 'You have selected invalid "{$a->field}": "{$a->value}"';
$string['invalidid'] = 'The Staff rule doesn\'t exist.';
$string['invalidnotifysendas'] = 'You must select at least one notify item when "Notification", "Authorisation" or "Registry request" is selected in "Notification type".';
$string['invalidnotifywithauthorisation'] = 'The email "{$a}" must exist in Moodle as a user for authorisation. They should also have permission to authorise enrolments. Perhaps choose "Registry request" instead.';
$string['invalidrequired'] = 'At least 1 {$a} is required';
$string['invalidrole'] = "You have selected an invalid role.";
$string['isoneof'] = 'Is one of: "{$a->list}" "{$a->field}"';

$string['managerules'] = 'Manage rules';
$string['matchesauser'] = 'Matches a user who:';
$string['matchespermissions'] = 'Matches the following permissions:';
$string['matchingcohorts'] = 'Matching cohorts';

$string['na'] = 'n/a';
$string['neverexpire'] = 'Never expire';
$string['newrule'] = 'New Staff rule';
$string['newsaved'] = 'New Staff rule created';
$string['nocohorts'] = 'No cohorts';
$string['nocourses'] = "You are not enrolled on any courses that can be un-enrolled from via this service.";
$string['nocoursesfound'] = 'No modules found that you can enrol on.';
$string['nomatchingmodules'] = 'No modules match the term <strong>{$a->coursesearch}</strong>';
$string['nonotification'] = 'No notification';
$string['nopermission'] = "You do not have permission to view this page (please check you are logged in).";
$string['noroles'] = 'No roles';
$string['norolesavailable'] = 'No roles available';
$string['noruleapplies'] = 'No enrolment rule applies to you for this module and role combination.';
$string['notenabled'] = 'Not enabled';
$string['notfieldlookslikeoneof'] = '"{$a->field}" doesn\'t look like one of "{$a->list}"';
$string['nothasanemaillike'] = 'Doesn\'t have an email that looks like "{$a}"';
$string['nothasausernamelike'] = 'Doesn\'t have a username that looks like "{$a}"';
$string['notification'] = 'Notification';
$string['notificationtemplates'] = 'Notification templates';
$string['notificationtemplates_desc'] = 'Email templates used when sending notifications.';
$string['notifyroles_desc'] = 'When setting up a rule, the rolese chosen here will show up as roles to notify or to ask to authorise, if required.';

$string['oldsettings'] = 'Old settings';
$string['oldsettings_desc'] = 'Parking old settings here for now.';
$string['or'] = ' OR ';
$string['otherunitleaders'] = "Other module leaders currently enrolled on this module are: ";

$string['permissions'] = 'Permissions';
$string['pluginname'] = "Staff Enrolment";
$string['previewrule'] = 'Preview rule';
$string['previewruleheading'] = 'Preview of rule: {$a->name}';

$string['qahecontact'] = 'QAHE contact email';
$string['qahecontact_desc'] = 'This account will be emailed when someone requests to be enrolled on a QA module as Module leader';

$string['regexerrorcode1'] = 'Internal error';
$string['regexerrorcode2'] = 'Backtrack limit exhausted';
$string['regexerrorcode3'] = 'Recursion limit exhausted';
$string['regexerrorcode4'] = 'Malformed UTF-8 characters, possibly incorrectly encoded';
$string['regexerrorcode5'] = 'The offset did not correspond to the beginning of a valid UTF-8 code point';
$string['regexhelp'] = '<h3>Regular Expression Help</h3>
<p>Regular expressions are a powerful way of matching patterns in text. They can be used to create complex filters for enrolment rules.</p>
<p>Here are some basic examples to get you started:</p>
<ul>
    <li><strong>^abc</strong> - Matches any string that starts with "abc".</li>
    <li><strong>xyz$</strong> - Matches any string that ends with "xyz".</li>
    <li><strong>^abc.*xyz$</strong> - Matches any string that starts with "abc" and ends with "xyz", with any characters in between.</li>
    <li><strong>[0-9]+</strong> - Matches any string that contains one or more digits.</li>
    <li><strong>[a-zA-Z]+</strong> - Matches any string that contains one or more letters (case insensitive).</li>
    <li><strong>(jobshop|consultant)</strong> - Matches either "jobshop" or "consultant".</li>
</ul>
<p>For more complex patterns and options, you can refer to the <a href="https://regexone.com/" target="_blank">RegexOne tutorial</a>.</p>
<p>Remember to test your regular expressions to ensure they work as expected!</p>';
$string['registryemails'] = 'Registry emails';
$string['registryemails_desc'] = 'Email addresses for various "Registries" - used to send a request to be enrolled for senior teaching roles';
$string['requestemailsubject'] = '{$a->rolename} enrolment request for {$a->shortname}';
$string['requestforenrolment'] = 'You are about to send a request for enrolment on <strong>{$a->coursename}</strong> with the role of <strong>{$a->rolename}</strong><br /><br />';
$string['requiredwithsendas'] = 'This field is required when "Notification" or "Authorisation" is selected in "Notification type"';
$string['restrictedsets'] = 'Restricted sets';
$string['restrictedsets_desc'] = 'Rule options are restricted to the values set here.';
$string['role'] = "Select a role";
$string['roleidsbcas'] = 'Comma separated list of BCAS role IDs';
$string['roleidsqahe'] = 'Comma separated list of QAHE role IDs';
$string['roleidssolent'] = 'Comma separated list of Solent role IDs';
$string['rule_deleted'] = 'Rule deleted';
$string['rule_edited'] = 'Rule edited';
$string['rulehasbeendisabled'] = 'Warning: This filter is invalid and has been disabled.';

$string['searchagain'] = 'Search again';
$string['selectamodule'] = 'Select a module';
$string['selectarole'] = 'Select a role';
$string['selectmodule'] = 'Select module';
$string['sendas'] = 'Notification type';
$string['sendas:authorisation'] = 'Authorisation';
$string['sendas:nonotification'] = 'No notification';
$string['sendas:notification'] = 'Notification';
$string['sendas:registryrequest'] = 'Registry request';
$string['sendrequest'] = 'You are about to be enrolled on <strong>{$a->fullname}</strong> with the role of <strong>{$a->role}</strong><br /><br />';
$string['sendrequestleader'] = 'You are about to send a request for enrolment on <strong>{$a->fullname}</strong> with the role of <strong>{$a->role}</strong><br /><br />';
$string['staffselfenrolmentunavailable'] = 'Staff self-enrolment is currently unavailable.';
$string['studentrecords'] = 'Student records email';

$string['title'] = 'Title';

$string['unenrol'] = "Unenrol from modules";
$string['unenrolconfirm'] = "You have been unenrolled from your selected modules.";
$string['unenrolfrommodules'] = 'Unenrol from modules';
$string['unenrolheader'] = "<br /><hr><h2>Staff unenrolment self-service</h2>";
$string['unenrolintro'] = "To un-enrol yourself from modules you no longer require access to as anything other than module leader, please use the unenrolment self-service:<br /><br />";
$string['unenrolselect'] = "Select the modules you wish to unenrol from:<br /><br />";
$string['unenrolwarning'] = "You have selected to unenrol from the following modules:<br /><br />";
$string['unitleaderid'] = "Module leader role id";
$string['unitselect'] = "Select a module from the list.<br /><br />If you are already enrolled on a module it will appear in the list but won't be available for selection.<br /><br />";
$string['updated'] = '"{$a}" has been updated.';
$string['usernamepattern'] = 'Username pattern';

$string['validateregexerror'] = 'Invalid Regular Expression. Error message: {$a->errormessage} ({$a->errorcode})';
$string['validdepts'] = 'Valid Department';
$string['validroles'] = 'Valid roles';
$string['validroles_desc'] = 'Preselected roles that are available in the Rule configuration (limits accidental adding of never to be used roles).
Student is deselected but student derived roles remain.';
