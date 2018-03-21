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
 * @package    mod_sharedpanel
 * @copyright  2016 NAGAOKA Chikako, KITA Toshihiro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_sharedpanel;

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');
require_once(__DIR__ . "/lib/Facebook/autoload.php");

global $CFG, $DB;

$state = required_param('state', PARAM_TEXT);
$config = get_config('sharedpanel');

$fb = new \Facebook\Facebook([
    'app_id' => $config->FBappID,
    'app_secret' => $config->FBsecret
]);

$callback = new \moodle_url($CFG->wwwroot . '/mod/sharedpanel/facebook_login.php');

$helper = $fb->getRedirectLoginHelper();
// If detect invalid access, close before execution.
if ($helper->getError()) {
    close_window(0, true);
}

try {
    $_SESSION['FBRLH_state'] = $state;
    $_SESSION['state'] = $state;

    $accesstoken = $helper->getAccessToken($callback->out());
} catch (\Facebook\Exceptions\FacebookResponseException $e) {
    redirect(new \moodle_url($CFG->wwwroot),
        get_string('facebook_get_user_access_token_failed', 'mod_sharedpanel', $e->getMessage())
    );
} catch (\Facebook\Exceptions\FacebookSDKException $e) {
    redirect(new \moodle_url($CFG->wwwroot),
        get_string('facebook_get_user_access_token_failed', 'mod_sharedpanel', $e->getMessage())
    );
}
if (isset($accesstoken)) {
    $_SESSION['FBRLH_state'] = $state;
    $_SESSION['state'] = $state;

    $data = new \stdClass();
    $data->id = $_SESSION['sharedpanel_instanceid'];
    $data->fbuseraccesstoken = $accesstoken->getValue();

    $DB->update_record('sharedpanel', $data);

    close_window(0, true);
} else if ($helper->getError()) {
    close_window(0, true);
}