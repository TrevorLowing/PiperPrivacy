<?php
/**
 * Monitor Class
 *
 * @package PiperPrivacy
 */

namespace PiperPrivacy;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Monitor
 * Handles monitoring and logging of plugin activities
 */
class Monitor {
    /**
     * Error log file
     *
     * @var string
     */
    private $error_log;

    /**
     * Activity log file
     *
     * @var string
     */
    private $activity_log;

    /**
     * Constructor
     */
    public function __construct() {
        $upload_dir = wp_upload_dir();
        $this->error_log = $upload_dir['basedir'] . '/piper-privacy/error.log';
        $this->activity_log = $upload_dir['basedir'] . '/piper-privacy/activity.log';

        // Create log directory if it doesn't exist
        wp_mkdir_p(dirname($this->error_log));

        // Initialize hooks
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('admin_init', [$this, 'check_system_requirements']);
        add_action('admin_notices', [$this, 'display_system_notices']);
        add_action('piper_privacy_form_submitted', [$this, 'log_form_submission'], 10, 2);
        add_action('piper_privacy_error', [$this, 'log_error']);
    }

    /**
     * Check system requirements
     */
    public function check_system_requirements() {
        $requirements = [
            'php' => '7.4',
            'wp' => '5.8',
            'memory_limit' => '64M',
            'max_execution_time' => 30,
            'upload_max_filesize' => '2M',
        ];

        $issues = [];

        // Check PHP version
        if (version_compare(PHP_VERSION, $requirements['php'], '<')) {
            $issues[] = sprintf(
                /* translators: %s: required PHP version */
                __('PHP version %s or higher is required.', 'piper-privacy'),
                $requirements['php']
            );
        }

        // Check WordPress version
        global $wp_version;
        if (version_compare($wp_version, $requirements['wp'], '<')) {
            $issues[] = sprintf(
                /* translators: %s: required WordPress version */
                __('WordPress version %s or higher is required.', 'piper-privacy'),
                $requirements['wp']
            );
        }

        // Check memory limit
        $memory_limit = wp_convert_hr_to_bytes(ini_get('memory_limit'));
        $required_memory = wp_convert_hr_to_bytes($requirements['memory_limit']);
        if ($memory_limit < $required_memory) {
            $issues[] = sprintf(
                /* translators: %s: required memory limit */
                __('Memory limit of %s or higher is required.', 'piper-privacy'),
                $requirements['memory_limit']
            );
        }

        // Store issues for display
        update_option('piper_privacy_system_issues', $issues);
    }

    /**
     * Display system notices
     */
    public function display_system_notices() {
        $issues = get_option('piper_privacy_system_issues', []);

        if (!empty($issues)) {
            echo '<div class="notice notice-error">';
            echo '<p><strong>' . esc_html__('PiperPrivacy System Requirements:', 'piper-privacy') . '</strong></p>';
            echo '<ul>';
            foreach ($issues as $issue) {
                echo '<li>' . esc_html($issue) . '</li>';
            }
            echo '</ul>';
            echo '</div>';
        }
    }

    /**
     * Log form submission
     *
     * @param int    $form_id Form ID.
     * @param string $form_type Form type.
     */
    public function log_form_submission($form_id, $form_type) {
        $message = sprintf(
            '[%s] Form submission: %s (ID: %d)',
            current_time('mysql'),
            $form_type,
            $form_id
        );

        $this->write_to_log($this->activity_log, $message);
    }

    /**
     * Log error
     *
     * @param \WP_Error|string $error Error object or message.
     */
    public function log_error($error) {
        if (is_wp_error($error)) {
            $message = sprintf(
                '[%s] Error: %s (Code: %s)',
                current_time('mysql'),
                $error->get_error_message(),
                $error->get_error_code()
            );
        } else {
            $message = sprintf(
                '[%s] Error: %s',
                current_time('mysql'),
                $error
            );
        }

        $this->write_to_log($this->error_log, $message);
    }

    /**
     * Write to log file
     *
     * @param string $file Log file path.
     * @param string $message Message to log.
     */
    private function write_to_log($file, $message) {
        error_log($message . PHP_EOL, 3, $file);
    }

    /**
     * Get plugin statistics
     *
     * @return array
     */
    public function get_statistics() {
        global $wpdb;

        $stats = [
            'forms' => [
                'collection' => $this->count_posts('privacy_collection'),
                'threshold' => $this->count_posts('privacy_threshold'),
                'impact' => $this->count_posts('privacy_impact'),
            ],
            'users' => count_users(),
            'errors' => $this->count_errors(),
            'performance' => $this->get_performance_metrics(),
        ];

        return $stats;
    }

    /**
     * Count posts of a specific type
     *
     * @param string $post_type Post type.
     * @return int
     */
    private function count_posts($post_type) {
        $counts = wp_count_posts($post_type);
        return array_sum((array) $counts);
    }

    /**
     * Count errors in the last 24 hours
     *
     * @return int
     */
    private function count_errors() {
        if (!file_exists($this->error_log)) {
            return 0;
        }

        $logs = file($this->error_log);
        $count = 0;
        $yesterday = strtotime('-24 hours');

        foreach ($logs as $log) {
            if (preg_match('/\[(.*?)\]/', $log, $matches)) {
                $time = strtotime($matches[1]);
                if ($time >= $yesterday) {
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Get performance metrics
     *
     * @return array
     */
    private function get_performance_metrics() {
        return [
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
        ];
    }

    /**
     * Clean old logs
     *
     * @param int $days Number of days to keep logs.
     */
    public function clean_logs($days = 30) {
        $files = [$this->error_log, $this->activity_log];
        $cutoff = strtotime("-$days days");

        foreach ($files as $file) {
            if (!file_exists($file)) {
                continue;
            }

            $logs = file($file);
            $new_logs = [];

            foreach ($logs as $log) {
                if (preg_match('/\[(.*?)\]/', $log, $matches)) {
                    $time = strtotime($matches[1]);
                    if ($time >= $cutoff) {
                        $new_logs[] = $log;
                    }
                }
            }

            file_put_contents($file, implode('', $new_logs));
        }
    }
}
