<?php

if(file_exists($CFG->dirroot . '/deadline/deadlines/lib.php')) {
    require_once($CFG->dirroot . '/deadline/deadlines/lib.php');
}

class block_deadlines extends block_list {

    public $context = null;

    public function init() {

        // check to see if deadlines is installed and enabled.
        if(get_config('deadline_deadlines','enabled') != 1) {
            //return false;
        }

        $this->title = get_string('pluginname', 'block_deadlines');

    }

    function applicable_formats() {
        return array(
                'site'   => false,
                'course' => true
        );
    }

    function instance_allow_multiple() {
        return false;
    }

    public function get_content() {

        global $DB, $COURSE, $USER;

        if(get_config('deadline_deadlines','enabled') != 1) {
            return false;
        }

        if ($this->content !== NULL) {
            return $this->content;
        }

        // can only be used in course context.

        $this->content = new stdClass;
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        if(has_capability('deadline/extensions:approveextension', context_course::instance($COURSE->id))) {
            // staff
            $params = array(
                    'id' => $COURSE->id
            );
            $link_name = "Extension Requests";
            if(get_config('deadline_extensions','show_pending_count') == '1') {
                if($pending_count = extensions_plugin::get_pending_count_for_user($USER->id, $COURSE->id) > 0) {
                    $link_name .= " " . html_writer::tag('sup', html_writer::tag('i',$pending_count));
                }
            }
        } else if (has_capability('deadline/extensions:requestextension', context_course::instance($COURSE->id))) {
            // student
            $params = array(
                    'id' => $COURSE->id
            );
            $link_name = "Request Extension";
        }

        $extensions_url = new moodle_url('/deadline/extensions/', $params);
        $extensions_link = html_writer::link($extensions_url, $link_name);

        $this->content->items[] = $extensions_link;

        $deadlines = new deadlines_plugin();

        // Loop all deadlines we know about in this course.
//         if($activities = $deadlines->activity_detail()) {
            // display
            $link = new moodle_url('');
            $this->content->items[] = '23/12/2013 - Some Activity';

//         }

        return $this->content;
    }

}
