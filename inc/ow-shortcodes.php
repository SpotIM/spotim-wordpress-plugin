<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * OpenWeb Comments Shortcode.
 *
 * @since 4.0.0
 * @since 5.0.0 Renamed from 'spotim_comments_shortcode' to 'ow_comments_shortcode'.
 */
function ow_comments_shortcode() {

    $options  = OW_Options::get_instance();
    $ow_id    = $options->get( 'spot_id' );
    $template = '';

    /**
     * Before loading OpenWeb comments template.
     *
     * @since 5.0.0
     *
     * @param string $template Comments template file to load.
     * @param int    $ow_id    OpenWeb ID.
     */
    $template = apply_filters( 'before_ow_comments', $template, $ow_id );

    /**
     * Before loading OpenWeb comments template.
     *
     * @since 4.0.0
     * @deprecated 5.0.0 Use {@see 'before_ow_comments'} instead.
     *
     * @param string $template Comments template file to load.
     * @param int    $ow_id    OpenWeb ID.
     */
    $template = apply_filters_deprecated(
        'before_spotim_comments',
        array( $template, $ow_id ),
        '5.0.0',
        'before_ow_comments',
        OW_FILTER_DEPRECATED_MESSAGE
    );

    // Load OpenWeb comments template
    ob_start();
    include( plugin_dir_path( dirname( __FILE__ ) ) . 'templates/comments-template.php' );
    $template .= ob_get_contents();
    ob_end_clean();

    /**
     * After loading OpenWeb comments template.
     *
     * @since 5.0.0
     *
     * @param string $template Comments template file to load.
     * @param int    $ow_id    OpenWeb ID.
     */
    $template = apply_filters( 'after_ow_comments', $template, $ow_id );

    /**
     * After loading OpenWeb comments template.
     *
     * @since 4.0.0
     * @deprecated 5.0.0 Use {@see 'after_ow_comments'} instead.
     *
     * @param string $template Comments template file to load.
     * @param int    $ow_id    OpenWeb ID.
     */
    $template = apply_filters_deprecated(
        'after_spotim_comments',
        array( $template, $ow_id ),
        '5.0.0',
        'after_ow_comments',
        OW_FILTER_DEPRECATED_MESSAGE
    );

    return $template;
}
add_shortcode( 'spotim_comments', 'ow_comments_shortcode' );

/**
 * OpenWeb Recirculation Shortcode.
 *
 * @since 4.0.0
 * @since 5.0.0 Renamed from 'spotim_recirculation_shortcode' to 'ow_recirculation_shortcode'.
 */
function ow_recirculation_shortcode() {

    $options  = OW_Options::get_instance();
    $ow_id  = $options->get( 'spot_id' );
    $template = '';

    /**
     * Before loading OpenWeb recirculation template.
     *
     * @since 5.0.0
     *
     * @param string $content The post content.
     * @param int    $ow_id   OpenWeb ID.
     */
    $template = apply_filters( 'before_ow_recirculation', $template, $ow_id );

    /**
     * Before loading OpenWeb recirculation template.
     *
     * @since 4.0.0
     * @deprecated 5.0.0 Use {@see 'before_ow_recirculation'} instead.
     *
     * @param string $template Recirculation template to load.
     * @param int    $ow_id    OpenWeb ID.
     */
    $template = apply_filters_deprecated(
        'before_spotim_recirculation',
        array( $template, $ow_id ),
        '5.0.0',
        'before_ow_recirculation',
        OW_FILTER_DEPRECATED_MESSAGE
    );

    // Load OpenWeb recirculation template
    ob_start();
    include( plugin_dir_path( dirname( __FILE__ ) ) . 'templates/recirculation-template.php' );
    $template .= ob_get_contents();
    ob_end_clean();

    /**
     * After loading OpenWeb recirculation template
     *
     * @since 5.0.0
     *
     * @param string $template Recirculation template to load.
     * @param int    $ow_id  OpenWeb ID.
     */
    $template = apply_filters( 'after_ow_recirculation', $template, $ow_id );

    /**
     * After loading OpenWeb recirculation template
     *
     * @since 4.0.0
     * @deprecated 5.0.0 Use {@see 'after_ow_recirculation'} instead.
     *
     * @param string $template Recirculation template to load.
     * @param int    $ow_id  OpenWeb ID.
     */
    $template = apply_filters_deprecated(
        'after_spotim_recirculation',
        array( $template, $ow_id ),
        '5.0.0',
        'after_ow_recirculation',
        OW_FILTER_DEPRECATED_MESSAGE
    );

    return $template;
}
add_shortcode( 'spotim_recirculation', 'ow_recirculation_shortcode' );

/**
 * OpenWeb Siderail Shortcode
 *
 * @since 4.2.0
 * @since 5.0.0 Renamed from 'spotim_siderail_shortcode' to 'ow_siderail_shortcode'.
 */
function ow_siderail_shortcode() {

    $options  = OW_Options::get_instance();
    $ow_id    = $options->get( 'spot_id' );
    $template = '';

    /**
     * Before loading OpenWeb siderail template.
     *
     * @since 5.0.0
     *
     * @param string $template Siderail template to load.
     * @param int    $ow_id    OpenWeb ID.
     */
    $template = apply_filters( 'before_ow_siderail', $template, $ow_id );

    /**
     * Before loading OpenWeb siderail template.
     *
     * @since 4.0.0
     * @deprecated 5.0.0 Use {@see 'before_ow_siderail'} instead.
     *
     * @param string $template Siderail template to load.
     * @param int    $ow_id    OpenWeb ID.
     */
    $template = apply_filters_deprecated(
        'before_spotim_siderail',
        array( $template, $ow_id ),
        '5.0.0',
        'before_ow_siderail',
        OW_FILTER_DEPRECATED_MESSAGE
    );

    // Load OpenWeb siderail template.
    ob_start();
    include( plugin_dir_path( dirname( __FILE__ ) ) . 'templates/siderail-template.php' );
    $template .= ob_get_contents();
    ob_end_clean();

    /**
     * After loading OpenWeb siderail template
     *
     * @since 5.0.0
     *
     * @param string $template Siderail template to load.
     * @param int    $ow_id    OpenWeb ID.
     */
    $template = apply_filters( 'after_ow_siderail', $template, $ow_id );

    /**
     * After loading OpenWeb siderail template
     *
     * @since 4.0.0
     * @deprecated 5.0.0 Use {@see 'after_ow_siderail'} instead.
     *
     * @param string $template Siderail template to load.
     * @param int    $ow_id    OpenWeb ID.
     */
    $template = apply_filters_deprecated(
        'after_spotim_siderail',
        array( $template, $ow_id ),
        '5.0.0',
        'after_ow_siderail',
        OW_FILTER_DEPRECATED_MESSAGE
    );

    return $template;
}
add_shortcode( 'spotim_siderail', 'ow_siderail_shortcode' );
