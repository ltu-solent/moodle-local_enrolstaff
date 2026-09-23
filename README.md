# Staff Enrolment Self-Service

## Overview

The `local_enrolstaff` plugin enables staff to self-enrol onto Moodle courses using a configurable, rule-based workflow. It is designed for Solent-style staff access patterns where users can search for a course, choose a role, and have the enrolment processed according to eligibility rules and notifications.

## Features

### 1. Self-service course enrolment

- Staff users can search for modules or course codes from a Moodle page dedicated to self-enrolment.
- Users select a valid role for the course and are shown the applicable enrolment options.
- Enrolment eligibility is controlled by rules configured for the institution.

### 2. Rule-based access control

- Rules decide which users can enrol in which courses.
- Filters can include:
  - departments
  - cohorts
  - authentication methods
  - email and username patterns
  - module and course code prefixes
  - excluded module codes or names
  - notification and authorisation roles
- Multiple filter values are treated as logical OR within a field and AND across fields.

### 3. Role and course matching

- Users can only enrol in roles that are explicitly allowed by the system configuration.
- Course searches can be restricted to permitted module prefixes or excluded by shortname/fullname patterns.
- Matching rules help control which staff groups are eligible to access specific courses.

### 4. Notification and authorisation workflows

- When a user requests enrolment, the plugin can:
  - automatically enrol them and notify the relevant staff contact
  - require approval from a module leader or other user before enrolment is granted
  - send a request to Registry for follow-up
- Emails can be customised with templated content for subject lines and bodies.
- Authorisation links can expire after a configurable number of days.

### 5. Module leader / owner communication

- Enrolment requests can notify the course owner or designated role holders.
- Administrators can configure a fallback backup email when no owner is available.
- Notification settings can be enabled or disabled per plugin configuration.

### 6. Expiring enrolments

- Enrolments can be set to expire after a defined period.
- This helps keep access current and reduces stale staff memberships.
- Expiry time is calculated when the enrolment is created and can be updated later if needed.

### 7. Bulk unenrolment

- Users can remove themselves from courses in bulk from the self-service area.
- This supports staff who no longer need access to previous modules or teaching pages.

### 8. Admin configuration and rule management

- Administrators can manage enrolment rules through a dedicated admin interface.
- Configuration includes:
  - available departments
  - available roles
  - available cohorts
  - backup and registry notification emails
  - default validation patterns
  - default enrolment durations
  - default authentication methods
- Rules can be created, edited, enabled, disabled, or deleted from the management screen.

### 9. Search and validation safeguards

- Course search includes helpful validation and user guidance.
- Sensitive or excluded modules can be hidden from search results.
- The plugin emits clear warnings when no matching modules are found or when access is not permitted.

## Configuration

The plugin is configured through the Moodle admin area under the local plugins section.

### Admin settings

- Available departments: defines which user department values can be used in rules.
- Available roles: sets which roles are valid choices for self-enrolment.
- Available cohorts: limits the cohorts that may be used in rule matching.
- Available backup notify email addresses: fallback recipients if no one is available to approve or receive a notification.
- Available registry email addresses: registry contacts used for staff enrolment requests that require follow-up.
- Default settings: includes default enrolment expiry durations and default matching patterns for email, username, departments, and authentication method.
- Default authentication method: controls the default auth method used when building rules.

### Rule management

Administrators can create and manage staff enrolment rules from the dedicated rule management page. Each rule can define:

- allowed roles
- course or module code filters
- department, cohort, and user profile criteria
- notification behaviour
- authorisation requirements
- Registry request handling
- expiry duration
- enable/disable status

### Rule behaviour

Rules are evaluated against the user and the chosen course, and only the matching rule for the selected role is used. The plugin supports notification-only, authorisation-required, and Registry-request patterns depending on the configured rule action.

## Typical workflow

1. A staff user opens the self-service enrolment page.
2. They choose a valid staff role.
3. They search for the relevant module or course.
4. The rule engine checks whether the user matches the course access conditions.
5. The request is either:
   - completed automatically,
   - sent for authorisation,
   - or sent to Registry for processing.
6. Relevant staff are notified and the enrolment record is created or managed accordingly.

## Summary

`local_enrolstaff` is a staff self-enrolment and governance solution for Moodle. It combines course search, configurable eligibility rules, approval workflows, expiry controls, and notifications to support controlled staff access to teaching and operational modules.
