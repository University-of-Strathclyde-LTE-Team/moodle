<?php

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

//dummy class - all plugins should be based off this.
abstract class deadline_plugin {

    private $plugin_weight = 0;

    public function get_links($linkarray) {
        return '';
    }

    /**
     * Get the weight for a particular plugin.
     *
     * @return number
     */
    public final function get_deadline_plugin_weight() {
        return $this->plugin_weight;
    }

    public final function module_supports_deadlines($modname) {
        return plugin_supports('mod', $modname, FEATURE_DEADLINE);
    }

    /**
     * hook to add deadline specific settings to a module settings page
     * @param object $mform  - Moodle form
     * @param object $context - current context
     * @param string $modulename - Name of the module
     */
    abstract public function get_form_elements($mform, $context, $modulename = "");

    /**
     * hook to save extensions specific settings on a module settings page
     * @param object $data - data from an mform submission.
     */
    abstract public function save_form_elements($data);

    /**
     * Hook for getting a deadline for a course module id
     * @param int $cmid
     *
     */
    abstract public function get_deadline($cmid, $deadline_type);

    /**
     * hook for cron
     *
     */
    abstract public function deadline_cron();

    /**
     * Method for ordering all plugins, higher weighted plugins have their
     * deadlines returned prior to lower weighted plugins
     */
    public final function order_plugins() {

        // load a list of all the deadline plugins
        $plugins[] = new object();


        // sort the deadline plugins based on their weights. Plugins
        // with a higher weight will have their deadlines take precedence.
        $weight = 'plugin_weight';
        usort($plugins, function($a, $b) use ($weight) {
            return $a->{$weight} > $b->{$weight} ? -1 : 1;
        });

        return $plugins;
    }

}
