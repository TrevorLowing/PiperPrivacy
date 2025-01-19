<?php
/**
 * Stakeholder Comment Added Email Template
 *
 * Available variables:
 * - stakeholder_name: Name of the stakeholder
 * - collection_name: Name of the privacy collection
 * - comment_author: Name of the person who added the comment
 * - comment_content: Content of the comment
 * - collection_url: URL to view the collection
 */
?>

<p><?php _e('Hello', 'piper-privacy'); ?> <?php echo esc_html($stakeholder_name); ?>,</p>

<p>
    <?php printf(
        __('A new comment has been added to the privacy collection "%s" by %s:', 'piper-privacy'),
        esc_html($collection_name),
        '<strong>' . esc_html($comment_author) . '</strong>'
    ); ?>
</p>

<blockquote>
    <?php echo esc_html($comment_content); ?>
</blockquote>

<p>
    <?php _e('Click the link below to view the full comment and respond:', 'piper-privacy'); ?>
</p>

<p>
    <a href="<?php echo esc_url($collection_url); ?>" class="button">
        <?php _e('View Comment', 'piper-privacy'); ?>
    </a>
</p>

<p>
    <?php _e('Best regards,', 'piper-privacy'); ?><br>
    <?php echo esc_html(get_bloginfo('name')); ?>
</p>
