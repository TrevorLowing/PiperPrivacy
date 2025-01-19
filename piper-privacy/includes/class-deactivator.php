<?php
namespace PiperPrivacy\Includes;

/**
 * Fired during plugin deactivation
 *
 * @package    PiperPrivacy
 * @subpackage PiperPrivacy/includes
 */
class Deactivator {
    /**
     * Deactivate the plugin
     */
    public static function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();

        // We don't remove roles, capabilities, or data on deactivation
        // This should only be done on uninstall if requested by the user
    }
}
