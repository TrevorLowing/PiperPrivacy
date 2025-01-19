<?php
declare(strict_types=1);

namespace PiperPrivacy\Includes\Fields;

/**
 * Threshold Fields Registration
 * 
 * Registers MetaBox fields for privacy threshold assessments
 */
class ThresholdFields {
    /**
     * Register fields
     */
    public function register() {
        add_filter('rwmb_meta_boxes', [$this, 'register_fields']);
    }

    /**
     * Register threshold fields
     *
     * @param array $meta_boxes Existing meta boxes
     * @return array Modified meta boxes
     */
    public function register_fields($meta_boxes) {
        $meta_boxes[] = [
            'title' => __('Threshold Assessment Details', 'piper-privacy'),
            'id' => 'privacy_threshold_details',
            'post_types' => ['privacy_threshold'],
            'context' => 'normal',
            'priority' => 'high',
            'fields' => [
                // System Information
                [
                    'name' => __('System Name', 'piper-privacy'),
                    'id' => 'system_name',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'name' => __('System Description', 'piper-privacy'),
                    'id' => 'system_description',
                    'type' => 'textarea',
                    'required' => true,
                    'rows' => 4,
                ],
                [
                    'name' => __('System Owner', 'piper-privacy'),
                    'id' => 'system_owner',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'name' => __('System Status', 'piper-privacy'),
                    'id' => 'system_status',
                    'type' => 'select',
                    'options' => [
                        'planning' => __('Planning', 'piper-privacy'),
                        'development' => __('Development', 'piper-privacy'),
                        'production' => __('Production', 'piper-privacy'),
                        'retired' => __('Retired', 'piper-privacy'),
                    ],
                    'required' => true,
                ],

                // Data Processing Details
                [
                    'name' => __('PII Categories', 'piper-privacy'),
                    'id' => 'pii_categories',
                    'type' => 'checkbox_list',
                    'options' => [
                        'contact' => __('Contact Information', 'piper-privacy'),
                        'financial' => __('Financial Data', 'piper-privacy'),
                        'health' => __('Health Information', 'piper-privacy'),
                        'biometric' => __('Biometric Data', 'piper-privacy'),
                        'location' => __('Location Data', 'piper-privacy'),
                        'behavioral' => __('Behavioral Data', 'piper-privacy'),
                        'professional' => __('Professional Information', 'piper-privacy'),
                        'education' => __('Education Information', 'piper-privacy'),
                        'demographic' => __('Demographic Data', 'piper-privacy'),
                        'other' => __('Other', 'piper-privacy'),
                    ],
                    'required' => true,
                ],
                [
                    'name' => __('Other PII Categories', 'piper-privacy'),
                    'id' => 'other_pii_categories',
                    'type' => 'textarea',
                    'visible' => ['pii_categories', '=', 'other'],
                ],
                [
                    'name' => __('Data Volume', 'piper-privacy'),
                    'id' => 'data_volume',
                    'type' => 'select',
                    'options' => [
                        'low' => __('Low (< 1,000 records)', 'piper-privacy'),
                        'medium' => __('Medium (1,000 - 100,000 records)', 'piper-privacy'),
                        'high' => __('High (> 100,000 records)', 'piper-privacy'),
                    ],
                    'required' => true,
                ],
                [
                    'name' => __('Processing Type', 'piper-privacy'),
                    'id' => 'processing_type',
                    'type' => 'select',
                    'options' => [
                        'manual' => __('Manual Processing', 'piper-privacy'),
                        'automated' => __('Automated Processing', 'piper-privacy'),
                        'profiling' => __('Automated Decision-Making/Profiling', 'piper-privacy'),
                    ],
                    'required' => true,
                ],

                // Risk Assessment
                [
                    'name' => __('Risk Level', 'piper-privacy'),
                    'id' => 'risk_level',
                    'type' => 'select',
                    'options' => [
                        'low' => __('Low', 'piper-privacy'),
                        'medium' => __('Medium', 'piper-privacy'),
                        'high' => __('High', 'piper-privacy'),
                    ],
                    'required' => true,
                ],
                [
                    'name' => __('Risk Factors', 'piper-privacy'),
                    'id' => 'risk_factors',
                    'type' => 'checkbox_list',
                    'options' => [
                        'sensitive_data' => __('Sensitive Data Processing', 'piper-privacy'),
                        'vulnerable_subjects' => __('Vulnerable Data Subjects', 'piper-privacy'),
                        'large_scale' => __('Large Scale Processing', 'piper-privacy'),
                        'new_tech' => __('New Technologies', 'piper-privacy'),
                        'monitoring' => __('Systematic Monitoring', 'piper-privacy'),
                        'international' => __('International Transfers', 'piper-privacy'),
                    ],
                ],
                [
                    'name' => __('Risk Description', 'piper-privacy'),
                    'id' => 'risk_description',
                    'type' => 'wysiwyg',
                    'options' => [
                        'textarea_rows' => 4,
                        'teeny' => true,
                    ],
                ],

                // Assessment Outcome
                [
                    'name' => __('PTA Recommendation', 'piper-privacy'),
                    'id' => 'pta_recommendation',
                    'type' => 'radio',
                    'options' => [
                        'no_pia' => __('No PIA Required', 'piper-privacy'),
                        'limited_pia' => __('Limited PIA Required', 'piper-privacy'),
                        'full_pia' => __('Full PIA Required', 'piper-privacy'),
                    ],
                    'required' => true,
                ],
                [
                    'name' => __('Recommendation Rationale', 'piper-privacy'),
                    'id' => 'recommendation_rationale',
                    'type' => 'textarea',
                    'required' => true,
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
                    'system_name' => [
                        'required' => true,
                    ],
                    'system_description' => [
                        'required' => true,
                        'minlength' => 50,
                    ],
                    'pii_categories' => [
                        'required' => true,
                    ],
                    'risk_level' => [
                        'required' => true,
                    ],
                    'pta_recommendation' => [
                        'required' => true,
                    ],
                ],
                'messages' => [
                    'system_name' => [
                        'required' => __('Please provide a system name', 'piper-privacy'),
                    ],
                    'system_description' => [
                        'required' => __('Please provide a system description', 'piper-privacy'),
                        'minlength' => __('System description must be at least 50 characters', 'piper-privacy'),
                    ],
                    'pii_categories' => [
                        'required' => __('Please select at least one PII category', 'piper-privacy'),
                    ],
                    'risk_level' => [
                        'required' => __('Please select a risk level', 'piper-privacy'),
                    ],
                    'pta_recommendation' => [
                        'required' => __('Please select a PTA recommendation', 'piper-privacy'),
                    ],
                ],
            ],
        ];

        return $meta_boxes;
    }
}
