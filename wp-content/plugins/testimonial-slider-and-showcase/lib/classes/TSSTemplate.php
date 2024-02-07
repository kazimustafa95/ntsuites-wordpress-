<?php
/**
 * Template Class.
 *
 * @package RT_TSS
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

if ( ! class_exists( 'TSSTemplate' ) ) :
	/**
	 * Template Class.
	 */
	class TSSTemplate {
		/**
		 * Class constructor
		 */
		public function __construct() {
			add_action( 'wp_enqueue_scripts', [ $this, 'loadTemplateScript' ] );
		}

		/**
		 * Scripts.
		 *
		 * @return void
		 */
		public function loadTemplateScript() {
			if ( get_post_type() == TSSPro()->post_type || is_post_type_archive( TSSPro()->post_type ) ) {
				wp_enqueue_style(
					[
						'tss-fontello',
						'tlp-owl-carousel-css',
						'tlp-owl-carousel-theme-css',
						'rt-tpg-css',
					]
				);
				wp_enqueue_script(
					[
						'jquery',
						'tlp-bootstrap-js',
						'tlp-image-load-js',
						'tlp-owl-carousel-js',
						'tlp-team-js',
					]
				);
			}
		}
	}
endif;
