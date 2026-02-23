@local @local_enrolstaff
Feature: Displays users that a rule filter would apply to
  As an admin
  In order to check that a rule applies as I expect
  I should be able to view all candidate users for a given rule

  Background:
    Given the following "cohorts" exist:
      | name     | idnumber  |
      | Cohort 1 | cohort1   |
      | Cohort 2 | cohort2   |
    And the following "roles" exist:
      | name              | shortname | archetype      |
      | Module leader     | leader    | editingteacher |
      | Associate Lecturer| tutor     | teacher        |
    And I set the enrolstaff role setting "availableroles" to "leader,tutor"
    And I set the enrolstaff role setting "notifyroles" to "leader"
    And I set the enrolstaff cohort setting "availablecohorts" to "cohort1,cohort2"
    And the following config values are set as admin:
      | availabledepartments | academic,management,support | local_enrolstaff |
    And the following "users" exist:
      | username | firstname | lastname | email                | department |
      | user1    | User      | One      | user1@solent.ac.uk   | academic   |
      | user2    | User      | Two      | user2@solent.ac.uk   | management |
      | user3    | User      | Three    | user3@external.com   | academic   |
      | user4    | User      | Four     | user4@solent.ac.uk   | support    |
      | user5    | User      | Five     | user5@partner.ac.uk  | academic   |
      | qaleader | QA        | Leader   | qaleader@qa.com      | academic   |
    And the following "cohort members" exist:
      | user  | cohort  |
      | user1 | cohort1 |
      | user2 | cohort1 |
      | user3 | cohort2 |

  @javascript
  Scenario: Preview rule with email pattern filter
    Given the following "local_enrolstaff > rule" exists:
      | title       | Test email filter |
      | roles       | tutor             |
      | email       | @solent.ac.uk     |
      | departments |                   |
      | username    |                   |
    When I am logged in as admin
    And I visit "/local/enrolstaff/manage.php"
    And I open the action menu in "Test email filter" "table_row"
    And I follow "Preview rule"
    Then I should see "Preview of rule: Test email filter"
    And I should see "User One"
    And I should see "User Two"
    And I should see "User Four"
    And I should not see "User Three"
    And I should not see "User Five"

  @javascript
  Scenario: Preview rule with department filter
    Given the following "local_enrolstaff > rule" exists:
      | title       | Test department filter |
      | roles       | tutor                  |
      | email       | @solent.ac.uk          |
      | departments | academic               |
      | username    |                        |
    When I am logged in as admin
    And I visit "/local/enrolstaff/manage.php"
    And I open the action menu in "Test department filter" "table_row"
    And I follow "Preview rule"
    Then I should see "Preview of rule: Test department filter"
    And I should see "User One"
    And I should not see "User Two"
    And I should not see "User Three"
    And I should not see "User Four"

  @javascript
  Scenario: Preview rule with multiple department filter
    Given the following "local_enrolstaff > rule" exists:
      | title       | Test multi department |
      | roles       | tutor                 |
      | email       | @solent.ac.uk         |
      | departments | academic,management   |
      | username    |                       |
    When I am logged in as admin
    And I visit "/local/enrolstaff/manage.php"
    And I open the action menu in "Test multi department" "table_row"
    And I follow "Preview rule"
    Then I should see "Preview of rule: Test multi department"
    And I should see "User One"
    And I should see "User Two"
    And I should not see "User Three"
    And I should not see "User Four"

  @javascript
  Scenario: Preview rule with cohort filter
    Given the following "local_enrolstaff > rule" exists:
      | title       | Test cohort filter |
      | roles       | tutor              |
      | email       |                    |
      | departments | academic           |
      | cohorts     | Cohort 1           |
      | username    |                    |
    When I am logged in as admin
    And I visit "/local/enrolstaff/manage.php"
    And I open the action menu in "Test cohort filter" "table_row"
    And I follow "Preview rule"
    Then I should see "Preview of rule: Test cohort filter"
    And I should see "User One"
    And I should see "Cohort 1"
    And I should not see "User Three"
    And I should not see "User Four"

  @javascript
  Scenario: Preview rule with username pattern filter
    Given the following "local_enrolstaff > rule" exists:
      | title       | Test username filter |
      | roles       | tutor                |
      | email       |                      |
      | departments | academic             |
      | username    | ^user[1-3]$          |
    When I am logged in as admin
    And I visit "/local/enrolstaff/manage.php"
    And I open the action menu in "Test username filter" "table_row"
    And I follow "Preview rule"
    Then I should see "Preview of rule: Test username filter"
    And I should see "User One"
    And I should see "User Three"
    And I should not see "User Four"
    And I should not see "User Five"

  @javascript
  Scenario: Preview rule with exclude email pattern
    Given the following "local_enrolstaff > rule" exists:
      | title       | Test exclude email |
      | roles       | tutor              |
      | email       |                    |
      | departments | academic           |
      | exemail     | @external.com      |
      | username    |                    |
    When I am logged in as admin
    And I visit "/local/enrolstaff/manage.php"
    And I open the action menu in "Test exclude email" "table_row"
    And I follow "Preview rule"
    Then I should see "Preview of rule: Test exclude email"
    And I should see "User One"
    And I should see "User Five"
    And I should not see "User Three"

  @javascript
  Scenario: Preview rule with exclude username pattern
    Given the following "local_enrolstaff > rule" exists:
      | title       | Test exclude username |
      | roles       | tutor                 |
      | email       | @solent.ac.uk         |
      | departments |                       |
      | exusername  | user1                 |
      | username    |                       |
    When I am logged in as admin
    And I visit "/local/enrolstaff/manage.php"
    And I open the action menu in "Test exclude username" "table_row"
    And I follow "Preview rule"
    Then I should see "Preview of rule: Test exclude username"
    And I should see "User Two"
    And I should see "User Four"
    And I should not see "User One"

  @javascript
  Scenario: Preview rule with combined filters
    Given the following "local_enrolstaff > rule" exists:
      | title       | Test combined filters |
      | roles       | tutor                 |
      | email       | @solent.ac.uk         |
      | departments | academic              |
      | cohorts     | Cohort 1              |
      | username    |                       |
    When I am logged in as admin
    And I visit "/local/enrolstaff/manage.php"
    And I open the action menu in "Test combined filters" "table_row"
    And I follow "Preview rule"
    Then I should see "Preview of rule: Test combined filters"
    And I should see "User One"
    And I should see "Cohort 1"
    And I should not see "User Two"
    And I should not see "User Three"
    And I should not see "User Four"

  @javascript
  Scenario: Preview rule matching no users
    Given the following "local_enrolstaff > rule" exists:
      | title       | Test no matches       |
      | roles       | tutor                 |
      | email       | @nonexistent.com      |
      | departments |                       |
      | username    |                       |
    When I am logged in as admin
    And I visit "/local/enrolstaff/manage.php"
    And I open the action menu in "Test no matches" "table_row"
    And I follow "Preview rule"
    Then I should see "Preview of rule: Test no matches"
    And I should see "Nothing to display"

  @javascript
  Scenario: Preview displays filter summary
    Given the following "local_enrolstaff > rule" exists:
      | title       | Test filter display |
      | roles       | tutor               |
      | email       | @solent.ac.uk       |
      | departments | academic,support    |
      | username    | ^user               |
      | exemail     | @external.com       |
      | username    |                     |
    When I am logged in as admin
    And I visit "/local/enrolstaff/manage.php"
    And I open the action menu in "Test filter display" "table_row"
    And I follow "Preview rule"
    Then I should see "Preview of rule: Test filter display"
    And I should see "Has an \"email\" that looks like \"@solent.ac.uk\""
    And I should see "Doesn't have an email that looks like \"@external.com\""

  @javascript
  Scenario: Preview rule with institution filter
    Given the following "users" exist:
      | username | firstname | lastname | email              | department | institution |
      | inst1    | Inst      | One      | inst1@solent.ac.uk | academic   | Solent      |
      | inst2    | Inst      | Two      | inst2@solent.ac.uk | academic   | Partner     |
    And the following "local_enrolstaff > rule" exists:
      | title       | Test institution filter |
      | roles       | tutor                   |
      | email       |                         |
      | departments | academic                |
      | institution | Solent                  |
      | username    |                         |
    When I am logged in as admin
    And I visit "/local/enrolstaff/manage.php"
    And I open the action menu in "Test institution filter" "table_row"
    And I follow "Preview rule"
    Then I should see "Preview of rule: Test institution filter"
    And I should see "Inst One"
    And I should not see "Inst Two"
