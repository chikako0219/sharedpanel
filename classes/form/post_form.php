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

class post_form extends \moodleform
{
    public function definition() {
        $mform = $this->_form;
        $cm = $this->_customdata['cm'];

        $mform->addElement('header', 'header', get_string('post_card', 'sharedpanel'));

        $mform->addElement('editor', 'content', get_string('cardcontent', 'sharedpanel'));
        $mform->setType('content', PARAM_RAW);
        $mform->addRule('content', get_string('required'), 'required', null, 'client');

        $mform->addElement('filepicker', 'attachment', get_string('file'), null,
            ['maxbytes' => 10 * 1000 * 1000, 'accepted_types' => '*']);

        $mform->addElement('text', 'tag', get_string('tag'));
        $mform->setType('tag', PARAM_NOTAGS);

        $mform->addElement('hidden', 'id', $cm->id);
        $mform->setType('id', PARAM_INT);

        $this->add_action_buttons(true);
    }
}