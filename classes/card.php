<?php
/**
 * Created by PhpStorm.
 * User: yue
 * Date: 2017/10/09
 * Time: 15:22
 */

namespace mod_sharedpanel;

class card
{
    private $moduleinstance;

    private $email_addr;
    private $email_password;

    private $email_host;
    private $email_port;

    function __construct($modinstance) {
        $this->moduleinstance = $modinstance;
    }
}