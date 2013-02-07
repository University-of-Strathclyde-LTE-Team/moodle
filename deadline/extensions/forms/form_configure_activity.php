<?php

require_once('form_base.php');

class form_configure_activity extends form_base {

    protected $page_name = "Configure Activity";

    public function __construct() {
        parent::__construct();

        //        $this->page_name = get_string('ext_indiv_req', Extensions::LANG_EXTENSIONS);

    }

    public function definition() {
        parent::definition();

        global $CFG, $USER;
        $mform =& $this->_form;

        $mform->addElement('hidden', 'action', 'save');
        $mform->addElement('hidden', 'page', 'configure_activity');

        $mform->addElement('header', 'general', get_string('ext_configure_activity', Extensions::LANG_EXTENSIONS));

        $activities = $this->get_activity_names($this->get_course());

        // Add the table to the form to allow extension enable/disable.
        $activity_table = $mform->addElement('extension_configure', 'activity_list', 'Activities', Extensions::build_activity_table($activities));

        // name value attribs
        $submit = $mform->addElement('submit', 'submit_changes', get_string('ext_submit_changes', Extensions::LANG_EXTENSIONS));

    }

    public function definition_after_data() {

        global $CFG, $COURSE, $USER;
        $mform =& $this->_form;

    }

    public function save_hook($form_data) {
        global $DB;
        // Save the items changed to the database.

//         var_dump($form_data);

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

            if($DB->record_exists('extensions_enabled', array('cm_id' => $cm_id))) {

                $item = new stdClass;
                $item->id     = Extensions::get_extension_enable_id_by_cmid($cm_id);
                $item->cm_id  = $cm_id;
                $item->status = $data['enabled'];
                $item->request_cutoff = $data['cutoff_date'];

                return $DB->update_record('extensions_enabled', $item);

            } else {
                // this item doesn't exist in the extensions enabled table.
                // create it, if it's being enabled.

                if($data['enabled'] == Extensions::EXT_ENABLED) {
                    $item = new stdClass;
                    $item->cm_id  = $cm_id;
                    $item->status = $data['enabled'];
                    $item->request_cutoff = $data['cutoff_date'];
                    $item->date_enabled = date('U');

                    return $DB->insert_record('extensions_enabled', $item);
                }

            }
        }

    }

}