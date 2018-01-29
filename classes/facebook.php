<?php

namespace mod_sharedpanel;

use Facebook\Authentication\AccessToken;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;

defined('MOODLE_INTERNAL') || die();

include __DIR__ . "/../lib/facebook/autoload.php";

class facebook extends card
{
    public function __construct($modinstance) {
        parent::__construct($modinstance);
    }

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
            $this->error->message = "認証情報が入力されていません。";
            return false;
        }

        $fb = new \Facebook\Facebook([
            'app_id' => $config->FBappID,
            'app_secret' => $config->FBsecret,
            'default_graph_version' => 'v2.10',
        ]);
        $access_token = $fb->getApp()->getAccessToken();
        try {
            $response = $fb->get(
                '/' . $this->moduleinstance->fbgroup1 . '/feed',
                $access_token->getValue()
            );
            $body = $response->getDecodedBody();
        } catch (FacebookResponseException $e) {
            return $e->getMessage();
        } catch (FacebookSDKException $e) {
            return $e->getMessage();
        }

        $cardObj = new card($this->moduleinstance);
        $cardids = [];

        foreach ($body['data'] as $data) {
            if ($DB->record_exists('sharedpanel_cards', ['messageid' => $data['id']])) {
                continue;
            }
            if (array_key_exists('message', $data)) {
                $cardids[] = $cardObj->add($data['message'], 'facebook', 'facebook', $data['id'], strtotime($data['updated_time']));

                // If post has attachments...
                try {
                    $response = $fb->get(
                        '/' . $data['id'] . '/attachments',
                        $access_token->getValue()
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
                    $cardObj->add_attachment($context, end($cardids), $content, $filename);
                }
            }
        }

        return $cardids;
    }

    public function get_error() {
        return $this->error;
    }
}