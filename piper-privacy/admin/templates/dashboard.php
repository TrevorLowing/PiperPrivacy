<div class="wrap">
    <h1><?php echo esc_html__('Privacy Management Dashboard', 'piper-privacy'); ?></h1>

    <div class="privacy-dashboard">
        <!-- Statistics Overview -->
        <div class="dashboard-section">
            <h2><?php echo esc_html__('Overview', 'piper-privacy'); ?></h2>
            <div class="stats-grid">
                <!-- Privacy Collections -->
                <div class="stat-box">
                    <h3><?php echo esc_html__('Privacy Collections', 'piper-privacy'); ?></h3>
                    <div class="stat-numbers">
                        <div class="stat-item">
                            <span class="stat-label"><?php echo esc_html__('Total:', 'piper-privacy'); ?></span>
                            <span class="stat-value"><?php echo esc_html($stats['collections']['total']); ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label"><?php echo esc_html__('Draft:', 'piper-privacy'); ?></span>
                            <span class="stat-value"><?php echo esc_html($stats['collections']['draft']); ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label"><?php echo esc_html__('Pending:', 'piper-privacy'); ?></span>
                            <span class="stat-value"><?php echo esc_html($stats['collections']['pending']); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Privacy Thresholds -->
                <div class="stat-box">
                    <h3><?php echo esc_html__('Privacy Thresholds', 'piper-privacy'); ?></h3>
                    <div class="stat-numbers">
                        <div class="stat-item">
                            <span class="stat-label"><?php echo esc_html__('Total:', 'piper-privacy'); ?></span>
                            <span class="stat-value"><?php echo esc_html($stats['thresholds']['total']); ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label"><?php echo esc_html__('Draft:', 'piper-privacy'); ?></span>
                            <span class="stat-value"><?php echo esc_html($stats['thresholds']['draft']); ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label"><?php echo esc_html__('Pending:', 'piper-privacy'); ?></span>
                            <span class="stat-value"><?php echo esc_html($stats['thresholds']['pending']); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Privacy Impact Assessments -->
                <div class="stat-box">
                    <h3><?php echo esc_html__('Privacy Impact Assessments', 'piper-privacy'); ?></h3>
                    <div class="stat-numbers">
                        <div class="stat-item">
                            <span class="stat-label"><?php echo esc_html__('Total:', 'piper-privacy'); ?></span>
                            <span class="stat-value"><?php echo esc_html($stats['impacts']['total']); ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label"><?php echo esc_html__('Draft:', 'piper-privacy'); ?></span>
                            <span class="stat-value"><?php echo esc_html($stats['impacts']['draft']); ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label"><?php echo esc_html__('Pending:', 'piper-privacy'); ?></span>
                            <span class="stat-value"><?php echo esc_html($stats['impacts']['pending']); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="dashboard-section">
            <h2><?php echo esc_html__('Quick Actions', 'piper-privacy'); ?></h2>
            <div class="quick-actions">
                <a href="<?php echo esc_url(admin_url('post-new.php?post_type=privacy_collection')); ?>" class="button button-primary">
                    <?php echo esc_html__('New Privacy Collection', 'piper-privacy'); ?>
                </a>
                <a href="<?php echo esc_url(admin_url('post-new.php?post_type=privacy_threshold')); ?>" class="button button-primary">
                    <?php echo esc_html__('New Threshold Analysis', 'piper-privacy'); ?>
                </a>
                <a href="<?php echo esc_url(admin_url('post-new.php?post_type=privacy_impact')); ?>" class="button button-primary">
                    <?php echo esc_html__('New Impact Assessment', 'piper-privacy'); ?>
                </a>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="dashboard-section">
            <h2><?php echo esc_html__('Recent Activity', 'piper-privacy'); ?></h2>
            <?php
            $recent_posts = get_posts([
                'post_type' => ['privacy_collection', 'privacy_threshold', 'privacy_impact'],
                'posts_per_page' => 5,
                'orderby' => 'modified',
                'order' => 'DESC',
            ]);

            if ($recent_posts) : ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php echo esc_html__('Title', 'piper-privacy'); ?></th>
                            <th><?php echo esc_html__('Type', 'piper-privacy'); ?></th>
                            <th><?php echo esc_html__('Status', 'piper-privacy'); ?></th>
                            <th><?php echo esc_html__('Details', 'piper-privacy'); ?></th>
                            <th><?php echo esc_html__('Modified', 'piper-privacy'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_posts as $post) : 
                            // Get post type specific details
                            $details = '';
                            switch ($post->post_type) {
                                case 'privacy_collection':
                                    $sharing_parties = pp_get_group_field('sharing_parties', $post->ID);
                                    $security_controls = pp_get_group_field('security_controls', $post->ID);
                                    $details = sprintf(
                                        __('Sharing Parties: %d | Security Controls: %d', 'piper-privacy'),
                                        is_array($sharing_parties) ? count($sharing_parties) : 0,
                                        is_array($security_controls) ? count($security_controls) : 0
                                    );
                                    break;
                                case 'privacy_threshold':
                                    $data_elements = pp_get_group_field('data_elements_analysis', $post->ID);
                                    $high_sensitivity = 0;
                                    if (is_array($data_elements)) {
                                        foreach ($data_elements as $element) {
                                            if ($element['sensitivity_level'] === 'high') {
                                                $high_sensitivity++;
                                            }
                                        }
                                    }
                                    $details = sprintf(
                                        __('Data Elements: %d | High Sensitivity: %d', 'piper-privacy'),
                                        is_array($data_elements) ? count($data_elements) : 0,
                                        $high_sensitivity
                                    );
                                    break;
                                case 'privacy_impact':
                                    $risks = pp_get_group_field('risk_assessment', $post->ID);
                                    $high_risks = 0;
                                    if (is_array($risks)) {
                                        foreach ($risks as $risk) {
                                            if ($risk['likelihood'] === 'high' || $risk['impact'] === 'high') {
                                                $high_risks++;
                                            }
                                        }
                                    }
                                    $details = sprintf(
                                        __('Total Risks: %d | High Risks: %d', 'piper-privacy'),
                                        is_array($risks) ? count($risks) : 0,
                                        $high_risks
                                    );
                                    break;
                            }
                            ?>
                            <tr>
                                <td>
                                    <a href="<?php echo esc_url(get_edit_post_link($post->ID)); ?>">
                                        <?php echo esc_html($post->post_title); ?>
                                    </a>
                                </td>
                                <td><?php echo esc_html(get_post_type_object($post->post_type)->labels->singular_name); ?></td>
                                <td><?php echo esc_html(get_post_status_object($post->post_status)->label); ?></td>
                                <td><?php echo esc_html($details); ?></td>
                                <td><?php echo esc_html(get_the_modified_date('', $post)); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p><?php echo esc_html__('No recent activity found.', 'piper-privacy'); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>
