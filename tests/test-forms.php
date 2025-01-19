<?php
/**
 * Class Test_Forms
 *
 * @package PiperPrivacy
 */

class Test_Forms extends WP_UnitTestCase {
    /**
     * Form processor instance
     *
     * @var \PiperPrivacy\Forms\Form_Processor
     */
    private $form_processor;

    /**
     * Form storage instance
     *
     * @var \PiperPrivacy\Forms\Form_Storage
     */
    private $form_storage;

    /**
     * Form notifications instance
     *
     * @var \PiperPrivacy\Forms\Form_Notifications
     */
    private $form_notifications;

    /**
     * Set up test environment
     */
    public function set_up() {
        parent::set_up();
        
        $this->form_processor = new \PiperPrivacy\Forms\Form_Processor();
        $this->form_storage = new \PiperPrivacy\Forms\Form_Storage();
        $this->form_notifications = new \PiperPrivacy\Forms\Form_Notifications();
    }

    /**
     * Test form validation
     */
    public function test_form_validation() {
        // Test collection form validation
        $_POST = [
            'form_type' => 'collection',
            'system_name' => 'Test System',
            'system_description' => 'Test Description',
            'pii_categories' => ['general_personal', 'contact'],
            'data_elements' => ['name', 'email'],
        ];

        $_POST['piper_privacy_form_nonce'] = wp_create_nonce('piper_privacy_form');

        // Process form
        $this->form_processor->process_form_submission();
        
        // Check if form was processed successfully
        $this->assertEmpty($GLOBALS['rwmb_frontend_form_errors']);

        // Test invalid form submission
        $_POST = [
            'form_type' => 'collection',
            'system_name' => '', // Required field is empty
            'system_description' => 'Test Description',
        ];

        $_POST['piper_privacy_form_nonce'] = wp_create_nonce('piper_privacy_form');

        // Process form
        $this->form_processor->process_form_submission();
        
        // Check if validation errors were caught
        $this->assertNotEmpty($GLOBALS['rwmb_frontend_form_errors']);
    }

    /**
     * Test form storage
     */
    public function test_form_storage() {
        // Create test form data
        $form_data = [
            'post_title' => 'Test System',
            'post_content' => 'Test Description',
            'post_type' => 'privacy_collection',
            'post_status' => 'publish',
        ];

        // Insert test post
        $post_id = wp_insert_post($form_data);

        // Add meta data
        update_post_meta($post_id, 'pii_categories', ['general_personal', 'contact']);
        update_post_meta($post_id, 'data_elements', ['name', 'email']);

        // Test data retrieval
        $stored_data = $this->form_storage->get_form_data($post_id, 'collection');

        $this->assertEquals('Test System', $stored_data['title']);
        $this->assertEquals(['general_personal', 'contact'], $stored_data['pii_categories']);
        $this->assertEquals(['name', 'email'], $stored_data['data_elements']);
    }

    /**
     * Test form notifications
     */
    public function test_form_notifications() {
        // Create test post
        $post_id = $this->factory->post->create([
            'post_type' => 'privacy_collection',
            'post_title' => 'Test System',
        ]);

        // Test data
        $data = [
            'system_name' => 'Test System',
            'system_description' => 'Test Description',
            'pii_categories' => ['general_personal', 'contact'],
        ];

        // Monitor email sending
        $emails = [];
        add_action('wp_mail', function($args) use (&$emails) {
            $emails[] = $args;
        });

        // Send notifications
        $this->form_notifications->send_collection_notifications($post_id, $data);

        // Verify emails were sent
        $this->assertNotEmpty($emails);
        $this->assertCount(2, $emails); // Admin and user notifications
    }

    /**
     * Test form integration
     */
    public function test_form_integration() {
        // Simulate form submission
        $_POST = [
            'form_type' => 'collection',
            'system_name' => 'Integration Test System',
            'system_description' => 'Integration Test Description',
            'pii_categories' => ['general_personal', 'contact'],
            'data_elements' => ['name', 'email'],
        ];

        $_POST['piper_privacy_form_nonce'] = wp_create_nonce('piper_privacy_form');

        // Process form
        $this->form_processor->process_form_submission();

        // Verify no validation errors
        $this->assertEmpty($GLOBALS['rwmb_frontend_form_errors']);

        // Get latest form submission
        $forms = $this->form_storage->get_forms('collection', [
            'posts_per_page' => 1,
            'orderby' => 'date',
            'order' => 'DESC',
        ]);

        $this->assertNotEmpty($forms);
        $form = reset($forms);

        // Verify stored data
        $this->assertEquals('Integration Test System', $form['title']);
        $this->assertEquals(['general_personal', 'contact'], $form['pii_categories']);
        $this->assertEquals(['name', 'email'], $form['data_elements']);
    }

    /**
     * Test form field validation
     */
    public function test_field_validation() {
        $test_cases = [
            // Test required fields
            [
                'field' => 'system_name',
                'value' => '',
                'should_pass' => false,
            ],
            // Test string length
            [
                'field' => 'system_name',
                'value' => str_repeat('a', 101), // Max length is 100
                'should_pass' => false,
            ],
            // Test valid input
            [
                'field' => 'system_name',
                'value' => 'Valid System Name',
                'should_pass' => true,
            ],
            // Test array input
            [
                'field' => 'pii_categories',
                'value' => ['general_personal', 'contact'],
                'should_pass' => true,
            ],
            // Test invalid array input
            [
                'field' => 'pii_categories',
                'value' => 'not_an_array',
                'should_pass' => false,
            ],
        ];

        foreach ($test_cases as $test) {
            $_POST = [
                'field' => $test['field'],
                'value' => $test['value'],
                'form_type' => 'collection',
                'nonce' => wp_create_nonce('piper_privacy_form'),
            ];

            // Test AJAX validation
            $_REQUEST['_ajax_nonce'] = wp_create_nonce('piper_privacy_form');
            $response = $this->form_processor->ajax_validate_field();

            if ($test['should_pass']) {
                $this->assertTrue($response->success);
            } else {
                $this->assertFalse($response->success);
            }
        }
    }

    /**
     * Test form submission with file upload
     */
    public function test_file_upload() {
        // Create test file
        $upload_dir = wp_upload_dir();
        $test_file = $upload_dir['path'] . '/test.pdf';
        file_put_contents($test_file, 'Test content');

        $_FILES['data_flow_diagram'] = [
            'name' => 'test.pdf',
            'type' => 'application/pdf',
            'tmp_name' => $test_file,
            'error' => 0,
            'size' => filesize($test_file),
        ];

        $_POST = [
            'form_type' => 'impact',
            'system_overview' => 'Test Overview',
            'project_scope' => 'Test Scope',
            'stakeholders' => 'Test Stakeholders',
        ];

        $_POST['piper_privacy_form_nonce'] = wp_create_nonce('piper_privacy_form');

        // Process form
        $this->form_processor->process_form_submission();

        // Verify file was uploaded
        $forms = $this->form_storage->get_forms('impact', [
            'posts_per_page' => 1,
            'orderby' => 'date',
            'order' => 'DESC',
        ]);

        $form = reset($forms);
        $attachment_id = get_post_meta($form['id'], 'data_flow_diagram', true);

        $this->assertNotEmpty($attachment_id);
        $this->assertTrue(wp_attachment_is_image($attachment_id) || 'application/pdf' === get_post_mime_type($attachment_id));

        // Clean up
        unlink($test_file);
    }

    /**
     * Test error handling
     */
    public function test_error_handling() {
        // Test invalid nonce
        $_POST = [
            'form_type' => 'collection',
            'system_name' => 'Test System',
            'piper_privacy_form_nonce' => 'invalid_nonce',
        ];

        $this->form_processor->process_form_submission();
        $this->assertNotEmpty($GLOBALS['rwmb_frontend_form_errors']);

        // Test invalid form type
        $_POST = [
            'form_type' => 'invalid_type',
            'system_name' => 'Test System',
        ];

        $_POST['piper_privacy_form_nonce'] = wp_create_nonce('piper_privacy_form');

        $this->form_processor->process_form_submission();
        $this->assertNotEmpty($GLOBALS['rwmb_frontend_form_errors']);
    }
}
