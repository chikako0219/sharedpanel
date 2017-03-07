<?php

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');

$id = optional_param('id', 0, PARAM_INT); // course module id
$c = optional_param('c', 0, PARAM_INT);  // ... card ID

confirm_sesskey();

if ($id) {
    $cm = get_coursemodule_from_id('sharedpanel', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $sharedpanel = $DB->get_record('sharedpanel', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

$PAGE->set_url('/mod/sharedpanel/deletecard.php', array('id' => $cm->id));
$PAGE->set_title(format_string($sharedpanel->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

/*
 * Other things you may want to set - remove if not needed.
 * $PAGE->set_cacheable(false);
 * $PAGE->set_focuscontrol('some-html-id');
 * $PAGE->add_body_class('sharedpanel-'.$somevar);
 */

$delarray = array('id' => $c, 'sharedpanelid' => $sharedpanel->id);

if (has_capability('moodle/course:manageactivities', $context)) {
    $cards = $DB->get_records('sharedpanel_cards', $delarray);
    $msg = "";
    foreach ($cards as $card) {
        $card->hidden = 1;
        $DB->update_record('sharedpanel_cards', $card);
        $msg .= "card #" . $card->id . " deleted (hidden).<br>";
    }
} else {
    $msg = "You have no permission to delete it.<br>";
}

redirect("view.php?id=$id", $msg, 3);

echo $OUTPUT->footer();
