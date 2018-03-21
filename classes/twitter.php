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

use Abraham\TwitterOAuth\TwitterOAuth;
use WebDriver\Exception;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib/twitteroauth/autoload.php');

class twitter extends card
{
    public function is_enabled() {
        $config = get_config('sharedpanel');
        if (empty($config->TWconsumerKey) ||
            empty($config->TWconsumerSecret) ||
            empty($config->TWaccessToken) ||
            empty($config->TWaccessTokenSecret)) {
            return false;
        } else {
            return true;
        }
    }

    public function import() {
        $config = get_config('sharedpanel');
        $connection = new TwitterOAuth(
            trim($config->TWconsumerKey),
            trim($config->TWconsumerSecret),
            trim($config->TWaccessToken),
            trim($config->TWaccessTokenSecret)
        );

        try {
            $credentials = $connection->get("account/verify_credentials");
        } catch (Exception $e) {
            return false;
        }

        if (property_exists($credentials, 'errors')) {
            $this->error->code = $credentials->errors[0]->code;
            $this->error->message = $credentials->errors[0]->message;

            return false;
        }

        $cond = ["q" => $this->moduleinstance->hashtag1, 'count' => '100', "include_entities" => true];

        $latestcard = self::get_last_card('twitter');
        if ($latestcard) {
            $cond['since_id'] = $latestcard->messageid;
        }
        $tweets = $connection->get("search/tweets", $cond);
        if (property_exists($tweets, 'errors') || !$tweets->statuses) {
            return null;
        }

        $cardobj = new card($this->moduleinstance);

        $cardids = [];
        foreach ($tweets->statuses as $tweet) {
            $content = $tweet->text;
            $content = mod_sharedpanel_utf8mb4_encode_numericentity($content);
            $username = mod_sharedpanel_utf8mb4_encode_numericentity($tweet->user->name);
            $cardids[] = $cardobj->add($content, $username, 'twitter', $tweet->id, strtotime($tweet->created_at));
        }

        return $cardids;
    }

    public function get_error() {
        return $this->error;
    }
}