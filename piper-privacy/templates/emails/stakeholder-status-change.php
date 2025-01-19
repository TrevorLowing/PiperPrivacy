<?php
/**
 * Stakeholder Status Change Email Template
 *
 * Available variables:
 * - stakeholder_name: Name of the stakeholder
 * - collection_name: Name of the privacy collection
 * - old_status: Previous status
 * - new_status: New status
 * - collection_url: URL to view the collection
 */
?>

<p><?php _e('Hello', 'piper-privacy'); ?> <?php echo esc_html($stakeholder_name); ?>,</p>

<p>
    <?php printf(
        __('The privacy collection "%s" has been updated from %s to %s.', 'piper-privacy'),
        esc_html($collection_name),
        '<strong>' . esc_html($old_status) . '</strong>',
        '<strong>' . esc_html($new_status) . '</strong>'
    ); ?>
</p>

<p>
    <?php _e('You can review the changes and provide feedback by clicking the link below:', 'piper-privacy'); ?>
</p>

<p>
    <a href="<?php echo esc_url($collection_url); ?>" class="button">
        <?php _e('View Privacy Collection', 'piper-privacy'); ?>
    </a>
</p>

<p>
    <?php _e('Best regards,', 'piper-privacy'); ?><br>
    <?php echo esc_html(get_bloginfo('name')); ?>
</p>
