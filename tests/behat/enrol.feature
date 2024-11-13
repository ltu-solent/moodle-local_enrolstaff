@local @local_enrolstaff @sol
Feature: Staff member self-enrols onto an existing course
  As a member of Staff
  In order to view content on another course
  I should be able to enrol myself onto another teacher's course
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
    | Module 1        | M1          | M1          | modules_FAC1 |
    | Module 2        | M2          | M2          | modules_FAC1 |
    | Counselling 101 | counselling | counselling | modules_FAC1 |
    | Education 101   | EDU101      | EDU101      | modules_FAC1 |
    | QModule 1       | QHO1        | QHO1        | modules_FAC1 |
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

  @javascript
  Scenario: SOL academic requests enrolment as Module leader
    Given I log in as "teacher1"
    When I visit "/local/enrolstaff/enrolstaff.php"
    Then I should see "Staff enrolment self-service"
    And I select "Module leader" from the "Role" singleselect
    And I press "Select a role"
    Then I should see "Please speak to the Module or Course Leader if you are unsure of the correct module or instance code."
    And I set the field "Module code" to "M1"
    And I press "Search"
    Then I should see "Select a module"
    And I click on "Module 1" "radio"
    And I press "Select module"
    Then I should see "You are about to send a request for enrolment on Module 1 with the role of Module leader"
    And I press "Confirm"
    And I should see "You will receive an email confirmation with further information."
    And I am on "Module 1" course homepage
    And I should see "You cannot enrol yourself in this course."

  @javascript
  Scenario: SOL academic self-enrols on module or course as Tutor
    Given I log in as "teacher1"
    When I visit "/local/enrolstaff/enrolstaff.php"
    And I select "Tutor" from the "Role" singleselect
    And I press "Select a role"
    Then I should see "Please speak to the Module or Course Leader if you are unsure of the correct module or instance code."
    And I set the field "Module code" to "M2"
    And I press "Search"
    Then I should see "Select a module"
    And I click on "Module 2" "radio"
    And I press "Select module"
    Then I should see "You are about to be enrolled on Module 2 with the role of Tutor"
    And I press "Confirm"
    Then I should see "You have been enrolled on M2 as Tutor"
    And I am on "Module 2" course homepage
    And I should see "Module 2"
    And I should see "New section"

  @javascript
  Scenario: QAHE academic requests enrolment as Module leader
    Given I log in as "qateacher1"
    When I visit "/local/enrolstaff/enrolstaff.php"
    Then I should see "Staff enrolment self-service"
    And I select "QA Module leader" from the "Role" singleselect
    And I press "Select a role"
    Then I should see "Please speak to the Module or Course Leader if you are unsure of the correct module or instance code."
    And I set the field "Module code" to "QHO"
    And I press "Search"
    Then I should see "Select a module"
    And I click on "QModule 1" "radio"
    And I press "Select module"
    Then I should see "You are about to send a request for enrolment on QModule 1 with the role of QA Module leader"
    And I press "Confirm"
    And I should see "You will receive an email confirmation with further information."
    And I am on "QModule 1" course homepage
    And I should see "You cannot enrol yourself in this course."

  @javascript
  Scenario: QAHE academic self-enrols on module or course as QATutor
    Given I log in as "qateacher1"
    When I visit "/local/enrolstaff/enrolstaff.php"
    And I select "QA Tutor" from the "Role" singleselect
    And I press "Select a role"
    Then I should see "Please speak to the Module or Course Leader if you are unsure of the correct module or instance code."
    And I set the field "Module code" to "QHO"
    And I press "Search"
    Then I should see "Select a module"
    And I click on "QModule 1" "radio"
    And I press "Select module"
    Then I should see "You are about to be enrolled on QModule 1 with the role of QA Tutor"
    And I press "Confirm"
    Then I should see "You have been enrolled on QHO1 as QA Tutor"
    And I am on "QModule 1" course homepage
    And I should see "QModule 1"
    And I should see "New section"
