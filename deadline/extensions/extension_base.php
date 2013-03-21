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
 * This file is the base class for the Extensions plugin. It contains all methods
 * to handle displaying and saving extensions and data submitted via it's forms.
 *
 * @package   deadline_extensions
 * @copyright 2013 University of South Australia {@link http://www.unisa.edu.au}
 * @author    James McLean <james.mclean@unisa.edu.au>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class extension_base {

    private $loaded_page = false;
    private $saved       = false;

    private $page        = null;
    private $course      = null;
    private $action      = null;
    private $ext_id      = null;
    private $cm_id       = null;
    private $user_type   = null;
    private $student_id  = null;

    const USR_STUDENT = 1;
    const USR_STAFF   = 2;

    public function __construct() {
        global $USER;

        // Set the form
        $this->form = new stdClass;

    }

    public function get_page_data($page) {

        $data = new stdClass;
        $data->id = $this->get_course()->id;

    }

    public function get_form_by_page($page = null) {

        $pageObj = new stdClass;

        if(is_null($page)) {
            $page = $this->get_page();
        }

        if($this->user_type == self::USR_STUDENT) {
            switch($page) {
                case 'requests':
                    $pageObj->file  = 'forms/form_student_requests.php';
                    $pageObj->class = 'form_student_requests';
                break;
                case 'request_edit':
                    $pageObj->file  = 'forms/form_request_edit.php';
                    $pageObj->class = 'form_request_edit';
                break;
                case 'request_new':
                    $pageObj->file  = 'forms/form_request_new.php';
                    $pageObj->class = 'form_request_new';
                break;

            }

            return $pageObj;
        }

        if ($this->user_type == self::USR_STAFF) {
            // Staff/Admin pages are here.
            switch ($page) {
                case 'requests':
                    $pageObj->file  = 'forms/form_staff_requests.php';
                    $pageObj->class = 'form_staff_requests';
                    break;
                case 'request_edit':
                    $pageObj->file  = 'forms/form_staff_request_edit.php';
                    $pageObj->class = 'form_staff_request_edit';
                    break;
                case 'request_create':
                    $pageObj->file  = 'forms/form_staff_request_create.php';
                    $pageObj->class = 'form_staff_request_create';
                    break;
                case 'global':
                    $pageObj->file  = 'forms/form_global.php';
                    $pageObj->class = 'form_global';
                    break;
                case 'global_add':
                    $pageObj->file  = 'forms/form_global_add.php';
                    $pageObj->class = 'form_global_add';
                    break;
                case 'global_edit':
                    $pageObj->file  = 'forms/form_global_edit.php';
                    $pageObj->class = 'form_global_edit';
                    break;
                case 'configure_activities':
                    $pageObj->file  = 'forms/form_configure_activities.php';
                    $pageObj->class = 'form_configure_activities';
                    break;
                case 'configure_activity':
                    $pageObj->file  = 'forms/form_configure_activity.php';
                    $pageObj->class = 'form_configure_activity';
                    break;
            }

            return $pageObj;
        }

    }

    //----------

    public function set_asmnt_id($asmnt_id = null) {
        $this->asmnt_id = $asmnt_id;
    }

    public function get_asmnt_id() {
        return $this->asmnt_id;
    }

    public function set_extension_id($ext_id = null) {
        if(!is_null($ext_id)) {
            $this->ext_id = $ext_id;
        }
    }

    public function get_extension_id() {
        return $this->ext_id;
    }

    public function set_action($action = null) {
        $this->action = $action;
    }

    public function get_action() {
        return $this->action;
    }

    /**
     * Get the form for a particular page in the Extensions.
     *
     * @param integer $page Page number to get
     * @return Object Object containing the Moodle Form for the specific Page.
     *
     */
    public function get_form($page = null) {
        global $CFG;

        $form_detail = $this->get_form_by_page($page);

        if (isset($form_detail->file) && file_exists($form_detail->file)) {
            require_once($form_detail->file);

            if (class_exists($form_detail->class)) {
                $form = $form_detail->class;

                return new $form('index.php');
            } else {
                throw new Exception('Form was found, but class could not be loaded.');
            }
        } else {
//            $this->unset_page();

//             print 'No relevant Form found for Page ' . $page;

            throw new Exception('No relevant Form found for Page ' . $page);
        }
    }

    public function set_course($course_id = null) {

        global $DB;

        $course_id = clean_param($course_id, PARAM_INT);

        if(!is_null($course_id)) {

            if($course_id != '0') {
                $course = $DB->get_record('course', array('id' => $course_id), '*', MUST_EXIST);
            } else {
                $course = new stdClass;
                $course->id = 0;
            }

            $this->course = $course;

        }

    }

    public function get_course() {
        return $this->course;
    }

    public function set_page($page = null) {
        if(!is_null($page)) {
            $this->page = $page;
        }

    }

    public function get_page() {
        return $this->page;
    }

    public function set_cmid($cm_id = null) {
        if(!is_null($cm_id)) {
            $this->cm_id = $cm_id;
        }
    }

    public function get_cmid() {
        return $this->cm_id;
    }

    public function set_student_id($sid = null) {
        if(!is_null($sid)) {
            $this->student_id = $sid;
        }
    }

    public function get_student_id() {
        return $this->student_id;
    }

    public function load_form($page = null) {
        global $COURSE;

        $mform = $this->get_form($page);

        if(!is_null($this->course)) {
            $mform->set_course($this->course);
        }

        if(!is_null($this->get_extension_id())) {
            $mform->set_extension_id($this->get_extension_id());
        }

        if($this->get_cmid() != 0) {
            $mform->set_cmid($this->get_cmid());
            $mform->load_activity_detail($this->get_cmid());
        }

        if($this->get_student_id() != 0) {
            $mform->set_student_id($this->get_student_id());
        }

//         if(!is_null($this->get_asmnt_id())) {
//             $mform->set_asmnt_id($this->get_asmnt_id());
//         }

//         if(isset($this->saved)) {
//             if(method_exists($mform, 'set_saved')) {
//                 $mform->set_saved($this->saved);
//             }
//         }

        $mform->post_form_load();

        add_to_log($COURSE->id, "extensions", "view", "index.php", "view " . $page, $this->get_cmid());

        return $mform;
    }

    private function load_user_data() {
        // If the user has the capability of requesting an extension, they
        // are a student. Show Student pages.
        // If the user has the capability of approving an extension, they
        // are a staff member. Show Staff pages.
        $course_id = $this->get_course()->id;

        if($course_id != 0) {
            if (has_capability('deadline/extensions:approveextension', context_course::instance($this->get_course()->id))) {
                $this->user_type = self::USR_STAFF;
            } else if(has_capability('deadline/extensions:requestextension', context_course::instance($this->get_course()->id))) {
                $this->user_type = self::USR_STUDENT;
            }
        } else {
            if (has_capability('deadline/extensions:approveextension', context_system::instance())) {
                $this->user_type = self::USR_STAFF;
            }
        }
    }

    public function display() {
        global $PAGE, $USER, $COURSE;

        add_to_log($COURSE->id, "extensions", "access", "index.php", "access extensions", $this->get_cmid());

        $this->load_user_data();

        try {
            $mform = $this->load_form();
            // If you need to set variables on the form, use $this->load_form().

        } catch (Exception $e) {
            print_error('Sorry, a problem was encountered when loading the form for this page. Error: ' . $e->getMessage());
            die;
        }

        // process form here.
        if ($mform->is_cancelled()) {

            redirect(new moodle_url('/deadline/extensions', array('id' => $this->get_course()->id)));

        } elseif ($fromform = $mform->get_data()) {

            if ($mform->is_submitted()) {
                // Handle the form submission here.

                if($this->get_action() == 'save') {

                    if(method_exists($mform, 'save_hook')) {

                        if(!$mform->save_hook($fromform)) {
                            add_to_log($COURSE->id, "extensions", "error", "index.php", "error when saving data " . $mform->get_page_name(), $this->get_cmid());
                            print_error('Error saving data.');
                        } else {
                            $this->saved = true;
                            add_to_log($COURSE->id, "extensions", "success", "index.php", "data saved successfully " . $mform->get_page_name(), $this->get_cmid());
                        } // end run save hook

                    } // end save hook exists

                } // end get action


                // Get the destination for this form when it's submitted
                // successfully.
                $page = null;
                if(method_exists($mform, 'get_save_destination')) {
                    $page = $mform->get_save_destination();
                }

                // Reload the form so we see the new data. We may have a new
                // page to display, too.
                $mform = $this->load_form($page);

            } // end if submitted

        } // end get data.

        $course = $this->get_course();

        $this->form->id = $course->id;

        $mform->set_data($this->form);

        $url = '?';
        $img_params = array('style', 'vertical-align: middle;');
        $url_params = array('id' => $this->get_course()->id);

        // Make sure the user is NOT a student, and the menu is enabled.
        if($this->user_type == self::USR_STAFF && get_config(extensions_plugin::EXTENSIONS_MOD_NAME, 'show_indiv_group')) {

            $content = "<div style=\"display: block; width: 450px; padding-bottom: 40px;\">";

            $img_params['src']  = extensions_plugin::EXTENSIONS_URL_PATH . "/assets/images/indiv_ext.png";
            $url_params['page'] = 'requests';

            $content .= "<div style=\"display: block; background-color: white; width: auto; float: left; padding: 5px;\">";
            $content .= html_writer::tag('img', null, $img_params);
            $content .= html_writer::link(new moodle_url($url, $url_params), get_string("ext_indiv_exts", extensions_plugin::EXTENSIONS_LANG));

            // insert pending count text here.
            $content .= extensions_plugin::get_pending_count_text($USER, null);

            $content .= "</div>";

            $img_params['src']  = extensions_plugin::EXTENSIONS_URL_PATH . "/assets/images/global_ext.png";
            $url_params['page'] = 'global';

            $content .= "<div style=\"display: block; background-color: white; width: 165px; float: right; padding: 5px;\">";
            $content .= html_writer::tag('img', null, $img_params);
            $content .= html_writer::link(new moodle_url($url, $url_params), get_string("ext_glob_exts", extensions_plugin::EXTENSIONS_LANG));
            $content .= "</div>";

            $content .= "</div>";

            print $content;

        }

        $mform->display();

    }

}
