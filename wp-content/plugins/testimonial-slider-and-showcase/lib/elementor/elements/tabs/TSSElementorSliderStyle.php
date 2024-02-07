<?php
/**
 * Elementor Slider Style Class.
 *
 * @package RT_TSS
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

if ( ! class_exists( 'TSSElementorSliderStyle' ) ) :
	/**
	 * Elementor Slider Style Class.
	 */
	class TSSElementorSliderStyle {

		/**
		 * Accumulates tab fields.
		 *
		 * @access private
		 * @static
		 *
		 * @var array
		 */
		private static $fields = [];

		/**
		 * Registering the tab contents.
		 *
		 * @access public
		 * @static
		 *
		 * @return array
		 */
		public static function register() {
			self::settings();

			return apply_filters( 'rttss_elementor_end_of_style_tab', self::$fields );
		}

		/**
		 * Method to add required fields.
		 *
		 * @access public
		 * @static
		 *
		 * @return void
		 */
		public static function settings() {
			$styleSections = new TSSElementorStyleSections();

			$sections = [
				'commonColors',
				'typography',
				'image',
			];

			foreach ( $sections as $section ) {
				self::$fields = array_merge( $styleSections->$section() );
			}
		}
	}
endif;
