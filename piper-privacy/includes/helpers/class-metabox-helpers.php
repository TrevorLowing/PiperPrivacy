<?php
declare(strict_types=1);

namespace PiperPrivacy\Includes\Helpers;

/**
 * MetaBox Helper Class
 * 
 * Provides MetaBox-specific helper functions
 */
class MetaboxHelpers {
    /**
     * Get field settings from MetaBox registry
     *
     * @param string $field_name Field name/key
     * @param string $meta_box_id Meta box ID (optional)
     * @return array|null Field settings or null if not found
     */
    public static function get_field_settings($field_name, $meta_box_id = '') {
        if (!function_exists('rwmb_get_registry')) {
            return null;
        }

        $registry = rwmb_get_registry('field');
        return $registry->get($field_name, $meta_box_id);
    }

    /**
     * Get all fields from a meta box
     *
     * @param string $meta_box_id Meta box ID
     * @return array Array of field settings
     */
    public static function get_meta_box_fields($meta_box_id) {
        if (!function_exists('rwmb_get_registry')) {
            return [];
        }

        $registry = rwmb_get_registry('meta_box');
        $meta_box = $registry->get($meta_box_id);
        
        return $meta_box ? $meta_box->fields : [];
    }

    /**
     * Get formatted value based on field type
     *
     * @param string $field_name Field name/key
     * @param mixed $value Raw value
     * @param int $post_id Post ID
     * @return mixed Formatted value
     */
    public static function format_field_value($field_name, $value, $post_id) {
        $settings = self::get_field_settings($field_name);
        if (!$settings) {
            return $value;
        }

        switch ($settings['type']) {
            case 'group':
                return self::format_group_value($value, $settings, $post_id);
            case 'file':
            case 'file_advanced':
                return self::format_file_value($value, $settings);
            case 'image':
            case 'image_advanced':
                return self::format_image_value($value, $settings);
            case 'date':
                return self::format_date_value($value, $settings);
            case 'time':
                return self::format_time_value($value, $settings);
            case 'datetime':
                return self::format_datetime_value($value, $settings);
            case 'taxonomy':
            case 'taxonomy_advanced':
                return self::format_taxonomy_value($value, $settings);
            case 'user':
                return self::format_user_value($value, $settings);
            case 'post':
                return self::format_post_value($value, $settings);
            default:
                return $value;
        }
    }

    /**
     * Format group field value
     *
     * @param mixed $value Raw value
     * @param array $settings Field settings
     * @param int $post_id Post ID
     * @return array Formatted group value
     */
    private static function format_group_value($value, $settings, $post_id) {
        if (!is_array($value)) {
            return [];
        }

        $formatted = [];
        foreach ($value as $group_index => $group_value) {
            $formatted[$group_index] = [];
            foreach ($settings['fields'] as $sub_field) {
                $sub_value = $group_value[$sub_field['id']] ?? null;
                $formatted[$group_index][$sub_field['id']] = self::format_field_value(
                    $sub_field['id'],
                    $sub_value,
                    $post_id
                );
            }
        }

        return $formatted;
    }

    /**
     * Format file field value
     *
     * @param mixed $value Raw value
     * @param array $settings Field settings
     * @return array Formatted file value
     */
    private static function format_file_value($value, $settings) {
        if (!$value) {
            return [];
        }

        $files = [];
        foreach ((array) $value as $file_id) {
            $file = get_attached_file($file_id);
            if ($file) {
                $files[] = [
                    'id' => $file_id,
                    'url' => wp_get_attachment_url($file_id),
                    'path' => $file,
                    'name' => basename($file),
                    'size' => filesize($file),
                    'mime_type' => get_post_mime_type($file_id),
                ];
            }
        }

        return $files;
    }

    /**
     * Format image field value
     *
     * @param mixed $value Raw value
     * @param array $settings Field settings
     * @return array Formatted image value
     */
    private static function format_image_value($value, $settings) {
        if (!$value) {
            return [];
        }

        $images = [];
        foreach ((array) $value as $image_id) {
            $image = wp_get_attachment_image_src($image_id, 'full');
            if ($image) {
                $images[] = [
                    'id' => $image_id,
                    'url' => $image[0],
                    'width' => $image[1],
                    'height' => $image[2],
                    'alt' => get_post_meta($image_id, '_wp_attachment_image_alt', true),
                    'title' => get_the_title($image_id),
                    'caption' => wp_get_attachment_caption($image_id),
                ];
            }
        }

        return $images;
    }

    /**
     * Format date value
     *
     * @param mixed $value Raw value
     * @param array $settings Field settings
     * @return string|null Formatted date
     */
    private static function format_date_value($value, $settings) {
        if (!$value) {
            return null;
        }

        $timestamp = is_numeric($value) ? (int) $value : strtotime($value);
        return $timestamp ? date_i18n(get_option('date_format'), $timestamp) : null;
    }

    /**
     * Format time value
     *
     * @param mixed $value Raw value
     * @param array $settings Field settings
     * @return string|null Formatted time
     */
    private static function format_time_value($value, $settings) {
        if (!$value) {
            return null;
        }

        $timestamp = is_numeric($value) ? (int) $value : strtotime($value);
        return $timestamp ? date_i18n(get_option('time_format'), $timestamp) : null;
    }

    /**
     * Format datetime value
     *
     * @param mixed $value Raw value
     * @param array $settings Field settings
     * @return string|null Formatted datetime
     */
    private static function format_datetime_value($value, $settings) {
        if (!$value) {
            return null;
        }

        $timestamp = is_numeric($value) ? (int) $value : strtotime($value);
        return $timestamp ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $timestamp) : null;
    }

    /**
     * Format taxonomy value
     *
     * @param mixed $value Raw value
     * @param array $settings Field settings
     * @return array Formatted taxonomy value
     */
    private static function format_taxonomy_value($value, $settings) {
        if (!$value) {
            return [];
        }

        $terms = [];
        foreach ((array) $value as $term_id) {
            $term = get_term($term_id);
            if (!is_wp_error($term) && $term) {
                $terms[] = [
                    'id' => $term->term_id,
                    'name' => $term->name,
                    'slug' => $term->slug,
                    'taxonomy' => $term->taxonomy,
                    'description' => $term->description,
                    'parent' => $term->parent,
                    'count' => $term->count,
                ];
            }
        }

        return $terms;
    }

    /**
     * Format user value
     *
     * @param mixed $value Raw value
     * @param array $settings Field settings
     * @return array Formatted user value
     */
    private static function format_user_value($value, $settings) {
        if (!$value) {
            return [];
        }

        $users = [];
        foreach ((array) $value as $user_id) {
            $user = get_userdata($user_id);
            if ($user) {
                $users[] = [
                    'id' => $user->ID,
                    'login' => $user->user_login,
                    'email' => $user->user_email,
                    'display_name' => $user->display_name,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'roles' => $user->roles,
                ];
            }
        }

        return $users;
    }

    /**
     * Format post value
     *
     * @param mixed $value Raw value
     * @param array $settings Field settings
     * @return array Formatted post value
     */
    private static function format_post_value($value, $settings) {
        if (!$value) {
            return [];
        }

        $posts = [];
        foreach ((array) $value as $post_id) {
            $post = get_post($post_id);
            if ($post) {
                $posts[] = [
                    'id' => $post->ID,
                    'title' => $post->post_title,
                    'slug' => $post->post_name,
                    'type' => $post->post_type,
                    'status' => $post->post_status,
                    'permalink' => get_permalink($post->ID),
                    'excerpt' => get_the_excerpt($post),
                ];
            }
        }

        return $posts;
    }
}
