<a
data-spmark="ad-choice"
href="https://dynamic-cdn.spot.im/yad/optout.html"
target="_blank">
    <amp-img src="https://publisher-assets.spot.im/yad/ad-choises.png" width="9px" height="9px" />
</a>

<amp-list
  width="auto"
  height="300"
  layout="fixed-height"
  max-items="1"
  items="items"
  src="<?php echo esc_url( sprintf( 'https://spotops.spot.im/spot/%s/recirculation/amp', rawurlencode( $option->get( 'spot_id' ) ) ) ); ?>"
  class="spotim-amp-list-ad">

    <template type="amp-mustache" id="amp-spotim-rc">
    {{^isVideo}}
    <amp-ad
        width={{width}}
        height={{height}}
        type="{{type}}"
        data-slot="{{code}}"
        json='{"targeting": {"ampRCSpotId": ["<?php echo esc_attr( $option->get( 'spot_id' ) ); ?>"] }}'>
        <div placeholder></div>
        <div fallback></div>
    </amp-ad>
    {{/isVideo}}

    {{#isVideo}}
    <amp-ad
        width={{width}}
        height={{height}}
        type="{{type}}"
        data-id="{{code}}"
    >
    </amp-ad>
    {{/isVideo}}
    </template>

</amp-list>
