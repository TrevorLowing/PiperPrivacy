<?php
/**
 * Privacy Metrics Dashboard Page
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <?php settings_errors(); ?>

    <div class="privacy-metrics">
        <!-- Metrics Overview -->
        <div class="metrics-overview">
            <h2><?php _e('Metrics Overview', 'piper-privacy'); ?></h2>
            <div class="metric-cards">
                <div class="metric-card">
                    <h3><?php _e('Collection Health', 'piper-privacy'); ?></h3>
                    <div class="metric-value">
                        <?php echo esc_html(get_option('piper_privacy_collection_health', '0%')); ?>
                    </div>
                    <div class="metric-trend">
                        <?php
                        $trend = get_option('piper_privacy_collection_health_trend', 0);
                        $trend_class = $trend >= 0 ? 'positive' : 'negative';
                        echo sprintf('<span class="trend %s">%+d%%</span>', esc_attr($trend_class), esc_html($trend));
                        ?>
                    </div>
                </div>

                <div class="metric-card">
                    <h3><?php _e('Control Effectiveness', 'piper-privacy'); ?></h3>
                    <div class="metric-value">
                        <?php echo esc_html(get_option('piper_privacy_control_effectiveness', '0%')); ?>
                    </div>
                    <div class="metric-trend">
                        <?php
                        $trend = get_option('piper_privacy_control_effectiveness_trend', 0);
                        $trend_class = $trend >= 0 ? 'positive' : 'negative';
                        echo sprintf('<span class="trend %s">%+d%%</span>', esc_attr($trend_class), esc_html($trend));
                        ?>
                    </div>
                </div>

                <div class="metric-card">
                    <h3><?php _e('Training Compliance', 'piper-privacy'); ?></h3>
                    <div class="metric-value">
                        <?php echo esc_html(get_option('piper_privacy_training_compliance', '0%')); ?>
                    </div>
                    <div class="metric-trend">
                        <?php
                        $trend = get_option('piper_privacy_training_compliance_trend', 0);
                        $trend_class = $trend >= 0 ? 'positive' : 'negative';
                        echo sprintf('<span class="trend %s">%+d%%</span>', esc_attr($trend_class), esc_html($trend));
                        ?>
                    </div>
                </div>

                <div class="metric-card">
                    <h3><?php _e('Incident Resolution', 'piper-privacy'); ?></h3>
                    <div class="metric-value">
                        <?php echo esc_html(get_option('piper_privacy_incident_resolution', '0%')); ?>
                    </div>
                    <div class="metric-trend">
                        <?php
                        $trend = get_option('piper_privacy_incident_resolution_trend', 0);
                        $trend_class = $trend >= 0 ? 'positive' : 'negative';
                        echo sprintf('<span class="trend %s">%+d%%</span>', esc_attr($trend_class), esc_html($trend));
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Metrics -->
        <div class="detailed-metrics">
            <h2><?php _e('Detailed Metrics', 'piper-privacy'); ?></h2>
            
            <!-- Collections Metrics -->
            <div class="metric-section">
                <h3><?php _e('Collections Metrics', 'piper-privacy'); ?></h3>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Metric', 'piper-privacy'); ?></th>
                            <th><?php _e('Current', 'piper-privacy'); ?></th>
                            <th><?php _e('Target', 'piper-privacy'); ?></th>
                            <th><?php _e('Trend', 'piper-privacy'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php _e('Active Collections', 'piper-privacy'); ?></td>
                            <td><?php echo esc_html(get_option('piper_privacy_active_collections', 0)); ?></td>
                            <td><?php echo esc_html(get_option('piper_privacy_target_collections', 0)); ?></td>
                            <td>
                                <?php
                                $trend = get_option('piper_privacy_collections_trend', 0);
                                $trend_class = $trend >= 0 ? 'positive' : 'negative';
                                echo sprintf('<span class="trend %s">%+d</span>', esc_attr($trend_class), esc_html($trend));
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td><?php _e('Documentation Coverage', 'piper-privacy'); ?></td>
                            <td><?php echo esc_html(get_option('piper_privacy_documentation_coverage', '0%')); ?></td>
                            <td>100%</td>
                            <td>
                                <?php
                                $trend = get_option('piper_privacy_documentation_trend', 0);
                                $trend_class = $trend >= 0 ? 'positive' : 'negative';
                                echo sprintf('<span class="trend %s">%+d%%</span>', esc_attr($trend_class), esc_html($trend));
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td><?php _e('Review Compliance', 'piper-privacy'); ?></td>
                            <td><?php echo esc_html(get_option('piper_privacy_review_compliance', '0%')); ?></td>
                            <td>100%</td>
                            <td>
                                <?php
                                $trend = get_option('piper_privacy_review_trend', 0);
                                $trend_class = $trend >= 0 ? 'positive' : 'negative';
                                echo sprintf('<span class="trend %s">%+d%%</span>', esc_attr($trend_class), esc_html($trend));
                                ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Controls Metrics -->
            <div class="metric-section">
                <h3><?php _e('Controls Metrics', 'piper-privacy'); ?></h3>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Metric', 'piper-privacy'); ?></th>
                            <th><?php _e('Current', 'piper-privacy'); ?></th>
                            <th><?php _e('Target', 'piper-privacy'); ?></th>
                            <th><?php _e('Trend', 'piper-privacy'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php _e('Implemented Controls', 'piper-privacy'); ?></td>
                            <td><?php echo esc_html(get_option('piper_privacy_implemented_controls', 0)); ?></td>
                            <td><?php echo esc_html(get_option('piper_privacy_target_controls', 0)); ?></td>
                            <td>
                                <?php
                                $trend = get_option('piper_privacy_controls_trend', 0);
                                $trend_class = $trend >= 0 ? 'positive' : 'negative';
                                echo sprintf('<span class="trend %s">%+d</span>', esc_attr($trend_class), esc_html($trend));
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td><?php _e('Control Testing', 'piper-privacy'); ?></td>
                            <td><?php echo esc_html(get_option('piper_privacy_control_testing', '0%')); ?></td>
                            <td>100%</td>
                            <td>
                                <?php
                                $trend = get_option('piper_privacy_testing_trend', 0);
                                $trend_class = $trend >= 0 ? 'positive' : 'negative';
                                echo sprintf('<span class="trend %s">%+d%%</span>', esc_attr($trend_class), esc_html($trend));
                                ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Training Metrics -->
            <div class="metric-section">
                <h3><?php _e('Training Metrics', 'piper-privacy'); ?></h3>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Metric', 'piper-privacy'); ?></th>
                            <th><?php _e('Current', 'piper-privacy'); ?></th>
                            <th><?php _e('Target', 'piper-privacy'); ?></th>
                            <th><?php _e('Trend', 'piper-privacy'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php _e('Training Completion', 'piper-privacy'); ?></td>
                            <td><?php echo esc_html(get_option('piper_privacy_training_completion', '0%')); ?></td>
                            <td>100%</td>
                            <td>
                                <?php
                                $trend = get_option('piper_privacy_training_trend', 0);
                                $trend_class = $trend >= 0 ? 'positive' : 'negative';
                                echo sprintf('<span class="trend %s">%+d%%</span>', esc_attr($trend_class), esc_html($trend));
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td><?php _e('Certification Rate', 'piper-privacy'); ?></td>
                            <td><?php echo esc_html(get_option('piper_privacy_certification_rate', '0%')); ?></td>
                            <td>100%</td>
                            <td>
                                <?php
                                $trend = get_option('piper_privacy_certification_trend', 0);
                                $trend_class = $trend >= 0 ? 'positive' : 'negative';
                                echo sprintf('<span class="trend %s">%+d%%</span>', esc_attr($trend_class), esc_html($trend));
                                ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Incident Metrics -->
            <div class="metric-section">
                <h3><?php _e('Incident Metrics', 'piper-privacy'); ?></h3>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Metric', 'piper-privacy'); ?></th>
                            <th><?php _e('Current', 'piper-privacy'); ?></th>
                            <th><?php _e('Target', 'piper-privacy'); ?></th>
                            <th><?php _e('Trend', 'piper-privacy'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php _e('Mean Time to Resolution', 'piper-privacy'); ?></td>
                            <td><?php echo esc_html(get_option('piper_privacy_mttr', '0 hours')); ?></td>
                            <td><?php echo esc_html(get_option('piper_privacy_target_mttr', '24 hours')); ?></td>
                            <td>
                                <?php
                                $trend = get_option('piper_privacy_mttr_trend', 0);
                                $trend_class = $trend <= 0 ? 'positive' : 'negative';
                                echo sprintf('<span class="trend %s">%+d hours</span>', esc_attr($trend_class), esc_html($trend));
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td><?php _e('Incident Rate', 'piper-privacy'); ?></td>
                            <td><?php echo esc_html(get_option('piper_privacy_incident_rate', '0/month')); ?></td>
                            <td><?php echo esc_html(get_option('piper_privacy_target_incident_rate', '0/month')); ?></td>
                            <td>
                                <?php
                                $trend = get_option('piper_privacy_incident_rate_trend', 0);
                                $trend_class = $trend <= 0 ? 'positive' : 'negative';
                                echo sprintf('<span class="trend %s">%+d</span>', esc_attr($trend_class), esc_html($trend));
                                ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Export Options -->
        <div class="metrics-export">
            <h2><?php _e('Export Options', 'piper-privacy'); ?></h2>
            <div class="export-actions">
                <button class="button" id="export-metrics-report">
                    <?php _e('Export Full Metrics Report', 'piper-privacy'); ?>
                </button>
                <button class="button" id="export-metrics-dashboard">
                    <?php _e('Export Dashboard View', 'piper-privacy'); ?>
                </button>
                <button class="button" id="schedule-metrics-report">
                    <?php _e('Schedule Regular Reports', 'piper-privacy'); ?>
                </button>
            </div>
        </div>
    </div>
</div>
