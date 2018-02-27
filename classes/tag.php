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

class tag
{
    private $moduleinstance;

    public function __construct($modinstance) {
        $this->moduleinstance = $modinstance;
    }

    public function get($cardid) {
        global $DB;
        return $DB->get_record('sharedpanel_card_tags', ['cardid' => $cardid]);
    }

    public function is_exists($cardid) {
        global $DB;
        return $DB->record_exists('sharedpanel_card_tags', ['cardid' => $cardid]);
    }

    public function set($cardid, $tag, $userid) {
        global $DB;

        $data = new \stdClass();
        $data->cardid = $cardid;
        $data->userid = $userid;
        $data->tag = $tag;
        $data->timecreated = time();

        return $DB->insert_record('sharedpanel_card_tags', $data);
    }

    public function update($cardid, $tag) {
        global $DB;

        $data = self::get($cardid);
        $data->tag = $tag;

        return $DB->update_record('sharedpanel_card_tags', $data);
    }

    public function delete($cardid) {
        global $DB;

        return $DB->delete_records('sharedpanel_card_tags', ['cardid' => $cardid]);
    }
}