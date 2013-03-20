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
 * This file contains the editing class for modifying an existing extension
 * request. It extends the existing 'new' class to re-use it's code.
 *
 * @package   deadline_extensions
 * @copyright 2013 University of South Australia {@link http://www.unisa.edu.au}
 * @author    James McLean <james.mclean@unisa.edu.au>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('form_base.php');
require_once('form_request_new.php');

class form_request_edit extends form_request_new {

    protected $page_name = null;

    public function __construct() {
        parent::__construct();

        $this->page_name = "Edit Extension Request";

        global $COURSE;
        add_to_log($COURSE->id, "extensions", "viewing", "index.php", "viewing " . $this->page_name, $this->get_cmid());
    }

    public function definition_after_data() {
        parent::definition_after_data();

        global $CFG, $COURSE, $USER, $course;

        // load a copy of the instanciated form object from this object.
        $mform =& $this->_form;

        $ext = extensions_plugin::get_extension_by_id($this->get_extension_id());

        $mform->setDefault('reason', $ext->request_text);
//         $mform->setDefault('attachments', ''); // how?
        $mform->setDefault('date', $ext->date);
        $mform->setDefault('time_ext', $ext->timelimit);
        $mform->setDefault('ext_staffmember_id', $ext->staff_id);
        $mform->setDefault('response_message', $ext->response_text);

        if($ext->date == 0) {

            if($mform->elementExists('type')) {
                $mform->setDefault('type', extensions_plugin::EXTENSION_TYPE_TIME);
            }

            if($mform->elementExists('time_ext')) {

                $deadline = new deadlines_plugin();
                $timelimit = $deadline->get_timelimit($ext->cm_id);

                $extension = $ext->timelimit - $timelimit;

                $mform->setDefault('time_ext', $extension);
            }

        } else {
            if($mform->elementExists('type')) {
                $mform->setDefault('type', extensions_plugin::EXTENSION_TYPE_DATE);
            }
        }

        // For some status' we need to lock the form down. Others it needs
        // to stay open so it can be modified.
        if($ext->status == extensions_plugin::STATUS_APPROVED  ||
                $ext->status == extensions_plugin::STATUS_DENIED    ||
                $ext->status == extensions_plugin::STATUS_WITHDRAWN ||
                $ext->status == extensions_plugin::STATUS_REVOKED) {
            $this->set_readonly(true);
        } else {
            // Remove the field showing what is in 'approved date' if this
            // isn't approved currently.
            if($mform->elementExists('granted_ext_date')) {
                $mform->removeElement('granted_ext_date');
            }

        }

        if($this->get_readonly()) {

            if($mform->elementExists('reason')) {
                $mform->freeze('reason');
            }

            if($mform->elementExists('date')) {
                $mform->freeze('date');
            }

            if($mform->elementExists('attachments')) {
                $mform->freeze('attachments');
            }

            if($mform->elementExists('type')) {
                $mform->freeze('type');
            }

            if($mform->elementExists('time_ext')) {
                $mform->freeze('time_ext');
            }

            if($mform->elementExists('ext_staffmember_id')) {
                $mform->freeze('ext_staffmember_id');
            }

            // Remove save/withdraw buttons
            if($mform->elementExists('buttona')) {
                $mform->removeElement('buttona');
            }
        }

    }

}