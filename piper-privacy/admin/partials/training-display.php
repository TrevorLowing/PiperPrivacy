<?php
/**
 * Privacy Training Management Page
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <?php settings_errors(); ?>

    <div class="privacy-training">
        <!-- Training Overview -->
        <div class="training-overview">
            <h2><?php _e('Training Overview', 'piper-privacy'); ?></h2>
            <div class="training-stats">
                <div class="stat-box">
                    <span class="stat-label"><?php _e('Active Courses', 'piper-privacy'); ?></span>
                    <span class="stat-value"><?php echo esc_html(get_option('piper_privacy_active_courses', 0)); ?></span>
                </div>
                <div class="stat-box">
                    <span class="stat-label"><?php _e('Enrolled Users', 'piper-privacy'); ?></span>
                    <span class="stat-value"><?php echo esc_html(get_option('piper_privacy_enrolled_users', 0)); ?></span>
                </div>
                <div class="stat-box">
                    <span class="stat-label"><?php _e('Completion Rate', 'piper-privacy'); ?></span>
                    <span class="stat-value"><?php echo esc_html(get_option('piper_privacy_completion_rate', '0%')); ?></span>
                </div>
            </div>
        </div>

        <!-- Add New Training Course -->
        <div class="add-course-section">
            <h2><?php _e('Add New Training Course', 'piper-privacy'); ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('add_training_course', 'course_nonce'); ?>
                <input type="hidden" name="action" value="add_training_course">

                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="course_title"><?php _e('Course Title', 'piper-privacy'); ?></label></th>
                        <td><input type="text" id="course_title" name="course_title" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="course_description"><?php _e('Description', 'piper-privacy'); ?></label></th>
                        <td><textarea id="course_description" name="course_description" class="large-text" rows="4" required></textarea></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="course_duration"><?php _e('Duration (minutes)', 'piper-privacy'); ?></label></th>
                        <td><input type="number" id="course_duration" name="course_duration" class="small-text" min="1" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="course_type"><?php _e('Course Type', 'piper-privacy'); ?></label></th>
                        <td>
                            <select id="course_type" name="course_type" required>
                                <option value="mandatory"><?php _e('Mandatory', 'piper-privacy'); ?></option>
                                <option value="optional"><?php _e('Optional', 'piper-privacy'); ?></option>
                                <option value="certification"><?php _e('Certification', 'piper-privacy'); ?></option>
                            </select>
                        </td>
                    </tr>
                </table>

                <?php submit_button(__('Add Course', 'piper-privacy')); ?>
            </form>
        </div>

        <!-- Training Course List -->
        <div class="course-list-section">
            <h2><?php _e('Available Courses', 'piper-privacy'); ?></h2>
            
            <?php
            $courses = get_option('piper_privacy_courses', array());
            if (!empty($courses)) :
            ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Course Title', 'piper-privacy'); ?></th>
                            <th><?php _e('Type', 'piper-privacy'); ?></th>
                            <th><?php _e('Duration', 'piper-privacy'); ?></th>
                            <th><?php _e('Enrolled', 'piper-privacy'); ?></th>
                            <th><?php _e('Completion Rate', 'piper-privacy'); ?></th>
                            <th><?php _e('Actions', 'piper-privacy'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $id => $course) : ?>
                            <tr>
                                <td><?php echo esc_html($course['title']); ?></td>
                                <td><?php echo esc_html($course['type']); ?></td>
                                <td><?php printf(_n('%d minute', '%d minutes', $course['duration'], 'piper-privacy'), $course['duration']); ?></td>
                                <td><?php echo esc_html($course['enrolled_count']); ?></td>
                                <td><?php echo esc_html($course['completion_rate'] . '%'); ?></td>
                                <td>
                                    <a href="#" class="edit-course" data-id="<?php echo esc_attr($id); ?>">
                                        <?php _e('Edit', 'piper-privacy'); ?>
                                    </a> |
                                    <a href="#" class="view-enrollments" data-id="<?php echo esc_attr($id); ?>">
                                        <?php _e('View Enrollments', 'piper-privacy'); ?>
                                    </a> |
                                    <a href="#" class="delete-course" data-id="<?php echo esc_attr($id); ?>">
                                        <?php _e('Delete', 'piper-privacy'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p><?php _e('No training courses found.', 'piper-privacy'); ?></p>
            <?php endif; ?>
        </div>

        <!-- Training Reports -->
        <div class="training-reports">
            <h2><?php _e('Training Reports', 'piper-privacy'); ?></h2>
            <div class="report-actions">
                <button class="button" id="export-completion-report">
                    <?php _e('Export Completion Report', 'piper-privacy'); ?>
                </button>
                <button class="button" id="export-compliance-report">
                    <?php _e('Export Compliance Report', 'piper-privacy'); ?>
                </button>
            </div>
        </div>
    </div>
</div>
