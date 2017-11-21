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
                  JOIN {sharedpanel_card_likes} card_likes ON card_likes.cardid = cards.id 
                 GROUP BY cards.id
                  ";

        if ($order === 'like') {
            $sql .= " ORDER BY like_count_0 DESC, card_likes.ltype ASC";
        } else if ($order === 'newest') {
            $sql .= " ORDER BY  card_likes.ltype DESC, cards.timecreated DESC";
        } else if ($order === 'important') {
            $sql .= " ORDER BY like_count_1 DESC, card_likes.ltype DESC";
        }

        return $DB->get_records_sql($sql);
    }

    static function get_tags($cardid) {
        global $DB;
        return $DB->get_records('sharedpanel_card_tags', ['cardid' => $cardid]);
    }
}