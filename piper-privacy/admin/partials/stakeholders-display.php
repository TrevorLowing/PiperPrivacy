<?php
/**
 * Stakeholder Management Page
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <?php settings_errors(); ?>

    <div class="stakeholder-management">
        <!-- Add New Stakeholder Form -->
        <div class="stakeholder-form-section">
            <h2><?php _e('Add New Stakeholder', 'piper-privacy'); ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('add_stakeholder', 'stakeholder_nonce'); ?>
                <input type="hidden" name="action" value="add_stakeholder">

                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="stakeholder_name"><?php _e('Name', 'piper-privacy'); ?></label></th>
                        <td><input type="text" id="stakeholder_name" name="stakeholder_name" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="stakeholder_role"><?php _e('Role', 'piper-privacy'); ?></label></th>
                        <td><input type="text" id="stakeholder_role" name="stakeholder_role" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="stakeholder_department"><?php _e('Department', 'piper-privacy'); ?></label></th>
                        <td><input type="text" id="stakeholder_department" name="stakeholder_department" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="stakeholder_email"><?php _e('Email', 'piper-privacy'); ?></label></th>
                        <td><input type="email" id="stakeholder_email" name="stakeholder_email" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="stakeholder_responsibilities"><?php _e('Responsibilities', 'piper-privacy'); ?></label></th>
                        <td><textarea id="stakeholder_responsibilities" name="stakeholder_responsibilities" class="large-text" rows="4"></textarea></td>
                    </tr>
                </table>

                <?php submit_button(__('Add Stakeholder', 'piper-privacy')); ?>
            </form>
        </div>

        <!-- Stakeholder List -->
        <div class="stakeholder-list-section">
            <h2><?php _e('Current Stakeholders', 'piper-privacy'); ?></h2>
            
            <?php
            $stakeholders = get_option('piper_privacy_stakeholders', array());
            if (!empty($stakeholders)) :
            ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Name', 'piper-privacy'); ?></th>
                            <th><?php _e('Role', 'piper-privacy'); ?></th>
                            <th><?php _e('Department', 'piper-privacy'); ?></th>
                            <th><?php _e('Email', 'piper-privacy'); ?></th>
                            <th><?php _e('Actions', 'piper-privacy'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stakeholders as $id => $stakeholder) : ?>
                            <tr>
                                <td><?php echo esc_html($stakeholder['name']); ?></td>
                                <td><?php echo esc_html($stakeholder['role']); ?></td>
                                <td><?php echo esc_html($stakeholder['department']); ?></td>
                                <td><?php echo esc_html($stakeholder['email']); ?></td>
                                <td>
                                    <a href="#" class="edit-stakeholder" data-id="<?php echo esc_attr($id); ?>">
                                        <?php _e('Edit', 'piper-privacy'); ?>
                                    </a> |
                                    <a href="#" class="delete-stakeholder" data-id="<?php echo esc_attr($id); ?>">
                                        <?php _e('Delete', 'piper-privacy'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p><?php _e('No stakeholders found.', 'piper-privacy'); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>
