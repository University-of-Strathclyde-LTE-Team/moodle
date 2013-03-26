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
 * This file contains the base class for any deadline plugins. All deadline
 * plugins must extend this class.
 *
 * @package   deadline
 * @copyright 2013 University of South Australia {@link http://www.unisa.edu.au}
 * @author    James McLean <james.mclean@unisa.edu.au>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    //  It must be included from a Moodle page
}

abstract class deadline_plugin {

    /**
     * hook to add deadline specific settings to a module settings page.
     *
     * @param object $mform  - Moodle form
     * @param object $context - current context
     * @param string $modulename - Name of the module
     */
    abstract public function get_form_elements($mform, $context, $modulename = "");

    /**
     * hook to save extensions specific settings on a module settings page.
     *
     * @param object $data - data from an mform submission.
     */
    abstract public function save_form_elements($data);

    /**
     * Abstract function to allow an activity to have it's own open
     * date specific to a user.
     *
     * @param int $cm_id Course Module ID
     * @param int $user_id User ID.
     */
    abstract public function get_my_open_date($cm_id, $user_id);

    /**
     * Abstract function to allow a an activity to check with Deadline if there
     * is a deadline specific to this user.
     *
     * @param int $cm_id Course Module ID to be checked.
     * @param int $user_id User ID to be checked.
     */
    abstract public function get_my_due_date($cm_id, $user_id);

    /**
     * Abstracti function to allow plugins to return a specific cutoff date for
     * and activity and User.
     *
     * @param int $cm_id Course Module ID for this activity.
     * @param int $user_id User to pass through to plugin for checking.
     */
    abstract public function get_my_cutoff_date($cm_id, $user_id);

    /**
     * Abstract function to get a specific time limit extension for an Activity and User.
     * Only used by Quiz module for now.
     *
     * @param int $cm_id Course Module ID to be checked.
     * @param int $user_id User ID to be checked.
     */
    abstract public function get_my_timelimit($cm_id, $user_id);

    /**
     * Abstract function to check if a deadline plugin has items to delete when
     * a Course Module is deleted.
     *
     * @param int $cmid Course Module to delete.
     */
    abstract protected function delete_cmid($cmid);

    /**
     * Method checks if a specific module supports Deadline functionality.
     *
     * @param string $modname Module to check for deadline support.
     */
    public final function activity_supports_deadlines($modname) {

        if (preg_match('#^mod_#', $modname)) {
            $modname = str_replace('mod_', '', $modname);
        }

        return plugin_supports('mod', $modname, FEATURE_DEADLINE);
    }

    /**
     * Method to determine if Extensions deadline plugin is installed and enabled.
     *
     * @return bool True if extensions is enabled. False if it is not enabled.
     */
    public function extensions_installed() {
        global $CFG;

        if (!file_exists($CFG->dirroot . '/deadline/extensions/lib.php')) {
            return false;
        }

        if (array_key_exists('extensions', $this->get_installed_plugins())) {
            if (extensions_plugin::is_enabled()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Private internal function used to check to see if an activity has a deadline
     * saved with the Deadlines module.
     *
     * @param int $cm_id Activity to check for deadline existance.
     */
    public function deadline_exists($cm_id = null) {
        global $DB;

        $options = array(
            'cm_id' => $cm_id
        );

        if ($DB->record_exists('deadline_deadlines', $options)) {
            return $DB->get_field('deadline_deadlines', 'id', $options, MUST_EXIST);
        } else {
            return false;
        }
    }

    /**
     * Add a record to the deadlines database table for a course module ID.
     *
     * @param int $cm_id Course Module ID to add the record for.
     */
    public function create_deadline_record($cm_id) {

        global $DB;

        if (!$this->deadline_exists($cm_id)) {
            $params = new stdClass();
            $params->cm_id = $cm_id;
            return $DB->insert_record('deadline_deadlines', $params, true);
        }

    }

    /**
     * Get the activity detail based on it's course module ID.
     * @param int $cm_id Course Module ID.
     * @return stdClass
     */
    public function activity_detail($cm_id = null) {

        $detail = get_coursemodule_from_id(null, $cm_id, 0, false, MUST_EXIST);
        return $detail;
    }

    /**
     * Get a list of plugins that the deadline module knows about
     *
     * @return array $plugins Array of plugins that the deadline module knows about.
     */
    private function get_installed_plugins() {
        return get_plugin_list('deadline');
    }

    // --------------------------------------------------------

    /**
     * Core function to get the open date for a specific course module ID and user.
     *
     * @param int $cm_id Course Module ID to check for.
     * @param int $user_id User ID to check for.
     */
    public function get_open_date($cm_id, $user_id) {
        global $DB, $CFG;

        $plugins = $this->get_installed_plugins();

        if (!$plugins = $this->get_installed_plugins()) {
            return 0;
        }

        $dates = array();

        // check the installed plugins for due dates.
        foreach ($plugins as $plugin => $path) {

            $plugin_code = $CFG->dirroot . '/deadline/' . $plugin . '/lib.php';

            if (file_exists($plugin_code)) {
                require_once($plugin_code);
            } else {
                // Skip this plugin. It's either some kind of ghost, or it's
                // totally broken!
                continue;
            }

            $plugin_class = $plugin . '_plugin';
            $this_plugin = new $plugin_class;

            if (method_exists($this_plugin, 'get_my_open_date')) {
                // Call the function from the plugin which will find the dates
                // that apply to this student.
                $dates[$plugin] = new stdClass;
                $dates[$plugin]->date_open = (int)$this_plugin->get_my_open_date($cm_id, $user_id);
            }

        }

        // find the longest date they have, as they may have multiple extensions
        // - individual
        // - global
        // - group
        return $this->get_longest_date($dates, 'date_open');
    }

    /**
     * Set the open date for a specific course module id into the deadlines table.
     *
     * @param int $cm_id Course Module ID to save the date for.
     * @param int $date Date to set as the open date.
     */
    public function set_open_date($cm_id, $date) {

        global $DB;

        if (!$deadline_id = $this->deadline_exists($cm_id)) {
            print_error('cannotadddeadline', '', course_get_url($data->course, $data->section), $data->modulename);
        }

        $data            = new stdClass();
        $data->id        = $deadline_id;
        $data->date_open = $date;

        if (!$DB->update_record('deadline_deadlines', $data)) {
            print_error('cannotupdatedeadline', '', course_get_url($data->course, $data->section), $data->modulename);
        }
    }

    // --------------------------------------------------------

    /**
     * Get each due date from all installed deadline plugins, and determine the
     * specific date to return. Usually this will be the longest date.
     *
     * @param int $cm_id Course Module ID to check the date for.
     * @param int $user_id User ID to check for.
     */
    public function get_due_date($cm_id, $user_id) {
        global $DB, $CFG;

        if (!$plugins = $this->get_installed_plugins()) {
            return 0;
        }

        $dates = array();

        // check the installed plugins for due dates.
        foreach ($plugins as $plugin => $path) {

            $plugin_code = $CFG->dirroot . '/deadline/' . $plugin . '/lib.php';

            if (file_exists($plugin_code)) {
                require_once($plugin_code);
            } else {
                // Skip this plugin. It's either some kind of ghost, or it's
                // totally broken!
                continue;
            }

            $plugin_class = $plugin . '_plugin';
            $this_plugin = new $plugin_class;

            if (method_exists($this_plugin, 'get_my_due_date')) {
                // Call the function from the plugin which will find the dates
                // that apply to this student.
                $dates[$plugin] = new stdClass;
                $dates[$plugin]->date_deadline = (int)$this_plugin->get_my_due_date($cm_id, $user_id);
            }

        }

        // find the longest date they have, as they may have multiple extensions
        // - individual
        // - global
        // - group
        return $this->get_longest_date($dates, 'date_deadline');
    }

    /**
     * Set the due date for a specific activity in the deadlines module.
     *
     * @param int $cm_id Course Module ID to set the date for.
     * @param int $date Date to set the date for.
     */
    public function set_due_date($cm_id, $date) {
        global $DB;

        if (!$deadline_id = $this->deadline_exists($cm_id)) {
            print_error('cannotadddeadline', '', course_get_url($data->course, $data->section), $data->modulename);
        }

        $data                = new stdClass();
        $data->id            = $deadline_id;
        $data->date_deadline = $date;

        if (!$DB->update_record('deadline_deadlines', $data)) {
            print_error('cannotupdatedeadline', '', course_get_url($data->course, $data->section), $data->modulename);
        }

    }

    // --------------------------------------------------------

    /**
     * Get the cutoff date from each deadlines plugin and then return the date
     * furthest in the future.
     *
     * @param int $cm_id Course Module ID
     * @param int $user_id User ID that this query relates to.
     */
    public function get_cut_off_date($cm_id, $user_id) {
        global $CFG;

        $plugins = $this->get_installed_plugins();

        $dates = array();

        // check the installed plugins for due dates.
        foreach ($plugins as $plugin => $path) {

            $plugin_code = $CFG->dirroot . '/deadline/' . $plugin . '/lib.php';

            if (file_exists($plugin_code)) {
                require_once($plugin_code);
            } else {
                // Skip this plugin. It's either some kind of ghost, or it's
                // totally broken!
                continue;
            }

            $plugin_class = $plugin . '_plugin';
            $this_plugin = new $plugin_class;

            if (method_exists($this_plugin, 'get_my_cutoff_date')) {
                // Call the function from the plugin which will find the dates
                // that apply to this student.
                $dates[$plugin] = new stdClass;
                $dates[$plugin]->date_cutoff = (int)$this_plugin->get_my_cutoff_date($cm_id, $user_id);
            }

        }

        // find the longest date they have, as they may have multiple extensions
        // - individual
        // - global
        // - group
        return $this->get_longest_date($dates, 'date_cutoff');
    }

    /**
     * Set the cutoff date in the deadlines table
     *
     * @param int $cm_id Course Module ID this date is for.
     * @param int $date Date to set the cutoff to.
     */
    public function set_cut_off_date($cm_id, $date) {

        global $DB;

        if (!$deadline_id = $this->deadline_exists($cm_id)) {
            print_error('cannotadddeadline', '', course_get_url($data->course, $data->section), $data->modulename);
        }

        $data              = new stdClass();
        $data->id          = $deadline_id;
        $data->date_cutoff = $date;

        if (!$DB->update_record('deadline_deadlines', $data)) {
            print_error('cannotupdatedeadline', '', course_get_url($data->course, $data->section), $data->modulename);
        }

    }

    // --------------------------------------------------------

    /**
     * Check each plugin for a specific timelimit for a specfic user.
     *
     * @param int $cm_id Course Module ID this query relates to.
     * @param int $user_id User ID this query relates to.
     */
    public function get_timelimit($cm_id, $user_id) {
        global $CFG;

        $plugins = $this->get_installed_plugins();

        $dates = array();

        // check the installed plugins for due dates.
        foreach ($plugins as $plugin => $path) {

            $plugin_code = $CFG->dirroot . '/deadline/' . $plugin . '/lib.php';

            if (file_exists($plugin_code)) {
                require_once($plugin_code);
            } else {
                // Skip this plugin. It's either some kind of ghost, or it's
                // totally broken!
                continue;
            }

            $plugin_class = $plugin . '_plugin';
            $this_plugin = new $plugin_class;

            if (method_exists($this_plugin, 'get_my_timelimit')) {
                // Call the function from the plugin which will find the dates
                // that apply to this student.
                $dates[$plugin] = new stdClass;
                $dates[$plugin]->timelimit = (int)$this_plugin->get_my_timelimit($cm_id, $user_id);
            }

        }

        // find the longest date they have, as they may have multiple extensions
        // - individual
        // - global
        // - group
        return $this->get_longest_date($dates, 'timelimit');
    }

    /**
     * Set the timelimit on a specific course module ID.
     *
     * @param int $cm_id Course module ID to set the date for.
     * @param int $timelimit Timelimit to set for this course module ID
     */
    public function set_timelimit($cm_id, $timelimit) {

        global $DB;

        if (!$deadline_id = $this->deadline_exists($cm_id)) {
            print_error('cannotadddeadline', '', course_get_url($data->course, $data->section), $data->modulename);
        }

        $data              = new stdClass();
        $data->id          = $deadline_id;
        $data->timelimit   = $timelimit;

        if (!$DB->update_record('deadline_deadlines', $data)) {
            print_error('cannotupdatedeadline', '', course_get_url($data->course, $data->section), $data->modulename);
        }

    }

    // --------------------------------------------------------

    /**
     * Get the largest number/longest date. Designed to be passed an array of
     * objects containing dates as returned from different plugins and then
     * return a single date to be used as the actual date.
     *
     * This can be upgraded at a later date to suit some kind of configuration
     * to allow people to apply some kind of config to the item.
     *
     * @param array $dates Array of dates to be checked
     * @param string $field Field name that should be checked for dates.
     */
    public final function get_longest_date($dates, $field = 'date_deadline') {

        // This function orders the items by the content of the field supplied
        // in $field. Largest/Longest date will be in element 0, etc.
        $dates = $this->date_sort($dates, $field);

        // Return the date that is the furthest in the future. In my limited
        // short sigted view this should always be the date that's returned,
        // assuming it's always an APPROVED date (up to the plugin to determine).
        return $dates['0']->{$field};
    }

    /**
     * Using a closure, order the items in the array according in highest to lowest
     *
     * @param array $data Array of objects to order
     * @param string $field Field that contains the data to check.
     * @return array Ordered array.
     */
    protected function date_sort($data, $field = 'date_deadline') {

        usort($data, function($a, $b) use ($field) {
            return $a->{$field} > $b->{$field} ? -1 : 1;
        });

        return $data;
    }

    /**
     * Function to be called when a course module is deleted. This will need to
     * load each plugin and tell them, so they can also delete data from their
     * tables
     *
     * @param array $eventdata Data provided by the event when the course module is deleted.
     * @return boolean True if hook executed successfully. False otherwise.
     */
    public final function module_deleted($eventdata) {

        // $eventdata consists of data like this:
        // $eventdata = new stdClass();
        // $eventdata->modulename = $modulename;
        // $eventdata->cmid       = $cm->id;
        // $eventdata->courseid   = $cm->course;
        // $eventdata->userid     = $USER->id;

        // call each installed module and delete their stored deadlines.

        $plugins = $this->get_installed_plugins();

        // foreach ($plugins as $plugin) {
        //     // delete any records this plugin has saved.
        //     $plugin->delete_cmid($eventdata->cmid);
        // }

        return true;
    }

    /**
     * Given a course detail, usually a course object or course ID, return a list
     * of all activities in the course.
     *
     * @param mixed $course Course to get a list of activities from.
     * @return Array Array containing an object with all activities in the requested course.
     */
    public function get_activity_names($course = null) {

        global $DB, $COURSE, $USER;

        $courses = array();

        // Handle the case of a 0 course ID
        if (!is_null($course) && $course->id == 0) {
            // get a list of all the course id's for this user.
            $courses = extensions_plugin::get_courses_with_extensions_for_userid($USER);
        } else {
            $courses[] = $COURSE;
        }

        foreach ($courses as $course) {

            // Use modinfo to get section order and also add in names
            if (empty($modinfo)) {
                $modinfo = get_fast_modinfo($course->id);
            }

            $result = array();
            foreach ($modinfo->sections as $sectioncms) {

                foreach ($sectioncms as $cmid) {

                    // this function will need to accept the modname and check it
                    // is an activity.
                    if (!$this->is_activity($modinfo->cms[$cmid]->modname)) {
                        continue;
                    }

                    // If this activity does not support deadlines, we can't do anything
                    // with it. We'll just have to ignore it...
                    if (!$this->activity_supports_deadlines($modinfo->cms[$cmid]->modname)) {
                        continue;
                    }

                    if ($modinfo->cms[$cmid]->visible == 1) {
                        $detail             = new stdClass;
                        $detail->id         = $cmid;
                        $detail->modname    = $modinfo->cms[$cmid]->modname;
                        $detail->name       = $modinfo->cms[$cmid]->name;
                        $detail->ext_status = extensions_plugin::get_extension_status_by_cmid($cmid);
                        $detail->visible    = $modinfo->cms[$cmid]->visible;

                        $result[$cmid] = $detail;
                    }
                }
            }
        }

        if (isset($result)) {
            return $result;
        }
    }

    /**
     * Check to see if a module is an activity.
     *
     * @param string $mod_name Module name to check.
     * @return boolean True if item is an activity. False if it is not.
     */
    public function is_activity($mod_name) {
        $archetype = plugin_supports('mod', $mod_name, FEATURE_MOD_ARCHETYPE, MOD_ARCHETYPE_OTHER);
        return ($archetype !== MOD_ARCHETYPE_RESOURCE && $archetype !== MOD_ARCHETYPE_SYSTEM);
    }

    /**
     * Code to save the open/close/cutoff/timelimit dates from the edit page of
     * an activity in the deadlines module dynamically.
     *
     * @param object $formdata Data from the Moodke form.
     * @return boolean Returns true if the save was successful.
     */
    public function save_plugin_fields($formdata = null) {

        global $CFG;

        $plugins = $this->get_installed_plugins();

        // send the data to every plugin.
        foreach ($plugins as $plugin => $path) {

            $plugin_code = $CFG->dirroot . '/deadline/' . $plugin . '/lib.php';

            if (file_exists($plugin_code)) {
                require_once($plugin_code);
            } else {
                // Skip this plugin. It's either some kind of ghost, or it's
                // totally broken!
                continue;
            }

            $plugin_class = $plugin . '_plugin';
            $this_plugin = new $plugin_class;
            $this_plugin->save_form_elements($formdata);

        }

        return true;
    }

}
