<?php

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

global $CFG;

require_once("HTML/QuickForm/element.php");
require_once($CFG->libdir . "/form/group.php");
require_once($CFG->libdir . "/formslib.php");

class MoodleQuickForm_extension_global extends MoodleQuickForm_group {

    private $table_data = null;

    public function MoodleQuickForm_extension_global($elementName=null, $elementLabel=null, $table_data = null, $attributes=null, $showchoose=false) {

        // This is essentially just to pass in the seperator argument, this works
        // ok without it, but puts a big ugly space before the group itself.
        parent::MoodleQuickForm_group($elementName, $elementLabel, null, '');

        $this->HTML_QuickForm_element($elementName, $elementLabel, $attributes);
//        $this->setLabel('');
        $this->_persistantFreeze = true;
        $this->_appendName = true;
        $this->_type = 'global_extensions';

        if(!is_null($table_data)) {
            $this->table_data = $table_data;
        }

        $this->renderer = new HTML_QuickForm_Renderer_Default();

    }

    public function _createElements() {
        return true;
    }

    public function toHtml() {
        parent::accept($this->renderer);

        return html_writer::table($this->table_data);
    }

    function accept(&$renderer, $required = false, $error = null) {

        $renderer->_elementTemplates['empty'] = "<!-- BEGIN error --><span style=\"color: #ff0000\">{error}</span><br /><!-- END error -->\t{element}";

        $renderer->renderElement($this, $required, $error);
    }

    function getElementTemplateType() {
        return 'empty';
    }

}