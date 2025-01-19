<?php
/**
 * Export Manager Class
 *
 * @package     PiperPrivacy
 * @subpackage  Modules\BreachNotification
 */

namespace PiperPrivacy\Modules\BreachNotification;

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Export Manager class
 */
class Export_Manager {
    /**
     * Available export formats
     *
     * @var array
     */
    private $export_formats = [
        'pdf' => [
            'name' => 'PDF',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
        ],
        'csv' => [
            'name' => 'CSV',
            'mime_type' => 'text/csv',
            'extension' => 'csv',
        ],
        'json' => [
            'name' => 'JSON',
            'mime_type' => 'application/json',
            'extension' => 'json',
        ],
    ];

    /**
     * Initialize the export manager
     */
    public function __construct() {
        add_action('wp_ajax_pp_export_breach', [$this, 'ajax_export_breach']);
        add_action('wp_ajax_pp_export_documents', [$this, 'ajax_export_documents']);
    }

    /**
     * Handle breach export via AJAX
     */
    public function ajax_export_breach() {
        check_ajax_referer('pp_export_breach', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $breach_id = isset($_POST['breach_id']) ? intval($_POST['breach_id']) : 0;
        $format = isset($_POST['format']) ? sanitize_text_field($_POST['format']) : 'pdf';

        if (!$breach_id || !isset($this->export_formats[$format])) {
            wp_send_json_error('Invalid parameters');
        }

        $breach = get_post($breach_id);
        if (!$breach || $breach->post_type !== 'pp_breach') {
            wp_send_json_error('Invalid breach');
        }

        // Get breach data
        $data = $this->get_breach_data($breach_id);

        // Generate export
        $file_path = $this->generate_export($data, $format);
        if (!$file_path) {
            wp_send_json_error('Export generation failed');
        }

        // Return download URL
        $download_url = add_query_arg([
            'action' => 'pp_download_export',
            'file' => basename($file_path),
            'nonce' => wp_create_nonce('pp_download_export'),
        ], admin_url('admin-ajax.php'));

        wp_send_json_success(['download_url' => $download_url]);
    }

    /**
     * Handle documents export via AJAX
     */
    public function ajax_export_documents() {
        check_ajax_referer('pp_export_documents', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $breach_id = isset($_POST['breach_id']) ? intval($_POST['breach_id']) : 0;
        if (!$breach_id) {
            wp_send_json_error('Invalid breach ID');
        }

        // Create ZIP archive
        $zip_file = $this->create_documents_archive($breach_id);
        if (!$zip_file) {
            wp_send_json_error('Archive creation failed');
        }

        // Return download URL
        $download_url = add_query_arg([
            'action' => 'pp_download_export',
            'file' => basename($zip_file),
            'nonce' => wp_create_nonce('pp_download_export'),
        ], admin_url('admin-ajax.php'));

        wp_send_json_success(['download_url' => $download_url]);
    }

    /**
     * Get breach data for export
     *
     * @param int $breach_id Breach ID.
     * @return array Breach data.
     */
    private function get_breach_data($breach_id) {
        $breach = get_post($breach_id);
        $risk_assessment = get_post_meta($breach_id, '_pp_risk_assessment', true);
        $compliance_analysis = get_post_meta($breach_id, '_pp_compliance_analysis', true);
        $document_manager = new Document_Manager();
        $documents = $document_manager->get_breach_documents($breach_id);

        return [
            'breach' => [
                'id' => $breach_id,
                'title' => $breach->post_title,
                'description' => $breach->post_content,
                'date_discovered' => get_post_meta($breach_id, '_pp_date_discovered', true),
                'date_reported' => get_post_meta($breach_id, '_pp_date_reported', true),
                'status' => get_post_meta($breach_id, '_pp_status', true),
            ],
            'risk_assessment' => $risk_assessment,
            'compliance_analysis' => $compliance_analysis,
            'documents' => $documents,
        ];
    }

    /**
     * Generate export file
     *
     * @param array  $data   Data to export.
     * @param string $format Export format.
     * @return string|false File path or false on failure.
     */
    private function generate_export($data, $format) {
        $export_dir = wp_upload_dir()['basedir'] . '/piper-privacy/exports';
        wp_mkdir_p($export_dir);

        $filename = sprintf(
            'breach-report-%d-%s.%s',
            $data['breach']['id'],
            date('Y-m-d-His'),
            $this->export_formats[$format]['extension']
        );
        $file_path = $export_dir . '/' . $filename;

        switch ($format) {
            case 'pdf':
                return $this->generate_pdf_export($data, $file_path);
            case 'csv':
                return $this->generate_csv_export($data, $file_path);
            case 'json':
                return $this->generate_json_export($data, $file_path);
            default:
                return false;
        }
    }

    /**
     * Generate PDF export
     *
     * @param array  $data      Data to export.
     * @param string $file_path Output file path.
     * @return string|false File path or false on failure.
     */
    private function generate_pdf_export($data, $file_path) {
        require_once(PP_PLUGIN_DIR . 'vendor/autoload.php');

        try {
            $mpdf = new \Mpdf\Mpdf([
                'margin_left' => 20,
                'margin_right' => 20,
                'margin_top' => 20,
                'margin_bottom' => 20,
            ]);

            // Generate PDF content
            $html = $this->get_pdf_template($data);
            $mpdf->WriteHTML($html);

            // Save PDF
            $mpdf->Output($file_path, 'F');

            return $file_path;
        } catch (\Exception $e) {
            error_log('PDF Export Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate CSV export
     *
     * @param array  $data      Data to export.
     * @param string $file_path Output file path.
     * @return string|false File path or false on failure.
     */
    private function generate_csv_export($data, $file_path) {
        $fp = fopen($file_path, 'w');
        if (!$fp) {
            return false;
        }

        // Write headers
        fputcsv($fp, [
            'Breach ID',
            'Title',
            'Description',
            'Date Discovered',
            'Date Reported',
            'Status',
            'Risk Score',
            'Risk Severity',
            'Required Notifications',
            'Compliance Frameworks',
        ]);

        // Write data
        fputcsv($fp, [
            $data['breach']['id'],
            $data['breach']['title'],
            $data['breach']['description'],
            $data['breach']['date_discovered'],
            $data['breach']['date_reported'],
            $data['breach']['status'],
            $data['risk_assessment']['score'],
            $data['risk_assessment']['severity'],
            implode(', ', array_keys(array_filter($data['risk_assessment']['notification_requirements']))),
            implode(', ', array_keys($data['compliance_analysis']['frameworks'])),
        ]);

        fclose($fp);
        return $file_path;
    }

    /**
     * Generate JSON export
     *
     * @param array  $data      Data to export.
     * @param string $file_path Output file path.
     * @return string|false File path or false on failure.
     */
    private function generate_json_export($data, $file_path) {
        $json = wp_json_encode($data, JSON_PRETTY_PRINT);
        if (file_put_contents($file_path, $json) === false) {
            return false;
        }
        return $file_path;
    }

    /**
     * Create ZIP archive of breach documents
     *
     * @param int $breach_id Breach ID.
     * @return string|false Archive path or false on failure.
     */
    private function create_documents_archive($breach_id) {
        $document_manager = new Document_Manager();
        $documents = $document_manager->get_breach_documents($breach_id);

        if (empty($documents)) {
            return false;
        }

        $export_dir = wp_upload_dir()['basedir'] . '/piper-privacy/exports';
        wp_mkdir_p($export_dir);

        $zip_file = sprintf(
            '%s/breach-documents-%d-%s.zip',
            $export_dir,
            $breach_id,
            date('Y-m-d-His')
        );

        $zip = new \ZipArchive();
        if ($zip->open($zip_file, \ZipArchive::CREATE) !== true) {
            return false;
        }

        foreach ($documents as $document) {
            $file_path = get_attached_file(attachment_url_to_postid($document['file_url']));
            if ($file_path && file_exists($file_path)) {
                $zip->addFile(
                    $file_path,
                    sprintf(
                        '%s/%s',
                        $document['type'],
                        basename($file_path)
                    )
                );
            }
        }

        $zip->close();
        return $zip_file;
    }

    /**
     * Get PDF template
     *
     * @param array $data Export data.
     * @return string HTML content.
     */
    private function get_pdf_template($data) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Breach Report - <?php echo esc_html($data['breach']['title']); ?></title>
            <style>
                body { font-family: Arial, sans-serif; }
                h1 { color: #1d2327; }
                h2 { color: #2c3338; margin-top: 20px; }
                table { width: 100%; border-collapse: collapse; margin: 10px 0; }
                th, td { padding: 8px; border: 1px solid #ddd; }
                th { background: #f0f0f1; }
                .severity { 
                    display: inline-block;
                    padding: 5px 10px;
                    border-radius: 3px;
                    color: #fff;
                }
                .severity.low { background: #00a32a; }
                .severity.medium { background: #dba617; }
                .severity.high { background: #d63638; }
                .severity.critical { background: #000; }
            </style>
        </head>
        <body>
            <h1>Breach Report</h1>
            
            <h2>Breach Details</h2>
            <table>
                <tr>
                    <th>Title</th>
                    <td><?php echo esc_html($data['breach']['title']); ?></td>
                </tr>
                <tr>
                    <th>Description</th>
                    <td><?php echo esc_html($data['breach']['description']); ?></td>
                </tr>
                <tr>
                    <th>Date Discovered</th>
                    <td><?php echo esc_html($data['breach']['date_discovered']); ?></td>
                </tr>
                <tr>
                    <th>Date Reported</th>
                    <td><?php echo esc_html($data['breach']['date_reported']); ?></td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td><?php echo esc_html($data['breach']['status']); ?></td>
                </tr>
            </table>

            <h2>Risk Assessment</h2>
            <table>
                <tr>
                    <th>Risk Score</th>
                    <td><?php echo esc_html($data['risk_assessment']['score']); ?></td>
                </tr>
                <tr>
                    <th>Severity</th>
                    <td>
                        <span class="severity <?php echo esc_attr(strtolower($data['risk_assessment']['severity'])); ?>">
                            <?php echo esc_html($data['risk_assessment']['severity']); ?>
                        </span>
                    </td>
                </tr>
            </table>

            <h3>Risk Factors</h3>
            <table>
                <tr>
                    <th>Factor</th>
                    <th>Score</th>
                    <th>Weight</th>
                    <th>Details</th>
                </tr>
                <?php foreach ($data['risk_assessment']['factors'] as $factor => $info) : ?>
                    <tr>
                        <td><?php echo esc_html(ucwords(str_replace('_', ' ', $factor))); ?></td>
                        <td><?php echo esc_html($info['score']); ?></td>
                        <td><?php echo esc_html($info['weight'] * 100); ?>%</td>
                        <td><?php echo esc_html(is_array($info['details']) ? json_encode($info['details']) : $info['details']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <h2>Compliance Analysis</h2>
            <?php foreach ($data['compliance_analysis']['frameworks'] as $framework => $info) : ?>
                <h3><?php echo esc_html($info['name']); ?></h3>
                <table>
                    <tr>
                        <th>Notification Type</th>
                        <th>Required</th>
                        <th>Deadline</th>
                    </tr>
                    <?php foreach ($info['notifications'] as $type => $notification) : ?>
                        <tr>
                            <td><?php echo esc_html(ucwords(str_replace('_', ' ', $type))); ?></td>
                            <td><?php echo $notification['required'] ? 'Yes' : 'No'; ?></td>
                            <td><?php echo esc_html($notification['deadline']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endforeach; ?>

            <h2>Documents</h2>
            <table>
                <tr>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Upload Date</th>
                    <th>Retention Date</th>
                </tr>
                <?php foreach ($data['documents'] as $document) : ?>
                    <tr>
                        <td><?php echo esc_html($document['title']); ?></td>
                        <td><?php echo esc_html(ucwords(str_replace('_', ' ', $document['type']))); ?></td>
                        <td><?php echo esc_html($document['upload_date']); ?></td>
                        <td><?php echo esc_html($document['retention_date']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
}
