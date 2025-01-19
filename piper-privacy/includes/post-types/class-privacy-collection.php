<?php
namespace PiperPrivacy\Includes\Post_Types;

/**
 * Privacy Collection Post Type
 *
 * @package    PiperPrivacy
 * @subpackage PiperPrivacy/includes/post-types
 */
class Privacy_Collection {
    /**
     * Post type name.
     *
     * @var string
     */
    const POST_TYPE = 'privacy_collection';

    /**
     * Register the post type.
     */
    public static function register() {
        error_log('Starting Privacy_Collection registration');
        
        try {
            $labels = [
                'name'                  => _x('Privacy Collections', 'Post type general name', 'piper-privacy'),
                'singular_name'         => _x('Privacy Collection', 'Post type singular name', 'piper-privacy'),
                'menu_name'            => _x('Collections', 'Admin Menu text', 'piper-privacy'),
                'add_new'              => __('Add New', 'piper-privacy'),
                'add_new_item'         => __('Add New Privacy Collection', 'piper-privacy'),
                'edit_item'            => __('Edit Privacy Collection', 'piper-privacy'),
                'new_item'             => __('New Privacy Collection', 'piper-privacy'),
                'view_item'            => __('View Privacy Collection', 'piper-privacy'),
                'search_items'         => __('Search Privacy Collections', 'piper-privacy'),
                'not_found'            => __('No privacy collections found', 'piper-privacy'),
                'not_found_in_trash'   => __('No privacy collections found in Trash', 'piper-privacy'),
                'parent_item_colon'    => __('Parent Privacy Collection:', 'piper-privacy'),
                'all_items'            => __('All Collections', 'piper-privacy'),
            ];

            $args = [
                'labels'             => $labels,
                'public'            => true,
                'publicly_queryable' => true,
                'show_ui'           => true,
                'show_in_menu'      => 'piper-privacy',
                'query_var'         => true,
                'rewrite'           => ['slug' => 'privacy-collection'],
                'capability_type'   => ['privacy_collection', 'privacy_collections'],
                'map_meta_cap'      => true,
                'has_archive'       => true,
                'hierarchical'      => false,
                'supports'          => [
                    'title',
                    'editor',
                    'author',
                    'excerpt',
                    'custom-fields',
                    'revisions'
                ],
                'show_in_rest'      => true,
                'menu_icon'         => 'dashicons-shield',
            ];

            error_log('Registering post type with args: ' . print_r($args, true));
            $result = register_post_type(self::POST_TYPE, $args);
            
            if (is_wp_error($result)) {
                error_log('Error registering post type: ' . $result->get_error_message());
                throw new \Exception('Failed to register post type: ' . $result->get_error_message());
            }
            
            error_log('Privacy_Collection post type registered successfully');
            
            // Register custom fields
            if (function_exists('register_field_group')) {
                $fields = [
                    [
                        'key' => 'field_collection_purpose',
                        'label' => 'Collection Purpose',
                        'name' => 'collection_purpose',
                        'type' => 'textarea',
                        'required' => 1,
                        'instructions' => 'Describe the specific purpose for collecting this personal data. Be clear and detailed about how the data will be used.',
                        'placeholder' => 'e.g., "To process customer orders and provide shipping updates"',
                    ],
                    [
                        'key' => 'field_data_categories',
                        'label' => 'Data Categories',
                        'name' => 'data_categories',
                        'type' => 'checkbox',
                        'instructions' => 'Select all categories of personal data that will be collected. Consider carefully as this affects compliance requirements.',
                        'choices' => [
                            'personal' => 'Personal Data (e.g., name, email, phone)',
                            'sensitive' => 'Sensitive Data (e.g., race, religion, political views)',
                            'financial' => 'Financial Data (e.g., credit card, bank details)',
                            'health' => 'Health Data (e.g., medical history, conditions)',
                            'biometric' => 'Biometric Data (e.g., fingerprints, facial recognition)',
                            'location' => 'Location Data (e.g., GPS coordinates, address)',
                        ],
                    ],
                    [
                        'key' => 'field_retention_period',
                        'label' => 'Retention Period',
                        'name' => 'retention_period',
                        'type' => 'number',
                        'required' => 1,
                        'instructions' => 'Specify how long this data will be kept (in months). Consider legal requirements and business needs when setting this period.',
                        'placeholder' => '24',
                        'min' => 1,
                        'append' => 'months',
                    ],
                    [
                        'key' => 'field_legal_basis',
                        'label' => 'Legal Basis',
                        'name' => 'legal_basis',
                        'type' => 'select',
                        'instructions' => 'Select the legal basis under GDPR for collecting this data. Each basis has specific requirements and obligations.',
                        'choices' => [
                            'consent' => 'Consent - Individual has given clear consent for specific purpose',
                            'contract' => 'Contract - Processing is necessary for a contract with the individual',
                            'legal_obligation' => 'Legal Obligation - Processing is necessary to comply with the law',
                            'vital_interests' => 'Vital Interests - Processing is necessary to protect someone\'s life',
                            'public_task' => 'Public Task - Processing is necessary to perform a task in the public interest',
                            'legitimate_interests' => 'Legitimate Interests - Processing is necessary for legitimate interests',
                        ],
                        'required' => 1,
                    ],
                    [
                        'key' => 'field_data_minimization',
                        'label' => 'Data Minimization Assessment',
                        'name' => 'data_minimization',
                        'type' => 'textarea',
                        'instructions' => 'Explain how you ensure only necessary data is collected. Describe any steps taken to minimize data collection.',
                        'placeholder' => 'e.g., "We only collect essential contact information needed for shipping, excluding optional fields"',
                        'required' => 1,
                    ],
                    [
                        'key' => 'field_security_measures',
                        'label' => 'Security Measures',
                        'name' => 'security_measures',
                        'type' => 'textarea',
                        'instructions' => 'Detail the security measures in place to protect this data. Include both technical and organizational measures.',
                        'placeholder' => 'e.g., "Data is encrypted at rest and in transit, access is restricted to authorized personnel only"',
                        'required' => 1,
                    ]
                ];

                register_field_group([
                    'id' => 'acf_collection_details',
                    'title' => 'Collection Details',
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
        } catch (\Exception $e) {
            error_log('Error in Privacy_Collection::register(): ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }
}
