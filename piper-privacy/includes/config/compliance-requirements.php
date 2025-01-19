<?php
/**
 * Compliance Requirements Configuration
 *
 * Defines jurisdiction-specific requirements and industry best practices
 * for privacy compliance validation.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get compliance requirements configuration
 *
 * @return array Configuration settings
 */
function pp_get_compliance_config() {
    return [
        'jurisdictions' => [
            'gdpr' => [
                'name' => 'GDPR',
                'description' => 'General Data Protection Regulation (EU)',
                'enabled' => false,
                'requirements' => [
                    'dpo' => true,
                    'breach_notification' => [
                        'required' => true,
                        'timeline' => 72 // hours
                    ],
                    'data_transfers' => [
                        'adequacy_decision' => true,
                        'sccs' => true
                    ],
                    'dsar_timeline' => 30 // days
                ]
            ],
            'ccpa' => [
                'name' => 'CCPA/CPRA',
                'description' => 'California Consumer Privacy Act/California Privacy Rights Act',
                'enabled' => false,
                'requirements' => [
                    'dpo' => false,
                    'breach_notification' => [
                        'required' => true,
                        'timeline' => 15 // days in California
                    ],
                    'data_transfers' => [
                        'adequacy_decision' => false,
                        'sccs' => false
                    ],
                    'dsar_timeline' => 45 // days
                ]
            ],
            'pipeda' => [
                'name' => 'PIPEDA',
                'description' => 'Personal Information Protection and Electronic Documents Act (Canada)',
                'enabled' => false,
                'requirements' => [
                    'dpo' => false,
                    'breach_notification' => [
                        'required' => true,
                        'timeline' => 'as_soon_as_feasible'
                    ],
                    'data_transfers' => [
                        'adequacy_decision' => false,
                        'sccs' => false
                    ],
                    'dsar_timeline' => 30 // days
                ]
            ]
        ],
        'industry_standards' => [
            'iso27701' => [
                'name' => 'ISO 27701',
                'description' => 'Privacy Information Management System',
                'enabled' => true,
                'requirements' => [
                    'privacy_policy' => true,
                    'risk_assessment' => true,
                    'incident_response' => true,
                    'training' => true
                ]
            ],
            'nist_privacy' => [
                'name' => 'NIST Privacy Framework',
                'description' => 'National Institute of Standards and Technology Privacy Framework',
                'enabled' => true,
                'requirements' => [
                    'privacy_policy' => true,
                    'risk_assessment' => true,
                    'incident_response' => true,
                    'training' => true
                ]
            ]
        ],
        'industry_specific' => [
            'healthcare' => [
                'name' => 'Healthcare',
                'description' => 'Healthcare-specific requirements (HIPAA, HITECH)',
                'enabled' => false,
                'requirements' => [
                    'phi_protection' => true,
                    'breach_notification' => [
                        'required' => true,
                        'timeline' => 60 // days
                    ],
                    'minimum_necessary' => true,
                    'patient_rights' => true
                ]
            ],
            'financial' => [
                'name' => 'Financial Services',
                'description' => 'Financial services requirements (GLBA, FCRA)',
                'enabled' => false,
                'requirements' => [
                    'financial_privacy_notice' => true,
                    'safeguards_rule' => true,
                    'consumer_reporting' => true
                ]
            ]
        ],
        'best_practices' => [
            'privacy_by_design' => [
                'enabled' => true,
                'requirements' => [
                    'data_minimization' => true,
                    'purpose_limitation' => true,
                    'security_measures' => true,
                    'transparency' => true,
                    'user_rights' => true
                ]
            ],
            'security' => [
                'enabled' => true,
                'requirements' => [
                    'encryption' => true,
                    'access_control' => true,
                    'audit_logging' => true,
                    'incident_response' => true,
                    'backup_recovery' => true
                ]
            ],
            'documentation' => [
                'enabled' => true,
                'requirements' => [
                    'policies_procedures' => true,
                    'training_materials' => true,
                    'audit_records' => true,
                    'compliance_records' => true
                ]
            ]
        ]
    ];
}

/**
 * Get active compliance requirements
 *
 * @return array Active requirements
 */
function pp_get_active_requirements() {
    $config = pp_get_compliance_config();
    $active = [
        'jurisdictions' => [],
        'industry_standards' => [],
        'industry_specific' => [],
        'best_practices' => []
    ];

    // Get active jurisdictions
    foreach ($config['jurisdictions'] as $key => $jurisdiction) {
        if ($jurisdiction['enabled']) {
            $active['jurisdictions'][$key] = $jurisdiction['requirements'];
        }
    }

    // Get active industry standards
    foreach ($config['industry_standards'] as $key => $standard) {
        if ($standard['enabled']) {
            $active['industry_standards'][$key] = $standard['requirements'];
        }
    }

    // Get active industry-specific requirements
    foreach ($config['industry_specific'] as $key => $industry) {
        if ($industry['enabled']) {
            $active['industry_specific'][$key] = $industry['requirements'];
        }
    }

    // Always include best practices
    $active['best_practices'] = $config['best_practices'];

    return $active;
}

/**
 * Check if a specific requirement is active
 *
 * @param string $category Category of requirement (jurisdictions, industry_standards, etc.)
 * @param string $key     Key of the requirement
 * @return bool Whether the requirement is active
 */
function pp_is_requirement_active($category, $key) {
    $config = pp_get_compliance_config();
    
    if (!isset($config[$category][$key])) {
        return false;
    }

    // Best practices are always active
    if ($category === 'best_practices') {
        return true;
    }

    return $config[$category][$key]['enabled'];
}

/**
 * Get requirement timeline
 *
 * @param string $category Category of requirement
 * @param string $key     Key of the requirement
 * @param string $timeline_key Key for the timeline value
 * @return mixed Timeline value or false if not found
 */
function pp_get_requirement_timeline($category, $key, $timeline_key) {
    $config = pp_get_compliance_config();
    
    if (!isset($config[$category][$key]['requirements'][$timeline_key])) {
        return false;
    }

    return $config[$category][$key]['requirements'][$timeline_key];
}
