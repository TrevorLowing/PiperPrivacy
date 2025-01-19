<?php
/**
 * Field Validation Functions
 * 
 * @package PiperPrivacy
 * @subpackage PiperPrivacy/includes/helpers
 */

if (!defined('WPINC')) {
    die;
}

/**
 * Validate email field
 *
 * @param string $value Email value to validate
 * @return array Validation result
 */
function pp_validate_email_field($value) {
    $result = [
        'valid' => false,
        'message' => ''
    ];

    if (empty($value)) {
        $result['message'] = __('Email address is required.', 'piper-privacy');
        return $result;
    }

    if (!is_email($value)) {
        $result['message'] = __('Invalid email address format.', 'piper-privacy');
        return $result;
    }

    $result['valid'] = true;
    return $result;
}

/**
 * Validate URL field
 *
 * @param string $value URL to validate
 * @param array  $args  Optional arguments (allowed_protocols, require_scheme)
 * @return array Validation result
 */
function pp_validate_url_field($value, $args = []) {
    $result = [
        'valid' => false,
        'message' => ''
    ];

    if (empty($value)) {
        $result['message'] = __('URL is required.', 'piper-privacy');
        return $result;
    }

    $defaults = [
        'allowed_protocols' => ['http', 'https'],
        'require_scheme' => true
    ];
    $args = wp_parse_args($args, $defaults);

    if (!wp_http_validate_url($value)) {
        $result['message'] = __('Invalid URL format.', 'piper-privacy');
        return $result;
    }

    $scheme = parse_url($value, PHP_URL_SCHEME);
    if ($args['require_scheme'] && !$scheme) {
        $result['message'] = __('URL must include http:// or https://.', 'piper-privacy');
        return $result;
    }

    if ($scheme && !in_array($scheme, $args['allowed_protocols'])) {
        $result['message'] = sprintf(
            __('URL must use one of the following protocols: %s.', 'piper-privacy'),
            implode(', ', $args['allowed_protocols'])
        );
        return $result;
    }

    $result['valid'] = true;
    return $result;
}

/**
 * Validate date field
 *
 * @param string $value Date value to validate
 * @param array  $args  Optional arguments (format, min_date, max_date)
 * @return array Validation result
 */
function pp_validate_date_field($value, $args = []) {
    $result = [
        'valid' => false,
        'message' => ''
    ];

    if (empty($value)) {
        $result['message'] = __('Date is required.', 'piper-privacy');
        return $result;
    }

    $defaults = [
        'format' => 'Y-m-d',
        'min_date' => '',
        'max_date' => ''
    ];
    $args = wp_parse_args($args, $defaults);

    $date = DateTime::createFromFormat($args['format'], $value);
    if (!$date || $date->format($args['format']) !== $value) {
        $result['message'] = sprintf(
            __('Invalid date format. Please use %s format.', 'piper-privacy'),
            $args['format']
        );
        return $result;
    }

    if (!empty($args['min_date'])) {
        $min_date = new DateTime($args['min_date']);
        if ($date < $min_date) {
            $result['message'] = sprintf(
                __('Date must be after %s.', 'piper-privacy'),
                $min_date->format($args['format'])
            );
            return $result;
        }
    }

    if (!empty($args['max_date'])) {
        $max_date = new DateTime($args['max_date']);
        if ($date > $max_date) {
            $result['message'] = sprintf(
                __('Date must be before %s.', 'piper-privacy'),
                $max_date->format($args['format'])
            );
            return $result;
        }
    }

    $result['valid'] = true;
    return $result;
}

/**
 * Validate color field
 *
 * @param string $value Color value to validate
 * @return array Validation result
 */
function pp_validate_color_field($value) {
    $result = [
        'valid' => false,
        'message' => ''
    ];

    if (empty($value)) {
        $result['message'] = __('Color is required.', 'piper-privacy');
        return $result;
    }

    if (!preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $value)) {
        $result['message'] = __('Invalid color format. Please use hexadecimal format (e.g., #FF0000).', 'piper-privacy');
        return $result;
    }

    $result['valid'] = true;
    return $result;
}

/**
 * Validate range field
 *
 * @param mixed $value Value to validate
 * @param array $args  Optional arguments (min, max, step)
 * @return array Validation result
 */
function pp_validate_range_field($value, $args = []) {
    $result = [
        'valid' => false,
        'message' => ''
    ];

    if (!is_numeric($value)) {
        $result['message'] = __('Value must be a number.', 'piper-privacy');
        return $result;
    }

    $defaults = [
        'min' => 0,
        'max' => 100,
        'step' => 1
    ];
    $args = wp_parse_args($args, $defaults);

    if ($value < $args['min'] || $value > $args['max']) {
        $result['message'] = sprintf(
            __('Value must be between %s and %s.', 'piper-privacy'),
            $args['min'],
            $args['max']
        );
        return $result;
    }

    // Check if value matches step
    $steps = ($value - $args['min']) / $args['step'];
    if (!is_int($steps)) {
        $result['message'] = sprintf(
            __('Value must be in increments of %s.', 'piper-privacy'),
            $args['step']
        );
        return $result;
    }

    $result['valid'] = true;
    return $result;
}

/**
 * Validate map coordinates
 *
 * @param array $value Map coordinates to validate
 * @return array Validation result
 */
function pp_validate_map_field($value) {
    $result = [
        'valid' => false,
        'message' => ''
    ];

    if (!is_array($value)) {
        $result['message'] = __('Invalid map coordinates format.', 'piper-privacy');
        return $result;
    }

    if (!isset($value['latitude']) || !isset($value['longitude'])) {
        $result['message'] = __('Both latitude and longitude are required.', 'piper-privacy');
        return $result;
    }

    $lat = $value['latitude'];
    $lng = $value['longitude'];

    if (!is_numeric($lat) || $lat < -90 || $lat > 90) {
        $result['message'] = __('Invalid latitude. Must be between -90 and 90.', 'piper-privacy');
        return $result;
    }

    if (!is_numeric($lng) || $lng < -180 || $lng > 180) {
        $result['message'] = __('Invalid longitude. Must be between -180 and 180.', 'piper-privacy');
        return $result;
    }

    $result['valid'] = true;
    return $result;
}

/**
 * Validate file field
 *
 * @param array $value File information to validate
 * @param array $args  Optional arguments (allowed_types, max_size)
 * @return array Validation result
 */
function pp_validate_file_field($value, $args = []) {
    $result = [
        'valid' => false,
        'message' => ''
    ];

    if (empty($value) || !is_array($value)) {
        $result['message'] = __('No file uploaded.', 'piper-privacy');
        return $result;
    }

    $defaults = [
        'allowed_types' => ['pdf', 'doc', 'docx'],
        'max_size' => 5 * 1024 * 1024 // 5MB
    ];
    $args = wp_parse_args($args, $defaults);

    // Check file type
    $file_type = wp_check_filetype(basename($value['name']));
    if (!in_array($file_type['ext'], $args['allowed_types'])) {
        $result['message'] = sprintf(
            __('Invalid file type. Allowed types: %s.', 'piper-privacy'),
            implode(', ', $args['allowed_types'])
        );
        return $result;
    }

    // Check file size
    if ($value['size'] > $args['max_size']) {
        $result['message'] = sprintf(
            __('File size exceeds maximum limit of %s MB.', 'piper-privacy'),
            $args['max_size'] / (1024 * 1024)
        );
        return $result;
    }

    $result['valid'] = true;
    return $result;
}

/**
 * Validate key-value pairs
 *
 * @param array $value Key-value pairs to validate
 * @param array $args  Optional arguments (required_keys, max_pairs)
 * @return array Validation result
 */
function pp_validate_key_value_field($value, $args = []) {
    $result = [
        'valid' => false,
        'message' => ''
    ];

    if (!is_array($value)) {
        $result['message'] = __('Invalid key-value format.', 'piper-privacy');
        return $result;
    }

    $defaults = [
        'required_keys' => [],
        'max_pairs' => 0
    ];
    $args = wp_parse_args($args, $defaults);

    // Check required keys
    foreach ($args['required_keys'] as $key) {
        if (!isset($value[$key]) || empty($value[$key])) {
            $result['message'] = sprintf(
                __('Missing required key: %s.', 'piper-privacy'),
                $key
            );
            return $result;
        }
    }

    // Check maximum pairs
    if ($args['max_pairs'] > 0 && count($value) > $args['max_pairs']) {
        $result['message'] = sprintf(
            __('Maximum number of pairs (%d) exceeded.', 'piper-privacy'),
            $args['max_pairs']
        );
        return $result;
    }

    $result['valid'] = true;
    return $result;
}

/**
 * Validate currency value
 *
 * @param mixed $value   Value to validate
 * @param array $args    Optional arguments (min, max, decimals)
 * @return array Validation result
 */
function pp_validate_currency_field($value, $args = []) {
    $result = [
        'valid' => false,
        'message' => ''
    ];

    if (!is_numeric($value)) {
        $result['message'] = __('Currency value must be a number.', 'piper-privacy');
        return $result;
    }

    $defaults = [
        'min' => 0,
        'max' => PHP_FLOAT_MAX,
        'decimals' => 2
    ];
    $args = wp_parse_args($args, $defaults);

    if ($value < $args['min']) {
        $result['message'] = sprintf(
            __('Value must be at least %s.', 'piper-privacy'),
            number_format($args['min'], $args['decimals'])
        );
        return $result;
    }

    if ($value > $args['max']) {
        $result['message'] = sprintf(
            __('Value must not exceed %s.', 'piper-privacy'),
            number_format($args['max'], $args['decimals'])
        );
        return $result;
    }

    // Check decimal places
    $decimal_places = strlen(substr(strrchr((string)$value, "."), 1));
    if ($decimal_places > $args['decimals']) {
        $result['message'] = sprintf(
            __('Value must not have more than %d decimal places.', 'piper-privacy'),
            $args['decimals']
        );
        return $result;
    }

    $result['valid'] = true;
    return $result;
}

/**
 * Validate data retention period
 *
 * @param string|int $value Period value
 * @param array     $args  Optional arguments (min_months, max_months, allow_indefinite)
 * @return array Validation result
 */
function pp_validate_retention_period($value, $args = []) {
    $result = [
        'valid' => false,
        'message' => ''
    ];

    $defaults = [
        'min_months' => 1,
        'max_months' => 120, // 10 years
        'allow_indefinite' => false
    ];
    $args = wp_parse_args($args, $defaults);

    if ($args['allow_indefinite'] && $value === 'indefinite') {
        $result['valid'] = true;
        return $result;
    }

    if (!is_numeric($value)) {
        $result['message'] = __('Retention period must be a number of months.', 'piper-privacy');
        return $result;
    }

    $months = intval($value);
    if ($months < $args['min_months']) {
        $result['message'] = sprintf(
            __('Retention period must be at least %d months.', 'piper-privacy'),
            $args['min_months']
        );
        return $result;
    }

    if ($months > $args['max_months']) {
        $result['message'] = sprintf(
            __('Retention period cannot exceed %d months.', 'piper-privacy'),
            $args['max_months']
        );
        return $result;
    }

    $result['valid'] = true;
    return $result;
}

/**
 * Validate data classification level
 *
 * @param string $value Classification level
 * @param array  $args  Optional arguments (allowed_levels)
 * @return array Validation result
 */
function pp_validate_data_classification($value, $args = []) {
    $result = [
        'valid' => false,
        'message' => ''
    ];

    $defaults = [
        'allowed_levels' => [
            'public',
            'internal',
            'confidential',
            'restricted',
            'secret'
        ]
    ];
    $args = wp_parse_args($args, $defaults);

    if (!in_array($value, $args['allowed_levels'])) {
        $result['message'] = sprintf(
            __('Invalid classification level. Allowed levels: %s.', 'piper-privacy'),
            implode(', ', $args['allowed_levels'])
        );
        return $result;
    }

    $result['valid'] = true;
    return $result;
}

/**
 * Validate data elements list
 *
 * @param array $value List of data elements
 * @param array $args  Optional arguments (required_categories, max_elements)
 * @return array Validation result
 */
function pp_validate_data_elements($value, $args = []) {
    $result = [
        'valid' => false,
        'message' => ''
    ];

    if (!is_array($value)) {
        $result['message'] = __('Data elements must be provided as a list.', 'piper-privacy');
        return $result;
    }

    $defaults = [
        'required_categories' => ['pii', 'purpose', 'source'],
        'max_elements' => 50
    ];
    $args = wp_parse_args($args, $defaults);

    if (count($value) > $args['max_elements']) {
        $result['message'] = sprintf(
            __('Number of data elements cannot exceed %d.', 'piper-privacy'),
            $args['max_elements']
        );
        return $result;
    }

    // Check required categories
    foreach ($args['required_categories'] as $category) {
        $found = false;
        foreach ($value as $element) {
            if (isset($element['category']) && $element['category'] === $category) {
                $found = true;
                break;
            }
        }
        if (!$found) {
            $result['message'] = sprintf(
                __('Missing required data element category: %s.', 'piper-privacy'),
                $category
            );
            return $result;
        }
    }

    $result['valid'] = true;
    return $result;
}

/**
 * Validate risk assessment
 *
 * @param array $value Risk assessment data
 * @param array $args  Optional arguments (required_fields, risk_levels)
 * @return array Validation result
 */
function pp_validate_risk_assessment($value, $args = []) {
    $result = [
        'valid' => false,
        'message' => ''
    ];

    if (!is_array($value)) {
        $result['message'] = __('Risk assessment must be provided as an array.', 'piper-privacy');
        return $result;
    }

    $defaults = [
        'required_fields' => ['risk_type', 'likelihood', 'impact', 'mitigation'],
        'risk_levels' => ['low', 'medium', 'high', 'critical']
    ];
    $args = wp_parse_args($args, $defaults);

    foreach ($args['required_fields'] as $field) {
        if (!isset($value[$field]) || empty($value[$field])) {
            $result['message'] = sprintf(
                __('Missing required risk assessment field: %s.', 'piper-privacy'),
                $field
            );
            return $result;
        }
    }

    if (isset($value['likelihood']) && !in_array($value['likelihood'], $args['risk_levels'])) {
        $result['message'] = sprintf(
            __('Invalid risk likelihood level. Allowed levels: %s.', 'piper-privacy'),
            implode(', ', $args['risk_levels'])
        );
        return $result;
    }

    if (isset($value['impact']) && !in_array($value['impact'], $args['risk_levels'])) {
        $result['message'] = sprintf(
            __('Invalid risk impact level. Allowed levels: %s.', 'piper-privacy'),
            implode(', ', $args['risk_levels'])
        );
        return $result;
    }

    $result['valid'] = true;
    return $result;
}

/**
 * Validate sharing party information
 *
 * @param array $value Sharing party data
 * @param array $args  Optional arguments (required_fields, sharing_types)
 * @return array Validation result
 */
function pp_validate_sharing_party($value, $args = []) {
    $result = [
        'valid' => false,
        'message' => ''
    ];

    if (!is_array($value)) {
        $result['message'] = __('Sharing party information must be provided as an array.', 'piper-privacy');
        return $result;
    }

    $defaults = [
        'required_fields' => ['name', 'purpose', 'type', 'country'],
        'sharing_types' => ['processor', 'controller', 'joint-controller', 'third-party']
    ];
    $args = wp_parse_args($args, $defaults);

    foreach ($args['required_fields'] as $field) {
        if (!isset($value[$field]) || empty($value[$field])) {
            $result['message'] = sprintf(
                __('Missing required sharing party field: %s.', 'piper-privacy'),
                $field
            );
            return $result;
        }
    }

    if (isset($value['type']) && !in_array($value['type'], $args['sharing_types'])) {
        $result['message'] = sprintf(
            __('Invalid sharing party type. Allowed types: %s.', 'piper-privacy'),
            implode(', ', $args['sharing_types'])
        );
        return $result;
    }

    if (isset($value['country'])) {
        $countries = wp_country_database();
        if (!isset($countries[$value['country']])) {
            $result['message'] = __('Invalid country code.', 'piper-privacy');
            return $result;
        }
    }

    $result['valid'] = true;
    return $result;
}

/**
 * Validate security controls
 *
 * @param array $value Security controls data
 * @param array $args  Optional arguments (required_categories, min_controls)
 * @return array Validation result
 */
function pp_validate_security_controls($value, $args = []) {
    $result = [
        'valid' => false,
        'message' => ''
    ];

    if (!is_array($value)) {
        $result['message'] = __('Security controls must be provided as an array.', 'piper-privacy');
        return $result;
    }

    $defaults = [
        'required_categories' => ['technical', 'organizational', 'physical'],
        'min_controls' => 1
    ];
    $args = wp_parse_args($args, $defaults);

    if (count($value) < $args['min_controls']) {
        $result['message'] = sprintf(
            __('At least %d security control(s) must be specified.', 'piper-privacy'),
            $args['min_controls']
        );
        return $result;
    }

    // Check required categories
    foreach ($args['required_categories'] as $category) {
        $found = false;
        foreach ($value as $control) {
            if (isset($control['category']) && $control['category'] === $category) {
                $found = true;
                break;
            }
        }
        if (!$found) {
            $result['message'] = sprintf(
                __('Missing security controls for category: %s.', 'piper-privacy'),
                $category
            );
            return $result;
        }
    }

    $result['valid'] = true;
    return $result;
}

/**
 * Validate privacy notice elements
 *
 * @param array $value Privacy notice elements
 * @param array $args  Optional arguments (required_sections)
 * @return array Validation result
 */
function pp_validate_privacy_notice($value, $args = []) {
    $result = [
        'valid' => false,
        'message' => ''
    ];

    if (!is_array($value)) {
        $result['message'] = __('Privacy notice elements must be provided as an array.', 'piper-privacy');
        return $result;
    }

    $defaults = [
        'required_sections' => [
            'collection_purpose',
            'data_categories',
            'legal_basis',
            'retention_period',
            'data_rights',
            'sharing_parties',
            'contact_info'
        ]
    ];
    $args = wp_parse_args($args, $defaults);

    foreach ($args['required_sections'] as $section) {
        if (!isset($value[$section]) || empty($value[$section])) {
            $result['message'] = sprintf(
                __('Missing required privacy notice section: %s.', 'piper-privacy'),
                $section
            );
            return $result;
        }
    }

    $result['valid'] = true;
    return $result;
}

/**
 * Validate data subject rights request
 *
 * @param array $value Rights request data
 * @param array $args  Optional arguments (allowed_rights, required_fields)
 * @return array Validation result
 */
function pp_validate_rights_request($value, $args = []) {
    $result = [
        'valid' => false,
        'message' => ''
    ];

    if (!is_array($value)) {
        $result['message'] = __('Rights request must be provided as an array.', 'piper-privacy');
        return $result;
    }

    $defaults = [
        'allowed_rights' => [
            'access',
            'rectification',
            'erasure',
            'portability',
            'restriction',
            'objection'
        ],
        'required_fields' => [
            'request_type',
            'subject_identity',
            'verification_method'
        ]
    ];
    $args = wp_parse_args($args, $defaults);

    foreach ($args['required_fields'] as $field) {
        if (!isset($value[$field]) || empty($value[$field])) {
            $result['message'] = sprintf(
                __('Missing required rights request field: %s.', 'piper-privacy'),
                $field
            );
            return $result;
        }
    }

    if (!in_array($value['request_type'], $args['allowed_rights'])) {
        $result['message'] = sprintf(
            __('Invalid rights request type. Allowed types: %s.', 'piper-privacy'),
            implode(', ', $args['allowed_rights'])
        );
        return $result;
    }

    $result['valid'] = true;
    return $result;
}

/**
 * Validate data breach notification
 *
 * @param array $value Breach notification data
 * @param array $args  Optional arguments (required_fields, risk_categories)
 * @return array Validation result
 */
function pp_validate_breach_notification($value, $args = []) {
    $result = [
        'valid' => false,
        'message' => ''
    ];

    if (!is_array($value)) {
        $result['message'] = __('Breach notification must be provided as an array.', 'piper-privacy');
        return $result;
    }

    $defaults = [
        'required_fields' => [
            'incident_date',
            'discovery_date',
            'affected_data',
            'affected_subjects',
            'risk_assessment',
            'mitigation_measures'
        ],
        'risk_categories' => [
            'confidentiality_breach',
            'integrity_breach',
            'availability_breach'
        ]
    ];
    $args = wp_parse_args($args, $defaults);

    foreach ($args['required_fields'] as $field) {
        if (!isset($value[$field]) || empty($value[$field])) {
            $result['message'] = sprintf(
                __('Missing required breach notification field: %s.', 'piper-privacy'),
                $field
            );
            return $result;
        }
    }

    // Validate dates
    $incident_date = strtotime($value['incident_date']);
    $discovery_date = strtotime($value['discovery_date']);
    $current_date = current_time('timestamp');

    if ($incident_date > $current_date) {
        $result['message'] = __('Incident date cannot be in the future.', 'piper-privacy');
        return $result;
    }

    if ($discovery_date > $current_date) {
        $result['message'] = __('Discovery date cannot be in the future.', 'piper-privacy');
        return $result;
    }

    if ($discovery_date < $incident_date) {
        $result['message'] = __('Discovery date cannot be before incident date.', 'piper-privacy');
        return $result;
    }

    // Check risk categories
    $has_risk_category = false;
    foreach ($args['risk_categories'] as $category) {
        if (isset($value['breach_type']) && $value['breach_type'] === $category) {
            $has_risk_category = true;
            break;
        }
    }

    if (!$has_risk_category) {
        $result['message'] = sprintf(
            __('Invalid breach type. Must be one of: %s.', 'piper-privacy'),
            implode(', ', $args['risk_categories'])
        );
        return $result;
    }

    $result['valid'] = true;
    return $result;
}

/**
 * Validate data processing record
 *
 * @param array $value Processing record data
 * @param array $args  Optional arguments (required_fields, processing_types)
 * @return array Validation result
 */
function pp_validate_processing_record($value, $args = []) {
    $result = [
        'valid' => false,
        'message' => ''
    ];

    if (!is_array($value)) {
        $result['message'] = __('Processing record must be provided as an array.', 'piper-privacy');
        return $result;
    }

    $defaults = [
        'required_fields' => [
            'processing_purpose',
            'legal_basis',
            'data_categories',
            'retention_period',
            'security_measures',
            'international_transfers'
        ],
        'processing_types' => [
            'collection',
            'recording',
            'organization',
            'structuring',
            'storage',
            'adaptation',
            'retrieval',
            'consultation',
            'use',
            'disclosure',
            'dissemination',
            'alignment',
            'combination',
            'restriction',
            'erasure',
            'destruction'
        ]
    ];
    $args = wp_parse_args($args, $defaults);

    foreach ($args['required_fields'] as $field) {
        if (!isset($value[$field]) || empty($value[$field])) {
            $result['message'] = sprintf(
                __('Missing required processing record field: %s.', 'piper-privacy'),
                $field
            );
            return $result;
        }
    }

    if (isset($value['processing_operations']) && is_array($value['processing_operations'])) {
        foreach ($value['processing_operations'] as $operation) {
            if (!in_array($operation, $args['processing_types'])) {
                $result['message'] = sprintf(
                    __('Invalid processing operation: %s. Allowed types: %s.', 'piper-privacy'),
                    $operation,
                    implode(', ', $args['processing_types'])
                );
                return $result;
            }
        }
    }

    $result['valid'] = true;
    return $result;
}

/**
 * Validate data transfer impact assessment
 *
 * @param array $value Transfer assessment data
 * @param array $args  Optional arguments (required_fields, adequacy_mechanisms)
 * @return array Validation result
 */
function pp_validate_transfer_assessment($value, $args = []) {
    $result = [
        'valid' => false,
        'message' => ''
    ];

    if (!is_array($value)) {
        $result['message'] = __('Transfer assessment must be provided as an array.', 'piper-privacy');
        return $result;
    }

    $defaults = [
        'required_fields' => [
            'recipient_country',
            'transfer_mechanism',
            'safeguards',
            'risk_assessment',
            'supplementary_measures'
        ],
        'adequacy_mechanisms' => [
            'adequacy_decision',
            'standard_contractual_clauses',
            'binding_corporate_rules',
            'code_of_conduct',
            'certification_mechanism',
            'derogation'
        ]
    ];
    $args = wp_parse_args($args, $defaults);

    foreach ($args['required_fields'] as $field) {
        if (!isset($value[$field]) || empty($value[$field])) {
            $result['message'] = sprintf(
                __('Missing required transfer assessment field: %s.', 'piper-privacy'),
                $field
            );
            return $result;
        }
    }

    if (!in_array($value['transfer_mechanism'], $args['adequacy_mechanisms'])) {
        $result['message'] = sprintf(
            __('Invalid transfer mechanism. Allowed mechanisms: %s.', 'piper-privacy'),
            implode(', ', $args['adequacy_mechanisms'])
        );
        return $result;
    }

    // Validate country
    if (isset($value['recipient_country'])) {
        $countries = wp_country_database();
        if (!isset($countries[$value['recipient_country']])) {
            $result['message'] = __('Invalid recipient country code.', 'piper-privacy');
            return $result;
        }
    }

    $result['valid'] = true;
    return $result;
}

/**
 * Validate legitimate interests assessment
 *
 * @param array $value LIA data
 * @param array $args  Optional arguments (required_fields)
 * @return array Validation result
 */
function pp_validate_legitimate_interests($value, $args = []) {
    $result = [
        'valid' => false,
        'message' => ''
    ];

    if (!is_array($value)) {
        $result['message'] = __('Legitimate interests assessment must be provided as an array.', 'piper-privacy');
        return $result;
    }

    $defaults = [
        'required_fields' => [
            'purpose_test' => [
                'processing_purpose',
                'legitimate_interest',
                'benefit_description',
                'impact_assessment'
            ],
            'necessity_test' => [
                'processing_necessity',
                'alternative_methods',
                'proportionality'
            ],
            'balancing_test' => [
                'data_subject_impact',
                'safeguards',
                'mitigating_measures',
                'conclusion'
            ]
        ]
    ];
    $args = wp_parse_args($args, $defaults);

    foreach ($args['required_fields'] as $test => $fields) {
        if (!isset($value[$test]) || !is_array($value[$test])) {
            $result['message'] = sprintf(
                __('Missing or invalid %s section in legitimate interests assessment.', 'piper-privacy'),
                $test
            );
            return $result;
        }

        foreach ($fields as $field) {
            if (!isset($value[$test][$field]) || empty($value[$test][$field])) {
                $result['message'] = sprintf(
                    __('Missing required field %s in %s section.', 'piper-privacy'),
                    $field,
                    $test
                );
                return $result;
            }
        }
    }

    $result['valid'] = true;
    return $result;
}

/**
 * Validate consent record
 *
 * @param array $value Consent record data
 * @param array $args  Optional arguments (required_fields, consent_types)
 * @return array Validation result
 */
function pp_validate_consent_record($value, $args = []) {
    $result = [
        'valid' => false,
        'message' => ''
    ];

    if (!is_array($value)) {
        $result['message'] = __('Consent record must be provided as an array.', 'piper-privacy');
        return $result;
    }

    $defaults = [
        'required_fields' => [
            'subject_id',
            'consent_type',
            'timestamp',
            'processing_purposes',
            'collection_method',
            'consent_text',
            'expiration_date',
            'withdrawal_method'
        ],
        'consent_types' => [
            'explicit',
            'unambiguous',
            'parental',
            'third_party'
        ]
    ];
    $args = wp_parse_args($args, $defaults);

    foreach ($args['required_fields'] as $field) {
        if (!isset($value[$field]) || empty($value[$field])) {
            $result['message'] = sprintf(
                __('Missing required consent record field: %s.', 'piper-privacy'),
                $field
            );
            return $result;
        }
    }

    if (!in_array($value['consent_type'], $args['consent_types'])) {
        $result['message'] = sprintf(
            __('Invalid consent type. Allowed types: %s.', 'piper-privacy'),
            implode(', ', $args['consent_types'])
        );
        return $result;
    }

    // Validate dates
    $timestamp = strtotime($value['timestamp']);
    $expiration = strtotime($value['expiration_date']);
    $current_date = current_time('timestamp');

    if ($timestamp > $current_date) {
        $result['message'] = __('Consent timestamp cannot be in the future.', 'piper-privacy');
        return $result;
    }

    if ($expiration <= $timestamp) {
        $result['message'] = __('Expiration date must be after consent timestamp.', 'piper-privacy');
        return $result;
    }

    $result['valid'] = true;
    return $result;
}

/**
 * Validate vendor assessment
 *
 * @param array $value Vendor assessment data
 * @param array $args  Optional arguments (required_fields, risk_levels)
 * @return array Validation result
 */
function pp_validate_vendor_assessment($value, $args = []) {
    $result = [
        'valid' => false,
        'message' => ''
    ];

    if (!is_array($value)) {
        $result['message'] = __('Vendor assessment must be provided as an array.', 'piper-privacy');
        return $result;
    }

    $defaults = [
        'required_fields' => [
            'vendor_info' => [
                'name',
                'contact_details',
                'services_provided',
                'data_access_level'
            ],
            'security_assessment' => [
                'security_measures',
                'certifications',
                'incident_response',
                'risk_level'
            ],
            'privacy_assessment' => [
                'privacy_program',
                'data_handling',
                'subprocessors',
                'international_transfers'
            ],
            'contractual_measures' => [
                'dpa_status',
                'breach_notification',
                'audit_rights',
                'termination_provisions'
            ]
        ],
        'risk_levels' => [
            'low',
            'medium',
            'high',
            'critical'
        ]
    ];
    $args = wp_parse_args($args, $defaults);

    foreach ($args['required_fields'] as $section => $fields) {
        if (!isset($value[$section]) || !is_array($value[$section])) {
            $result['message'] = sprintf(
                __('Missing or invalid %s section in vendor assessment.', 'piper-privacy'),
                $section
            );
            return $result;
        }

        foreach ($fields as $field) {
            if (!isset($value[$section][$field]) || empty($value[$section][$field])) {
                $result['message'] = sprintf(
                    __('Missing required field %s in %s section.', 'piper-privacy'),
                    $field,
                    $section
                );
                return $result;
            }
        }
    }

    if (!in_array($value['security_assessment']['risk_level'], $args['risk_levels'])) {
        $result['message'] = sprintf(
            __('Invalid risk level. Allowed levels: %s.', 'piper-privacy'),
            implode(', ', $args['risk_levels'])
        );
        return $result;
    }

    $result['valid'] = true;
    return $result;
}

/**
 * Validate automated decision-making impact assessment
 *
 * @param array $value DPIA data for automated decision-making
 * @param array $args  Optional arguments (required_fields, decision_types)
 * @return array Validation result
 */
function pp_validate_automated_decision_assessment($value, $args = []) {
    $result = [
        'valid' => false,
        'message' => ''
    ];

    if (!is_array($value)) {
        $result['message'] = __('Automated decision-making assessment must be provided as an array.', 'piper-privacy');
        return $result;
    }

    $defaults = [
        'required_fields' => [
            'system_description' => [
                'purpose',
                'scope',
                'decision_logic',
                'data_sources'
            ],
            'necessity_assessment' => [
                'business_need',
                'alternatives_considered',
                'proportionality'
            ],
            'risk_assessment' => [
                'discrimination_risks',
                'accuracy_risks',
                'transparency_risks',
                'mitigation_measures'
            ],
            'human_oversight' => [
                'review_process',
                'appeal_mechanism',
                'human_intervention_points',
                'oversight_procedures'
            ]
        ],
        'decision_types' => [
            'profiling',
            'credit_scoring',
            'recruitment',
            'performance_evaluation',
            'behavior_analysis',
            'eligibility_determination'
        ]
    ];
    $args = wp_parse_args($args, $defaults);

    foreach ($args['required_fields'] as $section => $fields) {
        if (!isset($value[$section]) || !is_array($value[$section])) {
            $result['message'] = sprintf(
                __('Missing or invalid %s section in automated decision-making assessment.', 'piper-privacy'),
                $section
            );
            return $result;
        }

        foreach ($fields as $field) {
            if (!isset($value[$section][$field]) || empty($value[$section][$field])) {
                $result['message'] = sprintf(
                    __('Missing required field %s in %s section.', 'piper-privacy'),
                    $field,
                    $section
                );
                return $result;
            }
        }
    }

    if (isset($value['decision_type']) && !in_array($value['decision_type'], $args['decision_types'])) {
        $result['message'] = sprintf(
            __('Invalid decision type. Allowed types: %s.', 'piper-privacy'),
            implode(', ', $args['decision_types'])
        );
        return $result;
    }

    // Validate specific requirements for automated decisions
    if (!isset($value['significant_impact_justification']) || empty($value['significant_impact_justification'])) {
        $result['message'] = __('Missing justification for significant impact on individuals.', 'piper-privacy');
        return $result;
    }

    if (!isset($value['algorithmic_transparency']) || empty($value['algorithmic_transparency'])) {
        $result['message'] = __('Missing explanation of algorithmic transparency measures.', 'piper-privacy');
        return $result;
    }

    $result['valid'] = true;
    return $result;
}

/**
 * Validate special category data processing
 *
 * @param array $value Special category data processing details
 * @param array $args  Optional arguments (required_fields, special_categories)
 * @return array Validation result
 */
function pp_validate_special_category_processing($value, $args = []) {
    $result = [
        'valid' => false,
        'message' => ''
    ];

    if (!is_array($value)) {
        $result['message'] = __('Special category processing details must be provided as an array.', 'piper-privacy');
        return $result;
    }

    $defaults = [
        'required_fields' => [
            'category_type',
            'legal_basis',
            'necessity_justification',
            'safeguards',
            'dpia_reference',
            'consultation_details'
        ],
        'special_categories' => [
            'racial_ethnic_origin',
            'political_opinions',
            'religious_beliefs',
            'trade_union_membership',
            'genetic_data',
            'biometric_data',
            'health_data',
            'sex_life_orientation',
            'criminal_convictions'
        ],
        'legal_bases' => [
            'explicit_consent',
            'employment_social_security',
            'vital_interests',
            'legitimate_activities',
            'public_data',
            'legal_claims',
            'substantial_public_interest',
            'health_social_care',
            'public_health',
            'archiving_research'
        ]
    ];
    $args = wp_parse_args($args, $defaults);

    foreach ($args['required_fields'] as $field) {
        if (!isset($value[$field]) || empty($value[$field])) {
            $result['message'] = sprintf(
                __('Missing required special category processing field: %s.', 'piper-privacy'),
                $field
            );
            return $result;
        }
    }

    if (!in_array($value['category_type'], $args['special_categories'])) {
        $result['message'] = sprintf(
            __('Invalid special category type. Allowed categories: %s.', 'piper-privacy'),
            implode(', ', $args['special_categories'])
        );
        return $result;
    }

    if (!in_array($value['legal_basis'], $args['legal_bases'])) {
        $result['message'] = sprintf(
            __('Invalid legal basis for special category processing. Allowed bases: %s.', 'piper-privacy'),
            implode(', ', $args['legal_bases'])
        );
        return $result;
    }

    // Validate DPIA requirement
    if ($value['dpia_required'] && empty($value['dpia_reference'])) {
        $result['message'] = __('DPIA reference is required for this type of special category processing.', 'piper-privacy');
        return $result;
    }

    $result['valid'] = true;
    return $result;
}

/**
 * Validate children's data processing
 *
 * @param array $value Children's data processing details
 * @param array $args  Optional arguments (required_fields, age_verification_methods)
 * @return array Validation result
 */
function pp_validate_childrens_data_processing($value, $args = []) {
    $result = [
        'valid' => false,
        'message' => ''
    ];

    if (!is_array($value)) {
        $result['message'] = __('Children\'s data processing details must be provided as an array.', 'piper-privacy');
        return $result;
    }

    $defaults = [
        'required_fields' => [
            'age_verification_method',
            'parental_consent_method',
            'risk_assessment',
            'safeguards',
            'data_minimization',
            'retention_limits',
            'privacy_notice_adaptation'
        ],
        'age_verification_methods' => [
            'document_upload',
            'parental_confirmation',
            'third_party_verification',
            'technical_measures',
            'self_declaration'
        ],
        'consent_methods' => [
            'electronic_form',
            'signed_document',
            'verified_email',
            'phone_verification',
            'video_confirmation'
        ]
    ];
    $args = wp_parse_args($args, $defaults);

    foreach ($args['required_fields'] as $field) {
        if (!isset($value[$field]) || empty($value[$field])) {
            $result['message'] = sprintf(
                __('Missing required children\'s data processing field: %s.', 'piper-privacy'),
                $field
            );
            return $result;
        }
    }

    if (!in_array($value['age_verification_method'], $args['age_verification_methods'])) {
        $result['message'] = sprintf(
            __('Invalid age verification method. Allowed methods: %s.', 'piper-privacy'),
            implode(', ', $args['age_verification_methods'])
        );
        return $result;
    }

    if (!in_array($value['parental_consent_method'], $args['consent_methods'])) {
        $result['message'] = sprintf(
            __('Invalid parental consent method. Allowed methods: %s.', 'piper-privacy'),
            implode(', ', $args['consent_methods'])
        );
        return $result;
    }

    // Validate age thresholds
    if (!isset($value['age_threshold']) || $value['age_threshold'] < 13) {
        $result['message'] = __('Age threshold must be at least 13 years.', 'piper-privacy');
        return $result;
    }

    // Validate risk assessment
    if (!isset($value['risk_assessment']['risks']) || !is_array($value['risk_assessment']['risks'])) {
        $result['message'] = __('Risk assessment must include specific risks to children.', 'piper-privacy');
        return $result;
    }

    $result['valid'] = true;
    return $result;
}

/**
 * Validate marketing preferences
 *
 * @param array $value Marketing preferences data
 * @param array $args  Optional arguments (required_fields, marketing_channels)
 * @return array Validation result
 */
function pp_validate_marketing_preferences($value, $args = []) {
    $result = [
        'valid' => false,
        'message' => ''
    ];

    if (!is_array($value)) {
        $result['message'] = __('Marketing preferences must be provided as an array.', 'piper-privacy');
        return $result;
    }

    $defaults = [
        'required_fields' => [
            'contact_channels',
            'preference_source',
            'timestamp',
            'proof_of_consent',
            'subscription_topics',
            'frequency_preferences'
        ],
        'marketing_channels' => [
            'email',
            'sms',
            'phone',
            'postal',
            'push_notification',
            'in_app',
            'social_media'
        ],
        'preference_sources' => [
            'web_form',
            'mobile_app',
            'phone_call',
            'written_form',
            'preference_center'
        ]
    ];
    $args = wp_parse_args($args, $defaults);

    foreach ($args['required_fields'] as $field) {
        if (!isset($value[$field]) || empty($value[$field])) {
            $result['message'] = sprintf(
                __('Missing required marketing preferences field: %s.', 'piper-privacy'),
                $field
            );
            return $result;
        }
    }

    // Validate marketing channels
    foreach ($value['contact_channels'] as $channel) {
        if (!in_array($channel, $args['marketing_channels'])) {
            $result['message'] = sprintf(
                __('Invalid marketing channel: %s. Allowed channels: %s.', 'piper-privacy'),
                $channel,
                implode(', ', $args['marketing_channels'])
            );
            return $result;
        }
    }

    if (!in_array($value['preference_source'], $args['preference_sources'])) {
        $result['message'] = sprintf(
            __('Invalid preference source. Allowed sources: %s.', 'piper-privacy'),
            implode(', ', $args['preference_sources'])
        );
        return $result;
    }

    // Validate timestamp
    $timestamp = strtotime($value['timestamp']);
    $current_date = current_time('timestamp');
    if ($timestamp > $current_date) {
        $result['message'] = __('Preference timestamp cannot be in the future.', 'piper-privacy');
        return $result;
    }

    $result['valid'] = true;
    return $result;
}

/**
 * Validate cookie consent management
 *
 * @param array $value Cookie consent data
 * @param array $args  Optional arguments (required_fields, cookie_categories)
 * @return array Validation result
 */
function pp_validate_cookie_consent($value, $args = []) {
    $result = [
        'valid' => false,
        'message' => ''
    ];

    if (!is_array($value)) {
        $result['message'] = __('Cookie consent data must be provided as an array.', 'piper-privacy');
        return $result;
    }

    $defaults = [
        'required_fields' => [
            'consent_version',
            'timestamp',
            'cookie_preferences',
            'user_agent',
            'consent_proof',
            'banner_version',
            'language'
        ],
        'cookie_categories' => [
            'necessary',
            'preferences',
            'statistics',
            'marketing',
            'social_media',
            'unclassified'
        ],
        'consent_proofs' => [
            'banner_interaction',
            'api_call',
            'preference_center',
            'stored_preference'
        ]
    ];
    $args = wp_parse_args($args, $defaults);

    foreach ($args['required_fields'] as $field) {
        if (!isset($value[$field]) || empty($value[$field])) {
            $result['message'] = sprintf(
                __('Missing required cookie consent field: %s.', 'piper-privacy'),
                $field
            );
            return $result;
        }
    }

    // Validate cookie preferences
    if (!is_array($value['cookie_preferences'])) {
        $result['message'] = __('Cookie preferences must be provided as an array.', 'piper-privacy');
        return $result;
    }

    foreach ($value['cookie_preferences'] as $category => $consent) {
        if (!in_array($category, $args['cookie_categories'])) {
            $result['message'] = sprintf(
                __('Invalid cookie category: %s. Allowed categories: %s.', 'piper-privacy'),
                $category,
                implode(', ', $args['cookie_categories'])
            );
            return $result;
        }

        if (!is_bool($consent)) {
            $result['message'] = sprintf(
                __('Consent value for category %s must be boolean.', 'piper-privacy'),
                $category
            );
            return $result;
        }
    }

    // Validate necessary cookies
    if (!isset($value['cookie_preferences']['necessary']) || !$value['cookie_preferences']['necessary']) {
        $result['message'] = __('Necessary cookies must be accepted.', 'piper-privacy');
        return $result;
    }

    // Validate consent proof
    if (!in_array($value['consent_proof'], $args['consent_proofs'])) {
        $result['message'] = sprintf(
            __('Invalid consent proof method. Allowed methods: %s.', 'piper-privacy'),
            implode(', ', $args['consent_proofs'])
        );
        return $result;
    }

    // Validate timestamp
    $timestamp = strtotime($value['timestamp']);
    $current_date = current_time('timestamp');
    if ($timestamp > $current_date) {
        $result['message'] = __('Consent timestamp cannot be in the future.', 'piper-privacy');
        return $result;
    }

    $result['valid'] = true;
    return $result;
}

/**
 * Validate Data Protection Impact Assessment (DPIA)
 *
 * @param array $value DPIA data
 * @param array $args  Optional arguments (required_fields, risk_levels)
 * @return array Validation result
 */
function pp_validate_dpia($value, $args = []) {
    $result = [
        'valid' => false,
        'message' => ''
    ];

    if (!is_array($value)) {
        $result['message'] = __('DPIA data must be provided as an array.', 'piper-privacy');
        return $result;
    }

    $defaults = [
        'required_fields' => [
            'processing_description' => [
                'nature',
                'scope',
                'context',
                'purposes'
            ],
            'necessity_assessment' => [
                'proportionality',
                'lawfulness',
                'data_minimization'
            ],
            'risk_assessment' => [
                'identified_risks',
                'risk_likelihood',
                'risk_severity',
                'existing_controls'
            ],
            'mitigation_measures' => [
                'technical_measures',
                'organizational_measures',
                'residual_risks'
            ],
            'consultation' => [
                'dpo_advice',
                'stakeholder_views',
                'data_subject_consultation'
            ]
        ],
        'risk_levels' => [
            'low',
            'medium',
            'high',
            'very_high'
        ],
        'consultation_types' => [
            'dpo',
            'supervisory_authority',
            'data_subjects',
            'processors',
            'security_experts'
        ]
    ];
    $args = wp_parse_args($args, $defaults);

    foreach ($args['required_fields'] as $section => $fields) {
        if (!isset($value[$section]) || !is_array($value[$section])) {
            $result['message'] = sprintf(
                __('Missing or invalid %s section in DPIA.', 'piper-privacy'),
                $section
            );
            return $result;
        }

        foreach ($fields as $field) {
            if (!isset($value[$section][$field]) || empty($value[$section][$field])) {
                $result['message'] = sprintf(
                    __('Missing required field %s in %s section.', 'piper-privacy'),
                    $field,
                    $section
                );
                return $result;
            }
        }
    }

    // Validate risk levels
    foreach ($value['risk_assessment']['identified_risks'] as $risk) {
        if (!in_array($risk['likelihood'], $args['risk_levels']) || !in_array($risk['severity'], $args['risk_levels'])) {
            $result['message'] = sprintf(
                __('Invalid risk level. Allowed levels: %s.', 'piper-privacy'),
                implode(', ', $args['risk_levels'])
            );
            return $result;
        }
    }

    // Validate consultation requirements
    $high_risk = false;
    foreach ($value['risk_assessment']['identified_risks'] as $risk) {
        if ($risk['likelihood'] === 'high' && $risk['severity'] === 'high') {
            $high_risk = true;
            break;
        }
    }

    if ($high_risk && empty($value['consultation']['supervisory_authority_consultation'])) {
        $result['message'] = __('Supervisory authority consultation is required for high-risk processing.', 'piper-privacy');
        return $result;
    }

    $result['valid'] = true;
    return $result;
}

/**
 * Validate cross-border transfer mechanism
 *
 * @param array $value Transfer mechanism data
 * @param array $args  Optional arguments (required_fields, transfer_tools)
 * @return array Validation result
 */
function pp_validate_transfer_mechanism($value, $args = []) {
    $result = [
        'valid' => false,
        'message' => ''
    ];

    if (!is_array($value)) {
        $result['message'] = __('Transfer mechanism data must be provided as an array.', 'piper-privacy');
        return $result;
    }

    $defaults = [
        'required_fields' => [
            'transfer_tool',
            'adequacy_decision',
            'appropriate_safeguards',
            'supplementary_measures',
            'essential_guarantees',
            'enforcement_mechanisms'
        ],
        'transfer_tools' => [
            'adequacy_decision',
            'standard_contractual_clauses',
            'binding_corporate_rules',
            'approved_code_of_conduct',
            'approved_certification_mechanism',
            'administrative_arrangement'
        ],
        'safeguard_types' => [
            'technical',
            'contractual',
            'organizational'
        ]
    ];
    $args = wp_parse_args($args, $defaults);

    foreach ($args['required_fields'] as $field) {
        if (!isset($value[$field]) || empty($value[$field])) {
            $result['message'] = sprintf(
                __('Missing required transfer mechanism field: %s.', 'piper-privacy'),
                $field
            );
            return $result;
        }
    }

    if (!in_array($value['transfer_tool'], $args['transfer_tools'])) {
        $result['message'] = sprintf(
            __('Invalid transfer tool. Allowed tools: %s.', 'piper-privacy'),
            implode(', ', $args['transfer_tools'])
        );
        return $result;
    }

    // Validate supplementary measures
    foreach ($value['supplementary_measures'] as $measure) {
        if (!isset($measure['type']) || !in_array($measure['type'], $args['safeguard_types'])) {
            $result['message'] = sprintf(
                __('Invalid safeguard type. Allowed types: %s.', 'piper-privacy'),
                implode(', ', $args['safeguard_types'])
            );
            return $result;
        }
    }

    $result['valid'] = true;
    return $result;
}

/**
 * Validate data minimization principles
 *
 * @param array $value Data minimization assessment
 * @param array $args  Optional arguments (required_fields)
 * @return array Validation result
 */
function pp_validate_data_minimization($value, $args = []) {
    $result = [
        'valid' => false,
        'message' => ''
    ];

    if (!is_array($value)) {
        $result['message'] = __('Data minimization assessment must be provided as an array.', 'piper-privacy');
        return $result;
    }

    $defaults = [
        'required_fields' => [
            'data_elements' => [
                'field_name',
                'purpose',
                'necessity_justification'
            ],
            'retention_criteria' => [
                'retention_period',
                'legal_basis',
                'business_need'
            ],
            'access_controls' => [
                'role',
                'access_level',
                'justification'
            ]
        ],
        'access_levels' => [
            'no_access',
            'read_only',
            'read_write',
            'full_access'
        ]
    ];
    $args = wp_parse_args($args, $defaults);

    foreach ($args['required_fields'] as $section => $fields) {
        if (!isset($value[$section]) || !is_array($value[$section])) {
            $result['message'] = sprintf(
                __('Missing or invalid %s section in data minimization assessment.', 'piper-privacy'),
                $section
            );
            return $result;
        }

        foreach ($value[$section] as $item) {
            foreach ($fields as $field) {
                if (!isset($item[$field]) || empty($item[$field])) {
                    $result['message'] = sprintf(
                        __('Missing required field %s in %s section.', 'piper-privacy'),
                        $field,
                        $section
                    );
                    return $result;
                }
            }
        }
    }

    // Validate access levels
    foreach ($value['access_controls'] as $control) {
        if (!in_array($control['access_level'], $args['access_levels'])) {
            $result['message'] = sprintf(
                __('Invalid access level. Allowed levels: %s.', 'piper-privacy'),
                implode(', ', $args['access_levels'])
            );
            return $result;
        }
    }

    $result['valid'] = true;
    return $result;
}

/**
 * Validate privacy by design requirements
 *
 * @param array $value Privacy by design assessment
 * @param array $args  Optional arguments (required_fields)
 * @return array Validation result
 */
function pp_validate_privacy_by_design($value, $args = []) {
    $result = [
        'valid' => false,
        'message' => ''
    ];

    if (!is_array($value)) {
        $result['message'] = __('Privacy by design assessment must be provided as an array.', 'piper-privacy');
        return $result;
    }

    $defaults = [
        'required_fields' => [
            'design_measures' => [
                'data_protection',
                'data_security',
                'data_accuracy',
                'data_minimization'
            ],
            'default_settings' => [
                'privacy_settings',
                'retention_periods',
                'access_controls'
            ],
            'technical_controls' => [
                'encryption',
                'pseudonymization',
                'anonymization',
                'monitoring'
            ],
            'organizational_measures' => [
                'policies',
                'procedures',
                'training',
                'audits'
            ]
        ],
        'privacy_principles' => [
            'lawfulness',
            'fairness',
            'transparency',
            'purpose_limitation',
            'data_minimization',
            'accuracy',
            'storage_limitation',
            'integrity_confidentiality',
            'accountability'
        ]
    ];
    $args = wp_parse_args($args, $defaults);

    foreach ($args['required_fields'] as $section => $fields) {
        if (!isset($value[$section]) || !is_array($value[$section])) {
            $result['message'] = sprintf(
                __('Missing or invalid %s section in privacy by design assessment.', 'piper-privacy'),
                $section
            );
            return $result;
        }

        foreach ($fields as $field) {
            if (!isset($value[$section][$field]) || empty($value[$section][$field])) {
                $result['message'] = sprintf(
                    __('Missing required field %s in %s section.', 'piper-privacy'),
                    $field,
                    $section
                );
                return $result;
            }
        }
    }

    // Validate privacy principles implementation
    foreach ($args['privacy_principles'] as $principle) {
        $found = false;
        foreach ($value['design_measures'] as $measure) {
            if (isset($measure['principle']) && $measure['principle'] === $principle) {
                $found = true;
                break;
            }
        }
        if (!$found) {
            $result['message'] = sprintf(
                __('Missing implementation of privacy principle: %s.', 'piper-privacy'),
                $principle
            );
            return $result;
        }
    }

    // Validate default privacy settings
    if (!isset($value['default_settings']['privacy_settings']['privacy_by_default']) || 
        !$value['default_settings']['privacy_settings']['privacy_by_default']) {
        $result['message'] = __('Privacy by default must be enabled in default settings.', 'piper-privacy');
        return $result;
    }

    $result['valid'] = true;
    return $result;
}

/**
 * Validate data subject access request (DSAR)
 *
 * @param array $value DSAR data
 * @param array $args  Optional arguments (required_fields, request_types)
 * @return array Validation result
 */
function pp_validate_dsar($value, $args = []) {
    $result = [
        'valid' => false,
        'message' => ''
    ];

    if (!is_array($value)) {
        $result['message'] = __('DSAR data must be provided as an array.', 'piper-privacy');
        return $result;
    }

    $defaults = [
        'required_fields' => [
            'request_details' => [
                'request_type',
                'subject_identity',
                'verification_method',
                'submission_date'
            ],
            'processing_details' => [
                'assigned_handler',
                'due_date',
                'status',
                'verification_status'
            ],
            'response_details' => [
                'data_scope',
                'format',
                'delivery_method',
                'completion_date'
            ],
            'audit_trail' => [
                'verification_records',
                'processing_steps',
                'communications',
                'decisions'
            ]
        ],
        'request_types' => [
            'access',
            'rectification',
            'erasure',
            'portability',
            'restriction',
            'objection'
        ],
        'verification_methods' => [
            'id_document',
            'email_verification',
            'security_questions',
            'video_call',
            'existing_credentials'
        ],
        'status_types' => [
            'received',
            'verification_pending',
            'in_progress',
            'extended',
            'completed',
            'rejected'
        ]
    ];
    $args = wp_parse_args($args, $defaults);

    foreach ($args['required_fields'] as $section => $fields) {
        if (!isset($value[$section]) || !is_array($value[$section])) {
            $result['message'] = sprintf(
                __('Missing or invalid %s section in DSAR.', 'piper-privacy'),
                $section
            );
            return $result;
        }

        foreach ($fields as $field) {
            if (!isset($value[$section][$field]) || empty($value[$section][$field])) {
                $result['message'] = sprintf(
                    __('Missing required field %s in %s section.', 'piper-privacy'),
                    $field,
                    $section
                );
                return $result;
            }
        }
    }

    if (!in_array($value['request_details']['request_type'], $args['request_types'])) {
        $result['message'] = sprintf(
            __('Invalid request type. Allowed types: %s.', 'piper-privacy'),
            implode(', ', $args['request_types'])
        );
        return $result;
    }

    if (!in_array($value['request_details']['verification_method'], $args['verification_methods'])) {
        $result['message'] = sprintf(
            __('Invalid verification method. Allowed methods: %s.', 'piper-privacy'),
            implode(', ', $args['verification_methods'])
        );
        return $result;
    }

    if (!in_array($value['processing_details']['status'], $args['status_types'])) {
        $result['message'] = sprintf(
            __('Invalid status. Allowed statuses: %s.', 'piper-privacy'),
            implode(', ', $args['status_types'])
        );
        return $result;
    }

    // Validate dates
    $submission_date = strtotime($value['request_details']['submission_date']);
    $due_date = strtotime($value['processing_details']['due_date']);
    $current_date = current_time('timestamp');

    if ($submission_date > $current_date) {
        $result['message'] = __('Submission date cannot be in the future.', 'piper-privacy');
        return $result;
    }

    if ($due_date < $submission_date) {
        $result['message'] = __('Due date cannot be before submission date.', 'piper-privacy');
        return $result;
    }

    $result['valid'] = true;
    return $result;
}

/**
 * Validate data breach response procedure
 *
 * @param array $value Breach response data
 * @param array $args  Optional arguments (required_fields, breach_types)
 * @return array Validation result
 */
function pp_validate_breach_response($value, $args = []) {
    $result = [
        'valid' => false,
        'message' => ''
    ];

    if (!is_array($value)) {
        $result['message'] = __('Breach response data must be provided as an array.', 'piper-privacy');
        return $result;
    }

    $defaults = [
        'required_fields' => [
            'breach_details' => [
                'incident_type',
                'discovery_date',
                'occurrence_date',
                'affected_data',
                'affected_subjects',
                'likely_consequences'
            ],
            'risk_assessment' => [
                'risk_level',
                'impact_assessment',
                'probability_assessment',
                'risk_factors'
            ],
            'notification_details' => [
                'dpa_notification',
                'data_subject_notification',
                'notification_content',
                'notification_method'
            ],
            'mitigation_measures' => [
                'immediate_actions',
                'remedial_actions',
                'preventive_measures',
                'effectiveness_monitoring'
            ]
        ],
        'breach_types' => [
            'confidentiality',
            'integrity',
            'availability',
            'unauthorized_access',
            'data_loss',
            'data_alteration'
        ],
        'risk_levels' => [
            'low',
            'medium',
            'high',
            'critical'
        ],
        'notification_methods' => [
            'email',
            'phone',
            'letter',
            'public_announcement',
            'website_notice'
        ]
    ];
    $args = wp_parse_args($args, $defaults);

    foreach ($args['required_fields'] as $section => $fields) {
        if (!isset($value[$section]) || !is_array($value[$section])) {
            $result['message'] = sprintf(
                __('Missing or invalid %s section in breach response.', 'piper-privacy'),
                $section
            );
            return $result;
        }

        foreach ($fields as $field) {
            if (!isset($value[$section][$field]) || empty($value[$section][$field])) {
                $result['message'] = sprintf(
                    __('Missing required field %s in %s section.', 'piper-privacy'),
                    $field,
                    $section
                );
                return $result;
            }
        }
    }

    if (!in_array($value['breach_details']['incident_type'], $args['breach_types'])) {
        $result['message'] = sprintf(
            __('Invalid breach type. Allowed types: %s.', 'piper-privacy'),
            implode(', ', $args['breach_types'])
        );
        return $result;
    }

    if (!in_array($value['risk_assessment']['risk_level'], $args['risk_levels'])) {
        $result['message'] = sprintf(
            __('Invalid risk level. Allowed levels: %s.', 'piper-privacy'),
            implode(', ', $args['risk_levels'])
        );
        return $result;
    }

    // Validate notification requirements
    if ($value['risk_assessment']['risk_level'] === 'high' || $value['risk_assessment']['risk_level'] === 'critical') {
        if (!$value['notification_details']['dpa_notification']) {
            $result['message'] = __('DPA notification required for high-risk breaches.', 'piper-privacy');
            return $result;
        }
        if (!$value['notification_details']['data_subject_notification']) {
            $result['message'] = __('Data subject notification required for high-risk breaches.', 'piper-privacy');
            return $result;
        }
    }

    // Validate dates
    $occurrence_date = strtotime($value['breach_details']['occurrence_date']);
    $discovery_date = strtotime($value['breach_details']['discovery_date']);
    $current_date = current_time('timestamp');
    $notification_deadline = 72 * 60 * 60; // 72 hours in seconds

    if ($occurrence_date > $current_date || $discovery_date > $current_date) {
        $result['message'] = __('Occurrence and discovery dates cannot be in the future.', 'piper-privacy');
        return $result;
    }

    if ($discovery_date < $occurrence_date) {
        $result['message'] = __('Discovery date cannot be before occurrence date.', 'piper-privacy');
        return $result;
    }

    if ($value['notification_details']['dpa_notification'] && 
        empty($value['notification_details']['late_notification_justification']) && 
        ($current_date - $discovery_date) > $notification_deadline) {
        $result['message'] = __('Justification required for DPA notification beyond 72 hours.', 'piper-privacy');
        return $result;
    }

    $result['valid'] = true;
    return $result;
}

/**
 * Validate processor agreement
 *
 * @param array $value Processor agreement data
 * @param array $args  Optional arguments (required_fields)
 * @return array Validation result
 */
function pp_validate_processor_agreement($value, $args = []) {
    $result = [
        'valid' => false,
        'message' => ''
    ];

    if (!is_array($value)) {
        $result['message'] = __('Processor agreement data must be provided as an array.', 'piper-privacy');
        return $result;
    }

    $defaults = [
        'required_fields' => [
            'agreement_details' => [
                'processor_identity',
                'processing_subject',
                'processing_duration',
                'processing_nature',
                'processing_purpose',
                'data_categories',
                'data_subject_categories'
            ],
            'processor_obligations' => [
                'instructions_binding',
                'confidentiality_commitment',
                'security_measures',
                'sub_processor_rules',
                'controller_assistance',
                'breach_notification',
                'data_deletion',
                'audit_rights'
            ],
            'technical_measures' => [
                'encryption',
                'confidentiality',
                'resilience',
                'restoration',
                'testing'
            ],
            'organizational_measures' => [
                'policies',
                'training',
                'confidentiality_agreements',
                'access_control',
                'incident_response'
            ]
        ],
        'processing_types' => [
            'collection',
            'recording',
            'organization',
            'storage',
            'adaptation',
            'retrieval',
            'consultation',
            'use',
            'disclosure',
            'combination',
            'restriction',
            'erasure'
        ]
    ];
    $args = wp_parse_args($args, $defaults);

    foreach ($args['required_fields'] as $section => $fields) {
        if (!isset($value[$section]) || !is_array($value[$section])) {
            $result['message'] = sprintf(
                __('Missing or invalid %s section in processor agreement.', 'piper-privacy'),
                $section
            );
            return $result;
        }

        foreach ($fields as $field) {
            if (!isset($value[$section][$field]) || empty($value[$section][$field])) {
                $result['message'] = sprintf(
                    __('Missing required field %s in %s section.', 'piper-privacy'),
                    $field,
                    $section
                );
                return $result;
            }
        }
    }

    // Validate processing types
    foreach ($value['agreement_details']['processing_nature'] as $type) {
        if (!in_array($type, $args['processing_types'])) {
            $result['message'] = sprintf(
                __('Invalid processing type: %s. Allowed types: %s.', 'piper-privacy'),
                $type,
                implode(', ', $args['processing_types'])
            );
            return $result;
        }
    }

    // Validate sub-processor requirements
    if ($value['processor_obligations']['sub_processors_allowed'] && 
        empty($value['processor_obligations']['sub_processor_requirements'])) {
        $result['message'] = __('Sub-processor requirements must be specified if sub-processors are allowed.', 'piper-privacy');
        return $result;
    }

    $result['valid'] = true;
    return $result;
}

/**
 * Validate DPO requirements
 *
 * @param array $value DPO requirements data
 * @param array $args  Optional arguments (required_fields)
 * @return array Validation result
 */
function pp_validate_dpo_requirements($value, $args = []) {
    $result = [
        'valid' => false,
        'message' => ''
    ];

    if (!is_array($value)) {
        $result['message'] = __('DPO requirements data must be provided as an array.', 'piper-privacy');
        return $result;
    }

    $defaults = [
        'required_fields' => [
            'dpo_details' => [
                'name',
                'contact_information',
                'qualifications',
                'reporting_line',
                'independence_safeguards'
            ],
            'responsibilities' => [
                'monitoring_compliance',
                'training_awareness',
                'advice_provision',
                'dpia_oversight',
                'authority_cooperation',
                'contact_point'
            ],
            'resource_allocation' => [
                'time_commitment',
                'budget',
                'staff_support',
                'training_resources'
            ],
            'documentation' => [
                'appointment_record',
                'tasks_inventory',
                'activity_reports',
                'communication_records'
            ]
        ],
        'qualification_types' => [
            'legal_expertise',
            'data_protection_law',
            'technical_knowledge',
            'business_understanding',
            'risk_management'
        ],
        'independence_criteria' => [
            'no_conflict_of_interest',
            'direct_board_reporting',
            'no_operational_duties',
            'sufficient_resources',
            'protected_status'
        ]
    ];
    $args = wp_parse_args($args, $defaults);

    foreach ($args['required_fields'] as $section => $fields) {
        if (!isset($value[$section]) || !is_array($value[$section])) {
            $result['message'] = sprintf(
                __('Missing or invalid %s section in DPO requirements.', 'piper-privacy'),
                $section
            );
            return $result;
        }

        foreach ($fields as $field) {
            if (!isset($value[$section][$field]) || empty($value[$section][$field])) {
                $result['message'] = sprintf(
                    __('Missing required field %s in %s section.', 'piper-privacy'),
                    $field,
                    $section
                );
                return $result;
            }
        }
    }

    // Validate qualifications
    $has_required_qualification = false;
    foreach ($value['dpo_details']['qualifications'] as $qualification) {
        if (in_array($qualification, $args['qualification_types'])) {
            $has_required_qualification = true;
            break;
        }
    }

    if (!$has_required_qualification) {
        $result['message'] = sprintf(
            __('DPO must have at least one of the following qualifications: %s.', 'piper-privacy'),
            implode(', ', $args['qualification_types'])
        );
        return $result;
    }

    // Validate independence
    foreach ($args['independence_criteria'] as $criterion) {
        if (!isset($value['dpo_details']['independence_safeguards'][$criterion]) || 
            !$value['dpo_details']['independence_safeguards'][$criterion]) {
            $result['message'] = sprintf(
                __('Missing independence criterion: %s.', 'piper-privacy'),
                $criterion
            );
            return $result;
        }
    }

    // Validate reporting line
    if ($value['dpo_details']['reporting_line'] !== 'highest_management') {
        $result['message'] = __('DPO must report to the highest level of management.', 'piper-privacy');
        return $result;
    }

    $result['valid'] = true;
    return $result;
}

/**
 * Validate Records of Processing Activities (ROPA)
 *
 * @param array $value ROPA data
 * @param array $args  Optional arguments
 * @return array Validation result
 */
function pp_validate_ropa($value, $args = []) {
    $result = [
        'valid' => false,
        'message' => ''
    ];

    if (!is_array($value)) {
        $result['message'] = __('ROPA data must be provided as an array.', 'piper-privacy');
        return $result;
    }

    // Get active requirements
    $active_requirements = pp_get_active_requirements();
    $is_gdpr_active = isset($active_requirements['jurisdictions']['gdpr']);

    $defaults = [
        'required_fields' => [
            'processing_details' => [
                'purpose',
                'categories_of_data',
                'data_subjects',
                'recipients',
                'retention_period'
            ],
            'security_measures' => [
                'technical_measures',
                'organizational_measures'
            ]
        ]
    ];

    // Add GDPR-specific requirements if active
    if ($is_gdpr_active) {
        $defaults['required_fields']['gdpr_specific'] = [
            'legal_basis',
            'transfers_to_third_countries',
            'transfer_safeguards'
        ];
    }

    $args = wp_parse_args($args, $defaults);

    foreach ($args['required_fields'] as $section => $fields) {
        if (!isset($value[$section]) || !is_array($value[$section])) {
            $result['message'] = sprintf(
                __('Missing or invalid %s section in ROPA.', 'piper-privacy'),
                $section
            );
            return $result;
        }

        foreach ($fields as $field) {
            if (!isset($value[$section][$field]) || empty($value[$section][$field])) {
                $result['message'] = sprintf(
                    __('Missing required field %s in %s section.', 'piper-privacy'),
                    $field,
                    $section
                );
                return $result;
            }
        }
    }

    // Validate retention periods
    if (!isset($value['processing_details']['retention_justification'])) {
        $result['message'] = __('Retention period justification is required.', 'piper-privacy');
        return $result;
    }

    // Validate security measures based on data sensitivity
    $sensitive_data = false;
    foreach ($value['processing_details']['categories_of_data'] as $category) {
        if (in_array($category, ['health', 'biometric', 'genetic', 'criminal'])) {
            $sensitive_data = true;
            break;
        }
    }

    if ($sensitive_data) {
        $required_measures = [
            'encryption',
            'access_control',
            'audit_logging',
            'backup'
        ];

        foreach ($required_measures as $measure) {
            if (!isset($value['security_measures']['technical_measures'][$measure]) || 
                !$value['security_measures']['technical_measures'][$measure]) {
                $result['message'] = sprintf(
                    __('Enhanced security measure %s required for sensitive data processing.', 'piper-privacy'),
                    $measure
                );
                return $result;
            }
        }
    }

    $result['valid'] = true;
    return $result;
}

/**
 * Validate International Transfer Requirements
 *
 * @param array $value Transfer requirements data
 * @param array $args  Optional arguments
 * @return array Validation result
 */
function pp_validate_international_transfers($value, $args = []) {
    $result = [
        'valid' => false,
        'message' => ''
    ];

    if (!is_array($value)) {
        $result['message'] = __('Transfer requirements data must be provided as an array.', 'piper-privacy');
        return $result;
    }

    // Get active requirements
    $active_requirements = pp_get_active_requirements();
    $is_gdpr_active = isset($active_requirements['jurisdictions']['gdpr']);

    $defaults = [
        'required_fields' => [
            'transfer_details' => [
                'recipient_country',
                'recipient_organization',
                'categories_of_data',
                'transfer_purpose'
            ],
            'security_measures' => [
                'technical_safeguards',
                'organizational_safeguards'
            ]
        ],
        'transfer_mechanisms' => [
            'contractual_clauses',
            'binding_corporate_rules',
            'codes_of_conduct',
            'certification'
        ]
    ];

    // Add GDPR-specific requirements if active
    if ($is_gdpr_active) {
        $defaults['required_fields']['gdpr_specific'] = [
            'adequacy_decision',
            'appropriate_safeguards',
            'derogation_grounds'
        ];
        $defaults['transfer_mechanisms'][] = 'standard_contractual_clauses';
    }

    $args = wp_parse_args($args, $defaults);

    foreach ($args['required_fields'] as $section => $fields) {
        if (!isset($value[$section]) || !is_array($value[$section])) {
            $result['message'] = sprintf(
                __('Missing or invalid %s section in transfer requirements.', 'piper-privacy'),
                $section
            );
            return $result;
        }

        foreach ($fields as $field) {
            if (!isset($value[$section][$field]) || empty($value[$section][$field])) {
                $result['message'] = sprintf(
                    __('Missing required field %s in %s section.', 'piper-privacy'),
                    $field,
                    $section
                );
                return $result;
            }
        }
    }

    // Validate transfer mechanism
    if (!isset($value['transfer_mechanism']) || 
        !in_array($value['transfer_mechanism'], $args['transfer_mechanisms'])) {
        $result['message'] = sprintf(
            __('Invalid transfer mechanism. Allowed mechanisms: %s.', 'piper-privacy'),
            implode(', ', $args['transfer_mechanisms'])
        );
        return $result;
    }

    // GDPR-specific validations
    if ($is_gdpr_active) {
        // Check for adequacy decision
        if (empty($value['gdpr_specific']['adequacy_decision']) && 
            empty($value['gdpr_specific']['appropriate_safeguards']) && 
            empty($value['gdpr_specific']['derogation_grounds'])) {
            $result['message'] = __('Under GDPR, transfers require either an adequacy decision, appropriate safeguards, or specific derogation grounds.', 'piper-privacy');
            return $result;
        }

        // Validate supplementary measures for non-adequate countries
        if (empty($value['gdpr_specific']['adequacy_decision']) && 
            empty($value['security_measures']['supplementary_measures'])) {
            $result['message'] = __('Supplementary measures required for transfers to non-adequate countries under GDPR.', 'piper-privacy');
            return $result;
        }
    }

    $result['valid'] = true;
    return $result;
}

/**
 * Validate Security Measures
 *
 * @param array $value Security measures data
 * @param array $args  Optional arguments
 * @return array Validation result
 */
function pp_validate_security_measures($value, $args = []) {
    $result = [
        'valid' => false,
        'message' => ''
    ];

    if (!is_array($value)) {
        $result['message'] = __('Security measures data must be provided as an array.', 'piper-privacy');
        return $result;
    }

    // Get active requirements
    $active_requirements = pp_get_active_requirements();
    $is_gdpr_active = isset($active_requirements['jurisdictions']['gdpr']);
    $is_healthcare = isset($active_requirements['industry_specific']['healthcare']);
    $is_financial = isset($active_requirements['industry_specific']['financial']);

    $defaults = [
        'required_fields' => [
            'technical_measures' => [
                'access_control',
                'encryption',
                'backup',
                'monitoring'
            ],
            'organizational_measures' => [
                'policies',
                'training',
                'incident_response',
                'audit'
            ],
            'risk_assessment' => [
                'threat_analysis',
                'vulnerability_assessment',
                'impact_evaluation',
                'controls_effectiveness'
            ]
        ]
    ];

    // Add industry-specific requirements
    if ($is_healthcare) {
        $defaults['required_fields']['healthcare_specific'] = [
            'phi_encryption',
            'emergency_access',
            'audit_trails',
            'authentication'
        ];
    }

    if ($is_financial) {
        $defaults['required_fields']['financial_specific'] = [
            'transaction_monitoring',
            'fraud_detection',
            'data_integrity',
            'secure_transmission'
        ];
    }

    $args = wp_parse_args($args, $defaults);

    foreach ($args['required_fields'] as $section => $fields) {
        if (!isset($value[$section]) || !is_array($value[$section])) {
            $result['message'] = sprintf(
                __('Missing or invalid %s section in security measures.', 'piper-privacy'),
                $section
            );
            return $result;
        }

        foreach ($fields as $field) {
            if (!isset($value[$section][$field]) || empty($value[$section][$field])) {
                $result['message'] = sprintf(
                    __('Missing required field %s in %s section.', 'piper-privacy'),
                    $field,
                    $section
                );
                return $result;
            }
        }
    }

    // Validate encryption requirements
    $encryption_requirements = ['data_at_rest', 'data_in_transit'];
    if ($is_healthcare) {
        $encryption_requirements[] = 'phi_specific';
    }
    if ($is_financial) {
        $encryption_requirements[] = 'pci_compliance';
    }

    foreach ($encryption_requirements as $req) {
        if (!isset($value['technical_measures']['encryption'][$req]) || 
            !$value['technical_measures']['encryption'][$req]) {
            $result['message'] = sprintf(
                __('Missing required encryption type: %s.', 'piper-privacy'),
                $req
            );
            return $result;
        }
    }

    // Validate access control
    if (!isset($value['technical_measures']['access_control']['rbac']) || 
        !$value['technical_measures']['access_control']['rbac']) {
        $result['message'] = __('Role-based access control (RBAC) is required.', 'piper-privacy');
        return $result;
    }

    // Validate monitoring and incident response
    if (!isset($value['technical_measures']['monitoring']['real_time']) || 
        !$value['technical_measures']['monitoring']['real_time']) {
        $result['message'] = __('Real-time security monitoring is required.', 'piper-privacy');
        return $result;
    }

    $result['valid'] = true;
    return $result;
}

/**
 * Validate Certification Mechanisms
 *
 * @param array $value Certification data
 * @param array $args  Optional arguments
 * @return array Validation result
 */
function pp_validate_certification($value, $args = []) {
    $result = [
        'valid' => false,
        'message' => ''
    ];

    if (!is_array($value)) {
        $result['message'] = __('Certification data must be provided as an array.', 'piper-privacy');
        return $result;
    }

    // Get active requirements
    $active_requirements = pp_get_active_requirements();
    $is_gdpr_active = isset($active_requirements['jurisdictions']['gdpr']);
    $is_healthcare = isset($active_requirements['industry_specific']['healthcare']);
    $is_financial = isset($active_requirements['industry_specific']['financial']);

    $defaults = [
        'required_fields' => [
            'certification_details' => [
                'scheme_name',
                'certification_body',
                'scope',
                'validity_period',
                'last_audit_date'
            ],
            'compliance_evidence' => [
                'policies',
                'procedures',
                'controls',
                'audit_results'
            ],
            'monitoring' => [
                'internal_audits',
                'external_audits',
                'continuous_monitoring',
                'improvement_actions'
            ]
        ],
        'certification_types' => [
            'iso27001',
            'iso27701',
            'soc2'
        ]
    ];

    // Add jurisdiction-specific certifications
    if ($is_gdpr_active) {
        $defaults['certification_types'][] = 'gdpr_certification';
    }
    if ($is_healthcare) {
        $defaults['certification_types'][] = 'hitrust';
    }
    if ($is_financial) {
        $defaults['certification_types'][] = 'pci_dss';
    }

    $args = wp_parse_args($args, $defaults);

    foreach ($args['required_fields'] as $section => $fields) {
        if (!isset($value[$section]) || !is_array($value[$section])) {
            $result['message'] = sprintf(
                __('Missing or invalid %s section in certification.', 'piper-privacy'),
                $section
            );
            return $result;
        }

        foreach ($fields as $field) {
            if (!isset($value[$section][$field]) || empty($value[$section][$field])) {
                $result['message'] = sprintf(
                    __('Missing required field %s in %s section.', 'piper-privacy'),
                    $field,
                    $section
                );
                return $result;
            }
        }
    }

    // Validate certification type
    if (!in_array($value['certification_details']['scheme_name'], $args['certification_types'])) {
        $result['message'] = sprintf(
            __('Invalid certification scheme. Allowed schemes: %s.', 'piper-privacy'),
            implode(', ', $args['certification_types'])
        );
        return $result;
    }

    // Validate certification validity
    $validity_end = strtotime($value['certification_details']['validity_period']['end']);
    $current_date = current_time('timestamp');

    if ($validity_end < $current_date) {
        $result['message'] = __('Certification has expired.', 'piper-privacy');
        return $result;
    }

    // Validate audit dates
    $last_audit = strtotime($value['certification_details']['last_audit_date']);
    $audit_interval = 365 * 24 * 60 * 60; // 1 year in seconds

    if (($current_date - $last_audit) > $audit_interval) {
        $result['message'] = __('Annual audit requirement not met.', 'piper-privacy');
        return $result;
    }

    $result['valid'] = true;
    return $result;
}
