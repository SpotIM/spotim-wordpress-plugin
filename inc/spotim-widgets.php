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
     * @since 4.0.0
     *
     * @access public
     */
	public function __construct() {

		parent::__construct(
			'spotim_recirculation_widget',
			esc_html__( 'SpotIM Recirculation', 'spotim-comments' ),
			array(
				'description' => esc_html__( 'SpotIM related content.', 'spotim-comments' ),
				'classname'   => 'spotim_recirculation',
			)
		);

	}

    /**
     * Widget
     *
     * @since 4.0.0
     *
     * @access public
     *
     * @return string
     */
	public function widget( $args, $instance ) {

		$options = SpotIM_Options::get_instance();
		$spot_id = $options->get( 'spot_id' );
		$title   = apply_filters( 'widget_title', empty( $instance['spotim_title'] ) ? '' : $instance['spotim_title'], $instance, $this->id_base );

		// Before widget tag
		echo $args['before_widget'];

		// Title
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		// Recirculation
		include( plugin_dir_path( dirname( __FILE__ ) ) . 'templates/recirculation-template.php' );

		// After widget tag
		echo $args['after_widget'];

	}

    /**
     * Form
     *
     * @since 4.0.0
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
		$spotim_title = !empty( $instance['spotim_title'] ) ? $instance['spotim_title'] : '';

		// Form fields
		echo '<p>';
		echo '	<label for="' . $this->get_field_id( 'spotim_title' ) . '" class="spotim_title_label">' . esc_html__( 'Title', 'spotim-comments' ) . '</label>';
		echo '	<input type="text" id="' . $this->get_field_id( 'spotim_title' ) . '" name="' . $this->get_field_name( 'spotim_title' ) . '" class="widefat" value="' . esc_attr( $spotim_title ) . '">';
		echo '</p>';

	}

    /**
     * Update
     *
     * @since 4.0.0
     *
     * @access public
     *
     * @return instance
     */
	public function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		$instance['spotim_title'] = !empty( $new_instance['spotim_title'] ) ? strip_tags( $new_instance['spotim_title'] ) : '';

		return $instance;

	}

}



/**
 * Register SpotIM Recirculation Widgets
 *
 * @since 4.0.0
 */
function spotim_register_recirculation_widgets() {
	register_widget( 'SpotIM_Recirculation_Widget' );
}
add_action( 'widgets_init', 'spotim_register_recirculation_widgets' );
