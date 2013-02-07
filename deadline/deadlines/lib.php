<?php

require_once($CFG->dirroot . '/deadline/lib.php');

class deadlines_plugin extends deadline_plugin {

    public function get_form_elements($mform, $context, $modulename = "") {

        if(!$this->module_supports_deadlines($modulename)) {
            return;
        }

        $mform->addElement('static','static', 'This is coming from the Deadlines module');

    }

    /**
     * hook to save extensions specific settings on a module settings page
     * @param object $data - data from an mform submission.
     */
    public function save_form_elements($data) {

    }

    /**
     * Hook for getting a deadline for a course module id
     * @param int $cmid
     *
     */
    public function get_deadline($cmid, $type) {

    }

    /**
     * hook for cron
     *
     */
    public function deadline_cron() {

    }


}
