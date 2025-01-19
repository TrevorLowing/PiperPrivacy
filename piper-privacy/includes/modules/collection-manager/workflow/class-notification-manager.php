<?php
namespace PiperPrivacy\Modules\CollectionManager\Workflow;

/**
 * Notification Manager
 * 
 * Handles all workflow notifications including emails, dashboard notices, and system notifications
 */
class NotificationManager {
    /**
     * Notification types
     */
    const TYPE_EMAIL = 'email';
    const TYPE_DASHBOARD = 'dashboard';
    const TYPE_SYSTEM = 'system';
    const TYPE_SLACK = 'slack';

    /**
     * Notification priorities
     */
    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    /**
     * Templates registry
     */
    private $templates = [];

    /**
     * Initialize notification manager
     */
    public function __construct() {
        $this->register_default_templates();
        $this->setup_hooks();
    }

    /**
     * Setup notification hooks
     */
    private function setup_hooks() {
        add_action('piper_privacy_stage_notification', [$this, 'process_stage_notification'], 10, 4);
        add_action('piper_privacy_workflow_notification', [$this, 'process_workflow_notification'], 10, 3);
        add_action('piper_privacy_error_notification', [$this, 'process_error_notification'], 10, 3);
        add_action('piper_privacy_status_changed', [$this, 'notify_stakeholders'], 10, 3);
        add_action('piper_privacy_document_generated', [$this, 'notify_document_stakeholders'], 10, 2);
        add_action('piper_privacy_comment_added', [$this, 'notify_comment_stakeholders'], 10, 2);
    }

    /**
     * Register default notification templates
     */
    private function register_default_templates() {
        // PTA Notifications
        $this->register_template('pta_required', [
            'subject' => __('Privacy Threshold Analysis Required', 'piper-privacy'),
            'message' => $this->get_template_content('pta-required'),
            'type' => self::TYPE_EMAIL,
            'priority' => self::PRIORITY_HIGH
        ]);

        // PIA Notifications
        $this->register_template('pia_required', [
            'subject' => __('Privacy Impact Assessment Required', 'piper-privacy'),
            'message' => $this->get_template_content('pia-required'),
            'type' => self::TYPE_EMAIL,
            'priority' => self::PRIORITY_HIGH
        ]);

        // Review Notifications
        $this->register_template('review_assigned', [
            'subject' => __('Privacy Review Assignment', 'piper-privacy'),
            'message' => $this->get_template_content('review-assigned'),
            'type' => self::TYPE_EMAIL,
            'priority' => self::PRIORITY_NORMAL
        ]);

        // Implementation Notifications
        $this->register_template('implementation_started', [
            'subject' => __('Privacy Controls Implementation Started', 'piper-privacy'),
            'message' => $this->get_template_content('implementation-started'),
            'type' => self::TYPE_EMAIL,
            'priority' => self::PRIORITY_NORMAL
        ]);

        // Error Notifications
        $this->register_template('workflow_error', [
            'subject' => __('Privacy Workflow Error', 'piper-privacy'),
            'message' => $this->get_template_content('workflow-error'),
            'type' => self::TYPE_EMAIL,
            'priority' => self::PRIORITY_URGENT
        ]);

        // Stakeholder Notifications
        $this->register_template('stakeholder_status_change', [
            'subject' => __('Privacy Collection Status Update', 'piper-privacy'),
            'message' => $this->get_template_content('stakeholder-status-change'),
            'type' => self::TYPE_EMAIL,
            'priority' => self::PRIORITY_NORMAL
        ]);

        $this->register_template('stakeholder_document_update', [
            'subject' => __('Privacy Collection Document Update', 'piper-privacy'),
            'message' => $this->get_template_content('stakeholder-document-update'),
            'type' => self::TYPE_EMAIL,
            'priority' => self::PRIORITY_NORMAL
        ]);

        $this->register_template('stakeholder_comment_added', [
            'subject' => __('New Comment on Privacy Collection', 'piper-privacy'),
            'message' => $this->get_template_content('stakeholder-comment-added'),
            'type' => self::TYPE_EMAIL,
            'priority' => self::PRIORITY_NORMAL
        ]);
    }

    /**
     * Register a notification template
     *
     * @param string $template_id
     * @param array  $config
     */
    public function register_template($template_id, $config) {
        $this->templates[$template_id] = wp_parse_args($config, [
            'subject' => '',
            'message' => '',
            'type' => self::TYPE_EMAIL,
            'priority' => self::PRIORITY_NORMAL
        ]);
    }

    /**
     * Process stage notification
     *
     * @param int    $post_id
     * @param string $stage_id
     * @param string $type
     * @param array  $data
     */
    public function process_stage_notification($post_id, $stage_id, $type, $data) {
        $template = $this->get_stage_template($stage_id, $type);
        if (!$template) {
            return;
        }

        $notification_data = array_merge($data, [
            'post_id' => $post_id,
            'stage_id' => $stage_id,
            'stage_name' => $this->get_stage_name($stage_id),
            'collection_title' => get_the_title($post_id)
        ]);

        $this->send_notification($template, $notification_data);
    }

    /**
     * Process workflow notification
     *
     * @param int    $post_id
     * @param string $type
     * @param array  $data
     */
    public function process_workflow_notification($post_id, $type, $data) {
        $template = $this->templates[$type] ?? null;
        if (!$template) {
            return;
        }

        $notification_data = array_merge($data, [
            'post_id' => $post_id,
            'collection_title' => get_the_title($post_id)
        ]);

        $this->send_notification($template, $notification_data);
    }

    /**
     * Process error notification
     *
     * @param int    $post_id
     * @param string $error_type
     * @param array  $data
     */
    public function process_error_notification($post_id, $error_type, $data) {
        $template = $this->templates['workflow_error'];
        
        $notification_data = array_merge($data, [
            'post_id' => $post_id,
            'collection_title' => get_the_title($post_id),
            'error_type' => $error_type,
            'error_message' => $data['error'] ?? __('Unknown error', 'piper-privacy')
        ]);

        $this->send_notification($template, $notification_data, self::PRIORITY_URGENT);
    }

    /**
     * Send notification
     *
     * @param array  $template
     * @param array  $data
     * @param string $priority
     */
    private function send_notification($template, $data, $priority = null) {
        $priority = $priority ?? $template['priority'];
        
        switch ($template['type']) {
            case self::TYPE_EMAIL:
                $this->send_email_notification($template, $data, $priority);
                break;

            case self::TYPE_DASHBOARD:
                $this->send_dashboard_notification($template, $data, $priority);
                break;

            case self::TYPE_SYSTEM:
                $this->send_system_notification($template, $data, $priority);
                break;

            case self::TYPE_SLACK:
                $this->send_slack_notification($template, $data, $priority);
                break;
        }
    }

    /**
     * Send email notification
     *
     * @param array  $template
     * @param array  $data
     * @param string $priority
     */
    private function send_email_notification($template, $data, $priority) {
        $subject = $this->parse_template($template['subject'], $data);
        $message = $this->parse_template($template['message'], $data);
        $headers = $this->get_email_headers($priority);

        // Get recipients
        $recipients = $this->get_notification_recipients($data);

        // Send email using WordPress
        foreach ($recipients as $recipient) {
            wp_mail($recipient, $subject, $message, $headers);
        }
    }

    /**
     * Send dashboard notification
     *
     * @param array  $template
     * @param array  $data
     * @param string $priority
     */
    private function send_dashboard_notification($template, $data, $priority) {
        $message = $this->parse_template($template['message'], $data);
        
        // Store notification in database for dashboard display
        $notification_id = wp_insert_post([
            'post_type' => 'privacy-notification',
            'post_status' => 'publish',
            'post_title' => $template['subject'],
            'post_content' => $message,
            'meta_input' => [
                'notification_type' => 'dashboard',
                'priority' => $priority,
                'data' => $data,
                'read_status' => false
            ]
        ]);

        do_action('piper_privacy_dashboard_notification_created', $notification_id, $data);
    }

    /**
     * Send system notification
     *
     * @param array  $template
     * @param array  $data
     * @param string $priority
     */
    private function send_system_notification($template, $data, $priority) {
        $message = $this->parse_template($template['message'], $data);
        
        // Log system notification
        error_log(sprintf(
            '[Privacy Collection Manager] %s: %s',
            $template['subject'],
            $message
        ));

        do_action('piper_privacy_system_notification_sent', $template, $data);
    }

    /**
     * Send Slack notification
     *
     * @param array  $template
     * @param array  $data
     * @param string $priority
     */
    private function send_slack_notification($template, $data, $priority) {
        if (!$this->is_slack_enabled()) {
            return;
        }

        $message = $this->parse_template($template['message'], $data);
        
        // Send to Slack using configured webhook
        $this->send_to_slack($message, $template['subject'], $priority);
    }

    /**
     * Get notification recipients
     *
     * @param array $data
     * @return array
     */
    private function get_notification_recipients($data) {
        $recipients = [];

        // Add specific recipients from data
        if (!empty($data['user_id'])) {
            $user = get_userdata($data['user_id']);
            if ($user) {
                $recipients[] = $user->user_email;
            }
        }

        // Add role-based recipients
        if (!empty($data['roles'])) {
            foreach ($data['roles'] as $role) {
                $users = get_users(['role' => $role]);
                foreach ($users as $user) {
                    $recipients[] = $user->user_email;
                }
            }
        }

        // Add privacy officer
        $privacy_officer_id = get_option('privacy_officer_user_id');
        if ($privacy_officer_id) {
            $officer = get_userdata($privacy_officer_id);
            if ($officer) {
                $recipients[] = $officer->user_email;
            }
        }

        return array_unique($recipients);
    }

    /**
     * Get email headers
     *
     * @param string $priority
     * @return array
     */
    private function get_email_headers($priority) {
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_bloginfo('admin_email') . '>'
        ];

        if ($priority === self::PRIORITY_URGENT) {
            $headers[] = 'X-Priority: 1';
        }

        return $headers;
    }

    /**
     * Parse template with data
     *
     * @param string $template
     * @param array  $data
     * @return string
     */
    private function parse_template($template, $data) {
        // Replace variables in template
        foreach ($data as $key => $value) {
            if (is_scalar($value)) {
                $template = str_replace('{' . $key . '}', $value, $template);
            }
        }

        return $template;
    }

    /**
     * Get template content
     *
     * @param string $template_name
     * @return string
     */
    private function get_template_content($template_name) {
        $template_file = PIPER_PRIVACY_DIR . 'templates/emails/' . $template_name . '.php';
        
        if (file_exists($template_file)) {
            ob_start();
            include $template_file;
            return ob_get_clean();
        }

        return '';
    }

    /**
     * Get stage name
     *
     * @param string $stage_id
     * @return string
     */
    private function get_stage_name($stage_id) {
        $stages = [
            'draft' => __('Draft', 'piper-privacy'),
            'pta_required' => __('PTA Required', 'piper-privacy'),
            'pta_in_progress' => __('PTA In Progress', 'piper-privacy'),
            'pta_review' => __('PTA Review', 'piper-privacy'),
            'pia_required' => __('PIA Required', 'piper-privacy'),
            'pia_in_progress' => __('PIA In Progress', 'piper-privacy'),
            'pia_review' => __('PIA Review', 'piper-privacy'),
            'implementation' => __('Implementation', 'piper-privacy'),
            'retirement' => __('Retirement', 'piper-privacy')
        ];

        return $stages[$stage_id] ?? $stage_id;
    }

    /**
     * Check if Slack integration is enabled
     *
     * @return bool
     */
    private function is_slack_enabled() {
        return get_option('piper_privacy_slack_enabled', false);
    }

    /**
     * Send message to Slack
     *
     * @param string $message
     * @param string $title
     * @param string $priority
     */
    private function send_to_slack($message, $title, $priority) {
        $webhook_url = get_option('piper_privacy_slack_webhook');
        if (!$webhook_url) {
            return;
        }

        $color = $this->get_slack_priority_color($priority);

        $payload = [
            'attachments' => [
                [
                    'color' => $color,
                    'title' => $title,
                    'text' => $message,
                    'footer' => get_bloginfo('name'),
                    'ts' => time()
                ]
            ]
        ];

        wp_remote_post($webhook_url, [
            'body' => json_encode($payload),
            'headers' => ['Content-Type' => 'application/json']
        ]);
    }

    /**
     * Get Slack priority color
     *
     * @param string $priority
     * @return string
     */
    private function get_slack_priority_color($priority) {
        $colors = [
            self::PRIORITY_LOW => '#36a64f',
            self::PRIORITY_NORMAL => '#2196f3',
            self::PRIORITY_HIGH => '#ffc107',
            self::PRIORITY_URGENT => '#dc3545'
        ];

        return $colors[$priority] ?? '#2196f3';
    }

    /**
     * Notify stakeholders of status changes
     *
     * @param int    $post_id    Post ID
     * @param string $old_status Old status
     * @param string $new_status New status
     */
    public function notify_stakeholders($post_id, $old_status, $new_status) {
        $stakeholders = get_field('stakeholders', $post_id);
        if (!$stakeholders || !is_array($stakeholders)) {
            return;
        }

        $post = get_post($post_id);
        $collection_name = $post->post_title;

        foreach ($stakeholders as $stakeholder) {
            if (!isset($stakeholder['notifications']) || !in_array('status_change', $stakeholder['notifications'])) {
                continue;
            }

            $this->send_notification([
                'template' => 'stakeholder_status_change',
                'to' => $stakeholder['email'],
                'data' => [
                    'stakeholder_name' => $stakeholder['name'],
                    'collection_name' => $collection_name,
                    'old_status' => $old_status,
                    'new_status' => $new_status,
                    'collection_url' => get_edit_post_link($post_id, 'raw'),
                ],
            ]);
        }
    }

    /**
     * Notify stakeholders of document updates
     *
     * @param int    $post_id      Post ID
     * @param string $document_type Document type
     */
    public function notify_document_stakeholders($post_id, $document_type) {
        $stakeholders = get_field('stakeholders', $post_id);
        if (!$stakeholders || !is_array($stakeholders)) {
            return;
        }

        $post = get_post($post_id);
        $collection_name = $post->post_title;

        foreach ($stakeholders as $stakeholder) {
            if (!isset($stakeholder['notifications']) || !in_array('documents', $stakeholder['notifications'])) {
                continue;
            }

            $this->send_notification([
                'template' => 'stakeholder_document_update',
                'to' => $stakeholder['email'],
                'data' => [
                    'stakeholder_name' => $stakeholder['name'],
                    'collection_name' => $collection_name,
                    'document_type' => $document_type,
                    'collection_url' => get_edit_post_link($post_id, 'raw'),
                ],
            ]);
        }
    }

    /**
     * Notify stakeholders of new comments
     *
     * @param int    $post_id    Post ID
     * @param int    $comment_id Comment ID
     */
    public function notify_comment_stakeholders($post_id, $comment_id) {
        $stakeholders = get_field('stakeholders', $post_id);
        if (!$stakeholders || !is_array($stakeholders)) {
            return;
        }

        $post = get_post($post_id);
        $collection_name = $post->post_title;
        $comment = get_comment($comment_id);
        $comment_author = $comment->comment_author;
        $comment_content = wp_trim_words($comment->comment_content, 50);

        foreach ($stakeholders as $stakeholder) {
            if (!isset($stakeholder['notifications']) || !in_array('comments', $stakeholder['notifications'])) {
                continue;
            }

            $this->send_notification([
                'template' => 'stakeholder_comment_added',
                'to' => $stakeholder['email'],
                'data' => [
                    'stakeholder_name' => $stakeholder['name'],
                    'collection_name' => $collection_name,
                    'comment_author' => $comment_author,
                    'comment_content' => $comment_content,
                    'collection_url' => get_edit_post_link($post_id, 'raw'),
                ],
            ]);
        }
    }
}