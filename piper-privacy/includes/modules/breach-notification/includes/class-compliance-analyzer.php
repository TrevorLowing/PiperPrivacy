<?php
/**
 * Compliance Analyzer Class
 *
 * @package     PiperPrivacy
 * @subpackage  Modules\BreachNotification
 */

namespace PiperPrivacy\Modules\BreachNotification;

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Compliance Analyzer class
 */
class Compliance_Analyzer {
    /**
     * Regulatory frameworks
     *
     * @var array
     */
    private $frameworks = [
        'gdpr' => [
            'name' => 'GDPR',
            'authority_notification' => [
                'required' => true,
                'deadline' => 72, // hours
                'exceptions' => [
                    'no_risk' => 'Breach is unlikely to result in a risk to rights and freedoms',
                    'encrypted' => 'Data was encrypted and keys were not compromised',
                ],
            ],
            'individual_notification' => [
                'required' => true,
                'deadline' => 'without_undue_delay',
                'exceptions' => [
                    'encrypted' => 'Data was encrypted and keys were not compromised',
                    'mitigated' => 'Subsequent measures have rendered the high risk unlikely',
                ],
            ],
            'documentation' => [
                'required' => true,
                'retention' => '5 years',
                'elements' => [
                    'breach_facts',
                    'effects',
                    'remedial_action',
                    'notification_details',
                ],
            ],
        ],
        'ccpa' => [
            'name' => 'CCPA',
            'authority_notification' => [
                'required' => false,
            ],
            'individual_notification' => [
                'required' => true,
                'deadline' => 'most_expedient_time',
                'threshold' => [
                    'unencrypted_pi' => true,
                    'unauthorized_access' => true,
                ],
            ],
            'documentation' => [
                'required' => true,
                'retention' => '2 years',
                'elements' => [
                    'breach_details',
                    'notification_records',
                ],
            ],
        ],
        'hipaa' => [
            'name' => 'HIPAA',
            'authority_notification' => [
                'required' => true,
                'deadline' => [
                    '500_or_more' => 60, // days
                    'less_than_500' => 'annual',
                ],
            ],
            'individual_notification' => [
                'required' => true,
                'deadline' => 60, // days
                'exceptions' => [
                    'encrypted' => 'Data was encrypted to NIST standards',
                    'low_risk' => 'Professional determination of low probability of compromise',
                ],
            ],
            'documentation' => [
                'required' => true,
                'retention' => '6 years',
                'elements' => [
                    'risk_assessment',
                    'notification_decisions',
                    'incident_details',
                ],
            ],
        ],
    ];

    /**
     * Analyze compliance requirements
     *
     * @param array $breach_data Breach data.
     * @param array $risk_assessment Risk assessment results.
     * @return array Compliance requirements.
     */
    public function analyze_requirements($breach_data, $risk_assessment) {
        $requirements = [];
        $applicable_frameworks = $this->determine_applicable_frameworks($breach_data);

        foreach ($applicable_frameworks as $framework_id) {
            $framework = $this->frameworks[$framework_id];
            $requirements[$framework_id] = $this->analyze_framework_requirements(
                $framework,
                $breach_data,
                $risk_assessment
            );
        }

        return [
            'frameworks' => $requirements,
            'summary' => $this->generate_requirement_summary($requirements),
            'deadlines' => $this->calculate_compliance_deadlines($requirements, $breach_data['detection_date']),
            'documentation' => $this->compile_documentation_requirements($requirements),
        ];
    }

    /**
     * Determine applicable frameworks
     *
     * @param array $breach_data Breach data.
     * @return array Applicable framework IDs.
     */
    private function determine_applicable_frameworks($breach_data) {
        $applicable = [];

        // Check GDPR applicability
        if ($this->is_gdpr_applicable($breach_data)) {
            $applicable[] = 'gdpr';
        }

        // Check CCPA applicability
        if ($this->is_ccpa_applicable($breach_data)) {
            $applicable[] = 'ccpa';
        }

        // Check HIPAA applicability
        if ($this->is_hipaa_applicable($breach_data)) {
            $applicable[] = 'hipaa';
        }

        return $applicable;
    }

    /**
     * Check GDPR applicability
     *
     * @param array $breach_data Breach data.
     * @return bool Whether GDPR applies.
     */
    private function is_gdpr_applicable($breach_data) {
        return (
            isset($breach_data['jurisdictions']) &&
            in_array('eu', $breach_data['jurisdictions'], true)
        ) || (
            isset($breach_data['affected_users_location']) &&
            in_array('eu', $breach_data['affected_users_location'], true)
        );
    }

    /**
     * Check CCPA applicability
     *
     * @param array $breach_data Breach data.
     * @return bool Whether CCPA applies.
     */
    private function is_ccpa_applicable($breach_data) {
        return (
            isset($breach_data['jurisdictions']) &&
            in_array('california', $breach_data['jurisdictions'], true)
        ) || (
            isset($breach_data['affected_users_location']) &&
            in_array('california', $breach_data['affected_users_location'], true)
        );
    }

    /**
     * Check HIPAA applicability
     *
     * @param array $breach_data Breach data.
     * @return bool Whether HIPAA applies.
     */
    private function is_hipaa_applicable($breach_data) {
        return (
            isset($breach_data['data_types']) &&
            in_array('health', $breach_data['data_types'], true)
        ) || (
            isset($breach_data['entity_type']) &&
            in_array($breach_data['entity_type'], ['covered_entity', 'business_associate'], true)
        );
    }

    /**
     * Analyze framework requirements
     *
     * @param array $framework Framework definition.
     * @param array $breach_data Breach data.
     * @param array $risk_assessment Risk assessment results.
     * @return array Framework requirements.
     */
    private function analyze_framework_requirements($framework, $breach_data, $risk_assessment) {
        $requirements = [
            'name' => $framework['name'],
            'notifications' => [],
            'documentation' => [],
            'exceptions' => [],
        ];

        // Analyze authority notification requirements
        if ($framework['authority_notification']['required']) {
            $requirements['notifications']['authority'] = $this->analyze_authority_notification(
                $framework['authority_notification'],
                $breach_data,
                $risk_assessment
            );
        }

        // Analyze individual notification requirements
        if ($framework['individual_notification']['required']) {
            $requirements['notifications']['individual'] = $this->analyze_individual_notification(
                $framework['individual_notification'],
                $breach_data,
                $risk_assessment
            );
        }

        // Analyze documentation requirements
        if ($framework['documentation']['required']) {
            $requirements['documentation'] = $this->analyze_documentation_requirements(
                $framework['documentation'],
                $breach_data
            );
        }

        return $requirements;
    }

    /**
     * Analyze authority notification requirements
     *
     * @param array $requirements Authority notification requirements.
     * @param array $breach_data Breach data.
     * @param array $risk_assessment Risk assessment results.
     * @return array Authority notification analysis.
     */
    private function analyze_authority_notification($requirements, $breach_data, $risk_assessment) {
        $analysis = [
            'required' => true,
            'deadline' => $requirements['deadline'],
            'exceptions_met' => [],
        ];

        // Check exceptions
        if (isset($requirements['exceptions'])) {
            foreach ($requirements['exceptions'] as $key => $description) {
                if ($this->check_exception($key, $breach_data, $risk_assessment)) {
                    $analysis['exceptions_met'][$key] = $description;
                }
            }
        }

        // Update requirement if exceptions apply
        if (!empty($analysis['exceptions_met'])) {
            $analysis['required'] = false;
        }

        return $analysis;
    }

    /**
     * Analyze individual notification requirements
     *
     * @param array $requirements Individual notification requirements.
     * @param array $breach_data Breach data.
     * @param array $risk_assessment Risk assessment results.
     * @return array Individual notification analysis.
     */
    private function analyze_individual_notification($requirements, $breach_data, $risk_assessment) {
        $analysis = [
            'required' => true,
            'deadline' => $requirements['deadline'],
            'exceptions_met' => [],
        ];

        // Check threshold requirements if they exist
        if (isset($requirements['threshold'])) {
            $analysis['required'] = $this->check_notification_threshold(
                $requirements['threshold'],
                $breach_data,
                $risk_assessment
            );
        }

        // Check exceptions
        if (isset($requirements['exceptions'])) {
            foreach ($requirements['exceptions'] as $key => $description) {
                if ($this->check_exception($key, $breach_data, $risk_assessment)) {
                    $analysis['exceptions_met'][$key] = $description;
                }
            }
        }

        // Update requirement if exceptions apply
        if (!empty($analysis['exceptions_met'])) {
            $analysis['required'] = false;
        }

        return $analysis;
    }

    /**
     * Check notification threshold
     *
     * @param array $threshold Threshold requirements.
     * @param array $breach_data Breach data.
     * @param array $risk_assessment Risk assessment results.
     * @return bool Whether threshold is met.
     */
    private function check_notification_threshold($threshold, $breach_data, $risk_assessment) {
        foreach ($threshold as $key => $required) {
            switch ($key) {
                case 'unencrypted_pi':
                    if ($required && !isset($breach_data['data_encrypted'])) {
                        return true;
                    }
                    break;
                case 'unauthorized_access':
                    if ($required && $breach_data['breach_type'] === 'unauthorized_access') {
                        return true;
                    }
                    break;
            }
        }
        return false;
    }

    /**
     * Check exception applicability
     *
     * @param string $exception Exception key.
     * @param array  $breach_data Breach data.
     * @param array  $risk_assessment Risk assessment results.
     * @return bool Whether exception applies.
     */
    private function check_exception($exception, $breach_data, $risk_assessment) {
        switch ($exception) {
            case 'no_risk':
                return $risk_assessment['severity'] === 'low';
            case 'encrypted':
                return isset($breach_data['data_encrypted']) && $breach_data['data_encrypted'];
            case 'mitigated':
                return isset($breach_data['risk_mitigated']) && $breach_data['risk_mitigated'];
            case 'low_risk':
                return $risk_assessment['severity'] === 'low';
            default:
                return false;
        }
    }

    /**
     * Generate requirement summary
     *
     * @param array $requirements Framework requirements.
     * @return array Requirement summary.
     */
    private function generate_requirement_summary($requirements) {
        $summary = [
            'authority_notification' => false,
            'individual_notification' => false,
            'shortest_deadline' => null,
            'documentation_required' => false,
            'applicable_frameworks' => [],
        ];

        foreach ($requirements as $framework_id => $framework) {
            $summary['applicable_frameworks'][] = $framework['name'];

            // Check notifications
            if (isset($framework['notifications']['authority']['required']) 
                && $framework['notifications']['authority']['required']
            ) {
                $summary['authority_notification'] = true;
            }

            if (isset($framework['notifications']['individual']['required']) 
                && $framework['notifications']['individual']['required']
            ) {
                $summary['individual_notification'] = true;
            }

            // Track shortest deadline
            if (isset($framework['notifications']['authority']['deadline'])) {
                $deadline = $framework['notifications']['authority']['deadline'];
                if (is_numeric($deadline) && 
                    (!$summary['shortest_deadline'] || $deadline < $summary['shortest_deadline'])
                ) {
                    $summary['shortest_deadline'] = $deadline;
                }
            }

            // Check documentation requirements
            if (!empty($framework['documentation'])) {
                $summary['documentation_required'] = true;
            }
        }

        return $summary;
    }

    /**
     * Calculate compliance deadlines
     *
     * @param array  $requirements Framework requirements.
     * @param string $detection_date Detection date.
     * @return array Compliance deadlines.
     */
    private function calculate_compliance_deadlines($requirements, $detection_date) {
        $detection_timestamp = strtotime($detection_date);
        $deadlines = [];

        foreach ($requirements as $framework_id => $framework) {
            $deadlines[$framework_id] = [];

            // Authority notification deadline
            if (isset($framework['notifications']['authority']['deadline'])) {
                $deadline = $framework['notifications']['authority']['deadline'];
                if (is_numeric($deadline)) {
                    $deadlines[$framework_id]['authority'] = date(
                        'Y-m-d H:i:s',
                        $detection_timestamp + ($deadline * HOUR_IN_SECONDS)
                    );
                }
            }

            // Individual notification deadline
            if (isset($framework['notifications']['individual']['deadline'])) {
                $deadline = $framework['notifications']['individual']['deadline'];
                if (is_numeric($deadline)) {
                    $deadlines[$framework_id]['individual'] = date(
                        'Y-m-d H:i:s',
                        $detection_timestamp + ($deadline * HOUR_IN_SECONDS)
                    );
                }
            }
        }

        return $deadlines;
    }

    /**
     * Compile documentation requirements
     *
     * @param array $requirements Framework requirements.
     * @return array Documentation requirements.
     */
    private function compile_documentation_requirements($requirements) {
        $documentation = [
            'required_elements' => [],
            'retention_periods' => [],
        ];

        foreach ($requirements as $framework_id => $framework) {
            if (isset($framework['documentation']['elements'])) {
                $documentation['required_elements'] = array_merge(
                    $documentation['required_elements'],
                    $framework['documentation']['elements']
                );
            }

            if (isset($framework['documentation']['retention'])) {
                $documentation['retention_periods'][$framework_id] = $framework['documentation']['retention'];
            }
        }

        // Remove duplicates
        $documentation['required_elements'] = array_unique($documentation['required_elements']);

        return $documentation;
    }
}
