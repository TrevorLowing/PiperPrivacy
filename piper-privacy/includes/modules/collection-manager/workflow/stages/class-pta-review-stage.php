<?php
namespace PiperPrivacy\Modules\CollectionManager\Workflow\Stages;

/**
 * PTA Review Stage Handler
 */
class PTAReviewStage extends BaseStage {
    /**
     * Review statuses
     */
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_MORE_INFO = 'more_info';

    /**
     * Get stage ID
     */
    protected function get_stage_id() {
        return 'pta_review';
    }

    /**
     * Get stage configuration
     */
    protected function get_config() {
        return [
            'title' => __('PTA Review', 'piper-privacy'),
            'description' => __('Privacy Threshold Analysis under review', 'piper-privacy'),
            'color' => '#6610f2',
            'requirements' => [
                'pta_reference' => [
                    'field' => 'current_pta',
                    'label' => __('Current PTA', 'piper-privacy'),
                    'required' => true,
                    'validation' => [$this, 'validate_pta_reference']
                ],
                'reviewers' => [
                    'field' => 'pta_reviewers',
                    'label' => __('Assigned Reviewers', 'piper-privacy'),
                    'required' => true
                ],
                'review_deadline' => [
                    'field' => 'review_deadline',
                    'label' => __('Review Deadline', 'piper-privacy'),
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
        $this->log_action($post_id, 'pta_review_started');

        // Initialize review tracking
        $this->initialize_review($post_id);

        // Send notifications to reviewers
        $this->notify_reviewers($post_id);

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

        $review_status = $this->get_review_status($post_id);

        switch ($review_status) {
            case self::STATUS_APPROVED:
                $this->process_approval($post_id);
                break;

            case self::STATUS_REJECTED:
                $this->process_rejection($post_id);
                break;

            case self::STATUS_MORE_INFO:
                $this->request_more_information($post_id);
                break;

            default:
                $this->handle_error(
                    new \WP_Error(
                        'invalid_review_status',
                        __('Invalid review status', 'piper-privacy')
                    ),
                    $post_id
                );
                return;
        }

        // Log completion
        $this->log_action($post_id, 'pta_review_completed', [
            'status' => $review_status,
            'timestamp' => current_time('mysql')
        ]);
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

        // Verify all reviewers have submitted
        if (!$this->verify_all_reviews_completed($post_id)) {
            return new \WP_Error(
                'incomplete_reviews',
                __('Not all reviewers have submitted their review', 'piper-privacy')
            );
        }

        return true;
    }

    /**
     * Initialize review tracking
     */
    protected function initialize_review($post_id) {
        $reviewers = get_post_meta($post_id, 'pta_reviewers', true);
        $review_data = [
            'started_at' => current_time('mysql'),
            'deadline' => get_post_meta($post_id, 'review_deadline', true),
            'reviewers' => array_fill_keys($reviewers, [
                'status' => 'pending',
                'completed_at' => null,
                'comments' => []
            ]),
            'status' => self::STATUS_PENDING
        ];

        update_post_meta($post_id, '_pta_review_data', $review_data);
    }

    /**
     * Notify reviewers
     */
    protected function notify_reviewers($post_id) {
        $reviewers = get_post_meta($post_id, 'pta_reviewers', true);
        foreach ($reviewers as $reviewer_id) {
            $this->send_notification($post_id, 'pta_review_assigned', [
                'reviewer_id' => $reviewer_id,
                'deadline' => get_post_meta($post_id, 'review_deadline', true)
            ]);
        }
    }

    /**
     * Process approval
     */
    protected function process_approval($post_id) {
        $pta_id = get_post_meta($post_id, 'current_pta', true);
        
        // Update PTA status
        update_post_meta($pta_id, 'pta_status', 'approved');
        update_post_meta($pta_id, 'approval_date', current_time('mysql'));
        update_post_meta($pta_id, 'approver_id', get_current_user_id());

        // Determine next stage based on risk level
        $risk_level = get_post_meta($pta_id, 'risk_level', true);
        $next_stage = $risk_level === 'high' ? 'pia_required' : 'implementation';

        // Update status and trigger next stage
        $this->update_status($post_id, self::STATUS_APPROVED);
        do_action('piper_privacy_workflow_advance', $post_id, $next_stage);
    }

    /**
     * Process rejection
     */
    protected function process_rejection($post_id) {
        $pta_id = get_post_meta($post_id, 'current_pta', true);
        
        // Update PTA status
        update_post_meta($pta_id, 'pta_status', 'rejected');
        update_post_meta($pta_id, 'rejection_date', current_time('mysql'));
        update_post_meta($pta_id, 'rejector_id', get_current_user_id());

        // Update status and return to draft
        $this->update_status($post_id, self::STATUS_REJECTED);
        do_action('piper_privacy_workflow_advance', $post_id, 'pta_in_progress');
    }

    /**
     * Request more information
     */
    protected function request_more_information($post_id) {
        $pta_id = get_post_meta($post_id, 'current_pta', true);
        
        // Update PTA status
        update_post_meta($pta_id, 'pta_status', 'info_requested');
        update_post_meta($pta_id, 'info_requested_date', current_time('mysql'));
        update_post_meta($pta_id, 'requestor_id', get_current_user_id());

        // Update status and return to analysis
        $this->update_status($post_id, self::STATUS_MORE_INFO);
        do_action('piper_privacy_workflow_advance', $post_id, 'pta_in_progress');
    }

    /**
     * Get current review status
     */
    protected function get_review_status($post_id) {
        $review_data = get_post_meta($post_id, '_pta_review_data', true);
        return $review_data['status'] ?? self::STATUS_PENDING;
    }

    /**
     * Verify all reviews are completed
     */
    protected function verify_all_reviews_completed($post_id) {
        $review_data = get_post_meta($post_id, '_pta_review_data', true);
        
        foreach ($review_data['reviewers'] as $reviewer_data) {
            if ($reviewer_data['status'] === 'pending') {
                return false;
            }
        }

        return true;
    }

    /**
     * Handle stage error
     */
    protected function handle_error($error, $post_id) {
        // Log error
        $this->log_action($post_id, 'pta_review_error', [
            'error' => $error->get_error_message()
        ]);

        // Send error notifications
        $this->send_notification($post_id, 'pta_review_error', [
            'error' => $error->get_error_message()
        ]);
    }
}