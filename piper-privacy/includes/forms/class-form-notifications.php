<?php
/**
 * Form Notifications Class
 *
 * @package PiperPrivacy
 */

namespace PiperPrivacy\Forms;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Form_Notifications
 * Handles email notifications for form submissions
 */
class Form_Notifications {
    /**
     * Email templates
     *
     * @var array
     */
    private $email_templates = [];

    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
        $this->setup_email_templates();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('piper_privacy_collection_form_processed', [$this, 'send_collection_notifications'], 10, 2);
        add_action('piper_privacy_threshold_form_processed', [$this, 'send_threshold_notifications'], 10, 2);
        add_action('piper_privacy_impact_form_processed', [$this, 'send_impact_notifications'], 10, 2);
    }

    /**
     * Set up email templates
     */
    private function setup_email_templates() {
        $this->email_templates = [
            'collection' => [
                'admin' => [
                    'subject' => __('New Privacy Collection Registration Submitted', 'piper-privacy'),
                    'message' => $this->get_collection_admin_template(),
                ],
                'user' => [
                    'subject' => __('Privacy Collection Registration Confirmation', 'piper-privacy'),
                    'message' => $this->get_collection_user_template(),
                ],
            ],
            'threshold' => [
                'admin' => [
                    'subject' => __('New Privacy Threshold Assessment Submitted', 'piper-privacy'),
                    'message' => $this->get_threshold_admin_template(),
                ],
                'user' => [
                    'subject' => __('Privacy Threshold Assessment Confirmation', 'piper-privacy'),
                    'message' => $this->get_threshold_user_template(),
                ],
            ],
            'impact' => [
                'admin' => [
                    'subject' => __('New Privacy Impact Assessment Submitted', 'piper-privacy'),
                    'message' => $this->get_impact_admin_template(),
                ],
                'user' => [
                    'subject' => __('Privacy Impact Assessment Confirmation', 'piper-privacy'),
                    'message' => $this->get_impact_user_template(),
                ],
            ],
        ];
    }

    /**
     * Send collection form notifications
     *
     * @param int   $post_id Post ID.
     * @param array $data Form data.
     */
    public function send_collection_notifications($post_id, $data) {
        $this->send_admin_notification('collection', $post_id, $data);
        $this->send_user_notification('collection', $post_id, $data);
    }

    /**
     * Send threshold form notifications
     *
     * @param int   $post_id Post ID.
     * @param array $data Form data.
     */
    public function send_threshold_notifications($post_id, $data) {
        $this->send_admin_notification('threshold', $post_id, $data);
        $this->send_user_notification('threshold', $post_id, $data);
    }

    /**
     * Send impact form notifications
     *
     * @param int   $post_id Post ID.
     * @param array $data Form data.
     */
    public function send_impact_notifications($post_id, $data) {
        $this->send_admin_notification('impact', $post_id, $data);
        $this->send_user_notification('impact', $post_id, $data);
    }

    /**
     * Send admin notification
     *
     * @param string $form_type Form type.
     * @param int    $post_id Post ID.
     * @param array  $data Form data.
     */
    private function send_admin_notification($form_type, $post_id, $data) {
        $admin_email = get_option('admin_email');
        $template = $this->email_templates[$form_type]['admin'];
        
        $message = sprintf(
            $template['message'],
            $data['system_name'] ?? '',
            get_edit_post_link($post_id, 'raw'),
            $this->format_data_for_email($data)
        );

        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . $admin_email . '>',
        ];

        wp_mail($admin_email, $template['subject'], $message, $headers);
    }

    /**
     * Send user notification
     *
     * @param string $form_type Form type.
     * @param int    $post_id Post ID.
     * @param array  $data Form data.
     */
    private function send_user_notification($form_type, $post_id, $data) {
        $current_user = wp_get_current_user();
        if (!$current_user->exists()) {
            return;
        }

        $template = $this->email_templates[$form_type]['user'];
        
        $message = sprintf(
            $template['message'],
            $current_user->display_name,
            $data['system_name'] ?? '',
            get_permalink($post_id),
            $this->format_data_for_email($data)
        );

        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>',
        ];

        wp_mail($current_user->user_email, $template['subject'], $message, $headers);
    }

    /**
     * Format data for email
     *
     * @param array $data Form data.
     * @return string
     */
    private function format_data_for_email($data) {
        $formatted = '<table style="width: 100%; border-collapse: collapse; margin-top: 20px;">';
        
        foreach ($data as $key => $value) {
            if (in_array($key, ['form_type', 'nonce'], true)) {
                continue;
            }

            $label = ucwords(str_replace('_', ' ', $key));
            $formatted .= sprintf(
                '<tr><th style="text-align: left; padding: 8px; background: #f8f9fa; border: 1px solid #dee2e6;">%s</th>',
                esc_html($label)
            );

            if (is_array($value)) {
                $value = implode(', ', array_map('esc_html', $value));
            }

            $formatted .= sprintf(
                '<td style="padding: 8px; border: 1px solid #dee2e6;">%s</td></tr>',
                wp_kses_post($value)
            );
        }

        $formatted .= '</table>';

        return $formatted;
    }

    /**
     * Get collection admin email template
     *
     * @return string
     */
    private function get_collection_admin_template() {
        return '
            <h2>New Privacy Collection Registration</h2>
            <p>A new privacy collection registration has been submitted for system: <strong>%s</strong></p>
            <p>You can review the submission here: <a href="%s">View Submission</a></p>
            <h3>Submission Details:</h3>
            %s
        ';
    }

    /**
     * Get collection user email template
     *
     * @return string
     */
    private function get_collection_user_template() {
        return '
            <h2>Privacy Collection Registration Confirmation</h2>
            <p>Hello %s,</p>
            <p>Your privacy collection registration for system <strong>%s</strong> has been submitted successfully.</p>
            <p>You can view your submission here: <a href="%s">View Submission</a></p>
            <h3>Submission Details:</h3>
            %s
            <p>We will review your submission and contact you if we need any additional information.</p>
        ';
    }

    /**
     * Get threshold admin email template
     *
     * @return string
     */
    private function get_threshold_admin_template() {
        return '
            <h2>New Privacy Threshold Assessment</h2>
            <p>A new privacy threshold assessment has been submitted for system: <strong>%s</strong></p>
            <p>You can review the submission here: <a href="%s">View Submission</a></p>
            <h3>Assessment Details:</h3>
            %s
        ';
    }

    /**
     * Get threshold user email template
     *
     * @return string
     */
    private function get_threshold_user_template() {
        return '
            <h2>Privacy Threshold Assessment Confirmation</h2>
            <p>Hello %s,</p>
            <p>Your privacy threshold assessment for system <strong>%s</strong> has been submitted successfully.</p>
            <p>You can view your submission here: <a href="%s">View Submission</a></p>
            <h3>Assessment Details:</h3>
            %s
            <p>Our privacy team will review your assessment and provide feedback if needed.</p>
        ';
    }

    /**
     * Get impact admin email template
     *
     * @return string
     */
    private function get_impact_admin_template() {
        return '
            <h2>New Privacy Impact Assessment</h2>
            <p>A new privacy impact assessment has been submitted for system: <strong>%s</strong></p>
            <p>You can review the submission here: <a href="%s">View Submission</a></p>
            <h3>Assessment Details:</h3>
            %s
        ';
    }

    /**
     * Get impact user email template
     *
     * @return string
     */
    private function get_impact_user_template() {
        return '
            <h2>Privacy Impact Assessment Confirmation</h2>
            <p>Hello %s,</p>
            <p>Your privacy impact assessment for system <strong>%s</strong> has been submitted successfully.</p>
            <p>You can view your submission here: <a href="%s">View Submission</a></p>
            <h3>Assessment Details:</h3>
            %s
            <p>Our Data Protection Officer will review your assessment and provide feedback.</p>
        ';
    }
}
