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

use action_menu_link;
use core\lang_string;
use core\output\action_menu;
use core\output\html_writer;
use core\output\pix_icon;
use core\url;
use core_table\sql_table;
use local_enrolstaff\local\api;
use local_enrolstaff\persistent\rule;

/**
 * Class rules_table
 *
 * @package    local_enrolstaff
 * @copyright  2025 Southampton Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rules_table extends sql_table {
    /**
     * Duration menu items
     *
     * @var array
     */
    private array $durationmenu = [];
    /**
     * Roles menu
     *
     * @var array
     */
    private array $rolesmenu = [];
    /**
     * Send notifaction or authorisation or not
     *
     * @var array
     */
    private array $sendasmenu = [];
    /**
     * Which roles get a notification if required.
     *
     * @var array
     */
    private array $notifymenu = [];
    /**
     * Constructore
     *
     * @param string $uniqueid
     */
    public function __construct($uniqueid) {
        parent::__construct($uniqueid);
        $this->useridfield = 'modifiedby';
        $columns = [
            'id' => 'id',
            'title' => new lang_string('title', 'local_enrolstaff'),
            'filter' => new lang_string('filter', 'local_enrolstaff'),
            'permissions' => new lang_string('permissions', 'local_enrolstaff'),
            'sendas' => new lang_string('field:sendas', 'local_enrolstaff'),
            'enabled' => new lang_string('enabled', 'local_enrolstaff'),
            'actions' => new lang_string('actions', 'local_enrolstaff'),
        ];
        $this->collapsible(false);
        $this->define_baseurl(new url('/local/enrolstaff/manage.php'));
        $this->define_columns(array_keys($columns));
        $this->define_headers(array_values($columns));
        $this->no_sorting('actions');
        $this->no_sorting('filter');
        $this->no_sorting('permissions');
        $this->no_sorting('sendas');
        $this->set_sql('*', '{local_enrolstaff}', '1=1');
        // Reusable lists.
        $this->durationmenu = api::get_duration_menu();
        $this->notifymenu = api::get_notify_menu();
        $this->rolesmenu = api::get_roles_menu();
        $this->sendasmenu = api::get_sendas_menu();
    }

    /**
     * Action column
     *
     * @param stdClass $row
     * @return string
     */
    public function col_actions($row) {
        global $OUTPUT;
        $actions = [];
        $actions[] = new action_menu_link(
            new url('/local/enrolstaff/rule.php', ['action' => 'edit', 'id' => $row->id]),
            new pix_icon('t/edit', ''),
            new lang_string('edit'),
            false
        );
        $actions[] = new action_menu_link(
            new url('/local/enrolstaff/rule.php', ['action' => ($row->enabled == 1) ? 'disable' : 'enable', 'id' => $row->id]),
            new pix_icon('t/' . (($row->enabled == 1) ? 'hide' : 'show'), ''),
            new lang_string(($row->enabled == 1) ? 'notenabled' : 'enabled', 'local_enrolstaff'),
            false
        );
        $actions[] = new action_menu_link(
            new url('/local/enrolstaff/previewrule.php', ['id' => $row->id]),
            new pix_icon('i/preview', ''),
            new lang_string('previewrule', 'local_enrolstaff'),
            false
        );
        $actions[] = new action_menu_link(
            new url('/local/enrolstaff/rule.php', ['action' => 'delete', 'id' => $row->id]),
            new pix_icon('t/delete', ''),
            new lang_string('delete'),
            false,
            [
                'class' => 'text-danger',
            ],
        );
        $actionsmenu = new action_menu($actions);
        return $OUTPUT->render_from_template('core/action_menu', $actionsmenu->export_for_template($OUTPUT));
    }

    /**
     * Enabled column
     *
     * @param stdClass $row
     * @return string
     */
    public function col_enabled($row): string {
        return ($row->enabled == 1) ? get_string('enabled', 'local_enrolstaff') : get_string('notenabled', 'local_enrolstaff');
    }

    /**
     * Filter column
     *
     * @param stdClass $row
     * @return string
     */
    public function col_filter($row): string {
        $rule = new rule($row->id);
        return $rule->print_filters();
    }

    /**
     * Permissions column
     *
     * @param stdClass $row
     * @return string
     */
    public function col_permissions($row): string {
        $plist = [];
        if (!empty($row->roleids)) {
            $roleids = api::clean_csv($row->roleids);
            $rolelist = [];
            foreach ($roleids as $roleid) {
                $rolelist[] = $this->rolesmenu[$roleid];
            }
            $plist[] = get_string(
                'enrolas',
                'local_enrolstaff',
                [
                    'list' => join(', ', $rolelist),
                    'period' => $this->durationmenu[$row->duration],
                ]
            );
        }
        if (!empty($row->codes)) {
            $plist[] = get_string(
                'fieldlookslikeoneof',
                'local_enrolstaff',
                [
                    'list' => $row->codes,
                    'field' => get_string('field:codes', 'local_enrolstaff'),
                ]
            );
        }
        if (!empty($row->excodes)) {
            $plist[] = get_string(
                'fieldlookslikeoneof',
                'local_enrolstaff',
                [
                    'list' => $row->excodes,
                    'field' => get_string('field:excodes', 'local_enrolstaff'),
                ]
            );
        }
        $html = get_string('matchespermissions', 'local_enrolstaff');
        $html .= html_writer::alist($plist);
        return $html;
    }

    /**
     * Send as column
     *
     * @param stdClass $row
     * @return string
     */
    public function col_sendas($row): string {
        $html = $this->sendasmenu[$row->sendas];
        if (!empty($row->notify)) {
            $roleids = api::clean_csv($row->notify);
            $authorisorroles = [];
            foreach ($roleids as $roleidoremail) {
                $isemail = filter_var($roleidoremail, FILTER_VALIDATE_EMAIL);
                if ($isemail) {
                    $authorisorroles[] = $roleidoremail;
                    continue;
                }
                if (isset($this->notifymenu[$roleidoremail])) {
                    $authorisorroles[] = $this->notifymenu[$roleidoremail];
                }
            }
            $html .= html_writer::empty_tag('br')
                . html_writer::alist($authorisorroles);
        }
        return $html;
    }

    /**
     * Rule name
     *
     * @param stdClass $row
     * @return string
     */
    public function col_title($row): string {
        $params = ['action' => 'edit', 'id' => $row->id];
        $edit = new url('/local/enrolstaff/rule.php', $params);
        $html = html_writer::link($edit, s($row->title));
        return $html;
    }
}
