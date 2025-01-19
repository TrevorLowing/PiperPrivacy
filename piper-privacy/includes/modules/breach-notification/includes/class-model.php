<?php
/**
 * Breach Notification Model
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
 * Breach Notification Model Class
 */
class Model {
    /**
     * Get breaches
     *
     * @param array $args Query arguments
     * @return array
     */
    public function get_breaches($args) {
        $query = new \WP_Query($args);
        $breaches = [];

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $breaches[] = $this->format_breach(get_post());
            }
            wp_reset_postdata();
        }

        return $breaches;
    }

    /**
     * Get single breach
     *
     * @param int $id Breach ID
     * @return array|\WP_Error
     */
    public function get_breach($id) {
        $post = get_post($id);

        if (!$post || 'pp_breach' !== $post->post_type) {
            return new \WP_Error(
                'not_found',
                __('Breach incident not found.', 'piper-privacy'),
                ['status' => 404]
            );
        }

        return $this->format_breach($post);
    }

    /**
     * Create breach
     *
     * @param array $data Breach data
     * @return array|\WP_Error
     */
    public function create_breach($data) {
        $post_data = [
            'post_type'    => 'pp_breach',
            'post_title'   => $data['title'],
            'post_content' => $data['description'],
            'post_status'  => 'publish',
        ];

        // Create post
        $post_id = wp_insert_post($post_data, true);

        if (is_wp_error($post_id)) {
            return $post_id;
        }

        // Set taxonomies
        if (!empty($data['severity'])) {
            wp_set_object_terms($post_id, $data['severity'], 'pp_breach_severity');
        }

        if (!empty($data['status'])) {
            wp_set_object_terms($post_id, $data['status'], 'pp_breach_status');
        }

        // Save breach meta
        $this->save_breach_meta($post_id, $data);

        return $this->get_breach($post_id);
    }

    /**
     * Update breach
     *
     * @param int   $id   Breach ID
     * @param array $data Breach data
     * @return array|\WP_Error
     */
    public function update_breach($id, $data) {
        $post = get_post($id);

        if (!$post || 'pp_breach' !== $post->post_type) {
            return new \WP_Error(
                'not_found',
                __('Breach incident not found.', 'piper-privacy'),
                ['status' => 404]
            );
        }

        $post_data = [
            'ID'           => $id,
            'post_title'   => $data['title'],
            'post_content' => $data['description'],
        ];

        // Update post
        $updated = wp_update_post($post_data, true);

        if (is_wp_error($updated)) {
            return $updated;
        }

        // Update taxonomies
        if (isset($data['severity'])) {
            wp_set_object_terms($id, $data['severity'], 'pp_breach_severity');
        }

        if (isset($data['status'])) {
            wp_set_object_terms($id, $data['status'], 'pp_breach_status');
        }

        // Update breach meta
        $this->save_breach_meta($id, $data);

        return $this->get_breach($id);
    }

    /**
     * Delete breach
     *
     * @param int $id Breach ID
     * @return bool|\WP_Error
     */
    public function delete_breach($id) {
        $post = get_post($id);

        if (!$post || 'pp_breach' !== $post->post_type) {
            return new \WP_Error(
                'not_found',
                __('Breach incident not found.', 'piper-privacy'),
                ['status' => 404]
            );
        }

        $deleted = wp_delete_post($id, true);

        if (!$deleted) {
            return new \WP_Error(
                'delete_failed',
                __('Failed to delete breach incident.', 'piper-privacy'),
                ['status' => 500]
            );
        }

        return true;
    }

    /**
     * Update breach status
     *
     * @param int    $id     Breach ID
     * @param string $status New status
     * @return array|\WP_Error
     */
    public function update_breach_status($id, $status) {
        $post = get_post($id);

        if (!$post || 'pp_breach' !== $post->post_type) {
            return new \WP_Error(
                'not_found',
                __('Breach incident not found.', 'piper-privacy'),
                ['status' => 404]
            );
        }

        wp_set_object_terms($id, $status, 'pp_breach_status');
        
        // Add status change to timeline
        $this->add_timeline_entry($id, 'status_change', [
            'from' => get_post_meta($id, '_pp_current_status', true),
            'to'   => $status,
        ]);

        update_post_meta($id, '_pp_current_status', $status);
        update_post_meta($id, '_pp_status_changed', current_time('mysql'));

        return $this->get_breach($id);
    }

    /**
     * Get breach notifications
     *
     * @param int $breach_id Breach ID
     * @return array
     */
    public function get_notifications($breach_id) {
        return get_post_meta($breach_id, '_pp_notifications', true) ?: [];
    }

    /**
     * Get pending notifications
     *
     * @return array
     */
    public function get_pending_notifications() {
        $args = [
            'post_type'      => 'pp_breach',
            'posts_per_page' => -1,
            'meta_query'     => [
                [
                    'key'     => '_pp_has_pending_notifications',
                    'value'   => '1',
                ],
            ],
        ];

        $pending = [];
        $breaches = $this->get_breaches($args);

        foreach ($breaches as $breach) {
            $notifications = $this->get_notifications($breach['id']);
            foreach ($notifications as $notification) {
                if ('pending' === $notification['status'] && strtotime($notification['schedule_date']) <= time()) {
                    $pending[] = [
                        'breach_id' => $breach['id'],
                        'notification' => $notification,
                    ];
                }
            }
        }

        return $pending;
    }

    /**
     * Save breach meta data
     *
     * @param int   $post_id Breach post ID
     * @param array $data    Breach data
     */
    private function save_breach_meta($post_id, $data) {
        $meta_fields = [
            'detection_date',
            'affected_data',
            'affected_users',
            'notify_authorities',
            'notify_affected',
            'mitigation_steps',
        ];

        foreach ($meta_fields as $field) {
            if (isset($data[$field])) {
                update_post_meta($post_id, '_pp_' . $field, $data[$field]);
            }
        }

        // Add creation to timeline if new
        if (!get_post_meta($post_id, '_pp_timeline', true)) {
            $this->add_timeline_entry($post_id, 'created');
        }

        // Save current status
        $status = wp_get_object_terms($post_id, 'pp_breach_status', ['fields' => 'slugs']);
        update_post_meta($post_id, '_pp_current_status', $status[0] ?? '');
    }

    /**
     * Add timeline entry
     *
     * @param int    $post_id Breach post ID
     * @param string $type    Entry type
     * @param array  $data    Additional data
     */
    private function add_timeline_entry($post_id, $type, $data = []) {
        $timeline = get_post_meta($post_id, '_pp_timeline', true) ?: [];
        
        $entry = [
            'type'      => $type,
            'user_id'   => get_current_user_id(),
            'timestamp' => current_time('mysql'),
            'data'      => $data,
        ];

        $timeline[] = $entry;
        update_post_meta($post_id, '_pp_timeline', $timeline);
    }

    /**
     * Format breach data
     *
     * @param \WP_Post $post Post object
     * @return array
     */
    private function format_breach($post) {
        $meta_fields = [
            'detection_date',
            'affected_data',
            'affected_users',
            'notify_authorities',
            'notify_affected',
            'mitigation_steps',
            'current_status',
            'status_changed',
            'timeline',
        ];

        $breach = [
            'id'          => $post->ID,
            'title'       => $post->post_title,
            'description' => $post->post_content,
            'author'      => $post->post_author,
            'created_at'  => $post->post_date_gmt,
            'updated_at'  => $post->post_modified_gmt,
        ];

        // Get taxonomies
        $severity = wp_get_object_terms($post->ID, 'pp_breach_severity');
        $breach['severity'] = !empty($severity) ? $severity[0]->slug : '';

        $status = wp_get_object_terms($post->ID, 'pp_breach_status');
        $breach['status'] = !empty($status) ? $status[0]->slug : '';

        // Get meta fields
        foreach ($meta_fields as $field) {
            $breach[$field] = get_post_meta($post->ID, '_pp_' . $field, true);
        }

        // Get notifications
        $breach['notifications'] = $this->get_notifications($post->ID);

        return $breach;
    }
}
