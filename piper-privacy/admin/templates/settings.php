<div class="wrap">
    <h1><?php echo esc_html__('Privacy Management Settings', 'piper-privacy'); ?></h1>

    <?php settings_errors('piper_privacy_settings'); ?>

    <form method="post" action="">
        <?php wp_nonce_field('piper_privacy_settings', 'piper_privacy_settings_nonce'); ?>

        <!-- Notification Settings -->
        <h2 class="title"><?php echo esc_html__('Notification Settings', 'piper-privacy'); ?></h2>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="email_notifications">
                        <?php echo esc_html__('Email Notifications', 'piper-privacy'); ?>
                    </label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" 
                               name="email_notifications" 
                               id="email_notifications"
                               value="1"
                               <?php checked(1, $settings['email_notifications'] ?? 0); ?>>
                        <?php echo esc_html__('Enable email notifications for workflow changes', 'piper-privacy'); ?>
                    </label>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="notification_recipients">
                        <?php echo esc_html__('Notification Recipients', 'piper-privacy'); ?>
                    </label>
                </th>
                <td>
                    <textarea name="notification_recipients" 
                              id="notification_recipients" 
                              class="large-text code" 
                              rows="3"
                              placeholder="<?php echo esc_attr__('Enter email addresses, one per line', 'piper-privacy'); ?>"><?php echo esc_textarea($settings['notification_recipients'] ?? ''); ?></textarea>
                    <p class="description">
                        <?php echo esc_html__('Enter additional email addresses to receive notifications (one per line)', 'piper-privacy'); ?>
                    </p>
                </td>
            </tr>
        </table>

        <!-- Collection Settings -->
        <h2 class="title"><?php echo esc_html__('Collection Settings', 'piper-privacy'); ?></h2>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="auto_expire_collections">
                        <?php echo esc_html__('Auto-Expire Collections', 'piper-privacy'); ?>
                    </label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" 
                               name="auto_expire_collections" 
                               id="auto_expire_collections"
                               value="1"
                               <?php checked(1, $settings['auto_expire_collections'] ?? 0); ?>>
                        <?php echo esc_html__('Automatically mark collections as expired after retention period', 'piper-privacy'); ?>
                    </label>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="expiration_warning_days">
                        <?php echo esc_html__('Expiration Warning', 'piper-privacy'); ?>
                    </label>
                </th>
                <td>
                    <input type="number" 
                           name="expiration_warning_days" 
                           id="expiration_warning_days"
                           value="<?php echo esc_attr($settings['expiration_warning_days'] ?? 30); ?>"
                           class="small-text"
                           min="1"
                           max="365">
                    <p class="description">
                        <?php echo esc_html__('Days before expiration to send warning notification', 'piper-privacy'); ?>
                    </p>
                </td>
            </tr>
        </table>

        <!-- Audit Settings -->
        <h2 class="title"><?php echo esc_html__('Audit Settings', 'piper-privacy'); ?></h2>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="enable_audit_logging">
                        <?php echo esc_html__('Audit Logging', 'piper-privacy'); ?>
                    </label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" 
                               name="enable_audit_logging" 
                               id="enable_audit_logging"
                               value="1"
                               <?php checked(1, $settings['enable_audit_logging'] ?? 1); ?>>
                        <?php echo esc_html__('Enable audit logging for all privacy management activities', 'piper-privacy'); ?>
                    </label>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="audit_retention_days">
                        <?php echo esc_html__('Audit Log Retention', 'piper-privacy'); ?>
                    </label>
                </th>
                <td>
                    <input type="number" 
                           name="audit_retention_days" 
                           id="audit_retention_days"
                           value="<?php echo esc_attr($settings['audit_retention_days'] ?? 365); ?>"
                           class="small-text"
                           min="30"
                           max="3650">
                    <p class="description">
                        <?php echo esc_html__('Number of days to retain audit log entries (minimum 30 days)', 'piper-privacy'); ?>
                    </p>
                </td>
            </tr>
        </table>

        <?php submit_button(); ?>
    </form>
</div>
