<?php
/**
 * Taxonomy Order Page.
 *
 * @package RT_TSS
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

$taxonomy_objects = TSSPro()->getAllTaxonomyObject();
?>

<div class="wrap">
	<h2><?php esc_html_e( 'Taxonomy Ordering', 'testimonial-slider-showcase' ); ?></h2>
	<div class="rt-admin-wrap">
		<?php
		if ( ! function_exists( 'get_term_meta' ) ) {
			?>
			<div class="update-message notice inline notice-error notice-alt">
				<p>
					<?php
					esc_html_e( 'Please update your WordPress to 4.4.0 or latest version to use taxonomy order functionality.', 'testimonial-slider-showcase' );
					?>
				</p>
			</div>
			<?php
		}
		?>
		<div class="taxonomy-wrapper">
			<label><?php esc_html_e( 'Select taxonomy', 'testimonial-slider-showcase' ); ?> </label>
			<select class="rt-select2" id="tss-taxonomy">
				<option value=""><?php esc_html_e( 'Select one taxonomy', 'testimonial-slider-showcase' ); ?></option>
				<?php
				if ( ! empty( $taxonomy_objects ) ) {
					foreach ( $taxonomy_objects as $sTaxonomy ) {
						echo '<option value="' . esc_attr( $sTaxonomy->name ) . '">' . esc_html( $sTaxonomy->label ) . '</option>';
					}
				}
				?>
			</select>
		</div>
		<div class="ordering-wrapper">
			<div id="term-wrapper">
				<p><?php esc_html_e( 'No taxonomy selected', 'testimonial-slider-showcase' ); ?></p>
			</div>
		</div>
	</div>
</div>
