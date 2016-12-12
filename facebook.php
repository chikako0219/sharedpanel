<?php
//error_reporting(E_ALL);
//ini_set( 'display_errors', 1 );

// Facebook PHP SDK関連ファイルの読込
//define('FACEBOOK_SDK_V4_SRC_DIR', '/var/www/html/moodle/mod/sharedpanel/facebook/src/Facebook/');
define('FACEBOOK_SDK_V4_SRC_DIR', __DIR__ . '/facebook/src/Facebook/');
require __DIR__ . '/facebook/src/Facebook/autoload.php';
use Facebook\FacebookClient;

//関数の作成
function add_card_from_facebook($sharedpanel){
  global $DB, $USER;

  $fbgroup1= $sharedpanel->fbgroup1;
  echo "<br/><hr>importing facebook ($fbgroup1) ... ";  ob_flush(); flush();

//echo '<h2> Facebook API Testプログラム (PHP) 2015/09/10</h2>';
//echo "* 初期処理　開始<br>\n";


$config = get_config('sharedpanel');

// Facebook App情報&アクセストークンの設定
$appId =  $config->FBappID;
$secret = $config->FBsecret;
$redirectUrl = $config->FBredirectUrl;
$token = $config->FBtoken;
/*
$appId = 'your App id';
$secret = 'your App secret';
$redirectUrl = 'your redirect URI';
$pageid = 'FACEBOOK PAGE ID';
$token = 'your Access Token';
*/

//echo "* 初期処理　終了<br>\n";

// Facebook アクセスクラスの初期化
//echo "* FBクラス初期化　開始<br>\n";
$fb = new Facebook\Facebook([
  'app_id' => $appId,
  'app_secret' => $secret,
  'default_graph_version' => 'v2.5',
  'default_access_token' => $token
  // . . .
  ]);
//echo "IsSet => ", isset($fb),"<br>";
//echo "* FBクラス初期化　終了<br>\n";
//echo "* FB.api　開始<br>\n";
$fbApp = $fb->getApp();
//echo "IsSet => ", isset($fbApp),"<br>";
//echo "* FB.api　終了 xxxxxxxxxxxx <br>\n";

//echo "* Send reqest 開始<br>\n";

// リクエスト用条件設定
$reqLimit = 10;
$timeS = strtotime("2015-08-18");
$Groupid = $fbgroup1;
// $Groupid = "551028761722859";
$OrgreqStr = "/".$Groupid ."/feed?fields=id,from,message,actions,child_attachments,application,likes, link,object_id,message_tags,created_time,updated_time,comments,picture,source,story,story_tags&limit=".$reqLimit."&since=".$timeS;

//リクエスト実施
$reqStr = $OrgreqStr;
$request = $fb->request('GET',$reqStr);


try {
  $response = $fb->getClient()->sendRequest($request);
} catch(Facebook\Exceptions\FacebookResponseException $e) {
  echo 'Graph returned an error: ' . $e->getMessage();
  echo "<br>\n";
} catch(Facebook\Exceptions\FacebookSDKException $e) {
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
  echo "<br>\n";
}

//echo "* Send reqest 終了)<br>\n";

// リクエスト結果の表示
if (isset($response)){
//	echo "<br>Get response <br>\n";

	$arrayResult = (array)$response;
	
// Feed結果全体の表示	
//	$n = count($arrayResult);

	//echo "<pre>\n";
	//echo "************* Feed結果全体の表示。**************\n"; 
    //echo "要素数は{$n}個です。\n";
	//echo "<br>\n";
    //var_dump($response);
	//echo "<br>\n";
	//echo "</pre>\n";

	// メッセージ内容の取り出しと表示	

	//echo "<pre>\n";
	//echo "************* メッセージ内容の取り出しと表示の表示。**************\n"; 

	//echo "<br>\n";
	
	// 現状トリッキーな方法で ["decodedBody":protected]を取り出しています。
	// 本当は["decodedBody":protected]文字列をダンプで調べてdatakey$で使用すべき 
	$bodyResult = array_slice($arrayResult,3, 1, true);
	$dataky = key($bodyResult);
	$dataResult = $bodyResult[$dataky]["data"];
	$n2 = count($dataResult);
	//echo "メッセージ数は{$n2}個です。<br>\n";
	
	for ($index = 0; $index < $n2; $index++){
		$ret1="";
		// echo "index ={$index}<br>";
		//echo "<br>****** {$index}番目のメッセージ<br>\n";
		$data01Result = $dataResult[$index];
		if($data01Result["message"]){
			//下記の部分はview.phpファイルで定義されているので，削除（2016.07.21）
			//echo '<div-facebook>';
			//echo "Message ID = ".$data01Result["id"]."<br>\n";
			// $ret1.= $data01Result["from"] ["name"]."<br>\n<br>";
			// $ret1.= "<hr><br>";
			if ($data01Result["picture"]){
                          $ret1.= "<a href='".$data01Result["link"]."' target='_blank'><img src=".$data01Result["picture"]." width=250px></a><br>\n<br>";
                        }
			$ret1.= $data01Result["message"]."<br/>";
			//clickableにしておく（linkのリンク）
			//echo $data01Result["link"]."<br>\n";
			//$ret1.= $data01Result["created_time"]."<br>\n<br><br>from Facebook";
      		//下記の部分はview.phpファイルで定義されているので，削除（2016.07.21）
			//echo '</div-facebook>';
		}
		else{
			//echo "<br>****** メッセージ本文を含まない投稿です<br>\n";
			continue;
		}

   // DBにあるカードと重複していれば登録しない（次の投稿の処理へ）
    $samecard = $DB->get_record('sharedpanel_cards', array('timeposted' => strtotime($data01Result["created_time"]), 'inputsrc' => 'facebook','sharedpanelid' => $sharedpanel->id,'hidden' => 0));
    if ($samecard != null){
      echo "Not imported : same facebook post by ".$data01Result["from"]["name"]." (".$data01Result["created_time"].")<br>\n";
      ob_flush(); flush();
      continue;
    }
		
	// DBにカードを追加
	$data = new stdClass;
        $data->sharedpanelid = $sharedpanel->id;
        $data->userid = $USER->id;
        $data->timeposted= strtotime($data01Result["created_time"]);
        $data->timecreated = time();
        $data->timemodified = $data->timecreated;
        $data->inputsrc = "facebook";
        $data->sender = $data01Result["from"]["name"];
        $data->content = $ret1;
        $data->id = $DB->insert_record('sharedpanel_cards', $data);

	// DBにタグを追加
        $tag = new stdClass;
        $tag->cardid = $data->id;
        $tag->userid = $USER->id;
        $tag->timecreated = $data->timecreated;
        $tag->tag = $Groupid;
        $tag->id = $DB->insert_record('sharedpanel_card_tags', $tag);
        echo "A card imported from facebook : a post by ".$data01Result["from"]["name"]."(".$data01Result["created_time"].")<br>\n";
        ob_flush(); flush();
		
	}
}
else{
	echo "Not get response <br>\n";
}

}

//