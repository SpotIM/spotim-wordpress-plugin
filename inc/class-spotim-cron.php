<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * SpotIM_Cron
 *
 * Plugin auto import cron job.
 *
 * @since 4.0.0
 */
class SpotIM_Cron {

    /**
     * Options
     *
     * @since  4.0.0
     *
     * @access private
     * @static
     *
     * @var SpotIM_Options
     */
    private static $options;

    /**
     * Constructor
     *
     * Get things started.
     *
     * @since  4.0.0
     *
     * @param SpotIM_Options $options Plugin options.
     *
     * @access public
     */
    public function __construct( $options ) {
        self::$options = $options;
        add_action( 'wp_loaded', array( $this, 'auto_import_cron_job' ) );
    }

    /**
     * Auto import cron job
     *
     * @since  4.0.0
     *
     * @access public
     *
     * @return void
     */
    public function auto_import_cron_job() {

        // Auto import interval
        $interval = self::$options->get( 'auto_import' );

        // Check if auto import enabled
        if ( ! in_array( $interval, array_keys( wp_get_schedules() ), true ) ) {
            return;
        }

        // Schedule cron job event, if not scheduled yet
        if ( ! wp_next_scheduled( 'spotim_scheduled_import', array() ) ) {
            wp_schedule_event( time(), $interval, 'spotim_scheduled_import' );
        }

        // Run cron job hook - import data
        add_action( 'spotim_scheduled_import', array( $this, 'run_import' ) );

    }

    /**
     * Run import
     *
     * @since  4.0.0
     *
     * @access public
     *
     * @return void
     */
    public function run_import() {

        // Get auto_sync token value.
        $execution_token = get_transient( 'spotim_auto_sync_cron_token' );

        // Are we currently running an auto-sync?
        if ( false !== $execution_token ) {
            // We have a Cron job running already, let's quit here
            return;
        }

        // Create execution token for facilitating a lock mechanism
        $execution_token = $this->generate_single_execution_token();

        // Register this job with a temporary transient
        set_transient( 'spotim_auto_sync_cron_token', $execution_token, $this->get_lock_interval() );

        $spot_id           = sanitize_text_field( self::$options->get( 'spot_id' ) );
        $import_token      = sanitize_text_field( self::$options->get( 'import_token' ) );
        $page_number       = 0;
        $posts_per_request = self::$options->get( 'posts_per_request' );
        $posts_per_request = ( ! empty( $posts_per_request ) ) ? absint( $posts_per_request ) : 10;

        if ( empty( $spot_id ) ) {
            return;
        }

        if ( empty( $import_token ) ) {
            return;
        }

        $this->set_time_limit( 0 );

        $import   = new SpotIM_Import( self::$options, true );
        $response = false;

        // Iterate over all posts, in bumps of $posts_per_iteration
        $import->log( 'Starting Cron auto-sync. Ex. ' . $execution_token );

        do {
            $import->log( 'Iteration (start) #' . $page_number . ', Token:' . $execution_token );
            $import->log( $spot_id, $import_token, $page_number, $posts_per_request );

            // Launch import for $posts_per_request posts on page $page_number
            $response = $import->start( $spot_id, $import_token, $page_number, $posts_per_request );

            $import->log( 'Iteration (end) #' . $page_number . ', Token:' . $execution_token );
            $import->log( $response );

            if ( $response && 'continue' === $response['status'] ) {
                $page_number ++;
            } // Increment

        } while ( $response && ( 'continue' === $response['status'] || 'refresh' === $response['status'] ) );

        $import->log( 'Finished Cron auto-sync', $response );

        // Delete lock
        delete_transient( 'spotim_auto_sync_cron_token' );

        // Are we successful?
        if ( 'success' === $response['status'] ) {
            $import->log( sprintf( 'Auto-sync ID %s finished successfully', $execution_token ) );
        } else {
            $import->log( sprintf( 'Auto-sync ID %s FAILED', $execution_token ) );
            $import->log( $response );
        }
    }

    /**
     * Get lock interval
     *
     * @since  4.3.0
     *
     * @access private
     *
     * @return int Time interval.
     */
    private function get_lock_interval() {
        $schedules = wp_get_schedules();
        $interval  = self::$options->get( 'auto_import' );

        // Check if schedule exists in WP
        if ( ! in_array( $interval, array_keys( $schedules ), true ) ) {
            return 0;
        }

        $full_interval = absint( $schedules[ $interval ]['interval'] );

        return floor( $full_interval / 2 );
    }

    /**
     * Generate single execution token
     *
     * @since  4.3.0
     *
     * @access private
     *
     * @return string Execution token.
     */
    private function generate_single_execution_token() {
        return sprintf( 'spotim_exec_%s', time() );
    }

    /**
     * Set Time Limit
     *
     * Wrapper for set_time_limit to see if it is enabled.
     *
     * @since  4.3.0
     *
     * @access private
     */
    private function set_time_limit( $limit = 0 ) {
        if ( function_exists( 'set_time_limit' ) && false === strpos( ini_get( 'disable_functions' ), 'set_time_limit' ) ) {
            set_time_limit( $limit );
        }
    }

}
