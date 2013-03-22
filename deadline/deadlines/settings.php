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
 * This file contains all settings options, to be set on installation of the
 * plugin.
 *
 * @package   deadline_deadlines
 * @copyright 2013 University of South Australia {@link http://www.unisa.edu.au}
 * @author    James McLean <james.mclean@unisa.edu.au>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

defined('DEADLINES_DISABLED') or define('DEADLINES_DISABLED', 0);
defined('DEADLINES_ENABLED')  or define('DEADLINES_ENABLED', 1);

require_once($CFG->dirroot . '/deadline/deadlines/lib.php');

// Define the 'Enabled' yes/no field.
$options_array = array(
        DEADLINES_DISABLED => get_string('no'),
        DEADLINES_ENABLED  => get_string('yes'),
);

$deadlines_enabled = new admin_setting_configselect(deadlines_plugin::DEADLINES_MOD_NAME . '/enabled',
        get_string('enable_deadlines', deadlines_plugin::DEADLINES_LANG),
        get_string('enable_deadlines', deadlines_plugin::DEADLINES_LANG),
        DEADLINES_ENABLED,
        $options_array);

// Add the 'Enabled' yes/no field.
$settings->add($deadlines_enabled);
