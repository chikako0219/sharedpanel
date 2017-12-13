<?php

namespace mod_sharedpanel;

use mod_sharedpanel\form\line_form;

require_once(__DIR__ . '/../../config.php');
require_once(dirname(__FILE__) . '/lib.php');

global $DB, $PAGE, $OUTPUT, $USER;

$id = optional_param('id', 0, PARAM_INT);
$n = optional_param('n', 0, PARAM_INT);

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

$context = \context_module::instance($cm->id);
require_login();

$PAGE->set_cm($cm);
$PAGE->set_url('/mod/sharedpanel/view.php', array('id' => $cm->id));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_title(format_string($sharedpanel->name));
$PAGE->set_pagelayout('incourse');
$PAGE->set_context($context);

$mform = new line_form(null,
    ['instance' => $sharedpanel->id]);

if ($mform->is_cancelled()) {
    redirect(new \moodle_url('view.php', ['id' => $cm->id]), "キャンセルしました。", 3);
} else if ($data = $mform->get_data()) {
    $lineidObj = new lineid($sharedpanel);
    if ($lineidObj->set_line_id($USER->id, $data->lineid)) {
        redirect(new \moodle_url('view.php', ['id' => $cm->id]), "LINE IDを登録しました。", 3);
    }
}

echo $OUTPUT->header();

$mform->display();

echo $OUTPUT->footer();