<?php
/**
 * Frontend Class.
 *
 * @package RT_TSS
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

if ( ! class_exists( 'TSSProFrontEnd' ) ) :
	/**
	 * Frontend Class.
	 */
	class TSSProFrontEnd {
		/**
		 * Class constructor
		 */
		public function __construct() {
			add_action( 'wp_enqueue_scripts', [ $this, 'tss_front_end' ] );
		}

		/**
		 * Enqueue
		 *
		 * @return void
		 */
		public function tss_front_end() {
			wp_enqueue_style( 'tss' );

			$settings = get_option( TSSPro()->options['settings'] );

			if ( ! empty( $settings['custom_css'] ) ) {
				wp_add_inline_style( 'tss', esc_html( $settings['custom_css'] ) );
			}
		}

	}
endif;
