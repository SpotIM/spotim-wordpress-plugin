<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * OW_Import
 *
 * Plugin import class.
 *
 * @since 3.0.0
 * @since 5.0.0 Renamed from 'SpotIM_Import' to 'OW_Import'.
 */
class OW_Import {

    /**
     * SpotIM Sync API URL
     */
    const SPOTIM_SYNC_API_URL = 'https://www.spot.im/api/open-api/v1/export/wordpress';
    const SPOTIM_LAST_MODIFIED_API_URL = 'https://www.spot.im/api/open-api/v1/spot-last-modified-conversations';

    /**
     * Options
     *
     * @since  3.0.0
     *
     * @access private
     *
     * @var OW_Options
     */
    private $options;

    private $total_changed_posts = [];

    private $needto_load_more_changed_posts = 0;

    private $is_force_sync = false;
    /**
     * Posts Per Request
     *
     * @since  3.0.0
     *
     * @access private
     *
     * @var int
     */
    private $posts_per_request;

    /**
     * Page Number
     *
     * @since  3.0.0
     *
     * @access private
     *
     * @var int
     */
    private $page_number;

    /**
     * Return values mode
     *
     * @since  4.3.0
     *
     * @access private
     *
     * @var int
     */
    private $return;

    /**
     * Constructor
     *
     * Get things started.
     *
     * @since  3.0.0
     *
     * @access public
     *
     * @param OW_Options $options Plugin options.
     * @param bool           $return  Should return mode be on? This will return values instead of echo to browser.
     *                                Default is false.
     */
    public function __construct( $options, $return = false ) {

        $this->options = $options;

        // Set default values - if not defined by the user in the settings page
        $this->posts_per_request = 100;
        $this->page_number       = 0;
        $this->return            = $return;

    }

    /**
     * Start the import.
     *
     * @since  3.0.0
     *
     * @access public
     *
     * @param int    $ow_id             OpenWeb ID.
     * @param string $import_token      Import token,
     * @param int    $page_number       Page number. Default is 0.
     * @param int    $posts_per_request Posts Per Request. Default is 1.
     * @param bool   $force             ignore post eTag and force re-sync everything.
     *
     * @return void
     */
    public function start( $ow_id, $import_token, $page_number = 0, $posts_per_request = 1, $force = false ) {

        // If not run in return mode, update these options
        if ( ! $this->return ) {

            // Save ow_id and import_token in plugin's options meta
            $this->options->update( 'ow_id', $ow_id );
            $this->options->update( 'import_token', $import_token );

            $this->page_number = $this->options->update(
                'page_number', absint( $page_number )
            );

            $this->posts_per_request              = $this->options->update(
                'posts_per_request', absint( $posts_per_request )
            );
            $this->is_force_sync                  = $this->options->get(
                'is_force_sync', false
            );
            $this->total_changed_posts            = get_option( "wp-spotim-settings_total_changed_posts", [] );
            $this->needto_load_more_changed_posts = $this->options->get( 'needto_load_more_changed_posts', 0 );
        } else {
            $this->page_number       = $page_number;
            $this->posts_per_request = $posts_per_request;
        }

        if ( $force ) {
            $this->reset_params();
            $this->is_force_sync = $this->options->update(
                'is_force_sync', true
            );
        }

        if ( ! is_array( $this->total_changed_posts ) || empty( $this->total_changed_posts ) || $this->needto_load_more_changed_posts > 0 ) {
            $this->get_changed_post_ids();
        }

        // fetch, merge comments and return a response
        $this->pull_comments( $this->total_changed_posts, $this->posts_per_request, $this->page_number * $this->posts_per_request );

        // return a response to client via json
        return $this->finish();
    }

    /**
     * Function to reset all variable and options.
     *
     * @return void
     */
    private function reset_params() {
        $this->total_changed_posts            = [];
        $this->page_number                    = $this->options->update( 'page_number', 0 );
        $this->needto_load_more_changed_posts = $this->options->update( 'needto_load_more_changed_posts', 0 );
        $this->options->update( 'spotim_last_sync_timestamp', null );
    }

    /**
     * Pull Comments
     *
     * Import comments from OpenWeb.Com and merge them.
     *
     * @since  3.0.0
     *
     * @access private
     *
     * @param array $post_ids An array of post IDs.
     *
     * @return void
     */
    private function pull_comments( $post_ids = array(), $limit = 10, $offset = 0 ) {
        if ( ! empty( $post_ids ) ) {
            // import comments data from OpenWeb.Com
            $streams = array();

            $this->log( 'Starting to fetch comments from OpenWeb.Com for ' . count( $post_ids ) . ' Posts.' );
            $streams = $this->fetch_comments( $post_ids, $limit, $offset );

            // Sync comments data with WordPress comments.
            $this->merge_comments( $streams );
        }
    }

    /**
     * Fetch Comments
     *
     * Import comments from OpenWeb.Com.
     *
     * @since  3.0.0
     *
     * @access private
     *
     * @param array $post_ids An array of post IDs.
     * @param int   $limit    Limit No of Comments.
     * @param int   $offset   Offset.
     *
     * @return array $streams An array of streams.
     */
    private function fetch_comments( $post_ids = array(), $limit, $offset ) {
        $streams         = array();
        $errored_streams = array();


        for ( $i = $offset; $i < count( $post_ids ) && $i < $limit + $offset; $i ++ ) {

            $post_id   = $post_ids[ $i ];

            $this->log( 'Syncing Comments for Post Id :- ' . $post_id );

            $post_etag = $this->is_force_sync ? 0 : ow_get_post_meta( $post_id, 'ow_etag', true );

            $stream = $this->request( array(
                'spot_id' => $this->options->get( 'ow_id' ),
                'post_id' => $post_id,
                'etag'    => absint( $post_etag ),
                'count'   => 1000,
                'token'   => $this->options->get( 'import_token' )
            ) );

            if ( $stream->is_ok ) {

                $this->log( 'Successfully Synced Post Id :- ' . $post_id . PHP_EOL );
                // Posts that synced successfully
                $streams[] = $stream->body;
            } else {

                $this->log( 'Sync Errored for Post Id :- ' . $post_id );
                $this->log( $stream->body );
                // Posts that returned errors
                $errored_streams[] = $stream->body;
            }
        }

        // Did we have any errors?
        if ( ! empty( $errored_streams ) ) {
            $this->errored_streams = $errored_streams;
        }

        return $streams;
    }

    /**
     * Merge Comments
     *
     * Sync comments data with WordPress comments.
     *
     * @since  3.0.0
     *
     * @access private
     *
     * @param array $streams An array of streams.
     *
     * @return void
     */
    private function merge_comments( $streams = array() ) {
        while ( ! empty( $streams ) ) {
            $stream    = array_shift( $streams );
            $did_error = false;

            if ( $stream->from_etag < $stream->new_etag ) {
                if ( ! empty( $stream->events ) ) {
                    $sync_status = OW_Comment::sync(
                        $stream->events,
                        $stream->users,
                        $stream->post_id
                    );

                    if ( ! $sync_status ) {

                        $return = array(
                            'status'  => 'error',
                            'message' => sprintf(
                                esc_html__( 'Could not import comments of from this url: %s', 'spotim-comments' ),
                                esc_attr( $stream->url )
                            )
                        );

                        $this->log( $return );
                        $did_error = true; // Set to true so that post meta isn't updated.
                    } else {
                        $did_error = false; // Set to false to keep existing behaviour.
                    }
                }

                if ( false === $did_error ) {
                    update_post_meta(
                        absint( $stream->post_id ),
                        'spotim_etag',
                        absint( $stream->new_etag ),
                        absint( $stream->from_etag )
                    );
                }
            }
        }
    }

    /**
     * Finish
     *
     * Return a response to client via json.
     *
     * @since  3.0.0
     *
     * @access private
     *
     * @return mixed
     */
    private function finish() {
        $response_args = array(
            'status'  => '',
            'message' => ''
        );

        $total_posts_count   = $this->get_posts_count();
        $current_posts_count = $this->posts_per_request;

        if ( 0 < $this->page_number ) {
            $current_posts_count = $current_posts_count + ( $this->posts_per_request * $this->page_number );
        }

        if ( 0 === $total_posts_count ) {
            $this->options->reset( 'is_force_sync', false );
            $response_args['status']  = 'success';
            $response_args['message'] = sanitize_text_field( __( 'Your website doesn\'t have any posts to sync.', 'spotim-comments' ) );
        } else if ( $this->needto_load_more_changed_posts > 0 ) {
            $parsed_message = sprintf(
                esc_html__( 'Loading %d total posts to sync.', 'spotim-comments' ),
                $total_posts_count
            );

            $response_args['status']  = 'refresh';
            $response_args['message'] = $parsed_message;

        } else if ( $current_posts_count < $total_posts_count ) {
            $parsed_message = sprintf(
                esc_html__( '%d / %d posts are synchronizing.', 'spotim-comments' ),
                $current_posts_count,
                $total_posts_count
            );

            $response_args['status'] = 'continue';

            if ( isset( $this->errored_streams ) && $this->errored_streams ) {
                $response_args['status']   = 'error';
                $parsed_message            .= ' ' . esc_html__( 'Some posts have errored.', 'spotim-comments' );

                // Sanitize the error message before sending response.
                if ( is_array( $this->errored_streams ) ) {
                    $this->errored_streams = array_map( 'esc_js', $this->errored_streams );
                } else {
                    $this->errored_streams = esc_js( $this->errored_streams );
                }

                $response_args['messages'] = $this->errored_streams;

                $this->log( 'Some posts have errored.' );
                $this->log( $this->errored_streams );
            }

            $response_args['message'] = $parsed_message;
        } else {
            $response_args['status']  = 'success';
            $response_args['message'] = sprintf( '%s ' . esc_html__( 'posts has been synced.', 'spotim-comments' ), $total_posts_count );

            $this->options->update( 'spotim_last_sync_timestamp', time() );
            $this->options->reset( 'is_force_sync', false );
            update_option( 'wp-spotim-settings_total_changed_posts', [] );

            if ( ! $this->return ) {
                $this->options->reset( 'page_number' );
            }
        }

        return ( $this->return ) ? $response_args : $this->response( $response_args );
    }

    /**
     * Get post count
     *
     * Retrieves count for all need to update posts
     *
     * @since  4.2.0
     *
     * @access public
     *
     * @return int
     */
    public function get_posts_count() {

        if ( $this->total_changed_posts ) {
            return count( $this->total_changed_posts );
        }

        return 0;
    }

    /**
     * Get Changed Post IDs
     *
     * Retrieve an array of changed post IDs.
     *
     * @since  3.0.0
     *
     * @access private
     *
     * @param int $posts_per_page Posts per page (Max 100). Default is 100.
     * @param int $page_number    Page number. Default is 0.
     *
     * @return array
     */
    private function get_changed_post_ids() {

        $offset = $this->needto_load_more_changed_posts;
        $limit  = 5000;

        $ow_id = $this->options->get( 'ow_id' );
        $sec_ago = $this->options->get( 'spotim_last_sync_timestamp', null );

        if ( ! $sec_ago ) {
            $sec_ago = time() + ( 60 * 30 );
        } else {
            $sec_ago = time() + ( 60 * 30 ) - $sec_ago;
        }

        $stream = $this->request( array(
            'spot_id' => $ow_id,
            'sec_ago' => $sec_ago,
            'limit'   => $limit,
            'offset'  => $offset
        ), self::SPOTIM_LAST_MODIFIED_API_URL );

        $body = ( $stream && isset( $stream->body ) ) ? $stream->body : null;

        if ( is_array( $body ) ) {

            $body = array_map( function ( $val ) use ( $ow_id ) {
                return str_replace( $ow_id . "_", '', $val );
            }, $body );

            if ( ! $this->total_changed_posts ) {
                $this->total_changed_posts = [];
            }

            $this->total_changed_posts = array_merge( $body, $this->total_changed_posts );
            update_option( 'wp-spotim-settings_total_changed_posts', $this->total_changed_posts );

            $offset += $limit;

            if ( count( $body ) > 0 ) {
                $this->needto_load_more_changed_posts = $this->options->update( 'needto_load_more_changed_posts', $offset );
                $this->finish();
            } else {
                $this->needto_load_more_changed_posts = $this->options->update( 'needto_load_more_changed_posts', 0 );
            }
        }
    }

    /**
     * Get Post IDs
     *
     * Retrieve an array of post IDs.
     *
     * @since  3.0.0
     *
     * @access private
     *
     * @param int $posts_per_page Posts per page (Max 100). Default is 100.
     * @param int $page_number    Page number. Default is 0.
     *
     * @return array
     */
    private function get_post_ids( $posts_per_page = 100, $page_number = 0 ) {
        // Set limits for post per page
        if ( $posts_per_page > 100 || $posts_per_page < 0 ) {
            $posts_per_page = 100;
        }

        // Set default values
        $args = array(
            'posts_per_page' => $posts_per_page,
            'post_type'      => array( 'post' ),
            'post_status'    => 'publish',
            'orderby'        => 'id',
            'order'          => 'ASC',
            'fields'         => 'ids'
        );

        // Set offset
        if ( 0 !== $page_number ) {
            $args['offset'] = $posts_per_page * $page_number;
        }

        if ( 1 === $this->options->get( 'enable_comments_on_page' ) ) {
            $args['post_type'][] = 'page';
        }

        $query = new WP_Query( $args );
        if ( $query->have_posts() ) {
            $post_ids = $query->posts;
        } else {
            $post_ids = array();
        }

        return $post_ids;
    }

    /**
     * Request
     *
     * Retrieve data from a remote server.
     *
     * @since  3.0.0
     *
     * @access private
     *
     * @param string|array $query_args Either a query variable key, or an associative array of query variables.
     * @param string       $custom_url custom url to fetch from
     *
     * @return object
     */
    private function request( $query_args, $custom_url = null ) {
        $url = add_query_arg( $query_args, ( $custom_url ) ? $custom_url : self::SPOTIM_SYNC_API_URL );

        $result        = new stdClass();
        $result->is_ok = false;

        // 60 second timeout will only work in non VIP env.
        $response = OW_WP::remote_get( $url, array(
            'sslverify' => true,
            'timeout'   => 60 //phpcs:ignore
        ), '', 5, 3 );

        if ( ! is_wp_error( $response ) &&
             'OK' === wp_remote_retrieve_response_message( $response ) &&
             200 === wp_remote_retrieve_response_code( $response ) ) {

            $response_body = json_decode( wp_remote_retrieve_body( $response ) );

            if ( isset( $response_body->success ) && false === $response_body->success ) {
                $result->is_ok = false;
            } else {
                $result->is_ok = true;
                $result->body  = $response_body;

                if ( is_object( $result->body ) ) {
                    $result->body->url = $url;
                }
            }
        }

        if ( ! $result->is_ok ) {
            $error = sprintf(
                esc_html__( 'Failed retrieving data for Post ID %s', 'spotim-comments' ),
                esc_html( $query_args['post_id'] )
            );

            // Log error
            $this->log( $error );
            $this->log( wp_remote_retrieve_body( $response ) );

            $result->body = $error;
        }

        return $result;
    }

    /**
     * Response
     *
     * Retrieve an array of post IDs.
     *
     * @since  3.0.0
     *
     * @access public
     *
     * @param array $args An associative array of query variables.
     *
     * @return void
     */
    public function response( $args = array() ) {
        $statuses_list = array( 'continue', 'refresh', 'success', 'cancel', 'error' );

        $defaults = array(
            'status'  => '',
            'message' => ''
        );

        if ( ! empty( $args ) ) {
            $args = array_merge( $defaults, $args );

            if ( ! empty( $args['status'] ) ) {
                $args['message'] = sanitize_text_field( $args['message'] );

                if ( in_array( $args['status'], $statuses_list, true ) ) {
                    wp_send_json( $args );
                }
            }
        }
    }

    /**
     * Logger
     *
     * Log any debug messages.
     *
     * @since  4.2.0
     *
     * @access public
     *
     * @param mixed $args Any amount of variables to add to log
     *
     * @return void
     */
    public function log( $message ) {
        // Logging will done only if enabled via config.
        // @codingStandardsIgnoreStart
        // Can we safely log?
        if ( WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
            $logText = 'OpenWeb Log: ';

            if ( func_num_args() == 1 && is_string( $message ) ) {
                $logText .= $message;
            } else {
                $logText .= print_r( func_get_args(), true );
            }

            error_log( $logText );
        }
        // @codingStandardsIgnoreEnd
    }

}
