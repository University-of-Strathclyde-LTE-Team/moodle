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
 * This file contains the base class for any deadline plugins. All deadline
 * plugins must extend this class.
 *
 * @package   core
 * @copyright 2013 University of South Australia {@link http://www.unisa.edu.au}
 * @author    James McLean <james.mclean@unisa.edu.au>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/



function deadline_get_form_elements($mform, $context = "", $modulename = "") {

    global $CFG;

    $plugins = get_plugin_list('deadline');

    foreach($plugins as $plugin => $dir) {



        $lib_file = $dir . '/lib.php';
        $class    = $plugin . '_plugin';

        // ensure the lib.php file exists
        if(file_exists($lib_file)) {
            // include it.
            require_once($lib_file);

            if(class_exists($class)) {
                $plugin_object = new $class;
                $plugin_object->get_form_elements($mform, $context);

            }

        }
    }


}

function deadline_add_course_navigation(navigation_node $coursenode, $course) {
    global $CFG;

    if(get_config('deadline_extensions', 'enabled') == '1') {
        require_once($CFG->dirroot . '/deadline/extensions/lib.php');
    } else {
        return;
    }

    $coursecontext = context_course::instance($course->id);

    // Determine access this user has:
    $access  = has_capability('deadline/extensions:accessextension', $coursecontext);
    $approve = has_capability('deadline/extensions:approveextension', $coursecontext);

    $request = has_capability('deadline/extensions:requestextension', $coursecontext); // Students are the only ones with this.

    $extnode = $coursenode->add(get_string('pluginname', extensions_plugin::EXTENSIONS_LANG), null, navigation_node::TYPE_CONTAINER, null, 'extensions');

    $params = array(
        'id' => $course->id
    );

    // Add Group & Individual Extensions
    if($access && $approve) {
        $url = new moodle_url('/deadline/extensions/', $params);
        $extnode->add(get_string('ext_indiv_exts', extensions_plugin::EXTENSIONS_LANG), $url, null, navigation_node::TYPE_SETTING, null, null);
    }

    $params['page'] = 'global';

    // Add Global Extensions
    if($access && $approve) {
        $url = new moodle_url('/deadline/extensions/', $params);
        $extnode->add(get_string('ext_global_ext', extensions_plugin::EXTENSIONS_LANG), $url, null, navigation_node::TYPE_SETTING, null, null);
    }

    // Add Activity Configuration
    $params['page'] = 'configure_activities';

    // Add Global Extensions
    if($access && $approve) {
        $url = new moodle_url('/deadline/extensions/', $params);
        $extnode->add(get_string('ext_configure_activities', extensions_plugin::EXTENSIONS_LANG), $url, null, navigation_node::TYPE_SETTING, null, null);
    }

    $params['page'] = 'requests';
    if($request && !$approve) {
        $url = new moodle_url('/deadline/extensions/', $params);
        $extnode->add(get_string('extsubmitreq', extensions_plugin::EXTENSIONS_LANG), $url, null, navigation_node::TYPE_SETTING, null, null);
    }

}
