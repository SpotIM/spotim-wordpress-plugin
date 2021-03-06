<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * OW_Options
 *
 * Plugin options.
 *
 * @since 2.0.0
 * @since 5.0.0 Renamed from 'SpotIM_Options' to 'OW_Options'.
 */
class OW_Options {

    /**
     * Instance
     *
     * @since  2.0.0
     *
     * @access private
     * @static
     *
     * @var OW_Options
     */
    private static $instance;

    /**
     * Data
     *
     * @since  2.0.0
     *
     * @access private
     *
     * @var array
     */
    private $data;

    /**
     * Default Options
     *
     * @since  2.0.0
     *
     * @access private
     *
     * @var array
     */
    private $default_options;

    /**
     * Slug
     *
     * @since  2.0.0
     *
     * @access public
     *
     * @var string
     */
    public $slug;

    /**
     * Option Group
     *
     * @since  2.0.0
     *
     * @access public
     *
     * @var string
     */
    public $option_group;

    /**
     * Active Tab
     *
     * @since  4.0.0
     *
     * @access public
     *
     * @var string
     */
    public $active_tab;

    /**
     * Constructor
     *
     * Get things started.
     *
     * @since  2.0.0
     *
     * @access protected
     */
    protected function __construct() {
        $this->slug            = OW_OPTION_SLUG;
        $this->option_group    = OW_OPTION_GROUP_NAME;
        $this->default_options = OW_SETTING_DEFAULT_OPTIONS;

        $this->data = $this->get_meta_data();

        $tab = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING );

        // Tab value is stored and only used for current tab verification.
        $this->active_tab = ( ! empty( $tab ) ) ? $tab : 'general';

    }

    /**
     * Get Instance
     *
     * @since  2.0.0
     *
     * @access public
     * @static
     *
     * @return OW_Options
     */
    public static function get_instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Create Options
     *
     * @since  2.0.0
     *
     * @access private
     *
     * @return array
     */
    private function create_options() {

        // Check old options.
        $old_options = get_option( 'wp-spotim-settings', [] );
        $options     = $this->default_options;

        if ( ! empty( $old_options ) ) {

            $options = wp_parse_args( $old_options, $options );

            if ( ! empty( $options['spot_id'] ) && empty( $options['ow_id'] ) ) {
                $options['ow_id'] = $options['spot_id'];
                unset( $options['spot_id'] ); // Remove this from array as its not required in new setting.
            }

            if ( isset( $options['spotim_last_sync_timestamp'] ) && empty( $options['last_sync_timestamp'] ) ) {
                $options['last_sync_timestamp'] = $options['spotim_last_sync_timestamp'];
            }

            $this->update_other_setting();

        }

        update_option( $this->slug, $options );

        return $options;
    }

    /**
     * Get Meta Data
     *
     * @since  2.0.0
     *
     * @access private
     *
     * @return array
     */
    private function get_meta_data() {
        $data = get_option( $this->slug, array() );

        if ( empty( $data ) ) {
            $data = $this->create_options();
        } else {
            $data['display_comments_count'] = sanitize_text_field( $data['display_comments_count'] );
            $data['display_post']           = sanitize_text_field( $data['display_post'] );
            $data['display_page']           = sanitize_text_field( $data['display_page'] );
            $data['display_attachment']     = sanitize_text_field( $data['display_attachment'] );
            $data['display_newsfeed']       = sanitize_text_field( $data['display_newsfeed'] );
            $data['display_rc_amp_ad_tag']  = isset( $data['display_rc_amp_ad_tag'] ) ? sanitize_text_field( $data['display_rc_amp_ad_tag'] ) : $this->default_options['display_rc_amp_ad_tag'];
            $data['enable_rating_reviews']  = isset( $data['enable_rating_reviews'] ) ? sanitize_text_field( $data['enable_rating_reviews'] ) : $this->default_options['enable_rating_reviews'];

            $data = array_merge( $this->default_options, $data );
        }

        return $data;
    }

    /**
     * Get
     *
     * @since  2.0.0
     *
     * @access public
     *
     * @param string     $key
     * @param array|bool $default_value
     *
     * @return array|false
     */
    public function get( $key = '', $default_value = false ) {
        return isset( $this->data[ $key ] ) ? $this->data[ $key ] : $default_value;
    }

    /**
     * Update
     *
     * @since  3.0.0
     *
     * @access public
     *
     * @param string $name  Option Name.
     * @param string $value Option Value.
     *
     * @return array
     */
    public function update( $name, $value ) {

        $new_option          = array();
        $new_option[ $name ] = $value;

        // Validate new option and retrieve with old ones to update as a whole
        $options = $this->validate( $new_option );

        $options_updated = update_option( $this->slug, $options );

        if ( $options_updated ) {
            $this->data = $options;
        }

        // return updated value
        return $this->data[ $name ];
    }

    /**
     * Reset
     *
     * @since  3.0.0
     *
     * @access public
     *
     * @param string $name
     *
     * @return array
     */
    public function reset( $name ) {
        $value = $this->get( $name );

        switch ( gettype( $value ) ) {
            case 'number':
                $value = 0;
                break;
            case 'string':
                $value = '';
                break;
            case 'boolean':
            default:
                $value = false;
        }

        return $this->update( $name, $value );
    }

    /**
     * Validate
     *
     * @since  3.0.0
     *
     * @access public
     *
     * @param array $input
     *
     * @return array
     */
    public function validate( $input ) {
        $options = $this->get_meta_data();

        foreach ( $input as $key => $value ) {
            switch ( $key ) {
                case 'display_comments_count':
                case 'display_post':
                case 'display_page':
                case 'display_attachment':
                case 'display_newsfeed':
                case 'display_rc_amp_ad_tag':
                case 'enable_rating_reviews':
                    $options[ $key ] = sanitize_text_field( $value );
                    break;
                case 'posts_per_request':
                    $value           = absint( $value );
                    $options[ $key ] = 0 === $value ? 1 : $value;
                    break;
                case 'page_number':
                    $options[ $key ] = absint( $value );
                    break;
                case 'auto_import':
                    $options[ $key ] = sanitize_text_field( $value );
                    // update scheduled cron job interval
                    $old_interval = wp_get_schedule( 'ow_scheduled_import' );
                    $new_interval = $value;
                    if ( $old_interval !== $new_interval ) {
                        wp_clear_scheduled_hook( 'ow_scheduled_import' );
                        wp_schedule_event( time(), $new_interval, 'ow_scheduled_import' );
                    }
                    break;
                case 'ow_id':
                case 'import_token':
                default:
                    $options[ $key ] = sanitize_text_field( $value );
                    break;
            }
        }

        return $options;
    }

    /**
     * Require File
     *
     * @since  2.1.0
     *
     * @access public
     *
     * @param string       $path
     * @param string|false $return_path
     *
     * @return string
     */
    public function require_file( $path = '', $return_path = false ) {
        $valid = validate_file( $path );

        if ( 0 === $valid || false === strpos( $path, '..' ) ) {
            if ( $return_path ) {
                $output = $path;
            } else {
                require_once $path; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
                $output = $valid;
            }
        } else {
            if ( $return_path ) {
                $output = '';
            } else {
                $output = $valid;
            }
        }

        return $output;
    }

    /**
     * Require Template
     *
     * @since  2.1.0
     *
     * @access public
     *
     * @param string       $path
     * @param string|false $return_path
     *
     * @return string
     */
    public function require_template( $path = '', $return_path = false ) {
        $path = plugin_dir_path( dirname( __FILE__ ) ) . 'templates/' . $path;

        return $this->require_file( $path, $return_path );
    }

    /**
     * Require JavaScript
     *
     * @since  3.0.0
     *
     * @access public
     *
     * @param string       $path
     * @param string|false $return_path
     *
     * @return string
     */
    public function require_javascript( $path = '', $return_path = false ) {
        $path = plugin_dir_url( dirname( __FILE__ ) ) . 'assets/javascripts/' . $path;

        return $this->require_file( $path, $return_path );
    }

    /**
     * Require Stylesheet
     *
     * @since  3.0.0
     *
     * @access public
     *
     * @param string       $path
     * @param string|false $return_path
     *
     * @return string
     */
    public function require_stylesheet( $path = '', $return_path = false ) {
        $path = plugin_dir_url( dirname( __FILE__ ) ) . 'assets/stylesheets/' . $path;

        return $this->require_file( $path, $return_path );
    }

    /**
     * Get next cron execution
     *
     * @since  4.0.0
     *
     * @access public
     *
     * @param int $timestamp
     *
     * @return string
     */
    function get_next_cron_execution( $timestamp ) {

        // Auto import recurrence
        $recurrence = $this->get( 'auto_import' );

        // Get allowed schedules
        $allowed_schedules    = array();
        $registered_schedules = wp_get_schedules();
        if ( ! empty( $registered_schedules ) ) {
            foreach ( $registered_schedules as $key => $value ) {
                unset( $value );
                $allowed_schedules[] = $key;
            }
        }

        // Check if auto import enabled
        if ( ! in_array( $recurrence, $allowed_schedules, true ) ) {
            return;
        }

        // Return the next cron execution text
        if ( ( $timestamp - time() ) <= 0 ) {
            return esc_html__( 'Next sync on next page refresh.', 'spotim-comments' );
        } else {
            return sprintf(
                esc_html__( 'Next sync in %s.', 'spotim-comments' ),
                human_time_diff( current_time( 'timestamp' ), $timestamp )
            );
        }

    }

    /**
     * Update other settings.
     *
     * @return void
     */
    protected function update_other_setting() {

        // Return if class not exist.
        if ( ! class_exists( 'OW_Activation_Upgrader_Process' ) ) {
            return;
        }

        $ow_activation_upgrader_process = OW_Activation_Upgrader_Process::get_instance();

        // Migrate single options.
        $ow_activation_upgrader_process->migrate_option( 'wp-spotim-settings_total_changed_posts', 'wp-ow-settings_total_changed_posts', [] );

        // Remove old cron.
        $ow_activation_upgrader_process->check_and_delete_old_cron();

    }

}
