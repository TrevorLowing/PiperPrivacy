<?php
/**
 * Analytics Manager Class
 * 
 * Handles analytics and reporting functionality for privacy collections, impacts, and thresholds.
 *
 * @package PiperPrivacy
 * @subpackage Analytics
 */

namespace PiperPrivacy\Analytics;

class Analytics_Manager {

    /**
     * Instance of this class.
     *
     * @var object
     */
    protected static $instance = null;

    /**
     * Get instance of the class
     *
     * @return object Instance of the class
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize the class
     */
    public function __construct() {
        add_action('admin_init', array($this, 'init_analytics'));
        add_action('wp_ajax_get_privacy_stats', array($this, 'get_privacy_stats'));
        add_action('wp_ajax_export_analytics_report', array($this, 'export_analytics_report'));
    }

    /**
     * Initialize analytics
     */
    public function init_analytics() {
        // Register scripts and styles for analytics dashboard
        wp_register_script(
            'piper-privacy-analytics',
            PIPER_PRIVACY_URL . 'assets/js/dashboard-charts.js',
            array('jquery', 'chart-js'),
            PIPER_PRIVACY_VERSION,
            true
        );

        wp_register_style(
            'piper-privacy-analytics',
            PIPER_PRIVACY_URL . 'assets/css/dashboard.css',
            array(),
            PIPER_PRIVACY_VERSION
        );
    }

    /**
     * Get privacy statistics
     */
    public function get_privacy_stats() {
        check_ajax_referer('piper_privacy_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }

        $stats = array(
            'collections' => $this->get_collection_stats(),
            'impacts' => $this->get_impact_stats(),
            'thresholds' => $this->get_threshold_stats(),
            'workflow' => $this->get_workflow_stats()
        );

        wp_send_json_success($stats);
    }

    /**
     * Get collection statistics
     *
     * @return array Collection statistics
     */
    private function get_collection_stats() {
        $collections = get_posts(array(
            'post_type' => 'privacy_collection',
            'posts_per_page' => -1,
            'post_status' => 'any'
        ));

        $stats = array(
            'total' => count($collections),
            'by_status' => array(),
            'by_type' => array(),
            'trend' => $this->get_trend_data('privacy_collection')
        );

        foreach ($collections as $collection) {
            $status = get_post_meta($collection->ID, '_workflow_status', true);
            $type = get_post_meta($collection->ID, '_collection_type', true);

            if (!isset($stats['by_status'][$status])) {
                $stats['by_status'][$status] = 0;
            }
            $stats['by_status'][$status]++;

            if (!isset($stats['by_type'][$type])) {
                $stats['by_type'][$type] = 0;
            }
            $stats['by_type'][$type]++;
        }

        return $stats;
    }

    /**
     * Get impact assessment statistics
     *
     * @return array Impact assessment statistics
     */
    private function get_impact_stats() {
        $impacts = get_posts(array(
            'post_type' => 'privacy_impact',
            'posts_per_page' => -1,
            'post_status' => 'any'
        ));

        $stats = array(
            'total' => count($impacts),
            'by_status' => array(),
            'by_risk_level' => array(),
            'trend' => $this->get_trend_data('privacy_impact')
        );

        foreach ($impacts as $impact) {
            $status = get_post_meta($impact->ID, '_workflow_status', true);
            $risk_level = get_post_meta($impact->ID, '_risk_level', true);

            if (!isset($stats['by_status'][$status])) {
                $stats['by_status'][$status] = 0;
            }
            $stats['by_status'][$status]++;

            if (!isset($stats['by_risk_level'][$risk_level])) {
                $stats['by_risk_level'][$risk_level] = 0;
            }
            $stats['by_risk_level'][$risk_level]++;
        }

        return $stats;
    }

    /**
     * Get threshold assessment statistics
     *
     * @return array Threshold assessment statistics
     */
    private function get_threshold_stats() {
        $thresholds = get_posts(array(
            'post_type' => 'privacy_threshold',
            'posts_per_page' => -1,
            'post_status' => 'any'
        ));

        $stats = array(
            'total' => count($thresholds),
            'by_status' => array(),
            'by_outcome' => array(),
            'trend' => $this->get_trend_data('privacy_threshold')
        );

        foreach ($thresholds as $threshold) {
            $status = get_post_meta($threshold->ID, '_workflow_status', true);
            $outcome = get_post_meta($threshold->ID, '_assessment_outcome', true);

            if (!isset($stats['by_status'][$status])) {
                $stats['by_status'][$status] = 0;
            }
            $stats['by_status'][$status]++;

            if (!isset($stats['by_outcome'][$outcome])) {
                $stats['by_outcome'][$outcome] = 0;
            }
            $stats['by_outcome'][$outcome]++;
        }

        return $stats;
    }

    /**
     * Get workflow statistics
     *
     * @return array Workflow statistics
     */
    private function get_workflow_stats() {
        $stats = array(
            'average_completion_time' => $this->calculate_average_completion_time(),
            'overdue_tasks' => $this->get_overdue_tasks_count(),
            'completion_rate' => $this->calculate_completion_rate(),
            'bottlenecks' => $this->identify_workflow_bottlenecks()
        );

        return $stats;
    }

    /**
     * Calculate average completion time for workflows
     *
     * @return float Average completion time in days
     */
    private function calculate_average_completion_time() {
        global $wpdb;

        $completion_times = $wpdb->get_col("
            SELECT TIMESTAMPDIFF(DAY, 
                MIN(CASE WHEN meta_key = '_workflow_start_date' THEN meta_value END),
                MAX(CASE WHEN meta_key = '_workflow_completion_date' THEN meta_value END)
            ) as completion_time
            FROM {$wpdb->postmeta}
            WHERE post_id IN (
                SELECT ID FROM {$wpdb->posts} 
                WHERE post_type IN ('privacy_collection', 'privacy_impact', 'privacy_threshold')
            )
            AND meta_key IN ('_workflow_start_date', '_workflow_completion_date')
            GROUP BY post_id
            HAVING completion_time IS NOT NULL
        ");

        if (empty($completion_times)) {
            return 0;
        }

        return array_sum($completion_times) / count($completion_times);
    }

    /**
     * Get count of overdue tasks
     *
     * @return int Number of overdue tasks
     */
    private function get_overdue_tasks_count() {
        global $wpdb;

        return $wpdb->get_var("
            SELECT COUNT(*)
            FROM {$wpdb->postmeta}
            WHERE meta_key = '_workflow_due_date'
            AND meta_value < CURRENT_DATE()
            AND post_id IN (
                SELECT ID FROM {$wpdb->posts}
                WHERE post_type IN ('privacy_collection', 'privacy_impact', 'privacy_threshold')
                AND post_status != 'completed'
            )
        ");
    }

    /**
     * Calculate workflow completion rate
     *
     * @return float Completion rate as percentage
     */
    private function calculate_completion_rate() {
        global $wpdb;

        $total = $wpdb->get_var("
            SELECT COUNT(*)
            FROM {$wpdb->posts}
            WHERE post_type IN ('privacy_collection', 'privacy_impact', 'privacy_threshold')
        ");

        if ($total === 0) {
            return 0;
        }

        $completed = $wpdb->get_var("
            SELECT COUNT(*)
            FROM {$wpdb->posts}
            WHERE post_type IN ('privacy_collection', 'privacy_impact', 'privacy_threshold')
            AND post_status = 'completed'
        ");

        return ($completed / $total) * 100;
    }

    /**
     * Identify workflow bottlenecks
     *
     * @return array Workflow stages with high processing times
     */
    private function identify_workflow_bottlenecks() {
        global $wpdb;

        $results = $wpdb->get_results("
            SELECT 
                meta_value as stage,
                AVG(TIMESTAMPDIFF(DAY, stage_start, stage_end)) as avg_duration
            FROM (
                SELECT 
                    post_id,
                    meta_value,
                    MIN(meta_date) as stage_start,
                    MAX(meta_date) as stage_end
                FROM {$wpdb->postmeta}
                WHERE meta_key = '_workflow_stage'
                GROUP BY post_id, meta_value
            ) as stage_durations
            GROUP BY stage
            ORDER BY avg_duration DESC
            LIMIT 5
        ");

        return $results;
    }

    /**
     * Get trend data for a specific post type
     *
     * @param string $post_type Post type to get trend data for
     * @return array Trend data
     */
    private function get_trend_data($post_type) {
        global $wpdb;

        $last_12_months = array();
        for ($i = 11; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i months"));
            $last_12_months[$month] = 0;
        }

        $results = $wpdb->get_results($wpdb->prepare("
            SELECT 
                DATE_FORMAT(post_date, '%Y-%m') as month,
                COUNT(*) as count
            FROM {$wpdb->posts}
            WHERE post_type = %s
            AND post_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY month
            ORDER BY month ASC
        ", $post_type));

        foreach ($results as $result) {
            $last_12_months[$result->month] = (int) $result->count;
        }

        return array_values($last_12_months);
    }

    /**
     * Export analytics report
     */
    public function export_analytics_report() {
        check_ajax_referer('piper_privacy_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }

        $stats = array(
            'collections' => $this->get_collection_stats(),
            'impacts' => $this->get_impact_stats(),
            'thresholds' => $this->get_threshold_stats(),
            'workflow' => $this->get_workflow_stats()
        );

        $report = $this->generate_report_content($stats);
        $filename = 'privacy-analytics-' . date('Y-m-d') . '.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // Add report content to CSV
        foreach ($report as $row) {
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }

    /**
     * Generate report content
     *
     * @param array $stats Statistics data
     * @return array Report content
     */
    private function generate_report_content($stats) {
        $report = array(
            array('Privacy Analytics Report - ' . date('Y-m-d')),
            array(''),
            array('Collections Summary'),
            array('Total Collections', $stats['collections']['total']),
            array(''),
            array('Status Breakdown'),
        );

        foreach ($stats['collections']['by_status'] as $status => $count) {
            $report[] = array($status, $count);
        }

        $report = array_merge($report, array(
            array(''),
            array('Impact Assessments Summary'),
            array('Total Impact Assessments', $stats['impacts']['total']),
            array(''),
            array('Risk Level Breakdown'),
        ));

        foreach ($stats['impacts']['by_risk_level'] as $risk_level => $count) {
            $report[] = array($risk_level, $count);
        }

        $report = array_merge($report, array(
            array(''),
            array('Threshold Assessments Summary'),
            array('Total Threshold Assessments', $stats['thresholds']['total']),
            array(''),
            array('Outcome Breakdown'),
        ));

        foreach ($stats['thresholds']['by_outcome'] as $outcome => $count) {
            $report[] = array($outcome, $count);
        }

        $report = array_merge($report, array(
            array(''),
            array('Workflow Metrics'),
            array('Average Completion Time (days)', number_format($stats['workflow']['average_completion_time'], 1)),
            array('Overdue Tasks', $stats['workflow']['overdue_tasks']),
            array('Completion Rate (%)', number_format($stats['workflow']['completion_rate'], 1)),
            array(''),
            array('Workflow Bottlenecks'),
        ));

        foreach ($stats['workflow']['bottlenecks'] as $bottleneck) {
            $report[] = array($bottleneck->stage, number_format($bottleneck->avg_duration, 1) . ' days');
        }

        return $report;
    }
}
