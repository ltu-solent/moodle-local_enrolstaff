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
 * Behat plugin generator
 *
 * @package    local_enrolstaff
 * @category   test
 * @copyright  2025 Southampton Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_local_enrolstaff_generator extends behat_generator_base {
    /**
     * Get all entities that can be created through this behat_generator
     *
     * @return array
     */
    protected function get_creatable_entities(): array {
        return [
            'rules' => [
                'singular' => 'rule',
                'datagenerator' => 'rule',
                'required' => ['title', 'roles'],
                'switchids' => [
                    'roles' => 'roleids',
                    'cohorts' => 'cohortids',
                ],
            ],
            'authorisations' => [
                'singular' => 'authorisation',
                'datagenerator' => 'authorisation',
                'required' => ['rule', 'requestor', 'authoriser', 'courseid'],
                'switchids' => [
                    'rule' => 'ruleid',
                    'requestor' => 'requestorid',
                    'authoriser' => 'authoriserid',
                    'course' => 'courseid',
                ],
            ],
        ];
    }

    /**
     * Map rule name to id
     *
     * @param string $rulename
     * @return integer
     */
    protected function get_rule_id(string $rulename): int {
        global $DB;
        return $DB->get_field('local_enrolstaff', 'id', ['title' => $rulename], MUST_EXIST);
    }

    /**
     * Maps cohort names to cohortids
     *
     * @param string $cohortnames
     * @return array
     */
    protected function get_cohorts_id(string $cohortnames): array {
        // Cf lti_coursecategories.
        global $DB;
        $cohortnames = explode(',', $cohortnames);
        $cohortids = [];
        foreach ($cohortnames as $cohortname) {
            $cohortid = $DB->get_field('cohort', 'id', ['name' => $cohortname], MUST_EXIST);
            $cohortids[] = $cohortid;
        }
        return $cohortids;
    }

    /**
     * Map role names to ids
     *
     * @param string $rolenames
     * @return array
     */
    protected function get_roles_id(string $rolenames): array {
        // Cf lti_coursecategories.
        global $DB;
        $rolenames = explode(',', $rolenames);
        $roleids = [];
        foreach ($rolenames as $rolename) {
            $roleid = $DB->get_field('role', 'id', ['shortname' => $rolename], MUST_EXIST);
            $roleids[] = $roleid;
        }
        return $roleids;
    }

    /**
     * Map authorisation identifier to id
     *
     * @param string $authorisationidentifier
     * @return integer
     */
    protected function get_authorisation_id(string $authorisationidentifier): int {
        global $DB;
        return $DB->get_field('local_enrolstaff_auth', 'id', ['id' => $authorisationidentifier], MUST_EXIST);
    }

    /**
     * Map requestor username to id
     *
     * @param string $requestorusername
     * @return integer
     */
    protected function get_requestor_id(string $requestorusername): int {
        global $DB;
        return $DB->get_field('user', 'id', ['username' => $requestorusername], MUST_EXIST);
    }

    /**
     * Map authoriser username to id
     *
     * @param string $authoriserusername
     * @return integer
     */
    protected function get_authoriser_id(string $authoriserusername): int {
        global $DB;
        return $DB->get_field('user', 'id', ['username' => $authoriserusername], MUST_EXIST);
    }
}
