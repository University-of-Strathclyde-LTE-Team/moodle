<?php


function deadline_get_form_elements_module($mform, $context, $modulename = "") {

    global $CFG;

    $plugins = get_plugin_list('deadline');

    foreach($plugins as $plugin => $dir) {

        $lib_file = $dir . '/lib.php';
        $class    = $plugin . '_plugin';

        // ensure the lib.php file exists
        if(file_exists($lib_file)) {
            // include it.
            require_once($lib_file);

            if(class_exists($class)) {
                $plugin_object = new $class;
                $plugin_object->get_form_elements($mform, $context, $modulename);

            }

        }
    }


}
