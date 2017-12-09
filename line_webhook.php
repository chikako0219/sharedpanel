<?php

namespace mod_sharedpanel;

global $DB;

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');

$id = required_param('id', PARAM_INT);
$events = required_param('events', PARAM_RAW);
$events = json_decode($events);

$sharedpanel = null;
if ($id) {
    $sharedpanel = $DB->get_record('sharedpanel', ['id' => $id]);
} else {
    die();
}

/**
 * Validate LINE signature
 */
$signature = base64_encode(hash_hmac('sha256', $events, $sharedpanel->line_channel_secret, true));
$haeders = getallheaders();

if (!$haeders['X-Line-Signature'] === $signature) {
    die();
}

/**
 * Create card
 */
$cardObj = new card($sharedpanel);
$cardObj->add_card($events['message']['text'], $events['source']['userId'], 'line', $events['replyToken']);

die();