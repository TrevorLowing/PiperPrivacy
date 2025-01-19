<?php
namespace PiperPrivacy\Includes\Integrations;

/**
 * Fluent Forms Integration
 *
 * @package    PiperPrivacy
 * @subpackage PiperPrivacy/includes/integrations
 */
class FluentFormsIntegration {
    /**
     * Register Fluent Forms
     */
    public function register_forms() {
        // Register forms only if Fluent Forms is active
        if (!defined('FLUENTFORM')) {
            return;
        }

        add_action('fluentform/loaded', [$this, 'register_custom_forms']);
        add_action('fluentform/submission_inserted', [$this, 'handle_form_submission'], 10, 3);
    }

    /**
     * Register custom forms with Fluent Forms
     */
    public function register_custom_forms() {
        // Privacy Collection Form
        $collection_form = [
            'title' => 'Privacy Collection Form',
            'form_fields' => json_encode([
                'fields' => [
                    [
                        'type' => 'text',
                        'label' => 'Collection Title',
                        'name' => 'collection_title',
                        'required' => true,
                    ],
                    [
                        'type' => 'textarea',
                        'label' => 'Collection Purpose',
                        'name' => 'collection_purpose',
                        'required' => true,
                    ],
                    [
                        'type' => 'textarea',
                        'label' => 'Data Elements',
                        'name' => 'data_elements',
                        'required' => true,
                    ],
                    [
                        'type' => 'select',
                        'label' => 'Collection Method',
                        'name' => 'collection_method',
                        'options' => [
                            'direct' => 'Direct from Individual',
                            'indirect' => 'Indirect Collection',
                            'automated' => 'Automated Collection',
                        ],
                        'required' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => 'Retention Period',
                        'name' => 'retention_period',
                        'required' => true,
                    ],
                ],
            ]),
        ];

        // Privacy Threshold Form
        $threshold_form = [
            'title' => 'Privacy Threshold Form',
            'form_fields' => json_encode([
                'fields' => [
                    [
                        'type' => 'text',
                        'label' => 'System Name',
                        'name' => 'system_name',
                        'required' => true,
                    ],
                    [
                        'type' => 'textarea',
                        'label' => 'System Description',
                        'name' => 'system_description',
                        'required' => true,
                    ],
                    [
                        'type' => 'checkbox',
                        'label' => 'PII Collection',
                        'name' => 'pii_collection',
                        'options' => [
                            'yes' => 'This system collects or processes PII',
                        ],
                    ],
                    [
                        'type' => 'checkbox',
                        'label' => 'PII Categories',
                        'name' => 'pii_categories',
                        'options' => [
                            'contact' => 'Contact Information',
                            'financial' => 'Financial Information',
                            'medical' => 'Medical Information',
                            'biometric' => 'Biometric Data',
                            'behavioral' => 'Behavioral Data',
                        ],
                    ],
                ],
            ]),
        ];

        // Privacy Impact Form
        $impact_form = [
            'title' => 'Privacy Impact Form',
            'form_fields' => json_encode([
                'fields' => [
                    [
                        'type' => 'text',
                        'label' => 'Assessment Title',
                        'name' => 'assessment_title',
                        'required' => true,
                    ],
                    [
                        'type' => 'textarea',
                        'label' => 'System Overview',
                        'name' => 'system_overview',
                        'required' => true,
                    ],
                    [
                        'type' => 'textarea',
                        'label' => 'Data Flow',
                        'name' => 'data_flow',
                        'required' => true,
                    ],
                    [
                        'type' => 'textarea',
                        'label' => 'Privacy Risks',
                        'name' => 'privacy_risks',
                        'required' => true,
                    ],
                    [
                        'type' => 'textarea',
                        'label' => 'Mitigation Measures',
                        'name' => 'mitigation_measures',
                        'required' => true,
                    ],
                    [
                        'type' => 'textarea',
                        'label' => 'Recommendations',
                        'name' => 'recommendations',
                        'required' => true,
                    ],
                ],
            ]),
        ];

        // Register forms with Fluent Forms
        $this->create_form_if_not_exists($collection_form);
        $this->create_form_if_not_exists($threshold_form);
        $this->create_form_if_not_exists($impact_form);
    }

    /**
     * Create a form if it doesn't already exist
     *
     * @param array $form_data Form configuration data
     */
    private function create_form_if_not_exists($form_data) {
        global $wpdb;

        // Check if form exists
        $existing_form = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}fluentform_forms WHERE title = %s",
            $form_data['title']
        ));

        if (!$existing_form) {
            $wpdb->insert(
                $wpdb->prefix . 'fluentform_forms',
                [
                    'title' => $form_data['title'],
                    'form_fields' => $form_data['form_fields'],
                    'status' => 'published',
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql'),
                ]
            );
        }
    }

    /**
     * Handle form submission
     *
     * @param array $insert_id Form submission ID
     * @param array $form_data Form data
     * @param object $form Form object
     */
    public function handle_form_submission($insert_id, $form_data, $form) {
        $form_title = $form->title;
        $post_type = '';
        $post_data = [];

        switch ($form_title) {
            case 'Privacy Collection Form':
                $post_type = 'privacy_collection';
                $post_data = [
                    'post_title' => sanitize_text_field($form_data['collection_title']),
                    'meta_input' => [
                        'collection_purpose' => sanitize_textarea_field($form_data['collection_purpose']),
                        'data_elements' => sanitize_textarea_field($form_data['data_elements']),
                        'collection_method' => sanitize_text_field($form_data['collection_method']),
                        'retention_period' => sanitize_text_field($form_data['retention_period']),
                    ],
                ];
                break;

            case 'Privacy Threshold Form':
                $post_type = 'privacy_threshold';
                $post_data = [
                    'post_title' => sanitize_text_field($form_data['system_name']),
                    'meta_input' => [
                        'system_description' => sanitize_textarea_field($form_data['system_description']),
                        'pii_collection' => isset($form_data['pii_collection']) ? 'yes' : 'no',
                        'pii_categories' => isset($form_data['pii_categories']) ? $form_data['pii_categories'] : [],
                    ],
                ];
                break;

            case 'Privacy Impact Form':
                $post_type = 'privacy_impact';
                $post_data = [
                    'post_title' => sanitize_text_field($form_data['assessment_title']),
                    'meta_input' => [
                        'system_overview' => sanitize_textarea_field($form_data['system_overview']),
                        'data_flow' => sanitize_textarea_field($form_data['data_flow']),
                        'privacy_risks' => sanitize_textarea_field($form_data['privacy_risks']),
                        'mitigation_measures' => sanitize_textarea_field($form_data['mitigation_measures']),
                        'recommendations' => sanitize_textarea_field($form_data['recommendations']),
                    ],
                ];
                break;
        }

        if ($post_type && $post_data) {
            $post_data = array_merge($post_data, [
                'post_type' => $post_type,
                'post_status' => 'draft',
                'post_author' => get_current_user_id(),
            ]);

            wp_insert_post($post_data);
        }
    }
}
