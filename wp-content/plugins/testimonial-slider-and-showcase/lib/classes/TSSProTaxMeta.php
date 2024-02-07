<?php
/**
 * Tax Meta Class.
 *
 * @package RT_TSS
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

if ( ! class_exists( 'TSSProTaxMeta' ) ) :
	/**
	 * Tax Meta Class.
	 */
	class TSSProTaxMeta {
		/**
		 * Class constructor
		 */
		public function __construct() {
			add_filter( 'manage_edit-testimonial-category_columns', [ $this, 'tss_taxonomy_columns' ] );
			add_filter( 'manage_testimonial-category_custom_column', [ $this, 'tss_taxonomy_column' ], 10, 3 );
			add_filter( 'manage_edit-testimonial-tag_columns', [ $this, 'tss_taxonomy_columns' ] );
			add_filter( 'manage_testimonial-tag_custom_column', [ $this, 'tss_taxonomy_column' ], 10, 3 );
			add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
			add_action( 'wp_ajax_tss-get-term-list', [ $this, 'ajax_get_term_list_taxonomy_slug' ] );
			add_action( 'wp_ajax_tss-update-temp-order', [ $this, 'ajax_update_term_order' ] );
			add_action( 'created_term', [ $this, 'save_taxonomy_fields' ], 10, 3 );
			add_action( 'edit_term', [ $this, 'save_taxonomy_fields' ], 10, 3 );
			add_action( 'admin_init', [ $this, 'save_taxonomy_fields' ], 10, 3 );
		}

		/**
		 * Save fields
		 *
		 * @param int    $term_id ID.
		 * @param string $tt_id ID.
		 * @param string $taxonomy Taxonomy.
		 * @return void
		 */
		public function save_taxonomy_fields( $term_id, $tt_id = '', $taxonomy = '' ) {
			if ( in_array( $taxonomy, TSSPro()->taxonomies ) ) {
				if ( ! TSSPro()->meta_exist( $term_id, '_order', 'term' ) ) {
					update_term_meta( $term_id, '_order', 0 );
				}
			}
		}

		/**
		 * Update order
		 *
		 * @return void
		 */
		public function ajax_update_term_order() {
			$html  = $msg = null;
			$error = true;

			if ( TSSPro()->verifyNonce() ) {
				$terms = ( ! empty( $_REQUEST['terms'] ) ? explode( ',', sanitize_text_field( $_POST['terms'] ) ) : [] );

				if ( $terms && ! empty( $terms ) ) {
					$error = false;

					foreach ( $terms as $key => $term_id ) {
						update_term_meta( $term_id, '_order', $key + 1 );
					}
				} else {
					$msg .= '<p>' . esc_html__( 'No term in list', 'testimonial-slider-showcase' ) . '</p>';
				}
			} else {
				$msg .= '<p>' . esc_html__( 'Security error', 'testimonial-slider-showcase' ) . '</p>';
			}

			wp_send_json(
				[
					'data'  => $html,
					'error' => $error,
					'msg'   => $msg,
				]
			);

			die();
		}

		/**
		 * Get term list
		 *
		 * @return void
		 */
		public function ajax_get_term_list_taxonomy_slug() {
			$html  = $msg = null;
			$error = true;
			if ( TSSPro()->verifyNonce() ) {
				$tax = ( ! empty( $_REQUEST['tax'] ) ? esc_attr( $_REQUEST['tax'] ) : null );

				if ( $tax ) {
					$error = false;
					$terms = get_terms(
						$tax,
						[
							'orderby'    => 'meta_value_num',
							'meta_key'   => '_order',
							'order'      => 'ASC',
							'hide_empty' => false,
						]
					);

					if ( ! empty( $terms ) ) {
						$html .= "<ul id='order-target' data-taxonomy='{$tax}'>";

						foreach ( $terms as $term ) {
							$html .= "<li data-id='{$term->term_id}'><span>{$term->name}</span></li>";
						}

						$html .= '</ul>';
					} else {
						$html .= '<p>' . esc_html__( 'No term found', 'testimonial-slider-showcase' ) . '</p>';
					}
				} else {
					$html .= '<p>' . esc_html__( 'Select a taxonomy', 'testimonial-slider-showcase' ) . '</p>';
				}
			} else {
				$html .= '<p>' . esc_html__( 'Security error', 'testimonial-slider-showcase' ) . '</p>';
			}

			wp_send_json(
				[
					'data'  => $html,
					'error' => $error,
					'msg'   => $msg,
				]
			);
			die();
		}

		/**
		 * Admin enqueue
		 *
		 * @return void
		 */
		public function admin_enqueue_scripts() {
			global $pagenow, $typenow;

			// validate page.
			if ( ! in_array( $pagenow, [ 'edit.php' ] ) && ! empty( $_REQUEST['page'] ) && $_REQUEST['page'] != 'tss_taxonomy_order' ) {
				return;
			}

			if ( $typenow != TSSPro()->post_type ) {
				return;
			}

			wp_enqueue_script(
				[
					'jquery',
					'jquery-ui-core',
					'jquery-ui-sortable',
					'tss-select2',
					'tss-admin-taxonomy',
				]
			);
			wp_enqueue_style(
				[
					'tss-select2',
					'tss-admin',
				]
			);

			wp_localize_script(
				'tss-admin-taxonomy',
				'tss',
				[
					'nonceId' => esc_attr( TSSPro()->nonceId() ),
					'nonce'   => esc_attr( wp_create_nonce( TSSPro()->nonceText() ) ),
					'ajaxurl' => esc_url( admin_url( 'admin-ajax.php' ) ),
				]
			);
		}

		/**
		 * Tax columns
		 *
		 * @param array $columns Columns.
		 * @return array
		 */
		public function tss_taxonomy_columns( $columns ) {
			$new_columns = [];

			if ( isset( $columns['cb'] ) ) {
				$new_columns['cb'] = $columns['cb'];
				unset( $columns['cb'] );
			}

			$new_columns_order['order'] = esc_html__( 'Order', 'testimonial-slider-showcase' );

			return array_merge( $new_columns, $columns, $new_columns_order );
		}

		/**
		 * Tax column.
		 *
		 * @param string $columns Columns.
		 * @param string $column Column.
		 * @param int    $id ID.
		 * @return string
		 */
		public function tss_taxonomy_column( $columns, $column, $id ) {
			if ( 'order' == $column ) {
				$columns .= get_term_meta( $id, '_order', true );
			}

			return $columns;
		}

	}

endif;
