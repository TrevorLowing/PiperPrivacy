<?php
/**
 * Stakeholder Document Update Email Template
 *
 * Available variables:
 * - stakeholder_name: Name of the stakeholder
 * - collection_name: Name of the privacy collection
 * - document_type: Type of document that was updated
 * - collection_url: URL to view the collection
 */
?>

<p><?php _e('Hello', 'piper-privacy'); ?> <?php echo esc_html($stakeholder_name); ?>,</p>

<p>
    <?php printf(
        __('A new %s has been generated for the privacy collection "%s".', 'piper-privacy'),
        '<strong>' . esc_html($document_type) . '</strong>',
        esc_html($collection_name)
    ); ?>
</p>

<p>
    <?php _e('Please review the document and provide any necessary feedback using the link below:', 'piper-privacy'); ?>
</p>

<p>
    <a href="<?php echo esc_url($collection_url); ?>" class="button">
        <?php _e('View Document', 'piper-privacy'); ?>
    </a>
</p>

<p>
    <?php _e('Best regards,', 'piper-privacy'); ?><br>
    <?php echo esc_html(get_bloginfo('name')); ?>
</p>
