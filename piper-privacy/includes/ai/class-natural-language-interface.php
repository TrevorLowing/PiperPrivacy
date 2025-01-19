<?php
namespace PiperPrivacy\Includes\AI;

/**
 * Natural Language Interface
 * 
 * Provides natural language interaction capabilities for privacy management
 *
 * @package    PiperPrivacy
 * @subpackage PiperPrivacy/includes/ai
 */
class NaturalLanguageInterface {
    /**
     * @var AIAssistant
     */
    private $ai_assistant;

    /**
     * Initialize the natural language interface
     */
    public function __construct() {
        $this->ai_assistant = new AIAssistant();

        add_action('admin_menu', [$this, 'add_chat_interface_page']);
        add_action('wp_ajax_privacy_chat_query', [$this, 'handle_chat_query']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    /**
     * Add chat interface page
     */
    public function add_chat_interface_page() {
        add_submenu_page(
            'privacy-dashboard',
            __('Privacy Assistant', 'piper-privacy'),
            __('Privacy Assistant', 'piper-privacy'),
            'manage_privacy',
            'privacy-assistant',
            [$this, 'render_chat_interface']
        );
    }

    /**
     * Enqueue necessary scripts
     *
     * @param string $hook_suffix The current admin page
     */
    public function enqueue_scripts($hook_suffix) {
        if ('privacy-dashboard_page_privacy-assistant' !== $hook_suffix) {
            return;
        }

        wp_enqueue_style(
            'privacy-chat-interface',
            PIPER_PRIVACY_URL . 'admin/css/chat-interface.css',
            [],
            PIPER_PRIVACY_VERSION
        );

        wp_enqueue_script(
            'privacy-chat-interface',
            PIPER_PRIVACY_URL . 'admin/js/chat-interface.js',
            ['jquery'],
            PIPER_PRIVACY_VERSION,
            true
        );

        wp_localize_script('privacy-chat-interface', 'privacyChatSettings', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('privacy_chat'),
            'i18n' => [
                'placeholder' => __('Ask me anything about privacy management...', 'piper-privacy'),
                'sending' => __('Sending...', 'piper-privacy'),
                'error' => __('Error occurred. Please try again.', 'piper-privacy'),
            ],
        ]);
    }

    /**
     * Render chat interface
     */
    public function render_chat_interface() {
        ?>
        <div class="wrap">
            <h1><?php _e('Privacy Assistant', 'piper-privacy'); ?></h1>
            
            <div class="privacy-chat-interface">
                <div class="chat-messages" id="chatMessages"></div>
                
                <div class="chat-input-area">
                    <textarea 
                        id="chatInput" 
                        placeholder="<?php esc_attr_e('Ask me anything about privacy management...', 'piper-privacy'); ?>"
                    ></textarea>
                    <button type="button" class="button button-primary" id="sendMessage">
                        <?php _e('Send', 'piper-privacy'); ?>
                    </button>
                </div>

                <div class="chat-suggestions">
                    <h3><?php _e('Suggested Questions', 'piper-privacy'); ?></h3>
                    <ul>
                        <li>
                            <a href="#" class="suggestion">
                                <?php _e('How do I create a new privacy collection?', 'piper-privacy'); ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="suggestion">
                                <?php _e('What are the required fields for a PTA?', 'piper-privacy'); ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="suggestion">
                                <?php _e('Show me privacy collections needing review', 'piper-privacy'); ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="suggestion">
                                <?php _e('Generate a privacy notice template', 'piper-privacy'); ?>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <style>
            .privacy-chat-interface {
                max-width: 800px;
                margin: 20px 0;
                background: #fff;
                border: 1px solid #ccd0d4;
                border-radius: 4px;
            }

            .chat-messages {
                height: 400px;
                padding: 20px;
                overflow-y: auto;
                border-bottom: 1px solid #ccd0d4;
            }

            .chat-message {
                margin-bottom: 15px;
                display: flex;
                align-items: flex-start;
            }

            .chat-message.user {
                flex-direction: row-reverse;
            }

            .message-content {
                max-width: 70%;
                padding: 10px 15px;
                border-radius: 15px;
                background: #f0f0f1;
            }

            .chat-message.user .message-content {
                background: #2271b1;
                color: #fff;
            }

            .chat-input-area {
                padding: 20px;
                display: flex;
                gap: 10px;
            }

            #chatInput {
                flex: 1;
                min-height: 60px;
                padding: 10px;
                border: 1px solid #ccd0d4;
                border-radius: 4px;
            }

            .chat-suggestions {
                padding: 20px;
                background: #f0f0f1;
                border-top: 1px solid #ccd0d4;
            }

            .chat-suggestions h3 {
                margin-top: 0;
            }

            .suggestion {
                text-decoration: none;
                color: #2271b1;
            }

            .suggestion:hover {
                text-decoration: underline;
            }
        </style>
        <?php
    }

    /**
     * Handle chat query AJAX request
     */
    public function handle_chat_query() {
        check_ajax_referer('privacy_chat', 'nonce');

        $query = sanitize_text_field($_POST['query']);
        
        try {
            $response = $this->process_query($query);
            wp_send_json_success(['response' => $response]);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * Process natural language query
     *
     * @param string $query User query
     * @return string Response
     */
    private function process_query($query) {
        // Prepare context for the query
        $context = $this->prepare_query_context($query);

        // Call OpenAI API through AI Assistant
        $response = $this->ai_assistant->call_openai_api('gpt-4', [
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a helpful privacy management assistant. Provide clear, accurate responses about privacy management processes, requirements, and best practices.',
                ],
                [
                    'role' => 'user',
                    'content' => $context . "\n\nQuery: " . $query,
                ],
            ],
            'temperature' => 0.7,
            'max_tokens' => 1000,
        ]);

        // Process and enhance the response
        return $this->enhance_response($response['choices'][0]['message']['content'], $query);
    }

    /**
     * Prepare context for query processing
     *
     * @param string $query User query
     * @return string Context
     */
    private function prepare_query_context($query) {
        $context = [];

        // Add system information
        $context[] = "Current WordPress Version: " . get_bloginfo('version');
        $context[] = "Plugin Version: " . PIPER_PRIVACY_VERSION;

        // Add privacy statistics
        $stats = $this->get_privacy_statistics();
        $context[] = "Privacy Collections: " . $stats['collections'];
        $context[] = "Privacy Thresholds: " . $stats['thresholds'];
        $context[] = "Privacy Impact Assessments: " . $stats['impacts'];

        // Add recent activity
        $recent = $this->get_recent_activity();
        if (!empty($recent)) {
            $context[] = "Recent Activity:";
            foreach ($recent as $activity) {
                $context[] = "- " . $activity;
            }
        }

        return implode("\n", $context);
    }

    /**
     * Get privacy management statistics
     *
     * @return array Statistics
     */
    private function get_privacy_statistics() {
        return [
            'collections' => wp_count_posts('privacy_collection')->publish,
            'thresholds' => wp_count_posts('privacy_threshold')->publish,
            'impacts' => wp_count_posts('privacy_impact')->publish,
        ];
    }

    /**
     * Get recent privacy activity
     *
     * @return array Recent activity
     */
    private function get_recent_activity() {
        global $wpdb;
        
        $activities = [];
        $table_name = $wpdb->prefix . 'privacy_audit_log';
        
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table_name} ORDER BY created_at DESC LIMIT %d",
                5
            )
        );

        foreach ($results as $result) {
            $post = get_post($result->post_id);
            if ($post) {
                $activities[] = sprintf(
                    '%s: %s (%s)',
                    get_post_type_object($post->post_type)->labels->singular_name,
                    $result->action_details,
                    human_time_diff(strtotime($result->created_at), current_time('timestamp')) . ' ago'
                );
            }
        }

        return $activities;
    }

    /**
     * Enhance AI response with additional information
     *
     * @param string $response Original response
     * @param string $query    Original query
     * @return string Enhanced response
     */
    private function enhance_response($response, $query) {
        // Add relevant links
        $response = $this->add_relevant_links($response);

        // Add action buttons if applicable
        $response = $this->add_action_buttons($response, $query);

        // Add related documentation
        $response = $this->add_related_documentation($response, $query);

        return $response;
    }

    /**
     * Add relevant admin links to response
     *
     * @param string $response Response text
     * @return string Enhanced response
     */
    private function add_relevant_links($response) {
        $links = [
            'privacy collection' => admin_url('post-new.php?post_type=privacy_collection'),
            'privacy threshold' => admin_url('post-new.php?post_type=privacy_threshold'),
            'privacy impact' => admin_url('post-new.php?post_type=privacy_impact'),
            'dashboard' => admin_url('admin.php?page=privacy-dashboard'),
            'settings' => admin_url('admin.php?page=privacy-settings'),
        ];

        foreach ($links as $text => $url) {
            $response = str_replace(
                $text,
                sprintf('<a href="%s">%s</a>', esc_url($url), $text),
                $response
            );
        }

        return $response;
    }

    /**
     * Add action buttons based on query intent
     *
     * @param string $response Response text
     * @param string $query    Original query
     * @return string Enhanced response
     */
    private function add_action_buttons($response, $query) {
        $buttons = [];

        // Create new item
        if (stripos($query, 'create') !== false || stripos($query, 'new') !== false) {
            if (stripos($query, 'collection') !== false) {
                $buttons[] = [
                    'text' => __('Create Privacy Collection', 'piper-privacy'),
                    'url' => admin_url('post-new.php?post_type=privacy_collection'),
                ];
            } elseif (stripos($query, 'threshold') !== false || stripos($query, 'pta') !== false) {
                $buttons[] = [
                    'text' => __('Create Privacy Threshold', 'piper-privacy'),
                    'url' => admin_url('post-new.php?post_type=privacy_threshold'),
                ];
            } elseif (stripos($query, 'impact') !== false || stripos($query, 'pia') !== false) {
                $buttons[] = [
                    'text' => __('Create Privacy Impact', 'piper-privacy'),
                    'url' => admin_url('post-new.php?post_type=privacy_impact'),
                ];
            }
        }

        // View items
        if (stripos($query, 'view') !== false || stripos($query, 'show') !== false || stripos($query, 'list') !== false) {
            if (stripos($query, 'collection') !== false) {
                $buttons[] = [
                    'text' => __('View Collections', 'piper-privacy'),
                    'url' => admin_url('edit.php?post_type=privacy_collection'),
                ];
            } elseif (stripos($query, 'threshold') !== false || stripos($query, 'pta') !== false) {
                $buttons[] = [
                    'text' => __('View Thresholds', 'piper-privacy'),
                    'url' => admin_url('edit.php?post_type=privacy_threshold'),
                ];
            } elseif (stripos($query, 'impact') !== false || stripos($query, 'pia') !== false) {
                $buttons[] = [
                    'text' => __('View Impacts', 'piper-privacy'),
                    'url' => admin_url('edit.php?post_type=privacy_impact'),
                ];
            }
        }

        if (!empty($buttons)) {
            $response .= "\n\n<div class='action-buttons'>";
            foreach ($buttons as $button) {
                $response .= sprintf(
                    '<a href="%s" class="button button-primary">%s</a> ',
                    esc_url($button['url']),
                    esc_html($button['text'])
                );
            }
            $response .= "</div>";
        }

        return $response;
    }

    /**
     * Add related documentation links
     *
     * @param string $response Response text
     * @param string $query    Original query
     * @return string Enhanced response
     */
    private function add_related_documentation($response, $query) {
        $docs = [];

        // Add relevant documentation based on query keywords
        if (stripos($query, 'collection') !== false) {
            $docs[] = [
                'title' => __('Privacy Collection Guide', 'piper-privacy'),
                'url' => admin_url('admin.php?page=privacy-docs&doc=collections'),
            ];
        }

        if (stripos($query, 'threshold') !== false || stripos($query, 'pta') !== false) {
            $docs[] = [
                'title' => __('Privacy Threshold Assessment Guide', 'piper-privacy'),
                'url' => admin_url('admin.php?page=privacy-docs&doc=threshold'),
            ];
        }

        if (stripos($query, 'impact') !== false || stripos($query, 'pia') !== false) {
            $docs[] = [
                'title' => __('Privacy Impact Assessment Guide', 'piper-privacy'),
                'url' => admin_url('admin.php?page=privacy-docs&doc=impact'),
            ];
        }

        if (!empty($docs)) {
            $response .= "\n\n<div class='related-docs'>";
            $response .= "<h4>" . __('Related Documentation', 'piper-privacy') . "</h4><ul>";
            foreach ($docs as $doc) {
                $response .= sprintf(
                    '<li><a href="%s">%s</a></li>',
                    esc_url($doc['url']),
                    esc_html($doc['title'])
                );
            }
            $response .= "</ul></div>";
        }

        return $response;
    }
}
