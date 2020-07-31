<?php
/**
 * OpenWeb comments template for AMP page.
 *
 * @package spotim-comments
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$options              = OW_Options::get_instance();
$ow_id                = $options->get( 'spot_id' );
$ow_post_id           = get_the_ID();
$front                = new OW_Frontend( $options );
$recirculation_method = $options->get( 'rc_embed_method' );

if ( ! empty( $ow_id ) && ! empty( $ow_post_id ) ) :
    ?>
<div class="ow-im-amp">
    <?php
    if ( ( 'top' === $recirculation_method ) && ( $front->has_ow_recirculation() ) ) {
        ob_start();
        include plugin_dir_path( dirname( __FILE__ ) ) . 'templates/recirculation-amp-template.php';
        $recirculation = ob_get_contents();
        ob_end_clean();

        echo wp_kses( $recirculation, OW_WP::$allowed_amp_tags );
    }
    ?>
<amp-iframe width="375" height="815" resizable
    sandbox="allow-scripts allow-same-origin allow-popups allow-top-navigation"
    layout="responsive" frameborder="0"
    src="<?php echo esc_url( sprintf( 'https://amp.spot.im/production.html?spot_im_highlight_immediate=true&spotId=%s&postId=%d', rawurlencode( $ow_id ), intval( $ow_post_id ) ) ); ?>" style="background:transparent" >
    <amp-img placeholder height="815" layout="fill" src="//amp.spot.im/loader.png"></amp-img>
    <div overflow class="ow-im-amp-overflow" tabindex="0" role="button" aria-label="Read more">Load more...</div>
</amp-iframe>
    <?php
    if ( ( 'bottom' === $recirculation_method ) && ( $front->has_ow_recirculation() ) ) {
        ob_start();
        include plugin_dir_path( dirname( __FILE__ ) ) . 'templates/recirculation-amp-template.php';
        $recirculation = ob_get_contents();
        ob_end_clean();

        echo wp_kses( $recirculation, OW_WP::$allowed_amp_tags );
    }
    ?>
</div>
    <?php
endif;
