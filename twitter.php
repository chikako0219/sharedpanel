<?php
// Twitter ------------------------------------------

require_once("locallib.php");

function add_card_from_twitter($sharedpanel){
  global $DB, $USER;

  $maxnumatatime = 10;  // only $maxnumatatime tweets are imported at a time
//  $postinguserinfo = true;
  $postinguserinfo = false;

  $andkey= $sharedpanel->hashtag1;
  echo "<br/><hr>importing twitter ($andkey) ...  ";  ob_flush(); flush();

  //TwitterAPI利用の承認情報
  require_once("twitteroauth.php");
  $consumerKey = "your consumer key";
  $consumerSecret = "your consumer secret";
  $accessToken = "your access token";
  $accessTokenSecret = "your access token secret";
 
  $twObj = new TwitterOAuth($consumerKey,$consumerSecret,$accessToken,$accessTokenSecret);
  $options = array('q'=>$andkey,'count'=>'100', "include_entities"=>true);
  $json = $twObj->OAuthRequest(
    "https://api.twitter.com/1.1/search/tweets.json",
    "GET",
    $options
  );
  $jset = json_decode($json, true);

// 古いTwitterの情報は取れないようだ。 t-kita

//  echo "<pre>"; var_dump($jset); echo "</pre>";   // debug
 
  $co= 0;
  foreach ($jset['statuses'] as $result){
   $ret1 = "";
   $name = $result['user']['name'];
   $link = $result['user']['profile_image_url'];
   $photo = $result['entities']['media'][0]['media_url'];
   $content = $result['text'];
   // 絵文字などを変換してDBエラーにならないようにする
   $content= utf8mb4_encode_numericentity($content);
   // URI を link に
   // http://www.phppro.jp/qa/688 
   $pat_sub= preg_quote('-._~%:/?#[]@!$&\'()*+,;=', '/');
   $pat= '/((http|https):\/\/[0-9a-z' . $pat_sub . ']+)/i';
   $rep= '<a href="\\1" target="_BLANK">\\1</a>';
   $content = preg_replace ($pat, $rep, $content);
   $updated = $result['created_at'];
   $time = date("Y-m-d H:i:s",strtotime($updated));

   if (preg_match('/^RT/',$content)){
     echo "Retweets not imported : by $name ($time).<br>\n";  ob_flush(); flush();
     continue;
   }  // ignore retweets

   if ($postinguserinfo){
     $ret1 .= "<img src='data:image/gif;base64,".base64_encode(file_get_contents($link))."'>"." ".$name.'<br>'.'<hr>';
   }
   if(empty($photo)){
//     $ret1 .= '<br>'.$content.'<br>'.$time.'<br>'.'<br>'."(search key = $andkey)<br> from Twitter";
     $ret1 .= '<br>'.$content.'<br>'.'<br>'."(search key = $andkey)";
   }else{
//     $ret1 .= "<img src='data:image/gif;base64,".base64_encode(file_get_contents($photo))."' width=250px>".'<br>'.$content.'<br>'.$time.'<br>'.'<br>'."(search key = $andkey)<br> from Twitter";
     $ret1 .= "<img src='data:image/gif;base64,".base64_encode(file_get_contents($photo))."' width=250px>".'<br>'.$content.'<br>'.'<br>'."(search key = $andkey)";
   }

   // DBにあるカードと重複していれば登録しない（次の投稿の処理へ）
    $samecard = $DB->get_record('sharedpanel_cards', array('timeposted' => strtotime($updated),'inputsrc' => 'twitter','sharedpanelid' => $sharedpanel->id,'hidden' => 0));
    if ($samecard != null){ 
      echo "Not imported : same tweet by $name ($time).<br>\n";	ob_flush(); flush();

      continue;
    }

   // DBにカードを追加
   $data = new stdClass;
   $data->sharedpanelid = $sharedpanel->id;
   $data->userid = $USER->id;
   $data->timeposted= strtotime($updated);
   $data->timecreated  = time();
   $data->timemodified = $data->timecreated;
   $data->inputsrc = "twitter";
   $data->content = $ret1;
   $data->sender  = $name; // ."<img src='$link'>";

   $data->id = $DB->insert_record('sharedpanel_cards', $data);

   foreach( get_tags($content) as $tagstr ){
     // DBにタグを追加
      $tag = new stdClass;
      $tag->cardid = $data->id;
      $tag->userid = $USER->id;
      $tag->timecreated = $data->timecreated;
      $tag->tag = $tagstr;
      $tag->id = $DB->insert_record('sharedpanel_card_tags', $tag);
   }
   echo "A card imported from twitter : a tweet by $name ($time).<br>\n"; ob_flush(); flush();

   $co++;
   if ($co >= $maxnumatatime){
     echo "Only $maxnumatatime tweets are imported at a time. Quitting.\n";
     break;
   }
  }  // foreach ($jset['statuses'] as $result)

}



// UTF-8 の4バイト文字を HTML 数値文字参照に変換する
// MySQL 5.5 で導入された utf8mb4 を使えない場合
// http://qiita.com/masakielastic/items/ec483b00ff6337a02878

function utf8mb4_encode_numericentity($str)
{
    $re = '/[^\x{0}-\x{FFFF}]/u';
    return preg_replace_callback($re, function($m) {
        $char = $m[0];
        $x = ord($char[0]);
        $y = ord($char[1]);
        $z = ord($char[2]);
        $w = ord($char[3]);
        $cp = (($x & 0x7) << 18) | (($y & 0x3F) << 12) | (($z & 0x3F) << 6) | ($w & 0x3F);
        return sprintf("&#x%X;", $cp);
    }, $str);
}

function utf8mb4_decode_numericentity($str)
{
    $re = '/&#(x[0-9a-fA-F]{5,6}|\d{5,7});/';
    return preg_replace_callback($re, function($m) {
        return html_entity_decode($m[0]);
    }, $str);
}
