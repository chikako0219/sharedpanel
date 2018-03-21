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

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once(__DIR__ . "/lib/Facebook/autoload.php");

/**
 * Module instance settings form
 */
class mod_sharedpanel_mod_form extends moodleform_mod
{

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG, $DB, $OUTPUT;

        $mform = $this->_form;
        $config = get_config('sharedpanel');

        $instanceid = $this->get_instance();
        if ($instanceid) {
            $instance = $DB->get_record('sharedpanel', ['id' => $instanceid], '*', MUST_EXIST);
        } else {
            $instance = null;
        }

        $mform->addElement('header', 'general', get_string('general', 'form'));
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
        if ($CFG->branch >= 29) {
            $this->standard_intro_elements();
        } else {
            $this->add_intro_editor();
        }

        // Twitter.
        $mform->addElement('header', 'sharedpanelfieldset_twitter', 'Twitter');
        $mform->setExpanded('sharedpanelfieldset_twitter');
        $mform->addElement('text', 'hashtag1', get_string('form_import_tweet_hashtag', 'mod_sharedpanel'));
        $mform->setType('hashtag1', PARAM_TEXT);

        // Email.
        $mform->addElement('header', 'sharedpanelfieldset_email', 'Email');
        $mform->setExpanded('sharedpanelfieldset_email');

        $mform->addElement('text', 'emailadr1', get_string('form_emailadr1', 'mod_sharedpanel'));
        $mform->setType('emailadr1', PARAM_TEXT);
        $mform->addElement('text', 'emailkey1', get_string('form_emailkey1', 'mod_sharedpanel'));
        $mform->setType('emailkey1', PARAM_TEXT);
        $mform->addElement('text', 'emailhost', get_string('form_emailhost', 'mod_sharedpanel'));
        $mform->setType('emailhost', PARAM_TEXT);
        $mform->addElement('text', 'emailport', get_string('form_emailport', 'mod_sharedpanel'));
        $mform->setType('emailport', PARAM_INT);
        $mform->addElement('advcheckbox', 'emailisssl', get_string('form_emailisssl', 'mod_sharedpanel'));
        $mform->addElement('passwordunmask', 'emailpas1', get_string('password', 'core'));
        $mform->setType('emailpas1', PARAM_TEXT);

        // Facebook.
        $fb = new \Facebook\Facebook([
            'app_id' => $config->FBappID,
            'app_secret' => $config->FBsecret
        ]);

        $mform->addElement('header', 'sharedpanelfieldset_facebook', 'Facebook');
        $mform->setExpanded('sharedpanelfieldset_facebook');

        $mform->addElement('text', 'fbgroup1', get_string('form_fbgroup1', 'mod_sharedpanel'));
        $mform->setType('fbgroup1', PARAM_TEXT);

        $mform->addElement('html', '<h5>Facebook User Access Token</h5>');

        if ($instance) {
            $mform->addElement('html',
                '<div class="well">' . get_string('facebook_get_user_access_token_msg', 'mod_sharedpanel') . '</div>');

            if ($instance->fbuseraccesstoken) {
                $mform->addElement('html',
                    '<div class="well">' . get_string('facebook_get_user_access_token_ok', 'mod_sharedpanel') . '</div>');
            } else {
                $mform->addElement('html',
                    '<div class="well">' . get_string('facebook_get_user_access_token_notyet', 'mod_sharedpanel') . '</div>');
            }
            $callback = new moodle_url($CFG->wwwroot . '/mod/sharedpanel/facebook_login.php');
            $helper = $fb->getRedirectLoginHelper();
            $url = new moodle_url($helper->getLoginUrl($callback->out(true), ['user_managed_groups']));
            $action = new \popup_action("click", $url, ["width" => "600px"]);
            $mform->addElement('html',
                $OUTPUT->action_link($url->out(),
                    get_string('facebook_get_user_access_token', 'mod_sharedpanel'),
                    $action,
                    ["class" => "btn btn-success"])
            );
        } else {
            $mform->addElement('html',
                '<div class="well">' . get_string('facebook_get_user_access_token_msg_reload', 'mod_sharedpanel') . '</div>');
        }

        // Evernote.
        $mform->addElement('header', 'sharedpanelfieldset_evernote', 'Evernote');
        $mform->setExpanded('sharedpanelfieldset_evernote');

        $mform->addElement('text', 'emailadr2', get_string('form_emailadr2', 'mod_sharedpanel'));
        $mform->setType('emailadr2', PARAM_TEXT);
        $mform->addElement('passwordunmask', 'emailpas2', get_string('form_emailpas2', 'mod_sharedpanel'));
        $mform->setType('emailpas2', PARAM_RAW);
        $mform->addElement('text', 'emailkey2', get_string('form_emailkey2', 'mod_sharedpanel'));
        $mform->setType('emailkey2', PARAM_TEXT);

        // LINE.
        $mform->addElement('header', 'sharedpanelfieldset_line', 'LINE');
        $mform->setExpanded('sharedpanelfieldset_line');

        $mform->addElement('text', 'line_channel_id', 'Channel ID');
        $mform->setType('line_channel_id', PARAM_TEXT);
        $mform->addElement('text', 'line_channel_secret', 'Channel secret key');
        $mform->setType('line_channel_secret', PARAM_TEXT);
        $mform->addElement('text', 'line_channel_access_token', 'Channel access token');
        $mform->setType('line_channel_access_token', PARAM_TEXT);

        if ($instanceid) {
            $_SESSION['sharedpanel_instanceid'] = $instanceid;
            $mform->addElement('html', '<h5>Webhook URL</h5>');
            if ((array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS'] === 'on')) {
                $mform->addElement('html',
                    '<div class="well">' . $CFG->wwwroot . '/mod/sharedpanel/line_webhook.php?id=' . $instanceid . '</div>');
            } else {
                $mform->addElement('html',
                    '<div class="well">' . get_string('form_line_warning_https', 'mod_sharedpanel') . '</div>');
            }
        }

        // Add standard elements, common to all modules.
        $this->standard_coursemodule_elements();

        // Add standard buttons, common to all modules.
        $this->add_action_buttons();
    }
}