<?php
/**
 * Document Generation view template
 *
 * @var array $documents Generated privacy documents
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

// Get current document type filter
$current_type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : 'all';
?>
<div class="wrap piper-privacy-documents">
    <h1 class="wp-heading-inline">
        <?php esc_html_e('Privacy Documents', 'piper-privacy'); ?>
    </h1>

    <!-- Document Generation Button -->
    <a href="#" class="page-title-action generate-document">
        <?php esc_html_e('Generate Document', 'piper-privacy'); ?>
    </a>

    <!-- Document Type Filter -->
    <div class="document-filters">
        <ul class="subsubsub">
            <li>
                <a href="<?php echo esc_url(add_query_arg('type', 'all')); ?>" 
                   class="<?php echo $current_type === 'all' ? 'current' : ''; ?>">
                    <?php esc_html_e('All Documents', 'piper-privacy'); ?>
                </a> |
            </li>
            <li>
                <a href="<?php echo esc_url(add_query_arg('type', 'pta')); ?>"
                   class="<?php echo $current_type === 'pta' ? 'current' : ''; ?>">
                    <?php esc_html_e('PTAs', 'piper-privacy'); ?>
                </a> |
            </li>
            <li>
                <a href="<?php echo esc_url(add_query_arg('type', 'pia')); ?>"
                   class="<?php echo $current_type === 'pia' ? 'current' : ''; ?>">
                    <?php esc_html_e('PIAs', 'piper-privacy'); ?>
                </a> |
            </li>
            <li>
                <a href="<?php echo esc_url(add_query_arg('type', 'reports')); ?>"
                   class="<?php echo $current_type === 'reports' ? 'current' : ''; ?>">
                    <?php esc_html_e('Reports', 'piper-privacy'); ?>
                </a>
            </li>
        </ul>
    </div>

    <!-- Document Generation Form -->
    <div id="document-generator" class="document-generator" style="display: none;">
        <form id="generate-document-form" method="post">
            <?php wp_nonce_field('generate_privacy_document', 'document_nonce'); ?>
            
            <div class="form-field">
                <label for="document_type"><?php esc_html_e('Document Type', 'piper-privacy'); ?></label>
                <select name="document_type" id="document_type" required>
                    <option value=""><?php esc_html_e('Select Document Type', 'piper-privacy'); ?></option>
                    <option value="pta"><?php esc_html_e('Privacy Threshold Analysis', 'piper-privacy'); ?></option>
                    <option value="pia"><?php esc_html_e('Privacy Impact Assessment', 'piper-privacy'); ?></option>
                    <option value="review"><?php esc_html_e('Privacy Review Report', 'piper-privacy'); ?></option>
                    <option value="compliance"><?php esc_html_e('Compliance Report', 'piper-privacy'); ?></option>
                </select>
            </div>

            <div class="form-field collection-select" style="display: none;">
                <label for="collection_id"><?php esc_html_e('Privacy Collection', 'piper-privacy'); ?></label>
                <select name="collection_id" id="collection_id">
                    <option value=""><?php esc_html_e('Select Privacy Collection', 'piper-privacy'); ?></option>
                    <?php
                    $collections = get_posts([
                        'post_type' => 'privacy-collection',
                        'posts_per_page' => -1,
                        'orderby' => 'title',
                        'order' => 'ASC'
                    ]);
                    foreach ($collections as $collection) {
                        printf(
                            '<option value="%d">%s</option>',
                            esc_attr($collection->ID),
                            esc_html($collection->post_title)
                        );
                    }
                    ?>
                </select>
            </div>

            <div class="form-field date-range" style="display: none;">
                <label for="date_start"><?php esc_html_e('Date Range', 'piper-privacy'); ?></label>
                <input type="date" name="date_start" id="date_start">
                <span><?php esc_html_e('to', 'piper-privacy'); ?></span>
                <input type="date" name="date_end" id="date_end">
            </div>

            <div class="form-field format-select">
                <label for="document_format"><?php esc_html_e('Format', 'piper-privacy'); ?></label>
                <select name="document_format" id="document_format" required>
                    <option value="pdf"><?php esc_html_e('PDF', 'piper-privacy'); ?></option>
                    <option value="docx"><?php esc_html_e('Word Document', 'piper-privacy'); ?></option>
                    <option value="html"><?php esc_html_e('HTML', 'piper-privacy'); ?></option>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="button button-primary">
                    <?php esc_html_e('Generate', 'piper-privacy'); ?>
                </button>
                <button type="button" class="button cancel-generation">
                    <?php esc_html_e('Cancel', 'piper-privacy'); ?>
                </button>
            </div>
        </form>
    </div>

    <!-- Document List -->
    <?php if ($documents): ?>
        <div class="document-list">
            <?php foreach ($documents as $document):
                $type = get_post_meta($document->ID, 'document_type', true);
                $format = get_post_meta($document->ID, 'document_format', true);
                $collection_id = get_post_meta($document->ID, 'collection_reference', true);
                $generated_by = get_userdata($document->post_author);
                ?>
                <div class="document-item type-<?php echo esc_attr($type); ?>">
                    <div class="document-icon">
                        <span class="dashicons <?php echo esc_attr($this->get_document_icon($type, $format)); ?>"></span>
                    </div>

                    <div class="document-details">
                        <h3><?php echo esc_html($document->post_title); ?></h3>
                        
                        <?php if ($collection_id): ?>
                            <div class="collection-reference">
                                <?php esc_html_e('Collection:', 'piper-privacy'); ?>
                                <a href="<?php echo esc_url(get_edit_post_link($collection_id)); ?>">
                                    <?php echo esc_html(get_the_title($collection_id)); ?>
                                </a>
                            </div>
                        <?php endif; ?>

                        <div class="document-meta">
                            <span class="generated-by">
                                <?php 
                                printf(
                                    /* translators: %s: user's display name */
                                    esc_html__('Generated by %s', 'piper-privacy'),
                                    esc_html($generated_by->display_name)
                                );
                                ?>
                            </span>
                            <span class="generated-date">
                                <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($document->post_date))); ?>
                            </span>
                            <span class="document-format">
                                <?php echo esc_html(strtoupper($format)); ?>
                            </span>
                        </div>
                    </div>

                    <div class="document-actions">
                        <a href="<?php echo esc_url(wp_get_attachment_url(get_post_meta($document->ID, '_document_file', true))); ?>" 
                           class="button" 
                           download>
                            <?php esc_html_e('Download', 'piper-privacy'); ?>
                        </a>
                        <?php if (current_user_can('edit_post', $document->ID)): ?>
                            <button type="button" 
                                    class="button regenerate-document" 
                                    data-id="<?php echo esc_attr($document->ID); ?>">
                                <?php esc_html_e('Regenerate', 'piper-privacy'); ?>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php
        // Pagination
        $pagination = paginate_links([
            'base' => add_query_arg('paged', '%#%'),
            'format' => '',
            'prev_text' => __('&laquo;'),
            'next_text' => __('&raquo;'),
            'total' => ceil(wp_count_posts('privacy-document')->publish / 20),
            'current' => max(1, get_query_var('paged'))
        ]);

        if ($pagination) {
            echo '<div class="tablenav"><div class="tablenav-pages">' . $pagination . '</div></div>';
        }
        ?>

    <?php else: ?>
        <p class="no-documents">
            <?php esc_html_e('No documents found.', 'piper-privacy'); ?>
        </p>
    <?php endif; ?>

    <!-- Regeneration Modal -->
    <div id="regeneration-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <h2><?php esc_html_e('Regenerate Document', 'piper-privacy'); ?></h2>
            <form id="regenerate-document-form" method="post">
                <?php wp_nonce_field('regenerate_privacy_document', 'regenerate_nonce'); ?>
                <input type="hidden" name="document_id" id="regenerate_document_id" value="">
                
                <div class="form-field">
                    <label for="regenerate_format"><?php esc_html_e('Format', 'piper-privacy'); ?></label>
                    <select name="regenerate_format" id="regenerate_format" required>
                        <option value="pdf"><?php esc_html_e('PDF', 'piper-privacy'); ?></option>
                        <option value="docx"><?php esc_html_e('Word Document', 'piper-privacy'); ?></option>
                        <option value="html"><?php esc_html_e('HTML', 'piper-privacy'); ?></option>
                    </select>
                </div>

                <div class="form-field">
                    <label for="regenerate_notes"><?php esc_html_e('Notes', 'piper-privacy'); ?></label>
                    <textarea name="regenerate_notes" id="regenerate_notes" rows="3"></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="button button-primary">
                        <?php esc_html_e('Regenerate', 'piper-privacy'); ?>
                    </button>
                    <button type="button" class="button cancel-modal">
                        <?php esc_html_e('Cancel', 'piper-privacy'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>