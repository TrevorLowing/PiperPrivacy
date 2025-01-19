<?php
/**
 * Form Processor Class
 *
 * @package PiperPrivacy
 */

namespace PiperPrivacy\Forms;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Form_Processor
 * Handles form validation and processing for privacy forms
 */
class Form_Processor {
    /**
     * Form validation rules
     *
     * @var array
     */
    private $validation_rules = [];

    /**
     * Form data
     *
     * @var array
     */
    private $form_data = [];

    /**
     * Form errors
     *
     * @var array
     */
    private $errors = [];

    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
        $this->setup_validation_rules();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('init', [$this, 'process_form_submission']);
        add_action('wp_ajax_validate_form_field', [$this, 'ajax_validate_field']);
        add_action('wp_ajax_nopriv_validate_form_field', [$this, 'ajax_validate_field']);
    }

    /**
     * Set up validation rules for different form types
     */
    private function setup_validation_rules() {
        $this->validation_rules = [
            'collection' => [
                'system_name' => [
                    'required' => true,
                    'type' => 'string',
                    'max_length' => 100,
                    'sanitize' => 'sanitize_text_field',
                ],
                'system_description' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize' => 'wp_kses_post',
                ],
                'pii_categories' => [
                    'required' => true,
                    'type' => 'array',
                    'sanitize' => [$this, 'sanitize_array'],
                ],
                'data_elements' => [
                    'required' => true,
                    'type' => 'array',
                    'sanitize' => [$this, 'sanitize_array'],
                ],
            ],
            'threshold' => [
                'system_name' => [
                    'required' => true,
                    'type' => 'string',
                    'max_length' => 100,
                    'sanitize' => 'sanitize_text_field',
                ],
                'system_description' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize' => 'wp_kses_post',
                ],
                'system_owner' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize' => 'sanitize_text_field',
                ],
                'risk_level' => [
                    'required' => true,
                    'type' => 'string',
                    'allowed_values' => ['low', 'medium', 'high', 'very_high'],
                    'sanitize' => 'sanitize_text_field',
                ],
            ],
            'impact' => [
                'system_overview' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize' => 'wp_kses_post',
                ],
                'project_scope' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize' => 'wp_kses_post',
                ],
                'stakeholders' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize' => 'sanitize_textarea_field',
                ],
                'privacy_risks' => [
                    'required' => true,
                    'type' => 'array',
                    'sanitize' => [$this, 'sanitize_risks_array'],
                ],
            ],
        ];
    }

    /**
     * Process form submission
     */
    public function process_form_submission() {
        if (!isset($_POST['piper_privacy_form_nonce']) || 
            !wp_verify_nonce($_POST['piper_privacy_form_nonce'], 'piper_privacy_form')) {
            return;
        }

        $form_type = sanitize_text_field($_POST['form_type'] ?? '');
        if (!in_array($form_type, ['collection', 'threshold', 'impact'], true)) {
            return;
        }

        $this->form_data = $_POST;
        
        if ($this->validate_form($form_type)) {
            $this->process_form($form_type);
        } else {
            $this->store_errors();
        }
    }

    /**
     * Validate form data
     *
     * @param string $form_type Form type.
     * @return bool
     */
    private function validate_form($form_type) {
        if (!isset($this->validation_rules[$form_type])) {
            return false;
        }

        $rules = $this->validation_rules[$form_type];
        $valid = true;

        foreach ($rules as $field => $rule) {
            if (!$this->validate_field($field, $rule)) {
                $valid = false;
            }
        }

        return $valid;
    }

    /**
     * Validate individual field
     *
     * @param string $field Field name.
     * @param array  $rule Validation rules.
     * @return bool
     */
    private function validate_field($field, $rule) {
        $value = $this->form_data[$field] ?? null;

        // Check required
        if (!empty($rule['required']) && empty($value)) {
            $this->errors[$field] = sprintf(
                /* translators: %s: field name */
                __('%s is required.', 'piper-privacy'),
                str_replace('_', ' ', ucfirst($field))
            );
            return false;
        }

        // Check type
        if (!empty($value)) {
            switch ($rule['type']) {
                case 'string':
                    if (!is_string($value)) {
                        $this->errors[$field] = __('Invalid value.', 'piper-privacy');
                        return false;
                    }
                    if (isset($rule['max_length']) && strlen($value) > $rule['max_length']) {
                        $this->errors[$field] = sprintf(
                            /* translators: %1$s: field name, %2$d: maximum length */
                            __('%1$s cannot exceed %2$d characters.', 'piper-privacy'),
                            str_replace('_', ' ', ucfirst($field)),
                            $rule['max_length']
                        );
                        return false;
                    }
                    break;

                case 'array':
                    if (!is_array($value)) {
                        $this->errors[$field] = __('Invalid value.', 'piper-privacy');
                        return false;
                    }
                    break;
            }

            // Check allowed values
            if (isset($rule['allowed_values']) && !in_array($value, $rule['allowed_values'], true)) {
                $this->errors[$field] = __('Invalid value selected.', 'piper-privacy');
                return false;
            }
        }

        return true;
    }

    /**
     * Process validated form
     *
     * @param string $form_type Form type.
     */
    private function process_form($form_type) {
        $sanitized_data = $this->sanitize_form_data($form_type);
        
        switch ($form_type) {
            case 'collection':
                $this->process_collection_form($sanitized_data);
                break;
                
            case 'threshold':
                $this->process_threshold_form($sanitized_data);
                break;
                
            case 'impact':
                $this->process_impact_form($sanitized_data);
                break;
        }

        // Redirect after successful processing
        $redirect_url = add_query_arg([
            'form' => $form_type,
            'status' => 'success',
        ], wp_get_referer());

        wp_safe_redirect($redirect_url);
        exit;
    }

    /**
     * Process collection form
     *
     * @param array $data Sanitized form data.
     */
    private function process_collection_form($data) {
        // Create post
        $post_data = [
            'post_title' => $data['system_name'],
            'post_content' => $data['system_description'],
            'post_type' => 'privacy_collection',
            'post_status' => 'publish',
        ];

        $post_id = wp_insert_post($post_data);

        if (!is_wp_error($post_id)) {
            // Save meta data
            foreach ($data as $key => $value) {
                if ($key !== 'system_name' && $key !== 'system_description') {
                    update_post_meta($post_id, $key, $value);
                }
            }

            // Trigger action for other plugins
            do_action('piper_privacy_collection_form_processed', $post_id, $data);
        }
    }

    /**
     * Process threshold form
     *
     * @param array $data Sanitized form data.
     */
    private function process_threshold_form($data) {
        $post_data = [
            'post_title' => $data['system_name'],
            'post_content' => $data['system_description'],
            'post_type' => 'privacy_threshold',
            'post_status' => 'publish',
        ];

        $post_id = wp_insert_post($post_data);

        if (!is_wp_error($post_id)) {
            foreach ($data as $key => $value) {
                if ($key !== 'system_name' && $key !== 'system_description') {
                    update_post_meta($post_id, $key, $value);
                }
            }

            // Send notification if high risk
            if (in_array($data['risk_level'], ['high', 'very_high'], true)) {
                $this->send_high_risk_notification($post_id, $data);
            }

            do_action('piper_privacy_threshold_form_processed', $post_id, $data);
        }
    }

    /**
     * Process impact form
     *
     * @param array $data Sanitized form data.
     */
    private function process_impact_form($data) {
        $post_data = [
            'post_title' => $data['system_overview'],
            'post_content' => $data['project_scope'],
            'post_type' => 'privacy_impact',
            'post_status' => 'publish',
        ];

        $post_id = wp_insert_post($post_data);

        if (!is_wp_error($post_id)) {
            foreach ($data as $key => $value) {
                if ($key !== 'system_overview' && $key !== 'project_scope') {
                    update_post_meta($post_id, $key, $value);
                }
            }

            // Handle file uploads
            if (!empty($_FILES['data_flow_diagram'])) {
                $this->handle_file_upload($post_id, 'data_flow_diagram');
            }

            do_action('piper_privacy_impact_form_processed', $post_id, $data);
        }
    }

    /**
     * Handle file upload
     *
     * @param int    $post_id Post ID.
     * @param string $field_name Field name.
     */
    private function handle_file_upload($post_id, $field_name) {
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }

        $file = $_FILES[$field_name];
        $upload_overrides = ['test_form' => false];

        $moved_file = wp_handle_upload($file, $upload_overrides);

        if ($moved_file && !isset($moved_file['error'])) {
            $file_type = wp_check_filetype(basename($moved_file['file']), null);
            $attachment_data = [
                'post_mime_type' => $file_type['type'],
                'post_title' => sanitize_file_name(basename($moved_file['file'])),
                'post_content' => '',
                'post_status' => 'inherit',
            ];

            $attach_id = wp_insert_attachment($attachment_data, $moved_file['file'], $post_id);
            update_post_meta($post_id, $field_name, $attach_id);
        }
    }

    /**
     * Send notification for high-risk assessments
     *
     * @param int   $post_id Post ID.
     * @param array $data Form data.
     */
    private function send_high_risk_notification($post_id, $data) {
        $admin_email = get_option('admin_email');
        $subject = sprintf(
            /* translators: %s: system name */
            __('[High Risk] Privacy Threshold Assessment for %s', 'piper-privacy'),
            $data['system_name']
        );

        $message = sprintf(
            /* translators: %1$s: system name, %2$s: risk level, %3$s: edit link */
            __(
                'A high-risk privacy threshold assessment has been submitted:\n\n' .
                'System: %1$s\n' .
                'Risk Level: %2$s\n\n' .
                'Please review the assessment here: %3$s',
                'piper-privacy'
            ),
            $data['system_name'],
            $data['risk_level'],
            get_edit_post_link($post_id, 'raw')
        );

        wp_mail($admin_email, $subject, $message);
    }

    /**
     * Sanitize form data
     *
     * @param string $form_type Form type.
     * @return array
     */
    private function sanitize_form_data($form_type) {
        $rules = $this->validation_rules[$form_type];
        $sanitized = [];

        foreach ($rules as $field => $rule) {
            if (isset($this->form_data[$field])) {
                if (is_callable($rule['sanitize'])) {
                    $sanitized[$field] = call_user_func($rule['sanitize'], $this->form_data[$field]);
                } else {
                    $sanitized[$field] = $this->form_data[$field];
                }
            }
        }

        return $sanitized;
    }

    /**
     * Sanitize array values
     *
     * @param array $array Input array.
     * @return array
     */
    public function sanitize_array($array) {
        if (!is_array($array)) {
            return [];
        }

        return array_map('sanitize_text_field', $array);
    }

    /**
     * Sanitize risks array
     *
     * @param array $risks Risks array.
     * @return array
     */
    public function sanitize_risks_array($risks) {
        if (!is_array($risks)) {
            return [];
        }

        return array_map(function($risk) {
            return [
                'risk_name' => sanitize_text_field($risk['risk_name'] ?? ''),
                'description' => sanitize_textarea_field($risk['description'] ?? ''),
                'impact_level' => sanitize_text_field($risk['impact_level'] ?? ''),
                'likelihood' => sanitize_text_field($risk['likelihood'] ?? ''),
            ];
        }, $risks);
    }

    /**
     * Store form errors in session
     */
    private function store_errors() {
        if (!session_id()) {
            session_start();
        }
        $_SESSION['piper_privacy_form_errors'] = $this->errors;
    }

    /**
     * AJAX field validation
     */
    public function ajax_validate_field() {
        check_ajax_referer('piper_privacy_form', 'nonce');

        $field = sanitize_text_field($_POST['field'] ?? '');
        $value = $_POST['value'] ?? '';
        $form_type = sanitize_text_field($_POST['form_type'] ?? '');

        if (!isset($this->validation_rules[$form_type][$field])) {
            wp_send_json_error(__('Invalid field.', 'piper-privacy'));
        }

        $rule = $this->validation_rules[$form_type][$field];
        $this->form_data[$field] = $value;

        if ($this->validate_field($field, $rule)) {
            wp_send_json_success();
        } else {
            wp_send_json_error($this->errors[$field]);
        }
    }
}
