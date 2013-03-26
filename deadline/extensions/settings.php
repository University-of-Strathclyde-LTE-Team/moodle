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
 * This file contains all the global settings and is executed on installation
 * of the plugin. It is also available via the Plugins -> Deadline -> Extensions
 * menu
 *
 * @package   deadline_extensions
 * @copyright 2013 University of South Australia {@link http://www.unisa.edu.au}
 * @author    James McLean <james.mclean@unisa.edu.au>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/deadline/extensions/lib.php');

defined('EXTENSIONS_DISABLED') or define('EXTENSIONS_DISABLED', 0);
defined('EXTENSIONS_ENABLED')  or define('EXTENSIONS_ENABLED', 1);

defined('EXTENSIONS_IND_DISABLED') or define('EXTENSIONS_IND_DISABLED', 0);
defined('EXTENSIONS_IND_ENABLED')  or define('EXTENSIONS_IND_ENABLED', 1);

defined('EXTENSIONS_GLO_DISABLED') or define('EXTENSIONS_GLO_DISABLED', 0);
defined('EXTENSIONS_GLO_ENABLED')  or define('EXTENSIONS_GLO_ENABLED', 1);

defined('EXTENSIONS_GROUP_DISABLED') or define('EXTENSIONS_GROUP_DISABLED', 0);
defined('EXTENSIONS_GROUP_ENABLED')  or define('EXTENSIONS_GROUP_ENABLED', 1);

defined('EXTENSIONS_FORCE_DISABLED') or define('EXTENSIONS_FORCE_DISABLED', 0);
defined('EXTENSIONS_FORCE_ENABLED')  or define('EXTENSIONS_FORCE_ENABLED', 1);

defined('EXTENSIONS_IG_MENU_DIS') or define('EXTENSIONS_IG_MENU_DIS', 0);
defined('EXTENSIONS_IG_MENU_ENA') or define('EXTENSIONS_IG_MENU_ENA', 1);

defined('EXTENSIONS_PEND_DIS') or define('EXTENSIONS_PEND_DIS', 0);
defined('EXTENSIONS_PEND_ENA') or define('EXTENSIONS_PEND_ENA', 1);

defined('EXTENSIONS_MULT_GLO_DIS') or define('EXTENSIONS_MULT_GLO_DIS', 0);
defined('EXTENSIONS_MULT_GLO_ENA') or define('EXTENSIONS_MULT_GLO_ENA', 1);

defined('EXTENSIONS_DUPLICATE_WARNING_DIS') or define('EXTENSIONS_DUPLICATE_WARNING_DIS', 0);
defined('EXTENSIONS_DUPLICATE_WARNING_ENA') or define('EXTENSIONS_DUPLICATE_WARNING_ENA', 1);

defined('EXTENSIONS_RESTRICT_AFTER_SUB_DIS') or define('EXTENSIONS_RESTRICT_AFTER_SUB_DIS', 0);
defined('EXTENSIONS_RESTRICT_AFTER_SUB_ENA') or define('EXTENSIONS_RESTRICT_AFTER_SUB_ENA', 1);

defined('EXTENSIONS_ALLOW_TIMELIMIT_DIS') or define('EXTENSIONS_ALLOW_TIMELIMIT_DIS', '0');
defined('EXTENSIONS_ALLOW_TIMELIMIT_ENA') or define('EXTENSIONS_ALLOW_TIMELIMIT_ENA', '1');

global $USER, $COURSE;

// Admin config setting items here.
if ($hassiteconfig) {

    // Only show these if Extensions is enabled?
    if (has_capability('moodle/site:config', context_system::instance()) &&
            extensions_plugin::is_enabled()) {

        $args = array();
        $ext_url = extensions_plugin::EXTENSIONS_URL_PATH . '/index.php';

        $ADMIN->add('root', new admin_category('extensions_root',
                get_string('pluginname', extensions_plugin::EXTENSIONS_LANG)));

        $args['page'] = 'requests';
        $this_url  = new moodle_url($ext_url, $args);
        $this_page = new admin_externalpage('extensions_pending',
                get_string('ext_indiv_req', extensions_plugin::EXTENSIONS_LANG), $this_url);
        $ADMIN->add('extensions_root', $this_page);

        $args['page'] = 'global';
        $this_url  = new moodle_url($ext_url, $args);
        $this_page = new admin_externalpage('extensions_declined',
                get_string('ext_glob_req', extensions_plugin::EXTENSIONS_LANG), $this_url);
        $ADMIN->add('extensions_root', $this_page);

        $args['page'] = 'configure_activity';
        $this_url =  new moodle_url($ext_url, $args);
        $this_page = new admin_externalpage('extensions_configure',
                get_string('ext_configure_activity', extensions_plugin::EXTENSIONS_LANG), $this_url);
        $ADMIN->add('extensions_root', $this_page);

    }

    // Add the configuration menu item here, but only for Admins.
    if (has_capability('moodle/site:config', context_system::instance())) { // admins with doanything in system context.

        // ------------------------------------------------------------
        // Settings Menu Item
        // ------------------------------------------------------------

        // Define the 'Enabled' yes/no field
        $options_array = array(
                EXTENSIONS_DISABLED => get_string('no'),
                EXTENSIONS_ENABLED  => get_string('yes'),
        );

        $extensions_enabled = new admin_setting_configselect(extensions_plugin::EXTENSIONS_MOD_NAME . '/enabled',
                                                      get_string('enable_extensions', extensions_plugin::EXTENSIONS_LANG),
                                                      get_string('enable_extensions', extensions_plugin::EXTENSIONS_LANG),
                                                      EXTENSIONS_ENABLED,
                                                      $options_array);

        // Add the 'Enabled' yes/no field
        $settings->add($extensions_enabled);

        // ------------------------------------------------------------
        // Settings Menu Item
        // ------------------------------------------------------------
        // Are Individual Extensions Enabled?
        $options_array = array(
                EXTENSIONS_IND_DISABLED => get_string('no'),
                EXTENSIONS_IND_ENABLED  => get_string('yes'),
        );

        $ind_exts_enabled = new admin_setting_configselect(extensions_plugin::EXTENSIONS_MOD_NAME . '/individual_enabled',
                                      get_string('enable_ind_extensions', extensions_plugin::EXTENSIONS_LANG),
                                      get_string('enable_ind_extensions', extensions_plugin::EXTENSIONS_LANG),
                                      EXTENSIONS_IND_ENABLED,
                                      $options_array);

        $settings->add($ind_exts_enabled);

        // ------------------------------------------------------------
        // Settings Menu Item
        // ------------------------------------------------------------
        // Are Global Extensions Enabled?
        $options_array = array(
                EXTENSIONS_GLO_DISABLED => get_string('no'),
                EXTENSIONS_GLO_ENABLED  => get_string('yes'),
        );

        $glob_ext_enabled = new admin_setting_configselect(extensions_plugin::EXTENSIONS_MOD_NAME . '/global_enabled',
                get_string('enable_glo_extensions', extensions_plugin::EXTENSIONS_LANG),
                get_string('enable_glo_extensions', extensions_plugin::EXTENSIONS_LANG),
                EXTENSIONS_GLO_ENABLED,
                $options_array);

        $settings->add($glob_ext_enabled);

        // ------------------------------------------------------------
        // Settings Menu Item
        // ------------------------------------------------------------
        // Are Group Extensions Enabled?
        $options_array = array(
                EXTENSIONS_GROUP_DISABLED => get_string('no'),
                EXTENSIONS_GROUP_ENABLED  => get_string('yes'),
        );

        $group_ext_enabled = new admin_setting_configselect(extensions_plugin::EXTENSIONS_MOD_NAME . '/group_enabled',
                get_string('enable_group_extensions', extensions_plugin::EXTENSIONS_LANG),
                get_string('enable_group_extensions', extensions_plugin::EXTENSIONS_LANG),
                EXTENSIONS_GROUP_ENABLED,
                $options_array);

        $settings->add($group_ext_enabled);

        // ------------------------------------------------------------
        // Settings Menu Item
        // ------------------------------------------------------------

        // Define a default request cutoff for extension requests
        $cutoff_options = extensions_plugin::get_cutoff_options();

        // Add the settings field to the site.
        $default_request_cutoff = new admin_setting_configselect(extensions_plugin::EXTENSIONS_MOD_NAME . '/req_cut_off',
                                                       'Request Cutoff (Time Prior)',
                                                       'Cut Off Request Submissions prior to due date.',
                                                       -1,
                                                       $cutoff_options);

        // Add the default cut off time.
        $settings->add($default_request_cutoff);

        // ------------------------------------------------------------
        // Settings Menu Item
        // ------------------------------------------------------------

        // Define a default request cutoff for extension requests
        $ext_length_options = extensions_plugin::get_default_extension_options();

        // Add the settings field to the site.
        $default_request_cutoff = new admin_setting_configselect(extensions_plugin::EXTENSIONS_MOD_NAME . '/default_ext_length',
                'Default extension length',
                'The default time added to the deadline for extension requests.',
                24,
                $ext_length_options);

        // Add the default cut off time.
        $settings->add($default_request_cutoff);

        // ------------------------------------------------------------
        // Settings Menu Item
        // ------------------------------------------------------------

        // Show the indiv/global menu on the screen itself?
        $ind_glo_menu_options = array(
                EXTENSIONS_IG_MENU_DIS => get_string('no'),
                EXTENSIONS_IG_MENU_ENA => get_string('yes')
        );

        $indiv_group_menu = new admin_setting_configselect(extensions_plugin::EXTENSIONS_MOD_NAME . '/show_indiv_group',
                    get_string('show_indiv_group', extensions_plugin::EXTENSIONS_LANG),
                    get_string('show_indiv_group', extensions_plugin::EXTENSIONS_LANG),
                    EXTENSIONS_IG_MENU_ENA,
                    $ind_glo_menu_options);

        // Add the show/hide indiv/global menu item
        $settings->add($indiv_group_menu);

        // ------------------------------------------------------------
        // Settings Menu Item
        // ------------------------------------------------------------

        $force_ext_enable_options = array(
                EXTENSIONS_FORCE_DISABLED => get_string('no'),
                EXTENSIONS_FORCE_ENABLED  => get_string('yes')
        );

        $indiv_group_menu = new admin_setting_configselect(extensions_plugin::EXTENSIONS_MOD_NAME . '/force_extension_enabled',
                get_string('force_extension_enabled', extensions_plugin::EXTENSIONS_LANG),
                get_string('force_extension_enabled_long', extensions_plugin::EXTENSIONS_LANG),
                EXTENSIONS_FORCE_ENABLED,
                $force_ext_enable_options);

        // Add the show/hide indiv/global menu item
        $settings->add($indiv_group_menu);


        // ------------------------------------------------------------
        // Settings Menu Item
        // ------------------------------------------------------------
        // Show the pending count?
        $pending_count_options = array(
                EXTENSIONS_PEND_DIS => get_string('no'),
                EXTENSIONS_PEND_ENA => get_string('yes')
        );

        $pending_count_menu = new admin_setting_configselect(extensions_plugin::EXTENSIONS_MOD_NAME . '/show_pending_count',
                    get_string('show_pending_count', extensions_plugin::EXTENSIONS_LANG),
                    get_string('show_pending_count', extensions_plugin::EXTENSIONS_LANG),
                    EXTENSIONS_PEND_ENA,
                    $pending_count_options);

        // Add the show/hide indiv/global menu item
        $settings->add($pending_count_menu);

        // ------------------------------------------------------------
        // Settings Menu Item
        // ------------------------------------------------------------
        $show_dup_warning = array(
                EXTENSIONS_DUPLICATE_WARNING_DIS => get_string('no'),
                EXTENSIONS_DUPLICATE_WARNING_ENA => get_string('yes')
        );

        $dup_warn_menu = new admin_setting_configselect(extensions_plugin::EXTENSIONS_MOD_NAME . '/show_duplicate_warn',
                    get_string('show_duplicate_warning', extensions_plugin::EXTENSIONS_LANG),
                    get_string('show_duplicate_warning', extensions_plugin::EXTENSIONS_LANG),
                    EXTENSIONS_DUPLICATE_WARNING_ENA,
                    $show_dup_warning);

        $settings->add($dup_warn_menu);

        // ------------------------------------------------------------
        // Settings Menu Item
        // ------------------------------------------------------------
        // Allow multiple global
        $allow_multiple_global_opts = array(
                EXTENSIONS_MULT_GLO_DIS => get_string('no'),
                EXTENSIONS_MULT_GLO_ENA => get_string('yes')
        );

        $mult_glob_menu = new admin_setting_configselect(extensions_plugin::EXTENSIONS_MOD_NAME . '/allow_multiple_global',
                get_string('allow_multiple_global_short', extensions_plugin::EXTENSIONS_LANG),
                get_string('allow_multiple_global_long', extensions_plugin::EXTENSIONS_LANG),
                EXTENSIONS_MULT_GLO_ENA,
                $allow_multiple_global_opts);

        // Add the show/hide indiv/global menu item
        $settings->add($mult_glob_menu);

        // ------------------------------------------------------------
        // Prevent requests after submission
        // ------------------------------------------------------------
        $prev_req_after_sub = array(
                EXTENSIONS_RESTRICT_AFTER_SUB_DIS => get_string('no'),
                EXTENSIONS_RESTRICT_AFTER_SUB_ENA => get_string('yes')
        );

        $prev_req_after_sub_menu = new admin_setting_configselect(extensions_plugin::EXTENSIONS_MOD_NAME . '/prevent_req_after_sub',
                get_string('prevent_req_after_subm', extensions_plugin::EXTENSIONS_LANG),
                get_string('prevent_req_after_subm_long', extensions_plugin::EXTENSIONS_LANG),
                EXTENSIONS_RESTRICT_AFTER_SUB_ENA,
                $prev_req_after_sub);

        // Add the show/hide indiv/global menu item.
        $settings->add($prev_req_after_sub_menu);

        // ------------------------------------------------------------
        // Deny timelimit extension requests by students
        // ------------------------------------------------------------
        $prevent_time_limit_requests = array(
                EXTENSIONS_ALLOW_TIMELIMIT_DIS => get_string('no'),
                EXTENSIONS_ALLOW_TIMELIMIT_ENA => get_string('yes')
        );

        $prevent_time_limit_menu = new admin_setting_configselect(extensions_plugin::EXTENSIONS_MOD_NAME . '/deny_timelimit_reqs',
                get_string('prevent_timelimit_reqs', extensions_plugin::EXTENSIONS_LANG),
                get_string('prevent_timelimit_reqs_long', extensions_plugin::EXTENSIONS_LANG),
                EXTENSIONS_ALLOW_TIMELIMIT_ENA,
                $prevent_time_limit_requests);

        $settings->add($prevent_time_limit_menu);
        // ------------------------------------------------------------
        // Approved
        // ------------------------------------------------------------

        $approved_menu = new admin_setting_configtext(extensions_plugin::EXTENSIONS_MOD_NAME . '/approved_subject',
                'Approved subject',
                'Approved subject',
                get_string('ext_email_response_subject', extensions_plugin::EXTENSIONS_LANG));

        $settings->add($approved_menu);

        $approved_text_menu = new admin_setting_configtextarea(extensions_plugin::EXTENSIONS_MOD_NAME . '/approved_text',
                'Approved body text',
                'Approved message text (HTML)',
                get_string('ext_email_response_text', extensions_plugin::EXTENSIONS_LANG));

        $settings->add($approved_text_menu);

        // ------------------------------------------------------------
        // Denied
        // ------------------------------------------------------------

        $denied_menu = new admin_setting_configtext(extensions_plugin::EXTENSIONS_MOD_NAME . '/denied_subject',
                'Denied subject',
                'Denied subject',
                get_string('ext_email_response_subject', extensions_plugin::EXTENSIONS_LANG));

        $settings->add($denied_menu);

        $denied_text_menu = new admin_setting_configtextarea(extensions_plugin::EXTENSIONS_MOD_NAME . '/denied_text',
                'Denied body text',
                'Denied message text (HTML)',
                get_string('ext_email_response_text', extensions_plugin::EXTENSIONS_LANG));

        $settings->add($denied_text_menu);

        // ------------------------------------------------------------
        // Withdrawn
        // ------------------------------------------------------------

        $withdrawn_menu = new admin_setting_configtext(extensions_plugin::EXTENSIONS_MOD_NAME . '/withdrawn_subject',
                'Withdrawn subject',
                'Withdrawn subject',
                get_string('ext_email_response_subject', extensions_plugin::EXTENSIONS_LANG));

        $settings->add($withdrawn_menu);

        $withdrawn_text_menu = new admin_setting_configtextarea(extensions_plugin::EXTENSIONS_MOD_NAME . '/withdrawn_text',
                'Withdrawn body text',
                'Withdrawn message text (HTML)',
                get_string('ext_email_response_text', extensions_plugin::EXTENSIONS_LANG));

        $settings->add($withdrawn_text_menu);

        // ------------------------------------------------------------
        // Revoked
        // ------------------------------------------------------------

        $revoked_menu = new admin_setting_configtext(extensions_plugin::EXTENSIONS_MOD_NAME . '/revoked_subject',
                'Revoked subject',
                'Revoked subject',
                get_string('ext_email_response_subject', extensions_plugin::EXTENSIONS_LANG));

        $settings->add($revoked_menu);

        $revoked_text_menu = new admin_setting_configtextarea(extensions_plugin::EXTENSIONS_MOD_NAME . '/revoked_text',
                'Revoked body text',
                'Revoked message text (HTML)',
                get_string('ext_email_response_text', extensions_plugin::EXTENSIONS_LANG));

        $settings->add($revoked_text_menu);

        // ------------------------------------------------------------
        // More Info Required content.
        // ------------------------------------------------------------

        $more_info_menu = new admin_setting_configtext(extensions_plugin::EXTENSIONS_MOD_NAME . '/more_info_subject',
                'More Info Reqd. message subject',
                'More Info Reqd. message subject',
                get_string('ext_email_response_subject', extensions_plugin::EXTENSIONS_LANG));

        $settings->add($more_info_menu);

        $more_info_text_menu = new admin_setting_configtextarea(extensions_plugin::EXTENSIONS_MOD_NAME . '/more_info_text',
                'More Info Reqd. body text',
                'More Info Reqd. message text (HTML)',
                get_string('ext_email_response_text', extensions_plugin::EXTENSIONS_LANG));

        $settings->add($more_info_text_menu);

    }

}
