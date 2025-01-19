<?php
namespace PiperPrivacy\Includes\Documents;

use PiperPrivacy\Includes\Workflow\WorkflowConfig;
use PiperPrivacy\Includes\Helpers\MetaboxHelpers;

/**
 * Document Generator
 * 
 * Handles generation of privacy-related documents
 *
 * @package    PiperPrivacy
 * @subpackage PiperPrivacy/includes/documents
 */
class DocumentGenerator {
    /**
     * @var WorkflowConfig
     */
    private WorkflowConfig $workflow_config;

    /**
     * @var array Error messages
     */
    private array $errors = [];

    /**
     * Initialize the document generator
     */
    public function __construct() {
        $this->workflow_config = new WorkflowConfig();

        add_action('piper_privacy_status_changed', [$this, 'generate_stage_documents'], 10, 3);
        add_action('piper_privacy_workflow_escalated', [$this, 'generate_escalation_report'], 10, 2);
        add_action('admin_notices', [$this, 'display_errors']);
    }

    /**
     * Display any errors that occurred during document generation
     */
    public function display_errors() {
        foreach ($this->errors as $error) {
            echo '<div class="notice notice-error"><p>' . esc_html($error) . '</p></div>';
        }
        $this->errors = [];
    }

    /**
     * Add an error message
     *
     * @param string $message Error message
     */
    private function add_error($message) {
        $this->errors[] = $message;
        error_log(sprintf('[PiperPrivacy] Document Generation Error: %s', $message));
    }

    /**
     * Generate documents for a workflow stage
     *
     * @param int    $post_id     Post ID
     * @param string $old_status  Old status
     * @param string $new_status  New status
     */
    public function generate_stage_documents($post_id, $old_status, $new_status) {
        try {
            $post = get_post($post_id);
            if (!$post) {
                throw new \Exception(sprintf('Post %d not found', $post_id));
            }
            
            switch ($post->post_type) {
                case 'privacy_collection':
                    $this->generate_collection_documents($post, $new_status);
                    break;
                case 'privacy_threshold':
                    $this->generate_threshold_documents($post, $new_status);
                    break;
                case 'privacy_impact':
                    $this->generate_impact_documents($post, $new_status);
                    break;
                default:
                    throw new \Exception(sprintf('Invalid post type: %s', $post->post_type));
            }
        } catch (\Exception $e) {
            $this->add_error($e->getMessage());
        }
    }

    /**
     * Generate collection documents
     *
     * @param WP_Post $post   Post object
     * @param string  $status Current status
     */
    private function generate_collection_documents($post, $status) {
        try {
            switch ($status) {
                case 'approved':
                    $this->generate_collection_summary($post);
                    $this->generate_privacy_notice($post);
                    $this->generate_data_map($post);
                    break;
                case 'retired':
                    $this->generate_retirement_certificate($post);
                    $this->generate_disposition_record($post);
                    break;
            }
        } catch (\Exception $e) {
            $this->add_error(sprintf('Error generating collection documents: %s', $e->getMessage()));
        }
    }

    /**
     * Generate threshold documents
     *
     * @param WP_Post $post   Post object
     * @param string  $status Current status
     */
    private function generate_threshold_documents($post, $status) {
        try {
            switch ($status) {
                case 'approved':
                    $this->generate_pta_report($post);
                    $this->generate_risk_assessment($post);
                    break;
            }
        } catch (\Exception $e) {
            $this->add_error(sprintf('Error generating threshold documents: %s', $e->getMessage()));
        }
    }

    /**
     * Generate impact documents
     *
     * @param WP_Post $post   Post object
     * @param string  $status Current status
     */
    private function generate_impact_documents($post, $status) {
        try {
            switch ($status) {
                case 'approved':
                    $this->generate_pia_report($post);
                    $this->generate_mitigation_plan($post);
                    break;
            }
        } catch (\Exception $e) {
            $this->add_error(sprintf('Error generating impact documents: %s', $e->getMessage()));
        }
    }

    /**
     * Generate collection summary document
     *
     * @param WP_Post $post Post object
     */
    private function generate_collection_summary($post) {
        try {
            $template = $this->get_template('collection-summary');
            if (!$template) {
                throw new \Exception('Collection summary template not found');
            }

            $sharing_parties = pp_get_group_field('sharing_parties', $post->ID);
            $formatted_sharing = array_map(function($party) {
                return [
                    'name' => sanitize_text_field($party['party_name'] ?? ''),
                    'purpose' => wp_kses_post($party['purpose'] ?? ''),
                    'data_shared' => wp_kses_post($party['data_shared'] ?? ''),
                ];
            }, $sharing_parties);

            $data = [
                'title' => sanitize_text_field($post->post_title),
                'purpose' => wp_kses_post(pp_get_field('collection_purpose', $post->ID)),
                'data_elements' => array_map('sanitize_text_field', (array) pp_get_field('data_elements', $post->ID)),
                'sharing_parties' => $formatted_sharing,
                'security_controls' => array_map(function($control) {
                    return [
                        'name' => sanitize_text_field($control['name'] ?? ''),
                        'description' => wp_kses_post($control['description'] ?? ''),
                        'status' => sanitize_text_field($control['status'] ?? ''),
                    ];
                }, pp_get_group_field('security_controls', $post->ID)),
                'retention_period' => pp_get_select_field('retention_period', $post->ID),
            ];

            $content = $this->render_template($template, $data);
            $this->save_document($post->ID, 'collection-summary', $content);
        } catch (\Exception $e) {
            $this->add_error(sprintf('Error generating collection summary: %s', $e->getMessage()));
        }
    }

    /**
     * Generate privacy notice document
     *
     * @param WP_Post $post Post object
     */
    private function generate_privacy_notice($post) {
        try {
            $template = $this->get_template('privacy-notice');
            if (!$template) {
                throw new \Exception('Privacy notice template not found');
            }

            $data = [
                'title' => sanitize_text_field($post->post_title),
                'purpose' => wp_kses_post(pp_get_field('collection_purpose', $post->ID)),
                'authority' => pp_get_select_field('legal_authority', $post->ID),
                'routine_uses' => wp_kses_post(pp_get_wysiwyg_field('routine_uses', $post->ID)),
                'disclosure' => wp_kses_post(pp_get_wysiwyg_field('disclosure_requirements', $post->ID)),
                'retention' => pp_get_select_field('retention_period', $post->ID),
            ];

            $content = $this->render_template($template, $data);
            $this->save_document($post->ID, 'privacy-notice', $content);
        } catch (\Exception $e) {
            $this->add_error(sprintf('Error generating privacy notice: %s', $e->getMessage()));
        }
    }

    /**
     * Generate data map document
     *
     * @param WP_Post $post Post object
     */
    private function generate_data_map($post) {
        try {
            $template = $this->get_template('data-map');
            if (!$template) {
                throw new \Exception('Data map template not found');
            }

            $data = [
                'title' => sanitize_text_field($post->post_title),
                'data_elements' => array_map('sanitize_text_field', (array) pp_get_field('data_elements', $post->ID)),
                'data_sources' => array_map('sanitize_text_field', (array) pp_get_field('data_sources', $post->ID)),
                'data_flows' => wp_kses_post(pp_get_wysiwyg_field('data_flows', $post->ID)),
                'sharing_parties' => array_map(function($party) {
                    return [
                        'name' => sanitize_text_field($party['party_name'] ?? ''),
                        'purpose' => wp_kses_post($party['purpose'] ?? ''),
                        'data_shared' => wp_kses_post($party['data_shared'] ?? ''),
                    ];
                }, pp_get_group_field('sharing_parties', $post->ID)),
                'systems' => array_map('sanitize_text_field', (array) pp_get_field('systems_involved', $post->ID)),
            ];

            $content = $this->render_template($template, $data);
            $this->save_document($post->ID, 'data-map', $content);
        } catch (\Exception $e) {
            $this->add_error(sprintf('Error generating data map: %s', $e->getMessage()));
        }
    }

    /**
     * Generate PTA report
     *
     * @param WP_Post $post Post object
     */
    private function generate_pta_report($post) {
        try {
            $template = $this->get_template('pta-report');
            if (!$template) {
                throw new \Exception('PTA report template not found');
            }

            $data = [
                'title' => sanitize_text_field($post->post_title),
                'system_name' => sanitize_text_field(pp_get_field('system_name', $post->ID)),
                'system_description' => wp_kses_post(pp_get_field('system_description', $post->ID)),
                'pii_categories' => pp_get_checkbox_list('pii_categories', $post->ID),
                'risk_level' => pp_get_select_field('risk_level', $post->ID),
                'risk_factors' => pp_get_checkbox_list('risk_factors', $post->ID),
                'recommendation' => [
                    'value' => sanitize_text_field(pp_get_field('pta_recommendation', $post->ID)),
                    'rationale' => wp_kses_post(pp_get_field('recommendation_rationale', $post->ID)),
                ],
            ];

            $content = $this->render_template($template, $data);
            $this->save_document($post->ID, 'pta-report', $content);
        } catch (\Exception $e) {
            $this->add_error(sprintf('Error generating PTA report: %s', $e->getMessage()));
        }
    }

    /**
     * Generate PIA report
     *
     * @param WP_Post $post Post object
     */
    private function generate_pia_report($post) {
        try {
            $template = $this->get_template('pia-report');
            if (!$template) {
                throw new \Exception('PIA report template not found');
            }

            $data = [
                'title' => sanitize_text_field($post->post_title),
                'system_overview' => wp_kses_post(pp_get_wysiwyg_field('system_overview', $post->ID)),
                'data_flow' => wp_kses_post(pp_get_wysiwyg_field('data_flow', $post->ID)),
                'privacy_principles' => array_map(function($principle) {
                    return [
                        'name' => sanitize_text_field($principle['principle'] ?? ''),
                        'assessment' => wp_kses_post($principle['assessment'] ?? ''),
                        'status' => sanitize_text_field($principle['compliance_status'] ?? ''),
                    ];
                }, pp_get_group_field('privacy_principles', $post->ID)),
                'privacy_risks' => array_map(function($risk) {
                    return [
                        'name' => sanitize_text_field($risk['risk_name'] ?? ''),
                        'description' => wp_kses_post($risk['description'] ?? ''),
                        'impact' => sanitize_text_field($risk['impact_level'] ?? ''),
                        'likelihood' => sanitize_text_field($risk['likelihood'] ?? ''),
                    ];
                }, pp_get_group_field('privacy_risks', $post->ID)),
                'mitigation_measures' => array_map(function($measure) {
                    return [
                        'name' => sanitize_text_field($measure['measure_name'] ?? ''),
                        'description' => wp_kses_post($measure['description'] ?? ''),
                        'status' => sanitize_text_field($measure['implementation_status'] ?? ''),
                        'date' => pp_get_date_field($measure['implementation_date'] ?? '', get_option('date_format')),
                    ];
                }, pp_get_group_field('mitigation_measures', $post->ID)),
                'recommendations' => wp_kses_post(pp_get_wysiwyg_field('recommendations', $post->ID)),
                'dpo_comments' => wp_kses_post(pp_get_wysiwyg_field('dpo_comments', $post->ID)),
            ];

            $content = $this->render_template($template, $data);
            $this->save_document($post->ID, 'pia-report', $content);
        } catch (\Exception $e) {
            $this->add_error(sprintf('Error generating PIA report: %s', $e->getMessage()));
        }
    }

    /**
     * Get template content
     *
     * @param string $template_name Template name
     * @return string|false Template content or false if not found
     */
    private function get_template($template_name) {
        $template_file = PIPER_PRIVACY_PATH . 'templates/documents/' . $template_name . '.php';
        if (!file_exists($template_file)) {
            return false;
        }
        return file_get_contents($template_file);
    }

    /**
     * Render template with data
     *
     * @param string $template Template content
     * @param array  $data     Template data
     * @return string Rendered content
     */
    private function render_template($template, $data) {
        // Extract data to make variables available in template
        extract($data);
        
        // Start output buffering
        ob_start();
        
        // Include template
        eval('?>' . $template);
        
        // Get and clean the buffer
        return ob_get_clean();
    }

    /**
     * Save document
     *
     * @param int    $post_id Post ID
     * @param string $type    Document type
     * @param string $content Document content
     */
    private function save_document($post_id, $type, $content) {
        $document = [
            'post_title' => sprintf('%s - %s', get_the_title($post_id), ucwords(str_replace('-', ' ', $type))),
            'post_content' => $content,
            'post_type' => 'privacy_document',
            'post_status' => 'publish',
            'post_author' => get_current_user_id(),
        ];

        $document_id = wp_insert_post($document);
        if (is_wp_error($document_id)) {
            throw new \Exception($document_id->get_error_message());
        }

        // Link document to original post
        update_post_meta($document_id, '_related_post', $post_id);
        update_post_meta($document_id, '_document_type', $type);
    }
}
