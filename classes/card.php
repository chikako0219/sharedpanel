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
        global $DB;

        $sql = "SELECT cards.*, card_likes.ltype, 
                      (SELECT COUNT(id) 
                               FROM {sharedpanel_card_likes} likes 
                              WHERE likes.cardid = cards.id AND rating != 0 AND ltype = 0) like_count_0,
                      (SELECT COUNT(id) 
                               FROM {sharedpanel_card_likes} likes 
                              WHERE likes.cardid = cards.id AND rating != 0 AND ltype = 1) like_count_1
                  FROM {sharedpanel_cards} cards
             LEFT JOIN {sharedpanel_card_likes} card_likes ON card_likes.cardid = cards.id 
                 WHERE cards.sharedpanelid = :sharedpanelid
                 GROUP BY cards.id
                  ";

        if ($order === 'like') {
            $sql .= " ORDER BY like_count_0 DESC, card_likes.ltype ASC";
        } else if ($order === 'newest') {
            $sql .= " ORDER BY  card_likes.ltype DESC, cards.timecreated DESC";
        } else if ($order === 'important') {
            $sql .= " ORDER BY like_count_1 DESC, card_likes.ltype DESC";
        }

        return $DB->get_records_sql($sql, ['sharedpanelid' => $this->moduleinstance->id]);
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

    function update_cards($cardid, $content) {
        global $DB;

        $data = new \stdClass();
        $data->id = $cardid;
        $data->content = $content;

        return $DB->update_record('sharedpanel_cards', $data);
    }

    function delete_cards($cardid) {
        global $DB;

        $DB->delete_records('sharedpanel_card_likes', ['cardid' => $cardid]);
        return $DB->delete_records('sharedpanel_cards', ['id' => $cardid]);
    }
}