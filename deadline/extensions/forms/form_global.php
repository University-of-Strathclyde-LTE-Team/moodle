<?php

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
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

        //---------------

        $ext = new extensions_plugin();

        $mform->addElement('header','general', $this->page_name);
        $mform->addElement('extension_global', 'global_extensions', 'Global', $ext->build_global_extensions_table($this->get_course()));

    }

}