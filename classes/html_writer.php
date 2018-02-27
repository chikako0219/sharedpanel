<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

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
        return $OUTPUT->user_picture($user) . self::link(
            new \moodle_url('/user/profile.php', ['id' => $user->id]), fullname($user)
            );
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

        $likeobj = new like($instance);

        $html = \html_writer::start_div('card span3 col-md-3', ['id' => 'card' . $card->id]);

        $tags = card::get_tags($card->id);
        $html .= self::start_div('card-header card-header' . $class);

        if (has_capability('moodle/course:manageactivities', $context)) {
            $dellink = new \moodle_url('deletecard.php', ['id' => $context->instanceid, 'c' => $card->id, 'sesskey' => sesskey()]);
            $icon = $OUTPUT->action_icon($dellink, new \pix_icon('t/delete', ''));

            $html .= self::span($icon, 'card-icon-del');
        }

        foreach ($tags as $tag) {
            $html .= self::span(self::link(new \moodle_url(''), $tag->tag));
        }
        $html .= self::end_div();

        $html .= self::start_div('card-body card-body' . $class);
        $html .= self::span($card->content);
        $html .= self::span(
            '<br/><br/>' . userdate($card->timeposted) . "<br/>" . $card->sender . "<br/> from " . $card->inputsrc);

        // If attachment exists.
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

            // Output image attachment or make download link.
            if ($file->get_mimetype() === 'image/png' ||
                $file->get_mimetype() === 'image/jpeg' ||
                $file->get_mimetype() === 'image/gif') {
                $url = \moodle_url::make_pluginfile_url(
                    $context->id,
                    $file->get_component(),
                    $file->get_filearea(),
                    $file->get_itemid(),
                    $file->get_filepath(),
                    $file->get_filename()
                );
                $html .= self::span(
                    self::empty_tag('img', ['src' => $url->out(), 'class' => 'card-body-attachment'])
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
                $html .= self::span(self::link($url, get_string('download'), ['class' => 'btn btn-success']));
            }
        }

        $html .= self::end_div();

        $html .= self::start_div('card-footer card-footer' . $class);
        $like = $likeobj->get($card->id, $USER->id, 0);

        $likecount0 = $likeobj->count($card->id, null, 0);
        $likecount0all = $likeobj->count($card->id, $USER->id, 0);
        $likecount1 = $likeobj->count($card->id, $USER->id, 1);
        $likecount1all = $likeobj->count($card->id, null, 1);

        if ($like != false && $likecount0 > 0) {
            $likeicon0 = self::span('✓', '', ['style' => 'color:red;']);
        } else {
            $likeicon0 = self::span('□', '', ['style' => 'color:red;']);
        }

        if ($like != false && $likecount1 > 0) {
            $likeicon1 = self::span('✓', '', ['style' => 'color:red;']);
        } else {
            $likeicon1 = self::span('□', '', ['style' => 'color:red;']);
        }

        $linklike = self::link(
            new \moodle_url('likes.php', ['id' => $context->instanceid, 'c' => $card->id, 'sesskey' => sesskey()]),
            get_string('interesting', 'sharedpanel') . $likeicon0
        );
        $linkunlike = self::link(
            new \moodle_url('likes.php', ['id' => $context->instanceid, 'c' => $card->id, 'sesskey' => sesskey(), 'ltype' => 1]),
            get_string('important', 'sharedpanel') . $likeicon1
        );

        if ($likecount0all > 0) {
            $linklike .= self::empty_tag('img', ['src' => 'red.png']) . "($likecount0)";
        }
        $html .= self::div($linklike);
        if ($likecount1all > 0) {
            $linkunlike .= self::empty_tag('img', ['src' => 'blue.png']) . "($likecount1)";
        }
        $html .= self::div($linkunlike);
        $html .= self::end_div();
        $html .= self::end_div();

        return $html;
    }
}