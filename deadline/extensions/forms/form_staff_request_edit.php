<?php

require_once('form_base.php');

class form_staff_request_edit extends form_base {

    protected $page_name = "Edit Individual Request";
    private   $read_only = false;

    private $filters = null;

    public function __construct() {
        parent::__construct();

        $this->page_name = get_string('ext_request_edit', Extensions::LANG_EXTENSIONS);
    }

    public function definition() {
        parent::definition();

        global $CFG, $COURSE, $course, $USER, $DB;

        $mform =& $this->_form;

        $mform->addElement('header', 'general', get_string('ext_request_detail',  Extensions::LANG_EXTENSIONS));

        $mform->addElement('static', 'other_req', null, null);
        $mform->addElement('static', 'other_req_links', null, null);

        $mform->addElement('static', 'assignment_name',    get_string("extselectassignment", Extensions::LANG_EXTENSIONS));
        $mform->addElement('static', 'ext_student_static', get_string('ext_student_name',  Extensions::LANG_EXTENSIONS));

        $mform->addElement('static', 'ext_reason_static', get_string('extreasonfor',  Extensions::LANG_EXTENSIONS));

        $mform->addElement('static', 'supdoc1', get_string('extsupporting', Extensions::LANG_EXTENSIONS), NULL);
        $mform->addElement('static', 'supdoc2', NULL, NULL);
        $mform->addElement('static', 'supdoc3', NULL, NULL);

        $mform->addElement('static', 'asmt_due_static',    get_string('extasmntdue',    Extensions::LANG_EXTENSIONS), null);
        $mform->addElement('static', 'ext_due_static',     get_string('extsubmission',  Extensions::LANG_EXTENSIONS), null);
        $mform->addElement('static', 'ext_date_requested', get_string('extrequestdate', Extensions::LANG_EXTENSIONS), null);

        // ----------------------------------------------------------------

        $mform->addElement('header','general', get_string('extapproval', Extensions::LANG_EXTENSIONS));


        $ext_status_code = $mform->addElement('select', 'ext_status_code', get_string('extstatus', Extensions::LANG_EXTENSIONS), Extensions::get_all_extension_status());
        $mform->addRule('ext_status_code', 'Please Select', 'required', null, 'client'); // TODO: Put this string in the LANG file.

        $ext_granted_date = $mform->addElement('date_time_selector', 'ext_granted_date', get_string('extapproveddate',Extensions::LANG_EXTENSIONS), Extensions::get_date_options());

        $response_text = $mform->addElement('htmleditor', 'response_text', get_string('extresponse',Extensions::LANG_EXTENSIONS), array('cols' => 60, 'rows' => 6));
        $mform->setType('response_text', PARAM_RAW); // to be cleaned before display

        $this->add_action_buttons(TRUE);
    }

    public function definition_after_data() {
        parent::definition_after_data();

        global $CFG, $COURSE, $course, $USER, $DB;

        // load a copy of the instanciated form object from this object.
        $mform =& $this->_form;

        $extension = Extensions::get_extension_by_id($this->get_extension_id());

        // Show duplicate extension warnings here.
        if(get_config(Extensions::EXTENSIONS_MOD_NAME, 'show_duplicate_warn')) {

            $links = null;

            if($dups = Extensions::duplicate_requests($extension->cm_id, $extension->student_id, $extension->id)) {
                // If there is any duplicate requests, populate those fields.

                $mform->setDefault('other_req', get_string('ext_other_req_exists',  Extensions::LANG_EXTENSIONS));

                foreach($dups as $dup) {

                    $string = get_string('duplicate_request', Extensions::LANG_EXTENSIONS);
                    $url = new moodle_url(Extensions::EXTENSIONS_URL_PATH . '/', array('page' => 'request_edit', 'eid' => $dup->id));

                    $links .= html_writer::link($url, $string) . ' ' . html_writer::tag('i', Extensions::get_status_string($dup->status)) . ' ' . html_writer::tag('br', null);

                }

                $mform->setDefault('other_req_links', $links);
            } else {
                // But if there isn't, we'll remove those fields as they leave
                // a big space if there isnt anything populated.
                if($mform->elementExists('other_req')) {
                    $mform->removeElement('other_req');
                }
                if($mform->elementExists('other_req_links')) {
                    $mform->removeElement('other_req_links');
                }
            }

        }

        //---------------

        // Assignment Name
        $name = Extensions::get_activity_name($extension->cm_id);
        $mod  = Extensions::get_activity_mod_by_cmid($extension->cm_id);
        $url  = new moodle_url('/mod/' . $mod . '/view.php', array('id' => $extension->cm_id));
        $link = html_writer::link($url, $name);

        $mform->setDefault('assignment_name', $link);

        // Student Name
//         $user = get_user_info_from_db('id', $ext->user_id);
        $user = $DB->get_record('user', array('id' => $extension->student_id));
        $user_link = "<a href=\"/user/view.php?id={$user->id}\" target=\"_blank\">{$user->firstname} {$user->lastname} - {$user->idnumber}</a>";
        $mform->setDefault('ext_student_static', $user_link);

        $due_date = Extensions::get_activity_due_date($extension->cm_id);


        // TODO: IMPLEMENT THIS!
        $docs = Extensions::get_extension_documents();

        if(isset($docs) && $docs != FALSE) {
            $i = 1;
            foreach($docs as $doc) {

                $field = 'supdoc' . $i;
                $path = "/user/u_file.php?id={$ext->user_id}&amp;file={$doc->doc_url}";

                $mform->setDefault($field, "<a href=\"$path\">" . basename($doc->doc_url) . "</a>");
                $i++;
            }

        } else {
            $mform->setDefault('supdoc1', get_string('ext_no_docs', Extensions::LANG_EXTENSIONS));
            $mform->removeElement('supdoc2');
            $mform->removeElement('supdoc3');
        }

        $mform->addElement('hidden', 'eid', $extension->id);
        $mform->setType('extid', PARAM_INT);

        $mform->addElement('hidden', 'action', 'save');
        $mform->setType('action', PARAM_ALPHA);

        $mform->addElement('hidden', 'page', 'request_edit');
        $mform->setType('page', PARAM_ALPHAEXT);

        // Make sure this user can actaully be approving this extension request.
        if(Extensions::is_extension_approver($extension->id, $USER->id)) {
            $this->set_readonly(false);
            $approver = true;
        } else {
            $this->set_readonly(true);
            $approver = false;
        }

        // The request can only modified when it's pending.
        if( ($extension->status == Extensions::STATUS_PENDING || $extension->status == Extensions::STATUS_APPROVED) && $approver == TRUE) {
            $this->set_readonly(false);
        } else {
            $this->set_readonly(true);
        }


        // Admins can always modify.
        if(is_siteadmin($USER->id)) {
            $this->set_readonly(false);
        }

        // ----------------------------------------------------------------

        if($extension->status == Extensions::STATUS_APPROVED || $extension->status == Extensions::STATUS_DENIED) {
            $readonly = true;
        }

        $mform->setDefault('ext_reason_static', $extension->request_text);

        $mform->setDefault('ext_status_code',    $extension->status);
        $mform->setDefault('ext_granted_date',   $extension->date);

        $mform->setDefault('asmt_due_static',    date(Extensions::get_date_format(), $due_date));

        $ext_diff = html_writer::tag('i', Extensions::date_difference($due_date, $extension->date) . ' days', array('class' => 'days_extension'));
        $mform->setDefault('ext_due_static',     date(Extensions::get_date_format(), $extension->date) . ' ' . $ext_diff);

        $req_diff_days = Extensions::date_difference($due_date, $extension->created);

        if($req_diff_days > 0) {
            // Request made AFTER due date.
            $req_diff = html_writer::tag('i', $req_diff_days . get_string('days_after', Extensions::LANG_EXTENSIONS));
        } else {
            // Request made BEFORE due date.
            $req_diff_days = $req_diff_days * -1; // Convert to positive number.
            $req_diff = html_writer::tag('i', $req_diff_days . get_string('days_prior', Extensions::LANG_EXTENSIONS));
        }

        $mform->setDefault('ext_date_requested', date(Extensions::get_date_format(), $extension->created) . ' ' . $req_diff);

        if(isset($extension->response_text) && $extension->response_text != false) {
            $mform->setDefault('response_text', $extension->response_text);
        }

        if($this->get_readonly()) {
            $mform->freeze('ext_status_code');
            $mform->freeze('response_text');
            $mform->freeze('ext_granted_date');

            if($approver === false) {
                $mform->addElement('static', 'static', null, get_string('ext_not_approver', Extensions::LANG_EXTENSIONS));
            }

            // Remove the button group.
            if($mform->elementExists('buttonar')) {
                $buttonGroup = $mform->removeElement('buttonar');
            }


        } else {

            //if($this->get_saved()) {
                $mform->addElement('static', 'static', null, get_string('ext_saved', Extensions::LANG_EXTENSIONS));
            //}

        }

        // insert the history here.
        if($history = Extensions::build_extension_history_table($extension->id)) {
            $mform->addElement('static', 'static', get_string('extension_history', Extensions::LANG_EXTENSIONS), html_writer::table($history, TRUE));
        }

    }

    public function validation($data, $files) {

        global $USER;

        $errors = array();

        $extension = Extensions::get_extension_by_id($this->get_extension_id());
        $due_date  = Extensions::get_activity_due_date($extension->cm_id);

        // if the request is DENIED then the message is compulsory
        if($data['ext_status_code'] == Extensions::STATUS_DENIED && strlen($data['response_text']) == '0' ) {
            $errors['response_text'] = get_string('ext_message_required', Extensions::LANG_EXTENSIONS);
        }

        if($data['ext_granted_date'] < $due_date) {
            $errors['ext_granted_date'] = get_string('ext_granted_before_due', Extensions::LANG_EXTENSIONS);
        }

        if(!Extensions::is_extension_approver($this->get_extension_id())) {
            $errors['ext_status_code'] = get_string('ext_not_approver', Extensions::LANG_EXTENSIONS);
        }

        return $errors;
    }

    public function save_hook($form_data = null) {

        // THIS WHOLE SAVE_HOOK METHOD WILL NEED TO BE EXTENSIVELY MODIFIED.

        global $USER, $DB;

        if(!is_null($form_data)) {
            if(isset($form_data->submitbutton)) {
                // Someone hit the save changes button. That's all they
                // can really do anyway

                $staff_detail   = $DB->get_record('user', array('id' => $ext_data->staff_id));
                $student_detail = $DB->get_record('user', array('id' => $ext_data->student_id));

                if(!Extensions::is_extension_approver($this->get_extension_id())) {
                    error(get_string('ext_not_approver', Extensions::LANG_EXTENSIONS));
                    exit;
                }

                $ext_data                = new stdClass;
                $ext_data->id            = $form_data->eid;
                $ext_data->status        = $form_data->status;
                $ext_data->response_text = $form_data->response_text;
                $ext_data->date          = $form_data->ext_granted_date;

                if($DB->update_record('extensions', $ext_data)) {

                    // ADD TO HISTORY.
                    // Add item to history table
                    $hist                = new stdClass;
                    $hist->extension_id  = $ext_id;
                    $hist->status        = $form_data->status;
                    $hist->user_id       = $USER->id;
                    $hist->response_text = $form_data->response_text;
                    $hist->change_date   = date("U");

                    if(!$DB->insert_record('extensions_history', $hist)) {
                        return false;
                    }

                    // SEND USER NOTIFICATION OF UPDATE
                    // Moodle2 ways:
                    // http://docs.moodle.org/dev/Messaging_2.0
                    // http://docs.moodle.org/dev/Events

                    $email_content  = get_string('ext_email_response_header', Extensions::LANG_EXTENSIONS, $student_detail->firstname);
                    $email_content .= ""; // TODO: insert the link to the student view page here

                    $message_data                    = new stdClass;
                    $message_data->component         = Extensions::EXTENSIONS_MOD_NAME;
                    $message_data->name              = 'posts';
                    $message_data->userfrom          = $staff_detail;
                    $message_data->userto            = $student_detail;
                    $message_data->subject           = get_string('ext_email_response_subject', Extensions::LANG_EXTENSIONS);
                    $message_data->fullmessage       = $email_content;
                    $message_data->fullmessageformat = FORMAT_HTML;
                    $message_data->smallmessage      = get_string('ext_email_response_subject', Extensions::LANG_EXTENSIONS);

                    events_trigger('message_send', $message_data);

                    // If the status has just been set to Revoked or Withdrawn,
                    // we don't want to add a calendar item etc.
                    if($form_data->status == Extensions::STATUS_REVOKED ||
                       $form_data->status == Extensions::STATUS_WITHDRAWN) {

                        // Remove any calendar events this user may have for this extension.



                    } else if($form_data->status == Extensions::STATUS_APPROVED) {

                        // Check to see if there is already a calendar event.
                        // Add a calendar event.

                        // ADD EVENT TO USER CALENDAR.



                    }

                    /*

                    // HAS NOT YET BEEN RE-WRITTEN
                    $this_data = get_record('unisa_asmnt_ext_stu', 'id', $form_data->extid);

                    $this->send_response_email($form_data->extid);

                    if($form_data->ext_status_code == AG_EXT_APPROVED) {

                        if($this->add_event($form_data, $form_data->extid)) {
                            return true;
                        } else {
                            return false;
                        }

                    } else if($form_data->ext_status_code == AG_EXT_REVOKED || $form_data->ext_status_code == AG_EXT_WITHDRAWN) {
                        // If this status has been set to revoked, remove the calendar event

                        // get the cmid from the extension ID.
                        $sql = "SELECT MCM.INSTANCE " .
                               "FROM M_COURSE_MODULES MCM, M_UNISA_ASMNT MUA " .
                               "WHERE MUA.MCOU_MOD_ID = MCM.ID " .
                               "AND MUA.ID = {$this_data->unisa_asmnt_id}";

                        if($instance = get_record_sql($sql)) {
                            if(record_exists('event', 'userid', $this_data->user_id, 'instance', $instance->instance, 'eventtype', 'due')) {
                                if(delete_records('event', 'userid', $this_data->user_id, 'instance', $instance->instance, 'eventtype', 'due')) {
                                    return true;
                                }
                            }
                        }
                    }
                    // HAS NOT YET BEEN RE-WRITTEN

                    */

                } else {
                    return false;
                }

                return true;
            }

        }

    } // end save_hook

    public function set_readonly($read_only) {
        if(is_bool($read_only)) {
            $this->read_only = $read_only;
        }
    }

    public function get_readonly() {
        return $this->read_only;
    }

}