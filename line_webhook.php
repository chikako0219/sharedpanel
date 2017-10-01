<?php
//ini_set( 'display_errors', 1 );

// usausa 
$channel_id = "1497560902";
$channel_secret = "b1d1e56c304f692c99dfd4847a8d6d56";
$channel_access_token = "Dq5rFpweKOLBh48WssCdx/fsGZ7ANT77eEV1u1XpT1KeNh+SIbg8pXo+43Tm+3M3mrDZOqHytP99bw/TDiTbDTfT3/U4Mf0r1UQSgETPvhaM5/atsAge2/iwvROV4P5cJ1gM5PY1rLZKjzW41AmZNgdB04t89/1O/w1cDnyilFU=";

/*
// SharedPanel_test
$channel_id = "1493987887";
$channel_secret = "da482dcd4dc7ad21aa1350e9b1b6b481";
$channel_access_token = "NhzNa7+2OlQ2B/nAQnMTcKOlSX0zZEAsCuHYmYqimQymEug4flOWqVNQWevSwic8U0KgVKT1BaPfVvwjQAySAsh2ShzJiUKfS1kk9Qa4JtTau/czY/YJrP53XRE7WjjZuPvX4jNzDphlkwaWPL+yFQdB04t89/1O/w1cDnyilFU=";
*/

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
mkdir($CFG->dataroot.'/sharedpanel/');
mkdir($CFG->dataroot.'/sharedpanel/line/');
$dir= $CFG->dataroot.'/sharedpanel/line/';

//file_put_contents('/tmp/line-debug', date("c")."\n");

$json_string = file_get_contents('php://input'); 
$json_object = json_decode($json_string,1);

$content = $json_object['events'][0];
$message_text = $content['message']['text'];
$message_id = $content['message']['id'];
$content_type = $content['message']['type'];

//$timestamp= $content['timestamp'];
// 全部で13桁。最初の10桁のみ使う。残り3桁は？
$timestamp= substr($content['timestamp'],0,10);

$userid= $content['source']['userId'];
 
if (in_array($content_type, array("image", "", 4))) {
//    api_get_message_content_request($message_id);  
    $url = "https://api.line.me/v2/bot/message/".$message_id."/content";
    $headers = array(
        "Content-Type: application/json",
        'ChannelId' . $GLOBALS['channel_id'],
        'ChannelSecret' . $GLOBALS['channel_secret'],
        'Authorization: Bearer ' . $GLOBALS['channel_access_token']
    ); 
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    $output = curl_exec($curl);
    file_put_contents($dir.$timestamp."-".$message_id."-".$userid.".image", $output);
}else{
    file_put_contents($dir.$timestamp."-".$message_id."-".$userid.".message", $message_text);
}
