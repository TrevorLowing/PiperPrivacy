<?php
namespace PiperPrivacy\Includes\Audit;

/**
 * Audit Trail
 * 
 * Handles comprehensive audit logging for privacy items
 *
 * @package    PiperPrivacy
 * @subpackage PiperPrivacy/includes/audit
 */
class AuditTrail {
    /**
     * Initialize the audit trail
     */
    public function __construct() {
        add_action('init', [$this, 'register_audit_log_table']);
        add_action('piper_privacy_status_changed', [$this, 'log_status_change'], 10, 3);
        add_action('piper_privacy_document_generated', [$this, 'log_document_generation'], 10, 3);
        add_action('piper_privacy_workflow_escalated', [$this, 'log_escalation'], 10, 2);
        add_action('save_post', [$this, 'log_content_changes'], 10, 3);
        add_action('added_post_meta', [$this, 'log_metadata_change'], 10, 4);
        add_action('updated_post_meta', [$this, 'log_metadata_change'], 10, 4);
        add_action('deleted_post_meta', [$this, 'log_metadata_change'], 10, 4);
    }

    /**
     * Register audit log table
     */
    public function register_audit_log_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'privacy_audit_log';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            user_id bigint(20) NOT NULL,
            action_type varchar(50) NOT NULL,
            action_details text NOT NULL,
            action_data longtext,
            ip_address varchar(45),
            user_agent varchar(255),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY post_id (post_id),
            KEY user_id (user_id),
            KEY action_type (action_type),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Log an audit event
     *
     * @param int    $post_id        Post ID
     * @param string $action_type    Type of action
     * @param string $action_details Action details
     * @param array  $action_data    Additional action data
     */
    private function log_event($post_id, $action_type, $action_details, $action_data = []) {
        global $wpdb;

        $user_id = get_current_user_id();
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $wpdb->insert(
            $wpdb->prefix . 'privacy_audit_log',
            [
                'post_id' => $post_id,
                'user_id' => $user_id,
                'action_type' => $action_type,
                'action_details' => $action_details,
                'action_data' => maybe_serialize($action_data),
                'ip_address' => $ip_address,
                'user_agent' => $user_agent,
            ],
            ['%d', '%d', '%s', '%s', '%s', '%s', '%s']
        );

        do_action('piper_privacy_audit_logged', $post_id, $action_type, $action_details, $action_data);
    }

    /**
     * Log status change
     *
     * @param int    $post_id    Post ID
     * @param string $old_status Old status
     * @param string $new_status New status
     */
    public function log_status_change($post_id, $old_status, $new_status) {
        $post = get_post($post_id);
        $details = sprintf(
            __('Status changed from %s to %s', 'piper-privacy'),
            get_post_status_object($old_status)->label,
            get_post_status_object($new_status)->label
        );

        $this->log_event($post_id, 'status_change', $details, [
            'old_status' => $old_status,
            'new_status' => $new_status,
        ]);
    }

    /**
     * Log document generation
     *
     * @param int    $post_id       Post ID
     * @param string $document_type Document type
     * @param int    $attachment_id Attachment ID
     */
    public function log_document_generation($post_id, $document_type, $attachment_id) {
        $details = sprintf(
            __('Generated document: %s', 'piper-privacy'),
            ucwords(str_replace('-', ' ', $document_type))
        );

        $this->log_event($post_id, 'document_generated', $details, [
            'document_type' => $document_type,
            'attachment_id' => $attachment_id,
        ]);
    }

    /**
     * Log workflow escalation
     *
     * @param int   $post_id      Post ID
     * @param float $days_overdue Days overdue
     */
    public function log_escalation($post_id, $days_overdue) {
        $details = sprintf(
            __('Workflow escalated (%.1f days overdue)', 'piper-privacy'),
            $days_overdue
        );

        $this->log_event($post_id, 'workflow_escalated', $details, [
            'days_overdue' => $days_overdue,
        ]);
    }

    /**
     * Log content changes
     *
     * @param int     $post_id Post ID
     * @param WP_Post $post    Post object
     * @param bool    $update  Whether this is an update
     */
    public function log_content_changes($post_id, $post, $update) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!in_array($post->post_type, ['privacy_collection', 'privacy_threshold', 'privacy_impact'])) {
            return;
        }

        if (!$update) {
            $details = __('Created new privacy item', 'piper-privacy');
            $this->log_event($post_id, 'item_created', $details);
            return;
        }

        $old_post = get_post($post_id);
        $changes = [];

        if ($old_post->post_title !== $post->post_title) {
            $changes['title'] = [
                'old' => $old_post->post_title,
                'new' => $post->post_title,
            ];
        }

        if ($old_post->post_content !== $post->post_content) {
            $changes['content'] = [
                'old' => wp_strip_all_tags($old_post->post_content),
                'new' => wp_strip_all_tags($post->post_content),
            ];
        }

        if (!empty($changes)) {
            $details = __('Updated privacy item content', 'piper-privacy');
            $this->log_event($post_id, 'content_updated', $details, $changes);
        }
    }

    /**
     * Log metadata changes
     *
     * @param int    $meta_id    Meta ID
     * @param int    $post_id    Post ID
     * @param string $meta_key   Meta key
     * @param mixed  $meta_value Meta value
     */
    public function log_metadata_change($meta_id, $post_id, $meta_key, $meta_value) {
        $post = get_post($post_id);
        if (!$post || !in_array($post->post_type, ['privacy_collection', 'privacy_threshold', 'privacy_impact'])) {
            return;
        }

        // Skip internal metadata
        if (strpos($meta_key, '_') === 0) {
            return;
        }

        $old_value = get_post_meta($post_id, $meta_key, true);
        $details = sprintf(
            __('Updated metadata: %s', 'piper-privacy'),
            $meta_key
        );

        $this->log_event($post_id, 'metadata_updated', $details, [
            'meta_key' => $meta_key,
            'old_value' => $old_value,
            'new_value' => $meta_value,
        ]);
    }

    /**
     * Get audit trail for a post
     *
     * @param int   $post_id    Post ID
     * @param array $args       Query arguments
     * @return array Audit trail entries
     */
    public function get_audit_trail($post_id, $args = []) {
        global $wpdb;

        $defaults = [
            'per_page' => 20,
            'page' => 1,
            'action_type' => '',
            'user_id' => 0,
            'date_start' => '',
            'date_end' => '',
            'orderby' => 'created_at',
            'order' => 'DESC',
        ];

        $args = wp_parse_args($args, $defaults);
        $table_name = $wpdb->prefix . 'privacy_audit_log';
        $offset = ($args['page'] - 1) * $args['per_page'];

        $where = $wpdb->prepare('WHERE post_id = %d', $post_id);

        if ($args['action_type']) {
            $where .= $wpdb->prepare(' AND action_type = %s', $args['action_type']);
        }

        if ($args['user_id']) {
            $where .= $wpdb->prepare(' AND user_id = %d', $args['user_id']);
        }

        if ($args['date_start']) {
            $where .= $wpdb->prepare(' AND created_at >= %s', $args['date_start']);
        }

        if ($args['date_end']) {
            $where .= $wpdb->prepare(' AND created_at <= %s', $args['date_end']);
        }

        $orderby = sanitize_sql_orderby("{$args['orderby']} {$args['order']}");
        $limit = $wpdb->prepare('LIMIT %d OFFSET %d', $args['per_page'], $offset);

        $query = "SELECT * FROM {$table_name} {$where} ORDER BY {$orderby} {$limit}";
        $results = $wpdb->get_results($query);

        return array_map(function($row) {
            $row->action_data = maybe_unserialize($row->action_data);
            $row->user = get_user_by('id', $row->user_id);
            return $row;
        }, $results);
    }

    /**
     * Get audit trail summary
     *
     * @param int $post_id Post ID
     * @return array Summary data
     */
    public function get_audit_summary($post_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'privacy_audit_log';

        $total_events = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table_name} WHERE post_id = %d",
                $post_id
            )
        );

        $event_types = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT action_type, COUNT(*) as count FROM {$table_name} 
                WHERE post_id = %d GROUP BY action_type",
                $post_id
            )
        );

        $users_involved = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT DISTINCT user_id FROM {$table_name} WHERE post_id = %d",
                $post_id
            )
        );

        $last_activity = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table_name} 
                WHERE post_id = %d ORDER BY created_at DESC LIMIT 1",
                $post_id
            )
        );

        if ($last_activity) {
            $last_activity->action_data = maybe_unserialize($last_activity->action_data);
            $last_activity->user = get_user_by('id', $last_activity->user_id);
        }

        return [
            'total_events' => $total_events,
            'event_types' => $event_types,
            'users_involved' => count($users_involved),
            'last_activity' => $last_activity,
        ];
    }
}
