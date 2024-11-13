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

namespace local_enrolstaff\local\hooks;

use stdClass;

/**
 * Class extend_user_menu
 *
 * @package    local_enrolstaff
 * @copyright  2024 Southampton Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class extend_user_menu {
    /**
     * Add Enrol staff to the user menu
     *
     * @param \core_user\hook\extend_user_menu $hook
     * @return void
     */
    public static function callback(\core_user\hook\extend_user_menu $hook): void {
        global $USER;
        $activeuser = new \local_enrolstaff\local\user($USER);
        $enabled = $activeuser->user_can_enrolself();
        if (!$enabled) {
            return;
        }
        $usermenuitem = new stdClass();
        $usermenuitem->itemtype = 'link';
        $usermenuitem->url = new \core\url('/local/enrolstaff/enrolstaff.php');
        $usermenuitem->pix = "i/course";
        $usermenuitem->title = get_string('enrol-selfservice', 'local_enrolstaff');
        $usermenuitem->titleidentifier = 'enrol-selfservice,local_enrolstaff';
        $hook->add_navitem($usermenuitem);
    }
}
