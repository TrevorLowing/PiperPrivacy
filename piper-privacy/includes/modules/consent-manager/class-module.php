<?php
/**
 * Consent Manager Module
 *
 * @package     PiperPrivacy
 * @subpackage  Modules\ConsentManager
 */

namespace PiperPrivacy\Modules\ConsentManager;

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Consent Manager Module Class
 */
class Module {
    /**
     * Module controller
     *
     * @var Controller
     */
    private $controller;

    /**
     * Initialize the module
     */
    public function __construct() {
        $this->load_dependencies();
        $this->define_hooks();
    }

    /**
     * Load module dependencies
     */
    private function load_dependencies() {
        require_once dirname(__FILE__) . '/includes/class-controller.php';
        require_once dirname(__FILE__) . '/includes/class-model.php';
        require_once dirname(__FILE__) . '/includes/class-view.php';

        $model = new Model();
        $view = new View();
        $this->controller = new Controller($model, $view);
    }

    /**
     * Define module hooks
     */
    private function define_hooks() {
        add_action('init', [$this, 'register_post_type']);
        add_action('rest_api_init', [$this->controller, 'register_routes']);
        add_action('admin_menu', [$this->controller, 'add_menu_pages']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_public_assets']);
        add_shortcode('privacy_consent_form', [$this->controller, 'render_consent_form']);
    }

    /**
     * Register consent post type
     */
    public function register_post_type() {
        $labels = [
            'name'                  => _x('Consent Records', 'Post type general name', 'piper-privacy'),
            'singular_name'         => _x('Consent Record', 'Post type singular name', 'piper-privacy'),
            'menu_name'            => _x('Consent Records', 'Admin Menu text', 'piper-privacy'),
            'add_new'              => __('Add New', 'piper-privacy'),
            'add_new_item'         => __('Add New Consent Record', 'piper-privacy'),
            'edit_item'            => __('Edit Consent Record', 'piper-privacy'),
            'new_item'             => __('New Consent Record', 'piper-privacy'),
            'view_item'            => __('View Consent Record', 'piper-privacy'),
            'search_items'         => __('Search Consent Records', 'piper-privacy'),
            'not_found'            => __('No consent records found', 'piper-privacy'),
            'not_found_in_trash'   => __('No consent records found in Trash', 'piper-privacy'),
        ];

        $args = [
            'labels'              => $labels,
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => false,
            'show_in_rest'        => true,
            'rest_base'           => 'consents',
            'capability_type'     => 'post',
            'hierarchical'        => false,
            'supports'            => ['title', 'author', 'revisions'],
            'has_archive'         => false,
            'rewrite'            => false,
            'query_var'          => false,
        ];

        register_post_type('pp_consent', $args);

        // Register consent taxonomies
        register_taxonomy('pp_consent_type', ['pp_consent'], [
            'labels' => [
                'name'              => _x('Consent Types', 'taxonomy general name', 'piper-privacy'),
                'singular_name'     => _x('Consent Type', 'taxonomy singular name', 'piper-privacy'),
                'menu_name'         => __('Consent Types', 'piper-privacy'),
            ],
            'hierarchical'      => true,
            'public'            => false,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'rest_base'         => 'consent-types',
            'query_var'         => true,
        ]);
    }

    /**
     * Enqueue public-facing assets
     */
    public function enqueue_public_assets() {
        wp_enqueue_style(
            'pp-consent-public',
            PIPER_PRIVACY_URL . 'modules/consent-manager/assets/css/public.css',
            [],
            PIPER_PRIVACY_VERSION
        );

        wp_enqueue_script(
            'pp-consent-public',
            PIPER_PRIVACY_URL . 'modules/consent-manager/assets/js/public.js',
            ['jquery'],
            PIPER_PRIVACY_VERSION,
            true
        );

        wp_localize_script('pp-consent-public', 'ppConsent', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('pp-consent-public'),
            'i18n'    => [
                'consentRequired' => __('Please provide your consent to continue.', 'piper-privacy'),
                'error'          => __('An error occurred. Please try again.', 'piper-privacy'),
                'success'        => __('Your consent preferences have been saved.', 'piper-privacy'),
            ],
        ]);
    }
}
