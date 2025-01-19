<?php
/**
 * Tests for Breach Notification Module
 *
 * @package     PiperPrivacy
 * @subpackage  Tests\Modules\BreachNotification
 */

namespace PiperPrivacy\Tests\Modules\BreachNotification;

use PiperPrivacy\Modules\BreachNotification\Module;
use WP_UnitTestCase;

/**
 * Module test case
 */
class Test_Module extends WP_UnitTestCase {
    /**
     * Module instance
     *
     * @var Module
     */
    private $module;

    /**
     * Set up test environment
     */
    public function set_up() {
        parent::set_up();
        $this->module = new Module();
    }

    /**
     * Test module initialization
     */
    public function test_initialization() {
        // Verify post type registration
        $post_type = get_post_type_object('pp_breach');
        $this->assertNotNull($post_type);
        $this->assertEquals('pp_breach', $post_type->name);
        $this->assertEquals('Breach Incident', $post_type->labels->singular_name);

        // Verify taxonomy registration
        $severity_taxonomy = get_taxonomy('pp_breach_severity');
        $this->assertNotNull($severity_taxonomy);
        $this->assertTrue($severity_taxonomy->hierarchical);

        $status_taxonomy = get_taxonomy('pp_breach_status');
        $this->assertNotNull($status_taxonomy);
        $this->assertTrue($status_taxonomy->hierarchical);

        // Verify default terms
        $severities = get_terms([
            'taxonomy' => 'pp_breach_severity',
            'hide_empty' => false,
        ]);
        $this->assertCount(4, $severities);
        $this->assertContains('critical', wp_list_pluck($severities, 'slug'));
        $this->assertContains('high', wp_list_pluck($severities, 'slug'));
        $this->assertContains('medium', wp_list_pluck($severities, 'slug'));
        $this->assertContains('low', wp_list_pluck($severities, 'slug'));

        $statuses = get_terms([
            'taxonomy' => 'pp_breach_status',
            'hide_empty' => false,
        ]);
        $this->assertCount(8, $statuses);
        $this->assertContains('draft', wp_list_pluck($statuses, 'slug'));
        $this->assertContains('detected', wp_list_pluck($statuses, 'slug'));
        $this->assertContains('confirmed', wp_list_pluck($statuses, 'slug'));
    }

    /**
     * Test cron schedules
     */
    public function test_cron_schedules() {
        $schedules = apply_filters('cron_schedules', []);
        $this->assertArrayHasKey('fifteen_minutes', $schedules);
        $this->assertEquals(15 * MINUTE_IN_SECONDS, $schedules['fifteen_minutes']['interval']);
    }

    /**
     * Test notification processing schedule
     */
    public function test_notification_schedule() {
        $this->assertTrue(wp_next_scheduled('pp_process_breach_notifications') > 0);
    }

    /**
     * Test module hooks
     */
    public function test_hooks() {
        $this->assertEquals(10, has_action('init', [$this->module, 'register_post_type']));
        $this->assertEquals(10, has_filter('cron_schedules', [$this->module, 'add_cron_schedules']));
    }

    /**
     * Clean up test environment
     */
    public function tear_down() {
        parent::tear_down();
        wp_clear_scheduled_hook('pp_process_breach_notifications');
    }
}
