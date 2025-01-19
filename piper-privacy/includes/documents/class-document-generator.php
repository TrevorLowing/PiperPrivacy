<?php
namespace PiperPrivacy\Includes\Documents;

use PiperPrivacy\Includes\Workflow\WorkflowConfig;

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
    private $workflow_config;

    /**
     * Initialize the document generator
     */
    public function __construct() {
        $this->workflow_config = new WorkflowConfig();

        add_action('piper_privacy_status_changed', [$this, 'generate_stage_documents'], 10, 3);
        add_action('piper_privacy_workflow_escalated', [$this, 'generate_escalation_report'], 10, 2);
    }

    /**
     * Generate documents for a workflow stage
     *
     * @param int    $post_id     Post ID
     * @param string $old_status  Old status
     * @param string $new_status  New status
     */
    public function generate_stage_documents($post_id, $old_status, $new_status) {
        $post = get_post($post_id);
        
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
        }
    }

    /**
     * Generate collection documents
     *
     * @param WP_Post $post   Post object
     * @param string  $status Current status
     */
    private function generate_collection_documents($post, $status) {
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
    }

    /**
     * Generate threshold documents
     *
     * @param WP_Post $post   Post object
     * @param string  $status Current status
     */
    private function generate_threshold_documents($post, $status) {
        switch ($status) {
            case 'approved':
                $this->generate_pta_report($post);
                $this->generate_risk_assessment($post);
                break;
        }
    }

    /**
     * Generate impact documents
     *
     * @param WP_Post $post   Post object
     * @param string  $status Current status
     */
    private function generate_impact_documents($post, $status) {
        switch ($status) {
            case 'approved':
                $this->generate_pia_report($post);
                $this->generate_mitigation_plan($post);
                break;
        }
    }

    /**
     * Generate collection summary document
     *
     * @param WP_Post $post Post object
     */
    private function generate_collection_summary($post) {
        $template = $this->get_template('collection-summary');
        $data = [
            'title' => $post->post_title,
            'purpose' => pp_get_field('collection_purpose', $post->ID),
            'data_elements' => pp_get_field('data_elements', $post->ID),
            'sharing_parties' => pp_get_group_field('sharing_parties', $post->ID),
            'security_controls' => pp_get_group_field('security_controls', $post->ID),
            'retention_period' => pp_get_field('retention_period', $post->ID),
        ];

        $content = $this->render_template($template, $data);
        $this->save_document($post->ID, 'collection-summary', $content);
    }

    /**
     * Generate privacy notice document
     *
     * @param WP_Post $post Post object
     */
    private function generate_privacy_notice($post) {
        $template = $this->get_template('privacy-notice');
        $data = [
            'title' => $post->post_title,
            'purpose' => pp_get_field('collection_purpose', $post->ID),
            'authority' => pp_get_field('legal_authority', $post->ID),
            'routine_uses' => pp_get_field('routine_uses', $post->ID),
            'disclosure' => pp_get_field('disclosure_requirements', $post->ID),
            'retention' => pp_get_field('retention_period', $post->ID),
        ];

        $content = $this->render_template($template, $data);
        $this->save_document($post->ID, 'privacy-notice', $content);
    }

    /**
     * Generate data map document
     *
     * @param WP_Post $post Post object
     */
    private function generate_data_map($post) {
        $template = $this->get_template('data-map');
        $data = [
            'title' => $post->post_title,
            'data_elements' => pp_get_field('data_elements', $post->ID),
            'data_sources' => pp_get_field('data_sources', $post->ID),
            'data_flows' => pp_get_field('data_flows', $post->ID),
            'sharing_parties' => pp_get_group_field('sharing_parties', $post->ID),
            'systems' => pp_get_field('systems_involved', $post->ID),
        ];

        $content = $this->render_template($template, $data);
        $this->save_document($post->ID, 'data-map', $content);
    }

    /**
     * Generate PTA report
     *
     * @param WP_Post $post Post object
     */
    private function generate_pta_report($post) {
        $template = $this->get_template('pta-report');
        $data = [
            'title' => $post->post_title,
            'system_name' => pp_get_field('system_name', $post->ID),
            'system_description' => pp_get_field('system_description', $post->ID),
            'pii_categories' => pp_get_field('pii_categories', $post->ID),
            'risk_level' => pp_get_field('risk_level', $post->ID),
            'recommendation' => pp_get_field('pta_recommendation', $post->ID),
        ];

        $content = $this->render_template($template, $data);
        $this->save_document($post->ID, 'pta-report', $content);
    }

    /**
     * Generate PIA report
     *
     * @param WP_Post $post Post object
     */
    private function generate_pia_report($post) {
        $template = $this->get_template('pia-report');
        $data = [
            'title' => $post->post_title,
            'system_overview' => pp_get_field('system_overview', $post->ID),
            'data_flow' => pp_get_field('data_flow', $post->ID),
            'privacy_risks' => pp_get_field('privacy_risks', $post->ID),
            'mitigation_measures' => pp_get_field('mitigation_measures', $post->ID),
            'recommendations' => pp_get_field('recommendations', $post->ID),
        ];

        $content = $this->render_template($template, $data);
        $this->save_document($post->ID, 'pia-report', $content);
    }

    /**
     * Generate retirement certificate
     *
     * @param WP_Post $post Post object
     */
    private function generate_retirement_certificate($post) {
        $template = $this->get_template('retirement-certificate');
        $data = [
            'title' => $post->post_title,
            'retirement_date' => current_time('mysql'),
            'retirement_reason' => pp_get_field('retirement_reason', $post->ID),
            'disposition_method' => pp_get_field('disposition_method', $post->ID),
            'certifying_official' => wp_get_current_user()->display_name,
        ];

        $content = $this->render_template($template, $data);
        $this->save_document($post->ID, 'retirement-certificate', $content);
    }

    /**
     * Generate escalation report
     *
     * @param int   $post_id      Post ID
     * @param float $days_overdue Days overdue
     */
    public function generate_escalation_report($post_id, $days_overdue) {
        $post = get_post($post_id);
        $template = $this->get_template('escalation-report');
        $data = [
            'title' => $post->post_title,
            'type' => get_post_type_object($post->post_type)->labels->singular_name,
            'status' => get_post_status_object($post->post_status)->label,
            'days_overdue' => $days_overdue,
            'deadline' => get_post_meta($post_id, '_workflow_stage_deadline', true),
            'stage_entry' => get_post_meta($post_id, '_workflow_stage_entry_time', true),
            'assigned_to' => get_post_meta($post_id, '_assigned_to', true),
        ];

        $content = $this->render_template($template, $data);
        $this->save_document($post_id, 'escalation-report', $content);
    }

    /**
     * Get document template
     *
     * @param string $template_name Template name
     * @return string Template content
     */
    private function get_template($template_name) {
        $template_file = PIPER_PRIVACY_DIR . "templates/documents/{$template_name}.php";
        
        if (file_exists($template_file)) {
            return file_get_contents($template_file);
        }

        return '';
    }

    /**
     * Render template with data
     *
     * @param string $template Template content
     * @param array  $data     Data to inject into template
     * @return string Rendered content
     */
    private function render_template($template, $data) {
        foreach ($data as $key => $value) {
            $template = str_replace("{{" . $key . "}}", $value, $template);
        }

        return $template;
    }

    /**
     * Save document to WordPress media library
     *
     * @param int    $post_id      Parent post ID
     * @param string $document_type Document type
     * @param string $content      Document content
     */
    private function save_document($post_id, $document_type, $content) {
        $post = get_post($post_id);
        $upload_dir = wp_upload_dir();
        $filename = sanitize_file_name(
            sprintf(
                '%s-%s-%s.pdf',
                $post->post_type,
                $document_type,
                current_time('Y-m-d-His')
            )
        );

        // Create PDF using TCPDF or similar library
        require_once PIPER_PRIVACY_DIR . 'includes/libraries/tcpdf/tcpdf.php';
        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(get_bloginfo('name'));
        $pdf->SetAuthor(wp_get_current_user()->display_name);
        $pdf->SetTitle($post->post_title . ' - ' . ucwords(str_replace('-', ' ', $document_type)));
        
        // Add content to PDF
        $pdf->AddPage();
        $pdf->writeHTML($content, true, false, true, false, '');
        
        // Save PDF
        $pdf_content = $pdf->Output('', 'S');
        $file_path = $upload_dir['path'] . '/' . $filename;
        file_put_contents($file_path, $pdf_content);

        // Create attachment in WordPress
        $attachment = [
            'post_mime_type' => 'application/pdf',
            'post_title' => $post->post_title . ' - ' . ucwords(str_replace('-', ' ', $document_type)),
            'post_content' => '',
            'post_status' => 'inherit',
            'post_parent' => $post_id,
        ];

        $attach_id = wp_insert_attachment($attachment, $file_path);
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata($attach_id, $file_path);
        wp_update_attachment_metadata($attach_id, $attach_data);

        // Store reference to document
        add_post_meta($post_id, '_privacy_document', [
            'type' => $document_type,
            'attachment_id' => $attach_id,
            'date_generated' => current_time('mysql'),
        ]);

        do_action('piper_privacy_document_generated', $post_id, $document_type, $attach_id);
    }
}
