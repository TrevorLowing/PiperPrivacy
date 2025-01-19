<?php
/**
 * Main admin dashboard display for PiperPrivacy
 */
?>
<div class="wrap piper-privacy-dashboard">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <?php settings_errors(); ?>

    <!-- Overview Cards -->
    <div class="overview-cards">
        <div class="card">
            <h3><span class="dashicons dashicons-shield"></span> <?php _e('Active Collections', 'piper-privacy'); ?></h3>
            <div class="card-count"><?php 
                $post_counts = wp_count_posts('privacy_collection');
                echo esc_html(isset($post_counts->publish) ? $post_counts->publish : 0); 
            ?></div>
            <div class="card-actions">
                <a href="<?php echo admin_url('edit.php?post_type=privacy_collection&post_status=publish'); ?>" class="button">
                    <?php _e('View All', 'piper-privacy'); ?>
                </a>
            </div>
        </div>

        <div class="card">
            <h3><span class="dashicons dashicons-warning"></span> <?php _e('Pending Reviews', 'piper-privacy'); ?></h3>
            <div class="card-count"><?php 
                $post_counts = wp_count_posts('privacy_collection');
                echo esc_html(isset($post_counts->draft) ? $post_counts->draft : 0); 
            ?></div>
            <div class="card-actions">
                <a href="<?php echo admin_url('edit.php?post_type=privacy_collection&post_status=draft'); ?>" class="button">
                    <?php _e('Review', 'piper-privacy'); ?>
                </a>
            </div>
        </div>

        <div class="card">
            <h3><span class="dashicons dashicons-analytics"></span> <?php _e('Risk Assessments', 'piper-privacy'); ?></h3>
            <div class="card-count"><?php 
                $post_counts = wp_count_posts('privacy_impact');
                echo esc_html(isset($post_counts->publish) ? $post_counts->publish : 0); 
            ?></div>
            <div class="card-actions">
                <a href="<?php echo admin_url('edit.php?post_type=privacy_impact'); ?>" class="button">
                    <?php _e('Manage', 'piper-privacy'); ?>
                </a>
            </div>
        </div>

        <div class="card">
            <h3><span class="dashicons dashicons-clock"></span> <?php _e('Due for Review', 'piper-privacy'); ?></h3>
            <?php 
            $args = array(
                'post_type' => 'privacy_collection',
                'meta_query' => array(
                    array(
                        'key' => 'next_review_date',
                        'value' => date('Y-m-d'),
                        'compare' => '<=',
                        'type' => 'DATE'
                    )
                )
            );
            $review_query = new WP_Query($args);
            ?>
            <div class="card-count"><?php echo $review_query->found_posts; ?></div>
            <div class="card-actions">
                <a href="<?php echo admin_url('edit.php?post_type=privacy_collection&review_status=due'); ?>" class="button">
                    <?php _e('Review Now', 'piper-privacy'); ?>
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions-section">
        <h2><?php _e('Quick Actions', 'piper-privacy'); ?></h2>
        <div class="quick-actions">
            <a href="<?php echo admin_url('post-new.php?post_type=privacy_collection'); ?>" class="action-button primary">
                <span class="dashicons dashicons-plus-alt"></span>
                <?php _e('New Collection', 'piper-privacy'); ?>
            </a>
            
            <a href="<?php echo admin_url('post-new.php?post_type=privacy_impact'); ?>" class="action-button">
                <span class="dashicons dashicons-chart-area"></span>
                <?php _e('New Impact Assessment', 'piper-privacy'); ?>
            </a>
            
            <a href="<?php echo admin_url('admin.php?page=piper-privacy-breach'); ?>" class="action-button warning">
                <span class="dashicons dashicons-warning"></span>
                <?php _e('Report Breach', 'piper-privacy'); ?>
            </a>
            
            <a href="<?php echo admin_url('admin.php?page=piper-privacy-export'); ?>" class="action-button">
                <span class="dashicons dashicons-download"></span>
                <?php _e('Export Report', 'piper-privacy'); ?>
            </a>
        </div>
    </div>

    <!-- Collection Lifecycle Management -->
    <div class="lifecycle-section">
        <h2><?php _e('Collection Lifecycle Management', 'piper-privacy'); ?></h2>
        <div class="lifecycle-stages">
            <div class="stage">
                <h3><?php _e('Planning', 'piper-privacy'); ?></h3>
                <ul>
                    <li><a href="<?php echo admin_url('post-new.php?post_type=privacy_collection'); ?>"><?php _e('Create Collection Plan', 'piper-privacy'); ?></a></li>
                    <li><a href="<?php echo admin_url('post-new.php?post_type=privacy_impact'); ?>"><?php _e('Impact Assessment', 'piper-privacy'); ?></a></li>
                    <li><a href="<?php echo admin_url('admin.php?page=piper-privacy-stakeholders'); ?>"><?php _e('Stakeholder Review', 'piper-privacy'); ?></a></li>
                </ul>
            </div>
            
            <div class="stage">
                <h3><?php _e('Implementation', 'piper-privacy'); ?></h3>
                <ul>
                    <li><a href="<?php echo admin_url('admin.php?page=piper-privacy-controls'); ?>"><?php _e('Privacy Controls', 'piper-privacy'); ?></a></li>
                    <li><a href="<?php echo admin_url('admin.php?page=piper-privacy-documentation'); ?>"><?php _e('Documentation', 'piper-privacy'); ?></a></li>
                    <li><a href="<?php echo admin_url('admin.php?page=piper-privacy-training'); ?>"><?php _e('Training Materials', 'piper-privacy'); ?></a></li>
                </ul>
            </div>
            
            <div class="stage">
                <h3><?php _e('Monitoring', 'piper-privacy'); ?></h3>
                <ul>
                    <li><a href="<?php echo admin_url('admin.php?page=piper-privacy-reviews'); ?>"><?php _e('Regular Reviews', 'piper-privacy'); ?></a></li>
                    <li><a href="<?php echo admin_url('admin.php?page=piper-privacy-incidents'); ?>"><?php _e('Incident Management', 'piper-privacy'); ?></a></li>
                    <li><a href="<?php echo admin_url('admin.php?page=piper-privacy-metrics'); ?>"><?php _e('Privacy Metrics', 'piper-privacy'); ?></a></li>
                </ul>
            </div>
            
            <div class="stage">
                <h3><?php _e('Maintenance', 'piper-privacy'); ?></h3>
                <ul>
                    <li><a href="<?php echo admin_url('admin.php?page=piper-privacy-updates'); ?>"><?php _e('Update Records', 'piper-privacy'); ?></a></li>
                    <li><a href="<?php echo admin_url('admin.php?page=piper-privacy-compliance'); ?>"><?php _e('Compliance Checks', 'piper-privacy'); ?></a></li>
                    <li><a href="<?php echo admin_url('admin.php?page=piper-privacy-retention'); ?>"><?php _e('Retention Management', 'piper-privacy'); ?></a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="recent-activity-section">
        <h2><?php _e('Recent Activity', 'piper-privacy'); ?></h2>
        <div class="activity-list">
            <?php
            $recent_items = get_posts(array(
                'post_type' => array('privacy_collection', 'privacy_impact', 'privacy_threshold'),
                'posts_per_page' => 5,
                'orderby' => 'modified',
                'order' => 'DESC'
            ));

            if ($recent_items) :
                foreach ($recent_items as $item) :
                    $post_type_obj = get_post_type_object($item->post_type);
                    $type_label = $post_type_obj->labels->singular_name;
                    $modified_time = human_time_diff(strtotime($item->post_modified), current_time('timestamp'));
            ?>
                <div class="activity-item">
                    <span class="activity-type"><?php echo esc_html($type_label); ?></span>
                    <span class="activity-title">
                        <a href="<?php echo get_edit_post_link($item->ID); ?>">
                            <?php echo esc_html($item->post_title); ?>
                        </a>
                    </span>
                    <span class="activity-meta">
                        <?php printf(__('Updated %s ago', 'piper-privacy'), $modified_time); ?>
                        <?php 
                        $status = get_post_status_object($item->post_status);
                        echo ' - ' . esc_html($status->label);
                        ?>
                    </span>
                </div>
            <?php
                endforeach;
            else :
            ?>
                <p><?php _e('No recent activity found.', 'piper-privacy'); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>
