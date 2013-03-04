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
 * This file contains all the capabilities for Extensions
 *
 * @package   deadline_extensions
 * @copyright 2013 University of South Australia {@link http://www.unisa.edu.au}
 * @author    James McLean <james.mclean@unisa.edu.au>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

$capabilities = array(
        'deadline/extensions:accessextension' => array(
                'riskbitmask'  => RISK_PERSONAL,
                'captype'      => 'read',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes'   => array(
                        'guest'   => CAP_PROHIBIT,
                        'student' => CAP_ALLOW,
                        'teacher' => CAP_ALLOW,
                        'editingteacher' => CAP_ALLOW,
                        'manager' => CAP_ALLOW,
                ),
        ),
        'deadline/extensions:requestextension'  => array(
                'captype'      => 'write',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes'   => array(
                        'guest'   => CAP_PROHIBIT,
                        'student' => CAP_ALLOW,
                        'teacher' => CAP_ALLOW,
                        'editingteacher' => CAP_ALLOW,
                        'manager' => CAP_ALLOW,
                ),
        ),
        'deadline/extensions:modifyextension'   => array(
                'riskbitmask'  => RISK_PERSONAL,
                'captype'      => 'write',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes'   => array(
                        'guest'   => CAP_PROHIBIT,
                        'student' => CAP_ALLOW,
                        'teacher' => CAP_ALLOW,
                        'editingteacher' => CAP_ALLOW,
                        'manager' => CAP_ALLOW,
                ),
        ),
        'deadline/extensions:withdrawextension' => array(
                'captype'      => 'write',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes'   => array(
                        'guest'   => CAP_PROHIBIT,
                        'student' => CAP_ALLOW,
                        'teacher' => CAP_ALLOW,
                        'editingteacher' => CAP_PROHIBIT,
                        'manager' => CAP_ALLOW,
                ),
        ),
        'deadline/extensions:revokeextension'   => array(
                'captype'      => 'write',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes'   => array(
                        'guest'   => CAP_PROHIBIT,
                        'student' => CAP_PROHIBIT,
                        'teacher' => CAP_ALLOW,
                        'editingteacher' => CAP_ALLOW,
                        'manager' => CAP_ALLOW,
                ),
        ),
        'deadline/extensions:approveextension'  => array(
                'captype'      => 'write',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes'   => array(
                        'guest'   => CAP_PROHIBIT,
                        'student' => CAP_PROHIBIT,
                        'teacher' => CAP_ALLOW,
                        'editingteacher' => CAP_ALLOW,
                        'manager' => CAP_ALLOW,
                ),
        ),
        'deadline/extensions:readextension'     => array(
                'riskbitmask'  => RISK_PERSONAL,
                'captype'      => 'read',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes'   => array(
                        'guest'   => CAP_PROHIBIT,
                        'student' => CAP_ALLOW,
                        'teacher' => CAP_ALLOW,
                        'editingteacher' => CAP_ALLOW,
                        'manager' => CAP_ALLOW,
                ),
        ),
        'deadline/extensions:modifyextension'   => array(
                'captype'      => 'read',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes'   => array(
                        'guest'   => CAP_PROHIBIT,
                        'student' => CAP_ALLOW,
                        'teacher' => CAP_ALLOW,
                        'editingteacher' => CAP_ALLOW,
                        'manager' => CAP_ALLOW,
                ),
        ),
        'deadline/extensions:deleteextension' => array(
                'captype'      => 'read',
                'contextlevel' => CONTEXT_COURSE,
                'archetypes'   => array(
                        'guest'   => CAP_PROHIBIT,
                        'student' => CAP_PROHIBIT,
                        'teacher' => CAP_PROHIBIT,
                        'editingteacher' => CAP_PROHIBIT,
                        'manager' => CAP_PROHIBIT,
                ),
        ),
);