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
 * This file contains the form used when staff are modifying an existing extension
 * request, such as to approve or deny.
 *
 * @package   deadline_extensions
 * @copyright 2013 University of South Australia {@link http://www.unisa.edu.au}
 * @author    James McLean <james.mclean@unisa.edu.au>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('form_base.php');

class form_staff_request_edit extends form_base {

    protected $page_name = "Edit Individual Request";
    private   $read_only = false;

    private $filters = null;

    public function __construct() {
        parent::__construct();

        $this->page_name = get_string('ext_request_edit', extensions_plugin::EXTENSIONS_LANG);
    }

    public function definition() {
        parent::definition();

        global $CFG, $COURSE, $course, $USER, $DB;

        $mform =& $this->_form;

        $mform->addElement('header', 'general', get_string('ext_request_detail',  extensions_plugin::EXTENSIONS_LANG));

        $mform->addElement('static', 'other_req', null, null);
        $mform->addElement('static', 'other_req_links', null, null);

        $mform->addElement('static', 'assignment_name',    get_string("extselectassignment", extensions_plugin::EXTENSIONS_LANG));
        $mform->addElement('static', 'ext_student_static', get_string('ext_student_name',  extensions_plugin::EXTENSIONS_LANG));

        $mform->addElement('static', 'ext_reason_static', get_string('extreasonfor',  extensions_plugin::EXTENSIONS_LANG));

        $mform->addElement('static', 'supdoc1', get_string('extsupporting', extensions_plugin::EXTENSIONS_LANG), NULL);
        $mform->addElement('static', 'supdoc2', NULL, NULL);
        $mform->addElement('static', 'supdoc3', NULL, NULL);

        $mform->addElement('static', 'asmt_due_static',    get_string('extasmntdue',    extensions_plugin::EXTENSIONS_LANG), null);
        $mform->addElement('static', 'ext_due_static',     get_string('extsubmission',  extensions_plugin::EXTENSIONS_LANG), null);
        $mform->addElement('static', 'ext_date_requested', get_string('extrequestdate', extensions_plugin::EXTENSIONS_LANG), null);

        // ----------------------------------------------------------------

        $mform->addElement('header','general', get_string('extapproval', extensions_plugin::EXTENSIONS_LANG));

        $ext_status_code = $mform->addElement('select', 'ext_status_code', get_string('extstatus', extensions_plugin::EXTENSIONS_LANG), extensions_plugin::get_all_extension_status());
        $mform->addRule('ext_status_code', 'Please Select', 'required', null, 'client'); // TODO: Put this string in the LANG file.

        $ext_granted_date = $mform->addElement('date_time_selector', 'ext_granted_date', get_string('extapproveddate',extensions_plugin::EXTENSIONS_LANG), extensions_plugin::get_date_options());

        $response_text = $mform->addElement('htmleditor', 'response_text', get_string('extresponse',extensions_plugin::EXTENSIONS_LANG), array('cols' => 60, 'rows' => 6));
        $mform->setType('response_text', PARAM_RAW); // to be cleaned before display

        $this->add_action_buttons(TRUE);
    }

    public function definition_after_data() {
        parent::definition_after_data();

        global $CFG, $COURSE, $course, $USER, $DB;

        // load a copy of the instanciated form object from this object.
        $mform =& $this->_form;

        if($this->get_extension_id()) {
            $extension = extensions_plugin::get_extension_by_id($this->get_extension_id());
        } else {
            // Staff adding on a student behalf.
            $deadline   = new deadlines_plugin();

            $current_deadline = $deadline->get_date_deadline($this->get_cmid());

            if($extension_default = get_config('deadline_extensions','default_ext_length')) {
                $extension_deadline = $current_deadline + ($extension_default * 3600);
            } else {
                $extension_deadline = $current_deadline + 3600;
            }

            $extension = new stdClass;
            $extension->id            = null;
            $extension->cm_id         = $this->get_cmid();
            $extension->student_id    = $this->get_student_id();
            $extension->status        = extensions_plugin::STATUS_APPROVED;
            $extension->request_text  = null;
            $extension->date          = $extension_deadline;
            $extension->created       = date('U');
            $extension->staff_created = true;
        }

        // Show duplicate extension warnings here.
        if(get_config(extensions_plugin::EXTENSIONS_MOD_NAME, 'show_duplicate_warn') == 1) {

            if($dups = extensions_plugin::duplicate_requests($extension->cm_id, $extension->student_id, $extension->id)) {
                // If there is any duplicate requests, populate those fields.

                $links = null;

                $mform->setDefault('other_req', get_string('ext_other_req_exists',  extensions_plugin::EXTENSIONS_LANG));

                foreach($dups as $dup) {

                    $string = get_string('duplicate_request', extensions_plugin::EXTENSIONS_LANG);
                    $params = array('page' => 'request_edit', 'eid' => $dup->id);
                    $url = new moodle_url(extensions_plugin::EXTENSIONS_URL_PATH . '/', $params);

                    $links .= html_writer::link($url, $string) . ' ' .
                              date(extensions_plugin::get_date_format(), $dup->date) . ' ' .
                              html_writer::tag('i', extensions_plugin::get_status_string($dup->status)) . ' ' .
                              html_writer::empty_tag('br');

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
        $name = extensions_plugin::get_activity_name($extension->cm_id);
        $mod  = extensions_plugin::get_activity_mod_by_cmid($extension->cm_id);
        $url  = new moodle_url('/mod/' . $mod . '/view.php', array('id' => $extension->cm_id));
        $link = html_writer::link($url, $name);

        $mform->setDefault('assignment_name', $link);

        // Student Name
        $user = $DB->get_record('user', array('id' => $extension->student_id));
        $params = array('id' => $user->id);

        $user_url  = new moodle_url('/user/view.php');
        $user_link = html_writer::link($user_url, "{$user->firstname} {$user->lastname} - {$user->idnumber}");
        $mform->setDefault('ext_student_static', $user_link);

        $due_date = extensions_plugin::get_activity_due_date($extension->cm_id);


        // TODO: IMPLEMENT THIS!
        $docs = extensions_plugin::get_extension_documents();

        if(isset($docs) && $docs != FALSE) {
            $i = 1;
            foreach($docs as $doc) {

                $field = 'supdoc' . $i;
                $path = "/user/u_file.php?id={$ext->user_id}&amp;file={$doc->doc_url}";

                $mform->setDefault($field, "<a href=\"$path\">" . basename($doc->doc_url) . "</a>");
                $i++;
            }

        } else {
            $mform->setDefault('supdoc1', get_string('ext_no_docs', extensions_plugin::EXTENSIONS_LANG));
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
        if(extensions_plugin::is_extension_approver($extension->id, $USER->id)) {
            $this->set_readonly(false);
            $approver = true;
        } else {
            $this->set_readonly(true);
            $approver = false;
        }

        // The request can only modified when it's pending.
        if( ($extension->status == extensions_plugin::STATUS_PENDING || $extension->status == extensions_plugin::STATUS_APPROVED) && $approver == TRUE) {
            $this->set_readonly(false);
        } else {
            $this->set_readonly(true);
        }


        // Admins can always modify.
        if(is_siteadmin($USER->id)) {
            $this->set_readonly(false);
        }

        // ----------------------------------------------------------------

        if($extension->status == extensions_plugin::STATUS_APPROVED || $extension->status == extensions_plugin::STATUS_DENIED) {
            $readonly = true;
        }

        $mform->setDefault('ext_reason_static', $extension->request_text);

        $mform->setDefault('ext_status_code',    $extension->status);
        $mform->setDefault('ext_granted_date',   $extension->date);

        $mform->setDefault('asmt_due_static',    date(extensions_plugin::get_date_format(), $due_date));

        $ext_diff = html_writer::tag('i', extensions_plugin::date_difference($due_date, $extension->date) . ' days', array('class' => 'days_extension'));
        $mform->setDefault('ext_due_static',     date(extensions_plugin::get_date_format(), $extension->date) . ' ' . $ext_diff);

        $req_diff_days = extensions_plugin::date_difference($due_date, $extension->created);

        if($req_diff_days > 0) {
            // Request made AFTER due date.
            $req_diff = html_writer::tag('i', $req_diff_days . get_string('days_after', extensions_plugin::EXTENSIONS_LANG));
        } else {
            // Request made BEFORE due date.
            $req_diff_days = $req_diff_days * -1; // Convert to positive number.
            $req_diff = html_writer::tag('i', $req_diff_days . get_string('days_prior', extensions_plugin::EXTENSIONS_LANG));
        }

        $mform->setDefault('ext_date_requested', date(extensions_plugin::get_date_format(), $extension->created) . ' ' . $req_diff);

        if(isset($extension->response_text) && $extension->response_text != false) {
            $mform->setDefault('response_text', $extension->response_text);
        }

        if($this->get_readonly()) {
            $mform->freeze('ext_status_code');
            $mform->freeze('response_text');
            $mform->freeze('ext_granted_date');

            if($approver === false) {
                $mform->addElement('static', 'static', null, get_string('ext_not_approver', extensions_plugin::EXTENSIONS_LANG));
            }

            // Remove the button group.
            if($mform->elementExists('buttonar')) {
                $buttonGroup = $mform->removeElement('buttonar');
            }


        } else {

            //if($this->get_saved()) {
                $mform->addElement('static', 'static', null, get_string('ext_saved', extensions_plugin::EXTENSIONS_LANG));
            //}

        }

        // insert the history here.
        if($history = extensions_plugin::build_extension_history_table($extension->id)) {
            $mform->addElement('static', 'extension_history', get_string('extension_history', extensions_plugin::EXTENSIONS_LANG), html_writer::table($history, TRUE));
        }

    }

    public function validation($data, $files) {

        global $USER;

        $errors = array();

        $extension = extensions_plugin::get_extension_by_id($this->get_extension_id());
        $due_date  = extensions_plugin::get_activity_due_date($extension->cm_id);

        // if the request is DENIED then the message is compulsory
        if($data['ext_status_code'] == extensions_plugin::STATUS_DENIED && strlen($data['response_text']) == '0' ) {
            $errors['response_text'] = get_string('ext_message_required', extensions_plugin::EXTENSIONS_LANG);
        }

        if($data['ext_granted_date'] < $due_date) {
            $errors['ext_granted_date'] = get_string('ext_granted_before_due', extensions_plugin::EXTENSIONS_LANG);
        }

        if(!extensions_plugin::is_extension_approver($this->get_extension_id())) {
            $errors['ext_status_code'] = get_string('ext_not_approver', extensions_plugin::EXTENSIONS_LANG);
        }

        return $errors;
    }

    public function save_hook($form_data = null) {

        global $USER, $DB;

        if(!is_null($form_data)) {
            if(isset($form_data->submitbutton)) {
                // Someone hit the save changes button. That's all they
                // can really do anyway

                if(!extensions_plugin::is_extension_approver($this->get_extension_id())) {
                    error(get_string('ext_not_approver', extensions_plugin::EXTENSIONS_LANG));
                    exit;
                }

                $ext_data                = new stdClass;
                $ext_data->id            = $form_data->eid;
                $ext_data->status        = $form_data->ext_status_code;
                $ext_data->response_text = $form_data->response_text;
                $ext_data->date          = $form_data->ext_granted_date;

                if($DB->update_record('deadline_extensions', $ext_data)) {

                    // Add to the extensions history table.
                    extensions_plugin::add_history($form_data);

                    // Send a message to the user to notify of the update.
                    extensions_plugin::notify_user($form_data);

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