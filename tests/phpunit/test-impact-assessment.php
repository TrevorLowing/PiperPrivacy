<?php
/**
 * Impact Assessment Tests
 *
 * @package PiperPrivacy
 */

class Test_Impact_Assessment extends WP_UnitTestCase {
    private $module;
    private $controller;
    private $model;
    private $view;

    public function set_up() {
        parent::set_up();
        
        // Create test user
        $this->user_id = $this->factory->user->create(['role' => 'administrator']);
        wp_set_current_user($this->user_id);

        // Initialize module
        $this->module = new \PiperPrivacy\Modules\ImpactAssessment\Module();
        $this->model = new \PiperPrivacy\Modules\ImpactAssessment\Model();
        $this->view = new \PiperPrivacy\Modules\ImpactAssessment\View();
        $this->controller = new \PiperPrivacy\Modules\ImpactAssessment\Controller($this->model, $this->view);
    }

    public function tear_down() {
        parent::tear_down();
        wp_delete_user($this->user_id);
    }

    public function test_post_type_registration() {
        $this->module->register_post_type();
        $this->assertTrue(post_type_exists('pp_assessment'));
    }

    public function test_create_assessment() {
        $data = [
            'title' => 'Test Assessment',
            'processing_activities' => 'Test processing activities',
            'risk_assessment' => 'Test risk assessment',
            'mitigation_measures' => 'Test mitigation measures',
            'dpo_recommendation' => 'Test recommendation',
            'review_date' => '2025-01-13',
            'status' => 'draft'
        ];

        $result = $this->model->create_assessment($data);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertEquals($data['title'], $result['title']);
        $this->assertEquals($data['status'], $result['status']);
    }

    public function test_get_assessment() {
        // Create test assessment
        $data = [
            'title' => 'Test Assessment',
            'processing_activities' => 'Test processing activities',
            'risk_assessment' => 'Test risk assessment',
            'status' => 'draft'
        ];
        $created = $this->model->create_assessment($data);

        // Get assessment
        $result = $this->model->get_assessment($created['id']);

        $this->assertIsArray($result);
        $this->assertEquals($created['id'], $result['id']);
        $this->assertEquals($data['title'], $result['title']);
    }

    public function test_update_assessment() {
        // Create test assessment
        $data = [
            'title' => 'Test Assessment',
            'processing_activities' => 'Test processing activities',
            'risk_assessment' => 'Test risk assessment',
            'status' => 'draft'
        ];
        $created = $this->model->create_assessment($data);

        // Update assessment
        $update_data = [
            'title' => 'Updated Assessment',
            'status' => 'pending'
        ];
        $result = $this->model->update_assessment($created['id'], $update_data);

        $this->assertIsArray($result);
        $this->assertEquals($created['id'], $result['id']);
        $this->assertEquals($update_data['title'], $result['title']);
        $this->assertEquals($update_data['status'], $result['status']);
    }

    public function test_delete_assessment() {
        // Create test assessment
        $data = [
            'title' => 'Test Assessment',
            'processing_activities' => 'Test processing activities',
            'risk_assessment' => 'Test risk assessment',
            'status' => 'draft'
        ];
        $created = $this->model->create_assessment($data);

        // Delete assessment
        $result = $this->model->delete_assessment($created['id']);

        $this->assertTrue($result);
        $this->assertNull(get_post($created['id']));
    }

    public function test_invalid_assessment_creation() {
        $data = [
            'title' => 'Test Assessment',
            // Missing required fields
        ];

        $result = $this->controller->create_assessment(
            new WP_REST_Request('POST', '/piper-privacy/v1/assessments')
        );

        $this->assertInstanceOf('WP_Error', $result);
        $this->assertEquals(400, $result->get_error_data()['status']);
    }

    public function test_unauthorized_access() {
        // Set user to subscriber
        wp_set_current_user($this->factory->user->create(['role' => 'subscriber']));

        $result = $this->controller->check_permission(
            new WP_REST_Request('GET', '/piper-privacy/v1/assessments')
        );

        $this->assertInstanceOf('WP_Error', $result);
        $this->assertEquals(403, $result->get_error_data()['status']);
    }
}
