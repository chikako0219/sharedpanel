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
 * Unit tests for (some of) mod/quiz/locallib.php.
 *
 * @package    mod_sharedpanel
 * @category   test
 * @copyright  2008 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 */


defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/sharedpanel/lib.php');

/**
 * @copyright  2008 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 */
class mod_sharedpanel_lib_testcase extends advanced_testcase
{

    /**
     * Test deleting a quiz instance.
     */
    public function test_sharedpanel_delete_instance()
    {
        global $SITE, $DB;
        $this->resetAfterTest(true);
        $this->setAdminUser();

        // Setup a sharedpanel with 1 standard and 1 random question.
        $sharedpanelgenerator = $this->getDataGenerator()->get_plugin_generator('mod_sharedpanel');
        $sharedpanel = $sharedpanelgenerator->create_instance(
            [
                'course' => $SITE->id,
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
            ]
        );

        $count = $DB->count_records('sharedpanel', ['id' => $sharedpanel->id]);
        $this->assertEquals(1, $count);

        sharedpanel_delete_instance($sharedpanel->id);

        // Check that the quiz was removed.
        $count = $DB->count_records('sharedpanel', ['id' => $sharedpanel->id]);
        $this->assertEquals(0, $count);
    }

    public function test_sharedpanel_update_instance()
    {
        global $SITE, $DB;
        $this->resetAfterTest(true);
        $this->setAdminUser();

        // Setup a quiz with 1 standard and 1 random question.
        $sharedpanelgenerator = $this->getDataGenerator()->get_plugin_generator('mod_sharedpanel');
        $sharedpanel = $sharedpanelgenerator->create_instance(
            [
                'course' => $SITE->id,
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
            ]
        );

        $update = sharedpanel_update_instance($sharedpanel);

    }
}
