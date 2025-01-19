<?php
namespace PiperPrivacy\Includes\I18n;

/**
 * Language Manager
 * 
 * Handles multi-language support and translation management
 *
 * @package    PiperPrivacy
 * @subpackage PiperPrivacy/includes/i18n
 */
class LanguageManager {
    /**
     * @var array Supported languages
     */
    private $supported_languages;

    /**
     * @var array Translation memory
     */
    private $translation_memory;

    /**
     * Initialize the language manager
     */
    public function __construct() {
        $this->supported_languages = [
            'en_US' => [
                'name' => __('English (US)', 'piper-privacy'),
                'native_name' => 'English (US)',
                'dir' => 'ltr',
            ],
            'es_ES' => [
                'name' => __('Spanish', 'piper-privacy'),
                'native_name' => 'Español',
                'dir' => 'ltr',
            ],
            'fr_FR' => [
                'name' => __('French', 'piper-privacy'),
                'native_name' => 'Français',
                'dir' => 'ltr',
            ],
            'ar_SA' => [
                'name' => __('Arabic', 'piper-privacy'),
                'native_name' => 'العربية',
                'dir' => 'rtl',
            ],
        ];

        add_action('init', [$this, 'initialize_language_support']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_menu', [$this, 'add_language_menu']);
        add_action('add_meta_boxes', [$this, 'add_translation_meta_box']);
        add_action('save_post', [$this, 'save_translations']);
        add_filter('the_content', [$this, 'filter_content_language']);
        add_filter('get_post_metadata', [$this, 'filter_metadata_language'], 10, 4);
        add_action('wp_ajax_translate_content', [$this, 'ajax_translate_content']);
    }

    /**
     * Initialize language support
     */
    public function initialize_language_support() {
        // Load translation memory
        $this->translation_memory = get_option('piper_privacy_translation_memory', []);

        // Register post type language support
        $post_types = ['privacy_collection', 'privacy_threshold', 'privacy_impact'];
        foreach ($post_types as $post_type) {
            add_post_type_support($post_type, 'languages');
        }

        // Load text domain
        load_plugin_textdomain('piper-privacy', false, PIPER_PRIVACY_DIR . 'languages');
    }

    /**
     * Register language settings
     */
    public function register_settings() {
        register_setting('piper_privacy_options', 'piper_privacy_enabled_languages', [
            'type' => 'array',
            'default' => ['en_US'],
        ]);

        register_setting('piper_privacy_options', 'piper_privacy_default_language', [
            'type' => 'string',
            'default' => 'en_US',
        ]);

        register_setting('piper_privacy_options', 'piper_privacy_translation_service', [
            'type' => 'string',
            'default' => 'none',
        ]);

        register_setting('piper_privacy_options', 'piper_privacy_translation_api_key');
    }

    /**
     * Add language management menu
     */
    public function add_language_menu() {
        add_submenu_page(
            'privacy-dashboard',
            __('Language Settings', 'piper-privacy'),
            __('Languages', 'piper-privacy'),
            'manage_privacy',
            'privacy-languages',
            [$this, 'render_language_page']
        );
    }

    /**
     * Render language settings page
     */
    public function render_language_page() {
        $enabled_languages = get_option('piper_privacy_enabled_languages');
        $default_language = get_option('piper_privacy_default_language');
        $translation_service = get_option('piper_privacy_translation_service');
        ?>
        <div class="wrap">
            <h1><?php _e('Language Settings', 'piper-privacy'); ?></h1>

            <form method="post" action="options.php">
                <?php settings_fields('piper_privacy_options'); ?>

                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><?php _e('Enabled Languages', 'piper-privacy'); ?></th>
                        <td>
                            <?php foreach ($this->supported_languages as $code => $language): ?>
                            <label>
                                <input 
                                    type="checkbox" 
                                    name="piper_privacy_enabled_languages[]" 
                                    value="<?php echo esc_attr($code); ?>"
                                    <?php checked(in_array($code, $enabled_languages)); ?>
                                >
                                <?php echo esc_html($language['name']); ?> 
                                (<?php echo esc_html($language['native_name']); ?>)
                            </label><br>
                            <?php endforeach; ?>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e('Default Language', 'piper-privacy'); ?></th>
                        <td>
                            <select name="piper_privacy_default_language">
                                <?php foreach ($this->supported_languages as $code => $language): ?>
                                <option 
                                    value="<?php echo esc_attr($code); ?>"
                                    <?php selected($code, $default_language); ?>
                                >
                                    <?php echo esc_html($language['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e('Translation Service', 'piper-privacy'); ?></th>
                        <td>
                            <select name="piper_privacy_translation_service">
                                <option value="none" <?php selected('none', $translation_service); ?>>
                                    <?php _e('Manual Translation', 'piper-privacy'); ?>
                                </option>
                                <option value="google" <?php selected('google', $translation_service); ?>>
                                    <?php _e('Google Cloud Translation', 'piper-privacy'); ?>
                                </option>
                                <option value="deepl" <?php selected('deepl', $translation_service); ?>>
                                    <?php _e('DeepL', 'piper-privacy'); ?>
                                </option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e('API Key', 'piper-privacy'); ?></th>
                        <td>
                            <input 
                                type="password" 
                                name="piper_privacy_translation_api_key" 
                                value="<?php echo esc_attr(get_option('piper_privacy_translation_api_key')); ?>"
                                class="regular-text"
                            >
                        </td>
                    </tr>
                </table>

                <?php submit_button(); ?>
            </form>

            <h2><?php _e('Translation Memory Statistics', 'piper-privacy'); ?></h2>
            <table class="widefat">
                <thead>
                    <tr>
                        <th><?php _e('Language', 'piper-privacy'); ?></th>
                        <th><?php _e('Translated Strings', 'piper-privacy'); ?></th>
                        <th><?php _e('Last Updated', 'piper-privacy'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->translation_memory as $lang => $data): ?>
                    <tr>
                        <td><?php echo esc_html($this->supported_languages[$lang]['name']); ?></td>
                        <td><?php echo count($data['strings']); ?></td>
                        <td><?php echo esc_html($data['last_updated']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Add translation meta box
     */
    public function add_translation_meta_box() {
        $post_types = ['privacy_collection', 'privacy_threshold', 'privacy_impact'];
        
        foreach ($post_types as $post_type) {
            add_meta_box(
                'privacy_translations',
                __('Translations', 'piper-privacy'),
                [$this, 'render_translation_meta_box'],
                $post_type,
                'normal',
                'high'
            );
        }
    }

    /**
     * Render translation meta box
     *
     * @param WP_Post $post Post object
     */
    public function render_translation_meta_box($post) {
        $enabled_languages = get_option('piper_privacy_enabled_languages');
        $default_language = get_option('piper_privacy_default_language');
        $translations = get_post_meta($post->ID, '_translations', true) ?: [];
        ?>
        <div class="translation-editor">
            <?php foreach ($enabled_languages as $lang_code): ?>
                <?php if ($lang_code === $default_language) continue; ?>
                <div class="translation-panel" data-lang="<?php echo esc_attr($lang_code); ?>">
                    <h3>
                        <?php echo esc_html($this->supported_languages[$lang_code]['name']); ?>
                        (<?php echo esc_html($this->supported_languages[$lang_code]['native_name']); ?>)
                    </h3>

                    <div class="translation-fields">
                        <p>
                            <label><?php _e('Title', 'piper-privacy'); ?></label>
                            <input 
                                type="text" 
                                name="translations[<?php echo esc_attr($lang_code); ?>][title]" 
                                value="<?php echo esc_attr($translations[$lang_code]['title'] ?? ''); ?>"
                                class="widefat"
                            >
                        </p>

                        <p>
                            <label><?php _e('Content', 'piper-privacy'); ?></label>
                            <?php
                            wp_editor(
                                $translations[$lang_code]['content'] ?? '',
                                'translation_' . $lang_code . '_content',
                                [
                                    'textarea_name' => "translations[{$lang_code}][content]",
                                    'media_buttons' => false,
                                    'textarea_rows' => 10,
                                ]
                            );
                            ?>
                        </p>

                        <p>
                            <button 
                                type="button" 
                                class="button translate-content"
                                data-lang="<?php echo esc_attr($lang_code); ?>"
                            >
                                <?php _e('Auto-translate', 'piper-privacy'); ?>
                            </button>
                            <span class="translation-status"></span>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('.translate-content').on('click', function() {
                var $button = $(this);
                var $status = $button.siblings('.translation-status');
                var langCode = $button.data('lang');
                
                $status.html('<?php _e('Translating...', 'piper-privacy'); ?>');
                $button.prop('disabled', true);
                
                $.post(ajaxurl, {
                    action: 'translate_content',
                    post_id: '<?php echo $post->ID; ?>',
                    target_lang: langCode,
                    nonce: '<?php echo wp_create_nonce('translate_content'); ?>'
                }, function(response) {
                    if (response.success) {
                        $('input[name="translations[' + langCode + '][title]"]')
                            .val(response.data.translations.title);
                        
                        var editor = tinymce.get('translation_' + langCode + '_content');
                        if (editor) {
                            editor.setContent(response.data.translations.content);
                        }
                        
                        $status.html('<?php _e('Translation complete', 'piper-privacy'); ?>');
                    } else {
                        $status.html(response.data.message);
                    }
                    $button.prop('disabled', false);
                });
            });
        });
        </script>

        <style>
            .translation-editor {
                margin-top: 20px;
            }

            .translation-panel {
                margin-bottom: 20px;
                padding: 15px;
                background: #fff;
                border: 1px solid #ccd0d4;
                border-radius: 4px;
            }

            .translation-panel h3 {
                margin-top: 0;
                padding-bottom: 10px;
                border-bottom: 1px solid #ccd0d4;
            }

            .translation-fields label {
                display: block;
                margin-bottom: 5px;
                font-weight: 600;
            }

            .translation-status {
                margin-left: 10px;
                font-style: italic;
            }
        </style>
        <?php
    }

    /**
     * Save translations
     *
     * @param int $post_id Post ID
     */
    public function save_translations($post_id) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!isset($_POST['translations'])) {
            return;
        }

        $translations = [];
        foreach ($_POST['translations'] as $lang_code => $data) {
            $translations[$lang_code] = [
                'title' => sanitize_text_field($data['title']),
                'content' => wp_kses_post($data['content']),
            ];
        }

        update_post_meta($post_id, '_translations', $translations);
    }

    /**
     * Filter content based on language
     *
     * @param string $content Post content
     * @return string Filtered content
     */
    public function filter_content_language($content) {
        $post = get_post();
        if (!$post || !post_type_supports($post->post_type, 'languages')) {
            return $content;
        }

        $current_lang = $this->get_current_language();
        $default_lang = get_option('piper_privacy_default_language');

        if ($current_lang === $default_lang) {
            return $content;
        }

        $translations = get_post_meta($post->ID, '_translations', true);
        if (!empty($translations[$current_lang]['content'])) {
            return $translations[$current_lang]['content'];
        }

        return $content;
    }

    /**
     * Filter metadata based on language
     *
     * @param mixed  $value     Metadata value
     * @param int    $object_id Object ID
     * @param string $meta_key  Meta key
     * @param bool   $single    Whether to return a single value
     * @return mixed Filtered value
     */
    public function filter_metadata_language($value, $object_id, $meta_key, $single) {
        if ($meta_key === '_translations') {
            return $value;
        }

        $post = get_post($object_id);
        if (!$post || !post_type_supports($post->post_type, 'languages')) {
            return $value;
        }

        $current_lang = $this->get_current_language();
        $default_lang = get_option('piper_privacy_default_language');

        if ($current_lang === $default_lang) {
            return $value;
        }

        $translations = get_post_meta($object_id, '_translations', true);
        if (!empty($translations[$current_lang][$meta_key])) {
            return $single ? $translations[$current_lang][$meta_key] : [$translations[$current_lang][$meta_key]];
        }

        return $value;
    }

    /**
     * Handle AJAX content translation
     */
    public function ajax_translate_content() {
        check_ajax_referer('translate_content', 'nonce');

        $post_id = intval($_POST['post_id']);
        $target_lang = sanitize_key($_POST['target_lang']);
        $post = get_post($post_id);

        if (!$post || !post_type_supports($post->post_type, 'languages')) {
            wp_send_json_error(['message' => __('Invalid post type', 'piper-privacy')]);
        }

        $translation_service = get_option('piper_privacy_translation_service');
        if ($translation_service === 'none') {
            wp_send_json_error(['message' => __('No translation service configured', 'piper-privacy')]);
        }

        try {
            $translations = $this->translate_content($post, $target_lang);
            wp_send_json_success(['translations' => $translations]);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * Translate content using configured service
     *
     * @param WP_Post $post        Post object
     * @param string  $target_lang Target language
     * @return array Translated content
     */
    private function translate_content($post, $target_lang) {
        $translation_service = get_option('piper_privacy_translation_service');
        $api_key = get_option('piper_privacy_translation_api_key');

        if (!$api_key) {
            throw new \Exception(__('Translation API key not configured', 'piper-privacy'));
        }

        $source_lang = get_option('piper_privacy_default_language');
        $content = [
            'title' => $post->post_title,
            'content' => $post->post_content,
        ];

        switch ($translation_service) {
            case 'google':
                return $this->translate_with_google($content, $source_lang, $target_lang);
            case 'deepl':
                return $this->translate_with_deepl($content, $source_lang, $target_lang);
            default:
                throw new \Exception(__('Invalid translation service', 'piper-privacy'));
        }
    }

    /**
     * Translate using Google Cloud Translation
     *
     * @param array  $content     Content to translate
     * @param string $source_lang Source language
     * @param string $target_lang Target language
     * @return array Translated content
     */
    private function translate_with_google($content, $source_lang, $target_lang) {
        $api_key = get_option('piper_privacy_translation_api_key');
        $translations = [];

        foreach ($content as $key => $text) {
            $response = wp_remote_post('https://translation.googleapis.com/language/translate/v2', [
                'body' => [
                    'q' => $text,
                    'source' => substr($source_lang, 0, 2),
                    'target' => substr($target_lang, 0, 2),
                    'key' => $api_key,
                ],
            ]);

            if (is_wp_error($response)) {
                throw new \Exception($response->get_error_message());
            }

            $body = json_decode(wp_remote_retrieve_body($response), true);
            if (isset($body['error'])) {
                throw new \Exception($body['error']['message']);
            }

            $translations[$key] = $body['data']['translations'][0]['translatedText'];
        }

        return $translations;
    }

    /**
     * Translate using DeepL
     *
     * @param array  $content     Content to translate
     * @param string $source_lang Source language
     * @param string $target_lang Target language
     * @return array Translated content
     */
    private function translate_with_deepl($content, $source_lang, $target_lang) {
        $api_key = get_option('piper_privacy_translation_api_key');
        $translations = [];

        foreach ($content as $key => $text) {
            $response = wp_remote_post('https://api-free.deepl.com/v2/translate', [
                'body' => [
                    'text' => $text,
                    'source_lang' => substr($source_lang, 0, 2),
                    'target_lang' => substr($target_lang, 0, 2),
                    'auth_key' => $api_key,
                ],
            ]);

            if (is_wp_error($response)) {
                throw new \Exception($response->get_error_message());
            }

            $body = json_decode(wp_remote_retrieve_body($response), true);
            if (isset($body['message'])) {
                throw new \Exception($body['message']);
            }

            $translations[$key] = $body['translations'][0]['text'];
        }

        return $translations;
    }

    /**
     * Get current language
     *
     * @return string Language code
     */
    private function get_current_language() {
        // Check URL parameter
        if (isset($_GET['lang'])) {
            $lang = sanitize_key($_GET['lang']);
            if (isset($this->supported_languages[$lang])) {
                return $lang;
            }
        }

        // Check user preference
        $user_lang = get_user_meta(get_current_user_id(), 'preferred_language', true);
        if ($user_lang && isset($this->supported_languages[$user_lang])) {
            return $user_lang;
        }

        // Fall back to default
        return get_option('piper_privacy_default_language');
    }
}
