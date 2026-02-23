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

use local_enrolstaff\persistent\authorise;
use local_enrolstaff\persistent\rule;

/**
 * Data generator class
 *
 * @package    local_enrolstaff
 * @category   test
 * @copyright  2025 Southampton Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_enrolstaff_generator extends component_generator_base {
    /** @var int Number of created rules. */
    protected $rulescount = 0;
    /**
     * Reset process.
     *
     * Do not call directly.
     *
     * @return void
     */
    public function reset() {
        $this->rulescount = 0;
    }
    /**
     * Helper to generate a rule record
     *
     * @param array|stdClass $record
     * @return rule
     */
    public function create_rule($record = null): rule {
        $this->rulescount++;
        $i = $this->rulescount;
        $record = (object)$record;
        if (!isset($record->title)) {
            $record->title = "Enrolment Rule {$i}";
        }
        if (!isset($record->roleids)) {
            $record->roleids = [4]; // Teacher role.
        }
        if (!isset($record->username)) {
            $default = get_config('local_enrolstaff', 'defaultusernamepattern');
            $record->username = $default;
        }
        if (!isset($record->exusername)) {
            $default = get_config('local_enrolstaff', 'defaultexusernamepattern');
            $record->exusername = $default;
        }
        if (!isset($record->email)) {
            $default = get_config('local_enrolstaff', 'defaultemailpattern');
            $record->email = $default;
        }
        if (!isset($record->exemail)) {
            $default = get_config('local_enrolstaff', 'defaultexemailpattern');
            $record->exemail = $default;
        }
        if (!isset($record->auths)) {
            $record->auths = '';
        }
        if (!isset($record->codes)) {
            $record->codes = '';
        }
        if (!isset($record->excodes)) {
            $record->excodes = '';
        }
        if (!isset($record->sendas)) {
            $record->sendas = "nonotification";
        }
        if (!isset($record->duration)) {
            $default = get_config('local_enrolstaff', 'defaultexpireenrolments') ?? '0';
            $record->duration = $default;
        }
        if (!isset($record->enabled)) {
            $record->enabled = 1;
        }
        $rule = new rule(0, $record);
        $rule->create();
        return $rule;
    }

    /**
     * Create authorisation object
     *
     * @param stdClass|array $record
     * @return authorise
     */
    public function create_authorisation($record = null): authorise {
        $record = (object)$record;
        if (!isset($record->roleid)) {
            $record->roleid = 4; // Teacher role.
        }
        if (!isset($record->validuntil)) {
            $record->validuntil = time() + WEEKSECS; // Valid for 1 week.
        }
        $authorisation = new authorise(0, $record);
        $authorisation->create();
        return $authorisation;
    }
}
