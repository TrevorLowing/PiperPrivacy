<?php
namespace PiperPrivacy\Modules\CollectionManager\Workflow\Stages;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Import WordPress classes
use \WP_Error;
use \WP_Post;

/**
 * Base Stage Handler
 */
abstract class BaseStage {
    /**
     * Stage ID
     *
     * @var string
     */
    protected $stage_id;

    /**
     * Stage configuration
     *
     * @var array
     */
    protected $config;

    /**
     * Initialize stage
     */
    public function __construct() {
        $this->stage_id = $this->get_stage_id();
        $this->config = $this->get_config();
        $this->setup_hooks();
    }

    /**
     * Get stage ID
     *
     * @return string
     */
    abstract protected function get_stage_id();

    /**
     * Get stage configuration
     *
     * @return array
     */
    abstract protected function get_config();

    /**
     * Setup stage hooks
     */
    protected function setup_hooks() {
        \add_action("piper_privacy_workflow_stage_{$this->stage_id}", [$this, 'process_stage'], 10, 1);
        \add_action("piper_privacy_stage_complete_{$this->stage_id}", [$this, 'complete_stage'], 10, 1);
        \add_filter("piper_privacy_stage_validation_{$this->stage_id}", [$this, 'validate_stage'], 10, 2);
    }

    /**
     * Process stage
     *
     * @param int $post_id
     */
    abstract public function process_stage($post_id);

    /**
     * Complete stage
     *
     * @param int $post_id
     */
    abstract public function complete_stage($post_id);

    /**
     * Get stage requirements
     *
     * @return array
     */
    protected function get_requirements() {
        return [];
    }

    /**
     * Check if stage requirements are met
     *
     * @param int $post_id
     * @return bool|WP_Error
     */
    protected function check_requirements($post_id) {
        $requirements = $this->get_requirements();
        
        foreach ($requirements as $requirement) {
            $check = $this->validate_requirement($requirement, $post_id);
            if (\is_wp_error($check)) {
                return $check;
            }
        }

        return true;
    }

    /**
     * Validate specific requirement
     *
     * @param array $requirement
     * @param int   $post_id
     * @return bool|WP_Error
     */
    protected function validate_requirement($requirement, $post_id) {
        $value = \get_post_meta($post_id, $requirement['field'], true);
        
        if (empty($value) && !empty($requirement['required'])) {
            return new WP_Error(
                'missing_required_field',
                sprintf(\__('Required field %s is missing', 'piper-privacy'), $requirement['label'])
            );
        }

        if (!empty($requirement['validation']) && is_callable($requirement['validation'])) {
            return call_user_func($requirement['validation'], $value, $post_id);
        }

        return true;
    }

    /**
     * Update stage status
     *
     * @param int    $post_id
     * @param string $status
     */
    protected function update_status($post_id, $status) {
        \update_post_meta($post_id, '_stage_status', $status);
        \update_post_meta($post_id, '_stage_updated', \current_time('mysql'));
        \update_post_meta($post_id, '_stage_user', \get_current_user_id());
    }

    /**
     * Log stage action
     *
     * @param int    $post_id
     * @param string $action
     * @param array  $data
     */
    protected function log_action($post_id, $action, $data = []) {
        $log = [
            'timestamp' => \current_time('mysql'),
            'user_id' => \get_current_user_id(),
            'action' => $action,
            'data' => $data
        ];

        $logs = \get_post_meta($post_id, '_stage_logs', true) ?: [];
        array_push($logs, $log);

        \update_post_meta($post_id, '_stage_logs', $logs);
    }

    /**
     * Send stage notification
     *
     * @param int    $post_id
     * @param string $type
     * @param array  $data
     */
    protected function send_notification($post_id, $type, $data = []) {
        \do_action('piper_privacy_stage_notification', $post_id, $this->stage_id, $type, $data);
    }

    /**
     * Validate stage requirements and status
     *
     * @param bool $is_valid Current validation state
     * @param int  $post_id Post ID
     * @return bool|WP_Error
     */
    public function validate_stage($is_valid, $post_id) {
        if (!$is_valid) {
            return false;
        }

        // Check base requirements
        $requirements = $this->check_requirements($post_id);
        if (\is_wp_error($requirements)) {
            return $requirements;
        }

        return true;
    }
}
