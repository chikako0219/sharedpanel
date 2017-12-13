<?php

namespace mod_sharedpanel\form;

use mod_sharedpanel\lineid;

defined("MOODLE_INTERNAL") || die();

global $CFG;
require_once "$CFG->libdir/formslib.php";

class line_form extends \moodleform
{
    public function definition() {
        global $DB, $USER;

        $mform = $this->_form;
        $instanceid = $this->_customdata["instance"];
        $instance = $DB->get_record('sharedpanel', ['id' => $instanceid]);

        $mform->addElement("hidden", "n", $instanceid);
        $mform->setType("n", PARAM_INT);

        $mform->addElement('header', 'header_question', 'あなたのLINE');
        $mform->setExpanded('header_question');

        $mform->addElement('text', 'lineid', 'LINE ID');
        $mform->setType('lineid', PARAM_TEXT);
        $lineidObj = new lineid($instance);
        if ($line = $lineidObj->get($USER->id)) {
            $mform->setDefault('lineid', $line->lineid);
        }
        $mform->addRule('lineid', 'LINE IDは必須です', 'required');

        $this->add_action_buttons(true, "保存する");
    }
}