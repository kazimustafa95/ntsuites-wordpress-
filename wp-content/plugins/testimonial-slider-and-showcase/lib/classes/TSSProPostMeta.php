<?php
/**
 * Post Meta Class.
 *
 * @package RT_TSS
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

if ( ! class_exists( 'TSSProPostMeta' ) ) :
	/**
	 * Post Meta Class.
	 */
	class TSSProPostMeta {
		/**
		 * Class construct
		 */
		public function __construct() {
			add_action( 'add_meta_boxes', [ $this, 'add_testimonial_meta_box' ] );
			add_action( 'save_post', [ $this, 'save_testimonial_meta_data' ], 10, 2 );
		}

		/**
		 * Meta box.
		 *
		 * @return void
		 */
		public function add_testimonial_meta_box() {
			add_meta_box( 'tss_meta_information', esc_html__( 'Testimonial\'s Information', 'testimonial-slider-showcase' ), [ $this, 'tss_meta_information' ], TSSPro()->post_type, 'normal', 'high' );
		}

		/**
		 * Meta fields
		 *
		 * @return void
		 */
		public function tss_meta_information() {
			wp_nonce_field( TSSPro()->nonceText(), TSSPro()->nonceId() );

			echo '<div class="tss-meta-wrapper">';
			TSSPro()->printHtml( TSSPro()->rtFieldGenerator( TSSPro()->singleTestimonialFields() ), true );
			echo '</div>';
		}

		/**
		 * Save meta box.
		 *
		 * @param int    $post_id ID.
		 * @param object $post Post object.
		 * @return void
		 */
		public function save_testimonial_meta_data( $post_id, $post ) {
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			if ( ! TSSPro()->verifyNonce() ) {
				return $post_id;
			}

			if ( TSSPro()->post_type != $post->post_type ) {
				return $post_id;
			}

			$mates = TSSPro()->tssTestimonialAllMetaFields();

			foreach ( $mates as $metaKey => $field ) {
				$rawValue       = isset( $_REQUEST[ $metaKey ] ) ? $_REQUEST[ $metaKey ] : null;
				$sanitizedValue = TSSPro()->sanitize( $field, $rawValue );

				if ( empty( $field['multiple'] ) ) {
					update_post_meta( $post_id, $metaKey, $sanitizedValue );
				} else {
					delete_post_meta( $post_id, $metaKey );

					if ( is_array( $sanitizedValue ) && ! empty( $sanitizedValue ) ) {
						foreach ( $sanitizedValue as $item ) {
							add_post_meta( $post_id, $metaKey, $item );
						}
					}
				}
			}
		}
	}
endif;
