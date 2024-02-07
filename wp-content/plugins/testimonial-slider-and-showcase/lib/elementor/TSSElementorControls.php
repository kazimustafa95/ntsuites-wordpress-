<?php
/**
 * Elementor Controls Class.
 *
 * @package RT_TSS
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

if ( ! class_exists( 'TSSElementorControls' ) ) :
	/**
	 * Elementor Controls Class.
	 */
	class TSSElementorControls {
		/**
		 * Controls
		 *
		 * @var object
		 */
		private static $controls;

		/**
		 * Register
		 *
		 * @param object $controls_manager Controls Manager.
		 * @return void
		 */
		public static function register( $controls_manager ) {
			self::includes();

			self::$controls = apply_filters(
				'rttss_elementor_custom_controls',
				[
					TSSImageSelectorControl::class,
				]
			);

			if ( empty( self::$controls ) ) {
				return;
			}

			// Registering the controls.
			self::registerControls( $controls_manager );
		}

		/**
		 * Register Controls
		 *
		 * @param object $controls_manager Controls Manager.
		 * @return void
		 */
		private static function registerControls( $controls_manager ) {
			foreach ( self::$controls as $control ) {
				$controls_manager->register( new $control() );
			}
		}

		/**
		 * Directory includes
		 *
		 * @return void
		 */
		private static function includes() {
			$directories = [
				'/elementor/controls/',
			];

			foreach ( $directories as $include ) {
				TSSPro()->loadClass( TSSPro()->incPath . $include );
			}
		}
	}

endif;
