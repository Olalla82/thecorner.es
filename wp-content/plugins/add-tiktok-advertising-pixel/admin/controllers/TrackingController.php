<?php

namespace Pagup\TiktokPixel\Controllers;

use Pagup\TiktokPixel\Core\Option;
class TrackingController {
    public function __construct() {
        add_action( 'wp_head', array(&$this, 'tiktok_pixel') );
    }

    public function tiktok_pixel() {
        if ( Option::check( 'ttap__enable' ) && Option::check( 'ttap__id' ) ) {
            if ( class_exists( 'woocommerce' ) ) {
                if ( !is_singular( 'product' ) && !is_cart() && !is_checkout() ) {
                    echo $this->tiktok( Option::get( 'ttap__id' ) ) . "\n";
                }
            } else {
                echo $this->tiktok( Option::get( 'ttap__id' ) ) . "\n";
            }
        }
        if ( ttap__fs()->can_use_premium_code__premium_only() && Option::check( 'ttap__developer_mode' ) ) {
            echo Option::get( 'ttap__developer_mode' ) . "\n";
        }
        if ( ttap__fs()->can_use_premium_code__premium_only() && Option::check( 'ttap__enable' ) && Option::check( 'ttap__id' ) ) {
            echo $this->tiktok_event();
        }
    }

    protected function tiktok( $tag ) {
        return "<!-- Tiktok Pixel Code --><script>!function (w, d, t) {w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=['page','track','identify','instances','debug','on','off','once','ready','alias','group','enableCookie','disableCookie'],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var i='https://analytics.tiktok.com/i18n/pixel/events.js';ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=i,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};var o=document.createElement('script');o.type='text/javascript',o.async=!0,o.src=i+'?sdkid='+e+'&lib='+t;var a=document.getElementsByTagName('script')[0];a.parentNode.insertBefore(o,a)};ttq.load('{$tag}');ttq.page();}(window, document, 'ttq');</script><script>ttq.track('Browse')</script><!-- End Tiktok Pixel Code -->";
    }

    protected function tiktok_event() {
        return;
    }

}

$TrackingControllers = new TrackingController();