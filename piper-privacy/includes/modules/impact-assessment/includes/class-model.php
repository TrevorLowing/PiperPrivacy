<?php
/**
 * Impact Assessment Model
 *
 * @package     PiperPrivacy
 * @subpackage  Modules\ImpactAssessment
 */

namespace PiperPrivacy\Modules\ImpactAssessment;

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Impact Assessment Model Class
 */
class Model {
    /**
     * Get assessments
     *
     * @param array $args Query arguments
     * @return array
     */
    public function get_assessments($args) {
        $query = new \WP_Query($args);
        $assessments = [];

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $assessments[] = $this->format_assessment(get_post());
            }
            wp_reset_postdata();
        }

        return $assessments;
    }

    /**
     * Get single assessment
     *
     * @param int $id Assessment ID
     * @return array|\WP_Error
     */
    public function get_assessment($id) {
        $post = get_post($id);

        if (!$post || 'pp_assessment' !== $post->post_type) {
            return new \WP_Error(
                'not_found',
                __('Assessment not found.', 'piper-privacy'),
                ['status' => 404]
            );
        }

        return $this->format_assessment($post);
    }

    /**
     * Create assessment
     *
     * @param array $data Assessment data
     * @return array|\WP_Error
     */
    public function create_assessment($data) {
        $post_data = [
            'post_type'    => 'pp_assessment',
            'post_title'   => sanitize_text_field($data['title']),
            'post_status'  => 'publish',
            'post_author'  => get_current_user_id(),
        ];

        // Create post
        $post_id = wp_insert_post($post_data, true);

        if (is_wp_error($post_id)) {
            return $post_id;
        }

        // Save assessment meta
        $this->save_assessment_meta($post_id, $data);

        return $this->get_assessment($post_id);
    }

    /**
     * Update assessment
     *
     * @param int   $id   Assessment ID
     * @param array $data Assessment data
     * @return array|\WP_Error
     */
    public function update_assessment($id, $data) {
        $post = get_post($id);

        if (!$post || 'pp_assessment' !== $post->post_type) {
            return new \WP_Error(
                'not_found',
                __('Assessment not found.', 'piper-privacy'),
                ['status' => 404]
            );
        }

        $post_data = [
            'ID'          => $id,
            'post_title'  => sanitize_text_field($data['title']),
        ];

        // Update post
        $updated = wp_update_post($post_data, true);

        if (is_wp_error($updated)) {
            return $updated;
        }

        // Update assessment meta
        $this->save_assessment_meta($id, $data);

        return $this->get_assessment($id);
    }

    /**
     * Delete assessment
     *
     * @param int $id Assessment ID
     * @return bool|\WP_Error
     */
    public function delete_assessment($id) {
        $post = get_post($id);

        if (!$post || 'pp_assessment' !== $post->post_type) {
            return new \WP_Error(
                'not_found',
                __('Assessment not found.', 'piper-privacy'),
                ['status' => 404]
            );
        }

        $deleted = wp_delete_post($id, true);

        if (!$deleted) {
            return new \WP_Error(
                'delete_failed',
                __('Failed to delete assessment.', 'piper-privacy'),
                ['status' => 500]
            );
        }

        return true;
    }

    /**
     * Save assessment meta data
     *
     * @param int   $post_id Assessment post ID
     * @param array $data    Assessment data
     */
    private function save_assessment_meta($post_id, $data) {
        $meta_fields = [
            'processing_activities',
            'risk_assessment',
            'mitigation_measures',
            'dpo_recommendation',
            'review_date',
            'status',
        ];

        foreach ($meta_fields as $field) {
            if (isset($data[$field])) {
                update_post_meta($post_id, '_pp_' . $field, $data[$field]);
            }
        }

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
     * Format assessment data
     *
     * @param \WP_Post $post Post object
     * @return array
     */
    private function format_assessment($post) {
        $meta_fields = [
            'processing_activities',
            'risk_assessment',
            'mitigation_measures',
            'dpo_recommendation',
            'review_date',
            'status',
        ];

        $assessment = [
            'id'            => $post->ID,
            'title'         => $post->post_title,
            'author'        => get_the_author_meta('display_name', $post->post_author),
            'created_at'    => $post->post_date_gmt,
            'updated_at'    => $post->post_modified_gmt,
        ];

        foreach ($meta_fields as $field) {
            $assessment[$field] = get_post_meta($post->ID, '_pp_' . $field, true);
        }

        $assessment['revisions'] = get_post_meta($post->ID, '_pp_revisions', true) ?: [];

        return $assessment;
    }
}
