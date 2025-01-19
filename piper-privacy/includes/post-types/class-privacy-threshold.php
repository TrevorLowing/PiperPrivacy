<?php
namespace PiperPrivacy\Includes\Post_Types;

/**
 * Privacy Threshold Post Type
 *
 * @package    PiperPrivacy
 * @subpackage PiperPrivacy/includes/post-types
 */
class Privacy_Threshold {
    /**
     * Post type name.
     *
     * @var string
     */
    const POST_TYPE = 'privacy_threshold';

    /**
     * Register the post type.
     */
    public static function register() {
        $labels = [
            'name'                  => _x('Privacy Thresholds', 'Post type general name', 'piper-privacy'),
            'singular_name'         => _x('Privacy Threshold', 'Post type singular name', 'piper-privacy'),
            'menu_name'            => _x('Thresholds', 'Admin Menu text', 'piper-privacy'),
            'add_new'              => __('Add New', 'piper-privacy'),
            'add_new_item'         => __('Add New Privacy Threshold', 'piper-privacy'),
            'edit_item'            => __('Edit Privacy Threshold', 'piper-privacy'),
            'new_item'             => __('New Privacy Threshold', 'piper-privacy'),
            'view_item'            => __('View Privacy Threshold', 'piper-privacy'),
            'search_items'         => __('Search Privacy Thresholds', 'piper-privacy'),
            'not_found'            => __('No privacy thresholds found', 'piper-privacy'),
            'not_found_in_trash'   => __('No privacy thresholds found in Trash', 'piper-privacy'),
            'parent_item_colon'    => __('Parent Privacy Threshold:', 'piper-privacy'),
            'all_items'            => __('All Thresholds', 'piper-privacy'),
        ];

        $args = [
            'labels'             => $labels,
            'public'            => true,
            'publicly_queryable' => true,
            'show_ui'           => true,
            'show_in_menu'      => 'piper-privacy',
            'query_var'         => true,
            'rewrite'           => ['slug' => 'privacy-threshold'],
            'capability_type'   => ['privacy_threshold', 'privacy_thresholds'],
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
                    'key' => 'field_threshold_type',
                    'label' => 'Threshold Type',
                    'name' => 'threshold_type',
                    'type' => 'select',
                    'instructions' => 'Select the type of privacy assessment needed. Each type has different requirements and scope.',
                    'choices' => [
                        'dpia' => 'Data Protection Impact Assessment (DPIA) - Required for high-risk processing under GDPR',
                        'pia' => 'Privacy Impact Assessment (PIA) - General privacy risk assessment',
                        'pta' => 'Privacy Threshold Assessment (PTA) - Initial screening assessment',
                    ],
                    'required' => 1,
                ],
                [
                    'key' => 'field_risk_level',
                    'label' => 'Risk Level',
                    'name' => 'risk_level',
                    'type' => 'select',
                    'instructions' => 'Assess the overall risk level based on the potential impact and likelihood of privacy breaches.',
                    'choices' => [
                        'low' => 'Low - Minimal privacy impact, standard safeguards sufficient',
                        'medium' => 'Medium - Moderate privacy concerns, additional controls needed',
                        'high' => 'High - Significant privacy risks, requires detailed assessment',
                        'critical' => 'Critical - Severe privacy implications, may need to reconsider processing',
                    ],
                    'required' => 1,
                ],
                [
                    'key' => 'field_assessment_criteria',
                    'label' => 'Assessment Criteria',
                    'name' => 'assessment_criteria',
                    'type' => 'repeater',
                    'instructions' => 'Define the criteria used to assess privacy risks. Each criterion should be weighted based on its importance.',
                    'sub_fields' => [
                        [
                            'key' => 'field_criterion',
                            'label' => 'Criterion',
                            'name' => 'criterion',
                            'type' => 'text',
                            'instructions' => 'Enter a specific criterion for privacy assessment (e.g., "Data sensitivity level", "Number of data subjects")',
                            'placeholder' => 'e.g., "Volume of data processed"',
                            'required' => 1,
                        ],
                        [
                            'key' => 'field_weight',
                            'label' => 'Weight',
                            'name' => 'weight',
                            'type' => 'number',
                            'instructions' => 'Assign importance from 1 (least important) to 10 (most important)',
                            'required' => 1,
                            'min' => 1,
                            'max' => 10,
                            'placeholder' => '5',
                        ],
                    ],
                    'button_label' => 'Add Assessment Criterion',
                    'min' => 1,
                ],
                [
                    'key' => 'field_mitigation_measures',
                    'label' => 'Mitigation Measures',
                    'name' => 'mitigation_measures',
                    'type' => 'repeater',
                    'instructions' => 'List specific measures to reduce identified privacy risks.',
                    'sub_fields' => [
                        [
                            'key' => 'field_measure',
                            'label' => 'Measure',
                            'name' => 'measure',
                            'type' => 'textarea',
                            'instructions' => 'Describe the specific measure and how it reduces privacy risk',
                            'placeholder' => 'e.g., "Implement end-to-end encryption for all data transfers"',
                            'required' => 1,
                        ],
                        [
                            'key' => 'field_implementation_status',
                            'label' => 'Implementation Status',
                            'name' => 'implementation_status',
                            'type' => 'select',
                            'instructions' => 'Current status of this mitigation measure',
                            'choices' => [
                                'planned' => 'Planned',
                                'in_progress' => 'In Progress',
                                'implemented' => 'Implemented',
                                'verified' => 'Implemented and Verified',
                            ],
                            'required' => 1,
                        ],
                    ],
                    'button_label' => 'Add Mitigation Measure',
                ],
            ];

            register_field_group([
                'id' => 'acf_threshold_details',
                'title' => 'Threshold Details',
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
     * Register taxonomies for the privacy threshold post type.
     */
    private static function register_taxonomies() {
        // Status taxonomy
        register_taxonomy('privacy_threshold_status', [self::POST_TYPE], [
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
            'rest_base'         => 'privacy-threshold-statuses',
            'query_var'         => true,
            'capabilities'      => [
                'manage_terms' => 'manage_privacy_thresholds',
                'edit_terms'   => 'manage_privacy_thresholds',
                'delete_terms' => 'manage_privacy_thresholds',
                'assign_terms' => 'edit_privacy_thresholds',
            ],
        ]);

        // PII Type taxonomy
        register_taxonomy('privacy_threshold_pii_type', [self::POST_TYPE], [
            'labels' => [
                'name'              => _x('PII Types', 'taxonomy general name', 'piper-privacy'),
                'singular_name'     => _x('PII Type', 'taxonomy singular name', 'piper-privacy'),
                'menu_name'         => __('PII Types', 'piper-privacy'),
                'all_items'         => __('All PII Types', 'piper-privacy'),
                'edit_item'         => __('Edit PII Type', 'piper-privacy'),
                'view_item'         => __('View PII Type', 'piper-privacy'),
                'update_item'       => __('Update PII Type', 'piper-privacy'),
                'add_new_item'      => __('Add New PII Type', 'piper-privacy'),
                'new_item_name'     => __('New PII Type Name', 'piper-privacy'),
                'search_items'      => __('Search PII Types', 'piper-privacy'),
            ],
            'hierarchical'      => true,
            'public'            => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'rest_base'         => 'privacy-threshold-pii-types',
            'query_var'         => true,
            'capabilities'      => [
                'manage_terms' => 'manage_privacy_thresholds',
                'edit_terms'   => 'manage_privacy_thresholds',
                'delete_terms' => 'manage_privacy_thresholds',
                'assign_terms' => 'edit_privacy_thresholds',
            ],
        ]);
    }
}
