<?php
namespace PiperPrivacy\Admin;

/**
 * The admin-specific functionality of the plugin.
 *
 * @package    PiperPrivacy
 * @subpackage PiperPrivacy/admin
 */
class Admin {
    /**
     * Initialize the class and set its properties.
     */
    public function __construct() {
    }

    /**
     * Register the stylesheets for the admin area.
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            'piper-privacy-admin',
            plugin_dir_url(__FILE__) . 'css/piper-privacy-admin.css',
            array(),
            PIPER_PRIVACY_VERSION,
            'all'
        );
        
        // Enqueue WordPress dashicons
        wp_enqueue_style('dashicons');
    }

    /**
     * Register the JavaScript for the admin area.
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            'piper-privacy-admin',
            plugin_dir_url(__FILE__) . 'js/piper-privacy-admin.js',
            array('jquery'),
            PIPER_PRIVACY_VERSION,
            false
        );
    }

    /**
     * Register the admin menu pages and subpages.
     */
    public function add_plugin_admin_menu() {
        // Main menu page
        add_menu_page(
            __('PiperPrivacy', 'piper-privacy'),
            __('PiperPrivacy', 'piper-privacy'),
            'manage_options',
            'piper-privacy',
            array($this, 'display_plugin_admin_page'),
            'dashicons-shield',
            30
        );

        // Planning subpages
        add_submenu_page(
            'piper-privacy',
            __('Stakeholders', 'piper-privacy'),
            __('Stakeholders', 'piper-privacy'),
            'manage_options',
            'piper-privacy-stakeholders',
            array($this, 'display_stakeholders_page')
        );

        // Implementation subpages
        add_submenu_page(
            'piper-privacy',
            __('Privacy Controls', 'piper-privacy'),
            __('Controls', 'piper-privacy'),
            'manage_options',
            'piper-privacy-controls',
            array($this, 'display_controls_page')
        );

        add_submenu_page(
            'piper-privacy',
            __('Documentation', 'piper-privacy'),
            __('Documentation', 'piper-privacy'),
            'manage_options',
            'piper-privacy-documentation',
            array($this, 'display_documentation_page')
        );

        add_submenu_page(
            'piper-privacy',
            __('Training Materials', 'piper-privacy'),
            __('Training', 'piper-privacy'),
            'manage_options',
            'piper-privacy-training',
            array($this, 'display_training_page')
        );

        // Monitoring subpages
        add_submenu_page(
            'piper-privacy',
            __('Reviews', 'piper-privacy'),
            __('Reviews', 'piper-privacy'),
            'manage_options',
            'piper-privacy-reviews',
            array($this, 'display_reviews_page')
        );

        add_submenu_page(
            'piper-privacy',
            __('Incidents', 'piper-privacy'),
            __('Incidents', 'piper-privacy'),
            'manage_options',
            'piper-privacy-incidents',
            array($this, 'display_incidents_page')
        );

        add_submenu_page(
            'piper-privacy',
            __('Privacy Metrics', 'piper-privacy'),
            __('Metrics', 'piper-privacy'),
            'manage_options',
            'piper-privacy-metrics',
            array($this, 'display_metrics_page')
        );

        // Maintenance subpages
        add_submenu_page(
            'piper-privacy',
            __('Updates', 'piper-privacy'),
            __('Updates', 'piper-privacy'),
            'manage_options',
            'piper-privacy-updates',
            array($this, 'display_updates_page')
        );

        add_submenu_page(
            'piper-privacy',
            __('Compliance', 'piper-privacy'),
            __('Compliance', 'piper-privacy'),
            'manage_options',
            'piper-privacy-compliance',
            array($this, 'display_compliance_page')
        );

        add_submenu_page(
            'piper-privacy',
            __('Retention', 'piper-privacy'),
            __('Retention', 'piper-privacy'),
            'manage_options',
            'piper-privacy-retention',
            array($this, 'display_retention_page')
        );

        // Tools subpages
        add_submenu_page(
            'piper-privacy',
            __('Export', 'piper-privacy'),
            __('Export', 'piper-privacy'),
            'manage_options',
            'piper-privacy-export',
            array($this, 'display_export_page')
        );

        add_submenu_page(
            'piper-privacy',
            __('Breach Management', 'piper-privacy'),
            __('Breach', 'piper-privacy'),
            'manage_options',
            'piper-privacy-breach',
            array($this, 'display_breach_page')
        );
    }

    /**
     * Display methods for admin pages
     */
    public function display_plugin_admin_page() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/admin-display.php';
    }

    public function display_stakeholders_page() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/stakeholders-display.php';
    }

    public function display_controls_page() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/controls-display.php';
    }

    public function display_documentation_page() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/documentation-display.php';
    }

    public function display_training_page() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/training-display.php';
    }

    public function display_reviews_page() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/reviews-display.php';
    }

    public function display_incidents_page() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/incidents-display.php';
    }

    public function display_metrics_page() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/metrics-display.php';
    }

    public function display_updates_page() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/updates-display.php';
    }

    public function display_compliance_page() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/compliance-display.php';
    }

    public function display_retention_page() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/retention-display.php';
    }

    public function display_export_page() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/export-display.php';
    }

    public function display_breach_page() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/breach-display.php';
    }
}
