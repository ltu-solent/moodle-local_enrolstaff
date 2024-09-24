@local @local_enrolstaff @sol @javascript
Feature: Staff member self-unenrols from an existing course
  As a member of Staff
  In order to remove an enrolment
  I should be able to unenrol myself from another teacher's course
  Background:
    Given the following "categories" exist:
    | name         | category | idnumber |
    | Courses      | 0        | CAT1 |
    | Faculty1     | CAT1     | FAC1 |
    | Course pages | FAC1     | courses_FAC1 |
    | Module pages | FAC1     | modules_FAC1 |
    | Level pages  | FAC1     | level_FAC1 |
    | Other        | 0        | Other |
    And the following "courses" exist:
    | fullname        | shortname   | idnumber    | category     | startdate      |
    | Course 1        | C1          | C1          | courses_FAC1 | ##2023-09-01## |
    | Course 2        | C2          | C2          | Other        | ##2023-09-01## |
    | Module 1        | M1          | M1          | modules_FAC1 | ##2023-09-01## |
    | Module 2        | M2          | M2          | modules_FAC1 | ##2023-09-01## |
    | Counselling 101 | counselling | counselling | modules_FAC1 | ##2023-09-01## |
    | Education 101   | EDU101      | EDU101      | modules_FAC1 | ##2023-09-01## |
    | QModule 1       | QHO1        | QHO1        | modules_FAC1 | ##2023-09-01## |
    And the following "users" exist:
    | username       | firstname   | lastname | email                       | department |
    | teacher1       | Teacher     | 1        | teacher1@solent.ac.uk       | Academic   |
    | leader1        | Leader      | 1        | leader1@solent.ac.uk        | academic   |
    | support1       | Support     | 1        | support1@solent.ac.uk       | support    |
    | manage1        | Manage      | 1        | manage1@solent.ac.uk        | management |
    | student1       | Student     | 1        | student1@solent.ac.uk       | student    |
    | qateacher1     | QaTeacher   | 1        | qateacher1@qa.com           | academic   |
    | qaleader1      | QaLeader    | 1        | qaleader1@solent.ac.uk      | academic   |
    | studentrecords | Student     | Records  | studentrecords@solent.ac.uk | Support    |
    | academicpartnerships | Academic | Partnerships | academic.partnerships@solent.ac.uk | support    |
    And the following "roles" exist:
    | name                   | shortname | archetype      |
    | Module leader          | leader    | editingteacher |
    | Tutor                  | tutor     | teacher        |
    | QA Module leader       | qaleader  | editingteacher |
    | QA Tutor               | qatutor   | teacher        |
    And I set the enrolstaff role setting "unitleaderid" to "leader,qaleader"
    And I set the enrolstaff role setting "roleids" to "leader,tutor"
    And I set the enrolstaff role setting "qaheroleids" to "qaleader,qatutor"
    And the following "course enrolments" exist:
    | user      | course | role     |
    | leader1   | C1     | leader   |
    | leader1   | M1     | leader   |
    | qaleader1 | QHO1   | qaleader |
    And the following config values are set as admin:
    | config           | value                       | plugin |
    | qahecodes        | QHO                         | local_enrolstaff |
    | excludeshortname | EDU,PDU                     | local_enrolstaff |
    | excludefullname  | counselling                 | local_enrolstaff |
    | studentrecords   | studentrecords@solent.ac.uk | local_enrolstaff |

  Scenario: SOL academic can unenrol from a module
    Given the following "course enrolments" exist:
    | user     | course | role    |
    | teacher1 | M2     | tutor   |
    | teacher1 | M2     | qatutor |
    And I log in as "teacher1"
    When I visit "/local/enrolstaff/enrolstaff.php"
    And I press "Unenrol from modules"
    Then I should see "Staff Unenrolment"
    And I should see "Module 2"
    And I should see "(already enrolled as Tutor, QA Tutor)"
    When I click on "Module 2" "checkbox"
    And I press "Unenrol from modules"
    Then I should see "You have selected to unenrol from the following modules:"
    And I should see "M2 - Module 2"
    When I press "Confirm"
    Then I should see "You have been unenrolled from your selected modules."
    When I am on "Module 2" course homepage
    Then I should not see "Topic 1"
    And I should see "Enrolment options"

  Scenario: SOL academic can unenrol from a hidden module
    Given the following "course enrolments" exist:
    | user     | course | role    |
    | teacher1 | M2     | tutor   |
    And I am logged in as "admin"
    And I am on "Module 2" course homepage
    And I navigate to "Settings" in current page administration
    And I set the field "Course visibility" to "Hide"
    And I press "Save and display"
    And I log in as "teacher1"
    When I am on "Module 2" course homepage
    Then I should see "Topic 1"
    When I visit "/local/enrolstaff/enrolstaff.php"
    And I press "Unenrol from modules"
    Then I should see "Staff Unenrolment"
    And I should see "Module 2"
    And I should see "(already enrolled as Tutor)"
    When I click on "Module 2" "checkbox"
    And I press "Unenrol from modules"
    Then I should see "You have selected to unenrol from the following modules:"
    And I should see "M2 - Module 2"
    When I press "Confirm"
    Then I should see "You have been unenrolled from your selected modules."
    When I am on "Module 2" course homepage
    Then I should see "This course is currently unavailable to students"

  Scenario: Removes other enrolments (self, csv)
    Given I log in as "admin"
    And I am on the "Module 2" "enrolment methods" page
    And I click on "Enable" "link" in the "Self enrolment (Student)" "table_row"
    And the following "course enrolments" exist:
    | user     | course | role    | enrol    |
    | teacher1 | M2     | student | self     |
    | teacher1 | M2     | tutor   | manual   |
    | teacher1 | M2     | qatutor | manual   |
    And I log in as "teacher1"
    When I am on "Module 2" course homepage
    Then I should see "Topic 1"
    When I visit "/local/enrolstaff/enrolstaff.php"
    And I press "Unenrol from modules"
    Then I should see "Staff Unenrolment"
    And I should see "Module 2"
    And I should see "(already enrolled as Student, Tutor, QA Tutor)"
    When I click on "Module 2" "checkbox"
    And I press "Unenrol from modules"
    Then I should see "You have selected to unenrol from the following modules:"
    And I should see "M2 - Module 2"
    When I press "Confirm"
    Then I should see "You have been unenrolled from your selected modules."
    When I am on "Module 2" course homepage
    # Because the module has self-enrolment enabled
    Then I should see "Self enrolment (Student)"
