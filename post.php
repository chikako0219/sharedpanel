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

namespace mod_sharedpanel;

global $CFG, $DB, $PAGE, $OUTPUT, $USER;

use mod_sharedpanel\form\post_form;

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');

require_once("$CFG->libdir/formslib.php");
require_once("locallib.php");

confirm_sesskey();

// --------------------------------------------------------------------------------

$id = optional_param('id', 0, PARAM_INT); // course module id
$c = optional_param('c', 0, PARAM_INT);  // ... card ID

if ($id) {
    $cm = get_coursemodule_from_id('sharedpanel', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $sharedpanel = $DB->get_record('sharedpanel', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    print_error('You must specify a course_module ID or an instance ID!!');
}

require_login($course, true, $cm);
$context = \context_module::instance($cm->id);

$PAGE->set_url('/mod/sharedpanel/post.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($sharedpanel->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

$mform = new post_form(null, array('cm' => $cm));

//Form processing and displaying is done here
if ($mform->is_cancelled()) {
    redirect(new \moodle_url('view.php', ['id' => $id]), get_string('post_cancel', 'mod_sharedpanel'), 3);
} else if ($data = $mform->get_data()) {
    $cardObj = new card($sharedpanel);

    $tag = $data->tag;

    $cardid = $cardObj->add($data->content["text"], fullname($USER->id), 'moodle');

    // If attach file...
    if ($filecontent = $mform->get_file_content('attachment')) {
        $cardObj->add_attachment($context, $cardid, $filecontent, $mform->get_new_filename('attachment'));
    }

    if ($tag) {
        $tagObj = new tag($sharedpanel);
        if (!$tagObj->is_exists($cardid)) {
            $tagObj->set($cardid, $tag, $USER->id);
        } else {
            $tagObj->update($cardid, $tag);
        }
    }

    redirect(new \moodle_url('view.php', ['id' => $id]), get_string('post_saved', 'mod_sharedpanel'), 5);
} else {
    echo $OUTPUT->header();
    $mform->display();
}

echo $OUTPUT->footer();
