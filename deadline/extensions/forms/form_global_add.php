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
 * This file contains a custom group element for showing a table of extensions
 * using standard Moodle forms functions and methods.
 *
 * @package   deadline_extensions
 * @copyright 2013 University of South Australia {@link http://www.unisa.edu.au}
 * @author    James McLean <james.mclean@unisa.edu.au>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    //  It must be included from a Moodle page
}

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/form/submit.php');

require_once('form_base.php');

class form_global_add extends form_base {

    protected $page_name = null;
    protected $save_destination = 'global';

    public function __construct($arg) {
        parent::__construct($arg);

        $this->page_name = get_string('ext_global_ext_create', extensions_plugin::EXTENSIONS_LANG);

        global $COURSE;
        add_to_log($COURSE->id, "extensions", "viewing", "index.php", "viewing " . $this->page_name, $this->get_cmid());
    }

    public function post_form_load() {
        parent::post_form_load();
    }

    public function definition() {
        parent::definition();

        global $CFG, $COURSE, $USER;
        $mform =& $this->_form;

        $mform->addElement('hidden', 'page', 'global_add');
        $mform->setType('page', PARAM_ALPHAEXT);

        $mform->addElement('hidden', 'action', 'save');
        $mform->setType('action', PARAM_ALPHAEXT);

        $mform->addElement('hidden', 'cmid',   '');

        $mform->addElement('header', 'header', get_string('ext_global_ext_create', extensions_plugin::EXTENSIONS_LANG));

        $mform->addElement('static', 'assignment_name', get_string("extselectassignment", extensions_plugin::EXTENSIONS_LANG));

        $mform->addElement('date_time_selector', 'cur_due_date', get_string('extcurrduedate', extensions_plugin::EXTENSIONS_LANG), $this->date_options);
        $mform->addElement('date_time_selector', 'ext_date', get_string("extglobalextdate", extensions_plugin::EXTENSIONS_LANG), $this->date_options);

        $mform->addElement('htmleditor', 'ext_reason', get_string("extreason", extensions_plugin::EXTENSIONS_LANG), array('cols' => 60, 'rows' => 6));
        $mform->setType('ext_reason', PARAM_RAW);

        $mform->addElement('static', 'grouping_name', get_string('grouping', 'group'));

        $groups_picker = $mform->addElement('select_picker', 'groups', get_string("extapplyto", extensions_plugin::EXTENSIONS_LANG));
        $groups_picker->set_multiple(true);
        // $groups_picker->loadOptions(null, $groups);

        $this->add_action_buttons(true, get_string("extsaveapply", extensions_plugin::EXTENSIONS_LANG));

    }

    public function definition_after_data() {
        parent::definition_after_data();

        global $CFG, $USER;
        $mform =& $this->_form;

        $deadlines  = new deadlines_plugin();
        $extensions = new extensions_plugin();
        $activity = $extensions->activity_detail($this->get_cmid());

        // Check to see if a global extension exists.
        $global_extensions = $extensions->get_activity_global_extensions($this->get_cmid());

        $mform->setDefault('assignment_name', $activity->name);

        $mform->setDefault('cmid', $this->get_cmid());

        $deadline = $deadlines->get_deadline_date_deadline($this->get_cmid());

        $mform->setDefault('cur_due_date', $deadline);
        $mform->freeze('cur_due_date');

        // Add the default amount of time to the extension date.
        $ext_deadline = $deadline + (get_config('deadline_extensions', 'default_ext_length') * 60 * 60);
        $mform->setDefault('ext_date', $ext_deadline);

        if (isset($activity->groupingid) && $activity->groupingid != '0') {
            // Is this activity assigned to a specific grouping? If so, we only want
            // to offer groups for selection in the grouping itself.

            $grouping_string_content  = groups_get_grouping_name($activity->groupingid) . ' ';
            $grouping_string_content .= html_writer::tag('i', get_string('only_grouping_groups', extensions_plugin::EXTENSIONS_LANG));

            $mform->setDefault('grouping_name', $grouping_string_content);
            $activity_groups = groups_get_all_groups($activity->course, 0, $activity->groupingid);

        } else {
            // If this activity is not assigned to a grouping, offer all groups as
            // options for extension.

            $mform->setDefault('grouping_name', html_writer::tag('i', get_string('no_grouping_assigned', extensions_plugin::EXTENSIONS_LANG)));
            $activity_groups = groups_get_all_groups($activity->course, 0, 0);

        }

        $groups = array();

        // Make sure we even have groups to add!
        if (count($activity_groups) > 0) {
            foreach ($activity_groups as $key => $group) {
                $groups['right'][$key] = $group->name;
            }

            $select_picker = $mform->getElement('groups');
            $select_picker->loadOptions(null, $groups);
        }

        // TODO: FIX THIS DISGUSTING TERNARY!
        // $mform->setDefault('ext_reason', isset($globalExtension->ext_reason) ? $globalExtension->ext_reason : '');

    }

    public function validation($data, $files) {
        global $CFG, $DB;

        $errors = array();

        // Do we allow multiple global extensions?
        if (!get_config('deadline_extensions', 'allow_multiple_global') == 1) {

            $params = array(
                    'cm_id'    => $data['cmid'],
                    'ext_type' => extensions_plugin::EXT_GROUP
            );

            // See if a global extensions exists already
            if ($DB->record_exists('deadline_extensions', $params)) {
                $errors['assignment_name'] = get_string('global_already_exists', extensions_plugin::EXTENSIONS_LANG);
            }
        }

        // Make sure the date provided is NOT BEFORE the duedate of the assessment
        $deadlines = new deadlines_plugin();
        $deadline = $deadlines->get_deadline_date_deadline($data['cmid']);

        // Check the extension is after any existing deadlines (includes existing extensions)
        if ($deadline > $data['ext_date']) {
            $errors['ext_date'] = get_string('extbeforedue', extensions_plugin::EXTENSIONS_LANG) . ' of ' . userdate($deadline);
        }

        if (!isset($data['groups'])) {
            $errors['groups'] = get_string('please_select_a_group', extensions_plugin::EXTENSIONS_LANG);
            return $errors;
        }

        if (!isset($data['groups']['leftContents'])) {
            $errors['groups'] = get_string('please_select_a_group', extensions_plugin::EXTENSIONS_LANG);
            return $errors;
        }

        if (isset($data['groups']) && $data['groups']['leftContents'] == "") {
            $errors['groups'] = get_string('please_select_a_group', extensions_plugin::EXTENSIONS_LANG);
            return $errors;
        }

        // Make sure there is no global extension for this group & activity yet.
        if (isset($data['groups']['leftContents']) && $data['groups']['leftContents'] != "") {
            // Groups are set, make sure they don't already have a global extension

            $group_list = array();

            $groups = explode(',', $data['groups']['leftContents']);

            list($groups_sql, $params) = $DB->get_in_or_equal($groups, SQL_PARAMS_NAMED);

            $params['cm_id'] = $data['cmid'];
            $params['ext_type'] = extensions_plugin::EXT_GLOBAL;

            $sql = "SELECT dea.id, dea.group_id " .
                    "FROM {deadline_extensions} de, {deadline_extensions_appto} dea " .
                    "WHERE de.id = dea.ext_id " .
                    "AND de.ext_type = :ext_type " .
                    "AND de.cm_id = :cm_id " .
                    "AND dea.group_id " . $groups_sql;

            if ($invalid_groups = $DB->get_records_sql($sql, $params)) { // group with extension found

                $group_names = array();

                foreach($invalid_groups as $invalid_group) {
                    $group_names[] = groups_get_group($invalid_group->group_id, 'name')->name;
                }

                $errors['groups'] = get_string('group_has_extension', extensions_plugin::EXTENSIONS_LANG, implode(', ', $group_names));
            }

        }

        return $errors;
    }


    public function save_hook($form_data = null) {

        global $DB, $CFG, $USER, $COURSE;

        // Save the selected groups from this items grouping to apply the
        // extension to.

        add_to_log($COURSE->id, "extensions", "changing", "index.php", "changing global extension ", $this->get_cmid());

        // Add extension to the extensions table.
        $extension                = new stdClass;
        $extension->ext_type      = extensions_plugin::EXT_GLOBAL;
        $extension->deadline_id   = deadlines_plugin::get_deadline_id_by_cmid($form_data->cmid);
        $extension->cm_id         = $form_data->cmid;
        $extension->staff_id      = $USER->id;
        $extension->response_text = $form_data->ext_reason;
        $extension->date          = $form_data->ext_date;
        $extension->status        = extensions_plugin::STATUS_APPROVED; // duh!
        $extension->created       = date('U');

        if (!$ext_id = $DB->insert_record('deadline_extensions', $extension)) {
            print_error('Problem saving global extension!');
            return false;
        }

        // Add groups to the extensions_appto table.
        if (isset($form_data->groups['leftContents'])) {
            $groups = explode(",", $form_data->groups['leftContents']);

            foreach ($groups as $group) {
                $appto           = new stdClass;
                $appto->ext_id   = $ext_id;
                $appto->group_id = $group;

                if (!$DB->insert_record('deadline_extensions_appto', $appto)) {
                    print_error('Problem adding groups to global extension!');
                    return false;
                }
            }
        }

        // Notify users in the groups of the extension.

        // Add item to the calendars of users in the selected groups.

        return true;

    }

    public function get_save_destination() {
        return 'global';
    }

}