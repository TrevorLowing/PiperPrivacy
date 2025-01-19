<?php
/**
 * Privacy Documentation Management Page
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <?php settings_errors(); ?>

    <div class="privacy-documentation">
        <!-- Document Categories -->
        <div class="doc-categories">
            <div class="category-box">
                <h3><?php _e('Policies', 'piper-privacy'); ?></h3>
                <a href="#" class="add-doc" data-category="policy"><?php _e('Add New Policy', 'piper-privacy'); ?></a>
                <?php
                $policies = get_posts(array(
                    'post_type' => 'privacy_document',
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'document_type',
                            'field' => 'slug',
                            'terms' => 'policy'
                        )
                    ),
                    'posts_per_page' => -1
                ));
                if ($policies) :
                ?>
                    <ul class="doc-list">
                        <?php foreach ($policies as $policy) : ?>
                            <li>
                                <a href="<?php echo get_edit_post_link($policy->ID); ?>">
                                    <?php echo esc_html($policy->post_title); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <p><?php _e('No policies found.', 'piper-privacy'); ?></p>
                <?php endif; ?>
            </div>

            <div class="category-box">
                <h3><?php _e('Procedures', 'piper-privacy'); ?></h3>
                <a href="#" class="add-doc" data-category="procedure"><?php _e('Add New Procedure', 'piper-privacy'); ?></a>
                <?php
                $procedures = get_posts(array(
                    'post_type' => 'privacy_document',
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'document_type',
                            'field' => 'slug',
                            'terms' => 'procedure'
                        )
                    ),
                    'posts_per_page' => -1
                ));
                if ($procedures) :
                ?>
                    <ul class="doc-list">
                        <?php foreach ($procedures as $procedure) : ?>
                            <li>
                                <a href="<?php echo get_edit_post_link($procedure->ID); ?>">
                                    <?php echo esc_html($procedure->post_title); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <p><?php _e('No procedures found.', 'piper-privacy'); ?></p>
                <?php endif; ?>
            </div>

            <div class="category-box">
                <h3><?php _e('Guidelines', 'piper-privacy'); ?></h3>
                <a href="#" class="add-doc" data-category="guideline"><?php _e('Add New Guideline', 'piper-privacy'); ?></a>
                <?php
                $guidelines = get_posts(array(
                    'post_type' => 'privacy_document',
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'document_type',
                            'field' => 'slug',
                            'terms' => 'guideline'
                        )
                    ),
                    'posts_per_page' => -1
                ));
                if ($guidelines) :
                ?>
                    <ul class="doc-list">
                        <?php foreach ($guidelines as $guideline) : ?>
                            <li>
                                <a href="<?php echo get_edit_post_link($guideline->ID); ?>">
                                    <?php echo esc_html($guideline->post_title); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <p><?php _e('No guidelines found.', 'piper-privacy'); ?></p>
                <?php endif; ?>
            </div>

            <div class="category-box">
                <h3><?php _e('Templates', 'piper-privacy'); ?></h3>
                <a href="#" class="add-doc" data-category="template"><?php _e('Add New Template', 'piper-privacy'); ?></a>
                <?php
                $templates = get_posts(array(
                    'post_type' => 'privacy_document',
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'document_type',
                            'field' => 'slug',
                            'terms' => 'template'
                        )
                    ),
                    'posts_per_page' => -1
                ));
                if ($templates) :
                ?>
                    <ul class="doc-list">
                        <?php foreach ($templates as $template) : ?>
                            <li>
                                <a href="<?php echo get_edit_post_link($template->ID); ?>">
                                    <?php echo esc_html($template->post_title); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <p><?php _e('No templates found.', 'piper-privacy'); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Document Search -->
        <div class="doc-search-section">
            <h2><?php _e('Search Documents', 'piper-privacy'); ?></h2>
            <form method="get">
                <input type="hidden" name="page" value="piper-privacy-documentation">
                <p>
                    <input type="text" name="doc_search" class="regular-text" placeholder="<?php esc_attr_e('Search documents...', 'piper-privacy'); ?>" value="<?php echo esc_attr(isset($_GET['doc_search']) ? $_GET['doc_search'] : ''); ?>">
                    <?php submit_button(__('Search', 'piper-privacy'), 'secondary', 'submit', false); ?>
                </p>
            </form>

            <?php
            if (isset($_GET['doc_search'])) :
                $search_query = sanitize_text_field($_GET['doc_search']);
                $search_results = get_posts(array(
                    'post_type' => 'privacy_document',
                    's' => $search_query,
                    'posts_per_page' => -1
                ));

                if ($search_results) :
            ?>
                    <div class="search-results">
                        <h3><?php printf(__('Search Results for: %s', 'piper-privacy'), esc_html($search_query)); ?></h3>
                        <ul class="doc-list">
                            <?php foreach ($search_results as $result) : ?>
                                <li>
                                    <a href="<?php echo get_edit_post_link($result->ID); ?>">
                                        <?php echo esc_html($result->post_title); ?>
                                    </a>
                                    <span class="doc-type">
                                        <?php
                                        $terms = wp_get_post_terms($result->ID, 'document_type');
                                        if ($terms) {
                                            echo esc_html($terms[0]->name);
                                        }
                                        ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php else : ?>
                    <p><?php _e('No documents found matching your search.', 'piper-privacy'); ?></p>
                <?php endif;
            endif; ?>
        </div>
    </div>
</div>
