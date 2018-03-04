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
use Facebook\Exceptions\FacebookResponseException;

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');
require_once(dirname(__FILE__) . '/locallib.php');

global $DB, $PAGE, $OUTPUT;

$id = optional_param('id', 0, PARAM_INT);

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

$cardidstwitter = $cardidsemails = $cardidsevernote = $cardidsfacebook = null;

// Twitter.
$twitterobj = new twitter($sharedpanel);
if ($twitterobj->is_enabled()) {
    $cardidstwitter = $twitterobj->import();
    if ($cardidstwitter != false || is_null($cardidstwitter) || is_array($cardidstwitter)) {
        if (count($cardidstwitter) == 0) {
            echo html_writer::message(notification::INFO, get_string('import_twitter_no_tweets', 'mod_sharedpanel'));
        } else {
            $str = new \stdClass();
            $str->source = 'Twitter';
            $str->count = count($cardidstwitter);
            echo html_writer::message(notification::SUCCESS, get_string('import_success', 'mod_sharedpanel', $str));
        }
    } else {
        echo html_writer::message(notification::ERROR, get_string('import_failed', 'mod_sharedpanel', 'Twitter'));
        $error = $twitterobj->get_error();
        debugging($error->code . ":" . $error->message);
    }
} else {
    echo html_writer::message(notification::INFO, get_string('import_no_authinfo', 'mod_sharedpanel', 'Twitter'));
}

// Email.
$emailobj = new email($sharedpanel);
if ($emailobj->is_enabled()) {
    $cardidsemails = $emailobj->import();

    if ($cardidsemails != false || is_null($cardidsemails) || is_array($cardidsemails)) {
        if (count($cardidsemails) == 0) {
            echo html_writer::message(notification::INFO, get_string('import_mail_no_mails', 'mod_sharedpanel'));
        } else {
            $str = new \stdClass();
            $str->count = count($cardidsemails);
            $str->source = 'email';
            echo html_writer::message(notification::SUCCESS, get_string('import_success', 'mod_sharedpanel', $str));
        }
    } else {
        echo html_writer::message(notification::ERROR, get_string('import_failed', 'mod_sharedpanel', 'mail'));
        $error = $emailobj->get_error();
        $cardidsemails = [];
        debugging($error->code . ":" . $error->message);
    }
} else {
    echo html_writer::message(notification::INFO, get_string('import_no_authinfo', 'mod_sharedpanel', 'mail'));
}

// Evernote.
$evernoteobj = new evernote($sharedpanel);
if ($evernoteobj->is_enabled()) {
    $cardidsevernote = $evernoteobj->import();
    if ($cardidsevernote != false || is_null($cardidsevernote) || is_array($cardidsevernote)) {
        if (count($cardidsevernote) == 0) {
            echo html_writer::message(notification::INFO, 'Evernote:' . get_string('import_mail_no_mails', 'mod_sharedpanel'));
        } else {
            $str = new \stdClass();
            $str->count = count($cardidsevernote);
            $str->source = 'Evernote';
            echo html_writer::message(notification::SUCCESS, get_string('import_success', 'mod_sharedpanel', $str));
        }
    } else {
        echo html_writer::message(notification::ERROR, get_string('import_failed', 'mod_sharedpanel', 'Evernote'));
        $error = $emailobj->get_error();
        $cardidsevernote = [];
        debugging($error->code . ":" . $error->message);
    }
} else {
    echo html_writer::message(notification::INFO, get_string('import_no_authinfo', 'mod_sharedpanel', 'Evernote'));
}

// Facebook.
$facebookobj = new facebook($sharedpanel);
if ($facebookobj->is_enabled()) {
    $cardidsfacebook = $facebookobj->import();
    if ($cardidsfacebook != false || is_null($cardidsfacebook) || is_array($cardidsfacebook)) {
        if (count($cardidsfacebook) == 0) {
            echo html_writer::message(notification::INFO, get_string('import_no_new', 'mod_sharedpanel', 'Facebook'));
        } else {
            $str = new \stdClass();
            $str->count = count($cardidsfacebook);
            $str->source = 'Facebook';
            echo html_writer::message(notification::SUCCESS, get_string('import_success', 'mod_sharedpanel', $str));
        }
    } else {
        $error = $facebookobj->get_error();
        echo html_writer::message(notification::ERROR, get_string('import_failed', 'mod_sharedpanel', 'Facebook') . '(' . $error->message . ')');
        $cardidsfacebook = [];
    }
} else {
    echo html_writer::message(notification::INFO, get_string('import_no_authinfo', 'mod_sharedpanel', 'Facebook'));
}

$totalcount = count($cardidstwitter) + count($cardidsemails) + count($cardidsevernote) + count($cardidsfacebook);
echo html_writer::message(notification::SUCCESS, get_string('import_finished', 'mod_sharedpanel', $totalcount));

echo html_writer::link(new \moodle_url('view.php', ['id' => $cm->id]), get_string('back', 'core'), ['class' => 'btn']);

echo $OUTPUT->footer();