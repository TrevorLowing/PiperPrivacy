<?php
/**
 * Breach Notification View
 *
 * @package     PiperPrivacy
 * @subpackage  Modules\BreachNotification
 */

namespace PiperPrivacy\Modules\BreachNotification;

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Breach Notification View Class
 */
class View {
    /**
     * Render admin page
     */
    public function render_admin_page() {
        // Enqueue scripts and styles
        wp_enqueue_script(
            'pp-breach-admin',
            PIPER_PRIVACY_URL . 'modules/breach-notification/assets/js/admin.js',
            ['jquery', 'wp-api'],
            PIPER_PRIVACY_VERSION,
            true
        );

        wp_enqueue_style(
            'pp-breach-admin',
            PIPER_PRIVACY_URL . 'modules/breach-notification/assets/css/admin.css',
            [],
            PIPER_PRIVACY_VERSION
        );

        // Localize script
        wp_localize_script('pp-breach-admin', 'ppBreach', [
            'nonce'   => wp_create_nonce('wp_rest'),
            'apiRoot' => esc_url_raw(rest_url('piper-privacy/v1')),
            'i18n'    => [
                'confirmDelete' => __('Are you sure you want to delete this breach incident?', 'piper-privacy'),
                'error'        => __('An error occurred. Please try again.', 'piper-privacy'),
                'success'      => __('Changes saved successfully.', 'piper-privacy'),
            ],
        ]);

        // Render template
        require_once dirname(__DIR__) . '/templates/admin-page.php';
    }

    /**
     * Render breach list
     *
     * @param array $breaches List of breaches
     */
    public function render_breach_list($breaches) {
        require_once dirname(__DIR__) . '/templates/breach-list.php';
    }

    /**
     * Render single breach
     *
     * @param array $breach Breach data
     */
    public function render_single_breach($breach) {
        require_once dirname(__DIR__) . '/templates/breach-single.php';
    }

    /**
     * Render breach form
     *
     * @param array $breach Optional breach data for editing
     */
    public function render_breach_form($breach = null) {
        require_once dirname(__DIR__) . '/templates/breach-form.php';
    }

    /**
     * Render breach timeline
     *
     * @param array $timeline Timeline entries
     */
    public function render_breach_timeline($timeline) {
        require_once dirname(__DIR__) . '/templates/breach-timeline.php';
    }

    /**
     * Render notification form
     *
     * @param int   $breach_id Breach ID
     * @param array $templates Available notification templates
     */
    public function render_notification_form($breach_id, $templates) {
        require_once dirname(__DIR__) . '/templates/notification-form.php';
    }

    /**
     * Render dashboard widget
     */
    public function render_dashboard_widget() {
        $model = new Model();
        $breaches = $model->get_breaches([
            'posts_per_page' => 5,
            'orderby'       => 'date',
            'order'         => 'DESC',
        ]);

        require_once dirname(__DIR__) . '/templates/dashboard-widget.php';
    }

    /**
     * Format timeline entry
     *
     * @param array $entry Timeline entry
     * @return string
     */
    public function format_timeline_entry($entry) {
        $user = get_userdata($entry['user_id']);
        $username = $user ? $user->display_name : __('System', 'piper-privacy');
        $time = get_date_from_gmt($entry['timestamp']);

        switch ($entry['type']) {
            case 'created':
                return sprintf(
                    /* translators: 1: username, 2: time */
                    __('Created by %1$s at %2$s', 'piper-privacy'),
                    esc_html($username),
                    esc_html($time)
                );

            case 'status_change':
                return sprintf(
                    /* translators: 1: username, 2: old status, 3: new status, 4: time */
                    __('Status changed by %1$s from "%2$s" to "%3$s" at %4$s', 'piper-privacy'),
                    esc_html($username),
                    esc_html(ucfirst($entry['data']['from'])),
                    esc_html(ucfirst($entry['data']['to'])),
                    esc_html($time)
                );

            case 'notification_sent':
                return sprintf(
                    /* translators: 1: notification type, 2: username, 3: time */
                    __('%1$s notification sent by %2$s at %3$s', 'piper-privacy'),
                    esc_html(ucfirst($entry['data']['type'])),
                    esc_html($username),
                    esc_html($time)
                );

            case 'comment_added':
                return sprintf(
                    /* translators: 1: username, 2: time */
                    __('Comment added by %1$s at %2$s', 'piper-privacy'),
                    esc_html($username),
                    esc_html($time)
                );

            default:
                return sprintf(
                    /* translators: 1: entry type, 2: username, 3: time */
                    __('%1$s by %2$s at %3$s', 'piper-privacy'),
                    esc_html(ucfirst($entry['type'])),
                    esc_html($username),
                    esc_html($time)
                );
        }
    }

    /**
     * Format severity level
     *
     * @param string $severity Severity slug
     * @return string
     */
    public function format_severity($severity) {
        $classes = [
            'critical' => 'pp-severity-critical',
            'high'     => 'pp-severity-high',
            'medium'   => 'pp-severity-medium',
            'low'      => 'pp-severity-low',
        ];

        $class = isset($classes[$severity]) ? $classes[$severity] : '';

        return sprintf(
            '<span class="pp-severity %s">%s</span>',
            esc_attr($class),
            esc_html(ucfirst($severity))
        );
    }

    /**
     * Format breach status
     *
     * @param string $status Status slug
     * @return string
     */
    public function format_status($status) {
        $classes = [
            'draft'      => 'pp-status-draft',
            'detected'   => 'pp-status-detected',
            'assessing'  => 'pp-status-assessing',
            'confirmed'  => 'pp-status-confirmed',
            'notifying'  => 'pp-status-notifying',
            'mitigating' => 'pp-status-mitigating',
            'resolved'   => 'pp-status-resolved',
            'closed'     => 'pp-status-closed',
        ];

        $class = isset($classes[$status]) ? $classes[$status] : '';

        return sprintf(
            '<span class="pp-status %s">%s</span>',
            esc_attr($class),
            esc_html(ucfirst($status))
        );
    }
}
