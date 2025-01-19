<?php
namespace PiperPrivacy\Frontend;

/**
 * The public-facing functionality of the plugin.
 *
 * @package    PiperPrivacy
 * @subpackage PiperPrivacy/public
 */
class PublicFacing {
    /**
     * Initialize the class and set its properties.
     */
    public function __construct() {
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            'piper-privacy-public',
            plugin_dir_url(__FILE__) . 'css/piper-privacy-public.css',
            array(),
            PIPER_PRIVACY_VERSION,
            'all'
        );
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            'piper-privacy-public',
            plugin_dir_url(__FILE__) . 'js/piper-privacy-public.js',
            array('jquery'),
            PIPER_PRIVACY_VERSION,
            false
        );
    }
}
