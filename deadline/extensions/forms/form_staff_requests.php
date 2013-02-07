<?php

require_once('form_base.php');

class form_staff_requests extends form_base {

    protected $page_name = "Individual Extension Requests";

    private $filters = null;

    public function __construct() {
        parent::__construct();

        //        $this->page_name = get_string('ext_indiv_req', Extensions::LANG_EXTENSIONS);

    }

    public function definition() {
        parent::definition();

        global $CFG, $USER;
        $mform =& $this->_form;

        $mform->addElement('hidden', 'action', 'save');
        $mform->addElement('hidden', 'page', 'requests');

        $mform->addElement('header', 'general', get_string('ext_act_section_header', Extensions::LANG_EXTENSIONS));

        $extoptions = array();
        $extoptions[] = $mform->createElement('select', 'ext_student_list', NULL);
        $extoptions[] = $mform->createElement('select', 'ext_activity', NULL);
        $extoptions[] = $mform->createElement('static', 'ext_date_txt', "", get_string('ext_act_ext_date', Extensions::LANG_EXTENSIONS));
        $extoptions[] = $mform->createElement('date_time_selector', 'ext_date');
        $extoptions[] = $mform->createElement('submit', 'create_stu_ext', get_string('ext_act_grant_ext', Extensions::LANG_EXTENSIONS));
        $mform->addGroup($extoptions, 'create_ext', '&nbsp;', array('&nbsp;'), false);

        // Make sure a student and assifnment has been selected.
        $mform->disabledIf('create_stu_ext', 'ext_activity', 'eq', 0);
        $mform->disabledIf('create_stu_ext', 'ext_student_list', 'eq', 0);

        $mform->addElement('header','filters_general', 'Filters');

        // Build the group for the Filter.
        $buttonarray = array();
        $buttonarray[] = $mform->createElement('static', 'filterby',   "", get_string('extfilterby', Extensions::LANG_EXTENSIONS));
        $buttonarray[] = $mform->createElement('select', 'activity', NULL);
        $buttonarray[] = $mform->createElement('select', 'status',     "");
        $buttonarray[] = $mform->createElement('select', 'class',      "");
        $buttonarray[] = $mform->createElement('select', 'users',      "");
        $buttonarray[] = $mform->createElement('submit', 'apply_filters', get_string('extapplyfilters', Extensions::LANG_EXTENSIONS));
        $buttonarray[] = $mform->createElement('submit', 'clear_filters', get_string("extclearfilter", Extensions::LANG_EXTENSIONS));
        $mform->addGroup($buttonarray, 'buttonar', '&nbsp;', array('&nbsp;'), false);

        // end filters

        $mform->addElement('header','general', $this->page_name);
        // Insert the table and data here.

        // Get extension data
        $extension_table = $mform->addElement('extension_requests','extension_requests', 'Requests', Extensions::build_extensions_table($this->filters));

        // set the element template here.

        // Only show the button to submit if there is requests in the system.
        if($extension_table->get_table_data() !== NULL) {
            // Add the approval buttons
            $submit_arr = array();

            $selectAllAttribs   = array('onClick' => 'checkUncheckAll(this);');
            $quickApproveArribs = array('onClick' => 'return popup();');

            $submit_arr[] = $mform->createElement('button', 'select_all', get_string('ext_select_all', Extensions::LANG_EXTENSIONS), $selectAllAttribs);
            $submit_arr[] = $mform->createElement('submit', 'approve_selected', get_string('ext_appr_selected', Extensions::LANG_EXTENSIONS), $quickApproveArribs);
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

        if($activitys = $this->get_activity_names($this->get_course())) {
            foreach($activitys as $activity) {
                $ele_activity->addOption($activity->name, $activity->id);
                $ext_activity->addOption($activity->name, $activity->id);
            }
        }

        // status
        if($status = Extensions::get_all_extension_status()) {
            $ele_status = $this->getGroupElement('status', $button_group);
            $ele_status->load($status);
        }

        // class
        if($classes = $this->get_course_groups($this->get_course())) {
            $ele_class = $this->getGroupElement('class', $button_group);
            $ele_class->load($classes);
        }

        // users
        if($users = $this->get_extension_approvers_by_course($this->get_course())) {
            $ele_users = $this->getGroupElement('users', $button_group);
            $ele_users->load($users);
        }

    }

    public function save_hook($form_data) {
        global $DB;

        return Extensions::save_quick_approve($form_data);
    }
}