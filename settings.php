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
 * SharePanel module admin settings and defaults
 *
 * @package    mod
 * @subpackage sharedpanel
 * @copyright  nagaoka, kita
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configcheckbox('sharedpanel/requiremodintro',
        get_string('requiremodintro', 'sharedpanel'), get_string('configrequiremodintro', 'sharedpanel'), 1));

    $settings->add(new admin_setting_heading('sharedpanel/facebook',
        get_string('facebook', 'sharedpanel'), get_string('facebook', 'sharedpanel')));
    $settings->add(new admin_setting_configtext('sharedpanel/FBappID',
        get_string('FBappID', 'sharedpanel'),
        get_string('FBappID_help', 'sharedpanel'), ''));

    $settings->add(new admin_setting_configtext('sharedpanel/FBsecret',
        get_string('FBsecret', 'sharedpanel'),
        get_string('FBsecret_help', 'sharedpanel'), ''));

    $settings->add(new admin_setting_heading('sharedpanel/twitter',
        get_string('twitter', 'sharedpanel'), get_string('twitter', 'sharedpanel')));
    $settings->add(new admin_setting_configtext('sharedpanel/TWconsumerKey',
        get_string('TWconsumerKey', 'sharedpanel'),
        get_string('TWconsumerKey_help', 'sharedpanel'), ''));

    $settings->add(new admin_setting_configtext('sharedpanel/TWconsumerSecret',
        get_string('TWconsumerSecret', 'sharedpanel'),
        get_string('TWconsumerSecret_help', 'sharedpanel'), ''));

    $settings->add(new admin_setting_configtext('sharedpanel/TWaccessToken',
        get_string('TWaccessToken', 'sharedpanel'),
        get_string('TWaccessToken_help', 'sharedpanel'), ''));

    $settings->add(new admin_setting_configtext('sharedpanel/TWaccessTokenSecret',
        get_string('TWaccessTokenSecret', 'sharedpanel'),
        get_string('TWaccessTokenSecret_help', 'sharedpanel'), ''));
}
