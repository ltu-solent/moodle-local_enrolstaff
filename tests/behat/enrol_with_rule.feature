@local @local_enrolstaff
Feature: Staff member self-enrols onto an existing course with rules
  As a member of Staff
  In order to view content on a course I am not enrolled on
  I should be able to enrol myself given a rule that permits me

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
    | username             | firstname | lastname     | email                              | department |
    | leader1              | Leader    | 1            | leader1@solent.ac.uk               | academic   |
    And the following "roles" exist:
    | name                   | shortname | archetype      |
    | Module leader          | leader    | editingteacher |
    | Tutor                  | tutor     | teacher        |
    | QA Module leader       | qaleader  | editingteacher |
    | QA Tutor               | qatutor   | teacher        |
    And the following "course enrolments" exist:
    | user      | course | role     |
    | leader1   | C1     | leader   |
    | leader1   | M1     | leader   |
    And the following config values are set as admin:
    | config           | value                       | plugin           |
    | excludeshortname | EDU,PDU                     | local_enrolstaff |
    | excludefullname  | counselling                 | local_enrolstaff |

  @javascript
  Scenario Outline: Menu item contains link to Self-service depending on matching rules
    Given the following "local_enrolstaff > rule" exists:
    | title       | test rule        |
    | username    |                  |
    | roles       | tutor            |
    | email       | @solent.ac.uk    |
    | departments | academic,support |
    | exusername  | jobshop          |
    | exemail     | jobshop          |
    And the following "users" exist:
    | username   | email   | department   |
    | <username> | <email> | <department> |
    And I am logged in as <username>
    And I click on ".usermenu" "css_element"
    Then I should <see> "Staff enrolment self-service"

    Examples:
    | username   | role     | email                 | department | see     |
    | test1      | tutor    | test@solent.ac.uk     | academic   | see     |
    | student1   | student  | student1@solent.ac.uk | student    | not see |
    | test1      | leader   | test1@solent.ac.uk    | academic   | see     |
    | qatutor    | qatutor  | qatutor@qa.com        | academic   | not see |
    | qaleader   | qaleader | qaleader@qa.com       | academic   | not see |

  @javascript
  Scenario Outline: No roles available though matching for the service
    Given the following "local_enrolstaff > rule" exists:
    | title       | test rule        |
    | username    |                  |
    | roles       | tutor            |
    | email       | @solent.ac.uk    |
    | departments | academic,support |
    | exusername  | jobshop          |
    | exemail     | jobshop          |
    And the following "users" exist:
    | username   | email   | department   |
    | <username> | <email> | <department> |
    And I am logged in as <username>
    And I follow "Staff enrolment self-service" in the user menu
    Then I should <see> "Staff enrolment self-service"
    And I click on "Role" "select"
    And I should see "Tutor" in the "Role" "select"

    Examples:
    | username   | role     | email                 | department | see     |
    | test1      | tutor    | test@solent.ac.uk     | academic   | see     |
    | test1      | leader   | test1@solent.ac.uk    | academic   | see     |