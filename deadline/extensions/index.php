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
 * This file is the front controller of the extensions plugin and all UI requests
 * to the plugin are via this form.
 *
 * @package   deadline_extensions
 * @copyright 2013 University of South Australia {@link http://www.unisa.edu.au}
 * @author    James McLean <james.mclean@unisa.edu.au>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

require_once('lib.php');
require_once('extension_base.php');

$id     = optional_param('id',      '0',        PARAM_INT);
$eid    = optional_param('eid',     '0',        PARAM_INT);
$page   = optional_param('page',    'requests', PARAM_ALPHAEXT);
$action = optional_param('action',  'display',  PARAM_ALPHAEXT);
$cm_id  = optional_param('cmid',    '0',        PARAM_INT);
$sid    = optional_param('student_id', '0',        PARAM_INT);

$this_url = new moodle_url('/deadline/extensions');

$url_params           = array();
$url_params['page']   = $page;
$url_params['id']     = $id;
$url_params['eid']    = $eid;
$url_params['action'] = $action;
$url_params['cmid']   = $cm_id;

// If we have a CM_ID then we should be in module context
if(isset($cm_id) && $cm_id > 0) {

    $cm = get_coursemodule_from_id(0, $cm_id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

    require_login($course, true, $cm);
    $context = context_module::instance($cm->id);

} else if(isset($eid) && $eid > 0) {

    // Get the $cm_id based on the extension ID.
    $cm_id = extensions_plugin::get_activity_id_by_extid($eid);

    $cm = get_coursemodule_from_id(0, $cm_id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

    require_login($course, true, $cm);
    $context = context_module::instance($cm->id);

} else if(isset($id) && $id > SITEID) {

    $params = array('id' => $id);
    $course = $DB->get_record('course', $params, '*', MUST_EXIST);

    require_login($course);

    $context = context_course::instance($course->id);
} else {
    require_login(null, false);
}

$extension = new extension_base();
$extension->set_course($course->id);
$extension->set_page($page);
$extension->set_action($action);
$extension->set_extension_id($eid);
$extension->set_cmid($cm_id);
$extension->set_student_id($sid);

$PAGE->set_url($this_url, $url_params);
$PAGE->set_pagelayout('incourse');
$PAGE->set_context($context);
$PAGE->requires->js(extensions_plugin::EXTENSIONS_URL_PATH . '/assets/js/extensions.js');
$PAGE->set_heading(get_string('extrequests', extensions_plugin::EXTENSIONS_MOD_NAME));

// Do the actual output to the screen here.
print $OUTPUT->header();
print $extension->display();
print $OUTPUT->footer();