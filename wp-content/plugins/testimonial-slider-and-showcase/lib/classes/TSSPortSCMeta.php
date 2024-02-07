<?php
/**
 * Shortcode Meta Class.
 *
 * @package RT_TSS
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

if ( ! class_exists( 'TSSPortSCMeta' ) ) :
	/**
	 * Shortcode Meta Class.
	 */
	class TSSPortSCMeta {
		/**
		 * Class constructor
		 */
		public function __construct() {
			add_action( 'add_meta_boxes', [ $this, 'rt_tss_sc_meta_boxes' ] );
			add_action( 'save_post', [ $this, 'save_rt_tss_sc_meta_data' ], 10, 2 );
			add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts_sc' ] );
			add_action( 'edit_form_after_title', [ $this, 'rt_tss_sc_after_title' ] );
			add_action( 'admin_init', [ $this, 'remove_all_meta_box' ] );
		}

		/**
		 * Remove meta boxes.
		 *
		 * @return void
		 */
		public function remove_all_meta_box() {
			if ( is_admin() ) {
				add_filter(
					'get_user_option_meta-box-order_{' . TSSPro()->shortCodePT . '}',
					[ $this, 'remove_all_meta_boxes_tss' ]
				);
			}
		}

		/**
		 * Remove all meta boxes.
		 *
		 * @return array
		 */
		public function remove_all_meta_boxes_tss() {
			global $wp_meta_boxes;
			$publishBox                             = $wp_meta_boxes[ TSSPro()->shortCodePT ]['side']['core']['submitdiv'];
			$scBox                                  = $wp_meta_boxes[ TSSPro()->shortCodePT ]['normal']['high']['rt_tss_sc_settings_meta'];
			$scPreviewBox                           = $wp_meta_boxes[ TSSPro()->shortCodePT ]['normal']['high']['rt_tss_sc_preview_meta'];
			$wp_meta_boxes[ TSSPro()->shortCodePT ] = [
				'side'   => [ 'core' => [ 'submitdiv' => $publishBox ] ],
				'normal' => [
					'high' => [
						'rt_tss_sc_settings_meta' => $scBox,
						'rt_tss_sc_preview_meta'  => $scPreviewBox,
					],
				],
			];

			return [];
		}

		/**
		 * After title text.
		 *
		 * @param object $post Post object.
		 * @return void
		 */
		public function rt_tss_sc_after_title( $post ) {
			if ( TSSPro()->shortCodePT !== $post->post_type ) {
				return;
			}

			$html  = null;
			$html .= '<div class="postbox" style="margin-bottom: 0;"><div class="inside">';
			$html .= '<p><input type="text" onfocus="this.select();" readonly="readonly" value="[rt-testimonial id=&quot;' . $post->ID . '&quot; title=&quot;' . esc_html( $post->post_title ) . '&quot;]" class="large-text code tlp-code-sc">
			<input type="text" onfocus="this.select();" readonly="readonly" value="&#60;&#63;php echo do_shortcode( &#39;[rt-testimonial id=&quot;' . absint( $post->ID ) . '&quot; title=&quot;' . esc_html( $post->post_title ) . '&quot;]&#39; ) &#63;&#62;" class="large-text code tlp-code-sc">
			</p>';
			$html .= '</div></div>';

			TSSPro()->printHtml( $html, true );
		}

		/**
		 * Meta boxes.
		 *
		 * @return void
		 */
		public function rt_tss_sc_meta_boxes() {
			add_meta_box(
				'rt_tss_sc_settings_meta',
				esc_html__( 'Short Code Generator', 'testimonial-slider-showcase' ),
				[ $this, 'rt_tss_sc_settings_selection' ],
				TSSPro()->shortCodePT,
				'normal',
				'high'
			);

			add_meta_box(
				'rt_tss_sc_preview_meta',
				esc_html__( 'Layout Preview', 'testimonial-slider-showcase' ),
				[ $this, 'rt_tss_sc_preview_selection' ],
				TSSPro()->shortCodePT,
				'normal',
				'high'
			);

			add_meta_box(
				'rt_plugin_tss_sc_pro_information',
				esc_html__( 'Documentation', 'testimonial-slider-showcase' ),
				[ $this, 'rt_plugin_sc_pro_information' ],
				TSSPro()->shortCodePT,
				'side'
			);
		}

		/**
		 * Promotion
		 *
		 * @return void
		 */
		public function rt_plugin_sc_pro_information() {
			$fb_link = 'https://www.facebook.com/groups/234799147426640/';
			$rt_link = 'https://www.radiustheme.com/';
			$contact = 'https://www.radiustheme.com/contact/';
			$review  = 'https://wordpress.org/support/plugin/testimonial-slider-and-showcase/reviews/?filter=5#new-post';

			$html = sprintf(
				'<div class="rt-document-box">
					<div class="rt-box-icon"><i class="dashicons dashicons-media-document"></i></div>
					<div class="rt-box-content">
						<h3 class="rt-box-title">%1$s</h3>
							<p>%2$s</p>
							<a href="%3$s" target="_blank" class="rt-admin-btn">%1$s</a>
					</div>
				</div>',
				esc_html__( 'Documentation', 'testimonial-slider-showcase' ),
				esc_html__( 'Get started by spending some time with the documentation we included step by step process with screenshots with video.', 'testimonial-slider-showcase' ),
				esc_url( TSSPro()->documentation_link() )
			);

			$html .= '<div class="rt-document-box">
						<div class="rt-box-icon"><i class="dashicons dashicons-sos"></i></div>
						<div class="rt-box-content">
							<h3 class="rt-box-title">Need Help?</h3>
								<p>Stuck with something? Please create a
								<a href="' . esc_url( $contact ) . '">ticket here</a> or post on <a href="' . esc_url( $fb_link ) . '">facebook group</a>. For emergency case join our <a href="' . esc_url( $rt_link ) . '">live chat</a>.</p>
								<a href="' . esc_url( $contact ) . '" target="_blank" class="rt-admin-btn">Get Support</a>
							</div>
						</div>';

			if ( ! function_exists( 'rttsp' ) ) {
				$html .= '<div class="rt-document-box">
                    <div class="rt-box-icon"><i class="dashicons dashicons-awards"></i></div>
                    <div class="rt-box-content">
                        <h3 class="rt-box-title">Pro Features</h3>
                        <ol style="margin-left: 13px;">
                            <li>30 Amazing Layouts with Grid, Slider, Isotope & Video.</li>
                            <li>Front End Submission</li>
                            <li>Layout Preview in Shortcode Settings.</li>
                            <li>Taxonomy Ordering</li>
                        </ol>
                        <a href="' . esc_url( TSSPro()->pro_version_link() ) . '" class="rt-admin-btn" target="_blank">Get Pro Version</a>
                    </div>
                </div>';
			}
			$html .= '<div class="rt-document-box">
						<div class="rt-box-icon"><i class="dashicons dashicons-smiley"></i></div>
						<div class="rt-box-content">
							<h3 class="rt-box-title">Happy Our Work?</h3>
							<p>Thank you for choosing Testimonial Slider. If you have found our plugin useful and makes you smile, please consider giving us a 5-star rating on WordPress.org. It will help us to grow.</p>
							<a target="_blank" href="' . esc_url( $review ) . '" class="rt-admin-btn">Yes, You Deserve It</a>
						</div>
					</div>';

			TSSPro()->printHtml( $html );
		}

		/**
		 * Fields
		 *
		 * @return void
		 */
		public function rt_tss_sc_settings_selection() {
			wp_nonce_field( TSSPro()->nonceText(), TSSPro()->nonceId() );

			$tab = get_post_meta( get_the_ID(), '_rtts_sc_tab', true );

			if ( ! $tab ) {
				$tab = 'layout';
			}

			$layout_tab      = ( 'layout' === $tab ) ? 'active' : '';
			$filtering_tab   = ( 'filtering' === $tab ) ? 'active' : '';
			$field_selection = ( 'field-selection' === $tab ) ? 'active' : '';
			$styling         = ( 'styling' === $tab ) ? 'active' : '';

			$html  = null;
			$html .= '<div id="sc-tabs" class="rt-tabs rt-tab-container">';
			$html .= '<ul class="tab-nav rt-tab-nav">
					<li class="' . esc_attr( $layout_tab ) . '"><a href="#sc-layout"><i class="dashicons dashicons-layout"></i>' . esc_html__( 'Layout', 'testimonial-slider-showcase' ) . '</a></li>
					<li class="' . esc_attr( $filtering_tab ) . '"><a href="#sc-filtering"><i class="dashicons dashicons-filter"></i>' . esc_html__( 'Filtering', 'testimonial-slider-showcase' ) . '</a></li>
					<li class="' . esc_attr( $field_selection ) . '"><a href="#sc-field-selection"><i class="dashicons dashicons-editor-table"></i>' . esc_html__( 'Field Selection', 'testimonial-slider-showcase' ) . '</a></li>
					<li class="' . esc_attr( $styling ) . '"><a href="#sc-styling"><i class="dashicons dashicons-admin-customizer"></i>' . esc_html__( 'Styling', 'testimonial-slider-showcase' ) . '</a></li>
				</ul>';

			$html           .= '<input type="hidden" id="_rtts_sc_tab" name="_rtts_sc_tab" value="' . esc_attr( $tab ) . '" />';
			$layout_tab      = ( 'layout' === $tab ) ? 'display: block' : '';
			$filtering_tab   = ( 'filtering' === $tab ) ? 'display: block' : '';
			$field_selection = ( 'field-selection' === $tab ) ? 'display: block' : '';
			$styling         = ( 'styling' === $tab ) ? 'display: block' : '';

			$html .= '<div id="sc-layout" class="rt-tab-content" style="' . esc_attr( $layout_tab ) . '">';
			$html .= '<div class="tab-content">';
			$html .= TSSPro()->rtFieldGenerator( TSSPro()->scLayoutMetaFields() );
			$html .= '</div>';
			$html .= '</div>';

			$html .= '<div id="sc-filtering" class="rt-tab-content" style="' . esc_attr( $filtering_tab ) . '">';
			$html .= '<div class="tab-content">';
			$html .= TSSPro()->rtFieldGenerator( TSSPro()->scFilterMetaFields() );
			$html .= '</div>';
			$html .= '</div>';

			$html .= '<div id="sc-field-selection" class="rt-tab-content" style="' . esc_attr( $field_selection ) . '">';
			$html .= '<div class="tab-content">';
			$html .= TSSPro()->rtFieldGenerator( TSSPro()->scItemMetaFields() );
			$html .= '</div>';
			$html .= '</div>';

			$html .= '<div id="sc-styling" class="rt-tab-content" style="' . esc_attr( $styling ) . '">';
			$html .= '<div class="tab-content">';
			$html .= TSSPro()->rtFieldGenerator( TSSPro()->scStyleFields() );
			$html .= '</div>';
			$html .= '</div>';
			$html .= '</div>';

			TSSPro()->printHtml( $html, true );
		}

		/**
		 * Preview
		 *
		 * @return void
		 */
		public function rt_tss_sc_preview_selection() {
			$html  = null;
			$html .= "<div class='tss-response'><span class='spinner'></span></div>";
			$html .= "<div id='tss-preview-container'>";
			$html .= '</div>';

			TSSPro()->printHtml( $html );
		}

		/**
		 * Save meta boxes.
		 *
		 * @param int    $post_id Post ID.
		 * @param object $post Post object.
		 * @return void
		 */
		public function save_rt_tss_sc_meta_data( $post_id, $post ) {
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			if ( ! TSSPro()->verifyNonce() ) {
				return $post_id;
			}

			if ( $post->post_type !== TSSPro()->shortCodePT ) {
				return $post_id;
			}

			$mates = TSSPro()->pfpScMetaFields();
			foreach ( $mates as $metaKey => $field ) {
				$rValue = ! empty( $_REQUEST[ $metaKey ] ) ? $_REQUEST[ $metaKey ] : null;
				$value  = TSSPro()->sanitize( $field, $rValue );
				if ( empty( $field['multiple'] ) ) {
					update_post_meta( $post_id, $metaKey, $value );
				} else {
					delete_post_meta( $post_id, $metaKey );
					if ( is_array( $value ) && ! empty( $value ) ) {
						foreach ( $value as $item ) {
							add_post_meta( $post_id, $metaKey, $item );
						}
					} else {
						update_post_meta( $post_id, $metaKey, '' );
					}
				}
			}

			// Save current tab.
			$sc_tab = isset( $_REQUEST['_rtts_sc_tab'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_rtts_sc_tab'] ) ) : '';
			update_post_meta( $post_id, '_rtts_sc_tab', $sc_tab );

		}

		/**
		 * Scripts
		 *
		 * @return void
		 */
		public function admin_enqueue_scripts_sc() {
			global $pagenow, $typenow;

			if ( ! in_array( $pagenow, [ 'post.php', 'post-new.php', 'edit.php' ] ) ) {
				return;
			}

			if ( TSSPro()->shortCodePT !== $typenow ) {
				return;
			}

			wp_enqueue_media();
			// scripts.
			wp_enqueue_script(
				[
					'jquery',
					'wp-color-picker',
					'tss-select2',
					class_exists( 'Rtcl' ) ? 'rtt-swiper' : 'swiper',
					'tss-image-load',
					'tss-isotope',
					'tss-admin-preview',
					'tss-admin-sc',
					'tss-admin',
				]
			);

			// styles.
			wp_enqueue_style(
				[
					'wp-color-picker',
					'tss-fontello',
					'tss-select2',
					'swiper',
					'tss-admin',
					'tss',
				]
			);

			$demo_url     = 'https://www.radiustheme.com/demo/plugins/testimonial-slider/';
			$layout_group = [
				'grid'   => [
					[
						'name'  => 'Layout 1',
						'value' => 'layout1',
						'img'   => esc_url( TSSPro()->assetsUrl ) . 'images/layouts/layout1.png',
						'demo'  => esc_url( $demo_url ) . 'grid-layout-1',
					],
					[
						'name'  => 'Layout 2',
						'value' => 'layout2',
						'img'   => esc_url( TSSPro()->assetsUrl ) . 'images/layouts/layout2.png',
						'demo'  => esc_url( $demo_url ) . 'grid-layout-2',
					],
				],
				'slider' => [
					[
						'name'  => 'Carousel 1',
						'value' => 'carousel1',
						'img'   => esc_url( TSSPro()->assetsUrl ) . 'images/layouts/carousel1.png',
						'demo'  => esc_url( $demo_url ) . 'slider-1',
					],
					[
						'name'  => 'Carousel 3',
						'value' => 'carousel3',
						'img'   => esc_url( TSSPro()->assetsUrl ) . 'images/layouts/carousel3.png',
						'demo'  => esc_url( $demo_url ) . 'slider-2',
					],
				],
			];

			$layout_group = apply_filters( 'rtts_layout_groups', $layout_group );

			wp_localize_script(
				'tss-admin',
				'tss',
				[
					'ajaxurl' => esc_url( admin_url( 'admin-ajax.php' ) ),
					'nonce'   => esc_attr( wp_create_nonce( TSSPro()->nonceText() ) ),
					'nonceId' => esc_attr( TSSPro()->nonceId() ),
				]
			);

			$layout = get_post_meta( get_the_ID(), 'tss_layout', true );

			if ( ! $layout ) {
				$layout = 'layout1';
			}

			wp_localize_script(
				'tss-admin-sc',
				'tss_layout',
				[
					'layout_group' => $layout_group,
					'layout'       => esc_attr( $layout ),
				]
			);
		}
	}
endif;
