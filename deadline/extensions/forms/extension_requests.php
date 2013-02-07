<?php

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

global $CFG;
require_once("HTML/QuickForm/element.php");
require_once($CFG->libdir . "/form/group.php");
require_once($CFG->libdir . "/formslib.php");
require_once('HTML/QuickForm/checkbox.php');

class MoodleQuickForm_extension_requests extends MoodleQuickForm_group {

    private $table_data = null;
    private $renderer   = null;

    public function MoodleQuickForm_extension_requests($elementName=null, $elementLabel=null, $table_data=null, $attributes=null, $showchoose=false) {

        // This is essentially just to pass in the seperator argument, this works
        // ok without it, but puts a big ugly space before the group itself.
        parent::MoodleQuickForm_group($elementName, $elementLabel, null, '');

        $this->HTML_QuickForm_element($elementName, $elementLabel, $attributes);
        $this->_persistantFreeze = true;
        $this->_appendName = true;
        $this->_type = 'extension_requests';

        if($table_data !== FALSE) {
            $this->set_table_data($table_data);
        }

        // Load the renderer for this form.
        $this->renderer = new HTML_QuickForm_Renderer_Default();

    }

    public function set_table_data($table_data = null) {

        if(!is_null($table_data)) {
            $this->table_data = $table_data;
            if(!$this->_createElements()) {
                error('Error creating table elements');
            }
        }

    }

    public function _createElements() {

        global $USER, $DB;

        if(is_null($this->table_data)) {
            return false;
        }

        if(!is_object($this->table_data)) {
            return false;
        }

        foreach($this->table_data as $key => $data) {
            if($key == 'data') {
                foreach($data as $key => $row) {
                    // var_dump($key, $row);

                    // So, this is a little hack. Just a little 'un.
                    if(strcmp($row->cells['10']->text, '{element}') == '0') {
                        // match found. Replace the string with the element.
                        $this->_elements[$key] = new HTML_QuickForm_checkbox($key, null, null);

                        // Any items that are already approved cannot be selected in this view.
                        if($DB->get_field('extensions', 'status', array('id' => $key)) == Extensions::STATUS_APPROVED) {
                            $this->_elements[$key]->removeAttribute('checked');
                            $this->_elements[$key]->updateAttributes(array('disabled'=>'disabled'));
                        }
                    }

                }
            }
        }

        return true;
    }

    public function toHtml() {

        parent::accept($this->renderer);

        foreach($this->_elements as $key => $data) {

            // Set the name of the group on the item
            $name = $this->getName();
            $elementName = $this->_elements[$key]->getName();
            $this->_elements[$key]->setName($name . '['. (strlen($elementName)? $elementName: $key) .']');

            // Generate the HTML element as added previously, and replace the text with the item.
            $this->table_data->data[$key]->cells['10']->text = $this->_elements[$key]->toHtml();
        }

        if(is_null($this->table_data)) {
            return get_string("ext_none_exist", Extensions::LANG_EXTENSIONS);
        } else {
            return html_writer::table($this->table_data, TRUE);
            //return print_table($this->table_data, TRUE);
        }
    }

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