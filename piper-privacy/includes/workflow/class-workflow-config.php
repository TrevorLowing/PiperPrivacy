<?php
namespace PiperPrivacy\Includes\Workflow;

/**
 * Workflow Configuration
 * 
 * Handles workflow configuration and customization
 *
 * @package    PiperPrivacy
 * @subpackage PiperPrivacy/includes/workflow
 */
class WorkflowConfig {
    /**
     * Get workflow configuration for a post type
     *
     * @param string $post_type Post type
     * @return array Workflow configuration
     */
    public function get_workflow_config($post_type) {
        $default_config = [
            'stages' => [
                'draft' => [
                    'label' => __('Draft', 'piper-privacy'),
                    'next_stages' => ['pending_review'],
                    'allowed_roles' => ['administrator', 'editor', 'author'],
                    'sla_days' => 5,
                ],
                'pending_review' => [
                    'label' => __('Pending Review', 'piper-privacy'),
                    'next_stages' => ['in_progress', 'draft'],
                    'allowed_roles' => ['administrator', 'editor'],
                    'sla_days' => 3,
                    'escalation_roles' => ['administrator'],
                ],
                'in_progress' => [
                    'label' => __('In Progress', 'piper-privacy'),
                    'next_stages' => ['approved', 'pending_review'],
                    'allowed_roles' => ['administrator', 'editor'],
                    'sla_days' => 10,
                    'escalation_roles' => ['administrator'],
                ],
                'approved' => [
                    'label' => __('Approved', 'piper-privacy'),
                    'next_stages' => ['retired', 'in_progress'],
                    'allowed_roles' => ['administrator'],
                    'sla_days' => null,
                ],
                'retired' => [
                    'label' => __('Retired', 'piper-privacy'),
                    'next_stages' => ['draft'],
                    'allowed_roles' => ['administrator'],
                    'sla_days' => null,
                ],
            ],
            'notifications' => [
                'pending_review' => [
                    'roles' => ['administrator', 'editor'],
                    'subject' => __('[{site_name}] {post_type} needs review: {post_title}', 'piper-privacy'),
                    'message' => __('A {post_type} requires your review.\n\nTitle: {post_title}\nStatus: {new_status}\nDeadline: {deadline}\n\nView: {edit_link}', 'piper-privacy'),
                ],
                'in_progress' => [
                    'roles' => ['administrator', 'editor', 'author'],
                    'subject' => __('[{site_name}] {post_type} in progress: {post_title}', 'piper-privacy'),
                    'message' => __('A {post_type} has been moved to In Progress status.\n\nTitle: {post_title}\nStatus: {new_status}\nDeadline: {deadline}\n\nView: {edit_link}', 'piper-privacy'),
                ],
                'approved' => [
                    'roles' => ['administrator', 'editor', 'author'],
                    'subject' => __('[{site_name}] {post_type} approved: {post_title}', 'piper-privacy'),
                    'message' => __('A {post_type} has been approved.\n\nTitle: {post_title}\nStatus: {new_status}\n\nView: {edit_link}', 'piper-privacy'),
                ],
                'escalation' => [
                    'roles' => ['administrator'],
                    'subject' => __('[{site_name}] {post_type} needs attention: {post_title}', 'piper-privacy'),
                    'message' => __('A {post_type} has exceeded its deadline and requires attention.\n\nTitle: {post_title}\nStatus: {current_status}\nDeadline: {deadline}\nDays Overdue: {days_overdue}\n\nView: {edit_link}', 'piper-privacy'),
                ],
            ],
            'sla_warning_days' => 2,
            'escalation_threshold_days' => 5,
        ];

        // Allow customization through filters
        return apply_filters("piper_privacy_workflow_config_{$post_type}", $default_config);
    }

    /**
     * Check if user can transition to a stage
     *
     * @param string  $post_type   Post type
     * @param string  $stage       Target stage
     * @param WP_User $user        User to check
     * @return bool Whether user can transition to stage
     */
    public function can_transition_to_stage($post_type, $stage, $user = null) {
        if (!$user) {
            $user = wp_get_current_user();
        }

        $config = $this->get_workflow_config($post_type);
        
        if (!isset($config['stages'][$stage])) {
            return false;
        }

        $allowed_roles = $config['stages'][$stage]['allowed_roles'];
        
        foreach ($user->roles as $role) {
            if (in_array($role, $allowed_roles)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get available transitions for a stage
     *
     * @param string  $post_type     Post type
     * @param string  $current_stage Current stage
     * @param WP_User $user          User to check
     * @return array Available transitions
     */
    public function get_available_transitions($post_type, $current_stage, $user = null) {
        if (!$user) {
            $user = wp_get_current_user();
        }

        $config = $this->get_workflow_config($post_type);
        
        if (!isset($config['stages'][$current_stage])) {
            return [];
        }

        $transitions = [];
        foreach ($config['stages'][$current_stage]['next_stages'] as $next_stage) {
            if ($this->can_transition_to_stage($post_type, $next_stage, $user)) {
                $transitions[$next_stage] = $config['stages'][$next_stage]['label'];
            }
        }

        return $transitions;
    }

    /**
     * Get SLA deadline for a stage
     *
     * @param string $post_type Post type
     * @param string $stage     Stage to check
     * @return int|null Number of days for SLA or null if no SLA
     */
    public function get_stage_sla($post_type, $stage) {
        $config = $this->get_workflow_config($post_type);
        
        if (!isset($config['stages'][$stage])) {
            return null;
        }

        return $config['stages'][$stage]['sla_days'];
    }

    /**
     * Get notification config for a stage
     *
     * @param string $post_type Post type
     * @param string $stage     Stage to get notifications for
     * @return array|null Notification config or null if none
     */
    public function get_notification_config($post_type, $stage) {
        $config = $this->get_workflow_config($post_type);
        
        if (!isset($config['notifications'][$stage])) {
            return null;
        }

        return $config['notifications'][$stage];
    }

    /**
     * Get escalation roles for a stage
     *
     * @param string $post_type Post type
     * @param string $stage     Stage to check
     * @return array|null Array of roles for escalation or null if none
     */
    public function get_escalation_roles($post_type, $stage) {
        $config = $this->get_workflow_config($post_type);
        
        if (!isset($config['stages'][$stage]['escalation_roles'])) {
            return null;
        }

        return $config['stages'][$stage]['escalation_roles'];
    }

    /**
     * Get SLA warning threshold
     *
     * @param string $post_type Post type
     * @return int Number of days before SLA to start warnings
     */
    public function get_sla_warning_threshold($post_type) {
        $config = $this->get_workflow_config($post_type);
        return $config['sla_warning_days'];
    }

    /**
     * Get escalation threshold
     *
     * @param string $post_type Post type
     * @return int Number of days after SLA to trigger escalation
     */
    public function get_escalation_threshold($post_type) {
        $config = $this->get_workflow_config($post_type);
        return $config['escalation_threshold_days'];
    }
}
