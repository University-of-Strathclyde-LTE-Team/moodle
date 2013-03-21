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
 * This file contains the main plugin methods as used by the deadline_extensions
 * plugin.
 *
 * @package   deadline_extensions
 * @copyright 2013 University of South Australia {@link http://www.unisa.edu.au}
 * @author    James McLean <james.mclean@unisa.edu.au>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot . '/deadline/lib.php');

class extensions_plugin extends deadline_plugin {

    private  $plugin_weight = 1;

    // Define the constants for Individual
    // and Class level extensions.
    const EXT_INDIVIDUAL = 0;
    const EXT_GROUP      = 1;
    const EXT_GLOBAL     = 2;

    const EXT_NO_SEL     = -1;
    const EXT_DISABLED   =  0;
    const EXT_ENABLED    =  1;

    // Define all the statuses here.
    const STATUS_NONE      = 0;
    const STATUS_PENDING   = 1;
    const STATUS_APPROVED  = 2;
    const STATUS_DENIED    = 3;
    const STATUS_WITHDRAWN = 4;
    const STATUS_REVOKED   = 5;
    const STATUS_MOREINFO  = 6;
    const STATUS_DELETED   = 7;

    const EXTENSIONS_LANG     = 'deadline_extensions';
    const EXTENSIONS_MOD_NAME = 'deadline_extensions';
    const EXTENSIONS_URL_PATH = '/deadline/extensions';

    const DATE_FORMAT      = 'l, j F Y, H:i A';

    const EXTENSION_TYPE_NONE  = -1;
    const EXTENSION_TYPE_DATE  =  1;
    const EXTENSION_TYPE_TIME  =  2;

    /**
     * Hook for this module to provide specific form fields for interaction in
     * an activity edit page.
     *
     * @see deadline_plugin::get_form_elements()
     * @param object $mform MoodleForm instance to modify
     * @param object $context Context for this form
     * @param string $modulename Module Name that is calling this form fragment
     */
    public function get_form_elements($mform, $context, $modulename = "") {

        // If deadline_extensions isn't explicitly enabled site-wide, don't
        // show the activity config items.
        if(!$this->is_enabled()) {
            return false;
        }

        $mform->addElement('header', 'general', get_string('settings', self::EXTENSIONS_LANG));

        // Add the extension form item.
        $this->extensions_form_item($mform);

        if(get_config('deadline_extensions', 'req_cut_off') != '-1') {
            $mform->addElement('select', 'extensions_cutoff', get_string('extensions_cutoff', extensions_plugin::EXTENSIONS_LANG), extensions_plugin::get_cutoff_options());
        }

//         $usersPicker = $mform->addElement('select_picker', 'users', 'Users');
    }

    public function extensions_form_item($mform) {
        $mform->addElement('selectyesno','extensions_allowed', get_string('allow_ext_requests', self::EXTENSIONS_LANG));

        if(get_config('deadline_extensions','force_extension_enabled') == '1') {
            $mform->setDefault('extensions_allowed', 1);
            $mform->freeze('extensions_allowed');
        } else {
            $mform->setDefault('extensions_allowed', extensions_plugin::extensions_enabled_cmid($cm_id));
        }

    }

    /**
     * hook to save extensions specific settings on a module settings page
     * @param object $data - data from an mform submission.
     */
    public function save_form_elements($data) {

        global $DB, $USER, $COURSE;

        if(isset($data->extensions_allowed)) {

            $params = array(
                    'cm_id' => $data->coursemodule
            );

            $ext_enabled = new stdClass();
            $ext_enabled->cm_id    = $data->coursemodule;
            $ext_enabled->status   = $data->extensions_allowed;
            $ext_enabled->staff_id = $USER->id;

            if(!$DB->record_exists('deadline_extensions_enabled', $params)) {

                // add the record.
                $ext_enabled->date_enabled = date('U');

                $DB->insert_record('deadline_extensions_enabled', $ext_enabled);

                add_to_log($COURSE->id, "extensions", "success", "index.php", "saving (inserting) form elements.", $this->get_cmid());
            } else {

                $ext_enabled->id = $DB->get_record('deadline_extensions_enabled', array('cm_id' => $data->coursemodule), 'id')->id;

                $DB->update_record('deadline_extensions_enabled', $ext_enabled);

                add_to_log($COURSE->id, "extensions", "success", "index.php", "saving (updating) form elements.", $this->get_cmid());
            }

            return true;
        }

    }

    // ------

    public static function activity_has_submission($cm_id = null, $user_id = null) {
        global $DB;

        $activity = extensions_plugin::get_activity_detail_by_cmid($cm_id);

        switch($activity->modname) {
            case 'assign':

                $params = array(
                    'assignment' => $activity->instance,
                    'userid'     => $user_id,
                    'status'     => 'submitted'
                );

                if($DB->record_exists('assign_submission', $params)) {
                    return true;
                }

                break;
            case 'quiz':

                $params = array(
                    'quiz'   => $activity->instance,
                    'userid' => $user_id,
                );

                if($DB->record_exists('quiz_attempts', $params)) {
                    return true;
                }

                break;
        }

        return false;
    }

    // ------

    /**
     * Method for getting the string version of a status.
     *
     * @param int $status
     */
    public static function get_status_string($status = null) {

        if(is_null($status)) {
            throw exception('Status cannot be null');
        }

        switch($status) {
            case self::STATUS_NONE:
                return get_string('status_none', self::EXTENSIONS_LANG);
                break;
            case self::STATUS_PENDING:
                return get_string('status_pending', self::EXTENSIONS_LANG);
                break;
            case self::STATUS_APPROVED:
                return get_string('status_approved', self::EXTENSIONS_LANG);
                break;
            case self::STATUS_DENIED:
                return get_string('status_denied', self::EXTENSIONS_LANG);
                break;
            case self::STATUS_WITHDRAWN:
                return get_string('status_withdrawn', self::EXTENSIONS_LANG);
                break;
            case self::STATUS_REVOKED:
                return get_string('status_revoked', self::EXTENSIONS_LANG);
                break;
            case self::STATUS_MOREINFO:
                return get_string('status_moreinfo', self::EXTENSIONS_LANG);
                break;
            case self::STATUS_DELETED:
                return get_string('status_deleted', self::EXTENSIONS_LANG);
                break;
        }

    }

    /**
     * Method to convert an extension type into the relevant string.
     * @param unknown_type $type
     */
    public static function get_type_string($type) {
        switch($type) {
            case self::EXT_INDIVIDUAL:
                return get_string('ext_individual', self::EXTENSIONS_LANG);
                break;
            case self::EXT_GROUP:
                return get_string('ext_group', self::EXTENSIONS_LANG);
                break;
            case self::EXT_GLOBAL:
                return get_string('ext_global', self::EXTENSIONS_LANG);
                break;
        }
    }

    public static function get_timelimit_options() {

        $options = array(
                '-1'  => '&nbsp;',
                '60'  => 'One minute',
                '120' => 'Two minutes'
        );

        return $options;
    }

    /**
     * Function to retrieve all available status as an array of strings.
     *
     * @return array $status
     */
    public static function get_all_extension_status() {
        $status = array();
        $status[self::STATUS_NONE]      = get_string('status_none',      self::EXTENSIONS_LANG);
        $status[self::STATUS_PENDING]   = get_string('status_pending',   self::EXTENSIONS_LANG);
        $status[self::STATUS_APPROVED]  = get_string('status_approved',  self::EXTENSIONS_LANG);
        $status[self::STATUS_DENIED]    = get_string('status_denied',    self::EXTENSIONS_LANG);
        $status[self::STATUS_WITHDRAWN] = get_string('status_withdrawn', self::EXTENSIONS_LANG);
        $status[self::STATUS_REVOKED]   = get_string('status_revoked',   self::EXTENSIONS_LANG);
        $status[self::STATUS_MOREINFO]  = get_string('status_moreinfo',  self::EXTENSIONS_LANG);

        return $status;
    }

    /**
     * Method to get a list of extension module enable/disable status
     *
     * @return array $options
     */
    public static function get_extension_enable_items() {
        return array(
                self::EXT_NO_SEL   => '&nbsp;',
                self::EXT_DISABLED => get_string('no'),
                self::EXT_ENABLED  => get_string('yes')
        );
    }

    /**
     * Method to get a consistant date length for start year and end year for
     * date selectors.
     *
     * @return array $options
     */
    public static function get_date_options() {
        return array(
                'startyear' => date('Y') - 1,
                'stopyear'  => date('Y') + 2,
                'step'      => 5,
                'optional'  => false
        );
    }

    /**
     * Method to get the currently defined date display format.
     * This will need to be modified to use a moodle-defined format over our own.
     *
     * @return string $date_format
     */
    public static function get_date_format() {
        return self::DATE_FORMAT;
    }

    /**
     * Method returns a list of options selectable for the date/time cutoff of
     * extension submission.
     *
     * @return array $options
     */
    public static function get_cutoff_options() {
        // TODO: Translate these strings.
        return array(
                '-1' => 'Disabled',
                '0'  => 'No Cutoff',
                '1'  => '1 Hour',
                '2'  => '2 Hours',
                '4'  => '4 Hours',
                '8'  => '8 Hours',
                '16' => '16 Hours',
                '24' => '1 Day',
                '36' => '1.5 Days',
                '48' => '2 Days'
        );
    }

    /**
     * Method gets a list of the default extension date options for selection on
     * the settings page.
     *
     * @return array $options
     */
    public static function get_default_extension_options() {
        // TODO: Translate these strings.
        return array(
                '24'  => '1 Day',
                '48'  => '2 Days',
                '72'  => '3 Days',
                '96'  => '4 Days',
                '120' => '5 Days',
                '168' => 'One week',
                '336' => 'Two weeks'
        );
    }

    /**
     * Method to determine if extensions are enabled for a specific course module
     * ID.
     *
     * @param int $enable_id
     */
    public static function get_extension_enable_id_by_cmid($cm_id = null) {

        global $DB;

        if(is_null($cm_id)) {
            return false;
        }

        return $DB->get_field('deadline_extensions_enabled', 'id', array('cm_id' => $cm_id));
    }

    /**
     * Extensions enabled for specific course module: DUPLICATE FUNCTIONALITY OF ABOVE!
     *
     * @param int $cm_id
     * @return boolean
     */
    public function extensions_enabled_cmid($cm_id = null) {
        global $DB;

        $conditions = array(
                'cm_id'  => $cm_id,
                'status' => self::EXT_ENABLED
        );

        return $DB->record_exists('deadline_extensions_enabled', $conditions);
    }

    /**
     * Get the extension status based on course module ID. DUPLICATE FUNCTIONALITY OF ABOVE.
     * @param unknown_type $cm_id
     */
    public static function get_extension_status_by_cmid($cm_id = null) {
        global $DB;

        $cm_id = clean_param($cm_id, PARAM_INT);

        return $DB->get_field('deadline_extensions_enabled', 'status', array('cm_id' => $cm_id));
    }

    /**
     * Method to get the module name for a specific course module ID.
     *
     * @param string $modulename
     */
    public static function get_activity_mod_by_cmid($cm_id = null) {
        return extensions_plugin::get_activity_detail_by_cmid($cm_id)->modname;
    }

    /**
     * Method to get the name of an activity based on the course module ID.
     *
     * @param string $activity_name
     */
    public static function get_activity_name($cm_id = null) {
        return extensions_plugin::get_activity_detail_by_cmid($cm_id)->name;
    }

    /**
     * Method to get an extension by it's extension ID in the deadline_extensions
     * table
     *
     * @param int $id Extension ID
     * @throws coding_exception
     */
    public static function get_extension_by_id($id = null) {

        if(is_null($id)) {
            throw new coding_exception('Extension ID Cannot be null. This must be fixed by a developer');
        }

        $detail = new extensions_plugin();
        $detail->id = $id;

        $ext = new extensions_plugin();
        return $ext->get_extension($detail);
    }

    public static function get_extensions_by_cmid($cm_id = null, $user_id = null) {
        global $DB;

        if(is_null($cm_id)) {
            //throw new coding_exception('CM ID Cannot be null. This must be fixed by a developer');
        }

        if(is_null($user_id)) {
            //throw new coding_exception('User ID Cannot be null. This must be fixed by a developer');
        }

        $detail = array(
                'cm_id' => $cm_id,
                'student_id' => $user_id
        );

        if($exts = $DB->get_records('deadline_extensions', $detail)) {
            return $exts;
        } else {
            return false;
        }
    }

    /**
     * Gets the details of the extension being enabled based on the course module ID.
     *
     * @param object $extensiondetail
     */
    public static function get_activity_extension_detail($cm_id = null) {
        global $DB;

        $cm_id = clean_param($cm_id, PARAM_INT);

        return $DB->get_record('deadline_extensions_enabled', array('cm_id' => $cm_id));
    }

    /**
     * Get extension request cutoff by course module ID.
     *
     * @param int $request_cutoff
     */
    public static function get_extension_cutoff_by_cmid($cm_id = null) {

        global $DB;

        $cm_id = clean_param($cm_id, PARAM_INT);
        $params = array('cm_id' => $cm_id);

        if($extension = $DB->get_record('deadline_extensions_enabled', $params, 'request_cutoff')) {
            return $extension->request_cutoff;
        }

        return false;
    }

    /**
     * Get the activity detail by course module ID.
     *
     * @param int $cm_id
     *
     * @return array $cm
     */
    public static function get_activity_detail_by_cmid($cm_id = null) {

        if(is_null($cm_id)) {
            return false;
        }

        $modinfo = get_fast_modinfo(extensions_plugin::get_courseid_for_cmid($cm_id)->course);
        return $modinfo->get_cm($cm_id);
    }

    public static function get_activity_type_by_cmid($cm_id) {
        return extensions_plugin::get_activity_detail_by_cmid($cm_id)->modname;
    }

    public static function get_activity_id_by_extid($ext_id) {
        global $DB;

        if(is_null($ext_id)) {
            return false;
        }

        $params = array(
                'id' => $ext_id
        );

        return $DB->get_field('deadline_extensions', 'cm_id', $params);
    }

    /**
     * Method to get the current activity open date based on the course module
     * ID and modules own table.
     *
     * @param int $cm_id Course Module ID to find the open date for.
     * @return int $open_date Open Date of the specific activity as defined in the activity
     */
    public static function get_activity_open_date($cm_id = null) {
        global $DB;

        if(is_null($cm_id)) {
            return false;
        }

        $activity_detail = extensions_plugin::get_activity_detail_by_cmid($cm_id);

        // Column names are as follows:
        // assign:
        // assignment:
        // quiz: timeopen
        // choice: timeopen
        // forum: assesstimestart
        // lesson: available
        // scorm: timeopen
        // workshop: assessmentstart

        $conditions = array(
                'id' => $activity_detail->instance
        );

        switch($activity_detail->modname) {
            case 'assign':
                return $DB->get_field('assign', 'allowsubmissionsfromdate', $conditions);
                break;
            case 'assignment':
                return $DB->get_field('assignment', 'timeavailable', $conditions);
                break;
            case 'quiz':
                return $DB->get_field('quiz', 'timeopen', $conditions);
                break;
            case 'choice':
                return $DB->get_field('choice', 'timeopen', $conditions);
                break;
            case 'forum':
                return $DB->get_field('forum', 'assesstimestart', $conditions);
                break;
            case 'lesson':
                return $DB->get_field('lesson', 'available', $conditions);
                break;
            case 'scorm':
                return $DB->get_field('scorm', 'timeopen', $conditions);
                break;
            case 'workshop':
                return $DB->get_field('workshop', 'assessmentstart', $conditions);
                break;
        }

        return -1;
    }

    /**
     * Get the current activity due date as defined in the activities own table.
     *
     * @param int $cm_id Course Module ID.
     * @return int $due_date Current Due Date for the course module.
     */
    public static function get_activity_due_date($cm_id = null) {
        global $DB;

        if(is_null($cm_id)) {
            return false;
        }

        $activity_detail = extensions_plugin::get_activity_detail_by_cmid($cm_id);

        // Column names are as follows:
        // assign: duedate
        // assignment: timedue
        // quiz: timeclose
        // choice: timeclose
        // forum: assesstimefinish
        // lesson: deadline
        // scorm: timeclose
        // workshop: assessmentend

        $conditions = array(
                'id' => $activity_detail->instance
        );

        switch($activity_detail->modname) {
            case 'assign':
                return $DB->get_field('assign', 'duedate', $conditions);
                break;
            case 'assignment':
                return $DB->get_field('assignment', 'timedue', $conditions);
                break;
            case 'quiz':
                return $DB->get_field('quiz', 'timeclose', $conditions);
                break;
            case 'choice':
                return $DB->get_field('choice', 'timeclose', $conditions);
                break;
            case 'forum':
                return $DB->get_field('forum', 'assesstimefinish', $conditions);
                break;
            case 'lesson':
                return $DB->get_field('lesson', 'deadline', $conditions);
                break;
            case 'scorm':
                return $DB->get_field('scorm', 'timeclose', $conditions);
                break;
            case 'workshop':
                return $DB->get_field('workshop', 'assessmentend', $conditions);
                break;

        }

    }

    public static function get_all_extensions_by_courseid($course_id = null) {

    }

    public static function get_all_extensions_by_student_id($student_id = null) {

    }

    public static function get_all_extensions_by_cmid($cm_id = null) {

    }

    public static function get_all_extensions_by_staffid($staff_id = null) {

    }

    /**
     * Count of extensions for a specific user, of a specific status (optional)
     *
     * @param int $staff_id ID Of the staff member to count extensions for.
     * @param int $status Status of the extensions to search for.
     * @return number
     */
    public static function get_count_all_extensions_by_staffid($staff_id = null, $status = null) {

        global $DB;

        if(is_null($staff_id)) {
            return 0;
        }

        $criteria['staff_id'] = $staff_id;

        if(!is_null($status)) {
            $criteria['status'] = $status;
        }

        // This will return ONLY the content of the COUNT column, as defined below
        return $DB->get_record('deadline_extensions', $criteria, 'COUNT(*) COUNT')->count;
    }

    public static function get_count_all_extensions_by_status($status = null) {

    }

    /**
     * Get all extensions based on a specific filter.
     *
     * @param array $filters Filters to apply to the search.
     * @return object $extensions Extensions discovered with the required criteria.
     */
    public static function get_count_all_extensions_by_filter($filters = null) {
        global $DB, $COURSE;

        if(isset($SESSION->ext_filters)) {
            $filters = $SESSION->ext_filters;
        } else {
            $filters = null;
        }

        $params = array();

        if(!is_null($filters)) {
            if(isset($filters->activity_id) && $filters->activity_id != 0) {
                $params['cm_id'] = $filters->activity;
            }

            if(isset($filters->status_id) && $filters->status_id != 0) {
                $params['status'] = $filters->status;
            }

            if(isset($filters->class_id) && $filters->class_id != 0) {
//                 $filter_string .= " AND MGG.GROUPID = '{$filters->class_id}' ";
            }

            if(isset($filters->users) && $filters->users != 0) {
                $params['staff_id'] = $filters->users;
            }
        }

        $types = array(
                extensions_plugin::EXT_INDIVIDUAL,
                extensions_plugin::EXT_GROUP
        );

        list($ext_type, $params) = $DB->get_in_or_equal($types, SQL_PARAMS_NAMED);

        $params['course'] = $COURSE->id;

        $sql = "SELECT de.* " .
               "FROM {deadline_extensions} de, {course_modules} cm " .
               "WHERE de.cm_id = cm.id AND de.ext_type " . $ext_type . " AND cm.course = :course";

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Get a count of all extensions for a specific course module id and status (optional).
     *
     * @param int $cm_id
     * @param int $status
     * @return object extensions found as with the specified parameterss
     */
    public static function get_extensions_count($cm_id = null, $status = null) {

        global $DB;

        if(is_null($cm_id)) {
            return 0;
        }

        $criteria = array('cm_id' => $cm_id);

        if(!is_null($status)) {
            $criteria['status'] = $status;
        }

        // This will return ONLY the content of the COUNT column, as defined below
        return $DB->get_record('deadline_extensions', $criteria, 'COUNT(*) COUNT')->count;
    }


    public static function is_extension_approver($ext = null, $user = null, $cmid = null) {
        // TODO: IMPLEMENT THIS!
        return true;
    }

    /**
     * Discover if the extensions module is enabled globally.
     *
     * @return boolean True of the module is enabled, otherwise false.
     */
    public function is_enabled() {
        if (get_config('deadline_extensions', 'enabled') == 1) {
            return true;
        }
        return false;
    }

    /**
     * Depending on the supplied status returns a string denoting weather that
     * status is enabled or not.
     *
     * @param string $status Yes for enabled, No for not enabled.
     */
    public static function extensions_enabled($status = NULL) {

        if(is_null($status)) {
            return false;
        }

        if($status) {
            return get_string('yes');
        } else {
            return get_string('no');
        }
    }

    /**
     * Based on the Course Module ID find the Course ID it is associated with.
     *
     * @param int $cm_id
     * @return object $course Object is returned containing a single course item with the course ID.
     */
    public static function get_courseid_for_cmid($cm_id = null) {

        global $DB;

        if(is_null($cm_id)) {
            return false;
        }

        $params = array('id' => $cm_id);

        return $DB->get_record('course_modules', $params, 'course');
    }

    public static function get_groupingid_for_cmid($cm_id = null) {
        global $DB;

        if(is_null($cm_id)) {
            return false;
        }

        $params = array('id' => $cm_id);

        return $DB->get_record('course_modules', $params, 'groupingid');
    }

    /**
     * Get the course with extensions for the supplied User.
     *
     * @param object $user User object containing a valid user ID.
     */
    public static function get_courses_with_extensions_for_userid($user = null) {

        global $DB;

        if(is_null($user)) {
            return false;
        }

        if(!isset($user->id)) {
            return false;
        }

        if(!$DB->record_exists('user', array('id' => $user->id))) {
            return false;
        }

        $sql = "SELECT DISTINCT mcm.course id " .
                "FROM {deadline_extensions} mde, {course_modules} mcm " .
                "WHERE mde.staff_id = ? and mde.cm_id = mcm.id";

        $params = array($user->id);

        $courses = $DB->get_records_sql($sql, $params);

        return $courses;
    }

    /**
     * Get a count of how many pending extensions there are for a specific
     * user in a specific course.
     *
     * @param int $user_id User ID to search for.
     * @param int $course_id Course ID to search for.
     * @return int $count Number of pending extension requests for the supplied user/course IDs.
     */
    public static function get_pending_count_for_user($user_id = null, $course_id = null) {

        if(is_null($user_id)) {
            return false;
        }

        if(is_null($course_id)) {
            return false;
        }

        return extensions_plugin::get_count_all_extensions_by_staffid($user_id, extensions_plugin::STATUS_PENDING, $course_id);
    }


    /**
     * Get a specific extension record based on the extension ID.
     *
     * @param extensions_plugin $detail
     * @return object $extension Extensions specific detail.
     */
    public function get_extension(extensions_plugin $detail) {
        global $DB;

        $params = array('id' => $detail->id);

        return $DB->get_record('deadline_extensions', $params);
    }

    public function send_response_email($ext_id = null) {

    }

    public function send_request_email($data, $status= null) {

    }

    public function add_event($form_data = null) {

    }

    /**
     * Check for duplicate requests with the provided details.
     *
     * @param int $cm_id Course Module ID. Required.
     * @param int $student_id Student ID. Required.
     * @param int $existing_id Existing ID to exclude from current search. Optional.
     * @return object $extensions Returns an object containing all the duplicate extensions.
     */
    public static function duplicate_requests($cm_id = null, $student_id = null, $existing_id = null, $status = null) {
        global $DB;

        // We sometimes want to see if there's duplicates that are not this ID
        if(!is_null($existing_id)) {

            $sql = "SELECT id, cm_id, staff_id, status, date, timelimit " .
                    "FROM {deadline_extensions} " .
                    "WHERE cm_id = ? AND student_id = ? AND id != ?";

            $vars = array($cm_id, $student_id, $existing_id);

        } else {
            // Other times we want to see if there is already a request, such as
            // as when requesting a new extension, and duplicates are dis-allowed.

            $sql = "SELECT id, cm_id, staff_id, status, date, timelimit " .
                    "FROM {deadline_extensions} " .
                    "WHERE cm_id = ? AND student_id = ?";

            $vars = array($cm_id, $student_id);

        }

        if(!is_null($status)) {
            $sql = $sql . " AND status = ? ";
            $vars[] = $status;
        }

        return $DB->get_records_sql($sql, $vars);
    }

    /**
     * Generate a formatted string suitable for use in a text link containing
     * the current pending count, for the specific user details provided.
     *
     * @param int $user User to use in query.
     * @param int $course Course ID to use in query.
     * @param bool $tags
     * @return mixed $text Returns a text string if show_pending_count is enabled, otherwise
     */
    public static function get_pending_count_text($user = null, $course = null, $tags = true) {

        if(is_null($user)) {
            return false;
        }

        if(get_config(extensions_plugin::EXTENSIONS_MOD_NAME, 'show_pending_count')) {

            $count = 0;

            if(is_null($course) || $course->id == '0') {
                // find all courses the user is associated with.
                $courses = extensions_plugin::get_courses_with_extensions_for_userid($user);

                // get the courses
                foreach($courses as $course) {
                    $count += extensions_plugin::get_count_all_extensions_by_staffid($user->id, extensions_plugin::STATUS_PENDING, $course->id);
                }

            } else {
                // just get the count for a single course.
                $count = extensions_plugin::get_count_all_extensions_by_staffid($user->id, extensions_plugin::STATUS_PENDING, $course->id);

            }

            if($count == '0') {
                return '';
            }

            if($tags) {
                $count_tag   = html_writer::tag('b',   $count);
                $content_tag = html_writer::tag('i',   $count_tag . ' ' . get_string('status_pending', extensions_plugin::EXTENSIONS_LANG));
                $main_tag    = html_writer::tag('sup', $content_tag);
                return $main_tag;
            } else {
                return $count . ' ' . get_string('status_pending', extensions_plugin::EXTENSIONS_LANG);
            }


        }
        return false;
    }

    public function get_group_submission_for_cmid($cmid = null) {
        global $DB;
        // check if this cm_id allows a group submission
        // is m_assign the only group submission item? Hopefully.

        // table m_assign
        // field teamsubmission == 1
        // field teamsubmissiongroupingid == grouping id that the groups come from (ID is valid grouping, 0 is all?)

        $grouping_id = null;

        $mod = $this->get_activity_detail_by_cmid($cmid);

        $params = array(
                'id' => $mod->instance
        );

        switch ($mod->modname) {
            case 'assign':
                $detail = $DB->get_record('assign', $params, 'id, teamsubmission, teamsubmissiongroupingid', MUST_EXIST);
                if($detail->teamsubmission != '0') {
                    // This is a team submission.
                    $grouping_id = $detail->teamsubmissiongroupingid;
                }
                break;
        }

        return $grouping_id;
    }

    public static function is_timelimit_extension($ext_id) {
        global $DB;

        $params = array(
                'id' => $ext_id,
                'date' => '0',
        );

        if($DB->record_exists('deadline_extensions', $params)) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * Save the data posted from the 'Quick Approve' function.
     *
     * @param object $form_data Form data as supplied from MoodleForm.
     */
    public static function save_quick_approve($form_data) {

        global $DB, $USER;

        foreach($form_data->extension_requests as $ext_id => $status) {

            // Update main record.
            $item = new stdClass;
            $item->id = $ext_id;
            $item->status = extensions_plugin::STATUS_APPROVED;

            if($DB->update_record('deadline_extensions', $item)) {

                $form_data->eid             = $item->id;
                $form_data->ext_status_code = $item->status;
                $form_data->response_text   = "";

                // Add to the extensions history table.
                extensions_plugin::add_history($form_data);

                // Send a message to the user to notify of the update.
                extensions_plugin::notify_user($form_data);

                $ext = extensions_plugin::get_extension_by_id($item->id);

                // add item to the calendar.
                $cal              = new stdClass();
                $cal->name        = '';
                $cal->description = '';
                $cal->userid      = '';
                $cal->modulename  = '';
                $cal->instance    = $ext->cm_id;
                $cal->eventtype   = '';
                $cal->timestart   = $ext->date;

                //calendar_event::create($cal);

            } else {
                return false;
            }

        }

        return true;

    }

    public function get_activity_group_extensions($cm_id) {
        global $DB;

        if(is_null($cm_id)) {
            return false;
        }

        $params = array(
                'cm_id' => $cm_id,
                'ext_type' => extensions_plugin::EXT_GROUP
        );

        return $DB->get_records('deadline_extensions', $params);
    }

    public static function get_extension_documents($ext_id = null) {

        return false;
    }

    /**
     * Determine the difference in due date and extension date, in days.
     *
     * @param int $due_date Due date for the activity.
     * @param int $extension_date Extension date for the activity.
     * @return int $days Number of days between Due Date and Extension date.
     */
    public static function date_difference($due_date = null, $extension_date = null) {

        if(is_null($due_date)) {
            return 0;
        }

        if(is_null($extension_date)) {
            return 0;
        }

        return floor( ($extension_date - $due_date) / 86400 );
    }

    // --
    // Methods to build Tables as used in Extensions.
    // --

    /**
     * Build a UI table displaying the history of a specific Extension.
     *
     * @param int $ext_id Extension ID to get the history for.
     * @return object $table Table containing the data to be rendered.
     */
    public static function build_extension_history_table($ext_id) {
        global $DB;

        $items = $DB->get_records('deadline_extensions_history', array('extension_id' => $ext_id), 'change_date desc');

        // Create the table
        $table = new html_table();

        $table->width = "100%";

        // TODO: Add these strings to translation file.
        $table->head  = array (
                'Extension Date',
                'Status',
                'Request Text',
                'Response Text',
                'Staff ID',
                'Changed By User',
                'Date Change Made'
        );

        foreach($items as $item) {
            // build a table here to return.

            $userDetail  = $DB->get_record('user', array('id' => $item->user_id),  '*', MUST_EXIST);
            $userLink    = new moodle_url('/user/view.php', array('id' => $item->user_id));

            // Create cells
            $extensionDate = new html_table_cell();
            $statusText    = new html_table_cell();
            $requestText   = new html_table_cell();
            $responseText  = new html_table_cell();
            $staffId       = new html_table_cell();
            $userId        = new html_table_cell();
            $dateChanged   = new html_table_cell();

            $noChangeText = html_writer::tag('i', get_string('no_change', extensions_plugin::EXTENSIONS_LANG));

            // Populate the cells with data
            if($item->date == 0) {
                $extensionDate->text = $noChangeText;
            } else {
                $extensionDate->text = userdate($item->date);
            }

            if($item->status == '') {
                $statusText->text    = $noChangeText;
            } else {
                $statusText->text    = extensions_plugin::get_status_string($item->status);
            }

            if(is_null($item->request_text)) {
                $requestText->text   = $noChangeText;
            } else {
                $requestText->text   = $item->request_text; // will need to be truncated
            }

            if(is_null($item->response_text)) {
                $responseText->text  = $noChangeText;
            } else {
                $responseText->text  = $item->response_text; // will need to be truncated
            }

            if($item->staff_id == 0) {
                $staffId->text       = $noChangeText;
            } else {
                $staffDetail   = $DB->get_record('user', array('id' => $item->staff_id), '*', MUST_EXIST);
                $staffLink     = new moodle_url('/user/view.php', array('id' => $item->staff_id));
                $staffId->text = html_writer::link($staffLink, $staffDetail->firstname . " " . $staffDetail->lastname);
            }

            $userId->text        = html_writer::link($userLink, $userDetail->firstname . " " . $userDetail->lastname);
            $dateChanged->text   = userdate($item->change_date);

            // Add the cells to a row
            $thisRow = new html_table_row();
            $thisRow->cells = array(
                    $extensionDate,
                    $statusText,
                    $requestText,
                    $responseText,
                    $staffId,
                    $userId,
                    $dateChanged
            );

            // Add the rows to a table.
            $table->data[$item->id] = $thisRow;

        }

        // Render and return the data.
        return $table;
    }

    public static function build_student_extensions_table($student_id = null, $course = null) {

        global $CFG, $USER, $DB;

        $table = new html_table();
        $table->width = "100%";

        $timenow = time();
        $table->head  = array (
                get_string("extassessmentname", extensions_plugin::EXTENSIONS_LANG),
                get_string("startdate",         extensions_plugin::EXTENSIONS_LANG),
                get_string("duedate",           extensions_plugin::EXTENSIONS_LANG),
                get_string("extensiondate",     extensions_plugin::EXTENSIONS_LANG),
                get_string("extrequestdate",    extensions_plugin::EXTENSIONS_LANG),
                get_string("extapprover",       extensions_plugin::EXTENSIONS_LANG),
                get_string("extstatus",         extensions_plugin::EXTENSIONS_LANG)
        );

        $table->align = array ("left", "left", "left", "left", "center", "center");

        $ext = new extensions_plugin;
        $activities = $ext->get_activity_names($course);

        $i = 0;

        foreach ($activities as $activity) {

            $deadlines  = new deadlines_plugin();
            $deadline   = $deadlines->get_deadlines_for_cmid($activity->id);
            $extensions = extensions_plugin::get_extensions_by_cmid($activity->id, $USER->id);

            $activityNameCell = new html_table_cell();
            $startDateCell    = new html_table_cell();
            $dueDateCell      = new html_table_cell();
            $extensionDate    = new html_table_cell();
            $requestDate      = new html_table_cell();
            $approver         = new html_table_cell();
            $status           = new html_table_cell();

            $params = array(
                    'id' => $activity->id
            );

            // Dim the row under the following circumstances:
            // Due Date passed
            // Not yet open
            // Extensions not allowed.

            $attribs = array(

            );

            $activity_url  = new moodle_url('/mod/' . $activity->modname . '/view.php', $params);
            $activity_link = html_writer::link($activity_url, $activity->name, $attribs);

            $activityNameCell->text = $activity_link;
            $startDateCell->text    = userdate($deadline->date_open);
            $dueDateCell->text      = userdate($deadline->date_deadline);
            $extensionDate->text    = '';
            $requestDate->text      = '';
            $approver->text         = '';
            $status->text           = ''; // extensions_plugin::get_status_string($this_extension->status);

            $thisRow = new html_table_row();
            $thisRow->cells = array(
                    $activityNameCell,
                    $startDateCell,
                    $dueDateCell,
                    $extensionDate,
                    $requestDate,
                    $approver,
                    $status
            );

            $table->data[$i] = $thisRow;

            $i++;

            if($extensions === false) {
                continue;
            }

            //-----
            // Add any extensions now.
            foreach($extensions as $extension) {

                $staff = $DB->get_record('user', array('id' => $extension->staff_id), '*', MUST_EXIST);

                // Missing columns will be carried over from above.
                $extensionDate    = new html_table_cell();
                $requestDate      = new html_table_cell();
                $approver         = new html_table_cell();
                $status           = new html_table_cell();

                if(extensions_plugin::is_timelimit_extension($extension->id)) {
                    $extensionDate->text    = ($extension->timelimit / 60) . ' ' . get_string('minutes', self::EXTENSIONS_LANG);
                } else {
                    $diff_days           = extensions_plugin::date_difference($deadline->date_deadline, $extension->date);
                    $date_string         = userdate($extension->date);
                    $date_diff           = html_writer::empty_tag('br') . html_writer::tag('i', $diff_days . get_string('days_after', extensions_plugin::EXTENSIONS_LANG));

                    $extensionDate->text    = $date_string . ' ' . $date_diff;
                }

                $requestDate->text      = userdate($extension->created);

                $params     = array('id' => $extension->staff_id);
                $staff_url  = new moodle_url('/user/profile.php', $params);
                $staff_link = html_writer::link($staff_url, $staff->firstname . ' ' . $staff->lastname);

                $approver->text         = $staff_link;

                $params    = array('eid' => $extension->id, 'page' => 'request_edit');
                $edit_url  = new moodle_url('/deadline/extensions/', $params);
                $edit_link = html_writer::link($edit_url, extensions_plugin::get_status_string($extension->status));

                $status->text           = $edit_link;

                $thisRow = new html_table_row();
                $thisRow->cells = array(
                        $activityNameCell,
                        $startDateCell,
                        $dueDateCell,
                        $extensionDate,
                        $requestDate,
                        $approver,
                        $status
                );

                $table->data[$i] = $thisRow;

                $i++;

            }

        }

        return $table;

    }

    /**
     * Build UI table to be displayed showing all extension requests for a specific course.
     *
     * @param array $filters Filters to be applied to the table display.
     */
    public static function build_extensions_table($filters = null) {

        global $DB, $OUTPUT;

        $table = new html_table();

        $table->width = "100%";

        $table->head  = array (
                '&nbsp;', // this column left intentionally blank.
                get_string("extstudentname",    self::EXTENSIONS_LANG),
                get_string("extusername",       self::EXTENSIONS_LANG),
                get_string("exttype",           self::EXTENSIONS_LANG),
                get_string("extassessmentname", self::EXTENSIONS_LANG),
                get_string("extduedate",        self::EXTENSIONS_LANG),
                get_string("extensiondate",     self::EXTENSIONS_LANG),
                get_string("extrequestdate",    self::EXTENSIONS_LANG),
                get_string("extstatus",         self::EXTENSIONS_LANG),
                get_string("extsentto",         self::EXTENSIONS_LANG),
                get_string("extapprove",        self::EXTENSIONS_LANG),
        );

        if($extensions = extensions_plugin::get_count_all_extensions_by_filter($filters)) {

            foreach($extensions as $extension) {

                // Get the details of the staff member and the student for use in this table row.
                $studentDetail = $DB->get_record('user', array('id' => $extension->student_id), '*', MUST_EXIST);
                $staffDetail   = $DB->get_record('user', array('id' => $extension->staff_id),   '*', MUST_EXIST);

                // Activity detail
                $activity = extensions_plugin::get_activity_detail_by_cmid($extension->cm_id);

                // Define the links used in the table below here.
                $studentNameLink     = new moodle_url('/user/profile.php', array('id' => $studentDetail->id));
                $studentUserNameLink = new moodle_url('/user/profile.php', array('id' => $studentDetail->id));
                $activityLink        = new moodle_url("/mod/{$activity->modname}/view.php", array('id' => $extension->cm_id));
                $staffLink           = new moodle_url('/user/view.php', array('id' => $staffDetail->id));
                $extensionEditUrl    = new moodle_url(extensions_plugin::EXTENSIONS_URL_PATH . '/', array('page' => 'request_edit', 'eid' => $extension->id));

                // Create the cell objects
                $pictureCell         = new html_table_cell();
                $studentNameCell     = new html_table_cell();
                $studentUserNameCell = new html_table_cell();
                $requestTypeCell     = new html_table_cell();
                $activityLinkCell    = new html_table_cell();
                $activityTimeDueCell = new html_table_cell();
                $requestedDateCell   = new html_table_cell();
                $createdDateCell     = new html_table_cell();
                $statusCell          = new html_table_cell();
                $staffNameCell       = new html_table_cell();
                $checkboxCell        = new html_table_cell();

                $deadline  = new deadlines_plugin();
                $deadlines = $deadline->get_deadlines_for_cmid($activity->id);

                // Add the text to each cell in the table.
                $pictureCell->text         = $OUTPUT->user_picture($studentDetail, array('size' => 50));
                $studentNameCell->text     = html_writer::link($studentNameLink, $studentDetail->firstname . " " . $studentDetail->lastname);
                $studentUserNameCell->text = html_writer::link($studentUserNameLink, $studentDetail->username);
                $requestTypeCell->text     = extensions_plugin::get_type_string($extension->ext_type);
                $activityLinkCell->text    = html_writer::link($activityLink, $activity->name);

                // Determine if this is a timelimit extension or a date extension
                if($extension->date == 0 && $extension->timelimit != 0) {
                    // Timelimit extension
                    $activityTimeDueCell->text = ($deadlines->timelimit / 60) . ' ' . get_string('minutes', self::EXTENSIONS_LANG);
                    $requestedDateCell->text   = ($extension->timelimit / 60) . ' ' . get_string('minutes', self::EXTENSIONS_LANG);
                } else {
                    // Date extension
                    $date_diff = html_writer::empty_tag('br') . html_writer::tag('i', extensions_plugin::date_difference($deadlines->date_deadline, $extension->date) . ' days', array('class' => 'days_extension'));

                    $activityTimeDueCell->text = userdate($deadlines->date_deadline);
                    $requestedDateCell->text   = userdate($extension->date) . ' ' . $date_diff;
                }

                $createdDateCell->text     = userdate($extension->created);
                $statusCell->text          = html_writer::link($extensionEditUrl, extensions_plugin::get_status_string($extension->status));
                $staffNameCell->text       = html_writer::link($staffLink, $staffDetail->firstname . " " . $staffDetail->lastname);
                $checkboxCell->text        = "{element}";

                $thisRow = new html_table_row();
                $thisRow->cells = array(
                        $pictureCell,
                        $studentNameCell,
                        $studentUserNameCell,
                        $requestTypeCell,
                        $activityLinkCell,
                        $activityTimeDueCell,
                        $requestedDateCell,
                        $createdDateCell,
                        $statusCell,
                        $staffNameCell,
                        $checkboxCell
                );

                $table->data[$extension->id] = $thisRow;
            }
        } else {
            // No requests found

            $noRecordsCell = new html_table_cell();

            $noRecordsCell->text = "No records found";
            $noRecordsCell->colspan = 12;

            $thisRow = new html_table_row();
            $thisRow->cells = array($noRecordsCell);

            $table->data[0] = $thisRow;
        }

        return $table;

    }

    /**
     * Generate UI table contents containing all activities in a specific course
     * and their current status, with some statistics for the activity.
     *
     * @param object $activities Activities to be rendered in this table.
     * @return object $table Table content to be later rendered.
     */
    public static function build_activity_table($activities) {

        global $DB, $OUTPUT, $USER, $COURSE;

        $table = new html_table();

        $table->width = "100%";

        // TODO: Add these strings to translation file.
        $table->head  = array (
                'Activity',
                'Extensions Enabled',
                'Cut Off Date',
                'Extensions',
                'Pending',
                'Approved',
                'Denied',
                ' '
        );

        // Get the list of activities for this course. This will need to be
        // course context only.

        foreach($activities as $activity) {

            // Get any extension details from the database for this activity.
            $extensionDetail = extensions_plugin::get_activity_extension_detail($activity->id);

            // Define cells for each column.
            $activityNameCell   = new html_table_cell();
            $activityExtensions = new html_table_cell();
            $activityCutoffDate = new html_table_cell();
            $extensionsCount    = new html_table_cell();
            $pendingCount       = new html_table_cell();
            $approvedCount      = new html_table_cell();
            $deniedCount        = new html_table_cell();
            $editLink           = new html_table_cell();

            $activityLink = new moodle_url("/mod/{$activity->modname}/view.php", array('id' => $activity->id));
            $editUrl      = new moodle_url(extensions_plugin::EXTENSIONS_URL_PATH . '/', array('page' => 'configure_activity', 'cmid' => $activity->id));

            // Populate the columns.
            $activityNameCell->text   = html_writer::link($activityLink, $activity->name);
            $activityExtensions->text = '##ext_enabled##'; // placeholder for the dropdown
            $activityCutoffDate->text = '##ext_cutoff##';  // placeholder for the dropdown

            // Set the alignment of the following fields.
            $class = array('class' => 'mdl-align');

            $extensionsCount->attributes = $class;
            $pendingCount->attributes    = $class;
            $approvedCount->attributes   = $class;
            $deniedCount->attributes     = $class;

            if($activity->ext_status) { // if the activity has extensions enabled.

                $extensionsCount->text = extensions_plugin::get_extensions_count($activity->id);
                $pendingCount->text    = extensions_plugin::get_extensions_count($activity->id, self::STATUS_PENDING);
                $approvedCount->text   = extensions_plugin::get_extensions_count($activity->id, self::STATUS_APPROVED);
                $deniedCount->text     = extensions_plugin::get_extensions_count($activity->id, self::STATUS_DENIED);

            } else {

                $extensionsCount->text = '--';
                $pendingCount->text    = '--';
                $approvedCount->text   = '--';
                $deniedCount->text     = '--';
            }

            $editLink->text  = html_writer::link($editUrl, get_string('edit'));

            $thisRow = new html_table_row();
            $thisRow->cells = array(
                    $activityNameCell,
                    $activityExtensions,
                    $activityCutoffDate,
                    $extensionsCount,
                    $pendingCount,
                    $approvedCount,
                    $deniedCount,
                    $editLink
            );

            $table->data[$activity->id] = $thisRow;
        }


        return $table;
    }



    public function build_global_extensions_table($course = null) {

        global $DB, $OUTPUT, $USER, $COURSE;

        $table = new html_table();

        $table->width = "100%";

        $table->head = array (
                get_string("extassessmentname", extensions_plugin::EXTENSIONS_LANG),
                get_string("extgrouping",       extensions_plugin::EXTENSIONS_LANG),
                get_string("startdate",         extensions_plugin::EXTENSIONS_LANG),
                get_string("extduedate",        extensions_plugin::EXTENSIONS_LANG),
                get_string("extdate",           extensions_plugin::EXTENSIONS_LANG),
                get_string("extcreator",        extensions_plugin::EXTENSIONS_LANG),
                get_string("extedit",           extensions_plugin::EXTENSIONS_LANG),
        );

        $table->align = array ("left", "left", "left", "left", "center","center","center");

        $ext = new extensions_plugin;

        $activities = $ext->get_activity_names($course);

        $deadlines = new deadlines_plugin();
        foreach($activities as $activity) {

            $activity_detail = $ext->activity_detail($activity->id);

            // Add a row here for each activity.
            $activityName       = new html_table_cell();
            $activityGrouping   = new html_table_cell();
            $activityStartDate  = new html_table_cell();
            $activityDueDate    = new html_table_cell();
            $activityExtDate    = new html_table_cell();
            $activityExtCreator = new html_table_cell();
            $activityExtEdit    = new html_table_cell();

            // Activity name
            $params = array('id' => $activity->id);
            $activity_link = new moodle_url('/mod/' . $activity->modname . '/view.php', $params);
            $activityName->text = html_writer::link($activity_link, $activity->name);

            // Grouping
            if(isset($activity_detail->groupingid) && $activity_detail->groupingid != 0) {
                $params = array('id' => $activity_detail->course);
                $grouping_link = new moodle_url('/group/groupings.php', $params);
                $grouping_name = html_writer::link($grouping_link, groups_get_grouping_name($activity_detail->groupingid));
                $activityGrouping->text = $grouping_name;
            } else {
                $activityGrouping->text = html_writer::tag('i', get_string('no_grouping_assigned_short', extensions_plugin::EXTENSIONS_LANG));
            }

            // Open Date
            if($open_date = $deadlines->get_deadline_date_open($activity->id)) {
                $activityStartDate->text = userdate($open_date);
            } else {
                $activityStartDate->text = html_writer::tag('i', 'No central deadline found.');
            }

            // Due Date
            if($deadline = $deadlines->get_deadline_date_deadline($activity->id)) {
                $activityDueDate->text = userdate($deadline);
            } else {
                $activityDueDate->text = html_writer::tag('i', 'No central deadline found.');
            }

            // Global Extension Date
            $activityExtDate->text = ' '; // nothing at this level.

            // Creator
            $activityExtCreator->text = ' ';

            // Edit/Create
            $params = array(
                    'cmid' => $activity->id,
                    'page' => 'global_add'
            );
            $link_url = new moodle_url('/deadline/extensions/', $params);
            $link_text = html_writer::link($link_url, get_string('create', extensions_plugin::EXTENSIONS_LANG));
            $activityExtEdit->text = $link_text;


            $thisRow = new html_table_row();
            $thisRow->cells = array(
                    $activityName,
                    $activityGrouping,
                    $activityStartDate,
                    $activityDueDate,
                    $activityExtDate,
                    $activityExtCreator,
                    $activityExtEdit
            );

            $table->data[$activity->id] = $thisRow;

            // Check if any Group extensions exist for this activity & course.

            $params = array(
                    'cm_id'    => $activity->id,
                    'ext_type' => extensions_plugin::EXT_GLOBAL
            );

            if($global_exts = $DB->get_records('deadline_extensions', $params)) {

                foreach($global_exts as $global_ext) {

                    $extensionDateCell = new html_table_cell();
                    $creatorCell       = new html_table_cell();
                    $editCell          = new html_table_cell();

                    $extensionDateCell->text = userdate($global_ext->date);

                    $staff = $DB->get_record('user', array('id' => $global_ext->staff_id), '*', MUST_EXIST);

                    $params = array('id' => $global_ext->staff_id);
                    $staff_url = new moodle_url('/user/profile.php', $params);
                    $staff_link = html_writer::link($staff_url, $staff->firstname . ' ' . $staff->lastname);

                    $creatorCell->text = $staff_link;

                    $params = array('eid' => $global_ext->id, 'cmid' => $activity->id,  'page' => 'global_edit');
                    $edit_url  = new moodle_url('/deadline/extensions/', $params);
                    $edit_link = html_writer::link($edit_url, get_string('edit', extensions_plugin::EXTENSIONS_LANG));
                    $editCell->text = $edit_link;

                    $ext_row = new html_table_row();
                    $ext_row->cells = array(
                            '','','','',
                            $extensionDateCell,
                            $creatorCell,
                            $editCell,
                            );

                    $table->data[$global_ext->id] = $ext_row;

                }

            }


        }

        return $table;
    }

    /**
     * Delete specific data stored by this module for a specific course module ID.
     *
     * @see deadline_plugin::delete_cmid()
     *
     * @param int $cmid Course Module ID to delete data for.
     * @return bool True returned if successful.
     */
    protected function delete_cmid($cm_id) {
        global $DB;

        $params = array('cmid' => $cm_id);

        if(!$DB->delete_records('deadline_extensions', $params)) {
            print_error('cannotdeletedeadlines', '', course_get_url($data->course, $data->section), $data->modulename);
        }

        return true;
    }

    public function get_individual_approved_extensions($cm_id, $user_id, $field = 'date') {

        global $DB;

        $params = array(
                'cm_id'      => $cm_id,
                'student_id' => $user_id,
                'status'     => extensions_plugin::STATUS_APPROVED,
                'ext_type'   => extensions_plugin::EXT_INDIVIDUAL
        );

        if($exts = $DB->get_records('deadline_extensions', $params)) {
            $exts = $this->date_sort($exts, $field);
            return $exts['0']->{$field};
        }

    }

    public function get_group_approved_extensions($cm_id = null, $user_id = null, $global = false, $field = 'date') {

        global $DB;

        if(is_null($cm_id)) {
            return 0;
        }

        if(is_null($user_id)) {
            return 0;
        }

        // Ok, the extension request should have a group assigned to it, found in the deadline_extensions_appto table.

        // get the course ID for this cm_id
        $course = extensions_plugin::get_courseid_for_cmid($cm_id);

        // Does this activity allow groupings?
        $grouping = extensions_plugin::get_groupingid_for_cmid($cm_id);

        // Based on the grouping assigned to a user and this users ID, we can
        // determine the group they are in, in this activity.
        if(!$groups = groups_get_all_groups($course->course, $user_id, $grouping->groupingid, 'g.id, g.name')) {
            // Activity has no grouping assigned, or user is not in any of the
            // groups assigned.
            return 0;
        }

        $act_groups = array();
        foreach($groups as $group) {
            $act_groups[] = $group->id;
        }

        // We have a list of the groups this user is in, based on the grouping
        // assigned to the activity itself.

        // Build SQL to see if there's any extensions approved for this group.
        $params = array();
        list($groups_in_sql, $params) = $DB->get_in_or_equal($act_groups, SQL_PARAMS_NAMED);

        $params['cm_id']    = $cm_id;
        $params['status']   = extensions_plugin::STATUS_APPROVED;

        // If this is a global extension then the type of extension will be different.
        if($global) {
            $params['ext_type'] = extensions_plugin::EXT_GLOBAL;
        } else {
            $params['ext_type'] = extensions_plugin::EXT_GROUP;
        }

        $sql = "SELECT de.* " .
                "FROM {deadline_extensions} de, {deadline_extensions_appto} dea " .
                "WHERE dea.ext_id = de.id " .
                "AND de.cm_id    = :cm_id " .
                "AND de.status   = :status " .
                "AND de.ext_type = :ext_type " .
                "AND dea.group_id " . $groups_in_sql;

        if($exts = $DB->get_records_sql($sql, $params)) {
            $exts = $this->date_sort($exts, $field);
            return $exts['0']->{$field};
        }

        return 0;
    }

    public function get_my_open_date($cm_id, $user_id = null) {
        // For now Extensions isn't modifying any open dates for specific users.
        // This will simply return 0 when called; and deadline will essentially
        // ignore this, as the open date from the Deadlines plugin will always
        // be later, and hence used over this date.

        // This will allow for modifications to either this plugin, or to allow
        // other plugins that allow for a certain earlier open date for example
        // as may be required in the case of a special needs student.
        return 0;
    }

    /**
     * Get the due date this module believes is the longest date.
     *
     * @see deadline_plugin::get_my_due_date()
     * @param int $cm_id Course Module ID to check for the specific dates for.
     * @param int $user_id User ID to use in generating the data for.
     * @return int $due_date Due date for this user for this activity.
     */
    public function get_my_due_date($cm_id, $user_id = null) {

        // Extensions are only valid in the context of a user. Weather that's
        // individual, global, or group based, there will always be a user. If
        // no user is specified then we simply return 0, deadline will then
        // ignore the result as it's always going to be WAY less than any other
        // specified deadline from any other module.
        if(is_null($user_id)) {
            return 0;
        }

        // We need to find any/all extensions on this activity that exist
        // for this user. They could be:

        // 1) Individual Extension
        $dates['indiv']  = $this->get_individual_approved_extensions($cm_id, $user_id, 'date');

        // 2) Group extension (for a group submission in mod_assign)
        $dates['group']  = $this->get_group_approved_extensions($cm_id, $user_id, false, 'date');

        // 3) Global Extension
        $dates['global'] = $this->get_group_approved_extensions($cm_id, $user_id, true, 'date');

        return $this->get_configured_date($dates);
    }

    public function get_my_timelimit($cm_id, $user_id = null) {
        // Insert code here for returning a specific time limit for activities
        // that allow a specific time limit (ie Quiz).
        if(is_null($user_id)) {
            return 0;
        }

        // Individual timelimit extension
        $timelimit['indiv'] = $this->get_individual_approved_extensions($cm_id, $user_id, 'timelimit');

        // Group timelimit extension.
        $timelimit['group']  = $this->get_group_approved_extensions($cm_id, $user_id, false, 'timelimit');

        // Global timelimit extension.
        $timelimit['global'] = $this->get_group_approved_extensions($cm_id, $user_id, true, 'timelimit');

        return $this->get_configured_date($timelimit);
    }

    public function get_configured_date($dates) {
        // This can be modified at a later date such that the exact date that
        // is returned and used could be manipulated, according to configuration, examples:
        // Group extensions take priority over Individual
        // Globals take priority over Group
        // Individuals take priority over Global.
        // There could be many combinations or preferences for different organisations.

        // For now my short-sighted view is that it should (probably) always simply
        // return the 'longest' extension date as the date used.
        return max($dates);
    }

    public function get_my_cutoff_date($cm_id, $user_id = null) {
        if(is_null($user_id)) {
            return 0;
        }

        // This isn't implemented as yet. Feel free to do it!

        return 0;
    }

    public static function add_history($form_data) {
        global $DB, $USER, $COURSE;

        // ADD TO HISTORY.
        // Add item to history table
        $hist                = new stdClass;
        $hist->extension_id  = $form_data->eid;
        $hist->status        = $form_data->ext_status_code;
        $hist->user_id       = $USER->id;
        $hist->response_text = $form_data->response_text;
        $hist->change_date   = date("U");

        if(!$DB->insert_record('deadline_extensions_history', $hist)) {
            return false;
        }

        add_to_log($COURSE->id, "extensions", "success", "index.php", "extension {$form_data->eid} history added!");

        return true;
    }

    public static function notify_user($form_data) {
        global $DB, $COURSE;

        // SEND USER NOTIFICATION OF UPDATE
        // http://docs.moodle.org/dev/Messaging_2.0

        $data = extensions_plugin::get_extension_by_id($form_data->eid);

        $staff_detail   = $DB->get_record('user', array('id' => $data->staff_id));
        $student_detail = $DB->get_record('user', array('id' => $data->student_id));

        // Generate a message to the user. In our case it's very generic.
        $message_data            = new stdClass;
        $message_data->component = extensions_plugin::EXTENSIONS_MOD_NAME;
        $message_data->name      = 'extension_updated';
        $message_data->userfrom  = $staff_detail;
        $message_data->userto    = $student_detail;

        // If the status has just been set to Revoked or Withdrawn,
        // we don't want to add a calendar item etc.
        if($form_data->ext_status_code == extensions_plugin::STATUS_APPROVED) {

            // Check to see if there is already a calendar event.
            // Add a calendar event.

            // ADD EVENT TO USER CALENDAR.
            $email_subject = get_config('deadline_extensions', 'approved_subject');
            $email_content = get_config('deadline_extensions', 'approved_text');
        }

        if($form_data->ext_status_code == extensions_plugin::STATUS_DENIED) {

            // Remove any calendar events this user may have for this extension.

            $email_subject = get_config('deadline_extensions', 'denied_subject');
            $email_content = get_config('deadline_extensions', 'denied_text');
        }

        if($form_data->ext_status_code == extensions_plugin::STATUS_WITHDRAWN) {

            // Remove any calendar events this user may have for this extension.

            $email_subject = get_config('deadline_extensions', 'withdrawn_subject');
            $email_content = get_config('deadline_extensions', 'withdrawn_text');
        }

        if($form_data->ext_status_code == extensions_plugin::STATUS_REVOKED) {

            // Remove any calendar events this user may have for this extension.

            $email_subject = get_config('deadline_extensions', 'revoked_subject');
            $email_content = get_config('deadline_extensions', 'revoked_text');
        }

        if($form_data->ext_status_code == extensions_plugin::STATUS_MOREINFO) {

            // Remove any calendar events this user may have for this extension.

            $email_subject = get_config('deadline_extensions', 'more_info_subject');
            $email_content = get_config('deadline_extensions', 'more_info_text');

        } else {

            $email_subject = get_config('deadline_extensions', 'more_info_subject');
            $email_content = get_config('deadline_extensions', 'more_info_text');

        }

        // Add a link to the extension page at the bottom of the email
        $params    = array('eid' => $form_data->eid);
        $link_url  = new moodle_url('/deadline/extensions/', $params);
        $link_text = get_string('ext_email_link', extensions_plugin::EXTENSIONS_LANG);

        $email_content .= html_writer::empty_tag('br');
        $email_content .= html_writer::empty_tag('br');
        $email_content .= html_writer::link($link_url, $link_text);
        $email_content .= html_writer::empty_tag('br');
        $email_content .= html_writer::empty_tag('br');
        $email_content .= get_string('ext_email_donot_reply', extensions_plugin::EXTENSIONS_LANG);

        $message_data->subject           = $email_subject;
        $message_data->fullmessage       = $email_content;
        $message_data->fullmessagehtml   = $email_content;
        $message_data->fullmessageformat = FORMAT_HTML;
        $message_data->smallmessage      = $email_subject;

        if(message_send($message_data)) {

            print"event trigger successful";

            add_to_log($COURSE->id, "extensions", "success", "index.php", "extension notification message successful!");
            return true;
        } else {

            print"event trigger failed";

            add_to_log($COURSE->id, "extensions", "error", "index.php", "extension notification message failed!");
            return false;
        }

    }

}

