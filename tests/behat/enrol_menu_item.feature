@local @local_enrolstaff
Feature: Usermenu contains link to Staff self-enrolments
  As a member of Staff
  In order to view content on a course I am not enrolled on
  I should expect to see a link for self-enrolments given a rule that applies to me
  Background:
    Given the following "roles" exist:
    | name                   | shortname | archetype      |
    | Module leader          | leader    | editingteacher |
    | Tutor                  | tutor     | teacher        |
    | QA Module leader       | qaleader  | editingteacher |
    | QA Tutor               | qatutor   | teacher        |
    And the following config values are set as admin:
    | config                  | value                       | plugin           |
    | availabledepartments    | academic,management,support | local_enrolstaff |
    | defaultemailpattern     | @solent.ac.uk               | local_enrolstaff |
    And I set the enrolstaff role setting "availableroles" to "leader,tutor,qaleader,qatutor"

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
    Then I should <seelink> "Staff enrolment self-service"

    Examples:
      | username   | email                 | department | seelink |
      | test1      | test@solent.ac.uk     | academic   | see     |
      | student1   | student1@solent.ac.uk | student    | not see |
      | test1      | test1@solent.ac.uk    | support    | see     |
      | qatutor    | qatutor@qa.com        | academic   | not see |
      | qaleader   | qaleader@qa.com       | academic   | not see |
      | jobshop1   | john@solent.ac.uk     | support    | not see |
      | john       | jobshop1@solent.ac.uk | support    | not see |
      | john       | john@solent.ac.uk     | management | not see |
