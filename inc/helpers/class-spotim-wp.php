<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * SpotIM_Wp
 *
 * WordPress Wrapper functions for all env.
 *
 * @since 3.0.0
 */
class SpotIM_WP {

    /**
     * Retrieve the raw response from the HTTP request using the GET method.
     *
     * @see wp_remote_request() For more information on the response array format.
     * @see WP_Http::request() For default arguments information.
     *
     * @param string $url            Site URL to retrieve.
     * @param array  $args           Optional. Request arguments. Default empty array.
     * @param string $fallback_value Optional. Set a fallback value to be returned if the external request fails.
     * @param int    $threshold      Optional. The number of fails required before subsequent requests automatically return the fallback value. Defaults to 3, with a maximum of 10.
     * @param int    $timeout        Optional. Number of seconds before the request times out. Valid values 1-3; defaults to 1.
     * @param int    $retry          Optional. Number of seconds before resetting the fail counter and the number of seconds to delay making new requests after the fail threshold is reached. Defaults to 20, with a minimum of 10.
     *
     * @return WP_Error|array The response or WP_Error on failure.
     */
    public static function spotim_remote_get( $url, $args = array(), $fallback_value = '', $threshold = 3, $timeout = 1, $retry = 20 ) {

        if ( spotim_is_vip() ) {
            $response = vip_safe_wp_remote_get( $url, $fallback_value, $threshold, $timeout, $retry, $args );
        } else {
            $response = wp_remote_get( $url, $args ); // phpcs:ignore
        }

        return $response;
    }
}
