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

namespace mod_sharedpanel;

defined('MOODLE_INTERNAL') || die();

class lineid
{
    private $moduleinstance;

    public function __construct($modinstance) {
        $this->moduleinstance = $modinstance;
    }

    public function get_by_userid($userid) {
        global $DB;
        return $DB->get_record('sharedpanel_lineids', ['userid' => $userid, 'sharedpanelid' => $this->moduleinstance->id]);
    }

    public function get_by_line_userid($lineuserid) {
        global $DB;
        $lineid = $DB->get_record_select(
            'sharedpanel_lineids',
            "sharedpanelid = :sharedpanelid AND ".$DB->sql_compare_text('lineuserid')." = :lineuserid",
            ['lineuserid' => $lineuserid, 'sharedpanelid' => $this->moduleinstance->id]);

        if ($lineid) {
            return $lineid;
        } else {
            return false;
        }
    }

    public function set_line_id($userid, $lineid) {
        global $DB;

        $data = new \stdClass();
        $data->sharedpanelid = $this->moduleinstance->id;
        $data->lineid = $lineid;
        $data->userid = $userid;

        if ($DB->record_exists('sharedpanel_lineids', ['userid' => $userid, 'sharedpanelid' => $this->moduleinstance->id])) {
            $data = self::get_by_userid($userid);
            $data->lineid = $lineid;
            $data->timemodified = time();
            return $DB->update_record('sharedpanel_lineids', $data);
        } else {
            $data->timecreated = time();
            $data->timemodified = time();
            return $DB->insert_record('sharedpanel_lineids', $data);
        }
    }

    public function set_line_userid($username, $lineuserid) {
        global $DB;

        if (!$DB->record_exists('user', ['username' => $username])) {
            return false;
        }

        $user = \core_user::get_user_by_username($username);

        if ($DB->record_exists('sharedpanel_lineids', ['userid' => $user->id, 'sharedpanelid' => $this->moduleinstance->id])) {
            $data = self::get_by_userid($user->id);
            $data->lineuserid = $lineuserid;
            return $DB->update_record('sharedpanel_lineids', $data);
        } else {
            return false;
        }
    }

    public function delete($userid) {
        global $DB;
        return $DB->delete_records('sharedpanel_lineids', ['userid' => $userid, 'sharedpanelid' => $this->moduleinstance->id]);
    }
}