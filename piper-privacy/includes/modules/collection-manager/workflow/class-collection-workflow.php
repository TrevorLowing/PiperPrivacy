<?php
namespace PiperPrivacy\Modules\CollectionManager\Workflow;

/**
 * Collection Workflow Handler
 */
class CollectionWorkflow extends BaseWorkflow {
    /**
     * Workflow stages
     *
     * @var array
     */
    protected $stages = [
        'draft' => [
            'title' => 'Draft',
            'color' => '#6c757d',
            'description' => 'Initial collection documentation'
        ],
        'pta_required' => [
            'title' => 'PTA Required',
            'color' => '#ffc107',
            'description' => 'Privacy Threshold Analysis needed'
        ],
        'pta_in_progress' => [
            'title' => 'PTA In Progress',
            'color' => '#17a2b8',
            'description' => 'PTA being conducted'
        ],
        'pta_review' => [
            'title' => 'PTA Review',
            'color' => '#6610f2',
            'description' => 'PTA under review'
        ],
        'pia_required' => [
            'title' => 'PIA Required',
            'color' => '#dc3545',
            'description' => 'Privacy Impact Assessment needed'
        ],
        'pia_in_progress' => [
            'title' => 'PIA In Progress',
            'color' => '#fd7e14',
            'description' => 'PIA being conducted'
        ],
        'pia_review' => [
            'title' => 'PIA Review',
            'color' => '#20c997',
            'description' => 'PIA under review'
        ],
        'implementation' => [
            'title' => 'Implementation',
            'color' => '#0dcaf0',
            'description' => 'Collection being implemented'
        ],
        'active' => [
            'title' => 'Active',
            'color' => '#198754',
            'description' => 'Collection active and monitored'
        ],
        'review' => [
            'title' => 'Under Review',
            'color' => '#0d6efd',
            'description' => 'Periodic review in progress'
        ],
        'retirement' => [
            'title' => 'Retirement',
            'color' => '#6c757d',
            'description' => 'Collection being retired'
        ],
        'archived' => [
            'title' => 'Archived',
            'color' => '#343a40',
            'description' => 'Collection archived'
        ]
    ];

    /**
     * Get workflow ID
     */
    protected function get_workflow_id() {
        return 'privacy_collection_workflow';
    }

    /**
     * Get workflow title
     */
    protected function get_workflow_title() {
        return __('Privacy Collection Workflow', 'piper-privacy');
    }

    /**
     * Get workflow stages
     */
    protected function get_stages() {
        return $this->stages;
    }

    /**
     * Check if post type is valid for this workflow
     */
    protected function is_valid_post_type($post) {
        return $post->post_type === 'privacy-collection';
    }

    /**
     * Get workflow stage for post status
     */
    protected function get_stage_for_status($status) {
        $status_stage_map = [
            'draft' => 'draft',
            'pending' => 'pta_review',
            'private' => 'implementation',
            'publish' => 'active',
            'archive' => 'archived'
        ];

        return $status_stage_map[$status] ?? null;
    }

    /**
     * Get notifications for stage
     */
    protected function get_stage_notifications($stage) {
        $notifications = [
            'pta_required' => [
                [
                    'type' => 'email',
                    'template' => 'pta_required',
                    'recipients' => ['privacy_officer', 'collection_owner'],
                    'subject' => __('Privacy Threshold Analysis Required', 'piper-privacy')
                ]
            ],
            'pta_review' => [
                [
                    'type' => 'email',
                    'template' => 'pta_review',
                    'recipients' => ['privacy_officer'],
                    'subject' => __('PTA Review Required', 'piper-privacy')
                ]
            ],
            'pia_required' => [
                [
                    'type' => 'email',
                    'template' => 'pia_required',
                    'recipients' => ['privacy_officer', 'collection_owner', 'system_owner'],
                    'subject' => __('Privacy Impact Assessment Required', 'piper-privacy')
                ]
            ],
            'pia_review' => [
                [
                    'type' => 'email',
                    'template' => 'pia_review',
                    'recipients' => ['privacy_officer', 'legal_officer'],
                    'subject' => __('PIA Review Required', 'piper-privacy')
                ]
            ],
            'implementation' => [
                [
                    'type' => 'email',
                    'template' => 'implementation_start',
                    'recipients' => ['collection_owner', 'system_owner'],
                    'subject' => __('Privacy Collection Implementation', 'piper-privacy')
                ]
            ],
            'review' => [
                [
                    'type' => 'email',
                    'template' => 'periodic_review',
                    'recipients' => ['privacy_officer', 'collection_owner'],
                    'subject' => __('Privacy Collection Review Required', 'piper-privacy')
                ]
            ],
            'retirement' => [
                [
                    'type' => 'email',
                    'template' => 'retirement_notice',
                    'recipients' => ['privacy_officer', 'collection_owner', 'system_owner'],
                    'subject' => __('Privacy Collection Retirement', 'piper-privacy')
                ]
            ]
        ];

        return $notifications[$stage] ?? [];
    }

    /**
     * Process stage change
     */
    public function process_stage_change($post_id, $old_stage, $new_stage) {
        parent::process_stage_change($post_id, $old_stage, $new_stage);

        // Handle stage-specific logic
        switch ($new_stage) {
            case 'pta_required':
                $this->initiate_pta($post_id);
                break;

            case 'pia_required':
                $this->initiate_pia($post_id);
                break;

            case 'active':
                $this->schedule_reviews($post_id);
                break;

            case 'retirement':
                $this->initiate_retirement($post_id);
                break;
        }
    }

    /**
     * Initiate PTA process
     */
    protected function initiate_pta($post_id) {
        // Create PTA draft
        $pta_id = wp_insert_post([
            'post_type' => 'privacy-threshold',
            'post_status' => 'draft',
            'post_title' => sprintf(
                __('PTA: %s', 'piper-privacy'),
                get_the_title($post_id)
            ),
            'meta_input' => [
                'collection_reference' => $post_id
            ]
        ]);

        // Update collection with PTA reference
        update_post_meta($post_id, 'current_pta', $pta_id);
        
        // Trigger PTA creation action
        do_action('piper_privacy_pta_initiated', $pta_id, $post_id);
    }

    /**
     * Initiate PIA process
     */
    protected function initiate_pia($post_id) {
        $current_pta = get_post_meta($post_id, 'current_pta', true);

        // Create PIA draft
        $pia_id = wp_insert_post([
            'post_type' => 'privacy-impact',
            'post_status' => 'draft',
            'post_title' => sprintf(
                __('PIA: %s', 'piper-privacy'),
                get_the_title($post_id)
            ),
            'meta_input' => [
                'collection_reference' => $post_id,
                'pta_reference' => $current_pta
            ]
        ]);

        // Update collection with PIA reference
        update_post_meta($post_id, 'current_pia', $pia_id);
        
        // Trigger PIA creation action
        do_action('piper_privacy_pia_initiated', $pia_id, $post_id);
    }

    /**
     * Schedule periodic reviews
     */
    protected function schedule_reviews($post_id) {
        // Schedule next review in 1 year
        $next_review = strtotime('+1 year');
        update_post_meta($post_id, 'next_review_date', date('Y-m-d', $next_review));

        // Schedule review reminder
        wp_schedule_single_event($next_review, 'piper_privacy_collection_review_due', [$post_id]);
    }

    /**
     * Initiate retirement process
     */
    protected function initiate_retirement($post_id) {
        // Create retirement documentation
        $retirement_id = wp_insert_post([
            'post_type' => 'privacy-document',
            'post_status' => 'draft',
            'post_title' => sprintf(
                __('Retirement Documentation: %s', 'piper-privacy'),
                get_the_title($post_id)
            ),
            'meta_input' => [
                'collection_reference' => $post_id,
                'document_type' => 'retirement'
            ]
        ]);

        // Update collection status
        update_post_meta($post_id, 'retirement_documentation', $retirement_id);
        
        // Trigger retirement process action
        do_action('piper_privacy_retirement_initiated', $retirement_id, $post_id);
    }

    /**
     * Get workflow settings
     */
    protected function get_workflow_settings() {
        return array_merge(parent::get_workflow_settings(), [
            'auto_pta' => true,
            'auto_pia' => true,
            'review_period' => 365, // days
            'notifications' => [
                'email' => true,
                'dashboard' => true,
                'slack' => false
            ],
            'reminders' => [
                'review_due' => 30, // days before
                'review_overdue' => 1, // days after
            ]
        ]);
    }
}