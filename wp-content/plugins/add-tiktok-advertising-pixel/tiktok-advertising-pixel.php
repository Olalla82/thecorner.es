<?php

/*
* Plugin Name: Add Tiktok Advertising Pixel for Tiktok App
* Description: Add Tiktok advertising pixel allows you to install Tiktok pixel properly on your website to track conversion & maximize ROI by ensuring your most important audiences see your ads.
* Author: Pagup
* Version: 1.2.8
* Author URI: https://pagup.com/
* Text Domain: add-tiktok-advertising-pixel
* Domain Path: /languages/
*/
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/******************************************
                Freemius Init
*******************************************/
if ( function_exists( 'ttap__fs' ) ) {
    ttap__fs()->set_basename( false, __FILE__ );
} else {
    if ( !function_exists( 'ttap__fs' ) ) {
        if ( !defined( 'TTAP_NAME' ) ) {
            define( 'TTAP_NAME', "add-tiktok-advertising-pixel" );
        }
        if ( !defined( 'TTAP_PLUGIN_BASE' ) ) {
            define( 'TTAP_PLUGIN_BASE', plugin_basename( __FILE__ ) );
        }
        if ( !defined( 'TTAP_PLUGIN_DIR' ) ) {
            define( 'TTAP_PLUGIN_DIR', plugins_url( '', __FILE__ ) );
        }
        require 'vendor/autoload.php';
        // Create a helper function for easy SDK access.
        function ttap__fs() {
            global $ttap__fs;
            if ( !isset( $ttap__fs ) ) {
                // Include Freemius SDK.
                require_once dirname( __FILE__ ) . '/vendor/freemius/start.php';
                $ttap__fs = fs_dynamic_init( array(
                    'id'              => '5033',
                    'slug'            => 'add-tiktok-advertising-pixel',
                    'type'            => 'plugin',
                    'public_key'      => 'pk_0d0a47215320fe3fe7e438492bbe0',
                    'is_premium'      => false,
                    'premium_suffix'  => 'Tiktok Pixel for Woocommerce',
                    'has_addons'      => true,
                    'has_paid_plans'  => true,
                    'trial'           => array(
                        'days'               => 7,
                        'is_require_payment' => true,
                    ),
                    'has_affiliation' => 'all',
                    'menu'            => array(
                        'slug'       => TTAP_NAME,
                        'first-path' => 'admin.php?page=' . TTAP_NAME,
                        'support'    => false,
                    ),
                    'is_live'         => true,
                ) );
            }
            return $ttap__fs;
        }

        // Init Freemius.
        ttap__fs();
        // Signal that SDK was initiated.
        do_action( 'ttap__fs_loaded' );
        function ttap__fs_settings_url() {
            return admin_url( 'admin.php?page=' . TTAP_NAME );
        }

        ttap__fs()->add_filter( 'connect_url', 'ttap__fs_settings_url' );
        ttap__fs()->add_filter( 'after_skip_url', 'ttap__fs_settings_url' );
        ttap__fs()->add_filter( 'after_connect_url', 'ttap__fs_settings_url' );
        ttap__fs()->add_filter( 'after_pending_connect_url', 'ttap__fs_settings_url' );
        function ttap__fs_custom_icon() {
            return dirname( __FILE__ ) . '/admin/assets/icon.jpg';
        }

        ttap__fs()->add_filter( 'plugin_icon', 'ttap__fs_custom_icon' );
        // freemius opt-in
        function ttap__fs_custom_connect_message(
            $message,
            $user_first_name,
            $product_title,
            $user_login,
            $site_link,
            $freemius_link
        ) {
            $break = "<br><br>";
            $more_plugins = '<p><a target="_blank" href="https://wordpress.org/plugins/meta-tags-for-seo/">Meta Tags for SEO</a>, <a target="_blank" href="https://wordpress.org/plugins/automatic-internal-links-for-seo/">Auto internal links for SEO</a>, <a target="_blank" href="https://wordpress.org/plugins/bulk-image-alt-text-with-yoast/">Bulk auto image Alt Text</a>, <a target="_blank" href="https://wordpress.org/plugins/bulk-image-title-attribute/">Bulk auto image Title Tag</a>, <a target="_blank" href="https://wordpress.org/plugins/mobilook/">Mobile view</a>, <a target="_blank" href="https://wordpress.org/plugins/better-robots-txt/">Wordpress Better-Robots.txt</a>, <a target="_blank" href="https://wordpress.org/plugins/wp-google-street-view/">Wp Google Street View</a>, <a target="_blank" href="https://wordpress.org/plugins/vidseo/">VidSeo</a>, ...</p>';
            return sprintf( esc_html__( 'Hey %1$s, %2$s Click on Allow & Continue to optimize your Tiktok pixel. %2$s Never miss an important update -- opt-in to our security and feature updates notifications. %2$s See you on the other side. Thanks', 'add-tiktok-advertising-pixel' ), $user_first_name, $break ) . $more_plugins;
        }

        ttap__fs()->add_filter(
            'connect_message',
            'ttap__fs_custom_connect_message',
            10,
            6
        );
    }
    class AddTiktokPixel {
        function __construct() {
            register_deactivation_hook( __FILE__, array(&$this, 'deactivate') );
            add_action( 'init', array(&$this, 'ttap__textdomain') );
        }

        public function deactivate() {
            if ( \Pagup\TiktokPixel\Core\Option::check( 'remove_settings' ) ) {
                delete_option( 'add-tiktok-advertising-pixel' );
            }
        }

        function ttap__textdomain() {
            load_plugin_textdomain( \Pagup\TiktokPixel\Core\Plugin::domain(), false, basename( dirname( __FILE__ ) ) . '/languages' );
        }

    }

    $atp = new AddTiktokPixel();
    /*-----------------------------------------
                  TRACK CONTROLLER
      ------------------------------------------*/
    require_once \Pagup\TiktokPixel\Core\Plugin::path( 'admin/controllers/TrackingController.php' );
    /*-----------------------------------------
                      Settings
      ------------------------------------------*/
    if ( is_admin() ) {
        include_once \Pagup\TiktokPixel\Core\Plugin::path( 'admin/Settings.php' );
    }
}