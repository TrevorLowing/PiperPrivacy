<?php
/**
 * Privacy Updates Management Page
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <?php settings_errors(); ?>

    <div class="privacy-updates">
        <!-- Updates Overview -->
        <div class="updates-overview">
            <h2><?php _e('Updates Overview', 'piper-privacy'); ?></h2>
            <div class="update-stats">
                <div class="stat-box">
                    <span class="stat-label"><?php _e('Pending Updates', 'piper-privacy'); ?></span>
                    <span class="stat-value"><?php echo esc_html(get_option('piper_privacy_pending_updates', 0)); ?></span>
                </div>
                <div class="stat-box">
                    <span class="stat-label"><?php _e('Completed Updates', 'piper-privacy'); ?></span>
                    <span class="stat-value"><?php echo esc_html(get_option('piper_privacy_completed_updates', 0)); ?></span>
                </div>
                <div class="stat-box">
                    <span class="stat-label"><?php _e('Success Rate', 'piper-privacy'); ?></span>
                    <span class="stat-value"><?php echo esc_html(get_option('piper_privacy_update_success_rate', '0%')); ?></span>
                </div>
            </div>
        </div>

        <!-- Record Update -->
        <div class="record-update-section">
            <h2><?php _e('Record New Update', 'piper-privacy'); ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('record_update', 'update_nonce'); ?>
                <input type="hidden" name="action" value="record_update">

                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="update_title"><?php _e('Update Title', 'piper-privacy'); ?></label></th>
                        <td><input type="text" id="update_title" name="update_title" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="update_type"><?php _e('Update Type', 'piper-privacy'); ?></label></th>
                        <td>
                            <select id="update_type" name="update_type" required>
                                <option value="collection"><?php _e('Collection Update', 'piper-privacy'); ?></option>
                                <option value="control"><?php _e('Control Update', 'piper-privacy'); ?></option>
                                <option value="process"><?php _e('Process Update', 'piper-privacy'); ?></option>
                                <option value="documentation"><?php _e('Documentation Update', 'piper-privacy'); ?></option>
                                <option value="system"><?php _e('System Update', 'piper-privacy'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="update_description"><?php _e('Description', 'piper-privacy'); ?></label></th>
                        <td><textarea id="update_description" name="update_description" class="large-text" rows="4" required></textarea></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="update_assignee"><?php _e('Assignee', 'piper-privacy'); ?></label></th>
                        <td>
                            <select id="update_assignee" name="update_assignee" required>
                                <?php
                                $stakeholders = get_option('piper_privacy_stakeholders', array());
                                foreach ($stakeholders as $id => $stakeholder) {
                                    echo '<option value="' . esc_attr($id) . '">' . esc_html($stakeholder['name']) . '</option>';
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="due_date"><?php _e('Due Date', 'piper-privacy'); ?></label></th>
                        <td><input type="date" id="due_date" name="due_date" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="update_priority"><?php _e('Priority', 'piper-privacy'); ?></label></th>
                        <td>
                            <select id="update_priority" name="update_priority" required>
                                <option value="low"><?php _e('Low', 'piper-privacy'); ?></option>
                                <option value="medium"><?php _e('Medium', 'piper-privacy'); ?></option>
                                <option value="high"><?php _e('High', 'piper-privacy'); ?></option>
                            </select>
                        </td>
                    </tr>
                </table>

                <?php submit_button(__('Record Update', 'piper-privacy')); ?>
            </form>
        </div>

        <!-- Pending Updates -->
        <div class="pending-updates-section">
            <h2><?php _e('Pending Updates', 'piper-privacy'); ?></h2>
            
            <?php
            $updates = get_option('piper_privacy_updates', array());
            $pending_updates = array_filter($updates, function($update) {
                return $update['status'] !== 'completed';
            });

            if (!empty($pending_updates)) :
            ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Title', 'piper-privacy'); ?></th>
                            <th><?php _e('Type', 'piper-privacy'); ?></th>
                            <th><?php _e('Assignee', 'piper-privacy'); ?></th>
                            <th><?php _e('Due Date', 'piper-privacy'); ?></th>
                            <th><?php _e('Priority', 'piper-privacy'); ?></th>
                            <th><?php _e('Status', 'piper-privacy'); ?></th>
                            <th><?php _e('Actions', 'piper-privacy'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_updates as $id => $update) : ?>
                            <tr>
                                <td><?php echo esc_html($update['title']); ?></td>
                                <td><?php echo esc_html($update['type']); ?></td>
                                <td><?php 
                                    $assignee = isset($stakeholders[$update['assignee']]) ? $stakeholders[$update['assignee']]['name'] : '';
                                    echo esc_html($assignee);
                                ?></td>
                                <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($update['due_date']))); ?></td>
                                <td>
                                    <span class="priority-badge priority-<?php echo esc_attr($update['priority']); ?>">
                                        <?php echo esc_html($update['priority']); ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html($update['status']); ?></td>
                                <td>
                                    <a href="#" class="view-update" data-id="<?php echo esc_attr($id); ?>">
                                        <?php _e('View', 'piper-privacy'); ?>
                                    </a> |
                                    <a href="#" class="edit-update" data-id="<?php echo esc_attr($id); ?>">
                                        <?php _e('Edit', 'piper-privacy'); ?>
                                    </a> |
                                    <a href="#" class="complete-update" data-id="<?php echo esc_attr($id); ?>">
                                        <?php _e('Complete', 'piper-privacy'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p><?php _e('No pending updates.', 'piper-privacy'); ?></p>
            <?php endif; ?>
        </div>

        <!-- Completed Updates -->
        <div class="completed-updates-section">
            <h2><?php _e('Completed Updates', 'piper-privacy'); ?></h2>
            
            <?php
            $completed_updates = array_filter($updates, function($update) {
                return $update['status'] === 'completed';
            });

            if (!empty($completed_updates)) :
            ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Title', 'piper-privacy'); ?></th>
                            <th><?php _e('Type', 'piper-privacy'); ?></th>
                            <th><?php _e('Completion Date', 'piper-privacy'); ?></th>
                            <th><?php _e('Completed By', 'piper-privacy'); ?></th>
                            <th><?php _e('Actions', 'piper-privacy'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($completed_updates as $id => $update) : ?>
                            <tr>
                                <td><?php echo esc_html($update['title']); ?></td>
                                <td><?php echo esc_html($update['type']); ?></td>
                                <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($update['completion_date']))); ?></td>
                                <td><?php 
                                    $completed_by = isset($stakeholders[$update['completed_by']]) ? $stakeholders[$update['completed_by']]['name'] : '';
                                    echo esc_html($completed_by);
                                ?></td>
                                <td>
                                    <a href="#" class="view-update" data-id="<?php echo esc_attr($id); ?>">
                                        <?php _e('View Details', 'piper-privacy'); ?>
                                    </a> |
                                    <a href="#" class="export-update" data-id="<?php echo esc_attr($id); ?>">
                                        <?php _e('Export Report', 'piper-privacy'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p><?php _e('No completed updates.', 'piper-privacy'); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>
