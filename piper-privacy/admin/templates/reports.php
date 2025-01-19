<div class="wrap">
    <h1><?php echo esc_html__('Privacy Management Reports', 'piper-privacy'); ?></h1>

    <!-- Report Navigation -->
    <h2 class="nav-tab-wrapper">
        <a href="#workflow-history" class="nav-tab nav-tab-active"><?php echo esc_html__('Workflow History', 'piper-privacy'); ?></a>
        <a href="#audit-log" class="nav-tab"><?php echo esc_html__('Audit Log', 'piper-privacy'); ?></a>
        <a href="#risk-report" class="nav-tab"><?php echo esc_html__('Risk Report', 'piper-privacy'); ?></a>
    </h2>

    <!-- Workflow History Report -->
    <div id="workflow-history" class="report-section">
        <div class="tablenav top">
            <div class="alignleft actions">
                <form method="get">
                    <input type="hidden" name="page" value="piper-privacy-reports">
                    <select name="workflow_filter">
                        <option value=""><?php echo esc_html__('All Types', 'piper-privacy'); ?></option>
                        <option value="privacy_collection"><?php echo esc_html__('Privacy Collections', 'piper-privacy'); ?></option>
                        <option value="privacy_threshold"><?php echo esc_html__('Privacy Thresholds', 'piper-privacy'); ?></option>
                        <option value="privacy_impact"><?php echo esc_html__('Privacy Impacts', 'piper-privacy'); ?></option>
                    </select>
                    <input type="submit" class="button" value="<?php echo esc_attr__('Filter', 'piper-privacy'); ?>">
                </form>
            </div>
            <div class="tablenav-pages">
                <!-- Pagination would go here -->
            </div>
        </div>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php echo esc_html__('Date', 'piper-privacy'); ?></th>
                    <th><?php echo esc_html__('Item', 'piper-privacy'); ?></th>
                    <th><?php echo esc_html__('From Stage', 'piper-privacy'); ?></th>
                    <th><?php echo esc_html__('To Stage', 'piper-privacy'); ?></th>
                    <th><?php echo esc_html__('User', 'piper-privacy'); ?></th>
                    <th><?php echo esc_html__('Comments', 'piper-privacy'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($reports['workflow_history'])) : ?>
                    <?php foreach ($reports['workflow_history'] as $item) : ?>
                        <tr>
                            <td><?php echo esc_html(wp_date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($item->created_at))); ?></td>
                            <td>
                                <a href="<?php echo esc_url(get_edit_post_link($item->object_id)); ?>">
                                    <?php echo esc_html($item->post_title); ?>
                                </a>
                            </td>
                            <td><?php echo esc_html($item->from_stage); ?></td>
                            <td><?php echo esc_html($item->to_stage); ?></td>
                            <td><?php echo esc_html($item->display_name); ?></td>
                            <td><?php echo esc_html($item->comments); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="6"><?php echo esc_html__('No workflow history found.', 'piper-privacy'); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Audit Log Report -->
    <div id="audit-log" class="report-section" style="display: none;">
        <div class="tablenav top">
            <div class="alignleft actions">
                <form method="get">
                    <input type="hidden" name="page" value="piper-privacy-reports">
                    <select name="audit_action">
                        <option value=""><?php echo esc_html__('All Actions', 'piper-privacy'); ?></option>
                        <option value="create"><?php echo esc_html__('Create', 'piper-privacy'); ?></option>
                        <option value="update"><?php echo esc_html__('Update', 'piper-privacy'); ?></option>
                        <option value="delete"><?php echo esc_html__('Delete', 'piper-privacy'); ?></option>
                    </select>
                    <input type="submit" class="button" value="<?php echo esc_attr__('Filter', 'piper-privacy'); ?>">
                </form>
            </div>
            <div class="tablenav-pages">
                <!-- Pagination would go here -->
            </div>
        </div>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php echo esc_html__('Date', 'piper-privacy'); ?></th>
                    <th><?php echo esc_html__('User', 'piper-privacy'); ?></th>
                    <th><?php echo esc_html__('Action', 'piper-privacy'); ?></th>
                    <th><?php echo esc_html__('Object Type', 'piper-privacy'); ?></th>
                    <th><?php echo esc_html__('Object ID', 'piper-privacy'); ?></th>
                    <th><?php echo esc_html__('Details', 'piper-privacy'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($reports['audit_log'])) : ?>
                    <?php foreach ($reports['audit_log'] as $item) : ?>
                        <tr>
                            <td><?php echo esc_html(wp_date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($item->created_at))); ?></td>
                            <td><?php echo esc_html($item->display_name); ?></td>
                            <td><?php echo esc_html($item->action); ?></td>
                            <td><?php echo esc_html($item->object_type); ?></td>
                            <td><?php echo esc_html($item->object_id); ?></td>
                            <td><?php echo esc_html($item->details); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="6"><?php echo esc_html__('No audit log entries found.', 'piper-privacy'); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Risk Report -->
    <div id="risk-report" class="report-section" style="display: none;">
        <div class="tablenav top">
            <div class="alignleft actions">
                <form method="get">
                    <input type="hidden" name="page" value="piper-privacy-reports">
                    <select name="risk_level">
                        <option value=""><?php echo esc_html__('All Risk Levels', 'piper-privacy'); ?></option>
                        <option value="high"><?php echo esc_html__('High Risk', 'piper-privacy'); ?></option>
                        <option value="medium"><?php echo esc_html__('Medium Risk', 'piper-privacy'); ?></option>
                        <option value="low"><?php echo esc_html__('Low Risk', 'piper-privacy'); ?></option>
                    </select>
                    <input type="submit" class="button" value="<?php echo esc_attr__('Filter', 'piper-privacy'); ?>">
                </form>
            </div>
        </div>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php echo esc_html__('Assessment', 'piper-privacy'); ?></th>
                    <th><?php echo esc_html__('Risk Description', 'piper-privacy'); ?></th>
                    <th><?php echo esc_html__('Likelihood', 'piper-privacy'); ?></th>
                    <th><?php echo esc_html__('Impact', 'piper-privacy'); ?></th>
                    <th><?php echo esc_html__('Mitigation Strategy', 'piper-privacy'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $args = [
                    'post_type' => 'privacy_impact',
                    'posts_per_page' => -1,
                ];

                // Add risk level filter
                if (!empty($_GET['risk_level'])) {
                    $risk_level = sanitize_text_field($_GET['risk_level']);
                    $args['meta_query'] = [
                        'relation' => 'OR',
                        [
                            'key' => 'risk_assessment_likelihood',
                            'value' => $risk_level,
                            'compare' => '=',
                        ],
                        [
                            'key' => 'risk_assessment_impact',
                            'value' => $risk_level,
                            'compare' => '=',
                        ],
                    ];
                }

                $assessments = get_posts($args);

                if ($assessments) :
                    foreach ($assessments as $assessment) :
                        $risks = pp_get_group_field('risk_assessment', $assessment->ID);
                        if ($risks) :
                            foreach ($risks as $risk) : ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo esc_url(get_edit_post_link($assessment->ID)); ?>">
                                            <?php echo esc_html($assessment->post_title); ?>
                                        </a>
                                    </td>
                                    <td><?php echo esc_html($risk['risk_description']); ?></td>
                                    <td>
                                        <span class="risk-level risk-<?php echo esc_attr($risk['likelihood']); ?>">
                                            <?php echo esc_html(ucfirst($risk['likelihood'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="risk-level risk-<?php echo esc_attr($risk['impact']); ?>">
                                            <?php echo esc_html(ucfirst($risk['impact'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo esc_html($risk['mitigation_strategy']); ?></td>
                                </tr>
                            <?php endforeach;
                        endif;
                    endforeach;
                else : ?>
                    <tr>
                        <td colspan="5"><?php echo esc_html__('No risk assessments found.', 'piper-privacy'); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Handle tab switching
    $('.nav-tab-wrapper a').on('click', function(e) {
        e.preventDefault();
        var target = $(this).attr('href');
        
        // Update active tab
        $('.nav-tab-wrapper a').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        
        // Show target section
        $('.report-section').hide();
        $(target).show();

        // Update URL hash without scrolling
        if (history.pushState) {
            history.pushState(null, null, target);
        }
    });

    // Check for hash in URL
    if (window.location.hash) {
        $('a[href="' + window.location.hash + '"]').trigger('click');
    }
});
</script>
