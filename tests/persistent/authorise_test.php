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

use local_enrolstaff\helper_trait;

/**
 * Tests for Staff Enrolment
 *
 * @package    local_enrolstaff
 * @category   test
 * @covers \local_enrolstaff\persistent\authorise
 * @copyright  2025 Southampton Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class authorise_test extends \advanced_testcase {
    use helper_trait;

    /**
     * Test authorisation record creation
     */
    public function test_authorise(): void {
        $this->resetAfterTest();
        $this->create_roles();
        set_config('availablenotifyroles', implode(',', [
            $this->roles['moduleleader'],
        ]), 'local_enrolstaff');
        set_config('availableroles', implode(',', [
            $this->roles['tutor'],
            $this->roles['moduleleader'],
            $this->roles['contentretrieval'],
        ]), 'local_enrolstaff');
        $requestor = $this->getDataGenerator()->create_user();
        $authoriser = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $this->getDataGenerator()->enrol_user($authoriser->id, $course->id, $this->roles['moduleleader']);
        /** @var local_enrolstaff_generator $esdg */
        $esdg = $this->getDataGenerator()->get_plugin_generator('local_enrolstaff');
        $rule = $esdg->create_rule([
            'roleids' => [$this->roles['tutor']],
            'notify' => ['r:' . $this->roles['moduleleader']],
            'enabled' => 1,
            'duration' => 182, // Days.
            'sendas' => 'authorisation',
        ]);
        set_config('enrolmentauthorisationvalidity', 7, 'local_enrolstaff');
        $validitydays = get_config('local_enrolstaff', 'enrolmentauthorisationvalidity');
        $validuntil = time() + ($validitydays * DAYSECS);
        $authorisation = $esdg->create_authorisation([
            'ruleid' => $rule->get('id'),
            'requestorid' => $requestor->id,
            'roleid' => $this->roles['tutor'],
            'courseid' => $course->id,
            'authoriserid' => $authoriser->id,
            'validuntil' => $validuntil,
        ]);

        $this->assertEquals($rule->get('id'), $authorisation->get('ruleid'));
        $urlparams = $authorisation->get_url_params();
        $this->assertEquals($authorisation->get('courseid'), $urlparams['courseid']);
        $this->assertEquals($authorisation->get('token'), $urlparams['token']);
        $this->assertEquals($authorisation->get_signature(), $urlparams['signature']);
    }

    /**
     * Check request token
     *
     * @return void
     */
    public function test_check_request_token(): void {
        $this->resetAfterTest();
        $this->create_roles();
        set_config('availablenotifyroles', implode(',', [
            $this->roles['moduleleader'],
        ]), 'local_enrolstaff');
        set_config('availableroles', implode(',', [
            $this->roles['tutor'],
            $this->roles['moduleleader'],
            $this->roles['contentretrieval'],
        ]), 'local_enrolstaff');
        $requestor = $this->getDataGenerator()->create_user();
        $authoriser = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        /** @var local_enrolstaff_generator $esdg */
        $esdg = $this->getDataGenerator()->get_plugin_generator('local_enrolstaff');
        $rule = $esdg->create_rule([
            'roleids' => [$this->roles['tutor']],
            'notify' => ['r:' . $this->roles['moduleleader']],
            'enabled' => 1,
            'duration' => 182, // Days.
            'sendas' => 'authorisation',
        ]);

        set_config('enrolmentauthorisationvalidity', 7, 'local_enrolstaff');
        $validitydays = get_config('local_enrolstaff', 'enrolmentauthorisationvalidity');
        $validuntil = time() + ($validitydays * DAYSECS);
        $authorisation = $esdg->create_authorisation([
            'ruleid' => $rule->get('id'),
            'requestorid' => $requestor->id,
            'roleid' => $this->roles['tutor'],
            'courseid' => $course->id,
            'authoriserid' => $authoriser->id,
            'validuntil' => $validuntil,
        ]);

        // Not logged in as authoriser.
        $this->assertEquals(
            authorise::AUTHORISERID_INVALID,
            $authorisation->check_request_token(
                $authorisation->get('token'),
                $authorisation->get_signature()
            )
        );

        $this->setUser($authoriser);
        // Valid token and signature.
        $this->assertTrue($authorisation->check_request_token(
            $authorisation->get('token'),
            $authorisation->get_signature()
        ));

        // Invalid token.
        $this->assertEquals(
            authorise::TOKEN_INVALID,
            $authorisation->check_request_token(
                'invalidtokenvalue',
                $authorisation->get_signature()
            )
        );

        // Invalid signature.
        $this->assertEquals(
            authorise::SIGNATURE_INVALID,
            $authorisation->check_request_token(
                $authorisation->get('token'),
                'invalidsignaturevalue'
            )
        );

        // Expired validity.
        $authorisation->set('validuntil', time() - 3600);
        $authorisation->save();
        $this->assertEquals(
            authorise::VALIDUNTIL_EXPIRED,
            $authorisation->check_request_token(
                $authorisation->get('token'),
                $authorisation->get_signature()
            )
        );
    }

    /**
     * Test authorisation enrolment process
     */
    public function test_authorise_enrolment(): void {
        $this->resetAfterTest();
        $this->create_roles();
        set_config('availablenotifyroles', implode(',', [
            $this->roles['moduleleader'],
        ]), 'local_enrolstaff');
        set_config('availableroles', implode(',', [
            $this->roles['tutor'],
            $this->roles['moduleleader'],
            $this->roles['contentretrieval'],
        ]), 'local_enrolstaff');
        $requestor = $this->getDataGenerator()->create_user();
        $authoriser = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $this->getDataGenerator()->enrol_user($authoriser->id, $course->id, $this->roles['moduleleader']);
        /** @var local_enrolstaff_generator $esdg */
        $esdg = $this->getDataGenerator()->get_plugin_generator('local_enrolstaff');
        $rule = $esdg->create_rule([
            'roleids' => [$this->roles['tutor']],
            'notify' => ['r:' . $this->roles['moduleleader']],
            'enabled' => 1,
            'duration' => 182, // Days.
            'sendas' => 'authorisation',
        ]);

        set_config('enrolmentauthorisationvalidity', 7, 'local_enrolstaff');
        $validitydays = get_config('local_enrolstaff', 'enrolmentauthorisationvalidity');
        $validuntil = time() + ($validitydays * DAYSECS);
        $authorisation = $esdg->create_authorisation([
            'ruleid' => $rule->get('id'),
            'requestorid' => $requestor->id,
            'roleid' => $this->roles['tutor'],
            'courseid' => $course->id,
            'authoriserid' => $authoriser->id,
            'validuntil' => $validuntil,
        ]);

        // Authorise enrolment.
        $this->setUser($authoriser);
        $authorisation->authorise_enrolment($rule);

        // Check user is enrolled with correct role.
        $context = \context_course::instance($course->id);
        $this->assertTrue(
            \is_enrolled($context, $requestor->id, '', true)
        );
        $this->assertTrue(
            \user_has_role_assignment($requestor->id, $this->roles['tutor'], $context->id)
        );
        // The authorisation record should be deleted.
        $this->assertFalse(
            authorise::record_exists($authorisation->get('id'))
        );
    }
}
