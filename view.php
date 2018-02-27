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
 * @package    mod_sharedpanel
 * @copyright  2016 NAGAOKA Chikako, KITA Toshihiro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');

global $DB, $PAGE, $OUTPUT;

$id = optional_param('id', 0, PARAM_INT);
$n = optional_param('n', 0, PARAM_INT);
$sortby = optional_param('sortby', 0, PARAM_INT);

if ($id) {
    $cm = get_coursemodule_from_id('sharedpanel', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
    $sharedpanel = $DB->get_record('sharedpanel', ['id' => $cm->instance], '*', MUST_EXIST);
} else if ($n) {
    $sharedpanel = $DB->get_record('sharedpanel', ['id' => $n], '*', MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $sharedpanel->course], '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('sharedpanel', $sharedpanel->id, $course->id, false, MUST_EXIST);
} else {
    print_error('You must specify a course_module ID or an instance ID');
}

$context = context_module::instance($cm->id);
require_login();

$PAGE->requires->css(new moodle_url("style.css"));

// Print the page header.
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');

$PAGE->set_cm($cm);
$PAGE->set_context($context);
$PAGE->set_url('/mod/sharedpanel/view.php', array('id' => $cm->id));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_title(format_string($sharedpanel->name));
$PAGE->set_pagelayout('incourse');

$cardobj = new \mod_sharedpanel\card($sharedpanel);

// Output starts here.
echo $OUTPUT->header();

echo html_writer::start_div();

echo html_writer::empty_tag('hr');
echo get_string('sortedas', 'sharedpanel');
echo \html_writer::start_div('btn-toolbar');

echo html_writer::start_div('btn-group');
echo html_writer::link(new moodle_url('view.php', ['id' => $id]),
    get_string('sort', 'sharedpanel'),
    ['class' => 'btn btn-primary']);
echo html_writer::end_div();

echo html_writer::start_div('btn-group');
echo html_writer::link(new moodle_url('view.php', ['id' => $id, 'sortby' => 1]),
    get_string('sortbylike1', 'sharedpanel'),
    ['class' => 'btn btn-primary']);
echo html_writer::end_div();

echo html_writer::end_div();
echo html_writer::end_div();

echo html_writer::empty_tag('hr');

echo \html_writer::start_div('btn-toolbar');
echo html_writer::start_div('btn-group');
echo html_writer::link(new moodle_url('camera/com.php', ['id' => $id, 'n' => $sharedpanel->id]),
    get_string('postmessage', 'sharedpanel'), ['class' => 'btn btn-primary']);
echo html_writer::end_div();
echo html_writer::start_div('btn-group');
echo html_writer::link(new moodle_url('line.php', ['id' => $id, 'n' => $sharedpanel->id]),
    get_string('postmessage_from_line', 'sharedpanel'), ['class' => 'btn btn-primary']);
echo html_writer::end_div();
echo html_writer::start_div('btn-group');
echo html_writer::empty_tag('input',
    [
        'type' => 'button',
        'value' => get_string('print', 'sharedpanel'),
        'onclick' => 'window.print()',
        'style' => 'margin:1ex;',
        'class' => 'btn btn-primary'
    ]);
echo html_writer::end_div();
echo html_writer::end_div();

echo html_writer::empty_tag('hr');

if (has_capability('moodle/course:manageactivities', $context)) {
    echo \html_writer::start_div('btn-toolbar');

    echo \html_writer::start_div('btn-group');
    echo html_writer::link(new moodle_url('importcard.php', ['id' => $id]),
        get_string('import', 'sharedpanel'), ['class' => 'btn btn-primary']);
    echo html_writer::end_div();

    echo \html_writer::start_div('btn-group');
    echo html_writer::link(new moodle_url('post.php', ['id' => $id, 'sesskey' => sesskey()]),
        get_string('post', 'sharedpanel'), ['class' => 'btn btn-primary']);
    echo html_writer::end_div();

    echo html_writer::end_div();
}

echo html_writer::empty_tag('hr');

$ratingmap = [];
if ($sortby) {
    $cards = $cardobj->gets('like');
} else {
    $cards = $cardobj->gets('important');
}

echo html_writer::start_div('', ['id' => '', 'class' => 'container']);
echo html_writer::start_div('', ['id' => 'diagramContainer', 'class' => 'row']);

foreach ($cards as $card) {
    echo \mod_sharedpanel\html_writer::card($sharedpanel, $context, $card);
}
echo html_writer::end_div();
echo html_writer::end_div();

echo '(total: ' . count($cards) . 'cards)';

echo $OUTPUT->footer();