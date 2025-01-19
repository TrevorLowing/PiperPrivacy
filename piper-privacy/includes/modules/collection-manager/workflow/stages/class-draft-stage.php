<?php
namespace PiperPrivacy\Modules\CollectionManager\Workflow\Stages;

/**
 * Draft Stage Handler
 */
class DraftStage extends BaseStage {
    /**
     * Get stage ID
     */
    protected function get_stage_id() {
        return 'draft';
    }

    /**
     * Get stage configuration
     */
    protected function get_config() {
        return [
            'title' => __('Draft', 'piper-privacy'),
            'description' => __('Initial collection documentation', 'piper-privacy'),
            'color' => '#6c757d',
            'requirements' => [
                'purpose_statement' => [
                    'field' => 'purpose_statement',
                    'label' => __('Purpose Statement', 'piper-privacy'),
                    'required' => true
                ],
                'legal_authority' => [
                    'field' => 'legal_authority',
                    'label' => __('Legal Authority', 'piper-privacy'),
                    'required' => true
                ],
                'system_name' => [
                    'field' => 'system_name',
                    'label' => __('System Name', 'piper-privacy'),
                    'required' => true
                ],
                'data_elements' => [
                    'field' => 'data_elements',
                    'label' => __('Data Elements', 'piper-privacy'),
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
        $this->log_action($post_id, 'draft_started');

        // Set initial status
        $this->update_status($post_id, 'in_progress');

        // Send notifications
        $this->send_notification($post_id, 'draft_started');
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
        $this->log_action($post_id, 'draft_completed');

        // Trigger next stage
        do_action('piper_privacy_workflow_advance', $post_id, 'pta_required');
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
        $purpose_statement = get_post_meta($post_id, 'purpose_statement', true);
        if (strlen($purpose_statement) < 50) {
            return new \WP_Error(
                'invalid_purpose_statement',
                __('Purpose statement must be at least 50 characters long', 'piper-privacy')
            );
        }

        return true;
    }

    /**
     * Handle stage error
     */
    protected function handle_error($error, $post_id) {
        // Log error
        $this->log_action($post_id, 'draft_error', [
            'error' => $error->get_error_message()
        ]);

        // Send error notification
        $this->send_notification($post_id, 'draft_error', [
            'error' => $error->get_error_message()
        ]);
    }

    /**
     * Get stage requirements
     */
    protected function get_requirements() {
        return $this->config['requirements'];
    }
}