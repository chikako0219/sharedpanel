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

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');

global $DB, $PAGE, $USER;

$id = optional_param('id', 0, PARAM_INT);
$cardid = optional_param('c', 0, PARAM_INT);
$ltype = optional_param('ltype', 0, PARAM_INT);

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

$likeobj = new like($sharedpanel);

$msg = "";
$like = $likeobj->is_liked($cardid, $USER->id, $ltype);
if (!$like) {
    $like = $likeobj->set($cardid, $USER->id, $ltype);
    $msg .= get_string('like_set_like', 'mod_sharedpanel', $cardid);
} else {
    $like = $likeobj->delete($cardid, $USER->id, $ltype);
    $msg .= get_string('like_set_unlike', 'mod_sharedpanel', $cardid);
}

redirect(new \moodle_url('view.php', ['id' => $id]), $msg, 2);