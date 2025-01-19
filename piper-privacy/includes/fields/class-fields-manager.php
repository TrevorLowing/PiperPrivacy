<?php
declare(strict_types=1);

namespace PiperPrivacy\Includes\Fields;

/**
 * Fields Manager
 * 
 * Manages registration of all MetaBox fields
 */
class FieldsManager {
    /**
     * Field registrars
     *
     * @var array
     */
    private array $registrars = [];

    /**
     * Initialize the fields manager
     */
    public function init() {
        // Register field classes
        $this->register_field_classes();

        // Initialize each registrar
        foreach ($this->registrars as $registrar) {
            $registrar->register();
        }

        // Add hooks
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_filter('rwmb_meta_boxes', [$this, 'modify_meta_boxes'], 20);
    }

    /**
     * Register field classes
     */
    private function register_field_classes() {
        $this->registrars = [
            new CollectionFields(),
            new ThresholdFields(),
            new ImpactFields(),
        ];
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets() {
        $screen = get_current_screen();
        
        // Only load on our post types
        if (!in_array($screen->post_type, ['privacy_collection', 'privacy_threshold', 'privacy_impact'])) {
            return;
        }

        wp_enqueue_style(
            'piper-privacy-admin',
            PIPER_PRIVACY_URL . 'admin/css/piper-privacy-admin.css',
            [],
            PIPER_PRIVACY_VERSION
        );

        wp_enqueue_script(
            'piper-privacy-admin',
            PIPER_PRIVACY_URL . 'admin/js/piper-privacy-admin.js',
            ['jquery'],
            PIPER_PRIVACY_VERSION,
            true
        );

        wp_localize_script('piper-privacy-admin', 'piperPrivacyAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('piper_privacy_nonce'),
        ]);
    }

    /**
     * Modify meta boxes
     *
     * @param array $meta_boxes Existing meta boxes
     * @return array Modified meta boxes
     */
    public function modify_meta_boxes($meta_boxes) {
        // Add any global modifications here
        foreach ($meta_boxes as &$box) {
            // Add custom classes
            $box['class'] = ($box['class'] ?? '') . ' piper-privacy-meta-box';
            
            // Add validation messages wrapper
            $box['custom_html'] = [
                'before' => '<div class="piper-privacy-validation-messages"></div>',
            ];
        }

        return $meta_boxes;
    }
}
