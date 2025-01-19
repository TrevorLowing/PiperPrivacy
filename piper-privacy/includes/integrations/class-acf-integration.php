<?php
namespace PiperPrivacy\Includes\Integrations;

/**
 * Advanced Custom Fields Integration
 *
 * @package    PiperPrivacy
 * @subpackage PiperPrivacy/includes/integrations
 */
class ACFIntegration {
    /**
     * Register ACF fields
     */
    public function register_fields() {
        add_action('acf/init', [$this, 'register_field_groups']);
    }

    /**
     * Register ACF field groups
     */
    public function register_field_groups() {
        if (!function_exists('acf_add_local_field_group')) {
            return;
        }

        // Privacy Collection Fields
        $this->register_collection_fields();
        
        // Privacy Threshold Fields
        $this->register_threshold_fields();
        
        // Privacy Impact Fields
        $this->register_impact_fields();

        // Stakeholder Fields
        $this->register_stakeholder_fields();
    }

    /**
     * Register stakeholder fields for Privacy Collection
     */
    private function register_stakeholder_fields() {
        acf_add_local_field_group([
            'key' => 'group_stakeholders',
            'title' => 'Stakeholders',
            'fields' => [
                [
                    'key' => 'field_stakeholders',
                    'label' => 'Stakeholders',
                    'name' => 'stakeholders',
                    'type' => 'repeater',
                    'layout' => 'table',
                    'sub_fields' => [
                        [
                            'key' => 'field_stakeholder_name',
                            'label' => 'Name',
                            'name' => 'name',
                            'type' => 'text',
                            'required' => 1,
                        ],
                        [
                            'key' => 'field_stakeholder_role',
                            'label' => 'Role',
                            'name' => 'role',
                            'type' => 'text',
                            'required' => 1,
                        ],
                        [
                            'key' => 'field_stakeholder_department',
                            'label' => 'Department',
                            'name' => 'department',
                            'type' => 'text',
                        ],
                        [
                            'key' => 'field_stakeholder_email',
                            'label' => 'Email',
                            'name' => 'email',
                            'type' => 'email',
                            'required' => 1,
                        ],
                        [
                            'key' => 'field_stakeholder_notifications',
                            'label' => 'Notifications',
                            'name' => 'notifications',
                            'type' => 'checkbox',
                            'choices' => [
                                'status_change' => 'Status Changes',
                                'comments' => 'New Comments',
                                'documents' => 'Document Updates',
                            ],
                            'default_value' => ['status_change'],
                        ],
                    ],
                ],
            ],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'privacy_collection',
                    ],
                ],
            ],
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
        ]);
    }

    /**
     * Register collection fields for Privacy Collection
     */
    private function register_collection_fields() {
        acf_add_local_field_group([
            'key' => 'group_privacy_collection',
            'title' => 'Privacy Collection Additional Details',
            'fields' => [
                [
                    'key' => 'field_sharing_parties',
                    'label' => 'Sharing Parties',
                    'name' => 'sharing_parties',
                    'type' => 'repeater',
                    'instructions' => 'Add organizations or parties with whom this information is shared',
                    'required' => 0,
                    'layout' => 'table',
                    'sub_fields' => [
                        [
                            'key' => 'field_party_name',
                            'label' => 'Party Name',
                            'name' => 'party_name',
                            'type' => 'text',
                            'required' => 1,
                        ],
                        [
                            'key' => 'field_sharing_purpose',
                            'label' => 'Sharing Purpose',
                            'name' => 'sharing_purpose',
                            'type' => 'textarea',
                            'required' => 1,
                        ],
                        [
                            'key' => 'field_sharing_agreement',
                            'label' => 'Agreement Reference',
                            'name' => 'agreement_reference',
                            'type' => 'text',
                            'required' => 0,
                        ],
                    ],
                ],
                [
                    'key' => 'field_security_controls',
                    'label' => 'Security Controls',
                    'name' => 'security_controls',
                    'type' => 'repeater',
                    'instructions' => 'List security controls in place for this collection',
                    'required' => 0,
                    'layout' => 'table',
                    'sub_fields' => [
                        [
                            'key' => 'field_control_name',
                            'label' => 'Control Name',
                            'name' => 'control_name',
                            'type' => 'text',
                            'required' => 1,
                        ],
                        [
                            'key' => 'field_control_description',
                            'label' => 'Description',
                            'name' => 'control_description',
                            'type' => 'textarea',
                            'required' => 1,
                        ],
                        [
                            'key' => 'field_control_status',
                            'label' => 'Status',
                            'name' => 'control_status',
                            'type' => 'select',
                            'choices' => [
                                'implemented' => 'Implemented',
                                'planned' => 'Planned',
                                'under_review' => 'Under Review',
                            ],
                            'required' => 1,
                        ],
                    ],
                ],
            ],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'privacy_collection',
                    ],
                ],
            ],
        ]);
    }

    /**
     * Register threshold fields for Privacy Threshold
     */
    private function register_threshold_fields() {
        acf_add_local_field_group([
            'key' => 'group_privacy_threshold',
            'title' => 'Privacy Threshold Additional Analysis',
            'fields' => [
                [
                    'key' => 'field_data_elements',
                    'label' => 'Data Elements Analysis',
                    'name' => 'data_elements_analysis',
                    'type' => 'repeater',
                    'instructions' => 'Analyze each data element collected',
                    'required' => 0,
                    'layout' => 'table',
                    'sub_fields' => [
                        [
                            'key' => 'field_element_name',
                            'label' => 'Element Name',
                            'name' => 'element_name',
                            'type' => 'text',
                            'required' => 1,
                        ],
                        [
                            'key' => 'field_sensitivity',
                            'label' => 'Sensitivity Level',
                            'name' => 'sensitivity_level',
                            'type' => 'select',
                            'choices' => [
                                'low' => 'Low',
                                'medium' => 'Medium',
                                'high' => 'High',
                            ],
                            'required' => 1,
                        ],
                        [
                            'key' => 'field_justification',
                            'label' => 'Collection Justification',
                            'name' => 'collection_justification',
                            'type' => 'textarea',
                            'required' => 1,
                        ],
                    ],
                ],
            ],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'privacy_threshold',
                    ],
                ],
            ],
        ]);
    }

    /**
     * Register impact fields for Privacy Impact
     */
    private function register_impact_fields() {
        acf_add_local_field_group([
            'key' => 'group_privacy_impact',
            'title' => 'Privacy Impact Additional Assessment',
            'fields' => [
                [
                    'key' => 'field_risk_assessment',
                    'label' => 'Risk Assessment',
                    'name' => 'risk_assessment',
                    'type' => 'repeater',
                    'instructions' => 'Assess each identified privacy risk',
                    'required' => 0,
                    'layout' => 'table',
                    'sub_fields' => [
                        [
                            'key' => 'field_risk_description',
                            'label' => 'Risk Description',
                            'name' => 'risk_description',
                            'type' => 'textarea',
                            'required' => 1,
                        ],
                        [
                            'key' => 'field_likelihood',
                            'label' => 'Likelihood',
                            'name' => 'likelihood',
                            'type' => 'select',
                            'choices' => [
                                'low' => 'Low',
                                'medium' => 'Medium',
                                'high' => 'High',
                            ],
                            'required' => 1,
                        ],
                        [
                            'key' => 'field_impact',
                            'label' => 'Impact',
                            'name' => 'impact',
                            'type' => 'select',
                            'choices' => [
                                'low' => 'Low',
                                'medium' => 'Medium',
                                'high' => 'High',
                            ],
                            'required' => 1,
                        ],
                        [
                            'key' => 'field_mitigation',
                            'label' => 'Mitigation Strategy',
                            'name' => 'mitigation_strategy',
                            'type' => 'textarea',
                            'required' => 1,
                        ],
                    ],
                ],
            ],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'privacy_impact',
                    ],
                ],
            ],
        ]);
    }
}
