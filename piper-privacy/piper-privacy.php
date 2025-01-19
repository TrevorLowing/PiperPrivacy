<?php
/**
 * The plugin bootstrap file
 *
 * @wordpress-plugin
 * Plugin Name:       PiperPrivacy
 * Plugin URI:        https://example.com/piper-privacy/
 * Description:       Complete privacy management system with breach notification, risk assessment, and compliance management.
 * Version:           1.0.0
 * Requires PHP:      8.0
 * Requires at least: 6.0
 * Author:            Your Name
 * Author URI:        https://example.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       piper-privacy
 * Domain Path:       /languages
 * 
 * Required plugins:
 * - Advanced Custom Fields (advanced-custom-fields/acf.php)
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Check if all required plugins are active
 * 
 * @return bool|WP_Error Returns true if all dependencies are met, WP_Error otherwise
 */
function piper_privacy_check_dependencies() {
    // Include plugin functions if not already included
    if (!function_exists('is_plugin_active')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    $required_plugins = [
        'advanced-custom-fields/acf.php' => 'Advanced Custom Fields',
        // Also check for ACF Pro in case that's installed instead
        'advanced-custom-fields-pro/acf.php' => 'Advanced Custom Fields Pro'
    ];

    $missing_plugins = [];
    $found_acf = false;
    foreach ($required_plugins as $plugin => $name) {
        // For ACF, we only need one version (either free or pro)
        if (strpos($plugin, 'advanced-custom-fields') !== false) {
            if (is_plugin_active($plugin)) {
                $found_acf = true;
            }
            continue;
        }
        // For other plugins, check normally
        if (!is_plugin_active($plugin)) {
            $missing_plugins[] = $name;
        }
    }

    // If neither ACF version was found, add it to missing plugins
    if (!$found_acf) {
        $missing_plugins[] = 'Advanced Custom Fields (or Pro version)';
    }

    if (!empty($missing_plugins)) {
        return new WP_Error(
            'missing_dependencies',
            sprintf(
                __('PiperPrivacy requires the following plugins to be installed and activated: %s', 'piper-privacy'),
                implode(', ', $missing_plugins)
            )
        );
    }

    return true;
}

/**
 * Display admin notice for missing dependencies
 */
function piper_privacy_admin_notice_dependencies() {
    if (!current_user_can('activate_plugins')) {
        return;
    }

    // Include plugin functions if not already included
    if (!function_exists('is_plugin_active')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    $check = piper_privacy_check_dependencies();
    if (is_wp_error($check)) {
        ?>
        <div class="notice notice-error">
            <p><?php echo esc_html($check->get_error_message()); ?></p>
        </div>
        <?php
    }
}

// Define plugin constants
define('PIPER_PRIVACY_VERSION', '1.0.0');
define('PIPER_PRIVACY_DIR', plugin_dir_path(__FILE__));
define('PIPER_PRIVACY_URL', plugin_dir_url(__FILE__));
define('PIPER_PRIVACY_DEBUG', true);

// Initialize error logging
ini_set('log_errors', 1);
ini_set('error_log', PIPER_PRIVACY_DIR . 'debug.log');
ini_set('display_errors', 1);
error_reporting(E_ALL);

error_log('PiperPrivacy plugin initialization started');

// Include the loader file
$loader_file = PIPER_PRIVACY_DIR . 'loader.php';
error_log('Loading loader file: ' . $loader_file);
if (file_exists($loader_file)) {
    require_once $loader_file;
    error_log('Loader file loaded successfully');
} else {
    error_log('Error: Loader file not found at ' . $loader_file);
    return;
}

try {
    error_log('Starting plugin activation process');

    // Load required files
    require_once PIPER_PRIVACY_DIR . 'includes/class-activator.php';
    require_once PIPER_PRIVACY_DIR . 'includes/post-types/class-privacy-collection.php';
    require_once PIPER_PRIVACY_DIR . 'includes/post-types/class-privacy-threshold.php';
    require_once PIPER_PRIVACY_DIR . 'includes/post-types/class-privacy-impact.php';

    error_log('Loaded activator class');

    // Register activation hook
    register_activation_hook(__FILE__, function() {
        error_log('Activation hook triggered');
        
        // Check dependencies before activating
        $check = piper_privacy_check_dependencies();
        if (is_wp_error($check)) {
            error_log('Dependency check failed: ' . $check->get_error_message());
            wp_die($check->get_error_message());
        }
        
        \PiperPrivacy\Includes\Activator::activate();
    });

    // Register deactivation hook
    register_deactivation_hook(__FILE__, function() {
        try {
            error_log('Deactivation hook triggered');
            require_once PIPER_PRIVACY_DIR . 'includes/class-deactivator.php';
            \PiperPrivacy\Includes\Deactivator::deactivate();
            error_log('Plugin deactivated successfully');
        } catch (Exception $e) {
            error_log('Error during deactivation: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    });

    // Add dependency check notice
    add_action('admin_notices', 'piper_privacy_admin_notice_dependencies');

    // Initialize the plugin
    add_action('plugins_loaded', function() {
        try {
            error_log('Initializing plugin');
            
            // Debug class existence
            error_log('Checking class existence:');
            error_log('Privacy_Collection exists: ' . (class_exists('\PiperPrivacy\Includes\Post_Types\Privacy_Collection') ? 'yes' : 'no'));
            error_log('Privacy_Threshold exists: ' . (class_exists('\PiperPrivacy\Includes\Post_Types\Privacy_Threshold') ? 'yes' : 'no'));
            error_log('Privacy_Impact exists: ' . (class_exists('\PiperPrivacy\Includes\Post_Types\Privacy_Impact') ? 'yes' : 'no'));
            
            // Register post types on init
            add_action('init', function() {
                error_log('Registering post types');
                \PiperPrivacy\Includes\Post_Types\Privacy_Collection::register();
                \PiperPrivacy\Includes\Post_Types\Privacy_Threshold::register();
                \PiperPrivacy\Includes\Post_Types\Privacy_Impact::register();
            });
            
            // Add the main menu
            add_action('admin_menu', function() {
                add_menu_page(
                    __('PiperPrivacy', 'piper-privacy'),
                    __('PiperPrivacy', 'piper-privacy'),
                    'manage_options',
                    'piper-privacy',
                    function() {
                        include PIPER_PRIVACY_DIR . 'admin/partials/piper-privacy-admin-display.php';
                    },
                    'dashicons-shield',
                    20
                );
            });
            
            error_log('Plugin initialized successfully');
        } catch (Exception $e) {
            error_log('Error during plugin initialization: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
        }
    });

} catch (Exception $e) {
    error_log('Error during plugin initialization: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
}