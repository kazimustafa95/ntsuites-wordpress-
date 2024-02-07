<?php
/**
 * Gutenberg class.
 *
 * @package RT_TSS
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

if ( ! class_exists( 'TSSGutenBurg' ) ) :
	/**
	 * Gutenberg class.
	 */
	class TSSGutenBurg {
		/**
		 * Version
		 *
		 * @var number
		 */
		protected $version;

		/**
		 * Class constructor
		 */
		public function __construct() {
			$this->version = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? time() : TSS_VERSION;

			add_action( 'enqueue_block_assets', [ $this, 'block_assets' ] );
			add_action( 'enqueue_block_editor_assets', [ $this, 'block_editor_assets' ] );

			if ( function_exists( 'register_block_type' ) ) {
				register_block_type(
					'radiustheme/tss',
					[
						'render_callback' => [ $this, 'render_shortcode' ],
					]
				);
			}
		}

		/**
		 * Render
		 *
		 * @param array $atts Attributes.
		 * @return string|void
		 */
		public static function render_shortcode( $atts ) {
			if ( empty( $atts['gridId'] ) ) {
				return;
			}

			return do_shortcode( '[rt-testimonial id="' . absint( $atts['gridId'] ) . '"]' );
		}

		/**
		 * Block assets
		 *
		 * @return void
		 */
		public function block_assets() {
			wp_enqueue_style( 'wp-blocks' );
		}

		/**
		 * Editor assets.
		 *
		 * @return void
		 */
		public function block_editor_assets() {
			// Scripts.
			wp_enqueue_script(
				'rt-tss-gb-block-js',
				esc_url( TSSPro()->assetsUrl ) . 'js/testimonial-slider-blocks.min.js',
				[ 'wp-blocks', 'wp-i18n', 'wp-element' ],
				$this->version,
				true
			);
			wp_localize_script(
				'rt-tss-gb-block-js',
				'tss',
				[
					'short_codes' => array_map( 'esc_html', TSSPro()->get_shortCode_list() ),
					'icon'        => esc_url( TSSPro()->assetsUrl ) . 'images/icon-32x32.png',
				]
			);
			wp_enqueue_style( 'wp-edit-blocks' );
		}
	}

endif;
