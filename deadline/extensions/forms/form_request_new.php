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
 * This file contains the form used when requesting a new extension.
 *
 * @package   deadline_extensions
 * @copyright 2013 University of South Australia {@link http://www.unisa.edu.au}
 * @author    James McLean <james.mclean@unisa.edu.au>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('form_base.php');

class form_request_new extends form_base {

    protected $page_name       = null;
    protected $activity_detail = null;


    public function __construct() {
        parent::__construct();

        //        $this->page_name = get_string('ext_indiv_req', extensions_plugin::EXTENSIONS_LANG);

    }

    public function post_form_load() {
        parent::post_form_load();

        $this->page_name = get_string('ext_indiv_req', extensions_plugin::EXTENSIONS_LANG);

    }

    public function definition() {
        parent::definition();

        // TODO: Make this work the Moodle2 way.
        //$this->set_upload_manager(new upload_manager('', false, false, null, false, (1024000 * 5), true, true, false));

        // load a copy of the instanciated form object from this object.
        $mform =& $this->_form;

        $mform->addElement('header', 'general', get_string('new_extension_request', extensions_plugin::EXTENSIONS_LANG));

        $mform->addElement('static','ext_error');

        $mform->addElement('static','assignment_name', get_string('extselectassignment', extensions_plugin::EXTENSIONS_LANG));

        $mform->addElement('static', 'group_detail', get_string('group_submission', extensions_plugin::EXTENSIONS_LANG), '');
        $mform->addElement('hidden', 'group_list', '0');
        $mform->addElement('hidden', 'group_submission', '0');

        $reason = $mform->addElement('htmleditor', 'reason', get_string('extreason', extensions_plugin::EXTENSIONS_LANG), array('cols' => 60, 'rows' => 6));
        $mform->addRule('reason', get_string('required'), 'required', null, 'client');
        $mform->addRule('reason', get_string('max_length_error', extensions_plugin::EXTENSIONS_LANG), 'maxlength', 4000, 'client');
        $mform->setType('reason', PARAM_TEXT);

//         $file_params = array('maxbytes' => $maxbytes, 'accepted_types' => '*');

//         $mform->addElement('filepicker', 'userfile', get_string('file'), null, $file_params);
        $file_params =  array('subdirs' => 0, 'maxfiles' => 4, 'accepted_types' => array('document') ); // make this dynamic.
        $mform->addElement('filemanager', 'attachments', get_string('extsupdoc',extensions_plugin::EXTENSIONS_LANG), null, $file_params);

        $options = array(
                extensions_plugin::EXTENSION_TYPE_NONE   => '&nbsp;',
                extensions_plugin::EXTENSION_TYPE_DATE => get_string('date_extension', extensions_plugin::EXTENSIONS_LANG),
                extensions_plugin::EXTENSION_TYPE_TIME => get_string('time_extension', extensions_plugin::EXTENSIONS_LANG)
        );

        $length = $mform->addElement('select', 'type', 'Date or Time Extension', $options);
        $mform->addRule('type', get_string('required'), 'required', null, 'client');

        $currdue = $mform->addElement('date_time_selector', 'currdue', get_string('extcurrduedate', extensions_plugin::EXTENSIONS_LANG), $this->date_options);

        $date = $mform->addElement('date_time_selector', 'date', get_string('extrequestdateacst', extensions_plugin::EXTENSIONS_LANG), $this->date_options);

        $mform->addElement('static', 'static_time_limit', 'Current time limit');

        $options = array(
                '-1'  => '&nbsp;',
                '60'  => 'One minute',
                '120' => 'Two minutes'
        );

        $length = $mform->addElement('select', 'time_ext', 'Time extension', $options);

        // -------------------------

        $mform->addElement('select', 'ext_staffmember_id', get_string('extsendto',extensions_plugin::EXTENSIONS_LANG));
        $mform->addRule('ext_staffmember_id', get_string("please_select", extensions_plugin::EXTENSIONS_LANG), 'required', null, 'client');

        $mform->addElement('header', 'staff_general', get_string('ext_staff_feedback',extensions_plugin::EXTENSIONS_LANG));

        $mform->addElement('static', 'status', get_string('extstatus', extensions_plugin::EXTENSIONS_LANG));

        $mform->addElement('date_time_selector', 'granted_ext_date', get_string('extapproveddate', extensions_plugin::EXTENSIONS_LANG), $this->date_options);
        $mform->freeze('granted_ext_date');

        $mform->addElement('static','response_message', get_string('ext_response_mesg', extensions_plugin::EXTENSIONS_LANG));


        $mform->addElement('hidden', 'cmid');
        $mform->setType('cmid', PARAM_INT);

        $mform->addElement('hidden', 'page', 'request_new');       // Need this so the destination is correct.
        $mform->setType('page', PARAM_ALPHAEXT);

        $mform->addElement('hidden', 'action',  'save');
        $mform->setType('action', PARAM_ALPHA);

        $mform->addElement('static','duplicate_message', '', '');

        // Create a button group for the cancel, withdraw and submit buttons.
        $buttonarray[] = &$mform->createElement('cancel');
        $buttonarray[] = &$mform->createElement('submit', 'withdrawbutton', get_string('extwithdraw', extensions_plugin::EXTENSIONS_LANG));
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton',   get_string('extresubmitreq', extensions_plugin::EXTENSIONS_LANG));

        if(isset($buttonarray)) {
            // Add an over-lay over the UI when the form is submitted to prevent
            // multiple submissions.
            $js = "<script type=\"text/javascript\">
            //<![CDATA[
            $('#mform2').submit(function(){

            $.blockUI.defaults.overlayCSS.backgroundColor = '#EDEDED';
            $.blockUI.defaults.overlayCSS.opacity = .4;
            $.blockUI.defaults.overlayCSS.border = 0;

            $('div.fcontainer').block({  message: null });
            });
            //]]>
            </script>";

            $buttonarray[] = &$mform->createElement('static','js', '&nbsp;', $js);
        }

        // withdrawbutton
        if($mform->elementExists('withdrawbutton')) {
            $mform->disabledIf('withdrawbutton', 'reason', 'eq', '');
        }

        // Only enable button if there is a reason present
        //$mform->disabledIf('submitbutton', 'reason', 'eq', '');
        // Only enable button if there is a staffmember selected
        $mform->disabledIf('submitbutton', 'ext_staffmember_id', 'eq', '0');

        $mform->addGroup($buttonarray, 'buttona', '', array(' '), false);

    }


    public function definition_after_data() {
        parent::definition_after_data();

        global $CFG, $COURSE, $USER, $course;

        // load a copy of the instanciated form object from this object.
        $mform =& $this->_form;

        $deadlines  = new deadlines_plugin();
        $extensions = new extensions_plugin();

        $deadline = $deadlines->get_deadlines_for_cmid($this->get_cmid());

        // if $assignment does not allow extensions, set read-only.
        if(!$extensions->extensions_enabled_cmid($this->get_cmid())) {
            $this->set_readonly(true);
            $mform->setDefault('ext_error', get_string('ext_not_allowed', extensions_plugin::EXTENSIONS_LANG));
        }

        if($deadline->date_open > date('U')) {
            $this->set_readonly(true);
            $mform->setDefault('ext_error', get_string('extnotopenyet', extensions_plugin::EXTENSIONS_LANG));
        }

        // See if the due date has passed
        if($deadline->date_deadline < date('U')) {
            $this->set_readonly(true);
            $mform->setDefault('ext_error', get_string('extduedatepassed', extensions_plugin::EXTENSIONS_LANG));
        }

        // This is a new request. We need to modify the submit buttons.
        $buttonGroup = $mform->getElement('buttona');
        $wb = $buttonGroup->getElement('withdrawbutton');

        foreach($buttonGroup->_elements as $key => $button) {

            // Remove withdraw button
            if($button->_type == 'submit' &&
                    $button->_attributes['name'] == 'withdrawbutton') {
                unset($buttonGroup->_elements[$key]);
            }

            // Set the button to the correct text
            if($button->_type == 'submit' &&
                    $button->_attributes['name'] == 'submitbutton') {
                $buttonGroup->_elements[$key]->setValue(get_string('extsubmitreq', extensions_plugin::EXTENSIONS_LANG));
            }

        }

        $users = $this->get_extension_approvers($this->get_cmid());

        $staff = $mform->getElement('ext_staffmember_id');
        $staff->load($users);

        $mform->setDefault('cmid', $this->get_cmid());

        $url_params    = array('id' => $this->get_cmid());
        $activity_url  = new moodle_url('/mod/' . $this->activity_detail['cm']->modname . '/view.php', $url_params);
        $activity_name = html_writer::link($activity_url, $this->activity_detail['cm']->name);

        $mform->setDefault('assignment_name', $activity_name);

        // group_detail
        if($grouping_id = $extensions->get_group_submission_for_cmid($this->get_cmid())) {

            // List the users groups.
            $groups = groups_get_all_groups($this->get_course()->id, $USER->id, $grouping_id, 'g.id, name');

            $group_list = '';
            $group_ids  = '';

            foreach($groups as $group) {
                $group_ids   = $group_ids . $group->id . ',';
                $group_list .= $group->name . ' ';
            }

            $group_text  = $group_list;
            $group_text .= ' ';
            $group_text .= html_writer::tag('i', get_string('group_submission_text', extensions_plugin::EXTENSIONS_LANG));

            $mform->setDefault('group_detail', $group_text);
            $mform->setDefault('group_list', $group_ids);
//             $mform->setDefault('hidden','group_detail', '1');

            if($mform->elementExists('group_submission')) {
                $mform->setDefault('group_submission', '1');
            }

        } else {
            if($mform->elementExists('group_detail')) {
                $mform->removeElement('group_detail');
            }

            if($mform->elementExists('group_submission')) {
                $mform->setDefault('group_submission', '0');
            }
        }

        if($mform->elementExists('currdue')) {
            $mform->setDefault('currdue', $deadline->date_deadline);
            $mform->freeze('currdue');
        }

        // Add the configured amount of time to the extension
        if($extension_default = get_config('deadline_extensions','default_ext_length')) {
            $extension_deadline = $deadline->date_deadline + ($extension_default * 3600);
        } else {
            $extension_deadline = $deadline->date_deadline + 3600;
        }

        if($mform->elementExists('date')) {
            $mform->setDefault('date', $extension_deadline);
        }

        // If this is NOT a quiz, we need to hide some fields.
        if(extensions_plugin::get_activity_type_by_cmid($this->get_cmid()) == 'quiz') {
            if($mform->elementExists('static_time_limit')) {

                $limit = $deadline->timelimit / 60;
                $limit = $limit . ' ' . get_string('minutes', extensions_plugin::EXTENSIONS_LANG);

                $mform->setDefault('static_time_limit', $limit);
                $mform->freeze('static_time_limit');
            }

            // Disable date if no type selection made
            $mform->disabledIf('date', 'type', 'eq', extensions_plugin::EXTENSION_TYPE_NONE);
            // Disable date if selection matches Time Extension
            $mform->disabledIf('date', 'type', 'eq', extensions_plugin::EXTENSION_TYPE_TIME);

            // Disable length if no type selection made
            $mform->disabledIf('time_ext', 'type', 'eq', extensions_plugin::EXTENSION_TYPE_NONE);
            // Disable length if selection matches Date Extension
            $mform->disabledIf('time_ext', 'type', 'eq', extensions_plugin::EXTENSION_TYPE_DATE);

        } else {

            // Remove the extension type option
            if($mform->elementExists('type')) {
                $mform->removeElement('type');
            }

            // Remove the static time limit option
            if($mform->elementExists('static_time_limit')) {
                $mform->removeElement('static_time_limit');
            }

            // Remove the time selection option
            if($mform->elementExists('time_ext')) {
                $mform->removeElement('time_ext');
            }

        }

        // check for duplicates.
        if($dups = extensions_plugin::duplicate_requests($this->get_cmid(), $USER->id)) {
            foreach($dups as $dup) {
                if($mform->elementExists('duplicate_message')) {

                    $params = array(
                            'eid'  => $dup->id,
                            'page' => 'request_edit'
                    );
                    $req_link = new moodle_url('/deadline/extensions/', $params);
                    $link = html_writer::link($req_link, get_string('duplicate_request_exists', extensions_plugin::EXTENSIONS_LANG));

                    $mform->setDefault('duplicate_message', $link);
                }
            }
        } else {
            if($mform->elementExists('duplicate_message')) {
                $mform->removeElement('duplicate_message');
            }
        }


        // if we have a response from a teacher, this is probably an 'edit'.
        // if so, then show the response area.
        if(isset($ext->response_message) && $ext->response_message != '') {

            $mform->setDefault('status', $this->get_status_by_code($ext->ext_status_code));

            if($ext->ext_status_code == AG_EXT_APPROVED) {
                $mform->setDefault('granted_ext_date', $ext->ext_granted_date);
                $mform->freeze('granted_ext_date');
            }

            $mform->setDefault('response_message', clean_param($ext->response_message, PARAM_TEXT));

        } else {
            /*************************************
             ***  3332 - BEGIN MODIFICATION    ***
            *************************************/
            if($mform->elementExists('staff_general')) {
                $mform->removeElement('staff_general');
            }

            if($mform->elementExists('status')) {
                $mform->removeElement('status');
            }

            if($mform->elementExists('granted_ext_date')) {
                $mform->removeElement('granted_ext_date');
            }

            if($mform->elementExists('response_message')) {
                $mform->removeElement('response_message');
            }
            /*************************************
             ***  3332 - END MODIFICATION      ***
            *************************************/
        }

        $mform->setDefault('asmntid', $this->get_cmid()); // Assessment ID

        if($this->get_readonly()) {

            if($mform->elementExists('reason')) {
                $mform->freeze('reason');
            }

            if($mform->elementExists('date')) {
                $mform->freeze('date');
            }

            if($mform->elementExists('ext_staffmember_id')) {
                $mform->freeze('ext_staffmember_id');
            }

            // Remove save/withdraw buttons
            $mform->removeElement('buttona');
        }
    }

    public function save_hook($form_data) {
        global $DB, $CFG, $COURSE, $USER;

        $deadlines  = new deadlines_plugin();
        $extensions = new extensions_plugin();

        $deadline = $deadlines->get_deadlines_for_cmid($this->get_cmid());

        if(isset($form_data->withdrawbutton)) {
            $type = 'withdraw';
        } else if(isset($form_data->submitbutton)) {
            $type = 'submit';
        } else if(isset($form_data->cancel)) {
            return true;
        }

        // Let's just make sure this is clean... Students are submitting this data..
        $form_data->group_submission   = clean_param($form_data->group_submission,   PARAM_INT);
        $form_data->ext_staffmember_id = clean_param($form_data->ext_staffmember_id, PARAM_INT);
        $form_data->group_list         = clean_param($form_data->group_list,         PARAM_TEXT);

        $form_data->reason   = clean_param($form_data->reason,   PARAM_TEXT);
        $form_data->page     = clean_param($form_data->page,     PARAM_TEXT);
        $form_data->action   = clean_param($form_data->action,   PARAM_TEXT);
        $form_data->date     = clean_param($form_data->date,     PARAM_INT);
        $form_data->id       = clean_param($form_data->id,       PARAM_INT);
        $form_data->cmid     = clean_param($form_data->cmid,     PARAM_INT);

        if(isset($form_data->type)) {
            $form_data->type     = clean_param($form_data->type,     PARAM_INT);
        }

        if(isset($form_data->time_ext)) {
            $form_data->time_ext = clean_param($form_data->time_ext, PARAM_INT);
        }

        // Looks like the supplied data should all be OK. It's been checked by
        // the validate functions and we've done some of our own validation, too.

        $data                = new stdClass;

        if(isset($form_data->group_submission) && $form_data->group_submission == 1) {
            $data->ext_type  = extensions_plugin::EXT_GROUP;
        } else {
            $data->ext_type  = extensions_plugin::EXT_INDIVIDUAL;
        }

        $data->cm_id         = $this->get_cmid();
        $data->deadline_id   = deadlines_plugin::get_deadline_id_by_cmid($this->get_cmid());
        $data->student_id    = $USER->id;
        $data->staff_id      = $form_data->ext_staffmember_id;
        $data->request_text  = $form_data->reason;
        $data->status        = extensions_plugin::STATUS_PENDING;
        $data->created       = date('U');

        // See if the user has selected a time based extension (quizzes).
        if(isset($form_data->type) && $form_data->type == extensions_plugin::EXTENSION_TYPE_TIME) {
            $existing_timelimit  = $deadline->timelimit;
            $extension_timelimit = $form_data->time_ext;

            $extended_timelimit = $existing_timelimit + $extension_timelimit;

            $data->timelimit = $extended_timelimit;
            $data->date      = 0;
        } else {
            $data->date          = $form_data->date;
        }

        if($ext_id = $DB->insert_record('deadline_extensions', $data, true)) {

            $extension = $data;
            $data->id = $ext_id;

            // If this is a group submission we need to add records to the appto
            // table for this.
            if($form_data->group_submission == 1) {
                // Split the group_list field
                $groups = explode(',', $form_data->group_list);

                foreach($groups as $group) {

                    if($group == '') {
                        continue;
                    }

                    $data = new stdClass;
                    $data->ext_id = $ext_id;
                    $data->group_id = $group;

                    $DB->insert_record('deadline_extensions_appto', $extension);
                }

            }

            // Handle the documents here
            $this->handle_documents($form_data, $data);

            $form_data->eid             = $ext_id;
            $form_data->ext_status_code = extensions_plugin::STATUS_PENDING;
            $form_data->response_text   = get_string('ext_act_add_reason', extensions_plugin::EXTENSIONS_LANG);

            // Add to the extensions history table.
            extensions_plugin::add_history($form_data);

            // Send a message to the user to notify of the update.
//             extensions_plugin::notify_user($form_data);

            return true;
        } else {
            return false;
        }

    }

    public function handle_documents($data, $ext = null) {

        global $USER, $CFG;

//         $context = context_user::instance($USER->id);
//         $component   = 'deadline_extensions';
//         $file_area   = 'attachment';

        $context     = context_user::instance($ext->student_id);
        $component   = 'deadline';
        $file_area   = 'extensions';
        $file_params = array('subdirs' => 0, 'maxbytes' => 102400000, 'maxfiles' => 5);

        $draftitemid = file_get_submitted_draft_itemid('attachments');

        $data->attachments = $draftitemid;

        file_prepare_draft_area($draftitemid, $context->id, $component, $file_area, $ext->id, $file_params);
        file_save_draft_area_files($data->attachments, $context->id, $component, $file_area, $ext->id, $file_params);

    }

    public function validation($data, $files) {
        return parent::validation($data, $files);
    }

     public function get_save_destination() {
         return 'requests';
     }

}