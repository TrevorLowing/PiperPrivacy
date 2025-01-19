<?php
namespace PiperPrivacy\Modules\CollectionManager\Workflow\Stages;

/**
 * PIA Required Stage Handler
 */
class PIARequiredStage extends BaseStage {
    /**
     * Get stage ID
     */
    protected function get_stage_id() {
        return 'pia_required';
    }

    /**
     * Get stage configuration
     */
    protected function get_config() {
        return [
            'title' => __('PIA Required', 'piper-privacy'),
            'description' => __('Privacy Impact Assessment required', 'piper-privacy'),
            'color' => '#dc3545',
            'requirements' => [
                'pta_reference' => [
                    'field' => 'current_pta',
                    'label' => __('Current PTA', 'piper-privacy'),
                    'required' => true,
                    'validation' => [$this, 'validate_pta_reference']
                ],
                'assigned_analyst' => [
                    'field' => 'pia_analyst',
                    'label' => __('Assigned Privacy Analyst', 'piper-privacy'),
                    'required' => true
                ],
                'stakeholders' => [
                    'field' => 'pia_stakeholders',
                    'label' => __('Key Stakeholders', 'piper-privacy'),
                    'required' => true
                ],
                'due_date' => [
                    'field' => 'pia_due_date',
                    'label' => __('PIA Due Date', 'piper-privacy'),
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
        $this->log_action($post_id, 'pia_required_started');

        // Set initial assignments
        $this->set_initial_assignments($post_id);

        // Create PIA draft
        $pia_id = $this->create_pia_draft($post_id);
        if (is_wp_error($pia_id)) {
            $this->handle_error($pia_id, $post_id);
            return;
        }

        // Send notifications
        $this->send_stage_notifications($post_id, $pia_id);

        // Update status
        $this->update_status($post_id, 'pending_pia');
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

        // Update status
        $this->update_status($post_id, 'completed');

        // Log completion
        $this->log_action($post_id, 'pia_required_completed', [
            'pia_id' => get_post_meta($post_id, 'current_pia', true)
        ]);

        // Trigger next stage
        do_action('piper_privacy_workflow_advance', $post_id, 'pia_in_progress');
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

        // Validate PTA status
        $pta_id = get_post_meta($post_id, 'current_pta', true);
        $pta_status = get_post_meta($pta_id, 'pta_status', true);
        if ($pta_status !== 'approved') {
            return new \WP_Error(
                'invalid_pta_status',
                __('PTA must be approved before starting PIA', 'piper-privacy')
            );
        }

        // Validate risk level
        $risk_level = get_post_meta($pta_id, 'risk_level', true);
        if ($risk_level !== 'high' && !apply_filters('piper_privacy_medium_risk_pia_required', false)) {
            return new \WP_Error(
                'invalid_risk_level',
                __('Risk level does not require PIA', 'piper-privacy')
            );
        }

        return true;
    }

    /**
     * Set initial assignments
     */
    protected function set_initial_assignments($post_id) {
        // Set default due date (30 days from now)
        $due_date = date('Y-m-d', strtotime('+30 days'));
        update_post_meta($post_id, 'pia_due_date', $due_date);

        // Auto-assign privacy analyst if configured
        $default_analyst = $this->get_default_analyst();
        if ($default_analyst) {
            update_post_meta($post_id, 'pia_analyst', $default_analyst);
        }

        // Set default stakeholders
        $this->set_default_stakeholders($post_id);
    }

    /**
     * Create PIA draft
     */
    protected function create_pia_draft($post_id) {
        $pta_id = get_post_meta($post_id, 'current_pta', true);

        $pia_id = wp_insert_post([
            'post_type' => 'privacy-impact',
            'post_status' => 'draft',
            'post_title' => sprintf(
                __('PIA: %s', 'piper-privacy'),
                get_the_title($post_id)
            ),
            'meta_input' => [
                'collection_reference' => $post_id,
                'pta_reference' => $pta_id,
                'pia_status' => 'draft',
                'created_date' => current_time('mysql')
            ]
        ]);

        if (!is_wp_error($pia_id)) {
            update_post_meta($post_id, 'current_pia', $pia_id);
        }

        return $pia_id;
    }

    /**
     * Send stage notifications
     */
    protected function send_stage_notifications($post_id, $pia_id) {
        // Notify assigned analyst
        $analyst_id = get_post_meta($post_id, 'pia_analyst', true);
        if ($analyst_id) {
            $this->send_notification($post_id, 'pia_assigned', [
                'analyst_id' => $analyst_id,
                'pia_id' => $pia_id
            ]);
        }

        // Notify stakeholders
        $stakeholders = get_post_meta($post_id, 'pia_stakeholders', true);
        foreach ($stakeholders as $stakeholder_id) {
            $this->send_notification($post_id, 'pia_stakeholder_notification', [
                'stakeholder_id' => $stakeholder_id,
                'pia_id' => $pia_id
            ]);
        }

        // Notify privacy officer
        $this->send_notification($post_id, 'pia_required_privacy_officer', [
            'pia_id' => $pia_id
        ]);
    }

    /**
     * Set default stakeholders
     */
    protected function set_default_stakeholders($post_id) {
        $stakeholders = [
            get_post_meta($post_id, 'system_owner', true),
            get_post_meta($post_id, 'privacy_officer', true),
            get_post_meta($post_id, 'information_owner', true)
        ];

        $stakeholders = array_filter($stakeholders);
        update_post_meta($post_id, 'pia_stakeholders', $stakeholders);
    }

    /**
     * Get default analyst
     */
    protected function get_default_analyst() {
        return apply_filters('piper_privacy_default_pia_analyst', 0);
    }

    /**
     * Validate PTA reference
     */
    protected function validate_pta_reference($pta_id) {
        $pta = get_post($pta_id);
        return $pta && $pta->post_type === 'privacy-threshold';
    }
}