<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * OW_Form_Helper
 *
 * Form helpers.
 *
 * @since 3.0.0
 * @since 5.0.0 Renamed from `SpotIM_Form_Helper` to `OW_Form_Helper`.
 */
class OW_Form_Helper {

	/**
	 * Set name
	 *
	 * @since  3.0.0
	 *
	 * @access private
	 * @static
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	private static function set_name( $args ) {
		if ( ! isset( $args['name'] ) ) {
			$args['name'] = sprintf(
				'%s[%s]',
				esc_attr( $args['page'] ),
				esc_attr( $args['id'] )
			);
		}

		return $args;
	}

	/**
	 * Get description
	 *
	 * @since  3.0.0
	 *
	 * @access private
	 * @static
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	private static function get_description_html( $text = '' ) {
		return sprintf( '<p class="description">%s</p>', wp_kses_post( $text ) );
	}

	/**
	 * Hidden fields
	 *
	 * @since  4.0.0
	 *
	 * @access public
	 * @static
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	public static function hidden_field( $args ) {
		$args          = self::set_name( $args );
		$args['value'] = sanitize_text_field( $args['value'] );

		// Text input template
		printf(
			'<input name="%1$s" type="hidden" value="%2$s" />',
			esc_attr( $args['name'] ), // Input's name.
			esc_attr( $args['value'] ) // Input's value.
		);
	}

	/**
	 * Radio fields
	 *
	 * @since  4.0.0
	 *
	 * @access public
	 * @static
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	public static function radio_fields( $args ) {
		$args     = self::set_name( $args );
		$template = '';

		foreach ( $args['fields'] as $key => $value ) {
			$template .= sprintf(
				'<label class="description"><input type="radio" name="%1$s" value="%2$s" %3$s /> %4$s</label><br>',
				esc_attr( $args['name'] ), // Input's name.
				esc_attr( $key ), // Input's value.
				checked( $args['value'], $key, 0 ), // If input checked or not.
				esc_html( $value ) // Translated text.
			);
		}

		// Description template
		if ( isset( $args['description'] ) ) {
			$template .= self::get_description_html( $args['description'] );
		}

		echo wp_kses( $template, self::get_whitelisted_tags() );
	}

	/**
	 * Text fields
	 *
	 * @since  3.0.0
	 *
	 * @access public
	 * @static
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	public static function text_field( $args ) {
		$args          = self::set_name( $args );
		$args['value'] = sanitize_text_field( $args['value'] );
		if ( isset( $args['other'] ) ) {
			$args['other'] = sanitize_text_field( $args['other'] );
		} else {
			$args['other'] = '';
		}

		// Text input template
		$template = sprintf(
			'<input name="%1$s" type="text" value="%2$s" autocomplete="off" %3$s />',
			esc_attr( $args['name'] ),  // Input's name.
			esc_attr( $args['value'] ), // Input's value.
			esc_attr( $args['other'] )  // Other input attributes like 'readonly' or `disabled`.
		);

		// Description template
		if ( isset( $args['description'] ) ) {
			$template .= self::get_description_html( $args['description'] );
		}

		echo wp_kses( $template, self::get_whitelisted_tags() );
	}

	/**
	 * Number fields
	 *
	 * @since  4.0.4
	 *
	 * @access public
	 * @static
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	public static function number_field( $args ) {
		$args          = self::set_name( $args );
		$args['value'] = (int) $args['value'];
		if ( isset( $args['other'] ) ) {
			$args['other'] = sanitize_text_field( $args['other'] );
		} else {
			$args['other'] = '';
		}

		// Text input template
		$template = sprintf(
			'<input name="%1$s" type="number" value="%2$s" min="%3$s" max="%4$s" autocomplete="off" %5$s />',
			esc_attr( $args['name'] ),  // Input's name.
			esc_attr( $args['value'] ), // Input's value.
			esc_attr( $args['min'] ),   // Input's min value.
			esc_attr( $args['max'] ),   // Input's max value.
			esc_attr( $args['other'] )  // Other input attributes like 'readonly' or `disabled`.
		);

		// Description template
		if ( isset( $args['description'] ) ) {
			$template .= self::get_description_html( $args['description'] );
		}

		echo wp_kses( $template, self::get_whitelisted_tags() );
	}

	/**
	 * Button fields
	 *
	 * @since  3.0.0
	 *
	 * @access public
	 * @static
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	public static function button( $args ) {
		$template = sprintf(
			'<button id="%1$s" class="button button-primary">%2$s</button>',
			esc_attr( $args['id'] ), // Button's id.
			esc_attr( $args['text'] ) // Button's text.
		);

		// Description template
		if ( isset( $args['description'] ) ) {
			$template .= self::get_description_html( $args['description'] );
		}

		echo wp_kses( $template, self::get_whitelisted_tags() );
	}

	/**
	 * Import Button fields
	 *
	 * @since  3.0.0
	 *
	 * @access public
	 * @static
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	public static function import_button( $args ) {
		$ow = ow_instance();

		// Import button
		$template = sprintf(
			'<button id="%1$s" class="button button-primary sync-button" data-import-token="%2$s" data-spot-id="%3$s" data-posts-per-request="%4$s">%5$s</button>',
			esc_attr( $args['import_button']['id'] ), // Button's id.
			esc_attr( $ow->options->get( 'import_token' ) ), // Import token
			esc_attr( $ow->options->get( 'spot_id' ) ), // Spot ID
			esc_attr( $ow->options->get( 'posts_per_request' ) ), // Posts per request
			esc_attr( $args['import_button']['text'] ) // Button's text.
		);

		// Force re-import (Delete import cache)
		$template .= sprintf(
			'<button id="%1$s" style="margin:0 10px;" class="button button-primary sync-button force" data-import-token="%2$s" data-spot-id="%3$s" data-posts-per-request="%4$s" data-force="true">%5$s</button>',
			esc_attr( $args['force_import_button']['id'] ), // Button's id.
			esc_attr( $ow->options->get( 'import_token' ) ), // Import token
			esc_attr( $ow->options->get( 'spot_id' ) ), // Spot ID
			esc_attr( $ow->options->get( 'posts_per_request' ) ), // Posts per request
			esc_attr( $args['force_import_button']['text'] ) // Button's text.
		);

		$template .= "<br />" . $args['force_import_button']['description'];

		// Cancel import
		$template .= sprintf(
			'<a href="#cancel" id="%1$s" class="">%2$s</a>',
			esc_attr( $args['cancel_import_link']['id'] ), // Link's id.
			esc_attr( $args['cancel_import_link']['text'] ) // Link's text.
		);

		// Description template
		$template .= self::get_description_html();
		$template .= '<div class="errors spotim-errors spotim-hide red-color"></div>';

		echo wp_kses( $template, self::get_whitelisted_tags() );
	}

	/**
	 * Get Allowed tags and attributes for form fields.
	 *
	 * @return array
	 */
	public static function get_whitelisted_tags() {

		$allowed_tags = array(
			'label'  => array(
				'class' => 'description',
			),
			'input'  => array(
				'type'         => array( 'radio', 'text', 'number' ),
				'name'         => array(),
				'value'        => array(),
				'checked'      => array(),
				'autocomplete' => array(),
				'min'          => array(),
				'max'          => array(),
				'readonly'     => array(),
				'disabled'     => array(),
			),
			'br'     => array(),
			'p'      => array(
				'class' => array(),
			),
			'button' => array(
				'id'                     => array(),
				'class'                  => array(),
				'data-import-token'      => array(),
				'data-spot-id'           => array(),
				'data-posts-per-request' => array(),
				'data-force'             => array(),
				'style'                  => array()
			),
			'a'      => array(
				'id'    => array(),
				'href'  => array(),
				'class' => array()
			),
			'div'    => array(
				'class' => array(),
			),
		);

		return $allowed_tags;
	}
}
