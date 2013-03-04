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
 * This file will define events that the Extensions module will respond to.
 *
 * @package   deadline_extensions
 * @copyright 2013 University of South Australia {@link http://www.unisa.edu.au}
 * @author    James McLean <james.mclean@unisa.edu.au>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

// Example Only:
// $handlers = array (
//         'user_deleted' => array (
//                 'handlerfile'      => '/local/nicehack/lib.php',
//                 'handlerfunction'  => 'nicehack_userdeleted_handler',
//                 'schedule'         => 'instant'
//         ),
// );

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/deadline/extensions/lib.php');

$path = $CFG->dirroot . '/deadline/extensions/lib.php';

require_once($path);
$ext = new extensions_plugin();

$handlers = array(
        // Handlers for Individual Extensions
        'indiv_ext_created'  => array(
                'handlerfile'      => $path,
                'handlerfunction'  => array($ext, 'indiv_ext_created'),
                'schedule'         => 'instant'
        ),
        'indiv_ext_modified' => array(
                'handlerfile'      => $path,
                'handlerfunction'  => array($ext, 'indiv_ext_modified'),
                'schedule'         => 'instant'
        ),
        'indiv_ext_deleted'  => array(
                'handlerfile'      => $path,
                'handlerfunction'  => array($ext, 'indiv_ext_deleted'),
                'schedule'         => 'instant'
        ),

        // Handlers for Global Extensions
        'global_ext_created'  => array(
                'handlerfile'      => $path,
                'handlerfunction'  => array($ext, 'global_ext_created'),
                'schedule'         => 'instant'
        ),
        'global_ext_modified' => array(
                'handlerfile'      => $path,
                'handlerfunction'  => array($ext, 'global_ext_modified'),
                'schedule'         => 'instant'
        ),
        'global_ext_deleted'  => array(
                'handlerfile'      => $path,
                'handlerfunction'  => array($ext, 'global_ext_deleted'),
                'schedule'         => 'instant'
        ),
);

// events_trigger('indiv_ext_created', 'test');