<?php


// This file will define events that the Extensions module will respond to.

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

$path = $CFG->dirroot . Extensions::EXTENSIONS_URL_PATH . '/lib.php';

require_once($path);
$ext = new Extensions();

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