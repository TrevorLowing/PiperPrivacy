<?php
/**
 * Privacy Reviews Management Page
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <?php settings_errors(); ?>

    <div class="privacy-reviews">
        <!-- Reviews Overview -->
        <div class="reviews-overview">
            <h2><?php _e('Reviews Overview', 'piper-privacy'); ?></h2>
            <div class="review-stats">
                <div class="stat-box">
                    <span class="stat-label"><?php _e('Pending Reviews', 'piper-privacy'); ?></span>
                    <span class="stat-value"><?php echo esc_html(get_option('piper_privacy_pending_reviews', 0)); ?></span>
                </div>
                <div class="stat-box">
                    <span class="stat-label"><?php _e('Completed Reviews', 'piper-privacy'); ?></span>
                    <span class="stat-value"><?php echo esc_html(get_option('piper_privacy_completed_reviews', 0)); ?></span>
                </div>
                <div class="stat-box">
                    <span class="stat-label"><?php _e('Overdue Reviews', 'piper-privacy'); ?></span>
                    <span class="stat-value"><?php echo esc_html(get_option('piper_privacy_overdue_reviews', 0)); ?></span>
                </div>
            </div>
        </div>

        <!-- Schedule Review -->
        <div class="schedule-review-section">
            <h2><?php _e('Schedule New Review', 'piper-privacy'); ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('schedule_review', 'review_nonce'); ?>
                <input type="hidden" name="action" value="schedule_review">

                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="review_type"><?php _e('Review Type', 'piper-privacy'); ?></label></th>
                        <td>
                            <select id="review_type" name="review_type" required>
                                <option value="collection"><?php _e('Collection Review', 'piper-privacy'); ?></option>
                                <option value="control"><?php _e('Control Review', 'piper-privacy'); ?></option>
                                <option value="process"><?php _e('Process Review', 'piper-privacy'); ?></option>
                                <option value="vendor"><?php _e('Vendor Review', 'piper-privacy'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="review_target"><?php _e('Review Target', 'piper-privacy'); ?></label></th>
                        <td>
                            <select id="review_target" name="review_target" required>
                                <option value=""><?php _e('Select Review Type First', 'piper-privacy'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="review_assignee"><?php _e('Assignee', 'piper-privacy'); ?></label></th>
                        <td>
                            <select id="review_assignee" name="review_assignee" required>
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
                        <th scope="row"><label for="review_scope"><?php _e('Review Scope', 'piper-privacy'); ?></label></th>
                        <td><textarea id="review_scope" name="review_scope" class="large-text" rows="4" required></textarea></td>
                    </tr>
                </table>

                <?php submit_button(__('Schedule Review', 'piper-privacy')); ?>
            </form>
        </div>

        <!-- Review List -->
        <div class="review-list-section">
            <h2><?php _e('Scheduled Reviews', 'piper-privacy'); ?></h2>
            
            <?php
            $reviews = get_option('piper_privacy_reviews', array());
            if (!empty($reviews)) :
            ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Type', 'piper-privacy'); ?></th>
                            <th><?php _e('Target', 'piper-privacy'); ?></th>
                            <th><?php _e('Assignee', 'piper-privacy'); ?></th>
                            <th><?php _e('Due Date', 'piper-privacy'); ?></th>
                            <th><?php _e('Status', 'piper-privacy'); ?></th>
                            <th><?php _e('Actions', 'piper-privacy'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reviews as $id => $review) : ?>
                            <tr>
                                <td><?php echo esc_html($review['type']); ?></td>
                                <td><?php echo esc_html($review['target']); ?></td>
                                <td><?php 
                                    $assignee = isset($stakeholders[$review['assignee']]) ? $stakeholders[$review['assignee']]['name'] : '';
                                    echo esc_html($assignee);
                                ?></td>
                                <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($review['due_date']))); ?></td>
                                <td><?php echo esc_html($review['status']); ?></td>
                                <td>
                                    <a href="#" class="start-review" data-id="<?php echo esc_attr($id); ?>">
                                        <?php _e('Start Review', 'piper-privacy'); ?>
                                    </a> |
                                    <a href="#" class="edit-review" data-id="<?php echo esc_attr($id); ?>">
                                        <?php _e('Edit', 'piper-privacy'); ?>
                                    </a> |
                                    <a href="#" class="delete-review" data-id="<?php echo esc_attr($id); ?>">
                                        <?php _e('Delete', 'piper-privacy'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p><?php _e('No reviews scheduled.', 'piper-privacy'); ?></p>
            <?php endif; ?>
        </div>

        <!-- Completed Reviews -->
        <div class="completed-reviews-section">
            <h2><?php _e('Completed Reviews', 'piper-privacy'); ?></h2>
            
            <?php
            $completed_reviews = array_filter($reviews, function($review) {
                return $review['status'] === 'completed';
            });

            if (!empty($completed_reviews)) :
            ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Type', 'piper-privacy'); ?></th>
                            <th><?php _e('Target', 'piper-privacy'); ?></th>
                            <th><?php _e('Reviewer', 'piper-privacy'); ?></th>
                            <th><?php _e('Completion Date', 'piper-privacy'); ?></th>
                            <th><?php _e('Result', 'piper-privacy'); ?></th>
                            <th><?php _e('Actions', 'piper-privacy'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($completed_reviews as $id => $review) : ?>
                            <tr>
                                <td><?php echo esc_html($review['type']); ?></td>
                                <td><?php echo esc_html($review['target']); ?></td>
                                <td><?php 
                                    $reviewer = isset($stakeholders[$review['reviewer']]) ? $stakeholders[$review['reviewer']]['name'] : '';
                                    echo esc_html($reviewer);
                                ?></td>
                                <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($review['completion_date']))); ?></td>
                                <td><?php echo esc_html($review['result']); ?></td>
                                <td>
                                    <a href="#" class="view-review" data-id="<?php echo esc_attr($id); ?>">
                                        <?php _e('View Details', 'piper-privacy'); ?>
                                    </a> |
                                    <a href="#" class="export-review" data-id="<?php echo esc_attr($id); ?>">
                                        <?php _e('Export', 'piper-privacy'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p><?php _e('No completed reviews found.', 'piper-privacy'); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>
