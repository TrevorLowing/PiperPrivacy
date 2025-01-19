<?php
namespace PiperPrivacy\Includes\Forms;

use PiperPrivacy\Includes\Helpers\MetaboxHelpers;

/**
 * Forms Manager
 * 
 * Handles frontend form configuration and processing using MetaBox
 *
 * @package    PiperPrivacy
 * @subpackage PiperPrivacy/includes/forms
 */
class FormsManager {
    /**
     * Initialize the forms manager
     */
    public function __construct() {
        // Register frontend form hooks
        add_action('init', [$this, 'register_form_handlers']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_form_assets']);
        add_action('rwmb_frontend_before_process', [$this, 'before_process_form']);
        add_action('rwmb_frontend_after_process', [$this, 'after_process_form']);
        add_filter('rwmb_frontend_validate', [$this, 'validate_form'], 10, 2);
    }

    /**
     * Register form handlers
     */
    public function register_form_handlers() {
        // Register collection form
        rwmb_frontend_form([
            'id' => 'privacy_collection_form',
            'post_type' => 'privacy_collection',
            'post_status' => 'draft',
            'meta_box_id' => 'collection_fields',
            'submit_button' => __('Submit Collection Registration', 'piper-privacy'),
            'confirmation' => [
                'message' => __('Thank you! Your privacy collection has been registered.', 'piper-privacy'),
                'redirect' => home_url('/privacy-collections/'),
            ],
        ]);

        // Register threshold form
        rwmb_frontend_form([
            'id' => 'privacy_threshold_form',
            'post_type' => 'privacy_threshold',
            'post_status' => 'draft',
            'meta_box_id' => 'threshold_fields',
            'submit_button' => __('Submit Threshold Assessment', 'piper-privacy'),
            'confirmation' => [
                'message' => __('Thank you! Your privacy threshold assessment has been submitted.', 'piper-privacy'),
                'redirect' => home_url('/privacy-thresholds/'),
            ],
        ]);

        // Register impact form
        rwmb_frontend_form([
            'id' => 'privacy_impact_form',
            'post_type' => 'privacy_impact',
            'post_status' => 'draft',
            'meta_box_id' => 'impact_fields',
            'submit_button' => __('Submit Impact Assessment', 'piper-privacy'),
            'confirmation' => [
                'message' => __('Thank you! Your privacy impact assessment has been submitted.', 'piper-privacy'),
                'redirect' => home_url('/privacy-impacts/'),
            ],
        ]);
    }

    /**
     * Enqueue form assets
     */
    public function enqueue_form_assets() {
        if (!is_page(['privacy-collection', 'privacy-threshold', 'privacy-impact'])) {
            return;
        }

        wp_enqueue_style(
            'piper-privacy-forms',
            PIPER_PRIVACY_URL . 'assets/css/forms.css',
            [],
            PIPER_PRIVACY_VERSION
        );

        wp_enqueue_script(
            'piper-privacy-forms',
            PIPER_PRIVACY_URL . 'assets/js/forms.js',
            ['jquery', 'rwmb-frontend-form'],
            PIPER_PRIVACY_VERSION,
            true
        );

        wp_localize_script('piper-privacy-forms', 'piperPrivacyForms', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('piper_privacy_form'),
            'i18n' => [
                'required' => __('This field is required.', 'piper-privacy'),
                'invalid' => __('Please enter a valid value.', 'piper-privacy'),
                'uploading' => __('Uploading file...', 'piper-privacy'),
                'maxFileSize' => __('File is too large.', 'piper-privacy'),
                'invalidFileType' => __('File type not allowed.', 'piper-privacy'),
            ],
        ]);
    }

    /**
     * Validate form submission
     *
     * @param bool  $validate Whether to validate
     * @param array $config   Form configuration
     */
    public function validate_form($validate, $config) {
        // Add custom validation rules
        if (!wp_verify_nonce($_POST['_wpnonce'], 'piper_privacy_form')) {
            $validate = false;
            rwmb_frontend_form_add_error('security', __('Security check failed.', 'piper-privacy'));
        }

        // Validate required fields
        $required_fields = $this->get_required_fields($config['meta_box_id']);
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $validate = false;
                rwmb_frontend_form_add_error($field, __('This field is required.', 'piper-privacy'));
            }
        }

        // Validate file uploads
        if (!empty($_FILES)) {
            foreach ($_FILES as $field => $file) {
                if ($file['error'] !== UPLOAD_ERR_OK && $file['error'] !== UPLOAD_ERR_NO_FILE) {
                    $validate = false;
                    rwmb_frontend_form_add_error($field, $this->get_file_error_message($file['error']));
                }
            }
        }

        return $validate;
    }

    /**
     * Before form processing
     *
     * @param array $config Form configuration
     */
    public function before_process_form($config) {
        // Set post title if not provided
        if (empty($_POST['post_title'])) {
            $_POST['post_title'] = sprintf(
                __('%s - %s', 'piper-privacy'),
                ucwords(str_replace('_', ' ', $config['post_type'])),
                current_time('Y-m-d H:i:s')
            );
        }

        // Set post author if not set
        if (empty($_POST['post_author'])) {
            $_POST['post_author'] = get_current_user_id();
        }

        // Add workflow status
        $_POST['workflow_status'] = 'draft';

        do_action('piper_privacy_before_form_process', $config);
    }

    /**
     * After form processing
     *
     * @param array $config Form configuration
     */
    public function after_process_form($config) {
        $post_id = rwmb_frontend_form_get_processed_post_id();

        // Send notifications
        $this->send_form_notifications($post_id, $config);

        // Trigger workflow
        do_action('piper_privacy_form_submitted', $post_id, $config);
    }

    /**
     * Get required fields for a meta box
     *
     * @param string $meta_box_id Meta box ID
     * @return array Required fields
     */
    private function get_required_fields($meta_box_id) {
        $required = [];
        $meta_box = rwmb_get_registry('meta_box')->get($meta_box_id);
        
        if ($meta_box) {
            foreach ($meta_box->fields as $field) {
                if (!empty($field['required'])) {
                    $required[] = $field['id'];
                }
            }
        }

        return $required;
    }

    /**
     * Get file upload error message
     *
     * @param int $error_code Error code
     * @return string Error message
     */
    private function get_file_error_message($error_code) {
        switch ($error_code) {
            case UPLOAD_ERR_INI_SIZE:
                return __('The uploaded file exceeds the upload_max_filesize directive in php.ini.', 'piper-privacy');
            case UPLOAD_ERR_FORM_SIZE:
                return __('The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form.', 'piper-privacy');
            case UPLOAD_ERR_PARTIAL:
                return __('The uploaded file was only partially uploaded.', 'piper-privacy');
            case UPLOAD_ERR_NO_TMP_DIR:
                return __('Missing a temporary folder.', 'piper-privacy');
            case UPLOAD_ERR_CANT_WRITE:
                return __('Failed to write file to disk.', 'piper-privacy');
            case UPLOAD_ERR_EXTENSION:
                return __('A PHP extension stopped the file upload.', 'piper-privacy');
            default:
                return __('Unknown upload error.', 'piper-privacy');
        }
    }

    /**
     * Send form notifications
     *
     * @param int   $post_id Post ID
     * @param array $config  Form configuration
     */
    private function send_form_notifications($post_id, $config) {
        $post = get_post($post_id);
        $author = get_userdata($post->post_author);
        $admin_email = get_option('admin_email');

        // Send author notification
        if ($author && $author->user_email) {
            $subject = sprintf(
                __('[%s] Your %s has been submitted', 'piper-privacy'),
                get_bloginfo('name'),
                strtolower(get_post_type_object($post->post_type)->labels->singular_name)
            );

            $message = sprintf(
                __('Thank you for submitting your %1$s. You can view it here: %2$s', 'piper-privacy'),
                strtolower(get_post_type_object($post->post_type)->labels->singular_name),
                get_permalink($post_id)
            );

            wp_mail($author->user_email, $subject, $message);
        }

        // Send admin notification
        $subject = sprintf(
            __('[%s] New %s Submission', 'piper-privacy'),
            get_bloginfo('name'),
            get_post_type_object($post->post_type)->labels->singular_name
        );

        $message = sprintf(
            __('A new %1$s has been submitted by %2$s. You can view it here: %3$s', 'piper-privacy'),
            strtolower(get_post_type_object($post->post_type)->labels->singular_name),
            $author ? $author->display_name : __('Unknown', 'piper-privacy'),
            get_edit_post_link($post_id, '')
        );

        wp_mail($admin_email, $subject, $message);
    }
}
