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

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');

global $DB, $PAGE, $OUTPUT;

$id = optional_param('id', 0, PARAM_INT);
$n = optional_param('n', 0, PARAM_INT);
$sortby = optional_param('sortby', 0, PARAM_INT);

if ($id) {
    $cm = get_coursemodule_from_id('sharedpanel', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
    $sharedpanel = $DB->get_record('sharedpanel', ['id' => $cm->instance], '*', MUST_EXIST);
} else if ($n) {
    $sharedpanel = $DB->get_record('sharedpanel', ['id' => $n], '*', MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $sharedpanel->course], '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('sharedpanel', $sharedpanel->id, $course->id, false, MUST_EXIST);
} else {
    print_error('You must specify a course_module ID or an instance ID');
}

$context = context_module::instance($cm->id);
require_login();

// Groupカード（カテゴリ分け）を表示するかどうか
$sharedpanel_dispgcard = true;
// ２つ目のいいねを使うか
$sharedpanel_likes2 = true;
// sender を表示するかどうか
$dispname = false;

// パネル毎にCSSを変える ... 仮実装
//$styfile= $CFG->dataroot.'/sharedpanel/style.css.'.$sharedpanel->id;
$styfile = __DIR__ . '/css/style.css.' . $sharedpanel->id;
if (file_exists($styfile)) {
    $PAGE->requires->css($styfile);
} else {
    $PAGE->requires->css(new moodle_url("style.css"));
}

// Print the page header.
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->js(new moodle_url('js/jsPlumb-2.1.5-min.js'));
$PAGE->requires->js(new moodle_url('js/card_admin.js'));

$PAGE->set_cm($cm);
$PAGE->set_context($context);
$PAGE->set_url('/mod/sharedpanel/view.php', array('id' => $cm->id));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_title(format_string($sharedpanel->name));

// Output starts here.
echo $OUTPUT->header();

echo html_writer::start_div();
echo get_string('sortedas', 'sharedpanel');
echo html_writer::link(new moodle_url('view.php', ['id' => $id]), get_string('sort', 'sharedpanel'));
echo html_writer::link(new moodle_url('view.php', ['id' => $id, 'sortby' => 1]), get_string('sortbylike1', 'sharedpanel'));
echo html_writer::end_div();

echo html_writer::start_div();
echo html_writer::empty_tag('input',
    ['type' => 'button', 'value' => get_string('print', 'sharedpanel'), 'onclick' => 'window.print()', 'style' => 'margin:1ex;']);
echo html_writer::end_div();

echo html_writer::start_div();
echo html_writer::link(new moodle_url('camera/com.php', ['id' => $id, 'n' => $sharedpanel->id]), get_string('postmessage', 'sharedpanel'));
echo html_writer::end_div();

if (has_capability('moodle/course:manageactivities', $context)) {

    echo html_writer::start_div();
    echo html_writer::link(new moodle_url('importcard.php', ['id' => $id]), get_string('import', 'sharedpanel'));
    echo html_writer::end_div();

    echo html_writer::start_div();
    echo html_writer::link(new moodle_url('post.php', ['id' => $id, 'sesskey' => sesskey()]), get_string('post', 'sharedpanel'));
    echo html_writer::end_div();

    echo html_writer::start_div();
    echo html_writer::link(new moodle_url('gcard.php', ['id' => $id]), get_string('groupcard', 'sharedpanel'));
    echo html_writer::end_div();
}

// CARDのデータをDBから取得
if ($sortby) {
    $cards = $DB->get_records('sharedpanel_cards', array('sharedpanelid' => $sharedpanel->id, 'hidden' => 0), 'timeposted DESC');
    $likest = $DB->get_records_sql(
        'SELECT cardid, sum(rating) sumr 
           FROM {sharedpanel_card_likes} 
          WHERE ltype = :ltype 
          GROUP BY cardid;', ['ltype' => $sortby]);
    foreach ($likest as $like1) {
        $ratingmap[$like1->cardid] = $like1->sumr;
    }
    foreach ($cards as $key => $row) {
        $timeposted[$key] = $row->timeposted;
        $rating[$key] = $row->rating;
        if ($ratingmap[$row->id]) {
            $rating2[$key] = $ratingmap[$row->id];
        } else {
            $rating2[$key] = 0;
        }
    }
    // $cards を最後のパラメータとして渡し、同じキーでソートする。
    array_multisort($rating2, SORT_DESC, $rating, SORT_DESC, $timeposted, SORT_DESC, $cards);
} else {
    $cards = $DB->get_records('sharedpanel_cards', array('sharedpanelid' => $sharedpanel->id, 'hidden' => 0), 'rating DESC, timeposted DESC');
}

// Group (Category) Card
$gcards = $DB->get_records('sharedpanel_gcards', array('sharedpanelid' => $sharedpanel->id, 'hidden' => 0), 'rating DESC');

echo html_writer::start_div('', ['id' => 'diagramContainer']);

// Groupカード ----------------------------------------------
if ($sharedpanel_dispgcard) {
    $leftpos = 300;
    $toppos = 300;
    $gcardnum = 0;
    foreach ($gcards as $gcard) {  // 各Groupカード
        if ($gcard->positionx == 0 and $gcard->positiony == 0) {
            $tstyle = "left:${leftpos}px;top:${toppos}px;";
            $leftpos += 300;
            $toppos += 10;
            if ($leftpos > 1200) {
                $leftpos = 300;
                $toppos += 440;
            }
        } else {
            $tstyle = "left:" . $gcard->positionx . "px;top:" . $gcard->positiony . "px;'";
        }
        $gcardnum = $gcard->id;

        // コンテンツ要素
        $gcardcontent = $gcard->content;

        // 上記要素を使って、Groupカードの表示
        $tstyle .= ' width:' . $gcard->sizex . 'px';
        echo html_writer::start_div('all-style0 card', ['id' => 'gcard' . $gcardnum, 'style' => $tstyle]);
        echo html_writer::div($gcardcontent, 'all-style2', ['style' => 'height:' . $gcard->sizey . 'px; width:' . $gcard->sizex . 'px;']);
        echo html_writer::start_div('all-style3', ['style' => 'width:' . $gcard->sizex . 'px;']);
        echo html_writer::span($likeslink);
        // 削除リンク要素 （教師だけに表示）
        if (has_capability('moodle/course:manageactivities', $context)) {
            $dellink = html_writer::link(new moodle_url('delgcard.php', ['id' => $id, 'c' => $gcard->id]), '削除する');
            echo html_writer::span($dellink, ['style' => 'margin-left:5em;']);
        }
        echo html_writer::end_div();
        echo html_writer::end_div();
    }
}
// Groupカード ----------------------------------------------

$leftpos = 420;
$toppos = 370;
$cnum = 0;
foreach ($cards as $card) {  // 各カード
    if ($card->positionx == 0 and $card->positiony == 0) {
        $tstyle = "style='left:${leftpos}px;top:${toppos}px;'";
        $leftpos += 300;
        $toppos += 10;
        if ($leftpos > 1200) {
            $leftpos = 420;
            $toppos += 440;
        }
    } else {
        $tstyle = "style='left:" . $card->positionx . "px;top:" . $card->positiony . "px;'";
    }
    $cardnum = $card->id;


    $guests_distinguished = true;

    $sessionid = session_id(); // for distingushing guests from different places

    if ($guests_distinguished && is_guest($context)) {
        $likearray = array('cardid' => $card->id, 'userid' => $USER->id, 'ltype' => 0, 'sessionid' => $sessionid);
        $likearray1 = array('cardid' => $card->id, 'userid' => $USER->id, 'ltype' => 1, 'sessionid' => $sessionid);
    } else {
        $likearray = array('cardid' => $card->id, 'userid' => $USER->id, 'ltype' => 0);
        $likearray1 = array('cardid' => $card->id, 'userid' => $USER->id, 'ltype' => 1);
    }
    // いいね! のリンク要素 ($likeslink)
    $like0 = $DB->get_record('sharedpanel_card_likes', $likearray);
    if ($like0 != false && $like0->rating > 0) {
        $maru1 = "<span style='font-size:17px;color:red;vertical-align:bottom;'>✓</span>";
    } else {
        $maru1 = "<span style='font-size:27px;color:red;vertical-align:middle;'>□</span>";
    }
    $likeslink = html_writer::link(new moodle_url('likes.php', ['id' => $id, 'c' => $card->id, 'sesskey' => sesskey()]), get_string('important', 'sharedpanel') . $maru1);


    $cardrating = $card->rating;
    $barwidth = $cardrating * 6;
    if ($barwidth > 150) {
        $barwidth = 150;
    }
    if ($cardrating > 0) {
        $likeslink .= " <img src='red.png' style='width:${barwidth}px; height:20px; vertical-align:middle;'>($cardrating)";
    }

    // もうひとつの「いいね!」(ltype==1) のリンク要素 ($likeslink1)
    if ($sharedpanel_likes2) {
        $like01 = $DB->get_record('sharedpanel_card_likes', $likearray1);
        if ($like01 != false && $like01->rating > 0) {
            $maru1 = "<span style='font-size:17px;color:#55f;vertical-align:bottom;'>✓</span>";
        } else {
            $maru1 = "<span style='font-size:27px;color:#55f;vertical-align:middle;'>□</span>";
        }
        $likeslink1 = "";
        $likeslink1 .= html_writer::link(new moodle_url('likes.php', ['id' => $id, 'c' => $card->id, 'ltype' => 1, 'sesskey' => sesskey()]), get_string('interesting', 'sharedpanel') . $maru1);

        $ratingsum1 = $DB->get_record_sql('SELECT sum(rating) as sumr FROM {sharedpanel_card_likes} WHERE cardid = ? AND ltype = ?', array($card->id, 1));
        $cardrating1 = $ratingsum1->sumr;
        $barwidth1 = $cardrating1 * 6;
        if ($barwidth1 > 150) {
            $barwidth1 = 150;
        }
        if ($cardrating1 > 0) {
            $likeslink1 .= " <img src='blue.png' style='width:${barwidth1}px; height:20px; vertical-align:middle;'>($cardrating1)";
        }
    }

    // タグ要素 ($taglink)
    $tags = $DB->get_records('sharedpanel_card_tags', array('cardid' => $card->id));
    $taglink = "";
    foreach ($tags as $tag) {
        $taglink .= "<a href=\"\"> $tag->tag </a> ";
    }

    // コンテンツ要素
    $cardcontent = $card->content;
    // 日付などを別途表示
    $cardcontent .= "<div style='font-size:60%;line-height:100%;'><br/><br/>" . date('c', $card->timeposted);
    if ($dispname) {
        $cardcontent .= "<br/>" . $card->sender;
    }
    $cardcontent .= "<br/> from " . $card->inputsrc . "</div>";

    // 削除リンク要素 （教師だけに表示）
    if (has_capability('moodle/course:manageactivities', $context)) {
        $dellink = html_writer::link(new moodle_url('deletecard.php', ['id' => $id, 'c' => $card->id, 'sesskey' => sesskey()]), '×');
    }

    $inputsrc = $card->inputsrc; // twtter, facebook, ....
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
echo html_writer::end_div();

echo '(total: ' . $cnum . 'cards)';

//----------------------------------------------------------------------------
// Finish the page.
echo $OUTPUT->footer();