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

class like
{
    private $moduleinstance;

    public function __construct($modinstance) {
        $this->moduleinstance = $modinstance;
    }

    public function get($cardid, $userid, $ltype) {
        global $DB;
        return $DB->get_record('sharedpanel_card_likes', ['cardid' => $cardid, 'userid' => $userid, 'ltype' => $ltype]);
    }

    public function gets($cardid, $ltype = null, $userid = null, $rating = false) {
        global $DB;

        $cond = ['cardid' => $cardid];
        if (!is_null($ltype)) {
            $cond['ltype'] = $ltype;
        }
        if (!is_null($userid)) {
            $cond['userid'] = $userid;
        }
        if ($rating) {
            $cond['ration'] = 1;
        }

        return $DB->get_records('sharedpanel_card_likes', $cond);
    }

    public function set($cardid, $userid, $ltype = 0) {
        global $DB;

        $like = new \stdClass;
        $like->cardid = $cardid;
        $like->userid = $userid;
        $like->timecreated = time();
        $like->ltype = $ltype;

        return $DB->insert_record('sharedpanel_card_likes', $like);
    }

    public function delete($cardid, $userid, $ltype) {
        global $DB;

        $like = $DB->get_record('sharedpanel_card_likes', ['cardid' => $cardid, 'userid' => $userid, 'ltype' => $ltype]);
        return $DB->delete_records('sharedpanel_card_likes', ['id' => $like->id]);
    }

    public function count($cardid, $userid = null, $ltype = null) {
        global $DB;

        if (is_null($userid)) {
            $cond = ['cardid' => $cardid];
        } else {
            $cond = ['cardid' => $cardid, 'userid' => $userid];
        }
        if (!is_null($ltype) && $ltype == 0) {
            $cond['ltype'] = 0;
        } else if (!is_null($ltype) && $ltype == 1) {
            $cond['ltype'] = 1;
        }

        return $DB->count_records('sharedpanel_card_likes', $cond);
    }

    public function is_liked($cardid, $userid, $ltype = null) {
        global $DB;
        return $DB->record_exists('sharedpanel_card_likes', ['cardid' => $cardid, 'userid' => $userid, 'ltype' => $ltype]);
    }
}