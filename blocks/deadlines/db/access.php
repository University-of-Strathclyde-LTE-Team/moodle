<?php

defined('MOODLE_INTERNAL') || die();

$capabilities = array(
        'block/deadlines:addinstance' => array(
                'riskbitmask' => RISK_PERSONAL,
                'captype' => 'write',
                'contextlevel' => CONTEXT_BLOCK,
                'archetypes' => array(
                        'editingteacher' => CAP_ALLOW,
                        'manager' => CAP_ALLOW,
                        'student' => CAP_ALLOW,
                ),
        ),
);