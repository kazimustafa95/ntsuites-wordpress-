<?php
/**
 * Ajax response class.
 *
 * @package RT_TSS
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

if ( ! class_exists( 'TSSAjaxResponse' ) ) :
	/**
	 * Ajax response class.
	 */
	class TSSAjaxResponse {
		/**
		 * Class constructor.
		 */
		public function __construct() {
			add_action( 'wp_ajax_shortCodeList', [ $this, 'shortCodeList' ] );
		}

		/**
		 * Shortcode List.
		 */
		public function shortCodeList() {
			$html = null;
			$scQ  = new WP_Query(
				[
					'post_type'      => [ TSSPro()->shortCodePT ],
					'order_by'       => 'title',
					'order'          => 'DESC',
					'post_status'    => 'publish',
					'posts_per_page' => -1,
				]
			);

			if ( $scQ->have_posts() ) {
				$html .= "<div class='mce-tss-container mce-form'>";
				$html .= "<div class='mce-tss-container-body'>";
				$html .= '<label class="mce-widget mce-label" style="padding: 20px;font-weight: bold;" for="scid">Select Short code</label>';
				$html .= "<select name='id' id='scid' style='width: 150px;margin: 15px;'>";
				$html .= "<option value=''>Default</option>";
				while ( $scQ->have_posts() ) {
					$scQ->the_post();
					$html .= "<option value='" . get_the_ID() . "'>" . get_the_title() . '</option>';
				}
				$html .= '</select>';
				$html .= '</div>';
				$html .= '</div>';
			} else {
				$html .= '<div>' . esc_html__( 'No shortcode found.', 'testimonial-slider-showcase' ) . '</div>';
			}

			TSSPro()->printHtml( $html, true );

			die();
		}
	}
endif;
