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

$cardids_twitter = $cardids_emails = $cardids_evernote = $cardids_facebook = null;

/**
 * Twitter
 */
$twitterObj = new twitter($sharedpanel);
if ($twitterObj->is_enabled()) {
    $cardids_twitter = $twitterObj->import();
    if ($cardids_twitter != false || is_null($cardids_twitter) || is_array($cardids_twitter)) {
        if (count($cardids_twitter) == 0) {
            echo html_writer::message(notification::INFO, 'Twitter:' . '新規ツイートはありませんでした。');
        } else {
            echo html_writer::message(notification::SUCCESS, 'Twitter:' . 'インポートに成功しました。');
        }
    } else {
        echo html_writer::message(notification::ERROR, 'Twitter:' . 'インポートに失敗しました。');
        $error = $twitterObj->get_error();
        debugging($error->code . ":" . $error->message);
    }
} else {
    echo html_writer::message(notification::INFO, 'Twitter:' . '認証情報が設定されていないため、スキップします。');
}

/**
 * email
 */
$emailObj = new email($sharedpanel);
if ($emailObj->is_enabled()) {
    $cardids_emails = $emailObj->import();

    if ($cardids_emails != false || is_null($cardids_emails) || is_array($cardids_emails)) {
        if (count($cardids_emails) == 0) {
            echo html_writer::message(notification::INFO, 'メール:' . '新規メールはありませんでした。');
        } else {
            echo html_writer::message(notification::SUCCESS, 'メール:' . count($cardids_emails) . '件をメールからインポートしました。');
        }
    } else {
        echo html_writer::message(notification::ERROR, 'メール:' . 'インポートに失敗しました。');
        $error = $emailObj->get_error();
        debugging($error->code . ":" . $error->message);
    }
} else {
    echo html_writer::message(notification::INFO, 'メール:' . '認証情報が設定されていないため、スキップします。');
}

/**
 * Evernote
 */
$evernoteObj = new evernote($sharedpanel);
if ($evernoteObj->is_enabled()) {
    $cardids_evernote = $evernoteObj->import();
    if ($cardids_evernote != false || is_null($cardids_evernote) || is_array($cardids_evernote)) {
        if (count($cardids_evernote) == 0) {
            echo html_writer::message(notification::INFO, 'Evernote:' . '新規はありませんでした。');
        } else {
            echo html_writer::message(notification::SUCCESS, 'Evernote:' . count($cardids_evernote) . '件をインポートしました。');
        }
    } else {
        echo html_writer::message(notification::ERROR, 'Evernote:' . 'インポートに失敗しました。');
        $error = $emailObj->get_error();
        debugging($error->code . ":" . $error->message);
    }
} else {
    echo html_writer::message(notification::INFO, 'Evernote:' . '認証情報が設定されていないため、スキップします。');
}

/**
 * Facebook
 */
$facebookObj = new facebook($sharedpanel);
if($facebookObj->is_enabled()){
    $cardids_facebook = $facebookObj->import();
    if ($cardids_facebook != false || is_null($cardids_facebook) || is_array($cardids_facebook)) {
        if (count($cardids_facebook) == 0) {
            echo html_writer::message(notification::INFO, 'Facebook:' . '新規はありませんでした。');
        } else {
            echo html_writer::message(notification::SUCCESS, 'Facebook:' . count($cardids_facebook) . '件をインポートしました。');
        }
    } else {
        echo html_writer::message(notification::ERROR, 'Facebook:' . 'インポートに失敗しました。');
        $error = $facebookObj->get_error();
        debugging($error->code . ":" . $error->message);
    }
}else{
    echo html_writer::message(notification::INFO, 'Evernote:' . '認証情報が設定されていないため、スキップします。');
}

$total_count = count($cardids_twitter) + count($cardids_emails) + count($cardids_evernote) + count($cardids_facebook);
echo html_writer::message(notification::SUCCESS, 'インポート処理が完了しました。インポートした件数は' . $total_count . '件です。');

echo html_writer::link(new \moodle_url('view.php', ['id' => $cm->id]), '戻る', ['class' => 'btn']);

//----------------------------------------------------------------------------
// Finish the page.
echo $OUTPUT->footer();