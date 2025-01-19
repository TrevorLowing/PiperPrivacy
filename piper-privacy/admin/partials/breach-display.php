<?php
/**
 * Privacy Breach Management Page
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <?php settings_errors(); ?>

    <div class="privacy-breach">
        <!-- Breach Overview -->
        <div class="breach-overview">
            <h2><?php _e('Breach Overview', 'piper-privacy'); ?></h2>
            <div class="breach-stats">
                <div class="stat-box">
                    <span class="stat-label"><?php _e('Active Breaches', 'piper-privacy'); ?></span>
                    <span class="stat-value"><?php echo esc_html(get_option('piper_privacy_active_breaches', 0)); ?></span>
                </div>
                <div class="stat-box">
                    <span class="stat-label"><?php _e('Resolved Breaches', 'piper-privacy'); ?></span>
                    <span class="stat-value"><?php echo esc_html(get_option('piper_privacy_resolved_breaches', 0)); ?></span>
                </div>
                <div class="stat-box">
                    <span class="stat-label"><?php _e('Average Resolution Time', 'piper-privacy'); ?></span>
                    <span class="stat-value"><?php echo esc_html(get_option('piper_privacy_avg_breach_resolution_time', '0 hours')); ?></span>
                </div>
            </div>
        </div>

        <!-- Report Breach -->
        <div class="report-breach-section">
            <h2><?php _e('Report New Breach', 'piper-privacy'); ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('report_breach', 'breach_nonce'); ?>
                <input type="hidden" name="action" value="report_breach">

                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="breach_title"><?php _e('Breach Title', 'piper-privacy'); ?></label></th>
                        <td><input type="text" id="breach_title" name="breach_title" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="breach_type"><?php _e('Breach Type', 'piper-privacy'); ?></label></th>
                        <td>
                            <select id="breach_type" name="breach_type" required>
                                <option value="unauthorized_access"><?php _e('Unauthorized Access', 'piper-privacy'); ?></option>
                                <option value="data_disclosure"><?php _e('Data Disclosure', 'piper-privacy'); ?></option>
                                <option value="data_loss"><?php _e('Data Loss', 'piper-privacy'); ?></option>
                                <option value="system_compromise"><?php _e('System Compromise', 'piper-privacy'); ?></option>
                                <option value="physical_breach"><?php _e('Physical Breach', 'piper-privacy'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="breach_severity"><?php _e('Severity', 'piper-privacy'); ?></label></th>
                        <td>
                            <select id="breach_severity" name="breach_severity" required>
                                <option value="low"><?php _e('Low', 'piper-privacy'); ?></option>
                                <option value="medium"><?php _e('Medium', 'piper-privacy'); ?></option>
                                <option value="high"><?php _e('High', 'piper-privacy'); ?></option>
                                <option value="critical"><?php _e('Critical', 'piper-privacy'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="breach_description"><?php _e('Description', 'piper-privacy'); ?></label></th>
                        <td><textarea id="breach_description" name="breach_description" class="large-text" rows="4" required></textarea></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="breach_date"><?php _e('Date of Breach', 'piper-privacy'); ?></label></th>
                        <td><input type="datetime-local" id="breach_date" name="breach_date" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="discovery_date"><?php _e('Date of Discovery', 'piper-privacy'); ?></label></th>
                        <td><input type="datetime-local" id="discovery_date" name="discovery_date" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="affected_data"><?php _e('Affected Data', 'piper-privacy'); ?></label></th>
                        <td>
                            <select id="affected_data" name="affected_data[]" multiple required>
                                <option value="personal"><?php _e('Personal Information', 'piper-privacy'); ?></option>
                                <option value="sensitive"><?php _e('Sensitive Information', 'piper-privacy'); ?></option>
                                <option value="financial"><?php _e('Financial Information', 'piper-privacy'); ?></option>
                                <option value="health"><?php _e('Health Information', 'piper-privacy'); ?></option>
                                <option value="credentials"><?php _e('Login Credentials', 'piper-privacy'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="affected_individuals"><?php _e('Number of Affected Individuals', 'piper-privacy'); ?></label></th>
                        <td><input type="number" id="affected_individuals" name="affected_individuals" class="small-text" min="0" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="breach_source"><?php _e('Source of Breach', 'piper-privacy'); ?></label></th>
                        <td>
                            <select id="breach_source" name="breach_source" required>
                                <option value="internal"><?php _e('Internal', 'piper-privacy'); ?></option>
                                <option value="external"><?php _e('External', 'piper-privacy'); ?></option>
                                <option value="third_party"><?php _e('Third Party', 'piper-privacy'); ?></option>
                                <option value="unknown"><?php _e('Unknown', 'piper-privacy'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="breach_status"><?php _e('Current Status', 'piper-privacy'); ?></label></th>
                        <td>
                            <select id="breach_status" name="breach_status" required>
                                <option value="investigating"><?php _e('Under Investigation', 'piper-privacy'); ?></option>
                                <option value="contained"><?php _e('Contained', 'piper-privacy'); ?></option>
                                <option value="mitigating"><?php _e('Mitigation in Progress', 'piper-privacy'); ?></option>
                                <option value="resolved"><?php _e('Resolved', 'piper-privacy'); ?></option>
                            </select>
                        </td>
                    </tr>
                </table>

                <?php submit_button(__('Report Breach', 'piper-privacy')); ?>
            </form>
        </div>

        <!-- Active Breaches -->
        <div class="active-breaches-section">
            <h2><?php _e('Active Breaches', 'piper-privacy'); ?></h2>
            
            <?php
            $breaches = get_option('piper_privacy_breaches', array());
            $active_breaches = array_filter($breaches, function($breach) {
                return $breach['status'] !== 'resolved';
            });

            if (!empty($active_breaches)) :
            ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Title', 'piper-privacy'); ?></th>
                            <th><?php _e('Type', 'piper-privacy'); ?></th>
                            <th><?php _e('Severity', 'piper-privacy'); ?></th>
                            <th><?php _e('Affected Count', 'piper-privacy'); ?></th>
                            <th><?php _e('Discovery Date', 'piper-privacy'); ?></th>
                            <th><?php _e('Status', 'piper-privacy'); ?></th>
                            <th><?php _e('Actions', 'piper-privacy'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($active_breaches as $id => $breach) : ?>
                            <tr>
                                <td><?php echo esc_html($breach['title']); ?></td>
                                <td><?php echo esc_html($breach['type']); ?></td>
                                <td>
                                    <span class="severity-badge severity-<?php echo esc_attr($breach['severity']); ?>">
                                        <?php echo esc_html($breach['severity']); ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html($breach['affected_individuals']); ?></td>
                                <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($breach['discovery_date']))); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo esc_attr($breach['status']); ?>">
                                        <?php echo esc_html($breach['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="#" class="view-breach" data-id="<?php echo esc_attr($id); ?>">
                                        <?php _e('View', 'piper-privacy'); ?>
                                    </a> |
                                    <a href="#" class="update-breach" data-id="<?php echo esc_attr($id); ?>">
                                        <?php _e('Update', 'piper-privacy'); ?>
                                    </a> |
                                    <a href="#" class="resolve-breach" data-id="<?php echo esc_attr($id); ?>">
                                        <?php _e('Resolve', 'piper-privacy'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p><?php _e('No active breaches.', 'piper-privacy'); ?></p>
            <?php endif; ?>
        </div>

        <!-- Resolved Breaches -->
        <div class="resolved-breaches-section">
            <h2><?php _e('Resolved Breaches', 'piper-privacy'); ?></h2>
            
            <?php
            $resolved_breaches = array_filter($breaches, function($breach) {
                return $breach['status'] === 'resolved';
            });

            if (!empty($resolved_breaches)) :
            ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Title', 'piper-privacy'); ?></th>
                            <th><?php _e('Type', 'piper-privacy'); ?></th>
                            <th><?php _e('Resolution Date', 'piper-privacy'); ?></th>
                            <th><?php _e('Resolution Time', 'piper-privacy'); ?></th>
                            <th><?php _e('Actions Taken', 'piper-privacy'); ?></th>
                            <th><?php _e('Actions', 'piper-privacy'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resolved_breaches as $id => $breach) : ?>
                            <tr>
                                <td><?php echo esc_html($breach['title']); ?></td>
                                <td><?php echo esc_html($breach['type']); ?></td>
                                <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($breach['resolution_date']))); ?></td>
                                <td><?php 
                                    $resolution_time = strtotime($breach['resolution_date']) - strtotime($breach['discovery_date']);
                                    echo esc_html(human_time_diff(0, $resolution_time));
                                ?></td>
                                <td><?php echo esc_html($breach['actions_taken']); ?></td>
                                <td>
                                    <a href="#" class="view-breach" data-id="<?php echo esc_attr($id); ?>">
                                        <?php _e('View Details', 'piper-privacy'); ?>
                                    </a> |
                                    <a href="#" class="export-report" data-id="<?php echo esc_attr($id); ?>">
                                        <?php _e('Export Report', 'piper-privacy'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p><?php _e('No resolved breaches.', 'piper-privacy'); ?></p>
            <?php endif; ?>
        </div>

        <!-- Notification Templates -->
        <div class="notification-templates">
            <h2><?php _e('Notification Templates', 'piper-privacy'); ?></h2>
            
            <?php
            $templates = get_option('piper_privacy_notification_templates', array());
            if (!empty($templates)) :
            ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Template Name', 'piper-privacy'); ?></th>
                            <th><?php _e('Purpose', 'piper-privacy'); ?></th>
                            <th><?php _e('Last Updated', 'piper-privacy'); ?></th>
                            <th><?php _e('Actions', 'piper-privacy'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($templates as $id => $template) : ?>
                            <tr>
                                <td><?php echo esc_html($template['name']); ?></td>
                                <td><?php echo esc_html($template['purpose']); ?></td>
                                <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($template['last_updated']))); ?></td>
                                <td>
                                    <a href="#" class="edit-template" data-id="<?php echo esc_attr($id); ?>">
                                        <?php _e('Edit', 'piper-privacy'); ?>
                                    </a> |
                                    <a href="#" class="preview-template" data-id="<?php echo esc_attr($id); ?>">
                                        <?php _e('Preview', 'piper-privacy'); ?>
                                    </a> |
                                    <a href="#" class="delete-template" data-id="<?php echo esc_attr($id); ?>">
                                        <?php _e('Delete', 'piper-privacy'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p><?php _e('No notification templates found.', 'piper-privacy'); ?></p>
            <?php endif; ?>

            <div class="add-template">
                <button class="button" id="add-template-button">
                    <?php _e('Add Template', 'piper-privacy'); ?>
                </button>
            </div>
        </div>
    </div>
</div>
