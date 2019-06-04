<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * SpotIM_Frontend
 *
 * Plugin frontend.
 *
 * @since 1.0.2
 */
class SpotIM_Frontend {

    /**
     * Options
     *
     * @since  2.0.2
     *
     * @access private
     * @static
     *
     * @var SpotIM_Options
     */
    private static $options;

    /**
     * Launch
     *
     * @since  2.0.0
     *
     * @access public
     *
     * @param SpotIM_Options $options Plugin options.
     *
     * @return void
     */
    public function __construct( $options ) {

        // Set options
        self::$options = $options;

        // Make sure Spot ID is not empty.
        $spot_id = self::$options->get( 'spot_id' );

        if ( empty( $spot_id ) ) {
            return;
        }

        $embed_method     = self::$options->get( 'embed_method' );
        $rc_embed_method  = self::$options->get( 'rc_embed_method' );
        $display_priority = self::$options->get( 'display_priority' );

        // SpotIM Newsfeed
        add_action( 'wp_footer', array( __CLASS__, 'add_spotim_newsfeed' ) );

        // SpotIM Recirculation
        if ( 'regular' === $rc_embed_method ) {

            // Add Recirculation after the content
            add_action( 'the_content', array( __CLASS__, 'add_spotim_recirculation' ), $display_priority );

        }

        // SpotIM Comments
        if ( 'content' === $embed_method ) {

            // Add after the content
            add_action( 'the_content', array( __CLASS__, 'the_content_comments_template' ), $display_priority );
            //Remove WP comments section (We expect for SPOT.IM section, we don't need the WP one)
            add_filter( 'comments_template', array( __CLASS__, 'empty_comments_template' ) );

        } else if ( 'comments' === $embed_method ) {
            // Replace the WordPress comments
            add_filter( 'comments_template', array( __CLASS__, 'filter_comments_template' ), 20 );
        } else if ( 'manual' === $embed_method ) {
            //Remove WP comments section (We expect for SPOT.IM section, we don't need the WP one)
            add_filter( 'comments_template', array( __CLASS__, 'empty_comments_template' ) );
        }

        // Comments count assign
        add_filter( 'the_content', array( __CLASS__, 'filter_comments_number' ), $display_priority );

        // AMP Recirculation styles and scripts hooks.
        add_action( 'amp_post_template_data', array( __CLASS__, 'amp_recirculation_scripts' ) );
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'amp_recirculation_styles' ) );

        // OG tags
        add_action( 'wp_head', array( __CLASS__, 'open_graph_tags' ) );

    }

    public static function display_comments() {
        if ( 'manual' === self::$options->get( 'embed_method' ) ) {

            // Ignoring as the code in templates/comments-template.php is already escaped.
            echo self::the_content_comments_template( "" ); // phpcs:ignore
        }
    }

    /**
     * Has Spot.IM comments
     *
     * @since  4.0.0
     *
     * @access public
     * @static
     *
     * @return bool
     */
    public static function has_spotim_comments() {
        global $post;

        // Bail if it's not a singular template
        if ( ! is_singular() ) {
            return false;
        }

        // Bail if comments are closed
        if ( ! comments_open() ) {
            return false;
        }

        // Bail if Spot.IM is disabled for this post type
        if ( '0' === self::$options->get( "display_{$post->post_type}" ) ) {
            return false;
        }

        // Bail if Spot.IM Comments are disabled for this specific content item
        $specific_display = get_post_meta( absint( $post->ID ), 'spotim_display_comments', true );
        $specific_display = in_array( $specific_display, array(
            'enable',
            'disable'
        ), true ) ? $specific_display : 'enable';
        if ( 'disable' === $specific_display ) {
            return false;
        }

        // Return true if all tests passed
        return true;
    }

    /**
     * Empty comments template
     *
     * @since  4.1.0
     *
     * @access public
     * @static
     *
     * @param string $template Comments template to load.
     *
     * @return string
     */
    public static function empty_comments_template( $template ) {

        if ( self::has_spotim_comments() ) {

            // Load empty comments template
            $require_template_path = self::$options->require_template( 'comments-template-empty.php', true );
            if ( ! empty( $require_template_path ) ) {
                $template = $require_template_path;
            }

        }

        return $template;
    }

    /**
     * Filter comments template
     *
     * @since  4.1.0
     *
     * @access public
     * @static
     *
     * @param string $template Comments template to load.
     *
     * @return string
     */
    public static function the_content_comments_template( $content ) {

        if ( self::has_spotim_comments() ) {

            if ( ! SpotIM_WP::spotim_is_amp() ) {
                // Load SpotIM comments template
                ob_start();
                include( plugin_dir_path( dirname( __FILE__ ) ) . 'templates/comments-template.php' );
                $content .= ob_get_contents();
                ob_end_clean();
            } else {
                // Display AMP comments if AMP.
                ob_start();
                include plugin_dir_path( dirname( __FILE__ ) ) . 'templates/comments-amp-template.php';
                $content .= ob_get_contents();
                ob_end_clean();
            }

        }

        return $content;

    }

    /**
     * Filter comments template
     *
     * @since  1.0.2
     *
     * @access public
     * @static
     *
     * @param string $template Comments template to load.
     *
     * @return string
     */
    public static function filter_comments_template( $template ) {
        if ( self::has_spotim_comments() ) {
            $spot_id = self::$options->get( 'spot_id' );

            /**
             * Before loading SpotIM comments template
             *
             * @since 4.0.0
             *
             * @param string $template Comments template to load.
             * @param int    $spot_id  SpotIM ID.
             */
            $template = apply_filters( 'before_spotim_comments', $template, $spot_id );

            // Don't filter the template if page is AMP.
            if ( SpotIM_WP::spotim_is_amp() ) {
                // Load SpotIM comments template
                $require_amp_template_path = self::$options->require_template( 'comments-amp-template.php', true );
                if ( ! empty( $require_amp_template_path ) ) {
                    $template = $require_amp_template_path;
                }
            } else {
                // Load SpotIM comments template
                $require_template_path = self::$options->require_template( 'comments-template.php', true );
                if ( ! empty( $require_template_path ) ) {
                    $template = $require_template_path;
                }
            }

            /**
             * After loading SpotIM comments template
             *
             * @since 4.0.0
             *
             * @param string $template Comments template to load.
             * @param int    $spot_id  SpotIM ID.
             */
            $template = apply_filters( 'after_spotim_comments', $template, $spot_id );
        }

        return $template;
    }

    /**
     * Add the comments number scripts
     *
     * @since  4.3.1
     *
     * @access public
     * @static
     *
     *
     * @return string
     */
    public static function comments_number_tags() {

        // Check wheter the singular and applied spotIm comments
        if ( false !== self::$options->get( 'display_comments_count' ) && '0' !== self::$options->get( 'display_comments_count' ) ) {

            $spot_id = self::$options->get( 'spot_id' );

            if ( ! empty( $spot_id ) ) {
                wp_enqueue_style( 'comments_number_stylesheet', self::$options->require_stylesheet( 'comments-number.css', true ) );
                self::$options->require_template( 'comments-number-template.php' );
            }
        }
    }

    /**
     * Filter comments number
     *
     * @since  4.3.1
     *
     * @access public
     * @static
     *
     *
     * @return string
     */
    public static function filter_comments_number( $content ) {

        if ( SpotIM_WP::spotim_is_amp() ) {
            return $content;
        }

        global $post;

        $counterPosition = self::$options->get( 'display_comments_count' );

        if ( '0' !== $counterPosition && self::has_spotim_comments() ) {

            // Comments count scripts
            add_filter( 'wp_footer', array( __CLASS__, 'comments_number_tags' ) );

            $commentsNumberContainerSpan = '<a href="#comments-anchor"><span class="spot-im-replies-count" data-post-id="' . absint( $post->ID ) . '"></span></a>';

            return $commentsNumberContainerSpan . $content;

        } elseif ( '0' !== $counterPosition && ! is_singular() && 1 === absint( self::$options->get( "display_{$post->post_type}" ) ) ) {

            // Display comment count on non singular pages for enabled post type.

            // Comments count scripts.
            add_filter( 'wp_footer', array( __CLASS__, 'comments_number_tags' ) );

            $commentsNumberContainerSpan = '<a href="' . esc_url( get_permalink( $post->ID ) ) . '"><span class="spot-im-replies-count" data-post-id="' . absint( $post->ID ) . '"></span></a>';

            return $content . $commentsNumberContainerSpan;

        }

        return $content;
    }

    /**
     * Has Spot.IM questions
     *
     * @since  4.0.0
     *
     * @access public
     * @static
     *
     * @return bool
     */
    public static function has_spotim_questions() {
        global $post;

        // Bail if it's not a singular template
        if ( ! is_singular() ) {
            return false;
        }

        // Bail if comments are closed
        if ( ! comments_open() ) {
            return false;
        }

        // Bail if Spot.IM is disabled for this post type
        if ( '0' === self::$options->get( "display_{$post->post_type}" ) ) {
            return false;
        }

        // Bail if Spot.IM questions are disabled for this specific content item
        $specific_display = get_post_meta( absint( $post->ID ), 'spotim_display_question', true );
        if ( empty( $specific_display ) ) {
            return false;
        }

        // Return true if all tests passed
        return true;
    }

    /**
     * Has Spot.IM recirculation
     *
     * @since  4.0.0
     *
     * @access public
     * @static
     *
     * @return bool
     */
    public static function has_spotim_recirculation() {
        global $post;

        // Bail if it's not a singular template
        if ( ! is_singular() ) {
            return false;
        }

        // Bail if comments are closed
        if ( ! comments_open() ) {
            return false;
        }

        // Bail if Spot.IM is disabled for this post type
        if ( '0' === self::$options->get( "display_{$post->post_type}" ) ) {
            return false;
        }

        // Bail if Recirculation are disabled
        if ( 'none' === self::$options->get( 'rc_embed_method' ) ) {
            return false;
        }

        // Bail if Spot.IM Recirculation are disabled for this specific content item
        $specific_display = get_post_meta( absint( $post->ID ), 'spotim_display_recirculation', true );
        $specific_display = in_array( $specific_display, array(
            'enable',
            'disable'
        ), true ) ? $specific_display : 'enable';
        if ( 'disable' === $specific_display ) {
            return false;
        }

        // Return true if all tests passed
        return true;
    }

    /**
     * Add Spot.IM recirculation to the content
     *
     * @since  4.0.0
     *
     * @access public
     * @static
     *
     * @param string $content The post content.
     *
     * @return bool
     */
    public static function add_spotim_recirculation( $content ) {

        if ( self::has_spotim_recirculation() ) {
            $spot_id = self::$options->get( 'spot_id' );

            /**
             * Before loading SpotIM recirculation template
             *
             * @since 4.0.0
             *
             * @param string $content The post content.
             * @param int    $spot_id SpotIM ID.
             */
            $content = apply_filters( 'before_spotim_recirculation', $content, $spot_id );

            if ( SpotIM_WP::spotim_is_amp() ) {
                // Load SpotIM recirculation AMP template.
                ob_start();
                include plugin_dir_path( dirname( __FILE__ ) ) . 'templates/recirculation-amp-template.php';
                $content .= ob_get_contents();
                ob_end_clean();
            } else {
                // Load SpotIM recirculation template.
                ob_start();
                include plugin_dir_path( dirname( __FILE__ ) ) . 'templates/recirculation-template.php';
                $content .= ob_get_contents();
                ob_end_clean();
            }

            /**
             * After loading SpotIM recirculation template
             *
             * @since 4.0.0
             *
             * @param string $content The post content.
             * @param int    $spot_id SpotIM ID.
             */
            $content = apply_filters( 'after_spotim_recirculation', $content, $spot_id );
        }

        return $content;
    }

    /**
     * Add Spot.IM newsfeed
     *
     * @since  4.3.0
     *
     * @access public
     * @static
     *
     * @return void
     */
    public static function add_spotim_newsfeed() {

        if ( 1 === absint( self::$options->get( 'display_newsfeed' ) ) ) {
            $spot_id = self::$options->get( 'spot_id' );

            if ( ! empty( $spot_id ) ) {
                self::$options->require_template( 'newsfeed-template.php' );
            }
        }

    }

    /**
     * Add Spot.IM Open Graph tags to the header
     *
     * @since  4.3.0
     *
     * @access public
     * @static
     */
    public static function open_graph_tags() {

        // Bail if it's not a singular template
        if ( ! is_singular() ) {
            return;
        }

        // Bail if Spot.IM Open Graph tags are disabled
        if ( 'true' !== self::$options->get( 'enable_og' ) ) {
            return;
        }

        // This is done because get_the_excerpt is called outside a loop,
        // which will cause issues when post doesn't have an excerpt.
        setup_postdata( get_the_ID() );

        // Set default Open Graph tags
        $tags = array(
            'og:url'         => get_permalink(),
            'og:type'        => 'article',
            'og:title'       => get_the_title(),
            'og:description' => get_the_excerpt(),
        );
        if ( has_post_thumbnail() ) {
            $tags['og:image'] = get_the_post_thumbnail_url();
        }

        /**
         * Filtering the default Open Graph tags added by Spot.IM.
         *
         * @since 4.3.0
         *
         * @param array $tags Default Open Graph tags.
         */
        $tags = (array) apply_filters( 'spotim_open_graph_tags', $tags );

        // Generate Open Graph tags markup
        foreach ( $tags as $tagname => $tag ) {
            printf(
                '<meta property="%s" content="%s" />',
                esc_attr( $tagname ),
                esc_attr( $tag )
            );
        }

        do_action( 'spotim_after_open_tags' );
    }

    /**
     * Display the markup of AMP comments.
     */
    public static function display_amp_comments() {
        if ( self::has_spotim_comments() ) {
            ob_start();
            // Load SpotIM AMP comments template.
            include plugin_dir_path( dirname( __FILE__ ) ) . 'templates/comments-amp-template.php';
            $amp_comments = ob_get_contents();
            ob_end_clean();
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Already escaped in template.
            echo $amp_comments;
        }
    }


    /**
     * AMP's Post data action which is used to modify the data of post, other template and user information.
     * Used here to enqueue scripts.
     * 
     * @param array $data Data related to posts and templates.
     * 
     * @return array
     */
    public static function amp_recirculation_scripts( $data ) {
        if ( SpotIM_WP::spotim_is_amp() && self::has_spotim_recirculation() && 'none' !== self::$options->get( 'rc_embed_method' ) ) {
            $data['amp_component_scripts']['amp-ad']        = 'https://cdn.ampproject.org/v0/amp-ad-0.1.js';
            $data['amp_component_scripts']['amp-list']      = 'https://cdn.ampproject.org/v0/amp-list-0.1.js';
            $data['amp_component_scripts']['amp-carousel']  = 'https://cdn.ampproject.org/v0/amp-carousel-0.1.js';
            $data['amp_component_scripts']['amp-analytics'] = 'https://cdn.ampproject.org/v0/amp-analytics-0.1.js';
            $data['amp_component_scripts']['amp-mustache']  = 'https://cdn.ampproject.org/v0/amp-mustache-0.2.js';
            $data['amp_component_scripts']['amp-iframe']    = 'https://cdn.ampproject.org/v0/amp-iframe-0.1.js';
            $data['amp_component_scripts']['amp-ad']        = 'https://cdn.ampproject.org/v0/amp-ad-0.1.js';
        }
        return $data;
    }

    /**
     * Add css for AMP recirculation.
     *
     * @return void
     */
    public static function amp_recirculation_styles() {
        if ( SpotIM_WP::spotim_is_amp() && self::has_spotim_recirculation() && 'none' !== self::$options->get( 'rc_embed_method' ) ) {
            wp_enqueue_style( 'amp-recirculation-style', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/stylesheets/recirculation-amp.css' );
        }
    }

}
