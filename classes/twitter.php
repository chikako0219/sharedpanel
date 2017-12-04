<?php

namespace mod_sharedpanel;

use Abraham\TwitterOAuth\TwitterOAuth;
use WebDriver\Exception;

defined('MOODLE_INTERNAL') || die();

include __DIR__ . "/../lib/twitteroauth/autoload.php";

class twitter extends card
{
    private $error;

    public function __construct($modinstance) {
        $this->error = new \stdClass();
        $this->error->code = 0;
        $this->error->message = "";

        parent::__construct($modinstance);
    }

    public function import() {
        $config = get_config('sharedpanel');
        if (empty($config->TWconsumerKey) || empty($config->TWconsumerSecret) || empty($config->TWaccessToken) || empty($config->TWaccessTokenSecret)) {
            $this->error->message = "認証情報が入力されていません。";
            return false;
        }

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

        $latest_card = self::get_last_card('twitter');
        if ($latest_card) {
            $cond['since_id'] = $latest_card->messageid;
        }
        $tweets = $connection->get("search/tweets", $cond);
        if (!$tweets->statuses) {
            return null;
        }

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

    public function get_error() {
        return $this->error;
    }
}