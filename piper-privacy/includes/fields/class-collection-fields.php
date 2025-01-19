<?php
declare(strict_types=1);

namespace PiperPrivacy\Includes\Fields;

/**
 * Collection Fields Registration
 * 
 * Registers MetaBox fields for privacy collections
 */
class CollectionFields {
    /**
     * Register fields
     */
    public function register() {
        add_filter('rwmb_meta_boxes', [$this, 'register_fields']);
    }

    /**
     * Register collection fields
     *
     * @param array $meta_boxes Existing meta boxes
     * @return array Modified meta boxes
     */
    public function register_fields($meta_boxes) {
        $meta_boxes[] = [
            'title' => __('Collection Details', 'piper-privacy'),
            'id' => 'privacy_collection_details',
            'post_types' => ['privacy_collection'],
            'context' => 'normal',
            'priority' => 'high',
            'fields' => [
                // Basic Collection Fields
                [
                    'name' => __('Collection Purpose', 'piper-privacy'),
                    'id' => 'collection_purpose',
                    'type' => 'textarea',
                    'desc' => __('Describe the purpose for collecting this information', 'piper-privacy'),
                    'required' => true,
                    'rows' => 4,
                ],
                [
                    'name' => __('Data Elements', 'piper-privacy'),
                    'id' => 'data_elements',
                    'type' => 'text_list',
                    'clone' => true,
                    'desc' => __('List the data elements being collected', 'piper-privacy'),
                    'required' => true,
                ],
                [
                    'name' => __('Data Sources', 'piper-privacy'),
                    'id' => 'data_sources',
                    'type' => 'text_list',
                    'clone' => true,
                    'desc' => __('List the sources of the data', 'piper-privacy'),
                ],
                [
                    'name' => __('Retention Period', 'piper-privacy'),
                    'id' => 'retention_period',
                    'type' => 'select',
                    'options' => [
                        '12_months' => __('12 Months', 'piper-privacy'),
                        '24_months' => __('24 Months', 'piper-privacy'),
                        '36_months' => __('36 Months', 'piper-privacy'),
                        '60_months' => __('60 Months', 'piper-privacy'),
                        'permanent' => __('Permanent', 'piper-privacy'),
                    ],
                    'required' => true,
                ],
                [
                    'name' => __('Legal Authority', 'piper-privacy'),
                    'id' => 'legal_authority',
                    'type' => 'select',
                    'options' => [
                        'consent' => __('Consent', 'piper-privacy'),
                        'contract' => __('Contract', 'piper-privacy'),
                        'legal_obligation' => __('Legal Obligation', 'piper-privacy'),
                        'vital_interests' => __('Vital Interests', 'piper-privacy'),
                        'public_task' => __('Public Task', 'piper-privacy'),
                        'legitimate_interests' => __('Legitimate Interests', 'piper-privacy'),
                    ],
                    'required' => true,
                ],
                // Sharing Parties Group
                [
                    'name' => __('Sharing Parties', 'piper-privacy'),
                    'id' => 'sharing_parties',
                    'type' => 'group',
                    'clone' => true,
                    'collapsible' => true,
                    'group_title' => ['field' => 'party_name'],
                    'fields' => [
                        [
                            'name' => __('Party Name', 'piper-privacy'),
                            'id' => 'party_name',
                            'type' => 'text',
                            'required' => true,
                        ],
                        [
                            'name' => __('Purpose', 'piper-privacy'),
                            'id' => 'purpose',
                            'type' => 'textarea',
                            'required' => true,
                        ],
                        [
                            'name' => __('Data Shared', 'piper-privacy'),
                            'id' => 'data_shared',
                            'type' => 'textarea',
                            'required' => true,
                        ],
                        [
                            'name' => __('Sharing Agreement', 'piper-privacy'),
                            'id' => 'sharing_agreement',
                            'type' => 'file_advanced',
                            'max_file_uploads' => 1,
                            'mime_type' => 'application/pdf',
                        ],
                    ],
                ],
                // Additional Details
                [
                    'name' => __('Routine Uses', 'piper-privacy'),
                    'id' => 'routine_uses',
                    'type' => 'wysiwyg',
                    'options' => [
                        'textarea_rows' => 4,
                        'teeny' => true,
                    ],
                ],
                [
                    'name' => __('Disclosure Requirements', 'piper-privacy'),
                    'id' => 'disclosure_requirements',
                    'type' => 'wysiwyg',
                    'options' => [
                        'textarea_rows' => 4,
                        'teeny' => true,
                    ],
                ],
                [
                    'name' => __('Privacy Notice URL', 'piper-privacy'),
                    'id' => 'privacy_notice_url',
                    'type' => 'url',
                    'desc' => __('Link to the relevant privacy notice', 'piper-privacy'),
                ],
                [
                    'name' => __('Review Date', 'piper-privacy'),
                    'id' => 'review_date',
                    'type' => 'date',
                    'required' => true,
                    'timestamp' => true,
                ],
            ],
            'validation' => [
                'rules' => [
                    'collection_purpose' => [
                        'required' => true,
                        'minlength' => 50,
                    ],
                    'data_elements' => [
                        'required' => true,
                    ],
                    'retention_period' => [
                        'required' => true,
                    ],
                    'legal_authority' => [
                        'required' => true,
                    ],
                ],
                'messages' => [
                    'collection_purpose' => [
                        'required' => __('Please provide a collection purpose', 'piper-privacy'),
                        'minlength' => __('Collection purpose must be at least 50 characters', 'piper-privacy'),
                    ],
                    'data_elements' => [
                        'required' => __('Please specify at least one data element', 'piper-privacy'),
                    ],
                    'retention_period' => [
                        'required' => __('Please select a retention period', 'piper-privacy'),
                    ],
                    'legal_authority' => [
                        'required' => __('Please select a legal authority', 'piper-privacy'),
                    ],
                ],
            ],
        ];

        return $meta_boxes;
    }
}
