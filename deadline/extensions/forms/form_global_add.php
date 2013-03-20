<?php

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once ($CFG->libdir . '/formslib.php');
require_once ($CFG->libdir . '/form/submit.php');

require_once ('form_base.php');

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


        $mform->addElement('static','assignment_name', get_string("extselectassignment", extensions_plugin::EXTENSIONS_LANG));

        $mform->addElement('date_time_selector', 'cur_due_date', get_string('extcurrduedate', extensions_plugin::EXTENSIONS_LANG), $this->date_options);
        $mform->addElement('date_time_selector', 'ext_date', get_string("extglobalextdate", extensions_plugin::EXTENSIONS_LANG), $this->date_options);

        $mform->addElement('htmleditor', 'ext_reason', get_string("extreason", extensions_plugin::EXTENSIONS_LANG), array('cols' => 60, 'rows' => 6));
        $mform->setType('ext_reason', PARAM_RAW);

        $mform->addElement('static','grouping_name', get_string('grouping', 'group'));

        $groupsPicker = $mform->addElement('select_picker', 'groups', get_string("extapplyto", extensions_plugin::EXTENSIONS_LANG));
        $groupsPicker->set_multiple(true);
        // $groupsPicker->loadOptions(null, $groups);


//         if($this->get_saved()) {
//             $mform->addElement('static', 'static', null, 'Saved Successfully');
//         }

        $this->add_action_buttons(TRUE, get_string("extsaveapply", extensions_plugin::EXTENSIONS_LANG));

    }


    public function definition_after_data() {
        parent::definition_after_data();

        global $CFG, $USER;
        $mform =& $this->_form;

        //---------------

        $deadlines  = new deadlines_plugin();
        $extensions = new extensions_plugin();
        $activity = $extensions->activity_detail($this->get_cmid());

        // check to see if a global extension exists
        $globalExtensions = $extensions->get_activity_group_extensions($this->get_cmid());

        $mform->setDefault('assignment_name', $activity->name);

        $mform->setDefault('cmid', $this->get_cmid());

        $deadline = $deadlines->get_deadline_date_deadline($this->get_cmid());

        $mform->setDefault('cur_due_date', $deadline);
        $mform->freeze('cur_due_date');

        // add the default amount of time to the extension date.
        $ext_deadline = $deadline + (get_config('deadline_extensions', 'default_ext_length') * 60 * 60);
        $mform->setDefault('ext_date', $ext_deadline);

//         // If there is a Global Extension date set, use that.
//         // Otherwise 'suggest' a 2 week addition (14 days, actually).
//         if(isset($globalExtension->ext_date)) {
//             $default_date = $globalExtension->ext_date;
//         } else {
//             if(isset($assignment->duedate)) {
//                 $default_date = $assignment->duedate + (86400 * 14);
//             } else {
//                 $default_date = date('U') + (86400 * 14);
//             }
//         }

//         $mform->setDefault('ext_date', $default_date);

        if(isset($activity->groupingid) && $activity->groupingid != '0') {
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
        if(sizeof($activity_groups) > 0) {
            foreach($activity_groups as $key => $group) {
                $groups['right'][$key] = $group->name;
            }

            $select_picker = $mform->getElement('groups');
            $select_picker->loadOptions(null, $groups);

        }

        // TODO: FIX THIS DISGUSTING TERNARY!
        $mform->setDefault('ext_reason', isset($globalExtension->ext_reason) ? $globalExtension->ext_reason : '');

    }

    public function validation($data, $files) {
        global $CFG, $DB;

        $errors = array();

        // Do we allow multiple global extensions?
        if(!get_config('deadline_extensions','allow_multiple_global') == 1) {

            $params = array(
                    'cm_id'    => $data['cmid'],
                    'ext_type' => extensions_plugin::EXT_GROUP
            );

            // See if a global extensions exists already
            if($DB->record_exists('deadline_extensions', $params)) {
                $errors['assignment_name'] = get_string('global_already_exists', extensions_plugin::EXTENSIONS_LANG);
            }
        }

        // make sure the date provided is NOT BEFORE the duedate of the assessment
        $deadlines = new deadlines_plugin();
        $deadline = $deadlines->get_deadline_date_deadline($data['cmid']);

        // check the extension is after any existing deadlines (includes existing extensions)
        if($deadline > $data['ext_date']) {
            $errors['ext_date'] = get_string('extbeforedue', extensions_plugin::EXTENSIONS_LANG) . ' of ' . userdate($deadline);
        }

        if(!isset($data['groups'])) {
            $errors['groups'] = get_string('please_select_a_group', extensions_plugin::EXTENSIONS_LANG);
            return $errors;
        }

        if(!isset($data['groups']['leftContents'])) {
            $errors['groups'] = get_string('please_select_a_group', extensions_plugin::EXTENSIONS_LANG);
            return $errors;
        }

        if(isset($data['groups']) && $data['groups']['leftContents'] == "") {
            $errors['groups'] = get_string('please_select_a_group', extensions_plugin::EXTENSIONS_LANG);
            return $errors;
        }

        // make sure there is no global extension for this group & activity yet.
        if(isset($data['groups']['leftContents']) && $data['groups']['leftContents'] != "") {
            // Groups are set, make sure they don't already have a global extension

            $groups = explode(',', $data['groups']['leftContents']);

            foreach($groups as $group_key => $group) {

                $params = array(
                        'gid'      => $group,
                        'cm_id'    => $data['cmid'],
                        'ext_type' => extensions_plugin::EXT_GLOBAL
                );

                $sql = "SELECT 1 " .
                       "FROM {deadline_extensions} de, {deadline_extensions_appto} dea " .
                       "WHERE de.id = dea.ext_id " .
                       "AND de.ext_type = :ext_type " .
                       "AND de.cm_id = :cm_id " .
                       "AND dea.group_id = :gid";

                if($DB->get_records_sql($sql, $params)) { // group with extension found
                    $group_name = groups_get_group($group, 'name');
                    $errors['groups'] = get_string('group_has_extension', extensions_plugin::EXTENSIONS_LANG, $group_name->name);
                }
            }

        }

        return $errors;
    }


    public function save_hook($form_data = null) {

        global $DB, $CFG, $USER, $COURSE;

        // Save the selected groups from this items grouping to apply the
        // extension to.

        add_to_log($COURSE->id, "extensions", "changing", "index.php", "changing global extension ", $this->get_cmid());

        // add extension to the extensions table
        $extension                = new stdClass;
        $extension->ext_type      = extensions_plugin::EXT_GLOBAL;
        $extension->deadline_id   = deadlines_plugin::get_deadline_id_by_cmid($form_data->cmid);
        $extension->cm_id         = $form_data->cmid;
        $extension->staff_id      = $USER->id;
        $extension->response_text = $form_data->ext_reason;
        $extension->date          = $form_data->ext_date;
        $extension->status        = extensions_plugin::STATUS_APPROVED; // duh!
        $extension->created       = date('U');

        if(!$ext_id = $DB->insert_record('deadline_extensions', $extension)) {
            print_error('Problem saving global extension!');
            return false;
        }

        // add groups to the extensions_appto table
        if(isset($form_data->groups['leftContents'])) {
            $groups = explode(",", $form_data->groups['leftContents']);

            foreach($groups as $group) {
                $appto           = new stdClass;
                $appto->ext_id   = $ext_id;
                $appto->group_id = $group;

                if(!$DB->insert_record('deadline_extensions_appto', $appto)) {
                    print_error('Problem adding groups to global extension!');
                    return false;
                }
            }
        }

        // notify users in the groups of the extension

        // add item to the calendars of users in the selected groups.

        return true;

            /*
            // old code.
            $failure = false;

            $newGlobal = new stdClass;
            if(isset($form_data->gexid)) {
                $newGlobal->id            = $form_data->gexid;
            }
            $newGlobal->unisa_asmnt_id    = $form_data->asmntid;
            $newGlobal->ext_date          = $form_data->ext_date;
            $newGlobal->ext_reason        = $form_data->ext_reason;
            $newGlobal->ext_creator       = $form_data->userid;
            $newGlobal->ext_applies_to_id = '0';

            if(isset($newGlobal->id)) {
                if(!$global_id = update_record('unisa_asmnt_ext_glo', $newGlobal)) {
                    $failure = true;
                }
            } else {
                if(!$newGlobal->id = insert_record('unisa_asmnt_ext_glo', $newGlobal, true)) {
                    $failure = true;
                }
            }



            // Delete existing records in the applies to table.
            if(!delete_records('unisa_asmnt_ext_appto', 'global_ext_id', $newGlobal->id)) {
                $failure = true;
            }

            $sql = "SELECT MM.NAME, MCM.INSTANCE " .
                "FROM {$CFG->prefix}COURSE_MODULES MCM, {$CFG->prefix}MODULES MM, {$CFG->prefix}UNISA_ASMNT MUA " .
                "WHERE MCM.MODULE = MM.ID " .
                "AND MCM.ID = MUA.MCOU_MOD_ID " .
                "AND MUA.ID = '{$form_data->asmntid}'";

            $detail = get_record_sql($sql);

            foreach($groups as $group) {

                $appto = new stdClass;
                $appto->global_ext_id = $newGlobal->id;
                $appto->group_id      = $group;

                if(!insert_record('unisa_asmnt_ext_appto', $appto)) {
                    $failure = true;
                } else {
                    // Group added OK, notify the students in the group.
                    $this->notify_students($group, $form_data->asmntid);
                }

                // Create the event for this group.

                $event              = new stdClass;
                $event->name        = get_string('ext_event_title', extensions_plugin::EXTENSIONS_LANG);
                $event->courseid    = $COURSE->id;
                $event->groupid     = $group;
                $event->modulename  = $detail->name; // needs to be module type
                $event->instance    = $detail->instance; // id in the module table
                $event->description = get_string('ext_event_description', extensions_plugin::EXTENSIONS_LANG);
                $event->eventtype   = 'due';
                $event->timestart   = $form_data->ext_date;

                // see if event exists
                if(record_exists('event', 'groupid', $group, 'modulename', $detail->name, 'instance', $detail->instance)) {

                    // Event exists, get ID so it can be updated.
                    $this_event = get_record('event', 'groupid', $group, 'modulename', $detail->name, 'instance', $detail->instance);
                    $event->id = $this_event->id;

                    // Update it.
                    if(!update_event($event)) {
                        error('Failed to update event.');
                    }

                } else {

                    // Add an event for this extension to the specific group.
                    if(!add_event($event)) {
                        error('Failed to add event.');
                    }
                }

            }

            if($failure === false) {
                return true;
            }

            */


    }

    public function get_save_destination() {
        return 'global';
    }

    private function notify_students($groupid = null, $asmnt_id = null) {

        global $CFG;

        if(is_null($groupid)) {
            return;
        }

        if(is_null($asmnt_id)) {
            return;
        }

        // We need to fetch the assignment name
        $sql = "SELECT MCM.ID AS MCM_ID, MCM.INSTANCE AS INSTANCE, MM.NAME, MM.NAME AS MODULE ".
               "FROM M_COURSE_MODULES MCM, M_MODULES MM, M_UNISA_ASMNT MUA " .
               "WHERE MCM.MODULE = MM.ID " .
               "AND MCM.ID = MUA.MCOU_MOD_ID " .
               "AND MUA.ID = '{$asmnt_id}'";

        $asmnt = get_record_sql($sql);

        // Get the actual assignment name.
        $asmnt_name = get_field($asmnt->module, 'name', 'id', $asmnt->instance);

        // get students in a particular group
        $sql = "SELECT MU.ID, MU.EMAIL, MU.FIRSTNAME, MU.LASTNAME " .
               "FROM {$CFG->prefix}GROUPS_MEMBERS MGM, {$CFG->prefix}USER MU " .
               "WHERE MGM.GROUPID = '{$groupid}' " .
               "AND MGM.USERID = MU.ID " .
               "AND LOWER(MU.DEPARTMENT) = 'student'";

        $users = get_records_sql($sql);

        if($users) {
            foreach($users as $user) {

                // Details of the student and assessment to add to the email.
                $detail = new stdClass;
                $detail->name = $user->firstname;
                $detail->asmnt_name = $asmnt_name; // todo add this.

                $body = get_string('ext_email_group_body', extensions_plugin::EXTENSIONS_LANG, $detail);

                $this->send_email(null, $user->email, $body, null);

            }
        }
    }

}