<?php

namespace Pagup\TiktokPixel\Controllers;

use Pagup\TiktokPixel\Core\Option;
use Pagup\TiktokPixel\Core\Plugin;
use Pagup\TiktokPixel\Core\Request;
class SettingsController {
    public function add_settings() {
        add_menu_page(
            __( 'TikTok Pixel Settings', 'add-tiktok-advertising-pixel' ),
            __( 'TikTok Pixel', 'add-tiktok-advertising-pixel' ),
            'manage_options',
            TTAP_NAME,
            array(&$this, 'page'),
            'dashicons-code-standards'
        );
    }

    public function page() {
        $safe = [
            "allow",
            "settings",
            "faq",
            "growth-tools"
        ];
        // check if tiktok booster addon is active
        $addon = Plugin::addon();
        if ( $addon ) {
            array_push( $safe, "booster" );
        }
        $success = '';
        if ( isset( $_POST['update'] ) ) {
            if ( function_exists( 'current_user_can' ) && !current_user_can( 'manage_options' ) ) {
                die( 'Sorry, not allowed...' );
            }
            check_admin_referer( 'ttap__settings', 'ttap__nonce' );
            if ( !isset( $_POST['ttap__nonce'] ) || !wp_verify_nonce( $_POST['ttap__nonce'], 'ttap__settings' ) ) {
                die( 'Sorry, not allowed. Nonce doesn\'t verify' );
            }
            $options = [
                'ttap__enable'    => Request::post( 'ttap__enable', $safe ),
                'ttap__id'        => ( Request::check( 'ttap__id' ) ? sanitize_text_field( $_POST['ttap__id'] ) : '' ),
                'boost-robot'     => Request::post( 'boost-robot', $safe ),
                'boost-alt'       => Request::post( 'boost-alt', $safe ),
                'ttap-bigta'      => Request::post( 'ttap-bigta', $safe ),
                'ttap-vidseo'     => Request::post( 'ttap-vidseo', $safe ),
                'remove_settings' => Request::post( 'remove_settings', $safe ),
            ];
            update_option( 'add-tiktok-advertising-pixel', $options );
            // update options
            echo '<div class="notice ttap-notice notice-success is-dismissible"><p><strong>' . esc_html__( 'Settings saved.' ) . '</strong></p></div>';
        }
        $options = new Option();
        $notification = new \Pagup\TiktokPixel\Controllers\NotificationController();
        echo $notification->support();
        //set active class for navigation tabs
        $active_tab = ( isset( $_GET['tab'] ) && in_array( $_GET['tab'], $safe ) ? sanitize_key( $_GET['tab'] ) : 'settings' );
        //Plugin::dd($_POST);
        // var_dump(Option::all());
        // purchase notification
        $purchase_url = "options-general.php?page=ttap-pricing";
        $get_pro = sprintf( wp_kses( __( '<a href="%s">Get Pro version</a> to enable', 'add-tiktok-advertising-pixel' ), array(
            'a' => array(
                'href'   => array(),
                'target' => array(),
            ),
        ) ), esc_url( $purchase_url ) );
        // Return Views
        if ( $active_tab == 'settings' ) {
            return Plugin::view( 'settings', compact(
                'active_tab',
                'options',
                'get_pro',
                'success',
                'addon'
            ) );
        }
        if ( $active_tab == 'faq' ) {
            return Plugin::view( "faq", compact( 'active_tab', 'addon' ) );
        }
        if ( $active_tab == 'growth-tools' ) {
            return Plugin::view( "growth-tools", compact( 'active_tab', 'addon' ) );
        }
        if ( $addon ) {
            if ( $active_tab == 'booster' ) {
                return Plugin::view( "tiktok-booster", compact( 'active_tab', 'addon' ) );
            }
        }
    }

}

$settings = new SettingsController();