<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * OW_i18n
 *
 * Plugin translation files.
 *
 * @since 4.0.1
 */
class OW_i18n {

	/**
	 * Constructor
	 *
	 * Get things started.
	 *
	 * @since  4.0.1
	 *
	 * @access public
	 */
	public function __construct() {

		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

	}

	/**
	 * Load text domain
	 *
	 * Load the plugin translation text domain.
	 *
	 * @since  4.0.1
	 *
	 * @access public
	 */
	public function load_textdomain() {

		load_plugin_textdomain( 'ow' );

	}

}
