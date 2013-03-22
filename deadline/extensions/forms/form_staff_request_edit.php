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

    protected $page_name = null;

    private $read_only = false;
    private $filters = null;

    public function __construct() {
        parent::__construct();

        $this->page_name = get_string('ext_request_edit', extensions_plugin::EXTENSIONS_LANG);
        global $COURSE;
        add_to_log($COURSE->id, "extensions", "viewing", "index.php", "viewing " . $this->page_name, $this->get_cmid());
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
        $mform->addElement('static', 'group_static',       get_string('group_extension', extensions_plugin::EXTENSIONS_LANG));
        $mform->addElement('static', 'ext_reason_static',  get_string('extreasonfor',  extensions_plugin::EXTENSIONS_LANG));
        $mform->addElement('static', 'supdoc1',            get_string('extsupporting', extensions_plugin::EXTENSIONS_LANG), null);
        $mform->addElement('static', 'asmt_due_static',    get_string('extasmntdue',    extensions_plugin::EXTENSIONS_LANG), null);
        $mform->addElement('static', 'ext_due_static',     get_string('extsubmission',  extensions_plugin::EXTENSIONS_LANG), null);
        $mform->addElement('static', 'ext_date_requested', get_string('extrequestdate', extensions_plugin::EXTENSIONS_LANG), null);

        $extension_status = extensions_plugin::get_all_extension_status();
        $extension_date_options =  extensions_plugin::get_date_options();
        $extension_timelimit_options = extensions_plugin::get_timelimit_options();

        $mform->addElement('header', 'general', get_string('extapproval', extensions_plugin::EXTENSIONS_LANG));

        $mform->addElement('select', 'ext_status_code', get_string('extstatus', extensions_plugin::EXTENSIONS_LANG), $extension_status);
        $mform->addRule('ext_status_code', get_string('please_select', extensions_plugin::EXTENSIONS_LANG), 'required', null, 'client');

        $mform->addElement('date_time_selector', 'ext_granted_date', get_string('extapproveddate', extensions_plugin::EXTENSIONS_LANG), $extension_date_options);

        $mform->addElement('select', 'ext_timelimit', get_string('approved_timelimit', extensions_plugin::EXTENSIONS_LANG), $extension_timelimit_options);

        $mform->addElement('htmleditor', 'response_text', get_string('extresponse', extensions_plugin::EXTENSIONS_LANG), array('cols' => 60, 'rows' => 6));
        $mform->setType('response_text', PARAM_RAW);

        $this->add_action_buttons(true);
    }

    public function definition_after_data() {
        parent::definition_after_data();

        global $CFG, $COURSE, $course, $USER, $DB;

        // Load a copy of the instanciated form object from this object.
        $mform =& $this->_form;

        $deadline   = new deadlines_plugin();
        $current_deadline  = $deadline->get_deadline_date_deadline($this->get_cmid());
        $current_timelimit = $deadline->get_deadline_timelimit($this->get_cmid());

        if ($this->get_extension_id()) {
            $extension = extensions_plugin::get_extension_by_id($this->get_extension_id());
        } else {

            // Staff adding on a student behalf.
            if ($extension_default = get_config('deadline_extensions', 'default_ext_length')) {
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
        if (get_config(extensions_plugin::EXTENSIONS_MOD_NAME, 'show_duplicate_warn') == 1) {

            if ($dups = extensions_plugin::duplicate_requests($extension->cm_id, $extension->student_id, $extension->id)) {
                // If there is any duplicate requests, populate those fields.

                $links = null;

                $mform->setDefault('other_req', get_string('ext_other_req_exists', extensions_plugin::EXTENSIONS_LANG));

                foreach ($dups as $dup) {

                    $string = get_string('duplicate_request', extensions_plugin::EXTENSIONS_LANG);
                    $params = array('page' => 'request_edit', 'eid' => $dup->id);
                    $url = new moodle_url(extensions_plugin::EXTENSIONS_URL_PATH . '/', $params);

                    if ($dup->date != 0) {
                        $dt = date(extensions_plugin::get_date_format(), $dup->date);
                    } else if ($dup->date == 0 && $dup->timelimit != 0) {
                        $dt = $dup->timelimit / 60 . ' ' . get_string('minutes', extensions_plugin::EXTENSIONS_LANG);
                    }

                    $links .= html_writer::link($url, $string) . ' ' .
                              $dt . ' ' .
                              html_writer::tag('i', extensions_plugin::get_status_string($dup->status)) . ' ' .
                              html_writer::empty_tag('br');

                }

                $mform->setDefault('other_req_links', $links);
            } else {
                // But if there isn't, we'll remove those fields as they leave
                // a big space if there isnt anything populated.
                if ($mform->elementExists('other_req')) {
                    $mform->removeElement('other_req');
                }
                if ($mform->elementExists('other_req_links')) {
                    $mform->removeElement('other_req_links');
                }
            }

        }

        // Assignment Name.
        $name = extensions_plugin::get_activity_name($extension->cm_id);
        $mod  = extensions_plugin::get_activity_mod_by_cmid($extension->cm_id);
        $url  = new moodle_url('/mod/' . $mod . '/view.php', array('id' => $extension->cm_id));
        $link = html_writer::link($url, $name);

        $mform->setDefault('assignment_name', $link);

        // Student Name.
        $user = $DB->get_record('user', array('id' => $extension->student_id));
        $params = array('id' => $user->id);

        $user_url  = new moodle_url('/user/view.php', $params);
        $user_link = html_writer::link($user_url, "{$user->firstname} {$user->lastname} - {$user->idnumber}");
        $mform->setDefault('ext_student_static', $user_link);

        // Group extension?
        if ($extension->ext_type == extensions_plugin::EXT_GROUP) {
            if ($mform->elementExists('group_static')) {

                $params = array(
                        'ext_id' => $extension->id
                );

                $group_names = '';

                // Get the groups for this extension request.
                if ($groups = $DB->get_records('deadline_extensions_appto', $params)) {

                    foreach ($groups as $group) {
                        $params = array(
                                'id' => $this->get_course()->id,
                                'grouping' => '0',
                                'group'    => $group->group_id
                        );

                        $group_url = new moodle_url('/group/overview.php', $params);
                        $group_name = groups_get_group_name($group->group_id);
                        $group_link = html_writer::link($group_url, $group_name, array('target' => 'blank'));

                        $group_names = $group_names . $group_link;
                    }
                }

                $mform->setDefault('group_static', $group_names);
            }
        } else {
            if ($mform->elementExists('group_static')) {
                $mform->removeElement('group_static');
            }
        }

        // Get the file(s) stored for this request.
        $fs = get_file_storage();
        $user_context = context_user::instance($extension->student_id);
        $component   = 'deadline';
        $file_area   = 'extensions';

        $files = $fs->get_area_files($user_context->id, $component, $file_area, $extension->id);

        if ($files) {
            $file_names = null;
            foreach ($files as $file) {

                if ($file->get_filename() == '.') {
                    continue;
                }

                $url      = "{$CFG->wwwroot}/pluginfile.php/{$file->get_contextid()}/{$component}/{$file_area}";
                $filename = $file->get_filename();
                $fileurl  = $url.$file->get_filepath().$file->get_itemid().'/'.$filename;
                $out[]    = html_writer::link($fileurl, $filename);

            }

            $br = html_writer::empty_tag('br');

            $mform->setDefault('supdoc1', implode($br, $out));
        }

        $mform->addElement('hidden', 'eid', $extension->id);
        $mform->setType('extid', PARAM_INT);

        $mform->addElement('hidden', 'action', 'save');
        $mform->setType('action', PARAM_ALPHA);

        $mform->addElement('hidden', 'page', 'request_edit');
        $mform->setType('page', PARAM_ALPHAEXT);

        // Make sure this user can actaully be approving this extension request.
        if (extensions_plugin::is_extension_approver($extension->id, $USER->id)) {
            $this->set_readonly(false);
            $approver = true;
        } else {
            $this->set_readonly(true);
            $approver = false;
        }

        // The request can only modified when it's pending.
        if ( ($extension->status == extensions_plugin::STATUS_PENDING || $extension->status == extensions_plugin::STATUS_APPROVED) && $approver == true) {
            $this->set_readonly(false);
        } else {
            $this->set_readonly(true);
        }

        // Admins can always modify.
        if (is_siteadmin($USER->id)) {
            $this->set_readonly(false);
        }

        if ($extension->status == extensions_plugin::STATUS_APPROVED || $extension->status == extensions_plugin::STATUS_DENIED) {
            $readonly = true;
        }

        $mform->setDefault('ext_reason_static', $extension->request_text);
        $mform->setDefault('ext_status_code',   $extension->status);

        if ($extension->date == 0 && extensions_plugin::get_activity_type_by_cmid($this->get_cmid()) == 'quiz') {

            // Ext granted date is not required in Timelimit extension mode.
            // Remove it.
            if ($mform->elementExists('ext_granted_date')) {
                $mform->removeElement('ext_granted_date');
            }

            // Set the current request in the dropdown.
            if ($mform->elementExists('ext_timelimit')) {
                $element = $mform->getElement('ext_timelimit');
                $selected_item =  $extension->timelimit - $current_timelimit;

                $element->setSelected($selected_item);
            }

            $detail = $mform->getElement('asmt_due_static');
            $detail->setLabel(get_string('current_timelimit', extensions_plugin::EXTENSIONS_LANG));
            $mform->setDefault('asmt_due_static', $current_timelimit / 60 . ' ' . get_string('minutes', extensions_plugin::EXTENSIONS_LANG));

            $detail = $mform->getElement('ext_due_static');
            $detail->setLabel(get_string('requested_timelimit', extensions_plugin::EXTENSIONS_LANG));
            $mform->setDefault('ext_due_static', $extension->timelimit / 60 . ' ' . get_string('minutes', extensions_plugin::EXTENSIONS_LANG));

        } else {

            $mform->setDefault('ext_granted_date',   $extension->date);

            $mform->setDefault('asmt_due_static', date(extensions_plugin::get_date_format(), $current_deadline));

            $ext_diff = html_writer::tag('i', extensions_plugin::date_difference($current_deadline, $extension->date) . ' days', array('class' => 'days_extension'));
            $mform->setDefault('ext_due_static',     date(extensions_plugin::get_date_format(), $extension->date) . ' ' . $ext_diff);

            if ($mform->elementExists('ext_timelimit')) {
                $mform->removeElement('ext_timelimit');
            }

        }

        $req_diff_days = extensions_plugin::date_difference($current_deadline, $extension->created);

        if ($req_diff_days > 0) {
            // Request made AFTER due date.
            $req_diff = html_writer::tag('i', $req_diff_days . get_string('days_after', extensions_plugin::EXTENSIONS_LANG));
        } else {
            // Request made BEFORE due date.
            $req_diff_days = $req_diff_days * -1; // Convert to positive number.
            $req_diff = html_writer::tag('i', $req_diff_days . get_string('days_prior', extensions_plugin::EXTENSIONS_LANG));
        }

        $mform->setDefault('ext_date_requested', date(extensions_plugin::get_date_format(), $extension->created) . ' ' . $req_diff);

        if (isset($extension->response_text) && $extension->response_text != false) {
            $mform->setDefault('response_text', $extension->response_text);
        }

        if ($this->get_readonly()) {
            $mform->freeze('ext_status_code');
            $mform->freeze('response_text');
            $mform->freeze('ext_granted_date');

            if ($approver === false) {
                $mform->addElement('static', 'static', null, get_string('ext_not_approver', extensions_plugin::EXTENSIONS_LANG));
            }

            // Remove the button group.
            if ($mform->elementExists('buttonar')) {
                $mform->removeElement('buttonar');
            }
        }

        // Insert the history here.
        if ($history = extensions_plugin::build_extension_history_table($extension->id)) {
            $mform->addElement('static', 'extension_history', get_string('extension_history', extensions_plugin::EXTENSIONS_LANG), html_writer::table($history, true));
        }

    }

    public function validation($data, $files) {

        global $USER;

        $errors = array();

        $extension = extensions_plugin::get_extension_by_id($this->get_extension_id());
        $deadline   = new deadlines_plugin();
        $due_date = $deadline->get_deadline_date_deadline($extension->cm_id);

        // If the request is DENIED then the message is compulsory.
        if ($data['ext_status_code'] == extensions_plugin::STATUS_DENIED && strlen($data['response_text']) == '0' ) {
            $errors['response_text'] = get_string('ext_message_required', extensions_plugin::EXTENSIONS_LANG);
        }

        if (isset($data['ext_granted_date'])) {
            if ($data['ext_granted_date'] < $due_date) {
                $errors['ext_granted_date'] = get_string('ext_granted_before_due', extensions_plugin::EXTENSIONS_LANG);
            }
        }

        if (!extensions_plugin::is_extension_approver($this->get_extension_id())) {
            $errors['ext_status_code'] = get_string('ext_not_approver', extensions_plugin::EXTENSIONS_LANG);
        }

        return $errors;
    }

    public function save_hook($form_data = null) {

        global $USER, $DB, $COURSE;

        if (!is_null($form_data)) {
            if (isset($form_data->submitbutton)) {

                // Someone hit the save changes button. That's all they can really do anyway.
                if (!extensions_plugin::is_extension_approver($this->get_extension_id())) {
                    add_to_log($COURSE->id, "extensions", "error", "index.php", "non-approver tried to modify", $this->get_cmid());
                    error(get_string('ext_not_approver', extensions_plugin::EXTENSIONS_LANG));
                    return false;
                }

                $ext_data                = new stdClass;
                $ext_data->id            = $form_data->eid;
                $ext_data->status        = $form_data->ext_status_code;
                $ext_data->response_text = $form_data->response_text;

                if (isset($form_data->ext_granted_date)) {
                    $ext_data->date      = $form_data->ext_granted_date;
                }
                if (isset($form_data->ext_timelimit)) {
                    $deadline = new deadlines_plugin();
                    $timelimit = $deadline->get_deadline_timelimit($this->get_cmid());

                    $ext_data->timelimit = $timelimit + $form_data->ext_timelimit;
                    $ext_data->date      = 0;
                }

                if ($DB->update_record('deadline_extensions', $ext_data)) {

                    add_to_log($COURSE->id, "extensions", "success", "index.php", "extension {$form_data->eid} updated successfully");

                    // Add to the extensions history table.
                    extensions_plugin::add_history($form_data);

                    // Send a message to the user to notify of the update.
                    extensions_plugin::notify_user($form_data);

                } else {
                    add_to_log($COURSE->id, "extensions", "error", "index.php", "extension creation failed!");
                    return false;
                }

                return true;
            }
        }

    }

    public function set_readonly($read_only) {
        if (is_bool($read_only)) {
            $this->read_only = $read_only;
        }
    }

    public function get_readonly() {
        return $this->read_only;
    }

}