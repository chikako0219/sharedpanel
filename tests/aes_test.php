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
require_once($CFG->dirroot . '/mod/sharedpanel/classes/aes.php');

/**
 * @copyright  2017 Takayuki Fuwa
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 */
class aes_test extends advanced_testcase
{
    public function test_aes_encrypt()
    {
        $this->resetAfterTest(true);
        $this->setAdminUser();

        $datasets = [
            'abcdefg1111aaaaa!!!???',
            'amdsfpom9qw2mfsda!!!?##???',
            'mafodpsmf240-qsaomlmsflsasfl-20q4k-w0eakf,apsd,f;;:,',
            'kdsflmo@m0-324asdlmlma-0-2twe',
            'あいうえおああああああ！？',
            '漢字テストあいうえおああああああああああああ１２３４５６７８',
        ];
        $encryption_key = \mod_sharedpanel\aes::generate_key();
        foreach ($datasets as $dataset) {
            $encrypt_string = \mod_sharedpanel\aes::get_aes_encrypt_string($dataset, $encryption_key);
            $this->assertNotEquals($encrypt_string, $dataset);

            $decrypted = \mod_sharedpanel\aes::get_aes_decrypt_string($encrypt_string, $encryption_key);
            $this->assertEquals($decrypted, $dataset);
        }
    }
}