<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * SpotIM Comments Shortcode
 *
 * @since 4.0.0
 */
function spotim_comments_shortcode() {

    $options  = OW_Options::get_instance();
    $spot_id  = $options->get( 'spot_id' );
    $template = '';

    /**
     * Before loading SpotIM comments template
     *
     * @since 4.0.0
     *
     * @param string $template Comments template file to load.
     * @param int    $spot_id  SpotIM ID.
     */
    $template = apply_filters( 'before_spotim_comments', $template, $spot_id );

    // Load SpotIM comments template
    ob_start();
    include( plugin_dir_path( dirname( __FILE__ ) ) . 'templates/comments-template.php' );
    $template .= ob_get_contents();
    ob_end_clean();

    /**
     * After loading SpotIM comments template
     *
     * @since 4.0.0
     *
     * @param string $template Comments template file to load.
     * @param int    $spot_id  SpotIM ID.
     */
    $template = apply_filters( 'after_spotim_comments', $template, $spot_id );

    return $template;
}

add_shortcode( 'spotim_comments', 'spotim_comments_shortcode' );


/**
 * SpotIM Recirculation Shortcode
 *
 * @since 4.0.0
 */
function spotim_recirculation_shortcode() {

    $options  = OW_Options::get_instance();
    $spot_id  = $options->get( 'spot_id' );
    $template = '';

    /**
     * Before loading SpotIM recirculation template
     *
     * @since 4.0.0
     *
     * @param string $template Recirculation template to load.
     * @param int    $spot_id  SpotIM ID.
     */
    $template = apply_filters( 'before_spotim_recirculation', $template, $spot_id );

    // Load SpotIM recirculation template
    ob_start();
    include( plugin_dir_path( dirname( __FILE__ ) ) . 'templates/recirculation-template.php' );
    $template .= ob_get_contents();
    ob_end_clean();

    /**
     * After loading SpotIM recirculation template
     *
     * @since 4.0.0
     *
     * @param string $template Recirculation template to load.
     * @param int    $spot_id  SpotIM ID.
     */
    $template = apply_filters( 'after_spotim_recirculation', $template, $spot_id );

    return $template;
}

add_shortcode( 'spotim_recirculation', 'spotim_recirculation_shortcode' );


/**
 * SpotIM Siderail Shortcode
 *
 * @since 4.2.0
 */
function spotim_siderail_shortcode() {

    $options  = OW_Options::get_instance();
    $spot_id  = $options->get( 'spot_id' );
    $template = '';

    /**
     * Before loading SpotIM siderail template
     *
     * @since 4.0.0
     *
     * @param string $template Siderail template to load.
     * @param int    $spot_id  SpotIM ID.
     */
    $template = apply_filters( 'before_spotim_siderail', $template, $spot_id );

    // Load SpotIM siderail template
    ob_start();
    include( plugin_dir_path( dirname( __FILE__ ) ) . 'templates/siderail-template.php' );
    $template .= ob_get_contents();
    ob_end_clean();

    /**
     * After loading SpotIM siderail template
     *
     * @since 4.0.0
     *
     * @param string $template Siderail template to load.
     * @param int    $spot_id  SpotIM ID.
     */
    $template = apply_filters( 'after_spotim_siderail', $template, $spot_id );

    return $template;
}

add_shortcode( 'spotim_siderail', 'spotim_siderail_shortcode' );
