<?php
/**
 * Form Storage Class
 *
 * @package PiperPrivacy
 */

namespace PiperPrivacy\Forms;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Form_Storage
 * Handles form data storage and retrieval
 */
class Form_Storage {
    /**
     * Post types
     *
     * @var array
     */
    private $post_types = [
        'collection' => 'privacy_collection',
        'threshold' => 'privacy_threshold',
        'impact' => 'privacy_impact',
    ];

    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('init', [$this, 'register_post_types']);
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post', [$this, 'save_form_meta']);
        add_filter('manage_privacy_collection_posts_columns', [$this, 'add_custom_columns']);
        add_filter('manage_privacy_threshold_posts_columns', [$this, 'add_custom_columns']);
        add_filter('manage_privacy_impact_posts_columns', [$this, 'add_custom_columns']);
        add_action('manage_posts_custom_column', [$this, 'render_custom_columns'], 10, 2);
    }

    /**
     * Register custom post types
     */
    public function register_post_types() {
        // Privacy Collection Registration
        register_post_type('privacy_collection', [
            'labels' => [
                'name' => __('Privacy Collections', 'piper-privacy'),
                'singular_name' => __('Privacy Collection', 'piper-privacy'),
                'add_new' => __('Add New', 'piper-privacy'),
                'add_new_item' => __('Add New Privacy Collection', 'piper-privacy'),
                'edit_item' => __('Edit Privacy Collection', 'piper-privacy'),
                'new_item' => __('New Privacy Collection', 'piper-privacy'),
                'view_item' => __('View Privacy Collection', 'piper-privacy'),
                'search_items' => __('Search Privacy Collections', 'piper-privacy'),
                'not_found' => __('No privacy collections found', 'piper-privacy'),
                'not_found_in_trash' => __('No privacy collections found in trash', 'piper-privacy'),
            ],
            'public' => true,
            'has_archive' => true,
            'menu_icon' => 'dashicons-shield',
            'supports' => ['title', 'editor', 'revisions'],
            'rewrite' => ['slug' => 'privacy-collections'],
            'show_in_rest' => true,
            'capability_type' => 'post',
            'map_meta_cap' => true,
        ]);

        // Privacy Threshold Assessment
        register_post_type('privacy_threshold', [
            'labels' => [
                'name' => __('Privacy Thresholds', 'piper-privacy'),
                'singular_name' => __('Privacy Threshold', 'piper-privacy'),
                'add_new' => __('Add New', 'piper-privacy'),
                'add_new_item' => __('Add New Privacy Threshold', 'piper-privacy'),
                'edit_item' => __('Edit Privacy Threshold', 'piper-privacy'),
                'new_item' => __('New Privacy Threshold', 'piper-privacy'),
                'view_item' => __('View Privacy Threshold', 'piper-privacy'),
                'search_items' => __('Search Privacy Thresholds', 'piper-privacy'),
                'not_found' => __('No privacy thresholds found', 'piper-privacy'),
                'not_found_in_trash' => __('No privacy thresholds found in trash', 'piper-privacy'),
            ],
            'public' => true,
            'has_archive' => true,
            'menu_icon' => 'dashicons-chart-area',
            'supports' => ['title', 'editor', 'revisions'],
            'rewrite' => ['slug' => 'privacy-thresholds'],
            'show_in_rest' => true,
            'capability_type' => 'post',
            'map_meta_cap' => true,
        ]);

        // Privacy Impact Assessment
        register_post_type('privacy_impact', [
            'labels' => [
                'name' => __('Privacy Impacts', 'piper-privacy'),
                'singular_name' => __('Privacy Impact', 'piper-privacy'),
                'add_new' => __('Add New', 'piper-privacy'),
                'add_new_item' => __('Add New Privacy Impact', 'piper-privacy'),
                'edit_item' => __('Edit Privacy Impact', 'piper-privacy'),
                'new_item' => __('New Privacy Impact', 'piper-privacy'),
                'view_item' => __('View Privacy Impact', 'piper-privacy'),
                'search_items' => __('Search Privacy Impacts', 'piper-privacy'),
                'not_found' => __('No privacy impacts found', 'piper-privacy'),
                'not_found_in_trash' => __('No privacy impacts found in trash', 'piper-privacy'),
            ],
            'public' => true,
            'has_archive' => true,
            'menu_icon' => 'dashicons-analytics',
            'supports' => ['title', 'editor', 'revisions'],
            'rewrite' => ['slug' => 'privacy-impacts'],
            'show_in_rest' => true,
            'capability_type' => 'post',
            'map_meta_cap' => true,
        ]);
    }

    /**
     * Add meta boxes
     */
    public function add_meta_boxes() {
        add_meta_box(
            'privacy_collection_details',
            __('Collection Details', 'piper-privacy'),
            [$this, 'render_collection_meta_box'],
            'privacy_collection',
            'normal',
            'high'
        );

        add_meta_box(
            'privacy_threshold_details',
            __('Threshold Details', 'piper-privacy'),
            [$this, 'render_threshold_meta_box'],
            'privacy_threshold',
            'normal',
            'high'
        );

        add_meta_box(
            'privacy_impact_details',
            __('Impact Details', 'piper-privacy'),
            [$this, 'render_impact_meta_box'],
            'privacy_impact',
            'normal',
            'high'
        );
    }

    /**
     * Render collection meta box
     *
     * @param \WP_Post $post Post object.
     */
    public function render_collection_meta_box($post) {
        wp_nonce_field('piper_privacy_meta_box', 'piper_privacy_meta_box_nonce');

        $meta = get_post_meta($post->ID);
        ?>
        <table class="form-table">
            <tr>
                <th><label for="pii_categories"><?php esc_html_e('PII Categories', 'piper-privacy'); ?></label></th>
                <td>
                    <?php
                    $categories = maybe_unserialize($meta['pii_categories'][0] ?? []);
                    $this->render_checkbox_list('pii_categories', $this->get_pii_categories(), $categories);
                    ?>
                </td>
            </tr>
            <tr>
                <th><label for="data_elements"><?php esc_html_e('Data Elements', 'piper-privacy'); ?></label></th>
                <td>
                    <?php
                    $elements = maybe_unserialize($meta['data_elements'][0] ?? []);
                    $this->render_checkbox_list('data_elements', $this->get_data_elements(), $elements);
                    ?>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Render threshold meta box
     *
     * @param \WP_Post $post Post object.
     */
    public function render_threshold_meta_box($post) {
        wp_nonce_field('piper_privacy_meta_box', 'piper_privacy_meta_box_nonce');

        $meta = get_post_meta($post->ID);
        ?>
        <table class="form-table">
            <tr>
                <th><label for="system_owner"><?php esc_html_e('System Owner', 'piper-privacy'); ?></label></th>
                <td>
                    <input type="text" id="system_owner" name="system_owner" value="<?php echo esc_attr($meta['system_owner'][0] ?? ''); ?>">
                </td>
            </tr>
            <tr>
                <th><label for="risk_level"><?php esc_html_e('Risk Level', 'piper-privacy'); ?></label></th>
                <td>
                    <select id="risk_level" name="risk_level">
                        <?php
                        $risk_level = $meta['risk_level'][0] ?? '';
                        $risk_levels = ['low', 'medium', 'high', 'very_high'];
                        foreach ($risk_levels as $level) {
                            printf(
                                '<option value="%s" %s>%s</option>',
                                esc_attr($level),
                                selected($risk_level, $level, false),
                                esc_html(ucfirst($level))
                            );
                        }
                        ?>
                    </select>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Render impact meta box
     *
     * @param \WP_Post $post Post object.
     */
    public function render_impact_meta_box($post) {
        wp_nonce_field('piper_privacy_meta_box', 'piper_privacy_meta_box_nonce');

        $meta = get_post_meta($post->ID);
        ?>
        <table class="form-table">
            <tr>
                <th><label for="stakeholders"><?php esc_html_e('Stakeholders', 'piper-privacy'); ?></label></th>
                <td>
                    <textarea id="stakeholders" name="stakeholders" rows="4"><?php echo esc_textarea($meta['stakeholders'][0] ?? ''); ?></textarea>
                </td>
            </tr>
            <tr>
                <th><label><?php esc_html_e('Privacy Risks', 'piper-privacy'); ?></label></th>
                <td>
                    <?php
                    $risks = maybe_unserialize($meta['privacy_risks'][0] ?? []);
                    if (!empty($risks)) {
                        foreach ($risks as $index => $risk) {
                            ?>
                            <div class="privacy-risk">
                                <p>
                                    <label><?php esc_html_e('Risk Name', 'piper-privacy'); ?></label><br>
                                    <input type="text" name="privacy_risks[<?php echo esc_attr($index); ?>][risk_name]" 
                                           value="<?php echo esc_attr($risk['risk_name'] ?? ''); ?>">
                                </p>
                                <p>
                                    <label><?php esc_html_e('Description', 'piper-privacy'); ?></label><br>
                                    <textarea name="privacy_risks[<?php echo esc_attr($index); ?>][description]" rows="3"><?php 
                                        echo esc_textarea($risk['description'] ?? ''); 
                                    ?></textarea>
                                </p>
                            </div>
                            <?php
                        }
                    }
                    ?>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Save form meta
     *
     * @param int $post_id Post ID.
     */
    public function save_form_meta($post_id) {
        if (!isset($_POST['piper_privacy_meta_box_nonce'])) {
            return;
        }

        if (!wp_verify_nonce($_POST['piper_privacy_meta_box_nonce'], 'piper_privacy_meta_box')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $fields = [
            'pii_categories',
            'data_elements',
            'system_owner',
            'risk_level',
            'stakeholders',
            'privacy_risks',
        ];

        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                $value = $_POST[$field];
                if (is_array($value)) {
                    $value = array_map('sanitize_text_field', $value);
                } else {
                    $value = sanitize_text_field($value);
                }
                update_post_meta($post_id, $field, $value);
            }
        }
    }

    /**
     * Add custom columns
     *
     * @param array $columns Columns array.
     * @return array
     */
    public function add_custom_columns($columns) {
        $new_columns = [];
        $new_columns['cb'] = $columns['cb'];
        $new_columns['title'] = $columns['title'];
        $new_columns['system_owner'] = __('System Owner', 'piper-privacy');
        $new_columns['risk_level'] = __('Risk Level', 'piper-privacy');
        $new_columns['date'] = $columns['date'];

        return $new_columns;
    }

    /**
     * Render custom columns
     *
     * @param string $column Column name.
     * @param int    $post_id Post ID.
     */
    public function render_custom_columns($column, $post_id) {
        switch ($column) {
            case 'system_owner':
                echo esc_html(get_post_meta($post_id, 'system_owner', true));
                break;

            case 'risk_level':
                $risk_level = get_post_meta($post_id, 'risk_level', true);
                echo esc_html(ucfirst($risk_level));
                break;
        }
    }

    /**
     * Get PII categories
     *
     * @return array
     */
    private function get_pii_categories() {
        return [
            'general_personal' => __('General Personal Information', 'piper-privacy'),
            'contact' => __('Contact Information', 'piper-privacy'),
            'government_id' => __('Government ID Numbers', 'piper-privacy'),
            'financial' => __('Financial Information', 'piper-privacy'),
            'health' => __('Health Information', 'piper-privacy'),
            'biometric' => __('Biometric Data', 'piper-privacy'),
            'genetic' => __('Genetic Data', 'piper-privacy'),
            'location' => __('Location Data', 'piper-privacy'),
            'criminal' => __('Criminal Records', 'piper-privacy'),
            'children' => __('Children\'s Data', 'piper-privacy'),
        ];
    }

    /**
     * Get data elements
     *
     * @return array
     */
    private function get_data_elements() {
        return [
            'name' => __('Name', 'piper-privacy'),
            'email' => __('Email', 'piper-privacy'),
            'phone' => __('Phone Number', 'piper-privacy'),
            'address' => __('Address', 'piper-privacy'),
            'ssn' => __('Social Security Number', 'piper-privacy'),
            'passport' => __('Passport Number', 'piper-privacy'),
            'drivers_license' => __('Driver\'s License', 'piper-privacy'),
            'credit_card' => __('Credit Card Information', 'piper-privacy'),
            'bank_account' => __('Bank Account Information', 'piper-privacy'),
            'medical_records' => __('Medical Records', 'piper-privacy'),
        ];
    }

    /**
     * Render checkbox list
     *
     * @param string $name Field name.
     * @param array  $options Options array.
     * @param array  $selected Selected values.
     */
    private function render_checkbox_list($name, $options, $selected) {
        foreach ($options as $value => $label) {
            printf(
                '<label><input type="checkbox" name="%s[]" value="%s" %s> %s</label><br>',
                esc_attr($name),
                esc_attr($value),
                checked(in_array($value, (array) $selected, true), true, false),
                esc_html($label)
            );
        }
    }

    /**
     * Get form data by ID
     *
     * @param int    $post_id Post ID.
     * @param string $form_type Form type.
     * @return array|false
     */
    public function get_form_data($post_id, $form_type) {
        if (!isset($this->post_types[$form_type])) {
            return false;
        }

        $post = get_post($post_id);
        if (!$post || $post->post_type !== $this->post_types[$form_type]) {
            return false;
        }

        $data = [
            'id' => $post->ID,
            'title' => $post->post_title,
            'content' => $post->post_content,
            'status' => $post->post_status,
            'date' => $post->post_date,
            'modified' => $post->post_modified,
        ];

        $meta = get_post_meta($post->ID);
        foreach ($meta as $key => $value) {
            $data[$key] = maybe_unserialize($value[0]);
        }

        return $data;
    }

    /**
     * Get forms by type
     *
     * @param string $form_type Form type.
     * @param array  $args Query arguments.
     * @return array
     */
    public function get_forms($form_type, $args = []) {
        if (!isset($this->post_types[$form_type])) {
            return [];
        }

        $default_args = [
            'post_type' => $this->post_types[$form_type],
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
        ];

        $args = wp_parse_args($args, $default_args);
        $posts = get_posts($args);

        $forms = [];
        foreach ($posts as $post) {
            $forms[] = $this->get_form_data($post->ID, $form_type);
        }

        return $forms;
    }
}
