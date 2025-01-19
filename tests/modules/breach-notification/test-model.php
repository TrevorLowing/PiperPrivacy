<?php
/**
 * Tests for Breach Notification Model
 *
 * @package     PiperPrivacy
 * @subpackage  Tests\Modules\BreachNotification
 */

namespace PiperPrivacy\Tests\Modules\BreachNotification;

use PiperPrivacy\Modules\BreachNotification\Model;
use WP_UnitTestCase;

/**
 * Model test case
 */
class Test_Model extends WP_UnitTestCase {
    /**
     * Model instance
     *
     * @var Model
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

        $this->model = new Model();
        $this->user_id = $this->factory->user->create();

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
        update_post_meta($this->breach_id, '_pp_notify_authorities', true);
        update_post_meta($this->breach_id, '_pp_notify_affected', true);
        update_post_meta($this->breach_id, '_pp_mitigation_steps', 'Test Steps');

        // Set test terms
        wp_set_object_terms($this->breach_id, 'high', 'pp_breach_severity');
        wp_set_object_terms($this->breach_id, 'detected', 'pp_breach_status');
    }

    /**
     * Test get breaches
     */
    public function test_get_breaches() {
        $breaches = $this->model->get_breaches([
            'post_type' => 'pp_breach',
            'posts_per_page' => -1,
        ]);

        $this->assertIsArray($breaches);
        $this->assertCount(1, $breaches);
        $this->assertEquals($this->breach_id, $breaches[0]['id']);
        $this->assertEquals('Test Breach', $breaches[0]['title']);
        $this->assertEquals('high', $breaches[0]['severity']);
        $this->assertEquals('detected', $breaches[0]['status']);
    }

    /**
     * Test get single breach
     */
    public function test_get_breach() {
        $breach = $this->model->get_breach($this->breach_id);

        $this->assertIsArray($breach);
        $this->assertEquals($this->breach_id, $breach['id']);
        $this->assertEquals('Test Breach', $breach['title']);
        $this->assertEquals('Test Description', $breach['description']);
        $this->assertEquals('high', $breach['severity']);
        $this->assertEquals('detected', $breach['status']);
        $this->assertEquals('2025-01-13 12:00:00', $breach['detection_date']);
        $this->assertEquals(['personal'], $breach['affected_data']);
        $this->assertEquals([$this->user_id], $breach['affected_users']);
        $this->assertTrue($breach['notify_authorities']);
        $this->assertTrue($breach['notify_affected']);
        $this->assertEquals('Test Steps', $breach['mitigation_steps']);
    }

    /**
     * Test get non-existent breach
     */
    public function test_get_nonexistent_breach() {
        $result = $this->model->get_breach(999999);
        $this->assertInstanceOf('WP_Error', $result);
        $this->assertEquals('not_found', $result->get_error_code());
    }

    /**
     * Test create breach
     */
    public function test_create_breach() {
        $data = [
            'title' => 'New Breach',
            'description' => 'New Description',
            'severity' => 'critical',
            'status' => 'confirmed',
            'detection_date' => '2025-01-13 13:00:00',
            'affected_data' => ['financial'],
            'affected_users' => [$this->user_id],
            'notify_authorities' => true,
            'notify_affected' => false,
            'mitigation_steps' => 'New Steps',
        ];

        $result = $this->model->create_breach($data);

        $this->assertIsArray($result);
        $this->assertGreaterThan(0, $result['id']);
        $this->assertEquals($data['title'], $result['title']);
        $this->assertEquals($data['description'], $result['description']);
        $this->assertEquals($data['severity'], $result['severity']);
        $this->assertEquals($data['status'], $result['status']);
        $this->assertEquals($data['detection_date'], $result['detection_date']);
        $this->assertEquals($data['affected_data'], $result['affected_data']);
        $this->assertEquals($data['affected_users'], $result['affected_users']);
        $this->assertEquals($data['notify_authorities'], $result['notify_authorities']);
        $this->assertEquals($data['notify_affected'], $result['notify_affected']);
        $this->assertEquals($data['mitigation_steps'], $result['mitigation_steps']);
    }

    /**
     * Test update breach
     */
    public function test_update_breach() {
        $data = [
            'title' => 'Updated Breach',
            'description' => 'Updated Description',
            'severity' => 'low',
            'status' => 'resolved',
        ];

        $result = $this->model->update_breach($this->breach_id, $data);

        $this->assertIsArray($result);
        $this->assertEquals($this->breach_id, $result['id']);
        $this->assertEquals($data['title'], $result['title']);
        $this->assertEquals($data['description'], $result['description']);
        $this->assertEquals($data['severity'], $result['severity']);
        $this->assertEquals($data['status'], $result['status']);
    }

    /**
     * Test delete breach
     */
    public function test_delete_breach() {
        $result = $this->model->delete_breach($this->breach_id);
        $this->assertTrue($result);

        $post = get_post($this->breach_id);
        $this->assertNull($post);
    }

    /**
     * Test update breach status
     */
    public function test_update_breach_status() {
        $result = $this->model->update_breach_status($this->breach_id, 'confirmed');

        $this->assertIsArray($result);
        $this->assertEquals($this->breach_id, $result['id']);
        $this->assertEquals('confirmed', $result['status']);

        // Check timeline entry
        $timeline = get_post_meta($this->breach_id, '_pp_timeline', true);
        $this->assertIsArray($timeline);
        $this->assertCount(1, $timeline);
        $this->assertEquals('status_change', $timeline[0]['type']);
        $this->assertEquals('detected', $timeline[0]['data']['from']);
        $this->assertEquals('confirmed', $timeline[0]['data']['to']);
    }

    /**
     * Test get notifications
     */
    public function test_get_notifications() {
        $notifications = [
            [
                'id' => 'notification_1',
                'type' => 'authority',
                'status' => 'pending',
            ],
        ];

        update_post_meta($this->breach_id, '_pp_notifications', $notifications);

        $result = $this->model->get_notifications($this->breach_id);
        $this->assertEquals($notifications, $result);
    }

    /**
     * Test get pending notifications
     */
    public function test_get_pending_notifications() {
        $notifications = [
            [
                'id' => 'notification_1',
                'type' => 'authority',
                'status' => 'pending',
                'schedule_date' => '2025-01-13 12:00:00',
            ],
        ];

        update_post_meta($this->breach_id, '_pp_notifications', $notifications);
        update_post_meta($this->breach_id, '_pp_has_pending_notifications', '1');

        $result = $this->model->get_pending_notifications();
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals($this->breach_id, $result[0]['breach_id']);
        $this->assertEquals($notifications[0], $result[0]['notification']);
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
