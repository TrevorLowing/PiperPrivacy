<?php
/**
 * Compliance Checker Class
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
 * Compliance Checker class
 */
class Compliance_Checker {
    /**
     * Compliance frameworks
     *
     * @var array
     */
    private $frameworks = [
        'gdpr' => [
            'name' => 'GDPR',
            'description' => 'General Data Protection Regulation',
            'notification_threshold' => 72, // hours
            'risk_based' => true,
            'exceptions' => [
                'encrypted_data' => 'Data was encrypted',
                'no_risk' => 'No risk to individuals',
                'measures_taken' => 'Subsequent measures eliminate risk',
            ],
            'documentation' => [
                'required' => [
                    'breach_details',
                    'risk_assessment',
                    'notification_records',
                    'remediation_steps',
                ],
                'retention' => '5 years',
            ],
        ],
        'ccpa' => [
            'name' => 'CCPA',
            'description' => 'California Consumer Privacy Act',
            'notification_threshold' => null, // No specific timeframe
            'risk_based' => false,
            'exceptions' => [],
            'documentation' => [
                'required' => [
                    'breach_details',
                    'notification_records',
                    'remediation_steps',
                ],
                'retention' => '2 years',
            ],
        ],
        'hipaa' => [
            'name' => 'HIPAA',
            'description' => 'Health Insurance Portability and Accountability Act',
            'notification_threshold' => 60, // days
            'risk_based' => false,
            'exceptions' => [
                'low_risk' => 'Low probability of data compromise',
                'encrypted_data' => 'Data was encrypted',
            ],
            'documentation' => [
                'required' => [
                    'breach_details',
                    'risk_assessment',
                    'notification_records',
                    'remediation_steps',
                    'training_records',
                ],
                'retention' => '6 years',
            ],
        ],
    ];

    /**
     * Initialize the compliance checker
     */
    public function __construct() {
        add_action('wp_ajax_pp_check_compliance', [$this, 'ajax_check_compliance']);
        add_action('pp_breach_updated', [$this, 'schedule_compliance_check']);
        add_action('pp_run_compliance_check', [$this, 'run_scheduled_check']);
    }

    /**
     * Handle compliance check via AJAX
     */
    public function ajax_check_compliance() {
        check_ajax_referer('pp_check_compliance', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $breach_id = isset($_POST['breach_id']) ? intval($_POST['breach_id']) : 0;
        if (!$breach_id) {
            wp_send_json_error('Invalid breach ID');
        }

        $result = $this->check_compliance($breach_id);
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        wp_send_json_success($result);
    }

    /**
     * Schedule compliance check
     *
     * @param int $breach_id Breach ID.
     */
    public function schedule_compliance_check($breach_id) {
        wp_schedule_single_event(
            time() + HOUR_IN_SECONDS,
            'pp_run_compliance_check',
            [$breach_id]
        );
    }

    /**
     * Run scheduled compliance check
     *
     * @param int $breach_id Breach ID.
     */
    public function run_scheduled_check($breach_id) {
        $result = $this->check_compliance($breach_id);
        if (!is_wp_error($result)) {
            update_post_meta($breach_id, '_pp_compliance_analysis', $result);
        }
    }

    /**
     * Check compliance for a breach
     *
     * @param int $breach_id Breach ID.
     * @return array|WP_Error Compliance analysis or error.
     */
    public function check_compliance($breach_id) {
        $breach = get_post($breach_id);
        if (!$breach || $breach->post_type !== 'pp_breach') {
            return new \WP_Error('invalid_breach', __('Invalid breach', 'piper-privacy'));
        }

        $risk_assessment = get_post_meta($breach_id, '_pp_risk_assessment', true);
        if (!$risk_assessment) {
            return new \WP_Error('no_risk_assessment', __('Risk assessment required', 'piper-privacy'));
        }

        $result = [
            'frameworks' => [],
            'summary' => [
                'authority_notification' => false,
                'individual_notification' => false,
                'shortest_deadline' => null,
            ],
        ];

        foreach ($this->frameworks as $framework_id => $framework) {
            $analysis = $this->analyze_framework_compliance(
                $framework_id,
                $framework,
                $breach,
                $risk_assessment
            );

            $result['frameworks'][$framework_id] = array_merge(
                ['name' => $framework['name']],
                $analysis
            );

            // Update summary
            if ($analysis['notifications']['authority']['required']) {
                $result['summary']['authority_notification'] = true;
            }
            if ($analysis['notifications']['individual']['required']) {
                $result['summary']['individual_notification'] = true;
            }

            // Track shortest deadline
            foreach ($analysis['notifications'] as $notification) {
                if ($notification['required'] && $notification['deadline']) {
                    if (!$result['summary']['shortest_deadline'] ||
                        strtotime($notification['deadline']) < strtotime($result['summary']['shortest_deadline'])) {
                        $result['summary']['shortest_deadline'] = $notification['deadline'];
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Analyze framework compliance
     *
     * @param string  $framework_id     Framework ID.
     * @param array   $framework        Framework configuration.
     * @param WP_Post $breach          Breach post object.
     * @param array   $risk_assessment Risk assessment data.
     * @return array Compliance analysis.
     */
    private function analyze_framework_compliance($framework_id, $framework, $breach, $risk_assessment) {
        $result = [
            'notifications' => [
                'authority' => [
                    'required' => false,
                    'deadline' => null,
                    'exceptions_met' => [],
                ],
                'individual' => [
                    'required' => false,
                    'deadline' => null,
                    'exceptions_met' => [],
                ],
            ],
            'documentation' => [
                'elements' => $framework['documentation']['required'],
                'retention' => $framework['documentation']['retention'],
            ],
        ];

        // Check if framework applies based on data types
        if (!$this->framework_applies($framework_id, $breach)) {
            return $result;
        }

        // Determine notification requirements
        $notification_required = $framework['risk_based']
            ? $risk_assessment['severity'] !== 'LOW'
            : true;

        if ($notification_required) {
            // Check for exceptions
            $exceptions_met = $this->check_exceptions($framework['exceptions'], $breach, $risk_assessment);
            
            if (empty($exceptions_met)) {
                // Set notification requirements and deadlines
                $discovery_date = get_post_meta($breach->ID, '_pp_date_discovered', true);
                if ($discovery_date) {
                    $deadline = $this->calculate_deadline(
                        $discovery_date,
                        $framework['notification_threshold']
                    );

                    $result['notifications']['authority']['required'] = true;
                    $result['notifications']['authority']['deadline'] = $deadline;

                    $result['notifications']['individual']['required'] = true;
                    $result['notifications']['individual']['deadline'] = $deadline;
                }
            } else {
                // Store met exceptions
                $result['notifications']['authority']['exceptions_met'] = $exceptions_met;
                $result['notifications']['individual']['exceptions_met'] = $exceptions_met;
            }
        }

        return $result;
    }

    /**
     * Check if framework applies to breach
     *
     * @param string  $framework_id Framework ID.
     * @param WP_Post $breach      Breach post object.
     * @return bool Whether framework applies.
     */
    private function framework_applies($framework_id, $breach) {
        $data_types = get_post_meta($breach->ID, '_pp_data_types', true);
        if (!$data_types) {
            return false;
        }

        switch ($framework_id) {
            case 'gdpr':
                return $this->has_eu_data($data_types);
            case 'ccpa':
                return $this->has_california_data($data_types);
            case 'hipaa':
                return $this->has_health_data($data_types);
            default:
                return false;
        }
    }

    /**
     * Check framework exceptions
     *
     * @param array   $exceptions      Framework exceptions.
     * @param WP_Post $breach         Breach post object.
     * @param array   $risk_assessment Risk assessment data.
     * @return array Met exceptions.
     */
    private function check_exceptions($exceptions, $breach, $risk_assessment) {
        $met_exceptions = [];

        foreach ($exceptions as $id => $description) {
            switch ($id) {
                case 'encrypted_data':
                    if (get_post_meta($breach->ID, '_pp_data_encrypted', true)) {
                        $met_exceptions[] = $description;
                    }
                    break;
                case 'no_risk':
                case 'low_risk':
                    if ($risk_assessment['severity'] === 'LOW') {
                        $met_exceptions[] = $description;
                    }
                    break;
                case 'measures_taken':
                    if (get_post_meta($breach->ID, '_pp_remediation_complete', true)) {
                        $met_exceptions[] = $description;
                    }
                    break;
            }
        }

        return $met_exceptions;
    }

    /**
     * Calculate notification deadline
     *
     * @param string    $discovery_date       Discovery date.
     * @param int|null  $notification_threshold Notification threshold in hours.
     * @return string|null Deadline date or null if no threshold.
     */
    private function calculate_deadline($discovery_date, $notification_threshold) {
        if (!$notification_threshold) {
            return null;
        }

        return date(
            'Y-m-d H:i:s',
            strtotime($discovery_date) + ($notification_threshold * HOUR_IN_SECONDS)
        );
    }

    /**
     * Check if breach involves EU data
     *
     * @param array $data_types Data types.
     * @return bool Whether breach involves EU data.
     */
    private function has_eu_data($data_types) {
        $eu_types = ['eu_personal_data', 'eu_sensitive_data'];
        return count(array_intersect($data_types, $eu_types)) > 0;
    }

    /**
     * Check if breach involves California data
     *
     * @param array $data_types Data types.
     * @return bool Whether breach involves California data.
     */
    private function has_california_data($data_types) {
        $ca_types = ['ca_personal_data', 'ca_sensitive_data'];
        return count(array_intersect($data_types, $ca_types)) > 0;
    }

    /**
     * Check if breach involves health data
     *
     * @param array $data_types Data types.
     * @return bool Whether breach involves health data.
     */
    private function has_health_data($data_types) {
        $health_types = ['health_data', 'medical_records'];
        return count(array_intersect($data_types, $health_types)) > 0;
    }
}
