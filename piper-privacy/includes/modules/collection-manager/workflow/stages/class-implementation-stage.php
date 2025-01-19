<?php
namespace PiperPrivacy\Modules\CollectionManager\Workflow\Stages;

/**
 * Implementation Stage Handler
 */
class ImplementationStage extends BaseStage {
    /**
     * Implementation statuses
     */
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_BLOCKED = 'blocked';

    /**
     * Get stage ID
     */
    protected function get_stage_id() {
        return 'implementation';
    }

    /**
     * Get stage configuration
     */
    protected function get_config() {
        return [
            'title' => __('Implementation', 'piper-privacy'),
            'description' => __('Privacy controls being implemented', 'piper-privacy'),
            'color' => '#0dcaf0',
            'requirements' => [
                'implementation_plan' => [
                    'field' => 'implementation_plan',
                    'label' => __('Implementation Plan', 'piper-privacy'),
                    'required' => true
                ],
                'control_list' => [
                    'field' => 'privacy_controls_list',
                    'label' => __('Privacy Controls', 'piper-privacy'),
                    'required' => true
                ],
                'testing_plan' => [
                    'field' => 'testing_plan',
                    'label' => __('Testing Plan', 'piper-privacy'),
                    'required' => true
                ],
                'stakeholder_signoff' => [
                    'field' => 'stakeholder_signoff',
                    'label' => __('Stakeholder Sign-off', 'piper-privacy'),
                    'required' => true
                ]
            ]
        ];
    }

    /**
     * Process stage
     */
    public function process_stage($post_id) {
        // Log implementation start
        $this->log_action($post_id, 'implementation_started');

        // Initialize implementation tracking
        $this->initialize_implementation_tracking($post_id);

        // Generate implementation documents
        $this->generate_implementation_documents($post_id);

        // Set up control tracking
        $this->setup_control_tracking($post_id);

        // Send notifications
        $this->send_stage_notifications($post_id);

        // Update status
        $this->update_status($post_id, self::STATUS_PENDING);
    }

    /**
     * Complete stage
     */
    public function complete_stage($post_id) {
        // Validate implementation completion
        $validation = $this->validate_stage(true, $post_id);
        if (is_wp_error($validation)) {
            $this->handle_error($validation, $post_id);
            return;
        }

        // Generate completion documentation
        $this->generate_completion_documents($post_id);

        // Update collection status
        update_post_meta($post_id, 'collection_status', 'active');

        // Schedule initial review
        $this->schedule_initial_review($post_id);

        // Update status
        $this->update_status($post_id, self::STATUS_COMPLETED);

        // Log completion
        $this->log_action($post_id, 'implementation_completed');

        // Trigger next stage
        do_action('piper_privacy_workflow_advance', $post_id, 'active');
    }

    /**
     * Initialize implementation tracking
     */
    private function initialize_implementation_tracking($post_id) {
        $tracking_data = [
            'started_at' => current_time('mysql'),
            'controls' => [],
            'milestones' => [],
            'testing_results' => [],
            'status' => self::STATUS_PENDING,
            'completion_percentage' => 0,
            'last_update' => current_time('mysql')
        ];

        update_post_meta($post_id, '_implementation_tracking', $tracking_data);
    }

    /**
     * Generate implementation documents
     */
    private function generate_implementation_documents($post_id) {
        // Create implementation plan document
        wp_insert_post([
            'post_type' => 'privacy-document',
            'post_status' => 'private',
            'post_title' => sprintf(
                __('Implementation Plan: %s', 'piper-privacy'),
                get_the_title($post_id)
            ),
            'meta_input' => [
                'collection_reference' => $post_id,
                'document_type' => 'implementation_plan',
                'status' => 'draft'
            ]
        ]);

        // Create testing plan document
        wp_insert_post([
            'post_type' => 'privacy-document',
            'post_status' => 'private',
            'post_title' => sprintf(
                __('Testing Plan: %s', 'piper-privacy'),
                get_the_title($post_id)
            ),
            'meta_input' => [
                'collection_reference' => $post_id,
                'document_type' => 'testing_plan',
                'status' => 'draft'
            ]
        ]);
    }

    /**
     * Setup control tracking
     */
    private function setup_control_tracking($post_id) {
        $controls = get_post_meta($post_id, 'privacy_controls_list', true);
        $tracking = [];

        foreach ($controls as $control) {
            $tracking[$control['id']] = [
                'status' => 'pending',
                'implementation_date' => null,
                'testing_date' => null,
                'verification_date' => null,
                'notes' => []
            ];
        }

        update_post_meta($post_id, '_control_tracking', $tracking);
    }

    /**
     * Generate completion documents
     */
    private function generate_completion_documents($post_id) {
        // Create implementation report
        wp_insert_post([
            'post_type' => 'privacy-document',
            'post_status' => 'publish',
            'post_title' => sprintf(
                __('Implementation Report: %s', 'piper-privacy'),
                get_the_title($post_id)
            ),
            'meta_input' => [
                'collection_reference' => $post_id,
                'document_type' => 'implementation_report',
                'completion_date' => current_time('mysql'),
                'status' => 'final'
            ]
        ]);

        // Create testing report
        wp_insert_post([
            'post_type' => 'privacy-document',
            'post_status' => 'publish',
            'post_title' => sprintf(
                __('Testing Report: %s', 'piper-privacy'),
                get_the_title($post_id)
            ),
            'meta_input' => [
                'collection_reference' => $post_id,
                'document_type' => 'testing_report',
                'completion_date' => current_time('mysql'),
                'status' => 'final'
            ]
        ]);
    }

    /**
     * Schedule initial review
     */
    private function schedule_initial_review($post_id) {
        $review_date = strtotime('+6 months');
        update_post_meta($post_id, 'next_review_date', date('Y-m-d', $review_date));
        wp_schedule_single_event($review_date, 'piper_privacy_collection_review_due', [$post_id]);
    }

    /**
     * Send stage notifications
     */
    private function send_stage_notifications($post_id) {
        // Notify system owner
        $this->send_notification($post_id, 'implementation_started_owner');

        // Notify privacy officer
        $this->send_notification($post_id, 'implementation_started_officer');

        // Notify technical team
        $this->send_notification($post_id, 'implementation_started_technical');
    }

    /**
     * Handle stage error
     */
    protected function handle_error($error, $post_id) {
        // Log error
        $this->log_action($post_id, 'implementation_error', [
            'error' => $error->get_error_message()
        ]);

        // Send error notifications
        $this->send_notification($post_id, 'implementation_error', [
            'error' => $error->get_error_message()
        ]);

        // Update status
        $this->update_status($post_id, self::STATUS_BLOCKED);
    }
}