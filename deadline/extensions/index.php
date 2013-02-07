<?php

require_once('../../config.php');

require_once('lib.php');
require_once('extension_base.php');

// Course ID
$id     = optional_param('id',      '0',        PARAM_INT);
$eid    = optional_param('eid',     '0',        PARAM_INT);
$page   = optional_param('page',    'requests', PARAM_ALPHAEXT);
$action = optional_param('action',  'display',  PARAM_ALPHAEXT);

// var_dump(events_trigger('indiv_ext_created', 'test'));
$extension = new extension_base();
$extension->set_course($id);
$extension->set_page($page);
$extension->set_action($action);
$extension->set_extension_id($eid);

$this_url = parse_url($_SERVER['REQUEST_URI']);
$path = clean_param($this_url['path'], PARAM_LOCALURL);

if(isset($id) && $id != 0) {
    // User is viewing in Course context. Show extensions only for this course
    // that they can decline/approve.
    $course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);
    require_login($course);

    $PAGE->set_url($this_url['path'], array('id' => $id));
    $PAGE->set_pagelayout('incourse');

    $PAGE->set_context(context_system::instance());

} else {
    // User is viewing in global context. Show extensions for all courses
    // they can approve/decline.
    require_login();
    $PAGE->set_url($this_url['path'], null);
    $PAGE->set_pagelayout('base');

    $context = get_context_instance(CONTEXT_SYSTEM);
    $PAGE->set_context($context);
}

$PAGE->requires->js(Extensions::EXTENSIONS_URL_PATH . '/assets/js/extensions.js');

$strplural = get_string("pluginname", Extensions::EXTENSIONS_MOD_NAME);
$PAGE->set_heading(get_string('extrequests', Extensions::EXTENSIONS_MOD_NAME));

// Do the actual output to the screen here.
print $OUTPUT->header();
print $extension->display();
print $OUTPUT->footer();