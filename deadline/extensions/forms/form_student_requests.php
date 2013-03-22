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
 * This file contains a the form to show a student all of their current extension
 * requests for a specific course.
 *
 * @package   deadline_extensions
 * @copyright 2013 University of South Australia {@link http://www.unisa.edu.au}
 * @author    James McLean <james.mclean@unisa.edu.au>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    //  It must be included from a Moodle page
}

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/form/submit.php');
require_once('form_base.php');

class form_student_requests extends form_base {

    protected $page_name = null;

    private $filters     = null;

    public function __construct() {
        parent::__construct();

        $this->page_name = get_string('ext_student_requests', extensions_plugin::EXTENSIONS_LANG);

        global $COURSE;
        add_to_log($COURSE->id, "extensions", "viewing", "index.php", "viewing " . $this->page_name, $this->get_cmid());
    }

    public function definition() {
        parent::definition();

        global $CFG, $COURSE, $USER;
        $mform =& $this->_form;

        $mform->addElement('header', 'general', get_string('ext_new_request', extensions_plugin::EXTENSIONS_LANG));

        $mform->addElement('select', 'cmid', get_string('extselectassignment', extensions_plugin::EXTENSIONS_LANG));
        $mform->addElement('submit', 'request_button', get_string('ext_do_request', extensions_plugin::EXTENSIONS_LANG));
        $mform->disabledif ('request_button', 'cmid', 'eq', '-1');

        // ---------------

        $mform->addElement('header', 'general', get_string('ext_current_requests', extensions_plugin::EXTENSIONS_LANG));
        // Get the table and the data of the existing extension requests
        $mform->addElement('extension_requests_student', 'extension_requests_student', '', extensions_plugin::build_student_extensions_table($USER->id));

    }

    public function definition_after_data() {
        //        parent::definition_after_data();

        global $CFG, $COURSE, $USER;
        $mform =& $this->_form;

        // ---------------

        $options = array();
        $options['-1'] = '&nbsp;';

        $ext = new extensions_plugin;
        $activities = $ext->get_activity_names($this->get_course());

        foreach ($activities as $activity) {
            $options[$activity->id] = $activity->name;
        }

        // Set options on the activities dropdown
        if ($mform->elementExists('cmid')) {
            $dd = $mform->getElement('cmid');
            $dd->load($options);
        }

        if ($mform->elementExists('extension_requests_student')) {
            $table = $mform->getElement('extension_requests_student');
            $table->set_table_data(extensions_plugin::build_student_extensions_table($USER->id, $this->get_course()));
        }

    }

    public function get_save_destination() {
        return 'request_new';
    }

    public function validation($data, $files) {
        return parent::validation($data, $files);
    }

    public function save_hook($form_data) {
        return true;
    }

}