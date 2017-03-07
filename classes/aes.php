<?php

namespace mod_sharedpanel;

class util
{
    public static function generate_key()
    {
        $bytes = openssl_random_pseudo_bytes(32);
        return bin2hex($bytes);
    }

    public static function get_aes_encrypt_string($raw_string, $encryption_key)
    {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-128-ecb'));
        return openssl_encrypt($raw_string, 'AES-128-ECB', $encryption_key, 0, $iv);
    }
}