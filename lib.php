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
 * Lib file for Enrolstaff
 *
 * @package   local_enrolstaff
 * @author    Mark Sharp <mark.sharp@solent.ac.uk>
 * @copyright 2022 Solent University {@link https://www.solent.ac.uk}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/**
 * Add enrolstaff link to user profile menu if the user is allowed to.
 *
 * @param array $navitems
 * @param stdClass $user
 * @param context $context
 * @param stdClass $course
 * @return array
 */
function local_enrolstaff_extend_navigation_menuuser($navitems, $user, $context, $course) {
    $activeuser = new \local_enrolstaff\local\user($user);
    $enabled = $activeuser->user_can_enrolself();
    if ($enabled) {
        $usermenuitem = new stdClass();
        $usermenuitem->itemtype = 'link';
        $usermenuitem->url = new moodle_url('/local/enrolstaff/enrolstaff.php');
        $usermenuitem->pix = "i/course";
        $usermenuitem->title = get_string('enrol-selfservice', 'local_enrolstaff');
        $usermenuitem->titleidentifier = 'enrol-selfservice,local_enrolstaff';
        $navitems[] = $usermenuitem;
    }
    return $navitems;
}
