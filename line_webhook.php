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

use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib/line/LINEBot.php');
require_once(__DIR__ . '/lib/line/autoload.php');
require_once(dirname(__FILE__) . '/lib.php');

global $DB;

http_response_code(200);

$id = required_param('id', PARAM_INT);

$sharedpanel = null;
if ($id) {
    $sharedpanel = $DB->get_record('sharedpanel', ['id' => $id]);
    $course = $DB->get_record('course', ['id' => $sharedpanel->course], '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('sharedpanel', $sharedpanel->id, $course->id, false, MUST_EXIST);
    $context = \context_module::instance($cm->id);
} else {
    die();
}

$cardobj = new card($sharedpanel);

// Loading line API.
$httpclient = new CurlHTTPClient($sharedpanel->line_channel_access_token);
$bot = new LINEBot($httpclient, ['channelSecret' => $sharedpanel->line_channel_secret]);
$signature = $_SERVER['HTTP_' . LINEBot\Constant\HTTPHeader::LINE_SIGNATURE];
$events = $bot->parseEventRequest(file_get_contents('php://input'), $signature);

foreach ($events as $event) {
    if ($event instanceof LINEBot\Event\MessageEvent\TextMessage) {
        if (preg_match("/^line_/", $event->getText())) {
            $lineidobj = new lineid($sharedpanel);

            $username = str_replace('line_', '', $event->getText());

            if (!$lineidobj->set_line_userid($username, $event->getUserId())) {
                $textmessagebuilder = new LINEBot\MessageBuilder\TextMessageBuilder(
                    get_string('line_try_agein', 'mod_sharedpanel')
                );
                $bot->replyMessage($event->getReplyToken(), $textmessagebuilder);
            } else {
                $textmessagebuilder = new LINEBot\MessageBuilder\TextMessageBuilder(
                    get_string('line_added_user', 'mod_sharedpanel', $username));
                $bot->replyMessage($event->getReplyToken(), $textmessagebuilder);
            }
        } else {
            $cardobj->add($event->getText(), $event->getUserId(), 'line', $event->getReplyToken());
            $textmessagebuilder = new LINEBot\MessageBuilder\TextMessageBuilder(
                get_string('line_post_message', 'mod_sharedpanel')
            );
            $bot->replyMessage($event->getReplyToken(), $textmessagebuilder);
        }
    } else if ($event instanceof LINEBot\Event\MessageEvent\ImageMessage) {
        $fs = get_file_storage();
        $response = $bot->getMessageContent($event->getMessageId());
        if ($response->isSucceeded()) {
            $filerecord = [
                'contextid' => $context->id,
                'component' => 'mod_sharedpanel',
                'filearea' => 'attachment',
                'itemid' => $event->getMessageId(),
                'filepath' => '/',
                'filename' => 'attacnhemt.jpg',
                'userid' => 1
            ];
            $fs->create_file_from_string($filerecord, $response->getRawBody());
            $url = \moodle_url::make_pluginfile_url(
                $context->id, 'mod_sharedpanel', 'attachment', $event->getMessageId(), '/', 'attacnhemt.jpg');
            $html = html_writer::empty_tag('img', ['src' => $url->out(false), 'width' => '250px']);

            $cardobj->add($html, $event->getUserId(), 'line', $event->getReplyToken());

            $textmessagebuilder = new LINEBot\MessageBuilder\TextMessageBuilder('画像を投稿しました。');
            $bot->replyMessage($event->getReplyToken(), $textmessagebuilder);
        } else {
            $textmessagebuilder = new LINEBot\MessageBuilder\TextMessageBuilder('画像投稿に失敗しました。');
            $bot->replyMessage($event->getReplyToken(), $textmessagebuilder);
        }
    }
}

die();