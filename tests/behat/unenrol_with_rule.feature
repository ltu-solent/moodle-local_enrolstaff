@local @local_enrolstaff
Feature: Staff member self-unenrols from an existing course with rules
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
    | fullname        | shortname   | idnumber    | category     |
    | Course 1        | C1          | C1          | courses_FAC1 |
    | Course 2        | C2          | C2          | Other        |
    | Module 1        | ABC101      | ABC101      | modules_FAC1 |
    | Module 2        | ABC102      | ABC102      | modules_FAC1 |
    | Counselling 101 | counselling | counselling | modules_FAC1 |
    | Education 101   | EDU101      | EDU101      | modules_FAC1 |
    | QModule 1       | QHO101      | QHO101      | modules_FAC1 |
    And the following "users" exist:
    | username | firstname | lastname | email                 | department |
    | leader1  | Leader    | 1        | leader1@solent.ac.uk  | academic   |
    | teacher1 | Teacher   | 1        | teacher1@solent.ac.uk | Academic   |
    And the following "roles" exist:
    | name               | shortname  | archetype      |
    | Module leader      | leader     | editingteacher |
    | Associate Lecturer | tutor      | teacher        |
    | Technician         | technician | editingteacher |
    And the following config values are set as admin:
    | config                  | value                       | plugin           |
    | availabledepartments    | academic,management,support | local_enrolstaff |
    | defaultemailpattern     | @solent.ac.uk               | local_enrolstaff |
    And I set the enrolstaff role setting "availableroles" to "leader,tutor,qaleader,qatutor"
    And I set the enrolstaff role setting "notifyroles" to "leader"

  @javascript
  Scenario: User can unenrol themselves from a module
    Given the following "course enrolments" exist:
    | user     | course | role       |
    | teacher1 | ABC102 | tutor      |
    | teacher1 | ABC102 | technician |
    And the following "local_enrolstaff > rule" exists:
    | title       | test rule tutor  |
    | username    |                  |
    | roles       | tutor            |
    | email       | @solent.ac.uk    |
    | departments | academic,support |
    And I log in as "teacher1"
    When I visit "/local/enrolstaff/enrolstaff.php"
    And I press "Unenrol from modules"
    Then I should see "Staff Unenrolment"
    And I should see "Module 2"
    And I should see "(already enrolled as Associate Lecturer, Technician)"
    When I click on "Module 2" "checkbox"
    And I press "Unenrol from modules"
    Then I should see "You have selected to unenrol from the following modules:"
    And I should see "ABC102 - Module 2"
    When I press "Confirm"
    Then I should see "You have been unenrolled from your selected modules."
    When I am on "Module 2" course homepage
    Then I should not see "New section"
    And I should see "Enrolment options"
  
  @javascript
  Scenario: User can unenrol themselves from a hidden module
    Given the following "course enrolments" exist:
    | user     | course | role       |
    | teacher1 | ABC102 | tutor      |
    And the following "local_enrolstaff > rule" exists:
    | title       | test rule tutor  |
    | username    |                  |
    | roles       | tutor            |
    | email       | @solent.ac.uk    |
    | departments | academic,support |
    And I am logged in as "admin"
    And I am on "Module 2" course homepage
    And I navigate to "Settings" in current page administration
    And I set the field "Course visibility" to "Hide"
    And I press "Save and display"
    And I log in as "teacher1"
    # By default editingteacher roles can see hidden courses they are enrolled on.
    When I am on "Module 2" course homepage
    Then I should see "New section"
    When I visit "/local/enrolstaff/enrolstaff.php"
    And I press "Unenrol from modules"
    Then I should see "Staff Unenrolment"
    And I should see "Module 2"
    And I should see "(already enrolled as Associate Lecturer)"
    When I click on "Module 2" "checkbox"
    And I press "Unenrol from modules"
    Then I should see "You have selected to unenrol from the following modules:"
    And I should see "ABC102 - Module 2"
    When I press "Confirm"
    Then I should see "You have been unenrolled from your selected modules."
    When I am on "Module 2" course homepage
    Then I should not see "New section"
    And I should see "This course is currently unavailable to students"

  @javascript
  Scenario: Removes self, csv and manual enrolments
    Given I log in as "admin"
    And I am on the "Module 2" "enrolment methods" page
    And I click on "Enable" "link" in the "Self enrolment (Student)" "table_row"
    And the following "course enrolments" exist:
    | user     | course | role       | enrol  |
    | teacher1 | ABC102 | student    | self   |
    | teacher1 | ABC102 | tutor      | manual |
    | teacher1 | ABC102 | technician | manual |
    And the following "local_enrolstaff > rule" exists:
    | title       | test rule tutor  |
    | username    |                  |
    | roles       | tutor            |
    | email       | @solent.ac.uk    |
    | departments | academic,support |
    And I log in as "teacher1"
    When I am on "Module 2" course homepage
    Then I should see "New section"
    When I visit "/local/enrolstaff/enrolstaff.php"
    And I press "Unenrol from modules"
    Then I should see "Staff Unenrolment"
    And I should see "Module 2"
    And I should see "(already enrolled as Student, Associate Lecturer, Technician)"
    When I click on "Module 2" "checkbox"
    And I press "Unenrol from modules"
    Then I should see "You have selected to unenrol from the following modules:"
    And I should see "ABC102 - Module 2"
    When I press "Confirm"
    Then I should see "You have been unenrolled from your selected modules."
    When I am on "Module 2" course homepage
    # Because the module has self-enrolment enabled
    Then I should see "Self enrolment (Student)"
