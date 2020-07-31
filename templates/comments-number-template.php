<?php
$options = OW_Options::get_instance();
$ow_id   = $options->get( 'spot_id' );
?>
<script async
        data-spotim-module="spotim-launcher"
        src="<?php echo esc_url( sprintf( 'https://launcher.spot.im/spot/%s?module=messages-count', $ow_id ) ); ?>"
        data-spot-id="<?php echo esc_attr( $ow_id ); ?>">
</script>
