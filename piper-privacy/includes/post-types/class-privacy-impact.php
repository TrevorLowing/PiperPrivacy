<?php
namespace PiperPrivacy\Includes\Post_Types;

/**
 * Privacy Impact Post Type
 *
 * @package    PiperPrivacy
 * @subpackage PiperPrivacy/includes/post-types
 */
class Privacy_Impact {
    /**
     * Post type name.
     *
     * @var string
     */
    const POST_TYPE = 'privacy_impact';

    /**
     * Register the post type.
     */
    public static function register() {
        $labels = [
            'name'                  => _x('Privacy Impact Assessments', 'Post type general name', 'piper-privacy'),
            'singular_name'         => _x('Privacy Impact Assessment', 'Post type singular name', 'piper-privacy'),
            'menu_name'            => _x('Impact Assessments', 'Admin Menu text', 'piper-privacy'),
            'add_new'              => __('Add New', 'piper-privacy'),
            'add_new_item'         => __('Add New Impact Assessment', 'piper-privacy'),
            'edit_item'            => __('Edit Impact Assessment', 'piper-privacy'),
            'new_item'             => __('New Impact Assessment', 'piper-privacy'),
            'view_item'            => __('View Impact Assessment', 'piper-privacy'),
            'search_items'         => __('Search Impact Assessments', 'piper-privacy'),
            'not_found'            => __('No impact assessments found', 'piper-privacy'),
            'not_found_in_trash'   => __('No impact assessments found in Trash', 'piper-privacy'),
            'parent_item_colon'    => __('Parent Impact Assessment:', 'piper-privacy'),
            'all_items'            => __('All Impact Assessments', 'piper-privacy'),
        ];

        $args = [
            'labels'             => $labels,
            'public'            => true,
            'publicly_queryable' => true,
            'show_ui'           => true,
            'show_in_menu'      => 'piper-privacy',
            'query_var'         => true,
            'rewrite'           => ['slug' => 'privacy-impact'],
            'capability_type'   => ['privacy_impact', 'privacy_impacts'],
            'map_meta_cap'      => true,
            'has_archive'       => true,
            'hierarchical'      => false,
            'supports'          => [
                'title',
                'editor',
                'author',
                'excerpt',
                'revisions',
                'custom-fields'
            ],
            'show_in_rest'      => true,
            'menu_icon'         => 'dashicons-shield',
        ];

        register_post_type(self::POST_TYPE, $args);

        // Register custom fields
        if (function_exists('register_field_group')) {
            $fields = [
                [
                    'key' => 'field_impact_scope',
                    'label' => 'Assessment Scope',
                    'name' => 'impact_scope',
                    'type' => 'textarea',
                    'instructions' => 'Define the scope of this privacy impact assessment. Include the systems, processes, or projects being assessed.',
                    'placeholder' => 'e.g., "This assessment covers the new customer data collection process including registration, profile updates, and data sharing with third-party services."',
                    'required' => 1,
                ],
                [
                    'key' => 'field_data_flows',
                    'label' => 'Data Flows',
                    'name' => 'data_flows',
                    'type' => 'repeater',
                    'instructions' => 'Document how personal data moves through your system. Include collection points, processing steps, storage locations, and data sharing.',
                    'button_label' => 'Add Data Flow',
                    'sub_fields' => [
                        [
                            'key' => 'field_flow_name',
                            'label' => 'Flow Name',
                            'name' => 'flow_name',
                            'type' => 'text',
                            'instructions' => 'Give this data flow a descriptive name',
                            'placeholder' => 'e.g., "User Registration Flow"',
                            'required' => 1,
                        ],
                        [
                            'key' => 'field_flow_description',
                            'label' => 'Description',
                            'name' => 'flow_description',
                            'type' => 'textarea',
                            'instructions' => 'Describe how data moves through this flow, including all processing steps',
                            'placeholder' => 'e.g., "1. User submits registration form\n2. Data validated and encrypted\n3. Stored in user database\n4. Confirmation email sent"',
                        ],
                        [
                            'key' => 'field_data_types',
                            'label' => 'Data Types',
                            'name' => 'data_types',
                            'type' => 'checkbox',
                            'instructions' => 'Select all types of data involved in this flow',
                            'choices' => [
                                'personal' => 'Personal Data (e.g., name, email, address)',
                                'sensitive' => 'Sensitive Data (e.g., health, religion, biometrics)',
                                'financial' => 'Financial Data (e.g., payment info, account details)',
                                'health' => 'Health Data (e.g., medical history, conditions)',
                            ],
                        ],
                    ],
                ],
                [
                    'key' => 'field_risk_assessment',
                    'label' => 'Risk Assessment',
                    'name' => 'risk_assessment',
                    'type' => 'repeater',
                    'instructions' => 'Identify and assess potential privacy risks. Consider both the likelihood of occurrence and potential impact.',
                    'button_label' => 'Add Risk Assessment',
                    'sub_fields' => [
                        [
                            'key' => 'field_risk_name',
                            'label' => 'Risk',
                            'name' => 'risk_name',
                            'type' => 'text',
                            'instructions' => 'Describe the specific privacy risk',
                            'placeholder' => 'e.g., "Unauthorized access to sensitive user data"',
                            'required' => 1,
                        ],
                        [
                            'key' => 'field_likelihood',
                            'label' => 'Likelihood',
                            'name' => 'likelihood',
                            'type' => 'select',
                            'instructions' => 'How likely is this risk to occur?',
                            'choices' => [
                                'low' => 'Low - Unlikely to occur with current controls',
                                'medium' => 'Medium - Possible to occur despite controls',
                                'high' => 'High - Likely to occur without additional measures',
                            ],
                            'required' => 1,
                        ],
                        [
                            'key' => 'field_impact',
                            'label' => 'Impact',
                            'name' => 'impact',
                            'type' => 'select',
                            'instructions' => 'How severe would the impact be if this risk occurred?',
                            'choices' => [
                                'low' => 'Low - Minor inconvenience or limited data exposure',
                                'medium' => 'Medium - Significant data exposure or regulatory issues',
                                'high' => 'High - Severe privacy breach or major compliance violation',
                            ],
                            'required' => 1,
                        ],
                        [
                            'key' => 'field_mitigation',
                            'label' => 'Mitigation Measures',
                            'name' => 'mitigation',
                            'type' => 'textarea',
                            'instructions' => 'Describe specific measures to reduce or eliminate this risk',
                            'placeholder' => 'e.g., "1. Implement role-based access control\n2. Enable audit logging\n3. Regular security training"',
                        ],
                    ],
                ],
                [
                    'key' => 'field_recommendations',
                    'label' => 'Recommendations',
                    'name' => 'recommendations',
                    'type' => 'textarea',
                    'instructions' => 'Provide overall recommendations based on the assessment. Include required changes, timeline, and resource needs.',
                    'placeholder' => 'e.g., "1. Implement encryption at rest (Priority: High)\n2. Update privacy policy (Priority: Medium)\n3. Conduct staff training (Priority: Medium)"',
                ],
            ];

            register_field_group([
                'id' => 'acf_impact_details',
                'title' => 'Impact Assessment Details',
                'fields' => $fields,
                'location' => [
                    [
                        [
                            'param' => 'post_type',
                            'operator' => '==',
                            'value' => self::POST_TYPE,
                        ],
                    ],
                ],
            ]);
        }
    }

    /**
     * Register taxonomies for the privacy impact assessment post type.
     */
    private static function register_taxonomies() {
        // Status taxonomy
        register_taxonomy('privacy_impact_status', [self::POST_TYPE], [
            'labels' => [
                'name'              => _x('Statuses', 'taxonomy general name', 'piper-privacy'),
                'singular_name'     => _x('Status', 'taxonomy singular name', 'piper-privacy'),
                'menu_name'         => __('Statuses', 'piper-privacy'),
                'all_items'         => __('All Statuses', 'piper-privacy'),
                'edit_item'         => __('Edit Status', 'piper-privacy'),
                'view_item'         => __('View Status', 'piper-privacy'),
                'update_item'       => __('Update Status', 'piper-privacy'),
                'add_new_item'      => __('Add New Status', 'piper-privacy'),
                'new_item_name'     => __('New Status Name', 'piper-privacy'),
                'search_items'      => __('Search Statuses', 'piper-privacy'),
            ],
            'hierarchical'      => true,
            'public'            => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'rest_base'         => 'privacy-impact-statuses',
            'query_var'         => true,
            'capabilities'      => [
                'manage_terms' => 'manage_privacy_impacts',
                'edit_terms'   => 'manage_privacy_impacts',
                'delete_terms' => 'manage_privacy_impacts',
                'assign_terms' => 'edit_privacy_impacts',
            ],
        ]);

        // Risk Level taxonomy
        register_taxonomy('privacy_impact_risk', [self::POST_TYPE], [
            'labels' => [
                'name'              => _x('Risk Levels', 'taxonomy general name', 'piper-privacy'),
                'singular_name'     => _x('Risk Level', 'taxonomy singular name', 'piper-privacy'),
                'menu_name'         => __('Risk Levels', 'piper-privacy'),
                'all_items'         => __('All Risk Levels', 'piper-privacy'),
                'edit_item'         => __('Edit Risk Level', 'piper-privacy'),
                'view_item'         => __('View Risk Level', 'piper-privacy'),
                'update_item'       => __('Update Risk Level', 'piper-privacy'),
                'add_new_item'      => __('Add New Risk Level', 'piper-privacy'),
                'new_item_name'     => __('New Risk Level Name', 'piper-privacy'),
                'search_items'      => __('Search Risk Levels', 'piper-privacy'),
            ],
            'hierarchical'      => true,
            'public'            => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'rest_base'         => 'privacy-impact-risks',
            'query_var'         => true,
            'capabilities'      => [
                'manage_terms' => 'manage_privacy_impacts',
                'edit_terms'   => 'manage_privacy_impacts',
                'delete_terms' => 'manage_privacy_impacts',
                'assign_terms' => 'edit_privacy_impacts',
            ],
        ]);

        // Control Category taxonomy
        register_taxonomy('privacy_impact_control', [self::POST_TYPE], [
            'labels' => [
                'name'              => _x('Control Categories', 'taxonomy general name', 'piper-privacy'),
                'singular_name'     => _x('Control Category', 'taxonomy singular name', 'piper-privacy'),
                'menu_name'         => __('Control Categories', 'piper-privacy'),
                'all_items'         => __('All Control Categories', 'piper-privacy'),
                'edit_item'         => __('Edit Control Category', 'piper-privacy'),
                'view_item'         => __('View Control Category', 'piper-privacy'),
                'update_item'       => __('Update Control Category', 'piper-privacy'),
                'add_new_item'      => __('Add New Control Category', 'piper-privacy'),
                'new_item_name'     => __('New Control Category Name', 'piper-privacy'),
                'search_items'      => __('Search Control Categories', 'piper-privacy'),
            ],
            'hierarchical'      => true,
            'public'            => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'rest_base'         => 'privacy-impact-controls',
            'query_var'         => true,
            'capabilities'      => [
                'manage_terms' => 'manage_privacy_impacts',
                'edit_terms'   => 'manage_privacy_impacts',
                'delete_terms' => 'manage_privacy_impacts',
                'assign_terms' => 'edit_privacy_impacts',
            ],
        ]);
    }
}
