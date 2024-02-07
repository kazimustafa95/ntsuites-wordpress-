<?php
/**
 * Widget class.
 *
 * @package RT_TSS
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

if ( ! class_exists( 'TSSInitWidget' ) ) :
	/**
	 * Widget class.
	 */
	class TSSInitWidget {
		/**
		 * Class constructor
		 */
		public function __construct() {
			add_action( 'widgets_init', [ $this, 'initWidget' ] );
		}

		/**
		 * Init widget
		 *
		 * @return void
		 */
		public function initWidget() {
			TSSPro()->loadWidget( TSSPro()->widgetsPath );
		}
	}
endif;
