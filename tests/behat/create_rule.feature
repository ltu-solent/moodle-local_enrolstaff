@local @local_enrolstaff
Feature: Site admin can create a new rule
  As a site admin
  In order to allow staff to self-enrol
  I need to create rules that permits enrolments

Background:
  Given the following "cohorts" exist:
  | name            | idnumber     |
  | Cohort 1        | cohort1      |
  | Cohort 2        | cohort2      |
  | Excluded cohort | notavailable |
  And the following "roles" exist:
  | name                   | shortname | archetype      |
  | Module leader          | leader    | editingteacher |
  | Associate Lecturer     | tutor     | teacher        |
  | QA Module leader       | qaleader  | editingteacher |
  | QA Tutor               | qatutor   | teacher        |
  | Exclude Tutor          | extutor   | editingteacher |
  And I set the enrolstaff role setting "availableroles" to "leader,tutor,qaleader,qatutor"
  And I set the enrolstaff role setting "notifyroles" to "leader"
  And I set the enrolstaff cohort setting "availablecohorts" to "cohort1,cohort2"
  And the following config values are set as admin:
  | defaultexpireenrolments | 547 | local_enrolstaff |

@javascript
Scenario: At least one Filter must be set
  # Because no one filter itself is required, if no filters are added the rule is automatically disabled.
  Given I am logged in as admin
  And the following config values are set as admin:
  | defaultusernamepattern |  | local_enrolstaff |
  | defaultemailpattern    |  | local_enrolstaff |
  | defaultdepartments     |  | local_enrolstaff |
  And I visit "/local/enrolstaff/manage.php"
  And I follow "New Staff rule"
  And I set the following fields to these values:
  | Title           | Test rule          |
  | Enabled         | Yes                |
  | Available roles | Associate Lecturer |
  | Authorisation   | No notification    |
  When I press "Save changes"
  Then I should see "New Staff rule created"
  And the following should exist in the "local_enrolstaff_rules" table:
  | -2-       | -6-         |
  | Test rule | Not enabled |
  When I click on "Test rule" "link"
  Then I should see "Choose at least one filter"
  And the "Enabled" "field" should be disabled
  And I set the following fields to these values:
  | Email pattern | @solent.ac.uk |
  When I press "Save changes"
  Then I should see "\"Test rule\" has been updated"
  And I should see "Has an \"email\" that looks like \"@solent.ac.uk\""
  # It's not automatically enabled.
  And the following should exist in the "local_enrolstaff_rules" table:
  | -2-       | -6-         |
  | Test rule | Not enabled |
  # But we can quickly enable it from an action menu.
  When I open the action menu in "Test rule" "table_row"
  And I choose "Enabled" in the open action menu
  Then the following should exist in the "local_enrolstaff_rules" table:
  | -2-       | -6-         |
  | Test rule | Enabled     |
  
@javascript
Scenario: Available field values are set
  # Some field options are controlled by availability settings.
  Given I am logged in as admin
  And the following config values are set as admin:
  | availabledepartments | academic,management,support | local_enrolstaff |
  | defaultdepartments   |  | local_enrolstaff |
  And I visit "/local/enrolstaff/manage.php"
  And I follow "New Staff rule"
  # Roles
  When I expand the "Available roles" autocomplete
  Then "Module leader" "autocomplete_suggestions" should exist
  And "Associate Lecturer" "autocomplete_suggestions" should exist
  And "QA Module leader" "autocomplete_suggestions" should exist
  And "QA Tutor" "autocomplete_suggestions" should exist
  And "Exclude Tutor" "autocomplete_suggestions" should not exist
  # Departments
  When I expand the "Departments" autocomplete
  Then "academic" "autocomplete_suggestions" should exist
  And "management" "autocomplete_suggestions" should exist
  And "manager" "autocomplete_suggestions" should not exist
  And "support" "autocomplete_suggestions" should exist
  And the following config values are set as admin:
  | availabledepartments | academic,manager,support | local_enrolstaff |
  And I reload the page
  When I expand the "Departments" autocomplete
  Then "academic" "autocomplete_suggestions" should exist
  And "management" "autocomplete_suggestions" should not exist
  And "manager" "autocomplete_suggestions" should exist
  And "support" "autocomplete_suggestions" should exist
  # Close the open autocomplete
  And I press the escape key
  # Cohorts
  When I expand the "Cohorts" autocomplete
  Then "Cohort 1" "autocomplete_suggestions" should exist
  And "Cohort 2" "autocomplete_suggestions" should exist
  And "Exclude cohort" "autocomplete_suggestions" should not exist
