<?php
/**
 * SpotIM comments template for AMP page.
 *
 * @package spotim-comments
 */

$option = OW_Options::get_instance();
?>
<amp-analytics>
    <script type="application/json">
    {
        "requests": {
        "loaded": "https://pix.spot.im/api/v1/pixel?m=eyJzcG90X2lkIjoic3BfSzF1WmhvU0EiLCJkb21haW4iOiJzaGVmaW5kcy5jb20iLCJzb3VyY2UiOiJyZWNpcmN1bGF0aW9uX2FtcCIsInNvdXJjZV92ZXJzaW9uIjoiMS4wLjAiLCJ0eXBlIjoibG9hZGVkIn0=",
        "engineload": "https://pix.spot.im/api/v1/pixel?m=eyJzcG90X2lkIjoic3BfSzF1WmhvU0EiLCJkb21haW4iOiJzaGVmaW5kcy5jb20iLCJzb3VyY2UiOiJyZWNpcmN1bGF0aW9uX2FtcCIsInNvdXJjZV92ZXJzaW9uIjoiMS4wLjAiLCJ0eXBlIjoiZW5naW5lLW1vbmV0aXphdGlvbi1sb2FkIn0="
        },
        "triggers": {
        "trackPageview": {
            "on": "visible",
            "request": ["loaded", "engineload"]
        }
        },
        "transport": {
        "beacon": false,
        "xhrpost": false,
        "image": true
        }
    }
    </script>
</amp-analytics>
<div
class="ow-title"
style="border-top-color:#ec2223">
    <?php esc_html_e( 'Popular in the Community', 'spotim-comments' ); ?>
</div>

<amp-list
  width="auto"
  height="200"
  layout="fixed-height"
  max-items="3"
  items="items"
  src="<?php echo esc_url( 'https://recirculation.spot.im/spot/json/' . rawurlencode( $option->get( 'ow_id' ) ) ); ?>"
  class="ow-amp-list "
>
  <template type="amp-mustache" id="amp-spotim-rc">
    <amp-carousel height="200" layout="fixed-height" type="carousel">
      {{#values}}
      <div class="item">
        <div class="item-content">
          <a class="item-link" href="{{url}}">
            <amp-img
              layout="fixed"
              width="100"
              height="75"
              alt="{{title}}"
              src="{{image}}"
            ></amp-img>
            <span class="item-title">{{ title }}</span>
          </a>
        </div>
        <div class="item-social">
          <a class="item-link" href="{{url}}">
            <amp-img
              layout="fixed"
              width="28"
              height="28"
              alt="{{username}}"
              src="{{avatar}}"
            ></amp-img>
            <div class="item-social-details">
              <div class="item-comment-head">
                <span>{{ username }}</span> &nbsp
                <span class="time">{{ time }}</span>
              </div>
            </div>
          </a>
        </div>
        <div class="item-comment-text">{{ messageText }}</div>
      </div>
      {{/values}}
    </amp-carousel>
  </template>
</amp-list>
<?php
if ( 1 === absint( $option->get( 'display_rc_amp_ad_tag' ) ) ) {
    include plugin_dir_path( dirname( __FILE__ ) ) . 'templates/recirculation-amp-ad-template.php';
}
?>
