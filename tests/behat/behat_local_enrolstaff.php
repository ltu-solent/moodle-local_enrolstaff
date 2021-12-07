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
 * Steps definitions related to local_enrolstaff.
 *
 * @package   local_enrolstaff
 * @category  test
 * @copyright 2021 Solent University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Gherkin\Node\TableNode as TableNode;

use Behat\Mink\Exception\ExpectationException as ExpectationException;

/**
 * Steps definitions related to local_enrolstaff.
 *
 * @copyright 2021 Solent University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_local_enrolstaff extends behat_base {
    /**
    * @Given /^I set the enrolstaff role setting "([^"]*)" to "([^"]*)"$/
    */
    public function set_enrolstaff_role_setting(string $setting, string $values): void {
        global $DB;
        $values = explode(",", $values);
        $ids = [];
        foreach ($values as $value) {
            $ids[] = $DB->get_field('role', 'id', ['shortname' => $value]);
        }

        set_config($setting, implode(',', $ids), 'local_enrolstaff');
    }
}