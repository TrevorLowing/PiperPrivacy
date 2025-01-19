<?php
/**
 * Privacy Assessments view template
 *
 * @var array $assessments Privacy assessments (PTA and PIA)
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

// Get current assessment type filter
$current_type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : 'all';
?>
<div class="wrap piper-privacy-assessments">
    <h1 class="wp-heading-inline">
        <?php esc_html_e('Privacy Assessments', 'piper-privacy'); ?>
    </h1>

    <!-- Type Filter -->
    <div class="assessment-filters">
        <ul class="subsubsub">
            <li>
                <a href="<?php echo esc_url(add_query_arg('type', 'all')); ?>" 
                   class="<?php echo $current_type === 'all' ? 'current' : ''; ?>">
                    <?php esc_html_e('All', 'piper-privacy'); ?>
                </a> |
            </li>
            <li>
                <a href="<?php echo esc_url(add_query_arg('type', 'privacy-threshold')); ?>"
                   class="<?php echo $current_type === 'privacy-threshold' ? 'current' : ''; ?>">
                    <?php esc_html_e('PTAs', 'piper-privacy'); ?>
                </a> |
            </li>
            <li>
                <a href="<?php echo esc_url(add_query_arg('type', 'privacy-impact')); ?>"
                   class="<?php echo $current_type === 'privacy-impact' ? 'current' : ''; ?>">
                    <?php esc_html_e('PIAs', 'piper-privacy'); ?>
                </a>
            </li>
        </ul>
    </div>

    <!-- Assessment Summary -->
    <div class="assessment-summary">
        <div class="summary-card">
            <h3><?php esc_html_e('PTAs Required', 'piper-privacy'); ?></h3>
            <div class="summary-count pta-required">
                <?php
                $pta_required = get_posts([
                    'post_type' => 'privacy-collection',
                    'meta_key' => '_workflow_status',
                    'meta_value' => 'pta_required',
                    'posts_per_page' => -1
                ]);
                echo count($pta_required);
                ?>
            </div>
        </div>

        <div class="summary-card">
            <h3><?php esc_html_e('PIAs Required', 'piper-privacy'); ?></h3>
            <div class="summary-count pia-required">
                <?php
                $pia_required = get_posts([
                    'post_type' => 'privacy-collection',
                    'meta_key' => '_workflow_status',
                    'meta_value' => 'pia_required',
                    'posts_per_page' => -1
                ]);
                echo count($pia_required);
                ?>
            </div>
        </div>

        <div class="summary-card">
            <h3><?php esc_html_e('Under Review', 'piper-privacy'); ?></h3>
            <div class="summary-count under-review">
                <?php
                $under_review = get_posts([
                    'post_type' => ['privacy-threshold', 'privacy-impact'],
                    'meta_key' => '_review_status',
                    'meta_value' => 'in_review',
                    'posts_per_page' => -1
                ]);
                echo count($under_review);
                ?>
            </div>
        </div>
    </div>

    <!-- Assessment List -->
    <?php if ($assessments): ?>
        <div class="assessment-list">
            <?php foreach ($assessments as $assessment):
                $collection_id = get_post_meta($assessment->ID, 'collection_reference', true);
                $collection = get_post($collection_id);
                $status = get_post_meta($assessment->ID, $assessment->post_type === 'privacy-threshold' ? 'pta_status' : 'pia_status', true);
                $assigned_to = get_post_meta($assessment->ID, 'assigned_analyst', true);
                $assigned_user = get_userdata($assigned_to);
                ?>
                <div class="assessment-item <?php echo esc_attr($assessment->post_type); ?>">
                    <div class="assessment-header">
                        <div class="assessment-type">
                            <?php echo $assessment->post_type === 'privacy-threshold' ? esc_html__('PTA', 'piper-privacy') : esc_html__('PIA', 'piper-privacy'); ?>
                        </div>
                        <div class="assessment-status <?php echo esc_attr($status); ?>">
                            <?php echo esc_html(ucfirst($status)); ?>
                        </div>
                    </div>

                    <div class="assessment-details">
                        <h3><?php echo esc_html($assessment->post_title); ?></h3>
                        <?php if ($collection): ?>
                            <div class="collection-reference">
                                <?php esc_html_e('Collection:', 'piper-privacy'); ?>
                                <a href="<?php echo esc_url(get_edit_post_link($collection_id)); ?>">
                                    <?php echo esc_html($collection->post_title); ?>
                                </a>
                            </div>
                        <?php endif; ?>

                        <div class="assessment-meta">
                            <?php if ($assigned_user): ?>
                                <div class="assigned-to">
                                    <?php esc_html_e('Assigned to:', 'piper-privacy'); ?>
                                    <?php echo esc_html($assigned_user->display_name); ?>
                                </div>
                            <?php endif; ?>

                            <div class="due-date">
                                <?php esc_html_e('Due:', 'piper-privacy'); ?>
                                <?php echo esc_html(date_i18n(get_option('date_format'), strtotime(get_post_meta($assessment->ID, 'due_date', true)))); ?>
                            </div>
                        </div>
                    </div>

                    <div class="assessment-progress">
                        <div class="progress-bar">
                            <div class="progress-value" style="width: <?php echo esc_attr(get_post_meta($assessment->ID, '_completion_percentage', true)); ?>%"></div>
                        </div>
                        <div class="progress-label">
                            <?php echo esc_html(get_post_meta($assessment->ID, '_completion_percentage', true)); ?>% <?php esc_html_e('Complete', 'piper-privacy'); ?>
                        </div>
                    </div>

                    <div class="assessment-actions">
                        <a href="<?php echo esc_url(get_edit_post_link($assessment->ID)); ?>" class="button">
                            <?php esc_html_e('View Assessment', 'piper-privacy'); ?>
                        </a>
                        <?php if (current_user_can('edit_post', $assessment->ID)): ?>
                            <button type="button" class="button update-status" data-id="<?php echo esc_attr($assessment->ID); ?>">
                                <?php esc_html_e('Update Status', 'piper-privacy'); ?>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="no-assessments">
            <?php esc_html_e('No assessments found.', 'piper-privacy'); ?>
        </p>
    <?php endif; ?>

    <!-- Status Update Modal -->
    <div id="status-update-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <h2><?php esc_html_e('Update Assessment Status', 'piper-privacy'); ?></h2>
            <form id="status-update-form" method="post">
                <?php wp_nonce_field('update_assessment_status', 'status_nonce'); ?>
                <input type="hidden" name="assessment_id" id="assessment_id" value="">
                
                <div class="form-field">
                    <label for="new_status"><?php esc_html_e('New Status', 'piper-privacy'); ?></label>
                    <select name="new_status" id="new_status">
                        <option value="draft"><?php esc_html_e('Draft', 'piper-privacy'); ?></option>
                        <option value="in_progress"><?php esc_html_e('In Progress', 'piper-privacy'); ?></option>
                        <option value="in_review"><?php esc_html_e('In Review', 'piper-privacy'); ?></option>
                        <option value="completed"><?php esc_html_e('Completed', 'piper-privacy'); ?></option>
                    </select>
                </div>

                <div class="form-field">
                    <label for="status_notes"><?php esc_html_e('Notes', 'piper-privacy'); ?></label>
                    <textarea name="status_notes" id="status_notes" rows="3"></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="button button-primary">
                        <?php esc_html_e('Update Status', 'piper-privacy'); ?>
                    </button>
                    <button type="button" class="button cancel-modal">
                        <?php esc_html_e('Cancel', 'piper-privacy'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>