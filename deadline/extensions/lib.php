<?php

require_once($CFG->dirroot . '/deadline/lib.php');

class extensions_plugin extends deadline_plugin {

    private  $plugin_weight = 1;

    // Define the constants for Individual
    // and Class level extensions.
    const EXT_INDIVIDUAL = 0;
    const EXT_CLASS      = 1;

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

    const LANG_EXTENSIONS     = 'deadline_extensions';
    const EXTENSIONS_MOD_NAME = 'deadline_extensions';
    const EXTENSIONS_URL_PATH = '/deadline/extensions';

    const DATE_FORMAT      = 'l, j F Y, H:i A';

    public function get_form_elements($mform, $context, $modulename = "") {

        $mform->addElement('selectyesno','extensions_allowed', 'Extensions allowed?');

    }

    /**
     * hook to save extensions specific settings on a module settings page
     * @param object $data - data from an mform submission.
     */
    public function save_form_elements($data) {

    }

    /**
     * Hook for getting a deadline for a course module id
     * @param int $cmid
     *
     */
    public function get_deadline($cmid, $deadline_type) {

    }

    /**
     * hook for cron
     *
     */
    public function deadline_cron() {

    }

    // ------

    /**
    * @param unknown_type $status
    */
    public static function get_status_string($status = null) {

        if(is_null($status)) {
            throw exception('Status cannot be null');
        }

        switch($status) {
            case '0':
                return get_string('status_none', self::LANG_EXTENSIONS);
                break;
            case '1':
                return get_string('status_pending', self::LANG_EXTENSIONS);
                break;
            case '2':
                return get_string('status_approved', self::LANG_EXTENSIONS);
                break;
            case '3':
                return get_string('status_denied', self::LANG_EXTENSIONS);
                break;
            case '4':
                return get_string('status_withdrawn', self::LANG_EXTENSIONS);
                break;
            case '5':
                return get_string('status_revoked', self::LANG_EXTENSIONS);
                break;
            case '6':
                return get_string('status_moreinfo', self::LANG_EXTENSIONS);
                break;
            case '7':
                return get_string('status_deleted', self::LANG_EXTENSIONS);
                break;
        }

    }

    public static function get_all_extension_status() {
        $status = array();
        $status[self::STATUS_NONE]      = get_string('status_none',      self::LANG_EXTENSIONS);
        $status[self::STATUS_PENDING]   = get_string('status_pending',   self::LANG_EXTENSIONS);
        $status[self::STATUS_APPROVED]  = get_string('status_approved',  self::LANG_EXTENSIONS);
        $status[self::STATUS_DENIED]    = get_string('status_denied',    self::LANG_EXTENSIONS);
        $status[self::STATUS_WITHDRAWN] = get_string('status_withdrawn', self::LANG_EXTENSIONS);
        $status[self::STATUS_REVOKED]   = get_string('status_revoked',   self::LANG_EXTENSIONS);
        $status[self::STATUS_MOREINFO]  = get_string('status_moreinfo',  self::LANG_EXTENSIONS);

        return $status;
    }

    public static function get_extension_enable_items() {
        return array(
                self::EXT_NO_SEL   => '&nbsp;',
                self::EXT_DISABLED => get_string('no'),
                self::EXT_ENABLED  => get_string('yes')
        );
    }

    public static function get_date_options() {
        return array(
                'startyear' => date('Y') - 1,
                'stopyear'  => date('Y') + 2,
                'step'      => 5,
                'optional'  => false
        );
    }

    public static function get_date_format() {
        return self::DATE_FORMAT;
    }

    public static function get_cutoff_options() {
        // TODO: Translate these strings.
        return array(
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

    public static function get_extension_enable_id_by_cmid($cm_id = null) {

        global $DB;

        if(is_null($cm_id)) {
            return false;
        }

        return $DB->get_field('extensions_enabled', 'id', array('cm_id' => $cm_id));
    }

    public static function get_activity_mod_by_cmid($cm_id = null) {
        return Extensions::get_activity_detail_by_cmid($cm_id)->modname;
    }

    public static function get_activity_name($cm_id = null) {
        return Extensions::get_activity_detail_by_cmid($cm_id)->name;
    }

    public static function get_extension_by_id($id = null) {

        if(is_null($id)) {
            throw new coding_exception('Extension ID Cannot be null. This must be fixed by a developer');
        }

        $detail = new Extension();
        $detail->id = $id;

        $ext = new Extensions();
        return $ext->get_extension($detail);
    }

    public static function get_extension_status_by_cmid($cm_id = null) {
        global $DB;

        $cm_id = clean_param($cm_id, PARAM_INT);

        return $DB->get_field('extensions_enabled', 'status', array('cm_id' => $cm_id));
    }

    public static function get_activity_extension_detail($cm_id = null) {
        global $DB;

        $cm_id = clean_param($cm_id, PARAM_INT);

        return $DB->get_record('extensions_enabled', array('cm_id' => $cm_id));
    }

    public static function get_extension_cutoff_by_cmid($cm_id = null) {

        global $DB;

        $cm_id = clean_param($cm_id, PARAM_INT);

        if($extension = $DB->get_record('extensions_enabled', array('cm_id' => $cm_id), 'request_cutoff')) {
            return $extension->request_cutoff;
        }

        return false;
    }

    public static function get_extension_by_cmid($cm_id = null) {

    }

    public static function get_activity_detail_by_cmid($cm_id = null) {

        if(is_null($cm_id)) {
            return false;
        }

        $modinfo = get_fast_modinfo(Extensions::get_courseid_for_cmid($cm_id)->course);
        return $modinfo->get_cm($cm_id);
    }

    public static function get_activity_open_date($cm_id = null) {
        global $DB;

        if(is_null($cm_id)) {
            return false;
        }

        $activity_detail = Extensions::get_activity_detail_by_cmid($cm_id);

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

    }

    public static function get_activity_due_date($cm_id = null) {
        global $DB;

        if(is_null($cm_id)) {
            return false;
        }

        $activity_detail = Extensions::get_activity_detail_by_cmid($cm_id);

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

    public static function get_count_all_extensions_by_staffid($staff_id = null, $status = null, $course_id = null) {

        global $DB;

        if(is_null($staff_id)) {
            return 0;
        }

        $criteria['staff_id'] = $staff_id;

        if(!is_null($status)) {
            $criteria['status'] = $status;
        }

        //         if(!is_null($course_id)) {

        //             $ids = $DB->get_records('course_modules', array('course' => $course_id), '', 'id');

        //             foreach($ids as $id) {
        //                 $items[] = $id->id;
        //             }

        //             var_dump($items);

        //             $criteria['cm_id'] = ' IN ' . implode($items, ',');
        //         }

        // This will return ONLY the content of the COUNT column, as defined below
        return $DB->get_record('extensions', $criteria, 'COUNT(*) COUNT')->count;

    }

    public static function get_count_all_extensions_by_status($status = null) {

    }

    public static function get_count_all_extensions_by_filter($filters = null) {
        global $DB;

        if(isset($SESSION->ext_filters)) {
            $filters = $SESSION->ext_filters;
        } else {
            $filters = null;
        }

        $filter_string = '';

        // TODO: ALL THIS WILL NEED TO CHANGE, THESE COLUMNS ARE INCORRECT.

        if(!is_null($filters)) {
            if(isset($filters->activity_id) && $filters->activity_id != 0) {
                $filter_string .= " AND mex.unisa_asmnt_id = '{$filters->activity_id}' ";
            }

            if(isset($filters->status_id) && $filters->status_id != 0) {
                $filter_string .= " AND mex.ext_status_code = '{$filters->status_id}' ";
            }

            if(isset($filters->class_id) && $filters->class_id != 0) {
                $filter_string .= " AND MGG.GROUPID = '{$filters->class_id}' ";
            }

            if(isset($filters->user_id) && $filters->user_id != 0) {
                $filter_string .= " AND mex.ext_staffmember_id = '{$filters->user_id}' ";
            }
        }

        return $DB->get_records('extensions');
    }

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
        return $DB->get_record('extensions', $criteria, 'COUNT(*) COUNT')->count;
    }

    public static function is_extension_approver($ext = null, $user = null) {
        // TODO: IMPLEMENT THIS!
        return true;
    }

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

    public static function get_courseid_for_cmid($cm_id = null) {

        global $DB;

        if(is_null($cm_id)) {
            return false;
        }

        return $DB->get_record('course_modules', array('id' => $cm_id), 'course');
    }

    public static function get_courses_with_extensions_for_userid($user = null) {

        global $DB;

        if(is_null($user)) {
            return false;
        }

        $sql = "SELECT DISTINCT mcm.course id " .
                "FROM {extensions} me, {course_modules} mcm " .
                "WHERE me.staff_id = ? and me.cm_id = mcm.id";

        $params = array($user->id);

        $courses = $DB->get_records_sql($sql, $params);

        return $courses;
    }



    public static function get_pending_count_for_user($user_id = null, $course_id = null) {

        if(is_null($user_id)) {
            return false;
        }

        if(is_null($course_id)) {
            return false;
        }

        if(!is_null($course_id)) {
            return 23939283;
        }
    }


    public function get_extension(Extension $detail) {
        global $DB;

        return $DB->get_record('extensions', array('id' => $detail->id));
        // get the extension here.
    }

    public function send_response_email($ext_id = null) {

    }

    public function send_request_email($data, $status= null) {

    }

    public function add_event($form_data = null) {

    }

    public static function duplicate_requests($cm_id = null, $student_id = null, $existing_id) {
        global $DB;

        $sql = "SELECT id, cm_id, staff_id, status " .
                "FROM {extensions} " .
                "WHERE cm_id = ? AND student_id = ? AND id != ?";

        $vars = array($cm_id, $student_id, $existing_id);

        return $DB->get_records_sql($sql, $vars);
    }

    public static function get_pending_count_text($user = null, $course = null, $tags = true) {

        if(is_null($user)) {
            return 0;
        }

        if(get_config(Extensions::EXTENSIONS_MOD_NAME, 'show_pending_count')) {

            $count = 0;

            if(is_null($course) || $course->id == '0') {
                // find all courses the user is associated with.
                $courses = Extensions::get_courses_with_extensions_for_userid($user);

                // get the courses
                foreach($courses as $course) {
                    $count += Extensions::get_count_all_extensions_by_staffid($user->id, Extensions::STATUS_PENDING, $course->id);
                }

            } else {
                // just get the count for a single course.
                $count = Extensions::get_count_all_extensions_by_staffid($user->id, Extensions::STATUS_PENDING, $course->id);

            }

            if($count == '0') {
                return '';
            }

            if($tags) {
                $count_tag   = html_writer::tag('b',   $count);
                $content_tag = html_writer::tag('i',   $count_tag . ' ' . get_string('status_pending', Extensions::LANG_EXTENSIONS));
                $main_tag    = html_writer::tag('sup', $content_tag);
                return $main_tag;
            } else {
                return $count . ' ' . get_string('status_pending', Extensions::LANG_EXTENSIONS);
            }


        }

    }

    public static function save_quick_approve($form_data) {

        global $DB, $USER;

        foreach($form_data->extension_requests as $ext_id => $status) {

            // Update main record.
            $item = new stdClass;
            $item->id = $ext_id;
            $item->status = Extensions::STATUS_APPROVED;

            if($DB->update_record('extensions', $item)) {

                // Add item to history table
                $hist = new stdClass;
                $hist->extension_id = $ext_id;
                $hist->status       = Extensions::STATUS_APPROVED;
                $hist->user_id      = $USER->id;
                $hist->change_date  = date("U");

                if(!$DB->insert_record('extensions_history', $hist)) {
                    return false;
                }

                $ext = Extensions::get_extension_by_id($item->id);

                // add item to the calendar.
                $cal              = new object;
                $cal->name        = '';
                $cal->description = '';
                $cal->userid      = '';
                $cal->modulename  = '';
                $cal->instance    = $ext->cm_id;
                $cal->eventtype   = '';
                $cal->timestart   = $ext->date;

                calendar_event::create($properties);

            } else {
                return false;
            }

        }

        return true;

    }

    public static function get_extension_documents($ext_id = null) {

        return false;
    }

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

    public static function build_extension_history_table($ext_id) {
        global $DB;

        $items = $DB->get_records('extensions_history', array('extension_id' => $ext_id), 'change_date desc');

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

            $noChangeText = html_writer::tag('i', get_string('no_change', Extensions::LANG_EXTENSIONS));

            // Populate the cells with data
            if($item->date == 0) {
                $extensionDate->text = $noChangeText;
            } else {
                $extensionDate->text = userdate($item->date);
            }

            if($item->status == '') {
                $statusText->text    = $noChangeText;
            } else {
                $statusText->text    = Extensions::get_status_string($item->status);
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

    public static function build_extensions_table($filters = null) {

        global $DB, $OUTPUT;

        $table = new html_table();

        $table->width = "100%";

        $table->head  = array (
                '&nbsp;', // this column left intentionally blank.
                get_string("extstudentname",    self::LANG_EXTENSIONS),
                get_string("extusername",       self::LANG_EXTENSIONS),
                get_string("extassessmentname", self::LANG_EXTENSIONS),
                get_string("extduedate",        self::LANG_EXTENSIONS),
                get_string("extensiondate",     self::LANG_EXTENSIONS),
                get_string("extrequestdate",    self::LANG_EXTENSIONS),
                get_string("extstatus",         self::LANG_EXTENSIONS),
                get_string("extclass",          self::LANG_EXTENSIONS),
                get_string("extsentto",         self::LANG_EXTENSIONS),
                get_string("extapprove",        self::LANG_EXTENSIONS),
        );

        if($extensions = Extensions::get_count_all_extensions_by_filter($filters)) {

            foreach($extensions as $extension) {

                // Get the details of the staff member and the student for use in this table row.
                $studentDetail = $DB->get_record('user', array('id' => $extension->student_id), '*', MUST_EXIST);
                $staffDetail   = $DB->get_record('user', array('id' => $extension->staff_id),   '*', MUST_EXIST);

                // Activity detail
                $activity = Extensions::get_activity_detail_by_cmid($extension->cm_id);

                // Define the links used in the table below here.
                $studentNameLink     = new moodle_url('/user/profile.php', array('id' => $studentDetail->id));
                $studentUserNameLink = new moodle_url('/user/profile.php', array('id' => $studentDetail->id));
                $activityLink        = new moodle_url("/mod/{$activity->modname}/view.php", array('id' => $extension->cm_id));
                $staffLink           = new moodle_url('/user/view.php', array('id' => $staffDetail->id));
                $extensionEditUrl    = new moodle_url(Extensions::EXTENSIONS_URL_PATH . '/', array('page' => 'request_edit', 'eid' => $extension->id));

                // Create the cell objects
                $pictureCell         = new html_table_cell();
                $studentNameCell     = new html_table_cell();
                $studentUserNameCell = new html_table_cell();
                $activityLinkCell    = new html_table_cell();
                $activityTimeDueCell = new html_table_cell();
                $requestedDateCell   = new html_table_cell();
                $createdDateCell     = new html_table_cell();
                $statusCell          = new html_table_cell();
                $blankCell           = new html_table_cell();
                $staffNameCell       = new html_table_cell();
                $checkboxCell        = new html_table_cell();

                $due_date = Extensions::get_activity_due_date($activity->id);
                $date_diff = html_writer::tag('i', Extensions::date_difference($due_date, $extension->date) . ' days', array('class' => 'days_extension'));

                // Add the text to each cell in the table.
                $pictureCell->text         = $OUTPUT->user_picture($studentDetail, array('size' => 50));
                $studentNameCell->text     = html_writer::link($studentNameLink, $studentDetail->firstname . " " . $studentDetail->lastname);
                $studentUserNameCell->text = html_writer::link($studentUserNameLink, $studentDetail->username);
                $activityLinkCell->text    = html_writer::link($activityLink, $activity->name);
                $activityTimeDueCell->text = userdate($due_date);
                $requestedDateCell->text   = userdate($extension->date) . ' ' . $date_diff;
                $createdDateCell->text     = userdate($extension->created);
                $statusCell->text          = html_writer::link($extensionEditUrl, Extensions::get_status_string($extension->status));
                $blankCell->text           = "&nbsp;";
                $staffNameCell->text       = html_writer::link($staffLink, $staffDetail->firstname . " " . $staffDetail->lastname);
                $checkboxCell->text        = "{element}";

                $thisRow = new html_table_row();
                $thisRow->cells = array(
                        $pictureCell,
                        $studentNameCell,
                        $studentUserNameCell,
                        $activityLinkCell,
                        $activityTimeDueCell,
                        $requestedDateCell,
                        $createdDateCell,
                        $statusCell,
                        $blankCell,
                        $staffNameCell,
                        $checkboxCell
                );

                $table->data[$extension->id] = $thisRow;
            }
        }

        return $table;

    }

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
                'Denied'
        );

        // Get the list of activities for this course. This will need to be
        // course context only.

        foreach($activities as $activity) {

            // Get any extension details from the database for this activity.
            $extensionDetail = Extensions::get_activity_extension_detail($activity->id);

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
            $editUrl      = new moodle_url(Extensions::EXTENSIONS_URL_PATH . '/', array('page' => 'activity_edit', 'cm_id' => $activity->id));

            // Populate the columns.
            $activityNameCell->text  = html_writer::link($activityLink, $activity->name);
            $activityExtensions->text = '##ext_enabled##'; // placeholder for the dropdown
            $activityCutoffDate->text = '##ext_cutoff##';  // placeholder for the dropdown

            // Set the alignment of the following fields.
            $class = array('class' => 'mdl-align');

            $extensionsCount->attributes = $class;
            $pendingCount->attributes    = $class;
            $approvedCount->attributes   = $class;
            $deniedCount->attributes     = $class;

            if($activity->ext_status) { // if the activity has extensions enabled.

                $extensionsCount->text = Extensions::get_extensions_count($activity->id);
                $pendingCount->text    = Extensions::get_extensions_count($activity->id, self::STATUS_PENDING);
                $approvedCount->text   = Extensions::get_extensions_count($activity->id, self::STATUS_APPROVED);
                $deniedCount->text     = Extensions::get_extensions_count($activity->id, self::STATUS_DENIED);

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

}

