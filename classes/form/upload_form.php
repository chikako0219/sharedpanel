<?php

namespace mod_sharedpanel\form;

defined("MOODLE_INTERNAL") || die();

global $CFG;
require_once "$CFG->libdir/formslib.php";

class upload_form extends \moodleform
{
    public function definition() {
        $mform = $this->_form;
        $cmid = $this->_customdata["cmid"];

        $mform->addElement("hidden", "cmid", $cmid);
        $mform->setType("cmid", PARAM_INT);

        $mform->addElement('textarea', 'comment', get_string('message', 'mod_sharedpanel'), 'wrap="virtual" rows="20" cols="50"');
        $mform->setType('comment', PARAM_TEXT);
        $mform->addRule('comment', get_string('upload_required_comment', 'mod_sharedpanel'), 'required');

        $mform->addElement('text', 'author_name', get_string('upload_optional_name', 'mod_sharedpanel'));
        $mform->setType('author_name', PARAM_TEXT);

        $this->add_action_buttons(true, get_string('save', 'core'));
    }
}