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

/**
 * Upgrade helper functions
 *
 * @package   mod_sharedpanel
 * @copyright 2016 Marina Glancy
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once '../classes/aes.php';

/**
 * Fill new field courseid in tables feedback_completed or feedback_completedtmp
 *
 * @param bool $tmp use for temporary table
 */
function mod_sharedpanel_upgrade_encryptionkey($tmp = false) {
    global $DB;

    $sharedpanels = $DB->get_records('sharedpanel', ['encryptionkey' => 0]);
    foreach ($sharedpanels as $sharedpanel) {
        $dataobject = new stdClass();
        $dataobject->id = $sharedpanel->id;
        $dataobject->encryptionkey = \mod_sharedpanel\aes::generate_key();
        $dataobject->emailpas1 = \mod_sharedpanel\aes::get_aes_encrypt_string($sharedpanel->emailpas1, $dataobject->encryptionkey);
        $dataobject->emailpas2 = \mod_sharedpanel\aes::get_aes_encrypt_string($sharedpanel->emailpas2, $dataobject->encryptionkey);
        $DB->update_record('sharedpanel', $dataobject);
    }
}