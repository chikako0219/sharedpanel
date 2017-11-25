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
//
/**
 * @package    mod_sharedpanel
 * @copyright  2016 NAGAOKA Chikako, KITA Toshihiro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
//
// Twitter ------------------------------------------

require_once("locallib.php");

function add_card_from_twitter($sharedpanel) {
    global $DB, $USER;

    $maxnumatatime = 10;  // only $maxnumatatime tweets are imported at a time
//  $postinguserinfo = true;
    $postinguserinfo = false;

    $andkey = $sharedpanel->hashtag1;
    echo "<br/><hr>importing twitter ($andkey) ...  ";
    ob_flush();
    flush();


    $config = get_config('sharedpanel');

    //TwitterAPI利用の承認情報
    require_once("twitteroauth.php");
    $consumerKey = $config->TWconsumerKey;
    $consumerSecret = $config->TWconsumerSecret;
    $accessToken = $config->TWaccessToken;
    $accessTokenSecret = $config->TWaccessTokenSecret;

    $twObj = new TwitterOAuth($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
    $options = array('q' => $andkey, 'count' => '100', "include_entities" => true);
    $json = $twObj->OAuthRequest(
        "https://api.twitter.com/1.1/search/tweets.json",
        "GET",
        $options
    );
    $jset = json_decode($json, true);

// 古いTwitterの情報は取れないようだ。 t-kita

    // echo "<pre>"; var_dump($jset); echo "</pre>";   // debug

    $co = 0;
    foreach ($jset['statuses'] as $result) {
        $ret1 = "";
        $name = $result['user']['name'];
        $link = $result['user']['profile_image_url'];
        $photo = $result['entities']['media'][0]['media_url'];
        $content = $result['text'];
        // 絵文字などを変換してDBエラーにならないようにする
        $content = mod_sharedpanel_utf8mb4_encode_numericentity($content);
        // URI を link に
        // http://www.phppro.jp/qa/688
        $pat_sub = preg_quote('-._~%:/?#[]@!$&\'()*+,;=', '/');
        $pat = '/((http|https):\/\/[0-9a-z' . $pat_sub . ']+)/i';
        $rep = '<a href="\\1" target="_BLANK">\\1</a>';
        $content = preg_replace($pat, $rep, $content);
        $updated = $result['created_at'];
        $time = date("Y-m-d H:i:s", strtotime($updated));

        /*
           if (preg_match('/^RT/',$content)){
             echo "Retweets not imported : by $name ($time).<br>\n";  ob_flush(); flush();
             continue;
           }  // ignore retweets
        */

        if ($postinguserinfo) {
            $ret1 .= "<img src='data:image/gif;base64," . base64_encode(file_get_contents($link)) . "'>" . " " . $name . '<br>' . '<hr>';
        }
        if (empty($photo)) {
//     $ret1 .= '<br>'.$content.'<br>'.$time.'<br>'.'<br>'."(search key = $andkey)<br> from Twitter";
            $ret1 .= '<br>' . $content . '<br>' . '<br>' . "(search key = $andkey)";
        } else {
//     $ret1 .= "<img src='data:image/gif;base64,".base64_encode(file_get_contents($photo))."' width=250px>".'<br>'.$content.'<br>'.$time.'<br>'.'<br>'."(search key = $andkey)<br> from Twitter";
            $ret1 .= "<img src='data:image/gif;base64," . base64_encode(file_get_contents($photo)) . "' width=250px>" . '<br>' . $content . '<br>' . '<br>' . "(search key = $andkey)";
        }

        // DBにあるカードと重複していれば登録しない（次の投稿の処理へ）
        $samecard = $DB->get_record('sharedpanel_cards', array('timeposted' => strtotime($updated), 'inputsrc' => 'twitter', 'sharedpanelid' => $sharedpanel->id, 'hidden' => 0));
        if ($samecard != null) {
            echo "Not imported : same tweet by $name ($time).<br>\n";
            ob_flush();
            flush();

            continue;
        }

        // DBにカードを追加
        $data = new stdClass;
        $data->sharedpanelid = $sharedpanel->id;
        $data->userid = $USER->id;
        $data->timeposted = strtotime($updated);
        $data->timecreated = time();
        $data->timemodified = $data->timecreated;
        $data->inputsrc = "twitter";
        $data->content = $ret1;
        $data->sender = $name; // ."<img src='$link'>";

        $data->id = $DB->insert_record('sharedpanel_cards', $data);

        foreach (mod_sharedpanel_get_tags($content) as $tagstr) {
            // DBにタグを追加
            $tag = new stdClass;
            $tag->cardid = $data->id;
            $tag->userid = $USER->id;
            $tag->timecreated = $data->timecreated;
            $tag->tag = $tagstr;
            $tag->id = $DB->insert_record('sharedpanel_card_tags', $tag);
        }
        echo "A card imported from twitter : a tweet by $name ($time).<br>\n";
        ob_flush();
        flush();

        $co++;
        if ($co >= $maxnumatatime) {
            echo "Only $maxnumatatime tweets are imported at a time. Quitting.\n";
            break;
        }
    }  // foreach ($jset['statuses'] as $result)

}



