<?php

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once ($CFG->libdir . '/formslib.php');
require_once ($CFG->libdir . '/form/submit.php');

require_once ('form_global_add.php');

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