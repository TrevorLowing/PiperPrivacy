<?php
/**
 * Template Loader for Breach Notification Module
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
 * Template loader class
 */
class Template_Loader {
    /**
     * Get template path
     *
     * @param string $template Template name.
     * @return string Template path.
     */
    private function get_template_path($template) {
        return plugin_dir_path(dirname(__FILE__)) . 'templates/emails/' . $template . '.php';
    }

    /**
     * Load template
     *
     * @param string $template Template name.
     * @return callable|null Template function if exists.
     */
    private function load_template($template) {
        $template_path = $this->get_template_path($template);
        
        if (file_exists($template_path)) {
            require_once $template_path;
            $function_name = 'pp_get_' . str_replace('-', '_', $template) . '_template';
            if (function_exists($function_name)) {
                return $function_name;
            }
        }
        
        return null;
    }

    /**
     * Get email template
     *
     * @param string $type   Template type.
     * @param array  $breach Breach data.
     * @param array  $user   User data (optional).
     * @return array|WP_Error Template data or error.
     */
    public function get_email_template($type, $breach, $user = null) {
        $template_map = [
            'authority' => 'authority-notification',
            'affected-user' => 'affected-user-notification',
            'internal' => 'internal-notification',
        ];

        if (!isset($template_map[$type])) {
            return new \WP_Error(
                'invalid_template_type',
                __('Invalid template type specified.', 'piper-privacy')
            );
        }

        $template_function = $this->load_template($template_map[$type]);
        if (!$template_function) {
            return new \WP_Error(
                'template_not_found',
                __('Email template not found.', 'piper-privacy')
            );
        }

        // Apply filters before generating template
        $breach = apply_filters('pp_breach_notification_template_breach_data', $breach, $type);
        if ($user) {
            $user = apply_filters('pp_breach_notification_template_user_data', $user, $type);
        }

        // Generate template
        $template = $user ? $template_function($breach, $user) : $template_function($breach);

        // Apply filters to generated template
        return apply_filters('pp_breach_notification_template', $template, $type, $breach, $user);
    }

    /**
     * Get preview of email template
     *
     * @param string $type   Template type.
     * @param array  $breach Breach data.
     * @param array  $user   User data (optional).
     * @return string HTML preview of the email.
     */
    public function get_template_preview($type, $breach, $user = null) {
        $template = $this->get_email_template($type, $breach, $user);
        
        if (is_wp_error($template)) {
            return sprintf(
                '<div class="notice notice-error"><p>%s</p></div>',
                esc_html($template->get_error_message())
            );
        }

        $preview = '<div class="pp-email-preview">';
        
        // Subject
        $preview .= sprintf(
            '<div class="pp-email-subject"><strong>%s:</strong> %s</div>',
            esc_html__('Subject', 'piper-privacy'),
            esc_html($template['subject'])
        );

        // Message
        $preview .= '<div class="pp-email-message">';
        $preview .= nl2br(esc_html($template['message']));
        $preview .= '</div>';

        $preview .= '</div>';

        return $preview;
    }

    /**
     * Register template preview styles
     */
    public function register_preview_styles() {
        wp_register_style(
            'pp-email-preview',
            plugins_url('assets/css/email-preview.css', dirname(__FILE__)),
            [],
            PIPER_PRIVACY_VERSION
        );
    }

    /**
     * Enqueue template preview styles
     */
    public function enqueue_preview_styles() {
        wp_enqueue_style('pp-email-preview');
    }
}
