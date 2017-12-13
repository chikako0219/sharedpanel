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
 * This file keeps track of upgrades to the sharedpanel module
 *
 * Sometimes, changes between versions involve alterations to database
 * structures and other major things that may break installations. The upgrade
 * function in this file will attempt to perform all the necessary actions to
 * upgrade your older installation to the current version. If there's something
 * it cannot do itself, it will tell you what you need to do.  The commands in
 * here will all be database-neutral, using the functions defined in DLL libraries.
 *
 * @package    mod_sharedpanel
 * @copyright  2016 NAGAOKA Chikako, KITA Toshihiro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute sharedpanel upgrade from the given old version
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_sharedpanel_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.

    if ($oldversion < 2016121200) {
        $table = new xmldb_table('sharedpanel');
        $field = new xmldb_field('encryptionkey', XMLDB_TYPE_TEXT, '10', false, XMLDB_NOTNULL, null, 0,
            'timemodified');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        mod_sharedpanel_upgrade_encryptionkey();

        upgrade_mod_savepoint(true, 2017030701, 'sharedpanel');
    }

    if ($oldversion < 2017112801) {
        $table = new xmldb_table('sharedpanel');

        $field = new xmldb_field('config0');
        if (!$dbman->field_exists($table, $field)) {
            $table->deleteField('config0');
        }
        $field = new xmldb_field('config');
        if (!$dbman->field_exists($table, $field)) {
            $table->deleteField('config');
        }

        $field = new xmldb_field('emailhost', XMLDB_TYPE_CHAR, '255', false, false, null, null, 'emailpas1');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2017112801, 'sharedpanel');
    }

    if ($oldversion < 2017120101) {
        $table = new xmldb_table('sharedpanel');

        $field = new xmldb_field('config0');
        if ($dbman->field_exists($table, $field)) {
            $table->deleteField('config0');
        }
        $field = new xmldb_field('config');
        if ($dbman->field_exists($table, $field)) {
            $table->deleteField('config');
        }
        $field = new xmldb_field('emailport', XMLDB_TYPE_INTEGER, '10', false, false, null, null, 'emailhost');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2017120101, 'sharedpanel');
    }

    if ($oldversion < 2017120601) {
        $table = new xmldb_table('sharedpanel');

        $field = new xmldb_field('line_channel_id', XMLDB_TYPE_CHAR, '255', false, false, null, null, 'emailkey2');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('line_channel_secret', XMLDB_TYPE_CHAR, '255', false, false, null, null, 'line_channel_id');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('line_channel_access_token', XMLDB_TYPE_CHAR, '255', false, false, null, null, 'line_channel_id');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2017120601, 'sharedpanel');
    }

    if ($oldversion < 2017121201) {
        $table = new xmldb_table('sharedpanel_lineids');
        if (!$dbman->table_exists($table)) {
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10');
            $table->add_field('sharedpanelid', XMLDB_TYPE_INTEGER, '10', false, false, null, null, 'id');
            $table->add_field('lineid', XMLDB_TYPE_CHAR, '255', false, false, null, null, 'sharedpanelid');
            $table->add_field('lineuserid', XMLDB_TYPE_CHAR, '255', false, false, null, null, 'lineid');
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', false, false, null, null, 'lineuserid');
            $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', false, false, null, null, 'lineuserid');

            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

            $dbman->create_table($table);
            $index1 = new xmldb_index('sharedpanelid', XMLDB_INDEX_NOTUNIQUE, ['sharedpanelid']);
            $dbman->add_index($table, $index1);
        }
        $field = new xmldb_field('id', XMLDB_TYPE_INTEGER, '10', false, false, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('sharedpanelid', XMLDB_TYPE_INTEGER, '10', false, false, null, null, 'id');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('lineid', XMLDB_TYPE_CHAR, '255', false, false, null, null, 'sharedpanelid');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('lineuserid', XMLDB_TYPE_CHAR, '255', false, false, null, null, 'lineid');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', false, false, null, null, 'lineuserid');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2017121201, 'sharedpanel');
    }

    if ($oldversion < 2017121202) {
        $table = new xmldb_table('sharedpanel_lineids');
        $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', false, false, null, null, 'lineuserid');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    if ($oldversion < 2017121302) {
        $table = new xmldb_table('sharedpanel_lineids');
        $field = new xmldb_field('userid', XMLDB_TYPE_INTEGER, '10', false, true, null, null, 'sharedpanelid');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    return true;
}
