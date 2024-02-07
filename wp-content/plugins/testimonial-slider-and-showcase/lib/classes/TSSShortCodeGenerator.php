<?php
/**
 * SC Generator Class.
 *
 * @package RT_TSS
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

if ( ! class_exists( 'TSSShortCodeGenerator' ) ) :
	/**
	 * SC Generator Class.
	 */
	class TSSShortCodeGenerator {
		/**
		 * Tag
		 *
		 * @var string
		 */
		public $shortcode_tag = 'rt_tss';

		/**
		 * Class constructor
		 */
		public function __construct() {
			if ( is_admin() ) {
				add_action( 'admin_head', [ $this, 'admin_head' ] );

				if ( ( isset( $_GET['post'] ) && 'tss-sc' === get_post_type( sanitize_text_field( wp_unslash( $_GET['post'] ) ) ) ) ||
				( isset( $_GET['post_type'] ) && 'tss-sc' === sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) ) ||
				( isset( $_GET['post'] ) && 'testimonial' === get_post_type( sanitize_text_field( wp_unslash( $_GET['post'] ) ) ) ) ||
				( isset( $_GET['post_type'] ) && 'testimonial' === sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) ) ) {
					add_action( 'admin_footer', [ $this, 'pro_alert_html' ] );
				}
			}
		}

		/**
		 * Marketing.
		 *
		 * @return void
		 */
		public function pro_alert_html() {
			if ( function_exists( 'rttsp' ) ) {
				return;
			}

			$html    = '';
			$proLink = 'https://www.radiustheme.com/downloads/wp-testimonial-slider-showcase-pro-wordpress/';

			$html .= '<div class="rtts-document-box rtts-alert rtts-pro-alert">
						<div class="rtts-box-icon"><i class="dashicons dashicons-lock"></i></div>
						<div class="rtts-box-content">
							<h3 class="rtts-box-title">' . esc_html__( 'Pro field alert!', 'testimonial-slider-showcase' ) . '</h3>
							<p><span></span>' . esc_html__( 'Sorry! this is a pro field. To use this field, you need to use pro plugin.', 'testimonial-slider-showcase' ) . '</p>
							<a href="' . esc_url( $proLink ) . '" target="_blank" class="rt-admin-btn">' . esc_html__( 'Upgrade to pro', 'testimonial-slider-showcase' ) . '</a>
							<a href="#" target="_blank" class="rtts-alert-close rtts-pro-alert-close">x</a>
						</div>
					</div>';

			TSSPro()->printHtml( $html );
		}

		/**
		 * Calls functions into the correct filters
		 *
		 * @return void
		 */
		public function admin_head() {
			if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
				return;
			}

			if ( 'true' == get_user_option( 'rich_editing' ) ) {
				add_filter( 'mce_external_plugins', [ $this, 'mce_external_plugins' ] );
				add_filter( 'mce_buttons', [ $this, 'mce_buttons' ] );
				echo '<style>';
				echo 'i.mce-i-rt_tss{';
				echo "background: url('" . esc_url( TSSPro()->assetsUrl ) . "images/icon-20x20.png');";
				echo '}';
				echo 'i.tss-vc-icon{';
				echo "background: url('" . esc_url( TSSPro()->assetsUrl ) . "images/icon-32x32.png');";
				echo '}';
				echo '</style>';
			}
		}
		/**
		 * Adds tinymce plugin
		 *
		 * @param  array $plugin_array Plugin.
		 * @return array
		 */
		public function mce_external_plugins( $plugin_array ) {
			$plugin_array[ $this->shortcode_tag ] = esc_url( TSSPro()->assetsUrl ) . 'js/mce-button.js';

			return $plugin_array;
		}

		/**
		 * Adds tinymce button
		 *
		 * @param  array $buttons Buttons.
		 * @return array
		 */
		public function mce_buttons( $buttons ) {
			array_push( $buttons, $this->shortcode_tag );

			return $buttons;
		}
	}
endif;
