<?php
/**
 * Class AccessibilityTest
 *
 * @package PiperPrivacy
 */

class AccessibilityTest extends WP_UnitTestCase {
    /**
     * Test ARIA roles are properly set
     */
    public function test_aria_roles() {
        // Get plugin output
        ob_start();
        do_action('piper_privacy_render_dashboard');
        $output = ob_get_clean();

        // Check for required ARIA roles
        $this->assertStringContainsString('role="main"', $output);
        $this->assertStringContainsString('role="navigation"', $output);
        $this->assertStringContainsString('role="search"', $output);
    }

    /**
     * Test heading structure
     */
    public function test_heading_structure() {
        ob_start();
        do_action('piper_privacy_render_dashboard');
        $output = ob_get_clean();

        // Check for proper heading hierarchy
        $this->assertStringContainsString('<h1', $output);
        $this->assertStringContainsString('<h2', $output);
        $this->assertStringContainsString('<h3', $output);
    }

    /**
     * Test form labels
     */
    public function test_form_labels() {
        ob_start();
        do_action('piper_privacy_render_assessment_form');
        $output = ob_get_clean();

        // Check for form labels
        $this->assertStringContainsString('<label for=', $output);
        $this->assertStringContainsString('aria-label=', $output);
    }

    /**
     * Test color contrast
     */
    public function test_color_contrast() {
        // Get plugin styles
        $styles = file_get_contents(PIPER_PRIVACY_PATH . 'assets/css/style.css');

        // Check for WCAG 2.1 AA compliant color combinations
        $this->assertStringContainsString('#ffffff', $styles); // Background
        $this->assertStringContainsString('#2c3338', $styles); // Text color
    }

    /**
     * Test keyboard navigation
     */
    public function test_keyboard_navigation() {
        ob_start();
        do_action('piper_privacy_render_dashboard');
        $output = ob_get_clean();

        // Check for tabindex attributes
        $this->assertStringContainsString('tabindex="0"', $output);
        $this->assertStringContainsString('role="button"', $output);
    }

    /**
     * Test alt text for images
     */
    public function test_image_alt_text() {
        ob_start();
        do_action('piper_privacy_render_dashboard');
        $output = ob_get_clean();

        // Check for alt attributes
        $this->assertStringContainsString('alt="', $output);
        $this->assertNotRegExp('/<img[^>]+alt=""/', $output);
    }

    /**
     * Test skip links
     */
    public function test_skip_links() {
        ob_start();
        do_action('piper_privacy_render_header');
        $output = ob_get_clean();

        // Check for skip to main content link
        $this->assertStringContainsString('Skip to main content', $output);
        $this->assertStringContainsString('class="skip-link"', $output);
    }

    /**
     * Test focus indicators
     */
    public function test_focus_indicators() {
        // Get plugin styles
        $styles = file_get_contents(PIPER_PRIVACY_PATH . 'assets/css/style.css');

        // Check for focus styles
        $this->assertStringContainsString(':focus {', $styles);
        $this->assertStringContainsString('outline:', $styles);
    }
}
