<?php

if (! defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

class MoodleQuickForm_select_picker extends MoodleQuickForm_group {

    public $leftUsers    = array();
    public $rightUsers   = array();

    public $_elements    = array();

    private $renderer    = null;
    private $optionsSet  = null;
    private $setMultiple = false;

    public function MoodleQuickForm_select_picker($elementName=null, $elementLabel=null, $optgrps=null, $attributes=null, $showChoose=false) {

        parent::MoodleQuickForm_group($elementName, $elementLabel, null, '');

        $this->HTML_QuickForm_element($elementName, $elementLabel, $attributes);
        $this->_persistantFreeze = true;
        $this->_appendName = true;
        $this->_type = 'select_picker';

        $this->loadOptions(null, $optgrps);
    }

    public function loadOptions($mform = null, $optgrps = null) {

        if (isset($optgrps)) {
            if (is_null($optgrps)) {
                $this->optionsSet = false;
            }

            if (isset($optgrps['left'])) {
                $this->leftUsers = $optgrps['left'];
                $this->optionsSet = true;

                if ($leftList = $this->getElement('left_list')) {
                    $leftList->loadArray($this->leftUsers);
                }
            }

            if (isset($optgrps['right'])) {
                $this->rightUsers = $optgrps['right'];
                $this->optionsSet = true;

                if ($rightList = $this->getElement('right_list')) {
                    $rightList->loadArray($this->rightUsers);
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
            $elementName = $this->_elements[$key]->getName();
            if ($index == $elementName) {
                return $this->_elements[$key];
                break;
            }
        }

        return false;
    }

    public function set_multiple($setMultiple = false) {

        $this->setMultiple = $setMultiple;

        // set multiple on the left item
        $leftBox = $this->getElement('left_list');
        $leftBox->setMultiple($this->setMultiple);

        // set multiple on the right item
        $rightBox = $this->getElement('right_list');
        $rightBox->setMultiple($this->setMultiple);
    }

    public function _createElements() {
        $rows = 15;

        // Left select area.
        $this->_elements[] = @MoodleQuickForm::createElement('static', 'static', null, '<td>');
        $attr = 'size="' . $rows . '" style="width: 250px;" onDblClick="M.deadline_extensions.opt.transferRight()"';
//         $leftList = @MoodleQuickForm::createElement('select', 'left_list', null, null, $attr);
        $this->_elements[] = @MoodleQuickForm::createElement('select', 'left_list', null, null, $attr);
        $this->_elements[] = @MoodleQuickForm::createElement('static', 'static', null, '</td><td>');

        // Buttons to move items left/right
        $attr = 'onClick="M.deadline_extensions.opt.transferLeft()" name="left"';
        $this->_elements[] = @MoodleQuickForm::createElement('button','right', "<-", $attr, null);
        $this->_elements[] = @MoodleQuickForm::createElement('static', 'static', null, '<br /><br /><br /><br /><br /><br />');

        $attr = 'onClick="M.deadline_extensions.opt.transferRight()" name="right"';
        $this->_elements[] = @MoodleQuickForm::createElement('button','left', "->", $attr, null);
        $this->_elements[] = @MoodleQuickForm::createElement('static', 'static', null, '</td><td>');

        // Right select area.
        $attr = 'size="' . $rows . '" style="width: 250px;" onDblClick="M.deadline_extensions.opt.transferLeft()"';
//         $rightList = @MoodleQuickForm::createElement('select','right_list', null, null, $attr);
        $this->_elements[] = @MoodleQuickForm::createElement('select','right_list', null, null, $attr);
        $this->_elements[] = @MoodleQuickForm::createElement('static', 'static', null, '</td>');

        // Hidden fields to store the content of the left/right areas in the form for submission
        $this->_elements[] = @MoodleQuickForm::createElement('hidden','leftContents', null, 'id="id_' . $this->_name . '_leftContents"', null);
        $this->_elements[] = @MoodleQuickForm::createElement('hidden','rightContents', null, 'id="id_' . $this->_name . '_rightContents"', null);

        // Strip the labels.
        foreach ($this->_elements as $element){
            if (method_exists($element, 'setHiddenLabel')){
                $element->setHiddenLabel(true);
            }
        }
    }

    public function onQuickFormEvent($event, $arg, &$caller) {
        switch ($event) {
            case 'createElement':
                //$caller->disabledIf($arg[0], $arg[0].'[off]', 'checked');
                //$caller->addRule($arg[0],'Please Select an Option', 'required', null, 'client');
                return parent::onQuickFormEvent($event, $arg, $caller);
                break;
            default:
                return parent::onQuickFormEvent($event, $arg, $caller);
        }
    }

    function toHtml() {
        global $CFG, $PAGE;

        // No options set. Just return the string and don't render select picker
        if (!$this->optionsSet) {
            return get_string("no_options_set", extensions_plugin::EXTENSIONS_LANG);
        }

        if (isset($this->setMultiple) && $this->setMultiple === true) {
            $multipleStr = '[]';
        } else {
            $multipleStr  = '';
        }

        $options = array(
                $this->_name . '[left_list]' . $multipleStr,
                $this->_name . '[right_list]' . $multipleStr,
                "id_{$this->_name}_leftContents",
                "id_{$this->_name}_rightContents"
        );

        $PAGE->requires->js(extensions_plugin::EXTENSIONS_URL_PATH . '/assets/js/select_picker.js');
        $PAGE->requires->js_init_call('M.deadline_extensions.init_select_picker', $options, true);

        include_once('HTML/QuickForm/Renderer/Default.php');
        $renderer = new HTML_QuickForm_Renderer_Default();
        $renderer->setElementTemplate('{element}');
        parent::accept($renderer);

        $tableHeader  = "<table>\n";
        $tableHeader .= "<tr>";
        $tableHeader .= "<th>" . get_string("selected_options", extensions_plugin::EXTENSIONS_LANG) . "</th>";
        $tableHeader .= "<td>&nbsp;</td>";
        $tableHeader .= "<th>" . get_string("available_options", extensions_plugin::EXTENSIONS_LANG) . "</th>";
        $tableHeader .= "</tr>\n";
        $tableHeader .= "<tr>";

        $tableFooter  = "</tr>\n";
        $tableFooter .= "</table>";

        return $tableHeader . $renderer->toHtml() . $tableFooter;
    }

    function accept(&$renderer, $required = false, $error = null) {
        $renderer->renderElement($this, $required, $error);
    }

}
