<?php
/**
 * Preview Class.
 *
 * @package RT_TSS
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

if ( ! class_exists( 'TSSPreview' ) ) :
	/**
	 * Preview Class.
	 */
	class TSSPreview {
		/**
		 * Class constructor
		 */
		public function __construct() {
			add_action( 'wp_ajax_tssPreviewAjaxCall', [ $this, 'tssPreviewAjaxCall' ] );
		}

		/**
		 * Ajax
		 *
		 * @return void
		 */
		public function tssPreviewAjaxCall() {
			$msg   = null;
			$html  = null;
			$scID  = null;
			$error = true;

			if ( TSSPro()->verifyNonce() ) {
				$error    = false;
				$rand     = wp_rand();
				$layoutID = 'rt-container-' . $rand;
				$layout   = ( ! empty( $_REQUEST['tss_layout'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tss_layout'] ) ) : 'layout1' );
				$dCol     = ( isset( $_REQUEST['tss_desktop_column'] ) ? absint( wp_unslash( $_REQUEST['tss_desktop_column'] ) ) : 3 );
				$tCol     = ( isset( $_REQUEST['tss_tab_column'] ) ? absint( wp_unslash( $_REQUEST['tss_tab_column'] ) ) : 2 );
				$mCol     = ( isset( $_REQUEST['tss_mobile_column'] ) ? absint( wp_unslash( $_REQUEST['tss_mobile_column'] ) ) : 1 );

				if ( ! in_array( $dCol, array_keys( TSSPro()->scColumns() ), true ) ) {
					$dCol = 3;
				}

				if ( ! in_array( $tCol, array_keys( TSSPro()->scColumns() ), true ) ) {
					$tCol = 2;
				}

				if ( ! in_array( $dCol, array_keys( TSSPro()->scColumns() ), true ) ) {
					$mCol = 1;
				}

				$dColItems = $dCol;
				$tColItems = $tCol;
				$mColItems = $mCol;

				$customImgSize = isset( $_REQUEST['tss_custom_image_size'] ) ? array_map( 'sanitize_text_field', $_REQUEST['tss_custom_image_size'] ) : [];
				$imgSize       = ( ! empty( $_REQUEST['tss_image_size'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tss_image_size'] ) ) : 'medium' );
				$defaultImgId  = ( ! empty( $_REQUEST['default_preview_image'] ) ? absint( $_REQUEST['default_preview_image'] ) : null );

				$isIsotope  = preg_match( '/isotope/', $layout );
				$isCarousel = preg_match( '/carousel/', $layout );

				/* Argument create */
				$containerDataAttr = false;
				$lazyLoadP         = false;
				$args              = [];
				$args['post_type'] = [ TSSPro()->post_type ];
				// Common filter
				/* post__in */
				$post__in = ( isset( $_REQUEST['tss_post__in'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tss_post__in'] ) ) : null );

				if ( $post__in ) {
					$post__in         = explode( ',', $post__in );
					$args['post__in'] = $post__in;
				}

				/* post__not_in */
				$post__not_in = ( isset( $_REQUEST['tss_post__not_in'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tss_post__not_in'] ) ) : null );

				if ( $post__not_in ) {
					$post__not_in         = explode( ',', $post__not_in );
					$args['post__not_in'] = $post__not_in;
				}

				/* LIMIT */
				$limit                  = ( ( empty( $_REQUEST['tss_limit'] ) || '-1' === $_REQUEST['tss_limit'] ) ? 10000000 : absint( $_REQUEST['tss_limit'] ) );
				$args['posts_per_page'] = $limit;
				$pagination             = ! empty( $_REQUEST['tss_pagination'] );
				$posts_loading_type     = ( ! empty( $_REQUEST['tss_pagination_type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tss_pagination_type'] ) ) : 'pagination' );

				if ( $pagination ) {
					$posts_per_page = ( isset( $_REQUEST['tss_posts_per_page'] ) ? absint( $_REQUEST['tss_posts_per_page'] ) : $limit );

					if ( $posts_per_page > $limit ) {
						$posts_per_page = $limit;
					}

					$args['posts_per_page'] = $posts_per_page;

					$paged         = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
					$offset        = $posts_per_page * ( (int) $paged - 1 );
					$args['paged'] = $paged;

					if ( absint( $args['posts_per_page'] ) > $limit - $offset ) {
						$args['posts_per_page'] = $limit - $offset;
					}
				}

				if ( $isCarousel ) {
					$args['posts_per_page'] = $limit;
				}

				// Taxonomy.
				$cats = ( isset( $_REQUEST['tss_categories'] ) ? array_map( 'absint', $_REQUEST['tss_categories'] ) : [] );
				$tags = ( isset( $_REQUEST['tss_tags'] ) ? array_map( 'absint', $_REQUEST['tss_tags'] ) : [] );
				$taxQ = [];

				if ( is_array( $cats ) && ! empty( $cats ) ) {
					$taxQ[] = [
						'taxonomy' => TSSPro()->taxonomies['category'],
						'field'    => 'term_id',
						'terms'    => $cats,
					];
				}

				if ( is_array( $tags ) && ! empty( $tags ) ) {
					$taxQ[] = [
						'taxonomy' => TSSPro()->taxonomies['tag'],
						'field'    => 'term_id',
						'terms'    => $tags,
					];
				}
				if ( ! empty( $taxQ ) ) {
					$args['tax_query'] = $taxQ;
					if ( count( $taxQ ) > 1 ) {
						$taxQ['relation'] = ! empty( $_REQUEST['tss_taxonomy_relation'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tss_taxonomy_relation'] ) ) : 'AND';
					}
				}

				// Order.
				$order_by = ( isset( $_REQUEST['tss_order_by'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tss_order_by'] ) ) : null );
				$order    = ( isset( $_REQUEST['tss_order'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tss_order'] ) ) : null );

				if ( $order ) {
					$args['order'] = $order;
				}

				if ( $order_by ) {
					$args['orderby'] = $order_by;
				}

				$testi_limit = ! empty( $_REQUEST['tss_testimonial_limit'] ) ? absint( $_REQUEST['tss_testimonial_limit'] ) : null;

				// Validation.
				$containerDataAttr .= " data-layout='{$layout}' data-desktop-col='{$dCol}'  data-tab-col='{$tCol}'  data-mobile-col='{$mCol}'";
				$dCol               = round( 12 / $dCol );
				$tCol               = round( 12 / $tCol );
				$mCol               = round( 12 / $mCol );
				if ( $isCarousel ) {
					$dCol = $tCol = $mCol = 12;
				}
				$arg = [];
				// $arg['scMeta']    = $scMeta;
				$arg['grid']      = "rt-col-md-{$dCol} rt-col-sm-{$tCol} rt-col-xs-{$mCol}";
				$gridType         = ! empty( $_REQUEST['tss_grid_style'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tss_grid_style'] ) ) : 'even';
				$arg['read_more'] = ! empty( $_REQUEST['tss_read_more_button_text'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tss_read_more_button_text'] ) ) : null;
				$arg['class']     = $gridType . '-grid-item';
				$arg['class']    .= ' tss-grid-item';
				$preLoader        = null;

				if ( $isIsotope ) {
					$arg['class'] .= ' isotope-item';
					$preLoader     = 'tss-pre-loader';
				}

				if ( $isCarousel ) {
					$arg['class'] .= ' slide-item swiper-slide';
					$preLoader     = 'tss-pre-loader';
				}

				$masonryG = null;

				if ( $gridType == 'even' ) {
					$masonryG      = ' tss-even';
					$arg['class'] .= ' even-grid-item';
				} elseif ( $gridType == 'masonry' && ! $isIsotope && ! $isCarousel ) {
					$masonryG      = ' tss-masonry';
					$arg['class'] .= ' masonry-grid-item';
				}

				$image_type = ! empty( $_REQUEST['tss_image_type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tss_image_type'] ) ) : 'normal';

				if ( $image_type == 'circle' ) {
					$arg['class'] .= ' tss-img-circle';
				}

				$margin = ! empty( $_REQUEST['tss_margin'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tss_margin'] ) ) : 'default';
				if ( $margin == 'no' ) {
					$arg['class'] .= ' no-margin';
				} else {
					$arg['class'] .= ' default-margin';
				}

				$image_shape = ! empty( $_REQUEST['tss_image_shape'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tss_image_shape'] ) ) : null;

				if ( $image_shape == 'circle' ) {
					$arg['class'] .= ' tss-img-circle';
				}

				$arg['items']       = ! empty( $_REQUEST['tss_item_fields'] ) ? array_map( 'sanitize_text_field', $_REQUEST['tss_item_fields'] ) : [];
				$arg['anchorClass'] = null;
				$arg['link']        = ! empty( $_REQUEST['tss_detail_page_link'] );

				$parentClass = ( ! empty( $_REQUEST['tss_parent_class'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tss_parent_class'] ) ) : null );

				$args['post_status'] = 'publish';

				if ( is_user_logged_in() && is_super_admin() ) {
					$args['post_status'] = [ 'publish', 'private' ];
				}

				// Start layout.
				$html .= TSSPro()->layoutStyle( $layoutID, $_REQUEST );
				$html .= "<div class='rt-container-fluid tss-wrapper {$parentClass}' id='{$layoutID}' {$containerDataAttr}>";
				$html .= "<div data-title='" . esc_html__( 'Loading ...', 'testimonial-slider-showcase' ) . "' class='rt-row tss-{$layout}{$masonryG} {$preLoader}'>";

				$tssQuery = new WP_Query( $args );

				if ( $tssQuery->have_posts() ) {
					if ( $isIsotope ) {
						$terms = get_terms(
							[
								'taxonomy'   => TSSPro()->taxonomies['category'],
								'hide_empty' => false,
								'orderby'    => 'meta_value_num',
								'order'      => 'ASC',
								'meta_key'   => '_order',
							]
						);

						$html          .= '<div class="tss-iso-filter"><div id="iso-button-' . $rand . '" class="tss-isotope-button-wrapper tooltip-active filter-button-group">';
						$htmlButton     = null;
						$fSelectTrigger = false;

						if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
							foreach ( $terms as $term ) {
								$tItem   = ! empty( $_REQUEST['tss_isotope_selected_filter'][0] ) ? absint( $_REQUEST['tss_isotope_selected_filter'][0] ) : null;
								$fSelect = null;

								if ( $tItem == $term->term_id ) {
									$fSelect        = ' selected';
									$fSelectTrigger = true;
								}

								$htmlButton .= "<span class='rt-iso-button {$fSelect}' data-filter-counter='' data-filter='.iso_{$term->term_id}' {$fSelect}>" . $term->name . '</span>';
							}
						}

						if ( empty( $_REQUEST['tss_isotope_filter_show_all'][0] ) ) {
							$fSelect = ( $fSelectTrigger ? null : 'class="selected"' );
							$html   .= "<span class='rt-iso-button' data-filter-counter='' data-filter='*' {$fSelect}>" . esc_html__( 'Show all', 'testimonial-slider-showcase' ) . '</span>';
						}

						$html .= $htmlButton;
						$html .= '</div>';

						if ( ! empty( $_REQUEST['tss_isotope_search_filtering'][0] ) ) {
							$html .= "<div class='iso-search'><input type='text' class='iso-search-input' placeholder='" . esc_html__( 'Search', 'testimonial-slider-showcase' ) . "' /></div>";
						}

						$html .= '</div>';

						$html .= '<div class="tss-isotope" id="tss-isotope-' . $rand . '">';
					} elseif ( $isCarousel ) {
						$smartSpeed         = ! empty( $_REQUEST['tss_carousel_speed'] ) ? absint( $_REQUEST['tss_carousel_speed'] ) : 250;
						$autoplayTimeout    = ! empty( $_REQUEST['tss_carousel_autoplay_timeout'] ) ? absint( $_REQUEST['tss_carousel_autoplay_timeout'] ) : 5000;
						$cOpt               = ! empty( $_REQUEST['tss_carousel_options'] ) ? array_map( 'sanitize_text_field', $_REQUEST['tss_carousel_options'] ) : [];
						$autoPlay           = ( in_array( 'autoplay', $cOpt, true ) ? 'true' : 'false' );
						$autoPlayHoverPause = ( in_array( 'autoplayHoverPause', $cOpt, true ) ? 'true' : 'false' );
						$nav                = ( in_array( 'nav', $cOpt, true ) ? 'true' : 'false' );
						$dots               = ( in_array( 'dots', $cOpt, true ) ? 'true' : 'false' );
						$loop               = ( in_array( 'loop', $cOpt, true ) ? 'true' : 'false' );
						$lazyLoad           = ( in_array( 'lazy_load', $cOpt, true ) ? 'true' : 'false' );
						$lazyLoadP          = ( in_array( 'lazy_load', $cOpt, true ) ? true : false );
						$autoHeight         = ( in_array( 'auto_height', $cOpt, true ) ? 'true' : 'false' );
						$rtl                = ( in_array( 'rtl', $cOpt, true ) ? 'dir="rtl"' : '' );
						$carouselWrapper    = 'carousel11' === $layout || 'carousel12' === $layout ? 'tss-carousel-main' : 'tss-carousel';

						$sliderMeta = [
							'tss_carousel_options'  => $cOpt,
							'tss_custom_image_size' => $customImgSize,
							'default_preview_image' => $defaultImgId,
							'tss_image_size'        => $imgSize,
						];

						if ( 'carousel11' === $layout ) {
							$html .= $this->renderThumbSlider( $scID, $tssQuery, $sliderMeta, $arg );
						}

						$html .= '<div class="carousel-wrapper">';
						$html .= "<div {$rtl} class='{$carouselWrapper} swiper'
										data-loop='{$loop}'
										data-items-desktop='{$dColItems}'
										data-items-tab='{$tColItems}'
										data-items-mobile='{$mColItems}'
										data-autoplay='{$autoPlay}'
										data-autoplay-timeout='{$autoplayTimeout}'
										data-autoplay-hover-pause='{$autoPlayHoverPause}'
										data-dots='{$dots}'
										data-nav='{$nav}'
										data-lazy-load='{$lazyLoad}'
										data-auto-height='{$autoHeight}'
										data-smart-speed='{$smartSpeed}'
										>";

						$html .= '<div class="swiper-wrapper">';
					}

					while ( $tssQuery->have_posts() ) :
						$tssQuery->the_post();
						$iID                 = get_the_ID();
						$arg['iID']          = $iID;
						$arg['author']       = get_the_title();
						$arg['designation']  = get_post_meta( $iID, 'tss_designation', true );
						$arg['company']      = get_post_meta( $iID, 'tss_company', true );
						$arg['location']     = get_post_meta( $iID, 'tss_location', true );
						$arg['rating']       = get_post_meta( $iID, 'tss_rating', true );
						$arg['video']        = get_post_meta( $iID, 'tss_video', true );
						$arg['social_media'] = get_post_meta( $iID, 'tss_social_media', true );
						$arg['pLink']        = get_permalink();
						$aHtml               = null;

						if ( in_array( 'read_more', $arg['items'], true ) && function_exists( 'rttsp' ) ) {
							$aHtml = "<a class='rt-read-more' href='" . esc_url( $arg['pLink'] ) . "'>{$arg['read_more']}</a>";
						}

						$arg['testimonial'] = get_the_content();

						if ( $testi_limit ) {
							$arg['testimonial'] = TSSPro()->strip_tags_content( get_the_content(), $testi_limit, $aHtml );
						}

						$arg['video_url'] = get_post_meta( $iID, 'tss_video', true );

						if ( $isIsotope ) {
							$termAs    = wp_get_post_terms(
								$iID,
								TSSPro()->taxonomies['category'],
								[ 'fields' => 'all' ]
							);
							$isoFilter = null;

							if ( ! empty( $termAs ) ) {
								foreach ( $termAs as $term ) {
									$isoFilter .= ' iso_' . $term->term_id;
									$isoFilter .= ' ' . $term->slug;
								}
							}

							$arg['isoFilter'] = $isoFilter;
						}

						if ( $lazyLoadP ) {
							$arg['lazyLoad'] = true;
							$arg['img']      = TSSPro()->getFeatureImage( $iID, $imgSize, $customImgSize, $defaultImgId, true );
						} else {
							$arg['lazyLoad'] = false;
							$arg['img']      = TSSPro()->getFeatureImage( $iID, $imgSize, $customImgSize, $defaultImgId );
						}

						$html .= TSSPro()->render( 'layouts/' . $layout, $arg );

					endwhile;

					if ( $isIsotope ) {
						$html .= '</div>';
					} elseif ( $isCarousel ) {
						if ( 'grid' !== $_REQUEST['tss_layout'] ) {
							$html .= '</div>';

							$html .= 'true' === $nav ? '<div class="swiper-arrow swiper-button-next"><i class="rttss-right-open"></i></div><div class="swiper-arrow swiper-button-prev"><i class="rttss-left-open"></i></div>' : '';
							$html .= 'true' === $dots ? '<div class="swiper-pagination"></div>' : '';
							$html .= '</div>';
							$html .= '</div>';

							if ( 'carousel12' === $layout ) {
								$html .= $this->renderThumbSlider( $scID, $tssQuery, $sliderMeta, $arg );
							}
						}
					}
				} else {
					$html .= '<p>' . esc_html__( 'No testimonial found', 'testimonial-slider-showcase' ) . '</p>';
				}

				if ( $isIsotope || $isCarousel ) {
					$html .= '<div class="rt-loading-overlay"></div><div class="rt-loading rt-ball-clip-rotate"><div></div></div>';
				}

				$html .= '</div>'; // End row.

				if ( $pagination && ! $isCarousel ) {
					$htmlUtility = null;

					if ( 'pagination' === $posts_loading_type ) {
						$htmlUtility .= TSSPro()->pagination( $tssQuery->max_num_pages, $args['posts_per_page'] );
					} elseif ( 'pagination_ajax' === $posts_loading_type && ! $isIsotope ) {
						$htmlUtility .= TSSPro()->pagination(
							$tssQuery->max_num_pages,
							$args['posts_per_page'],
							true,
							$scID
						);
					} elseif ( 'load_more' === $posts_loading_type ) {
						$postPp         = $tssQuery->query_vars['posts_per_page'];
						$page           = $tssQuery->query_vars['paged'];
						$foundPosts     = $tssQuery->found_posts;
						$totalPage      = $tssQuery->max_num_pages;
						$morePosts      = $foundPosts - ( $postPp * $page );
						$noMorePostText = esc_html__( 'No More Post to load', 'testimonial-slider-showcase' );
						$loadMoreText   = esc_html__( 'Load More', 'testimonial-slider-showcase' );
						$loadingText    = esc_html__( 'Loading ...', 'testimonial-slider-showcase' );
						$htmlUtility   .= '<div class="tss-load-more">
												<span class="rt-button" data-sc-id="' . absint( $scID ) . '" data-total-pages="' . absint( $totalPage ) . '" data-posts-per-page="' . absint( $postPp ) . '" data-found-posts="' . absint( $foundPosts ) . '" data-paged="1"
												data-no-more-post-text="' . esc_attr( $noMorePostText ) . '" data-loading-text="' . esc_attr( $loadingText ) . '">' . esc_html( $loadMoreText ) . ' <span>(' . absint( $morePosts ) . ')</span></span>
											</div>';
					} elseif ( 'load_on_scroll' === $posts_loading_type ) {
						$htmlUtility .= '<div class="tss-scroll-load-more" data-trigger="1" data-sc-id="' . absint( $scID ) . '" data-paged="2"></div>';
					}

					if ( $htmlUtility ) {
						$html .= "<div class='tss-utility'>" . $htmlUtility . '</div>';
					}
				}

				$html .= '</div>'; // container.
				wp_reset_postdata();

			} else {
				$msg = esc_html__( 'Security Error !!', 'testimonial-slider-showcase' );
			}

			wp_send_json(
				[
					'error' => $error,
					'msg'   => $msg,
					'data'  => $html,
				]
			);

		}

		/**
		 * Thumb slider.
		 *
		 * @param int    $scID ID.
		 * @param object $query Query.
		 * @param array  $meta_value Meta.
		 * @param array  $arg Arg.
		 * @return string
		 */
		public function renderThumbSlider( $scID, $query, $meta_value, $arg ) {
			$html = '';
			$cOpt = ! empty( $meta_value['tss_carousel_options'] ) ? array_filter( $meta_value['tss_carousel_options'] ) : [];

			$customImgSize = get_post_meta( $scID, 'tss_custom_image_size', true );
			$defaultImgId  = ( ! empty( $meta_value['default_preview_image'] ) ? absint( $meta_value['default_preview_image'] ) : null );
			$imgSize       = ( ! empty( $meta_value['tss_image_size'] ) ? sanitize_text_field( $meta_value['tss_image_size'] ) : 'medium' );

			$rtl = ( in_array( 'rtl', $cOpt ) ? 'dir="rtl"' : '' );

			$html     .= "<div {$rtl} class='tss-carousel-thumb swiper'>";
				$html .= '<div class="swiper-wrapper">';

			while ( $query->have_posts() ) :
				$query->the_post();
				$iID          = get_the_ID();
				$arg['iID']   = $iID;
				$arg['pLink'] = get_permalink();
				$lazyLoadP    = in_array( 'lazy_load', $cOpt ) ? true : false;

				if ( $lazyLoadP ) {
					$arg['lazyLoad'] = true;
					$arg['img']      = TSSPro()->getFeatureImage( $iID, $imgSize, $customImgSize, $defaultImgId, true );
				} else {
					$arg['lazyLoad'] = false;
					$arg['img']      = TSSPro()->getFeatureImage( $iID, $imgSize, $customImgSize, $defaultImgId );
				}

				$html .= TSSPro()->render( 'layouts/carousel_thumb', $arg );

				endwhile;

				$html .= '</div>';
			$html     .= '</div>';

			return $html;
		}
	}

endif;
