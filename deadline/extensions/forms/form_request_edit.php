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

class form_request_edit extends form_request_new {

    protected $page_name = null;

    public function __construct() {
        parent::__construct();

        $this->page_name = "Edit Extension Request";

    }

    public function definition_after_data() {

        parent::definition_after_data();

        global $CFG, $COURSE, $USER, $course;

        // load a copy of the instanciated form object from this object.
        $mform =& $this->_form;


        // todo: this all needs to be refactored.

        $ext = $this->get_extension_details();

        // For some status' we need to lock the form down. Others it needs
        // to stay open so it can be modified.
        if($ext->ext_status_code == extensions_plugin::STATUS_APPROVED  ||
                $ext->ext_status_code == extensions_plugin::STATUS_DENIED    ||
                $ext->ext_status_code == extensions_plugin::STATUS_WITHDRAWN ||
                $ext->ext_status_code == extensions_plugin::STATUS_REVOKED) {
            $this->set_readonly(true);
        } else {
            // Remove the field showing what is in 'approved date' if this
            // isn't approved currently.
            if($mform->elementExists('granted_ext_date')) {
                $mform->removeElement('granted_ext_date');
            }

        }

        $fileIcon = null;

        $extDocs = $this->get_extension_docs();

        if(is_array($extDocs)){
            $files = array();
            foreach($extDocs as $extDoc) {

                $icon = mimeinfo('icon',$extDoc->doc_url);
                $fileLink = "<a href='{$CFG->wwwroot}/user/u_file.php?id={$ext->user_id}&amp;file={$extDoc->doc_url}' ><img class='icon' src='$CFG->pixpath/f/$icon' alt='$icon'/>".basename($extDoc->doc_url)."</a>&nbsp;";
                //$fileIcon = "<a href='delete.php?cid={$course->id}&id={$extDoc->id}'>&nbsp;<img title='' src='{$CFG->pixpath}/t/delete.gif' class='iconsmall' alt='' /></a>";
                $files[] = $fileLink . $fileIcon;

            }
        }



    }

}