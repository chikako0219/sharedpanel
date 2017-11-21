<?php

namespace mod_sharedpanel;

use mod_sharedpanel\form\upload_form;

require_once(__DIR__ . '/../../../config.php');
require_once(__DIR__ . '/../lib.php');
require_once(__DIR__ . '/../locallib.php');

global $DB, $PAGE, $OUTPUT, $USER;

$cmid = required_param('cmid', PARAM_INT);

$cm = get_coursemodule_from_id('sharedpanel', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$sharedpanel = $DB->get_record('sharedpanel', ['id' => $cm->instance], '*', MUST_EXIST);

$context = \context_module::instance($cm->id);
require_login();

$PAGE->requires->jquery();

$PAGE->set_cm($cm);
$PAGE->set_context($context);
$PAGE->set_url('/mod/sharedpanel/camera/upload.php', ['cmid' => $cm->id]);
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_title(format_string($sharedpanel->name));

//Call Objects
$cardObj = new card($sharedpanel);

// Output starts here.
echo $OUTPUT->header();

$mform = new upload_form(null, ['cmid' => $cmid]);
if ($mform->is_cancelled()) {
    redirect(new \moodle_url('index.php', ['id' => $cm->id]), "キャンセルしました。", 3);
} else if ($data = $mform->get_data()) {
    $ret1 = "";
    if ($data->comment) {
        $ret1 .= $data->comment . "<br/><br/>";
    }

    $ret1 .= "<img src='data:image/gif;base64,";
    $ret1 .= mod_sharedpanel_rotatecompress_img($_FILES["capture"]["tmp_name"], 600);
    $ret1 .= "' width=85%><br/>";

    $d = new \stdClass();
    $d->sharedpanelid = $cm->instance;
    $d->userid = $USER->id;
    $d->rating = $USER->id;
    $d->content = $ret1;
    $d->comment = $data->comment;
    $d->hidden = "";
    $d->post = time();
    $d->create = time();
    $d->modify = time();
    $d->inputsrc = "camera";
    $d->messageid = 0;
    $d->sender = $data->author_name;
    $d->positionx = 0;
    $d->positiony = 0;

    $DB->insert_record("sharedpanel_cards", $d);

//    redirect(new \moodle_url('../view.php', ['id' => $cm->id]), "新規登録を行いました", 3);
}

$mform->display();

echo $OUTPUT->footer();