<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * SpotIM Recirculation Widget
 *
 * Plugin Widget.
 *
 * @since 4.0.0
 */
class SpotIM_Recirculation_Widget extends WP_Widget {

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
            esc_html__( 'Spot.IM Recirculation', 'ow' ),
            array(
                'description' => esc_html__( 'Spot.IM related content.', 'ow' ),
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

        // ignoring warning as variable is used in included template.
        $spot_id = $options->get( 'spot_id' ); //phpcs:ignore WordPressVIPMinimum.Variables.VariableAnalysis.UnusedVariable
        $title   = apply_filters( 'widget_title', empty( $instance['spotim_title'] ) ? '' : $instance['spotim_title'], $instance, $this->id_base );

        // Before widget tag
        echo wp_kses_post( $args['before_widget'] );

        // Title
        if ( ! empty( $title ) ) {
            echo wp_kses_post( $args['before_title'] . $title . $args['after_title'] );
        }

        // Recirculation
        include( plugin_dir_path( dirname( __FILE__ ) ) . 'templates/recirculation-template.php' );

        // After widget tag
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

        // Set default values
        $instance = wp_parse_args( (array) $instance, array(
            'spotim_title' => '',
        ) );

        // Retrieve an existing value from the database
        $spotim_title = ! empty( $instance['spotim_title'] ) ? $instance['spotim_title'] : '';

        // Form fields
        echo '<p>';
        echo '	<label for="' . esc_attr( $this->get_field_id( 'spotim_title' ) ) . '" class="spotim_title_label">' . esc_html__( 'Title', 'ow' ) . '</label>';
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
 * SpotIM Siderail Widget
 *
 * Plugin Widget.
 *
 * @since 4.2.0
 */
class SpotIM_Siderail_Widget extends WP_Widget {

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
            __( 'Spot.IM Siderail', 'ow' ),
            array(
                'description' => __( 'Spot.IM related content.', 'ow' ),
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

        // ignoring warning as variable is used in included template.
        $spot_id = $options->get( 'spot_id' ); // phpcs:ignore WordPressVIPMinimum.Variables.VariableAnalysis.UnusedVariable
        $title   = apply_filters( 'widget_title', empty( $instance['spotim_title'] ) ? '' : $instance['spotim_title'], $instance, $this->id_base );

        // Before widget tag
        echo wp_kses_post( $args['before_widget'] );

        // Title
        if ( ! empty( $title ) ) {
            echo wp_kses_post( $args['before_title'] . $title . $args['after_title'] );
        }

        // Siderail
        include( plugin_dir_path( dirname( __FILE__ ) ) . 'templates/siderail-template.php' );

        // After widget tag
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

        // Set default values
        $instance = wp_parse_args( (array) $instance, array(
            'spotim_title' => '',
        ) );

        // Retrieve an existing value from the database
        $spotim_title = ! empty( $instance['spotim_title'] ) ? $instance['spotim_title'] : '';

        // Form fields
        echo '<p>';
        echo '	<label for="' . esc_attr( $this->get_field_id( 'spotim_title' ) ) . '" class="spotim_title_label">' . esc_html__( 'Title', 'ow' ) . '</label>';
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
 * Register SpotIM Widgets
 *
 * Register recirculation and siderail widgets.
 *
 * @since 4.0.0
 * @since 4.2.0 Renamed from `spotim_register_recirculation_widgets()` to `spotim_register_widgets()`
 */
function spotim_register_widgets() {
    register_widget( 'SpotIM_Recirculation_Widget' );
    register_widget( 'SpotIM_Siderail_Widget' );
}

add_action( 'widgets_init', 'spotim_register_widgets' );
