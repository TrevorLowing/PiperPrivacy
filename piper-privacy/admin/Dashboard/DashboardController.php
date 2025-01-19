<?php
namespace PiperPrivacy\Admin\Dashboard;

/**
 * Main Dashboard Controller
 */
class DashboardController {
    /**
     * Initialize the dashboard
     */
    public function __construct() {
        add_action('admin_menu', [$this, 'register_dashboard_page']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_dashboard_assets']);
        add_action('wp_ajax_get_privacy_collection_stats', [$this, 'get_collection_stats']);
        add_action('wp_ajax_get_privacy_workflow_metrics', [$this, 'get_workflow_metrics']);
    }

    /**
     * Register dashboard page
     */
    public function register_dashboard_page() {
        add_menu_page(
            __('Privacy Management', 'piper-privacy'),
            __('Privacy Management', 'piper-privacy'),
            'manage_privacy_collections',
            'piper-privacy-dashboard',
            [$this, 'render_dashboard'],
            'dashicons-shield',
            30
        );
    }

    /**
     * Enqueue dashboard assets
     */
    public function enqueue_dashboard_assets($hook) {
        if ('toplevel_page_piper-privacy-dashboard' !== $hook) {
            return;
        }

        wp_enqueue_style(
            'piper-privacy-dashboard',
            PIPER_PRIVACY_URL . 'assets/css/dashboard.css',
            [],
            PIPER_PRIVACY_VERSION
        );

        wp_enqueue_script(
            'piper-privacy-dashboard',
            PIPER_PRIVACY_URL . 'assets/js/dashboard.js',
            ['jquery', 'wp-api', 'chartjs'],
            PIPER_PRIVACY_VERSION,
            true
        );

        wp_localize_script('piper-privacy-dashboard', 'piperPrivacyDashboard', [
            'nonce' => wp_create_nonce('piper_privacy_dashboard'),
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'i18n' => [
                'loadingStats' => __('Loading statistics...', 'piper-privacy'),
                'errorLoading' => __('Error loading data', 'piper-privacy'),
                'noData' => __('No data available', 'piper-privacy')
            ]
        ]);
    }

    /**
     * Render dashboard page
     */
    public function render_dashboard() {
        $workflow_stats = $this->get_workflow_statistics();
        $recent_activity = $this->get_recent_activity();
        $upcoming_actions = $this->get_upcoming_actions();
        $compliance_metrics = $this->get_compliance_metrics();

        include PIPER_PRIVACY_DIR . 'admin/views/dashboard.php';
    }

    /**
     * Get workflow statistics
     */
    private function get_workflow_statistics() {
        return [
            'total_collections' => $this->count_collections(),
            'active_workflows' => $this->count_active_workflows(),
            'pending_reviews' => $this->count_pending_reviews(),
            'compliance_rate' => $this->calculate_compliance_rate()
        ];
    }

    /**
     * Get recent activity
     */
    private function get_recent_activity() {
        global $wpdb;

        $activity = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}piper_privacy_activity_log 
            WHERE created_at >= %s 
            ORDER BY created_at DESC 
            LIMIT 10",
            date('Y-m-d H:i:s', strtotime('-30 days'))
        ));

        return array_map([$this, 'format_activity'], $activity);
    }

    /**
     * Get upcoming actions
     */
    private function get_upcoming_actions() {
        $actions = [];

        // Get reviews due in next 30 days
        $reviews = $this->get_upcoming_reviews();
        foreach ($reviews as $review) {
            $actions[] = [
                'type' => 'review',
                'title' => sprintf(
                    __('Review Due: %s', 'piper-privacy'),
                    get_the_title($review->collection_id)
                ),
                'due_date' => $review->due_date,
                'url' => $this->get_review_url($review->id),
                'priority' => $this->calculate_priority($review)
            ];
        }

        // Get implementations due
        $implementations = $this->get_upcoming_implementations();
        foreach ($implementations as $implementation) {
            $actions[] = [
                'type' => 'implementation',
                'title' => sprintf(
                    __('Implementation Due: %s', 'piper-privacy'),
                    get_the_title($implementation->collection_id)
                ),
                'due_date' => $implementation->due_date,
                'url' => $this->get_implementation_url($implementation->id),
                'priority' => $this->calculate_priority($implementation)
            ];
        }

        // Sort by due date and priority
        usort($actions, function($a, $b) {
            $date_diff = strtotime($a['due_date']) - strtotime($b['due_date']);
            if ($date_diff === 0) {
                return $b['priority'] - $a['priority'];
            }
            return $date_diff;
        });

        return $actions;
    }

    /**
     * Get compliance metrics
     */
    private function get_compliance_metrics() {
        return [
            'overall_rate' => $this->calculate_overall_compliance(),
            'review_completion' => $this->calculate_review_completion_rate(),
            'documentation_completion' => $this->calculate_documentation_completion(),
            'control_implementation' => $this->calculate_control_implementation_rate()
        ];
    }
}