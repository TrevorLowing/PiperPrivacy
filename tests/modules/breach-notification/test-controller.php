<?php
/**
 * Tests for Breach Notification Controller
 *
 * @package     PiperPrivacy
 * @subpackage  Tests\Modules\BreachNotification
 */

namespace PiperPrivacy\Tests\Modules\BreachNotification;

use PiperPrivacy\Modules\BreachNotification\Controller;
use PiperPrivacy\Modules\BreachNotification\Model;
use PiperPrivacy\Modules\BreachNotification\View;
use PiperPrivacy\Modules\BreachNotification\Notification;
use WP_UnitTestCase;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Controller test case
 */
class Test_Controller extends WP_UnitTestCase {
    /**
     * Controller instance
     *
     * @var Controller
     */
    private $controller;

    /**
     * Model mock
     *
     * @var Model|\PHPUnit\Framework\MockObject\MockObject
     */
    private $model;

    /**
     * View mock
     *
     * @var View|\PHPUnit\Framework\MockObject\MockObject
     */
    private $view;

    /**
     * Notification mock
     *
     * @var Notification|\PHPUnit\Framework\MockObject\MockObject
     */
    private $notification;

    /**
     * Test user ID
     *
     * @var int
     */
    private $user_id;

    /**
     * Set up test environment
     */
    public function set_up() {
        parent::set_up();

        // Create test user
        $this->user_id = $this->factory->user->create([
            'role' => 'administrator',
        ]);

        // Create mocks
        $this->model = $this->getMockBuilder(Model::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->view = $this->getMockBuilder(View::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->notification = $this->getMockBuilder(Notification::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->controller = new Controller(
            $this->model,
            $this->view,
            $this->notification
        );
    }

    /**
     * Test permission check
     */
    public function test_check_permission() {
        // Test unauthorized user
        wp_set_current_user(0);
        $request = new WP_REST_Request();
        $result = $this->controller->check_permission($request);
        $this->assertInstanceOf('WP_Error', $result);
        $this->assertEquals('rest_forbidden', $result->get_error_code());

        // Test authorized user
        wp_set_current_user($this->user_id);
        $result = $this->controller->check_permission($request);
        $this->assertTrue($result);
    }

    /**
     * Test get breaches
     */
    public function test_get_breaches() {
        wp_set_current_user($this->user_id);

        $breaches = [
            [
                'id' => 1,
                'title' => 'Test Breach',
                'severity' => 'high',
                'status' => 'detected',
            ],
        ];

        $this->model->expects($this->once())
            ->method('get_breaches')
            ->willReturn($breaches);

        $request = new WP_REST_Request('GET', '/piper-privacy/v1/breaches');
        $response = $this->controller->get_breaches($request);

        $this->assertInstanceOf(WP_REST_Response::class, $response);
        $this->assertEquals($breaches, $response->get_data());
    }

    /**
     * Test create breach
     */
    public function test_create_breach() {
        wp_set_current_user($this->user_id);

        $breach_data = [
            'title' => 'Test Breach',
            'description' => 'Test Description',
            'severity' => 'high',
            'status' => 'detected',
            'detection_date' => '2025-01-13 12:00:00',
            'affected_data' => ['personal'],
            'affected_users' => [1],
            'notify_authorities' => true,
            'notify_affected' => true,
            'mitigation_steps' => 'Test Steps',
        ];

        $created_breach = array_merge(['id' => 1], $breach_data);

        $this->model->expects($this->once())
            ->method('create_breach')
            ->with($this->equalTo($breach_data))
            ->willReturn($created_breach);

        $request = new WP_REST_Request('POST', '/piper-privacy/v1/breaches');
        foreach ($breach_data as $key => $value) {
            $request->set_param($key, $value);
        }

        $response = $this->controller->create_breach($request);

        $this->assertInstanceOf(WP_REST_Response::class, $response);
        $this->assertEquals($created_breach, $response->get_data());
    }

    /**
     * Test update breach
     */
    public function test_update_breach() {
        wp_set_current_user($this->user_id);

        $breach_data = [
            'title' => 'Updated Breach',
            'severity' => 'critical',
            'status' => 'confirmed',
        ];

        $updated_breach = array_merge(['id' => 1], $breach_data);

        $this->model->expects($this->once())
            ->method('update_breach')
            ->with(1, $this->equalTo($breach_data))
            ->willReturn($updated_breach);

        $request = new WP_REST_Request('PUT', '/piper-privacy/v1/breaches/1');
        foreach ($breach_data as $key => $value) {
            $request->set_param($key, $value);
        }

        $response = $this->controller->update_breach($request);

        $this->assertInstanceOf(WP_REST_Response::class, $response);
        $this->assertEquals($updated_breach, $response->get_data());
    }

    /**
     * Test delete breach
     */
    public function test_delete_breach() {
        wp_set_current_user($this->user_id);

        $this->model->expects($this->once())
            ->method('delete_breach')
            ->with(1)
            ->willReturn(true);

        $request = new WP_REST_Request('DELETE', '/piper-privacy/v1/breaches/1');
        $request->set_param('id', 1);

        $response = $this->controller->delete_breach($request);

        $this->assertInstanceOf(WP_REST_Response::class, $response);
        $this->assertEquals(['deleted' => true], $response->get_data());
    }

    /**
     * Test create notification
     */
    public function test_create_notification() {
        wp_set_current_user($this->user_id);

        $notification_data = [
            'breach_id' => 1,
            'type' => 'authority',
            'recipients' => ['test@example.com'],
            'template' => 'default',
            'schedule_date' => '2025-01-14 12:00:00',
        ];

        $created_notification = array_merge(['id' => 'notification_1'], $notification_data);

        $this->notification->expects($this->once())
            ->method('create')
            ->with($this->equalTo($notification_data))
            ->willReturn($created_notification);

        $request = new WP_REST_Request('POST', '/piper-privacy/v1/breaches/1/notifications');
        foreach ($notification_data as $key => $value) {
            $request->set_param($key, $value);
        }

        $response = $this->controller->create_notification($request);

        $this->assertInstanceOf(WP_REST_Response::class, $response);
        $this->assertEquals($created_notification, $response->get_data());
    }

    /**
     * Test process notifications
     */
    public function test_process_notifications() {
        $pending = [
            [
                'breach_id' => 1,
                'notification' => [
                    'id' => 'notification_1',
                    'type' => 'authority',
                ],
            ],
        ];

        $this->model->expects($this->once())
            ->method('get_pending_notifications')
            ->willReturn($pending);

        $this->notification->expects($this->once())
            ->method('send')
            ->with($this->equalTo($pending[0]));

        $this->controller->process_notifications();
    }

    /**
     * Clean up test environment
     */
    public function tear_down() {
        parent::tear_down();
        wp_delete_user($this->user_id);
    }
}
