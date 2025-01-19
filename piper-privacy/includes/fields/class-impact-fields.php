<?php
declare(strict_types=1);

namespace PiperPrivacy\Includes\Fields;

/**
 * Impact Fields Registration
 * 
 * Registers MetaBox fields for privacy impact assessments
 */
class ImpactFields {
    /**
     * Register fields
     */
    public function register() {
        add_filter('rwmb_meta_boxes', [$this, 'register_fields']);
    }

    /**
     * Register impact fields
     *
     * @param array $meta_boxes Existing meta boxes
     * @return array Modified meta boxes
     */
    public function register_fields($meta_boxes) {
        $meta_boxes[] = [
            'title' => __('Impact Assessment Details', 'piper-privacy'),
            'id' => 'privacy_impact_details',
            'post_types' => ['privacy_impact'],
            'context' => 'normal',
            'priority' => 'high',
            'fields' => [
                // System Overview
                [
                    'name' => __('System Overview', 'piper-privacy'),
                    'id' => 'system_overview',
                    'type' => 'wysiwyg',
                    'options' => [
                        'textarea_rows' => 6,
                        'teeny' => true,
                    ],
                    'required' => true,
                ],
                [
                    'name' => __('Related Threshold Assessment', 'piper-privacy'),
                    'id' => 'related_threshold',
                    'type' => 'post',
                    'post_type' => 'privacy_threshold',
                    'field_type' => 'select_advanced',
                    'required' => true,
                ],

                // Data Flow Analysis
                [
                    'name' => __('Data Flow', 'piper-privacy'),
                    'id' => 'data_flow',
                    'type' => 'wysiwyg',
                    'options' => [
                        'textarea_rows' => 6,
                        'teeny' => true,
                    ],
                    'required' => true,
                ],
                [
                    'name' => __('Data Flow Diagram', 'piper-privacy'),
                    'id' => 'data_flow_diagram',
                    'type' => 'file_advanced',
                    'max_file_uploads' => 1,
                    'mime_type' => 'image/*',
                ],

                // Privacy Principles Assessment
                [
                    'name' => __('Privacy Principles Assessment', 'piper-privacy'),
                    'id' => 'privacy_principles',
                    'type' => 'group',
                    'clone' => true,
                    'collapsible' => true,
                    'group_title' => ['field' => 'principle'],
                    'fields' => [
                        [
                            'name' => __('Principle', 'piper-privacy'),
                            'id' => 'principle',
                            'type' => 'select',
                            'options' => [
                                'lawfulness' => __('Lawfulness, Fairness and Transparency', 'piper-privacy'),
                                'purpose' => __('Purpose Limitation', 'piper-privacy'),
                                'minimization' => __('Data Minimization', 'piper-privacy'),
                                'accuracy' => __('Accuracy', 'piper-privacy'),
                                'retention' => __('Storage Limitation', 'piper-privacy'),
                                'security' => __('Integrity and Confidentiality', 'piper-privacy'),
                                'accountability' => __('Accountability', 'piper-privacy'),
                            ],
                            'required' => true,
                        ],
                        [
                            'name' => __('Assessment', 'piper-privacy'),
                            'id' => 'assessment',
                            'type' => 'textarea',
                            'required' => true,
                        ],
                        [
                            'name' => __('Compliance Status', 'piper-privacy'),
                            'id' => 'compliance_status',
                            'type' => 'select',
                            'options' => [
                                'compliant' => __('Compliant', 'piper-privacy'),
                                'partial' => __('Partially Compliant', 'piper-privacy'),
                                'non_compliant' => __('Non-Compliant', 'piper-privacy'),
                            ],
                            'required' => true,
                        ],
                    ],
                ],

                // Risk Assessment
                [
                    'name' => __('Privacy Risks', 'piper-privacy'),
                    'id' => 'privacy_risks',
                    'type' => 'group',
                    'clone' => true,
                    'collapsible' => true,
                    'group_title' => ['field' => 'risk_name'],
                    'fields' => [
                        [
                            'name' => __('Risk Name', 'piper-privacy'),
                            'id' => 'risk_name',
                            'type' => 'text',
                            'required' => true,
                        ],
                        [
                            'name' => __('Description', 'piper-privacy'),
                            'id' => 'description',
                            'type' => 'textarea',
                            'required' => true,
                        ],
                        [
                            'name' => __('Impact Level', 'piper-privacy'),
                            'id' => 'impact_level',
                            'type' => 'select',
                            'options' => [
                                'low' => __('Low', 'piper-privacy'),
                                'medium' => __('Medium', 'piper-privacy'),
                                'high' => __('High', 'piper-privacy'),
                            ],
                            'required' => true,
                        ],
                        [
                            'name' => __('Likelihood', 'piper-privacy'),
                            'id' => 'likelihood',
                            'type' => 'select',
                            'options' => [
                                'low' => __('Low', 'piper-privacy'),
                                'medium' => __('Medium', 'piper-privacy'),
                                'high' => __('High', 'piper-privacy'),
                            ],
                            'required' => true,
                        ],
                    ],
                ],

                // Mitigation Measures
                [
                    'name' => __('Mitigation Measures', 'piper-privacy'),
                    'id' => 'mitigation_measures',
                    'type' => 'group',
                    'clone' => true,
                    'collapsible' => true,
                    'group_title' => ['field' => 'measure_name'],
                    'fields' => [
                        [
                            'name' => __('Measure Name', 'piper-privacy'),
                            'id' => 'measure_name',
                            'type' => 'text',
                            'required' => true,
                        ],
                        [
                            'name' => __('Description', 'piper-privacy'),
                            'id' => 'description',
                            'type' => 'textarea',
                            'required' => true,
                        ],
                        [
                            'name' => __('Implementation Status', 'piper-privacy'),
                            'id' => 'implementation_status',
                            'type' => 'select',
                            'options' => [
                                'planned' => __('Planned', 'piper-privacy'),
                                'in_progress' => __('In Progress', 'piper-privacy'),
                                'implemented' => __('Implemented', 'piper-privacy'),
                            ],
                            'required' => true,
                        ],
                        [
                            'name' => __('Implementation Date', 'piper-privacy'),
                            'id' => 'implementation_date',
                            'type' => 'date',
                            'timestamp' => true,
                        ],
                    ],
                ],

                // Recommendations and Sign-off
                [
                    'name' => __('Recommendations', 'piper-privacy'),
                    'id' => 'recommendations',
                    'type' => 'wysiwyg',
                    'options' => [
                        'textarea_rows' => 6,
                        'teeny' => true,
                    ],
                    'required' => true,
                ],
                [
                    'name' => __('DPO Comments', 'piper-privacy'),
                    'id' => 'dpo_comments',
                    'type' => 'wysiwyg',
                    'options' => [
                        'textarea_rows' => 4,
                        'teeny' => true,
                    ],
                ],
                [
                    'name' => __('Next Review Date', 'piper-privacy'),
                    'id' => 'next_review_date',
                    'type' => 'date',
                    'required' => true,
                    'timestamp' => true,
                ],
            ],
            'validation' => [
                'rules' => [
                    'system_overview' => [
                        'required' => true,
                        'minlength' => 100,
                    ],
                    'related_threshold' => [
                        'required' => true,
                    ],
                    'data_flow' => [
                        'required' => true,
                        'minlength' => 100,
                    ],
                    'recommendations' => [
                        'required' => true,
                        'minlength' => 50,
                    ],
                ],
                'messages' => [
                    'system_overview' => [
                        'required' => __('Please provide a system overview', 'piper-privacy'),
                        'minlength' => __('System overview must be at least 100 characters', 'piper-privacy'),
                    ],
                    'related_threshold' => [
                        'required' => __('Please select a related threshold assessment', 'piper-privacy'),
                    ],
                    'data_flow' => [
                        'required' => __('Please provide data flow information', 'piper-privacy'),
                        'minlength' => __('Data flow must be at least 100 characters', 'piper-privacy'),
                    ],
                    'recommendations' => [
                        'required' => __('Please provide recommendations', 'piper-privacy'),
                        'minlength' => __('Recommendations must be at least 50 characters', 'piper-privacy'),
                    ],
                ],
            ],
        ];

        return $meta_boxes;
    }
}
