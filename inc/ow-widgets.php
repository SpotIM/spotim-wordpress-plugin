<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * OW_Recirculation_Widget
 *
 * Plugin Widget.
 *
 * @since 4.0.0
 * @since 5.0.0 Renamed from 'SpotIM_Recirculation_Widget' to 'OW_Recirculation_Widget'.
 */
class OW_Recirculation_Widget extends WP_Widget {

    /**
     * Constructor
     *
     * Get things started.
     *
     * @since  4.0.0
     *
     * @access public
     */
    public function __construct() {

        parent::__construct(
            'spotim_recirculation_widget',
            esc_html__( 'OpenWeb.Com Recirculation', 'spotim-comments' ),
            array(
                'description' => esc_html__( 'OpenWeb.Com related content.', 'spotim-comments' ),
                'classname'   => 'spotim_recirculation',
            )
        );

    }

    /**
     * Widget
     *
     * @since  4.0.0
     *
     * @access public
     *
     * @return string
     */
    public function widget( $args, $instance ) {

        $options = OW_Options::get_instance();

        // Ignoring warning as variable is used in included template.
        $ow_id = $options->get( 'ow_id' ); //phpcs:ignore WordPressVIPMinimum.Variables.VariableAnalysis.UnusedVariable
        $title = apply_filters( 'widget_title', empty( $instance['spotim_title'] ) ? '' : $instance['spotim_title'], $instance, $this->id_base );

        // Before widget tag.
        echo wp_kses_post( $args['before_widget'] );

        // Title.
        if ( ! empty( $title ) ) {
            echo wp_kses_post( $args['before_title'] . $title . $args['after_title'] );
        }

        // Recirculation.
        include( plugin_dir_path( dirname( __FILE__ ) ) . 'templates/recirculation-template.php' );

        // After widget tag.
        echo wp_kses_post( $args['after_widget'] );

    }

    /**
     * Form
     *
     * @since  4.0.0
     *
     * @access public
     *
     * @return void
     */
    public function form( $instance ) {

        // Set default values.
        $instance = wp_parse_args( (array) $instance, array(
            'spotim_title' => '',
        ) );

        // Retrieve an existing value from the database.
        $spotim_title = ! empty( $instance['spotim_title'] ) ? $instance['spotim_title'] : '';

        // Form fields.
        echo '<p>';
        echo '	<label for="' . esc_attr( $this->get_field_id( 'spotim_title' ) ) . '" class="spotim_title_label">' . esc_html__( 'Title', 'spotim-comments' ) . '</label>';
        echo '	<input type="text" id="' . esc_attr( $this->get_field_id( 'spotim_title' ) ) . '" name="' . esc_attr( $this->get_field_name( 'spotim_title' ) ) . '" class="widefat" value="' . esc_attr( $spotim_title ) . '">';
        echo '</p>';

    }

    /**
     * Update
     *
     * @since  4.0.0
     *
     * @access public
     *
     * @return instance
     */
    public function update( $new_instance, $old_instance ) {

        $instance = $old_instance;

        $instance['spotim_title'] = ( ! empty( $new_instance['spotim_title'] ) ) ? wp_strip_all_tags( $new_instance['spotim_title'] ) : '';

        return $instance;

    }

}


/**
 * OW_Siderail_Widget
 *
 * Plugin Widget.
 *
 * @since 4.2.0
 * @since 5.0.0 Renamed from 'SpotIM_Siderail_Widget' to 'OW_Siderail_Widget'.
 */
class OW_Siderail_Widget extends WP_Widget {

    /**
     * Constructor
     *
     * Get things started.
     *
     * @since  4.2.0
     *
     * @access public
     */
    public function __construct() {

        parent::__construct(
            'spotim_siderail_widget',
            __( 'OpenWeb.Com Siderail', 'spotim-comments' ),
            array(
                'description' => __( 'OpenWeb.Com related content.', 'spotim-comments' ),
                'classname'   => 'spotim_siderail',
            )
        );

    }

    /**
     * Widget
     *
     * @since  4.2.0
     *
     * @access public
     *
     * @return string
     */
    public function widget( $args, $instance ) {

        $options = OW_Options::get_instance();

        // Ignoring warning as variable is used in included template.
        $ow_id = $options->get( 'ow_id' ); // phpcs:ignore WordPressVIPMinimum.Variables.VariableAnalysis.UnusedVariable
        $title = apply_filters( 'widget_title', empty( $instance['spotim_title'] ) ? '' : $instance['spotim_title'], $instance, $this->id_base );

        // Before widget tag.
        echo wp_kses_post( $args['before_widget'] );

        // Title.
        if ( ! empty( $title ) ) {
            echo wp_kses_post( $args['before_title'] . $title . $args['after_title'] );
        }

        // Siderail.
        include( plugin_dir_path( dirname( __FILE__ ) ) . 'templates/siderail-template.php' );

        // After widget tag.
        echo wp_kses_post( $args['after_widget'] );

    }

    /**
     * Form
     *
     * @since  4.2.0
     *
     * @access public
     *
     * @return void
     */
    public function form( $instance ) {

        // Set default values.
        $instance = wp_parse_args( (array) $instance, array(
            'spotim_title' => '',
        ) );

        // Retrieve an existing value from the database.
        $spotim_title = ! empty( $instance['spotim_title'] ) ? $instance['spotim_title'] : '';

        // Form fields.
        echo '<p>';
        echo '	<label for="' . esc_attr( $this->get_field_id( 'spotim_title' ) ) . '" class="spotim_title_label">' . esc_html__( 'Title', 'spotim-comments' ) . '</label>';
        echo '	<input type="text" id="' . esc_attr( $this->get_field_id( 'spotim_title' ) ) . '" name="' . esc_attr( $this->get_field_name( 'spotim_title' ) ) . '" class="widefat" value="' . esc_attr( $spotim_title ) . '">';
        echo '</p>';

    }

    /**
     * Update
     *
     * @since  4.2.0
     *
     * @access public
     *
     * @return instance
     */
    public function update( $new_instance, $old_instance ) {

        $instance = $old_instance;

        $instance['spotim_title'] = ( ! empty( $new_instance['spotim_title'] ) ) ? wp_strip_all_tags( $new_instance['spotim_title'] ) : '';

        return $instance;

    }

}


/**
 * Register OpenWeb Widgets
 *
 * Register recirculation and siderail widgets.
 *
 * @since 4.0.0
 * @since 4.2.0 Renamed from 'spotim_register_recirculation_widgets' to 'spotim_register_widgets'.
 * @since 5.0.0 Renamed from 'spotim_register_widgets' to 'ow_register_widgets'.
 */
function ow_register_widgets() {
    register_widget( 'OW_Recirculation_Widget' );
    register_widget( 'OW_Siderail_Widget' );
}
add_action( 'widgets_init', 'ow_register_widgets' );
