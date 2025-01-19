<?php
namespace PiperPrivacy\Modules\CollectionManager\Workflow\Stages;

/**
 * PTA In Progress Stage Handler
 */
class PTAInProgressStage extends BaseStage {
    /**
     * Get stage ID
     */
    protected function get_stage_id() {
        return 'pta_in_progress';
    }

    /**
     * Get stage configuration
     */
    protected function get_config() {
        return [
            'title' => __('PTA In Progress', 'piper-privacy'),
            'description' => __('Privacy Threshold Analysis being conducted', 'piper-privacy'),
            'color' => '#17a2b8',
            'requirements' => [
                'pta_reference' => [
                    'field' => 'current_pta',
                    'label' => __('Current PTA', 'piper-privacy'),
                    'required' => true,
                    'validation' => [$this, 'validate_pta_reference']
                ],
                'contains_pii' => [
                    'field' => 'contains_pii',
                    'label' => __('PII Assessment', 'piper-privacy'),
                    'required' => true
                ],
                'risk_level' => [
                    'field' => 'risk_level',
                    'label' => __('Risk Level', 'piper-privacy'),
                    'required' => true
                ],
                'privacy_controls' => [
                    'field' => 'privacy_controls',
                    'label' => __('Privacy Controls', 'piper-privacy'),
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
        $this->log_action($post_id, 'pta_analysis_started');

        // Check for existing PTA
        $pta_id = get_post_meta($post_id, 'current_pta', true);
        if (!$pta_id) {
            $this->handle_error(
                new \WP_Error('missing_pta', __('No PTA found for this collection', 'piper-privacy')),
                $post_id
            );
            return;
        }

        // Set analysis status
        $this->update_pta_status($pta_id, 'in_progress');
        
        // Track progress metrics
        $this->initialize_progress_tracking($post_id);

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

        $pta_id = get_post_meta($post_id, 'current_pta', true);

        // Determine next stage based on risk level
        $risk_level = get_post_meta($pta_id, 'risk_level', true);
        $requires_pia = $this->check_pia_requirement($risk_level);

        // Update PTA status
        $this->update_pta_status($pta_id, 'completed');

        // Update collection status
        $this->update_status($post_id, 'completed');

        // Log completion with risk assessment
        $this->log_action($post_id, 'pta_analysis_completed', [
            'pta_id' => $pta_id,
            'risk_level' => $risk_level,
            'requires_pia' => $requires_pia
        ]);

        // Trigger appropriate next stage
        $next_stage = $requires_pia ? 'pia_required' : 'implementation';
        do_action('piper_privacy_workflow_advance', $post_id, $next_stage);
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

        // Get PTA reference
        $pta_id = get_post_meta($post_id, 'current_pta', true);
        
        // Validate PII assessment if applicable
        if (get_post_meta($pta_id, 'contains_pii', true) === 'yes') {
            $pii_categories = get_post_meta($pta_id, 'pii_categories', true);
            if (empty($pii_categories)) {
                return new \WP_Error(
                    'missing_pii_categories',
                    __('PII categories must be specified when PII is present', 'piper-privacy')
                );
            }
        }

        // Validate risk assessment
        $risk_factors = get_post_meta($pta_id, 'risk_factors', true);
        if (empty($risk_factors)) {
            return new \WP_Error(
                'missing_risk_factors',
                __('Risk factors must be documented', 'piper-privacy')
            );
        }

        return true;
    }

    /**
     * Validate PTA reference
     */
    protected function validate_pta_reference($pta_id) {
        $pta = get_post($pta_id);
        return $pta && $pta->post_type === 'privacy-threshold';
    }

    /**
     * Initialize progress tracking
     */
    protected function initialize_progress_tracking($post_id) {
        $tracking_data = [
            'started_at' => current_time('mysql'),
            'sections_completed' => [],
            'last_update' => current_time('mysql'),
            'completion_percentage' => 0
        ];

        update_post_meta($post_id, '_pta_progress', $tracking_data);
    }

    /**
     * Update PTA status
     */
    protected function update_pta_status($pta_id, $status) {
        update_post_meta($pta_id, 'pta_status', $status);
        
        // Log status change
        $this->log_action($pta_id, 'pta_status_changed', [
            'new_status' => $status,
            'timestamp' => current_time('mysql')
        ]);
    }

    /**
     * Send stage notifications
     */
    protected function send_stage_notifications($post_id) {
        // Notify assigned analyst
        $analyst_id = get_post_meta($post_id, 'assigned_analyst', true);
        if ($analyst_id) {
            $this->send_notification($post_id, 'pta_analysis_started', [
                'analyst_id' => $analyst_id
            ]);
        }

        // Notify privacy officer
        $this->send_notification($post_id, 'pta_analysis_started_officer');

        // Notify collection owner
        $this->send_notification($post_id, 'pta_analysis_started_owner');
    }

    /**
     * Check if PIA is required
     */
    protected function check_pia_requirement($risk_level) {
        // High risk automatically requires PIA
        if ($risk_level === 'high') {
            return true;
        }

        // Medium risk may require PIA based on other factors
        if ($risk_level === 'medium') {
            return apply_filters('piper_privacy_medium_risk_pia_required', false);
        }

        return false;
    }

    /**
     * Handle stage error
     */
    protected function handle_error($error, $post_id) {
        // Log error
        $this->log_action($post_id, 'pta_analysis_error', [
            'error' => $error->get_error_message()
        ]);

        // Send error notifications
        $this->send_notification($post_id, 'pta_analysis_error', [
            'error' => $error->get_error_message()
        ]);
    }
}