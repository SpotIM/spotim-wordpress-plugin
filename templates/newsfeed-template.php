<?php
$options = SpotIM_Options::get_instance();
$spot_id = $options->get( 'spot_id' );
?>
<script async
        src="<?php echo esc_url( 'https://launcher.spot.im/spot/' . $spot_id . '?module=newsfeed' ); ?>"
        data-spotim-module="spotim-launcher"
        data-wp-v="<?php echo esc_attr( 'p-' . OW_VERSION .'/wp-' . get_bloginfo( 'version' ) ); ?>"
></script>
