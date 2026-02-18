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
 * TODO describe file manage
 *
 * @package    local_enrolstaff
 * @copyright  2025 Southampton Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\context\system;
use core\lang_string;
use core\output\action_link;
use core\url;
use local_enrolstaff\tables\rules_table;

require('../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('local_enrolstaff/managerules', '', null, '/local/enrolstaff/manage.php');
$context = system::instance();

$url = new moodle_url('/local/enrolstaff/manage.php', []);
$PAGE->set_url($url);
$PAGE->set_context($context);

$PAGE->set_heading($SITE->fullname);
echo $OUTPUT->header();

echo "<h3>Notes</h3>";
echo "<p>There are three columns to note here:</p>";
$list = [
    '<strong>Filter:</strong> This chooses the user who this rule applies to.',
    '<strong>Permissions:</strong> This determines what roles and what courses are available to this user',
    '<strong>Authorisation:</strong> This determines whether anyone needs to know about this enrolment,
        or if any authorisation workflow is required. If the role is an "editingteacher" archetype role in the Permissions column,
        no enrolment will take place, only notification to the nominated Registry.',
];
echo html_writer::alist($list, null, 'ol');
$list = [
    'Multiple rules can apply to a user',
    'Watch out for rules that are too permissive',
    'Consider using the Location for limiting to Solent or to partners',
    'Consider using cohorts, since these can be based on Location - might be quicker',
    'Will use regular expressions on email and username to make more flexible',
];
echo html_writer::alist($list);

$new = new action_link(
    new url('/local/enrolstaff/rule.php', ['action' => 'new']),
    new lang_string('newrule', 'local_enrolstaff'),
    null,
    ['class' => 'btn btn-primary']
);
echo $OUTPUT->render($new);
$table = new rules_table('enrolstaff_rulestable');
$table->out(25, false);
echo $OUTPUT->footer();
