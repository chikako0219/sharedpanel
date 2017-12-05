<?php

namespace mod_sharedpanel;

defined('MOODLE_INTERNAL') || die();

include __DIR__ . "/../lib/facebook/autoload.php";

class facebook extends card
{
    public function __construct($modinstance) {
        parent::__construct($modinstance);
    }

    public function import() {
        $config = get_config('sharedpanel');
        if (empty($config->FBappID) || empty($config->FBredirectUrl) || empty($config->FBsecret) || empty($config->FBtoken)) {
            $this->error->message = "認証情報が入力されていません。";
            return false;
        }


    }

    public function get_error() {
        return $this->error;
    }
}