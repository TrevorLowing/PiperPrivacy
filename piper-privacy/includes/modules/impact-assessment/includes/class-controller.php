<?php
/**
 * Impact Assessment Controller
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
 * Impact Assessment Controller Class
 */
class Controller {
    /**
     * Model instance
     *
     * @var Model
     */
    private $model;

    /**
     * View instance
     *
     * @var View
     */
    private $view;

    /**
     * Initialize the controller
     *
     * @param Model $model Model instance
     * @param View  $view  View instance
     */
    public function __construct(Model $model, View $view) {
        $this->model = $model;
        $this->view = $view;
    }

    /**
     * Register REST API routes
     */
    public function register_routes() {
        register_rest_route('piper-privacy/v1', '/assessments', [
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [$this, 'get_assessments'],
                'permission_callback' => [$this, 'check_permission'],
            ],
            [
                'methods'             => \WP_REST_Server::CREATABLE,
                'callback'            => [$this, 'create_assessment'],
                'permission_callback' => [$this, 'check_permission'],
            ],
        ]);

        register_rest_route('piper-privacy/v1', '/assessments/(?P<id>\d+)', [
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [$this, 'get_assessment'],
                'permission_callback' => [$this, 'check_permission'],
            ],
            [
                'methods'             => \WP_REST_Server::EDITABLE,
                'callback'            => [$this, 'update_assessment'],
                'permission_callback' => [$this, 'check_permission'],
            ],
            [
                'methods'             => \WP_REST_Server::DELETABLE,
                'callback'            => [$this, 'delete_assessment'],
                'permission_callback' => [$this, 'check_permission'],
            ],
        ]);
    }

    /**
     * Add admin menu pages
     */
    public function add_menu_pages() {
        add_submenu_page(
            'piper-privacy',
            __('Impact Assessments', 'piper-privacy'),
            __('Impact Assessments', 'piper-privacy'),
            'manage_options',
            'pp-assessments',
            [$this->view, 'render_admin_page']
        );
    }

    /**
     * Check API permission
     *
     * @param \WP_REST_Request $request Request object
     * @return bool|\WP_Error
     */
    public function check_permission($request) {
        if (!current_user_can('manage_options')) {
            return new \WP_Error(
                'rest_forbidden',
                __('You do not have permission to access this resource.', 'piper-privacy'),
                ['status' => 403]
            );
        }
        return true;
    }

    /**
     * Get assessments
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response|\WP_Error
     */
    public function get_assessments($request) {
        $args = [
            'post_type'      => 'pp_assessment',
            'posts_per_page' => -1,
            'post_status'    => 'any',
        ];

        $assessments = $this->model->get_assessments($args);

        return rest_ensure_response($assessments);
    }

    /**
     * Get single assessment
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response|\WP_Error
     */
    public function get_assessment($request) {
        $id = $request->get_param('id');
        $assessment = $this->model->get_assessment($id);

        if (is_wp_error($assessment)) {
            return $assessment;
        }

        return rest_ensure_response($assessment);
    }

    /**
     * Create assessment
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response|\WP_Error
     */
    public function create_assessment($request) {
        $data = $this->prepare_item_for_database($request);
        
        if (is_wp_error($data)) {
            return $data;
        }

        $result = $this->model->create_assessment($data);

        if (is_wp_error($result)) {
            return $result;
        }

        return rest_ensure_response($result);
    }

    /**
     * Update assessment
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response|\WP_Error
     */
    public function update_assessment($request) {
        $id = $request->get_param('id');
        $data = $this->prepare_item_for_database($request);
        
        if (is_wp_error($data)) {
            return $data;
        }

        $result = $this->model->update_assessment($id, $data);

        if (is_wp_error($result)) {
            return $result;
        }

        return rest_ensure_response($result);
    }

    /**
     * Delete assessment
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response|\WP_Error
     */
    public function delete_assessment($request) {
        $id = $request->get_param('id');
        $result = $this->model->delete_assessment($id);

        if (is_wp_error($result)) {
            return $result;
        }

        return rest_ensure_response(['deleted' => true]);
    }

    /**
     * Prepare assessment data for database
     *
     * @param \WP_REST_Request $request Request object
     * @return array|\WP_Error
     */
    private function prepare_item_for_database($request) {
        $data = $request->get_params();

        // Validate required fields
        $required = ['title', 'processing_activities', 'risk_assessment'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return new \WP_Error(
                    'missing_field',
                    sprintf(__('Missing required field: %s', 'piper-privacy'), $field),
                    ['status' => 400]
                );
            }
        }

        // Validate assessment data
        $validation = pp_validate_dpia($data);
        if (!$validation['valid']) {
            return new \WP_Error(
                'invalid_assessment',
                $validation['message'],
                ['status' => 400]
            );
        }

        return $data;
    }
}
