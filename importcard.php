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

use core\notification;

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');
require_once(dirname(__FILE__) . '/locallib.php');

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

$PAGE->set_url('/mod/sharedpanel/importcard.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($sharedpanel->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// Output starts here.
echo $OUTPUT->header();

// Twitter
$twitterObj = new twitter($sharedpanel);
$cardids_twitter = $twitterObj->import();
if ($cardids_twitter != false || is_null($cardids_twitter) || is_array($cardids_twitter)) {
    if (count($cardids_twitter) == 0) {
        echo html_writer::message(\core\notification::INFO, '新規ツイートはありませんでした。');
    } else {
        echo html_writer::message(\core\notification::SUCCESS, 'Twitterからのインポートに成功しました。');
    }
} else {
    echo html_writer::message(\core\notification::ERROR, 'Twitterからのインポートに失敗しました。');
    $error = $twitterObj->get_error();
    debugging($error->code . ":" . $error->message);
}

$emailObj = new email($sharedpanel);
$cardids_emails = $emailObj->import();

if ($cardids_emails != false || is_null($cardids_emails) || is_array($cardids_emails)) {
    if (count($cardids_emails) == 0) {
        echo html_writer::message(\core\notification::INFO, '新規メールはありませんでした。');
    } else {
        echo html_writer::message(\core\notification::SUCCESS, count($cardids_emails) . '件をメールからインポートしました。');
    }
} else {
    echo html_writer::message(\core\notification::ERROR, 'メールからのインポートに失敗しました。');
    $error = $emailObj->get_error();
    debugging($error->code . ":" . $error->message);
}

//// Evernote
$evernoteObj = new evernote($sharedpanel);
$cardids_evernote = $evernoteObj->import();

if ($cardids_evernote != false || is_null($cardids_evernote) || is_array($cardids_evernote)) {
    if (count($cardids_evernote) == 0) {
        echo html_writer::message(\core\notification::INFO, '新規Evernoteはありませんでした。');
    } else {
        echo html_writer::message(\core\notification::SUCCESS, count($cardids_evernote) . '件をEvernoteからインポートしました。');
    }
} else {
    echo html_writer::message(\core\notification::ERROR, 'Evernoteからのインポートに失敗しました。');
    $error = $emailObj->get_error();
    debugging($error->code . ":" . $error->message);
}

$total_count = count($cardids_twitter) + count($cardids_emails) + count($cardids_evernote);

echo html_writer::message(notification::SUCCESS, 'インポート処理が完了しました。インポートした件数は' . $total_count . '件です。');

//----------------------------------------------------------------------------
// Finish the page.
echo $OUTPUT->footer();