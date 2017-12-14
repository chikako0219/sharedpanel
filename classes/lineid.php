<?php

namespace mod_sharedpanel;

defined('MOODLE_INTERNAL') || die();

class lineid
{
    private $moduleinstance;

    function __construct($modinstance) {
        $this->moduleinstance = $modinstance;
    }

    function get($userid) {
        global $DB;
        return $DB->get_record('sharedpanel_lineids', ['userid' => $userid, 'sharedpanelid' => $this->moduleinstance->id]);
    }

    function set_line_id($userid, $lineid) {
        global $DB;

        $data = new \stdClass();
        $data->sharedpanelid = $this->moduleinstance->id;
        $data->lineid = $lineid;
        $data->userid = $userid;

        if ($DB->record_exists('sharedpanel_lineids', ['userid' => $userid, 'sharedpanelid' => $this->moduleinstance->id])) {
            $data = self::get($userid);
            $data->lineid = $lineid;
            $data->timemodified = time();
            return $DB->update_record('sharedpanel_lineids', $data);
        } else {
            $data->timecreated = time();
            $data->timemodified = time();
            return $DB->insert_record('sharedpanel_lineids', $data);
        }
    }

    function set_line_userid($username, $lineuserid) {
        global $DB;

        if (!$DB->record_exists('user', ['username' => $username])) {
            return false;
        }

        $user = \core_user::get_user_by_username($username);

        if ($DB->record_exists('sharedpanel_lineids', ['userid' => $user->id, 'sharedpanelid' => $this->moduleinstance->id])) {
            $data = self::get($user->id);
            $data->lineuserid = $lineuserid;
            return $DB->update_record('sharedpanel_lineids', $data);
        } else {
            return false;
        }
    }

    function delete($userid) {
        global $DB;
        return $DB->delete_records('sharedpanel_lineids', ['userid' => $userid, 'sharedpanelid' => $this->moduleinstance->id]);
    }
}