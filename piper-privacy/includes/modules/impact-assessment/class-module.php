<?php
/**
 * Impact Assessment Module
 *
 * @package     PiperPrivacy
 * @subpackage  Modules\ImpactAssessment
 */

namespace PiperPrivacy\Modules\ImpactAssessment;

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Impact Assessment Module Class
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
    }

    /**
     * Register impact assessment post type
     */
    public function register_post_type() {
        $labels = [
            'name'                  => _x('Impact Assessments', 'Post type general name', 'piper-privacy'),
            'singular_name'         => _x('Impact Assessment', 'Post type singular name', 'piper-privacy'),
            'menu_name'            => _x('Impact Assessments', 'Admin Menu text', 'piper-privacy'),
            'add_new'              => __('Add New', 'piper-privacy'),
            'add_new_item'         => __('Add New Assessment', 'piper-privacy'),
            'edit_item'            => __('Edit Assessment', 'piper-privacy'),
            'new_item'             => __('New Assessment', 'piper-privacy'),
            'view_item'            => __('View Assessment', 'piper-privacy'),
            'search_items'         => __('Search Assessments', 'piper-privacy'),
            'not_found'            => __('No assessments found', 'piper-privacy'),
            'not_found_in_trash'   => __('No assessments found in Trash', 'piper-privacy'),
        ];

        $args = [
            'labels'              => $labels,
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => false,
            'show_in_rest'        => true,
            'rest_base'           => 'assessments',
            'capability_type'     => 'post',
            'hierarchical'        => false,
            'supports'            => ['title', 'editor', 'revisions'],
            'has_archive'         => false,
            'rewrite'            => false,
            'query_var'          => false,
        ];

        register_post_type('pp_assessment', $args);
    }
}
