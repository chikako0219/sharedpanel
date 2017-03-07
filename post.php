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

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

// --------------------------------------------------------------------------------

//moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");
require_once("locallib.php");

confirm_sesskey();
 
class post_form extends moodleform {
    //Add elements to form
    public function definition() {
        global $CFG;
 
        $mform = $this->_form; // Don't forget the underscore! 
        $cm = $this->_customdata['cm'];
        $context  = context_module::instance($cm->id);
 
	$mform->addElement('editor', 'content', get_string('cardcontent', 'sharedpanel'));
	$mform->setType('content', PARAM_RAW);
        $mform->addRule('content', get_string('required'), 'required', null, 'client');

	$mform->addElement('filepicker', 'userfile', get_string('file'), null, array('maxbytes' => 10*1000*1000, 'accepted_types' => '*'));

        $mform->addElement('text', 'tag', get_string('tag'));
        $mform->setType('tag', PARAM_NOTAGS);

//        $mform->addElement('text', 'sender', get_string('cardsender','sharedpanel'));
//        $mform->setType('sender', PARAM_NOTAGS);

//        $mform->addElement('text', 'email', get_string('email'));
//        $mform->setType('email', PARAM_NOTAGS);

        $mform->addElement('hidden', 'id', $cm->id);
        $mform->setType('id', PARAM_INT);
//        $mform->addElement('hidden', 'cmid');
//        $mform->setType('cmid', PARAM_INT);

        $this->add_action_buttons(true);

    }
    //Custom validation should be added here
//    function validation($data, $files) {
//        return array();
//    }

}

// --------------------------------------------------------------------------------
 
$id = optional_param('id', 0, PARAM_INT); // course module id
$c  = optional_param('c', 0, PARAM_INT);  // ... card ID
 
if ($id) {
    $cm         = get_coursemodule_from_id('sharedpanel', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $sharedpanel  = $DB->get_record('sharedpanel', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID!!');
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
 
$PAGE->set_url('/mod/sharedpanel/post.php', array('id' => $cm->id));
$PAGE->set_title(format_string($sharedpanel->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
 
/*
 * Other things you may want to set - remove if not needed.
 * $PAGE->set_cacheable(false);
 * $PAGE->set_focuscontrol('some-html-id');
 * $PAGE->add_body_class('sharedpanel-'.$somevar);
 */
 
// Output starts here.

// mimicing glossary/edit.php glossary/edit_form.php  t-kita

//$mform = new post_form();
$mform = new post_form(null, array('cm'=>$cm));

//Form processing and displaying is done here
if ($mform->is_cancelled()) {
    redirect("view.php?id=$id","キャンセルしました。",3);
} else if ($fromform = $mform->get_data()) {
    // In this case you process validated data. $mform->get_data() returns data posted in form.
    $time = time();
    $ret1 = "";
    if (!$c){
      $data = new stdClass;
      $data->sharedpanelid = $sharedpanel->id;
      $data->userid = $USER->id;
//      $data->timeposted= strtotime($updated);
      $data->timecreated  = $time;
//      $data->timemodified = $data->timecreated;
      $data->inputsrc = "moodlepost";
//      $data->content = $ret1;
      $data->content = "";
      $data->id = $DB->insert_record('sharedpanel_cards', $data);
    }
    $data->timeposted=    $time;
    $data->timemodified = $time;

    $formcontent = $fromform->content["text"];
    $filecontent = $mform->get_file_content('userfile');
    $ftag =  $fromform->tag;

    $name= $USER->lastname . " " . $USER->firstname;
    $data->sender = $name;
    if ($filecontent){
      $ret1 .= "<img src='data:image/gif;base64,".mod_sharedpanel_compress_img($filecontent,600)."' width=85%><br>";

    }
//    $ret1 .= $formcontent.'<br/>'.strftime('%c',$time).'<br/> posted on Moodle';
    $ret1 .= $formcontent;
    $data->content = $ret1;

    $DB->update_record('sharedpanel_cards', $data);

    if ($ftag){
      $tag = new stdClass;
      $tag->cardid = $data->id;
      $tag->userid = $USER->id;
      $tag->timecreated = $data->timemodified;
      $tag->tag = $ftag;
      $tag->id = $DB->insert_record('sharedpanel_card_tags', $tag);
    }

    redirect("view.php?id=$id","保存されました。",5);
} else {
    echo $OUTPUT->header();

    $mform->display();
} 

echo $OUTPUT->footer();
