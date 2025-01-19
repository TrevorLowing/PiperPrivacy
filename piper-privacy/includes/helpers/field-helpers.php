<?php
namespace PiperPrivacy\Includes\Helpers;

/**
 * Field helper functions for managing custom fields
 * Supports both MetaBox and ACF during transition
 */

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
            return $value;
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
    if (function_exists('rwmb_get_field_settings')) {
        $settings = rwmb_get_field_settings($field_name);
        if ($settings) {
            return $settings;
        }
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
    if (function_exists('rwmb_meta')) {
        return rwmb_get_field_settings($field_name) !== null;
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

        case 'number':
            if (!is_numeric($value)) {
                return new \WP_Error(
                    'invalid_number',
                    __('Value must be a number', 'piper-privacy')
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
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    return pp_get_field($field_name, $post_id) ?: [];
}

/**
 * Get select field value with label
 *
 * @param string $field_name Field name
 * @param int|null $post_id Post ID (optional)
 * @return array Array with 'value' and 'label' keys
 */
function pp_get_select_field($field_name, $post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    $value = pp_get_field($field_name, $post_id);
    $field = pp_get_field_settings($field_name);
    
    return [
        'value' => $value,
        'label' => isset($field['options'][$value]) ? $field['options'][$value] : $value
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
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    $values = pp_get_field($field_name, $post_id);
    if (!is_array($values)) {
        return [];
    }
    
    $field = pp_get_field_settings($field_name);
    $result = [];
    
    foreach ($values as $value) {
        $result[] = [
            'value' => $value,
            'label' => isset($field['options'][$value]) ? $field['options'][$value] : $value
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
    if (!$post_id) {
        $post_id = get_the_ID();
    }
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
    if (!$post_id) {
        $post_id = get_the_ID();
    }
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
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    if (empty($format)) {
        $format = get_option('date_format');
    }
    $value = pp_get_field($field_name, $post_id);
    return $value ? date($format, strtotime($value)) : '';
}

/**
 * Get WYSIWYG field with formatting
 *
 * @param string $field_name Field name
 * @param int|null $post_id Post ID (optional)
 * @return string Formatted HTML content
 */
function pp_get_wysiwyg_field($field_name, $post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    $content = pp_get_field($field_name, $post_id);
    return wpautop($content);
}

/**
 * Check if a checkbox field is checked
 *
 * @param string $field_name Field name
 * @param int|null $post_id Post ID (optional)
 * @return bool True if checked, false otherwise
 */
function pp_is_checked($field_name, $post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    return (bool) pp_get_field($field_name, $post_id);
}

/**
 * Get group field count
 *
 * @param string $field_name Field name
 * @param int|null $post_id Post ID (optional)
 * @return int Number of items in the group
 */
function pp_get_group_count($field_name, $post_id = null) {
    $group = pp_get_group_field($field_name, $post_id);
    return is_array($group) ? count($group) : 0;
}

/**
 * Get formatted address from address field
 *
 * @param string $field_name Field name
 * @param int|null $post_id Post ID (optional)
 * @return string Formatted address
 */
function pp_get_address_field($field_name, $post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    $address = pp_get_field($field_name, $post_id);
    if (!is_array($address)) {
        return '';
    }
    
    $parts = [];
    if (!empty($address['street'])) $parts[] = $address['street'];
    if (!empty($address['city'])) $parts[] = $address['city'];
    if (!empty($address['state'])) $parts[] = $address['state'];
    if (!empty($address['postal'])) $parts[] = $address['postal'];
    if (!empty($address['country'])) $parts[] = $address['country'];
    
    return implode(', ', $parts);
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
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    $user_id = pp_get_field($field_name, $post_id);
    if (!$user_id) {
        return '';
    }
    
    $user = get_userdata($user_id);
    return $user ? $user->$info : '';
}

/**
 * Get taxonomy field values with full term information
 *
 * @param string $field_name Field name
 * @param int|null $post_id Post ID (optional)
 * @return array Array of term objects
 */
function pp_get_taxonomy_field($field_name, $post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    $terms = pp_get_field($field_name, $post_id);
    if (!is_array($terms)) {
        return [];
    }
    
    return array_map(function($term) {
        return [
            'id' => $term->term_id,
            'name' => $term->name,
            'slug' => $term->slug,
            'description' => $term->description,
            'url' => get_term_link($term)
        ];
    }, $terms);
}

/**
 * Get color field with RGB and hex values
 *
 * @param string $field_name Field name
 * @param int|null $post_id Post ID (optional)
 * @return array Color information
 */
function pp_get_color_field($field_name, $post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    $color = pp_get_field($field_name, $post_id);
    if (empty($color)) {
        return null;
    }
    
    // Convert hex to RGB
    $hex = ltrim($color, '#');
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    return [
        'hex' => $color,
        'rgb' => "rgb($r,$g,$b)",
        'rgba' => "rgba($r,$g,$b,1)",
        'components' => [
            'r' => $r,
            'g' => $g,
            'b' => $b
        ]
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
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    $location = pp_get_field($field_name, $post_id);
    if (!is_array($location)) {
        return null;
    }
    
    return [
        'lat' => isset($location['latitude']) ? (float)$location['latitude'] : 0,
        'lng' => isset($location['longitude']) ? (float)$location['longitude'] : 0,
        'zoom' => isset($location['zoom']) ? (int)$location['zoom'] : 14,
        'address' => isset($location['address']) ? $location['address'] : '',
        'formatted' => sprintf(
            '%s, %s',
            isset($location['latitude']) ? $location['latitude'] : '',
            isset($location['longitude']) ? $location['longitude'] : ''
        )
    ];
}

/**
 * Get key-value field as associative array
 *
 * @param string $field_name Field name
 * @param int|null $post_id Post ID (optional)
 * @return array Associative array of key-value pairs
 */
function pp_get_key_value_field($field_name, $post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    $pairs = pp_get_group_field($field_name, $post_id);
    if (!is_array($pairs)) {
        return [];
    }
    
    $result = [];
    foreach ($pairs as $pair) {
        if (isset($pair['key']) && isset($pair['value'])) {
            $result[$pair['key']] = $pair['value'];
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
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    $value = pp_get_field($field_name, $post_id);
    
    return [
        'value' => $value,
        'min' => pp_get_field_settings($field_name)['min'] ?? 0,
        'max' => pp_get_field_settings($field_name)['max'] ?? 100,
        'step' => pp_get_field_settings($field_name)['step'] ?? 1
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
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    if (empty($format)) {
        $format = get_option('time_format');
    }
    $value = pp_get_field($field_name, $post_id);
    return $value ? date($format, strtotime($value)) : '';
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
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    $related_post = pp_get_field($field_name, $post_id);
    if (!$related_post) {
        return null;
    }
    
    $default_fields = [
        'ID' => $related_post->ID,
        'title' => get_the_title($related_post->ID),
        'permalink' => get_permalink($related_post->ID)
    ];
    
    if (empty($fields)) {
        return $default_fields;
    }
    
    $result = [];
    foreach ($fields as $field) {
        if (isset($related_post->$field)) {
            $result[$field] = $related_post->$field;
        }
    }
    
    return array_merge($default_fields, $result);
}

/**
 * Get switch field value with custom states
 *
 * @param string $field_name Field name
 * @param int|null $post_id Post ID (optional)
 * @return array Switch state information
 */
function pp_get_switch_field($field_name, $post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    $value = pp_get_field($field_name, $post_id);
    $field = pp_get_field_settings($field_name);
    
    return [
        'value' => $value,
        'state' => $value ? 'on' : 'off',
        'label' => $value ? 
            ($field['label_on'] ?? 'On') : 
            ($field['label_off'] ?? 'Off')
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
        // Add more as needed
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
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    $value = pp_get_field($field_name, $post_id);
    
    return [
        'raw' => $value,
        'formatted' => number_format($value, 2),
        'currency' => $currency,
        'symbol' => get_currency_symbol($currency),
        'display' => get_currency_symbol($currency) . number_format($value, 2)
    ];
}
