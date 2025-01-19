<?php
namespace PiperPrivacy\Includes\Stakeholders;

/**
 * Stakeholder Manager
 * 
 * Handles stakeholder management and notifications
 *
 * @package    PiperPrivacy
 * @subpackage PiperPrivacy/includes/stakeholders
 */
class StakeholderManager {
    /**
     * Initialize the stakeholder manager
     */
    public function __construct() {
        add_action('init', [$this, 'register_stakeholder_role']);
        add_action('add_meta_boxes', [$this, 'add_stakeholder_meta_box']);
        add_action('save_post', [$this, 'save_stakeholder_data']);
        add_action('piper_privacy_status_changed', [$this, 'notify_stakeholders'], 10, 3);
        add_action('piper_privacy_document_generated', [$this, 'notify_document_stakeholders'], 10, 3);
    }

    /**
     * Register stakeholder role
     */
    public function register_stakeholder_role() {
        add_role('privacy_stakeholder', __('Privacy Stakeholder', 'piper-privacy'), [
            'read' => true,
            'read_privacy_collection' => true,
            'read_privacy_threshold' => true,
            'read_privacy_impact' => true,
            'edit_privacy_collection' => true,
            'edit_privacy_threshold' => true,
            'edit_privacy_impact' => true,
        ]);
    }

    /**
     * Add stakeholder meta box
     */
    public function add_stakeholder_meta_box() {
        $post_types = ['privacy_collection', 'privacy_threshold', 'privacy_impact'];
        
        foreach ($post_types as $post_type) {
            add_meta_box(
                'privacy_stakeholders',
                __('Privacy Stakeholders', 'piper-privacy'),
                [$this, 'render_stakeholder_meta_box'],
                $post_type,
                'side',
                'default'
            );
        }
    }

    /**
     * Render stakeholder meta box
     *
     * @param WP_Post $post Post object
     */
    public function render_stakeholder_meta_box($post) {
        wp_nonce_field('save_stakeholder_data', 'stakeholder_nonce');

        $stakeholders = $this->get_stakeholders($post->ID);
        $roles = $this->get_stakeholder_roles();
        ?>
        <div class="stakeholder-list">
            <?php foreach ($stakeholders as $stakeholder): ?>
            <div class="stakeholder-item">
                <select name="stakeholder_users[]">
                    <option value=""><?php _e('Select User', 'piper-privacy'); ?></option>
                    <?php
                    $users = get_users(['role__in' => ['administrator', 'editor', 'privacy_stakeholder']]);
                    foreach ($users as $user) {
                        printf(
                            '<option value="%s" %s>%s</option>',
                            esc_attr($user->ID),
                            selected($stakeholder['user_id'], $user->ID, false),
                            esc_html($user->display_name)
                        );
                    }
                    ?>
                </select>
                <select name="stakeholder_roles[]">
                    <option value=""><?php _e('Select Role', 'piper-privacy'); ?></option>
                    <?php
                    foreach ($roles as $role => $label) {
                        printf(
                            '<option value="%s" %s>%s</option>',
                            esc_attr($role),
                            selected($stakeholder['role'], $role, false),
                            esc_html($label)
                        );
                    }
                    ?>
                </select>
                <button type="button" class="button remove-stakeholder">
                    <?php _e('Remove', 'piper-privacy'); ?>
                </button>
            </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="button add-stakeholder">
            <?php _e('Add Stakeholder', 'piper-privacy'); ?>
        </button>
        <script>
        jQuery(document).ready(function($) {
            $('.add-stakeholder').on('click', function() {
                var template = $('.stakeholder-item').first().clone();
                template.find('select').val('');
                $('.stakeholder-list').append(template);
            });

            $('.stakeholder-list').on('click', '.remove-stakeholder', function() {
                if ($('.stakeholder-item').length > 1) {
                    $(this).closest('.stakeholder-item').remove();
                }
            });
        });
        </script>
        <?php
    }

    /**
     * Save stakeholder data
     *
     * @param int $post_id Post ID
     */
    public function save_stakeholder_data($post_id) {
        if (!isset($_POST['stakeholder_nonce']) || 
            !wp_verify_nonce($_POST['stakeholder_nonce'], 'save_stakeholder_data')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        $post_type = get_post_type($post_id);
        if (!in_array($post_type, ['privacy_collection', 'privacy_threshold', 'privacy_impact'])) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $stakeholders = [];
        $user_ids = $_POST['stakeholder_users'] ?? [];
        $roles = $_POST['stakeholder_roles'] ?? [];

        foreach ($user_ids as $index => $user_id) {
            if (!empty($user_id) && !empty($roles[$index])) {
                $stakeholders[] = [
                    'user_id' => absint($user_id),
                    'role' => sanitize_key($roles[$index]),
                ];
            }
        }

        update_post_meta($post_id, '_privacy_stakeholders', $stakeholders);
    }

    /**
     * Get stakeholders for a post
     *
     * @param int $post_id Post ID
     * @return array Stakeholders
     */
    public function get_stakeholders($post_id) {
        $stakeholders = get_post_meta($post_id, '_privacy_stakeholders', true);
        return is_array($stakeholders) ? $stakeholders : [];
    }

    /**
     * Get stakeholder roles
     *
     * @return array Stakeholder roles
     */
    private function get_stakeholder_roles() {
        return [
            'program_manager' => __('Program Manager', 'piper-privacy'),
            'system_owner' => __('System Owner', 'piper-privacy'),
            'isso' => __('Information System Security Officer', 'piper-privacy'),
            'legal_officer' => __('Legal Officer', 'piper-privacy'),
            'privacy_officer' => __('Privacy Officer', 'piper-privacy'),
            'technical_lead' => __('Technical Lead', 'piper-privacy'),
            'data_custodian' => __('Data Custodian', 'piper-privacy'),
        ];
    }

    /**
     * Notify stakeholders of status change
     *
     * @param int    $post_id     Post ID
     * @param string $old_status  Old status
     * @param string $new_status  New status
     */
    public function notify_stakeholders($post_id, $old_status, $new_status) {
        $post = get_post($post_id);
        $stakeholders = $this->get_stakeholders($post_id);
        
        $subject = sprintf(
            __('[%s] %s Status Update: %s', 'piper-privacy'),
            get_bloginfo('name'),
            get_post_type_object($post->post_type)->labels->singular_name,
            $post->post_title
        );

        $message = sprintf(
            __("The status of the following item has changed:\n\nTitle: %s\nType: %s\nOld Status: %s\nNew Status: %s\n\nView: %s", 'piper-privacy'),
            $post->post_title,
            get_post_type_object($post->post_type)->labels->singular_name,
            get_post_status_object($old_status)->label,
            get_post_status_object($new_status)->label,
            get_edit_post_link($post_id, 'raw')
        );

        foreach ($stakeholders as $stakeholder) {
            $user = get_user_by('id', $stakeholder['user_id']);
            if ($user) {
                wp_mail($user->user_email, $subject, $message);
            }
        }
    }

    /**
     * Notify stakeholders of document generation
     *
     * @param int    $post_id       Post ID
     * @param string $document_type Document type
     * @param int    $attachment_id Attachment ID
     */
    public function notify_document_stakeholders($post_id, $document_type, $attachment_id) {
        $post = get_post($post_id);
        $stakeholders = $this->get_stakeholders($post_id);
        $document_url = wp_get_attachment_url($attachment_id);
        
        $subject = sprintf(
            __('[%s] New Document Generated: %s', 'piper-privacy'),
            get_bloginfo('name'),
            $post->post_title
        );

        $message = sprintf(
            __("A new document has been generated:\n\nTitle: %s\nType: %s\nDocument Type: %s\n\nView Document: %s", 'piper-privacy'),
            $post->post_title,
            get_post_type_object($post->post_type)->labels->singular_name,
            ucwords(str_replace('-', ' ', $document_type)),
            $document_url
        );

        foreach ($stakeholders as $stakeholder) {
            $user = get_user_by('id', $stakeholder['user_id']);
            if ($user) {
                wp_mail($user->user_email, $subject, $message);
            }
        }
    }
}
