<?php
/**
 * Risk Assessor Class
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
 * Risk Assessor class
 */
class Risk_Assessor {
    /**
     * Data sensitivity weights
     *
     * @var array
     */
    private $data_sensitivity_weights = [
        'health' => 100,        // Health/medical data
        'financial' => 90,      // Financial data (credit cards, bank accounts)
        'identity' => 85,       // Government IDs, SSN
        'credentials' => 80,    // Login credentials
        'contact' => 60,        // Contact information
        'personal' => 70,       // Personal details (DOB, address)
        'behavioral' => 50,     // Behavioral data
        'device' => 40,        // Device information
        'metadata' => 30,      // Metadata
        'public' => 10,        // Publicly available information
    ];

    /**
     * Breach type weights
     *
     * @var array
     */
    private $breach_type_weights = [
        'unauthorized_access' => 100,  // Unauthorized access
        'ransomware' => 95,           // Ransomware attack
        'data_exfiltration' => 90,    // Data theft
        'system_compromise' => 85,     // System compromise
        'insider_threat' => 80,        // Insider threat
        'accidental_disclosure' => 70, // Accidental disclosure
        'lost_device' => 65,          // Lost/stolen device
        'misconfiguration' => 60,      // System misconfiguration
    ];

    /**
     * Geographic scope weights
     *
     * @var array
     */
    private $geographic_weights = [
        'global' => 100,      // Global impact
        'multi_region' => 90, // Multiple regions
        'regional' => 80,     // Single region (e.g., EU)
        'national' => 70,     // Single country
        'local' => 50,       // Local area
    ];

    /**
     * Calculate risk score
     *
     * @param array $breach_data Breach data.
     * @return array Risk assessment results.
     */
    public function calculate_risk_score($breach_data) {
        $base_score = 0;
        $factors = [];

        // Calculate data sensitivity score
        $sensitivity_score = $this->calculate_data_sensitivity_score($breach_data['affected_data']);
        $base_score += $sensitivity_score;
        $factors['data_sensitivity'] = [
            'score' => $sensitivity_score,
            'weight' => 0.35, // 35% weight
            'details' => $this->get_data_sensitivity_details($breach_data['affected_data']),
        ];

        // Calculate breach type score
        $breach_type_score = $this->get_breach_type_score($breach_data['breach_type']);
        $base_score += $breach_type_score;
        $factors['breach_type'] = [
            'score' => $breach_type_score,
            'weight' => 0.25, // 25% weight
            'details' => $this->breach_type_weights[$breach_data['breach_type']],
        ];

        // Calculate scope score
        $scope_score = $this->calculate_scope_score($breach_data);
        $base_score += $scope_score;
        $factors['scope'] = [
            'score' => $scope_score,
            'weight' => 0.20, // 20% weight
            'details' => $this->get_scope_details($breach_data),
        ];

        // Calculate impact score
        $impact_score = $this->calculate_impact_score($breach_data);
        $base_score += $impact_score;
        $factors['impact'] = [
            'score' => $impact_score,
            'weight' => 0.20, // 20% weight
            'details' => $this->get_impact_details($breach_data),
        ];

        // Calculate final weighted score
        $final_score = $this->calculate_final_score($factors);

        // Determine severity level
        $severity = $this->determine_severity($final_score);

        // Generate notification requirements
        $notification_requirements = $this->determine_notification_requirements($severity, $factors);

        return [
            'score' => $final_score,
            'severity' => $severity,
            'factors' => $factors,
            'notification_requirements' => $notification_requirements,
            'recommendations' => $this->generate_recommendations($severity, $factors),
            'deadlines' => $this->calculate_deadlines($severity, $breach_data['detection_date']),
        ];
    }

    /**
     * Calculate data sensitivity score
     *
     * @param array $affected_data Affected data types.
     * @return float Sensitivity score.
     */
    private function calculate_data_sensitivity_score($affected_data) {
        $max_score = 0;
        $total_score = 0;

        foreach ($affected_data as $data_type) {
            if (isset($this->data_sensitivity_weights[$data_type])) {
                $total_score += $this->data_sensitivity_weights[$data_type];
                $max_score = max($max_score, $this->data_sensitivity_weights[$data_type]);
            }
        }

        // Use the highest individual score as a base and add 10% of the total for each additional type
        return $max_score + (($total_score - $max_score) * 0.1);
    }

    /**
     * Get data sensitivity details
     *
     * @param array $affected_data Affected data types.
     * @return array Sensitivity details.
     */
    private function get_data_sensitivity_details($affected_data) {
        $details = [];
        foreach ($affected_data as $data_type) {
            if (isset($this->data_sensitivity_weights[$data_type])) {
                $details[$data_type] = $this->data_sensitivity_weights[$data_type];
            }
        }
        return $details;
    }

    /**
     * Get breach type score
     *
     * @param string $breach_type Breach type.
     * @return int Breach type score.
     */
    private function get_breach_type_score($breach_type) {
        return isset($this->breach_type_weights[$breach_type]) 
            ? $this->breach_type_weights[$breach_type] 
            : 50; // Default score for unknown breach types
    }

    /**
     * Calculate scope score
     *
     * @param array $breach_data Breach data.
     * @return float Scope score.
     */
    private function calculate_scope_score($breach_data) {
        $geographic_score = isset($this->geographic_weights[$breach_data['geographic_scope']])
            ? $this->geographic_weights[$breach_data['geographic_scope']]
            : 50;

        $affected_users_count = count($breach_data['affected_users']);
        $user_scale_score = $this->calculate_user_scale_score($affected_users_count);

        return ($geographic_score + $user_scale_score) / 2;
    }

    /**
     * Calculate user scale score
     *
     * @param int $affected_users_count Number of affected users.
     * @return float User scale score.
     */
    private function calculate_user_scale_score($affected_users_count) {
        if ($affected_users_count >= 1000000) return 100;  // 1M+ users
        if ($affected_users_count >= 100000) return 90;    // 100K+ users
        if ($affected_users_count >= 10000) return 80;     // 10K+ users
        if ($affected_users_count >= 1000) return 70;      // 1K+ users
        if ($affected_users_count >= 100) return 60;       // 100+ users
        return 50;                                         // < 100 users
    }

    /**
     * Calculate impact score
     *
     * @param array $breach_data Breach data.
     * @return float Impact score.
     */
    private function calculate_impact_score($breach_data) {
        $impact_factors = [
            'financial_impact' => isset($breach_data['financial_impact']) ? $breach_data['financial_impact'] : 50,
            'reputation_impact' => isset($breach_data['reputation_impact']) ? $breach_data['reputation_impact'] : 50,
            'operational_impact' => isset($breach_data['operational_impact']) ? $breach_data['operational_impact'] : 50,
        ];

        return array_sum($impact_factors) / count($impact_factors);
    }

    /**
     * Calculate final score
     *
     * @param array $factors Risk factors.
     * @return float Final risk score.
     */
    private function calculate_final_score($factors) {
        $weighted_score = 0;
        foreach ($factors as $factor) {
            $weighted_score += $factor['score'] * $factor['weight'];
        }
        return round($weighted_score, 2);
    }

    /**
     * Determine severity level
     *
     * @param float $score Risk score.
     * @return string Severity level.
     */
    private function determine_severity($score) {
        if ($score >= 90) return 'critical';
        if ($score >= 75) return 'high';
        if ($score >= 50) return 'medium';
        return 'low';
    }

    /**
     * Determine notification requirements
     *
     * @param string $severity Severity level.
     * @param array  $factors Risk factors.
     * @return array Notification requirements.
     */
    private function determine_notification_requirements($severity, $factors) {
        $requirements = [
            'authority_notification' => false,
            'individual_notification' => false,
            'vendor_notification' => false,
            'insurance_notification' => false,
            'legal_consultation' => false,
        ];

        // Authority notification required for high/critical severity or sensitive data
        if ($severity === 'critical' || $severity === 'high' || $factors['data_sensitivity']['score'] >= 80) {
            $requirements['authority_notification'] = true;
        }

        // Individual notification based on severity and data sensitivity
        if ($severity === 'critical' || $factors['data_sensitivity']['score'] >= 70) {
            $requirements['individual_notification'] = true;
        }

        // Vendor notification if breach type suggests system compromise
        if ($factors['breach_type']['score'] >= 80) {
            $requirements['vendor_notification'] = true;
        }

        // Insurance notification for high impact or critical severity
        if ($severity === 'critical' || $factors['impact']['score'] >= 80) {
            $requirements['insurance_notification'] = true;
        }

        // Legal consultation for critical severity or high data sensitivity
        if ($severity === 'critical' || $factors['data_sensitivity']['score'] >= 85) {
            $requirements['legal_consultation'] = true;
        }

        return $requirements;
    }

    /**
     * Generate recommendations
     *
     * @param string $severity Severity level.
     * @param array  $factors Risk factors.
     * @return array Recommendations.
     */
    private function generate_recommendations($severity, $factors) {
        $recommendations = [
            'immediate_actions' => [],
            'mitigation_steps' => [],
            'prevention_measures' => [],
        ];

        // Immediate actions based on severity
        if ($severity === 'critical' || $severity === 'high') {
            $recommendations['immediate_actions'] = [
                'Establish incident response team immediately',
                'Initiate forensic investigation',
                'Implement containment measures',
                'Prepare external communications',
                'Contact legal counsel',
            ];
        } else {
            $recommendations['immediate_actions'] = [
                'Document incident details',
                'Assess affected systems',
                'Monitor for suspicious activity',
            ];
        }

        // Mitigation steps based on breach type
        if ($factors['breach_type']['score'] >= 80) {
            $recommendations['mitigation_steps'] = [
                'Isolate affected systems',
                'Reset all credentials',
                'Review access controls',
                'Update security configurations',
            ];
        }

        // Prevention measures based on impact
        if ($factors['impact']['score'] >= 70) {
            $recommendations['prevention_measures'] = [
                'Enhance monitoring systems',
                'Update security policies',
                'Conduct security training',
                'Implement additional controls',
            ];
        }

        return $recommendations;
    }

    /**
     * Calculate notification deadlines
     *
     * @param string $severity      Severity level.
     * @param string $detection_date Detection date.
     * @return array Deadlines.
     */
    private function calculate_deadlines($severity, $detection_date) {
        $detection_timestamp = strtotime($detection_date);
        $deadlines = [];

        // Authority notification deadline (72 hours for high/critical)
        if ($severity === 'critical' || $severity === 'high') {
            $deadlines['authority_notification'] = date('Y-m-d H:i:s', $detection_timestamp + (72 * HOUR_IN_SECONDS));
        } else {
            $deadlines['authority_notification'] = date('Y-m-d H:i:s', $detection_timestamp + (5 * DAY_IN_SECONDS));
        }

        // Individual notification deadline
        $deadlines['individual_notification'] = date('Y-m-d H:i:s', $detection_timestamp + (7 * DAY_IN_SECONDS));

        // Other deadlines
        $deadlines['documentation_completion'] = date('Y-m-d H:i:s', $detection_timestamp + (30 * DAY_IN_SECONDS));
        $deadlines['review_completion'] = date('Y-m-d H:i:s', $detection_timestamp + (60 * DAY_IN_SECONDS));

        return $deadlines;
    }
}
