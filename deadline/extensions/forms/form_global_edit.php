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

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/form/submit.php');

require_once('form_global_add.php');

class form_global_edit extends form_global_add {

    protected $page_name = null;
    protected $save_destination = 'global';

    public function __construct($arg) {
        parent::__construct($arg);

        $this->page_name = get_string('ext_global_ext_edit', extensions_plugin::EXTENSIONS_LANG);

        global $COURSE;
        add_to_log($COURSE->id, "extensions", "viewing", "index.php", "viewing " . $this->page_name, $this->get_cmid());
    }

    public function definition_after_data() {
        parent::definition_after_data();

        global $CFG, $USER;
        $mform =& $this->_form;

        $mform->setDefault('header', get_string('ext_global_ext_edit', extensions_plugin::EXTENSIONS_LANG));

        $mform->setDefault('page', 'global_edit');

    }

}