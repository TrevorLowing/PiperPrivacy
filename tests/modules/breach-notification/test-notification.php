<?php
/**
 * Tests for Breach Notification Handler
 *
 * @package     PiperPrivacy
 * @subpackage  Tests\Modules\BreachNotification
 */

namespace PiperPrivacy\Tests\Modules\BreachNotification;

use PiperPrivacy\Modules\BreachNotification\Notification;
use PiperPrivacy\Modules\BreachNotification\Model;
use WP_UnitTestCase;

/**
 * Notification test case
 */
class Test_Notification extends WP_UnitTestCase {
    /**
     * Notification instance
     *
     * @var Notification
     */
    private $notification;

    /**
     * Model mock
     *
     * @var Model|\PHPUnit\Framework\MockObject\MockObject
     */
    private $model;

    /**
     * Test breach ID
     *
     * @var int
     */
    private $breach_id;

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

        $this->model = $this->getMockBuilder(Model::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->notification = new Notification($this->model);

        // Create test user
        $this->user_id = $this->factory->user->create([
            'user_email' => 'test@example.com',
        ]);

        // Create test breach
        $this->breach_id = wp_insert_post([
            'post_type' => 'pp_breach',
            'post_title' => 'Test Breach',
            'post_content' => 'Test Description',
            'post_status' => 'publish',
            'post_author' => $this->user_id,
        ]);

        // Set test meta
        update_post_meta($this->breach_id, '_pp_detection_date', '2025-01-13 12:00:00');
        update_post_meta($this->breach_id, '_pp_affected_data', ['personal']);
        update_post_meta($this->breach_id, '_pp_affected_users', [$this->user_id]);
    }

    /**
     * Test create notification
     */
    public function test_create_notification() {
        $data = [
            'breach_id' => $this->breach_id,
            'type' => 'authority',
            'recipients' => ['authority@example.com'],
            'template' => 'default',
            'schedule_date' => '2025-01-14 12:00:00',
        ];

        $this->model->expects($this->once())
            ->method('get_breach')
            ->with($this->breach_id)
            ->willReturn([
                'id' => $this->breach_id,
                'title' => 'Test Breach',
                'description' => 'Test Description',
            ]);

        $result = $this->notification->create($data);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertEquals($data['type'], $result['type']);
        $this->assertEquals($data['recipients'], $result['recipients']);
        $this->assertEquals($data['template'], $result['template']);
        $this->assertEquals($data['schedule_date'], $result['schedule_date']);
        $this->assertEquals('pending', $result['status']);
    }

    /**
     * Test send notification
     */
    public function test_send_notification() {
        $notification = [
            'id' => 'notification_1',
            'type' => 'authority',
            'recipients' => ['authority@example.com'],
            'template' => 'default',
            'status' => 'pending',
        ];

        $breach = [
            'id' => $this->breach_id,
            'title' => 'Test Breach',
            'description' => 'Test Description',
            'severity' => 'high',
            'status' => 'confirmed',
            'detection_date' => '2025-01-13 12:00:00',
            'affected_data' => ['personal'],
            'affected_users' => [$this->user_id],
        ];

        $this->model->expects($this->once())
            ->method('get_breach')
            ->with($this->breach_id)
            ->willReturn($breach);

        add_filter('wp_mail', function($args) {
            $this->assertEquals(['authority@example.com'], $args['to']);
            $this->assertStringContainsString('Test Breach', $args['subject']);
            $this->assertStringContainsString('Test Description', $args['message']);
            return $args;
        });

        $result = $this->notification->send([
            'breach_id' => $this->breach_id,
            'notification' => $notification,
        ]);

        $this->assertTrue($result);

        // Check notification status update
        $notifications = get_post_meta($this->breach_id, '_pp_notifications', true);
        $updated = false;
        foreach ($notifications as $n) {
            if ($n['id'] === $notification['id']) {
                $this->assertEquals('sent', $n['status']);
                $updated = true;
                break;
            }
        }
        $this->assertTrue($updated);

        // Check timeline entry
        $timeline = get_post_meta($this->breach_id, '_pp_timeline', true);
        $this->assertIsArray($timeline);
        $this->assertCount(1, $timeline);
        $this->assertEquals('notification_sent', $timeline[0]['type']);
        $this->assertEquals($notification['id'], $timeline[0]['data']['notification_id']);
        $this->assertEquals($notification['type'], $timeline[0]['data']['type']);
    }

    /**
     * Test send notification failure
     */
    public function test_send_notification_failure() {
        $notification = [
            'id' => 'notification_1',
            'type' => 'authority',
            'recipients' => ['invalid-email'],
            'template' => 'default',
            'status' => 'pending',
        ];

        $breach = [
            'id' => $this->breach_id,
            'title' => 'Test Breach',
            'description' => 'Test Description',
        ];

        $this->model->expects($this->once())
            ->method('get_breach')
            ->with($this->breach_id)
            ->willReturn($breach);

        add_filter('wp_mail', function() {
            return false;
        });

        $result = $this->notification->send([
            'breach_id' => $this->breach_id,
            'notification' => $notification,
        ]);

        $this->assertFalse($result);

        // Check notification status update
        $notifications = get_post_meta($this->breach_id, '_pp_notifications', true);
        $updated = false;
        foreach ($notifications as $n) {
            if ($n['id'] === $notification['id']) {
                $this->assertEquals('failed', $n['status']);
                $updated = true;
                break;
            }
        }
        $this->assertTrue($updated);

        // Check timeline entry
        $timeline = get_post_meta($this->breach_id, '_pp_timeline', true);
        $this->assertIsArray($timeline);
        $this->assertCount(1, $timeline);
        $this->assertEquals('notification_failed', $timeline[0]['type']);
        $this->assertEquals($notification['id'], $timeline[0]['data']['notification_id']);
        $this->assertEquals($notification['type'], $timeline[0]['data']['type']);
    }

    /**
     * Test get template
     */
    public function test_get_template() {
        $breach = [
            'title' => 'Test Breach',
            'description' => 'Test Description',
            'severity' => 'high',
            'detection_date' => '2025-01-13 12:00:00',
            'affected_data' => ['personal'],
        ];

        $template = $this->notification->get_template('authority', $breach);

        $this->assertIsArray($template);
        $this->assertArrayHasKey('subject', $template);
        $this->assertArrayHasKey('message', $template);
        $this->assertStringContainsString($breach['title'], $template['subject']);
        $this->assertStringContainsString($breach['description'], $template['message']);
        $this->assertStringContainsString($breach['severity'], $template['message']);
        $this->assertStringContainsString($breach['detection_date'], $template['message']);
        $this->assertStringContainsString('personal', $template['message']);
    }

    /**
     * Clean up test environment
     */
    public function tear_down() {
        parent::tear_down();
        wp_delete_post($this->breach_id, true);
        wp_delete_user($this->user_id);
    }
}
