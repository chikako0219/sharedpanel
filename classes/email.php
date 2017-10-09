<?php

namespace mod_sharedpanel;

defined('MOODLE_INTERNAL') || die();

class email extends card
{
    private $moduleinstance;

    private $email_addr;
    private $email_password;

    private $email_host;
    private $email_port;

    function __construct($modinstance) {
        $this->email_addr = $modinstance->emailadr1;
        $this->email_password = aes::get_aes_decrypt_string($modinstance->emailpas1, $modinstance->encryptionkey);
        $this->email_port = 993;

        if (preg_match('/([^@]+)@gmail[.]com/', $this->email_addr, $ma)) {
            $this->email_host = 'imap.googlemail.com';
        } elseif (preg_match('/([^@]+)@yahoo[.]co[.]jp/', $this->email_addr, $ma)) {
            $this->email_host = 'imap.mail.yahoo.co.jp';
        } elseif (preg_match('/([^@]+)@yahoo[.]com/', $this->email_addr, $ma)) {
            $this->email_host = 'imap.mail.yahoo.com';
        } else {
            if (preg_match('/([^@]+)@([^@]+)/', $this->email_addr, $ma)) {
                $this->email_host = 'imap.' . $ma[2];
            }
        }

        parent::__construct($modinstance);
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

    public function add() {
        $message = "";

        if (($mbox = imap_open(SERVER . "INBOX", $this->email_addr, $this->email_password)) == false) {
            // If failed to connect with IMAP, return false.
            return false;
        }

        // Get list of mailboxes
        $mboxes = imap_mailboxmsginfo($mbox);
        $message .= "..." . $mboxes->Nmsgs . "emails found...<br>";

        if ($mboxes->NMsgs > 0) {
            $mail = null;

            for ($mailno = 1; $mailno < $mboxes->Nmsgs; $mailno++) {
                // Get Header Information
                $head = imap_headerinfo($mbox, $mailno);
                // If mail from evernote, then skip
                if ($head->from[0]->host === "evernote.com") {
                    continue;
                }

                // Get address
                $mail[$mailno]['address'] = $head->from[0]->mailbox . '@' . $head->from[0]->host;
                // get date
                $mail[$mailno]['date'] = $head->date;
                // If this email is already received, then skip.
                if (self::is_exists(strtotime($head->date))) {
                    $mail[$mailno]['address'] = "xxx";
                    $message .= "Not imported : same email as at " . $head->date . "<br>";
                    continue;
                }

                if (!empty($head->subject)) {
                    $mhead = imap_mime_header_decode($head->subject);
                    foreach ($mhead as $key => $value) {
                        if ($value->charset != 'default') {
                            $mail[$mailno]['subject'] = mb_convert_encoding($value->text, 'utf-8', $value->charset);
                        } else {
                            $mail[$mailno]['subject'] = $value->text;
                        }
                    }
                } else {
                    //@TODO
                    $mail[$mailno]['subject'] = "No title";
                }

                $mailinfo = imap_fetchstructure($mbox, $mailno);

            }
        }
    }
}