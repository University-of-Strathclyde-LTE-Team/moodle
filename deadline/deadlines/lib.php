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
 * This file is the main library of methods as used by the deadline_deadlines
 * plugin
 *
 * @package   deadline_deadlines
 * @copyright 2013 University of South Australia {@link http://www.unisa.edu.au}
 * @author    James McLean <james.mclean@unisa.edu.au>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot . '/deadline/lib.php');

class deadlines_plugin extends deadline_plugin {

    const DEADLINES_LANG     = 'deadline_deadlines';
    const DEADLINES_MOD_NAME = 'deadline_deadlines';
    const DEADLINES_URL_PATH = '/deadline/deadlines';

    public function get_form_elements($mform, $context, $modulename = "", $cm_id = null) {
        return;
    }

    /**
     * hook to save extensions specific settings on a module settings page
     * @param object $data - data from an mform submission.
     */
    public function save_form_elements($data) {

        return true;
    }

    protected function delete_cmid($cmid) {
        global $DB;

        if (!$DB->delete_records('deadline_deadlines', array('cmid' => $cmid))) {
            print_error('cannotdeletedeadlines', '', course_get_url($data->course, $data->section), $data->modulename);
        }

        return true;
    }

    /**
     * Hook for getting a deadline for a course module id
     * @param int $cmid
     *
     */
    public function get_deadline_date_deadline($cm_id) {

        if (is_null($cm_id)) {
            return 0;
        }

        return $this->get_deadline_field($cm_id, 'date_deadline');
    }

    public function get_deadline_date_open($cm_id) {

        if (is_null($cm_id)) {
            return 0;
        }

        return $this->get_deadline_field($cm_id, 'date_open');
    }

    public function get_deadline_date_cutoff($cm_id) {

        if (is_null($cm_id)) {
            return 0;
        }

        return $this->get_deadline_field($cm_id, 'date_cutoff');
    }

    public function get_deadline_timelimit($cm_id) {

        if (is_null($cm_id)) {
            return 0;
        }

        return $this->get_deadline_field($cm_id, 'timelimit');
    }

    public function get_deadline_attempts($cm_id) {

        if (is_null($cm_id)) {
            return 0;
        }

        return $this->get_deadline_field($cm_id, 'attempts');
    }

    public function get_deadline_field($cm_id, $field = 'date_deadline') {
        global $DB;

        $params = array(
                'cm_id' => $cm_id
        );

        if ($DB->record_exists('deadline_deadlines', $params)) {
            return $DB->get_field('deadline_deadlines', $field, $params);
        }
    }

    private function get_module_fields($modname) {

        // Sanity check the name.
        if (preg_match('#^mod_#', $modname)) {
            $modname = str_replace('mod_', '', $modname);
        }

        switch($modname) {
            case 'assign':

                $fields = new stdClass;
                $fields->date_open     = 'allowsubmissionsfromdate';
                $fields->date_deadline = 'duedate';
                $fields->date_cutoff   = 'cutoffdate';
                return $fields;

                break;
            case 'assignment':

                $fields = new stdClass;
                $fields->date_open     = 'timeavailable';
                $fields->date_deadline = 'timedue';
                $fields->date_cutoff   =  null;
                return $fields;

                break;
            case 'quiz':

                $fields = new stdClass;
                $fields->date_open     = 'timeopen';
                $fields->date_deadline = 'timeclose';
                $fields->date_cutoff   =  null;
                $fields->timelimit     = 'timelimit';
                return $fields;

                break;
            case 'choice':

                $fields = new stdClass;
                $fields->date_open     = 'timeopen';
                $fields->date_deadline = 'timeclose';
                $fields->date_cutoff   =  null;
                return $fields;

                break;
            case 'forum':

                $fields = new stdClass;
                $fields->date_open     = 'assesstimestart';
                $fields->date_deadline = 'assesstimefinish';
                $fields->date_cutoff   =  null;
                return $fields;

                break;
            case 'lesson':

                $fields = new stdClass;
                $fields->date_open     = 'available';
                $fields->date_deadline = 'deadline';
                $fields->date_cutoff   =  null;
                return $fields;

                break;
            case 'scorm':

                $fields = new stdClass;
                $fields->date_open     = 'timeopen';
                $fields->date_deadline = 'timeclose';
                $fields->date_cutoff   =  null;
                return $fields;

                break;
            case 'workshop':

                $fields = new stdClass;
                $fields->date_open     = 'assessmentstart';
                $fields->date_deadline = 'assessmentend';
                $fields->date_cutoff   =  null;
                return $fields;

                break;
        }

        return false;
    }

    public function save_deadlines($data, $modulename) {
        global $DB;

        // Make sure the course module id exists already.
        if (!$DB->record_exists('course_modules', array('id' => $data->coursemodule))) {
            print_error('cannotadddeadline', '', course_get_url($data->course, $data->section), $data->modulename);
        }

        if ($field_name = $this->get_module_fields($modulename)) {

            if (isset($data->{$field_name->date_open})) {
                $this->set_open_date($data->coursemodule, $data->{$field_name->date_open});
            }

            if (isset($data->{$field_name->date_deadline})) {
                $this->set_due_date($data->coursemodule, $data->{$field_name->date_deadline});
            }

            if (isset($data->{$field_name->date_cutoff})) {
                $this->set_cut_off_date($data->coursemodule, $data->{$field_name->date_cutoff});
            }

            if (isset($data->{$field_name->timelimit})) {
                $this->set_timelimit($data->coursemodule, $data->{$field_name->timelimit});
            }

        } else {
            print_error('fieldsnotfound', '', course_get_url($data->course, $data->section), $data->modulename);
        }

        return true;
    }

    public function get_deadlines_for_cmid($cm_id = null, $user_id = null) {

        if (is_null($cm_id)) {
            return false;
        }

        $data                = new stdClass();
        $data->date_open     = $this->get_open_date($cm_id, $user_id);
        $data->date_deadline = $this->get_due_date($cm_id, $user_id);
        $data->date_cutoff   = $this->get_cut_off_date($cm_id, $user_id);
        $data->timelimit     = $this->get_timelimit($cm_id, $user_id);
        // Insert attempts here when implemented.

        return $data;
    }

    public function get_deadline_dates($data, $modulename, $user_id = null) {

        // On edit the first call of this does not have a cm_id set.
        // Just do nothing, as there will be nothing to return anyway.
        if (!isset($data->cm_id)) {
            return $data;
        }

        switch($modulename) {
            case 'assign':
                $data->allowsubmissionsfromdate = $this->get_open_date($data->cm_id, $user_id);
                $data->duedate                  = $this->get_due_date($data->cm_id, $user_id);
                $data->cutoffdate               = $this->get_cut_off_date($data->cm_id, $user_id);

                $data->deadlines                = true;
                break;

            case 'quiz':
                $data->timeopen                 = $this->get_open_date($data->cm_id, $user_id);
                $data->timeclose                = $this->get_due_date($data->cm_id, $user_id);
                $data->timelimit                = $this->get_timelimit($data->cm_id, $user_id);
                // To be implemented later.
                // $data->attempts
                // $data->password
                // $data->extrapasswords

                $data->deadlines                = true;
                break;
        }

        return $data;
    }

    // Function to allow THIS plugin to get a due date for a specific course
    // module ID and user ID.
    public function get_my_due_date($cm_id, $user_id) {
        global $DB;

        // As this is the 'deadlines' plugin, the deadlines here should be
        // considered to be 'global' hence the $user_id being ignored here.

        $table = 'deadline_deadlines';

        $params = array(
            'cm_id' => $cm_id
        );

        if ($DB->record_exists($table, $params)) {
            $record = $DB->get_record($table, $params, '*', MUST_EXIST);

            return $record->date_deadline;
        } else {
            return false;
        }
    }

    public function get_my_open_date($cm_id, $user_id = null) {
        global $DB;

        // As this is the 'deadlines' plugin, the deadlines here should be
        // considered to be 'global' hence the $user_id being ignored here.

        $params = array(
                'cm_id' => $cm_id
        );

        if ($DB->record_exists('deadline_deadlines', $params)) {
            $record = $DB->get_record('deadline_deadlines', $params, '*', MUST_EXIST);

            return $record->date_open;
        } else {
            return false;
        }
    }

    public function get_my_cutoff_date($cm_id, $user_id = null) {
        global $DB;

        // As this is the 'deadlines' plugin, the deadlines here should be
        // considered to be 'global' hence the $user_id being ignored here.

        $params = array(
                'cm_id' => $cm_id
        );

        if ($DB->record_exists('deadline_deadlines', $params)) {
            $record = $DB->get_record('deadline_deadlines', $params, '*', MUST_EXIST);

            return $record->date_cutoff;
        } else {
            return false;
        }
    }

    public static function get_deadline_id_by_cmid($cm_id = null) {

        global $DB;

        if (is_null($cm_id)) {
            return false;
        }

        $params = array(
                'cm_id' => $cm_id
        );

        return $DB->get_field('deadline_deadlines', 'id', $params, MUST_EXIST);
    }

    public function get_my_timelimit($cm_id, $user_id = null) {
            global $DB;

        // As this is the 'deadlines' plugin, the deadlines here should be
        // considered to be 'global' hence the $user_id being ignored here.

        $params = array(
                'cm_id' => $cm_id
        );

        if ($DB->record_exists('deadline_deadlines', $params)) {
            $record = $DB->get_record('deadline_deadlines', $params, '*', MUST_EXIST);

            return $record->timelimit;
        } else {
            return false;
        }
    }

}
