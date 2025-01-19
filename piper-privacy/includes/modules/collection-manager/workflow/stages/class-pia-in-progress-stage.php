<?php
namespace PiperPrivacy\Modules\CollectionManager\Workflow\Stages;

/**
 * PIA In Progress Stage Handler
 */
class PIAInProgressStage extends BaseStage {
    /**
     * Get stage ID
     */
    protected function get_stage_id() {
        return 'pia_in_progress';
    }

    /**
     * Get stage configuration
     */
    protected function get_config() {
        return [
            'title' => __('PIA In Progress', 'piper-privacy'),
            'description' => __('Privacy Impact Assessment being conducted', 'piper-privacy'),
            'color' => '#fd7e14',
            'requirements' => [
                'pia_reference' => [
                    'field' => 'current_pia',
                    'label' => __('Current PIA', 'piper-privacy'),
                    'required' => true,
                    'validation' => [$this, 'validate_pia_reference']
                ],
                'data_flows' => [
                    'field' => 'data_flows',
                    'label' => __('Data Flow Documentation', 'piper-privacy'),
                    'required' => true
                ],
                'privacy_risks' => [
                    'field' => 'privacy_risks',
                    'label' => __('Privacy Risks', 'piper-privacy'),
                    'required' => true
                ],
                'mitigation_measures' => [
                    'field' => 'mitigation_measures',
                    'label' => __('Mitigation Measures', 'piper-privacy'),
                    'required' => true
                ],
                'access_controls' => [
                    'field' => 'access_controls',
                    'label' => __('Access Controls', 'piper-privacy'),
                    'required' => true
                ],
                'data_retention' => [
                    'field' => 'data_retention',
                    'label' => __('Data Retention Plans', 'piper-privacy'),
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
        $this->log_action($post_id, 'pia_analysis_started');

        // Initialize progress tracking
        $this->initialize_progress_tracking($post_id);

        // Set up stakeholder assignments
        $this->setup_stakeholder_assignments($post_id);

        // Create document drafts
        $this->create_document_drafts($post_id);

        // Send notifications
        $this->send_stage_notifications($post_id);

        // Update status
        $this->update_status($post_id, 'analysis_active');
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

        // Update PIA status
        $pia_id = get_post_meta($post_id, 'current_pia', true);
        update_post_meta($pia_id, 'pia_status', 'ready_for_review');

        // Generate final documents
        $this->generate_final_documents($post_id);

        // Update status
        $this->update_status($post_id, 'completed');

        // Log completion
        $this->log_action($post_id, 'pia_analysis_completed', [
            'pia_id' => $pia_id
        ]);

        // Trigger next stage
        do_action('piper_privacy_workflow_advance', $post_id, 'pia_review');
    }

    /**
     * Initialize progress tracking
     */
    protected function initialize_progress_tracking($post_id) {
        $sections = [
            'system_analysis' => [
                'title' => __('System Analysis', 'piper-privacy'),
                'status' => 'pending',
                'required_fields' => ['system_description', 'data_flows']
            ],
            'privacy_analysis' => [
                'title' => __('Privacy Analysis', 'piper-privacy'),
                'status' => 'pending',
                'required_fields' => ['privacy_risks', 'mitigation_measures']
            ],
            'controls_assessment' => [
                'title' => __('Controls Assessment', 'piper-privacy'),
                'status' => 'pending',
                'required_fields' => ['access_controls', 'security_measures']
            ],
            'data_management' => [
                'title' => __('Data Management', 'piper-privacy'),
                'status' => 'pending',
                'required_fields' => ['data_retention', 'data_disposal']
            ]
        ];

        update_post_meta($post_id, '_pia_progress', [
            'sections' => $sections,
            'started_at' => current_time('mysql'),
            'last_update' => current_time('mysql'),
            'completion_percentage' => 0
        ]);
    }

    /**
     * Setup stakeholder assignments
     */
    protected function setup_stakeholder_assignments($post_id) {
        $stakeholders = get_post_meta($post_id, 'pia_stakeholders', true);
        $assignments = [];

        foreach ($stakeholders as $stakeholder_id) {
            $assignments[$stakeholder_id] = [
                'assigned_sections' => $this->get_stakeholder_sections($stakeholder_id),
                'status' => 'pending',
                'last_activity' => null,
                'comments' => []
            ];
        }

        update_post_meta($post_id, '_pia_assignments', $assignments);
    }

    /**
     * Get stakeholder sections based on role
     */
    protected function get_stakeholder_sections($user_id) {
        $sections = [];
        
        if (user_can($user_id, 'privacy_officer')) {
            $sections = ['privacy_analysis', 'controls_assessment'];
        } elseif (user_can($user_id, 'system_owner')) {
            $sections = ['system_analysis', 'data_management'];
        } elseif (user_can($user_id, 'information_owner')) {
            $sections = ['data_management'];
        }

        return apply_filters('piper_privacy_pia_stakeholder_sections', $sections, $user_id);
    }

    /**
     * Create document drafts
     */
    protected function create_document_drafts($post_id) {
        $pia_id = get_post_meta($post_id, 'current_pia', true);
        
        // Create system analysis document
        wp_insert_post([
            'post_type' => 'privacy-document',
            'post_status' => 'draft',
            'post_title' => sprintf(
                __('System Analysis: %s', 'piper-privacy'),
                get_the_title($post_id)
            ),
            'meta_input' => [
                'pia_reference' => $pia_id,
                'document_type' => 'system_analysis',
                'status' => 'draft'
            ]
        ]);

        // Create privacy analysis document
        wp_insert_post([
            'post_type' => 'privacy-document',
            'post_status' => 'draft',
            'post_title' => sprintf(
                __('Privacy Analysis: %s', 'piper-privacy'),
                get_the_title($post_id)
            ),
            'meta_input' => [
                'pia_reference' => $pia_id,
                'document_type' => 'privacy_analysis',
                'status' => 'draft'
            ]
        ]);
    }

    /**
     * Generate final documents
     */
    protected function generate_final_documents($post_id) {
        $pia_id = get_post_meta($post_id, 'current_pia', true);
        
        // Generate final PIA report
        wp_insert_post([
            'post_type' => 'privacy-document',
            'post_status' => 'publish',
            'post_title' => sprintf(
                __('PIA Report: %s', 'piper-privacy'),
                get_the_title($post_id)
            ),
            'meta_input' => [
                'pia_reference' => $pia_id,
                'document_type' => 'pia_report',
                'status' => 'final',
                'generated_date' => current_time('mysql')
            ]
        ]);
    }

    /**
     * Send stage notifications
     */
    protected function send_stage_notifications($post_id) {
        $assignments = get_post_meta($post_id, '_pia_assignments', true);
        
        foreach ($assignments as $user_id => $assignment) {
            $this->send_notification($post_id, 'pia_section_assignment', [
                'user_id' => $user_id,
                'sections' => $assignment['assigned_sections']
            ]);
        }

        // Notify privacy officer
        $this->send_notification($post_id, 'pia_analysis_started_officer');

        // Notify system owner
        $this->send_notification($post_id, 'pia_analysis_started_owner');
    }

    /**
     * Validate PIA reference
     */
    protected function validate_pia_reference($pia_id) {
        $pia = get_post($pia_id);
        return $pia && $pia->post_type === 'privacy-impact';
    }

    /**
     * Handle stage error
     */
    protected function handle_error($error, $post_id) {
        // Log error
        $this->log_action($post_id, 'pia_analysis_error', [
            'error' => $error->get_error_message()
        ]);

        // Send error notifications
        $this->send_notification($post_id, 'pia_analysis_error', [
            'error' => $error->get_error_message()
        ]);
    }
}