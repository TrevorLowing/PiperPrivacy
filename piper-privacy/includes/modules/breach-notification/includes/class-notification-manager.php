<?php
/**
 * Notification Manager Class
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
 * Notification Manager class
 */
class Notification_Manager {
    /**
     * Notification types
     *
     * @var array
     */
    private $notification_types = [
        'deadline' => [
            'name' => 'Deadline',
            'description' => 'Notification deadlines approaching',
            'priority' => 'high',
        ],
        'document' => [
            'name' => 'Document',
            'description' => 'Document retention dates approaching',
            'priority' => 'medium',
        ],
        'status' => [
            'name' => 'Status',
            'description' => 'Breach status updates',
            'priority' => 'normal',
        ],
    ];

    /**
     * Initialize the notification manager
     */
    public function __construct() {
        add_action('init', [$this, 'schedule_notifications']);
        add_action('pp_check_deadlines', [$this, 'check_deadlines']);
        add_action('pp_check_document_retention', [$this, 'check_document_retention']);
        add_action('transition_post_status', [$this, 'handle_status_change'], 10, 3);
        add_action('wp_ajax_pp_get_notifications', [$this, 'ajax_get_notifications']);
        add_action('wp_ajax_pp_mark_notification_read', [$this, 'ajax_mark_notification_read']);
    }

    /**
     * Schedule notification checks
     */
    public function schedule_notifications() {
        if (!wp_next_scheduled('pp_check_deadlines')) {
            wp_schedule_event(time(), 'daily', 'pp_check_deadlines');
        }

        if (!wp_next_scheduled('pp_check_document_retention')) {
            wp_schedule_event(time(), 'daily', 'pp_check_document_retention');
        }
    }

    /**
     * Check for approaching deadlines
     */
    public function check_deadlines() {
        $breaches = get_posts([
            'post_type' => 'pp_breach',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ]);

        foreach ($breaches as $breach) {
            $risk_assessment = get_post_meta($breach->ID, '_pp_risk_assessment', true);
            $compliance_analysis = get_post_meta($breach->ID, '_pp_compliance_analysis', true);

            if (!$risk_assessment || !$compliance_analysis) {
                continue;
            }

            // Check notification deadlines
            foreach ($compliance_analysis['frameworks'] as $framework) {
                foreach ($framework['notifications'] as $type => $notification) {
                    if (!$notification['required']) {
                        continue;
                    }

                    $deadline = strtotime($notification['deadline']);
                    $warning_threshold = strtotime('+7 days');

                    if ($deadline <= $warning_threshold && $deadline > time()) {
                        $this->create_notification(
                            'deadline',
                            sprintf(
                                /* translators: 1: Framework name, 2: Notification type, 3: Deadline date */
                                __('%1$s %2$s notification deadline approaching: %3$s', 'piper-privacy'),
                                $framework['name'],
                                ucwords(str_replace('_', ' ', $type)),
                                wp_date(get_option('date_format'), $deadline)
                            ),
                            $breach->ID
                        );
                    }
                }
            }
        }
    }

    /**
     * Check for documents approaching retention date
     */
    public function check_document_retention() {
        $document_manager = new Document_Manager();
        $documents = $document_manager->check_retention_dates();

        foreach ($documents as $document) {
            $breach_id = get_post_meta($document->ID, '_pp_breach_id', true);
            $retention_date = get_post_meta($document->ID, '_pp_retention_date', true);

            $this->create_notification(
                'document',
                sprintf(
                    /* translators: 1: Document title, 2: Retention date */
                    __('Document "%1$s" retention date approaching: %2$s', 'piper-privacy'),
                    $document->post_title,
                    wp_date(get_option('date_format'), strtotime($retention_date))
                ),
                $breach_id
            );
        }
    }

    /**
     * Handle breach status changes
     *
     * @param string  $new_status New status.
     * @param string  $old_status Old status.
     * @param WP_Post $post       Post object.
     */
    public function handle_status_change($new_status, $old_status, $post) {
        if ($post->post_type !== 'pp_breach' || $new_status === $old_status) {
            return;
        }

        $this->create_notification(
            'status',
            sprintf(
                /* translators: 1: Breach title, 2: Old status, 3: New status */
                __('Breach "%1$s" status changed from %2$s to %3$s', 'piper-privacy'),
                $post->post_title,
                ucwords($old_status),
                ucwords($new_status)
            ),
            $post->ID
        );

        // Send email notification for important status changes
        if (in_array($new_status, ['publish', 'closed'], true)) {
            $this->send_status_email($post, $new_status);
        }
    }

    /**
     * Create a notification
     *
     * @param string $type    Notification type.
     * @param string $message Notification message.
     * @param int    $breach_id Breach ID.
     * @return int|WP_Error Notification ID or error.
     */
    private function create_notification($type, $message, $breach_id) {
        if (!isset($this->notification_types[$type])) {
            return new \WP_Error('invalid_type', __('Invalid notification type', 'piper-privacy'));
        }

        return wp_insert_post([
            'post_type' => 'pp_notification',
            'post_title' => $message,
            'post_status' => 'publish',
            'meta_input' => [
                '_pp_notification_type' => $type,
                '_pp_breach_id' => $breach_id,
                '_pp_read' => false,
                '_pp_priority' => $this->notification_types[$type]['priority'],
            ],
        ]);
    }

    /**
     * Send status change email
     *
     * @param WP_Post $breach      Breach post object.
     * @param string  $new_status  New status.
     */
    private function send_status_email($breach, $new_status) {
        $admin_email = get_option('admin_email');
        $subject = sprintf(
            /* translators: 1: Breach title, 2: New status */
            __('[%1$s] Breach Status Changed to %2$s', 'piper-privacy'),
            get_bloginfo('name'),
            ucwords($new_status)
        );

        $message = sprintf(
            /* translators: 1: Breach title, 2: New status, 3: Admin URL */
            __(
                "The status of breach \"%1\$s\" has been changed to %2\$s.\n\n" .
                "View the breach details here: %3\$s",
                'piper-privacy'
            ),
            $breach->post_title,
            ucwords($new_status),
            admin_url('admin.php?page=piper-privacy-breach&action=view&id=' . $breach->ID)
        );

        wp_mail($admin_email, $subject, $message);
    }

    /**
     * Get notifications via AJAX
     */
    public function ajax_get_notifications() {
        check_ajax_referer('pp_get_notifications', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $notifications = get_posts([
            'post_type' => 'pp_notification',
            'posts_per_page' => 50,
            'orderby' => 'date',
            'order' => 'DESC',
            'meta_query' => [
                [
                    'key' => '_pp_read',
                    'value' => false,
                ],
            ],
        ]);

        $result = [];
        foreach ($notifications as $notification) {
            $type = get_post_meta($notification->ID, '_pp_notification_type', true);
            $breach_id = get_post_meta($notification->ID, '_pp_breach_id', true);
            $priority = get_post_meta($notification->ID, '_pp_priority', true);

            $result[] = [
                'id' => $notification->ID,
                'message' => $notification->post_title,
                'type' => $type,
                'priority' => $priority,
                'breach_id' => $breach_id,
                'date' => $notification->post_date,
            ];
        }

        wp_send_json_success($result);
    }

    /**
     * Mark notification as read via AJAX
     */
    public function ajax_mark_notification_read() {
        check_ajax_referer('pp_mark_notification_read', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $notification_id = isset($_POST['notification_id']) ? intval($_POST['notification_id']) : 0;
        if (!$notification_id) {
            wp_send_json_error('Invalid notification ID');
        }

        update_post_meta($notification_id, '_pp_read', true);
        wp_send_json_success();
    }

    /**
     * Register notification post type
     */
    public function register_post_type() {
        register_post_type('pp_notification', [
            'labels' => [
                'name' => __('Notifications', 'piper-privacy'),
                'singular_name' => __('Notification', 'piper-privacy'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'supports' => ['title'],
            'capability_type' => 'post',
            'capabilities' => [
                'create_posts' => 'manage_options',
                'edit_post' => 'manage_options',
                'delete_post' => 'manage_options',
            ],
        ]);
    }
}
