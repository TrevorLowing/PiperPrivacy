<?php
namespace PiperPrivacy\Includes\Integrations;

/**
 * Meta Box Integration
 *
 * @package    PiperPrivacy
 * @subpackage PiperPrivacy/includes/integrations
 */
class MetaboxIntegration {
    /**
     * Register Meta Box fields
     */
    public function register_fields() {
        add_filter('rwmb_meta_boxes', [$this, 'register_meta_boxes']);
    }

    /**
     * Register meta boxes and fields
     *
     * @param array $meta_boxes Existing meta boxes
     * @return array Modified meta boxes
     */
    public function register_meta_boxes($meta_boxes) {
        // Privacy Collection Fields
        $meta_boxes[] = [
            'title'      => __('Collection Details', 'piper-privacy'),
            'id'         => 'privacy_collection_details',
            'post_types' => ['privacy_collection'],
            'fields'     => [
                // Basic Collection Fields
                [
                    'name'    => __('Collection Purpose', 'piper-privacy'),
                    'id'      => 'collection_purpose',
                    'type'    => 'textarea',
                    'desc'    => __('Describe the purpose for collecting this information', 'piper-privacy'),
                ],
                // Sharing Parties Group
                [
                    'name'    => __('Sharing Parties', 'piper-privacy'),
                    'id'      => 'sharing_parties',
                    'type'    => 'group',
                    'clone'   => true,
                    'fields'  => [
                        [
                            'name'     => __('Party Name', 'piper-privacy'),
                            'id'       => 'party_name',
                            'type'     => 'text',
                            'required' => true,
                        ],
                        [
                            'name'     => __('Sharing Purpose', 'piper-privacy'),
                            'id'       => 'sharing_purpose',
                            'type'     => 'textarea',
                            'required' => true,
                        ],
                        [
                            'name' => __('Agreement Reference', 'piper-privacy'),
                            'id'   => 'agreement_reference',
                            'type' => 'text',
                        ],
                    ],
                ],
                // Security Controls Group
                [
                    'name'    => __('Security Controls', 'piper-privacy'),
                    'id'      => 'security_controls',
                    'type'    => 'group',
                    'clone'   => true,
                    'fields'  => [
                        [
                            'name'     => __('Control Name', 'piper-privacy'),
                            'id'       => 'control_name',
                            'type'     => 'text',
                            'required' => true,
                        ],
                        [
                            'name'     => __('Description', 'piper-privacy'),
                            'id'       => 'control_description',
                            'type'     => 'textarea',
                            'required' => true,
                        ],
                        [
                            'name'     => __('Status', 'piper-privacy'),
                            'id'       => 'control_status',
                            'type'     => 'select',
                            'options'  => [
                                'implemented'  => __('Implemented', 'piper-privacy'),
                                'planned'      => __('Planned', 'piper-privacy'),
                                'under_review' => __('Under Review', 'piper-privacy'),
                            ],
                            'required' => true,
                        ],
                    ],
                ],
            ],
        ];

        // Privacy Threshold Fields
        $meta_boxes[] = [
            'title'      => __('Threshold Analysis', 'piper-privacy'),
            'id'         => 'privacy_threshold_analysis',
            'post_types' => ['privacy_threshold'],
            'fields'     => [
                [
                    'name'    => __('Data Elements Analysis', 'piper-privacy'),
                    'id'      => 'data_elements_analysis',
                    'type'    => 'group',
                    'clone'   => true,
                    'fields'  => [
                        [
                            'name'     => __('Element Name', 'piper-privacy'),
                            'id'       => 'element_name',
                            'type'     => 'text',
                            'required' => true,
                        ],
                        [
                            'name'     => __('Sensitivity Level', 'piper-privacy'),
                            'id'       => 'sensitivity_level',
                            'type'     => 'select',
                            'options'  => [
                                'low'    => __('Low', 'piper-privacy'),
                                'medium' => __('Medium', 'piper-privacy'),
                                'high'   => __('High', 'piper-privacy'),
                            ],
                            'required' => true,
                        ],
                        [
                            'name'     => __('Collection Justification', 'piper-privacy'),
                            'id'       => 'collection_justification',
                            'type'     => 'textarea',
                            'required' => true,
                        ],
                    ],
                ],
            ],
        ];

        // Privacy Impact Fields
        $meta_boxes[] = [
            'title'      => __('Impact Assessment', 'piper-privacy'),
            'id'         => 'privacy_impact_assessment',
            'post_types' => ['privacy_impact'],
            'fields'     => [
                [
                    'name'    => __('Risk Assessment', 'piper-privacy'),
                    'id'      => 'risk_assessment',
                    'type'    => 'group',
                    'clone'   => true,
                    'fields'  => [
                        [
                            'name'     => __('Risk Description', 'piper-privacy'),
                            'id'       => 'risk_description',
                            'type'     => 'textarea',
                            'required' => true,
                        ],
                        [
                            'name'     => __('Likelihood', 'piper-privacy'),
                            'id'       => 'likelihood',
                            'type'     => 'select',
                            'options'  => [
                                'low'    => __('Low', 'piper-privacy'),
                                'medium' => __('Medium', 'piper-privacy'),
                                'high'   => __('High', 'piper-privacy'),
                            ],
                            'required' => true,
                        ],
                        [
                            'name'     => __('Impact', 'piper-privacy'),
                            'id'       => 'impact',
                            'type'     => 'select',
                            'options'  => [
                                'low'    => __('Low', 'piper-privacy'),
                                'medium' => __('Medium', 'piper-privacy'),
                                'high'   => __('High', 'piper-privacy'),
                            ],
                            'required' => true,
                        ],
                    ],
                ],
            ],
        ];

        return $meta_boxes;
    }
}
