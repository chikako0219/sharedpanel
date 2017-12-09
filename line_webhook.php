<?php

namespace mod_sharedpanel;

global $DB;

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');

$id = required_param('id', PARAM_INT);

$events = file_get_contents('php://input');

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

$message_text = $events->{'events'}[0]->{'message'}->{'text'};
$message_userid = $events->{'events'}[0]->{'source'}->{'userId'};
$message_replytoken = $events->{'events'}[0]->{'replyToken'};

$cardObj = new card($sharedpanel);
$cardObj->add_card($message_text, $message_userid, 'line', $message_replytoken);

die();