<?php
/**
 * Privacy Incidents Management Page
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <?php settings_errors(); ?>

    <div class="privacy-incidents">
        <!-- Incidents Overview -->
        <div class="incidents-overview">
            <h2><?php _e('Incidents Overview', 'piper-privacy'); ?></h2>
            <div class="incident-stats">
                <div class="stat-box">
                    <span class="stat-label"><?php _e('Open Incidents', 'piper-privacy'); ?></span>
                    <span class="stat-value"><?php echo esc_html(get_option('piper_privacy_open_incidents', 0)); ?></span>
                </div>
                <div class="stat-box">
                    <span class="stat-label"><?php _e('High Priority', 'piper-privacy'); ?></span>
                    <span class="stat-value"><?php echo esc_html(get_option('piper_privacy_high_priority_incidents', 0)); ?></span>
                </div>
                <div class="stat-box">
                    <span class="stat-label"><?php _e('Resolved This Month', 'piper-privacy'); ?></span>
                    <span class="stat-value"><?php echo esc_html(get_option('piper_privacy_resolved_incidents_month', 0)); ?></span>
                </div>
            </div>
        </div>

        <!-- Report Incident -->
        <div class="report-incident-section">
            <h2><?php _e('Report New Incident', 'piper-privacy'); ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('report_incident', 'incident_nonce'); ?>
                <input type="hidden" name="action" value="report_incident">

                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="incident_title"><?php _e('Incident Title', 'piper-privacy'); ?></label></th>
                        <td><input type="text" id="incident_title" name="incident_title" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="incident_type"><?php _e('Incident Type', 'piper-privacy'); ?></label></th>
                        <td>
                            <select id="incident_type" name="incident_type" required>
                                <option value="data_breach"><?php _e('Data Breach', 'piper-privacy'); ?></option>
                                <option value="unauthorized_access"><?php _e('Unauthorized Access', 'piper-privacy'); ?></option>
                                <option value="data_loss"><?php _e('Data Loss', 'piper-privacy'); ?></option>
                                <option value="policy_violation"><?php _e('Policy Violation', 'piper-privacy'); ?></option>
                                <option value="system_malfunction"><?php _e('System Malfunction', 'piper-privacy'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="incident_priority"><?php _e('Priority', 'piper-privacy'); ?></label></th>
                        <td>
                            <select id="incident_priority" name="incident_priority" required>
                                <option value="low"><?php _e('Low', 'piper-privacy'); ?></option>
                                <option value="medium"><?php _e('Medium', 'piper-privacy'); ?></option>
                                <option value="high"><?php _e('High', 'piper-privacy'); ?></option>
                                <option value="critical"><?php _e('Critical', 'piper-privacy'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="incident_description"><?php _e('Description', 'piper-privacy'); ?></label></th>
                        <td><textarea id="incident_description" name="incident_description" class="large-text" rows="4" required></textarea></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="incident_date"><?php _e('Date of Incident', 'piper-privacy'); ?></label></th>
                        <td><input type="datetime-local" id="incident_date" name="incident_date" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="affected_data"><?php _e('Affected Data', 'piper-privacy'); ?></label></th>
                        <td><textarea id="affected_data" name="affected_data" class="large-text" rows="3" required></textarea></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="incident_assignee"><?php _e('Assignee', 'piper-privacy'); ?></label></th>
                        <td>
                            <select id="incident_assignee" name="incident_assignee" required>
                                <?php
                                $stakeholders = get_option('piper_privacy_stakeholders', array());
                                foreach ($stakeholders as $id => $stakeholder) {
                                    echo '<option value="' . esc_attr($id) . '">' . esc_html($stakeholder['name']) . '</option>';
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                </table>

                <?php submit_button(__('Report Incident', 'piper-privacy')); ?>
            </form>
        </div>

        <!-- Active Incidents -->
        <div class="active-incidents-section">
            <h2><?php _e('Active Incidents', 'piper-privacy'); ?></h2>
            
            <?php
            $incidents = get_option('piper_privacy_incidents', array());
            $active_incidents = array_filter($incidents, function($incident) {
                return $incident['status'] !== 'resolved';
            });

            if (!empty($active_incidents)) :
            ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Title', 'piper-privacy'); ?></th>
                            <th><?php _e('Type', 'piper-privacy'); ?></th>
                            <th><?php _e('Priority', 'piper-privacy'); ?></th>
                            <th><?php _e('Date', 'piper-privacy'); ?></th>
                            <th><?php _e('Assignee', 'piper-privacy'); ?></th>
                            <th><?php _e('Status', 'piper-privacy'); ?></th>
                            <th><?php _e('Actions', 'piper-privacy'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($active_incidents as $id => $incident) : ?>
                            <tr>
                                <td><?php echo esc_html($incident['title']); ?></td>
                                <td><?php echo esc_html($incident['type']); ?></td>
                                <td>
                                    <span class="priority-badge priority-<?php echo esc_attr($incident['priority']); ?>">
                                        <?php echo esc_html($incident['priority']); ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($incident['date']))); ?></td>
                                <td><?php 
                                    $assignee = isset($stakeholders[$incident['assignee']]) ? $stakeholders[$incident['assignee']]['name'] : '';
                                    echo esc_html($assignee);
                                ?></td>
                                <td><?php echo esc_html($incident['status']); ?></td>
                                <td>
                                    <a href="#" class="view-incident" data-id="<?php echo esc_attr($id); ?>">
                                        <?php _e('View', 'piper-privacy'); ?>
                                    </a> |
                                    <a href="#" class="update-incident" data-id="<?php echo esc_attr($id); ?>">
                                        <?php _e('Update', 'piper-privacy'); ?>
                                    </a> |
                                    <a href="#" class="resolve-incident" data-id="<?php echo esc_attr($id); ?>">
                                        <?php _e('Resolve', 'piper-privacy'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p><?php _e('No active incidents.', 'piper-privacy'); ?></p>
            <?php endif; ?>
        </div>

        <!-- Resolved Incidents -->
        <div class="resolved-incidents-section">
            <h2><?php _e('Resolved Incidents', 'piper-privacy'); ?></h2>
            
            <?php
            $resolved_incidents = array_filter($incidents, function($incident) {
                return $incident['status'] === 'resolved';
            });

            if (!empty($resolved_incidents)) :
            ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Title', 'piper-privacy'); ?></th>
                            <th><?php _e('Type', 'piper-privacy'); ?></th>
                            <th><?php _e('Resolution Date', 'piper-privacy'); ?></th>
                            <th><?php _e('Resolution Summary', 'piper-privacy'); ?></th>
                            <th><?php _e('Actions', 'piper-privacy'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resolved_incidents as $id => $incident) : ?>
                            <tr>
                                <td><?php echo esc_html($incident['title']); ?></td>
                                <td><?php echo esc_html($incident['type']); ?></td>
                                <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($incident['resolution_date']))); ?></td>
                                <td><?php echo esc_html($incident['resolution_summary']); ?></td>
                                <td>
                                    <a href="#" class="view-incident" data-id="<?php echo esc_attr($id); ?>">
                                        <?php _e('View Details', 'piper-privacy'); ?>
                                    </a> |
                                    <a href="#" class="export-incident" data-id="<?php echo esc_attr($id); ?>">
                                        <?php _e('Export Report', 'piper-privacy'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p><?php _e('No resolved incidents.', 'piper-privacy'); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>
