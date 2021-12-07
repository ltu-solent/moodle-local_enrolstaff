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
 * Test enrolstaff api functions
 *
 * @package   local_enrolstaff
 * @author    Mark Sharp <mark.sharp@solent.ac.uk>
 * @copyright 2021 Solent University {@link https://www.solent.ac.uk}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/helper_trait.php');

class local_enrolstaff_api_testcase extends advanced_testcase {

    use local_enrolstaff_helper_trait;

    private function setup_bitsnbobs() {
        $this->create_roles();
        $this->create_categories();
        $this->create_courses();
        $this->create_users();
        $this->set_configs();
        
    }
    public function test_moduleleader() {
        $this->resetAfterTest();
        $this->setup_bitsnbobs();
        $this->getDataGenerator()->enrol_user($this->users['leader1']->id,
            $this->courses['C1']->id,
            $this->moduleleader);
        $this->getDataGenerator()->enrol_user($this->users['leader1']->id,
            $this->courses['M1']->id,
            $this->moduleleader);
        $this->getDataGenerator()->enrol_user($this->users['teacher1']->id,
            $this->courses['M1']->id,
            $this->moduleleader);

        $moduleleaders = \local_enrolstaff\local\api::moduleleader($this->courses['C1']->id);
        $this->assertCount(1, $moduleleaders);
        $moduleleaders = \local_enrolstaff\local\api::moduleleader($this->courses['M1']->id);
        $this->assertCount(2, $moduleleaders);

        $this->getDataGenerator()->enrol_user($this->users['qateacher1']->id,
            $this->courses['M1']->id,
            $this->qamoduleleader);
        $moduleleaders = \local_enrolstaff\local\api::moduleleader($this->courses['M1']->id);
        $this->assertCount(3, $moduleleaders);
    }

    public function test_is_partner_course() {
        $this->resetAfterTest();
        $this->setup_bitsnbobs();
        $is_partner = \local_enrolstaff\local\api::is_partner_course($this->courses['QHO1']);
        $this->assertTrue($is_partner);

        $is_partner = \local_enrolstaff\local\api::is_partner_course($this->courses['M1']);
        $this->assertFalse($is_partner);
    }
}