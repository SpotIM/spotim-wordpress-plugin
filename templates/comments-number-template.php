<?php
$options = OW_Options::get_instance();
$spot_id = $options->get( 'spot_id' );
?>
<script async
        data-spotim-module="spotim-launcher"
        src="<?php echo esc_url( sprintf( 'https://launcher.spot.im/spot/%s?module=messages-count', $spot_id ) ); ?>"
        data-spot-id="<?php echo esc_attr( $spot_id ); ?>">
</script>
