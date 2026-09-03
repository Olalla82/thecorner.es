<?php

namespace OXI_FLIP_BOX_PLUGINS\Includes\Admin;

/**
 * Admin Menu Class
 *
 * @since 2.10.1
 */
class Menu {

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

	public function __construct() {
		global $wpdb;
		$this->parent_table = $wpdb->prefix . 'oxi_div_style';
		$this->child_table  = $wpdb->prefix . 'oxi_div_list';
		$this->import_table = $wpdb->prefix . 'oxi_div_import';
		add_action( 'admin_menu', [ $this, 'regiter_admin_menu' ] );
	}

	public function regiter_admin_menu() {
		$user_role = get_option( 'oxi_addons_user_permission' );
        $role_object = get_role( $user_role );
        $first_key = '';
        if ( isset( $role_object->capabilities ) && is_array( $role_object->capabilities ) ) {
            reset( $role_object->capabilities );
            $first_key = key( $role_object->capabilities );
        } else {
            $first_key = 'manage_options';
        }
        add_menu_page( 'Flip Box', 'Flip Box', $first_key, 'oxi-flip-box-ultimate', [ $this, 'Flip_Home' ] );
        add_submenu_page( 'oxi-flip-box-ultimate', 'Flip Box', 'Flip Box', $first_key, 'oxi-flip-box-ultimate', [ $this, 'Flip_Home' ] );
        add_submenu_page( 'oxi-flip-box-ultimate', 'Create New', 'Create New', $first_key, 'oxi-flip-box-ultimate-new', [ $this, 'Flip_Create' ] );
        add_submenu_page( 'oxi-flip-box-ultimate', 'Import Templates', 'Import Templates', $first_key, 'oxi-flip-box-ultimate-import', [ $this, 'Flip_Import' ] );
        add_submenu_page( 'oxi-flip-box-ultimate', 'Getting Started', 'Getting Started', $first_key, 'flipbox-getting-started', [ $this, 'wpkin_flipbox_getting_started' ] );
        add_submenu_page( 'oxi-flip-box-ultimate', 'Settings', 'Settings', $first_key, 'oxi-flip-box-ultimate-settings', [ $this, 'Flip_Settings' ] );
        // add_submenu_page('oxi-flip-box-ultimate', 'Oxilab Addons', 'Oxilab Addons', $first_key, 'oxi-flip-box-ultimate-addons', [$this, 'Flip_Addons']);
	}

	public function Flip_Home() {
		new Pages\Home();
    }

	public function Flip_Create() {
		global $wpdb;
        $styleid = ( ! empty( $_GET['styleid'] ) ? (int) $_GET['styleid'] : '' );
        if ( ! empty( $styleid ) && $styleid > 0 ) :
            $style = $wpdb->get_row( $wpdb->prepare( 'SELECT style_name FROM ' . $this->parent_table . ' WHERE id = %d ', $styleid ), ARRAY_A );
            $style = ucfirst( $style['style_name'] );
            $cls = '\OXI_FLIP_BOX_PLUGINS\Inc\\' . $style;
            if ( class_exists( $cls ) ) :
                new $cls();
            endif;
        else :
			new Pages\Create();
        endif;
    }

	public function Flip_Import() {
		new Pages\Import();
    }

	public function wpkin_flipbox_getting_started() {
		new Pages\GettingStarted();
    }

    public function Flip_Settings() {
		new Pages\Settings();
    }

	public function Flip_Addons() {
		new Pages\Addons();
    }
}
