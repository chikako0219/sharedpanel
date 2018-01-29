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

    function get($cardid) {
        global $DB;
        return $DB->get_record('sharedpanel_cards', ['id' => $cardid]);
    }

    function gets($order = 'like') {
        global $DB, $USER;

        $sql = "
            SELECT *, (select count(*) from {sharedpanel_card_likes} l where l.userid = :userid and l.ltype = :ltype and l.cardid = c.id) likes 
              FROM {sharedpanel_cards} c WHERE c.hidden = 0 
        ";

        $ltype = 0;
        if ($order === 'like') {
            $ltype = 0;
            $sql .= " ORDER BY likes DESC, c.timecreated DESC, c.gravity ASC";
        } else if ($order === 'newest') {
            $sql .= " ORDER BY c.timecreated DESC, c.gravity ASC";
        } else if ($order === 'important') {
            $ltype = 1;
            $sql .= " ORDER BY likes DESC, c.timecreated DESC, c.gravity ASC";
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

    function add($content, $sender, $inputsrc = 'moodle', $messageid = "", $timeupdated = "") {
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
        $data->sender = $sender;
        $data->messageid = $messageid;
        $data->content = $content;
        $data->hidden = 0;
        $data->inputsrc = $inputsrc;
        $data->attachment_filename = '';
        $cards = self::gets();
        if (!$cards) {
            $data->gravity = 0;
        } else {
            $card = end($cards);
            $data->gravity = $card->gravity + 1;
        }

        return $DB->insert_record('sharedpanel_cards', $data);
    }

    function add_attachment($context, $cardid, $content, $filename) {
        global $DB;

        $fs = get_file_storage();

        $fileinfo = [
            'contextid' => $context->id,
            'component' => 'mod_sharedpanel',
            'filearea' => 'attachment',
            'itemid' => $cardid,
            'filepath' => '/',
            'filename' => $filename
        ];
        $fs->create_file_from_string($fileinfo, $content);

        $card = self::get($cardid);
        $card->attachment_filename = $filename;

        return $DB->update_record('sharedpanel_cards', $card);
    }

    function add_attachment_by_pathname($context, $cardid, $filepath, $filename){
        global $DB;

        $fs = get_file_storage();

        $fileinfo = [
            'contextid' => $context->id,
            'component' => 'mod_sharedpanel',
            'filearea' => 'attachment',
            'itemid' => $cardid,
            'filepath' => '/',
            'filename' => $filename
        ];
        $fs->create_file_from_pathname($fileinfo, $filepath);

        $card = self::get($cardid);
        $card->attachment_filename = $filename;

        return $DB->update_record('sharedpanel_cards', $card);
    }

    function update($cardid, $content) {
        global $DB;

        $data = new \stdClass();
        $data->id = $cardid;
        $data->content = $content;

        return $DB->update_record('sharedpanel_cards', $data);
    }

    function delete($cardid) {
        global $DB;

        $card = self::get($cardid);
        $card->hidden = 1;

        return $DB->update_record('sharedpanel_cards', $card);
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