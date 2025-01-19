<?php
namespace PiperPrivacy\Modules\CollectionManager\Workflow;

/**
 * Workflow Status Tracker
 * 
 * Handles tracking and reporting of workflow progress across stages
 */
class WorkflowTracker {
    /**
     * Status constants
     */
    const STATUS_NOT_STARTED = 'not_started';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_BLOCKED = 'blocked';

    /**
     * Stage weights for progress calculation
     */
    private $stage_weights = [
        'draft' => 5,
        'pta_required' => 5,
        'pta_in_progress' => 15,
        'pta_review' => 10,
        'pia_required' => 5,
        'pia_in_progress' => 25,
        'pia_review' => 15,
        'implementation' => 15,
        'retirement' => 5
    ];

    /**
     * Get workflow progress for a collection
     *
     * @param int $collection_id
     * @return array Progress data
     */
    public function get_workflow_progress($collection_id) {
        $current_stage = $this->get_current_stage($collection_id);
        $completed_stages = $this->get_completed_stages($collection_id);
        $stage_statuses = $this->get_stage_statuses($collection_id);
        $completion_percentage = $this->calculate_completion_percentage($completed_stages);

        return [
            'current_stage' => $current_stage,
            'completed_stages' => $completed_stages,
            'stage_statuses' => $stage_statuses,
            'completion_percentage' => $completion_percentage,
            'started_at' => get_post_meta($collection_id, '_workflow_started', true),
            'last_update' => get_post_meta($collection_id, '_workflow_updated', true),
            'is_blocked' => $this->check_if_blocked($collection_id)
        ];
    }

    /**
     * Get current workflow stage
     *
     * @param int $collection_id
     * @return string Current stage ID
     */
    public function get_current_stage($collection_id) {
        return get_post_meta($collection_id, '_workflow_stage', true) ?: 'draft';
    }

    /**
     * Get completed workflow stages
     *
     * @param int $collection_id
     * @return array Completed stage IDs
     */
    private function get_completed_stages($collection_id) {
        return get_post_meta($collection_id, '_completed_stages', true) ?: [];
    }

    /**
     * Get status for all stages
     *
     * @param int $collection_id
     * @return array Stage statuses
     */
    private function get_stage_statuses($collection_id) {
        $current_stage = $this->get_current_stage($collection_id);
        $completed_stages = $this->get_completed_stages($collection_id);
        $statuses = [];

        foreach (array_keys($this->stage_weights) as $stage) {
            if (in_array($stage, $completed_stages)) {
                $statuses[$stage] = self::STATUS_COMPLETED;
            } elseif ($stage === $current_stage) {
                $statuses[$stage] = self::STATUS_IN_PROGRESS;
            } else {
                $statuses[$stage] = self::STATUS_NOT_STARTED;
            }
        }

        return $statuses;
    }

    /**
     * Calculate overall completion percentage
     *
     * @param array $completed_stages
     * @return float Completion percentage
     */
    private function calculate_completion_percentage($completed_stages) {
        $total_weight = array_sum($this->stage_weights);
        $completed_weight = 0;

        foreach ($completed_stages as $stage) {
            $completed_weight += $this->stage_weights[$stage] ?? 0;
        }

        return round(($completed_weight / $total_weight) * 100, 1);
    }

    /**
     * Check if workflow is blocked
     *
     * @param int $collection_id
     * @return bool
     */
    private function check_if_blocked($collection_id) {
        $current_stage = $this->get_current_stage($collection_id);
        $stage_status = get_post_meta($collection_id, '_stage_status', true);

        return $stage_status === self::STATUS_BLOCKED;
    }

    /**
     * Update workflow progress
     *
     * @param int    $collection_id
     * @param string $stage
     * @param string $status
     */
    public function update_progress($collection_id, $stage, $status) {
        $completed_stages = $this->get_completed_stages($collection_id);

        if ($status === self::STATUS_COMPLETED && !in_array($stage, $completed_stages)) {
            $completed_stages[] = $stage;
            update_post_meta($collection_id, '_completed_stages', $completed_stages);
        }

        update_post_meta($collection_id, '_workflow_updated', current_time('mysql'));
        
        // Trigger progress update action
        do_action('piper_privacy_workflow_progress_updated', $collection_id, [
            'stage' => $stage,
            'status' => $status,
            'completion_percentage' => $this->calculate_completion_percentage($completed_stages)
        ]);
    }

    /**
     * Get workflow timeline
     *
     * @param int $collection_id
     * @return array Timeline events
     */
    public function get_workflow_timeline($collection_id) {
        $stage_logs = get_post_meta($collection_id, '_stage_logs', true) ?: [];
        $timeline = [];

        foreach ($stage_logs as $log) {
            $timeline[] = [
                'timestamp' => $log['timestamp'],
                'stage' => $log['stage'] ?? '',
                'action' => $log['action'],
                'user_id' => $log['user_id'],
                'data' => $log['data'] ?? []
            ];
        }

        return $timeline;
    }

    /**
     * Get workflow metrics
     *
     * @param int $collection_id
     * @return array Workflow metrics
     */
    public function get_workflow_metrics($collection_id) {
        $started_at = get_post_meta($collection_id, '_workflow_started', true);
        $current_stage = $this->get_current_stage($collection_id);
        $completed_stages = $this->get_completed_stages($collection_id);

        return [
            'days_in_workflow' => $this->calculate_days_in_workflow($started_at),
            'days_in_current_stage' => $this->calculate_days_in_stage($collection_id, $current_stage),
            'average_stage_duration' => $this->calculate_average_stage_duration($collection_id, $completed_stages),
            'completion_rate' => $this->calculate_completion_rate($collection_id),
            'blocking_incidents' => $this->count_blocking_incidents($collection_id)
        ];
    }

    /**
     * Calculate days in workflow
     */
    private function calculate_days_in_workflow($started_at) {
        if (!$started_at) {
            return 0;
        }

        $start = new \DateTime($started_at);
        $now = new \DateTime();
        
        return $start->diff($now)->days;
    }

    /**
     * Calculate days in current stage
     */
    private function calculate_days_in_stage($collection_id, $stage) {
        $stage_start = get_post_meta($collection_id, "_stage_{$stage}_start", true);
        if (!$stage_start) {
            return 0;
        }

        $start = new \DateTime($stage_start);
        $now = new \DateTime();
        
        return $start->diff($now)->days;
    }

    /**
     * Calculate average stage duration
     */
    private function calculate_average_stage_duration($collection_id, $completed_stages) {
        if (empty($completed_stages)) {
            return 0;
        }

        $total_days = 0;
        foreach ($completed_stages as $stage) {
            $start = get_post_meta($collection_id, "_stage_{$stage}_start", true);
            $end = get_post_meta($collection_id, "_stage_{$stage}_end", true);
            
            if ($start && $end) {
                $start_date = new \DateTime($start);
                $end_date = new \DateTime($end);
                $total_days += $start_date->diff($end_date)->days;
            }
        }

        return round($total_days / count($completed_stages), 1);
    }

    /**
     * Calculate completion rate
     */
    private function calculate_completion_rate($collection_id) {
        $total_stages = count($this->stage_weights);
        $completed_stages = count($this->get_completed_stages($collection_id));
        $days_in_workflow = $this->calculate_days_in_workflow(
            get_post_meta($collection_id, '_workflow_started', true)
        );

        if ($days_in_workflow === 0) {
            return 0;
        }

        return round(($completed_stages / $total_stages) / $days_in_workflow, 3);
    }

    /**
     * Count blocking incidents
     */
    private function count_blocking_incidents($collection_id) {
        $logs = get_post_meta($collection_id, '_stage_logs', true) ?: [];
        $blocking_count = 0;

        foreach ($logs as $log) {
            if (strpos($log['action'], '_error') !== false || 
                ($log['data']['status'] ?? '') === self::STATUS_BLOCKED) {
                $blocking_count++;
            }
        }

        return $blocking_count;
    }
}