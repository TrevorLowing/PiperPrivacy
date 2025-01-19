<?php
/**
 * Impact Assessment View
 *
 * @package     PiperPrivacy
 * @subpackage  Modules\ImpactAssessment
 */

namespace PiperPrivacy\Modules\ImpactAssessment;

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Impact Assessment View Class
 */
class View {
    /**
     * Render admin page
     */
    public function render_admin_page() {
        // Enqueue scripts and styles
        wp_enqueue_script(
            'pp-assessment-admin',
            PIPER_PRIVACY_URL . 'modules/impact-assessment/assets/js/admin.js',
            ['jquery', 'wp-api'],
            PIPER_PRIVACY_VERSION,
            true
        );

        wp_enqueue_style(
            'pp-assessment-admin',
            PIPER_PRIVACY_URL . 'modules/impact-assessment/assets/css/admin.css',
            [],
            PIPER_PRIVACY_VERSION
        );

        // Localize script
        wp_localize_script('pp-assessment-admin', 'ppAssessment', [
            'nonce'   => wp_create_nonce('wp_rest'),
            'apiRoot' => esc_url_raw(rest_url('piper-privacy/v1')),
        ]);

        // Render template
        require_once dirname(__DIR__) . '/templates/admin-page.php';
    }

    /**
     * Render assessment form
     *
     * @param array $assessment Assessment data
     */
    public function render_assessment_form($assessment = []) {
        require_once dirname(__DIR__) . '/templates/assessment-form.php';
    }

    /**
     * Render assessment list
     *
     * @param array $assessments List of assessments
     */
    public function render_assessment_list($assessments) {
        require_once dirname(__DIR__) . '/templates/assessment-list.php';
    }

    /**
     * Render single assessment
     *
     * @param array $assessment Assessment data
     */
    public function render_assessment($assessment) {
        require_once dirname(__DIR__) . '/templates/assessment-single.php';
    }

    /**
     * Render assessment history
     *
     * @param array $revisions List of revisions
     */
    public function render_assessment_history($revisions) {
        require_once dirname(__DIR__) . '/templates/assessment-history.php';
    }
}
