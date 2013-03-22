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
    die('Direct access to this script is forbidden.');    // It must be included from a Moodle page.
}

global $CFG;

require_once("HTML/QuickForm/element.php");
require_once($CFG->libdir . "/form/group.php");
require_once($CFG->libdir . "/formslib.php");

class MoodleQuickForm_extension_global extends MoodleQuickForm_group {

    private $table_data = null;

    public function MoodleQuickForm_extension_global($element_name=null, $element_label=null, $table_data = null, $attributes=null, $showchoose=false) {

        // This is essentially just to pass in the seperator argument, this works
        // ok without it, but puts a big ugly space before the group itself.
        parent::__construct($element_name, $element_label, null, '');

        $this->HTML_QuickForm_element($element_name, $element_label, $attributes);
        // $this->setLabel('');
        $this->_persistantFreeze = true;
        $this->_appendName = true;
        $this->_type = 'global_extensions';

        if (!is_null($table_data)) {
            $this->table_data = $table_data;
        }

        $this->renderer = new HTML_QuickForm_Renderer_Default();

    }

    public function _create_elements() {
        return true;
    }

    public function toHtml() {
        parent::accept($this->renderer);

        return html_writer::table($this->table_data);
    }

    public function accept(&$renderer, $required = false, $error = null) {

        $renderer->_elementTemplates['empty'] = "<!-- BEGIN error --><span style=\"color: #ff0000\">{error}</span><br /><!-- END error -->\t{element}";

        $renderer->renderElement($this, $required, $error);
    }

    public function getElementTemplateType() {
        return 'empty';
    }

}