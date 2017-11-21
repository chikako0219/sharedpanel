<?php

$guests_distinguished = true;
//$guests_distinguished = false;

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');

$id = optional_param('id', 0, PARAM_INT); // course module id
$c = optional_param('c', 0, PARAM_INT);  // ... card ID
$ltype = optional_param('ltype', 0, PARAM_INT);  // like type

confirm_sesskey();

if ($id) {
    $cm = get_coursemodule_from_id('sharedpanel', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $sharedpanel = $DB->get_record('sharedpanel', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    print_error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

$PAGE->set_url('/mod/sharedpanel/deletecard.php', array('id' => $cm->id));
$PAGE->set_title(format_string($sharedpanel->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

$sessionid = session_id();

if ($USER->id == 1) {
    $is_guest = true;
} else {
    $is_guest = false;
}
if ($guests_distinguished and $is_guest) {
    $likearray = array('cardid' => $c, 'userid' => $USER->id, 'ltype' => $ltype, 'sessionid' => $sessionid);
} else {
    $likearray = array('cardid' => $c, 'userid' => $USER->id, 'ltype' => $ltype);
}

$msg = "";
$like = $DB->get_record('sharedpanel_card_likes', $likearray);
if (!$like) {
    $like = new stdClass;
    $like->cardid = $c;
    $like->userid = $USER->id;
    $like->timecreated = time();
    $like->rating = 1;
    $like->comment = '';
    $like->ltype = $ltype;
    if ($guests_distinguished and $is_guest) {
        $like->sessionid = $sessionid;
    } // guest はそれぞれ区別する
    $like->id = $DB->insert_record('sharedpanel_card_likes', $like);
    // echo "card #".$like->cardid." liked by you.<br>";
    $msg .= "カード #" . $like->cardid . " に いいね! しました。<br>";
} else if ($like->rating == 0) {
    $like->rating = 1;
    $DB->update_record('sharedpanel_card_likes', $like);
    // echo "card #".$like->cardid." liked by you. (modified)<br>";
    $msg .= "カード #" . $like->cardid . " に いいね! しました。（変更）<br>";
} else {
    $like->rating = 0;
    $DB->update_record('sharedpanel_card_likes', $like);
    // echo "card #".$like->cardid." not liked by you.<br>";
    $msg .= "カード #" . $like->cardid . " の いいね! を解除しました。<br>";
}

if ($ltype == 0) {
    // いいねのカウント
    $likes = $DB->get_records('sharedpanel_card_likes', array('cardid' => $c, 'ltype' => $ltype));
    $likesco = 0;
    foreach ($likes as $liketmp) {
        $likesco += $liketmp->rating;
    }
    if ($like) { // if not DB table nodata for the card
        $card = $DB->get_record('sharedpanel_cards', array('sharedpanelid' => $sharedpanel->id, 'hidden' => 0, 'id' => $c));
        $card->rating = $likesco;
        $card->id = $DB->update_record('sharedpanel_cards', $card);
    }
}

redirect(new moodle_url('view.php', ['id' => $id]), $msg, 2);

//echo $OUTPUT->footer();
