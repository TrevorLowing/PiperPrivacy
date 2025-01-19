<?php
namespace PiperPrivacy\Modules\CollectionManager\Workflow\Stages;

use WP_Error;

/**
 * Import WordPress functions
 */
use function \__;
use function \update_post_meta;
use function \get_post_meta;
use function \wp_insert_post;
use function \get_the_title;
use function \current_time;
use function \user_can;
use function \apply_filters;
use function \do_action;
use function \is_wp_error;

/**
 * PTA Required Stage Handler
 */
class PTARequiredStage extends BaseStage {
    /**
     * Get stage ID
     */
    protected function get_stage_id() {
        return 'pta_required';
    }

    /**
     * Get stage configuration
     */
    protected function get_config() {
        return [
            'title' => __('PTA Required', 'piper-privacy'),
            'description' => __('Privacy Threshold Analysis needed', 'piper-privacy'),
            'color' => '#ffc107',
            'requirements' => [
                'collection_status' => [
                    'field' => 'collection_status',
                    'label' => __('Collection Status', 'piper-privacy'),
                    'required' => true,
                    'validation' => [$this, 'validate_collection_status']
                ],
                'assigned_analyst' => [
                    'field' => 'assigned_analyst',
                    'label' => __('Assigned Privacy Analyst', 'piper-privacy'),
                    'required' => true
                ],
                'due_date' => [
                    'field' => 'pta_due_date',
                    'label' => __('PTA Due Date', 'piper-privacy'),
                    'required' => true
                ]
            ]
        ];
    }

    /**
     * Process stage
     */
    public function process_stage($post_id) {
        // Log stage entry
        $this->log_action($post_id, 'pta_required_started');

        // Set initial assignments and deadlines
        $this->set_initial_assignments($post_id);

        // Send notifications
        $this->send_stage_notifications($post_id);

        // Update status
        $this->update_status($post_id, 'pending_pta');
    }

    /**
     * Complete stage
     */
    public function complete_stage($post_id) {
        // Validate requirements
        $validation = $this->validate_stage(true, $post_id);
        if (is_wp_error($validation)) {
            $this->handle_error($validation, $post_id);
            return;
        }

        // Create PTA draft
        $pta_id = $this->create_pta_draft($post_id);
        if (is_wp_error($pta_id)) {
            $this->handle_error($pta_id, $post_id);
            return;
        }

        // Update status
        $this->update_status($post_id, 'completed');

        // Log completion
        $this->log_action($post_id, 'pta_required_completed', [
            'pta_id' => $pta_id
        ]);

        // Trigger next stage
        do_action('piper_privacy_workflow_advance', $post_id, 'pta_in_progress');
    }

    /**
     * Validate stage requirements
     */
    public function validate_stage($is_valid, $post_id) {
        // Check base requirements
        $requirements = $this->check_requirements($post_id);
        if (is_wp_error($requirements)) {
            return $requirements;
        }

        // Additional validations
        if (!$this->validate_assignments($post_id)) {
            return new \WP_Error(
                'invalid_assignments',
                __('Required assignments are incomplete', 'piper-privacy')
            );
        }

        return true;
    }

    /**
     * Set initial assignments and deadlines
     */
    protected function set_initial_assignments($post_id) {
        // Set default due date (e.g., 14 days from now)
        $due_date = date('Y-m-d', strtotime('+14 days'));
        update_post_meta($post_id, 'pta_due_date', $due_date);

        // Auto-assign privacy analyst if configured
        $default_analyst = $this->get_default_analyst();
        if ($default_analyst) {
            update_post_meta($post_id, 'assigned_analyst', $default_analyst);
        }
    }

    /**
     * Send stage notifications
     */
    protected function send_stage_notifications($post_id) {
        // Notify privacy officer
        $this->send_notification($post_id, 'pta_required_privacy_officer');

        // Notify collection owner
        $this->send_notification($post_id, 'pta_required_owner');

        // Notify assigned analyst
        $assigned_analyst = get_post_meta($post_id, 'assigned_analyst', true);
        if ($assigned_analyst) {
            $this->send_notification($post_id, 'pta_required_analyst', [
                'analyst_id' => $assigned_analyst
            ]);
        }
    }

    /**
     * Create PTA draft
     */
    protected function create_pta_draft($post_id) {
        return wp_insert_post([
            'post_type' => 'privacy-threshold',
            'post_status' => 'draft',
            'post_title' => sprintf(
                __('PTA: %s', 'piper-privacy'),
                get_the_title($post_id)
            ),
            'meta_input' => [
                'collection_reference' => $post_id,
                'pta_status' => 'draft',
                'created_date' => current_time('mysql')
            ]
        ]);
    }

    /**
     * Validate collection status
     */
    protected function validate_collection_status($value) {
        $valid_statuses = ['draft_complete', 'pending_pta'];
        return in_array($value, $valid_statuses);
    }

    /**
     * Validate assignments
     */
    protected function validate_assignments($post_id) {
        $analyst = get_post_meta($post_id, 'assigned_analyst', true);
        return !empty($analyst) && user_can($analyst, 'privacy_analyst');
    }

    /**
     * Get default analyst
     */
    protected function get_default_analyst() {
        // Implementation could involve round-robin assignment or workload balancing
        return apply_filters('piper_privacy_default_analyst', 0);
    }
}