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

defined('MOODLE_INTERNAL') || die();

trait local_enrolstaff_helper_trait {
    
    public $moduleleader;
    public $qamoduleleader;
    public $coursescat;
    public $modulescat;
    public $levelscat;
    public $otherscat;
    public $courses;
    public $teachingroles;
    public $qateachingroles;
    public $users;

    public function create_roles() {
        /**
         * Module leader roles will only send a message to student registry, partnerships or admin.
         * Users will not be automatically enrolled.
         */
        $this->moduleleader = $this->getDataGenerator()->create_role([
            'shortname' => 'moduleleader',
            'name' => 'Module leader',
            'archetype' => 'editingteacher'
        ]);
        /**
         * Teaching roles will be automatically enrolled with a curtesy email sent to the module leader.
         */
        $this->teachingroles[] = $this->moduleleader;
        $this->qamoduleleader = $this->getDataGenerator()->create_role([
            'shortname' => 'qamoduleleader',
            'name' => 'QA Module leader',
            'archetype' => 'editingteacher'
        ]);
        $this->qateachingroles[] = $this->qamoduleleader;
        set_config('unitleaderid', $this->moduleleader . ',' . $this->qamoduleleader, 'local_enrolstaff');
        
        $this->teachingroles[] = $this->getDataGenerator()->create_role([
            'shortname' => 'tutor',
            'name' => 'Tutor',
            'archetype' => 'teacher'
        ]);
        $this->qateachingroles[] = $this->getDataGenerator()->create_role([
            'shortname' => 'qatutor',
            'name' => 'QA Tutor',
            'archetype' => 'teacher'
        ]);
        set_config('roleids', implode(",", $this->teachingroles), 'local_enrolstaff');
        set_config('qaheroleids', implode(",", $this->qateachingroles), 'local_enrolstaff');
    }

    public function create_categories() {
        /**
         * Our basic course category structure is:
         * /Courses/Faculty/Courses
         * /Courses/Faculty/Modules
         * 
         * The courses that will be enrollable will be in a category with courses_ or modules_ idnumbers.
         */
        // Not discoverable.
        $cat1 = $this->getDataGenerator()->create_category([
            'idnumber' => 'CAT1',
            'parent' => 0,
            'name' => 'Courses'
        ]);
        // Not discoverable.
        $catfac1 = $this->getDataGenerator()->create_category([
            'idnumber' => 'FAC1',
            'parent' => $cat1->id,
            'name' => 'Faculty1'
        ]);
        // Discoverable.
        $this->coursescat = $this->getDataGenerator()->create_category([
            'idnumber' => 'courses_FAC1',
            'parent' => $catfac1->id,
            'name' => 'Course pages'
        ]);
        // Discoverable.
        $this->modulescat = $this->getDataGenerator()->create_category([
            'idnumber' => 'modules_FAC1',
            'parent' => $catfac1->id,
            'name' => 'Module pages'
        ]);
        // Not discoverable.
        $this->levelscat = $this->getDataGenerator()->create_category([
            'idnumber' => 'level_FAC1',
            'parent' => $catfac1->id,
            'name' => 'Level pages'
        ]);
        // Not discoverable.
        $this->otherscat = $this->getDataGenerator()->create_category([
            'idnumber' => 'Other',
            'parent' => 0,
            'name' => 'Other'
        ]);

    }

    public function create_courses() {
        // Discoverable.
        $this->courses['C1'] = $this->getDataGenerator()->create_course([
            'fullname' => 'Course 1',
            'shortname' => 'C1',
            'idnumber' => 'C1',
            'category' => $this->coursescat->id
        ]);
        // Not discoverable.
        $this->courses['C2'] = $this->getDataGenerator()->create_course([
            'fullname' => 'Course 2',
            'shortname' => 'C2',
            'idnumber' => 'C2',
            'category' => $this->otherscat->id
        ]);
        // Discoverable.
        $this->courses['M1'] = $this->getDataGenerator()->create_course([
            'fullname' => 'Module 1',
            'shortname' => 'M1',
            'idnumber' => 'M1',
            'category' => $this->modulescat->id
        ]);
        // Discoverable.
        $this->courses['M2'] = $this->getDataGenerator()->create_course([
            'fullname' => 'Module 2',
            'shortname' => 'M2',
            'idnumber' => 'M2',
            'category' => $this->modulescat->id
        ]);
        // Restricted.
        $this->courses['counselling'] = $this->getDataGenerator()->create_course([
            'fullname' => 'Counselling 101',
            'shortname' => 'counselling',
            'idnumber' => 'counselling',
            'category' => $this->modulescat->id
        ]);
        // Restricted.
        $this->courses['EDU101'] = $this->getDataGenerator()->create_course([
            'fullname' => 'Education 101',
            'shortname' => 'EDU101',
            'idnumber' => 'EDU101',
            'category' => $this->modulescat->id
        ]);
        // Restricted.
        $this->courses['EXCLUDE101'] = $this->getDataGenerator()->create_course([
            'fullname' => 'Exclude 101',
            'shortname' => 'EXCLUDE101',
            'idnumber' => 'EXCLUDE101',
            'category' => $this->modulescat->id
        ]);
        // Limited to QAHE partners.
        $this->courses['QHO1'] = $this->getDataGenerator()->create_course([
            'fullname' => 'QModule 1',
            'shortname' => 'QHO1',
            'idnumber' => 'QHO1',
            'category' => $this->modulescat->id
        ]);
    }

    public function create_users() {

        $this->users['teacher1'] = $this->getDataGenerator()->create_user([
            'username' => 'teacher1',
            'firstname' => 'Teacher',
            'lastname' => '1',
            'email' => 'teacher1@solent.ac.uk',
            'department' => 'academic'
        ]);
        $this->users['leader1'] = $this->getDataGenerator()->create_user([
            'username' => 'leader1',
            'firstname' => 'Leader',
            'lastname' => '1',
            'email' => 'leader1@solent.ac.uk',
            'department' => 'academic'
        ]);
        $this->users['support1'] = $this->getDataGenerator()->create_user([
            'username' => 'support1',
            'firstname' => 'Support',
            'lastname' => '1',
            'email' => 'support1@solent.ac.uk',
            'department' => 'support'
        ]);
        $this->users['manage1'] = $this->getDataGenerator()->create_user([
            'username' => 'manage1',
            'firstname' => 'Manage',
            'lastname' => '1',
            'email' => 'manage1@solent.ac.uk',
            'department' => 'management'
        ]);
        // Cannot use this service.
        $this->users['student1'] = $this->getDataGenerator()->create_user([
            'username' => 'student1',
            'firstname' => 'Student',
            'lastname' => '1',
            'email' => 'student1@solent.ac.uk',
            'department' => 'student'
        ]);
        // Limited to QAHE courses.
        $this->users['qateacher1'] = $this->getDataGenerator()->create_user([
            'username' => 'qateacher1',
            'firstname' => 'QaTeacher',
            'lastname' => '1',
            'email' => 'qateacher1@qa.com',
            'department' => 'academic'
        ]);
        // Limited to QAHE courses.
        $this->users['qaleader1'] = $this->getDataGenerator()->create_user([
            'username' => 'qaleader1',
            'firstname' => 'QaLeader',
            'lastname' => '1',
            'email' => 'qaleader1@qa.com',
            'department' => 'academic'
        ]);
        $this->users['studentrecords'] = $this->getDataGenerator()->create_user([
            'username' => 'studentrecords',
            'firstname' => 'Student',
            'lastname' => 'Records',
            'email' => 'studentrecords@solent.ac.uk',
            'department' => 'support'
        ]);
        $this->users['academicpartnerships'] = $this->getDataGenerator()->create_user([
            'username' => 'academicpartnerships',
            'firstname' => 'Academic',
            'lastname' => 'Partnerships',
            'email' => 'academic.partnerships@solent.ac.uk',
            'department' => 'support'
        ]);
        // Cannot use this service.
        $this->users['jobshop101'] = $this->getDataGenerator()->create_user([
            'username' => 'jobshop101',
            'firstname' => 'Herbert',
            'lastname' => 'Jobshop',
            'email' => 'jobshop101@solent.ac.uk',
            'department' => 'support'
        ]);
    }

    public function set_configs() {
        set_config('excludeshortname', 'EDU', 'local_enrolstaff');
        set_config('excludefullname', 'counselling', 'local_enrolstaff');
        set_config('excludeid', $this->courses['EXCLUDE101']->id, 'local_enrolstaff');
        set_config('studentrecords', 'studentrecords@solent.ac.uk', 'local_enrolstaff');
        set_config('qahecontact', 'academic.partnerships@solent.ac.uk', 'local_enrolstaff');
        set_config('qahecodes', 'QHO', 'local_enrolstaff');
    }
}
