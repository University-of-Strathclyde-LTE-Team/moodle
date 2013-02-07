<?php

defined('MOODLE_INTERNAL') || die;

defined('EXTENSIONS_DISABLED') or define('EXTENSIONS_DISABLED', 0);
defined('EXTENSIONS_ENABLED')  or define('EXTENSIONS_ENABLED', 1);

defined('EXTENSIONS_IND_DISABLED') or define('EXTENSIONS_IND_DISABLED', 0);
defined('EXTENSIONS_IND_ENABLED')  or define('EXTENSIONS_IND_ENABLED', 1);

defined('EXTENSIONS_GLO_DISABLED') or define('EXTENSIONS_GLO_DISABLED', 0);
defined('EXTENSIONS_GLO_ENABLED')  or define('EXTENSIONS_GLO_ENABLED', 1);

defined('EXTENSIONS_IG_MENU_DIS') or define('EXTENSIONS_IG_MENU_DIS', 0);
defined('EXTENSIONS_IG_MENU_ENA') or define('EXTENSIONS_IG_MENU_ENA', 1);

defined('EXTENSIONS_PEND_DIS') or define('EXTENSIONS_PEND_DIS', 0);
defined('EXTENSIONS_PEND_ENA') or define('EXTENSIONS_PEND_ENA', 1);

defined('EXTENSIONS_DUPLICATE_WARNING_DIS') or define('EXTENSIONS_DUPLICATE_WARNING_DIS', 0);
defined('EXTENSIONS_DUPLICATE_WARNING_ENA') or define('EXTENSIONS_DUPLICATE_WARNING_ENA', 1);

require_once($CFG->dirroot . '/local/extensions/lib.php');

global $USER, $COURSE;

// $id = optional_param('id', '0', PARAM_INT); // Course ID

// if($id != '0') {
//     $course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);
// } else {
//     $course = new stdClass;
//     $course->id = 0;
// }

// Admin config setting items here.
if ($hassiteconfig) { // needs this condition or there is error on login page

    // only show these if Extensions is enabled?
    if(has_capability('moodle/site:config', context_system::instance())) {

        $args = array();

        $ext_url = Extensions::EXTENSIONS_URL_PATH . '/index.php';

        $ADMIN->add('root',            new admin_category('extensions_root', get_string('pluginname', Extensions::LANG_EXTENSIONS)));

        $args['page'] = 'requests';
        $ADMIN->add('extensions_root', new admin_externalpage('extensions_pending', get_string('ext_indiv_req', Extensions::LANG_EXTENSIONS), new moodle_url($ext_url, $args)));

        $args['page'] = 'global';
        $ADMIN->add('extensions_root', new admin_externalpage('extensions_declined', get_string('ext_glob_req', Extensions::LANG_EXTENSIONS), new moodle_url($ext_url, $args)));

        $args['page'] = 'configure_activity';
        $ADMIN->add('extensions_root', new admin_externalpage('extensions_configure', get_string('ext_configure_activity', Extensions::LANG_EXTENSIONS), new moodle_url($ext_url, $args)));

    }

    // Add the configuration menu item here, but only for Admins.
    if(has_capability('moodle/site:config', context_system::instance())) { // admins with doanything in system context.

        // Add a menu item under our normal Extensions menu, so there is another way to access the configuration.
        $settings = new admin_settingpage('local_extensions', get_string('manage_extensions', Extensions::LANG_EXTENSIONS));
        $ADMIN->add('extensions_root', $settings);

        // Add a menu item 'Extensions' under the Plugins menu, so there is a standard place for configuration to be accessed.
        $ADMIN->add('modules', new admin_category('modules_ext_root', get_string('pluginname', Extensions::LANG_EXTENSIONS)));
        $ADMIN->add('modules_ext_root', new admin_settingpage('local_extensions', get_string('configuration', Extensions::LANG_EXTENSIONS)));

        // Define the admin menu settings and fields here.
        // See lib/adminlib.php for these and more items.

        // ------------------------------------------------------------
        // Settings Menu Item
        // ------------------------------------------------------------

        // Define the 'Enabled' yes/no field
        $optionsArray = array(
                EXTENSIONS_DISABLED => get_string('no'),
                EXTENSIONS_ENABLED  => get_string('yes'),
        );

        $extensions_enabled = new admin_setting_configselect(Extensions::EXTENSIONS_MOD_NAME . '/enabled',
                                                      get_string('enable_extensions', Extensions::LANG_EXTENSIONS),
                                                      get_string('enable_extensions', Extensions::LANG_EXTENSIONS),
                                                      EXTENSIONS_ENABLED,
                                                      $optionsArray);

        // Add the 'Enabled' yes/no field
        $settings->add($extensions_enabled);

        // ------------------------------------------------------------
        // Settings Menu Item
        // ------------------------------------------------------------
        // Are Individual Extensions Enabled?
        $optionsArray = array(
                EXTENSIONS_IND_DISABLED => get_string('no'),
                EXTENSIONS_IND_ENABLED  => get_string('yes'),
        );

        $individualextensions_enabled = new admin_setting_configselect(Extensions::EXTENSIONS_MOD_NAME . '/individual_enabled',
                                      get_string('enable_ind_extensions', Extensions::LANG_EXTENSIONS),
                                      get_string('enable_ind_extensions', Extensions::LANG_EXTENSIONS),
                                      EXTENSIONS_IND_ENABLED,
                                      $optionsArray);

        $settings->add($individualextensions_enabled);

        // ------------------------------------------------------------
        // Settings Menu Item
        // ------------------------------------------------------------
        // Are Global Extensions Enabled?
        $optionsArray = array(
                EXTENSIONS_GLO_DISABLED => get_string('no'),
                EXTENSIONS_GLO_ENABLED  => get_string('yes'),
        );

        $globallextensions_enabled = new admin_setting_configselect(Extensions::EXTENSIONS_MOD_NAME . '/global_enabled',
                get_string('enable_glo_extensions', Extensions::LANG_EXTENSIONS),
                get_string('enable_glo_extensions', Extensions::LANG_EXTENSIONS),
                EXTENSIONS_GLO_ENABLED,
                $optionsArray);

        $settings->add($globallextensions_enabled);

        // ------------------------------------------------------------
        // Settings Menu Item
        // ------------------------------------------------------------

        // Define a default request cutoff for extension requests
        $cutoffOptions = Extensions::get_cutoff_options();

        // Add the settings field to the site.
        $defaultRequestCutOff = new admin_setting_configselect(Extensions::EXTENSIONS_MOD_NAME . '/req_cut_off',
                                                       'Request Cutoff (Time Prior)',
                                                       'Cut Off Request Submissions prior to due date.',
                                                       24,
                                                       $cutoffOptions);

        // Add the default cut off time.
        $settings->add($defaultRequestCutOff);

        // ------------------------------------------------------------
        // Settings Menu Item
        // ------------------------------------------------------------

        // Which roles can approve extensions?


        // ------------------------------------------------------------
        // Settings Menu Item
        // ------------------------------------------------------------


        // ------------------------------------------------------------
        // Settings Menu Item
        // ------------------------------------------------------------

        // Show the indiv/global menu on the screen itself?
        $indivGlobalMenuOptions = array(
                EXTENSIONS_IG_MENU_DIS => get_string('no'),
                EXTENSIONS_IG_MENU_ENA => get_string('yes')
        );

        $indivGlobalMenu = new admin_setting_configselect(Extensions::EXTENSIONS_MOD_NAME . '/show_indiv_global',
                    get_string('show_indiv_global', Extensions::LANG_EXTENSIONS),
                    get_string('show_indiv_global', Extensions::LANG_EXTENSIONS),
                    EXTENSIONS_IG_MENU_ENA,
                    $indivGlobalMenuOptions);

        // Add the show/hide indiv/global menu item
        $settings->add($indivGlobalMenu);

        // ------------------------------------------------------------
        // Settings Menu Item
        // ------------------------------------------------------------
        // Show the pending count?
        $pendingCountOptions = array(
                EXTENSIONS_PEND_DIS => get_string('no'),
                EXTENSIONS_PEND_ENA => get_string('yes')
        );

        $pendingCountMenu = new admin_setting_configselect(Extensions::EXTENSIONS_MOD_NAME . '/show_pending_count',
                    get_string('show_pending_count', Extensions::LANG_EXTENSIONS),
                    get_string('show_pending_count', Extensions::LANG_EXTENSIONS),
                    EXTENSIONS_PEND_ENA,
                    $pendingCountOptions);

        // Add the show/hide indiv/global menu item
        $settings->add($pendingCountMenu);

        // ------------------------------------------------------------
        // Settings Menu Item
        // ------------------------------------------------------------
        $showDuplicateWarning = array(
                EXTENSIONS_DUPLICATE_WARNING_DIS => get_string('no'),
                EXTENSIONS_DUPLICATE_WARNING_ENA => get_string('yes')
        );

        $duplicateWarningMenu = new admin_setting_configselect(Extensions::EXTENSIONS_MOD_NAME . '/show_duplicate_warn',
                    get_string('show_duplicate_warning', Extensions::LANG_EXTENSIONS),
                    get_string('show_duplicate_warning', Extensions::LANG_EXTENSIONS),
                    EXTENSIONS_DUPLICATE_WARNING_ENA,
                    $showDuplicateWarning);

        $settings->add($duplicateWarningMenu);

        // ------------------------------------------------------------
        // Settings Menu Item
        // ------------------------------------------------------------


        // ------------------------------------------------------------
        // Settings Menu Item
        // ------------------------------------------------------------


    }
}



