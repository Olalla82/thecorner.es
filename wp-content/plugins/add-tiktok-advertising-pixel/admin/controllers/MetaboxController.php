<?php

namespace Pagup\TiktokPixel\Controllers;

use Pagup\TiktokPixel\Core\Plugin;
use Pagup\TiktokPixel\Core\Request;
class MetaboxController {
    public function add_metabox() {
    }

    public function metabox( $post ) {
        $data = [
            'ttap__event'     => get_post_meta( $post->ID, 'ttap__event', true ),
            'ttap__eventarea' => get_post_meta( $post->ID, 'ttap__eventarea', true ),
        ];
        //$meta = get_post_meta($post->ID, 'ttap__eventarea', true);
        //var_dump($meta);
        return Plugin::view( 'metabox', $data );
    }

    public function metadata( $postid ) {
    }

}

$metabox = new MetaboxController();