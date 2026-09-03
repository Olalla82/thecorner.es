<?php

namespace OXI_FLIP_BOX_PLUGINS\Inc_Helper;

/**
 *
 * @author biplo
 */
trait Public_Helper {

	/**
	 * Define $wpdb
	 *
	 * @since 3.1.0
	 */
	public $wpdb;

	/**
	 * Database Parent Table
	 *
	 * @since 3.1.0
	 */
	public $parent_table;

	/**
	 * Database Import Table
	 *
	 * @since 3.1.0
	 */
	public $import_table;

	/**
	 * Database Import Table
	 *
	 * @since 3.1.0
	 */
	public $child_table;


	public function Public_loader() {
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->parent_table = $this->wpdb->prefix . 'oxi_div_style';
		$this->child_table = $this->wpdb->prefix . 'oxi_div_list';
		$this->import_table = $this->wpdb->prefix . 'oxi_div_import';
	}


    public function html_special_charecter( $data ) {
        $data = html_entity_decode( $data );
        $data = str_replace( "\'", "'", $data );
        $data = str_replace( '\"', '"', $data );
        $data = do_shortcode( $data, $ignore_html = false );
        return $data;
    }

    public function admin_special_charecter( $data ) {
        $data = html_entity_decode( $data );
        $data = str_replace( "\'", "'", $data );
        $data = str_replace( '\"', '"', $data );
        return $data;
    }

	public function icon_font_selector( $data ) {
        $icon = explode( ' ', $data );
        $fadata = get_option( 'oxi_addons_font_awesome' );
        $faversion = get_option( 'oxi_addons_font_awesome_version' );
        $faversion = explode( '||', $faversion );
        if ( $fadata != 'no' ) {
            wp_enqueue_style( 'font-awesome-' . $faversion[0], $faversion[1] );
        }
        $files = '<i class="' . $data . ' oxi-icons"></i>';
        return $files;
    }

    public function shortcode_render( $styleid, $user ) {
        if ( ! empty( (int) $styleid ) && ! empty( $user ) ) :
            // Initialize wpdb if not already done
            if ( $this->wpdb === null ) {
                $this->Public_loader();
            }
            $style = $this->wpdb->get_row( $this->wpdb->prepare( 'SELECT * FROM ' . $this->parent_table . ' WHERE id = %d ', $styleid ), ARRAY_A );
            $style_name = ucfirst( $style['style_name'] );
            $child = $this->wpdb->get_results( $this->wpdb->prepare( "SELECT * FROM $this->child_table WHERE styleid = %d ORDER by id ASC", $styleid ), ARRAY_A );
            $C = 'OXI_FLIP_BOX_PLUGINS\Public_Render\\' . $style_name;
            if ( class_exists( $C ) ) :
                new $C( $style, $child, $user );
            endif;
        endif;
    }

	public function font_familly_charecter( $data ) {

        $check = get_option( 'oxi_addons_google_font' );
        $custom = [
            'Arial' => '',
            'Helvetica+Neue' => '',
            'Courier+New' => '',
            'Times+New+Roman' => '',
            'Comic+Sans+MS' => '',
            'Verdana' => '',
            'Impact' => '',
            'cursive' => '',
            'inherit' => '',
        ];
        if ( $check != 'no' && ! array_key_exists( $data, $custom ) ) :
            wp_enqueue_style( '' . $data . '', 'https://fonts.googleapis.com/css?family=' . $data . '' );
        endif;
        $data = str_replace( '+', ' ', $data );
        $data = explode( ':', $data );
        $data = $data[0];
        $data = '"' . $data . '"';

        return $data;
    }

    /**
     * Plugin Name Convert to View
     *
     * @since 2.0.0
     */
    public function name_converter( $data ) {
        $data = str_replace( '_', ' ', $data );
        $data = str_replace( '-', ' ', $data );
        $data = str_replace( '+', ' ', $data );
        return ucwords( $data );
    }
}
