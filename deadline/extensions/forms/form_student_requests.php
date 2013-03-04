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
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once ($CFG->libdir . '/formslib.php');
require_once ($CFG->libdir . '/form/submit.php');
require_once ('form_request_common.php');
require_once ('u_moodleform_extension.php');

class form_student_requests extends form_base {

    protected $page_name = null;
    protected $save_destination = 'request_new';

    private $filters = null;

    public function __construct() {
        parent::__construct();

        $this->page_name = get_string('ext_student_requests','u_extension_lang');
    }

    public function definition() {
        parent::definition();

        global $CFG, $COURSE, $USER;
        $mform =& $this->_form;

    }

    public function definition_after_data() {
        //        parent::definition_after_data();

        global $CFG, $COURSE, $USER;
        $mform =& $this->_form;

        require_login($this->course->id);
        $context = get_context_instance(CONTEXT_COURSE, $this->course->id);

        //---------------

        $mform->addElement('header','general', get_string('ext_new_request','u_extension_lang'));

        $options = array();
        $options['-1'] = '&nbsp;';

        if($activities = $this->get_assignment_names()) {

            foreach($activities as $activity) {
                //$options[$activity->id] = $activity->name;
                $options[$activity->id] = $this->get_activity_name_by_module($activity->name, $extension->instance);
            }

            $mform->addElement('select', 'asmntid', get_string('extselectassignment','u_extension_lang'), $options);
            $mform->addElement('submit', 'request_button', get_string('ext_do_request','u_extension_lang'));
            $mform->disabledIf('request_button', 'asmntid', 'eq', '-1');
        } else {
            $mform->addElement('static', 'asmntid', '', get_string('ext_no_summ', 'u_extension_lang'));
        }

        //---------------

        $mform->addElement('header','general', get_string('ext_current_requests','u_extension_lang'));
        // Get the table and the data of the existing extension requests
        $mform->addElement('extension_requests','extension_requests', 'Requests', $this->get_student_assignments());

    }

    public function validation($data, $files) {
        return parent::validation($data, $files);
    }

    public function save_hook($form_data) {

        global $SESSION;

        // Insert Code Here.

        return true;
    }

}