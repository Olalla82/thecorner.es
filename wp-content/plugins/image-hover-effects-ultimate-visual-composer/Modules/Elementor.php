<?php

namespace OXI_FLIP_BOX_PLUGINS\Modules;



class Elementor {
    public function __construct() {
        add_action( 'elementor/widgets/register', [ $this, 'register' ] );
    }

    public function register( $widgets_manager ) {
        if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
            return;
        }
        if ( ! class_exists( __NAMESPACE__ . '\Flipbox_Elementor_Widget' ) ) {
            wpkin_define_flipbox_elementor_widget();
        }
                        $widgets_manager->register( new Flipbox_Elementor_Widget() );
    }
}
function wpkin_define_flipbox_elementor_widget() {
    if ( class_exists( '\Elementor\Widget_Base' ) && ! class_exists( __NAMESPACE__ . '\Flipbox_Elementor_Widget' ) ) {
        class Flipbox_Elementor_Widget extends \Elementor\Widget_Base {
            public function get_name() { return 'wpkin_flipbox'; }
            public function get_title() { return 'Flipbox'; }
            public function get_icon() { return 'eicon-flip-box'; }
            public function get_categories() { return [ 'general' ]; }
            public function get_style_depends() { return [ 'oxi-animation', 'flip-box-addons-style' ]; }
            public function get_script_depends() { return [ 'jquery', 'waypoints.min', 'flipbox-addons-jquery' ]; }
            protected function register_controls() {
                global $wpdb;
                $options = [];
                $table = $wpdb->prefix . 'oxi_div_style';
                $rows = $wpdb->get_results( $wpdb->prepare( "SELECT id, name FROM {$table} WHERE type = %s ORDER BY id DESC", 'flip' ), ARRAY_A );
                if ( $rows ) {
                    foreach ( $rows as $row ) {
                        $label = ( isset( $row['name'] ) && $row['name'] !== '' ? $row['name'] : 'Flipbox' ) . ' (#' . $row['id'] . ')';
                        $options[ (string) $row['id'] ] = $label;
                    }
                }
                $default = '';
                if ( ! empty( $options ) ) {
                    $keys = array_keys( $options );
                    $default = reset( $keys );
                }
                $this->start_controls_section( 'section_flipbox', [ 'label' => 'Flipbox' ] );
                $this->add_control( 'id', [
                    'label' => 'Shortcode',
                    'type' => \Elementor\Controls_Manager::SELECT,
                    'options' => $options,
                    'default' => $default,
                ] );
                $this->end_controls_section();
            }
            protected function render() {
                $settings = $this->get_settings_for_display();
                $id = isset( $settings['id'] ) ? $settings['id'] : '';
                echo do_shortcode( '[oxilab_flip_box id="' . esc_attr( $id ) . '"]' );
            }
        }
    }
}