<?php
/**
 * Plugin Name:       Flipbox - Awesomes Flip Boxes Image Overlay
 * Plugin URI:        https://wpkin.com
 * Description:       Flipbox - Awesomes Flip Boxes Image Overlay is the most easiest Flip builder Plugin. Create multiple Flip or  Flipboxes  with this.
 * Version:           2.10.5
 * Author:            WPKIN
 * Author URI:        https://wpkin.com
 * Text Domain:       oxi-flip-box-plugin
 * License:           GPLv2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) {
	wp_die( esc_html__( 'You can\'t access this page', 'oxi-flip-box-plugin' ) );
}

/* *
 * Including composer autoloader globally.
 *
 * @since 2.10.0
 */
require_once __DIR__ . '/vendor/autoload.php';

if ( ! function_exists( 'wpkin_fb_v' ) ) {
    // Create a helper function for easy SDK access.
    function wpkin_fb_v() {
        global $wpkin_fb_v;

        if ( ! isset( $wpkin_fb_v ) ) {
            $wpkin_fb_v = fs_dynamic_init(
                [
					'id'                  => '20098',
					'slug'                => 'oxi-flip-box-ultimate',
					'type'                => 'plugin',
					'public_key'          => 'pk_1332bbabb9a9c9654b5703b6cd244',
					'is_premium'          => false,
					'has_premium_version' => false,
					'has_addons'          => false,
					'has_paid_plans'      => true,
					'menu'                => [
						'slug'       => 'oxi-flip-box-ultimate',
						'first-path' => 'admin.php?page=flipbox-getting-started',
						'contact'        => false,
						'support'        => false,
						'pricing'        => false,
					],
				]
            );
        }

        return $wpkin_fb_v;
    }

    // Init Freemius.
    wpkin_fb_v();
    // Signal that SDK was initiated.
    do_action( 'wpkin_fb_v_loaded' );
}

/** If class `WPKin_Flipbox` doesn't exists yet. */
if ( ! class_exists( 'WPKin_Flipbox' ) ) {

	/**
	 * Sets up and initializes the plugin.
	 * Main initiation class
	 *
	 * @since 1.0.0
	 */
	final class WPKin_Flipbox {

		use \OXI_FLIP_BOX_PLUGINS\Inc_Helper\Public_Helper;
    	use \OXI_FLIP_BOX_PLUGINS\Inc_Helper\Admin_helper;

		/**
		 * Plugin Version
		 */
		const VERSION = '2.10.5';

		/**
		 * Php Version
		 */
		const MIN_PHP_VERSION = '7.4';

		/**
		 * WordPress Version
		 */
		const MIN_WP_VERSION = '6.2';



		const ADMINMENU = 'get_oxilab_addons_menu';

		/**
		 * Class Constractor
		 */
		private function __construct() {

			$this->define_constance();
			register_activation_hook( __FILE__, [ $this, 'activate' ] );
			register_deactivation_hook( __FILE__, [ $this, 'deactivate' ] );
			add_action( 'upgrader_process_complete', [ $this, 'wpkin_upgrader_process_complete' ], 10, 2 );
			do_action( 'oxi-flip-box-plugin/before_init' );
			// Load translation
			add_action( 'init', [ $this, 'i18n' ] );
			add_action( 'plugins_loaded', [ $this, 'init_plugin' ] );
		}

		/**
		 * Initilize a singleton instance
		 *
		 * @return /Product_Layouts
		 */
		public static function init() {

			static $instance = false;

			if ( ! $instance ) {
				$instance = new self();
			}

			return $instance;
		}

		/**
		 * Plugin Constance
		 *
		 * @return void
		 */
		public function define_constance() {

			define( 'OXI_FLIP_BOX_FILE', __FILE__ );
			define( 'OXI_FLIP_BOX_BASENAME', plugin_basename( OXI_FLIP_BOX_FILE ) );
			define( 'OXI_FLIP_BOX_PATH', plugin_dir_path( OXI_FLIP_BOX_FILE ) );
			define( 'OXI_FLIP_BOX_URL', plugins_url( '/', OXI_FLIP_BOX_FILE ) );
			define( 'OXI_FLIP_BOX_PLUGIN_VERSION', self::VERSION );
			define( 'OXI_FLIP_BOX_MINIMUM_PHP_VERSION', self::MIN_PHP_VERSION );
			define( 'OXI_FLIP_BOX_MINIMUM_WP_VERSION', self::MIN_WP_VERSION );
		}

		/**
		 * Load Textdomain
		 *
		 * Load plugin localization files.
		 *
		 * Fired by `init` action hook.
		 *
		 * @since 1.0.0
		 *
		 * @access public
		 */
		public function i18n() {
			load_plugin_textdomain( 'oxi-flip-box-plugin', false, dirname( OXI_FLIP_BOX_BASENAME ) . '/languages/' );
		}

		/**
		 * After Activate Plugin
		 *
		 * Fired by `register_activation_hook` hook.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 *
		 * @access public
		 */
	public function activate() {
		if ( ! class_exists( '\OXI_FLIP_BOX_PLUGINS\Includes\Installation' ) ) {
			require_once __DIR__ . '/Includes/Installation.php';
		}
		$Installation = new \OXI_FLIP_BOX_PLUGINS\Includes\Installation();
		$Installation->plugin_activation_hook();
	}

		/**
		 * After Deactivate Plugin
		 *
		 * Fired by `register_deactivation_hook` hook.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 *
		 * @access public
		 */
		public function deactivate() {
		}

		/**
		 * Plugins Loaded
		 *
		 * @return void
		 */
		public function init_plugin() {
			new OXI_FLIP_BOX_PLUGINS\Includes\Assets();
			$this->Shortcode_loader();
			$this->Public_loader();
			if ( is_admin() ) {
				new OXI_FLIP_BOX_PLUGINS\Includes\Admin();
				$this->Admin_Filters();
				$this->User_Admin();
				$this->User_Reviews();
			}
		}

		/**
		 * Upgrade hook
		 *
		 * @since 2.3.0
		 */
	public function wpkin_upgrader_process_complete( $upgrader_object, $options ) {
		if ( ! class_exists( '\OXI_FLIP_BOX_PLUGINS\Includes\Installation' ) ) {
			require_once __DIR__ . '/Includes/Installation.php';
		}
		$Installation = new \OXI_FLIP_BOX_PLUGINS\Includes\Installation();
		$Installation->plugin_upgrade_hook( $upgrader_object, $options );
	}

		/**
		 * Shortcode loader
		 *
		 * @since 3.1.0
		 * @access public
		 */
		protected function Shortcode_loader() {
			add_shortcode( 'oxilab_flip_box', [ $this, 'wp_shortcode' ] );
			new \OXI_FLIP_BOX_PLUGINS\Modules\Visual_Composer();
			$Flipbox_Widget = new \OXI_FLIP_BOX_PLUGINS\Modules\Widget();
			add_filter( 'widget_text', 'do_shortcode' );
			add_action( 'widgets_init', [ $Flipbox_Widget, 'flip_register_flipwidget' ] );
		}

		/**
		 * Execute Shortcode
		 *
		 * @since 3.1.0
		 * @access public
		 */
		public function wp_shortcode( $atts ) {
			extract( shortcode_atts( [ 'id' => ' ' ], $atts ) );
			$styleid = $atts['id'];
			ob_start();
			$this->shortcode_render( $styleid, 'user' );
			return ob_get_clean();
		}

		public function Admin_Filters() {
			add_filter( $this->fixed_data( '6f78692d666c69702d626f782d737570706f72742d616e642d636f6d6d656e7473' ), [ $this, $this->fixed_data( '537570706f7274416e64436f6d6d656e7473' ) ] );
			add_filter( $this->fixed_data( '6f78692d666c69702d626f782d706c7567696e2f70726f5f76657273696f6e' ), [ $this, $this->fixed_data( '636865636b5f63757272656e745f74616273' ) ] );
			add_filter( $this->fixed_data( '6f78692d666c69702d626f782d706c7567696e2f61646d696e5f6d656e75' ), [ $this, $this->fixed_data( '6f78696c61625f61646d696e5f6d656e75' ) ] );
		}

		public function User_Admin() {
			add_action( 'admin_head', [ $this, 'Admin_Icon' ] );
			add_action( 'wp_ajax_oxi_flip_box_data', [ $this, 'data_process' ] );
			add_action( 'admin_head', [ $this, 'welcome_remove_menus' ] );
		}
	}

}

/**
 * Initilize the main plugin
 *
 * @return /WPKin_Flipbox
 */
function wpkin_flipbox() {

	if ( class_exists( 'WPKin_Flipbox' ) ) {
		return WPKin_Flipbox::init();
	}

	return false;
}

/**
 * Kick-off the plugin
 */
wpkin_flipbox();
