<?php
/**
 * Forms Bootstrap File
 *
 * @package PiperPrivacy
 */

namespace PiperPrivacy\Forms;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Initialize forms functionality
 */
function init_forms() {
    // Load required files
    require_once PIPER_PRIVACY_PATH . 'includes/forms/class-form-processor.php';
    require_once PIPER_PRIVACY_PATH . 'includes/forms/class-form-storage.php';
    require_once PIPER_PRIVACY_PATH . 'includes/forms/class-form-notifications.php';
    require_once PIPER_PRIVACY_PATH . 'includes/forms/class-forms-integration.php';

    // Initialize forms integration
    $forms = new Forms_Integration();

    // Allow other plugins to access forms integration
    $GLOBALS['piper_privacy_forms'] = $forms;

    // Add hooks for plugin activation/deactivation
    register_activation_hook(PIPER_PRIVACY_FILE, __NAMESPACE__ . '\activate_forms');
    register_deactivation_hook(PIPER_PRIVACY_FILE, __NAMESPACE__ . '\deactivate_forms');
}
add_action('plugins_loaded', __NAMESPACE__ . '\init_forms');

/**
 * Activation hook callback
 */
function activate_forms() {
    // Create required database tables
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    // Register post types
    $storage = new Form_Storage();
    $storage->register_post_types();

    // Clear permalinks
    flush_rewrite_rules();
}

/**
 * Deactivation hook callback
 */
function deactivate_forms() {
    // Unregister post types
    unregister_post_type('privacy_collection');
    unregister_post_type('privacy_threshold');
    unregister_post_type('privacy_impact');

    // Clear permalinks
    flush_rewrite_rules();
}

/**
 * Get forms instance
 *
 * @return Forms_Integration|null
 */
function piper_privacy_forms() {
    return $GLOBALS['piper_privacy_forms'] ?? null;
}

/**
 * Helper function to render collection form
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function render_collection_form($atts = []) {
    $forms = piper_privacy_forms();
    return $forms ? $forms->render_collection_form($atts) : '';
}

/**
 * Helper function to render threshold form
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function render_threshold_form($atts = []) {
    $forms = piper_privacy_forms();
    return $forms ? $forms->render_threshold_form($atts) : '';
}

/**
 * Helper function to render impact form
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function render_impact_form($atts = []) {
    $forms = piper_privacy_forms();
    return $forms ? $forms->render_impact_form($atts) : '';
}

/**
 * Helper function to get form statistics
 *
 * @return array
 */
function get_form_stats() {
    $forms = piper_privacy_forms();
    return $forms ? $forms->get_form_stats() : [];
}

/**
 * Helper function to export form data
 *
 * @param string $form_type Form type.
 * @param array  $args Query arguments.
 * @return array
 */
function export_form_data($form_type, $args = []) {
    $forms = piper_privacy_forms();
    return $forms ? $forms->export_form_data($form_type, $args) : [];
}
