<?php
namespace PiperPrivacy\Modules\CollectionManager\Workflow;

/**
 * Base Workflow Handler
 */
abstract class BaseWorkflow {
    /**
     * Workflow ID
     *
     * @var string
     */
    protected $workflow_id;

    /**
     * Workflow stages
     *
     * @var array
     */
    protected $stages = [];

    /**
     * Current stage
     *
     * @var string
     */
    protected $current_stage;

    /**
     * Initialize workflow
     */
    public function __construct() {
        $this->workflow_id = $this->get_workflow_id();
        $this->stages = $this->get_stages();
        $this->setup_hooks();
    }

    /**
     * Get workflow ID
     *
     * @return string
     */
    abstract protected function get_workflow_id();

    /**
     * Get workflow stages
     *
     * @return array
     */
    abstract protected function get_stages();

    /**
     * Setup workflow hooks
     */
    protected function setup_hooks() {
        add_action('init', [$this, 'register_workflow']);
        add_action('transition_post_status', [$this, 'handle_status_transition'], 10, 3);
        add_action('piper_privacy_workflow_stage_changed', [$this, 'process_stage_change'], 10, 3);
    }

    /**
     * Register the workflow with FluentBoards
     */
    public function register_workflow() {
        if (!function_exists('fb_register_workflow')) {
            return;
        }

        fb_register_workflow($this->workflow_id, [
            'title' => $this->get_workflow_title(),
            'stages' => $this->stages,
            'settings' => $this->get_workflow_settings()
        ]);
    }

    /**
     * Get workflow title
     *
     * @return string
     */
    abstract protected function get_workflow_title();

    /**
     * Get workflow settings
     *
     * @return array
     */
    protected function get_workflow_settings() {
        return [
            'auto_assignment' => true,
            'notifications' => true,
            'deadline_tracking' => true
        ];
    }

    /**
     * Handle post status transitions
     *
     * @param string  $new_status
     * @param string  $old_status
     * @param WP_Post $post
     */
    public function handle_status_transition($new_status, $old_status, $post) {
        if (!$this->is_valid_post_type($post)) {
            return;
        }

        $this->process_status_change($post, $old_status, $new_status);
    }

    /**
     * Check if post type is valid for this workflow
     *
     * @param WP_Post $post
     * @return bool
     */
    abstract protected function is_valid_post_type($post);

    /**
     * Process post status change
     *
     * @param WP_Post $post
     * @param string  $old_status
     * @param string  $new_status
     */
    protected function process_status_change($post, $old_status, $new_status) {
        $stage = $this->get_stage_for_status($new_status);
        if ($stage) {
            $this->update_workflow_stage($post->ID, $stage);
        }
    }

    /**
     * Get workflow stage for post status
     *
     * @param string $status
     * @return string|null
     */
    abstract protected function get_stage_for_status($status);

    /**
     * Update workflow stage
     *
     * @param int    $post_id
     * @param string $stage
     */
    protected function update_workflow_stage($post_id, $stage) {
        $old_stage = get_post_meta($post_id, '_workflow_stage', true);
        
        if ($old_stage !== $stage) {
            update_post_meta($post_id, '_workflow_stage', $stage);
            
            do_action('piper_privacy_workflow_stage_changed', $post_id, $old_stage, $stage);
        }
    }

    /**
     * Process workflow stage change
     *
     * @param int    $post_id
     * @param string $old_stage
     * @param string $new_stage
     */
    public function process_stage_change($post_id, $old_stage, $new_stage) {
        $this->send_stage_notifications($post_id, $old_stage, $new_stage);
        $this->update_stage_meta($post_id, $new_stage);
        $this->trigger_stage_actions($post_id, $new_stage);
    }

    /**
     * Send notifications for stage change
     *
     * @param int    $post_id
     * @param string $old_stage
     * @param string $new_stage
     */
    protected function send_stage_notifications($post_id, $old_stage, $new_stage) {
        $notifications = $this->get_stage_notifications($new_stage);
        
        foreach ($notifications as $notification) {
            $this->send_notification($notification, [
                'post_id' => $post_id,
                'old_stage' => $old_stage,
                'new_stage' => $new_stage
            ]);
        }
    }

    /**
     * Get notifications for stage
     *
     * @param string $stage
     * @return array
     */
    abstract protected function get_stage_notifications($stage);

    /**
     * Send notification
     *
     * @param array $notification
     * @param array $data
     */
    protected function send_notification($notification, $data) {
        if (!function_exists('fb_send_notification')) {
            return;
        }

        fb_send_notification($notification, $data);
    }

    /**
     * Update stage metadata
     *
     * @param int    $post_id
     * @param string $stage
     */
    protected function update_stage_meta($post_id, $stage) {
        update_post_meta($post_id, '_workflow_stage_updated', current_time('mysql'));
        update_post_meta($post_id, '_workflow_stage_user', get_current_user_id());
    }

    /**
     * Trigger stage-specific actions
     *
     * @param int    $post_id
     * @param string $stage
     */
    protected function trigger_stage_actions($post_id, $stage) {
        do_action("piper_privacy_workflow_stage_{$stage}", $post_id);
    }
}