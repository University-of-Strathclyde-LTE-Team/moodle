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
 * This file contains the events that the deadline_deadlines plugin actions.
 *
 * @package   deadline_deadlines
 * @copyright 2013 University of South Australia {@link http://www.unisa.edu.au}
 * @author    James McLean <james.mclean@unisa.edu.au>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


function xmldb_deadline_deadlines_install() {

    global $DB, $CFG;

    require_once($CFG->dirroot . '/deadline/lib.php');
    require_once($CFG->dirroot . '/deadline/deadlines/lib.php');

    $deadlines = new deadlines_plugin();

    // Load all entries in course_modules.
    $course_modules = $DB->get_records('course_modules');

    // For items that support deadlines, copy the data into the deadlines
    // records.
    foreach($course_modules as $course_module) {

        $module = $DB->get_record('modules', array('id' => $course_module->module));

        if(!$deadlines->activity_supports_deadlines($module->name)) {
            continue;
        }

        if($course_module->instance == 0) {
            continue;
        }

        // Get the activity detail now.
        $module_detail = $DB->get_record($module->name, array('id' => $course_module->instance), '*', MUST_EXIST);

        if (!$deadlines->deadline_exists($course_module->id)) {

            if(!$deadlines->create_deadline_record($course_module->id)) {
                throw new moodle_exception('Creation of deadline record failed!');
            } else {

                $module_detail->coursemodule = $course_module->id;

                if(!$deadlines->save_deadlines($module_detail, $module->name)) {
                    throw new moodle_exception('Saving deadline detail failed!');
                }

            }
        }

    }

    return true;
}