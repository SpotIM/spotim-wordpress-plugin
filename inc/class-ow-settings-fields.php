<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * OW_Settings_Fields
 *
 * Plugin settings fields.
 *
 * @since 2.0.0
 * @since 5.0.0 Renamed from `SpotIM_Settings_Fields` to `OW_Settings_Fields`.
 */
class OW_Settings_Fields {

	/**
	 * Constructor
	 *
	 * Get things started.
	 *
	 * @since  2.0.0
	 *
	 * @access public
	 *
	 * @param OW_Options $options Plugin options.
	 */
	public function __construct( $options ) {
		$this->options = $options;
	}

	/**
	 * Register Settings
	 *
	 * Register admin settings for the plugin.
	 *
	 * @since  2.0.0
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function register_settings() {
		register_setting(
			$this->options->option_group,
			$this->options->slug,
			array( $this->options, 'validate' )
		);
	}

	/**
	 * General Settings Section Header
	 *
	 * @since  2.0.0
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function general_settings_section_header() {
		echo '<p>';
		esc_html_e( 'OpenWeb.Com\'s WordPress plugin is currently available for OpenWeb.Com partners only.', 'ow' );
		echo '<br>';
		printf(
			esc_html__( 'To become a partner and retrieve your OpenWeb ID (OW ID), please submit your information %1$shere%2$s', 'ow' ),
			'<a href="https://www.openweb.com/contact/" target="_blank">',
			'</a>'
		);
		echo '</p>';
	}

	/**
	 * Display Settings Section Header
	 *
	 * @since  2.0.0
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function display_settings_section_header() {
		echo '<p>' . esc_html__( 'Select where to display OpenWeb.Com comment box.', 'ow' ) . '</p>';
	}

	/**
	 * Advanced Settings Section Header
	 *
	 * @since  4.1.0
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function advanced_settings_section_header() {
		echo '<p>' . esc_html__( 'Your OpenWeb.Com account manager may ask you to change these settings after reviewing the installation.', 'ow' ) . '</p>';
	}

	/**
	 * Import Settings Section Header
	 *
	 * @since  2.0.0
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function import_settings_section_header() {
		echo '<p>';
		esc_html_e( 'Export your comments from OpenWeb.Com to WordPress.', 'ow' );
		echo '<br><em>';
		esc_html_e( 'This is different from importing comments from WordPress to OpenWeb.Com.', 'ow' );
		echo '<br>';
		esc_html_e( 'Contact your OpenWeb.Com account manager to configure import from WordPress to OpenWeb.Com.', 'ow' );
		echo '</em></p>';
	}

	/**
	 * Register General Section
	 *
	 * @since  2.0.0
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function register_general_section() {
		$spot_id = $this->options->get( 'spot_id' );

		add_settings_section(
			'general_settings_section',
			esc_html__( 'General Options', 'ow' ),
			array( $this, 'general_settings_section_header' ),
			$this->options->slug
		);

		add_settings_field(
			'spot_id',
			esc_html__( 'OpenWeb ID', 'ow' ),
			array( 'OW_Form_Helper', 'text_field' ),
			$this->options->slug,
			'general_settings_section',
			array(
				'id'          => 'spot_id',
				'page'        => $this->options->slug,
				'description' => esc_html__( 'Contact your OpenWeb.Com account manager to get your OpenWeb ID.', 'ow' ),
				'value'       => $spot_id
			)
		);

	}

	/**
	 * Register Display Section
	 *
	 * @since  2.0.0
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function register_display_section() {
		add_settings_section(
			'display_settings_section',
			esc_html__( 'Display Options', 'ow' ),
			array( $this, 'display_settings_section_header' ),
			$this->options->slug
		);

		$post_types = get_post_types( array( 'public' => true ), 'objects' );

		if ( ! empty( $post_types ) ) {

			foreach ( $post_types as $value ) {

				// Check if post type support comments
				if ( post_type_supports( $value->name, 'comments' ) ) {

					$display_value = $this->options->get( "display_{$value->name}" );

					// Backwards compitability check - rewrite old structure
					if ( ( 'comments' === $display_value ) || ( 'comments_recirculation' === $display_value ) ) {
						$display_value = 1;
					}

					add_settings_field(
						"display_{$value->name}",
						sprintf( esc_html__( 'Display on %s', 'ow' ), $value->label ),
						array( 'OW_Form_Helper', 'radio_fields' ),
						$this->options->slug,
						'display_settings_section',
						array(
							'id'     => "display_{$value->name}",
							'page'   => $this->options->slug,
							'fields' => array(
								'0' => esc_html__( 'Disable', 'ow' ),
								'1' => esc_html__( 'Enable', 'ow' ),
							),
							'value'  => $display_value
						)
					);

				}

			}

		}

		add_settings_field(
			'display_comments_count',
			esc_html__( 'Display Comments Count', 'ow' ),
			array( 'OW_Form_Helper', 'radio_fields' ),
			$this->options->slug,
			'display_settings_section',
			array(
				'id'     => "display_comments_count",
				'page'   => $this->options->slug,
				'fields' => array(
					'0'     => esc_html__( 'Disable', 'ow' ),
					'title' => esc_html__( 'Below title', 'ow' )
				),
				'value'  => $this->options->get( 'display_comments_count' )
			)
		);

		add_settings_field(
			'display_newsfeed',
			esc_html__( 'Display Newsfeed on non-article pages', 'ow' ),
			array( 'OW_Form_Helper', 'radio_fields' ),
			$this->options->slug,
			'display_settings_section',
			array(
				'id'     => "display_newsfeed",
				'page'   => $this->options->slug,
				'fields' => array(
					'0' => esc_html__( 'Disable', 'ow' ),
					'1' => esc_html__( 'Enable', 'ow' )
				),
				'value'  => $this->options->get( 'display_newsfeed' )
			)
		);

		add_settings_field(
			'comments_per_page',
			esc_html__( 'Comments Per Page', 'ow' ),
			array( 'OW_Form_Helper', 'number_field' ),
			$this->options->slug,
			'display_settings_section',
			array(
				'id'          => 'comments_per_page',
				'description' => esc_html__( 'Default: 10', 'ow' ),
				'page'        => $this->options->slug,
				'value'       => $this->options->get( 'comments_per_page' ),
				'min'         => 1,
				'max'         => '999'
			)
		);

	}

	/**
	 * Register Advanced Section
	 *
	 * @since  4.1.0
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function register_advanced_section() {

		add_settings_section(
			'advanced_settings_section',
			esc_html__( 'Advanced Options', 'ow' ),
			array( $this, 'advanced_settings_section_header' ),
			$this->options->slug
		);

		add_settings_field(
			'embed_method',
			esc_html__( 'Comments Embed Method', 'ow' ),
			array( 'OW_Form_Helper', 'radio_fields' ),
			$this->options->slug,
			'advanced_settings_section',
			array(
				'id'          => 'embed_method',
				'page'        => $this->options->slug,
				'fields'      => array(
					'comments' => esc_html__( 'Replace WordPress Comments', 'ow' ),
					'content'  => esc_html__( 'Insert After the Content', 'ow' ),
					'manual'   => esc_html__( 'Let the theme decide', 'ow' ),
				),
				'description' => esc_html__( "When choosing 'Let the theme decide', please inject the 'OW_Frontend::display_comments()' code wherever comments should be displayed.", 'ow' ),
				'value'       => $this->options->get( 'embed_method' )
			)
		);

		add_settings_field(
			'rc_embed_method',
			esc_html__( 'Recirculation Embed Method', 'ow' ),
			array( 'OW_Form_Helper', 'radio_fields' ),
			$this->options->slug,
			'advanced_settings_section',
			array(
				'id'     => 'rc_embed_method',
				'page'   => $this->options->slug,
				'fields' => array(
					'regular' => esc_html__( 'Regular', 'ow' ),
					'top'     => esc_html__( 'Inline - top', 'ow' ),
					'bottom'  => esc_html__( 'Inline - bottom', 'ow' ),
					'none'    => esc_html__( 'None', 'ow' ),
				),
				'value'  => $this->options->get( 'rc_embed_method' )
			)
		);

		add_settings_field(
			'display_rc_amp_ad_tag',
			esc_html__( 'Recirculation AMP Ad tag', 'ow' ),
			array( 'OW_Form_Helper', 'radio_fields' ),
			$this->options->slug,
			'advanced_settings_section',
			array(
				'id'          => 'display_rc_amp_ad_tag',
				'page'        => $this->options->slug,
				'fields'      => array(
					'1' => esc_html__( 'Enable', 'ow' ),
					'0' => esc_html__( 'Disable', 'ow' ),
				),
				'description' => esc_html__( 'Please contact your account manager to activate the Ad tag for AMP powered pages.', 'ow' ),
				'value'       => $this->options->get( 'display_rc_amp_ad_tag' ),
			)
		);

		add_settings_field(
			'enable_rating_reviews',
			esc_html__( 'Star rating reviews in conversation', 'ow' ),
			array( 'OW_Form_Helper', 'radio_fields' ),
			$this->options->slug,
			'advanced_settings_section',
			array(
				'id'          => 'enable_rating_reviews',
				'page'        => $this->options->slug,
				'fields'      => array(
					'1' => esc_html__( 'Enable', 'ow' ),
					'0' => esc_html__( 'Disable', 'ow' ),
				),
				'value'       => $this->options->get( 'enable_rating_reviews' ),
			)
		);



		add_settings_field(
			'display_priority',
			esc_html__( 'Display Priority', 'ow' ),
			array( 'OW_Form_Helper', 'number_field' ),
			$this->options->slug,
			'advanced_settings_section',
			array(
				'id'    => 'display_priority',
				'page'  => $this->options->slug,
				'value' => $this->options->get( 'display_priority' ),
				'min'   => '0',
				'max'   => '10000'
			)
		);

		add_settings_field(
			'enable_seo',
			esc_html__( 'Enable SEO', 'ow' ),
			array( 'OW_Form_Helper', 'radio_fields' ),
			$this->options->slug,
			'advanced_settings_section',
			array(
				'id'     => 'enable_seo',
				'page'   => $this->options->slug,
				'fields' => array(
					'false' => esc_html__( 'Disable', 'ow' ),
					'true'  => esc_html__( 'Enable', 'ow' ),
				),
				'value'  => $this->options->get( 'enable_seo' )
			)
		);

		add_settings_field(
			'enable_og',
			esc_html__( 'OpenGraph Tags', 'ow' ),
			array( 'OW_Form_Helper', 'radio_fields' ),
			$this->options->slug,
			'advanced_settings_section',
			array(
				'id'     => 'enable_og',
				'page'   => $this->options->slug,
				'fields' => array(
					'false' => esc_html__( 'Disable', 'ow' ),
					'true'  => esc_html__( 'Enable', 'ow' ),
				),
				'value'  => $this->options->get( 'enable_og' )
			)
		);

		add_settings_field(
			'class',
			esc_html__( 'Container Class', 'ow' ),
			array( 'OW_Form_Helper', 'text_field' ),
			$this->options->slug,
			'advanced_settings_section',
			array(
				'id'    => 'class',
				'page'  => $this->options->slug,
				'value' => $this->options->get( 'class' ),
			)
		);

		add_settings_field(
			'disqus_shortname',
			esc_html__( 'Disqus Shortname', 'ow' ),
			array( 'OW_Form_Helper', 'text_field' ),
			$this->options->slug,
			'advanced_settings_section',
			array(
				'id'    => 'disqus_shortname',
				'page'  => $this->options->slug,
				'value' => $this->options->get( 'disqus_shortname' ),
			)
		);

		add_settings_field(
			'disqus_identifier',
			esc_html__( 'Disqus Identifier Structure', 'ow' ),
			array( 'OW_Form_Helper', 'radio_fields' ),
			$this->options->slug,
			'advanced_settings_section',
			array(
				'id'     => 'disqus_identifier',
				'page'   => $this->options->slug,
				'fields' => array(
					'id'           => esc_html__( 'ID', 'ow' ),
					'short_url'    => esc_html__( 'Short URL', 'ow' ),
					'id_short_url' => esc_html__( 'ID + Short URL (Default)', 'ow' ),
				),
				'value'  => $this->options->get( 'disqus_identifier' )
			)
		);

		add_settings_field(
			'import_button',
			esc_html__( 'Start Manual Sync', 'ow' ),
			array( 'OW_Form_Helper', 'import_button' ),
			$this->options->slug,
			'advanced_settings_section',
			array(
				'import_button'       => array(
					'id'   => 'import_button',
					'text' => esc_html__( 'Sync Now', 'ow' )
				),
				'force_import_button' => array(
					'id'          => 'force_import_button',
					'text'        => esc_html__( 'Reset + Sync Now', 'ow' ),
					'description' => esc_html__( 'Use Sync Now to sync data starting from the last sync time.', 'ow' ) . "<br />" . esc_html__( 'Use Reset + Sync Now to clear any old synced data and start a fresh sync.', 'ow' )
				),
				'cancel_import_link'  => array(
					'id'   => 'cancel_import_link',
					'text' => esc_html__( 'Cancel', 'ow' )
				)
			)
		);

	}

	/**
	 * Register Import Section
	 *
	 * @since  2.0.0
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function register_import_section() {

		// Initialize array to display cron schedules.
		$schedule_fields = array();

		add_settings_section(
			'import_settings_section',
			esc_html__( 'Comments Sync Options', 'ow' ),
			array( $this, 'import_settings_section_header' ),
			$this->options->slug
		);

		add_settings_field(
			'import_token',
			esc_html__( 'Sync Token', 'ow' ),
			array( 'OW_Form_Helper', 'text_field' ),
			$this->options->slug,
			'import_settings_section',
			array(
				'id'          => 'import_token',
				'page'        => $this->options->slug,
				'description' => esc_html__( 'Contact your OpenWeb.Com account manager to get your sync token.', 'ow' ),
				'value'       => $this->options->get( 'import_token' )
			)
		);

		$spot_id              = $this->options->get( 'spot_id' );
		$import_token         = $this->options->get( 'import_token' );
		$schedule_fields['0'] = esc_html__( 'No', 'ow' );
		$registered_schedules = wp_get_schedules();
		if ( ! empty( $registered_schedules ) ) {
			foreach ( $registered_schedules as $key => $value ) {
				$schedule_fields[ $key ] = $value['display'];
			}
		}

		add_settings_field(
			'auto_import',
			esc_html__( 'Enable Auto Sync', 'ow' ),
			array( 'OW_Form_Helper', 'radio_fields' ),
			$this->options->slug,
			'import_settings_section',
			array(
				'id'          => 'auto_import',
				'page'        => $this->options->slug,
				'description' => esc_html__( 'Enable auto-sync and set how often should it reoccur.', 'ow' )
								 . '<br>'
								 . $this->options->get_next_cron_execution( wp_next_scheduled( 'spotim_scheduled_import' ) )
								 . ( empty( $spot_id ) ? ' ' . esc_html__( 'OpenWeb ID is missing.', 'ow' ) : '' )
								 . ( empty( $import_token ) ? ' ' . esc_html__( 'Import token is missing.', 'ow' ) : '' ),
				'fields'      => $schedule_fields,
				'value'       => $this->options->get( 'auto_import' )
			)
		);

		// If import is running don't allow the user to update "Posts Per Request"
		$other_attr = '';
		if ( $this->options->get( 'page_number' ) > 0 ) {
			$other_attr = 'readonly';
		}

		add_settings_field(
			'posts_per_request',
			esc_html__( 'Posts Per Request', 'ow' ),
			array( 'OW_Form_Helper', 'number_field' ),
			$this->options->slug,
			'import_settings_section',
			array(
				'id'          => 'posts_per_request',
				'page'        => $this->options->slug,
				'description' => esc_html__( 'On every sync, several requests will be made to your server. This is the amount of posts that will be retrieved in each request. Default: 10.', 'ow' ),
				'value'       => $this->options->get( 'posts_per_request' ),
				'min'         => '0',
				'max'         => '100',
				'other'       => $other_attr
			)
		);

		// hidden spot id for the import js
		add_settings_field(
			'spot_id',
			null,
			array( 'OW_Form_Helper', 'hidden_field' ),
			$this->options->slug,
			'import_settings_section',
			array(
				'id'    => 'spot_id',
				'page'  => $this->options->slug,
				'value' => $spot_id
			)
		);

	}

}
