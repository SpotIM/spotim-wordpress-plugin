<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * OW_Feed
 *
 * Comments feed.
 *
 * @since 4.1.0
 * @since 5.0.0 Renamed from `SpotIM_Feed` to `OW_Feed`.
 */
class OW_Feed {

    /**
     * Feed
     *
     * Feed name to access in URL (eg. /feed/spotim/)
     *
     * @since  4.1.0
     *
     * @access public
     *
     * @var string
     */
    public $feed = 'spotim';

    /**
     * Constructor
     *
     * Get things started.
     *
     * @since  4.1.0
     *
     * @access public
     */
    public function __construct() {

        add_action( 'init', array( $this, 'init' ) );
        add_filter( 'feed_content_type', array( $this, 'content_type' ), 10, 2 );
        add_filter( 'pre_get_posts', array( $this, 'pre_get_posts' ) );

    }

    /**
     * Init
     *
     * @since  4.1.0
     *
     * @access public
     *
     * @return void
     */
    public function init() {

        add_feed( $this->feed, array( $this, 'output' ) );

    }

    /**
     * Content type
     *
     * Return the correct HTTP header for Content-type.
     *
     * @since  4.1.0
     *
     * @access public
     *
     * @param string $content_type Content type indicating the type of data that a feed contains.
     * @param string $type         Type of feed.
     *
     * @return string
     */
    public function content_type( $content_type, $type ) {

        if ( $this->feed === $type ) {
            return 'application/json';
        }

        return $content_type;

    }

    /**
     * Pre Get Posts
     *
     * Modify the query.
     *
     * @since  4.1.0
     *
     * @access public
     *
     * @param WP_Query $query The WP_Query instance.
     *
     * @return WP_Query
     */
    public function pre_get_posts( $query ) {

        if ( $query->is_main_query() && $query->is_feed( $this->feed ) ) {

            // show all results
            $query->set( 'nopaging', 1 );

        }

        return $query;

    }

    /**
     * Output
     *
     * Return the feed output.
     *
     * @since  4.1.0
     *
     * @access public
     *
     * @return void
     */
    public function output() {

        if ( have_posts() ) {

            while ( have_posts() ) {
                the_post();
                echo wp_json_encode( new OW_JSON_Feed( get_the_id() ) );
            }
        }

    }

}
