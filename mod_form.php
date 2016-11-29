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

/**
 * The main sharedpanel configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_sharedpanel
 * @copyright  2016 NAGAOKA Chikako, KITA Toshihiro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form
 */
class mod_sharedpanel_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {

        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are showed.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('sharedpanelname', 'sharedpanel'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'sharedpanelname', 'sharedpanel');

        // Adding the standard "intro" and "introformat" fields.
        $this->add_intro_editor();

        // Adding the rest of sharedpanel settings, spreeading all them into this fieldset
        // ... or adding more fieldsets ('header' elements) if needed for better logic.
        //$mform->addElement('static', 'label1', 'sharedpanelsetting1', 'Your sharedpanel fields go here. Replace me!');
        //$mform->addElement('header', 'sharedpanelfieldset', get_string('sharedpanelfieldset', 'sharedpanel'));
        //$mform->addElement('static', 'label2', 'sharedpanelsetting2', 'Your sharedpanel fields go here. Replace me!');

	
        $mform->addElement('header', 'sharedpanelfieldset', 'Twitter');
        $mform->addElement('text', 'hashtag1', 'インポートするTweetのハッシュタグ');

        $mform->addElement('header', 'sharedpanelfieldset', 'Email');
        $mform->addElement('text', 'emailadr1','インポート対象のメールアドレス');
        $mform->addElement('passwordunmask', 'emailpas1','パスワード');
        $mform->setType('emailpas1', PARAM_RAW);
        $mform->addElement('text', 'emailkey1', 'メール表題に含まれるキーワード');

        $mform->addElement('header', 'sharedpanelfieldset', 'Facebook');
        $mform->addElement('text', 'fbgroup1','FacebookグループID');

        $mform->addElement('header', 'sharedpanelfieldset', 'Evernote');
        $mform->addElement('text', 'emailadr2','インポート対象のメールアドレス(Evernote用)');
        $mform->addElement('passwordunmask', 'emailpas2','パスワード(Evernote用)');
        $mform->setType('emailpas2', PARAM_RAW);
        $mform->addElement('text', 'emailkey2', 'メール表題に含まれるキーワード(Evernote用)');

        $mform->addElement('header', 'sharedpanelfieldset', 'その他');
        $mform->addElement('text', 'config0','config0');

// Add FacebookToken form, unique to this module.
//        <input type="text" name="name" size="30" value=>$facebooktoken>;
//        echo $facebooktoken;       	   

        // Add standard elements, common to all modules.
        $this->standard_coursemodule_elements();

        // Add standard buttons, common to all modules.
        $this->add_action_buttons();
    }
}
