<?php
/**
 * Workflow Dashboard Template
 *
 * @package    PiperPrivacy
 * @subpackage PiperPrivacy/admin/templates
 */

// Get workflow statistics
$workflow_stats = [
    'privacy_collection' => wp_count_posts('privacy_collection'),
    'privacy_threshold' => wp_count_posts('privacy_threshold'),
    'privacy_impact' => wp_count_posts('privacy_impact'),
];

// Get recent workflow transitions
global $wpdb;
$recent_transitions = $wpdb->get_results(
    "SELECT h.*, p.post_title, p.post_type, u.display_name
    FROM {$wpdb->prefix}privacy_workflow_history h
    JOIN {$wpdb->posts} p ON h.post_id = p.ID
    JOIN {$wpdb->users} u ON h.user_id = u.ID
    ORDER BY h.date DESC
    LIMIT 10"
);

// Get items requiring attention (pending review or in progress)
$attention_needed = [];
$post_types = ['privacy_collection', 'privacy_threshold', 'privacy_impact'];
foreach ($post_types as $post_type) {
    $items = get_posts([
        'post_type' => $post_type,
        'post_status' => ['pending_review', 'in_progress'],
        'posts_per_page' => -1,
    ]);
    if (!empty($items)) {
        $attention_needed[$post_type] = $items;
    }
}
?>

<div class="wrap">
    <h1><?php _e('Privacy Workflow Dashboard', 'piper-privacy'); ?></h1>

    <!-- Workflow Statistics -->
    <div class="workflow-stats-grid">
        <?php foreach ($workflow_stats as $post_type => $stats) : 
            $post_type_obj = get_post_type_object($post_type);
            $total = array_sum((array)$stats);
        ?>
            <div class="workflow-stat-card">
                <h3><?php echo esc_html($post_type_obj->labels->name); ?></h3>
                <div class="stat-numbers">
                    <div class="total-count">
                        <span class="count"><?php echo esc_html($total); ?></span>
                        <span class="label"><?php _e('Total', 'piper-privacy'); ?></span>
                    </div>
                    <div class="status-counts">
                        <div class="status-count">
                            <span class="count"><?php echo esc_html($stats->pending_review); ?></span>
                            <span class="label"><?php _e('Pending Review', 'piper-privacy'); ?></span>
                        </div>
                        <div class="status-count">
                            <span class="count"><?php echo esc_html($stats->in_progress); ?></span>
                            <span class="label"><?php _e('In Progress', 'piper-privacy'); ?></span>
                        </div>
                        <div class="status-count">
                            <span class="count"><?php echo esc_html($stats->approved); ?></span>
                            <span class="label"><?php _e('Approved', 'piper-privacy'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="workflow-dashboard-grid">
        <!-- Items Needing Attention -->
        <div class="workflow-section attention-needed">
            <h2><?php _e('Items Needing Attention', 'piper-privacy'); ?></h2>
            <?php if (!empty($attention_needed)) : ?>
                <?php foreach ($attention_needed as $post_type => $items) :
                    $post_type_obj = get_post_type_object($post_type);
                ?>
                    <div class="attention-group">
                        <h3><?php echo esc_html($post_type_obj->labels->name); ?></h3>
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th><?php _e('Title', 'piper-privacy'); ?></th>
                                    <th><?php _e('Status', 'piper-privacy'); ?></th>
                                    <th><?php _e('Last Updated', 'piper-privacy'); ?></th>
                                    <th><?php _e('Actions', 'piper-privacy'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item) : ?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo esc_url(get_edit_post_link($item->ID)); ?>">
                                                <?php echo esc_html($item->post_title); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?php echo esc_attr($item->post_status); ?>">
                                                <?php echo esc_html(get_post_status_object($item->post_status)->label); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo esc_html(get_the_modified_date('', $item->ID)); ?>
                                        </td>
                                        <td>
                                            <a href="<?php echo esc_url(get_edit_post_link($item->ID)); ?>" class="button button-small">
                                                <?php _e('Review', 'piper-privacy'); ?>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <p><?php _e('No items currently need attention.', 'piper-privacy'); ?></p>
            <?php endif; ?>
        </div>

        <!-- Recent Activity -->
        <div class="workflow-section recent-activity">
            <h2><?php _e('Recent Activity', 'piper-privacy'); ?></h2>
            <?php if (!empty($recent_transitions)) : ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Item', 'piper-privacy'); ?></th>
                            <th><?php _e('Type', 'piper-privacy'); ?></th>
                            <th><?php _e('Status Change', 'piper-privacy'); ?></th>
                            <th><?php _e('User', 'piper-privacy'); ?></th>
                            <th><?php _e('Date', 'piper-privacy'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_transitions as $transition) : 
                            $post_type_obj = get_post_type_object($transition->post_type);
                        ?>
                            <tr>
                                <td>
                                    <a href="<?php echo esc_url(get_edit_post_link($transition->post_id)); ?>">
                                        <?php echo esc_html($transition->post_title); ?>
                                    </a>
                                </td>
                                <td><?php echo esc_html($post_type_obj->labels->singular_name); ?></td>
                                <td>
                                    <span class="status-change">
                                        <span class="status-badge status-<?php echo esc_attr($transition->old_status); ?>">
                                            <?php echo esc_html(get_post_status_object($transition->old_status)->label); ?>
                                        </span>
                                        →
                                        <span class="status-badge status-<?php echo esc_attr($transition->new_status); ?>">
                                            <?php echo esc_html(get_post_status_object($transition->new_status)->label); ?>
                                        </span>
                                    </span>
                                </td>
                                <td><?php echo esc_html($transition->display_name); ?></td>
                                <td><?php echo esc_html(human_time_diff(strtotime($transition->date), current_time('timestamp'))); ?> ago</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p><?php _e('No recent activity.', 'piper-privacy'); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>
