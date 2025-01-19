<?php
namespace PiperPrivacy\Admin\Dashboard;

/**
 * Dashboard Charts Controller
 */
class DashboardCharts {
    /**
     * Initialize charts
     */
    public function __construct() {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_chart_assets']);
        add_action('wp_ajax_get_privacy_trend_data', [$this, 'get_trend_data']);
        add_action('wp_ajax_get_compliance_distribution', [$this, 'get_compliance_distribution']);
    }

    /**
     * Enqueue chart assets
     */
    public function enqueue_chart_assets($hook) {
        if ('toplevel_page_piper-privacy-dashboard' !== $hook) {
            return;
        }

        wp_enqueue_script(
            'chartjs',
            'https://cdn.jsdelivr.net/npm/chart.js',
            [],
            '4.0.0',
            true
        );

        wp_enqueue_script(
            'piper-privacy-charts',
            PIPER_PRIVACY_URL . 'assets/js/dashboard-charts.js',
            ['jquery', 'chartjs'],
            PIPER_PRIVACY_VERSION,
            true
        );

        wp_localize_script('piper-privacy-charts', 'piperPrivacyCharts', [
            'nonce' => wp_create_nonce('piper_privacy_charts'),
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'colors' => [
                'primary' => '#2271b1',
                'success' => '#198754',
                'warning' => '#ffc107',
                'danger' => '#dc3545',
                'info' => '#0dcaf0'
            ]
        ]);
    }

    /**
     * Get trend data for charts
     */
    public function get_trend_data() {
        check_ajax_referer('piper_privacy_charts', 'nonce');

        global $wpdb;

        // Get collection trends over time
        $trends = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                DATE_FORMAT(created_at, '%%Y-%%m') as month,
                COUNT(*) as count,
                status
            FROM {$wpdb->prefix}piper_privacy_collections
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%%Y-%%m'), status
            ORDER BY month ASC"
        ));

        // Get compliance trends
        $compliance = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                DATE_FORMAT(measured_at, '%%Y-%%m') as month,
                AVG(rate) as rate
            FROM {$wpdb->prefix}piper_privacy_compliance_history
            WHERE measured_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(measured_at, '%%Y-%%m')
            ORDER BY month ASC"
        ));

        wp_send_json_success([
            'collections' => $this->format_trend_data($trends),
            'compliance' => $this->format_compliance_data($compliance)
        ]);
    }

    /**
     * Get compliance distribution data
     */
    public function get_compliance_distribution() {
        check_ajax_referer('piper_privacy_charts', 'nonce');

        global $wpdb;

        // Get PTA status distribution
        $pta_stats = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                status,
                COUNT(*) as count
            FROM {$wpdb->prefix}piper_privacy_threshold
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY status"
        ));

        // Get PIA status distribution
        $pia_stats = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                status,
                COUNT(*) as count
            FROM {$wpdb->prefix}piper_privacy_impact
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY status"
        ));

        wp_send_json_success([
            'pta' => $this->format_distribution_data($pta_stats),
            'pia' => $this->format_distribution_data($pia_stats)
        ]);
    }

    /**
     * Format trend data for charts
     */
    private function format_trend_data($trends) {
        $data = [];
        $months = [];
        $statuses = [];

        // Collect unique months and statuses
        foreach ($trends as $trend) {
            if (!in_array($trend->month, $months)) {
                $months[] = $trend->month;
            }
            if (!in_array($trend->status, $statuses)) {
                $statuses[] = $trend->status;
            }
        }

        // Initialize data structure
        foreach ($statuses as $status) {
            $data[$status] = array_fill(0, count($months), 0);
        }

        // Fill in actual values
        foreach ($trends as $trend) {
            $month_index = array_search($trend->month, $months);
            $data[$trend->status][$month_index] = (int) $trend->count;
        }

        return [
            'labels' => $months,
            'datasets' => array_map(function($status) use ($data) {
                return [
                    'label' => ucfirst($status),
                    'data' => $data[$status]
                ];
            }, $statuses)
        ];
    }

    /**
     * Format compliance data for charts
     */
    private function format_compliance_data($compliance) {
        $labels = [];
        $rates = [];

        foreach ($compliance as $item) {
            $labels[] = $item->month;
            $rates[] = (float) $item->rate;
        }

        return [
            'labels' => $labels,
            'datasets' => [[
                'label' => __('Compliance Rate', 'piper-privacy'),
                'data' => $rates
            ]]
        ];
    }

    /**
     * Format distribution data for charts
     */
    private function format_distribution_data($stats) {
        $labels = [];
        $counts = [];

        foreach ($stats as $stat) {
            $labels[] = ucfirst($stat->status);
            $counts[] = (int) $stat->count;
        }

        return [
            'labels' => $labels,
            'data' => $counts
        ];
    }
}