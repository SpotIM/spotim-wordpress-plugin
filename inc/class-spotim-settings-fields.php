<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * SpotIM_Settings_Fields
 *
 * Plugin settings fields.
 *
 * @since 2.0.0
 */
class SpotIM_Settings_Fields {

    /**
     * Constructor
     *
     * Get things started.
     *
     * @since 2.0.0
     *
     * @access public
     *
     * @param SpotIM_Options $options Plugin options.
     */
    public function __construct( $options ) {
        $this->options = $options;
    }

    /**
     * Register Settings
     *
     * Register admin settings for the plugin.
     *
     * @since 2.0.0
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
     * @since 2.0.0
     *
     * @access public
     *
     * @return void
     */
    public function general_settings_section_header() {
        echo '<p>';
        esc_html_e( 'Spot.IM\'s WordPress plugin is currently available for Spot.IM partners only.', 'spotim-comments' );
        echo '<br>';
        printf(
            __( 'To become a partner and retrieve your Spot ID, please submit your information <a href="%s" target="_blank">here</a>.', 'spotim-comments'),
            'https://www.spot.im/request-an-invite/'
        );
        echo '</p>';
    }

    /**
     * Display Settings Section Header
     *
     * @since 2.0.0
     *
     * @access public
     *
     * @return void
     */
    public function display_settings_section_header() {
        echo '<p>' . esc_html__( 'Select where to display Spot.IM.', 'spotim-comments' ) . '</p>';
    }

    /**
     * Advanced Settings Section Header
     *
     * @since 4.1.0
     *
     * @access public
     *
     * @return void
     */
    public function advanced_settings_section_header() {
        echo '<p>' . esc_html__( 'Your Spot.IM account manager may ask you to change these settings after revewing the installation.', 'spotim-comments' ) . '</p>';
    }

    /**
     * Import Settings Section Header
     *
     * @since 2.0.0
     *
     * @access public
     *
     * @return void
     */
    public function import_settings_section_header() {
        echo '<p>';
        esc_html_e( 'Export your comments from Spot.IM to WordPress.', 'spotim-comments' );
        echo '<br><em>';
        esc_html_e( 'This is different from importing comments from WordPress to Spot.IM.', 'spotim-comments' );
        echo '<br>';
        esc_html_e( 'Contact your Spot.IM account manager to configure import from WordPress to Spot.IM.', 'spotim-comments' );
        echo '</em></p>';
    }

    /**
     * Register General Section
     *
     * @since 2.0.0
     *
     * @access public
     *
     * @return void
     */
    public function register_general_section() {
        $spot_id = $this->options->get( 'spot_id' );

        add_settings_section(
            'general_settings_section',
            esc_html__( 'General Options', 'spotim-comments' ),
            array( $this, 'general_settings_section_header' ),
            $this->options->slug
        );

        add_settings_field(
            'spot_id',
            esc_html__( 'Spot ID', 'spotim-comments' ),
            array( 'SpotIM_Form_Helper', 'text_field' ),
            $this->options->slug,
            'general_settings_section',
            array(
                'id' => 'spot_id',
                'page' => $this->options->slug,
                'description' => esc_html__( 'Contact your Spot.IM account manager to get your Spot ID.' , 'spotim-comments' ),
                'value' => $spot_id
            )
        );

    }

    /**
     * Register Display Section
     *
     * @since 2.0.0
     *
     * @access public
     *
     * @return void
     */
    public function register_display_section() {
        add_settings_section(
            'display_settings_section',
            esc_html__( 'Display Options', 'spotim-comments' ),
            array( $this, 'display_settings_section_header' ),
            $this->options->slug
        );

        $post_types = get_post_types( array( 'public' => true ), 'objects' );

        if ( ! empty( $post_types ) ) {

            foreach ( $post_types as $key => $value ) {

                // Check if post type support comments
                if ( post_type_supports( $value->name, 'comments' ) ) {

                    $display_value = $this->options->get( "display_{$value->name}" );

                    // Backwards compitability check - rewrite old structure
                    if ( ( 'comments' === $display_value ) || ( 'comments_recirculation' === $display_value ) ) { $display_value = 1; }

                    add_settings_field(
                        "display_{$value->name}",
                        sprintf( esc_html__( 'Display on %s', 'spotim-comments' ), $value->label ),
                        array( 'SpotIM_Form_Helper', 'radio_fields' ),
                        $this->options->slug,
                        'display_settings_section',
                        array(
                            'id' => "display_{$value->name}",
                            'page' => $this->options->slug,
                            'fields' => array(
                                '0' => esc_html__( 'Disable', 'spotim-comments' ),
                                '1' => esc_html__( 'Enable', 'spotim-comments' ),
                            ),
                            'value' => $display_value
                        )
                    );

                }

            }

        }

        add_settings_field(
            'display_comments_count',
            esc_html__( 'Display Comments Count', 'spotim-comments' ),
            array( 'SpotIM_Form_Helper', 'radio_fields' ),
            $this->options->slug,
            'display_settings_section',
            array(
                'id' => "display_comments_count",
                'page' => $this->options->slug,
                'fields' => array(
                    '0' => esc_html__( 'Disable', 'spotim-comments' ),
                    'title' => esc_html__( 'Below title', 'spotim-comments' )
                ),
                'value' => $this->options->get( 'display_comments_count' )
            )
        );

        add_settings_field(
            'comments_per_page',
            esc_html__( 'Comments Per Page', 'spotim-comments' ),
            array( 'SpotIM_Form_Helper', 'number_field' ),
            $this->options->slug,
            'display_settings_section',
            array(
                'id' => 'comments_per_page',
                'description' => esc_html__( 'Default: 10', 'spotim-comments' ),
                'page' => $this->options->slug,
                'value' => $this->options->get( 'comments_per_page' ),
                'min' => 1,
                'max' => '999'
            )
        );

    }

    /**
     * Register Advanced Section
     *
     * @since 4.1.0
     *
     * @access public
     *
     * @return void
     */
    public function register_advanced_section() {

        add_settings_section(
            'advanced_settings_section',
            esc_html__( 'Advanced Options', 'spotim-comments' ),
            array( $this, 'advanced_settings_section_header' ),
            $this->options->slug
        );

        add_settings_field(
            'embed_method',
            esc_html__( 'Comments Embed Method', 'spotim-comments' ),
            array( 'SpotIM_Form_Helper', 'radio_fields' ),
            $this->options->slug,
            'advanced_settings_section',
            array(
                'id' => 'embed_method',
                'page' => $this->options->slug,
                'fields' => array(
                    'comments' => esc_html__( 'Replace WordPress Comments', 'spotim-comments' ),
                    'content' => esc_html__( 'Insert After the Content', 'spotim-comments' ),
                    'manual' => esc_html__( 'Let the theme decide', 'spotim-comments' ),
                ),
                'description' => esc_html__( "When choosing 'Let the theme decide', please inject the 'SpotIM_Frontend::display_comments()' code wherever comments should be displayed.", 'spotim-comments' ),
                'value' => $this->options->get( 'embed_method' )
            )
        );

        add_settings_field(
            'rc_embed_method',
            esc_html__( 'Recirculation Embed Method', 'spotim-comments' ),
            array( 'SpotIM_Form_Helper', 'radio_fields' ),
            $this->options->slug,
            'advanced_settings_section',
            array(
                'id' => 'rc_embed_method',
                'page' => $this->options->slug,
                'fields' => array(
                    'regular' => esc_html__( 'Regular', 'spotim-comments' ),
                    'top' => esc_html__( 'Inline - top', 'spotim-comments' ),
                    'bottom' => esc_html__( 'Inline - bottom', 'spotim-comments' ),
                    'none' => esc_html__( 'None', 'spotim-comments' ),
                ),
                'value' => $this->options->get( 'rc_embed_method' )
            )
        );

        add_settings_field(
            'display_priority',
            esc_html__( 'Display Priority', 'spotim-comments' ),
            array( 'SpotIM_Form_Helper', 'number_field' ),
            $this->options->slug,
            'advanced_settings_section',
            array(
                'id' => 'display_priority',
                'page' => $this->options->slug,
                'value' => $this->options->get( 'display_priority' ),
                'min' => '0',
                'max' => '10000'
            )
        );

        add_settings_field(
            'enable_seo',
            esc_html__( 'Enable SEO', 'spotim-comments' ),
            array( 'SpotIM_Form_Helper', 'radio_fields' ),
            $this->options->slug,
            'advanced_settings_section',
            array(
                'id' => 'enable_seo',
                'page' => $this->options->slug,
                'fields' => array(
                    'false' => esc_html__( 'Disable', 'spotim-comments' ),
                    'true' => esc_html__( 'Enable', 'spotim-comments' ),
                ),
                'value' => $this->options->get( 'enable_seo' )
            )
        );

        add_settings_field(
            'enable_og',
            esc_html__( 'OpenGraph Tags', 'spotim-comments' ),
            array( 'SpotIM_Form_Helper', 'radio_fields' ),
            $this->options->slug,
            'advanced_settings_section',
            array(
                'id' => 'enable_og',
                'page' => $this->options->slug,
                'fields' => array(
                    'false' => esc_html__( 'Disable', 'spotim-comments' ),
                    'true' => esc_html__( 'Enable', 'spotim-comments' ),
                ),
                'value' => $this->options->get( 'enable_og' )
            )
        );

        add_settings_field(
            'class',
            esc_html__( 'Container Class', 'spotim-comments' ),
            array( 'SpotIM_Form_Helper', 'text_field' ),
            $this->options->slug,
            'advanced_settings_section',
            array(
                'id' => 'class',
                'page' => $this->options->slug,
                'value' => $this->options->get( 'class' ),
            )
        );

        add_settings_field(
            'disqus_shortname',
            esc_html__( 'Disqus Shortname', 'spotim-comments' ),
            array( 'SpotIM_Form_Helper', 'text_field' ),
            $this->options->slug,
            'advanced_settings_section',
            array(
                'id' => 'disqus_shortname',
                'page' => $this->options->slug,
                'value' => $this->options->get( 'disqus_shortname' ),
            )
        );

        add_settings_field(
            'disqus_identifier',
            esc_html__( 'Disqus Identifier Structure', 'spotim-comments' ),
            array( 'SpotIM_Form_Helper', 'radio_fields' ),
            $this->options->slug,
            'advanced_settings_section',
            array(
                'id' => 'disqus_identifier',
                'page' => $this->options->slug,
                'fields' => array(
                    'id' => esc_html__( 'ID', 'spotim-comments' ),
                    'short_url' => esc_html__( 'Short URL', 'spotim-comments' ),
                    'id_short_url' => esc_html__( 'ID + Short URL (Default)', 'spotim-comments' ),
                ),
                'value' => $this->options->get( 'disqus_identifier' )
            )
        );

        add_settings_field(
            'import_button',
            esc_html__( 'Start Manual Sync', 'spotim-comments' ),
            array( 'SpotIM_Form_Helper', 'import_button' ),
            $this->options->slug,
            'advanced_settings_section',
            array(
                'import_button' => array(
                    'id' => 'import_button',
                    'text' => esc_html__( 'Sync Now', 'spotim-comments' )
                ),
                'force_import_button' => array(
                    'id' => 'force_import_button',
                    'text' => esc_html__( 'Reset + Sync Now', 'spotim-comments' ),
                    'description' => esc_html__( 'Use Sync Now to sync data starting from the last sync time.', 'spotim-comments' )."<br />".esc_html__('Use Reset + Sync Now to clear any old synced data and start a fresh sync.', 'spotim-comments')
                ),
                'cancel_import_link' => array(
                    'id' => 'cancel_import_link',
                    'text' => esc_html__( 'Cancel', 'spotim-comments' )
                )
            )
        );

    }

    /**
     * Register Import Section
     *
     * @since 2.0.0
     *
     * @access public
     *
     * @return void
     */
    public function register_import_section() {

        add_settings_section(
            'import_settings_section',
            esc_html__( 'Comments Sync Options', 'spotim-comments' ),
            array( $this, 'import_settings_section_header' ),
            $this->options->slug
        );

        add_settings_field(
            'import_token',
            esc_html__( 'Sync Token', 'spotim-comments' ),
            array( 'SpotIM_Form_Helper', 'text_field' ),
            $this->options->slug,
            'import_settings_section',
            array(
                'id' => 'import_token',
                'page' => $this->options->slug,
                'description' => esc_html__( 'Contact your Spot.IM account manager to get your sync token.', 'spotim-comments' ),
                'value' => $this->options->get( 'import_token' )
            )
        );

        $spot_id = $this->options->get( 'spot_id' );
        $import_token = $this->options->get( 'import_token' );
        $schedule_fields['0'] = esc_html__( 'No', 'spotim-comments' );
        $registered_schedules = wp_get_schedules();
        if ( ! empty( $registered_schedules ) ) {
            foreach ( $registered_schedules as $key => $value ) {
                $schedule_fields[ $key ] = $value['display'];
            }
        }

        add_settings_field(
            'auto_import',
            esc_html__( 'Enable Auto Sync', 'spotim-comments' ),
            array( 'SpotIM_Form_Helper', 'radio_fields' ),
            $this->options->slug,
            'import_settings_section',
            array(
                'id' => 'auto_import',
                'page' => $this->options->slug,
                'description' => esc_html__( 'Enable auto-sync and set how often should it reoccur.', 'spotim-comments' )
                    . '<br>'
                    . $this->options->get_next_cron_execution( wp_next_scheduled( 'spotim_scheduled_import' ) )
                    . ( empty( $spot_id ) ? ' ' . esc_html__( 'Spot ID is missing.', 'spotim-comments' ) : '' )
                    . ( empty( $import_token ) ? ' ' . esc_html__( 'Import token is missing.', 'spotim-comments' ) : '' ),
                'fields' => $schedule_fields,
                'value' => $this->options->get( 'auto_import' )
            )
        );

        // If import is running don't allow the user to update "Posts Per Request"
        $other_attr = '';
        if ( $this->options->get( 'page_number' ) > 0 ) {
            $other_attr = 'readonly';
        }

        add_settings_field(
            'posts_per_request',
            esc_html__( 'Posts Per Request', 'spotim-comments' ),
            array( 'SpotIM_Form_Helper', 'number_field' ),
            $this->options->slug,
            'import_settings_section',
            array(
                'id' => 'posts_per_request',
                'page' => $this->options->slug,
                'description' => esc_html__( 'On every sync, several requests will be made to your server. This is the amount of posts that will be retrieved in each request. Default: 10.', 'spotim-comments' ),
                'value' => $this->options->get( 'posts_per_request' ),
                'min' => '0',
                'max' => '100',
                'other' => $other_attr
            )
        );

        // hidden spot id for the import js
        add_settings_field(
            'spot_id',
            null,
            array( 'SpotIM_Form_Helper', 'hidden_field' ),
            $this->options->slug,
            'import_settings_section',
            array(
                'id' => 'spot_id',
                'page' => $this->options->slug,
                'value' => $spot_id
            )
        );

    }

}
