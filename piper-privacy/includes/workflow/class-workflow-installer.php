<?php
namespace PiperPrivacy\Includes\Workflow;

/**
 * Workflow Installer
 * 
 * Handles database table creation and updates for the workflow system
 *
 * @package    PiperPrivacy
 * @subpackage PiperPrivacy/includes/workflow
 */
class WorkflowInstaller {
    /**
     * Install workflow tables and data
     */
    public static function install() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'privacy_workflow_history';

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            old_status varchar(50) NOT NULL,
            new_status varchar(50) NOT NULL,
            user_id bigint(20) NOT NULL,
            comment text,
            date datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY post_id (post_id),
            KEY user_id (user_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Uninstall workflow tables
     */
    public static function uninstall() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'privacy_workflow_history';
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
    }
}
