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

namespace local_enrolstaff\event;

use core\url;

/**
 * Event rule_deleted
 *
 * @package    local_enrolstaff
 * @copyright  2025 Southampton Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rule_deleted extends \core\event\base {
    #[\Override]
    protected function init() {
        $this->data['objecttable'] = 'local_enrolstaff';
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    #[\Override]
    public static function get_name() {
        return get_string('rule_deleted', 'local_enrolstaff');
    }

    #[\Override]
    public function get_description() {
        return "User {$this->userid} has deleted the rule with id {$this->objectid}";
    }

    #[\Override]
    public function get_url() {
        return new url('/local/enrolstaff/manage.php');
    }
}
