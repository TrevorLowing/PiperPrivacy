<?php
namespace PiperPrivacy\Includes\Accessibility;

/**
 * Accessibility Manager
 * 
 * Handles accessibility features and compliance
 *
 * @package    PiperPrivacy
 * @subpackage PiperPrivacy/includes/accessibility
 */
class AccessibilityManager {
    /**
     * Initialize the accessibility manager
     */
    public function __construct() {
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('admin_footer', [$this, 'render_accessibility_toolbar']);
        add_filter('admin_body_class', [$this, 'add_accessibility_classes']);
        add_filter('the_content', [$this, 'enhance_content_accessibility']);
        add_filter('post_thumbnail_html', [$this, 'enhance_image_accessibility'], 10, 5);
        add_action('wp_ajax_toggle_accessibility_feature', [$this, 'ajax_toggle_feature']);
    }

    /**
     * Register accessibility settings
     */
    public function register_settings() {
        register_setting('piper_privacy_options', 'piper_privacy_accessibility_settings', [
            'type' => 'array',
            'default' => [
                'high_contrast' => false,
                'large_text' => false,
                'reduce_motion' => false,
                'keyboard_focus' => true,
                'screen_reader' => true,
            ],
        ]);
    }

    /**
     * Enqueue accessibility scripts and styles
     */
    public function enqueue_scripts() {
        wp_enqueue_style(
            'privacy-accessibility',
            PIPER_PRIVACY_URL . 'admin/css/accessibility.css',
            [],
            PIPER_PRIVACY_VERSION
        );

        wp_enqueue_script(
            'privacy-accessibility',
            PIPER_PRIVACY_URL . 'admin/js/accessibility.js',
            ['jquery'],
            PIPER_PRIVACY_VERSION,
            true
        );

        wp_localize_script('privacy-accessibility', 'privacyAccessibility', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('privacy_accessibility'),
            'settings' => get_option('piper_privacy_accessibility_settings'),
            'i18n' => [
                'toggleHighContrast' => __('Toggle High Contrast', 'piper-privacy'),
                'toggleLargeText' => __('Toggle Large Text', 'piper-privacy'),
                'toggleReduceMotion' => __('Toggle Reduce Motion', 'piper-privacy'),
                'toggleKeyboardFocus' => __('Toggle Keyboard Focus', 'piper-privacy'),
                'toggleScreenReader' => __('Toggle Screen Reader Support', 'piper-privacy'),
            ],
        ]);
    }

    /**
     * Render accessibility toolbar
     */
    public function render_accessibility_toolbar() {
        $screen = get_current_screen();
        if (!$this->is_privacy_screen($screen)) {
            return;
        }

        $settings = get_option('piper_privacy_accessibility_settings');
        ?>
        <div id="accessibilityToolbar" class="accessibility-toolbar" role="region" aria-label="<?php esc_attr_e('Accessibility Controls', 'piper-privacy'); ?>">
            <button 
                type="button" 
                class="toolbar-toggle" 
                aria-expanded="false"
                aria-controls="accessibilityControls"
            >
                <span class="dashicons dashicons-universal-access"></span>
                <span class="screen-reader-text">
                    <?php _e('Toggle Accessibility Controls', 'piper-privacy'); ?>
                </span>
            </button>

            <div id="accessibilityControls" class="toolbar-controls" hidden>
                <div class="control-group">
                    <button 
                        type="button" 
                        class="feature-toggle <?php echo $settings['high_contrast'] ? 'active' : ''; ?>"
                        data-feature="high_contrast"
                        aria-pressed="<?php echo $settings['high_contrast'] ? 'true' : 'false'; ?>"
                    >
                        <span class="dashicons dashicons-visibility"></span>
                        <?php _e('High Contrast', 'piper-privacy'); ?>
                    </button>

                    <button 
                        type="button" 
                        class="feature-toggle <?php echo $settings['large_text'] ? 'active' : ''; ?>"
                        data-feature="large_text"
                        aria-pressed="<?php echo $settings['large_text'] ? 'true' : 'false'; ?>"
                    >
                        <span class="dashicons dashicons-editor-textcolor"></span>
                        <?php _e('Large Text', 'piper-privacy'); ?>
                    </button>

                    <button 
                        type="button" 
                        class="feature-toggle <?php echo $settings['reduce_motion'] ? 'active' : ''; ?>"
                        data-feature="reduce_motion"
                        aria-pressed="<?php echo $settings['reduce_motion'] ? 'true' : 'false'; ?>"
                    >
                        <span class="dashicons dashicons-controls-pause"></span>
                        <?php _e('Reduce Motion', 'piper-privacy'); ?>
                    </button>

                    <button 
                        type="button" 
                        class="feature-toggle <?php echo $settings['keyboard_focus'] ? 'active' : ''; ?>"
                        data-feature="keyboard_focus"
                        aria-pressed="<?php echo $settings['keyboard_focus'] ? 'true' : 'false'; ?>"
                    >
                        <span class="dashicons dashicons-keyboard"></span>
                        <?php _e('Keyboard Focus', 'piper-privacy'); ?>
                    </button>

                    <button 
                        type="button" 
                        class="feature-toggle <?php echo $settings['screen_reader'] ? 'active' : ''; ?>"
                        data-feature="screen_reader"
                        aria-pressed="<?php echo $settings['screen_reader'] ? 'true' : 'false'; ?>"
                    >
                        <span class="dashicons dashicons-megaphone"></span>
                        <?php _e('Screen Reader', 'piper-privacy'); ?>
                    </button>
                </div>

                <div class="keyboard-shortcuts" role="region" aria-label="<?php esc_attr_e('Keyboard Shortcuts', 'piper-privacy'); ?>">
                    <h3><?php _e('Keyboard Shortcuts', 'piper-privacy'); ?></h3>
                    <dl>
                        <dt><kbd>Alt</kbd> + <kbd>1</kbd></dt>
                        <dd><?php _e('Dashboard', 'piper-privacy'); ?></dd>
                        
                        <dt><kbd>Alt</kbd> + <kbd>2</kbd></dt>
                        <dd><?php _e('New Privacy Collection', 'piper-privacy'); ?></dd>
                        
                        <dt><kbd>Alt</kbd> + <kbd>3</kbd></dt>
                        <dd><?php _e('New Privacy Threshold', 'piper-privacy'); ?></dd>
                        
                        <dt><kbd>Alt</kbd> + <kbd>4</kbd></dt>
                        <dd><?php _e('New Privacy Impact', 'piper-privacy'); ?></dd>
                        
                        <dt><kbd>/</kbd></dt>
                        <dd><?php _e('Search', 'piper-privacy'); ?></dd>
                        
                        <dt><kbd>?</kbd></dt>
                        <dd><?php _e('Show All Shortcuts', 'piper-privacy'); ?></dd>
                    </dl>
                </div>
            </div>
        </div>

        <style>
            .accessibility-toolbar {
                position: fixed;
                bottom: 20px;
                right: 20px;
                z-index: 9999;
                background: #fff;
                border: 1px solid #ccd0d4;
                border-radius: 4px;
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            }

            .toolbar-toggle {
                padding: 10px;
                border: none;
                background: none;
                cursor: pointer;
            }

            .toolbar-controls {
                padding: 15px;
                border-top: 1px solid #ccd0d4;
            }

            .control-group {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }

            .feature-toggle {
                display: flex;
                align-items: center;
                gap: 8px;
                padding: 8px 12px;
                border: 1px solid #ccd0d4;
                border-radius: 4px;
                background: #f0f0f1;
                cursor: pointer;
            }

            .feature-toggle.active {
                background: #2271b1;
                color: #fff;
            }

            .keyboard-shortcuts {
                margin-top: 15px;
                padding-top: 15px;
                border-top: 1px solid #ccd0d4;
            }

            .keyboard-shortcuts h3 {
                margin: 0 0 10px;
                font-size: 14px;
            }

            .keyboard-shortcuts dl {
                margin: 0;
                display: grid;
                grid-template-columns: auto 1fr;
                gap: 8px;
                align-items: center;
            }

            .keyboard-shortcuts dt {
                font-weight: normal;
            }

            .keyboard-shortcuts kbd {
                padding: 2px 6px;
                border: 1px solid #ccd0d4;
                border-radius: 3px;
                background: #f0f0f1;
                font-size: 12px;
            }
        </style>
        <?php
    }

    /**
     * Add accessibility classes to admin body
     *
     * @param string $classes Space-separated list of classes
     * @return string Modified classes
     */
    public function add_accessibility_classes($classes) {
        $settings = get_option('piper_privacy_accessibility_settings');
        
        foreach ($settings as $feature => $enabled) {
            if ($enabled) {
                $classes .= " accessibility-{$feature}";
            }
        }

        return $classes;
    }

    /**
     * Enhance content accessibility
     *
     * @param string $content Post content
     * @return string Enhanced content
     */
    public function enhance_content_accessibility($content) {
        // Add ARIA landmarks
        $content = str_replace(
            ['<div class="privacy-section">', '<div class="privacy-controls">'],
            ['<div class="privacy-section" role="region">', '<div class="privacy-controls" role="group">'],
            $content
        );

        // Add skip links
        if (strpos($content, 'privacy-section') !== false) {
            $content = '<a href="#privacy-main" class="screen-reader-text">' . 
                __('Skip to main content', 'piper-privacy') . 
                '</a>' . $content;
        }

        // Enhance table accessibility
        $content = preg_replace(
            '/<table>/',
            '<table role="grid">',
            $content
        );

        // Enhance form accessibility
        $content = preg_replace(
            '/<input([^>]*)>/',
            '<input$1 aria-invalid="false">',
            $content
        );

        return $content;
    }

    /**
     * Enhance image accessibility
     *
     * @param string $html              Image HTML
     * @param int    $post_id           Post ID
     * @param int    $post_thumbnail_id Thumbnail ID
     * @param string $size              Size name
     * @param array  $attr              Attributes
     * @return string Enhanced image HTML
     */
    public function enhance_image_accessibility($html, $post_id, $post_thumbnail_id, $size, $attr) {
        // Add alt text if missing
        if (strpos($html, 'alt="') === false) {
            $post = get_post($post_id);
            $alt_text = $post ? $post->post_title : '';
            $html = str_replace('<img', '<img alt="' . esc_attr($alt_text) . '"', $html);
        }

        // Add role and aria-label
        if (strpos($html, 'role="') === false) {
            $html = str_replace('<img', '<img role="img"', $html);
        }

        return $html;
    }

    /**
     * Handle AJAX toggle of accessibility features
     */
    public function ajax_toggle_feature() {
        check_ajax_referer('privacy_accessibility', 'nonce');

        $feature = sanitize_key($_POST['feature']);
        $enabled = rest_sanitize_boolean($_POST['enabled']);

        $settings = get_option('piper_privacy_accessibility_settings');
        $settings[$feature] = $enabled;
        update_option('piper_privacy_accessibility_settings', $settings);

        wp_send_json_success(['settings' => $settings]);
    }

    /**
     * Check if current screen is a privacy screen
     *
     * @param WP_Screen $screen Current screen
     * @return bool Whether screen is privacy-related
     */
    private function is_privacy_screen($screen) {
        $privacy_screens = [
            'privacy_collection',
            'privacy_threshold',
            'privacy_impact',
            'privacy-dashboard',
            'privacy-settings',
            'privacy-assistant',
        ];

        return $screen && (
            in_array($screen->post_type, $privacy_screens) ||
            in_array($screen->base, $privacy_screens)
        );
    }
}
