<?php
/**
 * Provide a admin area view for the plugin
 *
 * @package    PiperPrivacy
 * @subpackage PiperPrivacy/admin/partials
 */
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="piper-privacy-dashboard">
        <div class="postbox">
            <h2 class="hndle"><span><?php _e('Privacy Management Dashboard', 'piper-privacy'); ?></span></h2>
            <div class="inside">
                <p><?php _e('Welcome to the Privacy Management Dashboard. Here you can manage privacy collections, thresholds, and impact assessments.', 'piper-privacy'); ?></p>
                
                <div class="piper-privacy-stats">
                    <div class="stat-box">
                        <h3><?php _e('Collections', 'piper-privacy'); ?></h3>
                        <?php
                        $collections = wp_count_posts('privacy_collection');
                        echo '<p>' . esc_html($collections->publish) . ' ' . __('published', 'piper-privacy') . '</p>';
                        echo '<p>' . esc_html($collections->draft) . ' ' . __('drafts', 'piper-privacy') . '</p>';
                        ?>
                    </div>
                    
                    <div class="stat-box">
                        <h3><?php _e('Thresholds', 'piper-privacy'); ?></h3>
                        <?php
                        $thresholds = wp_count_posts('privacy_threshold');
                        echo '<p>' . esc_html($thresholds->publish) . ' ' . __('published', 'piper-privacy') . '</p>';
                        echo '<p>' . esc_html($thresholds->draft) . ' ' . __('drafts', 'piper-privacy') . '</p>';
                        ?>
                    </div>
                    
                    <div class="stat-box">
                        <h3><?php _e('Impact Assessments', 'piper-privacy'); ?></h3>
                        <?php
                        $impacts = wp_count_posts('privacy_impact');
                        echo '<p>' . esc_html($impacts->publish) . ' ' . __('published', 'piper-privacy') . '</p>';
                        echo '<p>' . esc_html($impacts->draft) . ' ' . __('drafts', 'piper-privacy') . '</p>';
                        ?>
                    </div>
                </div>
                
                <div class="piper-privacy-actions">
                    <a href="<?php echo esc_url(admin_url('post-new.php?post_type=privacy_collection')); ?>" class="button button-primary">
                        <?php _e('New Privacy Collection', 'piper-privacy'); ?>
                    </a>
                    <a href="<?php echo esc_url(admin_url('post-new.php?post_type=privacy_threshold')); ?>" class="button button-primary">
                        <?php _e('New Privacy Threshold', 'piper-privacy'); ?>
                    </a>
                    <a href="<?php echo esc_url(admin_url('post-new.php?post_type=privacy_impact')); ?>" class="button button-primary">
                        <?php _e('New Impact Assessment', 'piper-privacy'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
