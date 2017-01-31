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

/**
 * @package    mod_sharedpanel
 * @copyright  2017 NAGAOKA Chikako, KITA Toshihiro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//ini_set('display_errors',1);

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
 
$id = optional_param('id', 0, PARAM_INT); // course module id
$c  = optional_param('c', 0, PARAM_INT);  // ... card ID
 
if ($id) {
    $cm         = get_coursemodule_from_id('sharedpanel', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $sharedpanel  = $DB->get_record('sharedpanel', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}
 
require_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);

/*
$event = \mod_sharedpanel\event\course_module_viewed::create(array(
    'objectid' => $PAGE->cm->instance,
    'context' => $PAGE->context,
));
$event->add_record_snapshot('course', $PAGE->course);
// In the next line you can use $PAGE->activityrecord if you have set it, or skip this line if you don't have a record.
$event->add_record_snapshot($PAGE->cm->modname, $activityrecord);
$event->trigger();
*/

$config = get_config('sharedpanel');

$client_id = $config->FBappID;
$client_secret = $config->FBsecret;
// $redirectUrl = $config->FBredirectUrl;
// $token = $config->FBtoken;
$Groupid= $sharedpanel->fbgroup1;
$redirect_uri = $CFG->wwwroot.'/mod/sharedpanel/facebook.php?id='.$id;

$code = $_REQUEST['code'];

if (!$code) {
//  Graph APIをコール
    header('Location: https://graph.facebook.com/oauth/authorize'
	               . '?client_id=' . $client_id
	               . '&scope=user_managed_groups'
	               . '&redirect_uri=' . urlencode($redirect_uri));
    exit(0);		       
} else {
//  アクセストークン取得用のURLを生成
    $token_url = 'https://graph.facebook.com/oauth/access_token' 
	           . '?client_id=' . $client_id
	           . '&redirect_uri=' . urlencode($redirect_uri) 
	           . '&client_secret=' . $client_secret
	           . '&code=' . $code;

//  アクセストークン取得
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $token_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $token = curl_exec($ch);

    $url = "https://graph.facebook.com/v2.8/".$Groupid."/feed?fields=id,name,created_time,updated_time,message,from,picture,link&".$token."&limit=25";
    $ret0 = json_decode(file_get_contents($url));
    $retdata = $ret0->data;    

// Print the page header.
 
    $PAGE->set_url('/mod/sharedpanel/deletecard.php', array('id' => $cm->id));
    $PAGE->set_title(format_string($sharedpanel->name));
    $PAGE->set_heading(format_string($course->fullname));
    $PAGE->set_context($context);
 
/*
 * Other things you may want to set - remove if not needed.
 * $PAGE->set_cacheable(false);
 * $PAGE->set_focuscontrol('some-html-id');
 * $PAGE->add_body_class('sharedpanel-'.$somevar);
 */ 
// Output starts here.
    echo $OUTPUT->header();

    // echo "<pre>"; var_dump($ret); echo "</pre>";  // debug
}

echo "<br/>importing facebook ($Groupid) ... <br/>";  ob_flush(); flush();

$n2 = count($retdata);

for ($index = 0; $index < $n2; $index++){
    $content="";
    $ret = $retdata[$index];
    
    // echo "index ".$index."/".$n2."\n"; var_dump($ret);   // debug
    if($ret->message){
        if ($ret->picture){
            $content.= "<a href='".$ret->link."' target='_blank'><img src=".$ret->picture." width=250px></a><br>\n<br>";
        }
        $content.= $ret->message."<br/>";
    }else{
        continue;
    }

   // DBにあるカードと重複していれば登録しない（次の投稿の処理へ）
    $samecard = $DB->get_record('sharedpanel_cards', array('timeposted' => strtotime($ret->created_time), 'inputsrc' => 'facebook','sharedpanelid' => $sharedpanel->id,'hidden' => 0));
    if ($samecard != null){
        echo "Not imported : same facebook post by ".$ret->from->name." (".$ret->created_time.")<br>\n";
    ob_flush(); flush();
        continue;
    }
		
// DBにカードを追加
    $data = new stdClass;
    $data->sharedpanelid = $sharedpanel->id;
    $data->userid = $USER->id;
    $data->timeposted= strtotime($ret->created_time);
    $data->timecreated = time();
    $data->timemodified = $data->timecreated;
    $data->inputsrc = "facebook";
    $data->sender = $ret->from->name;
    $data->content = $content;
    $data->id = $DB->insert_record('sharedpanel_cards', $data);

// DBにタグを追加
    $tag = new stdClass;
    $tag->cardid = $data->id;
    $tag->userid = $USER->id;
    $tag->timecreated = $data->timecreated;
    $tag->tag = $Groupid;
    $tag->id = $DB->insert_record('sharedpanel_card_tags', $tag);
    echo "A card imported from facebook : a post by ".$ret->from->name."(".$ret->created_time.")<br>\n";
    ob_flush(); flush();
}

echo "<br/><a href=\"./view.php?id=$id\"><span style='background-color:orange;padding:1ex;color:black;'><b>Importing done.</b></span></a><br/>"; ob_flush(); flush();

// Finish the page.
echo $OUTPUT->footer();
