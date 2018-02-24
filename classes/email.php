<?php

namespace mod_sharedpanel;

defined('MOODLE_INTERNAL') || die();

class email extends card
{
    protected $moduleinstance;

    private $email_addr;
    private $email_password;

    private $email_port;

    private $cardObj;

    function __construct($modinstance) {
        $this->email_addr = $modinstance->emailadr1;
        $this->email_password = $modinstance->emailpas1;
        $this->email_port = $modinstance->emailport;
        $this->cardObj = new card($modinstance);

        parent::__construct($modinstance);
    }

    public function is_enabled() {
        if (empty($this->moduleinstance->emailhost) ||
            empty($this->moduleinstance->emailport) ||
            empty($this->email_addr) ||
            empty($this->email_password)) {
            return false;
        }
        return true;
    }

    public function get($date = null) {
        global $DB;

        $cond = [
            'inputsrc' => 'email',
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
            'inputsrc' => 'email',
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

        if ($this->moduleinstance->emailisssl === '1') {
            $mailbox = '{' . $this->moduleinstance->emailhost . ':' . $this->moduleinstance->emailport . '/novalidate-cert/imap/ssl}' . "INBOX";
        } else {
            $mailbox = '{' . $this->moduleinstance->emailhost . ':' . $this->moduleinstance->emailport . '/novalidate-cert/imap}' . "INBOX";
        }

        $mbox = imap_open($mailbox, $this->email_addr, $this->email_password, OP_READONLY);
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

            if (strpos($head->from[0]->host, "evernote.com") !== false) {
                continue;
            }

            $subject = mb_convert_encoding(imap_base64($body), 'utf-8', 'auto');

            $cardid = $this->cardObj->add($subject, $head->fromaddress, 'email', $messageid);
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