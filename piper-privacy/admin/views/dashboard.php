<?php
/**
 * Main Dashboard View
 * 
 * @var array $workflow_stats Workflow statistics
 * @var array $recent_activity Recent activity data
 * @var array $upcoming_actions Upcoming actions
 * @var array $compliance_metrics Compliance metrics
 */
?>
<div class="wrap piper-privacy-dashboard">
    <h1><?php _e('Privacy Management Dashboard', 'piper-privacy'); ?></h1>

    <!-- Overview Cards -->
    <div class="overview-cards">
        <div class="card total-collections">
            <div class="card-content">
                <h3><?php _e('Total Collections', 'piper-privacy'); ?></h3>
                <div class="stat"><?php echo esc_html($workflow_stats['total_collections']); ?></div>
            </div>
            <div class="card-footer">
                <a href="<?php echo esc_url(admin_url('admin.php?page=piper-privacy-collections')); ?>">
                    <?php _e('View All', 'piper-privacy'); ?> →
                </a>
            </div>
        </div>

        <div class="card active-workflows">
            <div class="card-content">
                <h3><?php _e('Active Workflows', 'piper-privacy'); ?></h3>
                <div class="stat"><?php echo esc_html($workflow_stats['active_workflows']); ?></div>
            </div>
            <div class="card-footer">
                <a href="<?php echo esc_url(admin_url('admin.php?page=piper-privacy-workflows')); ?>">
                    <?php _e('View All', 'piper-privacy'); ?> →
                </a>
            </div>
        </div>

        <div class="card pending-reviews">
            <div class="card-content">
                <h3><?php _e('Pending Reviews', 'piper-privacy'); ?></h3>
                <div class="stat"><?php echo esc_html($workflow_stats['pending_reviews']); ?></div>
            </div>
            <div class="card-footer">
                <a href="<?php echo esc_url(admin_url('admin.php?page=piper-privacy-reviews')); ?>">
                    <?php _e('View All', 'piper-privacy'); ?> →
                </a>
            </div>
        </div>

        <div class="card compliance-rate">
            <div class="card-content">
                <h3><?php _e('Compliance Rate', 'piper-privacy'); ?></h3>
                <div class="stat"><?php echo esc_html($workflow_stats['compliance_rate']); ?>%</div>
            </div>
            <div class="card-footer">
                <a href="<?php echo esc_url(admin_url('admin.php?page=piper-privacy-compliance')); ?>">
                    <?php _e('View Details', 'piper-privacy'); ?> →
                </a>
            </div>
        </div>
    </div>

    <div class="dashboard-grid">
        <!-- Upcoming Actions -->
        <div class="grid-item upcoming-actions">
            <h2><?php _e('Upcoming Actions', 'piper-privacy'); ?></h2>
            <?php if (!empty($upcoming_actions)) : ?>
                <ul class="actions-list">
                    <?php foreach ($upcoming_actions as $action) : ?>
                        <li class="action-item priority-<?php echo esc_attr($action['priority']); ?>">
                            <div class="action-header">
                                <span class="action-type"><?php echo esc_html($action['type']); ?></span>
                                <span class="due-date"><?php echo esc_html($action['due_date']); ?></span>
                            </div>
                            <div class="action-title"><?php echo esc_html($action['title']); ?></div>
                            <a href="<?php echo esc_url($action['url']); ?>" class="button button-small">
                                <?php _e('Take Action', 'piper-privacy'); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else : ?>
                <p class="no-data"><?php _e('No upcoming actions', 'piper-privacy'); ?></p>
            <?php endif; ?>
        </div>

        <!-- Recent Activity -->
        <div class="grid-item recent-activity">
            <h2><?php _e('Recent Activity', 'piper-privacy'); ?></h2>
            <?php if (!empty($recent_activity)) : ?>
                <ul class="activity-list">
                    <?php foreach ($recent_activity as $activity) : ?>
                        <li class="activity-item type-<?php echo esc_attr($activity['type']); ?>">
                            <div class="activity-header">
                                <span class="activity-user"><?php echo esc_html($activity['user']->display_name); ?></span>
                                <span class="activity-time"><?php echo esc_html(human_time_diff(strtotime($activity['timestamp']))); ?></span>
                            </div>
                            <div class="activity-message"><?php echo esc_html($activity['message']); ?></div>
                            <a href="<?php echo esc_url($activity['url']); ?>" class="activity-link">
                                <?php _e('View Details', 'piper-privacy'); ?> →
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else : ?>
                <p class="no-data"><?php _e('No recent activity', 'piper-privacy'); ?></p>
            <?php endif; ?>
        </div>

        <!-- Compliance Metrics -->
        <div class="grid-item compliance-metrics">
            <h2><?php _e('Compliance Metrics', 'piper-privacy'); ?></h2>
            <div class="metrics-grid">
                <div class="metric">
                    <label><?php _e('Overall Compliance', 'piper-privacy'); ?></label>
                    <div class="metric-value">
                        <div class="progress-bar" style="width: <?php echo esc_attr($compliance_metrics['overall_rate']); ?>%">
                            <span><?php echo esc_html($compliance_metrics['overall_rate']); ?>%</span>
                        </div>
                    </div>
                </div>

                <div class="metric">
                    <label><?php _e('Review Completion', 'piper-privacy'); ?></label>
                    <div class="metric-value">
                        <div class="progress-bar" style="width: <?php echo esc_attr($compliance_metrics['review_completion']); ?>%">
                            <span><?php echo esc_html($compliance_metrics['review_completion']); ?>%</span>
                        </div>
                    </div>
                </div>

                <div class="metric">
                    <label><?php _e('Documentation', 'piper-privacy'); ?></label>
                    <div class="metric-value">
                        <div class="progress-bar" style="width: <?php echo esc_attr($compliance_metrics['documentation_completion']); ?>%">
                            <span><?php echo esc_html($compliance_metrics['documentation_completion']); ?>%</span>
                        </div>
                    </div>
                </div>

                <div class="metric">
                    <label><?php _e('Controls Implementation', 'piper-privacy'); ?></label>
                    <div class="metric-value">
                        <div class="progress-bar" style="width: <?php echo esc_attr($compliance_metrics['control_implementation']); ?>%">
                            <span><?php echo esc_html($compliance_metrics['control_implementation']); ?>%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="dashboard-actions">
        <a href="<?php echo esc_url(admin_url('admin.php?page=piper-privacy-collections&action=new')); ?>" class="button button-primary">
            <?php _e('New Privacy Collection', 'piper-privacy'); ?>
        </a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=piper-privacy-reports')); ?>" class="button">
            <?php _e('View Reports', 'piper-privacy'); ?>
        </a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=piper-privacy-settings')); ?>" class="button">
            <?php _e('Settings', 'piper-privacy'); ?>
        </a>
    </div>
</div>