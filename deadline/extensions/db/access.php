<?php
// This file contains all the capabilities for Extensions

/*
 * $string['extensions:requestextension']  = 'Request Extension';
 * $string['extensions:modifyextension']   = 'Modify Extension';
 * $string['extensions:withdrawextension'] = 'Withdraw Extension';
 * $string['extensions:revokeextension']   = 'Revoke Extension';
 * $string['extensions:approveextension']  = 'Approve Extension';
 *
 * $string['extensions:readextension']     = 'Read Extension';
 */

// /** No capability change */
// define('CAP_INHERIT', 0);
// /** Allow permission, overrides CAP_PREVENT defined in parent contexts */
// define('CAP_ALLOW', 1);
// /** Prevent permission, overrides CAP_ALLOW defined in parent contexts */
// define('CAP_PREVENT', -1);
// /** Prohibit permission, overrides everything in current and child contexts */
// define('CAP_PROHIBIT', -1000);

// /** System context level - only one instance in every system */
// define('CONTEXT_SYSTEM', 10);
// /** User context level -  one instance for each user describing what others can do to user */
// define('CONTEXT_USER', 30);
// /** Course category context level - one instance for each category */
// define('CONTEXT_COURSECAT', 40);
// /** Course context level - one instances for each course */
// define('CONTEXT_COURSE', 50);
// /** Course module context level - one instance for each course module */
// define('CONTEXT_MODULE', 70);
// /**
//  * Block context level - one instance for each block, sticky blocks are tricky
//  * because ppl think they should be able to override them at lower contexts.
//  * Any other context level instance can be parent of block context.
//  */
// define('CONTEXT_BLOCK', 80);

// /** Capability allow management of trusts - NOT IMPLEMENTED YET - see {@link http://docs.moodle.org/dev/Hardening_new_Roles_system} */
// define('RISK_MANAGETRUST', 0x0001);
// /** Capability allows changes in system configuration - see {@link http://docs.moodle.org/dev/Hardening_new_Roles_system} */
// define('RISK_CONFIG',      0x0002);
// /** Capability allows user to add scripted content - see {@link http://docs.moodle.org/dev/Hardening_new_Roles_system} */
// define('RISK_XSS',         0x0004);
// /** Capability allows access to personal user information - see {@link http://docs.moodle.org/dev/Hardening_new_Roles_system} */
// define('RISK_PERSONAL',    0x0008);
// /** Capability allows users to add content others may see - see {@link http://docs.moodle.org/dev/Hardening_new_Roles_system} */
// define('RISK_SPAM',        0x0010);
// /** capability allows mass delete of data belonging to other users - see {@link http://docs.moodle.org/dev/Hardening_new_Roles_system} */
// define('RISK_DATALOSS',    0x0020);

$capabilities = array(
        'local/extensions:accessextension' => array(
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
        'local/extensions:requestextension'  => array(
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
        'local/extensions:modifyextension'   => array(
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
        'local/extensions:withdrawextension' => array(
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
        'local/extensions:revokeextension'   => array(
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
        'local/extensions:approveextension'  => array(
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
        'local/extensions:readextension'     => array(
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
        'local/extensions:modifyextension'   => array(
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
        'local/extensions:deleteextension' => array(
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