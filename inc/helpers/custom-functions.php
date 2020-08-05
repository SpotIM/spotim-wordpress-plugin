<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get post meta.
 *
 * @param int    $post_id Post ID.
 * @param string $key     Meta Key.
 * @param bool   $single  Flag to set return type. Same as 'get_post_meta' function.
 *
 * @since 5.0.0
 *
 * @access public
 *
 * @return mixed
 */
function ow_get_post_meta( $post_id, $key, $single = false ) {

    if ( empty( $post_id ) || empty( $key ) ) {
        return;
    }

    $meta_value = get_post_meta( $post_id, $key, $single );

    if ( true === metadata_exists( 'post', $post_id, $key ) ) {
        return $meta_value;
    }

    // Check is OpenWeb meta or not.
    $is_spot_im_meta = strpos( $key, 'ow' );

    // Return as its not OpenWeb meta.
    if ( false === $is_spot_im_meta ) {
        return $meta_value;
    }

    // Replace 'ow' with 'spotim' to match old meta key.
    $spot_im_key = str_replace( 'ow', 'spotim', $key );
    $meta_value  = get_post_meta( $post_id, $spot_im_key, $single );

    // Set old key data to new one.
    update_post_meta( $post_id, $key, $meta_value );

    return $meta_value;

}

/**
 * Function to add deprecation message.
 *
 * @since 5.0.0
 *
 * @param string $message Message to show as deprecation notice.
 *
 * @return bool
 */
function ow_deprecated( $message ) {

    // Return if message empty or 'WP_DEBUG' not defined or 'WP_DEBUG' set to false.
    if ( empty( $message ) || ! defined( 'WP_DEBUG' ) || false === WP_DEBUG ) {
        return false;
    }

    $level = defined( 'E_USER_DEPRECATED' ) ? E_USER_DEPRECATED : E_USER_WARNING;

    // Return true if error_type is valid else false.
    return trigger_error( esc_html( $message ), $level ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error

}

/**
 * Function to add notice for deprecated metas.
 *
 * @param null|array|string $value     The value should return - a single metadata value,
 *                                     or an array of values.
 * @param int               $object_id ID of the object metadata is for.
 * @param string            $meta_key  Metadata key.
 * @param bool              $single    Whether to return only the first value of the specified $meta_key.
 *
 * @return void
 */
function ow_deprecated_meta( $value, $object_id, $meta_key, $single ) {

    $deprecated_metas = ow_get_deprecated_meta();

    if ( ! isset( $deprecated_metas[$meta_key] ) ) {
        return $value;
    }

    ow_deprecated(
        sprintf(
            // translators: %1$s Old meta key, %2$s: New meta key.
            __( 'Meta key %1$s is deprecated. This meta %1$s may remove in future. Please use %2$s meta instead.', 'spotim-comments' ),
            esc_html( $meta_key ),
            esc_html( $deprecated_metas[$meta_key] )
        )
    );

    return $value;
}
add_filter( 'get_post_metadata', 'ow_deprecated_meta', 10, 4 );

/**
 * Function to get deprecated meta list.
 *
 * @return array
 */
function ow_get_deprecated_meta() {

    return [
        'spotim_display_comments'      => 'ow_display_comments',
        'spotim_display_question'      => 'ow_display_question',
        'spotim_display_recirculation' => 'ow_display_recirculation',
        'spotim_etag'                  => 'ow_etag',
        'spotim_messages_map'          => 'ow_messages_map',
    ];

}
