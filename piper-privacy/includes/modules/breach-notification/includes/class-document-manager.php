<?php
/**
 * Document Manager Class
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
 * Document Manager class
 */
class Document_Manager {
    /**
     * Document types
     *
     * @var array
     */
    private $document_types = [
        'evidence' => [
            'name' => 'Evidence',
            'description' => 'System logs, screenshots, and other evidence related to the breach',
            'required' => true,
            'retention' => '5 years',
        ],
        'notification' => [
            'name' => 'Notification',
            'description' => 'Copies of notifications sent to authorities, individuals, and other parties',
            'required' => true,
            'retention' => '5 years',
        ],
        'assessment' => [
            'name' => 'Assessment',
            'description' => 'Risk assessments and impact analyses',
            'required' => true,
            'retention' => '5 years',
        ],
        'communication' => [
            'name' => 'Communication',
            'description' => 'Internal and external communications regarding the breach',
            'required' => true,
            'retention' => '5 years',
        ],
        'remediation' => [
            'name' => 'Remediation',
            'description' => 'Documentation of steps taken to address and prevent future breaches',
            'required' => true,
            'retention' => '5 years',
        ],
    ];

    /**
     * Initialize the document manager
     */
    public function __construct() {
        add_action('init', [$this, 'register_post_type']);
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post', [$this, 'save_meta_boxes']);
        add_action('wp_ajax_pp_upload_document', [$this, 'ajax_upload_document']);
        add_action('wp_ajax_pp_delete_document', [$this, 'ajax_delete_document']);
        add_action('before_delete_post', [$this, 'delete_associated_documents']);
        add_filter('upload_dir', [$this, 'modify_upload_dir']);
    }

    /**
     * Register document post type
     */
    public function register_post_type() {
        register_post_type('pp_document', [
            'labels' => [
                'name' => __('Documents', 'piper-privacy'),
                'singular_name' => __('Document', 'piper-privacy'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'supports' => ['title', 'author'],
            'capability_type' => 'post',
            'capabilities' => [
                'create_posts' => 'manage_options',
                'edit_post' => 'manage_options',
                'delete_post' => 'manage_options',
            ],
        ]);
    }

    /**
     * Add meta boxes
     */
    public function add_meta_boxes() {
        add_meta_box(
            'pp_document_details',
            __('Document Details', 'piper-privacy'),
            [$this, 'render_meta_box'],
            'pp_document',
            'normal',
            'high'
        );
    }

    /**
     * Render meta box
     *
     * @param WP_Post $post Post object.
     */
    public function render_meta_box($post) {
        $document_type = get_post_meta($post->ID, '_pp_document_type', true);
        $breach_id = get_post_meta($post->ID, '_pp_breach_id', true);
        $file_path = get_post_meta($post->ID, '_pp_file_path', true);
        $retention_date = get_post_meta($post->ID, '_pp_retention_date', true);
        
        wp_nonce_field('pp_document_meta_box', 'pp_document_meta_box_nonce');
        ?>
        <table class="form-table">
            <tr>
                <th scope="row"><?php esc_html_e('Document Type', 'piper-privacy'); ?></th>
                <td>
                    <select name="pp_document_type" id="pp_document_type">
                        <?php foreach ($this->document_types as $type => $data) : ?>
                            <option value="<?php echo esc_attr($type); ?>" <?php selected($document_type, $type); ?>>
                                <?php echo esc_html($data['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description document-type-description"></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Related Breach', 'piper-privacy'); ?></th>
                <td>
                    <select name="pp_breach_id" id="pp_breach_id">
                        <option value=""><?php esc_html_e('Select a breach', 'piper-privacy'); ?></option>
                        <?php
                        $breaches = get_posts([
                            'post_type' => 'pp_breach',
                            'posts_per_page' => -1,
                            'orderby' => 'date',
                            'order' => 'DESC',
                        ]);
                        foreach ($breaches as $breach) {
                            printf(
                                '<option value="%d" %s>%s</option>',
                                esc_attr($breach->ID),
                                selected($breach_id, $breach->ID, false),
                                esc_html($breach->post_title)
                            );
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('File', 'piper-privacy'); ?></th>
                <td>
                    <?php if ($file_path) : ?>
                        <div class="pp-document-file">
                            <a href="<?php echo esc_url(wp_get_attachment_url($file_path)); ?>" target="_blank">
                                <?php echo esc_html(basename(get_attached_file($file_path))); ?>
                            </a>
                        </div>
                    <?php else : ?>
                        <div class="pp-document-upload">
                            <input type="file" name="pp_document_file" id="pp_document_file" />
                            <p class="description">
                                <?php esc_html_e('Allowed file types: PDF, DOC, DOCX, XLS, XLSX, PNG, JPG, JPEG', 'piper-privacy'); ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Retention Date', 'piper-privacy'); ?></th>
                <td>
                    <input type="date" name="pp_retention_date" id="pp_retention_date" 
                           value="<?php echo esc_attr($retention_date); ?>" />
                    <p class="description">
                        <?php esc_html_e('Date when this document can be safely deleted', 'piper-privacy'); ?>
                    </p>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Save meta box data
     *
     * @param int $post_id Post ID.
     */
    public function save_meta_box($post_id) {
        if (!isset($_POST['pp_document_meta_box_nonce'])) {
            return;
        }

        if (!wp_verify_nonce($_POST['pp_document_meta_box_nonce'], 'pp_document_meta_box')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('manage_options')) {
            return;
        }

        // Save document type
        if (isset($_POST['pp_document_type'])) {
            update_post_meta(
                $post_id,
                '_pp_document_type',
                sanitize_text_field($_POST['pp_document_type'])
            );
        }

        // Save breach ID
        if (isset($_POST['pp_breach_id'])) {
            update_post_meta(
                $post_id,
                '_pp_breach_id',
                intval($_POST['pp_breach_id'])
            );
        }

        // Save retention date
        if (isset($_POST['pp_retention_date'])) {
            update_post_meta(
                $post_id,
                '_pp_retention_date',
                sanitize_text_field($_POST['pp_retention_date'])
            );
        }
    }

    /**
     * Handle document upload via AJAX
     */
    public function ajax_upload_document() {
        check_ajax_referer('pp_upload_document', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        if (!isset($_FILES['file'])) {
            wp_send_json_error('No file uploaded');
        }

        $breach_id = isset($_POST['breach_id']) ? intval($_POST['breach_id']) : 0;
        $document_type = isset($_POST['document_type']) ? sanitize_text_field($_POST['document_type']) : '';

        if (!$breach_id || !$document_type) {
            wp_send_json_error('Missing required fields');
        }

        // Create document post
        $document_id = wp_insert_post([
            'post_type' => 'pp_document',
            'post_title' => sanitize_text_field($_FILES['file']['name']),
            'post_status' => 'publish',
            'meta_input' => [
                '_pp_document_type' => $document_type,
                '_pp_breach_id' => $breach_id,
                '_pp_retention_date' => date('Y-m-d', strtotime('+5 years')),
            ],
        ]);

        if (is_wp_error($document_id)) {
            wp_send_json_error($document_id->get_error_message());
        }

        // Handle file upload
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        $file_id = media_handle_upload('file', $document_id);

        if (is_wp_error($file_id)) {
            wp_delete_post($document_id, true);
            wp_send_json_error($file_id->get_error_message());
        }

        update_post_meta($document_id, '_pp_file_path', $file_id);

        wp_send_json_success([
            'document_id' => $document_id,
            'file_url' => wp_get_attachment_url($file_id),
            'file_name' => basename(get_attached_file($file_id)),
        ]);
    }

    /**
     * Handle document deletion via AJAX
     */
    public function ajax_delete_document() {
        check_ajax_referer('pp_delete_document', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $document_id = isset($_POST['document_id']) ? intval($_POST['document_id']) : 0;
        if (!$document_id) {
            wp_send_json_error('Invalid document ID');
        }

        $file_id = get_post_meta($document_id, '_pp_file_path', true);
        if ($file_id) {
            wp_delete_attachment($file_id, true);
        }

        wp_delete_post($document_id, true);
        wp_send_json_success();
    }

    /**
     * Delete associated documents when a breach is deleted
     *
     * @param int $post_id Post ID.
     */
    public function delete_associated_documents($post_id) {
        if (get_post_type($post_id) !== 'pp_breach') {
            return;
        }

        $documents = get_posts([
            'post_type' => 'pp_document',
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => '_pp_breach_id',
                    'value' => $post_id,
                ],
            ],
        ]);

        foreach ($documents as $document) {
            $file_id = get_post_meta($document->ID, '_pp_file_path', true);
            if ($file_id) {
                wp_delete_attachment($file_id, true);
            }
            wp_delete_post($document->ID, true);
        }
    }

    /**
     * Modify upload directory for breach documents
     *
     * @param array $dirs Upload directory information.
     * @return array Modified upload directory information.
     */
    public function modify_upload_dir($dirs) {
        if (!isset($_POST['breach_id'])) {
            return $dirs;
        }

        $breach_id = intval($_POST['breach_id']);
        if (!$breach_id) {
            return $dirs;
        }

        $dirs['subdir'] = '/piper-privacy/breaches/' . $breach_id;
        $dirs['path'] = $dirs['basedir'] . $dirs['subdir'];
        $dirs['url'] = $dirs['baseurl'] . $dirs['subdir'];

        return $dirs;
    }

    /**
     * Get documents for a breach
     *
     * @param int $breach_id Breach ID.
     * @return array Documents.
     */
    public function get_breach_documents($breach_id) {
        $documents = get_posts([
            'post_type' => 'pp_document',
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => '_pp_breach_id',
                    'value' => $breach_id,
                ],
            ],
        ]);

        $result = [];
        foreach ($documents as $document) {
            $file_id = get_post_meta($document->ID, '_pp_file_path', true);
            $result[] = [
                'id' => $document->ID,
                'title' => $document->post_title,
                'type' => get_post_meta($document->ID, '_pp_document_type', true),
                'file_url' => wp_get_attachment_url($file_id),
                'file_name' => basename(get_attached_file($file_id)),
                'retention_date' => get_post_meta($document->ID, '_pp_retention_date', true),
                'upload_date' => $document->post_date,
            ];
        }

        return $result;
    }

    /**
     * Check for documents nearing retention date
     *
     * @return array Documents nearing retention date.
     */
    public function check_retention_dates() {
        $warning_threshold = date('Y-m-d', strtotime('+30 days'));
        
        return get_posts([
            'post_type' => 'pp_document',
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => '_pp_retention_date',
                    'value' => [$warning_threshold, date('Y-m-d')],
                    'type' => 'DATE',
                    'compare' => 'BETWEEN',
                ],
            ],
        ]);
    }
}
