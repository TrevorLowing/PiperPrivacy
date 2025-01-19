<?php
/**
 * Consent Manager Model
 *
 * @package     PiperPrivacy
 * @subpackage  Modules\ConsentManager
 */

namespace PiperPrivacy\Modules\ConsentManager;

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Consent Manager Model Class
 */
class Model {
    /**
     * Get consents
     *
     * @param array $args Query arguments
     * @return array
     */
    public function get_consents($args) {
        $query = new \WP_Query($args);
        $consents = [];

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $consents[] = $this->format_consent(get_post());
            }
            wp_reset_postdata();
        }

        return $consents;
    }

    /**
     * Get single consent
     *
     * @param int $id Consent ID
     * @return array|\WP_Error
     */
    public function get_consent($id) {
        $post = get_post($id);

        if (!$post || 'pp_consent' !== $post->post_type) {
            return new \WP_Error(
                'not_found',
                __('Consent record not found.', 'piper-privacy'),
                ['status' => 404]
            );
        }

        return $this->format_consent($post);
    }

    /**
     * Create consent
     *
     * @param array $data Consent data
     * @return array|\WP_Error
     */
    public function create_consent($data) {
        $post_data = [
            'post_type'    => 'pp_consent',
            'post_title'   => sprintf(
                /* translators: 1: user ID, 2: consent type */
                __('Consent Record - User %1$s - %2$s', 'piper-privacy'),
                $data['user_id'],
                $data['consent_type']
            ),
            'post_status'  => 'publish',
            'post_author'  => $data['user_id'],
        ];

        // Create post
        $post_id = wp_insert_post($post_data, true);

        if (is_wp_error($post_id)) {
            return $post_id;
        }

        // Set consent type
        wp_set_object_terms($post_id, $data['consent_type'], 'pp_consent_type');

        // Save consent meta
        $this->save_consent_meta($post_id, $data);

        return $this->get_consent($post_id);
    }

    /**
     * Update consent
     *
     * @param int   $id   Consent ID
     * @param array $data Consent data
     * @return array|\WP_Error
     */
    public function update_consent($id, $data) {
        $post = get_post($id);

        if (!$post || 'pp_consent' !== $post->post_type) {
            return new \WP_Error(
                'not_found',
                __('Consent record not found.', 'piper-privacy'),
                ['status' => 404]
            );
        }

        // Update consent type if provided
        if (!empty($data['consent_type'])) {
            wp_set_object_terms($id, $data['consent_type'], 'pp_consent_type');
        }

        // Update consent meta
        $this->save_consent_meta($id, $data);

        return $this->get_consent($id);
    }

    /**
     * Delete consent
     *
     * @param int $id Consent ID
     * @return bool|\WP_Error
     */
    public function delete_consent($id) {
        $post = get_post($id);

        if (!$post || 'pp_consent' !== $post->post_type) {
            return new \WP_Error(
                'not_found',
                __('Consent record not found.', 'piper-privacy'),
                ['status' => 404]
            );
        }

        $deleted = wp_delete_post($id, true);

        if (!$deleted) {
            return new \WP_Error(
                'delete_failed',
                __('Failed to delete consent record.', 'piper-privacy'),
                ['status' => 500]
            );
        }

        return true;
    }

    /**
     * Verify consent
     *
     * @param int    $user_id      User ID
     * @param string $consent_type Consent type
     * @return bool
     */
    public function verify_consent($user_id, $consent_type) {
        $args = [
            'post_type'      => 'pp_consent',
            'author'         => $user_id,
            'posts_per_page' => 1,
            'tax_query'      => [
                [
                    'taxonomy' => 'pp_consent_type',
                    'field'    => 'slug',
                    'terms'    => $consent_type,
                ],
            ],
            'meta_query'     => [
                [
                    'key'     => '_pp_status',
                    'value'   => 'granted',
                ],
            ],
        ];

        $query = new \WP_Query($args);
        return $query->have_posts();
    }

    /**
     * Withdraw consent
     *
     * @param int    $user_id      User ID
     * @param string $consent_type Consent type
     * @return bool|\WP_Error
     */
    public function withdraw_consent($user_id, $consent_type) {
        $args = [
            'post_type'      => 'pp_consent',
            'author'         => $user_id,
            'posts_per_page' => -1,
            'tax_query'      => [
                [
                    'taxonomy' => 'pp_consent_type',
                    'field'    => 'slug',
                    'terms'    => $consent_type,
                ],
            ],
        ];

        $query = new \WP_Query($args);

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                update_post_meta(get_the_ID(), '_pp_status', 'withdrawn');
                update_post_meta(get_the_ID(), '_pp_withdrawn_date', current_time('mysql'));
            }
            wp_reset_postdata();
            return true;
        }

        return new \WP_Error(
            'not_found',
            __('No active consent records found.', 'piper-privacy'),
            ['status' => 404]
        );
    }

    /**
     * Save consent meta data
     *
     * @param int   $post_id Consent post ID
     * @param array $data    Consent data
     */
    private function save_consent_meta($post_id, $data) {
        $meta_fields = [
            'status',
            'ip_address',
            'user_agent',
            'expiry_date',
            'additional_info',
        ];

        foreach ($meta_fields as $field) {
            if (isset($data[$field])) {
                update_post_meta($post_id, '_pp_' . $field, $data[$field]);
            }
        }

        // Save consent version and timestamp
        update_post_meta($post_id, '_pp_version', PIPER_PRIVACY_VERSION);
        update_post_meta($post_id, '_pp_timestamp', current_time('mysql'));

        // Save revision
        $revision_data = [
            'user_id'     => get_current_user_id(),
            'timestamp'   => current_time('mysql'),
            'changes'     => $data,
        ];
        
        $revisions = get_post_meta($post_id, '_pp_revisions', true) ?: [];
        $revisions[] = $revision_data;
        update_post_meta($post_id, '_pp_revisions', $revisions);
    }

    /**
     * Format consent data
     *
     * @param \WP_Post $post Post object
     * @return array
     */
    private function format_consent($post) {
        $meta_fields = [
            'status',
            'ip_address',
            'user_agent',
            'expiry_date',
            'additional_info',
            'version',
            'timestamp',
        ];

        $consent = [
            'id'            => $post->ID,
            'user_id'       => $post->post_author,
            'created_at'    => $post->post_date_gmt,
            'updated_at'    => $post->post_modified_gmt,
        ];

        // Get consent type
        $types = wp_get_object_terms($post->ID, 'pp_consent_type');
        $consent['consent_type'] = !empty($types) ? $types[0]->slug : '';

        foreach ($meta_fields as $field) {
            $consent[$field] = get_post_meta($post->ID, '_pp_' . $field, true);
        }

        $consent['revisions'] = get_post_meta($post->ID, '_pp_revisions', true) ?: [];

        return $consent;
    }
}
