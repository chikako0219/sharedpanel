<?php

namespace mod_sharedpanel;

defined('MOODLE_INTERNAL') || die();

class like
{
    private $moduleinstance;

    function __construct($modinstance) {
        $this->moduleinstance = $modinstance;
    }

    function get($cardid, $userid, $ltype) {
        global $DB;
        return $DB->get_record('sharedpanel_card_likes', ['cardid' => $cardid, 'userid' => $userid, 'ltype' => $ltype]);
    }

    function gets($cardid, $ltype = null, $userid = null, $rating = false) {
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

    function set($cardid, $userid, $ltype = 0) {
        global $DB;

        $like = new \stdClass;
        $like->cardid = $cardid;
        $like->userid = $userid;
        $like->timecreated = time();
        $like->ltype = $ltype;

        return $DB->insert_record('sharedpanel_card_likes', $like);
    }

    function unset($cardid, $userid, $ltype) {
        global $DB;

        $like = $DB->get_record('sharedpanel_card_likes', ['cardid' => $cardid, 'userid' => $userid, 'ltype' => $ltype]);
        return $DB->delete_records('sharedpanel_card_likes', ['id' => $like->id]);
    }

    function count($cardid, $userid = null, $ltype = null) {
        global $DB;

        if (is_null($userid)) {
            $cond = ['cardid' => $cardid];
        } else {
            $cond = ['cardid' => $cardid, 'userid' => $userid];
        }
        if (!is_null($ltype) && $ltype == 0) {
            $cond['ltype'] = 0;
        } elseif (!is_null($ltype) && $ltype == 1) {
            $cond['ltype'] = 1;
        }

        return $DB->count_records('sharedpanel_card_likes', $cond);
    }

    function is_liked($cardid, $userid, $ltype = null) {
        global $DB;
        return $DB->record_exists('sharedpanel_card_likes', ['cardid' => $cardid, 'userid' => $userid, 'ltype' => $ltype]);
    }
}