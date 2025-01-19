<?php
namespace PiperPrivacy\Includes\AI;

/**
 * AI Assistant
 * 
 * Handles AI-powered features including document generation and privacy analysis
 *
 * @package    PiperPrivacy
 * @subpackage PiperPrivacy/includes/ai
 */
class AIAssistant {
    /**
     * OpenAI API key
     *
     * @var string
     */
    private $api_key;

    /**
     * Initialize the AI assistant
     */
    public function __construct() {
        $this->api_key = get_option('piper_privacy_openai_api_key');

        add_action('admin_init', [$this, 'register_settings']);
        add_action('add_meta_boxes', [$this, 'add_ai_meta_boxes']);
        add_action('wp_ajax_generate_privacy_document', [$this, 'ajax_generate_privacy_document']);
        add_action('wp_ajax_analyze_privacy_risks', [$this, 'ajax_analyze_privacy_risks']);
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('piper_privacy_options', 'piper_privacy_openai_api_key');
        register_setting('piper_privacy_options', 'piper_privacy_ai_model');
    }

    /**
     * Add AI meta boxes
     */
    public function add_ai_meta_boxes() {
        $post_types = ['privacy_collection', 'privacy_threshold', 'privacy_impact'];
        
        foreach ($post_types as $post_type) {
            add_meta_box(
                'privacy_ai_assistant',
                __('AI Assistant', 'piper-privacy'),
                [$this, 'render_ai_meta_box'],
                $post_type,
                'side',
                'default'
            );
        }
    }

    /**
     * Render AI meta box
     *
     * @param WP_Post $post Post object
     */
    public function render_ai_meta_box($post) {
        ?>
        <div class="ai-assistant-controls">
            <button type="button" class="button generate-document" data-post-id="<?php echo esc_attr($post->ID); ?>">
                <?php _e('Generate Document', 'piper-privacy'); ?>
            </button>
            <button type="button" class="button analyze-risks" data-post-id="<?php echo esc_attr($post->ID); ?>">
                <?php _e('Analyze Privacy Risks', 'piper-privacy'); ?>
            </button>
            <div class="ai-status"></div>
        </div>
        <script>
        jQuery(document).ready(function($) {
            $('.generate-document').on('click', function() {
                var postId = $(this).data('post-id');
                var $status = $(this).closest('.ai-assistant-controls').find('.ai-status');
                
                $status.html('<?php _e('Generating document...', 'piper-privacy'); ?>');
                
                $.post(ajaxurl, {
                    action: 'generate_privacy_document',
                    post_id: postId,
                    nonce: '<?php echo wp_create_nonce('ai_assistant'); ?>'
                }, function(response) {
                    if (response.success) {
                        $status.html(response.data.message);
                    } else {
                        $status.html(response.data.message);
                    }
                });
            });

            $('.analyze-risks').on('click', function() {
                var postId = $(this).data('post-id');
                var $status = $(this).closest('.ai-assistant-controls').find('.ai-status');
                
                $status.html('<?php _e('Analyzing privacy risks...', 'piper-privacy'); ?>');
                
                $.post(ajaxurl, {
                    action: 'analyze_privacy_risks',
                    post_id: postId,
                    nonce: '<?php echo wp_create_nonce('ai_assistant'); ?>'
                }, function(response) {
                    if (response.success) {
                        $status.html(response.data.message);
                    } else {
                        $status.html(response.data.message);
                    }
                });
            });
        });
        </script>
        <?php
    }

    /**
     * Handle document generation AJAX request
     */
    public function ajax_generate_privacy_document() {
        check_ajax_referer('ai_assistant', 'nonce');

        $post_id = intval($_POST['post_id']);
        $post = get_post($post_id);

        if (!$post || !in_array($post->post_type, ['privacy_collection', 'privacy_threshold', 'privacy_impact'])) {
            wp_send_json_error(['message' => __('Invalid post type', 'piper-privacy')]);
        }

        try {
            $document = $this->generate_document($post);
            wp_send_json_success([
                'message' => __('Document generated successfully', 'piper-privacy'),
                'document' => $document,
            ]);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * Handle privacy risk analysis AJAX request
     */
    public function ajax_analyze_privacy_risks() {
        check_ajax_referer('ai_assistant', 'nonce');

        $post_id = intval($_POST['post_id']);
        $post = get_post($post_id);

        if (!$post || !in_array($post->post_type, ['privacy_collection', 'privacy_threshold', 'privacy_impact'])) {
            wp_send_json_error(['message' => __('Invalid post type', 'piper-privacy')]);
        }

        try {
            $analysis = $this->analyze_risks($post);
            wp_send_json_success([
                'message' => __('Risk analysis completed', 'piper-privacy'),
                'analysis' => $analysis,
            ]);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * Generate document using AI
     *
     * @param WP_Post $post Post object
     * @return array Generated document data
     */
    private function generate_document($post) {
        if (!$this->api_key) {
            throw new \Exception(__('OpenAI API key not configured', 'piper-privacy'));
        }

        // Prepare context for the AI
        $context = $this->prepare_document_context($post);

        // Call OpenAI API
        $response = $this->call_openai_api('gpt-4', [
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an expert privacy officer assistant. Generate professional privacy documentation based on the provided context.',
                ],
                [
                    'role' => 'user',
                    'content' => $context,
                ],
            ],
            'temperature' => 0.7,
            'max_tokens' => 2000,
        ]);

        // Process and structure the response
        $document = $this->process_generated_document($response, $post);

        // Save the document
        $this->save_generated_document($document, $post);

        return $document;
    }

    /**
     * Analyze privacy risks using AI
     *
     * @param WP_Post $post Post object
     * @return array Risk analysis data
     */
    private function analyze_risks($post) {
        if (!$this->api_key) {
            throw new \Exception(__('OpenAI API key not configured', 'piper-privacy'));
        }

        // Prepare context for the AI
        $context = $this->prepare_risk_context($post);

        // Call OpenAI API
        $response = $this->call_openai_api('gpt-4', [
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an expert privacy risk analyst. Analyze the privacy risks and provide detailed recommendations based on the provided context.',
                ],
                [
                    'role' => 'user',
                    'content' => $context,
                ],
            ],
            'temperature' => 0.7,
            'max_tokens' => 2000,
        ]);

        // Process and structure the response
        $analysis = $this->process_risk_analysis($response, $post);

        // Save the analysis
        $this->save_risk_analysis($analysis, $post);

        return $analysis;
    }

    /**
     * Prepare document generation context
     *
     * @param WP_Post $post Post object
     * @return string Context for AI
     */
    private function prepare_document_context($post) {
        $context = [];

        // Basic information
        $context[] = "Title: " . $post->post_title;
        $context[] = "Type: " . get_post_type_object($post->post_type)->labels->singular_name;
        $context[] = "Content: " . $post->post_content;

        // Custom fields
        $fields = [];
        $meta_keys = get_post_custom_keys($post->ID);
        if ($meta_keys) {
            foreach ($meta_keys as $key) {
                $fields[$key] = pp_get_field($key, $post->ID);
            }
        }
        if ($fields) {
            foreach ($fields as $key => $value) {
                if (is_string($value)) {
                    $context[] = ucwords(str_replace('_', ' ', $key)) . ": " . $value;
                }
            }
        }

        // Stakeholders
        $stakeholders = get_post_meta($post->ID, '_privacy_stakeholders', true);
        if ($stakeholders) {
            $context[] = "Stakeholders:";
            foreach ($stakeholders as $stakeholder) {
                $user = get_user_by('id', $stakeholder['user_id']);
                $context[] = "- " . $user->display_name . " (" . $stakeholder['role'] . ")";
            }
        }

        return implode("\n\n", $context);
    }

    /**
     * Prepare risk analysis context
     *
     * @param WP_Post $post Post object
     * @return string Context for AI
     */
    private function prepare_risk_context($post) {
        $context = [];

        // Basic information
        $context[] = "Title: " . $post->post_title;
        $context[] = "Type: " . get_post_type_object($post->post_type)->labels->singular_name;
        $context[] = "Content: " . $post->post_content;

        // Data elements
        $data_elements = pp_get_field('data_elements', $post->ID);
        if ($data_elements) {
            $context[] = "Data Elements:";
            foreach ($data_elements as $element) {
                $context[] = "- " . $element;
            }
        }

        // Security controls
        $security_controls = pp_get_group_field('security_controls', $post->ID);
        if ($security_controls) {
            $context[] = "Security Controls:";
            foreach ($security_controls as $control) {
                $context[] = "- " . $control;
            }
        }

        // Data sharing
        $sharing_parties = pp_get_group_field('sharing_parties', $post->ID);
        if ($sharing_parties) {
            $context[] = "Data Sharing:";
            foreach ($sharing_parties as $party) {
                $context[] = "- " . $party;
            }
        }

        return implode("\n\n", $context);
    }

    /**
     * Process generated document
     *
     * @param array   $response OpenAI API response
     * @param WP_Post $post     Post object
     * @return array Processed document
     */
    private function process_generated_document($response, $post) {
        $content = $response['choices'][0]['message']['content'];

        // Parse sections from the content
        $sections = [];
        $current_section = '';
        $current_content = [];

        foreach (explode("\n", $content) as $line) {
            if (preg_match('/^#+\s+(.+)$/', $line, $matches)) {
                if ($current_section) {
                    $sections[$current_section] = implode("\n", $current_content);
                }
                $current_section = $matches[1];
                $current_content = [];
            } else {
                $current_content[] = $line;
            }
        }

        if ($current_section) {
            $sections[$current_section] = implode("\n", $current_content);
        }

        return [
            'post_id' => $post->ID,
            'type' => $post->post_type,
            'sections' => $sections,
            'generated_at' => current_time('mysql'),
        ];
    }

    /**
     * Process risk analysis
     *
     * @param array   $response OpenAI API response
     * @param WP_Post $post     Post object
     * @return array Processed analysis
     */
    private function process_risk_analysis($response, $post) {
        $content = $response['choices'][0]['message']['content'];

        // Parse risks and recommendations
        $risks = [];
        $recommendations = [];
        $current_section = null;
        $current_item = [];

        foreach (explode("\n", $content) as $line) {
            if (preg_match('/^#+\s+Risks\s*$/', $line)) {
                $current_section = 'risks';
            } elseif (preg_match('/^#+\s+Recommendations\s*$/', $line)) {
                $current_section = 'recommendations';
            } elseif (preg_match('/^-\s+(.+)$/', $line, $matches)) {
                if ($current_item) {
                    if ($current_section === 'risks') {
                        $risks[] = $current_item;
                    } elseif ($current_section === 'recommendations') {
                        $recommendations[] = $current_item;
                    }
                }
                $current_item = ['description' => $matches[1], 'details' => []];
            } elseif ($current_item && trim($line)) {
                $current_item['details'][] = $line;
            }
        }

        if ($current_item) {
            if ($current_section === 'risks') {
                $risks[] = $current_item;
            } elseif ($current_section === 'recommendations') {
                $recommendations[] = $current_item;
            }
        }

        return [
            'post_id' => $post->ID,
            'type' => $post->post_type,
            'risks' => $risks,
            'recommendations' => $recommendations,
            'analyzed_at' => current_time('mysql'),
        ];
    }

    /**
     * Save generated document
     *
     * @param array   $document Document data
     * @param WP_Post $post     Post object
     */
    private function save_generated_document($document, $post) {
        update_post_meta($post->ID, '_ai_generated_document', $document);
        
        do_action('piper_privacy_document_generated', 
            $post->ID, 
            'ai-generated', 
            wp_json_encode($document)
        );
    }

    /**
     * Save risk analysis
     *
     * @param array   $analysis Analysis data
     * @param WP_Post $post     Post object
     */
    private function save_risk_analysis($analysis, $post) {
        update_post_meta($post->ID, '_ai_risk_analysis', $analysis);
        
        do_action('piper_privacy_risk_analyzed', 
            $post->ID, 
            wp_json_encode($analysis)
        );
    }

    /**
     * Call OpenAI API
     *
     * @param string $model   Model to use
     * @param array  $payload API payload
     * @return array API response
     */
    private function call_openai_api($model, $payload) {
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json',
            ],
            'body' => wp_json_encode(array_merge(['model' => $model], $payload)),
            'timeout' => 60,
        ]);

        if (is_wp_error($response)) {
            throw new \Exception($response->get_error_message());
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (isset($body['error'])) {
            throw new \Exception($body['error']['message']);
        }

        return $body;
    }
}
