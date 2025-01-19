<?php
/**
 * Field Helper Functions
 * 
 * @package PiperPrivacy
 * @subpackage PiperPrivacy/includes/helpers
 */

if (!defined('WPINC')) {
    die;
}

/**
 * Get field value
 *
 * @param string $field_name Field name
 * @param int    $post_id    Post ID (optional)
 * @return mixed Field value
 */
function pp_get_field($field_name, $post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    return rwmb_meta($field_name, ['object_type' => 'post'], $post_id);
}

/**
 * Update field value
 *
 * @param string $field_name Field name
 * @param mixed  $value      Field value
 * @param int    $post_id    Post ID (optional)
 * @return bool|int Meta ID if the key didn't exist, true on successful update, false on failure
 */
function pp_update_field($field_name, $value, $post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    return update_post_meta($post_id, $field_name, $value);
}

/**
 * Get group field value
 *
 * @param string $field_name Field name
 * @param int    $post_id    Post ID (optional)
 * @return array Field values
 */
function pp_get_group_field($field_name, $post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    return rwmb_meta($field_name, ['object_type' => 'post'], $post_id) ?: [];
}

/**
 * Display field value
 *
 * @param string $field_name Field name
 * @param int    $post_id    Post ID (optional)
 */
function pp_the_field($field_name, $post_id = null) {
    echo pp_get_field($field_name, $post_id);
}

/**
 * Get select field value with label
 *
 * @param string $field_name Field name
 * @param int    $post_id    Post ID (optional)
 * @return array Array with 'value' and 'label' keys
 */
function pp_get_select_field($field_name, $post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    $value = rwmb_meta($field_name, ['object_type' => 'post'], $post_id);
    $field = rwmb_get_field_settings($field_name);
    
    return [
        'value' => $value,
        'label' => isset($field['options'][$value]) ? $field['options'][$value] : $value
    ];
}

/**
 * Get checkbox list values with labels
 *
 * @param string $field_name Field name
 * @param int    $post_id    Post ID (optional)
 * @return array Array of selected values with their labels
 */
function pp_get_checkbox_list($field_name, $post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    $values = rwmb_meta($field_name, ['object_type' => 'post'], $post_id);
    if (!is_array($values)) {
        return [];
    }
    
    $field = rwmb_get_field_settings($field_name);
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
 * @param int    $post_id    Post ID (optional)
 * @return array File information including URL, path, and metadata
 */
function pp_get_file_field($field_name, $post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    return rwmb_meta($field_name, ['object_type' => 'post'], $post_id);
}

/**
 * Get image field with specified size
 *
 * @param string $field_name Field name
 * @param string $size       Image size (thumbnail, medium, large, full)
 * @param int    $post_id    Post ID (optional)
 * @return array Image information including URL and metadata
 */
function pp_get_image_field($field_name, $size = 'thumbnail', $post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    return rwmb_meta($field_name, ['size' => $size, 'object_type' => 'post'], $post_id);
}

/**
 * Get date field in specified format
 *
 * @param string $field_name Field name
 * @param string $format     Date format (default WordPress date format if not specified)
 * @param int    $post_id    Post ID (optional)
 * @return string Formatted date
 */
function pp_get_date_field($field_name, $format = '', $post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    if (empty($format)) {
        $format = get_option('date_format');
    }
    $value = rwmb_meta($field_name, ['object_type' => 'post'], $post_id);
    return $value ? date($format, strtotime($value)) : '';
}

/**
 * Get WYSIWYG field with formatting
 *
 * @param string $field_name Field name
 * @param int    $post_id    Post ID (optional)
 * @return string Formatted HTML content
 */
function pp_get_wysiwyg_field($field_name, $post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    $content = rwmb_meta($field_name, ['object_type' => 'post'], $post_id);
    return wpautop($content);
}

/**
 * Check if a checkbox field is checked
 *
 * @param string $field_name Field name
 * @param int    $post_id    Post ID (optional)
 * @return bool True if checked, false otherwise
 */
function pp_is_checked($field_name, $post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    return (bool) rwmb_meta($field_name, ['object_type' => 'post'], $post_id);
}

/**
 * Get group field count
 *
 * @param string $field_name Field name
 * @param int    $post_id    Post ID (optional)
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
 * @param int    $post_id    Post ID (optional)
 * @return string Formatted address
 */
function pp_get_address_field($field_name, $post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    $address = rwmb_meta($field_name, ['object_type' => 'post'], $post_id);
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
 * @param string $info       User information to return (display_name, email, etc.)
 * @param int    $post_id    Post ID (optional)
 * @return mixed User information
 */
function pp_get_user_field($field_name, $info = 'display_name', $post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    $user_id = rwmb_meta($field_name, ['object_type' => 'post'], $post_id);
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
 * @param int    $post_id    Post ID (optional)
 * @return array Array of term objects
 */
function pp_get_taxonomy_field($field_name, $post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    $terms = rwmb_meta($field_name, ['object_type' => 'post'], $post_id);
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
 * @param int    $post_id    Post ID (optional)
 * @return array Color information
 */
function pp_get_color_field($field_name, $post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    $color = rwmb_meta($field_name, ['object_type' => 'post'], $post_id);
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
 * @param int    $post_id    Post ID (optional)
 * @return array Map coordinates and location info
 */
function pp_get_map_field($field_name, $post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    $location = rwmb_meta($field_name, ['object_type' => 'post'], $post_id);
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
 * @param int    $post_id    Post ID (optional)
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
 * @param int    $post_id    Post ID (optional)
 * @return array Range information
 */
function pp_get_range_field($field_name, $post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    $value = rwmb_meta($field_name, ['object_type' => 'post'], $post_id);
    
    return [
        'value' => $value,
        'min' => rwmb_get_field_settings($field_name)['min'] ?? 0,
        'max' => rwmb_get_field_settings($field_name)['max'] ?? 100,
        'step' => rwmb_get_field_settings($field_name)['step'] ?? 1
    ];
}

/**
 * Get time field in specified format
 *
 * @param string $field_name Field name
 * @param string $format     Time format (default WordPress time format if not specified)
 * @param int    $post_id    Post ID (optional)
 * @return string Formatted time
 */
function pp_get_time_field($field_name, $format = '', $post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    if (empty($format)) {
        $format = get_option('time_format');
    }
    $value = rwmb_meta($field_name, ['object_type' => 'post'], $post_id);
    return $value ? date($format, strtotime($value)) : '';
}

/**
 * Get post field with specific post information
 *
 * @param string $field_name Field name
 * @param array  $fields     Post fields to return (default: ID, title, permalink)
 * @param int    $post_id    Post ID (optional)
 * @return array Post information
 */
function pp_get_post_field($field_name, $fields = [], $post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    $related_post = rwmb_meta($field_name, ['object_type' => 'post'], $post_id);
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
 * @param int    $post_id    Post ID (optional)
 * @return array Switch state information
 */
function pp_get_switch_field($field_name, $post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    $value = rwmb_meta($field_name, ['object_type' => 'post'], $post_id);
    $field = rwmb_get_field_settings($field_name);
    
    return [
        'value' => $value,
        'state' => $value ? 'on' : 'off',
        'label' => $value ? 
            ($field['label_on'] ?? 'On') : 
            ($field['label_off'] ?? 'Off')
    ];
}

/**
 * Get field choices for select, radio, or checkbox_list fields
 *
 * @param string $field_name Field name
 * @return array Array of available choices
 */
function pp_get_field_choices($field_name) {
    $field = rwmb_get_field_settings($field_name);
    return $field['options'] ?? [];
}

/**
 * Get formatted currency field
 *
 * @param string $field_name Field name
 * @param string $currency   Currency code (default: USD)
 * @param int    $post_id    Post ID (optional)
 * @return array Currency information
 */
function pp_get_currency_field($field_name, $currency = 'USD', $post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    $value = rwmb_meta($field_name, ['object_type' => 'post'], $post_id);
    
    return [
        'raw' => $value,
        'formatted' => number_format($value, 2),
        'currency' => $currency,
        'symbol' => get_currency_symbol($currency),
        'display' => get_currency_symbol($currency) . number_format($value, 2)
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
