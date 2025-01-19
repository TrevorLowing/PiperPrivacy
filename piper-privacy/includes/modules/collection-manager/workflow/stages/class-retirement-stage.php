<?php
namespace PiperPrivacy\Modules\CollectionManager\Workflow\Stages;

/**
 * Retirement Stage Handler
 */
class RetirementStage extends BaseStage {
    /**
     * Retirement statuses
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PLANNING = 'planning';
    const STATUS_DATA_DISPOSAL = 'data_disposal';
    const STATUS_ARCHIVING = 'archiving';
    const STATUS_COMPLETED = 'completed';

    /**
     * Get stage ID
     */
    protected function get_stage_id() {
        return 'retirement';
    }

    /**
     * Get stage configuration
     */
    protected function get_config() {
        return [
            'title' => __('Collection Retirement', 'piper-privacy'),
            'description' => __('Privacy collection being retired', 'piper-privacy'),
            'color' => '#6c757d',
            'requirements' => [
                'retirement_plan' => [
                    'field' => 'retirement_plan',
                    'label' => __('Retirement Plan', 'piper-privacy'),
                    'required' => true
                ],
                'data_disposition' => [
                    'field' => 'data_disposition_plan',
                    'label' => __('Data Disposition Plan', 'piper-privacy'),
                    'required' => true
                ],
                'stakeholder_notification' => [
                    'field' => 'stakeholder_notification_plan',
                    'label' => __('Stakeholder Notification Plan', 'piper-privacy'),
                    'required' => true
                ],
                'archive_plan' => [
                    'field' => 'archive_plan',
                    'label' => __('Archive Plan', 'piper-privacy'),
                    'required' => true
                ]
            ]
        ];
    }

    /**
     * Process stage
     */
    public function process_stage($post_id) {
        // Log retirement start
        $this->log_action($post_id, 'retirement_started');

        // Initialize retirement tracking
        $this->initialize_retirement_tracking($post_id);

        // Generate retirement documents
        $this->generate_retirement_documents($post_id);

        // Send notifications
        $this->send_retirement_notifications($post_id);

        // Update status
        $this->update_status($post_id, self::STATUS_PENDING);
    }

    /**
     * Complete stage
     */
    public function complete_stage($post_id) {
        // Validate retirement completion
        $validation = $this->validate_stage(true, $post_id);
        if (is_wp_error($validation)) {
            $this->handle_error($validation, $post_id);
            return;
        }

        // Generate completion documentation
        $this->generate_completion_documents($post_id);

        // Update collection status
        update_post_meta($post_id, 'collection_status', 'retired');

        // Archive the collection
        $this->archive_collection($post_id);

        // Update status
        $this->update_status($post_id, self::STATUS_COMPLETED);

        // Log completion
        $this->log_action($post_id, 'retirement_completed');

        // Trigger final archive stage
        do_action('piper_privacy_workflow_advance', $post_id, 'archived');
    }

    /**
     * Initialize retirement tracking
     */
    private function initialize_retirement_tracking($post_id) {
        $tracking_data = [
            'started_at' => current_time('mysql'),
            'phases' => [
                'planning' => [
                    'status' => 'pending',
                    'completed_at' => null
                ],
                'data_disposal' => [
                    'status' => 'pending',
                    'completed_at' => null
                ],
                'archiving' => [
                    'status' => 'pending',
                    'completed_at' => null
                ]
            ],
            'completion_percentage' => 0,
            'last_update' => current_time('mysql')
        ];

        update_post_meta($post_id, '_retirement_tracking', $tracking_data);
    }

    /**
     * Generate retirement documents
     */
    private function generate_retirement_documents($post_id) {
        // Create retirement plan document
        wp_insert_post([
            'post_type' => 'privacy-document',
            'post_status' => 'private',
            'post_title' => sprintf(
                __('Retirement Plan: %s', 'piper-privacy'),
                get_the_title($post_id)
            ),
            'meta_input' => [
                'collection_reference' => $post_id,
                'document_type' => 'retirement_plan',
                'status' => 'draft'
            ]
        ]);

        // Create data disposition plan
        wp_insert_post([
            'post_type' => 'privacy-document',
            'post_status' => 'private',
            'post_title' => sprintf(
                __('Data Disposition Plan: %s', 'piper-privacy'),
                get_the_title($post_id)
            ),
            'meta_input' => [
                'collection_reference' => $post_id,
                'document_type' => 'data_disposition_plan',
                'status' => 'draft'
            ]
        ]);
    }

    /**
     * Generate completion documents
     */
    private function generate_completion_documents($post_id) {
        // Create retirement report
        wp_insert_post([
            'post_type' => 'privacy-document',
            'post_status' => 'publish',
            'post_title' => sprintf(
                __('Retirement Report: %s', 'piper-privacy'),
                get_the_title($post_id)
            ),
            'meta_input' => [
                'collection_reference' => $post_id,
                'document_type' => 'retirement_report',
                'completion_date' => current_time('mysql'),
                'status' => 'final'
            ]
        ]);

        // Create disposition certificate
        wp_insert_post([
            'post_type' => 'privacy-document',
            'post_status' => 'publish',
            'post_title' => sprintf(
                __('Data Disposition Certificate: %s', 'piper-privacy'),
                get_the_title($post_id)
            ),
            'meta_input' => [
                'collection_reference' => $post_id,
                'document_type' => 'disposition_certificate',
                'completion_date' => current_time('mysql'),
                'status' => 'final'
            ]
        ]);
    }

    /**
     * Archive collection
     */
    private function archive_collection($post_id) {
        // Update post status
        wp_update_post([
            'ID' => $post_id,
            'post_status' => 'archive'
        ]);

        // Create archive package
        wp_insert_post([
            'post_type' => 'privacy-document',
            'post_status' => 'publish',
            'post_title' => sprintf(
                __('Archive Package: %s', 'piper-privacy'),
                get_the_title($post_id)
            ),
            'meta_input' => [
                'collection_reference' => $post_id,
                'document_type' => 'archive_package',
                'archive_date' => current_time('mysql'),
                'status' => 'final',
                'retention_period' => apply_filters('piper_privacy_archive_retention_period', '+7 years')
            ]
        ]);

        // Schedule archive deletion
        $deletion_date = strtotime('+7 years');
        wp_schedule_single_event($deletion_date, 'piper_privacy_archive_deletion_due', [$post_id]);
    }

    /**
     * Send retirement notifications
     */
    private function send_retirement_notifications($post_id) {
        // Notify privacy officer
        $this->send_notification($post_id, 'retirement_started_officer');

        // Notify system owner
        $this->send_notification($post_id, 'retirement_started_owner');

        // Notify stakeholders
        $stakeholders = get_post_meta($post_id, 'collection_stakeholders', true);
        foreach ($stakeholders as $stakeholder_id) {
            $this->send_notification($post_id, 'retirement_started_stakeholder', [
                'stakeholder_id' => $stakeholder_id
            ]);
        }
    }

    /**
     * Handle stage error
     */
    protected function handle_error($error, $post_id) {
        // Log error
        $this->log_action($post_id, 'retirement_error', [
            'error' => $error->get_error_message()
        ]);

        // Send error notifications
        $this->send_notification($post_id, 'retirement_error', [
            'error' => $error->get_error_message()
        ]);
    }
}