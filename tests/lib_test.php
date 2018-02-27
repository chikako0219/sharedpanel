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
 * @copyright  2016 NAGAOKA Chikako, KITA Toshihiro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/sharedpanel/lib.php');

/**
 * @copyright  nagaoka, kita
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 */
class mod_sharedpanel_lib_testcase extends advanced_testcase
{

    /**
     * Test deleting a sharedpanel instance.
     */
    public function test_sharedpanel_delete_instance() {
        global $SITE, $DB;
        $this->resetAfterTest(true);
        $this->setAdminUser();

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
                'config' => 'config',
                'encryptionkey' => 'asfdsaf3ewdsaf',
            ]
        );

        $count = $DB->count_records('sharedpanel', ['id' => $sharedpanel->id]);
        $this->assertEquals(1, $count);

        sharedpanel_delete_instance($sharedpanel->id);

        // Check that the sharedpanel was removed.
        $count = $DB->count_records('sharedpanel', ['id' => $sharedpanel->id]);
        $this->assertEquals(0, $count);
    }

    public function test_sharedpanel_add_instance() {
        global $DB;

        $this->resetAfterTest(true);
        $this->setAdminUser();

        $sharedpanel = new stdClass();
        $sharedpanel->course = 1;
        $sharedpanel->name = 'Test instance1';
        $sharedpanel->intro = 'Intro Intro Intro Intro Intro Intro';
        $sharedpanel->introformat = 1;
        $sharedpanel->timecreated = 1486911049;
        $sharedpanel->timemodified = 1486911049;
        $sharedpanel->hashtag1 = 'hashtagtest';

        $sharedpanel->fbgroup1 = 'hashtagtest';

        $sharedpanel->emailisssl = '0';
        $sharedpanel->emailadr1 = 'test1@example.com';
        $sharedpanel->emailpas1 = 'emailpassword';
        $sharedpanel->emailkey1 = 'keyword1';

        $sharedpanel->emailkey2 = 'emailpassword';
        $sharedpanel->emailadr2 = 'test2@example.com';
        $sharedpanel->emailpas2 = 'keyword2';

        $sharedpanel->line_channel_id = '1234567890';
        $sharedpanel->line_channel_secret = 'sadmfamcxmpoqw09-23jnmmf092knfdskfn029jp';
        $sharedpanel->line_channel_access_token = 'jfpkjwq409ijpldfxv0i45j;pldfamjg42509j';

        $sharedpanelid = sharedpanel_add_instance($sharedpanel);

        $sharedpanel = $DB->get_record('sharedpanel', ['id' => $sharedpanelid]);

        $this->assertTrue($DB->record_exists('sharedpanel', ['id' => $sharedpanelid]));
        $this->assertEquals('0', $sharedpanel->emailisssl);
    }

    public function test_sharedpanel_update_instance() {
        global $DB, $SITE;

        $this->resetAfterTest(true);
        $this->setAdminUser();

        $sharedpanelgenerator = $this->getDataGenerator()->get_plugin_generator('mod_sharedpanel');
        $sharedpanel = $sharedpanelgenerator->create_instance(
            [
                'course' => $SITE->id
            ]
        );

        $sharedpanel = $DB->get_record('sharedpanel', ['id' => $sharedpanel->id]);
        $sharedpanel->instance = $sharedpanel->id;

        $update = sharedpanel_update_instance($sharedpanel);

        $this->assertTrue($update);
        $this->assertTimeCurrent($sharedpanel->timemodified);

        $count = $DB->count_records('sharedpanel', ['id' => $sharedpanel->id]);
        $this->assertEquals(1, $count);
    }
}