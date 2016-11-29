<?php

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

// --------------------------------------------------------------------------------

//moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");
 
class post_form extends moodleform {
    //Add elements to form
    public function definition() {
        global $CFG;
 
        $mform = $this->_form; // Don't forget the underscore! 
        $cm = $this->_customdata['cm'];
        $context  = context_module::instance($cm->id);
 
	$mform->addElement('editor', 'content', get_string('gcardcontent', 'sharedpanel'));
	$mform->setType('content', PARAM_RAW);
//        $mform->addRule('content', get_string('required'), 'required', null, 'client');

	$mform->addElement('filepicker', 'userfile', get_string('file'), null, array('maxbytes' => 10*1000*1000, 'accepted_types' => '*'));

//        $mform->addElement('text', 'tag', get_string('tag'));
//        $mform->setType('tag', PARAM_NOTAGS);

        $mform->addElement('text', 'sizex', 'X size', 'sharedpanel');
        $mform->setType('sizex', PARAM_INT);
        $mform->setDefault('sizex', 600);

        $mform->addElement('text', 'sizey', 'Y size', 'sharedpanel');
        $mform->setType('sizey', PARAM_INT);
        $mform->setDefault('sizey', 600);

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
$context = get_context_instance(CONTEXT_MODULE, $cm->id);
//$context = context_module::instance($cm->id);
 
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
echo $OUTPUT->header();

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
      $data->timecreated  = $time;
      $data->content = "";
      $data->id = $DB->insert_record('sharedpanel_gcards', $data);
    }
    $data->timemodified = $time;

    $formcontent = $fromform->content["text"];
    $filecontent = $mform->get_file_content('userfile');
    $ftag =  $fromform->tag;

    if ($filecontent){
//      $ret1 .= "<img src='data:image/gif;base64,".base64_encode($filecontent)."' width=85%><br>";
      $ret1 .= "<img src='data:image/gif;base64,".compress_img($filecontent,600)."' width=200px><br>";

    }
    $ret1 .= $formcontent;
    $data->content = $ret1;
    $data->sizex = $fromform->sizex;
    $data->sizey = $fromform->sizey;

    $DB->update_record('sharedpanel_gcards', $data);

/*
    if ($ftag){
      $tag = new stdClass;
      $tag->cardid = $data->id;
      $tag->userid = $USER->id;
      $tag->timecreated = $data->timemodified;
      $tag->tag = $ftag;
      $tag->id = $DB->insert_record('sharedpanel_card_tags', $tag);
    }
*/

    redirect("view.php?id=$id","保存されました。",5);

} else {
    // this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
    // or on the first display of the form.
    // Set default data (if any)
    //  $mform->set_data($toform);
    //displays the form
    $mform->display();
} 

echo $OUTPUT->footer();


function compress_img($attached, $width){
//  $imagea= imap_base64($attached);
//  $imagea= imagecreatefromstring($imagea);
  $imagea= imagecreatefromstring($attached);
  $imagea= imagescale($imagea, $width, -1);  // proportionally compress image with $width
  $jpegfile= tempnam("/tmp", "email-jpg-");
  imagejpeg($imagea,$jpegfile);
  imagedestroy($imagea);
  $attached= base64_encode(file_get_contents($jpegfile));
  unlink($jpegfile);
  return $attached;
}
