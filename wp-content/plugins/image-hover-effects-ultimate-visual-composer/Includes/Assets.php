<?php

namespace OXI_FLIP_BOX_PLUGINS\Includes;

/**
 * Assets Handler Class
 *
 * @since 2.10.1
 */
class Assets {

	/**
	 * Assets class constructor
	 *
	 * @since 2.10.1
	 */
	public function __construct() {

		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scriptss' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'public_enqueue_scripts' ] );
	}

	/**
	 * Method admin_enqueue_scriptss.
	 *
	 * @since 2.10.1
	 */
	public function admin_enqueue_scriptss() {
		$current_screen = get_current_screen()->id;
		$current_page   = isset( $_GET['page'] ) && $_GET['page'] ? $_GET['page'] : '';

		wp_enqueue_style( 'oxi_flip-global-admin-style', OXI_FLIP_BOX_URL . 'asset/backend/css/global-admin.css', false, OXI_FLIP_BOX_PLUGIN_VERSION );

		if ( 'flipbox-getting-started' === $current_page ) {
			//CSS
			wp_enqueue_style( 'flip-box-admin-welcome', OXI_FLIP_BOX_URL . 'asset/backend/css/getting-started.css', false, filemtime( OXI_FLIP_BOX_PATH . 'asset/backend/css/getting-started.css' ) );
			//JS
			wp_enqueue_script( 'flip-box-admin-welcome-js', OXI_FLIP_BOX_URL . 'asset/backend/js/getting-started.js', [ 'jquery' ], filemtime( OXI_FLIP_BOX_PATH . 'asset/backend/js/getting-started.js' ), true );
		}
	}

	/**
	 * Method public_enqueue_scripts.
	 *
	 * @since 2.10.1
	 */
	public function public_enqueue_scripts() {
	}
}
