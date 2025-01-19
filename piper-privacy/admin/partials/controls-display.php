<?php
/**
 * Privacy Controls Management Page
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <?php settings_errors(); ?>

    <div class="privacy-controls">
        <!-- Controls Overview -->
        <div class="controls-overview">
            <h2><?php _e('Privacy Controls Overview', 'piper-privacy'); ?></h2>
            <div class="controls-stats">
                <div class="stat-box">
                    <span class="stat-label"><?php _e('Active Controls', 'piper-privacy'); ?></span>
                    <span class="stat-value"><?php echo esc_html(get_option('piper_privacy_active_controls', 0)); ?></span>
                </div>
                <div class="stat-box">
                    <span class="stat-label"><?php _e('Controls Due for Review', 'piper-privacy'); ?></span>
                    <span class="stat-value"><?php echo esc_html(get_option('piper_privacy_controls_due_review', 0)); ?></span>
                </div>
                <div class="stat-box">
                    <span class="stat-label"><?php _e('Control Effectiveness', 'piper-privacy'); ?></span>
                    <span class="stat-value"><?php echo esc_html(get_option('piper_privacy_control_effectiveness', '0%')); ?></span>
                </div>
            </div>
        </div>

        <!-- Add New Control -->
        <div class="add-control-section">
            <h2><?php _e('Add New Control', 'piper-privacy'); ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('add_privacy_control', 'control_nonce'); ?>
                <input type="hidden" name="action" value="add_privacy_control">

                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="control_name"><?php _e('Control Name', 'piper-privacy'); ?></label></th>
                        <td><input type="text" id="control_name" name="control_name" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="control_type"><?php _e('Control Type', 'piper-privacy'); ?></label></th>
                        <td>
                            <select id="control_type" name="control_type" required>
                                <option value="technical"><?php _e('Technical', 'piper-privacy'); ?></option>
                                <option value="administrative"><?php _e('Administrative', 'piper-privacy'); ?></option>
                                <option value="physical"><?php _e('Physical', 'piper-privacy'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="control_description"><?php _e('Description', 'piper-privacy'); ?></label></th>
                        <td><textarea id="control_description" name="control_description" class="large-text" rows="4" required></textarea></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="control_owner"><?php _e('Control Owner', 'piper-privacy'); ?></label></th>
                        <td>
                            <select id="control_owner" name="control_owner" required>
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
                        <th scope="row"><label for="review_frequency"><?php _e('Review Frequency (days)', 'piper-privacy'); ?></label></th>
                        <td><input type="number" id="review_frequency" name="review_frequency" class="small-text" value="90" min="1" required></td>
                    </tr>
                </table>

                <?php submit_button(__('Add Control', 'piper-privacy')); ?>
            </form>
        </div>

        <!-- Control List -->
        <div class="controls-list-section">
            <h2><?php _e('Implemented Controls', 'piper-privacy'); ?></h2>
            
            <?php
            $controls = get_option('piper_privacy_controls', array());
            if (!empty($controls)) :
            ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Control Name', 'piper-privacy'); ?></th>
                            <th><?php _e('Type', 'piper-privacy'); ?></th>
                            <th><?php _e('Owner', 'piper-privacy'); ?></th>
                            <th><?php _e('Last Review', 'piper-privacy'); ?></th>
                            <th><?php _e('Status', 'piper-privacy'); ?></th>
                            <th><?php _e('Actions', 'piper-privacy'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($controls as $id => $control) : ?>
                            <tr>
                                <td><?php echo esc_html($control['name']); ?></td>
                                <td><?php echo esc_html($control['type']); ?></td>
                                <td><?php 
                                    $owner = isset($stakeholders[$control['owner']]) ? $stakeholders[$control['owner']]['name'] : '';
                                    echo esc_html($owner);
                                ?></td>
                                <td><?php 
                                    $last_review = isset($control['last_review']) ? human_time_diff(strtotime($control['last_review']), current_time('timestamp')) . ' ago' : 'Never';
                                    echo esc_html($last_review);
                                ?></td>
                                <td><?php echo esc_html($control['status']); ?></td>
                                <td>
                                    <a href="#" class="edit-control" data-id="<?php echo esc_attr($id); ?>">
                                        <?php _e('Edit', 'piper-privacy'); ?>
                                    </a> |
                                    <a href="#" class="review-control" data-id="<?php echo esc_attr($id); ?>">
                                        <?php _e('Review', 'piper-privacy'); ?>
                                    </a> |
                                    <a href="#" class="delete-control" data-id="<?php echo esc_attr($id); ?>">
                                        <?php _e('Delete', 'piper-privacy'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p><?php _e('No controls found.', 'piper-privacy'); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>
