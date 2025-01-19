<?php
/**
 * Privacy Compliance Management Page
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <?php settings_errors(); ?>

    <div class="privacy-compliance">
        <!-- Compliance Overview -->
        <div class="compliance-overview">
            <h2><?php _e('Compliance Overview', 'piper-privacy'); ?></h2>
            <div class="compliance-stats">
                <div class="stat-box">
                    <span class="stat-label"><?php _e('Overall Compliance', 'piper-privacy'); ?></span>
                    <span class="stat-value"><?php echo esc_html(get_option('piper_privacy_overall_compliance', '0%')); ?></span>
                </div>
                <div class="stat-box">
                    <span class="stat-label"><?php _e('Active Requirements', 'piper-privacy'); ?></span>
                    <span class="stat-value"><?php echo esc_html(get_option('piper_privacy_active_requirements', 0)); ?></span>
                </div>
                <div class="stat-box">
                    <span class="stat-label"><?php _e('Due Assessments', 'piper-privacy'); ?></span>
                    <span class="stat-value"><?php echo esc_html(get_option('piper_privacy_due_assessments', 0)); ?></span>
                </div>
            </div>
        </div>

        <!-- Compliance Framework -->
        <div class="compliance-framework">
            <h2><?php _e('Compliance Framework', 'piper-privacy'); ?></h2>
            
            <?php
            $frameworks = get_option('piper_privacy_compliance_frameworks', array());
            if (!empty($frameworks)) :
            ?>
                <div class="framework-grid">
                    <?php foreach ($frameworks as $id => $framework) : ?>
                        <div class="framework-card">
                            <h3><?php echo esc_html($framework['name']); ?></h3>
                            <div class="framework-meta">
                                <span class="framework-version"><?php echo esc_html($framework['version']); ?></span>
                                <span class="framework-status status-<?php echo esc_attr($framework['status']); ?>">
                                    <?php echo esc_html($framework['status']); ?>
                                </span>
                            </div>
                            <div class="framework-progress">
                                <div class="progress-bar" style="width: <?php echo esc_attr($framework['compliance_rate']); ?>%">
                                    <span class="progress-text"><?php echo esc_html($framework['compliance_rate'] . '%'); ?></span>
                                </div>
                            </div>
                            <div class="framework-actions">
                                <a href="#" class="view-requirements" data-id="<?php echo esc_attr($id); ?>">
                                    <?php _e('View Requirements', 'piper-privacy'); ?>
                                </a>
                                <a href="#" class="view-assessment" data-id="<?php echo esc_attr($id); ?>">
                                    <?php _e('View Assessment', 'piper-privacy'); ?>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <p><?php _e('No compliance frameworks configured.', 'piper-privacy'); ?></p>
            <?php endif; ?>

            <div class="add-framework">
                <button class="button" id="add-framework-button">
                    <?php _e('Add Compliance Framework', 'piper-privacy'); ?>
                </button>
            </div>
        </div>

        <!-- Requirements Management -->
        <div class="requirements-management">
            <h2><?php _e('Requirements Management', 'piper-privacy'); ?></h2>
            
            <?php
            $requirements = get_option('piper_privacy_compliance_requirements', array());
            if (!empty($requirements)) :
            ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Requirement', 'piper-privacy'); ?></th>
                            <th><?php _e('Framework', 'piper-privacy'); ?></th>
                            <th><?php _e('Status', 'piper-privacy'); ?></th>
                            <th><?php _e('Last Assessment', 'piper-privacy'); ?></th>
                            <th><?php _e('Due Date', 'piper-privacy'); ?></th>
                            <th><?php _e('Actions', 'piper-privacy'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requirements as $id => $requirement) : ?>
                            <tr>
                                <td><?php echo esc_html($requirement['name']); ?></td>
                                <td><?php echo esc_html($requirement['framework']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo esc_attr($requirement['status']); ?>">
                                        <?php echo esc_html($requirement['status']); ?>
                                    </span>
                                </td>
                                <td><?php 
                                    $last_assessment = isset($requirement['last_assessment']) ? 
                                        human_time_diff(strtotime($requirement['last_assessment']), current_time('timestamp')) . ' ago' : 
                                        __('Never', 'piper-privacy');
                                    echo esc_html($last_assessment);
                                ?></td>
                                <td><?php 
                                    echo isset($requirement['due_date']) ? 
                                        esc_html(date_i18n(get_option('date_format'), strtotime($requirement['due_date']))) : 
                                        '—';
                                ?></td>
                                <td>
                                    <a href="#" class="assess-requirement" data-id="<?php echo esc_attr($id); ?>">
                                        <?php _e('Assess', 'piper-privacy'); ?>
                                    </a> |
                                    <a href="#" class="view-history" data-id="<?php echo esc_attr($id); ?>">
                                        <?php _e('History', 'piper-privacy'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p><?php _e('No compliance requirements found.', 'piper-privacy'); ?></p>
            <?php endif; ?>
        </div>

        <!-- Assessment History -->
        <div class="assessment-history">
            <h2><?php _e('Assessment History', 'piper-privacy'); ?></h2>
            
            <?php
            $assessments = get_option('piper_privacy_compliance_assessments', array());
            if (!empty($assessments)) :
            ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Date', 'piper-privacy'); ?></th>
                            <th><?php _e('Framework', 'piper-privacy'); ?></th>
                            <th><?php _e('Assessor', 'piper-privacy'); ?></th>
                            <th><?php _e('Result', 'piper-privacy'); ?></th>
                            <th><?php _e('Actions', 'piper-privacy'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($assessments as $id => $assessment) : ?>
                            <tr>
                                <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($assessment['date']))); ?></td>
                                <td><?php echo esc_html($assessment['framework']); ?></td>
                                <td><?php 
                                    $assessor = isset($stakeholders[$assessment['assessor']]) ? $stakeholders[$assessment['assessor']]['name'] : '';
                                    echo esc_html($assessor);
                                ?></td>
                                <td>
                                    <span class="result-badge result-<?php echo esc_attr(strtolower($assessment['result'])); ?>">
                                        <?php echo esc_html($assessment['result']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="#" class="view-assessment" data-id="<?php echo esc_attr($id); ?>">
                                        <?php _e('View Details', 'piper-privacy'); ?>
                                    </a> |
                                    <a href="#" class="export-assessment" data-id="<?php echo esc_attr($id); ?>">
                                        <?php _e('Export Report', 'piper-privacy'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p><?php _e('No assessment history found.', 'piper-privacy'); ?></p>
            <?php endif; ?>
        </div>

        <!-- Export Options -->
        <div class="compliance-export">
            <h2><?php _e('Export Options', 'piper-privacy'); ?></h2>
            <div class="export-actions">
                <button class="button" id="export-compliance-report">
                    <?php _e('Export Compliance Report', 'piper-privacy'); ?>
                </button>
                <button class="button" id="export-assessment-history">
                    <?php _e('Export Assessment History', 'piper-privacy'); ?>
                </button>
                <button class="button" id="schedule-compliance-report">
                    <?php _e('Schedule Regular Reports', 'piper-privacy'); ?>
                </button>
            </div>
        </div>
    </div>
</div>
