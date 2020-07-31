<?php
$options = OW_Options::get_instance();
$ow_id   = $options->get( 'spot_id' );
?>
<script async
        src="<?php echo esc_url( 'https://launcher.spot.im/spot/' . $ow_id . '?module=newsfeed' ); ?>"
        data-spotim-module="spotim-launcher"
        data-wp-v="<?php echo esc_attr( 'p-' . OW_VERSION .'/wp-' . get_bloginfo( 'version' ) ); ?>"
></script>
