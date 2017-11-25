<?php

namespace mod_sharedpanel;

use Abraham\TwitterOAuth\TwitterOAuth;

defined('MOODLE_INTERNAL') || die();

include __DIR__ . "/../lib/twitteroauth/autoload.php";

class twitter extends card
{
    function import() {
        $config = get_config('sharedpanel');
        if (empty($config->TWconsumerKey) || empty($config->TWconsumerSecret) || empty($config->TWaccessToken) || empty($config->TWaccessTokenSecret)) {
            return false;
        }

        $connection = new TwitterOAuth(
            trim($config->TWconsumerKey),
            trim($config->TWconsumerSecret),
            trim($config->TWaccessToken),
            trim($config->TWaccessTokenSecret)
        );

        $credentials = $connection->get("account/verify_credentials");

        $latest_card = self::get_last_card('twitter');

        $tweets = $connection->get("search/tweets",
            ["q" => $this->moduleinstance->hashtag1, 'count' => '100', "include_entities" => true, 'since_id' => $latest_card->messageid]);

        $cardObj = new card($this->moduleinstance);

        $cardids = [];
        foreach ($tweets->statuses as $tweet) {
            $content = $tweet->text;
            $content = mod_sharedpanel_utf8mb4_encode_numericentity($content);
            $username = mod_sharedpanel_utf8mb4_encode_numericentity($tweet->user->name);
            $cardids[] = $cardObj->add_card($content, $username, 'twitter', $tweet->id, strtotime($tweet->created_at));
        }

        return $cardids;
    }
}