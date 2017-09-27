<html>
<head>
    <meta http-equiv="Content-Type"
          content="text/html; charset=utf-8">


    <style type="text/css">
        <!--

        div1 {
            word-wrap: break-word;
            width: 260px;
            height: 400px;
            background-color: #ddeeff;
            font-size: small;
            margin-right: 10px;
            float: left;
            padding: 10px;
            margin-bottom: 10px;
            border: #29088A;
            overflow: scroll;
            border-style: solid;
            border-color: #29088A;
        }

        -->

    </style>

</head>

<?php

ini_set('display_errors', 1);
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');

$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$n = optional_param('n', 0, PARAM_INT);  // ... sharedpanel instance ID - it should be named as the first character of the module.

if ($id) {
    $cm = get_coursemodule_from_id('sharedpanel', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $sharedpanel = $DB->get_record('sharedpanel', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($n) {
    $sharedpanel = $DB->get_record('sharedpanel', array('id' => $n), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $sharedpanel->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('sharedpanel', $sharedpanel->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);
//$context = context_module::instance($cm->id);

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

// Print the page header.

$PAGE->set_url('/mod/sharedpanel/view.php', array('id' => $cm->id));
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

// Conditions to show the intro can change to look for own settings or whatever.
/*
if ($sharedpanel->intro) {
    echo $OUTPUT->box(format_module_intro('sharedpanel', $sharedpanel, $cm->id), 'generalbox mod_introbox', 'sharedpanelintro');
}
*/

// Replace the following lines with you own code.
//echo $OUTPUT->heading('Yay! It works!');


// 文字列の挿入
//----------------------------------------------------------------------------
$andkey = $_POST['Mojiretsu1'];
$ret1 = "";

// プルダウンメニュー表示/
//----------------------------------------------------------------------------
// ファイル名を取得、配列に格納する
$dir = ($CFG->dataroot . '/sharedpanel/');
$files1 = scandir($dir);

//配列の中身確認用
//for ($i=0; $i < count($files1); $i++) { 
//echo "<option>".$files1[$i]."</option>";
//}

// 選択リストの値を取得
$name = "menu1";
$selected_value = $_POST[$name];

// 選択リストの要素を配列に格納 → この配列からドロップダウンリストを作成
echo '</br>  Please select the past files';
$ar_menu1 = $files1;

// 配列から選択リストを作成する関数
// パラメータ：配列／選択リスト名／選択値
function disp_list($array, $name, $selected_value = "") {
    echo '<select name="' . $name . '">';
    while (list($value, $text) = each($array)) {
        echo "<option ";
        if ($selected_value == $value) {
            echo " selected ";
        }
        echo ' value="' . $value . '">' . $text . "</option>";
    }
    echo "</select>";
}


//----------------------------------------------------------------------------


//検索キーワードの取得
echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">';
disp_list($ar_menu1, $name, $selected_value);
echo '<br><br>Please enter keywords: <br><input type="text" name="Mojiretsu1"><br>';
echo '<input type="hidden" name="id" value="' . $id . '">';
echo '<input type="submit" name="submit" value="Enter"></form><br/><br/>';
echo '<p>You are researching about:' . $andkey . '</p>';
echo '<br>';
echo '<input type="button" value="印刷する" onclick="window.print()">';
echo '<br>';


//エラー発見コード
//ini_set( 'display_errors', 1 );// debug

//TwitterAPI利用の承認情報
require_once("twitteroauth.php");
$consumerKey = "yr18wDxGUDDaF2t68qLCmIYOm";
$consumerSecret = "OZoNAoMckytTqAngAHZsLysgfvpiGwFb2W5Ath0ssEjGinwhaA";
$accessToken = "2896286647-PrAn6gmKrimrI9kYLdL32Fjq6EsnW3FWVnHkQA8";
$accessTokenSecret = "HE0D6IwJ7KhH0bFvJ4elcezjltwcs5mXK8cBP5drul20U";

$twObj = new TwitterOAuth($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);

$options = array('q' => $andkey, 'count' => '9', "include_entities" => true);


$json = $twObj->OAuthRequest(
    "https://api.twitter.com/1.1/search/tweets.json",
    "GET",
    $options
);

$jset = json_decode($json, true);

foreach ($jset['statuses'] as $result) {
    $name = $result['user']['name'];
    $link = $result['user']['profile_image_url'];
    $photo = $result['entities']['media'][0]['media_url'];
    $content = $result['text'];
    $updated = $result['created_at'];
    $time = $time = date("Y-m-d H:i:s", strtotime($updated));

    if (empty($photo)) {
        $ret1 .= '<div style="background-color:#F0FFFF; margin-right: 10px; float: left; overflow: hidden; width: 260px; height: 400px; padding: 10px; margin-bottom: 10px; border: 1px dotted #87CEEB;">';
        $ret1 .= "<img src='data:image/gif;base64," . base64_encode(file_get_contents($link)) . "'>" . " " . $name . '<br>' . '<hr>' . '<br>' . $content . '<br>' . $time . '<br>' . '<br>' . "from Twitter";
        $ret1 .= '</div>';
    } else {
        $ret1 .= '<div style="background-color:#F0FFFF; margin-right: 10px; float: left; overflow: hidden; width: 260px; height: 400px; padding: 10px; margin-bottom: 10px; border: 1px dotted #87CEEB;">';
        $ret1 .= "<img src='data:image/gif;base64," . base64_encode(file_get_contents($link)) . "'>" . " " . $name . '<br>' . '<hr>' . "<img src='data:image/gif;base64," . base64_encode(file_get_contents($photo)) . "' width=150px>" . '<br>' . $content . '<br>' . $time . '<br>' . '<br>' . "from Twitter";
        $ret1 .= '</div>';
        //echo '<br>';
    }
}

mkdir($CFG->dataroot . '/sharedpanel/');
$outfile = $CFG->dataroot . '/sharedpanel/' . date("Y-md-His") . $andkey;
file_put_contents($outfile, $ret1);

$ret2 = file_get_contents($outfile);

echo $ret2;


//メール ----------------------------------------------------------------------------
//エラー表示
ini_set('display_errors', 1);


// 必要な定数を設定
define('GMAIL_HOST', 'imap.googlemail.com');
define('GMAIL_PORT', 993);
define('GMAIL_ACCOUNT', 'info.officesakura@gmail.com');
define('GMAIL_PASSWORD', 'sakura00');
define('SERVER', '{' . GMAIL_HOST . ':' . GMAIL_PORT . '/novalidate-cert/imap/ssl}');

// メールボックスへの IMAP ストリームをオープン
if (($mbox = imap_open(SERVER . "INBOX", GMAIL_ACCOUNT, GMAIL_PASSWORD)) == false) {
    // 失敗処理を記述...
}
// メールボックスの情報を取得
$mboxes = imap_mailboxmsginfo($mbox);

// メッセージ数の有無
if ($mboxes->Nmsgs != 0) {
    // 情報を格納する変数を初期化
    $mail = null;
    for ($mailno = 1; $mailno <= $mboxes->Nmsgs; $mailno++) {
        // ヘッダー情報の取得
        $head = imap_header($mbox, $mailno);
        // アドレスの取得
        $mail[$mailno]['address'] = $head->from[0]->mailbox . '@' . $head->from[0]->host;
        // タイトルの有無
        if (!empty($head->subject)) {
            // タイトルをデコード
            $mhead = imap_mime_header_decode($head->subject);
            foreach ($mhead as $key => $value) {
                if ($value->charset != 'default') {
                    $mail[$mailno]['subject'] = mb_convert_encoding($value->text, 'utf-8', $value->charset);
                } else {
                    $mail[$mailno]['subject'] = $value->text;
                }
            }
        } else {
            // タイトルがない場合の処理を記述...
        }
        // 格納用変数の初期化
        $charset = null;
        $encoding = null;
        $attached_data = null;
        $parameters = null;
        // メール構造を取得
        $info = imap_fetchstructure($mbox, $mailno);
        if (!empty($info->parts)) {
            // 
            $parts_cnt = count($info->parts);
            for ($p = 0; $p < $parts_cnt; $p++) {
                // タイプにより処理を分ける
                // [参考] http://www.php.net/manual/ja/function.imap-fetchstructure.php
                if ($info->parts[$p]->type == 0) {
                    if (empty($charset)) {
                        $charset = $info->parts[$p]->parameters[0]->value;
                    }
                    if (empty($encoding)) {
                        $encoding = $info->parts[$p]->encoding;
                    }
                } elseif (!empty($info->parts[$p]->parts) && $info->parts[$p]->parts[$p]->type == 0) {
                    $parameters = $info->parts[$p]->parameters[0]->value;
                    if (empty($charset)) {
                        $charset = $info->parts[$p]->parts[$p]->parameters[0]->value;
                    }
                    if (empty($encoding)) {
                        $encoding = $info->parts[$p]->parts[$p]->encoding;
                    }
                } elseif ($info->parts[$p]->type == 5) {
                    $files = imap_mime_header_decode($info->parts[$p]->dparameters[0]->value);
                    if (!empty($files) && is_array($files)) {
                        $attached_data[$p]['file_name'] = null;
                        foreach ($files as $key => $file) {
                            if ($file->charset != 'default') {
                                $attached_data[$p]['file_name'] .= mb_convert_encoding($file->text, 'utf-8', $file->charset);
                            } else {
                                $attached_data[$p]['file_name'] .= $file->text;
                            }
                        }
                    }
                    $attached_data[$p]['content_type'] = $info->parts[$p]->subtype;
                }
            }
        } else {
            $charset = $info->parameters[0]->value;
            $encoding = $info->encoding;
        }
        if (empty($charset)) {
            // エラー処理を記述...
        }
        // 本文を取得
        $body = imap_fetchbody($mbox, $mailno, 1, FT_INTERNAL);
        $body = trim($body);
        if (!empty($body)) {
            // タイプによってエンコード変更
            switch ($encoding) {
                case 0 :
                    $mail[$mailno]['body'] = mb_convert_encoding($body, "utf-8", $charset);
                    break;
                case 1 :
                    $encode_body = imap_8bit($body);
                    $encode_body = imap_qprint($encode_body);
                    $mail[$mailno]['body'] = mb_convert_encoding($encode_body, "utf-8", $charset);
                    break;
                case 3 :
                    $encode_body = imap_base64($body);
                    $mail[$mailno]['body'] = mb_convert_encoding($encode_body, "utf-8", $charset);
                    break;
                case 4 :
                    $encode_body = imap_qprint($body);
                    $mail[$mailno]['body'] = mb_convert_encoding($encode_body, 'utf-8', $charset);
                    break;
                case 2 :
                case 5 :
                default:
                    // エラー処理を記述...
                    break;
            }
        } else {
            // エラー処理を記述...
        }

        // 添付を取得

        if (!empty($attached_data)) {
            foreach ($attached_data as $key => $value) {
                $attached = imap_fetchbody($mbox, $mailno, $key + 1, FT_INTERNAL);
                if (empty($attached)) break;
                // ファイル名を一意の名前にする(同じファイルが存在しないように)
                list($name, $ex) = explode('.', $value['file_name']);
                $mail[$mailno]['attached_file'][$key]['file_name'] = $name . '_' . time() . '_' . $key . '.' . $ex;
                //$mail[$mailno]['attached_file'][$key]['image']=imap_base64($attached);
                $mail[$mailno]['attached_file'][$key]['imageb'] = $attached;
                $mail[$mailno]['attached_file'][$key]['content_type'] = 'Content-type: image/' . strtolower($value['content_type']);
            }
        }

        // メールの削除
//      imap_delete($mbox, $mailno);
    }
    // 削除用にマークされたすべてのメッセージを削除
//  imap_expunge($mbox);

    // $mailの中身を確認
//var_dump($mail);

    foreach ($mail as $mail2) {
//Evernote処理
        if ($mail2['address'] == "no-reply@evernote.com") {
            echo '<div style="background-color:#F0FFF0; font-size:small; margin-right: 10px; float: left; overflow: hidden; width: 260px; height: 400px; padding: 10px; margin-bottom: 10px; border: 1px dotted #008000;">';
            echo $mail2['subject'], "<br>", "<br>", "<br>" . "<hr>", "<br>", $mail2['body'], "<br>", "<br>", "from Evernote", "</div>";
//メール処理
        } else {
            $mail3 = $mail2['attached_file'];
            echo '<div style="background-color:#FAEBD7; font-size:small; margin-right: 10px; float: left; overflow: hidden; width: 260px; height: 400px; padding: 10px; margin-bottom: 10px; border: 1px dotted #F4A460;">';
            echo $mail2['subject'], "<br>", "<br>", $mail2['address'], "<br>", "<hr>", "<br>";
            //echo "<img src='".$mail3."''>"."<br>","<br>";
            foreach ($mail3 as $mail4) {
                echo "<img src='data:image/gif;base64," . $mail4['imageb'] . "'>" . "<br>", "<br>";
            }
            echo $mail2['body'], "<br>", "<br>", "from Email", "</div>";
        }
    }
}

//Facebook-------------------------------------------------------------------


error_reporting(E_ALL);
ini_set('display_errors', 1);


// Facebook PHP SDK関連ファイルの読込
define('FACEBOOK_SDK_V4_SRC_DIR', '/var/www/html/moodle/mod/sharedresource/facebook/src/Facebook/');
require __DIR__ . '/facebook/src/Facebook/autoload.php';

use Facebook\FacebookClient;

//echo '<h2> Facebook API Testプログラム (PHP) 2015/09/10</h2>';
//echo "* 初期処理　開始<br>\n";

// Facebook App情報&アクセストークンの設定
$appId = '470135086524440';
$secret = '477518944f6a207acfd5c2c2a203b256';
$redirectUrl = 'http://gensai.tkita.net/';
$pageid = 'FACEBOOK PAGE ID';
$token = 'CAAGrldrUKBgBABsAfTiZBuase4KoSHGuLqZCH0BDdGVuEhuS0jjdX2ZApjfLlI21mZAZBOx0MjlO8iMX0ZCNcd8tTaMQahJl62ECS5usSize5ZAkLCxRp02M2mt3h503SlF3Lcb0i5yKMIpYYZAHqm2IOKNdZBTaY9KGxGylZCZBVIUF88m0acn2xVC';

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
$Groupid = "551028761722859";
$OrgreqStr = "/" . $Groupid . "/feed?fields=id,from,message,actions,child_attachments,application,likes, link,object_id,message_tags,created_time,updated_time,comments,picture,source,story,story_tags&limit=" . $reqLimit . "&since=" . $timeS;

//リクエスト実施
$reqStr = $OrgreqStr;
$request = $fb->request('GET', $reqStr);

try {
    $response = $fb->getClient()->sendRequest($request);
} catch (Facebook\Exceptions\FacebookResponseException $e) {
    echo 'Graph returned an error: ' . $e->getMessage();
    echo "<br>\n";
} catch (Facebook\Exceptions\FacebookSDKException $e) {
    echo 'Facebook SDK returned an error: ' . $e->getMessage();
    echo "<br>\n";
}

//echo "* Send reqest 終了)<br>\n";

// リクエスト結果の表示
if (isset($response)) {
    echo "<br>Get response <br>\n";

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
    $bodyResult = array_slice($arrayResult, 3, 1, true);
    $dataky = key($bodyResult);
    $dataResult = $bodyResult[$dataky]["data"];
    $n2 = count($dataResult);
    //echo "メッセージ数は{$n2}個です。<br>\n";

    for ($index = 0; $index < $n2; $index++) {
        // echo "index ={$index}<br>";
        //echo "<br>****** {$index}番目のメッセージ<br>\n";
        $data01Result = $dataResult[$index];
        if ($data01Result["message"]) {
            echo '<div1>';
            //echo "Message ID = ".$data01Result["id"]."<br>\n";
            echo $data01Result["from"] ["name"] . "<br>\n<br>";
            echo "<hr><br>";
            echo $data01Result["message"] . "<br>\n<br>";
            echo "<a href='" . $data01Result["link"] . "' target='_blank'><img src=" . $data01Result["picture"] . " width=150px></a><br>\n<br>";
            //clickableにしておく（linkのリンク）
            //echo $data01Result["link"]."<br>\n";
            echo $data01Result["created_time"] . "<br>\n<br><br>from Facebook";
            echo '</div1>';
        } else {
            //echo "<br>****** メッセージ本文を含まない投稿です<br>\n";
        }
    }
    echo "<br>\n";
    echo "</pre>\n";

} else {
    echo "Not get response <br>\n";
}

//----------------------------------------------------------------------------
// Finish the page.
echo $OUTPUT->footer();

?>

</html>

