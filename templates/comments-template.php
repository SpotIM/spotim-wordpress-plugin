<?php
$options = SpotIM_Options::get_instance();
$front = new SpotIM_Frontend( $options );
$spot_id = $options->get( 'spot_id' );
$recirculation_method = $options->get( 'rc_embed_method' );
switch( $options->get( 'disqus_identifier' ) ) {
    case 'id':
        $disqus_identifier = get_the_id();
        break;
    case 'short_url':
        $disqus_identifier = esc_url( site_url( '/?p=' . get_the_id() ) );
        break; 
    case 'id_short_url':
    default:
        $disqus_identifier = get_the_id() . ' ' . esc_url( site_url( '/?p=' . get_the_id() ) );
}
?>
<div id="comments-anchor" class="spot-im-comments <?php echo esc_attr( apply_filters( 'spotim_comments_class', $options->get( 'class' ) ) ); ?>">
    <?php
    if ( ( 'top' === $recirculation_method ) && ( $front->has_spotim_recirculation() ) ) {
        ob_start();
        include( plugin_dir_path( dirname( __FILE__ ) ) . 'templates/recirculation-template.php' );
        $recirculation = ob_get_contents();
        ob_end_clean();
        echo $recirculation;
    }
    ?>
    <script async
        data-spotim-module="spotim-launcher"
        data-article-tags="<?php echo implode(', ' , wp_get_post_tags( get_the_ID(), array( 'fields' => 'names' ) )); ?>"
        src="<?php echo esc_url( 'https://launcher.spot.im/spot/' . $spot_id ); ?>"
        data-post-id="<?php echo esc_attr( apply_filters( 'spotim_comments_post_id', get_the_ID() ) ); ?>"
        data-post-url="<?php echo esc_url( apply_filters( 'spotim_comments_post_url', get_permalink() ) ); ?>"
        data-short-url="<?php echo esc_url( apply_filters( 'spotim_comments_disqus_short_url', site_url( '/?p=' . get_the_id() ) ) ); ?>"
        data-messages-count="<?php echo esc_attr( apply_filters( 'spotim_comments_messages_count', $options->get( 'comments_per_page' ) ) ); ?>"
        data-wp-import-endpoint="<?php echo esc_url( apply_filters( 'spotim_comments_feed_link', get_post_comments_feed_link( get_the_id(), 'spotim' ) ) ); ?>"
        data-facebook-url="<?php echo esc_url( apply_filters( 'spotim_comments_facebook_url', get_permalink() ) ); ?>"
        data-disqus-shortname="<?php echo apply_filters( 'spotim_comments_disqus_shortname', $options->get( 'disqus_shortname' ) ); ?>"
        data-disqus-url="<?php echo esc_url( apply_filters( 'spotim_comments_disqus_url', get_permalink() ) ); ?>"
        data-disqus-identifier="<?php echo apply_filters( 'spotim_comments_disqus_identifier', $disqus_identifier ); ?>"
        data-community-question="<?php echo esc_attr( apply_filters( 'spotim_comments_community_question',  get_post_meta( get_the_id(), 'spotim_display_question', true ) ) ); ?>"
        data-seo-enabled="<?php echo apply_filters( 'spotim_comments_seo_enabled', $options->get( 'enable_seo' ) ); ?>"
        data-wp-v="<?php echo esc_attr( 'p-4.3.0/wp-' . get_bloginfo( 'version' ) ); ?>"
    ></script>
    <?php
    if ( ( 'bottom' === $recirculation_method ) && ( $front->has_spotim_recirculation() ) ) {
        ob_start();
        include( plugin_dir_path( dirname( __FILE__ ) ) . 'templates/recirculation-template.php' );
        $recirculation = ob_get_contents();
        ob_end_clean();
        echo $recirculation;
    }
    ?>
</div>
