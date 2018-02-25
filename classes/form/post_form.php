<?php

namespace mod_sharedpanel\form;

defined("MOODLE_INTERNAL") || die();
global $CFG;
require_once "$CFG->libdir/formslib.php";

class post_form extends \moodleform
{
    public function definition() {
        $mform = $this->_form;
        $cm = $this->_customdata['cm'];

        $mform->addElement('header', 'header', get_string('post_card', 'sharedpanel'));

        $mform->addElement('editor', 'content', get_string('cardcontent', 'sharedpanel'));
        $mform->setType('content', PARAM_RAW);
        $mform->addRule('content', get_string('required'), 'required', null, 'client');

        $mform->addElement('filepicker', 'attachment', get_string('file'), null, array('maxbytes' => 10 * 1000 * 1000, 'accepted_types' => '*'));

        $mform->addElement('text', 'tag', get_string('tag'));
        $mform->setType('tag', PARAM_NOTAGS);

        $mform->addElement('hidden', 'id', $cm->id);
        $mform->setType('id', PARAM_INT);

        $this->add_action_buttons(true);
    }
}