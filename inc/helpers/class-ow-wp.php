<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * OW_WP
 *
 * WordPress Wrapper functions for all env.
 *
 * @since 3.0.0
 * @since 5.0.0 Renamed from `SpotIM_WP` to `OW_WP`.
 */
class OW_WP {

	/**
	 * List of allowed tags for AMP templates.
	 * @var array
	*/
	public static $allowed_amp_tags = array(
		'div' => array(
			'class'       => array(),
			'overflow'    => array(),
			'tabindex'    => array(),
			'role'        => array(),
			'aria-label'  => array(),
			'style'       => array(),
			'placeholder' => array(),
			'fallback'    => array(),
		),
		'amp-iframe'      => array(
			'width'       => array(),
			'height'      => array(),
			'resizable'   => array(),
			'sandbox'     => array(),
			'layout'      => array(),
			'frameborder' => array(),
			'src'         => array(),
			'style'       => array(),
		),
		'amp-analytics' => array(),
		'script' => array(
			'type' => array( 'application/json' ),
		),
		'amp-list' => array(
			'width'     => array(),
			'height'    => array(),
			'layout'    => array(),
			'max-items' => array(),
			'items'     => array(),
			'src'       => array(),
			'class'     => array(),
		),
		'template' => array(
			'type' => array(),
			'id'   => array(),
		),
		'amp-carousel' => array(
			'height' => array(),
			'layout' => array(),
			'type'   => array(),
		),
		'a' => array(
			'class'       => array(),
			'href'        => array(),
			'data-spmark' => array(),
			'target'      => array(),
		),
		'amp-img' => array(
			'layout'      => array(),
			'width'       => array(),
			'height'      => array(),
			'alt'         => array(),
			'src'         => array(),
			'placeholder' => array(),
		),
		'span' => array(
			'class' => array(),
		),
		'amp-ad' => array(
			'width'     => array(),
			'height'    => array(),
			'type'      => array(),
			'data-slot' => array(),
			'json'      => array(),
			'data-id'   => array(),
		)
	);

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
	public static function remote_get( $url, $args = array(), $fallback_value = '', $threshold = 3, $timeout = 1, $retry = 20 ) {

		if ( ow_is_vip() ) {
			$response = vip_safe_wp_remote_get( $url, $fallback_value, $threshold, $timeout, $retry, $args );
		} else {
			$response = wp_remote_get( $url, $args ); // phpcs:ignore
		}

		return $response;
	}

	/**
	 * Checks if plugin is active and the current page is amp page.
	 *
	 * @access public
	 * @static
	 *
	 * @return bool
	 */
	public static function is_amp() {
		return function_exists( 'is_amp_endpoint' ) && is_amp_endpoint();
	}
}
