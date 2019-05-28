<?php
/**
 * SpotIM comments template for AMP page.
 *
 * @package spotim-comments
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$spot_id      = SpotIM_Options::get_instance()->get( 'spot_id' );
$spot_post_id = get_the_ID();
if( ! empty( $spot_id ) && ! empty( $spot_post_id ) ) :
    ?>
<amp-iframe width="375" height="815" resizable
    sandbox="allow-scripts allow-same-origin allow-popups allow-top-navigation"
    layout="responsive" frameborder="0"
    src="https://amp.spot.im/production.html?spot_im_highlight_immediate=true&spotId=<?php echo esc_attr( $spot_id ); ?>&postId=<?php echo esc_attr( $spot_post_id ); ?>" style="background:transparent" >
    <amp-img placeholder height="815" layout="fill" src="//amp.spot.im/loader.png"></amp-img>
    <div overflow class="spot-im-amp-overflow" tabindex="0" role="button" aria-label="Read more">Load more...</div>
</amp-iframe>
    <?php
endif;
