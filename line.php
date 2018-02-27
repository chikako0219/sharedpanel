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
    redirect(new \moodle_url('view.php', ['id' => $cm->id]), get_string('post_cancel', 'mod_sharedpanel'), 3);
} else if ($data = $mform->get_data()) {
    $lineidobj = new lineid($sharedpanel);
    if ($lineidobj->set_line_id($USER->id, $data->lineid)) {
        redirect(new \moodle_url('view.php', ['id' => $cm->id]), get_string('line_registered_id', 'mod_sharedpanel'), 3);
    }
}

echo $OUTPUT->header();

$mform->display();

echo $OUTPUT->footer();