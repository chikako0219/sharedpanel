<?php
// LINE ------------------------------------------

require_once("locallib.php");

function add_card_from_line($sharedpanel){
    global $DB, $USER, $CFG;

    $maxnumatatime = 10;  // only $maxnumatatime messages are imported at a time
 
//    $andkey= $sharedpanel->hashtag1;
    $andkey= "";
    echo "<br/><hr>importing line ($andkey) ... <br/> ";  ob_flush(); flush();

    $co= 0;
    $dir= $CFG->dataroot.'/sharedpanel/line/';
    foreach (scandir($dir) as $file){
        $ma= explode('-',$file);
        if (count($ma)>1){
	    $timestamp= $ma[0];
	    $message_id= $ma[1];
	    $userid= $ma[2];
        }else{
	    continue;
        }

        $ret1 = "";  $content = "";

	// 現状、message と image が別のカードでインポートされる ... どのようにくっつけるか

        if (preg_match('/[.]message/', $file)){
            $content= file_get_contents($dir.$file);
            // 絵文字などを変換してDBエラーにならないようにする
            $content= mod_sharedpanel_utf8mb4_encode_numericentity($content);
            // URI を link に
            // http://www.phppro.jp/qa/688 
            $pat_sub= preg_quote('-._~%:/?#[]@!$&\'()*+,;=', '/');
            $pat= '/((http|https):\/\/[0-9a-z' . $pat_sub . ']+)/i';
            $rep= '<a href="\\1" target="_BLANK">\\1</a>';
            $content = preg_replace ($pat, $rep, $content);
            $ret1 .= '<br>'.$content.'<br>'.'<br>';
        }

        if (preg_match('/[.]image$/', $file)){
            $ret1 .= "<img src='data:image/gif;base64,".base64_encode(file_get_contents($dir.$file))."' width=250px>".'<br>'.$content.'<br>'.'<br>';
        }

	if ($andkey !== ""){ $ret1 .= "(search key = $andkey)"; }
	$name= $userid;  // 本当はIDでなく名前を入れたいが、とりあえず。
	$time = $timestamp;

        // DBにあるカードと重複していれば登録しない（次の投稿の処理へ）
        $samecard = $DB->get_record('sharedpanel_cards', array('timeposted' => $time,'inputsrc' => 'LINE','sharedpanelid' => $sharedpanel->id,'hidden' => 0));
        if ($samecard != null){ 
            echo "Not imported : same line message by $name ($time).<br>\n"; ob_flush(); flush();
            continue;
        }
        // content に $andkey が含まれなければ登録しない（次の投稿の処理へ）
        if ( $andkey !== "" && !preg_match("/$andkey/",$content) ){  continue;  }

        // DBにカードを追加
        $data = new stdClass;
        $data->sharedpanelid = $sharedpanel->id;
        $data->userid = $USER->id;
        $data->timeposted= $time;
        $data->timecreated  = time();
        $data->timemodified = $data->timecreated;
        $data->inputsrc = "LINE";
        $data->content = $ret1;
        $data->sender  = $name; // ."<img src='$link'>";

        $data->id = $DB->insert_record('sharedpanel_cards', $data);

        foreach( mod_sharedpanel_get_tags($content) as $tagstr ){
            // DBにタグを追加
            $tag = new stdClass;
            $tag->cardid = $data->id;
            $tag->userid = $USER->id;
            $tag->timecreated = $data->timecreated;
            $tag->tag = $tagstr;
            $tag->id = $DB->insert_record('sharedpanel_card_tags', $tag);
        }
        echo "A card imported from LINE : a message by $name ($time).<br>\n"; ob_flush(); flush();

        $co++;
        if ($co >= $maxnumatatime){
            echo "Only $maxnumatatime messages are imported at a time. Quitting.\n";
            break;
        }
    } // foreach (scandir($dir) as $file)
}

