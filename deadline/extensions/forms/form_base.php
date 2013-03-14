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
 * This file is the base class for all forms used in the deadline_extensions module
 *
 * @package   deadline_extensions
 * @copyright 2013 University of South Australia {@link http://www.unisa.edu.au}
 * @author    James McLean <james.mclean@unisa.edu.au>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once ($CFG->libdir  . '/formslib.php');
require_once ($CFG->libdir  . '/form/group.php');
require_once ($CFG->dirroot . '/user/lib.php');
require_once ($CFG->dirroot . '/deadline/deadlines/lib.php');

MoodleQuickForm::registerElementType('extension_requests',         "forms/extension_requests.php",  'MoodleQuickForm_extension_requests');
MoodleQuickForm::registerElementType('extension_requests_student', "forms/extension_requests_student.php",  'MoodleQuickForm_extension_requests_student');
MoodleQuickForm::registerElementType('extension_configure',        "forms/extension_configure.php", 'MoodleQuickForm_extension_configure');
MoodleQuickForm::registerElementType('extension_global',           "forms/extension_global.php",    'MoodleQuickForm_extension_global');
MoodleQuickForm::registerElementType('select_picker',              "forms/select_picker.php",       'MoodleQuickForm_select_picker');

class form_base extends moodleform {

    protected $course     = null;
    protected $ext_id     = null;
    protected $readonly   = false;
    protected $cm_id      = false;
    protected $student_id = null;

    protected $activity_detail = null;

    public    $date_options = null;

    /**
     * Constructor method.
     *
     * @param $arg Arguments to be passed to the parent.
     * @return none.
     *
     */
    public function __construct($arg = null) {

        parent::__construct($arg);

        $this->date_options = array(
                'startyear' => date('Y') - 1,
                'stopyear'  => date('Y') + 2,
                'step'      => 5,
                'optional'  => false
        );

    }

    /**
     *
     * Base definition. All child overide methods should call this as the
     * parent so that the ID is set correctly
     *
     * @param none
     * @return none
     *
     */
    public function definition() {
        $mform =& $this->_form;

        // Add a hidden field for the Course ID.
        $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);

    }

    public function post_form_load() {

    }

    public function load_activity_detail($cm_id = null) {

        if(is_null($cm_id)) {
            return false;
        }

        $deadlines = new deadlines_plugin();
        $this->activity_detail['cm'] = $deadlines->activity_detail($this->get_cmid());
        $this->activity_detail['dl'] = $deadlines->get_deadlines_for_cmid($this->get_cmid());

    }

    /**
     *
     * Base definition. This will need to be overridden
     *
     * @param none
     * @return none
     *
     */
    public function definition_after_data() {
        $mform =& $this->_form;

    }

    public function set_student_id($sid = null) {
        if(!is_null($sid)) {
            $this->student_id = $sid;
        }
    }

    public function get_student_id() {
        return $this->student_id;
    }

    public function set_cmid($cm_id = null) {
        if(!is_null($cm_id)) {
            $this->cm_id = $cm_id;
        }
    }

    public function get_cmid() {
        return $this->cm_id;
    }

    public function get_page_name() {
        return $this->page_name;
    }

    protected function get_all_students() {

        global $COURSE, $DB;

        $context = get_context_instance(CONTEXT_COURSE, $COURSE->id);
        $roleid  = $DB->get_field('role', 'id', array('shortname' => 'student'), MUST_EXIST);

        if (!$contextusers = get_role_users($roleid, $context, false, 'u.id, u.firstname, u.lastname, u.email', 'u.lastname, u.firstname')) {
            $contextusers = array();
        } else {
            return $contextusers;
        }

    }

    public function set_course($course = null) {

        if(!is_null($course)) {
            $this->course = $course;
        }

    }

    public function get_course() {
        return $this->course;
    }

    public function get_course_id() {
        return $this->get_course()->id;
    }


    public function set_extension_id($ext_id = null) {
        if(!is_null($ext_id)) {
            $this->ext_id = $ext_id;
        }
    }

    public function get_extension_id() {
        return $this->ext_id;
    }


    public function getGroupElement($index = null, $elementGroup = null) {

        if(is_null($index)) {
            return false;
        }

        foreach (array_keys($elementGroup->_elements) as $key) {
            $elementName = $elementGroup->_elements[$key]->getName();
            if ($index == $elementName) {
                return $elementGroup->_elements[$key];
                break;
            }
        }

        return false;
    }

    public function get_course_groups($course = null) {
        $groups = groups_get_all_groups($course->id, 0, 0, 'g.id, name');

        $all_groups = array();
        $all_groups['-1'] = get_string('group', 'group');
        foreach($groups as $id => $data) {
            $all_groups[$id] = $data->name;
        }

        return $all_groups;
    }

    public function get_extension_approvers($cm_id = null) {
        global $DB, $COURSE;

        $params = array(
                'ext_en_id' => extensions_plugin::get_extension_enable_id_by_cmid($cm_id)
        );

        if($DB->record_exists('deadline_extensions_appv', $params)) {
            // there are approvers listed for this activity. Only select those
            $users = $DB->get_records('deadline_extensions_appv', $params);
        } else {
            // No approvers listed for this activity. Use all staff roles
            // that have the correct capability: 'deadline/extensions:approveextension'

            $context = context_course::instance($COURSE->id);
            $users = get_users_by_capability($context, 'deadline/extensions:approveextension');

        }

        if(isset($users)) {
            $user_list = array();
            $user_list[-1] = "&nbsp;";

            foreach($users as $user) {
                if(isset($user->user_id)) {
                    $detail = $DB->get_record('user', array('id' => $user->user_id));
                    $user_list[$user->user_id] = $detail->firstname . ' ' . $detail->lastname;
                } else {
                    $user_list[$user->id] = $user->firstname . ' ' . $user->lastname;
                }
            }

            return $user_list;
        }
    }

    public function get_extension_approvers_by_course($course = null) {
        $users = array();
        $users['-1'] = get_string('approvers', extensions_plugin::EXTENSIONS_LANG);

        return $users;
    }

    public function set_readonly($readonly = false) {
        $this->readonly = $readonly;
    }

    public function get_readonly() {
        return $this->readonly;
    }

    public function validation($data, $files) {

        global $DB, $CFG, $USER;

        $errors = array();

        if($this->get_cmid() == '-1') {
            $errors['cmid'] = get_string('invalid_activity', extensions_plugin::EXTENSIONS_LANG);
            return $errors;
        }

        // See if this activity even allows extensions
        if(!extensions_plugin::extensions_enabled_cmid($this->get_cmid())) {
            $errors['cmid'] = get_string('extmessnotpermitted', extensions_plugin::EXTENSIONS_LANG);
            return $errors;
        }

        // See if this user already has and extension request that's pending.
        if(!isset($data['page'])) {
            if(get_config(extensions_plugin::EXTENSIONS_MOD_NAME, 'show_duplicate_warn') == 1) {
                if(extensions_plugin::duplicate_requests($this->get_cmid(), $USER->id, null, extensions_plugin::STATUS_PENDING)) {
                    $errors['cmid'] = get_string('ext_already_pending', extensions_plugin::EXTENSIONS_LANG);
                }
            }
        }

        // Check to see if there is already an existing submission for this activity for
        // this user.
        if(get_config('deadline_extensions', 'prevent_req_after_sub') == '1') {
            if(extensions_plugin::activity_has_submission($this->get_cmid(), $USER->id)) {
                $errors['cmid'] = get_string('extalreadysubmitted', 'u_extension_lang');
                return $errors;
            }
        }

        // See if this item is OPEN for submissions
        $deadlines = new deadlines_plugin();
        $deadline = $deadlines->get_deadlines_for_cmid($this->get_cmid());

        if($deadline->date_open > date('U')) {
            $errors['date'] = get_string('extnotopenyet', extensions_plugin::EXTENSIONS_LANG);
        }

        // See if the due date has passed
        if($deadline->date_deadline < date('U')) {
            $errors['date'] = get_string('extduedatepassed', extensions_plugin::EXTENSIONS_LANG);
        }

        // If this is a new request or an edit, check the requested date against the due date
        if(isset($data['page']) && ($data['page'] == 'request_new' || $data['page'] == 'request_edit')) {
            if(isset($data['date']) && $data['date'] < $deadline->date_deadline) {
                $errors['date'] = get_string('extbeforedue', extensions_plugin::EXTENSIONS_LANG);
            }
        }

        return $errors;
    }
}