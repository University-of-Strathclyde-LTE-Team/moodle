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
 * This file contains a form used when configuring a specific activity for
 * extensions.
 *
 * @package   deadline_extensions
 * @copyright 2013 University of South Australia {@link http://www.unisa.edu.au}
 * @author    James McLean <james.mclean@unisa.edu.au>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('form_base.php');

class form_configure_activity extends form_base {


    public function __construct() {
        parent::__construct();

    }

    public function definition() {
        parent::definition();

        global $CFG, $USER;
        $mform =& $this->_form;

        $mform->addElement('hidden', 'page', 'configure_activity');
        $mform->setType('page', PARAM_ALPHAEXT);

        $mform->addElement('hidden', 'action', 'save');
        $mform->setType('action', PARAM_ALPHAEXT);

        $mform->addElement('hidden', 'cmid');
        $mform->setType('cmid', PARAM_INT);

        $mform->addElement('header', 'general', get_string('ext_configure_activity', extensions_plugin::EXTENSIONS_LANG));
        $mform->addElement('static', 'activity_name', get_string('activity'));
        $mform->addElement('select', 'extensions_enabled', get_string('enable_extensions', extensions_plugin::EXTENSIONS_LANG), extensions_plugin::get_extension_enable_items());

        if(get_config('deadline_extensions', 'req_cut_off') != '-1') {
            $mform->addElement('select', 'extensions_cutoff', get_string('extensions_cutoff', extensions_plugin::EXTENSIONS_LANG), extensions_plugin::get_cutoff_options());
        }

        $picker = $mform->addElement('select_picker', 'staff_approvers', get_string('extension_approvers', extensions_plugin::EXTENSIONS_LANG));
        $picker->set_multiple(TRUE);

        $this->add_action_buttons(TRUE, get_string("save", extensions_plugin::EXTENSIONS_LANG));
    }

    public function definition_after_data() {
        parent::definition_after_data();

        global $CFG, $USER;
        $mform =& $this->_form;

        $ext_users = array();
        $ext_users['left']  = array();
        $ext_users['right'] = array();

        $activity = extensions_plugin::get_activity_detail_by_cmid($this->get_cmid());

        $mform->setDefault('cmid', $this->get_cmid());

        $mform->setDefault('activity_name', $activity->name);

        $context = context_course::instance($activity->course);

        $roles = get_roles_used_in_context($context);

        foreach($roles as $role) {

            if($role->shortname == 'student') {
                continue;
            }

            $users = get_role_users($role->id, $context, false, 'u.id, u.username, u.firstname, u.lastname');

            foreach($users as $user) {
                $ext_users['right'][$user->id] = $user->firstname . ' ' . $user->lastname . ' (' . $role->shortname . ')';
            }
        }

        $enabled = $mform->getElement('extensions_enabled');
        $enabled->setSelected(extensions_plugin::get_extension_status_by_cmid($this->get_cmid()));

        if($mform->elementExists('extensions_cutoff')) {
            $cutoff = $mform->getElement('extensions_cutoff');
            $cutoff->setSelected(extensions_plugin::get_extension_cutoff_by_cmid($this->get_cmid()));
        }

        $staff = $mform->getElement('staff_approvers');
        $staff->loadOptions(null, $ext_users);

    }

    public function validation($data, $files) {

        $errors = array();

        if(isset($data['extensions_enabled']) && $data['extensions_enabled'] == extensions_plugin::EXT_ENABLED) {

            if(isset($data['staff_approvers']['leftContents']) && $data['staff_approvers']['leftContents'] == '') {
                $errors['staff_approvers'] = get_string('must_select_one_approver', extensions_plugin::EXTENSIONS_LANG);
            }

        }

        return $errors;
    }

    public function save_hook($form_data) {

        global $DB;

        $params = array(
                'cm_id' => $form_data->cmid
        );

        if($DB->record_exists('deadline_extensions_enabled', $params)) {

            // get the specific extensions enabled record ID
            $id = $DB->get_field('deadline_extensions_enabled', 'id', $params);

            // save the extension approval details
            $data = new stdClass;
            $data->id     = $id;
            $data->cm_id  = $form_data->cmid;
            $data->status = $form_data->extensions_enabled;
            $data->request_cutoff = $form_data->extensions_cutoff;

            $DB->update_record('deadline_extensions_enabled', $data);

            // save the staff members as approvers for this activity.
            $staff_ids = explode(',', $form_data->staff_approvers['leftContents']);

            foreach($staff_ids as $staff_id) {

                $staff = new stdClass;
                $staff->ext_en_id = $id;
                $staff->user_id   = $staff_id;

                $DB->insert_record('deadline_extensions_appv', $staff);

            }

            return true;
        }
    }

}