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
 * @copyright  2016 NAGAOKA Chikako, KITA Toshihiro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

//echo '<meta name="viewport" content="width=device-width, initial-scale=0.5">';
echo '<link rel="stylesheet" href="../../lib/jquery/ui-1.11.4/theme/smoothness/jquery-ui.css">';

echo '
    <script src="../../lib/jquery/jquery-1.11.2.min.js"></script>
    <script src="../../lib/jquery/ui-1.11.4/jquery-ui.min.js"></script>
    <script src="jsPlumb-2.1.5-min.js"></script>
';
    

$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // ... sharedpanel instance ID - it should be named as the first character of the module.
//$import  = optional_param('import', 0, PARAM_INT);  // import new cards
$sortby = optional_param('sortby', 0, PARAM_INT);
 
if ($id) {
    $cm         = get_coursemodule_from_id('sharedpanel', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $sharedpanel  = $DB->get_record('sharedpanel', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($n) {
    $sharedpanel  = $DB->get_record('sharedpanel', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $sharedpanel->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('sharedpanel', $sharedpanel->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}
 
require_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);
//$context = context_module::instance($cm->id);

// Groupカード（カテゴリ分け）を表示するかどうか
//$sharedpanel_dispgcard= true;
$sharedpanel_dispgcard= false;

// ２つ目のいいねを使うか
//$sharedpanel_likes2= true;
$sharedpanel_likes2= false;

// sender を表示するかどうか
$dispname= false;

// ページをリロードするか
$reloadpage= true;

// ページをリロードする秒間隔
$intdelay= 90;

// パネル毎にCSSを変える ... 仮実装
//$styfile= $CFG->dataroot.'/sharedpanel/style.css.'.$sharedpanel->id;
$styfile= __DIR__ . '/css/style.css.'.$sharedpanel->id;
if (file_exists($styfile)){
  echo '<style>'.file_get_contents($styfile).'</style>';
}else{
  echo '<link rel="stylesheet" type="text/css" href="./style.css" media="screen">';
}

echo '
<script>
//        $(".item").resizable({
    $(".app-style0").resizable({

        resize : function(event, ui) {            
//                jsPlumb.repaint(ui.helper);
            },
            handles: "all"
    });

  jsPlumb.ready(function() {
            
//            jsPlumb.connect({
//                source:"item_left",
//                target:"item_right",
//                endpoint:"Rectangle"
//            });
';

if (has_capability('moodle/course:manageactivities', $context)){
  echo '
  jsPlumb.draggable($(".card"), {
//   containment: "parent",
   stop: function(event) {
     if ($(event.target).find("select").length == 0) {  saveState(event.target);  }
   }
  }); 
  ';
} // if (has_capability('moodle/course:manageactivities', $context))

echo '
  });

  var allPositions = function() {
   var blocks = []
   $("#diagramContainer .card").each(function (idx, elem) {
    var $elem = $(elem);
    blocks.push({
        cardid: $elem.attr("id"),
        positionx: parseInt($elem.css("left"), 10),
        positiony: parseInt($elem.css("top"), 10)
    });
   });
   var serializedData = JSON.stringify(blocks);
   return serializedData;
  }

  var saveState = function(state) {
    $.post("'.$CFG->wwwroot.'/mod/sharedpanel/cardxy/",{data:allPositions()} );
  }
 </script> 
';


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
if ($reloadpage){  $PAGE->set_periodic_refresh_delay($intdelay);  }
 
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
 

// プルダウンメニュー表示/
//----------------------------------------------------------------------------
// ファイル名を取得、配列に格納する
//$dir = ($CFG->dataroot.'/sharedpanel/');
//$files1 = scandir($dir);
 
//配列の中身確認用
//for ($i=0; $i < count($files1); $i++) { 
//echo "<option>".$files1[$i]."</option>";
//}
 
/*
// 選択リストの値を取得
$name = "menu1";
$selected_value = $_POST[$name];
  
// 選択リストの要素を配列に格納 → この配列からドロップダウンリストを作成
echo '</br>  Please select the past files';
$ar_menu1 = $files1;
 
// 配列から選択リストを作成する関数
// パラメータ：配列／選択リスト名／選択値
// 並び替えのキーを選ぶように
function disp_list($array, $name, $selected_value = "") {
    echo '<select name="' . $name . '" onchange="this.form.submit()">';
    while (list($value, $text) = each($array)) {
        echo "<option ";
        if ($selected_value == $value) {
            echo " selected ";
        }
        echo ' value="'.$value.'">' . $text . "</option>";
    }
    echo "</select>";
}
*/

//echo session_id();

if ($reloadpage){  echo "(このページは $intdelay 秒毎にリロードされます)<br/><br/>";  }
echo get_string('sortedas', 'sharedpanel');

echo " <a href='./view.php?id=$id&sortby=50'>".get_string('sort', 'sharedpanel')."</a>";
echo " / <a href='./view.php?id=$id&sortby=1'>".get_string('sortbylike1', 'sharedpanel')."</a><br/>";
echo "<a href='./view.php?id=$id&sortby=51'>"."ピックアップしたカードだけ表示"."</a>";
echo " / <a href='./view.php?id=$id'>"."すべてのカードを表示"."</a>";
//echo '<input type="button" value="'.get_string('print', 'sharedpanel').'" onclick="window.print()"><br/>';
echo ' / <a href="#" onclick="window.print()">'.get_string('print', 'sharedpanel').'</a><br/><br/>';

//echo "<form method='get' action='./importcard.php?id=$id'>";
//echo '<input type="button" value="import" onclick="submit()">';
//echo '</form><br>';

echo "<a href='./camera/com.php?id=$id&n=$sharedpanel->id'>".get_string('postmessage', 'sharedpanel')."</a><br><br>";

echo "<a href='./camera/?id=$id&n=$sharedpanel->id'>".get_string('camera', 'sharedpanel')."</a><br><br>";

if (has_capability('moodle/course:manageactivities', $context)){
  echo "<a href='./facebook.php?id=$id'>".get_string('facebookimport', 'sharedpanel')."</a>";
  echo " / <a href='./importcard.php?id=$id'>".get_string('import', 'sharedpanel')."</a><br><br>";
  echo "<a href='./post.php?id=$id'>".get_string('post', 'sharedpanel')."</a><br><br>";
  echo "<a href='./gcard.php?id=$id'>".get_string('groupcard', 'sharedpanel')."</a><br><br>";
}

 
// CARDのデータをDBから取得  
if ($sortby==1){ // 面白いね!(２個目の投票）の多い順でソート  
  $cards =  $DB->get_records('sharedpanel_cards', array('sharedpanelid' => $sharedpanel->id, 'hidden' => 0),'timeposted DESC');  
  $likest = $DB->get_records_sql('SELECT cardid, sum(rating) as sumr FROM {sharedpanel_card_likes} WHERE ltype = ? GROUP BY cardid;', array($sortby));  
  foreach ($likest as $like1){  
    $ratingmap[$like1->cardid]= $like1->sumr;  
  }  
  foreach ($cards as $key => $row) {  
    $timeposted[$key]  = $row->timeposted;  
    $rating[$key]      = $row->rating;  
    if ($ratingmap[$row->id]){  
      $rating2[$key]     = $ratingmap[$row->id];  
    }else{  
      $rating2[$key]     = 0;  
    }  
  }  
  // $cards を最後のパラメータとして渡し、同じキーでソートする。  
  array_multisort($rating2, SORT_DESC, $rating, SORT_DESC, $timeposted, SORT_DESC, $cards);  
}else if ($sortby==51){ // いいね!したものだけ表示  
  if ($USER->id==1){ // guest access  
      $cards = $DB->get_records_sql('select c.* from {sharedpanel_cards} c, {sharedpanel_card_likes} lk where c.sharedpanelid=? and lk.userid=? and c.hidden=0 and lk.rating=1 and c.id=lk.cardid and lk.sessionid=? order by c.timeposted DESC;', array( $sharedpanel->id,$USER->id,session_id() ));  
  }else{  
      $cards = $DB->get_records_sql('select c.* from {sharedpanel_cards} c, {sharedpanel_card_likes} lk where c.sharedpanelid=? and lk.userid=? and c.hidden=0 and lk.rating=1 and c.id=lk.cardid order by c.timeposted DESC;', array($sharedpanel->id,$USER->id));  
  }  
}else if ($sortby==50){ // 重要だね!(1個目の投票）の順  
  $cards = $DB->get_records('sharedpanel_cards', array('sharedpanelid' => $sharedpanel->id, 'hidden' => 0),'rating DESC, timeposted DESC');  
}else{ // すべて表示（時間順）  
  $cards = $DB->get_records('sharedpanel_cards', array('sharedpanelid' => $sharedpanel->id, 'hidden' => 0),'timeposted DESC');  
}  


// Group (Category) Card
$gcards = $DB->get_records('sharedpanel_gcards', array('sharedpanelid' => $sharedpanel->id, 'hidden' => 0),'rating DESC ');

echo '<div id="diagramContainer">'."\n";


// Groupカード ----------------------------------------------
if ($sharedpanel_dispgcard){
$leftpos = 300;  $toppos  = 300;
$gcardnum = 0;
foreach ($gcards as $gcard){  // 各Groupカード
  if ($gcard->positionx==0 and $gcard->positiony==0){
    $tstyle= "style='left:${leftpos}px;top:${toppos}px;'";
    $leftpos += 300;   $toppos  += 10;
    if ($leftpos>1200){ $leftpos = 300; $toppos += 440; }
  }else{
    $tstyle= "style='left:".$gcard->positionx."px;top:".$gcard->positiony."px;'";
  }
  $gcardnum= $gcard->id;

/*
  // いいね! のリンク要素 ($likeslink)
  $like0 = $DB->get_record('sharedpanel_card_likes', array('cardid' => $card->id,'userid' => $USER->id));
  if ($like0->rating > 0){
    $mylike = "✓";
  }else{
    $mylike = "";
  }
  $likeslink = "";
  $likeslink .= "<a href=\"./likes.php?id=$id&c=$card->id\" >いいね!</a> ";
  if ($card->rating > 0){  $likeslink .= $mylike . "($card->rating)";  }
//  $likeslink .= "</span><br>";  

  // タグ要素 ($taglink)
  $tags = $DB->get_records('sharedpanel_card_tags', array('cardid' => $card->id));
  $taglink = "";
  foreach ($tags as $tag){
//    $taglink .= "<a href=\"\" style='background-color:blue; color:white;'> $tag->tag </a> ";
    $taglink .= "<a href=\"\"> $tag->tag </a> ";
  }
*/
  // コンテンツ要素
  $gcardcontent= $gcard->content;

  // 削除リンク要素 （教師だけに表示）
  if (has_capability('moodle/course:manageactivities', $context)){
    $dellink = "<a href=\"./delgcard.php?id=$id&c=$gcard->id\">削除する</a>";
  }
  // 上記要素を使って、Groupカードの表示
  echo "
<div class='all-style0 card' id='gcard$gcardnum' $tstyle style='width:".$gcard->sizex."px;'>
  <!--
  <div class='all-style1'>
    $taglink <br/>
  </div>
  -->
  <div class='all-style2' style='height:".$gcard->sizey."px; width:".$gcard->sizex."px;'>
    $gcardcontent
  </div>
  <div class='all-style3' style='width:".$gcard->sizex."px;'>
    <span>$likeslink</span> <span style='margin-left:5em;'>$dellink</span>
  </div>
</div>
  ";
}
} // if ($sharedpanel_dispgcard)
// Groupカード ----------------------------------------------



$leftpos = 420;  $toppos  = 370;
$cnum = 0;
foreach ($cards as $card){  // 各カード
  if ($card->positionx==0 and $card->positiony==0){
    $tstyle= "style='left:${leftpos}px;top:${toppos}px;'";
    $leftpos += 300;   $toppos  += 10;
    if ($leftpos>1200){ $leftpos = 420; $toppos += 440; }
  }else{
    $tstyle= "style='left:".$card->positionx."px;top:".$card->positiony."px;'";
  }
  $cardnum= $card->id;


  $guests_distinguished = true;
  //$guests_distinguished = false;

  $sessionid = session_id(); // for distingushing guests from different places
  if ($USER->id == 1){  $is_guest = true;  }

  if ($guests_distinguished and $is_guest){
    $likearray=  array('cardid' => $card->id,'userid' => $USER->id, 'ltype' => 0, 'sessionid' => $sessionid );
    $likearray1= array('cardid' => $card->id,'userid' => $USER->id, 'ltype' => 1, 'sessionid' => $sessionid );
  }else{
    $likearray=  array('cardid' => $card->id,'userid' => $USER->id, 'ltype' => 0);
    $likearray1= array('cardid' => $card->id,'userid' => $USER->id, 'ltype' => 1);
  }
  // いいね! のリンク要素 ($likeslink)
  $like0 = $DB->get_record('sharedpanel_card_likes', $likearray);
  if ($like0->rating > 0){
//    $mylike = "✓";
//    $maru1 = "<span style='font-size:28px;color:red;vertical-align:middle;'>■</span>";
    $maru1 = "<span style='font-size:17px;color:red;vertical-align:bottom;'>✓</span>";
  }else{
//    $mylike = "";
    $maru1 = "<span style='font-size:27px;color:red;vertical-align:middle;'>□</span>";
//    $maru1 = "";
  }
  $likeslink = "";
  $likeslink .= "<a href=\"./likes.php?id=$id&c=$card->id\" >".get_string('important', 'sharedpanel')." $maru1 </a> ";

  $cardrating= $card->rating;
  $barwidth= $cardrating*6; if($barwidth>150){ $barwidth= 150; }
  if ($cardrating > 0){  
//    $likeslink .= "($cardrating) <img src='red.png' style='width:${barwidth}px; height:20px; vertical-align:middle;'>";
    $likeslink .= " <img src='red.png' style='width:${barwidth}px; height:20px; vertical-align:middle;'>($cardrating)";
  }

  // もうひとつの「いいね!」(ltype==1) のリンク要素 ($likeslink1)
if ($sharedpanel_likes2){
  $like01 = $DB->get_record('sharedpanel_card_likes', $likearray1);
  if ($like01->rating > 0){
//    $mylike = "✓";
//    $maru1 = "<span style='font-size:28px;color:#55f;vertical-align:middle;'>■</span>";
    $maru1 = "<span style='font-size:17px;color:#55f;vertical-align:bottom;'>✓</span>";
  }else{
//    $mylike = "";
    $maru1 = "<span style='font-size:27px;color:#55f;vertical-align:middle;'>□</span>";
//    $maru1 = "";
  }
  $likeslink1 = "";
  $likeslink1 .= "<a href=\"./likes.php?id=$id&c=$card->id&ltype=1\" >".get_string('interesting', 'sharedpanel')."$maru1 </a> ";

  $ratingsum1 = $DB->get_record_sql('SELECT sum(rating) as sumr FROM {sharedpanel_card_likes} WHERE cardid = ? AND ltype = ?', array($card->id, 1));
//  $cardrating1= $card->rating;
  $cardrating1= $ratingsum1->sumr;
  $barwidth1= $cardrating1*6; if($barwidth1>150){ $barwidth1= 150; }
  if ($cardrating1 > 0){  
//    $likeslink1 .= "($cardrating1) <img src='blue.png' style='width:${barwidth1}px; height:20px; vertical-align:middle;'>";
    $likeslink1 .= " <img src='blue.png' style='width:${barwidth1}px; height:20px; vertical-align:middle;'>($cardrating1)";
  }
}


  // タグ要素 ($taglink)
  $tags = $DB->get_records('sharedpanel_card_tags', array('cardid' => $card->id));
  $taglink = "";
  foreach ($tags as $tag){
//    $taglink .= "<a href=\"\" style='background-color:blue; color:white;'> $tag->tag </a> ";
    $taglink .= "<a href=\"\"> $tag->tag </a> ";
  }

  // コンテンツ要素
  $cardcontent= $card->content;
  // 日付などを別途表示
  $cardcontent .= "<div style='font-size:60%;line-height:100%;'><br/><br/>".date('c',$card->timeposted);
  if ($dispname){  $cardcontent .= "<br/>".$card->sender;  }
  $cardcontent .= "<br/> from ".$card->inputsrc."</div>";

  // 削除リンク要素 （教師だけに表示）
  if (has_capability('moodle/course:manageactivities', $context)){
//    $dellink = "<a href=\"./deletecard.php?id=$id&c=$card->id\">削除する</a>";
    $dellink = "<a href=\"./deletecard.php?id=$id&c=$card->id\"> × </a>";
  }

  $inputsrc= $card->inputsrc; // twtter, facebook, ....
  // 上記4要素を使って、カードの表示
  echo "
<div class='$inputsrc-style0 all-style0 card' id='card$cardnum' $tstyle>
  <div class='$inputsrc-style1 all-style1'>
    <span style='background-color:lightblue; font-size:25px; padding:1px;'> $dellink </span>
    <span> $taglink </span> <br/>
  </div>
  <div class='$inputsrc-style2 all-style2'>
    $cardcontent
  </div>
  <div class='all-style3'>
    <div>$likeslink</div> 
    <div>$likeslink1</div> 
  </div>
</div>
";
$cnum++;
}  // foreach ($cards as $card)
echo "</div>\n";

echo "(total: $cnum cards)";

//----------------------------------------------------------------------------
// Finish the page.
echo $OUTPUT->footer();



// mkdir($CFG->dataroot.'/sharedpanel/');
// $outfile= $CFG->dataroot.'/sharedpanel/'.date("Y-md-His").$andkey;
// file_put_contents($outfile,$ret1);
 
// $ret2 = file_get_contents($outfile);
// echo $ret2;

