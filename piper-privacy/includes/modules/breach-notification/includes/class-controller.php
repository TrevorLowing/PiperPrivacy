<?php
/**
 * Breach Notification Controller
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
 * Breach Notification Controller Class
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
     * Notification instance
     *
     * @var Notification
     */
    private $notification;

    /**
     * Initialize the controller
     *
     * @param Model        $model        Model instance
     * @param View         $view         View instance
     * @param Notification $notification Notification instance
     */
    public function __construct(Model $model, View $view, Notification $notification) {
        $this->model = $model;
        $this->view = $view;
        $this->notification = $notification;
    }

    /**
     * Register REST API routes
     */
    public function register_routes() {
        register_rest_route('piper-privacy/v1', '/breaches', [
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [$this, 'get_breaches'],
                'permission_callback' => [$this, 'check_permission'],
            ],
            [
                'methods'             => \WP_REST_Server::CREATABLE,
                'callback'            => [$this, 'create_breach'],
                'permission_callback' => [$this, 'check_permission'],
            ],
        ]);

        register_rest_route('piper-privacy/v1', '/breaches/(?P<id>\d+)', [
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [$this, 'get_breach'],
                'permission_callback' => [$this, 'check_permission'],
            ],
            [
                'methods'             => \WP_REST_Server::EDITABLE,
                'callback'            => [$this, 'update_breach'],
                'permission_callback' => [$this, 'check_permission'],
            ],
            [
                'methods'             => \WP_REST_Server::DELETABLE,
                'callback'            => [$this, 'delete_breach'],
                'permission_callback' => [$this, 'check_permission'],
            ],
        ]);

        register_rest_route('piper-privacy/v1', '/breaches/(?P<id>\d+)/notifications', [
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [$this, 'get_notifications'],
                'permission_callback' => [$this, 'check_permission'],
            ],
            [
                'methods'             => \WP_REST_Server::CREATABLE,
                'callback'            => [$this, 'create_notification'],
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
            __('Breach Notification', 'piper-privacy'),
            __('Breach Notification', 'piper-privacy'),
            'manage_options',
            'pp-breaches',
            [$this->view, 'render_admin_page']
        );
    }

    /**
     * Add dashboard widget
     */
    public function add_dashboard_widget() {
        wp_add_dashboard_widget(
            'pp_breach_dashboard',
            __('Recent Data Breaches', 'piper-privacy'),
            [$this->view, 'render_dashboard_widget']
        );
    }

    /**
     * Process scheduled notifications
     */
    public function process_notifications() {
        $pending = $this->model->get_pending_notifications();

        foreach ($pending as $notification) {
            $this->notification->send($notification);
        }
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
     * Get breaches
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response|\WP_Error
     */
    public function get_breaches($request) {
        $args = [
            'post_type'      => 'pp_breach',
            'posts_per_page' => -1,
            'post_status'    => 'any',
        ];

        // Add filters
        if ($request->get_param('severity')) {
            $args['tax_query'][] = [
                'taxonomy' => 'pp_breach_severity',
                'field'    => 'slug',
                'terms'    => $request->get_param('severity'),
            ];
        }

        if ($request->get_param('status')) {
            $args['tax_query'][] = [
                'taxonomy' => 'pp_breach_status',
                'field'    => 'slug',
                'terms'    => $request->get_param('status'),
            ];
        }

        $breaches = $this->model->get_breaches($args);
        return rest_ensure_response($breaches);
    }

    /**
     * Get single breach
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response|\WP_Error
     */
    public function get_breach($request) {
        $breach = $this->model->get_breach($request->get_param('id'));

        if (is_wp_error($breach)) {
            return $breach;
        }

        return rest_ensure_response($breach);
    }

    /**
     * Create breach
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response|\WP_Error
     */
    public function create_breach($request) {
        $data = $this->prepare_item_for_database($request);
        
        if (is_wp_error($data)) {
            return $data;
        }

        $result = $this->model->create_breach($data);

        if (is_wp_error($result)) {
            return $result;
        }

        // Schedule initial notifications if needed
        if (!empty($data['notify_authorities']) && 'confirmed' === $data['status']) {
            $this->notification->schedule_authority_notification($result['id']);
        }

        if (!empty($data['notify_affected']) && 'confirmed' === $data['status']) {
            $this->notification->schedule_affected_notification($result['id']);
        }

        return rest_ensure_response($result);
    }

    /**
     * Update breach
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response|\WP_Error
     */
    public function update_breach($request) {
        $data = $this->prepare_item_for_database($request);
        
        if (is_wp_error($data)) {
            return $data;
        }

        $result = $this->model->update_breach($request->get_param('id'), $data);

        if (is_wp_error($result)) {
            return $result;
        }

        return rest_ensure_response($result);
    }

    /**
     * Delete breach
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response|\WP_Error
     */
    public function delete_breach($request) {
        $result = $this->model->delete_breach($request->get_param('id'));

        if (is_wp_error($result)) {
            return $result;
        }

        return rest_ensure_response(['deleted' => true]);
    }

    /**
     * Get breach notifications
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response|\WP_Error
     */
    public function get_notifications($request) {
        $notifications = $this->model->get_notifications($request->get_param('id'));
        return rest_ensure_response($notifications);
    }

    /**
     * Create notification
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response|\WP_Error
     */
    public function create_notification($request) {
        $data = [
            'breach_id'      => $request->get_param('id'),
            'type'           => $request->get_param('type'),
            'recipients'     => $request->get_param('recipients'),
            'template'       => $request->get_param('template'),
            'schedule_date'  => $request->get_param('schedule_date'),
        ];

        $result = $this->notification->create($data);

        if (is_wp_error($result)) {
            return $result;
        }

        return rest_ensure_response($result);
    }

    /**
     * Handle AJAX save breach
     */
    public function ajax_save_breach() {
        check_ajax_referer('pp-breach-save', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'piper-privacy')]);
        }

        $data = $this->prepare_item_for_database(new \WP_REST_Request());
        
        if (is_wp_error($data)) {
            wp_send_json_error(['message' => $data->get_error_message()]);
        }

        $result = empty($_POST['breach_id']) 
            ? $this->model->create_breach($data)
            : $this->model->update_breach($_POST['breach_id'], $data);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        wp_send_json_success($result);
    }

    /**
     * Handle AJAX update status
     */
    public function ajax_update_status() {
        check_ajax_referer('pp-breach-status', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'piper-privacy')]);
        }

        $breach_id = $_POST['breach_id'];
        $status = $_POST['status'];

        $result = $this->model->update_breach_status($breach_id, $status);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        wp_send_json_success($result);
    }

    /**
     * Prepare breach data for database
     *
     * @param \WP_REST_Request $request Request object
     * @return array|\WP_Error
     */
    private function prepare_item_for_database($request) {
        $data = [];

        // Get POST data if this is an AJAX request
        if ('POST' === $_SERVER['REQUEST_METHOD']) {
            $data = [
                'title'             => sanitize_text_field($_POST['title']),
                'description'       => wp_kses_post($_POST['description']),
                'severity'          => sanitize_text_field($_POST['severity']),
                'status'            => sanitize_text_field($_POST['status']),
                'detection_date'    => sanitize_text_field($_POST['detection_date']),
                'affected_data'     => array_map('sanitize_text_field', (array) $_POST['affected_data']),
                'affected_users'    => array_map('sanitize_text_field', (array) $_POST['affected_users']),
                'notify_authorities' => !empty($_POST['notify_authorities']),
                'notify_affected'   => !empty($_POST['notify_affected']),
                'mitigation_steps'  => wp_kses_post($_POST['mitigation_steps']),
            ];
        } else {
            // Get REST API request data
            $data = [
                'title'             => $request->get_param('title'),
                'description'       => $request->get_param('description'),
                'severity'          => $request->get_param('severity'),
                'status'            => $request->get_param('status'),
                'detection_date'    => $request->get_param('detection_date'),
                'affected_data'     => $request->get_param('affected_data'),
                'affected_users'    => $request->get_param('affected_users'),
                'notify_authorities' => $request->get_param('notify_authorities'),
                'notify_affected'   => $request->get_param('notify_affected'),
                'mitigation_steps'  => $request->get_param('mitigation_steps'),
            ];
        }

        // Validate required fields
        $required = ['title', 'description', 'severity', 'status', 'detection_date'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return new \WP_Error(
                    'missing_field',
                    sprintf(__('Missing required field: %s', 'piper-privacy'), $field),
                    ['status' => 400]
                );
            }
        }

        return $data;
    }
}
