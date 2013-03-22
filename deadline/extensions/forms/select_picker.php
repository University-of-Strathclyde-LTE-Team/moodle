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

if (! defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    // It must be included from a Moodle page.
}

class MoodleQuickForm_select_picker extends MoodleQuickForm_group {

    public $left_users   = array();
    public $right_users  = array();

    public $_elements    = array();

    private $renderer    = null;
    private $options_set  = null;
    private $set_multiple = false;

    public function MoodleQuickForm_select_picker($element_name=null, $element_label=null, $optgrps=null, $attributes=null, $show_choose=false) {

        parent::__construct($element_name, $element_label, null, '');

        $this->HTML_QuickForm_element($element_name, $element_label, $attributes);
        $this->_persistantFreeze = true;
        $this->_appendName = true;
        $this->_type = 'select_picker';

        $this->loadOptions(null, $optgrps);
    }

    public function loadOptions($mform = null, $optgrps = null) {

        if (isset($optgrps)) {
            if (is_null($optgrps)) {
                $this->options_set = false;
            }

            if (isset($optgrps['left'])) {
                $this->left_users = $optgrps['left'];
                $this->options_set = true;

                if ($left_list = $this->getElement('left_list')) {
                    $left_list->loadArray($this->left_users);
                }
            }

            if (isset($optgrps['right'])) {
                $this->right_users = $optgrps['right'];
                $this->options_set = true;

                if ($right_list = $this->getElement('right_list')) {
                    $right_list->loadArray($this->right_users);
                }
            }
        }
    }

    // Get a single element from the Group.
    public function getElement($index = null) {

        if (is_null($index)) {
            return false;
        }

        foreach (array_keys($this->_elements) as $key) {
            $element_name = $this->_elements[$key]->getName();
            if ($index == $element_name) {
                return $this->_elements[$key];
                break;
            }
        }

        return false;
    }

    public function set_multiple($set_multiple = false) {

        $this->set_multiple = $set_multiple;

        // Set multiple on the left item
        $left_box = $this->getElement('left_list');
        $left_box->setMultiple($this->set_multiple);

        // Set multiple on the right item
        $right_box = $this->getElement('right_list');
        $right_box->setMultiple($this->set_multiple);
    }

    public function _createElements() {
        $rows = 15;

        // Left select area.
        $this->_elements[] = @MoodleQuickForm::createElement('static', 'static', null, '<td>');
        $attr = 'size="' . $rows . '" style="width: 250px;" onDblClick="M.deadline_extensions.opt.transferRight()"';
        // $left_list = @MoodleQuickForm::createElement('select', 'left_list', null, null, $attr);
        $this->_elements[] = @MoodleQuickForm::createElement('select', 'left_list', null, null, $attr);
        $this->_elements[] = @MoodleQuickForm::createElement('static', 'static', null, '</td><td>');

        // Buttons to move items left/right
        $attr = 'onClick="M.deadline_extensions.opt.transferLeft()" name="left"';
        $this->_elements[] = @MoodleQuickForm::createElement('button', 'right', "<-", $attr, null);
        $this->_elements[] = @MoodleQuickForm::createElement('static', 'static', null, '<br /><br /><br /><br /><br /><br />');

        $attr = 'onClick="M.deadline_extensions.opt.transferRight()" name="right"';
        $this->_elements[] = @MoodleQuickForm::createElement('button', 'left', "->", $attr, null);
        $this->_elements[] = @MoodleQuickForm::createElement('static', 'static', null, '</td><td>');

        // Right select area.
        $attr = 'size="' . $rows . '" style="width: 250px;" onDblClick="M.deadline_extensions.opt.transferLeft()"';
        // $right_list = @MoodleQuickForm::createElement('select', 'right_list', null, null, $attr);
        $this->_elements[] = @MoodleQuickForm::createElement('select', 'right_list', null, null, $attr);
        $this->_elements[] = @MoodleQuickForm::createElement('static', 'static', null, '</td>');

        // Hidden fields to store the content of the left/right areas in the form for submission
        $this->_elements[] = @MoodleQuickForm::createElement('hidden', 'leftContents', null, 'id="id_' . $this->_name . '_leftContents"', null);
        $this->_elements[] = @MoodleQuickForm::createElement('hidden', 'rightContents', null, 'id="id_' . $this->_name . '_rightContents"', null);

        // Strip the labels.
        foreach ($this->_elements as $element) {
            if (method_exists($element, 'setHiddenLabel')) {
                $element->setHiddenLabel(true);
            }
        }
    }

    public function onQuickFormEvent($event, $arg, &$caller) {
        switch ($event) {
            case 'createElement':
                // $caller->disabledif ($arg[0], $arg[0].'[off]', 'checked');
                // $caller->addRule($arg[0], 'Please Select an Option', 'required', null, 'client');
                return parent::onQuickFormEvent($event, $arg, $caller);
                break;
            default:
                return parent::onQuickFormEvent($event, $arg, $caller);
        }
    }

    public function toHtml() {
        global $CFG, $PAGE;

        // No options set. Just return the string and don't render select picker
        if (!$this->options_set) {
            return get_string("no_options_set", extensions_plugin::EXTENSIONS_LANG);
        }

        if (isset($this->set_multiple) && $this->set_multiple === true) {
            $multiple_str = '[]';
        } else {
            $multiple_str  = '';
        }

        $options = array(
                $this->_name . '[left_list]' . $multiple_str,
                $this->_name . '[right_list]' . $multiple_str,
                "id_{$this->_name}_leftContents",
                "id_{$this->_name}_rightContents"
        );

        $PAGE->requires->js(extensions_plugin::EXTENSIONS_URL_PATH . '/assets/js/select_picker.js');
        $PAGE->requires->js(extensions_plugin::EXTENSIONS_URL_PATH . '/extensions.js');
        $PAGE->requires->js_init_call('M.deadline_extensions.init_select_picker', $options, true);

        include_once('HTML/QuickForm/Renderer/Default.php');
        $renderer = new HTML_QuickForm_Renderer_Default();
        $renderer->setElementTemplate('{element}');
        parent::accept($renderer);

        $table_header  = "<table>\n";
        $table_header .= "<tr>";
        $table_header .= "<th>" . get_string("selected_options", extensions_plugin::EXTENSIONS_LANG) . "</th>";
        $table_header .= "<td>&nbsp;</td>";
        $table_header .= "<th>" . get_string("available_options", extensions_plugin::EXTENSIONS_LANG) . "</th>";
        $table_header .= "</tr>\n";
        $table_header .= "<tr>";

        $table_footer  = "</tr>\n";
        $table_footer .= "</table>";

        return $table_header . $renderer->toHtml() . $table_footer;
    }

    public function accept(&$renderer, $required = false, $error = null) {
        $renderer->renderElement($this, $required, $error);
    }

}
