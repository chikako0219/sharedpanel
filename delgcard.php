<?php

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');

$id = required_param('id', PARAM_INT); // course module id
$gcardid = required_param('gcard', PARAM_INT); // course module id

if ($id) {
    $cm = get_coursemodule_from_id('sharedpanel', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $sharedpanel = $DB->get_record('sharedpanel', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

confirm_sesskey();

$PAGE->set_url('/mod/sharedpanel/delgcard.php', array('id' => $cm->id));
$PAGE->set_title(format_string($sharedpanel->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

if (has_capability('moodle/course:manageactivities', $context)) {
    $gcardObj = new \mod_sharedpanel\gcard($sharedpanel);
    $result = $gcardObj->delete($gcardid);

    if ($result) {
        $msg = "gcard #" . $gcardid . " deleted (hidden).";
    } else {
        $msg = "You have no permission to delete it.";
    }
} else {
    $msg = "You have no permission to delete it.";
}

redirect(new moodle_url('view.php', ['id' => $id]), $msg, 3);