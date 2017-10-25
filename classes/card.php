<?php
/**
 * Created by PhpStorm.
 * User: yue
 * Date: 2017/10/09
 * Time: 15:22
 */

namespace mod_sharedpanel;

class card
{
    private $moduleinstance;

    function __construct($modinstance) {
        $this->moduleinstance = $modinstance;
    }

    function get_gcards($hidden = 0, $order = 'rating DESC') {
        global $DB;
        return $DB->get_records('sharedpanel_gcards', ['sharedpanelid' => $this->moduleinstance->id, 'hidden' => $hidden], $order);
    }

    function get_cards($hidden = 0, $order = 'rating DESC, timeposted DESC') {
        global $DB;
        return $DB->get_records('sharedpanel_cards', ['sharedpanelid' => $this->moduleinstance->id, 'hidden' => $hidden], $order);
    }
}