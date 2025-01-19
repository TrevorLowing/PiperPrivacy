<?php
namespace PiperPrivacy\Includes\Workflow;

/**
 * Workflow Manager
 * 
 * Handles workflow state transitions and notifications for privacy-related items
 *
 * @package    PiperPrivacy
 * @subpackage PiperPrivacy/includes/workflow
 */
class WorkflowManager {
    /**
     * @var WorkflowConfig
     */
    private $config;

    /**
     * @var WorkflowSLA
     */
    private $sla;

    /**
     * Initialize the workflow manager
     */
    public function __construct() {
        $this->config = new WorkflowConfig();
        $this->sla = new WorkflowSLA();

        add_action('init', [$this, 'register_post_statuses']);
        add_action('transition_post_status', [$this, 'handle_status_transition'], 10, 3);
        add_action('add_meta_boxes', [$this, 'add_workflow_meta_box']);
        add_action('save_post', [$this, 'save_workflow_data']);
    }

    /**
     * Register custom post statuses for workflow stages
     */
    public function register_post_statuses() {
        register_post_status('draft', [
            'label' => _x('Draft', 'post status', 'piper-privacy'),
            'public' => true,
            'exclude_from_search' => false,
            'show_in_admin_all_list' => true,
            'show_in_admin_status_list' => true,
            'label_count' => _n_noop('Draft <span class="count">(%s)</span>', 'Drafts <span class="count">(%s)</span>', 'piper-privacy'),
        ]);

        register_post_status('pending_review', [
            'label' => _x('Pending Review', 'post status', 'piper-privacy'),
            'public' => true,
            'exclude_from_search' => false,
            'show_in_admin_all_list' => true,
            'show_in_admin_status_list' => true,
            'label_count' => _n_noop('Pending Review <span class="count">(%s)</span>', 'Pending Review <span class="count">(%s)</span>', 'piper-privacy'),
        ]);

        register_post_status('in_progress', [
            'label' => _x('In Progress', 'post status', 'piper-privacy'),
            'public' => true,
            'exclude_from_search' => false,
            'show_in_admin_all_list' => true,
            'show_in_admin_status_list' => true,
            'label_count' => _n_noop('In Progress <span class="count">(%s)</span>', 'In Progress <span class="count">(%s)</span>', 'piper-privacy'),
        ]);

        register_post_status('approved', [
            'label' => _x('Approved', 'post status', 'piper-privacy'),
            'public' => true,
            'exclude_from_search' => false,
            'show_in_admin_all_list' => true,
            'show_in_admin_status_list' => true,
            'label_count' => _n_noop('Approved <span class="count">(%s)</span>', 'Approved <span class="count">(%s)</span>', 'piper-privacy'),
        ]);

        register_post_status('retired', [
            'label' => _x('Retired', 'post status', 'piper-privacy'),
            'public' => true,
            'exclude_from_search' => false,
            'show_in_admin_all_list' => true,
            'show_in_admin_status_list' => true,
            'label_count' => _n_noop('Retired <span class="count">(%s)</span>', 'Retired <span class="count">(%s)</span>', 'piper-privacy'),
        ]);
    }

    /**
     * Handle post status transitions
     *
     * @param string  $new_status New post status
     * @param string  $old_status Old post status
     * @param WP_Post $post       Post object
     */
    public function handle_status_transition($new_status, $old_status, $post) {
        if (!in_array($post->post_type, ['privacy_collection', 'privacy_threshold', 'privacy_impact'])) {
            return;
        }

        // Check if user can make this transition
        if (!$this->config->can_transition_to_stage($post->post_type, $new_status)) {
            wp_die(__('You do not have permission to make this status change.', 'piper-privacy'));
        }

        // Log the transition
        $this->log_transition($post->ID, $old_status, $new_status);

        // Send notifications
        $this->send_notifications($post->ID, $old_status, $new_status);

        // Trigger any necessary actions
        do_action('piper_privacy_status_changed', $post->ID, $old_status, $new_status);
    }

    /**
     * Add workflow meta box to privacy-related post types
     */
    public function add_workflow_meta_box() {
        $post_types = ['privacy_collection', 'privacy_threshold', 'privacy_impact'];
        
        foreach ($post_types as $post_type) {
            add_meta_box(
                'privacy_workflow_status',
                __('Workflow Status', 'piper-privacy'),
                [$this, 'render_workflow_meta_box'],
                $post_type,
                'side',
                'high'
            );
        }
    }

    /**
     * Render workflow meta box
     *
     * @param WP_Post $post Post object
     */
    public function render_workflow_meta_box($post) {
        // Add nonce for security
        wp_nonce_field('privacy_workflow_meta_box', 'privacy_workflow_nonce');

        // Get current status and available transitions
        $current_status = $post->post_status;
        $available_transitions = $this->get_available_transitions($current_status);

        // Get workflow history
        $history = $this->get_workflow_history($post->ID);

        // Output the meta box HTML
        ?>
        <div class="privacy-workflow-status">
            <p>
                <strong><?php _e('Current Status:', 'piper-privacy'); ?></strong>
                <span class="status-<?php echo esc_attr($current_status); ?>">
                    <?php echo esc_html($this->get_status_label($current_status)); ?>
                </span>
            </p>

            <?php if (!empty($available_transitions)) : ?>
                <p>
                    <label for="workflow_transition">
                        <?php _e('Change Status:', 'piper-privacy'); ?>
                    </label>
                    <select name="workflow_transition" id="workflow_transition">
                        <option value=""><?php _e('— Select —', 'piper-privacy'); ?></option>
                        <?php foreach ($available_transitions as $status => $label) : ?>
                            <option value="<?php echo esc_attr($status); ?>">
                                <?php echo esc_html($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </p>

                <p>
                    <label for="workflow_comment">
                        <?php _e('Comment:', 'piper-privacy'); ?>
                    </label>
                    <textarea name="workflow_comment" id="workflow_comment" rows="2" class="widefat"></textarea>
                </p>
            <?php endif; ?>

            <?php if (!empty($history)) : ?>
                <div class="workflow-history">
                    <h4><?php _e('History', 'piper-privacy'); ?></h4>
                    <ul>
                        <?php foreach ($history as $entry) : ?>
                            <li>
                                <span class="history-date"><?php echo esc_html($entry->date); ?></span>
                                <span class="history-transition">
                                    <?php echo sprintf(
                                        __('Changed from %1$s to %2$s', 'piper-privacy'),
                                        $this->get_status_label($entry->old_status),
                                        $this->get_status_label($entry->new_status)
                                    ); ?>
                                </span>
                                <?php if (!empty($entry->comment)) : ?>
                                    <span class="history-comment"><?php echo esc_html($entry->comment); ?></span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Save workflow meta box data
     *
     * @param int $post_id Post ID
     */
    public function save_workflow_data($post_id) {
        // Security checks
        if (!isset($_POST['privacy_workflow_nonce']) || 
            !wp_verify_nonce($_POST['privacy_workflow_nonce'], 'privacy_workflow_meta_box')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check if this is a valid post type
        $post_type = get_post_type($post_id);
        if (!in_array($post_type, ['privacy_collection', 'privacy_threshold', 'privacy_impact'])) {
            return;
        }

        // Check if we have a status transition
        if (empty($_POST['workflow_transition'])) {
            return;
        }

        // Update the post status
        $new_status = sanitize_text_field($_POST['workflow_transition']);
        $comment = !empty($_POST['workflow_comment']) ? 
            sanitize_textarea_field($_POST['workflow_comment']) : '';

        wp_update_post([
            'ID' => $post_id,
            'post_status' => $new_status,
        ]);

        // Save the comment if provided
        if (!empty($comment)) {
            add_post_meta($post_id, '_workflow_comment', $comment);
        }
    }

    /**
     * Get available transitions based on current status
     *
     * @param string $current_status Current post status
     * @return array Array of available status transitions
     */
    private function get_available_transitions($current_status) {
        return $this->config->get_available_transitions($post->post_type, $current_status);
    }

    /**
     * Get workflow history for a post
     *
     * @param int $post_id Post ID
     * @return array Array of workflow history entries
     */
    private function get_workflow_history($post_id) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'privacy_workflow_history';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE post_id = %d ORDER BY date DESC",
            $post_id
        ));
    }

    /**
     * Log a status transition
     *
     * @param int    $post_id     Post ID
     * @param string $old_status  Old status
     * @param string $new_status  New status
     */
    private function log_transition($post_id, $old_status, $new_status) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'privacy_workflow_history';
        $comment = get_post_meta($post_id, '_workflow_comment', true);
        
        $wpdb->insert(
            $table_name,
            [
                'post_id' => $post_id,
                'old_status' => $old_status,
                'new_status' => $new_status,
                'user_id' => get_current_user_id(),
                'comment' => $comment,
                'date' => current_time('mysql'),
            ],
            ['%d', '%s', '%s', '%d', '%s', '%s']
        );

        // Clean up the comment meta
        delete_post_meta($post_id, '_workflow_comment');
    }

    /**
     * Send notifications for status transitions
     *
     * @param int    $post_id     Post ID
     * @param string $old_status  Old status
     * @param string $new_status  New status
     */
    private function send_notifications($post_id, $old_status, $new_status) {
        $post = get_post($post_id);
        $post_type_obj = get_post_type_object($post->post_type);
        $post_type_label = $post_type_obj->labels->singular_name;

        // Get users to notify based on roles and workflow stage
        $users_to_notify = $this->get_users_to_notify($new_status);

        if (empty($users_to_notify)) {
            return;
        }

        $subject = sprintf(
            __('[%s] %s Status Update: %s', 'piper-privacy'),
            get_bloginfo('name'),
            $post_type_label,
            $post->post_title
        );

        $message = sprintf(
            __('The status of %1$s "%2$s" has been changed from %3$s to %4$s.', 'piper-privacy'),
            $post_type_label,
            $post->post_title,
            $this->get_status_label($old_status),
            $this->get_status_label($new_status)
        );

        // Add the comment if one exists
        $comment = get_post_meta($post_id, '_workflow_comment', true);
        if (!empty($comment)) {
            $message .= "\n\n" . __('Comment:', 'piper-privacy') . "\n" . $comment;
        }

        $message .= "\n\n" . sprintf(
            __('View: %s', 'piper-privacy'),
            get_edit_post_link($post_id, 'raw')
        );

        // Send notifications
        foreach ($users_to_notify as $user) {
            wp_mail($user->user_email, $subject, $message);
        }
    }

    /**
     * Get users to notify based on workflow status
     *
     * @param string $status Workflow status
     * @return array Array of user objects
     */
    private function get_users_to_notify($status) {
        $roles_to_notify = [
            'pending_review' => ['administrator', 'editor'],
            'in_progress' => ['administrator', 'editor', 'author'],
            'approved' => ['administrator', 'editor', 'author'],
            'retired' => ['administrator', 'editor'],
        ];

        if (!isset($roles_to_notify[$status])) {
            return [];
        }

        $users = get_users([
            'role__in' => $roles_to_notify[$status],
            'fields' => ['user_email'],
        ]);

        return $users;
    }

    /**
     * Get human-readable status label
     *
     * @param string $status Status key
     * @return string Status label
     */
    private function get_status_label($status) {
        $labels = [
            'draft' => __('Draft', 'piper-privacy'),
            'pending_review' => __('Pending Review', 'piper-privacy'),
            'in_progress' => __('In Progress', 'piper-privacy'),
            'approved' => __('Approved', 'piper-privacy'),
            'retired' => __('Retired', 'piper-privacy'),
        ];

        return isset($labels[$status]) ? $labels[$status] : $status;
    }
}
