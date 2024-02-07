<?php
/**
 * VC Init Class.
 *
 * @package RT_TSS
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

if ( ! class_exists( 'TSSVCInit' ) ) :
	/**
	 * VC Init Class.
	 */
	class TSSVCInit {
		/**
		 * Class Constructor
		 */
		public function __construct() {
			if ( ! defined( 'WPB_VC_VERSION' ) ) {
				return;
			}

			add_action( 'vc_before_init', [ $this, 'tssIntegrationVc' ] );
		}

		/**
		 * SC List
		 *
		 * @return void
		 */
		function scListA() {
			$sc            = [];
			$scQ           = get_posts(
				[
					'post_type'      => 'tss-sc',
					'order_by'       => 'title',
					'order'          => 'DESC',
					'post_status'    => 'publish',
					'posts_per_page' => -1,
				]
			);
			$sc['Default'] = '';

			if ( count( $scQ ) ) {
				foreach ( $scQ as $post ) {
					$sc[ $post->post_title ] = $post->ID;
				}
			}

			return $sc;
		}

		/**
		 * VC inegration
		 *
		 * @return void
		 */
		public function tssIntegrationVc() {
			vc_map(
				[
					'name'              => esc_html__( 'Testimonial Pro', 'testimonial-slider-showcase' ),
					'base'              => 'rt-testimonial',
					'class'             => '',
					'icon'              => esc_url( TSSPro()->assetsUrl ) . 'images/icon-32x32.png',
					'controls'          => 'full',
					'category'          => 'Content',
					'admin_enqueue_js'  => '',
					'admin_enqueue_css' => '',
					'params'            => [
						[
							'type'        => 'dropdown',
							'heading'     => esc_html__( 'Short Code', 'testimonial-slider-showcase' ),
							'param_name'  => 'id',
							'value'       => $this->scListA(),
							'admin_label' => true,
							'description' => esc_html__( 'Short Code list', 'testimonial-slider-showcase' ),
						],
					],
				]
			);
		}
	}
endif;
