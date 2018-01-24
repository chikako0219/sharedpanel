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

    /**
     * Return user icon and link
     *
     * @param $userid
     * @return string
     * @throws \dml_exception
     */
    public static function userlink($userid) {
        global $OUTPUT;

        $user = \core_user::get_user($userid);
        return $OUTPUT->user_picture($user) . html_writer::link(new \moodle_url('/user/profile.php', ['id' => $user->id]), fullname($user));
    }

    public static function card($instance, $context, $card) {
        global $USER, $DB, $OUTPUT;

        $likeObj = new like($instance);

        $html = \html_writer::start_div('card span3', ['id' => 'card' . $card->id]);

        //@TODO typeによって追加でclassを渡す。
        $tags = card::get_tags($card->id);
        $html .= html_writer::start_div('card-header-common');

        if (has_capability('moodle/course:manageactivities', $context)) {
            $dellink = new \moodle_url('deletecard.php', ['id' => $context->instanceid, 'c' => $card->id, 'sesskey' => sesskey()]);
            $icon = $OUTPUT->action_icon($dellink, new \pix_icon('t/delete', ''));

            $html .= html_writer::span($icon, 'card-icon-del');
        }

        foreach ($tags as $tag) {
            $html .= html_writer::span(\html_writer::link(new \moodle_url(''), $tag->tag));
        }
        $html .= html_writer::end_div();

        $html .= html_writer::start_div('card-body');
        $html .= html_writer::span($card->content);
        $html .= html_writer::span(
            '<br/><br/>' . date('c', $card->timeposted) . "<br/>" . $card->sender . "<br/> from " . $card->inputsrc);
        $html .= html_writer::end_div();

        $html .= html_writer::start_div('card-footer');
        $like = $likeObj->get($card->id, $USER->id, 0);

        $like_count_0 = $likeObj->count($card->id, $USER->id, 0);
        $like_count_1 = $likeObj->count($card->id, $USER->id, 1);

        if ($like != false && $like_count_0 > 0) {
            $like_icon_0 = html_writer::span('✓', '', ['style' => 'font-size:17px;color:red;vertical-align:bottom;']);
        } else {
            $like_icon_0 = html_writer::span('□', '', ['style' => 'font-size:27px;color:red;vertical-align:middle;']);
        }

        if ($like != false && $like_count_1 > 0) {
            $like_icon_1 = html_writer::span('✓', '', ['style' => 'font-size:17px;color:red;vertical-align:bottom;']);
        } else {
            $like_icon_1 = html_writer::span('□', '', ['style' => 'font-size:27px;color:red;vertical-align:middle;']);
        }

        $link_like = html_writer::link(
            new \moodle_url('likes.php', ['id' => $context->instanceid, 'c' => $card->id, 'sesskey' => sesskey()]),
            get_string('interesting', 'sharedpanel') . $like_icon_0
        );
        $link_unlike = html_writer::link(
            new \moodle_url('likes.php', ['id' => $context->instanceid, 'c' => $card->id, 'sesskey' => sesskey(), 'ltype' => 1]),
            get_string('important', 'sharedpanel') . $like_icon_1
        );

        //@TODO SQLの外出し
//        $ratingsum1 = $DB->get_record_sql(
//            'SELECT sum(rating) as sumr FROM {sharedpanel_card_likes} WHERE cardid = :cardid AND ltype = :ltype',
//            ['cardid' => $card->id, 'ltype' => 1]);
//        $cardrating1 = $ratingsum1->sumr;

        if ($like_count_0 > 0) {
            $link_like .= html_writer::empty_tag('img', ['src' => 'red.png']) . "($like_count_0)";
        }
        $html .= html_writer::div($link_like);
        if ($like_count_1 > 0) {
            $link_unlike .= html_writer::empty_tag('img', ['src' => 'blue.png']) . "($like_count_1)";
        }
        $html .= html_writer::div($link_unlike);

        $html .= html_writer::end_div();

        $html .= html_writer::end_div();

        return $html;
    }
}