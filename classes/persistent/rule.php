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

namespace local_enrolstaff\persistent;

use core\context\course;
use core\lang_string;
use core\output\html_writer;
use core\persistent;
use core\user;
use local_enrolstaff\local\api;

/**
 * Class rule
 *
 * @package    local_enrolstaff
 * @copyright  2025 Southampton Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rule extends persistent {
    /**
     * Table name for enrolstaff rules
     */
    const TABLE = 'local_enrolstaff';

    /**
     * Filter validation must require one of
     */
    const ONEOF = ['auths', 'cohortids', 'departments', 'email', 'exemail', 'exusername', 'institution', 'username'];

    /**
     * Return the definition of the properties for this model.
     *
     * @return array
     */
    protected static function define_properties() {
        return [
            'title' => [
                'type' => PARAM_TEXT,
            ],
            'email' => [
                'type' => PARAM_TEXT,
                'default' => function () {
                    return get_config('local_enrolstaff', 'defaultemailpattern');
                },
            ],
            'username' => [
                'type' => PARAM_TEXT,
                'default' => function () {
                    return get_config('local_enrolstaff', 'defaultusernamepattern');
                },
            ],
            'exemail' => [
                'type' => PARAM_TEXT,
                'default' => function () {
                    return get_config('local_enrolstaff', 'defaultexemailpattern');
                },
            ],
            'exusername' => [
                'type' => PARAM_TEXT,
                'default' => function () {
                    return get_config('local_enrolstaff', 'defaultexusernamepattern');
                },
            ],
            'departments' => [
                'type' => PARAM_TAGLIST,
                'default' => function () {
                    return get_config('local_enrolstaff', 'defaultdepartments');
                },
                'multiple' => true,
                'null' => NULL_ALLOWED,
            ],
            'cohortids' => [
                'type' => PARAM_SEQUENCE,
                'default' => '',
            ],
            'institution' => [
                'type' => PARAM_TEXT,
                'default' => '',
            ],
            'auths' => [
                'type' => PARAM_TAGLIST,
                'default' => function () {
                    return get_config('local_enrolstaff', 'defaultauths');
                },
                'multiple' => true,
            ],
            'roleids' => [
                'type' => PARAM_SEQUENCE,
            ],
            'duration' => [
                'type' => PARAM_INT,
                'default' => function () {
                    return get_config('local_enrolstaff', 'defaultexpireenrolments');
                },
                'choice' => api::get_duration_menu(),
            ],
            'codes' => [
                'type' => PARAM_TAGLIST,
                'default' => '',
            ],
            'excodes' => [
                'type' => PARAM_TAGLIST,
                'default' => '',
            ],
            'sendas' => [
                'type' => PARAM_ALPHA,
                'default' => 'notification',
                'choice' => api::get_sendas_menu(),
            ],
            'notify' => [
                'type' => PARAM_TAGLIST,
                'default' => function () {
                    return get_config('local_enrolstaff', 'defaultnotify');
                },
            ],
            'enabled' => [
                'type' => PARAM_BOOL,
                'default' => false,
            ],
        ];
    }

    #[\Override]
    protected function before_validate() {
        // The Persistent validator doesn't like arrays, so transform into csv strings before validation.
        if (is_array($this->raw_get('auths'))) {
            $this->set('auths', implode(',', $this->raw_get('auths')));
        }
        if (is_array($this->raw_get('codes'))) {
            // Autocomplete seems to add a comma which messes with the implode, so trim first.
            $value = $this->raw_get('codes');
            $this->set('codes', trim(implode(',', $value), "\s,"));
        }
        if (is_array($this->raw_get('cohortids'))) {
            $this->set('cohortids', implode(',', $this->raw_get('cohortids')));
        }
        if (is_array($this->raw_get('departments'))) {
            $this->set('departments', implode(',', $this->raw_get('departments')));
        }
        if (is_array($this->raw_get('excodes'))) {
            $value = $this->raw_get('excodes');
            $this->set('excodes', trim(implode(',', $value), "\s,"));
        }
        if (is_array($this->raw_get('notify'))) {
            $value = $this->raw_get('notify');
            $items = [];
            foreach ($value as $item) {
                if (strpos($item, ':') === false) {
                    // Assume email if no prefix.
                    $items['e:' . $item] = $item;
                } else {
                    $items[$item] = $item;
                }
            }
            $items = trim(implode(',', $items), "\s,");
            $this->set('notify', $items);
        }
        if (is_array($this->raw_get('roleids'))) {
            $this->set('roleids', implode(',', $this->raw_get('roleids')));
        }
    }

    /**
     * Validate email pattern.
     *
     * Will not check it looks like an email, only that it's a working regular expression.
     *
     * @param string $selected
     * @return bool|lang_string
     */
    protected function validate_email($selected) {
        return $this->validate_regularexpression($selected);
    }

    /**
     * Validate username pattern
     *
     * @param string $selected
     * @return bool|lang_string
     */
    protected function validate_username($selected) {
        return $this->validate_regularexpression($selected);
    }

    /**
     * Validate exclude email pattern
     *
     * @param string $selected
     * @return bool|lang_string
     */
    protected function validate_exemail($selected) {
        return $this->validate_regularexpression($selected);
    }

    /**
     * Validate exclude username pattern
     *
     * @param string $selected
     * @return bool|lang_string
     */
    protected function validate_exusername($selected) {
        return $this->validate_regularexpression($selected);
    }

    /**
     * Valid authentication methods
     *
     * @param string $selected
     * @return bool|lang_string
     */
    protected function validate_auths($selected) {
        $validauths = array_keys(api::get_auth_menu());
        return $this->validate_field_array($selected, 'auths', $validauths);
    }

    /**
     * Validate cohort ids
     *
     * @param string $selected
     * @return bool|lang_string
     */
    protected function validate_cohortids($selected) {
        global $DB;
        if (empty($selected)) {
            return true;
        }
        $validcohorts = array_keys(api::get_cohorts_menu());
        $cohortids = api::clean_csv($selected);
        $fieldname = get_string('field:cohortids', 'local_enrolstaff');
        foreach ($cohortids as $cohortid) {
            if (!in_array($cohortid, $validcohorts)) {
                return new lang_string('invalidfield', 'local_enrolstaff', ['field' => $fieldname, 'value' => $cohortid]);
            }
            if (!$DB->record_exists('cohort', ['id' => $cohortid])) {
                return new lang_string('invalidfield', 'local_enrolstaff', ['field' => $fieldname, 'value' => $cohortid]);
            }
        }
        return true;
    }

    /**
     * Validate departments
     *
     * @param string $selected
     * @return bool|lang_string
     */
    protected function validate_departments($selected) {
        $validdepts = array_keys(api::get_depts_menu());
        return $this->validate_field_array($selected, 'departments', $validdepts);
    }

    /**
     * Validate duration
     *
     * @param string $selected
     * @return bool|lang_string
     */
    protected function validate_duration($selected) {
        $validduration = array_keys(api::get_duration_menu());
        return $this->validate_field_array($selected, 'duration', $validduration, false);
    }

    /**
     * Validate notify
     *
     * @param string $selected
     * @return bool|lang_string
     */
    protected function validate_notify($selected) {
        global $DB;
        $items = api::clean_csv($selected);
        $validroleids = array_keys(api::get_notify_menu());
        if (in_array($this->get('sendas'), ['notification', 'authorisation', 'registryrequest']) && empty($items)) {
            return new lang_string('invalidnotifysendas', 'local_enrolstaff', get_string('field:notify', 'local_enrolstaff'));
        }
        foreach ($items as $item) {
            if (strpos($item, ':') === false) {
                // Assume email if no prefix.
                $item = 'e:' . $item;
            }
            [$type, $value] = explode(':', $item);
            $invalidparams = [
                'field' => get_string('field:notify', 'local_enrolstaff'),
                'value' => $item,
            ];
            if ($type == 'r') {
                if (!in_array($item, $validroleids)) {
                    return new lang_string('invalidfield', 'local_enrolstaff', $invalidparams);
                }
            } else if ($type === 'e') {
                $isemail = filter_var($value, FILTER_VALIDATE_EMAIL);
                if (!$isemail) {
                    return new lang_string('invalidfield', 'local_enrolstaff', $invalidparams);
                }
                // If the sendas requires an email, this email must exist in Moodle as a user.
                // We can't check permissions to authorise for this user here because we don't know the target course context.
                // I would rather not send an email that can't be acted upon.
                if (!$DB->record_exists('user', ['email' => $value])) {
                    return new lang_string('invalidfield', 'local_enrolstaff', $invalidparams);
                }
            } else {
                return new lang_string('invalidfield', 'local_enrolstaff', $invalidparams);
            }
        }
        return true;
    }

    /**
     * Validate roleids
     *
     * @param string $selected
     * @return bool|string
     */
    protected function validate_roleids($selected) {
        $validroleids = array_keys(api::get_roles_menu());
        return $this->validate_field_array($selected, 'roleids', $validroleids, false);
    }

    /**
     * Generic validator for arrays, requires a lang string "field:fieldname"
     *
     * @param array|string $items
     * @param string $fieldname
     * @param array $validitems
     * @param bool $isoptional
     * @return bool|lang_string
     */
    private function validate_field_array($items, $fieldname, array $validitems = [], $isoptional = true) {
        if (empty($validitems)) {
            // Nothing to validate.
            return true;
        }
        // If you explode an empty string you'll get an empty single array item.
        // So if this field is optional, return early.
        if (empty($items) && $isoptional) {
            return true;
        }
        if (!is_array($items)) {
            $items = explode(',', $items);
        }
        if (empty($items) && $isoptional) {
            return true;
        }
        $fieldstring = get_string('field:' . $fieldname, 'local_enrolstaff');
        if (empty($items) && !$isoptional) {
            return new lang_string('invalidrequired', 'local_enrolstaff', $fieldstring);
        }
        foreach ($items as $item) {
            if (!in_array($item, $validitems)) {
                return new lang_string('invalidfield', 'local_enrolstaff', ['field' => $fieldstring, 'value' => $item]);
            }
        }
        return true;
    }

    /**
     * Validate regular expression
     *
     * @param string $regex
     * @return bool|lang_string
     */
    private function validate_regularexpression(string $regex) {
        $invalidregex = false;
        if (empty($regex)) {
            // Assuming the "required" property will throw an error if empty.
            return true;
        }
        $delimited = '/' . $regex . '/';
        $invalidregex = (@preg_match($delimited, '') === false);
        if ($invalidregex) {
            $errorcode = preg_last_error();
            $errormessage = get_string('regexerrorcode' . $errorcode, 'local_enrolstaff');
            // PREG_INTERNAL_ERROR: 1
            // PREG_BACKTRACK_LIMIT_ERROR: 2
            // PREG_RECURSION_LIMIT_ERROR: 3
            // PREG_BAD_UTF8_ERROR: 4
            // PREG_BAD_UTF8_OFFSET_ERROR: 5.

            return new lang_string('validateregexerror', 'local_enrolstaff', [
                'errorcode' => $errorcode,
                'errormessage' => $errormessage,
            ]);
        }
        return true;
    }

    /**
     * Override the default getter for auths, return an array
     *
     * @return array
     */
    protected function get_auths(): array {
        return api::clean_csv($this->raw_get('auths'));
    }

    /**
     * Override the default getter for codes, return an array
     *
     * @return array
     */
    protected function get_codes(): array {
        return api::clean_csv($this->raw_get('codes'));
    }

    /**
     * Override the default getter for excodes, return an array
     *
     * @return array
     */
    protected function get_excodes(): array {
        return api::clean_csv($this->raw_get('excodes'));
    }

    /**
     * Override the default getter for departments, return an array
     *
     * @return array
     */
    protected function get_departments(): array {
        return api::clean_csv($this->raw_get('departments'));
    }

    /**
     * Override the default getter for cohortids, return an array
     *
     * @return array
     */
    protected function get_cohortids(): array {
        return api::clean_csv($this->raw_get('cohortids'));
    }

    /**
     * Override the default getter for notify, return an array
     *
     * @return array
     */
    protected function get_notify(): array {
        return api::clean_csv($this->raw_get('notify'));
    }

    /**
     * Override the default getter for roleids, return an array
     *
     * @return array
     */
    protected function get_roleids(): array {
        return api::clean_csv($this->raw_get('roleids'));
    }

    #[\Override]
    protected function after_create() {
        $this->check_atleastone();
    }

    #[\Override]
    protected function after_update($result) {
        $this->check_atleastone();
    }

    #[\Override]
    protected function before_create() {
        $value = $this->raw_get('notify');
        $value = api::clean_csv($value);
        $items = [];
        foreach ($value as $item) {
            if (strpos($item, ':') === false) {
                // Assume email if no prefix.
                $items['e:' . $item] = $item;
            } else {
                $items[$item] = $item;
            }
        }
        $items = trim(implode(',', $items), "\s,");
        $this->set('notify', $items);
    }

    #[\Override]
    protected function before_update() {
        $this->before_create();
    }

    /**
     * When saving, if there are no defined filters, ensure rule is disabled.
     *
     * @return void
     */
    private function check_atleastone() {
        if ($this->get('enabled') == 0) {
            return;
        }
        $chosen = array_filter(static::ONEOF, function ($item) {
            $value = $this->get($item);
            return !empty($value);
        });
        if (count($chosen) == 0) {
            $this->set('enabled', 0);
            $this->save();
        }
    }

    /**
     * Does this rule apply to this course for matching course codes
     *
     * @param stdClass $course
     * @return bool
     */
    public function rule_applies_to_course($course): bool {
        $codes = $this->get('codes');
        $excodes = $this->get('excodes');
        // If there are no codes to match against, it will match automatically.
        $codecount = count($codes);
        $excodecount = count($excodes);
        $isamatch = true;
        if ($codecount == 0 && $excodecount == 0) {
            return $isamatch;
        }

        $shortname = $course->shortname;

        if (count($codes) > 0) {
            // We need a positive match.
            $isamatch = false;
            foreach ($codes as $code) {
                if (strpos($shortname, $code) !== false) {
                    $isamatch = true;
                }
            }
        }
        if (count($excodes) > 0) {
            foreach ($excodes as $excode) {
                if (strpos($shortname, $excode) !== false) {
                    $isamatch = false;
                }
            }
        }
        return $isamatch;
    }

    /**
     * Get SQL to filter users based on this rule
     *
     * @return array [$select, $from, $params, $conditions]
     */
    public function get_userfilter_sql(): array {
        global $DB;
        $select = 'u.id, u.username, u.email, u.firstname, u.lastname, u.department, u.institution, u.auth,' .
            ' u.firstnamephonetic, u.lastnamephonetic, u.middlename, u.alternatename';
        $from = '{user} u';
        $params = [];
        $conditions = [
            'u.suspended = 0',
            'u.deleted = 0',
        ];
        // Build SQL conditions based on the rule filters.
        if (!empty($this->get('email'))) {
            $conditions[] = 'u.email ' . $DB->sql_regex() . ' :emailpattern';
            $params['emailpattern'] = $this->get('email');
        }
        if (!empty($this->get('username'))) {
            $conditions[] = 'u.username ' . $DB->sql_regex() . ' :usernamepattern';
            $params['usernamepattern'] = $this->get('username');
        }
        if (!empty($this->get('exemail'))) {
            $conditions[] = 'u.email ' . $DB->sql_regex(false) . ' :exemailpattern';
            $params['exemailpattern'] = $this->get('exemail');
        }
        if (!empty($this->get('exusername'))) {
            $conditions[] = 'u.username ' . $DB->sql_regex(false) . ' :exusernamepattern';
            $params['exusernamepattern'] = $this->get('exusername');
        }
        if (!empty($this->get('departments'))) {
            $departments = $this->get('departments');
            [$deptsql, $deptparams] = api::get_sql_for_field_in_array('u.department', $departments, 'dept');
            $conditions[] = $deptsql;
            $params = array_merge($params, $deptparams);
        }
        if (!empty($this->get('institution'))) {
            $conditions[] = 'u.institution = :institution';
            $params['institution'] = $this->get('institution');
        }
        if (!empty($this->get('auths'))) {
            $auths = $this->get('auths');
            [$authsql, $authparams] = api::get_sql_for_field_in_array('u.auth', $auths, 'auth');
            $conditions[] = $authsql;
            $params = array_merge($params, $authparams);
        }
        if (!empty($this->get('cohortids'))) {
            $cohortids = $this->get('cohortids');
            [$cohsql, $cohparams] = api::get_sql_for_cohort_membership('u.id', $cohortids, 'coh');
            $conditions[] = $cohsql;
            $params = array_merge($params, $cohparams);
        }
        return [$select, $from, $params, $conditions];
    }

    /**
     * Print filters as human readable list
     *
     * @return string
     */
    public function print_filters(): string {
        global $DB;
        // Matches a user who:
        // * Has an "email" address that looks like '{$email}' OR Has a "username" that looks like '{$username}'
        // * Doesn't have an "email" address that looks like '{$exemail}'
        // OR Doesn't have a "username" that looks like '{$exusername}'
        // * Is in one of: "{$departments}" "departments"
        // * Is in one of: "{$cohorts}" "cohorts"
        // * Is in '{$institution}' "institution"
        // * Has one of "{$auths}" "authentication methods".
        $filterlist = [];
        $emailusername = [];
        if (!empty($this->get('email'))) {
            $emailusername[] = get_string('hasanemaillike', 'local_enrolstaff', $this->get('email'));
        }
        if (!empty($this->get('username'))) {
            $emailusername[] = get_string('hasausernamelike', 'local_enrolstaff', $this->get('username'));
        }
        if (!empty($emailusername)) {
            $filterlist[] = join(get_string('or', 'local_enrolstaff'), $emailusername);
        }

        $exemailusername = [];
        if (!empty($this->get('exemail'))) {
            $exemailusername[] = get_string('nothasanemaillike', 'local_enrolstaff', $this->get('exemail'));
        }
        if (!empty($this->get('exusername'))) {
            $exemailusername[] = get_string('nothasausernamelike', 'local_enrolstaff', $this->get('exusername'));
        }
        if (!empty($exemailusername)) {
            $filterlist[] = join(get_string('or', 'local_enrolstaff'), $exemailusername);
        }
        if (!empty($this->get('departments'))) {
            $depts = join(', ', $this->get('departments'));
            $filterlist[] = get_string(
                'isoneof',
                'local_enrolstaff',
                [
                    'list' => $depts,
                    'field' => get_string('field:departments', 'local_enrolstaff'),
                ]
            );
        }
        if (!empty($this->get('cohortids'))) {
            $cohortids = $this->get('cohortids');
            [$insql, $inparams] = $DB->get_in_or_equal($cohortids, SQL_PARAMS_NAMED);
            $cohorts = $DB->get_fieldset_select('cohort', 'name', "id {$insql}", $inparams);
            $list = join(', ', $cohorts);
            $filterlist[] = get_string(
                'isoneof',
                'local_enrolstaff',
                [
                    'list' => $list,
                    'field' => get_string('field:cohortids', 'local_enrolstaff'),
                ]
            );
        }
        if (!empty($this->get('institution'))) {
            $filterlist[] = get_string(
                'hasfield',
                'local_enrolstaff',
                [
                    'item' => $this->get('institution'),
                    'field' => get_string('field:institution', 'local_enrolstaff'),
                ]
            );
        }
        if (!empty($this->get('auths'))) {
            $auths = join(', ', $this->get('auths'));
            $filterlist[] = get_string(
                'hasoneof',
                'local_enrolstaff',
                [
                    'list' => $auths,
                    'field' => get_string('field:authenticationmethods', 'local_enrolstaff'),
                ]
            );
        }

        $html = get_string('matchesauser', 'local_enrolstaff');
        $html .= html_writer::alist($filterlist);

        return $html;
    }

    /**
     * Get contacts for notifications based on rule settings
     *
     * @param stdClass $course
     * @param stdClass $role We may do something with the role here in future.
     * @return array (email, name)[]
     */
    public function get_contacts($course, $role): array {
        global $DB;
        $contacts = [];
        // Maybe no notification is required.
        if ($this->get('sendas') == 'nonotification') {
            return $contacts;
        }
        $allnotify = $this->get('notify');
        $notification = in_array($this->get('sendas'), ['notification', 'registryrequest']);
        // Get course managers.
        $roleids = [];
        foreach ($allnotify as $item) {
            [$type, $value] = explode(':', $item);
            // Authorisation only works if the authoriser is already a course manager.
            if ($type === 'r') {
                $roleids[] = $value;
            } else {
                // Only send direct emails if notification is required.
                if ($notification) {
                    // Try to find user account for this email.
                    $user = user::get_user_by_email($value);
                    if ($user) {
                        $contacts[] = $user;
                    }
                }
            }
        }
        if (!empty($roleids)) {
            $roleids = array_unique($roleids);
        } else {
            // No roles to get managers for.
            return $contacts;
        }
        $managers = api::get_users_with_roles($course->id, $roleids);
        foreach ($managers as $manager) {
            $contacts[] = $manager;
        }
        return $contacts;
    }

    /**
     * Enrol user using rule specifications
     *
     * @param int $userid
     * @param int $courseid
     * @param int $roleid
     * @return void
     */
    public function enrol_user($userid, $courseid, $roleid) {
        global $DB;
        $plugin = enrol_get_plugin('manual');
        $instance = $DB->get_record('enrol', ['courseid' => $courseid, 'enrol' => 'manual']);
        if (!$instance) {
            $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
            $fields = [
                'status'          => '0',
                'roleid'          => '5',
                'enrolperiod'     => '0',
                'expirynotify'    => '0',
                'notifyall'       => '0',
                'expirythreshold' => '86400',
            ];
            $instance = $plugin->add_instance($course, $fields);
        }
        $expiry = $this->get('duration');
        if ($expiry > 0) {
            $expiry = time() + (DAYSECS * $expiry);
        }
        $instance = $DB->get_record('enrol', ['courseid' => $courseid, 'enrol' => 'manual']);
        // Check for existing enrolment with this role.
        $coursecontext = course::instance($courseid);
        // Don't really care which enrolment method, just that the user has the role in this course.
        $roleassignments = get_user_roles($coursecontext, $userid, true);
        foreach ($roleassignments as $ra) {
            if ($ra->roleid == $roleid) {
                return;
            }
        }
        $plugin->enrol_user($instance, $userid, $roleid, time(), $expiry, null, null);
    }
}
