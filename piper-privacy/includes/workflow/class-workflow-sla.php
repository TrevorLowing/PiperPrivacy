<?php
namespace PiperPrivacy\Includes\Workflow;

/**
 * Workflow SLA Manager
 * 
 * Handles SLA tracking and escalations
 *
 * @package    PiperPrivacy
 * @subpackage PiperPrivacy/includes/workflow
 */
class WorkflowSLA {
    /**
     * @var WorkflowConfig
     */
    private $config;

    /**
     * Initialize the SLA manager
     */
    public function __construct() {
        $this->config = new WorkflowConfig();
        
        // Schedule daily SLA check
        if (!wp_next_scheduled('piper_privacy_check_slas')) {
            wp_schedule_event(time(), 'daily', 'piper_privacy_check_slas');
        }

        add_action('piper_privacy_check_slas', [$this, 'check_all_slas']);
        add_action('transition_post_status', [$this, 'track_stage_entry'], 10, 3);
    }

    /**
     * Track when an item enters a stage
     *
     * @param string  $new_status New post status
     * @param string  $old_status Old post status
     * @param WP_Post $post       Post object
     */
    public function track_stage_entry($new_status, $old_status, $post) {
        if (!in_array($post->post_type, ['privacy_collection', 'privacy_threshold', 'privacy_impact'])) {
            return;
        }

        if ($new_status !== $old_status) {
            update_post_meta($post->ID, '_workflow_stage_entry_time', current_time('mysql'));
            $this->calculate_deadline($post->ID, $new_status);
        }
    }

    /**
     * Calculate and store deadline for current stage
     *
     * @param int    $post_id Post ID
     * @param string $stage   Current stage
     */
    public function calculate_deadline($post_id, $stage) {
        $post = get_post($post_id);
        $sla_days = $this->config->get_stage_sla($post->post_type, $stage);

        if ($sla_days === null) {
            delete_post_meta($post_id, '_workflow_stage_deadline');
            return;
        }

        $entry_time = get_post_meta($post_id, '_workflow_stage_entry_time', true);
        if (!$entry_time) {
            return;
        }

        $deadline = date('Y-m-d H:i:s', strtotime($entry_time . " +{$sla_days} days"));
        update_post_meta($post_id, '_workflow_stage_deadline', $deadline);
    }

    /**
     * Check SLAs for all items
     */
    public function check_all_slas() {
        $post_types = ['privacy_collection', 'privacy_threshold', 'privacy_impact'];
        
        foreach ($post_types as $post_type) {
            $items = get_posts([
                'post_type' => $post_type,
                'post_status' => ['pending_review', 'in_progress'],
                'posts_per_page' => -1,
            ]);

            foreach ($items as $item) {
                $this->check_item_sla($item);
            }
        }
    }

    /**
     * Check SLA for a specific item
     *
     * @param WP_Post $post Post object
     */
    public function check_item_sla($post) {
        $deadline = get_post_meta($post->ID, '_workflow_stage_deadline', true);
        if (!$deadline) {
            return;
        }

        $now = current_time('mysql');
        $days_until_deadline = (strtotime($deadline) - strtotime($now)) / DAY_IN_SECONDS;
        $warning_threshold = $this->config->get_sla_warning_threshold($post->post_type);
        $escalation_threshold = $this->config->get_escalation_threshold($post->post_type);

        // Check for approaching deadline
        if ($days_until_deadline > 0 && $days_until_deadline <= $warning_threshold) {
            $this->send_deadline_warning($post, $days_until_deadline);
        }
        // Check for missed deadline
        elseif ($days_until_deadline < 0) {
            $days_overdue = abs($days_until_deadline);
            if ($days_overdue >= $escalation_threshold) {
                $this->escalate_item($post, $days_overdue);
            }
        }
    }

    /**
     * Send warning about approaching deadline
     *
     * @param WP_Post $post              Post object
     * @param float   $days_until_deadline Days until deadline
     */
    private function send_deadline_warning($post, $days_until_deadline) {
        $notification = $this->config->get_notification_config($post->post_type, $post->post_status);
        if (!$notification) {
            return;
        }

        $users = get_users(['role__in' => $notification['roles']]);
        $deadline = get_post_meta($post->ID, '_workflow_stage_deadline', true);

        $subject = str_replace(
            ['{site_name}', '{post_type}', '{post_title}'],
            [get_bloginfo('name'), get_post_type_object($post->post_type)->labels->singular_name, $post->post_title],
            __('[{site_name}] Deadline Approaching: {post_type} - {post_title}', 'piper-privacy')
        );

        $message = sprintf(
            __("The following item is approaching its deadline:\n\nTitle: %s\nType: %s\nStatus: %s\nDeadline: %s\nDays Remaining: %.1f\n\nPlease take action: %s", 'piper-privacy'),
            $post->post_title,
            get_post_type_object($post->post_type)->labels->singular_name,
            get_post_status_object($post->post_status)->label,
            $deadline,
            $days_until_deadline,
            get_edit_post_link($post->ID, 'raw')
        );

        foreach ($users as $user) {
            wp_mail($user->user_email, $subject, $message);
        }
    }

    /**
     * Escalate an overdue item
     *
     * @param WP_Post $post         Post object
     * @param float   $days_overdue Days overdue
     */
    private function escalate_item($post, $days_overdue) {
        $escalation_roles = $this->config->get_escalation_roles($post->post_type, $post->post_status);
        if (!$escalation_roles) {
            return;
        }

        $users = get_users(['role__in' => $escalation_roles]);
        $deadline = get_post_meta($post->ID, '_workflow_stage_deadline', true);

        $subject = str_replace(
            ['{site_name}', '{post_type}', '{post_title}'],
            [get_bloginfo('name'), get_post_type_object($post->post_type)->labels->singular_name, $post->post_title],
            __('[{site_name}] ESCALATION: Overdue {post_type} - {post_title}', 'piper-privacy')
        );

        $message = sprintf(
            __("The following item requires immediate attention:\n\nTitle: %s\nType: %s\nStatus: %s\nDeadline: %s\nDays Overdue: %.1f\n\nPlease take action: %s", 'piper-privacy'),
            $post->post_title,
            get_post_type_object($post->post_type)->labels->singular_name,
            get_post_status_object($post->post_status)->label,
            $deadline,
            $days_overdue,
            get_edit_post_link($post->ID, 'raw')
        );

        foreach ($users as $user) {
            wp_mail($user->user_email, $subject, $message);
        }

        // Log the escalation
        update_post_meta($post->ID, '_workflow_escalated', current_time('mysql'));
        do_action('piper_privacy_workflow_escalated', $post->ID, $days_overdue);
    }
}
