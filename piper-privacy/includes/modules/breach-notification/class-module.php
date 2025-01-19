<?php
/**
 * Breach Notification Module
 *
 * @package     PiperPrivacy
 * @subpackage  Modules\BreachNotification
 */

namespace PiperPrivacy\Modules\BreachNotification;

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Breach Notification Module Class
 */
class Module {
    /**
     * Module controller
     *
     * @var Controller
     */
    private $controller;

    /**
     * Template loader
     *
     * @var Template_Loader
     */
    private $template_loader;

    /**
     * Risk assessor
     *
     * @var Risk_Assessor
     */
    private $risk_assessor;

    /**
     * Compliance analyzer
     *
     * @var Compliance_Analyzer
     */
    private $compliance_analyzer;

    /**
     * Document manager
     *
     * @var Document_Manager
     */
    private $document_manager;

    /**
     * Export manager
     *
     * @var Export_Manager
     */
    private $export_manager;

    /**
     * Notification manager
     *
     * @var Notification_Manager
     */
    private $notification_manager;

    /**
     * Compliance checker
     *
     * @var Compliance_Checker
     */
    private $compliance_checker;

    /**
     * Initialize the module
     */
    public function __construct() {
        $this->load_dependencies();
        $this->define_hooks();
        $this->init();
    }

    /**
     * Load module dependencies
     */
    private function load_dependencies() {
        require_once dirname(__FILE__) . '/includes/class-controller.php';
        require_once dirname(__FILE__) . '/includes/class-model.php';
        require_once dirname(__FILE__) . '/includes/class-view.php';
        require_once dirname(__FILE__) . '/includes/class-notification.php';
        require_once dirname(__FILE__) . '/includes/class-template-loader.php';
        require_once dirname(__FILE__) . '/includes/class-risk-assessor.php';
        require_once dirname(__FILE__) . '/includes/class-compliance-analyzer.php';
        require_once dirname(__FILE__) . '/includes/class-document-manager.php';
        require_once dirname(__FILE__) . '/includes/class-export-manager.php';
        require_once dirname(__FILE__) . '/includes/class-notification-manager.php';
        require_once dirname(__FILE__) . '/includes/class-compliance-checker.php';

        $model = new Model();
        $view = new View();
        $notification = new Notification();
        $this->controller = new Controller($model, $view, $notification);
        $this->document_manager = new Document_Manager();
        $this->export_manager = new Export_Manager();
        $this->notification_manager = new Notification_Manager();
        $this->compliance_checker = new Compliance_Checker();
    }

    /**
     * Define module hooks
     */
    private function define_hooks() {
        // Register post type and taxonomies
        add_action('init', [$this, 'register_post_type']);
        
        // REST API endpoints
        add_action('rest_api_init', [$this->controller, 'register_routes']);
        
        // Admin menu
        add_action('admin_menu', [$this->controller, 'add_menu_pages']);
        
        // Notifications
        add_action('pp_process_breach_notifications', [$this->controller, 'process_notifications']);
        
        // Dashboard widget
        add_action('wp_dashboard_setup', [$this->controller, 'add_dashboard_widget']);
        
        // AJAX handlers
        add_action('wp_ajax_pp_save_breach', [$this->controller, 'ajax_save_breach']);
        add_action('wp_ajax_pp_update_breach_status', [$this->controller, 'ajax_update_status']);
        
        // Cron schedules
        add_filter('cron_schedules', [$this, 'add_cron_schedules']);
        
        // Schedule notification processing
        if (!wp_next_scheduled('pp_process_breach_notifications')) {
            wp_schedule_event(time(), 'hourly', 'pp_process_breach_notifications');
        }
    }

    /**
     * Initialize the module
     */
    public function init() {
        // Load required classes

        // Initialize components
        $this->template_loader = new Template_Loader();
        $this->risk_assessor = new Risk_Assessor();
        $this->compliance_analyzer = new Compliance_Analyzer();

        // Register hooks
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('wp_ajax_pp_assess_breach_risk', [$this, 'ajax_assess_breach_risk']);
        add_action('wp_ajax_pp_analyze_compliance', [$this, 'ajax_analyze_compliance']);
        add_filter('pp_breach_data', [$this, 'enhance_breach_data'], 10, 1);
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets() {
        $screen = get_current_screen();
        if ($screen && strpos($screen->id, 'piper-privacy') !== false) {
            wp_enqueue_style(
                'pp-breach-notification-admin',
                plugin_dir_url(__FILE__) . 'assets/css/admin.css',
                [],
                PP_VERSION
            );

            wp_enqueue_script(
                'pp-breach-notification-admin',
                plugin_dir_url(__FILE__) . 'assets/js/admin.js',
                ['jquery'],
                PP_VERSION,
                true
            );

            wp_localize_script('pp-breach-notification-admin', 'ppBreachNotification', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('pp_breach_notification'),
                'i18n' => [
                    'confirmDelete' => __('Are you sure you want to delete this item?', 'piper-privacy'),
                    'uploadError' => __('Error uploading file. Please try again.', 'piper-privacy'),
                    'exportError' => __('Error generating export. Please try again.', 'piper-privacy'),
                    'notificationError' => __('Error processing notification. Please try again.', 'piper-privacy'),
                ],
            ]);
        }
    }

    /**
     * AJAX handler for breach risk assessment
     */
    public function ajax_assess_breach_risk() {
        check_ajax_referer('pp_breach_notification', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $breach_id = isset($_POST['breach_id']) ? intval($_POST['breach_id']) : 0;
        if (!$breach_id) {
            wp_send_json_error('Invalid breach ID');
        }

        $breach_data = $this->get_breach_data($breach_id);
        if (!$breach_data) {
            wp_send_json_error('Breach not found');
        }

        try {
            $assessment = $this->risk_assessor->calculate_risk_score($breach_data);
            update_post_meta($breach_id, '_pp_risk_assessment', $assessment);
            wp_send_json_success($assessment);
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    /**
     * AJAX handler for compliance analysis
     */
    public function ajax_analyze_compliance() {
        check_ajax_referer('pp_breach_notification', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $breach_id = isset($_POST['breach_id']) ? intval($_POST['breach_id']) : 0;
        if (!$breach_id) {
            wp_send_json_error('Invalid breach ID');
        }

        $breach_data = $this->get_breach_data($breach_id);
        if (!$breach_data) {
            wp_send_json_error('Breach not found');
        }

        $risk_assessment = get_post_meta($breach_id, '_pp_risk_assessment', true);
        if (!$risk_assessment) {
            wp_send_json_error('Risk assessment not found');
        }

        try {
            $compliance = $this->compliance_analyzer->analyze_requirements($breach_data, $risk_assessment);
            update_post_meta($breach_id, '_pp_compliance_analysis', $compliance);
            wp_send_json_success($compliance);
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    /**
     * Enhance breach data with additional information
     *
     * @param array $breach_data Original breach data.
     * @return array Enhanced breach data.
     */
    public function enhance_breach_data($breach_data) {
        // Add risk assessment if available
        $risk_assessment = get_post_meta($breach_data['id'], '_pp_risk_assessment', true);
        if ($risk_assessment) {
            $breach_data['risk_assessment'] = $risk_assessment;
        }

        // Add compliance analysis if available
        $compliance_analysis = get_post_meta($breach_data['id'], '_pp_compliance_analysis', true);
        if ($compliance_analysis) {
            $breach_data['compliance_analysis'] = $compliance_analysis;
        }

        return $breach_data;
    }

    /**
     * Register breach post type and taxonomies
     */
    public function register_post_type() {
        // Register Breach Incident post type
        $labels = [
            'name'                  => _x('Breach Incidents', 'Post type general name', 'piper-privacy'),
            'singular_name'         => _x('Breach Incident', 'Post type singular name', 'piper-privacy'),
            'menu_name'             => _x('Breach Incidents', 'Admin Menu text', 'piper-privacy'),
            'add_new'              => __('Add New', 'piper-privacy'),
            'add_new_item'         => __('Add New Breach Incident', 'piper-privacy'),
            'edit_item'            => __('Edit Breach Incident', 'piper-privacy'),
            'new_item'             => __('New Breach Incident', 'piper-privacy'),
            'view_item'            => __('View Breach Incident', 'piper-privacy'),
            'search_items'         => __('Search Breach Incidents', 'piper-privacy'),
            'not_found'            => __('No breach incidents found', 'piper-privacy'),
            'not_found_in_trash'   => __('No breach incidents found in Trash', 'piper-privacy'),
        ];

        $args = [
            'labels'             => $labels,
            'public'             => false,
            'show_ui'            => true,
            'show_in_menu'       => false,
            'show_in_rest'       => true,
            'rest_base'          => 'breaches',
            'capability_type'    => 'post',
            'hierarchical'       => false,
            'supports'           => ['title', 'editor', 'author', 'revisions'],
            'has_archive'        => false,
            'rewrite'           => false,
            'query_var'         => false,
        ];

        register_post_type('pp_breach', $args);

        // Register breach severity taxonomy
        register_taxonomy('pp_breach_severity', ['pp_breach'], [
            'labels' => [
                'name'              => _x('Severity Levels', 'taxonomy general name', 'piper-privacy'),
                'singular_name'     => _x('Severity Level', 'taxonomy singular name', 'piper-privacy'),
                'menu_name'         => __('Severity Levels', 'piper-privacy'),
            ],
            'hierarchical'      => true,
            'public'            => false,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'rest_base'         => 'breach-severities',
            'query_var'         => true,
        ]);

        // Register breach status taxonomy
        register_taxonomy('pp_breach_status', ['pp_breach'], [
            'labels' => [
                'name'              => _x('Breach Statuses', 'taxonomy general name', 'piper-privacy'),
                'singular_name'     => _x('Breach Status', 'taxonomy singular name', 'piper-privacy'),
                'menu_name'         => __('Breach Statuses', 'piper-privacy'),
            ],
            'hierarchical'      => true,
            'public'            => false,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'rest_base'         => 'breach-statuses',
            'query_var'         => true,
        ]);

        // Register default terms
        $this->register_default_terms();
    }

    /**
     * Register default taxonomy terms
     */
    private function register_default_terms() {
        // Severity levels
        $severities = [
            'critical' => __('Critical', 'piper-privacy'),
            'high'     => __('High', 'piper-privacy'),
            'medium'   => __('Medium', 'piper-privacy'),
            'low'      => __('Low', 'piper-privacy'),
        ];

        foreach ($severities as $slug => $name) {
            if (!term_exists($slug, 'pp_breach_severity')) {
                wp_insert_term($name, 'pp_breach_severity', ['slug' => $slug]);
            }
        }

        // Breach statuses
        $statuses = [
            'draft'      => __('Draft', 'piper-privacy'),
            'detected'   => __('Detected', 'piper-privacy'),
            'assessing'  => __('Under Assessment', 'piper-privacy'),
            'confirmed'  => __('Confirmed', 'piper-privacy'),
            'notifying'  => __('Notifying', 'piper-privacy'),
            'mitigating' => __('Mitigating', 'piper-privacy'),
            'resolved'   => __('Resolved', 'piper-privacy'),
            'closed'     => __('Closed', 'piper-privacy'),
        ];

        foreach ($statuses as $slug => $name) {
            if (!term_exists($slug, 'pp_breach_status')) {
                wp_insert_term($name, 'pp_breach_status', ['slug' => $slug]);
            }
        }
    }

    /**
     * Add custom cron schedules
     *
     * @param array $schedules Existing cron schedules
     * @return array
     */
    public function add_cron_schedules($schedules) {
        // Add a schedule for checking notification deadlines
        $schedules['fifteen_minutes'] = [
            'interval' => 15 * MINUTE_IN_SECONDS,
            'display'  => __('Every Fifteen Minutes', 'piper-privacy'),
        ];

        return $schedules;
    }
}
