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
 * Library of interface functions and constants for module sharedpanel
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 * All the sharedpanel specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package    mod_sharedpanel
 * @copyright  2016 NAGAOKA Chikako, KITA Toshihiro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Moodle core API
 */

/**
 * Returns the information on whether the module supports a feature
 *
 * @see plugin_supports() in lib/moodlelib.php
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function sharedpanel_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the sharedpanel into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $sharedpanel An object from the form in mod_form.php
 * @param mod_sharedpanel_mod_form $mform
 * @return int The id of the newly inserted sharedpanel record
 */
function sharedpanel_add_instance(stdClass $sharedpanel, mod_sharedpanel_mod_form $mform = null) {
    global $DB;

    $sharedpanel->timecreated = time();

    return $DB->insert_record('sharedpanel', $sharedpanel);
}

/**
 * Updates an instance of the sharedpanel in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $sharedpanel An object from the form in mod_form.php
 * @param mod_sharedpanel_mod_form $mform
 * @return boolean Success/Fail
 */
function sharedpanel_update_instance(stdClass $sharedpanel, mod_sharedpanel_mod_form $mform = null) {
    global $DB;

    $data = $DB->get_record('sharedpanel', ['id' => $sharedpanel->instance]);

    $sharedpanel->id = $sharedpanel->instance;
    $sharedpanel->timemodified = time();

    return $DB->update_record('sharedpanel', $sharedpanel);
}

/**
 * Removes an instance of the sharedpanel from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function sharedpanel_delete_instance($id) {
    global $DB;

    if (!$sharedpanel = $DB->get_record('sharedpanel', array('id' => $id))) {
        return false;
    }

    $cards = $DB->get_records('sharedpanel_cards', ['sharedpanelid' => $sharedpanel->id]);
    foreach ($cards as $card) {
        $DB->delete_records('sharedpanel_card_likes', ['cardid' => $card->id]);
        $DB->delete_records('sharedpanel_card_tags', ['cardid' => $card->id]);
    }

    $DB->delete_records('sharedpanel_cards', ['sharedpanelid' => $sharedpanel->id]);
    $DB->delete_records('sharedpanel_lineids', ['sharedpanelid' => $sharedpanel->id]);
    $DB->delete_records('sharedpanel', ['id' => $sharedpanel->id]);

    return true;
}

/**
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return stdClass|null
 */
function sharedpanel_user_outline($course, $user, $mod, $sharedpanel) {

    $return = new stdClass();
    $return->time = 0;
    $return->info = '';
    return $return;
}

/**
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @param stdClass $course the current course record
 * @param stdClass $user the record of the user we are generating report for
 * @param cm_info $mod course module info
 * @param stdClass $sharedpanel the module instance record
 * @return void, is supposed to echp directly
 */
function sharedpanel_user_complete($course, $user, $mod, $sharedpanel) {
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in sharedpanel activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 */
function sharedpanel_print_recent_activity($course, $viewfullnames, $timestart) {
    return false; // True if anything was printed, otherwise false.
}

/**
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * {@link sharedpanel_print_recent_mod_activity()}.
 *
 * @param array $activities sequentially indexed array of objects with the 'cmid' property
 * @param int $index the index in the $activities to use for the next record
 * @param int $timestart append activity since this time
 * @param int $courseid the id of the course we produce the report for
 * @param int $cmid course module id
 * @param int $userid check for a particular user's activity only, defaults to 0 (all users)
 * @param int $groupid check for a particular group's activity only, defaults to 0 (all groups)
 * @return void adds items into $activities and increases $index
 */
function sharedpanel_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid = 0, $groupid = 0) {
}

/**
 * Prints single activity item prepared by {@see sharedpanel_get_recent_mod_activity()}
 *
 * @return void
 */
function sharedpanel_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function sharedpanel_cron() {
    return true;
}

/**
 * Returns all other caps used in the module
 *
 * @example return array('moodle/site:accessallgroups');
 * @return array
 */
function sharedpanel_get_extra_capabilities() {
    return array();
}

/**
 * Gradebook API                                                              //
 */

/**
 * File API                                                                   //
 */

/**
 * Returns the lists of all browsable file areas within the given module context
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array of [(string)filearea] => (string)description
 */
function sharedpanel_get_file_areas($course, $cm, $context) {
    return array();
}

/**
 * File browsing support for sharedpanel file areas
 *
 * @package mod_sharedpanel
 * @category files
 *
 * @param file_browser $browser
 * @param array $areas
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return file_info instance or null if not found
 */
function sharedpanel_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    return null;
}

/**
 * Serves the files from the sharedpanel file areas
 *
 * @package mod_sharedpanel
 * @category files
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the sharedpanel's context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 */
function sharedpanel_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload, array $options = array()) {
    global $DB, $CFG;

    if ($context->contextlevel != CONTEXT_MODULE) {
        send_file_not_found();
    }

    require_login($course, true, $cm);

    $fs = get_file_storage();
    $itemid = (int)array_shift($args);
    $relativepath = implode('/', $args);
    $fullpath = "/{$context->id}/mod_sharedpanel/$filearea/$itemid/$relativepath";
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        send_file_not_found();
    }

    send_stored_file($file, 0, 0, $forcedownload);
}

/**
 * Navigation API                                                             //
 */

/**
 * Extends the global navigation tree by adding sharedpanel nodes if there is a relevant content
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $navref An object representing the navigation tree node of the sharedpanel module instance
 * @param stdClass $course
 * @param stdClass $module
 * @param cm_info $cm
 */
function sharedpanel_extend_navigation(navigation_node $navref, stdclass $course, stdclass $module, cm_info $cm) {
}

/**
 * Extends the settings navigation with the sharedpanel settings
 *
 * This function is called when the context for the page is a sharedpanel module. This is not called by AJAX
 * so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav {@link settings_navigation}
 * @param navigation_node $sharedpanelnode {@link navigation_node}
 */
function sharedpanel_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $sharedpanelnode = null) {
}
