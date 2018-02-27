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
require_once($CFG->dirroot . '/mod/sharedpanel/db/upgradelib.php');

/**
 * @copyright  2017 Takayuki Fuwa
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 */
class mod_sharedpanel_upgradelib_testcase extends advanced_testcase
{

    /**
     * Test deleting a sharedpanel instance.
     */
    public function test_mod_sharedpanel_upgrade_encryptionkey() {
        global $DB;
        $this->resetAfterTest(true);
        $this->setAdminUser();

        $dataset = $this->createCsvDataSet(['sharedpanel' => __DIR__ . '/fixtures/sharedpanel_update.csv']);
        $this->loadDataSet($dataset);

        $count = $DB->count_records('sharedpanel', ['encryptionkey' => 0]);
        $this->assertEquals(10, $count);
        $count = $DB->count_records('sharedpanel');
        $this->assertEquals(10, $count);

        $count = $DB->count_records('sharedpanel', ['encryptionkey' => 0]);
        $this->assertEquals(0, $count);
        $count = $DB->count_records('sharedpanel');
        $this->assertEquals(10, $count);

        $sharedpanels = $DB->get_records('sharedpanel');
        $i = 0;
        foreach ($sharedpanels as $sharedpanel) {
            $emailpas1 = $sharedpanel->emailpas1;
            $emailpas2 = $sharedpanel->emailpas2;

            $this->assertEquals('emailpas' . $i, $emailpas1);
            $this->assertEquals('emailpas' . $i, $emailpas2);

            $i++;
        }
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

        $sharedpanel->emailadr1 = 'test1@example.com';
        $sharedpanel->emailpas1 = 'emailpassword';
        $sharedpanel->emailkey1 = 'keyword1';

        $sharedpanel->emailkey2 = 'emailpassword';
        $sharedpanel->emailadr2 = 'test2@example.com';
        $sharedpanel->emailpas2 = 'keyword2';

        $sharedpanel->config0 = 'aaaa';
        $sharedpanel->config = 'aaaaaaa';

        $sharedpanelid = sharedpanel_add_instance($sharedpanel);

        $sharedpanel = $DB->get_record('sharedpanel', ['id' => $sharedpanelid]);

        $count = $DB->count_records('sharedpanel', ['id' => $sharedpanelid]);
        $this->assertEquals(1, $count);

        $this->assertNotNull($sharedpanel->encryptionkey);
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

        $update = sharedpanel_update_instance($sharedpanel);

        $this->assertTrue($update);
        $this->assertTimeCurrent($sharedpanel->timemodified);

        $count = $DB->count_records('sharedpanel', ['id' => $sharedpanel->id]);
        $this->assertEquals(1, $count);
    }
}