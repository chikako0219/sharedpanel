<?php

namespace mod_sharedpanel;

global $DB, $PAGE, $USER;

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');

$id = optional_param('id', 0, PARAM_INT); // course module id
$cardid = optional_param('c', 0, PARAM_INT);  // ... card ID
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
$context = \context_module::instance($cm->id);

$PAGE->set_url('/mod/sharedpanel/deletecard.php', array('id' => $cm->id));
$PAGE->set_title(format_string($sharedpanel->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

$likeObj = new like($sharedpanel);

$msg = "";
$like = $likeObj->is_liked($cardid, $USER->id, $ltype);
if (!$like) {
    $like = $likeObj->set($cardid, $USER->id, $ltype);
    $msg .= "カード #" . $cardid . " に いいね! しました。<br>";
} else {
    $like = $likeObj->unset($cardid, $USER->id, $ltype);
    $msg .= "カード #" . $cardid . " の いいね! を解除しました。<br>";
}

//if ($ltype == 0) {
//    $likes = $likeObj->gets($cardid, $ltype, true);
//    if ($like) {
//        $card = $DB->get_record('sharedpanel_cards', ['sharedpanelid' => $sharedpanel->id, 'hidden' => 0, 'id' => $cardid]);
//        $card->rating = count($likes);
//        $card->id = $DB->update_record('sharedpanel_cards', $card);
//    }
//}

redirect(new \moodle_url('view.php', ['id' => $id]), $msg, 2);