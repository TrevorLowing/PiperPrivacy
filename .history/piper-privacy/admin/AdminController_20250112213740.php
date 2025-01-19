<?php
namespace PiperPrivacy\Admin;

/**
 * The admin-specific functionality of the plugin
 *
 * @package    PiperPrivacy
 * @subpackage PiperPrivacy/admin
 */
class AdminController {
    /**
     * Initialize the admin interface
     */
    public function __construct() {
        add_action('admin_menu', [$this, 'register_admin_pages']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    /**
     * Register admin pages
     */
    public function register_admin_pages() {
        // Main menu
        add_menu_page(
            __('Privacy Manager', 'piper-privacy'),
            __('Privacy Manager', 'piper-privacy'),
            'manage_options',
            'piper-privacy',
            [$this, 'render_dashboard_page'],
            'dashicons-shield',
            30
        );

        // Workflow Dashboard submenu
        add_submenu_page(
            'piper-privacy',
            __('Workflow Dashboard', 'piper-privacy'),
            __('Workflow', 'piper-privacy'),
            'manage_options',
            'piper-privacy-workflow',
            [$this, 'render_workflow_dashboard']
        );

        // Reports submenu
        add_submenu_page(
            'piper-privacy',
            __('Reports', 'piper-privacy'),
            __('Reports', 'piper-privacy'),
            'manage_options',
            'piper-privacy-reports',
            [$this, 'render_reports_page']
        );

        // Settings submenu
        add_submenu_page(
            'piper-privacy',
            __('Settings', 'piper-privacy'),
            __('Settings', 'piper-privacy'),
            'manage_options',
            'piper-privacy-settings',
            [$this, 'render_settings_page']
        );
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets() {
        $screen = get_current_screen();
        
        // Common admin styles
        wp_enqueue_style(
            'piper-privacy-admin',
            PIPER_PRIVACY_URL . 'admin/css/admin.css',
            [],
            PIPER_PRIVACY_VERSION
        );

        // Workflow dashboard styles
        if ($screen->id === 'privacy-manager_page_piper-privacy-workflow') {
            wp_enqueue_style(
                'piper-privacy-workflow-dashboard',
                PIPER_PRIVACY_URL . 'admin/css/workflow-dashboard.css',
                [],
                PIPER_PRIVACY_VERSION
            );
        }

        wp_enqueue_script(
            'piper-privacy-admin',
            PIPER_PRIVACY_URL . 'admin/js/admin.js',
            ['jquery', 'wp-api'],
            PIPER_PRIVACY_VERSION,
            true
        );

        wp_localize_script('piper-privacy-admin', 'piperPrivacyAdmin', [
            'nonce' => wp_create_nonce('wp_rest'),
            'restUrl' => rest_url('piper-privacy/v1'),
        ]);
    }

    /**
     * Render dashboard page
     */
    public function render_dashboard_page() {
        // Get statistics
        $stats = $this->get_dashboard_statistics();

        // Render the dashboard template
        include PIPER_PRIVACY_DIR . 'admin/templates/dashboard.php';
    }

    /**
     * Render workflow dashboard page
     */
    public function render_workflow_dashboard() {
        require_once PIPER_PRIVACY_DIR . 'admin/templates/workflow-dashboard.php';
    }

    /**
     * Render reports page
     */
    public function render_reports_page() {
        // Get report data
        $reports = $this->get_report_data();

        // Render the reports template
        include PIPER_PRIVACY_DIR . 'admin/templates/reports.php';
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        // Get current settings
        $settings = get_option('piper_privacy_settings', []);

        // Handle form submission
        if (isset($_POST['piper_privacy_settings_nonce']) && 
            wp_verify_nonce($_POST['piper_privacy_settings_nonce'], 'piper_privacy_settings')) {
            
            $settings = $this->save_settings($_POST);
        }

        // Render the settings template
        include PIPER_PRIVACY_DIR . 'admin/templates/settings.php';
    }

    /**
     * Get dashboard statistics
     *
     * @return array Dashboard statistics
     */
    private function get_dashboard_statistics() {
        $stats = [
            'collections' => [
                'total' => wp_count_posts('privacy_collection')->publish,
                'draft' => wp_count_posts('privacy_collection')->draft,
                'pending' => wp_count_posts('privacy_collection')->pending,
            ],
            'thresholds' => [
                'total' => wp_count_posts('privacy_threshold')->publish,
                'draft' => wp_count_posts('privacy_threshold')->draft,
                'pending' => wp_count_posts('privacy_threshold')->pending,
            ],
            'impacts' => [
                'total' => wp_count_posts('privacy_impact')->publish,
                'draft' => wp_count_posts('privacy_impact')->draft,
                'pending' => wp_count_posts('privacy_impact')->pending,
            ],
        ];

        return $stats;
    }

    /**
     * Get report data
     *
     * @return array Report data
     */
    private function get_report_data() {
        global $wpdb;

        $reports = [
            'workflow_history' => $wpdb->get_results(
                "SELECT h.*, p.post_title, u.display_name
                FROM {$wpdb->prefix}piper_privacy_workflow_history h
                LEFT JOIN {$wpdb->posts} p ON h.object_id = p.ID
                LEFT JOIN {$wpdb->users} u ON h.user_id = u.ID
                ORDER BY h.created_at DESC
                LIMIT 100"
            ),
            'audit_log' => $wpdb->get_results(
                "SELECT l.*, u.display_name
                FROM {$wpdb->prefix}piper_privacy_audit_log l
                LEFT JOIN {$wpdb->users} u ON l.user_id = u.ID
                ORDER BY l.created_at DESC
                LIMIT 100"
            ),
        ];

        return $reports;
    }

    /**
     * Save settings
     *
     * @param array $data Form data
     * @return array Updated settings
     */
    private function save_settings($data) {
        $settings = [
            'email_notifications' => isset($data['email_notifications']) ? 1 : 0,
            'notification_recipients' => sanitize_textarea_field($data['notification_recipients'] ?? ''),
            'auto_expire_collections' => isset($data['auto_expire_collections']) ? 1 : 0,
            'expiration_warning_days' => absint($data['expiration_warning_days'] ?? 30),
            'enable_audit_logging' => isset($data['enable_audit_logging']) ? 1 : 0,
            'audit_retention_days' => absint($data['audit_retention_days'] ?? 365),
        ];

        update_option('piper_privacy_settings', $settings);

        add_settings_error(
            'piper_privacy_settings',
            'settings_updated',
            __('Settings saved successfully.', 'piper-privacy'),
            'updated'
        );

        return $settings;
    }
}