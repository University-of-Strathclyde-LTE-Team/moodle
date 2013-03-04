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

class form_configure_activities extends form_base {

    protected $page_name = "Configure Activities";

    public function __construct() {
        parent::__construct();

        //        $this->page_name = get_string('ext_indiv_req', extensions_plugin::EXTENSIONS_LANG);

    }

    public function definition() {
        parent::definition();

        global $CFG, $USER;
        $mform =& $this->_form;

        $mform->addElement('hidden', 'page', 'configure_activities');
        $mform->setType('page', PARAM_ALPHAEXT);

        $mform->addElement('hidden', 'action', 'save');
        $mform->setType('action', PARAM_ALPHAEXT);

        $mform->addElement('header', 'general', get_string('ext_configure_activities', extensions_plugin::EXTENSIONS_LANG));

        $ext = new extensions_plugin;
        $activities = $ext->get_activity_names($this->get_course());

        // Add the table to the form to allow extension enable/disable.
        $activity_table = $mform->addElement('extension_configure', 'activity_list', get_string('activities'), extensions_plugin::build_activity_table($activities));

        // name value attribs
        $submit = $mform->addElement('submit', 'submit_changes', get_string('ext_submit_changes', extensions_plugin::EXTENSIONS_LANG));

    }

    public function definition_after_data() {

        global $CFG, $COURSE, $USER;
        $mform =& $this->_form;

    }

    public function save_hook($form_data) {
        global $DB;
        // Save the items changed to the database.

        $data_to_save = array();

        // loop through the 'enabled' items.
        foreach($form_data->enabled as $key => $value) {
            $data_to_save[$key]['enabled'] = $value;
        }

        // loop through the 'cutoff_date' items.
        foreach($form_data->cutoff_date as $key => $value) {
            $data_to_save[$key]['cutoff_date'] = $value;
        }

        foreach($data_to_save as $cm_id => $data) {

            if($DB->record_exists('deadline_extensions_enabled', array('cm_id' => $cm_id))) {

                $item = new stdClass;
                $item->id     = extensions_plugin::get_extension_enable_id_by_cmid($cm_id);
                $item->cm_id  = $cm_id;
                $item->status = $data['enabled'];
                $item->request_cutoff = $data['cutoff_date'];

                return $DB->update_record('deadline_extensions_enabled', $item);

            } else {
                // this item doesn't exist in the extensions enabled table.
                // create it, if it's being enabled.

                if($data['enabled'] == extensions_plugin::EXT_ENABLED) {
                    $item = new stdClass;
                    $item->cm_id  = $cm_id;
                    $item->status = $data['enabled'];
                    $item->request_cutoff = $data['cutoff_date'];
                    $item->date_enabled = date('U');

                    return $DB->insert_record('deadline_extensions_enabled', $item);
                }

            }
        }

    }

    public function validation($data, $files) {
        // make sure that we're not disabling extensions on an item which has
        // pending extension requests.


    }

}