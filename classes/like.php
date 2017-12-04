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
        $like->rating = 1;
        $like->comment = '';
        $like->ltype = $ltype;

        return $DB->insert_record('sharedpanel_card_likes', $like);
    }

    function update($cardid, $userid, $ltype) {
        global $DB;

        $like = self::get($cardid, $userid, $ltype);
        $like->rating = 1;
        return $DB->update_record('sharedpanel_card_likes', $like);
    }

    function unset($cardid, $userid, $ltype) {
        global $DB;

        $like = $DB->get_record('sharedpanel_card_likes', ['cardid' => $cardid, 'userid' => $userid, 'ltype' => $ltype]);
        $like->rating = 0;
        return $DB->update_record('sharedpanel_card_likes', $like);
    }
}