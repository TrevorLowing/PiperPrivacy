<?php
/**
 * PiperPrivacy Plugin Loader
 *
 * @package    PiperPrivacy
 * @since      1.0.0
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Include helper functions
require_once PIPER_PRIVACY_DIR . 'includes/helpers/field-helpers.php';
require_once PIPER_PRIVACY_DIR . 'includes/helpers/field-validators.php';

// Autoloader
spl_autoload_register(function ($class) {
    error_log("Attempting to autoload class: " . $class);
    
    // Project-specific namespace prefix
    $prefix = 'PiperPrivacy\\';

    // Base directory for the namespace prefix
    $base_dir = PIPER_PRIVACY_DIR . 'includes/';

    // If the class doesn't use the namespace prefix, skip it
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        error_log("Class $class does not match prefix $prefix - skipping");
        return;
    }

    // Get the relative class name
    $relative_class = substr($class, $len);
    error_log("Relative class name: " . $relative_class);

    // Split the relative class name into parts
    $path_parts = explode('\\', $relative_class);
    error_log("Path parts: " . implode(', ', $path_parts));
    
    // Remove redundant Includes part if present
    if ($path_parts[0] === 'Includes') {
        array_shift($path_parts);
        error_log("Removed redundant Includes part");
    }
    
    // Convert each part to directory format (lowercase with hyphens)
    $dir_parts = array_map(function($part) {
        // Convert underscores to hyphens and lowercase
        return strtolower(str_replace('_', '-', $part));
    }, $path_parts);
    error_log("Directory parts: " . implode(', ', $dir_parts));
    
    // Get the class name from the last part
    $class_name = array_pop($dir_parts);
    error_log("Class name: " . $class_name);
    
    // Convert class name to file name format
    $file_name = 'class-' . $class_name . '.php';
    error_log("File name: " . $file_name);
    
    // Build the full path
    $file_path = $base_dir . 'includes/' . implode('/', $dir_parts) . '/' . $file_name;
    error_log("Full file path: " . $file_path);

    // If the file exists, require it
    if (file_exists($file_path)) {
        error_log("File exists, requiring: " . $file_path);
        require_once $file_path;
        return;
    }
    error_log("File does not exist: " . $file_path);
});