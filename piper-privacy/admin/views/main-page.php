<?php
/**
 * Main admin dashboard view
 *
 * @var array $collections Privacy collections
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap piper-privacy-dashboard">
    <h1 class="wp-heading-inline">
        <?php esc_html_e('Privacy Collections', 'piper-privacy'); ?>
    </h1>

    <a href="<?php echo esc_url(admin_url('admin.php?page=piper-privacy&action=new')); ?>" class="page-title-action">
        <?php esc_html_e('Add New', 'piper-privacy'); ?>
    </a>

    <?php if (isset($_GET['message'])): ?>
        <div class="notice notice-success is-dismissible">
            <p>
                <?php
                switch ($_GET['message']) {
                    case 'created':
                        esc_html_e('Privacy collection created.', 'piper-privacy');
                        break;
                    case 'updated':
                        esc_html_e('Privacy collection updated.', 'piper-privacy');
                        break;
                    case 'deleted':
                        esc_html_e('Privacy collection deleted.', 'piper-privacy');
                        break;
                    case 'retired':
                        esc_html_e('Privacy collection retired.', 'piper-privacy');
                        break;
                }
                ?>
            </p>
        </div>
    <?php endif; ?>

    <!-- Status Overview -->
    <div class="status-overview">
        <div class="status-card">
            <h3><?php esc_html_e('Active Collections', 'piper-privacy'); ?></h3>
            <div class="status-count active">
                <?php echo esc_html(wp_count_posts('privacy-collection')->publish); ?>
            </div>
        </div>

        <div class="status-card">
            <h3><?php esc_html_e('Pending Reviews', 'piper-privacy'); ?></h3>
            <div class="status-count pending">
                <?php 
                $pending_reviews = get_posts([
                    'post_type' => 'privacy-collection',
                    'meta_key' => '_review_status',
                    'meta_value' => 'pending',
                    'posts_per_page' => -1
                ]);
                echo count($pending_reviews);
                ?>
            </div>
        </div>

        <div class="status-card">
            <h3><?php esc_html_e('Recent Activity', 'piper-privacy'); ?></h3>
            <div class="status-count activity">
                <?php
                $recent_activity = get_posts([
                    'post_type' => 'privacy-collection',
                    'posts_per_page' => -1,
                    'date_query' => [
                        'after' => '1 week ago'
                    ]
                ]);
                echo count($recent_activity);
                ?>
            </div>
        </div>
    </div>

    <!-- Workflow Progress -->
    <div class="workflow-progress">
        <h2><?php esc_html_e('Current Workflows', 'piper-privacy'); ?></h2>
        
        <?php
        $workflows = get_posts([
            'post_type' => 'privacy-collection',
            'meta_query' => [
                [
                    'key' => '_workflow_status',
                    'value' => ['draft', 'pta_required', 'pta_in_progress', 'pta_review', 'pia_required', 'pia_in_progress', 'pia_review'],
                    'compare' => 'IN'
                ]
            ],
            'posts_per_page' => 5
        ]);

        if ($workflows): ?>
            <div class="workflow-list">
                <?php foreach ($workflows as $workflow): ?>
                    <div class="workflow-item">
                        <div class="workflow-header">
                            <h3><?php echo esc_html($workflow->post_title); ?></h3>
                            <span class="workflow-stage <?php echo esc_attr(get_post_meta($workflow->ID, '_workflow_status', true)); ?>">
                                <?php echo esc_html(get_post_meta($workflow->ID, '_workflow_status', true)); ?>
                            </span>
                        </div>
                        <div class="workflow-progress-bar">
                            <div class="progress-value" style="width: <?php echo esc_attr(get_post_meta($workflow->ID, '_workflow_progress', true)); ?>%"></div>
                        </div>
                        <div class="workflow-actions">
                            <a href="<?php echo esc_url(admin_url('admin.php?page=piper-privacy&action=edit&id=' . $workflow->ID)); ?>" class="button">
                                <?php esc_html_e('View Details', 'piper-privacy'); ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="no-workflows">
                <?php esc_html_e('No active workflows.', 'piper-privacy'); ?>
            </p>
        <?php endif; ?>
    </div>

    <!-- Recent Activity -->
    <div class="recent-activity">
        <h2><?php esc_html_e('Recent Activity', 'piper-privacy'); ?></h2>
        
        <?php
        $activities = get_posts([
            'post_type' => ['privacy-collection', 'privacy-threshold', 'privacy-impact'],
            'posts_per_page' => 10,
            'orderby' => 'modified'
        ]);

        if ($activities): ?>
            <div class="activity-list">
                <?php foreach ($activities as $activity): 
                    $type = get_post_type_object($activity->post_type);
                    $modified_by = get_userdata($activity->post_author);
                    ?>
                    <div class="activity-item">
                        <div class="activity-icon <?php echo esc_attr($activity->post_type); ?>"></div>
                        <div class="activity-details">
                            <div class="activity-header">
                                <span class="activity-type"><?php echo esc_html($type->labels->singular_name); ?></span>
                                <span class="activity-time"><?php echo esc_html(human_time_diff(strtotime($activity->post_modified))); ?> ago</span>
                            </div>
                            <div class="activity-title"><?php echo esc_html($activity->post_title); ?></div>
                            <div class="activity-meta">
                                <?php esc_html_e('Modified by', 'piper-privacy'); ?> 
                                <?php echo esc_html($modified_by->display_name); ?>
                            </div>
                        </div>
                        <div class="activity-actions">
                            <a href="<?php echo esc_url(get_edit_post_link($activity->ID)); ?>" class="button button-small">
                                <?php esc_html_e('View', 'piper-privacy'); ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="no-activity">
                <?php esc_html_e('No recent activity.', 'piper-privacy'); ?>
            </p>
        <?php endif; ?>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <h2><?php esc_html_e('Quick Actions', 'piper-privacy'); ?></h2>
        <div class="action-buttons">
            <a href="<?php echo esc_url(admin_url('admin.php?page=piper-privacy&action=new')); ?>" class="button button-primary">
                <?php esc_html_e('New Collection', 'piper-privacy'); ?>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=piper-privacy-reviews')); ?>" class="button">
                <?php esc_html_e('Pending Reviews', 'piper-privacy'); ?>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=piper-privacy-reports')); ?>" class="button">
                <?php esc_html_e('Generate Report', 'piper-privacy'); ?>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=piper-privacy-settings')); ?>" class="button">
                <?php esc_html_e('Settings', 'piper-privacy'); ?>
            </a>
        </div>
    </div>
</div>