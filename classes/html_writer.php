<?php

namespace mod_sharedpanel;

defined('MOODLE_INTERNAL') || die();

use core\notification;

class html_writer extends \html_writer
{
    /**
     * Return message box
     *
     * @param string $type
     * @param string $content
     * @return string
     */
    public static function message($type, $content) {
        switch ($type) {
            case notification::SUCCESS:
                return \html_writer::div($content, 'alert alert-success');
            case notification::ERROR:
                return \html_writer::div($content, 'alert alert-error');
            case notification::WARNING:
                return \html_writer::div($content, 'alert');
            case notification::INFO:
                return \html_writer::div($content, 'alert alert-info');
        }
    }

    public static function userlink($userid) {
        global $OUTPUT;

        $user = \core_user::get_user($userid);
        return $OUTPUT->user_picture($user) . html_writer::link(new \moodle_url('/user/profile.php', ['id' => $user->id]), fullname($user));
    }

    public static function card($context, $card, $tstyle) {
        global $DB, $USER;

        $html = \html_writer::start_div('all-style0 card', ['id' => 'card' . $card->id, 'style' => $tstyle]);
        $html .= \html_writer::start_div($card->inputsrc . '-style1 all-style1');

        if (has_capability('moodle/course:manageactivities', $context)) {
            $dellink = html_writer::link(new \moodle_url('deletecard.php', ['id' => $context->instanceid, 'c' => $card->id, 'sesskey' => sesskey()]), '×');
            $html .= html_writer::span($dellink, '', ['style' => 'background-color:lightblue; font-size:25px; padding:1px;']);
        }

        $tags = card::get_tags($card->id);
        foreach ($tags as $tag) {
            $html .= html_writer::span(\html_writer::link(new \moodle_url(''), $tag->tag));
        }
        $html .= html_writer::end_div();

        // コンテンツ要素
        $cardcontent = $card->content;
        // 日付などを別途表示
        $cardcontent .= html_writer::div(
            '<br/><br/>' . date('c', $card->timeposted) . "<br/>" . $card->sender . "<br/> from " . $card->inputsrc
            , '', ['style' => 'font-size:60%;line-height:100%;']);

        $html .= html_writer::div($cardcontent, $card->inputsrc . '-style2 all-style2');

        $html .= html_writer::start_div('all-style3');

        $likearray = array('cardid' => $card->id, 'userid' => $USER->id, 'ltype' => 0);

        $like0 = $DB->get_record('sharedpanel_card_likes', $likearray);
        if ($like0 != false && $like0->rating > 0) {
            $maru1 = html_writer::span('✓', '', ['style' => 'font-size:17px;color:red;vertical-align:bottom;']);
        } else {
            $maru1 = html_writer::span('□', '', ['style' => 'font-size:27px;color:red;vertical-align:middle;']);
        }

        $likeslink = html_writer::link(new \moodle_url('likes.php', ['id' => $context->instanceid, 'c' => $card->id, 'sesskey' => sesskey()]), get_string('important', 'sharedpanel') . $maru1);
        $likeslink1 = html_writer::link(new \moodle_url('likes.php', ['id' => $context->instanceid, 'c' => $card->id, 'ltype' => 1, 'sesskey' => sesskey()]), get_string('interesting', 'sharedpanel') . $maru1);

        $ratingsum1 = $DB->get_record_sql('SELECT sum(rating) as sumr FROM {sharedpanel_card_likes} WHERE cardid = ? AND ltype = ?', [$card->id, 1]);
        $cardrating1 = $ratingsum1->sumr;

        $barwidth = $card->rating * 6;
        if ($barwidth > 150) {
            $barwidth = 150;
        }
        if ($card->rating > 0) {
            $likeslink .= " <img src='red.png' style='width:' . $barwidth . 'px; height:20px; vertical-align:middle;'>($card->like_count_0)";
        }
        if ($cardrating1 > 0) {
            $likeslink1 .= " <img src='blue.png' style='width: ' . $barwidth . 'px; height:20px; vertical-align:middle;'>($card->like_count_1)";
        }

        $html .= html_writer::div($likeslink);
        $html .= html_writer::div($likeslink1);

        $html .= html_writer::end_div();
        $html .= html_writer::end_div();

        return $html;
    }
}