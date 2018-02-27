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

use mod_sharedpanel\lineid;

require_once(__DIR__ . '/../../../../config.php');

require_login();

defined("MOODLE_INTERNAL") || die();

global $CFG;
require_once($CFG->libdir . '/formslib.php');

class line_form extends \moodleform
{
    public function definition() {
        global $DB, $USER;

        $mform = $this->_form;
        $instanceid = $this->_customdata["instance"];
        $instance = $DB->get_record('sharedpanel', ['id' => $instanceid]);

        $mform->addElement("hidden", "n", $instanceid);
        $mform->setType("n", PARAM_INT);

        $mform->addElement('header', 'header_question', get_string('line_your_line', 'mod_sharedpanel'));
        $mform->setExpanded('header_question');

        $mform->addElement('text', 'lineid', 'LINE ID');
        $mform->setType('lineid', PARAM_TEXT);
        $lineidobj = new lineid($instance);
        if ($line = $lineidobj->get_by_userid($USER->id)) {
            $mform->setDefault('lineid', $line->lineid);
        }
        $mform->addRule('lineid', get_string('line_lineid_required', 'mod_sharedpanel'), 'required');

        $this->add_action_buttons(true, get_string('save', 'mod_sharedpanel'));
    }
}