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

namespace mod_sharedpanel\form;

require_once(__DIR__ . '/../../../../config.php');

require_login();

defined("MOODLE_INTERNAL") || die();

global $CFG;
require_once("$CFG->libdir/formslib.php");

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

        $this->add_action_buttons(true, get_string('save', 'mod_sharedpanel'));
    }
}