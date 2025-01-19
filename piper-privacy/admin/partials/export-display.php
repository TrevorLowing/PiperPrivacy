<?php
/**
 * Privacy Export Management Page
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <?php settings_errors(); ?>

    <div class="privacy-export">
        <!-- Export Overview -->
        <div class="export-overview">
            <h2><?php _e('Export Overview', 'piper-privacy'); ?></h2>
            <div class="export-stats">
                <div class="stat-box">
                    <span class="stat-label"><?php _e('Pending Exports', 'piper-privacy'); ?></span>
                    <span class="stat-value"><?php echo esc_html(get_option('piper_privacy_pending_exports', 0)); ?></span>
                </div>
                <div class="stat-box">
                    <span class="stat-label"><?php _e('Completed Exports', 'piper-privacy'); ?></span>
                    <span class="stat-value"><?php echo esc_html(get_option('piper_privacy_completed_exports', 0)); ?></span>
                </div>
                <div class="stat-box">
                    <span class="stat-label"><?php _e('Average Processing Time', 'piper-privacy'); ?></span>
                    <span class="stat-value"><?php echo esc_html(get_option('piper_privacy_avg_export_time', '0 minutes')); ?></span>
                </div>
            </div>
        </div>

        <!-- Export Options -->
        <div class="export-options">
            <h2><?php _e('Export Options', 'piper-privacy'); ?></h2>
            
            <div class="export-cards">
                <!-- Privacy Collections Export -->
                <div class="export-card">
                    <h3><?php _e('Privacy Collections', 'piper-privacy'); ?></h3>
                    <p><?php _e('Export all privacy collection records with associated metadata.', 'piper-privacy'); ?></p>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <?php wp_nonce_field('export_collections', 'collections_export_nonce'); ?>
                        <input type="hidden" name="action" value="export_collections">
                        
                        <div class="export-options">
                            <label>
                                <input type="checkbox" name="include_metadata" value="1" checked>
                                <?php _e('Include Metadata', 'piper-privacy'); ?>
                            </label>
                            <label>
                                <input type="checkbox" name="include_history" value="1" checked>
                                <?php _e('Include History', 'piper-privacy'); ?>
                            </label>
                        </div>

                        <div class="export-format">
                            <select name="export_format" required>
                                <option value="csv"><?php _e('CSV', 'piper-privacy'); ?></option>
                                <option value="json"><?php _e('JSON', 'piper-privacy'); ?></option>
                                <option value="xml"><?php _e('XML', 'piper-privacy'); ?></option>
                            </select>
                        </div>

                        <?php submit_button(__('Export Collections', 'piper-privacy'), 'secondary'); ?>
                    </form>
                </div>

                <!-- Compliance Reports Export -->
                <div class="export-card">
                    <h3><?php _e('Compliance Reports', 'piper-privacy'); ?></h3>
                    <p><?php _e('Generate comprehensive compliance reports for selected frameworks.', 'piper-privacy'); ?></p>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <?php wp_nonce_field('export_compliance', 'compliance_export_nonce'); ?>
                        <input type="hidden" name="action" value="export_compliance">
                        
                        <div class="export-options">
                            <?php
                            $frameworks = get_option('piper_privacy_compliance_frameworks', array());
                            foreach ($frameworks as $id => $framework) :
                            ?>
                                <label>
                                    <input type="checkbox" name="frameworks[]" value="<?php echo esc_attr($id); ?>">
                                    <?php echo esc_html($framework['name']); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>

                        <div class="date-range">
                            <label>
                                <?php _e('From:', 'piper-privacy'); ?>
                                <input type="date" name="date_from" required>
                            </label>
                            <label>
                                <?php _e('To:', 'piper-privacy'); ?>
                                <input type="date" name="date_to" required>
                            </label>
                        </div>

                        <?php submit_button(__('Export Reports', 'piper-privacy'), 'secondary'); ?>
                    </form>
                </div>

                <!-- Metrics Dashboard Export -->
                <div class="export-card">
                    <h3><?php _e('Metrics Dashboard', 'piper-privacy'); ?></h3>
                    <p><?php _e('Export privacy metrics and analytics data.', 'piper-privacy'); ?></p>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <?php wp_nonce_field('export_metrics', 'metrics_export_nonce'); ?>
                        <input type="hidden" name="action" value="export_metrics">
                        
                        <div class="export-options">
                            <label>
                                <input type="checkbox" name="include_trends" value="1" checked>
                                <?php _e('Include Trends', 'piper-privacy'); ?>
                            </label>
                            <label>
                                <input type="checkbox" name="include_forecasts" value="1">
                                <?php _e('Include Forecasts', 'piper-privacy'); ?>
                            </label>
                        </div>

                        <div class="time-period">
                            <select name="time_period" required>
                                <option value="last_month"><?php _e('Last Month', 'piper-privacy'); ?></option>
                                <option value="last_quarter"><?php _e('Last Quarter', 'piper-privacy'); ?></option>
                                <option value="last_year"><?php _e('Last Year', 'piper-privacy'); ?></option>
                                <option value="custom"><?php _e('Custom Range', 'piper-privacy'); ?></option>
                            </select>
                        </div>

                        <?php submit_button(__('Export Metrics', 'piper-privacy'), 'secondary'); ?>
                    </form>
                </div>

                <!-- Audit Trail Export -->
                <div class="export-card">
                    <h3><?php _e('Audit Trail', 'piper-privacy'); ?></h3>
                    <p><?php _e('Export detailed audit logs of privacy-related activities.', 'piper-privacy'); ?></p>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <?php wp_nonce_field('export_audit', 'audit_export_nonce'); ?>
                        <input type="hidden" name="action" value="export_audit">
                        
                        <div class="export-options">
                            <label>
                                <input type="checkbox" name="include_user_actions" value="1" checked>
                                <?php _e('User Actions', 'piper-privacy'); ?>
                            </label>
                            <label>
                                <input type="checkbox" name="include_system_events" value="1" checked>
                                <?php _e('System Events', 'piper-privacy'); ?>
                            </label>
                            <label>
                                <input type="checkbox" name="include_automated_tasks" value="1" checked>
                                <?php _e('Automated Tasks', 'piper-privacy'); ?>
                            </label>
                        </div>

                        <div class="date-range">
                            <label>
                                <?php _e('From:', 'piper-privacy'); ?>
                                <input type="date" name="date_from" required>
                            </label>
                            <label>
                                <?php _e('To:', 'piper-privacy'); ?>
                                <input type="date" name="date_to" required>
                            </label>
                        </div>

                        <?php submit_button(__('Export Audit Trail', 'piper-privacy'), 'secondary'); ?>
                    </form>
                </div>
            </div>
        </div>

        <!-- Export History -->
        <div class="export-history">
            <h2><?php _e('Export History', 'piper-privacy'); ?></h2>
            
            <?php
            $exports = get_option('piper_privacy_export_history', array());
            if (!empty($exports)) :
            ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Date', 'piper-privacy'); ?></th>
                            <th><?php _e('Type', 'piper-privacy'); ?></th>
                            <th><?php _e('Format', 'piper-privacy'); ?></th>
                            <th><?php _e('Size', 'piper-privacy'); ?></th>
                            <th><?php _e('Status', 'piper-privacy'); ?></th>
                            <th><?php _e('Exported By', 'piper-privacy'); ?></th>
                            <th><?php _e('Actions', 'piper-privacy'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($exports as $id => $export) : ?>
                            <tr>
                                <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($export['date']))); ?></td>
                                <td><?php echo esc_html($export['type']); ?></td>
                                <td><?php echo esc_html(strtoupper($export['format'])); ?></td>
                                <td><?php echo esc_html(size_format($export['size'])); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo esc_attr($export['status']); ?>">
                                        <?php echo esc_html($export['status']); ?>
                                    </span>
                                </td>
                                <td><?php 
                                    $exporter = isset($stakeholders[$export['exported_by']]) ? $stakeholders[$export['exported_by']]['name'] : '';
                                    echo esc_html($exporter);
                                ?></td>
                                <td>
                                    <?php if ($export['status'] === 'completed') : ?>
                                        <a href="<?php echo esc_url($export['download_url']); ?>" class="button button-small">
                                            <?php _e('Download', 'piper-privacy'); ?>
                                        </a>
                                    <?php elseif ($export['status'] === 'processing') : ?>
                                        <span class="processing-status">
                                            <?php _e('Processing...', 'piper-privacy'); ?>
                                        </span>
                                    <?php else : ?>
                                        <span class="error-status">
                                            <?php _e('Failed', 'piper-privacy'); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p><?php _e('No export history found.', 'piper-privacy'); ?></p>
            <?php endif; ?>
        </div>

        <!-- Scheduled Exports -->
        <div class="scheduled-exports">
            <h2><?php _e('Scheduled Exports', 'piper-privacy'); ?></h2>
            
            <?php
            $scheduled = get_option('piper_privacy_scheduled_exports', array());
            if (!empty($scheduled)) :
            ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Name', 'piper-privacy'); ?></th>
                            <th><?php _e('Type', 'piper-privacy'); ?></th>
                            <th><?php _e('Frequency', 'piper-privacy'); ?></th>
                            <th><?php _e('Next Run', 'piper-privacy'); ?></th>
                            <th><?php _e('Recipients', 'piper-privacy'); ?></th>
                            <th><?php _e('Status', 'piper-privacy'); ?></th>
                            <th><?php _e('Actions', 'piper-privacy'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($scheduled as $id => $schedule) : ?>
                            <tr>
                                <td><?php echo esc_html($schedule['name']); ?></td>
                                <td><?php echo esc_html($schedule['type']); ?></td>
                                <td><?php echo esc_html($schedule['frequency']); ?></td>
                                <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($schedule['next_run']))); ?></td>
                                <td><?php echo esc_html(implode(', ', array_map(function($id) use ($stakeholders) {
                                    return isset($stakeholders[$id]) ? $stakeholders[$id]['name'] : '';
                                }, $schedule['recipients']))); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo esc_attr($schedule['status']); ?>">
                                        <?php echo esc_html($schedule['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="#" class="edit-schedule" data-id="<?php echo esc_attr($id); ?>">
                                        <?php _e('Edit', 'piper-privacy'); ?>
                                    </a> |
                                    <a href="#" class="delete-schedule" data-id="<?php echo esc_attr($id); ?>">
                                        <?php _e('Delete', 'piper-privacy'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p><?php _e('No scheduled exports found.', 'piper-privacy'); ?></p>
            <?php endif; ?>

            <div class="add-schedule">
                <button class="button" id="add-schedule-button">
                    <?php _e('Add Scheduled Export', 'piper-privacy'); ?>
                </button>
            </div>
        </div>
    </div>
</div>
