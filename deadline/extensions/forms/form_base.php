<?php

require_once ($CFG->libdir.'/formslib.php');

MoodleQuickForm::registerElementType('extension_requests', "forms/extension_requests.php", 'MoodleQuickForm_extension_requests');
MoodleQuickForm::registerElementType('extension_configure', "forms/extension_configure.php", 'MoodleQuickForm_extension_configure');

class form_base extends moodleform {

    protected $course = null;
    protected $ext_id = null;

    /**
     * Constructor method.
     *
     * @param $arg Arguments to be passed to the parent.
     * @return none.
     *
     */
    public function __construct($arg = null) {

        parent::__construct($arg);

    }

    /**
     *
     * Base definition. All child overide methods should call this as the
     * parent so that the ID is set correctly
     *
     * @param none
     * @return none
     *
     */
    public function definition() {
        $mform =& $this->_form;

        // Add a hidden field for the Course ID.
        $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);

    }

    /**
     *
     * Base definition. This will need to be overridden
     *
     * @param none
     * @return none
     *
     */
    public function definition_after_data() {
        $mform =& $this->_form;

    }

    public function get_page_name() {
        return $this->page_name;
    }

    protected function get_all_students() {

        global $COURSE, $DB;

        $context = get_context_instance(CONTEXT_COURSE, $COURSE->id);
        $roleid  = $DB->get_field('role', 'id', array('shortname' => 'student'), MUST_EXIST);

        if (!$contextusers = get_role_users($roleid, $context, false, 'u.id, u.firstname, u.lastname, u.email', 'u.lastname, u.firstname')) {
            $contextusers = array();
        } else {
            return $contextusers;
        }

    }

    public function set_course($course = null) {

        if(!is_null($course)) {
            $this->course = $course;
        }

    }

    public function get_course() {
        return $this->course;
    }

    public function get_course_id() {
        return $this->get_course()->id;
    }


    public function set_extension_id($ext_id = null) {
        if(!is_null($ext_id)) {
            $this->ext_id = $ext_id;
        }
    }

    public function get_extension_id() {
        return $this->ext_id;
    }


    public function getGroupElement($index = null, $elementGroup = null) {

        if(is_null($index)) {
            return false;
        }

        foreach (array_keys($elementGroup->_elements) as $key) {
            $elementName = $elementGroup->_elements[$key]->getName();
            if ($index == $elementName) {
                return $elementGroup->_elements[$key];
                break;
            }
        }

        return false;
    }

    public function get_activity_names($course = null) {

        global $DB, $COURSE, $USER;

        $courses = array();

        // handle the case of a 0 course ID
        if(!is_null($course) && $course->id == 0) {
            // get a list of all the course id's for this user.
            $courses = Extensions::get_courses_with_extensions_for_userid($USER);
        } else {
            $courses[] = $COURSE;
        }


        foreach($courses as $course) {

            // Use modinfo to get section order and also add in names
            if (empty($modinfo)) {
                $modinfo = get_fast_modinfo($course->id);
            }

            $result = array();
            foreach ($modinfo->sections as $sectioncms) {

                foreach ($sectioncms as $cmid) {

                    // this function will need to accept the modname and check it
                    // is an activity.
                    if(!$this->is_activity($modinfo->cms[$cmid]->modname)) {
                        continue;
                    }

                    if($modinfo->cms[$cmid]->visible == 1) {
                        $detail = new stdClass;
                        $detail->id         = $cmid;
                        $detail->modname    = $modinfo->cms[$cmid]->modname;
                        $detail->name       = $modinfo->cms[$cmid]->name;
                        $detail->ext_status = Extensions::get_extension_status_by_cmid($cmid);
                        $detail->visible    = $modinfo->cms[$cmid]->visible;

                        $result[$cmid] = $detail;
                    }
                }
            }
        }
        return $result;
    }

    public function is_activity($mod_name) {
        $archetype = plugin_supports('mod', $mod_name, FEATURE_MOD_ARCHETYPE, MOD_ARCHETYPE_OTHER);
        return ($archetype !== MOD_ARCHETYPE_RESOURCE && $archetype !== MOD_ARCHETYPE_SYSTEM);
    }

    public function get_course_groups($course = null) {
        // TODO: IMPLEMENT ME
        return false;
    }

    public function get_extension_approvers_by_course($course = null) {
        // TODO: IMPLEMENT ME
        return false;
    }

}