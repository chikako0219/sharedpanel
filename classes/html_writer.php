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
     * @throws \moodle_exception
     */
    public static function userlink($userid) {
        global $OUTPUT;

        $user = \core_user::get_user($userid);
        return $OUTPUT->user_picture($user) . html_writer::link(new \moodle_url('/user/profile.php', ['id' => $user->id]), fullname($user));
    }

    public static function card($instance, $context, $card) {
        global $USER, $OUTPUT;
        switch ($card->inputsrc) {
            case "facebook" :
                $class = "-facebook";
                break;
            case "twitter" :
                $class = "-twitter";
                break;
            case "moodle" :
                $class = "-moodle";
                break;
            case "line" :
                $class = "-line";
                break;
            case "camera" :
                $class = "-camera";
                break;
            default :
                $class = "";
                break;
        }

        $likeObj = new like($instance);

        $html = \html_writer::start_div('card span3 col-md-3', ['id' => 'card' . $card->id]);

        $tags = card::get_tags($card->id);
        $html .= html_writer::start_div('card-header card-header' . $class);

        if (has_capability('moodle/course:manageactivities', $context)) {
            $dellink = new \moodle_url('deletecard.php', ['id' => $context->instanceid, 'c' => $card->id, 'sesskey' => sesskey()]);
            $icon = $OUTPUT->action_icon($dellink, new \pix_icon('t/delete', ''));

            $html .= html_writer::span($icon, 'card-icon-del');
        }

        foreach ($tags as $tag) {
            $html .= html_writer::span(\html_writer::link(new \moodle_url(''), $tag->tag));
        }
        $html .= html_writer::end_div();

        $html .= html_writer::start_div('card-body card-body' . $class);
        $html .= html_writer::span($card->content);
        $html .= html_writer::span(
            '<br/><br/>' . userdate($card->timeposted) . "<br/>" . $card->sender . "<br/> from " . $card->inputsrc);

        //If attachment exists
        if (!is_null($card->attachment_filename) && !empty($card->attachment_filename)) {
            $fs = get_file_storage();
            $file = $fs->get_file(
                $context->id,
                'mod_sharedpanel',
                'attachment',
                $card->id,
                '/',
                $card->attachment_filename
            );

            //Output image attachment or make download link
            if ($file->get_mimetype() === 'image/png' || $file->get_mimetype() === 'image/jpeg' || $file->get_mimetype() === 'image/gif') {
                $url = \moodle_url::make_pluginfile_url(
                    $context->id,
                    $file->get_component(),
                    $file->get_filearea(),
                    $file->get_itemid(),
                    $file->get_filepath(),
                    $file->get_filename()
                );
                $html .= html_writer::span(
                    html_writer::empty_tag('img', ['src' => $url->out(), 'class' => 'card-body-attachment'])
                );
            } else {
                $url = \moodle_url::make_pluginfile_url(
                    $context->id,
                    $file->get_component(),
                    $file->get_filearea(),
                    $file->get_itemid(),
                    $file->get_filepath(),
                    $file->get_filename(),
                    true
                );
                $html .= html_writer::span(html_writer::link($url, get_string('download'), ['class' => 'btn btn-success']));
            }
        }

        $html .= html_writer::end_div();

        $html .= html_writer::start_div('card-footer card-footer' . $class);
        $like = $likeObj->get($card->id, $USER->id, 0);

        $like_count_0 = $likeObj->count($card->id, null, 0);
        $like_count_0_all = $likeObj->count($card->id, $USER->id, 0);
        $like_count_1 = $likeObj->count($card->id, $USER->id, 1);
        $like_count_1_all = $likeObj->count($card->id, null, 1);

        if ($like != false && $like_count_0 > 0) {
            $like_icon_0 = html_writer::span('✓', '', ['style' => 'color:red;']);
        } else {
            $like_icon_0 = html_writer::span('□', '', ['style' => 'color:red;']);
        }

        if ($like != false && $like_count_1 > 0) {
            $like_icon_1 = html_writer::span('✓', '', ['style' => 'color:red;']);
        } else {
            $like_icon_1 = html_writer::span('□', '', ['style' => 'color:red;']);
        }

        $link_like = html_writer::link(
            new \moodle_url('likes.php', ['id' => $context->instanceid, 'c' => $card->id, 'sesskey' => sesskey()]),
            get_string('interesting', 'sharedpanel') . $like_icon_0
        );
        $link_unlike = html_writer::link(
            new \moodle_url('likes.php', ['id' => $context->instanceid, 'c' => $card->id, 'sesskey' => sesskey(), 'ltype' => 1]),
            get_string('important', 'sharedpanel') . $like_icon_1
        );

        if ($like_count_0_all > 0) {
            $link_like .= html_writer::empty_tag('img', ['src' => 'red.png']) . "($like_count_0)";
        }
        $html .= html_writer::div($link_like);
        if ($like_count_1_all > 0) {
            $link_unlike .= html_writer::empty_tag('img', ['src' => 'blue.png']) . "($like_count_1)";
        }
        $html .= html_writer::div($link_unlike);

        $html .= html_writer::end_div();

        $html .= html_writer::end_div();

        return $html;
    }
}