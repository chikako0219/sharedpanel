<?php

namespace mod_sharedpanel;

global $DB;

use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib/line/LINEBot.php');
require_once(__DIR__ . '/lib/line/autoload.php');
require_once(dirname(__FILE__) . '/lib.php');

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

$cardObj = new card($sharedpanel);

/**
 * Load LINE
 */

$httpClient = new CurlHTTPClient($sharedpanel->line_channel_access_token);
$bot = new LINEBot($httpClient, ['channelSecret' => $sharedpanel->line_channel_secret]);
$signature = $_SERVER['HTTP_' . LINEBot\Constant\HTTPHeader::LINE_SIGNATURE];
$events = $bot->parseEventRequest(file_get_contents('php://input'), $signature);

error_log($events);

foreach ($events as $event) {
    if ($event instanceof LINEBot\Event\MessageEvent\TextMessage) {
        if (preg_match("/^line_/", $event->getText())) {
            $lineidObj = new lineid($sharedpanel);

            $username = str_replace('line_', '', $event->getText());

            if (!$lineidObj->set_line_userid($username, $event->getUserId())) {
                $textMessageBuilder = new LINEBot\MessageBuilder\TextMessageBuilder(
                    'もう一度入力してください。\n 例えば、ログインIDがb1007222の場合、"line_b1007222"と入力してください。'
                );
                $bot->replyMessage($event->getReplyToken(), $textMessageBuilder);
            } else {
                $textMessageBuilder = new LINEBot\MessageBuilder\TextMessageBuilder('ユーザーID' . $username . 'を登録しました。');
                $bot->replyMessage($event->getReplyToken(), $textMessageBuilder);
            }
        } else {
            $cardObj->add($event->getText(), $event->getUserId(), 'line', $event->getReplyToken());
            $textMessageBuilder = new LINEBot\MessageBuilder\TextMessageBuilder(
                'メッセージを投稿しました。'
            );
            $bot->replyMessage($event->getReplyToken(), $textMessageBuilder);
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
            $url = \moodle_url::make_pluginfile_url($context->id, 'mod_sharedpanel', 'attachment', $event->getMessageId(), '/', 'attacnhemt.jpg');
            $html = html_writer::empty_tag('img', ['src' => $url->out(false), 'width' => '250px']);

            $cardObj->add($html, $event->getUserId(), 'line', $event->getReplyToken());

            $textMessageBuilder = new LINEBot\MessageBuilder\TextMessageBuilder('画像を投稿しました。');
            $bot->replyMessage($event->getReplyToken(), $textMessageBuilder);
        } else {
            $textMessageBuilder = new LINEBot\MessageBuilder\TextMessageBuilder('画像投稿に失敗しました。');
            $bot->replyMessage($event->getReplyToken(), $textMessageBuilder);
        }
    }
}

die();