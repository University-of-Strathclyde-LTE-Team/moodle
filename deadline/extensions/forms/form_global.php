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
 * This file contains a custom group element for showing a table of extensions
 * using standard Moodle forms functions and methods.
 *
 * @package   deadline_extensions
 * @copyright 2013 University of South Australia {@link http://www.unisa.edu.au}
 * @author    James McLean <james.mclean@unisa.edu.au>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

require_once('form_base.php');

class form_global extends form_base {

    protected $page_name = null;

    public function __construct($arg) {
        parent::__construct($arg);

        $this->page_name = get_string('ext_global_ext', extensions_plugin::EXTENSIONS_LANG);
        global $COURSE;
        add_to_log($COURSE->id, "extensions", "viewing", "index.php", "viewing " . $this->page_name, $this->get_cmid());
    }

    public function definition() {
        parent::definition();

        global $CFG, $COURSE, $USER;
        $mform =& $this->_form;

    }

    public function definition_after_data() {
        parent::definition_after_data();

        global $CFG, $COURSE, $USER;
        $mform =& $this->_form;

        $ext = new extensions_plugin();
        $table =  $ext->build_global_extensions_table($this->get_course());

        $mform->addElement('header', 'general', $this->page_name);
        $mform->addElement('extension_global', 'global_extensions', 'Global', $table);

    }

}