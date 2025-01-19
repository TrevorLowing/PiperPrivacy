<?php
/**
 * Consent Manager View
 *
 * @package     PiperPrivacy
 * @subpackage  Modules\ConsentManager
 */

namespace PiperPrivacy\Modules\ConsentManager;

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Consent Manager View Class
 */
class View {
    /**
     * Render admin page
     */
    public function render_admin_page() {
        // Enqueue scripts and styles
        wp_enqueue_script(
            'pp-consent-admin',
            PIPER_PRIVACY_URL . 'modules/consent-manager/assets/js/admin.js',
            ['jquery', 'wp-api'],
            PIPER_PRIVACY_VERSION,
            true
        );

        wp_enqueue_style(
            'pp-consent-admin',
            PIPER_PRIVACY_URL . 'modules/consent-manager/assets/css/admin.css',
            [],
            PIPER_PRIVACY_VERSION
        );

        // Localize script
        wp_localize_script('pp-consent-admin', 'ppConsent', [
            'nonce'   => wp_create_nonce('wp_rest'),
            'apiRoot' => esc_url_raw(rest_url('piper-privacy/v1')),
        ]);

        // Render template
        require_once dirname(__DIR__) . '/templates/admin-page.php';
    }

    /**
     * Render consent list
     *
     * @param array $consents List of consents
     */
    public function render_consent_list($consents) {
        require_once dirname(__DIR__) . '/templates/consent-list.php';
    }

    /**
     * Render consent form
     *
     * @param array $atts Form attributes
     */
    public function render_consent_form($atts) {
        $consent_type = sanitize_text_field($atts['type']);
        $title = !empty($atts['title']) ? sanitize_text_field($atts['title']) : '';
        $description = !empty($atts['description']) ? wp_kses_post($atts['description']) : '';

        // Get current user
        $user_id = get_current_user_id();

        // Check if user has already consented
        $has_consent = false;
        if ($user_id) {
            $model = new Model();
            $has_consent = $model->verify_consent($user_id, $consent_type);
        }

        require_once dirname(__DIR__) . '/templates/consent-form.php';
    }

    /**
     * Render single consent
     *
     * @param array $consent Consent data
     */
    public function render_single_consent($consent) {
        require_once dirname(__DIR__) . '/templates/consent-single.php';
    }

    /**
     * Render consent history
     *
     * @param array $revisions List of revisions
     */
    public function render_consent_history($revisions) {
        require_once dirname(__DIR__) . '/templates/consent-history.php';
    }

    /**
     * Render consent preferences
     *
     * @param int $user_id User ID
     */
    public function render_consent_preferences($user_id) {
        $model = new Model();
        $consents = $model->get_consents([
            'author' => $user_id,
            'posts_per_page' => -1,
        ]);

        require_once dirname(__DIR__) . '/templates/consent-preferences.php';
    }
}
