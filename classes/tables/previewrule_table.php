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

namespace local_enrolstaff\tables;

use core\url;
use core_table\sql_table;
use lang_string;
use local_enrolstaff\local\api;
use local_enrolstaff\persistent\rule;

/**
 * Class previewrule_table
 *
 * @package    local_enrolstaff
 * @copyright  2025 Southampton Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class previewrule_table extends sql_table {
    /**
     * previewrule_table constructor.
     *
     * @param string $uniqueid
     * @param rule $rule
     */
    public function __construct(string $uniqueid, rule $rule) {
        global $DB;
        parent::__construct($uniqueid);
        $this->set_attribute('id', 'local_enrolstaff_previewrules');
        $this->useridfield = 'id';
        $columns = [
            'id' => 'id',
            'auth' => new lang_string('field:authenticationmethods', 'local_enrolstaff'),
            'username' => new lang_string('username'),
            'fullname' => new lang_string('fullname'),
            'email' => new lang_string('email'),
            'department' => new lang_string('department'),
            'institution' => new lang_string('institution', 'local_enrolstaff'),
            'cohorts' => new lang_string('matchingcohorts', 'local_enrolstaff'),
        ];
        $this->collapsible(false);
        $this->define_baseurl(new url('/local/enrolstaff/previewrule.php', ['id' => $rule->get('id')]));
        $this->define_columns(array_keys($columns));
        $this->define_headers(array_values($columns));
        $this->is_sortable(false);
        $this->is_downloadable(false);
        [$select, $from, $params, $conditions] = $rule->get_userfilter_sql();
        $where = '';
        $cohortselect = ", '' cohorts";
        if (!empty($rule->get('cohortids'))) {
            // Add cohort column listing all the matching cohort names for each user, include it as a subquery.
            // We're adding it here instead of get_userfilter_sql() as this is just for display.
            [$insql, $inparams] = $DB->get_in_or_equal($rule->get('cohortids'), SQL_PARAMS_NAMED, 'scids');
            $cohortselect = ", (SELECT GROUP_CONCAT(c.name SEPARATOR ', ')
                FROM {cohort} c
                JOIN {cohort_members} cm ON cm.cohortid = c.id
                WHERE cm.userid = u.id
                AND c.id $insql
            ) cohorts";
            $params = array_merge($params, $inparams);
        }
        if (!empty($conditions)) {
            $where = implode(' AND ', $conditions);
        }
        $this->set_sql($select . $cohortselect, $from, $where, $params);
    }
}
