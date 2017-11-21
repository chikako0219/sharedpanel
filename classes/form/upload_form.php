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

        $mform->addElement('textarea', 'comment', 'メッセージ', 'wrap="virtual" rows="20" cols="50"');
        $mform->setType('comment', PARAM_TEXT);
        $mform->addRule('comment', 'コメントは必須です', 'required');

        $mform->addElement('text', 'author_name', '名前(任意)');
        $mform->setType('author_name', PARAM_TEXT);

        $this->add_action_buttons(true, "保存する");
    }
}