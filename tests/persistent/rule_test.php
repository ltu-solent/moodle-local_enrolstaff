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

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../helper_trait.php');

use core\invalid_persistent_exception;
use local_enrolstaff\helper_trait;
use local_enrolstaff\local\api;
use stdClass;

/**
 * Tests for Staff Enrolment
 *
 * @package    local_enrolstaff
 * @category   test
 * @covers \local_enrolstaff\persistent\rule
 * @copyright  2025 Southampton Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class rule_test extends \advanced_testcase {
    use helper_trait;

    /**
     * Set up the required default settings, roles, courses etc
     *
     * @return void
     */
    private function setup_bitsnbobs() {
        $this->create_roles();
        $this->create_categories();
        $this->create_courses();
        $this->create_users();
        $this->set_configs();
    }

    /**
     * Test creating a rule
     */
    public function test_create_rule(): void {
        $this->resetAfterTest();
        $this->create_roles();
        $record = new stdClass();
        $record->title = 'Test Rule';
        $record->roleids = [
            $this->roles['tutor'],
            $this->roles['moduleleader'],
        ];
        $record->sendas = 'nonotification';
        $rule = new rule(0, $record);
        $rule->create();
        $this->assertIsInt($rule->get('id'));
        $this->assertGreaterThan(0, $rule->get('id'));
        $this->assertEquals('Test Rule', $rule->get('title'));
        $this->assertEquals($record->roleids, $rule->get('roleids'));
    }

    /**
     * The user filters require at least one field set
     *
     * @param array $record Rule fields
     * @param bool $expectedexception
     * @return void
     * @dataProvider atleastone_provider
     */
    public function test_atleastone($record, $expectedexception): void {
        $this->resetAfterTest();
        $this->create_roles();
        /** @var \local_enrolstaff_generator $esdg */
        $esdg = $this->getDataGenerator()->get_plugin_generator('local_enrolstaff');
        $input = [
            // Even if you set enabled=1, "check atleastone" will set enabled to 0, so validation will never fail in this condition.
            'enabled' => 1,
            'roles' => '',
            'email' => '',
            'username' => '',
            'departments' => '',
        ];

        if (isset($record['cohortids'])) {
            $this->create_cohorts();
            set_config('availablecohorts', implode(',', [
                $this->cohorts['cohort1']->id,
                $this->cohorts['cohort2']->id,
            ]), 'local_enrolstaff');
            $input['cohorts'] = implode(',', $record['cohorts']);
        }

        if (isset($record['departments'])) {
            $input['departments'] = $record['departments'];
        }

        try {
            $rule = $esdg->create_rule($input);
            if ($expectedexception) {
                $this->assertFalse($rule->get('enabled'));
            }
        } catch (\core\invalid_persistent_exception $e) {
            if (!$expectedexception) {
                $this->fail('Unexpected exception thrown: ' . $e->getMessage());
            }
        }
    }

    /**
     * At least one provider - not testing all variations here as validation checks are done elsewhere
     *
     * @return array
     */
    public static function atleastone_provider(): array {
        return [
            'no fields set - record disabled' => [
                'record' => [],
                'expectedexception' => true,
            ],
            'cohorts set' => [
                'record' => [
                    'cohorts' => ['cohort1', 'cohort2'],
                ],
                'expectedexception' => false,
            ],
            'departments set' => [
                'record' => [
                    'departments' => ['academic', 'management'],
                ],
                'expectedexception' => false,
            ],
        ];
    }

    /**
     * Test rule applies to course - universal exclusions are handled in user::course_search.
     *
     * @param stdClass $record
     * @param string $shortname
     * @param bool|string $expected
     * @return void
     * @dataProvider rule_applies_to_course_provider
     */
    public function test_rule_applies_to_course($record, $shortname, $expected): void {
        $this->resetAfterTest();
        $this->create_categories();
        $this->create_courses();
        /** @var \local_enrolstaff_generator $esdg */
        $esdg = $this->getDataGenerator()->get_plugin_generator('local_enrolstaff');
        $rule = $esdg->create_rule([
            'codes' => $record['codes'],
            'excodes' => $record['excodes'],
        ]);
        $course = $this->courses[$shortname];
        $this->assertEquals($expected, $rule->rule_applies_to_course($course));
    }

    /**
     * Rule applies to course provider
     *
     * @return array
     */
    public static function rule_applies_to_course_provider(): array {
        return [
            'no filters - applies to all' => [
                'record' => [
                    'codes' => [],
                    'excodes' => [],
                ],
                'shortname' => 'ABC101',
                'expected' => true,
            ],
            'matching code' => [
                'record' => [
                    'codes' => ['ABC101', 'ABC102'],
                    'excodes' => [],
                ],
                'shortname' => 'ABC101',
                'expected' => true,
            ],
            'non-matching code' => [
                'record' => [
                    'codes' => ['ABC102', 'ABC103'],
                    'excodes' => [],
                ],
                'shortname' => 'ABC101',
                'expected' => false,
            ],
            'excluded code present - contradictory, but excodes wins' => [
                'record' => [
                    'codes' => ['ABC101', 'ABC102'],
                    'excodes' => ['ABC101'],
                ],
                'shortname' => 'ABC101',
                'expected' => false,
            ],
            'excluded code absent' => [
                'record' => [
                    'codes' => ['ABC101', 'ABC102'],
                    'excodes' => ['ABC103'],
                ],
                'shortname' => 'ABC101',
                'expected' => true,
            ],
            'only excluded code present' => [
                'record' => [
                    'codes' => [],
                    'excodes' => ['ABC101'],
                ],
                'shortname' => 'ABC101',
                'expected' => false,
            ],
            'only excluded code absent' => [
                'record' => [
                    'codes' => [],
                    'excodes' => ['ABC102'],
                ],
                'shortname' => 'ABC101',
                'expected' => true,
            ],
        ];
    }

    /**
     * Test auths validation
     *
     * @return void
     */
    public function test_validate_auths(): void {
        $this->resetAfterTest();
        $this->create_roles();
        $fieldname = get_string('field:auths', 'local_enrolstaff');
        /** @var \local_enrolstaff_generator $esdg */
        $esdg = $this->getDataGenerator()->get_plugin_generator('local_enrolstaff');
        try {
            $esdg->create_rule([
                'auths' => ['manual', 'invalidauth'],
            ]);
            $this->fail('Expected exception not thrown');
        } catch (invalid_persistent_exception $e) {
            $this->assertStringContainsString(
                get_string('invalidfield', 'local_enrolstaff', ['field' => $fieldname, 'value' => 'invalidauth']),
                $e->getMessage()
            );
        }

        // Valid auths.
        $auths = ['manual', 'email'];
        try {
            $rule = $esdg->create_rule([
                'auths' => $auths,
            ]);
            $this->assertEquals($auths, $rule->get('auths'));
        } catch (invalid_persistent_exception $e) {
            $this->fail('Exception thrown unexpectedly: ' . $e->getMessage());
        }
    }

    /**
     * Test cohorts validation
     *
     * @return void
     */
    public function test_validate_cohorts(): void {
        $this->resetAfterTest();
        $this->create_cohorts();
        set_config('availablecohorts', implode(',', [
            $this->cohorts['cohort1']->id,
            $this->cohorts['cohort2']->id,
        ]), 'local_enrolstaff');
        $fieldname = get_string('field:cohortids', 'local_enrolstaff');

        /** @var \local_enrolstaff_generator $esdg */
        $esdg = $this->getDataGenerator()->get_plugin_generator('local_enrolstaff');
        $cohorts = [
            $this->cohorts['cohort1']->id,
            9999, // Invalid cohort ID.
        ];
        try {
            $esdg->create_rule([
                'cohortids' => $cohorts,
            ]);
            $this->fail('Expected exception not thrown');
        } catch (invalid_persistent_exception $e) {
            $this->assertStringContainsString(
                get_string('invalidfield', 'local_enrolstaff', ['field' => $fieldname, 'value' => '9999']),
                $e->getMessage()
            );
        }

        // Include a cohort that exists, but not in available cohorts.
        $cohorts = [
            $this->cohorts['cohort1']->id,
            $this->cohorts['cohort3']->id,
        ];
        try {
            $esdg->create_rule([
                'cohortids' => $cohorts,
            ]);
            $this->fail('Expected exception not thrown');
        } catch (invalid_persistent_exception $e) {
            $this->assertStringContainsString(
                get_string('invalidfield', 'local_enrolstaff', ['field' => $fieldname, 'value' => $this->cohorts['cohort3']->id]),
                $e->getMessage()
            );
        }

        // Valid cohorts.
        $cohorts = [
            $this->cohorts['cohort1']->id,
            $this->cohorts['cohort2']->id,
        ];
        $rule = $esdg->create_rule([
            'cohortids' => $cohorts,
        ]);
        $this->assertEquals($cohorts, $rule->get('cohortids'));
    }

    /**
     * Test departments validation
     *
     * @return void
     */
    public function test_validate_departments(): void {
        $this->resetAfterTest();
        set_config('availabledepartments', 'academic,management,support', 'local_enrolstaff');
        /** @var \local_enrolstaff_generator $esdg */
        $esdg = $this->getDataGenerator()->get_plugin_generator('local_enrolstaff');
        $fieldname = get_string('field:departments', 'local_enrolstaff');
        // Include an invalid department ID.
        $departments = [
            'academic',
            'INVALIDDEPT',
        ];

        try {
            $esdg->create_rule([
                'departments' => $departments,
            ]);
            $this->fail('Expected exception not thrown');
        } catch (invalid_persistent_exception $e) {
            $this->assertStringContainsString(
                get_string('invalidfield', 'local_enrolstaff', ['field' => $fieldname, 'value' => 'INVALIDDEPT']),
                $e->getMessage()
            );
        }

        // Valid departments.
        $departments = [
            'academic',
            'management',
        ];
        $rule = $esdg->create_rule([
            'departments' => $departments,
        ]);
        $this->assertEquals($departments, $rule->get('departments'));
    }

    /**
     * Test duration validation
     *
     * @return void
     */
    public function test_validate_duration(): void {
        $this->resetAfterTest();
        /** @var \local_enrolstaff_generator $esdg */
        $esdg = $this->getDataGenerator()->get_plugin_generator('local_enrolstaff');
        $fieldname = get_string('field:duration', 'local_enrolstaff');
        // Invalid duration - not in selection.
        $duration = 181;
        try {
            $esdg->create_rule(['duration' => $duration]);
            $this->fail('Expected exception not thrown');
        } catch (invalid_persistent_exception $e) {
            $this->assertStringContainsString(
                get_string('invalidfield', 'local_enrolstaff', ['field' => $fieldname, 'value' => 181]),
                $e->getMessage()
            );
        }

        // Valid duration.
        $duration = 182;
        $rule = $esdg->create_rule(['duration' => $duration]);
        $this->assertEquals($duration, $rule->get('duration'));
    }

    /**
     * Test regex patterns validation
     *
     * Although this is specifically testing email regex patterns,
     * the same validation is used for other regex fields (i.e.). exemail, username & exusername).
     * @param string $value The regex pattern to test
     * @param mixed $expected True if valid, otherwise the error code suffix
     * @dataProvider validate_regex_provider
     */
    public function test_validate_regex($value, $expected): void {
        $this->resetAfterTest();
        /** @var \local_enrolstaff_generator $esdg */
        $esdg = $this->getDataGenerator()->get_plugin_generator('local_enrolstaff');
        try {
            $rule = $esdg->create_rule(['email' => $value]);
        } catch (\core\invalid_persistent_exception $e) {
            if ($expected === true) {
                $this->fail('Unexpected exception thrown: ' . $e->getMessage());
            } else {
                $this->assertStringContainsString(get_string('regexerrorcode' . $expected, 'local_enrolstaff'), $e->getMessage());
                return;
            }
        }

        $this->assertIsInt($rule->get('id'));
        $this->assertGreaterThan(0, $rule->get('id'));
        $this->assertEquals($value, $rule->get('email'));
    }

    /**
     * Validate regex provider
     *
     * @return array
     */
    public static function validate_regex_provider(): array {
        return [
            'plain text' => [
                'value' => "example.com",
                'expected' => true,
            ],
            'simple regex' => [
                'value' => ".*@example\.com",
                'expected' => true,
            ],
            'complex regex' => [
                'value' => "^(.*@example\.com|.*@test\.com|.*@another\.com)$",
                'expected' => true,
            ],
            'invalid regex' => [
                // No closing bracket.
                'value' => "^(.*@example\.com|.*@test\.com|.*@another\.com$",
                'expected' => PREG_INTERNAL_ERROR,
            ],
            'backtrack limit error' => [
                // This pattern causes catastrophic backtracking.
                'value' => "^(a+)+b$",
                'expected' => PREG_BACKTRACK_LIMIT_ERROR,
            ],
        ];
    }

    /**
     * Test roleids validation with availableroles
     *
     * @return void
     */
    public function test_validate_roleids(): void {
        $this->resetAfterTest();
        $this->create_roles();
        $availableroleids = [
            $this->roles['tutor'],
            $this->roles['moduleleader'],
            $this->roles['contentretrieval'],
        ];
        set_config('availableroles', implode(',', $availableroleids), 'local_enrolstaff');
        $fieldname = get_string('field:roleids', 'local_enrolstaff');
        /** @var \local_enrolstaff_generator $esdg */
        $esdg = $this->getDataGenerator()->get_plugin_generator('local_enrolstaff');

        // Include an invalid role ID.
        $roleids = [
            $this->roles['tutor'],
            5,
        ];

        try {
            $esdg->create_rule([
                'roleids' => $roleids,
            ]);
            $this->fail('Expected exception not thrown');
        } catch (invalid_persistent_exception $e) {
            $this->assertStringContainsString(
                get_string('invalidfield', 'local_enrolstaff', [
                    'field' => $fieldname,
                    'value' => 5,
                ]),
                $e->getMessage()
            );
        }
        // At least one roleids required.
        $roleids = [];
        try {
            $esdg->create_rule([
                'roleids' => $roleids,
            ]);
            $this->fail('Expected exception not thrown');
        } catch (invalid_persistent_exception $e) {
            $this->assertStringContainsString(
                get_string('invalidfield', 'local_enrolstaff', [
                    'field' => $fieldname,
                    'value' => '',
                ]),
                $e->getMessage()
            );
        }
        // Valid roles.
        $roleids = [
            $this->roles['tutor'],
            $this->roles['moduleleader'],
        ];
        $rule = $esdg->create_rule([
            'roleids' => $roleids,
        ]);
        $this->assertEquals($roleids, $rule->get('roleids'));
    }
}
