<?php

namespace mod_sharedpanel;

class card
{
    protected $moduleinstance;
    protected $error;

    function __construct($modinstance) {
        $this->moduleinstance = $modinstance;
        $this->error = new \stdClass();
        $this->error->code = 0;
        $this->error->message = "";
    }

    function get_gcards($hidden = 0, $order = 'rating DESC') {
        global $DB;
        return $DB->get_records('sharedpanel_gcards', ['sharedpanelid' => $this->moduleinstance->id, 'hidden' => $hidden], $order);
    }

    function get_cards($order = 'like') {
        global $DB, $USER;

        $sql = "
            SELECT *, (select count(*) from {sharedpanel_card_likes} l where l.userid = :userid and l.ltype = :ltype and l.cardid = c.id) likes 
              FROM {sharedpanel_cards} c
        ";

        $ltype = 0;
        if ($order === 'like') {
            $ltype = 0;
            $sql .= " ORDER BY likes DESC, c.timecreated DESC";
        } else if ($order === 'newest') {
            $sql .= " ORDER BY c.timecreated DESC";
        } else if ($order === 'important') {
            $ltype = 1;
            $sql .= " ORDER BY likes DESC, c.timecreated DESC";
        }

        return $DB->get_records_sql($sql, ['userid' => $USER->id, 'ltype' => $ltype]);
    }

    function get_last_card($inputsrc) {
        global $DB;
        $cards = $DB->get_records('sharedpanel_cards', ['sharedpanelid' => $this->moduleinstance->id, 'inputsrc' => $inputsrc], 'id DESC');

        return $cards ? current($cards) : false;
    }

    static function get_tags($cardid) {
        global $DB;
        return $DB->get_records('sharedpanel_card_tags', ['cardid' => $cardid]);
    }

    function add_card($content, $sender, $inputsrc = 'moodle', $messageid = "", $timeupdated = "") {
        global $DB, $USER;

        $data = new \stdClass;
        $data->sharedpanelid = $this->moduleinstance->id;
        $data->userid = $USER->id;
        if (empty($timeupdated)) {
            $data->timeposted = time();
        } else {
            $data->timeposted = $timeupdated;
        }
        $data->timecreated = time();
        $data->timemodified = time();
        $data->rating = 0;
        $data->sender = $sender;
        $data->messageid = $messageid;
        $data->content = $content;
        $data->comment = '';
        $data->hidden = 0;
        $data->inputsrc = $inputsrc;
        $data->positionx = 0;
        $data->positiony = 0;

        return $DB->insert_record('sharedpanel_cards', $data);
    }

    function add_gcard($userid, $content) {
        global $DB;

        $data = new \stdClass;
        $data->sharedpanelid = $this->moduleinstance->id;
        $data->userid = $userid;
        $data->timecreated = time();
        $data->content = $content;

        return $DB->insert_record('sharedpanel_gcards', $data);
    }

    function update_cards($cardid, $content) {
        global $DB;

        $data = new \stdClass();
        $data->id = $cardid;
        $data->content = $content;

        return $DB->update_record('sharedpanel_cards', $data);
    }

    function delete_card($cardid) {
        global $DB;

        $DB->delete_records('sharedpanel_card_likes', ['cardid' => $cardid]);
        return $DB->delete_records('sharedpanel_cards', ['id' => $cardid]);
    }

    function switch_hide_card($cardid) {
        global $DB;

        $card = $DB->get_record('sharedpanel_cards', ['id' => $cardid]);

        if ($card->hidden == 1) {
            $card->hidden = 0;
        } else {
            $card->hidden = 1;
        }

        return $DB->update_record('sharedpanel_cards', $card);
    }
}