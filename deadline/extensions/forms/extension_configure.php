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
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

global $CFG;
require_once("HTML/QuickForm/element.php");
require_once($CFG->libdir . "/form/group.php");
require_once($CFG->libdir . "/formslib.php");
require_once('HTML/QuickForm/checkbox.php');
require_once('HTML/QuickForm/select.php');

class MoodleQuickForm_extension_configure extends MoodleQuickForm_group {

    private $table_data = null;
    private $renderer   = null;

    public function MoodleQuickForm_extension_configure($elementName=null, $elementLabel=null, $table_data=null, $attributes=null, $showchoose=false) {

        // This is essentially just to pass in the seperator argument, this works
        // ok without it, but puts a big ugly space before the group itself.
        parent::MoodleQuickForm_group($elementName, $elementLabel, null, '');

        $this->HTML_QuickForm_element($elementName, $elementLabel, $attributes);
        //        $this->setLabel('');
        $this->_persistantFreeze = true;
//         $this->_appendName = true;
        $this->_appendName = false;
        $this->_type = 'extension_configure';

        if($table_data !== FALSE) {
            $this->set_table_data($table_data);
        }

        $this->renderer = new HTML_QuickForm_Renderer_Default();
    }

    public function set_table_data($table_data = null) {

        if(!is_null($table_data)) {
            $this->table_data = $table_data;
            $this->_createElements();
        }

    }

    public function _createElements() {

        global $USER;

        if(is_null($this->table_data)) {
            return null;
        }

        if(!is_object($this->table_data)) {
            return null;
        }

        foreach($this->table_data as $key => $data) {

            if($key != 'data') {
                continue;
            }

            foreach($data as $key => $row) {
                // For each row, add the options to enable/disable
                // extensions for each item.

                foreach($row->cells as $cell) {
                    if(strcmp($cell->text, '##ext_enabled##') == 0) {

                        $thisItem = $key . '-enabled';

                        $element = new HTML_QuickForm_select('enabled['.$key.']', null, extensions_plugin::get_extension_enable_items());

                        if(get_config('deadline_extensions','force_extension_enabled') == '1') {
                            $element->setSelected(1);
                            $element->freeze();
                        } else {
                            $element->setSelected(extensions_plugin::extensions_enabled_cmid($cm_id));
                        }

                        $this->_elements[$thisItem] = $element;
                    }

                    if(strcmp($cell->text, '##ext_cutoff##') == 0) {

                        $thisItem = $key . '-cutoff';

                        $element = new HTML_QuickForm_select('cutoff_date['.$key.']', null, extensions_plugin::get_cutoff_options());
                        if(get_config('deadline_extensions', 'req_cut_off') == '-1') {
                            $element->setSelected(get_config('deadline_extensions', 'req_cut_off'));
                            $element->freeze();
                        } else {
                            $element->setSelected(extensions_plugin::get_extension_cutoff_by_cmid($key));
                        }

                        $this->_elements[$thisItem] = $element;

                    }
                }
            }
        }

        return true;
    }

    //---

    public function toHtml() {

        parent::accept($this->renderer);

        foreach($this->_elements as $key => $data) {

            // Generate the HTML element as added previously, and replace the text with the item.
            if(preg_match('#-enabled$#', $key)) {
                $itemId = str_replace("-enabled", '', $key);
                $this->table_data->data[$itemId]->cells['1']->text = $this->_elements[$key]->toHtml();
            }

            if(preg_match('#-cutoff$#', $key)) {
                $itemId = str_replace("-cutoff", '', $key);
                $this->table_data->data[$itemId]->cells['2']->text = $this->_elements[$key]->toHtml();
            }

        }

        if(is_null($this->table_data)) {
            return get_string("ext_none_exist", extensions_plugin::EXTENSIONS_LANG);
        } else {
            return html_writer::table($this->table_data, TRUE);
            //return print_table($this->table_data, TRUE);
        }
    }

    //---

    public function get_table_data() {
        return $this->table_data;
    }

    function accept(&$renderer, $required = false, $error = null) {

        // Add the custom template to the renderer for use.
        $renderer->_elementTemplates['empty'] = "<!-- BEGIN error --><span style=\"color: #ff0000\">{error}</span><br /><!-- END error -->\t{element}";

        $renderer->renderElement($this, $required, $error, $this->table_data);
    }

    function getElementTemplateType() {
        return 'empty';
    }

}