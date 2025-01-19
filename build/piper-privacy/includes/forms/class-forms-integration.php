<?php
/**
 * Forms Integration Class
 *
 * @package PiperPrivacy
 */

namespace PiperPrivacy\Forms;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Forms_Integration
 * Integrates all form-related functionality
 */
class Forms_Integration {
    /**
     * Form processor instance
     *
     * @var Form_Processor
     */
    private $processor;

    /**
     * Form storage instance
     *
     * @var Form_Storage
     */
    private $storage;

    /**
     * Form notifications instance
     *
     * @var Form_Notifications
     */
    private $notifications;

    /**
     * Constructor
     */
    public function __construct() {
        $this->init_components();
        $this->init_hooks();
    }

    /**
     * Initialize form components
     */
    private function init_components() {
        $this->processor = new Form_Processor();
        $this->storage = new Form_Storage();
        $this->notifications = new Form_Notifications();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        
        // Add shortcodes
        add_shortcode('privacy_collection_form', [$this, 'render_collection_form']);
        add_shortcode('privacy_threshold_form', [$this, 'render_threshold_form']);
        add_shortcode('privacy_impact_form', [$this, 'render_impact_form']);

        // Add AJAX handlers
        add_action('wp_ajax_get_form_data', [$this, 'ajax_get_form_data']);
        add_action('wp_ajax_nopriv_get_form_data', [$this, 'ajax_get_form_data']);

        // Add form success message
        add_action('wp_footer', [$this, 'render_form_messages']);
    }

    /**
     * Enqueue assets
     */
    public function enqueue_assets() {
        // Main CSS
        wp_enqueue_style(
            'piper-privacy-forms',
            PIPER_PRIVACY_URL . 'assets/css/ui-enhancements.css',
            [],
            PIPER_PRIVACY_VERSION
        );

        // Multi-step form JS
        wp_enqueue_script(
            'piper-privacy-multi-step',
            PIPER_PRIVACY_URL . 'assets/js/multi-step.js',
            ['jquery'],
            PIPER_PRIVACY_VERSION,
            true
        );

        // UI enhancements JS
        wp_enqueue_script(
            'piper-privacy-ui',
            PIPER_PRIVACY_URL . 'assets/js/ui-enhancements.js',
            ['jquery'],
            PIPER_PRIVACY_VERSION,
            true
        );

        // Localize script
        wp_localize_script('piper-privacy-ui', 'piperPrivacyForms', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('piper_privacy_form'),
            'i18n' => [
                'required' => __('This field is required.', 'piper-privacy'),
                'invalid' => __('Please enter a valid value.', 'piper-privacy'),
                'uploading' => __('Uploading file...', 'piper-privacy'),
                'success' => __('Form submitted successfully!', 'piper-privacy'),
                'error' => __('An error occurred. Please try again.', 'piper-privacy'),
            ],
        ]);
    }

    /**
     * Render collection form
     *
     * @param array $atts Shortcode attributes.
     * @return string
     */
    public function render_collection_form($atts) {
        $defaults = [
            'title' => __('Privacy Collection Registration', 'piper-privacy'),
            'description' => __('Register a new privacy collection for your system.', 'piper-privacy'),
        ];

        $atts = shortcode_atts($defaults, $atts);

        ob_start();
        include PIPER_PRIVACY_PATH . 'templates/forms/collection-form.php';
        return ob_get_clean();
    }

    /**
     * Render threshold form
     *
     * @param array $atts Shortcode attributes.
     * @return string
     */
    public function render_threshold_form($atts) {
        $defaults = [
            'title' => __('Privacy Threshold Assessment', 'piper-privacy'),
            'description' => __('Complete a privacy threshold assessment for your system.', 'piper-privacy'),
        ];

        $atts = shortcode_atts($defaults, $atts);

        ob_start();
        include PIPER_PRIVACY_PATH . 'templates/forms/threshold-form.php';
        return ob_get_clean();
    }

    /**
     * Render impact form
     *
     * @param array $atts Shortcode attributes.
     * @return string
     */
    public function render_impact_form($atts) {
        $defaults = [
            'title' => __('Privacy Impact Assessment', 'piper-privacy'),
            'description' => __('Complete a privacy impact assessment for your system.', 'piper-privacy'),
        ];

        $atts = shortcode_atts($defaults, $atts);

        ob_start();
        include PIPER_PRIVACY_PATH . 'templates/forms/impact-form.php';
        return ob_get_clean();
    }

    /**
     * AJAX handler for getting form data
     */
    public function ajax_get_form_data() {
        check_ajax_referer('piper_privacy_form', 'nonce');

        $form_id = isset($_POST['form_id']) ? absint($_POST['form_id']) : 0;
        $form_type = isset($_POST['form_type']) ? sanitize_text_field($_POST['form_type']) : '';

        if (!$form_id || !$form_type) {
            wp_send_json_error(__('Invalid request.', 'piper-privacy'));
        }

        $form_data = $this->storage->get_form_data($form_id, $form_type);
        if (!$form_data) {
            wp_send_json_error(__('Form not found.', 'piper-privacy'));
        }

        wp_send_json_success($form_data);
    }

    /**
     * Render form messages
     */
    public function render_form_messages() {
        if (!isset($_GET['form']) || !isset($_GET['status'])) {
            return;
        }

        $form_type = sanitize_text_field($_GET['form']);
        $status = sanitize_text_field($_GET['status']);

        if ($status === 'success') {
            $message = sprintf(
                /* translators: %s: form type */
                __('Your %s has been submitted successfully.', 'piper-privacy'),
                str_replace('_', ' ', $form_type)
            );

            printf(
                '<div class="piper-privacy-message success">%s</div>',
                esc_html($message)
            );
        }
    }

    /**
     * Get form submission statistics
     *
     * @return array
     */
    public function get_form_stats() {
        $stats = [];
        $form_types = ['collection', 'threshold', 'impact'];

        foreach ($form_types as $type) {
            $forms = $this->storage->get_forms($type);
            $stats[$type] = [
                'total' => count($forms),
                'this_month' => $this->count_forms_this_month($forms),
                'high_risk' => $this->count_high_risk_forms($forms),
            ];
        }

        return $stats;
    }

    /**
     * Count forms submitted this month
     *
     * @param array $forms Forms array.
     * @return int
     */
    private function count_forms_this_month($forms) {
        $count = 0;
        $current_month = date('m');
        $current_year = date('Y');

        foreach ($forms as $form) {
            $form_date = date_parse($form['date']);
            if ($form_date['month'] == $current_month && $form_date['year'] == $current_year) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Count high risk forms
     *
     * @param array $forms Forms array.
     * @return int
     */
    private function count_high_risk_forms($forms) {
        $count = 0;

        foreach ($forms as $form) {
            if (isset($form['risk_level']) && in_array($form['risk_level'], ['high', 'very_high'], true)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Export form data
     *
     * @param string $form_type Form type.
     * @param array  $args Query arguments.
     * @return array
     */
    public function export_form_data($form_type, $args = []) {
        $forms = $this->storage->get_forms($form_type, $args);
        $export_data = [];

        foreach ($forms as $form) {
            $export_data[] = [
                'id' => $form['id'],
                'title' => $form['title'],
                'submission_date' => $form['date'],
                'status' => $form['status'],
                'data' => $this->prepare_form_data_for_export($form),
            ];
        }

        return $export_data;
    }

    /**
     * Prepare form data for export
     *
     * @param array $form Form data.
     * @return array
     */
    private function prepare_form_data_for_export($form) {
        $export_data = [];

        // Remove internal fields
        $exclude_fields = ['ID', 'id', 'title', 'content', 'status', 'date', 'modified'];
        foreach ($form as $key => $value) {
            if (!in_array($key, $exclude_fields, true)) {
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }
                $export_data[$key] = $value;
            }
        }

        return $export_data;
    }
}
