<?php
/**
 * Plugin Name:         OpenWeb.Com Comments
 * Plugin URI:          https://wordpress.org/plugins/spotim-comments/
 * Description:         Real-time comments widget turns your site into its own content-circulating ecosystem.
 * Version:             4.5.2
 * Author:              OpenWeb.Com
 * Author URI:          https://github.com/SpotIM
 * License:             GPLv2
 * License URI:         license.txt
 * Text Domain:         ow
 * GitHub Plugin URI:   git@github.com:SpotIM/wordpress-comments-plugin.git
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Store Plugin version for internal use.
if ( ! defined( 'OW_VERSION' ) ) {
	/**
	 * The version of the plugin
	 *
	 * @since  4.5.2
	 */
	define( 'OW_VERSION', '4.5.2' );
}

/**
 * WP_OW
 *
 * A general class for Spot.IM comments for WordPress.
 *
 * @since 1.0.2
 */
class WP_OW {

	/**
	 * Instance
	 *
	 * @since  1.0.2
	 *
	 * @access private
	 * @static
	 *
	 * @var WP_OW
	 */
	private static $instance;

	/**
	 * Constructor
	 *
	 * Get things started.
	 *
	 * @since  1.0.2
	 *
	 * @access protected
	 */
	protected function __construct() {

		// Load plugin files
		$this->load_files();

		// Get the Options
		$this->options = OW_Options::get_instance();

		// Run the plugin
		new OW_i18n();
		new SpotIM_Cron( $this->options );
		new OW_Feed();

		if ( is_admin() ) {

			// Admin Page
			new OW_Admin( $this->options );

		} else {

			// Frontend code: embed script, comments template, comments count.
			new OW_Frontend( $this->options );

		}

	}

	/**
	 * Get Instance
	 *
	 * @since  2.0.0
	 *
	 * @access public
	 * @static
	 *
	 * @return WP_OW
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self;
		}

		return self::$instance;

	}

	/**
	 * Load plugin files
	 *
	 * @since  4.3.0 The functionality moved to a method.
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function load_files() {

		$inc_class_dir = plugin_dir_path( __FILE__ ) . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR;

		$files = [
			'helpers/class-ow-form.php',
			'helpers/class-ow-message.php',
			'helpers/class-ow-comment.php',
			'helpers/class-ow-json-feed.php',
			'helpers/class-ow-wp.php',
			'class-ow-i18n.php',
			'class-ow-import.php',
			'class-ow-options.php',
			'class-ow-settings-fields.php',
			'class-ow-metabox.php',
			'class-ow-admin.php',
			'class-ow-frontend.php',
			'class-ow-feed.php',
			'class-spotim-cron.php',
			'spotim-shortcodes.php',
			'spotim-widgets.php',
		];

		foreach ( $files as $file ) {

			$file = $inc_class_dir . $file;

			if ( file_exists( $file ) ) {
				require_once( $file );
			}
		}

	}

}

/**
 * OW Instance
 *
 * @since 1.0
 *
 * @return WP_OW
 */
function ow_instance() {
	return WP_OW::get_instance();
}
add_action( 'plugins_loaded', 'ow_instance', 0 );

/**
 * Check if current environment is `VIP-GO` or not.
 *
 * @return bool returns true if current site is available on VIP-GO, otherwise false
 */
function ow_is_vip() {
	if ( defined( 'SPOTIM_IS_VIP_DEBUG' ) && SPOTIM_IS_VIP_DEBUG ) { // Setting WPCOM_IS_VIP_ENV in local won't work.
		return true;
	}

	if ( defined( 'WPCOM_IS_VIP_ENV' ) && true === WPCOM_IS_VIP_ENV ) {
		return true;
	} else {
		return false;
	}
}
