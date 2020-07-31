<?php
$options               = OW_Options::get_instance();
$front                 = new OW_Frontend( $options );
$ow_id                 = $options->get( 'spot_id' );
$recirculation_method  = $options->get( 'rc_embed_method' );
$enable_rating_reviews = 0 === absint( $options->get( 'enable_rating_reviews' ) ) ? 'false' : 'true';

switch ( $options->get( 'disqus_identifier' ) ) {
    case 'id':
        $disqus_identifier = get_the_id();
        break;
    case 'short_url':
        $disqus_identifier = esc_url( site_url( '/?p=' . get_the_id() ) );
        break;
    case 'id_short_url':
    default:
        $disqus_identifier = get_the_id() . ' ' . esc_url( site_url( '/?p=' . get_the_id() ) );
}

$ow_comment_class   = $options->get( 'class' );
$post_id            = get_the_ID();
$permalink          = get_permalink();
$short_url          = site_url( '/?p=' . $post_id );
$comment_per_page   = $options->get( 'comments_per_page' );
$wp_import_endpoint = get_post_comments_feed_link( $post_id, 'spotim' );
$fb_url             = $permalink;
$disqus_short_name  = $options->get( 'disqus_shortname' );
$disqus_url         = $permalink;
$question           = get_post_meta( $post_id, 'spotim_display_question', true );
$enable_seo         = $options->get( 'enable_seo' );

/**
 * Filtering the CSS class.
 *
 * @since 5.0.0
 *
 * @param string $ow_comment_class CSS class .
 */
$ow_comment_class = apply_filters( 'ow_comments_class', $ow_comment_class );

/**
 * Filtering the CSS class.
 *
 * @deprecated 5.0.0 Use {@see 'ow_comments_class'} instead.
 *
 * @param array $json_feed_query_args Default feed query args.
 */
$ow_comment_class = apply_filters_deprecated(
    'spotim_comments_class',
    array( $ow_comment_class ),
    '5.0.0',
    'ow_comments_class',
    OW_FILTER_DEPRECATED_MESSAGE
);

/**
 * Filtering post id.
 *
 * @since 5.0.0
 *
 * @param int $post_id Current post ID.
 */
$post_id = apply_filters( 'ow_comments_post_id', $post_id );

/**
 * Filtering post id.
 *
 * @deprecated 5.0.0 Use {@see 'ow_comments_post_id'} instead.
 *
 * @param int $post_id Current post ID.
 */
$post_id = apply_filters_deprecated(
    'spotim_comments_post_id',
    array( $post_id ),
    '5.0.0',
    'ow_comments_post_id',
    OW_FILTER_DEPRECATED_MESSAGE
);

/**
 * Filtering permalink.
 *
 * @since 5.0.0
 *
 * @param string $permalink Current post permalink.
 */
$permalink = apply_filters( 'ow_comments_post_url', $permalink );

/**
 * Filtering permalink.
 *
 * @deprecated 5.0.0 Use {@see 'ow_comments_post_url'} instead.
 *
 * @param string $permalink Current post permalink.
 */
$permalink = apply_filters_deprecated(
    'spotim_comments_post_url',
    array( $permalink ),
    '5.0.0',
    'ow_comments_post_url',
    OW_FILTER_DEPRECATED_MESSAGE
);

/**
 * Filtering disqus short url.
 *
 * @since 5.0.0
 *
 * @param string $short_url Disqus short url.
 */
$short_url = apply_filters( 'ow_comments_disqus_short_url', $short_url );

/**
 * Filtering disqus short url.
 *
 * @deprecated 5.0.0 Use {@see 'ow_comments_disqus_short_url'} instead.
 *
 * @param string $short_url Disqus short url.
 */
$short_url = apply_filters_deprecated(
    'spotim_comments_disqus_short_url',
    array( $short_url ),
    '5.0.0',
    'ow_comments_disqus_short_url',
    OW_FILTER_DEPRECATED_MESSAGE
);

/**
 * Filtering comment message count.
 *
 * @since 5.0.0
 *
 * @param int $comment_per_page Comments message count.
 */
$comment_per_page = apply_filters( 'ow_comments_messages_count', $comment_per_page );

/**
 * Filtering comment message count.
 *
 * @deprecated 5.0.0 Use {@see 'ow_comments_messages_count'} instead.
 *
 * @param int $comment_per_page Comments message count.
 */
$comment_per_page = apply_filters_deprecated(
    'spotim_comments_messages_count',
    array( $comment_per_page ),
    '5.0.0',
    'ow_comments_messages_count',
    OW_FILTER_DEPRECATED_MESSAGE
);

/**
 * Filtering comments feed link.
 *
 * @since 5.0.0
 *
 * @param string $wp_import_endpoint Comments feed link.
 */
$wp_import_endpoint = apply_filters( 'ow_comments_feed_link', $wp_import_endpoint );

/**
 * Filtering comments feed link.
 *
 * @deprecated 5.0.0 Use {@see 'ow_comments_feed_link'} instead.
 *
 * @param string $wp_import_endpoint Comments feed link.
 */
$wp_import_endpoint = apply_filters_deprecated(
    'spotim_comments_feed_link',
    array( $wp_import_endpoint ),
    '5.0.0',
    'ow_comments_feed_link',
    OW_FILTER_DEPRECATED_MESSAGE
);

/**
 * Filtering comments fb URL.
 *
 * @since 5.0.0
 *
 * @param string $fb_url Comments fb url.
 */
$fb_url = apply_filters( 'ow_comments_facebook_url', $fb_url );

/**
 * Filtering comments fb URL.
 *
 * @deprecated 5.0.0 Use {@see 'ow_comments_facebook_url'} instead.
 *
 * @param string $fb_url Comments fb url.
 */
$fb_url = apply_filters_deprecated(
    'spotim_comments_facebook_url',
    array( $fb_url ),
    '5.0.0',
    'ow_comments_facebook_url',
    OW_FILTER_DEPRECATED_MESSAGE
);

/**
 * Filtering disqus short name.
 *
 * @since 5.0.0
 *
 * @param string $disqus_short_name Disqus short name.
 */
$disqus_short_name = apply_filters( 'ow_comments_disqus_shortname', $disqus_short_name );

/**
 * Filtering disqus short name.
 *
 * @deprecated 5.0.0 Use {@see 'ow_comments_disqus_shortname'} instead.
 *
 * @param string $disqus_short_name Disqus short name.
 */
$disqus_short_name = apply_filters_deprecated(
    'spotim_comments_disqus_shortname',
    array( $disqus_short_name ),
    '5.0.0',
    'ow_comments_disqus_shortname',
    OW_FILTER_DEPRECATED_MESSAGE
);

/**
 * Filtering disqus url.
 *
 * @since 5.0.0
 *
 * @param string $disqus_url Disqus url.
 */
$disqus_url = apply_filters( 'ow_comments_disqus_url', $disqus_url );

/**
 * Filtering disqus url.
 *
 * @deprecated 5.0.0 Use {@see 'ow_comments_disqus_url'} instead.
 *
 * @param string $disqus_url Disqus url.
 */
$disqus_url = apply_filters_deprecated(
    'spotim_comments_disqus_url',
    array( $disqus_url ),
    '5.0.0',
    'ow_comments_disqus_url',
    OW_FILTER_DEPRECATED_MESSAGE
);

/**
 * Filtering disqus identifier.
 *
 * @since 5.0.0
 *
 * @param string $disqus_identifier Disqus identifier.
 */
$disqus_identifier = apply_filters( 'ow_comments_disqus_identifier', $disqus_identifier );

/**
 * Filtering disqus identifier.
 *
 * @deprecated 5.0.0 Use {@see 'ow_comments_disqus_identifier'} instead.
 *
 * @param string $disqus_identifier Disqus identifier.
 */
$disqus_identifier = apply_filters_deprecated(
    'spotim_comments_disqus_identifier',
    array( $disqus_identifier ),
    '5.0.0',
    'ow_comments_disqus_identifier',
    OW_FILTER_DEPRECATED_MESSAGE
);

/**
 * Filtering community question.
 *
 * @since 5.0.0
 *
 * @param string $question Community question.
 */
$question = apply_filters( 'ow_comments_community_question', $question );

/**
 * Filtering community question.
 *
 * @deprecated 5.0.0 Use {@see 'ow_comments_community_question'} instead.
 *
 * @param string $question Community question.
 */
$question = apply_filters_deprecated(
    'spotim_comments_community_question',
    array( $question ),
    '5.0.0',
    'ow_comments_community_question',
    OW_FILTER_DEPRECATED_MESSAGE
);

/**
 * Filtering enable seo setting.
 *
 * @since 5.0.0
 *
 * @param string $enable_seo Enable seo setting value.
 */
$enable_seo = apply_filters( 'ow_comments_seo_enabled', $enable_seo );

/**
 * Filtering enable seo setting.
 *
 * @deprecated 5.0.0 Use {@see 'ow_comments_seo_enabled'} instead.
 *
 * @param string $enable_seo Enable seo setting value.
 */
$enable_seo = apply_filters_deprecated(
    'spotim_comments_seo_enabled',
    array( $enable_seo ),
    '5.0.0',
    'ow_comments_seo_enabled',
    OW_FILTER_DEPRECATED_MESSAGE
);


?>

<div id="comments-anchor" class="ow-comments <?php echo esc_attr( $ow_comment_class ); ?>">
    <?php
    if ( ( 'top' === $recirculation_method ) && ( $front->has_ow_recirculation() ) ) {
        ob_start();
        include( plugin_dir_path( dirname( __FILE__ ) ) . 'templates/recirculation-template.php' );
        $recirculation = ob_get_contents();
        ob_end_clean();

        // Ignoring as the code in templates/recirculation-template.php is already escaped.
        echo $recirculation; // phpcs:ignore
    }
    ?>
    <script async
            data-spotim-module="spotim-launcher"
            data-article-tags="<?php echo esc_attr( implode( ', ', wp_get_post_tags( get_the_ID(), array( 'fields' => 'names' ) ) ) ); ?>"
            src="<?php echo esc_url( 'https://launcher.spot.im/spot/' . $ow_id ); ?>"
            data-social-reviews="<?php echo esc_attr( $enable_rating_reviews ); ?>"
            data-post-id="<?php echo esc_attr( $post_id ); ?>"
            data-post-url="<?php echo esc_url( $permalink ); ?>"
            data-short-url="<?php echo esc_url( $short_url ); ?>"
            data-messages-count="<?php echo esc_attr( $comment_per_page ); ?>"
            data-wp-import-endpoint="<?php echo esc_url( $wp_import_endpoint ); ?>"
            data-facebook-url="<?php echo esc_url( $fb_url ); ?>"
            data-disqus-shortname="<?php echo esc_attr( $disqus_short_name ); ?>"
            data-disqus-url="<?php echo esc_url( $disqus_url ); ?>"
            data-disqus-identifier="<?php echo esc_attr( $disqus_identifier ); ?>"
            data-community-question="<?php echo esc_attr( $question ); ?>"
            data-seo-enabled="<?php echo esc_attr( $enable_seo ); ?>"
            data-wp-v="<?php echo esc_attr( 'p-' . OW_VERSION .'/wp-' . get_bloginfo( 'version' ) ); ?>"
    ></script>
    <?php
    if ( ( 'bottom' === $recirculation_method ) && ( $front->has_ow_recirculation() ) ) {
        ob_start();
        include( plugin_dir_path( dirname( __FILE__ ) ) . 'templates/recirculation-template.php' );
        $recirculation = ob_get_contents();
        ob_end_clean();

        // Ignoring as the code in templates/recirculation-template.php is already escaped.
        echo $recirculation; // phpcs:ignore
    }
    ?>
</div>
