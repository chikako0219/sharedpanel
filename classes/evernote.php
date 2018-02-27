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

defined('MOODLE_INTERNAL') || die();

class evernote extends card
{
    protected $moduleinstance;

    private $emailaddr;
    private $emailpassword;
    private $emailport;

    private $cardobj;

    public function __construct($modinstance) {
        $this->emailaddr = $modinstance->emailadr1;
        $this->emailpassword = $modinstance->emailpas1;
        $this->emailport = 993;
        $this->cardobj = new card($modinstance);

        parent::__construct($modinstance);
    }

    public function is_enabled() {
        if (empty($this->moduleinstance->emailhost)
            || empty($this->moduleinstance->emailport)
            || empty($this->emailaddr)
            || empty($this->emailpassword)) {
            return false;
        }
        return true;
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

        if ($this->moduleinstance->emailisssl === '1') {
            $mailbox = '{' . $this->moduleinstance->emailhost .
                ':' .
                $this->moduleinstance->emailport .
                '/novalidate-cert/imap/ssl}' .
                "INBOX";
        } else {
            $mailbox = '{' . $this->moduleinstance->emailhost .
                ':' .
                $this->moduleinstance->emailport .
                '/novalidate-cert/imap}' .
                "INBOX";
        }

        $mbox = imap_open($mailbox, $this->emailaddr, $this->emailpassword, OP_READONLY);
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

            $cardid = $this->cardobj->add($subject, $head->fromaddress, 'evernote', $messageid);
            $cardids[] = $cardid;
            foreach (mod_sharedpanel_get_tags($subject) as $tagstr) {
                $tagobj = new tag($this->moduleinstance);
                $tagobj->set($cardid, $tagstr, $USER->id);
            }
        }

        return $cardids;
    }

    public function get_error() {
        return $this->error;
    }
}