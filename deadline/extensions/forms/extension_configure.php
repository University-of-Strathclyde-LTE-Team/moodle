<?php

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



        if(!is_null($this->table_data)) {
            if(is_object($this->table_data)) {
                foreach($this->table_data as $key => $data) {
                    if($key == 'data') {
                        foreach($data as $key => $row) {
//                             var_dump($key, $row);

                            // For each row, add the options to enable/disable
                            // extensions for each item.

                            foreach($row->cells as $cell) {
                                if(strcmp($cell->text, '##ext_enabled##') == 0) {

                                    $thisItem = $key . '-enabled';

                                    $element = new HTML_QuickForm_select('enabled['.$key.']', null, Extensions::get_extension_enable_items());
                                    $element->setSelected(Extensions::get_extension_status_by_cmid($key));

                                    $this->_elements[$thisItem] = $element;
                                }

                                if(strcmp($cell->text, '##ext_cutoff##') == 0) {

                                    $thisItem = $key . '-cutoff';

                                    $element = new HTML_QuickForm_select('cutoff_date['.$key.']', null, Extensions::get_cutoff_options());
                                    $element->setSelected(Extensions::get_extension_cutoff_by_cmid($key));

                                    $this->_elements[$thisItem] = $element;

                                }

                            }
                        }
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
            return get_string("ext_none_exist", Extensions::LANG_EXTENSIONS);
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