<?php
namespace PiperPrivacy\Includes;

use PiperPrivacy\Includes\Post_Types\Privacy_Collection;
use PiperPrivacy\Includes\Post_Types\Privacy_Threshold;
use PiperPrivacy\Includes\Post_Types\Privacy_Impact;

/**
 * Fired during plugin activation
 *
 * @package    PiperPrivacy
 * @subpackage PiperPrivacy/includes
 */
class Activator {
    /**
     * Activate the plugin
     */
    public static function activate() {
        global $wp_rewrite;
        
        // Create required database tables
        self::create_tables();
        
        // Register post types first so capabilities are available
        self::register_post_types();
        
        // Set up roles and capabilities
        self::setup_roles();
        
        // Create default taxonomies
        self::create_taxonomies();
        
        // Flush rewrite rules
        $wp_rewrite->flush_rules();
    }

    /**
     * Create required database tables
     */
    private static function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Audit Log table
        $table_name = $wpdb->prefix . 'piper_privacy_audit_log';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            action varchar(50) NOT NULL,
            object_type varchar(50) NOT NULL,
            object_id bigint(20) NOT NULL,
            details longtext NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY object_type (object_type),
            KEY object_id (object_id)
        ) $charset_collate;";

        // Workflow History table
        $table_name = $wpdb->prefix . 'piper_privacy_workflow_history';
        $sql .= "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            workflow_id bigint(20) NOT NULL,
            object_id bigint(20) NOT NULL,
            from_stage varchar(50) NOT NULL,
            to_stage varchar(50) NOT NULL,
            user_id bigint(20) NOT NULL,
            comments text,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY workflow_id (workflow_id),
            KEY object_id (object_id),
            KEY user_id (user_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Register post types
     */
    private static function register_post_types() {
        try {
            error_log('Registering Privacy_Collection post type');
            Privacy_Collection::register();
            error_log('Privacy_Collection registered');
            
            error_log('Registering Privacy_Threshold post type');
            Privacy_Threshold::register();
            error_log('Privacy_Threshold registered');
            
            error_log('Registering Privacy_Impact post type');
            Privacy_Impact::register();
            error_log('Privacy_Impact registered');
        } catch (\Exception $e) {
            error_log('Error registering post types: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Create default taxonomies
     */
    private static function create_taxonomies() {
        // First register the taxonomies
        register_taxonomy('privacy_collection_status', 'privacy_collection', [
            'hierarchical' => true,
            'labels' => [
                'name' => __('Collection Statuses', 'piper-privacy'),
                'singular_name' => __('Collection Status', 'piper-privacy'),
            ],
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => ['slug' => 'collection-status'],
            'capabilities' => [
                'manage_terms' => 'edit_privacy_collections',
                'edit_terms' => 'edit_privacy_collections',
                'delete_terms' => 'edit_privacy_collections',
                'assign_terms' => 'edit_privacy_collections'
            ]
        ]);

        register_taxonomy('privacy_threshold_status', 'privacy_threshold', [
            'hierarchical' => true,
            'labels' => [
                'name' => __('Threshold Statuses', 'piper-privacy'),
                'singular_name' => __('Threshold Status', 'piper-privacy'),
            ],
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => ['slug' => 'threshold-status'],
            'capabilities' => [
                'manage_terms' => 'edit_privacy_thresholds',
                'edit_terms' => 'edit_privacy_thresholds',
                'delete_terms' => 'edit_privacy_thresholds',
                'assign_terms' => 'edit_privacy_thresholds'
            ]
        ]);

        register_taxonomy('privacy_impact_risk', 'privacy_impact', [
            'hierarchical' => true,
            'labels' => [
                'name' => __('Impact Risk Levels', 'piper-privacy'),
                'singular_name' => __('Impact Risk Level', 'piper-privacy'),
            ],
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => ['slug' => 'impact-risk'],
            'capabilities' => [
                'manage_terms' => 'edit_privacy_impacts',
                'edit_terms' => 'edit_privacy_impacts',
                'delete_terms' => 'edit_privacy_impacts',
                'assign_terms' => 'edit_privacy_impacts'
            ]
        ]);

        // Then add the terms
        wp_insert_term('Draft', 'privacy_collection_status');
        wp_insert_term('Under Review', 'privacy_collection_status');
        wp_insert_term('Approved', 'privacy_collection_status');
        wp_insert_term('Retired', 'privacy_collection_status');

        wp_insert_term('Draft', 'privacy_threshold_status');
        wp_insert_term('Under Review', 'privacy_threshold_status');
        wp_insert_term('Approved', 'privacy_threshold_status');

        wp_insert_term('Low', 'privacy_impact_risk');
        wp_insert_term('Medium', 'privacy_impact_risk');
        wp_insert_term('High', 'privacy_impact_risk');
    }

    /**
     * Set up roles and capabilities
     */
    private static function setup_roles() {
        // Add administrator capabilities first
        $admin = get_role('administrator');
        if ($admin) {
            // Privacy Collections
            $admin->add_cap('read_privacy_collection');
            $admin->add_cap('read_private_privacy_collections');
            $admin->add_cap('edit_privacy_collection');
            $admin->add_cap('edit_privacy_collections');
            $admin->add_cap('edit_others_privacy_collections');
            $admin->add_cap('edit_published_privacy_collections');
            $admin->add_cap('publish_privacy_collections');
            $admin->add_cap('delete_privacy_collections');
            $admin->add_cap('delete_others_privacy_collections');
            $admin->add_cap('delete_published_privacy_collections');
            $admin->add_cap('delete_private_privacy_collections');

            // Privacy Thresholds
            $admin->add_cap('read_privacy_threshold');
            $admin->add_cap('read_private_privacy_thresholds');
            $admin->add_cap('edit_privacy_threshold');
            $admin->add_cap('edit_privacy_thresholds');
            $admin->add_cap('edit_others_privacy_thresholds');
            $admin->add_cap('edit_published_privacy_thresholds');
            $admin->add_cap('publish_privacy_thresholds');
            $admin->add_cap('delete_privacy_thresholds');
            $admin->add_cap('delete_others_privacy_thresholds');
            $admin->add_cap('delete_published_privacy_thresholds');
            $admin->add_cap('delete_private_privacy_thresholds');

            // Privacy Impacts
            $admin->add_cap('read_privacy_impact');
            $admin->add_cap('read_private_privacy_impacts');
            $admin->add_cap('edit_privacy_impact');
            $admin->add_cap('edit_privacy_impacts');
            $admin->add_cap('edit_others_privacy_impacts');
            $admin->add_cap('edit_published_privacy_impacts');
            $admin->add_cap('publish_privacy_impacts');
            $admin->add_cap('delete_privacy_impacts');
            $admin->add_cap('delete_others_privacy_impacts');
            $admin->add_cap('delete_published_privacy_impacts');
            $admin->add_cap('delete_private_privacy_impacts');
        }

        // Privacy Officer role
        add_role('privacy_officer', __('Privacy Officer', 'piper-privacy'), [
            'read' => true,
            // Privacy Collections
            'read_privacy_collection' => true,
            'read_private_privacy_collections' => true,
            'edit_privacy_collection' => true,
            'edit_privacy_collections' => true,
            'edit_others_privacy_collections' => true,
            'edit_published_privacy_collections' => true,
            'publish_privacy_collections' => true,
            'delete_privacy_collections' => true,
            'delete_others_privacy_collections' => true,
            'delete_published_privacy_collections' => true,
            'delete_private_privacy_collections' => true,
            
            // Privacy Thresholds
            'read_privacy_threshold' => true,
            'read_private_privacy_thresholds' => true,
            'edit_privacy_threshold' => true,
            'edit_privacy_thresholds' => true,
            'edit_others_privacy_thresholds' => true,
            'edit_published_privacy_thresholds' => true,
            'publish_privacy_thresholds' => true,
            'delete_privacy_thresholds' => true,
            'delete_others_privacy_thresholds' => true,
            'delete_published_privacy_thresholds' => true,
            'delete_private_privacy_thresholds' => true,
            
            // Privacy Impacts
            'read_privacy_impact' => true,
            'read_private_privacy_impacts' => true,
            'edit_privacy_impact' => true,
            'edit_privacy_impacts' => true,
            'edit_others_privacy_impacts' => true,
            'edit_published_privacy_impacts' => true,
            'publish_privacy_impacts' => true,
            'delete_privacy_impacts' => true,
            'delete_others_privacy_impacts' => true,
            'delete_published_privacy_impacts' => true,
            'delete_private_privacy_impacts' => true,
        ]);

        // System Owner role
        add_role('system_owner', __('System Owner', 'piper-privacy'), [
            'read' => true,
            // Basic editing capabilities
            'read_privacy_collection' => true,
            'edit_privacy_collection' => true,
            'edit_privacy_collections' => true,
            'read_privacy_threshold' => true,
            'edit_privacy_threshold' => true,
            'edit_privacy_thresholds' => true,
            'read_privacy_impact' => true,
            'edit_privacy_impact' => true,
            'edit_privacy_impacts' => true,
        ]);
    }
}
