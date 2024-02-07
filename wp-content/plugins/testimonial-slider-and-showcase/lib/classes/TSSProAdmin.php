<?php
/**
 * Admin Class.
 *
 * @package RT_TSS
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

if ( ! class_exists( 'TSSProAdmin' ) ) :
	/**
	 * Admin Class.
	 */
	class TSSProAdmin {
		/**
		 * Class constructor
		 */
		public function __construct() {
			add_filter( 'manage_edit-testimonial_columns', [ $this, 'arrange_testimonial_columns' ] );
			add_action( 'manage_testimonial_posts_custom_column', [ $this, 'manage_testimonial_columns' ], 10, 2 );
			add_action( 'restrict_manage_posts', [ $this, 'add_taxonomy_filters' ] );
			add_filter( 'manage_edit-tss-sc_columns', [ $this, 'arrange_rt_sc_code_selection_columns' ] );
			add_action( 'manage_tss-sc_posts_custom_column', [ $this, 'manage_rt_sc_code_selection_columns' ], 10, 2 );
		}

		/**
		 * Admin columns
		 *
		 * @param array $columns Columns.
		 * @return array
		 */
		public function arrange_rt_sc_code_selection_columns( $columns ) {
			$shortcode = [ 'shortcode' => esc_html__( 'Testimonial ShortCode', 'testimonial-slider-showcase' ) ];

			return array_slice( $columns, 0, 2, true ) + $shortcode + array_slice( $columns, 1, null, true );
		}

		/**
		 * Admin column
		 *
		 * @param array $column Column.
		 * @return void
		 */
		public function manage_rt_sc_code_selection_columns( $column ) {
			switch ( $column ) {
				case 'shortcode':
					echo '<input type="text" onfocus="this.select();" readonly="readonly" value="[rt-testimonial id=&quot;' . get_the_ID() . '&quot; title=&quot;' . get_the_title() . '&quot;]" class="large-text code tlp-code-sc">';
					break;
				default:
					break;
			}
		}

		/**
		 * Arrange columns
		 *
		 * @param array $columns Columns.
		 * @return array
		 */
		public function arrange_testimonial_columns( $columns ) {
			$column_thumbnail   = [ 'tss_thumbnail' => esc_html__( 'Image', 'testimonial-slider-showcase' ) ];
			$column_designation = [ 'tss_designation' => esc_html__( 'Designation', 'testimonial-slider-showcase' ) ];
			$column_company     = [ 'tss_company' => esc_html__( 'Company', 'testimonial-slider-showcase' ) ];
			$column_location    = [ 'tss_location' => esc_html__( 'Location', 'testimonial-slider-showcase' ) ];

			return array_slice( $columns, 0, 2, true ) + $column_thumbnail + $column_designation + $column_company + $column_location + array_slice( $columns, 1, null, true );
		}

		/**
		 * Manage Column
		 *
		 * @param array $column Column.
		 * @return void
		 */
		public function manage_testimonial_columns( $column ) {
			switch ( $column ) {
				case 'tss_thumbnail':
					echo get_the_post_thumbnail( get_the_ID(), [ 55, 55 ] );
					break;
				case 'tss_designation':
					echo esc_html( get_post_meta( get_the_ID(), 'tss_designation', true ) );
					break;
				case 'tss_company':
					echo esc_html( get_post_meta( get_the_ID(), 'tss_company', true ) );
					break;
				case 'tss_location':
					echo esc_html( get_post_meta( get_the_ID(), 'tss_location', true ) );
					break;
			}
		}

		/**
		 * Taxonomy filter.
		 *
		 * @return void
		 */
		public function add_taxonomy_filters() {
			global $typenow;

			if ( TSSPro()->post_type !== $typenow ) {
				return;
			}

			if ( ! function_exists( 'rttsp' ) ) {
				return;
			}
			foreach ( TSSPro()->taxonomies as $tax_slug ) {
				TSSPro()->printHtml( $this->build_taxonomy_filter( $tax_slug ), true );
			}
		}

		/**
		 * Build an individual dropdown filter.
		 *
		 * @param  string $tax_slug Taxonomy slug to build filter for.
		 *
		 * @return string Markup, or empty string if taxonomy has no terms.
		 */
		protected function build_taxonomy_filter( $tax_slug ) {
			$terms = get_terms( $tax_slug );

			if ( 0 == count( $terms ) ) {
				return '';
			}

			$tax_name         = $this->get_taxonomy_name_from_slug( $tax_slug );
			$current_tax_slug = isset( $_GET[ $tax_slug ] ) ? sanitize_text_field( wp_unslash( $_GET[ $tax_slug ] ) ) : false;
			$filter           = '<select name="' . esc_attr( $tax_slug ) . '" id="' . esc_attr( $tax_slug ) . '" class="postform">';
			$filter          .= '<option value="0">' . esc_html( $tax_name ) . '</option>';
			$filter          .= $this->build_term_options( $terms, $current_tax_slug );
			$filter          .= '</select>';

			return $filter;
		}

		/**
		 * Get the friendly taxonomy name, if given a taxonomy slug.
		 *
		 * @param  string $tax_slug Taxonomy slug.
		 *
		 * @return string Friendly name of taxonomy, or empty string if not a valid taxonomy.
		 */
		protected function get_taxonomy_name_from_slug( $tax_slug ) {
			$tax_obj = get_taxonomy( $tax_slug );

			if ( ! $tax_obj ) {
				return '';
			}

			return $tax_obj->labels->name;
		}

		/**
		 * Build a series of option elements from an array.
		 *
		 * Also checks to see if one of the options is selected.
		 *
		 * @param  array  $terms Array of term objects.
		 * @param  string $current_tax_slug Slug of currently selected term.
		 *
		 * @return string Markup.
		 */
		protected function build_term_options( $terms, $current_tax_slug ) {
			$options = '';

			foreach ( $terms as $term ) {
				$options .= sprintf(
					"<option value='%s' %s />%s</option>",
					esc_attr( $term->slug ),
					selected( $current_tax_slug, $term->slug, false ),
					esc_html( $term->name . '(' . $term->count . ')' )
				);
			}

			return $options;
		}

	}
endif;
