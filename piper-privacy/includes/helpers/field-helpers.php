<?php
namespace PiperPrivacy\Includes\Helpers;

/**
 * Field helper functions for managing custom fields
 * Supports both MetaBox and ACF during transition
 */

use PiperPrivacy\Includes\Helpers\MetaboxHelpers;

/**
 * Get a field value
 *
 * @param string $field_name Field name/key
 * @param int|null $post_id Post ID (optional)
 * @return mixed Field value
 */
function pp_get_field($field_name, $post_id = null) {
    // Get post ID if not provided
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    // Try MetaBox first
    if (function_exists('rwmb_meta')) {
        $value = rwmb_meta($field_name, '', $post_id);
        if ($value !== '') {
            return MetaboxHelpers::format_field_value($field_name, $value, $post_id);
        }
    }

    // Fallback to ACF
    if (function_exists('get_field')) {
        return get_field($field_name, $post_id);
    }

    // Last resort: get post meta
    return get_post_meta($post_id, $field_name, true);
}

/**
 * Update a field value
 *
 * @param string $field_name Field name/key
 * @param mixed $value Field value
 * @param int|null $post_id Post ID (optional)
 * @return bool Success status
 */
function pp_update_field($field_name, $value, $post_id = null) {
    // Get post ID if not provided
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    // Try MetaBox first
    if (function_exists('rwmb_meta')) {
        return rwmb_meta_update($field_name, $value, $post_id);
    }

    // Fallback to ACF
    if (function_exists('update_field')) {
        return update_field($field_name, $value, $post_id);
    }

    // Last resort: update post meta
    return update_post_meta($post_id, $field_name, $value);
}

/**
 * Display a field value
 *
 * @param string $field_name Field name/key
 * @param int|null $post_id Post ID (optional)
 */
function pp_the_field($field_name, $post_id = null) {
    echo pp_get_field($field_name, $post_id);
}

/**
 * Get field settings
 *
 * @param string $field_name Field name/key
 * @return array Field settings
 */
function pp_get_field_settings($field_name) {
    // Try MetaBox first
    $settings = MetaboxHelpers::get_field_settings($field_name);
    if ($settings) {
        return $settings;
    }

    // Fallback to ACF
    if (function_exists('get_field_object')) {
        $field = get_field_object($field_name);
        if ($field) {
            return [
                'type' => $field['type'],
                'name' => $field['label'],
                'id' => $field['name'],
                'options' => $field['choices'] ?? [],
                'required' => $field['required'] ?? false,
                'desc' => $field['instructions'] ?? '',
                'default' => $field['default_value'] ?? '',
            ];
        }
    }

    return [];
}

/**
 * Get field choices/options
 *
 * @param string $field_name Field name/key
 * @return array Field choices
 */
function pp_get_field_choices($field_name) {
    $settings = pp_get_field_settings($field_name);
    return $settings['options'] ?? [];
}

/**
 * Check if a field exists
 *
 * @param string $field_name Field name/key
 * @param int|null $post_id Post ID (optional)
 * @return bool Whether the field exists
 */
function pp_field_exists($field_name, $post_id = null) {
    // Try MetaBox
    $settings = MetaboxHelpers::get_field_settings($field_name);
    if ($settings) {
        return true;
    }

    // Try ACF
    if (function_exists('get_field_object')) {
        return get_field_object($field_name, $post_id) !== null;
    }

    return false;
}

/**
 * Get all fields for a post
 *
 * @param int|null $post_id Post ID (optional)
 * @return array All field values
 */
function pp_get_fields($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    // Try MetaBox first
    if (function_exists('rwmb_meta')) {
        $meta = rwmb_meta_all($post_id);
        if (!empty($meta)) {
            // Format all values
            foreach ($meta as $field_name => $value) {
                $meta[$field_name] = MetaboxHelpers::format_field_value($field_name, $value, $post_id);
            }
            return $meta;
        }
    }

    // Fallback to ACF
    if (function_exists('get_fields')) {
        return get_fields($post_id) ?: [];
    }

    // Last resort: get all post meta
    return get_post_meta($post_id);
}

/**
 * Validate field value
 *
 * @param string $field_name Field name/key
 * @param mixed $value Value to validate
 * @return bool|WP_Error True if valid, WP_Error if invalid
 */
function pp_validate_field($field_name, $value) {
    $settings = pp_get_field_settings($field_name);
    
    if (empty($settings)) {
        return true;
    }

    // Required check
    if (!empty($settings['required']) && empty($value)) {
        return new \WP_Error(
            'required_field',
            sprintf(__('Field %s is required', 'piper-privacy'), $settings['name'])
        );
    }

    // Type validation
    switch ($settings['type']) {
        case 'email':
            if (!is_email($value)) {
                return new \WP_Error(
                    'invalid_email',
                    __('Invalid email address', 'piper-privacy')
                );
            }
            break;
            
        case 'url':
            if (!filter_var($value, FILTER_VALIDATE_URL)) {
                return new \WP_Error(
                    'invalid_url',
                    __('Invalid URL', 'piper-privacy')
                );
            }
            break;
            
        case 'number':
            if (!is_numeric($value)) {
                return new \WP_Error(
                    'invalid_number',
                    __('Invalid number', 'piper-privacy')
                );
            }
            break;
    }

    return true;
}

/**
 * Get group field value
 *
 * @param string $field_name Field name
 * @param int|null $post_id Post ID (optional)
 * @return array Field values
 */
function pp_get_group_field($field_name, $post_id = null) {
    $value = pp_get_field($field_name, $post_id);
    return is_array($value) ? $value : [];
}

/**
 * Get select field value with label
 *
 * @param string $field_name Field name
 * @param int|null $post_id Post ID (optional)
 * @return array Array with 'value' and 'label' keys
 */
function pp_get_select_field($field_name, $post_id = null) {
    $value = pp_get_field($field_name, $post_id);
    $choices = pp_get_field_choices($field_name);
    
    return [
        'value' => $value,
        'label' => $choices[$value] ?? $value,
    ];
}

/**
 * Get checkbox list values with labels
 *
 * @param string $field_name Field name
 * @param int|null $post_id Post ID (optional)
 * @return array Array of selected values with their labels
 */
function pp_get_checkbox_list($field_name, $post_id = null) {
    $values = (array) pp_get_field($field_name, $post_id);
    $choices = pp_get_field_choices($field_name);
    
    $result = [];
    foreach ($values as $value) {
        $result[] = [
            'value' => $value,
            'label' => $choices[$value] ?? $value,
        ];
    }
    
    return $result;
}

/**
 * Get file field value with full information
 *
 * @param string $field_name Field name
 * @param int|null $post_id Post ID (optional)
 * @return array File information including URL, path, and metadata
 */
function pp_get_file_field($field_name, $post_id = null) {
    return pp_get_field($field_name, $post_id);
}

/**
 * Get image field with specified size
 *
 * @param string $field_name Field name
 * @param string $size Image size (thumbnail, medium, large, full)
 * @param int|null $post_id Post ID (optional)
 * @return array Image information including URL and metadata
 */
function pp_get_image_field($field_name, $size = 'thumbnail', $post_id = null) {
    return pp_get_field($field_name, $post_id);
}

/**
 * Get date field in specified format
 *
 * @param string $field_name Field name
 * @param string $format Date format (default WordPress date format if not specified)
 * @param int|null $post_id Post ID (optional)
 * @return string Formatted date
 */
function pp_get_date_field($field_name, $format = '', $post_id = null) {
    $value = pp_get_field($field_name, $post_id);
    if (!$value) {
        return '';
    }

    $format = $format ?: get_option('date_format');
    return date_i18n($format, strtotime($value));
}

/**
 * Get WYSIWYG field with formatting
 *
 * @param string $field_name Field name
 * @param int|null $post_id Post ID (optional)
 * @return string Formatted HTML content
 */
function pp_get_wysiwyg_field($field_name, $post_id = null) {
    $value = pp_get_field($field_name, $post_id);
    return wpautop($value);
}

/**
 * Check if a checkbox field is checked
 *
 * @param string $field_name Field name
 * @param int|null $post_id Post ID (optional)
 * @return bool True if checked, false otherwise
 */
function pp_is_checked($field_name, $post_id = null) {
    $value = pp_get_field($field_name, $post_id);
    return !empty($value);
}

/**
 * Get group field count
 *
 * @param string $field_name Field name
 * @param int|null $post_id Post ID (optional)
 * @return int Number of items in the group
 */
function pp_get_group_count($field_name, $post_id = null) {
    $value = pp_get_field($field_name, $post_id);
    return is_array($value) ? count($value) : 0;
}

/**
 * Get formatted address from address field
 *
 * @param string $field_name Field name
 * @param int|null $post_id Post ID (optional)
 * @return string Formatted address
 */
function pp_get_address_field($field_name, $post_id = null) {
    $address = pp_get_field($field_name, $post_id);
    if (!is_array($address)) {
        return '';
    }

    $parts = [];
    if (!empty($address['street'])) {
        $parts[] = $address['street'];
    }
    if (!empty($address['city'])) {
        $city_parts = [$address['city']];
        if (!empty($address['state'])) {
            $city_parts[] = $address['state'];
        }
        if (!empty($address['zip'])) {
            $city_parts[] = $address['zip'];
        }
        $parts[] = implode(', ', $city_parts);
    }
    if (!empty($address['country'])) {
        $parts[] = $address['country'];
    }

    return implode("\n", $parts);
}

/**
 * Get user field with specific information
 *
 * @param string $field_name Field name
 * @param string $info User information to return (display_name, email, etc.)
 * @param int|null $post_id Post ID (optional)
 * @return mixed User information
 */
function pp_get_user_field($field_name, $info = 'display_name', $post_id = null) {
    $value = pp_get_field($field_name, $post_id);
    if (!$value) {
        return '';
    }

    $user = get_userdata($value);
    if (!$user) {
        return '';
    }

    return $user->$info ?? '';
}

/**
 * Get taxonomy field values with full term information
 *
 * @param string $field_name Field name
 * @param int|null $post_id Post ID (optional)
 * @return array Array of term objects
 */
function pp_get_taxonomy_field($field_name, $post_id = null) {
    return pp_get_field($field_name, $post_id);
}

/**
 * Get color field with RGB and hex values
 *
 * @param string $field_name Field name
 * @param int|null $post_id Post ID (optional)
 * @return array Color information
 */
function pp_get_color_field($field_name, $post_id = null) {
    $value = pp_get_field($field_name, $post_id);
    if (!$value) {
        return [
            'hex' => '',
            'rgb' => '',
            'rgba' => '',
        ];
    }

    // Convert hex to RGB
    $hex = ltrim($value, '#');
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));

    return [
        'hex' => $value,
        'rgb' => "rgb($r, $g, $b)",
        'rgba' => "rgba($r, $g, $b, 1)",
    ];
}

/**
 * Get map field with formatted coordinates
 *
 * @param string $field_name Field name
 * @param int|null $post_id Post ID (optional)
 * @return array Map coordinates and location info
 */
function pp_get_map_field($field_name, $post_id = null) {
    return pp_get_field($field_name, $post_id);
}

/**
 * Get key-value field as associative array
 *
 * @param string $field_name Field name
 * @param int|null $post_id Post ID (optional)
 * @return array Associative array of key-value pairs
 */
function pp_get_key_value_field($field_name, $post_id = null) {
    $value = pp_get_field($field_name, $post_id);
    if (!is_array($value)) {
        return [];
    }

    $result = [];
    foreach ($value as $item) {
        if (isset($item['key']) && isset($item['value'])) {
            $result[$item['key']] = $item['value'];
        }
    }

    return $result;
}

/**
 * Get range field with min and max values
 *
 * @param string $field_name Field name
 * @param int|null $post_id Post ID (optional)
 * @return array Range information
 */
function pp_get_range_field($field_name, $post_id = null) {
    $value = pp_get_field($field_name, $post_id);
    if (!is_array($value)) {
        return [
            'min' => 0,
            'max' => 0,
        ];
    }

    return [
        'min' => $value['min'] ?? 0,
        'max' => $value['max'] ?? 0,
    ];
}

/**
 * Get time field in specified format
 *
 * @param string $field_name Field name
 * @param string $format Time format (default WordPress time format if not specified)
 * @param int|null $post_id Post ID (optional)
 * @return string Formatted time
 */
function pp_get_time_field($field_name, $format = '', $post_id = null) {
    $value = pp_get_field($field_name, $post_id);
    if (!$value) {
        return '';
    }

    $format = $format ?: get_option('time_format');
    return date_i18n($format, strtotime($value));
}

/**
 * Get post field with specific post information
 *
 * @param string $field_name Field name
 * @param array $fields Post fields to return (default: ID, title, permalink)
 * @param int|null $post_id Post ID (optional)
 * @return array Post information
 */
function pp_get_post_field($field_name, $fields = [], $post_id = null) {
    return pp_get_field($field_name, $post_id);
}

/**
 * Get switch field value with custom states
 *
 * @param string $field_name Field name
 * @param int|null $post_id Post ID (optional)
 * @return array Switch state information
 */
function pp_get_switch_field($field_name, $post_id = null) {
    $value = pp_get_field($field_name, $post_id);
    $settings = pp_get_field_settings($field_name);

    $states = $settings['states'] ?? [
        'on' => __('On', 'piper-privacy'),
        'off' => __('Off', 'piper-privacy'),
    ];

    return [
        'value' => $value,
        'label' => $states[$value] ?? $value,
        'is_on' => $value === 'on' || $value === true || $value === 1,
    ];
}

/**
 * Helper function to get currency symbol
 *
 * @param string $currency Currency code
 * @return string Currency symbol
 */
function get_currency_symbol($currency) {
    $symbols = [
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
        'JPY' => '¥',
    ];

    return $symbols[$currency] ?? $currency;
}

/**
 * Get formatted currency field
 *
 * @param string $field_name Field name
 * @param string $currency Currency code (default: USD)
 * @param int|null $post_id Post ID (optional)
 * @return array Currency information
 */
function pp_get_currency_field($field_name, $currency = 'USD', $post_id = null) {
    $value = pp_get_field($field_name, $post_id);
    if (!$value) {
        return [
            'amount' => 0,
            'currency' => $currency,
            'symbol' => get_currency_symbol($currency),
            'formatted' => get_currency_symbol($currency) . '0.00',
        ];
    }

    return [
        'amount' => $value,
        'currency' => $currency,
        'symbol' => get_currency_symbol($currency),
        'formatted' => get_currency_symbol($currency) . number_format($value, 2),
    ];
}
