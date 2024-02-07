<?php
/**
 * Layout: Layout 1.
 *
 * @package RT_TSS
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

$level = [];

if ( in_array( 'designation', $items, true ) && $designation ) {
	$level[] = "<span class='author-designation'>" . esc_html( $designation ) . '</span>';
}

if ( in_array( 'company', $items, true ) && $company ) {
	$level[] = "<span class='item-company'>" . esc_html( $company ) . '</span>';
}

if ( in_array( 'location', $items, true ) && $location ) {
	$level[] = "<span class='author-location'>" . esc_html( $location ) . '</span>';
}

$html  = null;
$html .= "<div class='{$grid} {$class}'>";
$html .= '<div class="single-item-wrapper">';
$html .= '<div class="tss-meta-info tss-left">';

if ( in_array( 'author_img', $items, true ) ) {
	$html .= $link && function_exists( 'rttsp' ) ? "<div class='profile-img-wrapper'><a href='{$pLink}'>{$img}</a></div>" : "<div class='profile-img-wrapper'>{$img}</div>";
}

if ( in_array( 'author', $items, true ) && $author ) {
	$html .= $link && function_exists( 'rttsp' ) ? "<h3 class='author-name'><a href='{$pLink}'>" . esc_html( $author ) . '</a></h3>' : "<h3 class='author-name'>" . esc_html( $author ) . '</h3>';
}

if ( ! empty( $level ) ) {
	$level     = array_filter( $level );
	$levelList = implode( ', ', $level );
	$html     .= '<h4 class="author-bio">' . $levelList . '</h4>';
}

if ( in_array( 'rating', $items, true ) ) {
	$html .= '<div class="rating-wrapper">';

	for ( $i = 1; $i <= 5; $i++ ) {
		$starClass = 'filled';

		if ( $i > $rating ) {
			$starClass = 'empty';
		}

		$html .= "<span data-star='$i' class='star-$i dashicons dashicons-star-{$starClass}' aria-hidden='true'></span>";
	}

	$html .= '</div>';
}

if ( in_array( 'social_media', $items, true ) && ! empty( $social_media ) && function_exists( 'rttsp' ) ) {
	$html .= "<div class='author-social'>";

	foreach ( $social_media as $sid => $url ) {
		$html .= "<a href='{$url}' target='_blank'><span class='dashicons dashicons-{$sid}'></span></a>";
	}

	$html .= '</div>';
}

if ( in_array( 'social_share', $items, true ) && function_exists( 'rttsp' ) ) {
	$html .= TSSPro()->rtShare( $iID, $scMeta, $shareItems );
}

$html .= '</div>';
$html .= '<div class="item-content-wrapper tss-right">';

if ( in_array( 'testimonial', $items, true ) && $testimonial ) {
	$html .= "<div class='item-content'>{$testimonial}</div>";
}

$html .= '</div>';
$html .= '</div>';
$html .= '</div>';

TSSPro()->printHtml( $html );
