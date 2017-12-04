<?php

namespace mod_sharedpanel;

defined('MOODLE_INTERNAL') || die();

class tag
{
    private $moduleinstance;

    function __construct($modinstance) {
        $this->moduleinstance = $modinstance;
    }

    function get($cardid) {
        global $DB;
        return $DB->get_record('sharedpanel_card_tags', ['cardid' => $cardid]);
    }

    function is_exists($cardid){
        global $DB;
        return $DB->record_exists('sharedpanel_card_tags', ['cardid' => $cardid]);
    }

    function set($cardid, $tag, $userid) {
        global $DB;

        $data = new \stdClass();
        $data->cardid = $cardid;
        $data->userid = $userid;
        $data->tag = $tag;
        $data->timecreated = time();

        return $DB->insert_record('sharedpanel_card_tags', $data);
    }

    function update($cardid, $tag){
        global $DB;

        $data = self::get($cardid);
        $data->tag = $tag;

        return $DB->update_record('sharedpanel_card_tags', $data);
    }

    function unset($cardid) {
        global $DB;

        return $DB->delete_records('sharedpanel_card_tags', ['cardid' => $cardid]);
    }
}