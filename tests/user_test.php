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

namespace local_enrolstaff;

use advanced_testcase;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/helper_trait.php');

/**
 * Tests user functions
 * @covers \local_enrolstaff\local\user
 * @group sol
 */
final class user_test extends advanced_testcase {
    use helper_trait;

    /**
     * Set up the required default settings, roles, courses etc
     *
     * @return void
     */
    private function setup_bitsnbobs() {
        $this->create_roles();
        $this->create_categories();
        $this->create_courses();
        $this->create_users();
        $this->set_configs();
    }

    /**
     * The roles menu depends on who you are and the rules that apply.
     *
     * @return void
     */
    public function test_get_roles_menu(): void {
        global $USER;
        $this->resetAfterTest();
        $this->setup_bitsnbobs();
        set_config('availableroles', implode(',', [
            $this->roles['tutor'],
            $this->roles['moduleleader'],
            $this->roles['contentretrieval'],
            $this->roles['qatutor'],
            $this->roles['qamoduleleader'],
        ]), 'local_enrolstaff');

        // Needs some rules.
        /** @var \local_enrolstaff_generator $esdg */
        $esdg = $this->getDataGenerator()->get_plugin_generator('local_enrolstaff');
        // Rule for normal teaching roles.
        $esdg->create_rule([
            'email' => '@solent.ac.uk',
            'username' => '',
            'enabled' => 1,
            'roleids' => [
                $this->roles['tutor'],
                $this->roles['moduleleader'],
            ],
        ]);
        // Rule for QA teaching roles.
        $esdg->create_rule([
            'email' => '@qa.com',
            'username' => '',
            'enabled' => 1,
            'roleids' => [
                $this->roles['qatutor'],
                $this->roles['qamoduleleader'],
            ],
        ]);
        // Rule for content retrieval role.
        // So now there are 2 rules for qa.com users, but you can set different authorisation requirements.
        $esdg->create_rule([
            'email' => '@qa.com',
            'username' => '',
            'enabled' => 1,
            'roleids' => [
                $this->roles['contentretrieval'],
            ],
        ]);

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
        $this->assertCount(3, $menuitems);
        $this->assertNotContains('Module leader', $menuitems);
        $this->assertNotContains('Tutor', $menuitems);
        $this->assertContains('QA Module leader', $menuitems);
        $this->assertContains('QA Tutor', $menuitems);
        $this->assertContains('Content retrieval', $menuitems);

        $this->setUser($this->users['jobshop101']);
        $activeuser = new \local_enrolstaff\local\user($USER);
        $menuitems = $activeuser->get_roles_menu();
        $this->assertCount(0, $menuitems);

        $this->setUser($this->users['student1']);
        $activeuser = new \local_enrolstaff\local\user($USER);
        $menuitems = $activeuser->get_roles_menu();
        $this->assertCount(0, $menuitems);

        $this->setUser($this->users['contentretrieval']);
        $activeuser = new \local_enrolstaff\local\user($USER);
        $menuitems = $activeuser->get_roles_menu();
        $this->assertCount(3, $menuitems);
        $this->assertContains('Content retrieval', $menuitems);
        $this->assertNotContains('Module leader', $menuitems);
        $this->assertNotContains('Tutor', $menuitems);
        $this->assertContains('QA Module leader', $menuitems);
        $this->assertContains('QA Tutor', $menuitems);
    }

    /**
     * Validation of selected role.
     *
     * @return void
     */
    public function test_is_role_valid(): void {
        global $DB, $USER;
        $this->resetAfterTest();
        $this->setup_bitsnbobs();
        set_config('availableroles', implode(',', [
            $this->roles['tutor'],
            $this->roles['moduleleader'],
            $this->roles['contentretrieval'],
            $this->roles['qatutor'],
            $this->roles['qamoduleleader'],
        ]), 'local_enrolstaff');

        // Needs some rules.
        /** @var \local_enrolstaff_generator $esdg */
        $esdg = $this->getDataGenerator()->get_plugin_generator('local_enrolstaff');
        // Rule for normal teaching roles.
        $esdg->create_rule([
            'email' => '@solent.ac.uk',
            'username' => '',
            'enabled' => 1,
            'roleids' => [
                $this->roles['tutor'],
                $this->roles['moduleleader'],
            ],
        ]);
        // Rule for QA teaching roles.
        $esdg->create_rule([
            'email' => '@qa.com',
            'username' => '',
            'enabled' => 1,
            'roleids' => [
                $this->roles['qatutor'],
                $this->roles['qamoduleleader'],
            ],
        ]);
        // Rule for content retrieval role.
        // So now there are 2 rules for qa.com users, but you can set different authorisation requirements.
        $esdg->create_rule([
            'email' => '@qa.com',
            'username' => '',
            'enabled' => 1,
            'roleids' => [
                $this->roles['contentretrieval'],
            ],
        ]);

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
        $isvalid = $activeuser->is_role_valid($this->roles['contentretrieval']);
        $this->assertTrue($isvalid);

        $role = $DB->get_record('role', ['shortname' => 'manager']);
        $isvalid = $activeuser->is_role_valid($role->id);
        $this->assertFalse($isvalid);
        $role = $DB->get_record('role', ['shortname' => 'student']);
        $isvalid = $activeuser->is_role_valid($role->id);
        $this->assertFalse($isvalid);
    }

    /**
     * Can this person enrol themeselves?
     *
     * @return void
     */
    public function test_user_can_enrolself(): void {
        global $USER;
        $this->resetAfterTest();
        $this->setup_bitsnbobs();
        set_config('availableroles', implode(',', [
            $this->roles['tutor'],
            $this->roles['moduleleader'],
            $this->roles['contentretrieval'],
            $this->roles['qatutor'],
            $this->roles['qamoduleleader'],
        ]), 'local_enrolstaff');

        set_config('defaultdepartments', 'academic,support,management', 'local_enrolstaff');
        set_config('defaultexemailpattern', 'jobshop', 'local_enrolstaff');
        set_config('defaultexusernamepattern', 'jobshop', 'local_enrolstaff');

        // Needs some rules.
        /** @var \local_enrolstaff_generator $esdg */
        $esdg = $this->getDataGenerator()->get_plugin_generator('local_enrolstaff');
        // Rule for normal teaching roles.
        $esdg->create_rule([
            'email' => '@solent.ac.uk',
            'username' => '',
            'enabled' => 1,
            'roleids' => [
                $this->roles['tutor'],
                $this->roles['moduleleader'],
            ],

        ]);
        // Rule for QA teaching roles.
        $esdg->create_rule([
            'email' => '@qa.com',
            'username' => '',
            'enabled' => 1,
            'roleids' => [
                $this->roles['qatutor'],
                $this->roles['qamoduleleader'],
            ],
        ]);
        // Rule for content retrieval role.
        // So now there are 2 rules for qa.com users, but you can set different authorisation requirements.
        $esdg->create_rule([
            'email' => '@qa.com',
            'username' => '',
            'enabled' => 1,
            'roleids' => [
                $this->roles['contentretrieval'],
            ],
        ]);

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

        $this->setUser($this->users['contentretrieval']);
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

        // Add more tests for more complex rules later.
    }

    /**
     * Can this user enrol themselves on a given course?
     *
     * @param array $rule
     * @param string $user User key
     * @param array $canenrolon List of courses user can enrol on
     * @return void
     * @dataProvider can_enrolselfon_provider
     */
    public function test_can_enrolselfon($rule, $user, $canenrolon): void {
        global $USER;
        $this->resetAfterTest();
        $this->setup_bitsnbobs();

        set_config('availableroles', implode(',', [
            $this->roles['tutor'],
            $this->roles['moduleleader'],
            $this->roles['contentretrieval'],
            $this->roles['qatutor'],
            $this->roles['qamoduleleader'],
        ]), 'local_enrolstaff');
        /** @var \local_enrolstaff_generator $esdg */
        $esdg = $this->getDataGenerator()->get_plugin_generator('local_enrolstaff');
        $searchrole = $this->roles['tutor'];
        if (isset($rule['role'])) {
            $rule['roleids'][] = $this->roles[$rule['role']];
            $searchrole = $this->roles[$rule['role']];
        } else {
            $rule['roleids'] = [$this->roles['tutor']]; // Tutor role.
        }
        $esdg->create_rule($rule);

        $this->setUser($this->users[$user]);
        $activeuser = new \local_enrolstaff\local\user($USER);
        foreach ($canenrolon as $coursecode => $expectedcanenrol) {
            // You need to do a course search first to populate the cache.
            $activeuser->course_search($coursecode, $searchrole);
            $canenrol = $activeuser->can_enrolselfon($this->courses[$coursecode]->id);
            $this->assertEquals(
                $expectedcanenrol,
                $canenrol,
                "Failed asserting that user {$user} can enrolselfon course {$coursecode}."
            );
        }
    }

    /**
     * Data provider for can_enrolselfon
     *
     * @return array
     */
    public static function can_enrolselfon_provider(): array {
        return [
            'Solent' => [
                'rule' => [
                    'email' => '@solent.ac.uk',
                    'username' => '',
                    'exemail' => 'qa.com$',
                    'exusername' => 'jobshop',
                    'enabled' => 1,
                    'excodes' => ['EXC', 'QHO'],
                ],
                'user' => 'teacher1',
                'canenrolon' => [
                    'ABC101' => true,
                    'XXBAMAK1' => true,
                    'XXBAMAK2' => false,
                    'counselling' => false,
                    'EDU101' => false,
                    'QHO1' => false,
                    'EXCLUDE101' => false,
                ],
            ],
            'QA' => [
                'rule' => [
                    'email' => '@qa.com',
                    'username' => '',
                    'exemail' => 'solent.ac.uk$',
                    'exusername' => 'jobshop',
                    'enabled' => 1,
                    'excodes' => ['EXC', 'ABC', 'XX'],
                ],
                'user' => 'qateacher1',
                'canenrolon' => [
                    'ABC101' => false,
                    'XXBAMAK1' => false,
                    'XXBAMAK2' => false,
                    'counselling' => false,
                    'EDU101' => false,
                    'QHO1' => true,
                    'EXCLUDE101' => false,
                ],
            ],
            'QA content retrieval' => [
                'rule' => [
                    'email' => '@qa.com',
                    'username' => '',
                    'enabled' => 1,
                    'excodes' => ['EXC', 'XX', 'QHO'],
                    'roles' => ['contentretrieval'],
                ],
                'user' => 'contentretrieval',
                'canenrolon' => [
                    'ABC101' => true,
                    'XXBAMAK1' => false,
                    'XXBAMAK2' => false,
                    'counselling' => false,
                    'EDU101' => false,
                    'QHO1' => false,
                    'EXCLUDE101' => false,
                ],
            ],
        ];
    }

    /**
     * Course search is limited by the users permissions to be enrolled on courses.
     *
     * @param array $rule
     * @param string $user User this rule applies to.
     * @param array $matches Count of matches for search term
     * @return void
     * @dataProvider course_search_provider
     */
    public function test_course_search($rule, $user, $matches): void {
        $this->resetAfterTest();
        $this->create_categories();
        $this->create_courses();
        $this->create_users();
        $this->create_roles();
        $this->set_configs();
        /** @var \local_enrolstaff_generator $esdg */
        $esdg = $this->getDataGenerator()->get_plugin_generator('local_enrolstaff');
        $failingusers = [
            'teacher1' => 'teacher1',
            'qateacher1' => 'qateacher1',
            'jobshop101' => 'jobshop101',
        ];
        unset($failingusers[$user]);
        $rule['roleids'] = [$this->roles['tutor']]; // Tutor role.
        $newrule = $esdg->create_rule($rule);

        $this->setUser($this->users[$user]);
        global $USER;
        $activeuser = new \local_enrolstaff\local\user($USER);

        foreach ($rule['codes'] as $code) {
            $result = $activeuser->course_search($code, $this->roles['tutor']);
            // Courses will now be [$ruleid => $courses].
            $courses = [];
            foreach ($result as $ruleid => $rcourses) {
                $courses = array_merge($rcourses, $courses);
            }
            $this->assertCount(
                $matches[$code] ?? 0,
                $courses,
                "Failed asserting that user {$user} could access courses with code {$code}."
            );
        }
        foreach ($rule['excodes'] as $code) {
            $result = $activeuser->course_search($code, $this->roles['tutor']);
            // Courses will now be [$ruleid => $courses].
            $courses = [];
            foreach ($result as $ruleid => $rcourses) {
                $courses = array_merge($rcourses, $courses);
            }
            $this->assertCount(
                0,
                $courses,
                "Failed asserting that user {$user} could not access courses with code {$code}."
            );
        }
        foreach ($failingusers as $failinguser) {
            $this->setUser($this->users[$failinguser]);
            $activeuser = new \local_enrolstaff\local\user($USER);
            foreach ($rule['codes'] as $code) {
                $result = $activeuser->course_search($code, $this->roles['tutor']);
                // Courses will now be [$ruleid => $courses].
                $courses = [];
                foreach ($result as $ruleid => $rcourses) {
                    $courses = array_merge($rcourses, $courses);
                }
                $this->assertCount(
                    0,
                    $courses,
                    "Failed asserting that user {$failinguser} could not access courses with code {$code}."
                );
            }
        }
    }

    /**
     * Data provider for course search
     *
     * @return array
     */
    public static function course_search_provider(): array {
        return [
            'Solent ex qa and jobshop' => [
                'rule' => [
                    'email' => '@solent.ac.uk',
                    'username' => '',
                    'exemail' => 'qa.com$',
                    'exusername' => 'jobshop',
                    'enabled' => 1,
                    'codes' => ['ABC', 'XXBAMAK'],
                    'excodes' => ['EXC', 'QHO'],
                ],
                'user' => 'teacher1',
                'matches' => [
                    'ABC' => 2,
                    'XXBAMAK' => 1,
                ],
            ],
            'QA ex solent and jobshop' => [
                'rule' => [
                    'email' => '@qa.com',
                    'username' => '',
                    'exemail' => 'solent.ac.uk$',
                    'exusername' => 'jobshop',
                    'codes' => ['QHO'],
                    'excodes' => ['EXC', 'ABC'],
                    'enabled' => 1,
                ],
                'user' => 'qateacher1',
                'matches' => [
                    'QHO' => 1,
                ],
            ],
        ];
    }

    /**
     * Course search falls back on hard rules if no other rules apply.
     *
     * @return void
     */
    public function test_course_search_hard_rules(): void {
        global $USER;
        $this->resetAfterTest();
        $this->create_categories();
        $this->create_courses();
        $this->create_users();
        $this->create_roles();
        $this->set_configs();
        /** @var \local_enrolstaff_generator $esdg */
        $esdg = $this->getDataGenerator()->get_plugin_generator('local_enrolstaff');

        // Create a minimal rule (overriding default exclusions), that falls back on the hard rules.
        $esdg->create_rule([
            'email' => '@solent.ac.uk',
            'username' => '',
            'exemail' => '',
            'exusername' => '',
            'enabled' => 1,
            'roleids' => [$this->roles['tutor']], // Tutor role.
        ]);
        $this->setUser($this->users['teacher1']);
        $activeuser = new \local_enrolstaff\local\user($USER);
        $result = $activeuser->course_search('course', $this->roles['tutor']);
        // Courses will now be [$ruleid => $courses].
        $courses = [];
        foreach ($result as $ruleid => $rcourses) {
            $courses = array_merge($rcourses, $courses);
        }
        $this->assertCount(1, $courses);
        $result = $activeuser->course_search('module', $this->roles['tutor']);
        // Courses will now be [$ruleid => $courses].
        $courses = [];
        foreach ($result as $ruleid => $rcourses) {
            $courses = array_merge($rcourses, $courses);
        }
        $this->assertCount(3, $courses);
        // Excode: EDU.
        $result = $activeuser->course_search('EDU101', $this->roles['tutor']);
        // Courses will now be [$ruleid => $courses].
        $courses = [];
        foreach ($result as $ruleid => $rcourses) {
            $courses = array_merge($rcourses, $courses);
        }
        $this->assertCount(0, $courses);
        // Exfullname: Counselling.
        $result = $activeuser->course_search('counselling', $this->roles['tutor']);
        // Courses will now be [$ruleid => $courses].
        $courses = [];
        foreach ($result as $ruleid => $rcourses) {
            $courses = array_merge($rcourses, $courses);
        }
        $this->assertCount(0, $courses);
    }
}
