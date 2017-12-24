<?php

namespace mod_sharedpanel\form;

defined("MOODLE_INTERNAL") || die();
global $CFG;
require_once "$CFG->libdir/formslib.php";

class gcard_form extends \moodleform
{
    public function definition() {
        $mform = $this->_form;
        $cm = $this->_customdata['cm'];

        $mform->addElement('editor', 'content', get_string('gcardcontent', 'sharedpanel'));
        $mform->setType('content', PARAM_RAW);

        $mform->addElement('filepicker', 'userfile', get_string('file'), null, ['accepted_types' => 'image']);

        $mform->addElement('text', 'sizex', 'X size', 'sharedpanel');
        $mform->setType('sizex', PARAM_INT);
        $mform->setDefault('sizex', 600);

        $mform->addElement('text', 'sizey', 'Y size', 'sharedpanel');
        $mform->setType('sizey', PARAM_INT);
        $mform->setDefault('sizey', 600);

        $mform->addElement('text', 'tag', 'Tag', 'sharedpanel');
        $mform->setType('tag', PARAM_TEXT);

        $mform->addElement('hidden', 'id', $cm->id);
        $mform->setType('id', PARAM_INT);

        $this->add_action_buttons();
    }
}