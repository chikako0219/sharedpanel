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

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');

global $DB, $PAGE, $OUTPUT;

$id = optional_param('id', 0, PARAM_INT); // course module id

if ($id) {
    $cm = get_coursemodule_from_id('sharedpanel', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $sharedpanel = $DB->get_record('sharedpanel', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    print_error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$context = \context_module::instance($cm->id);

// Print the page header.

$PAGE->set_url('/mod/sharedpanel/deletecard.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($sharedpanel->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// Output starts here.
echo $OUTPUT->header();

// LINE
include_once(dirname(__FILE__) . '/line.php');
//add_card_from_line($sharedpanel);

// Twitter
$twitterObj = new twitter($sharedpanel);
$cardids_twitter = $twitterObj->import();

if ($cardids_twitter != false) {
    echo html_writer::message(\core\notification::SUCCESS, 'Twitterからのインポートに成功しました。');
} else {
    echo html_writer::message(\core\notification::ERROR, 'Twitterからのインポートに失敗しました。');
    $error = $twitterObj->get_error();
    debugging($error->code . ":" . $error->message);
}

$emailObj = new email($sharedpanel);
$cardids_emails = $emailObj->import();

if ($cardids_emails != false) {
    echo html_writer::message(\core\notification::SUCCESS, 'メールからのインポートに成功しました。');
} else {
    echo html_writer::message(\core\notification::ERROR, 'メールからのインポートに失敗しました。');
    $error = $emailObj->get_error();
    debugging($error->code . ":" . $error->message);
}


// Email
//include_once(dirname(__FILE__) . '/email.php');
//if ($sharedpanel->emailadr1 == "") {
//    echo "<br/><hr>no email address.<br/>";
//    ob_flush();
//    flush();
//} else {
//    //add_card_from_email($sharedpanel);
//    add_card_from_email($sharedpanel, 600); // 大きな画像は幅600pxに縮小される
//}
//// Evernote
//include_once(dirname(__FILE__) . '/evernote.php');
//add_card_from_evernote($sharedpanel);


echo "<br/><hr><a href=\"./view.php?id=$id\"><span style='background-color:orange;padding:1ex;color:black;'><b>Importing done.</b></span></a><br/>";
ob_flush();
flush();

//----------------------------------------------------------------------------
// Finish the page.
echo $OUTPUT->footer();




// mkdir($CFG->dataroot.'/sharedpanel/');
// $outfile= $CFG->dataroot.'/sharedpanel/'.date("Y-md-His").$andkey;
// file_put_contents($outfile,$ret1);

// $ret2 = file_get_contents($outfile);
// echo $ret2;

