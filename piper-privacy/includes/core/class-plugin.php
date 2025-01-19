<?php
/**
 * The core plugin class.
 *
 * @package    PiperPrivacy
 * @subpackage Core
 */

namespace PiperPrivacy\Core;

/**
 * The core plugin class.
 */
class Plugin {
    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @var      Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * Initialize the core functionality of the plugin.
     */
    public function __construct() {
        if (!class_exists('\PiperPrivacy\Core\Loader')) {
            throw new \Exception('Required class Loader not found');
        }
        $this->loader = new Loader();
        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     */
    private function load_dependencies() {
        // Core dependencies are now handled by the autoloader
    }

    /**
     * Define the locale for this plugin for internationalization.
     */
    private function set_locale() {
        $plugin_i18n = new I18n();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     */
    private function define_admin_hooks() {
        // Admin hooks
        $this->loader->add_action('admin_enqueue_scripts', $this, 'enqueue_admin_styles');
        $this->loader->add_action('admin_enqueue_scripts', $this, 'enqueue_admin_scripts');
        $this->loader->add_action('admin_menu', $this, 'add_plugin_admin_menu');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     */
    private function define_public_hooks() {
        // Public hooks if needed
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * Enqueue admin styles
     */
    public function enqueue_admin_styles() {
        wp_enqueue_style(
            'piper-privacy-admin',
            PIPER_PRIVACY_URL . 'admin/css/admin.css',
            [],
            PIPER_PRIVACY_VERSION,
            'all'
        );
    }

    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts() {
        wp_enqueue_script(
            'piper-privacy-admin',
            PIPER_PRIVACY_URL . 'admin/js/admin.js',
            ['jquery'],
            PIPER_PRIVACY_VERSION,
            true
        );
    }

    /**
     * Add plugin admin menu
     */
    public function add_plugin_admin_menu() {
        add_menu_page(
            __('PiperPrivacy', 'piper-privacy'),
            __('PiperPrivacy', 'piper-privacy'),
            'manage_options',
            'piper-privacy',
            [$this, 'display_plugin_admin_page'],
            'dashicons-shield',
            30
        );
    }

    /**
     * Display plugin admin page
     */
    public function display_plugin_admin_page() {
        include_once PIPER_PRIVACY_DIR . 'admin/partials/admin-display.php';
    }
}
