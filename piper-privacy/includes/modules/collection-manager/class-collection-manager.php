<?php
namespace PiperPrivacy\Modules\CollectionManager;

/**
 * Collection Manager Module
 */
class CollectionManager {
    /**
     * Module instance
     */
    private static $instance = null;

    /**
     * Post Types registry
     */
    private $post_types = [];

    /**
     * Get module instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize the module
     */
    public function __construct() {
        $this->define_constants();
        $this->load_dependencies();
        $this->init_components();
        $this->setup_hooks();
    }

    /**
     * Define module constants
     */
    private function define_constants() {
        define('PIPER_PRIVACY_CM_VERSION', '1.0.0');
        define('PIPER_PRIVACY_CM_PATH', plugin_dir_path(dirname(__FILE__, 3)));
        define('PIPER_PRIVACY_CM_URL', plugin_dir_url(dirname(__FILE__, 3)));
    }

    /**
     * Load module dependencies
     */
    private function load_dependencies() {
        require_once dirname(__FILE__) . '/PostTypes/PrivacyCollection.php';
        require_once dirname(__FILE__) . '/PostTypes/PrivacyThreshold.php';
        require_once dirname(__FILE__) . '/PostTypes/PrivacyImpact.php';
        require_once dirname(__FILE__) . '/Forms/CollectionForm.php';
        require_once dirname(__FILE__) . '/Workflow/CollectionWorkflow.php';
    }

    /**
     * Initialize module components
     */
    private function init_components() {
        // Initialize post types
        $this->post_types['collection'] = new PostTypes\PrivacyCollection();
        $this->post_types['threshold'] = new PostTypes\PrivacyThreshold();
        $this->post_types['impact'] = new PostTypes\PrivacyImpact();

        // Initialize forms and workflows
        new Forms\CollectionForm();
        new Workflow\CollectionWorkflow();
    }

    /**
     * Setup module hooks
     */
    private function setup_hooks() {
        add_action('init', [$this, 'register_post_types']);
        add_action('admin_menu', [$this, 'add_menu_pages']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    /**
     * Register post types
     */
    public function register_post_types() {
        foreach ($this->post_types as $post_type) {
            $post_type->register();
        }
    }

    /**
     * Add admin menu pages
     */
    public function add_menu_pages() {
        add_menu_page(
            __('Privacy Collections', 'piper-privacy'),
            __('Privacy Collections', 'piper-privacy'),
            'manage_options',
            'privacy-collections',
            [$this, 'render_main_page'],
            'dashicons-shield',
            30
        );
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'privacy-collections') !== false) {
            wp_enqueue_style(
                'piper-privacy-cm-admin',
                PIPER_PRIVACY_CM_URL . 'assets/css/admin.css',
                [],
                PIPER_PRIVACY_CM_VERSION
            );

            wp_enqueue_script(
                'piper-privacy-cm-admin',
                PIPER_PRIVACY_CM_URL . 'assets/js/admin.js',
                ['jquery'],
                PIPER_PRIVACY_CM_VERSION,
                true
            );
        }
    }

    /**
     * Render main admin page
     */
    public function render_main_page() {
        include dirname(__FILE__) . '/Admin/views/main-page.php';
    }
}