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

use core\persistent;

/**
 * Class authorise
 *
 * @package    local_enrolstaff
 * @copyright  2025 Southampton Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class authorise extends persistent {
    /** Table name for this persistent class. */
    const TABLE = 'local_enrolstaff_auth';
    /** Token is invalid. */
    const TOKEN_INVALID = 'invalidtoken';
    /** Signature is invalid. */
    const SIGNATURE_INVALID = 'invalidsignature';
    /** Valid until date is expired. */
    const VALIDUNTIL_EXPIRED = 'validuntilexpired';
    /** Authoriserid is invalid. */
    const AUTHORISERID_INVALID = 'invalidauthoriserid';

    /**
     * Define the properties of the persistent class.
     *
     * @return array
     */
    protected static function define_properties(): array {
        return [
            'ruleid' => [
                'type' => PARAM_INT,
            ],
            'requestorid' => [
                'type' => PARAM_INT,
            ],
            'roleid' => [
                'type' => PARAM_INT,
            ],
            'courseid' => [
                'type' => PARAM_INT,
            ],
            'authoriserid' => [
                'type' => PARAM_INT,
            ],
            'token' => [
                'type' => PARAM_ALPHANUMEXT,
                'length' => 32,
                'required' => true,
                'default' => function () {
                    return bin2hex(random_bytes(16));
                },
            ],
            'validuntil' => [
                'type' => PARAM_INT,
            ],
        ];
    }

    /**
     * Check request token
     *
     * @param string $token
     * @param string $signature
     * @return string|bool
     */
    public function check_request_token(string $token, string $signature) {
        global $USER;
        if ($this->get('token') != $token) {
            return self::TOKEN_INVALID;
        }
        if (time() > $this->get('validuntil')) {
            return self::VALIDUNTIL_EXPIRED;
        }
        if ($this->get_signature() != $signature) {
            return self::SIGNATURE_INVALID;
        }
        if ($this->get('authoriserid') != $USER->id) {
            return self::AUTHORISERID_INVALID;
        }
        return true;
    }

    /**
     * Get Signature
     *
     * @return string
     */
    public function get_signature(): string {
        return sha1(
            $this->get('id') . '|' .
            $this->get('authoriserid') . '|' .
            $this->get('validuntil') . '|' .
            $this->get('courseid')
        );
    }

    /**
     * Get url params for authorisation link
     *
     * @return array
     */
    public function get_url_params(): array {
        return [
            'token' => $this->get('token'),
            'signature' => $this->get_signature(),
            'courseid' => $this->get('courseid'),
        ];
    }

    /**
     * Authorise enrolment
     *
     * @param rule $rule
     * @return void
     */
    public function authorise_enrolment(rule $rule): void {
        global $USER;
        // Should check the current user can authorise in this context.
        if ($this->get('authoriserid') != $USER->id) {
            throw new \moodle_exception('authorise:' . self::AUTHORISERID_INVALID, 'local_enrolstaff');
        }
        // Enrol the user according to the rule.
        if (!in_array($this->get('roleid'), $rule->get('roleids'))) {
            throw new \moodle_exception('authorise:roleidnotinrule', 'local_enrolstaff');
        }
        $rule->enrol_user($this->get('requestorid'), $this->get('courseid'), $this->get('roleid'));
        // Delete this authorisation record.
        $this->delete();
    }
}
