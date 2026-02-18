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
 * Sets up the plugin with sensible settings and objects that can used in testing.
 *
 * @package   local_enrolstaff
 * @author    Mark Sharp <mark.sharp@solent.ac.uk>
 * @copyright 2021 Solent University {@link https://www.solent.ac.uk}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_enrolstaff;

use core_course_category;
use stdClass;

/**
 * Some reusable components for unit tests
 */
trait helper_trait {
    /**
     * Module leader role
     *
     * @var int
     */
    public int $moduleleader;

    /**
     * QA module leader role
     *
     * @var int
     */
    public int $qamoduleleader;

    /**
     * Courses category
     *
     * @var core_course_category
     */
    public core_course_category $coursescat;

    /**
     * Modules category
     *
     * @var core_course_category
     */
    public core_course_category $modulescat;

    /**
     * Levels category
     *
     * @var core_course_category
     */
    public core_course_category $levelscat;

    /**
     * Others category
     *
     * @var core_course_category
     */
    public core_course_category $otherscat;

    /**
     * Cohorts
     *
     * @var array
     */
    public array $cohorts = [];

    /**
     * Courses/Modules
     *
     * @var array
     */
    public array $courses;

    /**
     * All roles
     *
     * @var array
     */
    public array $roles = [];

    /**
     * Teaching role
     *
     * @var array
     */
    public array $teachingroles;

    /**
     * Qa teaching roles
     *
     * @var array
     */
    public array $qateachingroles;

    /**
     * Users
     *
     * @var array
     */
    public array $users;

    /**
     * Sets up roles and role settings for plugins
     *
     * @return void
     */
    public function create_roles() {
         // Module leader roles will only send a message to student registry, partnerships or admin.
         // Users will not be automatically enrolled.
        $this->moduleleader = $this->getDataGenerator()->create_role([
            'shortname' => 'moduleleader',
            'name' => 'Module leader',
            'archetype' => 'editingteacher',
        ]);
        $this->roles['moduleleader'] = $this->moduleleader;
        $this->roles['contentretrieval'] = $this->getDataGenerator()->create_role([
            'shortname' => 'contentretrieval',
            'name' => 'Content retrieval',
            'archetype' => 'student',
        ]);

        // Teaching roles will be automatically enrolled with a curtesy email sent to the module leader.
        $this->teachingroles[] = $this->moduleleader;
        $this->qamoduleleader = $this->getDataGenerator()->create_role([
            'shortname' => 'qamoduleleader',
            'name' => 'QA Module leader',
            'archetype' => 'editingteacher',
        ]);
        $this->roles['qamoduleleader'] = $this->qamoduleleader;
        $this->qateachingroles[] = $this->qamoduleleader;
        set_config('unitleaderid', $this->moduleleader . ',' . $this->qamoduleleader, 'local_enrolstaff');

        $this->teachingroles[] = $this->getDataGenerator()->create_role([
            'shortname' => 'tutor',
            'name' => 'Tutor',
            'archetype' => 'teacher',
        ]);
        $this->roles['tutor'] = end($this->teachingroles);
        $this->qateachingroles[] = $this->getDataGenerator()->create_role([
            'shortname' => 'qatutor',
            'name' => 'QA Tutor',
            'archetype' => 'teacher',
        ]);
        $this->roles['qatutor'] = end($this->qateachingroles);
        set_config('roleids', implode(",", $this->teachingroles), 'local_enrolstaff');
        set_config('qaheroleids', implode(",", $this->qateachingroles), 'local_enrolstaff');
    }

    /**
     * Sets up a Solent category structure, used to find modules/courses.
     *
     * @return void
     */
    public function create_categories() {
        // Our basic course category structure is:
        // /Courses/Faculty/Courses
        // /Courses/Faculty/Modules
        // The courses that will be enrollable will be in a category with courses_ or modules_ idnumbers.

        // Not discoverable.
        $cat1 = $this->getDataGenerator()->create_category([
            'idnumber' => 'CAT1',
            'parent' => 0,
            'name' => 'Courses',
        ]);
        // Not discoverable.
        $catfac1 = $this->getDataGenerator()->create_category([
            'idnumber' => 'FAC1',
            'parent' => $cat1->id,
            'name' => 'Faculty1',
        ]);
        // Discoverable.
        $this->coursescat = $this->getDataGenerator()->create_category([
            'idnumber' => 'courses_FAC1',
            'parent' => $catfac1->id,
            'name' => 'Course pages',
        ]);
        // Discoverable.
        $this->modulescat = $this->getDataGenerator()->create_category([
            'idnumber' => 'modules_FAC1',
            'parent' => $catfac1->id,
            'name' => 'Module pages',
        ]);
        // Not discoverable.
        $this->levelscat = $this->getDataGenerator()->create_category([
            'idnumber' => 'level_FAC1',
            'parent' => $catfac1->id,
            'name' => 'Level pages',
        ]);
        // Not discoverable.
        $this->otherscat = $this->getDataGenerator()->create_category([
            'idnumber' => 'Other',
            'parent' => 0,
            'name' => 'Other',
        ]);
    }

    /**
     * Sets up some cohorts to use in testing.
     *
     * @return void
     */
    public function create_cohorts() {
        // Create some cohorts to use in testing.
        $this->cohorts['cohort1'] = $this->getDataGenerator()->create_cohort([
            'name' => 'Cohort 1',
            'idnumber' => 'COH1',
        ]);
        $this->cohorts['cohort2'] = $this->getDataGenerator()->create_cohort([
            'name' => 'Cohort 2',
            'idnumber' => 'COH2',
        ]);
        $this->cohorts['cohort3'] = $this->getDataGenerator()->create_cohort([
            'name' => 'Cohort 3',
            'idnumber' => 'COH3',
        ]);
    }

    /**
     * Sets up some courses which can or cannot be selected
     *
     * @return void
     */
    public function create_courses() {
        // Discoverable.
        $this->courses['XXBAMAK1'] = $this->getDataGenerator()->create_course([
            'fullname' => 'Course 1',
            'shortname' => 'XXBAMAK1',
            'idnumber' => 'XXBAMAK1',
            'category' => $this->coursescat->id,
        ]);
        // Not discoverable.
        $this->courses['XXBAMAK2'] = $this->getDataGenerator()->create_course([
            'fullname' => 'Course 2',
            'shortname' => 'XXBAMAK2',
            'idnumber' => 'XXBAMAK2',
            'category' => $this->otherscat->id,
        ]);
        // Discoverable.
        $this->courses['ABC101'] = $this->getDataGenerator()->create_course([
            'fullname' => 'Module 1',
            'shortname' => 'ABC101',
            'idnumber' => 'ABC101',
            'category' => $this->modulescat->id,
        ]);
        // Discoverable.
        $this->courses['ABC102'] = $this->getDataGenerator()->create_course([
            'fullname' => 'Module 2',
            'shortname' => 'ABC102',
            'idnumber' => 'ABC102',
            'category' => $this->modulescat->id,
        ]);
        // Restricted.
        $this->courses['counselling'] = $this->getDataGenerator()->create_course([
            'fullname' => 'Counselling 101',
            'shortname' => 'counselling',
            'idnumber' => 'counselling',
            'category' => $this->modulescat->id,
        ]);
        // Restricted.
        $this->courses['EDU101'] = $this->getDataGenerator()->create_course([
            'fullname' => 'Education 101',
            'shortname' => 'EDU101',
            'idnumber' => 'EDU101',
            'category' => $this->modulescat->id,
        ]);
        // Restricted.
        $this->courses['EXCLUDE101'] = $this->getDataGenerator()->create_course([
            'fullname' => 'Exclude 101',
            'shortname' => 'EXCLUDE101',
            'idnumber' => 'EXCLUDE101',
            'category' => $this->modulescat->id,
        ]);
        // Restricted.
        $this->courses['EXCLUDE102'] = $this->getDataGenerator()->create_course([
            'fullname' => 'Exclude 102',
            'shortname' => 'EXCLUDE102',
            'idnumber' => 'EXCLUDE102',
            'category' => $this->modulescat->id,
        ]);
        // Limited to QAHE partners.
        $this->courses['QHO1'] = $this->getDataGenerator()->create_course([
            'fullname' => 'QModule 1',
            'shortname' => 'QHO1',
            'idnumber' => 'QHO1',
            'category' => $this->modulescat->id,
        ]);
    }

    /**
     * Different types of users to test.
     *
     * @return void
     */
    public function create_users() {
        $this->users['teacher1'] = $this->getDataGenerator()->create_user([
            'username' => 'teacher1',
            'firstname' => 'Teacher',
            'lastname' => '1',
            'email' => 'teacher1@solent.ac.uk',
            'department' => 'academic',
        ]);
        $this->users['leader1'] = $this->getDataGenerator()->create_user([
            'username' => 'leader1',
            'firstname' => 'Leader',
            'lastname' => '1',
            'email' => 'leader1@solent.ac.uk',
            'department' => 'Academic',
        ]);
        $this->users['support1'] = $this->getDataGenerator()->create_user([
            'username' => 'support1',
            'firstname' => 'Support',
            'lastname' => '1',
            'email' => 'support1@solent.ac.uk',
            'department' => 'support',
        ]);
        $this->users['manage1'] = $this->getDataGenerator()->create_user([
            'username' => 'manage1',
            'firstname' => 'Manage',
            'lastname' => '1',
            'email' => 'manage1@solent.ac.uk',
            'department' => 'management',
        ]);
        // Cannot use this service.
        $this->users['student1'] = $this->getDataGenerator()->create_user([
            'username' => 'student1',
            'firstname' => 'Student',
            'lastname' => '1',
            'email' => 'student1@solent.ac.uk',
            'department' => 'student',
        ]);
        // Limited to QAHE courses.
        $this->users['qateacher1'] = $this->getDataGenerator()->create_user([
            'username' => 'qateacher1',
            'firstname' => 'QaTeacher',
            'lastname' => '1',
            'email' => 'qateacher1@qa.com',
            'department' => 'academic',
        ]);
        // Limited to QAHE courses.
        $this->users['qaleader1'] = $this->getDataGenerator()->create_user([
            'username' => 'qaleader1',
            'firstname' => 'QaLeader',
            'lastname' => '1',
            'email' => 'qaleader1@qa.com',
            'department' => 'Academic',
        ]);
        $this->users['studentrecords'] = $this->getDataGenerator()->create_user([
            'username' => 'studentrecords',
            'firstname' => 'Student',
            'lastname' => 'Records',
            'email' => 'studentrecords@solent.ac.uk',
            'department' => 'support',
        ]);
        $this->users['academicpartnerships'] = $this->getDataGenerator()->create_user([
            'username' => 'academicpartnerships',
            'firstname' => 'Academic',
            'lastname' => 'Partnerships',
            'email' => 'academic.partnerships@solent.ac.uk',
            'department' => 'Support',
        ]);
        // Cannot use this service.
        $this->users['jobshop101'] = $this->getDataGenerator()->create_user([
            'username' => 'jobshop101',
            'firstname' => 'Herbert',
            'lastname' => 'Jobshop',
            'email' => 'jobshop101@solent.ac.uk',
            'department' => 'support',
        ]);
        $this->users['contentretrieval'] = $this->getDataGenerator()->create_user([
            'username' => 'contentretrieval',
            'firstname' => 'Content',
            'lastname' => 'Retrieval',
            'email' => 'contentretrieval@qa.com',
            'department' => 'academic',
        ]);
    }

    /**
     * Plugin settings with some realistic defaults.
     *
     * @return void
     */
    public function set_configs() {
        set_config('excludeshortname', 'EDU', 'local_enrolstaff');
        set_config('excludefullname', 'counselling', 'local_enrolstaff');
        set_config('excludeid', $this->courses['EXCLUDE101']->id, 'local_enrolstaff');
        set_config('studentrecords', 'studentrecords@solent.ac.uk', 'local_enrolstaff');
        set_config('qahecontact', 'academic.partnerships@solent.ac.uk', 'local_enrolstaff');
        set_config('qahecodes', 'QHO', 'local_enrolstaff');
    }
}
