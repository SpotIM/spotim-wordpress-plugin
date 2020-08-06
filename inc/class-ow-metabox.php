<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * OW_Meta_Box
 *
 * Plugin meta box displayed in posts and pages.
 *
 * @since 4.0.0
 * @since 5.0.0 Renamed from 'SpotIM_Meta_Box' to 'OW_Meta_Box'.
 */
class OW_Meta_Box {

    /**
     * Options
     *
     * @since  4.0.0
     *
     * @access private
     * @static
     *
     * @var OW_Options
     */
    private static $options;

    /**
     * Constructor
     *
     * Get things started.
     *
     * @since  4.0.0
     *
     * @access public
     *
     * @param OW_Options $options Plugin options.
     */
    public function __construct( $options ) {

        if ( is_admin() ) {
            self::$options = $options;
            add_action( 'load-post.php', array( $this, 'init_metabox' ) );
            add_action( 'load-post-new.php', array( $this, 'init_metabox' ) );
        }

    }

    /**
     * Init Meta Box
     *
     * @since  4.0.0
     *
     * @access public
     *
     * @return void
     */
    public function init_metabox() {

        add_action( 'add_meta_boxes', array( $this, 'add_metabox' ) );
        add_action( 'save_post', array( $this, 'save_metabox' ), 10, 2 );

    }

    /**
     * Add Meta Box
     *
     * @since  4.0.0
     *
     * @access public
     *
     * @return void
     */
    public function add_metabox() {

        // Default screen where the boxes should be display in
        $screen = array();

        // Available post types
        $post_types = get_post_types( array( 'public' => true ) );

        // Apply metabox only to selected post types
        foreach ( $post_types as $post_type ) {

            // only for post types where Spot.IM is enabled
            if ( '0' !== self::$options->get( "display_{$post_type}" ) ) {
                $screen[] = $post_type;
            }

        }

        // Bail if no post types selected
        if ( empty( $screen ) ) {
            return;
        }

        // Add metaboxes to selected post types
        add_meta_box(
            'openweb',
            esc_html__( 'OpenWeb.Com', 'spotim-comments' ),
            array( $this, 'render_metabox' ),
            $screen,
            'advanced',
            'default'
        );

    }

    /**
     * Render Meta Box
     *
     * @since  4.0.0
     *
     * @access public
     *
     * @return void
     */
    public function render_metabox( $post ) {

        // Add nonce for security and authentication.
        wp_nonce_field( 'nonce_action', 'nonce' );

        // Retrieve an existing value from the database.
        $ow_display_comments      = ow_get_post_meta( $post->ID, 'ow_display_comments', true );
        $ow_display_question      = ow_get_post_meta( $post->ID, 'ow_display_question', true );
        $ow_display_recirculation = ow_get_post_meta( $post->ID, 'ow_display_recirculation', true );

        // Set default values.
        if ( empty( $ow_display_comments ) ) {
            $ow_display_comments = 'enable';
        }

        // Set default values.
        if ( empty( $ow_display_question ) ) {
            $ow_display_question = '';
        }

        // Set default values.
        if ( empty( $ow_display_recirculation ) ) {
            $ow_display_recirculation = 'enable';
        }

        // Form fields.
        echo '<table class="form-table">';

        echo '    <tr>';
        echo '		<th><label for="ow_display_comments" class="ow_display_comments_label">' . esc_html__( 'Comments', 'spotim-comments' ) . '</label></th>';
        echo '		<td>';
        echo '			<select id="ow_display_comments" name="ow_display_comments" class="ow_display_comments_field">';
        echo '			<option value="enable" ' . selected( $ow_display_comments, 'enable', false ) . '> ' . esc_html__( 'Enable', 'spotim-comments' ) . '</option>';
        echo '			<option value="disable" ' . selected( $ow_display_comments, 'disable', false ) . '> ' . esc_html__( 'Disable', 'spotim-comments' ) . '</option>';
        echo '			</select>';
        echo '			<p class="description">' . esc_html__( 'Show OpenWeb.Com comments.', 'spotim-comments' ) . '</p>';
        echo '		</td>';
        echo '	</tr>';

        echo '	<tr>';
        echo '		<th><label for="ow_display_question" class="ow_display_question_label">' . esc_html__( 'Community Question', 'spotim-comments' ) . '</label></th>';
        echo '		<td>';
        echo '			<input type="text" id="ow_display_question" name="ow_display_question" class="ow_display_question_field" value="' . esc_attr( $ow_display_question ) . '">';
        echo '			<p class="description">' . esc_html__( 'Show OpenWeb.Com community question.', 'spotim-comments' ) . '</p>';
        echo '		</td>';
        echo '	</tr>';

        // Check if recirculation is disabled globally
        if ( 'none' === self::$options->get( 'rc_embed_method' ) ) {
            echo '<tr style="display: none;" />';
        } else {
            echo '<tr>';
        }
        echo '		<th><label for="ow_display_recirculation" class="ow_display_recirculation_label">' . esc_html__( 'Recirculation', 'spotim-comments' ) . '</label></th>';
        echo '		<td>';
        echo '			<select id="ow_display_recirculation" name="ow_display_recirculation" class="ow_display_recirculation_field">';
        echo '			<option value="enable" ' . selected( $ow_display_recirculation, 'enable', false ) . '> ' . esc_html__( 'Enable', 'spotim-comments' ) . '</option>';
        echo '			<option value="disable" ' . selected( $ow_display_recirculation, 'disable', false ) . '> ' . esc_html__( 'Disable', 'spotim-comments' ) . '</option>';
        echo '			</select>';
        echo '			<p class="description">' . esc_html__( 'Show OpenWeb.Com recirculation.', 'spotim-comments' ) . '</p>';
        echo '		</td>';
        echo '	</tr>';

        echo '</table>';

    }

    /**
     * Save Meta Box
     *
     * @since  4.0.0
     *
     * @access public
     *
     * @return void
     */
    public function save_metabox( $post_id, $post ) {

        $nonce = filter_input( INPUT_POST, 'nonce', FILTER_SANITIZE_STRING );

        // Check if a nonce is set and is valid.
        if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'nonce_action' ) ) {
            return;
        }

        // Check if the user has permissions to save data.
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Check if it's not an autosave.
        if ( wp_is_post_autosave( $post_id ) ) {
            return;
        }

        // Check if it's not a revision.
        if ( wp_is_post_revision( $post_id ) ) {
            return;
        }

        // Sanitize user input.
        $display_comments_value      = filter_input( INPUT_POST, 'ow_display_comments', FILTER_SANITIZE_STRING );
        $display_question_value      = filter_input( INPUT_POST, 'ow_display_question', FILTER_SANITIZE_STRING );
        $display_recirculation_value = filter_input( INPUT_POST, 'ow_display_recirculation', FILTER_SANITIZE_STRING );

        $new_ow_display_comments      = ( ! empty( $display_comments_value ) ) ? $display_comments_value : '';
        $new_ow_display_question      = ( ! empty( $display_question_value ) ) ? $display_question_value : '';
        $new_ow_display_recirculation = ( ! empty( $display_recirculation_value ) ) ? $display_recirculation_value : '';

        // Update the meta field in the database.
        update_post_meta( $post_id, 'ow_display_comments', $new_ow_display_comments );
        update_post_meta( $post_id, 'ow_display_question', $new_ow_display_question );
        update_post_meta( $post_id, 'ow_display_recirculation', $new_ow_display_recirculation );

    }

}
