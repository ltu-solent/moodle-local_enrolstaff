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
 * TODO describe file previewrule
 *
 * @package    local_enrolstaff
 * @copyright  2025 Southampton Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\context\system;
use core\url;
use local_enrolstaff\persistent\rule;
use local_enrolstaff\tables\previewrule_table;

require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
$ruleid = required_param('id', PARAM_INT);
$rule = new rule($ruleid);
$params = ['id' => $ruleid];

admin_externalpage_setup('local_enrolstaff/managerules', '', $params, '/local/enrolstaff/previewrule.php');

$context = system::instance();
$heading = get_string('previewruleheading', 'local_enrolstaff', ['name' => $rule->get('title')]);
$url = new url('/local/enrolstaff/previewrule.php', $params);
$PAGE->navbar->add(get_string('localplugins'), new url('/admin/category.php?category=localplugins'));
$PAGE->navbar->add(get_string('pluginname', 'local_enrolstaff'), new url('/admin/category.php?category=local_enrolstaffcat'));
$PAGE->navbar->add(get_string('managerules', 'local_enrolstaff'), new url('/local/enrolstaff/manage.php'));
$PAGE->navbar->add(get_string('previewrule', 'local_enrolstaff'), $url);
$PAGE->set_context($context);
$PAGE->set_heading($heading);
$PAGE->set_title($heading);
$PAGE->set_url($url);

echo $OUTPUT->header();

echo $rule->print_filters();

$table = new previewrule_table('local_enrolstaff_previewrule_table', $rule);
$table->out(100, true);

echo $OUTPUT->footer();
