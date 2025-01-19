<?php
declare(strict_types=1);

namespace PiperPrivacy\Tests\Data;

/**
 * Test Data Generator
 * 
 * Generates test data for privacy collections, thresholds, and impact assessments
 */
class TestDataGenerator {
    /**
     * Generate test data for all post types
     */
    public static function generate_all() {
        self::generate_collections();
        self::generate_thresholds();
        self::generate_impacts();
    }

    /**
     * Generate test privacy collections
     */
    public static function generate_collections() {
        $collections = [
            [
                'post_title' => 'Customer Data Collection',
                'post_type' => 'privacy_collection',
                'post_status' => 'publish',
                'meta_input' => [
                    'collection_purpose' => 'Collection of customer data for CRM system',
                    'data_elements' => [
                        'Full Name',
                        'Email Address',
                        'Phone Number',
                        'Mailing Address'
                    ],
                    'data_sources' => [
                        'Web Forms',
                        'Phone Calls',
                        'Email Correspondence'
                    ],
                    'retention_period' => '24 months',
                    'legal_authority' => 'Customer Consent',
                    'routine_uses' => 'Customer communication and service delivery',
                    'sharing_parties' => [
                        [
                            'party_name' => 'Email Service Provider',
                            'purpose' => 'Email delivery',
                            'data_shared' => 'Email address only'
                        ],
                        [
                            'party_name' => 'CRM System',
                            'purpose' => 'Customer management',
                            'data_shared' => 'All customer data'
                        ]
                    ]
                ]
            ],
            [
                'post_title' => 'Employee Records',
                'post_type' => 'privacy_collection',
                'post_status' => 'publish',
                'meta_input' => [
                    'collection_purpose' => 'Management of employee records and HR data',
                    'data_elements' => [
                        'Full Name',
                        'SSN',
                        'Bank Details',
                        'Address'
                    ],
                    'data_sources' => [
                        'HR Forms',
                        'Employee Portal',
                        'Direct Submission'
                    ],
                    'retention_period' => '7 years',
                    'legal_authority' => 'Employment Contract',
                    'routine_uses' => 'Payroll, benefits administration',
                    'sharing_parties' => [
                        [
                            'party_name' => 'Payroll Provider',
                            'purpose' => 'Salary processing',
                            'data_shared' => 'Name, bank details'
                        ],
                        [
                            'party_name' => 'Benefits Provider',
                            'purpose' => 'Benefits administration',
                            'data_shared' => 'Name, dependents info'
                        ]
                    ]
                ]
            ]
        ];

        foreach ($collections as $collection) {
            $post_id = wp_insert_post($collection);
            self::update_meta_fields($post_id, $collection['meta_input']);
        }
    }

    /**
     * Generate test threshold assessments
     */
    public static function generate_thresholds() {
        $thresholds = [
            [
                'post_title' => 'Customer Data PTA',
                'post_type' => 'privacy_threshold',
                'post_status' => 'publish',
                'meta_input' => [
                    'system_name' => 'CRM System',
                    'system_description' => 'Customer relationship management system',
                    'pii_categories' => [
                        'Contact Information',
                        'Financial Data',
                        'Usage Data'
                    ],
                    'risk_level' => 'medium',
                    'pta_recommendation' => 'Full PIA Required',
                    'data_volume' => 'high',
                    'sensitivity' => 'medium',
                    'processing_type' => 'automated'
                ]
            ],
            [
                'post_title' => 'Marketing Analytics PTA',
                'post_type' => 'privacy_threshold',
                'post_status' => 'publish',
                'meta_input' => [
                    'system_name' => 'Analytics Platform',
                    'system_description' => 'Marketing analytics and tracking',
                    'pii_categories' => [
                        'Usage Data',
                        'Behavioral Data',
                        'Device Information'
                    ],
                    'risk_level' => 'low',
                    'pta_recommendation' => 'No PIA Required',
                    'data_volume' => 'high',
                    'sensitivity' => 'low',
                    'processing_type' => 'automated'
                ]
            ]
        ];

        foreach ($thresholds as $threshold) {
            $post_id = wp_insert_post($threshold);
            self::update_meta_fields($post_id, $threshold['meta_input']);
        }
    }

    /**
     * Generate test impact assessments
     */
    public static function generate_impacts() {
        $impacts = [
            [
                'post_title' => 'CRM System PIA',
                'post_type' => 'privacy_impact',
                'post_status' => 'publish',
                'meta_input' => [
                    'system_overview' => 'Comprehensive assessment of CRM system privacy impacts',
                    'data_flow' => 'Customer data flows from web forms to CRM system',
                    'privacy_risks' => [
                        'Unauthorized access',
                        'Data breach',
                        'Excessive data collection'
                    ],
                    'mitigation_measures' => [
                        'Access controls',
                        'Encryption',
                        'Data minimization'
                    ],
                    'recommendations' => [
                        'Implement MFA',
                        'Regular security audits',
                        'Data retention policies'
                    ]
                ]
            ],
            [
                'post_title' => 'HR System PIA',
                'post_type' => 'privacy_impact',
                'post_status' => 'publish',
                'meta_input' => [
                    'system_overview' => 'Privacy impact assessment for HR management system',
                    'data_flow' => 'Employee data collected through HR portal',
                    'privacy_risks' => [
                        'Sensitive data exposure',
                        'Unauthorized access',
                        'Data retention issues'
                    ],
                    'mitigation_measures' => [
                        'Role-based access',
                        'Data encryption',
                        'Audit logging'
                    ],
                    'recommendations' => [
                        'Regular access reviews',
                        'Employee privacy training',
                        'Data disposal procedures'
                    ]
                ]
            ]
        ];

        foreach ($impacts as $impact) {
            $post_id = wp_insert_post($impact);
            self::update_meta_fields($post_id, $impact['meta_input']);
        }
    }

    /**
     * Update meta fields using appropriate function based on availability
     */
    private static function update_meta_fields($post_id, $fields) {
        foreach ($fields as $key => $value) {
            if (function_exists('rwmb_meta_update')) {
                rwmb_meta_update($key, $post_id, $value);
            } elseif (function_exists('update_field')) {
                update_field($key, $value, $post_id);
            } else {
                update_post_meta($post_id, $key, $value);
            }
        }
    }
}
