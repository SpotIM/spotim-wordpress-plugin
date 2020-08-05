<?php
/**
 * Plugin Name:         OpenWeb.Com Comments
 * Plugin URI:          https://wordpress.org/plugins/spotim-comments/
 * Description:         Real-time comments widget turns your site into its own content-circulating ecosystem.
 * Version:             5.0.0-alpha
 * Author:              OpenWeb.Com
 * Author URI:          https://github.com/SpotIM
 * License:             GPLv2
 * License URI:         license.txt
 * Text Domain:         spotim-comments
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
    define( 'OW_VERSION', '5.0.0-alpha' );
}

// Store deprecated filter message.
if ( ! defined( 'OW_FILTER_DEPRECATED_MESSAGE' ) ) {
    /**
     * The filter deprecation message.
     *
     * @since  5.0.0
     */
    define( 'OW_FILTER_DEPRECATED_MESSAGE', esc_html__( 'This filter may remove in future releases.', 'spotim-comments' ) );
}

// Store setting option name.
if ( ! defined( 'OW_OPTION_SLUG' ) ) {
    /**
     * Setting option name/slug.
     *
     * @since  5.0.0
     */
    define( 'OW_OPTION_SLUG', 'wp-ow-settings' );
}

// Store setting option group name.
if ( ! defined( 'OW_OPTION_GROUP_NAME' ) ) {
    /**
     * Setting option group name/slug.
     *
     * @since  5.0.0
     */
    define( 'OW_OPTION_GROUP_NAME', 'wp-ow-options' );
}

// Store setting default options.
if ( ! defined( 'OW_SETTING_DEFAULT_OPTIONS' ) ) {
    /**
     * Setting default options.
     *
     * @since  5.0.0
     */
    define( 'OW_SETTING_DEFAULT_OPTIONS', array(
        // General
        'ow_id'                        => '',
        // Display
        'display_post'                 => '1',
        'display_page'                 => '1',
        'display_attachment'           => '1',
        'comments_per_page'            => 10,
        'display_comments_count'       => '0',
        'display_newsfeed'             => '0',
        // Advanced
        'embed_method'                 => 'content',
        'rc_embed_method'              => 'regular',
        'display_rc_amp_ad_tag'        => '0',
        'enable_rating_reviews'        => '0',
        'display_priority'             => 9999,
        'enable_seo'                   => 'false',
        'enable_og'                    => 'false',
        'class'                        => 'comments-area',
        'disqus_shortname'             => '',
        'disqus_identifier'            => 'id_short_url',
        // Import
        'import_token'                 => '',
        'auto_import'                  => 0,
        'posts_per_request'            => 10,
    ) );
}

require_once plugin_dir_path( __FILE__ ) . DIRECTORY_SEPARATOR . 'inc/class-ow-activation-upgrader-process.php';

$ow_activation_upgrader_process = OW_Activation_Upgrader_Process::get_instance();

register_activation_hook( __FILE__, array( $ow_activation_upgrader_process, 'plugin_activation' ) );
add_action( 'upgrader_process_complete', array( $ow_activation_upgrader_process, 'plugin_upgrade' ), 10, 2 );

/**
 * WP_OW
 *
 * A general class for OpenWeb comments for WordPress.
 *
 * @since 1.0.2
 * @since 5.0.0 Renamed from 'WP_SpotIM' to 'WP_OW'.
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
        new OW_Cron( $this->options );
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
            'helpers/ow-functions.php',
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
            'class-ow-cron.php',
            'ow-shortcodes.php',
            'ow-widgets.php',
        ];

        foreach ( $files as $file ) {

            $file = $inc_class_dir . $file;

            if ( file_exists( $file ) ) {
                require_once $file; //phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
            }
        }

    }

}

/**
 * OW Instance
 *
 * @since 1.0
 * @since 5.0.0 Renamed from 'spotim_instance' to 'ow_instance'.
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
    if ( defined( 'OW_IS_VIP_DEBUG' ) && OW_IS_VIP_DEBUG ) { // Setting WPCOM_IS_VIP_ENV in local won't work.
        return true;
    }

    if ( defined( 'WPCOM_IS_VIP_ENV' ) && true === WPCOM_IS_VIP_ENV ) {
        return true;
    } else {
        return false;
    }
}
