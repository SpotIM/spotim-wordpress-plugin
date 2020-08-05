<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * OW_Activation_Upgrader_Process.
 *
 * Plugin activation and upgrader actions..
 *
 * @since 5.0.0
 */
class OW_Activation_Upgrader_Process {

    /**
     * Instance
     *
     * @since  5.0.0
     *
     * @access private
     * @static
     *
     * @var OW_Activation_Upgrader_Process
     */
    private static $instance;

    /**
     * Constructor
     *
     * Get things started.
     *
     * @since 5.0.0
     *
     * @access protected
     */
    protected function __construct() {
        $this->slug            = OW_OPTION_SLUG;
        $this->default_options = OW_SETTING_DEFAULT_OPTIONS;
    }

    /**
     * Get Instance
     *
     * @since  5.0.0
     *
     * @access public
     * @static
     *
     * @return OW_Activation_Upgrader_Process
     */
    public static function get_instance() {

        if ( is_null( self::$instance ) ) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Function to execute on plugin activation.
     *
     * @since  5.0.0
     *
     * @return void
     */
    public function plugin_activation() {

        $plugin_main_file = 'spotim-comments/spotim-comments.php';
        $plugin_header    = $this->get_plugin_header( $plugin_main_file );

        // Check if old option is available.
        // If not available then set default setting.
        $this->check_and_update_settings( $plugin_header );
    }

    /**
     * Function to migrate old setting to new setting options.
     *
     * @since  5.0.0
     *
     * @param Object $plugin_updater_object Plugin updater class object.
     * @param array  $options               Action options.
     *
     * @return void
     */
    public function plugin_upgrade( $plugin_updater_object, $options ) {

        // Return if plugin details not found.
        if ( empty( $plugin_updater_object ) || empty( $plugin_updater_object->result ) || empty( $plugin_updater_object->result['destination_name'] ) ) {
            return;
        }

        $plugin_slug = $plugin_updater_object->result['destination_name'];

        // If plugin is not spotim comments plugin then return.
        if ( 'spotim-comments' !== $plugin_slug ) {
            return;
        }

        $plugin_main_file = $plugin_updater_object->plugin_info();
        $plugin_header    = $this->get_plugin_header( $plugin_main_file );

        // Check if old option is available.
        // If not available then set default setting.
        $this->check_and_update_settings( $plugin_header );
    }

    /**
     * Function to get plugin header.
     *
     * @since  5.0.0
     *
     * @return array
     */
    protected function get_plugin_header( $plugin_main_file ) {

        // If plugin main file not found then return.
        if ( empty( $plugin_main_file ) ) {
            return [];
        }

        if ( defined( 'WP_PLUGIN_DIR' ) ) {
            $plugin_dir = WP_PLUGIN_DIR;
        } else {
            $plugin_dir = __DIR__;
        }

        $plugin_main_file_path = $plugin_dir . '/' . $plugin_main_file;

        if ( false === file_exists( $plugin_main_file_path ) ) {
            return [];
        }

        $plugin_header = get_plugin_data( $plugin_main_file_path, false, false );

        return $plugin_header;
    }

    /**
     * Function to check and update setting using old settings.
     *
     * @since  5.0.0
     *
     * @param array $plugin_header Plugin details.
     *
     * @return void
     */
    protected function check_and_update_settings( $plugin_header ) {

        // If plugin header not found or plugin version not found or
        // New plugin version less than 5.0.0-alpha then return.
        if (
            empty( $plugin_header ) ||
            empty( $plugin_header['Version'] ) ||
            version_compare( $plugin_header['Version'], '5.0.0-alpha', '<' )
        ) {
            return;
        }

        // Check setting option is already available.
        $is_setting_available = get_option( $this->slug, array() );

        // If setting option is already available then return.
        if ( ! empty( $is_setting_available ) ) {
            return;
        }

        // Old option group.
        $old_settings   = get_option( 'wp-spotim-settings', array() );
        $final_settings = wp_parse_args( $old_settings, $this->default_options );

        if ( ! empty( $final_settings['spot_id'] ) && empty( $final_settings['ow_id'] ) ) {
            $final_settings['ow_id'] = $final_settings['spot_id'];
            unset( $final_settings['spot_id'] ); // Remove this from array as its not required in new setting.
        }

        if ( isset( $final_settings['spotim_last_sync_timestamp'] ) && empty( $final_settings['ow_last_sync_timestamp'] ) ) {
            $final_settings['ow_last_sync_timestamp'] = $final_settings['spotim_last_sync_timestamp'];
        }

        // Update old options in new setting option.
        update_option( $this->slug, $final_settings );

        // Migrate single options.
        $this->migrate_option( 'wp-spotim-settings_total_changed_posts', 'wp-ow-settings_total_changed_posts', [] );
    }

    /**
     * Function to migrate option.
     *
     * @since  5.0.0
     *
     * @param string $old_option_name Old option name.
     * @param string $new_option_name New option name.
     * @param mixed  $default         Default value to set.
     *
     * @return void
     */
    protected function migrate_option( $old_option_name, $new_option_name, $default = '' ) {

        // If empty param return false.
        if ( empty( $old_option_name ) || empty( $new_option_name ) ) {
            return false;
        }

        // Get value from new option.
        $new_value = get_option( $new_option_name, $default );

        // If value found in new option then return.
        if ( ! empty( $new_value ) ) {
            return true;
        }

        // Get old option value.
        $old_value = get_option( $old_option_name, $default );

        // Set old value to new option.
        update_option( $new_option_name, $old_value );

        return true;
    }

}
