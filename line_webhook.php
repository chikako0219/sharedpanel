<?php

namespace mod_sharedpanel;

global $DB;

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib/line/LINEBot.php');
require_once(dirname(__FILE__) . '/lib.php');

$id = required_param('id', PARAM_INT);

$events = file_get_contents('php://input');

$sharedpanel = null;
if ($id) {
    $sharedpanel = $DB->get_record('sharedpanel', ['id' => $id]);
    $cm = get_coursemodule_from_id('sharedpanel', $id, 0, false, MUST_EXIST);
    $context = \context_module::instance($cm->id);
} else {
    die();
}

/**
 * Validate LINE signature
 */
$signature = base64_encode(hash_hmac('sha256', $events, $sharedpanel->line_channel_secret, true));
if (!$_SERVER['X-Line-Signature'] === $signature) {
    die();
}

/**
 * Decode JSON
 */
$events = json_decode($events);

/**
 * Create card
 */
if ($events->{'events'}[0]->{'type'} === 'message') {
    $message_id = $events->{'events'}[0]->{'message'}->{'id'};
    $message_userid = $events->{'events'}[0]->{'source'}->{'userId'};
    $message_replytoken = $events->{'events'}[0]->{'replyToken'};

    $httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($sharedpanel->line_channel_access_token);
    $bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $sharedpanel->line_channel_secret]);
    $response = $bot->getMessageContent($message_id);
    if (!$response->isSucceeded()) {
        die();
    }

    switch ($events->{'events'}[0]->{'type'}) {
        case 'message':
            $message_text = $events->{'events'}[0]->{'message'}->{'text'};
            $cardObj = new card($sharedpanel);
            $cardObj->add_card($message_text, $message_userid, 'line', $message_replytoken);

            $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder('カードを作成しました。');
            $response = $bot->replyMessage($message_replytoken, $textMessageBuilder);

            break;

        case 'image' :
            $fs = get_file_storage();

            $response = $bot->getMessageContent($message_id);
            if ($response->isSucceeded()) {
                $tempfile = tmpfile();
                fwrite($tempfile, $response->getRawBody());

                $filerecord = array(
                    'contextid' => $context->id,
                    'component' => 'mod_sharedpanel',
                    'filearea' => 'attachment',
                    'itemid' => $message_id,
                    'filepath' => '/',
                    'filename' => 'attacnhemt.jpg',
                    'userid' => 1
                );

                $fs->create_file_from_string($filerecord, $tempfile);

                $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder('画像を投稿しました。');
                $response = $bot->replyMessage($message_replytoken, $textMessageBuilder);
            } else {
                $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder('画像投稿に失敗しました。');
                $response = $bot->replyMessage($message_replytoken, $textMessageBuilder);

                error_log($response->getHTTPStatus() . ' ' . $response->getRawBody());
            }
            break;
    }
}

die();