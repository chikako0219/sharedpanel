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

defined('MOODLE_INTERNAL') || die();

/**
 * Quiz module test data generator class
 *
 * @package mod_sharedpanel
 * @copyright  nagaoka, kita
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_sharedpanel_generator extends testing_module_generator
{
    public function create_instance($record = null, array $options = null) {
        global $CFG;

        require_once($CFG->dirroot . '/mod/sharedpanel/locallib.php');
        $record = (object)(array)$record;

        $defaultsharedpanelsettings = [
            'hashtag1' => 'sharedpanel',
            'emailadr1' => 'emailadr1@example.com',
            'emailpas1' => 'emailpas1',
            'emailkey1' => 'emailkey',
            'fbgroup1' => 'fbgroup1',
            'emailadr2' => 'emailadr2@example.com',
            'emailpas2' => 'emailpas2',
            'emailkey2' => 'emailkey2',
            'config0' => 'config0',
            'config' => 'config'
        ];

        foreach ($defaultsharedpanelsettings as $name => $value) {
            if (!isset($record->{$name})) {
                $record->{$name} = $value;
            }
        }

        return parent::create_instance($record, (array)$options);
    }
}
