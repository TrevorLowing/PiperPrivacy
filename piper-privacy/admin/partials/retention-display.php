<?php
/**
 * Privacy Retention Management Page
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <?php settings_errors(); ?>

    <div class="privacy-retention">
        <!-- Retention Overview -->
        <div class="retention-overview">
            <h2><?php _e('Retention Overview', 'piper-privacy'); ?></h2>
            <div class="retention-stats">
                <div class="stat-box">
                    <span class="stat-label"><?php _e('Active Policies', 'piper-privacy'); ?></span>
                    <span class="stat-value"><?php echo esc_html(get_option('piper_privacy_active_retention_policies', 0)); ?></span>
                </div>
                <div class="stat-box">
                    <span class="stat-label"><?php _e('Due for Review', 'piper-privacy'); ?></span>
                    <span class="stat-value"><?php echo esc_html(get_option('piper_privacy_retention_due_review', 0)); ?></span>
                </div>
                <div class="stat-box">
                    <span class="stat-label"><?php _e('Compliance Rate', 'piper-privacy'); ?></span>
                    <span class="stat-value"><?php echo esc_html(get_option('piper_privacy_retention_compliance', '0%')); ?></span>
                </div>
            </div>
        </div>

        <!-- Add Retention Policy -->
        <div class="add-retention-section">
            <h2><?php _e('Add Retention Policy', 'piper-privacy'); ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('add_retention_policy', 'retention_nonce'); ?>
                <input type="hidden" name="action" value="add_retention_policy">

                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="policy_name"><?php _e('Policy Name', 'piper-privacy'); ?></label></th>
                        <td><input type="text" id="policy_name" name="policy_name" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="data_type"><?php _e('Data Type', 'piper-privacy'); ?></label></th>
                        <td>
                            <select id="data_type" name="data_type" required>
                                <option value="personal"><?php _e('Personal Data', 'piper-privacy'); ?></option>
                                <option value="sensitive"><?php _e('Sensitive Data', 'piper-privacy'); ?></option>
                                <option value="financial"><?php _e('Financial Data', 'piper-privacy'); ?></option>
                                <option value="health"><?php _e('Health Data', 'piper-privacy'); ?></option>
                                <option value="employment"><?php _e('Employment Data', 'piper-privacy'); ?></option>
                                <option value="communication"><?php _e('Communication Data', 'piper-privacy'); ?></option>
                                <option value="technical"><?php _e('Technical Data', 'piper-privacy'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="retention_period"><?php _e('Retention Period', 'piper-privacy'); ?></label></th>
                        <td>
                            <input type="number" id="retention_period" name="retention_period" class="small-text" min="1" required>
                            <select id="retention_unit" name="retention_unit" required>
                                <option value="days"><?php _e('Days', 'piper-privacy'); ?></option>
                                <option value="months"><?php _e('Months', 'piper-privacy'); ?></option>
                                <option value="years"><?php _e('Years', 'piper-privacy'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="legal_basis"><?php _e('Legal Basis', 'piper-privacy'); ?></label></th>
                        <td>
                            <select id="legal_basis" name="legal_basis" required>
                                <option value="legal_obligation"><?php _e('Legal Obligation', 'piper-privacy'); ?></option>
                                <option value="contractual"><?php _e('Contractual Necessity', 'piper-privacy'); ?></option>
                                <option value="legitimate_interests"><?php _e('Legitimate Interests', 'piper-privacy'); ?></option>
                                <option value="consent"><?php _e('Consent', 'piper-privacy'); ?></option>
                                <option value="vital_interests"><?php _e('Vital Interests', 'piper-privacy'); ?></option>
                                <option value="public_interest"><?php _e('Public Interest', 'piper-privacy'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="justification"><?php _e('Justification', 'piper-privacy'); ?></label></th>
                        <td><textarea id="justification" name="justification" class="large-text" rows="4" required></textarea></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="disposal_method"><?php _e('Disposal Method', 'piper-privacy'); ?></label></th>
                        <td>
                            <select id="disposal_method" name="disposal_method" required>
                                <option value="deletion"><?php _e('Permanent Deletion', 'piper-privacy'); ?></option>
                                <option value="anonymization"><?php _e('Anonymization', 'piper-privacy'); ?></option>
                                <option value="pseudonymization"><?php _e('Pseudonymization', 'piper-privacy'); ?></option>
                                <option value="archival"><?php _e('Secure Archival', 'piper-privacy'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="review_frequency"><?php _e('Review Frequency (months)', 'piper-privacy'); ?></label></th>
                        <td><input type="number" id="review_frequency" name="review_frequency" class="small-text" min="1" value="12" required></td>
                    </tr>
                </table>

                <?php submit_button(__('Add Policy', 'piper-privacy')); ?>
            </form>
        </div>

        <!-- Active Retention Policies -->
        <div class="active-policies-section">
            <h2><?php _e('Active Retention Policies', 'piper-privacy'); ?></h2>
            
            <?php
            $policies = get_option('piper_privacy_retention_policies', array());
            if (!empty($policies)) :
            ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Policy Name', 'piper-privacy'); ?></th>
                            <th><?php _e('Data Type', 'piper-privacy'); ?></th>
                            <th><?php _e('Retention Period', 'piper-privacy'); ?></th>
                            <th><?php _e('Last Review', 'piper-privacy'); ?></th>
                            <th><?php _e('Next Review', 'piper-privacy'); ?></th>
                            <th><?php _e('Status', 'piper-privacy'); ?></th>
                            <th><?php _e('Actions', 'piper-privacy'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($policies as $id => $policy) : ?>
                            <tr>
                                <td><?php echo esc_html($policy['name']); ?></td>
                                <td><?php echo esc_html($policy['data_type']); ?></td>
                                <td><?php 
                                    printf(
                                        _n('%d %s', '%d %s', $policy['retention_period'], 'piper-privacy'),
                                        $policy['retention_period'],
                                        $policy['retention_unit']
                                    ); 
                                ?></td>
                                <td><?php 
                                    $last_review = isset($policy['last_review']) ? 
                                        human_time_diff(strtotime($policy['last_review']), current_time('timestamp')) . ' ago' : 
                                        __('Never', 'piper-privacy');
                                    echo esc_html($last_review);
                                ?></td>
                                <td><?php 
                                    if (isset($policy['next_review'])) {
                                        $next_review_date = strtotime($policy['next_review']);
                                        $now = current_time('timestamp');
                                        if ($next_review_date < $now) {
                                            echo '<span class="review-overdue">' . __('Overdue', 'piper-privacy') . '</span>';
                                        } else {
                                            echo esc_html(human_time_diff($now, $next_review_date));
                                        }
                                    } else {
                                        echo '—';
                                    }
                                ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo esc_attr($policy['status']); ?>">
                                        <?php echo esc_html($policy['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="#" class="view-policy" data-id="<?php echo esc_attr($id); ?>">
                                        <?php _e('View', 'piper-privacy'); ?>
                                    </a> |
                                    <a href="#" class="edit-policy" data-id="<?php echo esc_attr($id); ?>">
                                        <?php _e('Edit', 'piper-privacy'); ?>
                                    </a> |
                                    <a href="#" class="review-policy" data-id="<?php echo esc_attr($id); ?>">
                                        <?php _e('Review', 'piper-privacy'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p><?php _e('No retention policies found.', 'piper-privacy'); ?></p>
            <?php endif; ?>
        </div>

        <!-- Retention Schedule -->
        <div class="retention-schedule">
            <h2><?php _e('Retention Schedule', 'piper-privacy'); ?></h2>
            
            <?php
            $schedule = get_option('piper_privacy_retention_schedule', array());
            if (!empty($schedule)) :
            ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Data Type', 'piper-privacy'); ?></th>
                            <th><?php _e('Records Count', 'piper-privacy'); ?></th>
                            <th><?php _e('Due for Disposal', 'piper-privacy'); ?></th>
                            <th><?php _e('Next Disposal Date', 'piper-privacy'); ?></th>
                            <th><?php _e('Actions', 'piper-privacy'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($schedule as $type => $info) : ?>
                            <tr>
                                <td><?php echo esc_html($type); ?></td>
                                <td><?php echo esc_html($info['records_count']); ?></td>
                                <td><?php echo esc_html($info['due_for_disposal']); ?></td>
                                <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($info['next_disposal_date']))); ?></td>
                                <td>
                                    <a href="#" class="view-records" data-type="<?php echo esc_attr($type); ?>">
                                        <?php _e('View Records', 'piper-privacy'); ?>
                                    </a> |
                                    <a href="#" class="initiate-disposal" data-type="<?php echo esc_attr($type); ?>">
                                        <?php _e('Initiate Disposal', 'piper-privacy'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p><?php _e('No retention schedule data available.', 'piper-privacy'); ?></p>
            <?php endif; ?>
        </div>

        <!-- Disposal History -->
        <div class="disposal-history">
            <h2><?php _e('Disposal History', 'piper-privacy'); ?></h2>
            
            <?php
            $disposals = get_option('piper_privacy_disposal_history', array());
            if (!empty($disposals)) :
            ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Date', 'piper-privacy'); ?></th>
                            <th><?php _e('Data Type', 'piper-privacy'); ?></th>
                            <th><?php _e('Records Disposed', 'piper-privacy'); ?></th>
                            <th><?php _e('Method', 'piper-privacy'); ?></th>
                            <th><?php _e('Executed By', 'piper-privacy'); ?></th>
                            <th><?php _e('Actions', 'piper-privacy'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($disposals as $id => $disposal) : ?>
                            <tr>
                                <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($disposal['date']))); ?></td>
                                <td><?php echo esc_html($disposal['data_type']); ?></td>
                                <td><?php echo esc_html($disposal['records_count']); ?></td>
                                <td><?php echo esc_html($disposal['method']); ?></td>
                                <td><?php 
                                    $executor = isset($stakeholders[$disposal['executed_by']]) ? $stakeholders[$disposal['executed_by']]['name'] : '';
                                    echo esc_html($executor);
                                ?></td>
                                <td>
                                    <a href="#" class="view-disposal" data-id="<?php echo esc_attr($id); ?>">
                                        <?php _e('View Details', 'piper-privacy'); ?>
                                    </a> |
                                    <a href="#" class="export-certificate" data-id="<?php echo esc_attr($id); ?>">
                                        <?php _e('Export Certificate', 'piper-privacy'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p><?php _e('No disposal history found.', 'piper-privacy'); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>
