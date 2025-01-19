<?php
/**
 * Breach Notification Handler
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
 * Breach Notification Handler Class
 */
class Notification {
    /**
     * Create notification
     *
     * @param array $data Notification data
     * @return array|\WP_Error
     */
    public function create($data) {
        if (empty($data['breach_id']) || empty($data['type']) || empty($data['recipients'])) {
            return new \WP_Error(
                'missing_data',
                __('Missing required notification data.', 'piper-privacy'),
                ['status' => 400]
            );
        }

        $notification = [
            'id'            => uniqid('notification_'),
            'type'          => $data['type'],
            'recipients'    => $data['recipients'],
            'template'      => $data['template'] ?? '',
            'status'        => 'pending',
            'created_at'    => current_time('mysql'),
            'schedule_date' => $data['schedule_date'] ?? current_time('mysql'),
        ];

        // Get existing notifications
        $notifications = get_post_meta($data['breach_id'], '_pp_notifications', true) ?: [];
        $notifications[] = $notification;

        // Update notifications
        update_post_meta($data['breach_id'], '_pp_notifications', $notifications);
        update_post_meta($data['breach_id'], '_pp_has_pending_notifications', '1');

        // Add to timeline
        $this->add_timeline_entry($data['breach_id'], 'notification_scheduled', [
            'type' => $notification['type'],
            'schedule_date' => $notification['schedule_date'],
        ]);

        return $notification;
    }

    /**
     * Send notification
     *
     * @param array $data Notification data
     * @return bool|\WP_Error
     */
    public function send($data) {
        $breach_id = $data['breach_id'];
        $notification = $data['notification'];

        // Get breach data
        $model = new Model();
        $breach = $model->get_breach($breach_id);

        if (is_wp_error($breach)) {
            return $breach;
        }

        // Get notification template
        $template = $this->get_template($notification['type'], $notification['template']);
        
        if (is_wp_error($template)) {
            return $template;
        }

        // Process template with breach data
        $content = $this->process_template($template, $breach);

        // Send notifications based on type
        $result = false;
        switch ($notification['type']) {
            case 'authority':
                $result = $this->send_authority_notification($content, $notification['recipients']);
                break;

            case 'affected_users':
                $result = $this->send_affected_users_notification($content, $notification['recipients']);
                break;

            case 'internal':
                $result = $this->send_internal_notification($content, $notification['recipients']);
                break;
        }

        if (is_wp_error($result)) {
            return $result;
        }

        // Update notification status
        $notifications = get_post_meta($breach_id, '_pp_notifications', true) ?: [];
        foreach ($notifications as &$n) {
            if ($n['id'] === $notification['id']) {
                $n['status'] = 'sent';
                $n['sent_at'] = current_time('mysql');
                break;
            }
        }

        // Update notifications
        update_post_meta($breach_id, '_pp_notifications', $notifications);

        // Check if there are any remaining pending notifications
        $has_pending = false;
        foreach ($notifications as $n) {
            if ('pending' === $n['status']) {
                $has_pending = true;
                break;
            }
        }
        update_post_meta($breach_id, '_pp_has_pending_notifications', $has_pending ? '1' : '');

        // Add to timeline
        $this->add_timeline_entry($breach_id, 'notification_sent', [
            'type' => $notification['type'],
            'notification_id' => $notification['id'],
        ]);

        return true;
    }

    /**
     * Schedule authority notification
     *
     * @param int $breach_id Breach ID
     * @return array|\WP_Error
     */
    public function schedule_authority_notification($breach_id) {
        // Get authority notification settings
        $settings = get_option('pp_breach_notification_settings', []);
        $authority_email = $settings['authority_email'] ?? '';
        $notification_deadline = $settings['authority_notification_deadline'] ?? 72; // hours

        if (empty($authority_email)) {
            return new \WP_Error(
                'missing_settings',
                __('Authority notification email not configured.', 'piper-privacy'),
                ['status' => 400]
            );
        }

        // Calculate schedule date (deadline from detection date)
        $model = new Model();
        $breach = $model->get_breach($breach_id);
        
        if (is_wp_error($breach)) {
            return $breach;
        }

        $detection_date = strtotime($breach['detection_date']);
        $schedule_date = date('Y-m-d H:i:s', $detection_date + ($notification_deadline * HOUR_IN_SECONDS));

        return $this->create([
            'breach_id'     => $breach_id,
            'type'          => 'authority',
            'recipients'    => [$authority_email],
            'template'      => 'authority_notification',
            'schedule_date' => $schedule_date,
        ]);
    }

    /**
     * Schedule affected users notification
     *
     * @param int $breach_id Breach ID
     * @return array|\WP_Error
     */
    public function schedule_affected_notification($breach_id) {
        // Get notification settings
        $settings = get_option('pp_breach_notification_settings', []);
        $notification_deadline = $settings['affected_notification_deadline'] ?? 72; // hours

        // Get breach data
        $model = new Model();
        $breach = $model->get_breach($breach_id);
        
        if (is_wp_error($breach)) {
            return $breach;
        }

        // Get affected users
        $affected_users = $breach['affected_users'];
        if (empty($affected_users)) {
            return new \WP_Error(
                'no_affected_users',
                __('No affected users specified.', 'piper-privacy'),
                ['status' => 400]
            );
        }

        // Calculate schedule date
        $detection_date = strtotime($breach['detection_date']);
        $schedule_date = date('Y-m-d H:i:s', $detection_date + ($notification_deadline * HOUR_IN_SECONDS));

        return $this->create([
            'breach_id'     => $breach_id,
            'type'          => 'affected_users',
            'recipients'    => $affected_users,
            'template'      => 'affected_users_notification',
            'schedule_date' => $schedule_date,
        ]);
    }

    /**
     * Send authority notification
     *
     * @param string $content    Email content
     * @param array  $recipients Recipient email addresses
     * @return bool|\WP_Error
     */
    private function send_authority_notification($content, $recipients) {
        $subject = __('Data Breach Notification', 'piper-privacy');
        $headers = ['Content-Type: text/html; charset=UTF-8'];

        foreach ($recipients as $recipient) {
            $sent = wp_mail($recipient, $subject, $content, $headers);
            if (!$sent) {
                return new \WP_Error(
                    'send_failed',
                    __('Failed to send authority notification.', 'piper-privacy'),
                    ['status' => 500]
                );
            }
        }

        return true;
    }

    /**
     * Send affected users notification
     *
     * @param string $content    Email content
     * @param array  $recipients Recipient user IDs
     * @return bool|\WP_Error
     */
    private function send_affected_users_notification($content, $recipients) {
        $subject = __('Important Privacy Notice', 'piper-privacy');
        $headers = ['Content-Type: text/html; charset=UTF-8'];

        foreach ($recipients as $user_id) {
            $user = get_userdata($user_id);
            if (!$user) {
                continue;
            }

            $personalized_content = str_replace(
                ['[user_name]', '[user_email]'],
                [$user->display_name, $user->user_email],
                $content
            );

            $sent = wp_mail($user->user_email, $subject, $personalized_content, $headers);
            if (!$sent) {
                return new \WP_Error(
                    'send_failed',
                    __('Failed to send affected users notification.', 'piper-privacy'),
                    ['status' => 500]
                );
            }
        }

        return true;
    }

    /**
     * Send internal notification
     *
     * @param string $content    Email content
     * @param array  $recipients Recipient email addresses
     * @return bool|\WP_Error
     */
    private function send_internal_notification($content, $recipients) {
        $subject = __('Internal Data Breach Update', 'piper-privacy');
        $headers = ['Content-Type: text/html; charset=UTF-8'];

        foreach ($recipients as $recipient) {
            $sent = wp_mail($recipient, $subject, $content, $headers);
            if (!$sent) {
                return new \WP_Error(
                    'send_failed',
                    __('Failed to send internal notification.', 'piper-privacy'),
                    ['status' => 500]
                );
            }
        }

        return true;
    }

    /**
     * Get notification template
     *
     * @param string $type     Notification type
     * @param string $template Template name
     * @return string|\WP_Error
     */
    private function get_template($type, $template) {
        // Get template content from options or file
        $templates = get_option('pp_breach_notification_templates', []);
        
        if (!empty($templates[$type][$template])) {
            return $templates[$type][$template];
        }

        // Fallback to default template file
        $template_file = dirname(__DIR__) . "/templates/emails/{$type}-{$template}.php";
        
        if (file_exists($template_file)) {
            ob_start();
            include $template_file;
            return ob_get_clean();
        }

        return new \WP_Error(
            'template_not_found',
            __('Notification template not found.', 'piper-privacy'),
            ['status' => 404]
        );
    }

    /**
     * Process template with breach data
     *
     * @param string $template Template content
     * @param array  $breach   Breach data
     * @return string
     */
    private function process_template($template, $breach) {
        $replacements = [
            '[breach_title]'       => $breach['title'],
            '[breach_description]' => $breach['description'],
            '[detection_date]'     => get_date_from_gmt($breach['detection_date']),
            '[affected_data]'      => implode(', ', $breach['affected_data']),
            '[severity]'           => ucfirst($breach['severity']),
            '[status]'            => ucfirst($breach['status']),
            '[mitigation_steps]'   => $breach['mitigation_steps'],
            '[site_name]'         => get_bloginfo('name'),
            '[site_url]'          => get_bloginfo('url'),
        ];

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $template
        );
    }

    /**
     * Add timeline entry
     *
     * @param int    $breach_id Breach ID
     * @param string $type      Entry type
     * @param array  $data      Additional data
     */
    private function add_timeline_entry($breach_id, $type, $data = []) {
        $timeline = get_post_meta($breach_id, '_pp_timeline', true) ?: [];
        
        $entry = [
            'type'      => $type,
            'user_id'   => get_current_user_id(),
            'timestamp' => current_time('mysql'),
            'data'      => $data,
        ];

        $timeline[] = $entry;
        update_post_meta($breach_id, '_pp_timeline', $timeline);
    }
}
