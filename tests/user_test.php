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

class local_enrolstaff_user_testcase extends advanced_testcase {

    use local_enrolstaff_helper_trait;
    private function setup_bitsnbobs() {
        $this->create_roles();
        $this->create_categories();
        $this->create_courses();
        $this->create_users();
        $this->set_configs();
        
    }

    public function test_properties() {
        global $USER;
        $this->resetAfterTest();
        $this->setup_bitsnbobs();

        $this->setUser(get_admin());
        $activeuser = new \local_enrolstaff\local\user($USER);
        $this->assertEquals(explode(',', get_config('local_enrolstaff', 'roleids')), $activeuser->validroles);

        $this->setUser($this->users['teacher1']);
        $activeuser = new \local_enrolstaff\local\user($USER);
        $this->assertEquals('solent.ac.uk', $activeuser->domain);
        $this->assertEquals(explode(',', get_config('local_enrolstaff', 'roleids')), $activeuser->validroles);
        
        $this->setUser($this->users['qateacher1']);
        $activeuser = new \local_enrolstaff\local\user($USER);
        $this->assertEquals('qa.com', $activeuser->domain);
        $this->assertEquals(explode(',', get_config('local_enrolstaff', 'qaheroleids')), $activeuser->validroles);

        $this->setUser($this->users['jobshop101']);
        $activeuser = new \local_enrolstaff\local\user($USER);
        $this->assertEquals([], $activeuser->validroles);

        $this->setUser($this->users['student1']);
        $activeuser = new \local_enrolstaff\local\user($USER);
        $this->assertEquals([], $activeuser->validroles);
    }

    public function test_get_roles_menu() {
        global $USER;
        $this->resetAfterTest();
        $this->setup_bitsnbobs();

        $this->setUser($this->users['teacher1']);
        $activeuser = new \local_enrolstaff\local\user($USER);
        $menuitems = $activeuser->get_roles_menu();
        $this->assertCount(count($this->teachingroles), $menuitems);
        $this->assertContains('Module leader', $menuitems);
        $this->assertContains('Tutor', $menuitems);
        $this->assertNotContains('QA Module leader', $menuitems);
        $this->assertNotContains('QA Tutor', $menuitems);

        $this->setUser($this->users['qateacher1']);
        $activeuser = new \local_enrolstaff\local\user($USER);
        $menuitems = $activeuser->get_roles_menu();
        $this->assertCount(count($this->qateachingroles), $menuitems);
        $this->assertNotContains('Module leader', $menuitems);
        $this->assertNotContains('Tutor', $menuitems);
        $this->assertContains('QA Module leader', $menuitems);
        $this->assertContains('QA Tutor', $menuitems);

        $this->setUser($this->users['jobshop101']);
        $activeuser = new \local_enrolstaff\local\user($USER);
        $menuitems = $activeuser->get_roles_menu();
        $this->assertCount(0, $menuitems);

        $this->setUser($this->users['student1']);
        $activeuser = new \local_enrolstaff\local\user($USER);
        $menuitems = $activeuser->get_roles_menu();
        $this->assertCount(0, $menuitems);
    }

    public function test_is_role_valid() {
        global $DB, $USER;
        $this->resetAfterTest();
        $this->setup_bitsnbobs();

        $this->setUser($this->users['teacher1']);
        $activeuser = new \local_enrolstaff\local\user($USER);
        foreach ($this->teachingroles as $teachingrole) {
            $isvalid = $activeuser->is_role_valid($teachingrole);
            $this->assertTrue($isvalid);
        }
        foreach ($this->qateachingroles as $teachingrole) {
            $isvalid = $activeuser->is_role_valid($teachingrole);
            $this->assertFalse($isvalid);
        }
        $role = $DB->get_record('role', ['shortname' => 'manager']);
        $isvalid = $activeuser->is_role_valid($role->id);
        $this->assertFalse($isvalid);
        $role = $DB->get_record('role', ['shortname' => 'student']);
        $isvalid = $activeuser->is_role_valid($role->id);
        $this->assertFalse($isvalid);


        $this->setUser($this->users['qateacher1']);
        $activeuser = new \local_enrolstaff\local\user($USER);
        foreach ($this->teachingroles as $teachingrole) {
            $isvalid = $activeuser->is_role_valid($teachingrole);
            $this->assertFalse($isvalid);
        }
        foreach ($this->qateachingroles as $teachingrole) {
            $isvalid = $activeuser->is_role_valid($teachingrole);
            $this->assertTrue($isvalid);
        }
        $role = $DB->get_record('role', ['shortname' => 'manager']);
        $isvalid = $activeuser->is_role_valid($role->id);
        $this->assertFalse($isvalid);
        $role = $DB->get_record('role', ['shortname' => 'student']);
        $isvalid = $activeuser->is_role_valid($role->id);
        $this->assertFalse($isvalid);
    }

    public function test_user_can_enrolself() {
        global $USER;
        $this->resetAfterTest();
        $this->setup_bitsnbobs();

        $this->setUser($this->users['teacher1']);
        $activeuser = new \local_enrolstaff\local\user($USER);
        $canenrol = $activeuser->user_can_enrolself();
        $this->assertTrue($canenrol);

        $this->setUser($this->users['qateacher1']);
        $activeuser = new \local_enrolstaff\local\user($USER);
        $canenrol = $activeuser->user_can_enrolself();
        $this->assertTrue($canenrol);

        $this->setUser($this->users['leader1']);
        $activeuser = new \local_enrolstaff\local\user($USER);
        $canenrol = $activeuser->user_can_enrolself();
        $this->assertTrue($canenrol);

        $this->setUser($this->users['qaleader1']);
        $activeuser = new \local_enrolstaff\local\user($USER);
        $canenrol = $activeuser->user_can_enrolself();
        $this->assertTrue($canenrol);

        $this->setUser($this->users['support1']);
        $activeuser = new \local_enrolstaff\local\user($USER);
        $canenrol = $activeuser->user_can_enrolself();
        $this->assertTrue($canenrol);

        $this->setUser($this->users['jobshop101']);
        $activeuser = new \local_enrolstaff\local\user($USER);
        $canenrol = $activeuser->user_can_enrolself();
        $this->assertFalse($canenrol);

        $this->setUser($this->users['student1']);
        $activeuser = new \local_enrolstaff\local\user($USER);
        $canenrol = $activeuser->user_can_enrolself();
        $this->assertFalse($canenrol);

    }

    public function test_can_enrolselfon() {
        global $USER;
        $this->resetAfterTest();
        $this->setup_bitsnbobs();

        $this->setUser($this->users['teacher1']);
        $activeuser = new \local_enrolstaff\local\user($USER);
        $canenrol = $activeuser->can_enrolselfon($this->courses['M1']->id);
        $this->assertTrue($canenrol);

        $canenrol = $activeuser->can_enrolselfon($this->courses['C1']->id);
        $this->assertTrue($canenrol);

        $canenrol = $activeuser->can_enrolselfon($this->courses['C2']->id);
        $this->assertFalse($canenrol);

        $canenrol = $activeuser->can_enrolselfon($this->courses['counselling']->id);
        $this->assertFalse($canenrol);

        $canenrol = $activeuser->can_enrolselfon($this->courses['EDU101']->id);
        $this->assertFalse($canenrol);

        $canenrol = $activeuser->can_enrolselfon($this->courses['QHO1']->id);
        $this->assertTrue($canenrol);

        $canenrol = $activeuser->can_enrolselfon($this->courses['EXCLUDE101']->id);
        $this->assertFalse($canenrol);

        $this->setUser($this->users['qateacher1']);
        $activeuser = new \local_enrolstaff\local\user($USER);
        $canenrol = $activeuser->can_enrolselfon($this->courses['M1']->id);
        $this->assertFalse($canenrol);

        $canenrol = $activeuser->can_enrolselfon($this->courses['C1']->id);
        $this->assertFalse($canenrol);

        $canenrol = $activeuser->can_enrolselfon($this->courses['C2']->id);
        $this->assertFalse($canenrol);

        $canenrol = $activeuser->can_enrolselfon($this->courses['counselling']->id);
        $this->assertFalse($canenrol);

        $canenrol = $activeuser->can_enrolselfon($this->courses['EDU101']->id);
        $this->assertFalse($canenrol);

        $canenrol = $activeuser->can_enrolselfon($this->courses['QHO1']->id);
        $this->assertTrue($canenrol);

        $canenrol = $activeuser->can_enrolselfon($this->courses['EXCLUDE101']->id);
        $this->assertFalse($canenrol);
    }

    /**
     * Course search is limited by the users permissions to be enrolled on courses.
     *
     * @return void
     */
    public function test_course_search() {
        global $USER;
        $this->resetAfterTest();
        $this->setup_bitsnbobs();

        $this->setUser($this->users['teacher1']);
        $activeuser = new \local_enrolstaff\local\user($USER);
        $courses = $activeuser->course_search('course');
        $this->assertCount(1, $courses);
        $courses = $activeuser->course_search('module');
        $this->assertCount(3, $courses);
        $courses = $activeuser->course_search('exclude');
        $this->assertCount(0, $courses);
        $courses = $activeuser->course_search('counselling');
        $this->assertCount(0, $courses);

        $this->setUser($this->users['qateacher1']);
        $activeuser = new \local_enrolstaff\local\user($USER);
        $courses = $activeuser->course_search('course');
        $this->assertCount(0, $courses);
        $courses = $activeuser->course_search('module');
        $this->assertCount(1, $courses);
        $courses = $activeuser->course_search('exclude');
        $this->assertCount(0, $courses);
        $courses = $activeuser->course_search('counselling');
        $this->assertCount(0, $courses);

        $this->setUser($this->users['jobshop101']);
        $activeuser = new \local_enrolstaff\local\user($USER);
        $courses = $activeuser->course_search('course');
        $this->assertCount(0, $courses);
        $courses = $activeuser->course_search('module');
        $this->assertCount(0, $courses);
        $courses = $activeuser->course_search('exclude');
        $this->assertCount(0, $courses);
        $courses = $activeuser->course_search('counselling');
        $this->assertCount(0, $courses);
    }
}