<?php

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
 
$id = optional_param('id', 0, PARAM_INT); // course module id
$c  = optional_param('c', 0, PARAM_INT);  // ... card ID
 
if ($id) {
    $cm         = get_coursemodule_from_id('sharedpanel', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $sharedpanel  = $DB->get_record('sharedpanel', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}
 
require_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);
//$context = context_module::instance($cm->id);
 
/*
$event = \mod_sharedpanel\event\course_module_viewed::create(array(
    'objectid' => $PAGE->cm->instance,
    'context' => $PAGE->context,
));
$event->add_record_snapshot('course', $PAGE->course);
// In the next line you can use $PAGE->activityrecord if you have set it, or skip this line if you don't have a record.
$event->add_record_snapshot($PAGE->cm->modname, $activityrecord);
$event->trigger();
*/
 
// Print the page header.
 
$PAGE->set_url('/mod/sharedpanel/deletecard.php', array('id' => $cm->id));
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
 

// Twitter
include_once(dirname(__FILE__).'/twitter.php');
//add_card_from_twitter($sharedpanel, "#amakusa1125");
add_card_from_twitter($sharedpanel);

// Facebook
include_once(dirname(__FILE__).'/facebook.php');
add_card_from_facebook($sharedpanel);

// Email
include_once(dirname(__FILE__).'/email.php');
if ($sharedpanel->emailadr1 == ""){
  echo "<br/><hr>no email address.<br/>"; ob_flush(); flush();
}else{
  //add_card_from_email($sharedpanel);
  add_card_from_email($sharedpanel, 600); // 大きな画像は幅600pxに縮小される
}
// Evernote
include_once(dirname(__FILE__).'/evernote.php');
add_card_from_evernote($sharedpanel);

echo "<br/><hr><a href=\"./view.php?id=$id\"><span style='background-color:orange;padding:1ex;color:black;'><b>Importing done.</b></span></a><br/>"; ob_flush(); flush();

//----------------------------------------------------------------------------
// Finish the page.
echo $OUTPUT->footer();




// mkdir($CFG->dataroot.'/sharedpanel/');
// $outfile= $CFG->dataroot.'/sharedpanel/'.date("Y-md-His").$andkey;
// file_put_contents($outfile,$ret1);
 
// $ret2 = file_get_contents($outfile);
// echo $ret2;

