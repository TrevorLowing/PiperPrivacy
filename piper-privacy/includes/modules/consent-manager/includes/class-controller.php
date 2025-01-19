<?php
/**
 * Consent Manager Controller
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
 * Consent Manager Controller Class
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
        register_rest_route('piper-privacy/v1', '/consents', [
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [$this, 'get_consents'],
                'permission_callback' => [$this, 'check_permission'],
            ],
            [
                'methods'             => \WP_REST_Server::CREATABLE,
                'callback'            => [$this, 'create_consent'],
                'permission_callback' => [$this, 'check_public_permission'],
            ],
        ]);

        register_rest_route('piper-privacy/v1', '/consents/(?P<id>\d+)', [
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [$this, 'get_consent'],
                'permission_callback' => [$this, 'check_permission'],
            ],
            [
                'methods'             => \WP_REST_Server::EDITABLE,
                'callback'            => [$this, 'update_consent'],
                'permission_callback' => [$this, 'check_permission'],
            ],
            [
                'methods'             => \WP_REST_Server::DELETABLE,
                'callback'            => [$this, 'delete_consent'],
                'permission_callback' => [$this, 'check_permission'],
            ],
        ]);

        register_rest_route('piper-privacy/v1', '/consents/verify', [
            'methods'             => \WP_REST_Server::READABLE,
            'callback'            => [$this, 'verify_consent'],
            'permission_callback' => [$this, 'check_public_permission'],
        ]);

        register_rest_route('piper-privacy/v1', '/consents/withdraw', [
            'methods'             => \WP_REST_Server::CREATABLE,
            'callback'            => [$this, 'withdraw_consent'],
            'permission_callback' => [$this, 'check_public_permission'],
        ]);
    }

    /**
     * Add admin menu pages
     */
    public function add_menu_pages() {
        add_submenu_page(
            'piper-privacy',
            __('Consent Manager', 'piper-privacy'),
            __('Consent Manager', 'piper-privacy'),
            'manage_options',
            'pp-consents',
            [$this->view, 'render_admin_page']
        );
    }

    /**
     * Check admin API permission
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
     * Check public API permission
     *
     * @param \WP_REST_Request $request Request object
     * @return bool|\WP_Error
     */
    public function check_public_permission($request) {
        return true;
    }

    /**
     * Get consents
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response|\WP_Error
     */
    public function get_consents($request) {
        $args = [
            'post_type'      => 'pp_consent',
            'posts_per_page' => -1,
            'post_status'    => 'any',
        ];

        // Add filters
        if ($request->get_param('user_id')) {
            $args['author'] = $request->get_param('user_id');
        }

        if ($request->get_param('consent_type')) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'pp_consent_type',
                    'field'    => 'slug',
                    'terms'    => $request->get_param('consent_type'),
                ],
            ];
        }

        $consents = $this->model->get_consents($args);

        return rest_ensure_response($consents);
    }

    /**
     * Get single consent
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response|\WP_Error
     */
    public function get_consent($request) {
        $id = $request->get_param('id');
        $consent = $this->model->get_consent($id);

        if (is_wp_error($consent)) {
            return $consent;
        }

        return rest_ensure_response($consent);
    }

    /**
     * Create consent
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response|\WP_Error
     */
    public function create_consent($request) {
        $data = $this->prepare_item_for_database($request);
        
        if (is_wp_error($data)) {
            return $data;
        }

        $result = $this->model->create_consent($data);

        if (is_wp_error($result)) {
            return $result;
        }

        return rest_ensure_response($result);
    }

    /**
     * Update consent
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response|\WP_Error
     */
    public function update_consent($request) {
        $id = $request->get_param('id');
        $data = $this->prepare_item_for_database($request);
        
        if (is_wp_error($data)) {
            return $data;
        }

        $result = $this->model->update_consent($id, $data);

        if (is_wp_error($result)) {
            return $result;
        }

        return rest_ensure_response($result);
    }

    /**
     * Delete consent
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response|\WP_Error
     */
    public function delete_consent($request) {
        $id = $request->get_param('id');
        $result = $this->model->delete_consent($id);

        if (is_wp_error($result)) {
            return $result;
        }

        return rest_ensure_response(['deleted' => true]);
    }

    /**
     * Verify consent
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response|\WP_Error
     */
    public function verify_consent($request) {
        $user_id = $request->get_param('user_id');
        $consent_type = $request->get_param('consent_type');

        if (!$user_id || !$consent_type) {
            return new \WP_Error(
                'missing_params',
                __('User ID and consent type are required.', 'piper-privacy'),
                ['status' => 400]
            );
        }

        $result = $this->model->verify_consent($user_id, $consent_type);
        return rest_ensure_response(['has_consent' => $result]);
    }

    /**
     * Withdraw consent
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response|\WP_Error
     */
    public function withdraw_consent($request) {
        $user_id = $request->get_param('user_id');
        $consent_type = $request->get_param('consent_type');

        if (!$user_id || !$consent_type) {
            return new \WP_Error(
                'missing_params',
                __('User ID and consent type are required.', 'piper-privacy'),
                ['status' => 400]
            );
        }

        $result = $this->model->withdraw_consent($user_id, $consent_type);

        if (is_wp_error($result)) {
            return $result;
        }

        return rest_ensure_response(['withdrawn' => true]);
    }

    /**
     * Render consent form shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string
     */
    public function render_consent_form($atts) {
        $atts = shortcode_atts([
            'type' => '',
            'title' => '',
            'description' => '',
        ], $atts, 'privacy_consent_form');

        if (empty($atts['type'])) {
            return '';
        }

        ob_start();
        $this->view->render_consent_form($atts);
        return ob_get_clean();
    }

    /**
     * Prepare consent data for database
     *
     * @param \WP_REST_Request $request Request object
     * @return array|\WP_Error
     */
    private function prepare_item_for_database($request) {
        $data = $request->get_params();

        // Validate required fields
        $required = ['consent_type', 'user_id', 'status'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return new \WP_Error(
                    'missing_field',
                    sprintf(__('Missing required field: %s', 'piper-privacy'), $field),
                    ['status' => 400]
                );
            }
        }

        // Validate consent data
        $validation = pp_validate_consent($data);
        if (!$validation['valid']) {
            return new \WP_Error(
                'invalid_consent',
                $validation['message'],
                ['status' => 400]
            );
        }

        return $data;
    }
}
