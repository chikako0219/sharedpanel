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
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

$PAGE->set_url('/mod/sharedpanel/deletecard.php', array('id' => $cm->id));
$PAGE->set_title(format_string($sharedpanel->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

if (has_capability('moodle/course:manageactivities', $context)) {
    $cardObj = new \mod_sharedpanel\card($sharedpanel);
    $cardObj->delete($c);
    $msg = get_string('deletecard_delete_success', 'mod_sharedpanel', $c);
} else {
    $msg = get_string('deletecard_no_permission', 'mod_sharedpanel');
}

redirect(new moodle_url('view.php', ['id' => $id]), $msg, 3);

echo $OUTPUT->footer();
