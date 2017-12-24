<?php

namespace mod_sharedpanel;

class gcard
{
    protected $moduleinstance;
    protected $error;

    function __construct($modinstance) {
        $this->moduleinstance = $modinstance;
        $this->error = new \stdClass();
        $this->error->code = 0;
        $this->error->message = "";
    }

    function get($gcardid) {
        global $DB;
        return $DB->get_record('sharedpanel_gcards', ['id' => $gcardid]);
    }

    function gets($hidden = 0, $order = 'rating DESC') {
        global $DB;
        return $DB->get_records('sharedpanel_gcards', ['sharedpanelid' => $this->moduleinstance->id, 'hidden' => $hidden], $order);
    }

    function add($userid, $content, $sizex, $sizey) {
        global $DB;

        $data = new \stdClass;
        $data->sharedpanelid = $this->moduleinstance->id;
        $data->userid = $userid;
        $data->timecreated = time();
        $data->content = $content;
        $data->comment = "";
        $data->hidden = 0;
        $data->timecreated = time();
        $data->timemodified = time();
        $data->positionx = "";
        $data->positiony = "";
        $data->sizex = $sizex;
        $data->sizey = $sizey;

        return $DB->insert_record('sharedpanel_gcards', $data);
    }

    function update($gcardid, $content) {
        global $DB;

        $data = new \stdClass();
        $data->id = $gcardid;
        $data->content = $content;

        return $DB->update_record('sharedpanel_gcards', $data);
    }

    function delete($gcardid) {
        global $DB;

        $gcard = self::get($gcardid);
        $gcard->hidden = 1;

        return $DB->update_record('sharedpanel_gcards', $gcard);
    }
}