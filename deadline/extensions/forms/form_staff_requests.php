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
 * This file contains the form used to show staff all requests in the system
 *
 * @package   deadline_extensions
 * @copyright 2013 University of South Australia {@link http://www.unisa.edu.au}
 * @author    James McLean <james.mclean@unisa.edu.au>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('form_base.php');

class form_staff_requests extends form_base {

    protected $page_name = "Individual Extension Requests";

    private $filters = null;

    public function __construct() {
        parent::__construct();

        global $COURSE;

        $this->page_name = get_string('ext_indiv_req', extensions_plugin::EXTENSIONS_LANG);
        add_to_log($COURSE->id, "extensions", "viewing", "index.php", "viewing " . $this->page_name, $this->get_cmid());

    }

    public function definition() {
        parent::definition();

        global $CFG, $USER, $SESSION, $PAGE;
        $mform =& $this->_form;

        $filters = null;

        $mform->addElement('hidden', 'action', 'save');
        $mform->addElement('hidden', 'page', 'requests');

        $mform->addElement('header', 'general', get_string('ext_act_section_header', extensions_plugin::EXTENSIONS_LANG));

        $extoptions = array();
        $extoptions[] = $mform->createElement('select', 'ext_student_list', NULL);
        $extoptions[] = $mform->createElement('select', 'ext_activity', NULL);
        $extoptions[] = $mform->createElement('static', 'ext_date_txt', "", get_string('ext_act_ext_date', extensions_plugin::EXTENSIONS_LANG));
        $extoptions[] = $mform->createElement('date_time_selector', 'ext_date');
        $extoptions[] = $mform->createElement('submit', 'create_stu_ext', get_string('ext_act_grant_ext', extensions_plugin::EXTENSIONS_LANG));
        $mform->addGroup($extoptions, 'create_ext', '&nbsp;', array('&nbsp;'), false);

        // Make sure a student and assifnment has been selected.
        $mform->disabledIf('create_stu_ext', 'ext_activity', 'eq', 0);
        $mform->disabledIf('create_stu_ext', 'ext_student_list', 'eq', 0);

        $mform->addElement('header','filters_general', 'Filters');

        // Build the group for the Filter.
        $buttonarray = array();
        $buttonarray[] = $mform->createElement('static', 'filterby',      "", get_string('extfilterby', extensions_plugin::EXTENSIONS_LANG));
        $buttonarray[] = $mform->createElement('select', 'activity',      NULL);
        $buttonarray[] = $mform->createElement('select', 'status',        "");
        $buttonarray[] = $mform->createElement('select', 'group',         "");
        $buttonarray[] = $mform->createElement('select', 'users',         "");
        $buttonarray[] = $mform->createElement('submit', 'apply_filters', get_string('extapplyfilters', extensions_plugin::EXTENSIONS_LANG));
        $buttonarray[] = $mform->createElement('submit', 'clear_filters', get_string("extclearfilter", extensions_plugin::EXTENSIONS_LANG));
        $mform->addGroup($buttonarray, 'buttonar', '&nbsp;', array('&nbsp;'), false);

        // end filters

        $mform->addElement('header','general', $this->page_name);
        // Insert the table and data here.

        // Get extension data
        $extension_table = $mform->addElement('extension_requests','extension_requests', 'Requests', extensions_plugin::build_extensions_table($filters));

        // set the element template here.

        // Only show the button to submit if there is requests in the system.
        if($extension_table->get_table_data() !== NULL) {
            // Add the approval buttons
            $submit_arr = array();

//             $PAGE->requires->js_init_call('M.deadline_extensions.check_uncheck_all');

            $selectAllAttribs   = array('onClick' => 'checkUncheckAll(this);');
            $quickApproveArribs = array('onClick' => 'return popup(\'Are you sure you would like to approve these extensions?\');');

            $submit_arr[] = $mform->createElement('button', 'select_all', get_string('ext_select_all', extensions_plugin::EXTENSIONS_LANG), $selectAllAttribs);
            $submit_arr[] = $mform->createElement('submit', 'approve_selected', get_string('ext_appr_selected', extensions_plugin::EXTENSIONS_LANG), $quickApproveArribs);
            $mform->disabledIf('approve_selected', 'extension_requests', 'noneselected');
            $mform->addGroup($submit_arr, 'submit_arr', '&nbsp;', array(' '), false);
        }


    }

    public function definition_after_data() {

        global $CFG, $COURSE, $USER;
        $mform =& $this->_form;

        //---------------
        // set the assignemnt filters
        // Get the group so we can load the elements individually
        $button_group = $mform->getElement('buttonar');
        $create_ext   = $mform->getElement('create_ext');

        // Student here.
        $students = $this->getGroupElement('ext_student_list', $create_ext);
        $students->addOption('Student', '0');

        // get all students in this course
        // add to the dropdown.
        if($all_students = $this->get_all_students()) {
            foreach($all_students as $student) {
                $students->addOption($student->firstname . ' ' . $student->lastname, $student->id);
            }
        }

        // assignment
        $ele_activity = $this->getGroupElement('activity', $button_group);
        $ele_activity->addOption('Activity', '0');

        $ext_activity = $this->getGroupElement('ext_activity', $create_ext);
        $ext_activity->addOption('Activity', '0');

        $ext = new extensions_plugin;

        if($activities = $ext->get_activity_names($this->get_course())) {
            foreach($activities as $activity) {
                $ele_activity->addOption($activity->name, $activity->id);
                $ext_activity->addOption($activity->name, $activity->id);
            }
        }

        // status
        if($status = extensions_plugin::get_all_extension_status()) {
            $ele_status = $this->getGroupElement('status', $button_group);
            $ele_status->load($status);
        }

        // class
        if($groups = $this->get_course_groups($this->get_course())) {
            $ele_group = $this->getGroupElement('group', $button_group);
            $ele_group->load($groups);
        }

        // users
        if($users = $this->get_extension_approvers_by_course($this->get_course())) {
            $ele_users = $this->getGroupElement('users', $button_group);
            $ele_users->load($users);
        }

    }

    public function validation($data, $files) {
        global $DB;

        $errors = array();

        // ensure everything about each of these is correct.

        // Check if someone is adding an extension on a students behalf.
        if(isset($data['create_stu_ext'])) {

            // Any errors set here, must use 'create_ext' so they show correctly
            // if they don't they won't show at all.

            // This is intentionally not checking that an item has an extension
            // or not, as it may be required, in an emergency situation, that an
            // extension must be applied by an Academic. This will allow it.

            // This allows an extensions to be applied at any time, either before or after the due date.
            // So there's really no point in checking it.

            // Sanity checks to make sure it's not prior to the actual due date
            // of the item itself.

            $params = array(
                    'cm_id' => $data['ext_activity']
            );

            // Make sure this activity has a deadline entry.
            if(!$DB->record_exists('deadline_deadlines', $params)) {
                $errors['create_ext'] = get_string('no_deadline_setup', extensions_plugin::EXTENSIONS_LANG);
                return $errors;
            }

            // Make sure the requested extension date is after the actual due date.
            $deadlines = new deadlines_plugin();
            $deadline = $deadlines->get_deadlines_for_cmid($data['ext_activity'], $data['ext_student_list']);

            // check the extension is after any existing deadlines (includes existing extensions)
            if($deadline->date_deadline > $data['ext_date']) {
                $errors['create_ext'] = get_string('extbeforedue', extensions_plugin::EXTENSIONS_LANG) . ' of ' . userdate($deadline->date_deadline);
                return $errors;
            }



        }

        if(isset($data['extension_requests']) && is_array($data['extension_requests'])) {

            foreach($data['extension_requests'] as $ext_id => $val) {
                // Make sure the activity is expecting extensions
                $cm_id = extensions_plugin::get_activity_id_by_extid($ext_id);

                if(!extensions_plugin::extensions_enabled_cmid($cm_id)) {
                    $errors['extension_requests'] = get_string('extnotpermitted_staff', extensions_plugin::EXTENSIONS_LANG);
                }

                // Make sure the dates are valid. Check the request date is actually
                // after the default deadline date.

            }

        }

        return $errors;
    }

    public function save_hook($form_data) {
        global $DB, $USER, $COURSE;

        // Quick Approve.
        if(isset($form_data->approve_selected)) {
            return extensions_plugin::save_quick_approve($form_data);
        }

        if (isset($form_data->create_stu_ext)) {
            // Create an extension for the listed student.

            // this needs to check these details...
            $ext                = new stdClass();
            $ext->ext_type      = extensions_plugin::EXT_INDIVIDUAL;
            $ext->cm_id         = $form_data->ext_activity;
            $ext->deadline_id   = deadlines_plugin::get_deadline_id_by_cmid($form_data->ext_activity);
            $ext->student_id    = $form_data->ext_student_list;
            $ext->staff_id      = $USER->id;
            $ext->response_text = get_string('ext_act_add_reason', extensions_plugin::EXTENSIONS_LANG);
            $ext->date          = $form_data->ext_date;
            $ext->status        = extensions_plugin::STATUS_APPROVED;
            $ext->created       = date("U");

            if($ext_id = $DB->insert_record('deadline_extensions', $ext, true)) {

                add_to_log($COURSE->id, "extensions", "success", "index.php", "extension {$ext_id} creation successful!", $this->get_cmid());

                $form_data->eid             = $ext_id;
                $form_data->ext_status_code = extensions_plugin::STATUS_APPROVED;
                $form_data->response_text   = get_string('ext_act_add_reason', extensions_plugin::EXTENSIONS_LANG);

                // Add to the extensions history table.
                extensions_plugin::add_history($form_data);

                // Send a message to the user to notify of the update.
                extensions_plugin::notify_user($form_data);

                return true;
            } else {
                add_to_log($COURSE->id, "extensions", "error", "index.php", "extension creation failed!", $this->get_cmid());
                return false;
            }

        }

        if (isset($form_data->apply_filters)) {

            $filter_data = array(
                    'activity'     => $form_data->activity,
                    'status'       => $form_data->status,
                    'users'        => $form_data->users,
                    'group'        => $form_data->group
            );

            $SESSION->ext_filters = $filter_data;
        }

        if (isset($form_data->clear_filters)) {
            // Clear filters button was selected. Clear them ;)
            $SESSION->ext_filters = null;
        }

        return true;
    }
}