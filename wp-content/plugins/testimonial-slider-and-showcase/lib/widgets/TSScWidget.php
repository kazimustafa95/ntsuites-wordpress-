<?php
/**
 * Widget Class.
 *
 * @package RT_TSS
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

if ( ! class_exists( 'TSScWidget' ) ) :
	/**
	 * Widget Class.
	 */
	class TSScWidget extends WP_Widget {
		/**
		 * TLP TEAM widget setup
		 */
		public function __construct() {
			$widget_ops = [
				'classname'   => 'widget_rt_tss_owl_carousel',
				'description' => esc_html__( 'Display Testimonial', 'testimonial-slider-showcase' ),
			];
			parent::__construct(
				'widget_tlp_port_owl_carousel',
				esc_html__( 'Testimonial Slider And Showcase', 'testimonial-slider-showcase' ),
				$widget_ops
			);
		}

		/**
		 * Frontend render.
		 *
		 * @param array $args Args.
		 * @param array $instance Instance.
		 * @return void
		 */
		public function widget( $args, $instance ) {
			extract( $args );

			$id = ( ! empty( $instance['id'] ) ? $instance['id'] : null );

			echo $before_widget; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

			if ( ! empty( $instance['title'] ) ) {
				echo $args['before_title'] . apply_filters( 'widget_title', ( isset( $instance['title'] ) ? esc_html( $instance['title'] ) : 'Testimonials' ) ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}

			if ( ! empty( $id ) ) {
				echo do_shortcode( '[rt-testimonial id="' . absint( $id ) . '" ]' );
			}

			echo $after_widget; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		/**
		 * Form
		 *
		 * @param array $instance Instance.
		 * @return void
		 */
		public function form( $instance ) {

			$scList   = TSSPro()->get_shortCode_list();
			$defaults = [
				'title' => 'Testimonials',
				'id'    => null,
			];
			$instance = wp_parse_args( (array) $instance, $defaults ); ?>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
					<?php
					esc_html_e( 'Title:', 'testimonial-slider-showcase' );
					?>
				</label>
				<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" style="width:100%;"/>
			</p>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'id' ) ); ?>">
					<?php
					esc_html_e( 'Select a shortcode', 'testimonial-slider-showcase' );
					?>
				</label>
				<select id="<?php echo esc_attr( $this->get_field_id( 'id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'id' ) ); ?>">
					<option value="">Select one</option>
					<?php
					if ( ! empty( $scList ) ) {
						foreach ( $scList as $scId => $sc ) {
							$selected = ( $scId == $instance['id'] ? 'selected' : null );
							echo '<option value="' . absint( $scId ) . '" ' . esc_attr( $selected ) . '>' . esc_html( $sc ) . '</option>';
						}
					}
					?>
				</select></p>
			<?php
		}

		/**
		 * Update
		 *
		 * @param array $new_instance New Instance.
		 * @param array $old_instance Old Instance.
		 * @return array
		 */
		public function update( $new_instance, $old_instance ) {
			$instance          = [];
			$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
			$instance['id']    = ( ! empty( $new_instance['id'] ) ) ? absint( $new_instance['id'] ) : '';

			return $instance;
		}
	}
endif;
