<?php
namespace PiperPrivacy\Includes\Integrations;

/**
 * Fluent Boards Integration
 *
 * @package    PiperPrivacy
 * @subpackage PiperPrivacy/includes/integrations
 */
class FluentBoardsIntegration {
    /**
     * Register workflows
     */
    public function register_workflows() {
        // Register workflows only if Fluent Boards is active
        if (!defined('FLUENTBOARDS')) {
            return;
        }

        add_action('fluentboard/init', [$this, 'register_boards']);
        add_action('fluentboard/card_created', [$this, 'handle_card_creation'], 10, 2);
        add_action('fluentboard/card_moved', [$this, 'handle_card_movement'], 10, 3);
    }

    /**
     * Register custom boards with Fluent Boards
     */
    public function register_boards() {
        // Privacy Collection Workflow
        $collection_workflow = [
            'title' => 'Privacy Collection Workflow',
            'description' => 'Workflow for managing privacy collection lifecycle',
            'columns' => [
                [
                    'title' => 'Draft',
                    'slug' => 'draft',
                ],
                [
                    'title' => 'Under Review',
                    'slug' => 'under_review',
                ],
                [
                    'title' => 'Legal Review',
                    'slug' => 'legal_review',
                ],
                [
                    'title' => 'Privacy Officer Review',
                    'slug' => 'privacy_officer_review',
                ],
                [
                    'title' => 'Approved',
                    'slug' => 'approved',
                ],
                [
                    'title' => 'Retired',
                    'slug' => 'retired',
                ],
            ],
        ];

        // Privacy Threshold Workflow
        $threshold_workflow = [
            'title' => 'Privacy Threshold Workflow',
            'description' => 'Workflow for managing privacy threshold analysis',
            'columns' => [
                [
                    'title' => 'Draft',
                    'slug' => 'draft',
                ],
                [
                    'title' => 'System Owner Review',
                    'slug' => 'system_owner_review',
                ],
                [
                    'title' => 'Privacy Officer Review',
                    'slug' => 'privacy_officer_review',
                ],
                [
                    'title' => 'Legal Review',
                    'slug' => 'legal_review',
                ],
                [
                    'title' => 'Approved',
                    'slug' => 'approved',
                ],
            ],
        ];

        // Privacy Impact Workflow
        $impact_workflow = [
            'title' => 'Privacy Impact Workflow',
            'description' => 'Workflow for managing privacy impact assessments',
            'columns' => [
                [
                    'title' => 'Draft',
                    'slug' => 'draft',
                ],
                [
                    'title' => 'System Owner Review',
                    'slug' => 'system_owner_review',
                ],
                [
                    'title' => 'Privacy Officer Review',
                    'slug' => 'privacy_officer_review',
                ],
                [
                    'title' => 'Legal Review',
                    'slug' => 'legal_review',
                ],
                [
                    'title' => 'ISSO Review',
                    'slug' => 'isso_review',
                ],
                [
                    'title' => 'Final Approval',
                    'slug' => 'final_approval',
                ],
            ],
        ];

        // Register boards with Fluent Boards
        $this->create_board_if_not_exists($collection_workflow);
        $this->create_board_if_not_exists($threshold_workflow);
        $this->create_board_if_not_exists($impact_workflow);
    }

    /**
     * Create a board if it doesn't already exist
     *
     * @param array $board_data Board configuration data
     */
    private function create_board_if_not_exists($board_data) {
        global $wpdb;

        // Check if board exists
        $existing_board = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}fluentboard_boards WHERE title = %s",
            $board_data['title']
        ));

        if (!$existing_board) {
            // Create board
            $wpdb->insert(
                $wpdb->prefix . 'fluentboard_boards',
                [
                    'title' => $board_data['title'],
                    'description' => $board_data['description'],
                    'status' => 'published',
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql'),
                ]
            );

            $board_id = $wpdb->insert_id;

            // Create columns
            foreach ($board_data['columns'] as $order => $column) {
                $wpdb->insert(
                    $wpdb->prefix . 'fluentboard_columns',
                    [
                        'board_id' => $board_id,
                        'title' => $column['title'],
                        'slug' => $column['slug'],
                        'order' => $order,
                        'created_at' => current_time('mysql'),
                        'updated_at' => current_time('mysql'),
                    ]
                );
            }
        }
    }

    /**
     * Handle card creation
     *
     * @param int $card_id Card ID
     * @param array $card_data Card data
     */
    public function handle_card_creation($card_id, $card_data) {
        if (isset($card_data['post_id'])) {
            $post_id = $card_data['post_id'];
            $post_type = get_post_type($post_id);

            // Update post status based on initial column
            switch ($post_type) {
                case 'privacy_collection':
                    wp_set_object_terms($post_id, 'draft', 'privacy_collection_status');
                    break;
                case 'privacy_threshold':
                    wp_set_object_terms($post_id, 'draft', 'privacy_threshold_status');
                    break;
                case 'privacy_impact':
                    wp_set_object_terms($post_id, 'draft', 'privacy_impact_status');
                    break;
            }

            // Log the workflow history
            $this->log_workflow_history($post_id, '', 'draft', get_current_user_id(), '');
        }
    }

    /**
     * Handle card movement between columns
     *
     * @param int $card_id Card ID
     * @param string $from_column Previous column slug
     * @param string $to_column New column slug
     */
    public function handle_card_movement($card_id, $from_column, $to_column) {
        global $wpdb;

        $card = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}fluentboard_cards WHERE id = %d",
            $card_id
        ));

        if ($card && isset($card->post_id)) {
            $post_id = $card->post_id;
            $post_type = get_post_type($post_id);

            // Update post status based on new column
            switch ($post_type) {
                case 'privacy_collection':
                    wp_set_object_terms($post_id, $to_column, 'privacy_collection_status');
                    break;
                case 'privacy_threshold':
                    wp_set_object_terms($post_id, $to_column, 'privacy_threshold_status');
                    break;
                case 'privacy_impact':
                    wp_set_object_terms($post_id, $to_column, 'privacy_impact_status');
                    break;
            }

            // Log the workflow history
            $this->log_workflow_history($post_id, $from_column, $to_column, get_current_user_id(), '');
        }
    }

    /**
     * Log workflow history
     *
     * @param int $post_id Post ID
     * @param string $from_stage Previous stage
     * @param string $to_stage New stage
     * @param int $user_id User ID
     * @param string $comments Comments
     */
    private function log_workflow_history($post_id, $from_stage, $to_stage, $user_id, $comments) {
        global $wpdb;

        $wpdb->insert(
            $wpdb->prefix . 'piper_privacy_workflow_history',
            [
                'workflow_id' => 0, // We don't have workflow IDs in this implementation
                'object_id' => $post_id,
                'from_stage' => $from_stage,
                'to_stage' => $to_stage,
                'user_id' => $user_id,
                'comments' => $comments,
                'created_at' => current_time('mysql'),
            ]
        );
    }
}
