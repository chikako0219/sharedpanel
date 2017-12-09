<?php

namespace mod_sharedpanel;

defined('MOODLE_INTERNAL') || die();

class evernote extends card
{
    protected $moduleinstance;

    private $email_addr;
    private $email_password;
    private $email_port;

    private $cardObj;

    function __construct($modinstance) {
        $this->email_addr = $modinstance->emailadr1;
        $this->email_password = aes::get_aes_decrypt_string($modinstance->emailpas1, $modinstance->encryptionkey);
        $this->email_port = 993;
        $this->cardObj = new card($modinstance);

        parent::__construct($modinstance);
    }

    public function get($date = null) {
        global $DB;

        $cond = [
            'inputsrc' => 'evernote',
            'sharedpanelid' => $this->moduleinstance->id,
            'hidden' => 0
        ];
        if (!is_null($date)) {
            $cond['timeposted'] = $date;
        }

        return $DB->get_record('sharedpanel_cards', $cond);
    }

    public function is_exists($date = null) {
        global $DB;

        $cond = [
            'inputsrc' => 'evernote',
            'sharedpanelid' => $this->moduleinstance->id,
            'hidden' => 0
        ];
        if (!is_null($date)) {
            $cond['timeposted'] = $date;
        }

        return $DB->record_exists('sharedpanel_cards', $cond);
    }

    public function import() {
        global $DB, $USER;

        $mbox = imap_open('{' . $this->moduleinstance->emailhost . ':' . $this->moduleinstance->emailport . '/novalidate-cert/imap/ssl}' . "INBOX", $this->email_addr, $this->email_password, OP_READONLY);
        if (!$mbox) {
            $this->error->message = imap_last_error();
            return false;
        }
        $messageids = imap_search($mbox, "SUBJECT " . $this->moduleinstance->emailkey1, SE_UID);
        if (!$messageids) {
            return null;
        }
        $cardids = [];
        foreach ($messageids as $num => $messageid) {
            if ($DB->record_exists('sharedpanel_cards', ['messageid' => $messageid])) {
                continue;
            }

            $num++;
            $head = imap_headerinfo($mbox, $num);
            $body = imap_fetchbody($mbox, $num, 1, FT_INTERNAL);
            $body = trim($body);

            if (!strpos($head->from[0]->host, "evernote.com")) {
                continue;
            }

            $subject = mb_convert_encoding(imap_base64($body), 'utf-8', 'auto');

            $cardid = $this->cardObj->add_card($subject, $head->fromaddress, 'evernote', $messageid);
            $cardids[] = $cardid;
            foreach (mod_sharedpanel_get_tags($subject) as $tagstr) {
                $tagObj = new tag($this->moduleinstance);
                $tagObj->set($cardid, $tagstr, $USER->id);
            }
        }

        return $cardids;
    }

    public function get_error() {
        return $this->error;
    }
}