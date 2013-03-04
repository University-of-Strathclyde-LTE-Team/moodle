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
require_once('form_staff_request_edit.php');

class form_staff_request_create extends form_staff_request_edit {

    public function definition_after_data() {
        parent::definition_after_data();

        $mform =& $this->_form;

        if(!is_null($this->get_cmid())) {
            $mform->addElement('hidden', 'cmid', $this->get_cmid());
        }

        if(!is_null($this->get_student_id())) {
            $mform->addElement('hidden', 'student_id', $this->get_student_id());
        }

        if ($mform->elementExists('page')) {
            $mform->setDefault('page', 'request_create');
        }

        // remove fields that make no sense in this view
        if($mform->elementExists('ext_due_static')) {
            $mform->removeElement('ext_due_static');
        }

        if($mform->elementExists('ext_reason_static')) {
            $mform->removeElement('ext_reason_static');
        }

        if($mform->elementExists('ext_date_requested')) {
            $mform->removeElement('ext_date_requested');
        }

        if($mform->elementExists('supdoc1')) {
            $mform->removeElement('supdoc1');
        }

        if($mform->elementExists('ext_status_code')) {
            $status = $mform->getElement('ext_status_code');
            $status->freeze();
        }

        if($mform->elementExists('extension_history')) {
            $mform->removeElement('extension_history');
        }

    }

    public function validation($data, $files) {
        global $CFG, $DB;

        $errors = array();

        $deadline   = new deadlines_plugin();
        $due_date = $deadline->get_date_deadline($data['cmid']);

        if($data['ext_granted_date'] < $due_date) {
            $errors['ext_granted_date'] = get_string('ext_granted_before_due', extensions_plugin::EXTENSIONS_LANG);
        }

        if(!extensions_plugin::is_extension_approver(null, null, $data['cmid'])) {
            $errors['ext_status_code'] = get_string('ext_not_approver', extensions_plugin::EXTENSIONS_LANG);
        }

        return $errors;
    }

    public function save_hook($form_data) {

        global $DB, $USER;

        if(!is_null($form_data)) {
            if(isset($form_data->submitbutton)) {
                // Someone hit the save changes button. That's all they
                // can really do anyway

                if(!extensions_plugin::is_extension_approver(null, null, $form_data->cmid)) {
                    error(get_string('ext_not_approver', extensions_plugin::EXTENSIONS_LANG));
                    exit;
                }

                $ext_data                = new stdClass;
                $ext_data->ext_type      = extensions_plugin::EXT_INDIVIDUAL; // This might need to change to allow groups too?
                $ext_data->cm_id         = $form_data->cmid;
                $ext_data->deadline_id   = deadlines_plugin::get_deadline_id_by_cmid($form_data->cmid);
                $ext_data->student_id    = $form_data->student_id;
                $ext_data->staff_id      = $USER->id;
                $ext_data->response_text = $form_data->response_text;
                $ext_data->date          = $form_data->ext_granted_date;
                $ext_data->status        = $form_data->ext_status_code;
                $ext_data->created       = date('U');

                if($ext_id = $DB->insert_record('deadline_extensions', $ext_data, true)) {

                    $form_data->eid = $ext_id;

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

    }

    public function get_save_destination() {
        return 'requests';
    }

}