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

namespace mod_sharedpanel;

use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . "/../lib/Facebook/autoload.php");

class facebook extends card
{
    public function is_enabled() {
        $config = get_config('sharedpanel');
        if (empty($config->FBappID) ||
            empty($config->FBsecret)
        ) {
            return false;
        }
        return true;
    }

    public function import() {
        global $DB;
        $cm = get_coursemodule_from_instance('sharedpanel', $this->moduleinstance->id);
        $context = \context_module::instance($cm->id);

        $config = get_config('sharedpanel');
        if (empty($config->FBappID) || empty($config->FBsecret)) {
            $this->error->message = get_string('facebook_no_authinfo', 'mod_sharedpanel');
            return false;
        }
        if (empty($this->moduleinstance->fbgroup1)) {
            $this->error->code = 400;
            $this->error->message = get_string('facebook_no_groupid', 'mod_sharedpanel');
            return false;
        }

        $fb = new \Facebook\Facebook([
            'app_id' => $config->FBappID,
            'app_secret' => $config->FBsecret
        ]);
        $accesstoken = $this->moduleinstance->fbuseraccesstoken;
        $fb->setDefaultAccessToken($accesstoken);

        $token = $fb->getDefaultAccessToken();
        if ($token->isExpired()) {
            $this->error->code = 400;
            $this->error->message = get_string('facebook_get_user_access_token_expired', 'mod_sharedpanel');

            return false;
        }

        try {
            $response = $fb->get(
                '/' . $this->moduleinstance->fbgroup1 . '/feed',
                $accesstoken
            );
            $body = $response->getDecodedBody();
        } catch (FacebookResponseException $e) {
            $this->error->code = $e->getCode();
            $this->error->message = $e->getMessage();
            return false;
        } catch (FacebookSDKException $e) {
            $this->error->code = $e->getCode();
            $this->error->message = $e->getMessage();
            return false;
        }

        $cardobj = new card($this->moduleinstance);
        $cardids = [];

        foreach ($body['data'] as $data) {
            if ($DB->record_exists('sharedpanel_cards', ['messageid' => $data['id']])) {
                continue;
            }
            if (array_key_exists('message', $data)) {
                $cardids[] = $cardobj->add($data['message'], 'facebook', 'facebook', $data['id'], strtotime($data['updated_time']));

                // If post has attachments...
                try {
                    $response = $fb->get(
                        '/' . $data['id'] . '/attachments',
                        $accesstoken
                    );
                    $body = $response->getDecodedBody();
                } catch (FacebookResponseException $e) {
                    return $e->getMessage();
                } catch (FacebookSDKException $e) {
                    return $e->getMessage();
                }
                if (!empty($body['data']) && $body['data'][0]['type'] === 'photo') {
                    $content = file_get_contents($body['data'][0]['media']['image']['src']);
                    $filename = basename($body['data'][0]['media']['image']['src']);
                    $cardobj->add_attachment($context, end($cardids), $content, $filename);
                }
            }
        }

        return $cardids;
    }

    public function get_error() {
        return $this->error;
    }
}