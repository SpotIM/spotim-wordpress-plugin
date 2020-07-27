<?php
/**
 * Plugin Name:         OpenWeb.com Comments
 * Plugin URI:          https://wordpress.org/plugins/spotim-comments/
 * Description:         Real-time comments widget turns your site into its own content-circulating ecosystem.
 * Version:             4.5.2
 * Author:              OpenWeb.com
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
 * WP_SpotIM
 *
 * A general class for Spot.IM comments for WordPress.
 *
 * @since 1.0.2
 */
class WP_SpotIM {

    /**
     * Instance
     *
     * @since  1.0.2
     *
     * @access private
     * @static
     *
     * @var WP_SpotIM
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
        $this->options = SpotIM_Options::get_instance();

        // Run the plugin
        new SpotIM_i18n();
        new SpotIM_Cron( $this->options );
        new SpotIM_Feed();

        if ( is_admin() ) {

            // Admin Page
            new SpotIM_Admin( $this->options );

        } else {

            // Frontend code: embed script, comments template, comments count.
            new SpotIM_Frontend( $this->options );

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
     * @return WP_SpotIM
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
            'helpers/class-spotim-form.php',
            'helpers/class-spotim-message.php',
            'helpers/class-spotim-comment.php',
            'helpers/class-spotim-json-feed.php',
            'helpers/class-spotim-wp.php',
            'class-spotim-i18n.php',
            'class-spotim-import.php',
            'class-spotim-options.php',
            'class-spotim-settings-fields.php',
            'class-spotim-metabox.php',
            'class-spotim-admin.php',
            'class-spotim-frontend.php',
            'class-spotim-feed.php',
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
 * Spotim Instance
 *
 * @since 1.0
 *
 * @return WP_SpotIM
 */
function spotim_instance() {
    return WP_SpotIM::get_instance();
}

add_action( 'plugins_loaded', 'spotim_instance', 0 );

/**
 * Check if current environment is `VIP-GO` or not.
 *
 * @return bool returns true if current site is available on VIP-GO, otherwise false
 */
function spotim_is_vip() {
    if ( defined( 'SPOTIM_IS_VIP_DEBUG' ) && SPOTIM_IS_VIP_DEBUG ) { // Setting WPCOM_IS_VIP_ENV in local won't work.
        return true;
    }

    if ( defined( 'WPCOM_IS_VIP_ENV' ) && true === WPCOM_IS_VIP_ENV ) {
        return true;
    } else {
        return false;
    }
}
