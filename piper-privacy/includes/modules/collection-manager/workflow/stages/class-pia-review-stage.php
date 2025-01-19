<?php
namespace PiperPrivacy\Modules\CollectionManager\Workflow\Stages;

/**
 * PIA Review Stage Handler
 */
class PIAReviewStage extends BaseStage {
    /**
     * Review statuses
     */
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_MORE_INFO = 'more_info';
    const STATUS_CONDITIONAL = 'conditional';

    /**
     * Get stage ID
     */
    protected function get_stage_id() {
        return 'pia_review';
    }

    /**
     * Get stage configuration
     */
    protected function get_config() {
        return [
            'title' => __('PIA Review', 'piper-privacy'),
            'description' => __('Privacy Impact Assessment under review', 'piper-privacy'),
            'color' => '#20c997',
            'requirements' => [
                'pia_reference' => [
                    'field' => 'current_pia',
                    'label' => __('Current PIA', 'piper-privacy'),
                    'required' => true,
                    'validation' => [$this, 'validate_pia_reference']
                ],
                'data_assessment' => [
                    'field' => 'data_assessment_complete',
                    'label' => __('Data Assessment', 'piper-privacy'),
                    'required' => true
                ],
                'privacy_controls' => [
                    'field' => 'privacy_controls_verified',
                    'label' => __('Privacy Controls', 'piper-privacy'),
                    'required' => true
                ],
                'legal_review' => [
                    'field' => 'legal_review_complete',
                    'label' => __('Legal Review', 'piper-privacy'),
                    'required' => true
                ]
            ]
        ];
    }

    /**
     * Process stage
     */
    public function process_stage($post_id) {
        // Log review start
        $this->log_action($post_id, 'pia_review_started');

        // Initialize review data
        $this->initialize_review_data($post_id);

        // Assign reviewers
        $this->assign_reviewers($post_id);

        // Generate review documents
        $this->generate_review_documents($post_id);

        // Send notifications
        $this->send_review_notifications($post_id);

        // Update status
        $this->update_status($post_id, self::STATUS_PENDING);
    }

    /**
     * Complete stage
     */
    public function complete_stage($post_id) {
        // Validate review completion
        $validation = $this->validate_stage(true, $post_id);
        if (is_wp_error($validation)) {
            $this->handle_error($validation, $post_id);
            return;
        }

        // Process review decision
        $decision = $this->get_review_decision($post_id);
        
        switch ($decision['status']) {
            case self::STATUS_APPROVED:
                $this->process_approval($post_id, $decision);
                break;

            case self::STATUS_REJECTED:
                $this->process_rejection($post_id, $decision);
                break;

            case self::STATUS_MORE_INFO:
                $this->request_more_information($post_id, $decision);
                break;

            case self::STATUS_CONDITIONAL:
                $this->process_conditional_approval($post_id, $decision);
                break;

            default:
                $this->handle_error(
                    new \WP_Error(
                        'invalid_decision',
                        __('Invalid review decision status', 'piper-privacy')
                    ),
                    $post_id
                );
                return;
        }

        // Log completion
        $this->log_action($post_id, 'pia_review_completed', [
            'decision' => $decision
        ]);
    }

    /**
     * Initialize review data
     */
    private function initialize_review_data($post_id) {
        $review_data = [
            'started_at' => current_time('mysql'),
            'reviewers' => [],
            'reviews' => [],
            'status' => self::STATUS_PENDING,
            'decision' => null,
            'conditions' => [],
            'comments' => []
        ];

        update_post_meta($post_id, '_pia_review_data', $review_data);
    }

    /**
     * Assign reviewers
     */
    private function assign_reviewers($post_id) {
        $reviewers = [
            'privacy_officer' => $this->get_privacy_officer(),
            'legal_officer' => $this->get_legal_officer(),
            'system_owner' => get_post_meta($post_id, 'system_owner', true)
        ];

        $review_data = get_post_meta($post_id, '_pia_review_data', true);
        $review_data['reviewers'] = array_filter($reviewers);
        
        update_post_meta($post_id, '_pia_review_data', $review_data);
    }

    /**
     * Generate review documents
     */
    private function generate_review_documents($post_id) {
        $pia_id = get_post_meta($post_id, 'current_pia', true);

        // Generate review package
        wp_insert_post([
            'post_type' => 'privacy-document',
            'post_status' => 'private',
            'post_title' => sprintf(
                __('PIA Review Package: %s', 'piper-privacy'),
                get_the_title($post_id)
            ),
            'meta_input' => [
                'pia_reference' => $pia_id,
                'document_type' => 'pia_review_package',
                'status' => 'active'
            ]
        ]);
    }

    /**
     * Send review notifications
     */
    private function send_review_notifications($post_id) {
        $review_data = get_post_meta($post_id, '_pia_review_data', true);
        
        foreach ($review_data['reviewers'] as $role => $user_id) {
            $this->send_notification($post_id, 'pia_review_assigned', [
                'user_id' => $user_id,
                'role' => $role
            ]);
        }
    }

    /**
     * Get review decision
     */
    private function get_review_decision($post_id) {
        $review_data = get_post_meta($post_id, '_pia_review_data', true);
        return $review_data['decision'];
    }

    /**
     * Process approval
     */
    private function process_approval($post_id, $decision) {
        $pia_id = get_post_meta($post_id, 'current_pia', true);

        // Update PIA status
        update_post_meta($pia_id, 'pia_status', 'approved');
        update_post_meta($pia_id, 'approval_date', current_time('mysql'));
        update_post_meta($pia_id, 'approver_id', get_current_user_id());

        // Generate final approval document
        $this->generate_approval_document($post_id, $decision);

        // Update status and advance workflow
        $this->update_status($post_id, self::STATUS_APPROVED);
        do_action('piper_privacy_workflow_advance', $post_id, 'implementation');
    }

    /**
     * Process rejection
     */
    private function process_rejection($post_id, $decision) {
        $pia_id = get_post_meta($post_id, 'current_pia', true);

        // Update PIA status
        update_post_meta($pia_id, 'pia_status', 'rejected');
        update_post_meta($pia_id, 'rejection_date', current_time('mysql'));
        update_post_meta($pia_id, 'rejection_reason', $decision['reason']);

        // Update status and return to PIA
        $this->update_status($post_id, self::STATUS_REJECTED);
        do_action('piper_privacy_workflow_advance', $post_id, 'pia_in_progress');
    }

    /**
     * Request more information
     */
    private function request_more_information($post_id, $decision) {
        $pia_id = get_post_meta($post_id, 'current_pia', true);

        // Update PIA status
        update_post_meta($pia_id, 'pia_status', 'info_requested');
        update_post_meta($pia_id, 'info_requested', $decision['requested_info']);

        // Update status and return to PIA
        $this->update_status($post_id, self::STATUS_MORE_INFO);
        do_action('piper_privacy_workflow_advance', $post_id, 'pia_in_progress');
    }

    /**
     * Process conditional approval
     */
    private function process_conditional_approval($post_id, $decision) {
        $pia_id = get_post_meta($post_id, 'current_pia', true);

        // Update PIA status
        update_post_meta($pia_id, 'pia_status', 'conditionally_approved');
        update_post_meta($pia_id, 'approval_conditions', $decision['conditions']);
        update_post_meta($pia_id, 'condition_deadline', $decision['condition_deadline']);

        // Generate conditional approval document
        $this->generate_conditional_approval_document($post_id, $decision);

        // Update status and advance workflow with conditions
        $this->update_status($post_id, self::STATUS_CONDITIONAL);
        do_action('piper_privacy_workflow_advance', $post_id, 'implementation', [
            'conditions' => $decision['conditions']
        ]);
    }

    /**
     * Generate approval document
     */
    private function generate_approval_document($post_id, $decision) {
        wp_insert_post([
            'post_type' => 'privacy-document',
            'post_status' => 'publish',
            'post_title' => sprintf(
                __('PIA Approval: %s', 'piper-privacy'),
                get_the_title($post_id)
            ),
            'meta_input' => [
                'pia_reference' => get_post_meta($post_id, 'current_pia', true),
                'document_type' => 'pia_approval',
                'approval_date' => current_time('mysql'),
                'approver_id' => get_current_user_id(),
                'approval_notes' => $decision['notes']
            ]
        ]);
    }

    /**
     * Generate conditional approval document
     */
    private function generate_conditional_approval_document($post_id, $decision) {
        wp_insert_post([
            'post_type' => 'privacy-document',
            'post_status' => 'publish',
            'post_title' => sprintf(
                __('PIA Conditional Approval: %s', 'piper-privacy'),
                get_the_title($post_id)
            ),
            'meta_input' => [
                'pia_reference' => get_post_meta($post_id, 'current_pia', true),
                'document_type' => 'pia_conditional_approval',
                'approval_date' => current_time('mysql'),
                'approver_id' => get_current_user_id(),
                'conditions' => $decision['conditions'],
                'condition_deadline' => $decision['condition_deadline'],
                'approval_notes' => $decision['notes']
            ]
        ]);
    }

    /**
     * Get privacy officer
     */
    private function get_privacy_officer() {
        return get_option('privacy_officer_user_id');
    }

    /**
     * Get legal officer
     */
    private function get_legal_officer() {
        return get_option('legal_officer_user_id');
    }

    /**
     * Validate PIA reference
     */
    private function validate_pia_reference($pia_id) {
        $pia = get_post($pia_id);
        return $pia && $pia->post_type === 'privacy-impact';
    }

    /**
     * Handle stage error
     */
    protected function handle_error($error, $post_id) {
        // Log error
        $this->log_action($post_id, 'pia_review_error', [
            'error' => $error->get_error_message()
        ]);

        // Send error notifications
        $this->send_notification($post_id, 'pia_review_error', [
            'error' => $error->get_error_message()
        ]);
    }
}