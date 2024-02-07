<?php
/**
 * Options Class.
 *
 * @package RT_TSS
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

if ( ! class_exists( 'TSSProOptions' ) ) :
	/**
	 * Options Class.
	 */
	class TSSProOptions {
		public function generalSettings() {
			$settings = get_option( TSSPro()->options['settings'] );

			return [
				'slug' => [
					'label'       => esc_html__( 'Slug', 'testimonial-slider-showcase' ),
					'type'        => 'text',
					'description' => esc_html__( 'Slug configuration', 'testimonial-slider-showcase' ),
					'attr'        => "size='10'",
					'value'       => ( ! empty( $settings['slug'] ) ? sanitize_title_with_dashes( $settings['slug'] ) : 'testimonial' ),
				],
			];
		}

		public function tssFrontEndSubmitFields() {
			$fields            = $this->formAllFields();
			$settings          = get_option( TSSPro()->options['settings'] );
			$activeFields      = ( ! empty( $settings['form_fields'] ) ? array_map( 'sanitize_text_field', $settings['form_fields'] ) : [] );
			$mergeActiveFields = array_merge( $activeFields, array_keys( $this->frontEndMandatoryFields() ) );
			$newFields         = [];

			foreach ( $fields as $key => $value ) {
				if ( in_array( $key, $mergeActiveFields ) ) {
					$newFields[ $key ] = $value;
				}
			}

			return apply_filters( 'tss_front_end_submit_fields', $newFields, $mergeActiveFields, $activeFields );
		}

		public function frontEndMandatoryFields() {
			$fields = $this->frontEndFields();

			if ( isset( $fields['tss_feature_image'] ) ) {
				unset( $fields['tss_feature_image'] );
			}

			return apply_filters( 'tss_front_end_mandatory_fields', $fields );
		}

		public function formAllFields() {
			$fields                                 = $this->singleTestimonialFields();
			$fields['tss_social_media']['frontEnd'] = true;
			$fields                                 = $this->frontEndFields() + $fields + $this->frontEndRecaptcha();

			return apply_filters( 'tss_from_all_fields', $fields );
		}

		public function frontEndRecaptcha() {
			$fields = [
				'tss_recaptcha' => [
					'type' => 'recaptcha',
				],
			];

			return apply_filters( 'tss_from_end_recaptcha_field', $fields );
		}

		public function frontEndFields() {
			$fields = [
				'tss_name'          => [
					'label'    => esc_html__( 'Name', 'testimonial-slider-showcase' ),
					'type'     => 'text',
					'required' => true,
				],
				'tss_testimonial'   => [
					'label'    => esc_html__( 'Testimonial', 'testimonial-slider-showcase' ),
					'type'     => 'textarea',
					'required' => true,
				],
				'tss_feature_image' => [
					'label' => esc_html__( 'Image', 'testimonial-slider-showcase' ),
					'type'  => 'simple_image',
				],
			];

			return apply_filters( 'tss_front_end_fields', $fields );
		}

		public function detailFieldControl() {
			$settings      = get_option( TSSPro()->options['settings'] );
			$detail_option = $this->single_page_field();

			unset( $detail_option['read_more'] );

			return [
				'field'              => [
					'label'       => esc_html__( 'Select the field', 'testimonial-slider-showcase' ),
					'type'        => 'checkbox',
					'options'     => $detail_option,
					'default'     => array_keys(
						[
							'author'      => esc_html__( 'Author', 'testimonial-slider-showcase' ),
							'author_img'  => esc_html__( 'Author Image', 'testimonial-slider-showcase' ),
							'testimonial' => esc_html__( 'Short Description', 'testimonial-slider-showcase' ),
							'designation' => esc_html__( 'Designation', 'testimonial-slider-showcase' ),
							'company'     => esc_html__( 'Company', 'testimonial-slider-showcase' ),
							'location'    => esc_html__( 'Location', 'testimonial-slider-showcase' ),
							'rating'      => esc_html__( 'Rating', 'testimonial-slider-showcase' ),
						]
					),
					'multiple'    => true,
					'alignment'   => 'vertical',
					'description' => esc_html__(
						'Select the option which you like to display',
						'testimonial-slider-showcase'
					),
					'value'       => ( ! empty( $settings['field'] ) ? array_map( 'sanitize_text_field', $settings['field'] ) : [] ),
				],
				'social_share_items' => [
					'type'        => 'checkbox',
					'name'        => 'social_share_items',
					'holderClass' => 'tss-hidden tss-social-share-fields-single',
					'label'       => esc_html__( 'Social share items', 'testimonial-slider-showcase' ),
					'id'          => 'social_share_items',
					'alignment'   => 'vertical',
					'multiple'    => true,
					'options'     => $this->socialShareItemList(),
					'value'       => ( ! empty( $settings['social_share_items'] ) ? array_map( 'sanitize_text_field', $settings['social_share_items'] ) : [] ),
				],
			];
		}

		public function formFieldControl() {
			$settings = get_option( TSSPro()->options['settings'] );

			return [
				'form_fields'                => [
					'label'       => esc_html__( 'Select the field', 'testimonial-slider-showcase' ),
					'type'        => 'checkbox',
					'options'     => $this->get_formFieldControl_fields(),
					'multiple'    => true,
					'alignment'   => 'vertical',
					'description' => esc_html__(
						'Select the option which you like to display',
						'testimonial-slider-showcase'
					),
					'value'       => ( ! empty( $settings['form_fields'] ) ? array_map( 'sanitize_text_field', $settings['form_fields'] ) : [] ),
				],
				'notification_disable'       => [
					'label'       => esc_html__( 'Disable admin notification', 'testimonial-slider-showcase' ),
					'optionLabel' => esc_html__( 'Disable', 'testimonial-slider-showcase' ),
					'option'      => 1,
					'type'        => 'switch',
					'value'       => ( isset( $settings['notification_disable'] ) && 1 === $settings['notification_disable'] ? 1 : null ),
				],
				'notification_email'         => [
					'label'       => esc_html__( 'Admin Notification to Email', 'testimonial-slider-showcase' ),
					'type'        => 'email',
					'attr'        => 'size="40"',
					'description' => esc_html__( 'If blank this will be the admin email.', 'testimonial-slider-showcase' ),
					'default'     => get_option( 'admin_email' ),
					'value'       => ( isset( $settings['notification_email'] ) ? esc_attr( $settings['notification_email'] ) : null ),
				],
				'notification_email_subject' => [
					'label' => esc_html__( 'Notification Email Subject', 'testimonial-slider-showcase' ),
					'type'  => 'text',
					'attr'  => 'size="50"',
					'value' => ( isset( $settings['notification_email_subject'] ) && $settings['notification_email_subject'] ? esc_html( $settings['notification_email_subject'] ) : esc_html__( '[{site_name}] New Testimonial received', 'testimonial-slider-showcase' ) ),
				],
			];
		}

		public function get_formFieldControl_fields() {
			$fields   = $this->formAllFields();
			$newField = [];

			foreach ( $fields as $key => $value ) {
				if ( ! in_array( $key, [ 'tss_name', 'tss_testimonial' ] ) ) {
					if ( $key == 'tss_recaptcha' ) {
						$newField[ $key ] = esc_html__( 'reCAPTCHA', 'testimonial-slider-showcase' );
					} else {
						$newField[ $key ] = $value['label'];
					}
				}
			}

			return $newField;
		}

		public function recaptchaFields() {
			$settings = get_option( TSSPro()->options['settings'] );

			return [
				'tss_site_key'   => [
					'label' => esc_html__( 'Site Key', 'testimonial-slider-showcase' ),
					'type'  => 'text',
					'class' => 'full-width',
					'value' => ( ! empty( $settings['tss_site_key'] ) ? esc_attr( $settings['tss_site_key'] ) : null ),
				],
				'tss_secret_key' => [
					'label' => esc_html__( 'Secret Key', 'testimonial-slider-showcase' ),
					'type'  => 'text',
					'class' => 'full-width',
					'value' => ( ! empty( $settings['tss_secret_key'] ) ? esc_attr( $settings['tss_secret_key'] ) : null ),
				],
			];
		}

		public function othersSettings() {
			$settings = get_option( TSSPro()->options['settings'] );

			return [
				'custom_css' => [
					'label'       => esc_html__( 'Custom CSS', 'testimonial-slider-showcase' ),
					'type'        => 'custom_css',
					'description' => esc_html__( 'Add your custom css here!!!', 'testimonial-slider-showcase' ),
					'value'       => ( ! empty( $settings['custom_css'] ) ? esc_html( $settings['custom_css'] ) : null ),
				],
			];
		}

		public function rtLicenceField() {
			$settings       = get_option( TSSPro()->options['settings'] );
			$status         = ! empty( $settings['license_status'] ) && 'valid' === $settings['license_status'] ? true : false;
			$license_status = ! empty( $settings['license_key'] ) ? sprintf(
				"<span class='license-status'>%s</span>",
				$status ? "<input type='submit' class='button-secondary rt-licensing-btn danger' name='license_deactivate' value='" . esc_html__( 'Deactivate License', 'testimonial-slider-showcase' ) . "'/>"
					: "<input type='submit' class='button-secondary rt-licensing-btn button-primary' name='license_activate' value='" . esc_html__( 'Activate License', 'testimonial-slider-showcase' ) . "'/>"
			) : ' ';

			return [
				'license_key' => [
					'type'            => 'text',
					'attr'            => 'style="min-width:300px;"',
					'label'           => esc_html__( 'Enter your license key', 'testimonial-slider-showcase' ),
					'description_adv' => TSSPro()->htmlKses( $license_status, 'advanced' ),
					'id'              => 'license_key',
					'value'           => isset( $settings['license_key'] ) ? esc_attr( $settings['license_key'] ) : '',
				],
			];
		}

		public function socialShareItemList() {
			$list = [
				'facebook'  => esc_html__( 'Facebook', 'testimonial-slider-showcase' ),
				'twitter'   => esc_html__( 'Twitter', 'testimonial-slider-showcase' ),
				'linkedin'  => esc_html__( 'LinkedIn', 'testimonial-slider-showcase' ),
				'pinterest' => esc_html__( 'Pinterest', 'testimonial-slider-showcase' ),
				'email'     => esc_html__( 'Email', 'testimonial-slider-showcase' ),
			];

			return apply_filters( 'tss_social_share_item_list', $list );
		}

		public function scItemMetaFields() {
			return [
				'tss_item_fields'    => [
					'type'        => 'checkbox',
					'label'       => esc_html__( 'Field selection', 'testimonial-slider-showcase' ),
					'multiple'    => true,
					'alignment'   => 'vertical',
					'default'     => array_keys( $this->sc_fieldSelection_fields() ),
					'options'     => $this->sc_fieldSelection_fields(),
					'description' => esc_html__( 'Check the field which you want to display', 'testimonial-slider-showcase' ),
				],
				'social_share_items' => [
					'type'        => 'checkbox',
					'name'        => 'social_share_items',
					'holderClass' => 'tss-hidden tss-social-share-fields',
					'label'       => esc_html__( 'Social share items', 'testimonial-slider-showcase' ),
					'is_pro'      => true,
					'id'          => 'social_share_items',
					'alignment'   => 'vertical',
					'multiple'    => true,
					'options'     => $this->socialShareItemList(),
				],
			];
		}

		public function scLayout() {
			return [
				'layout1'   => esc_html__( 'Grid', 'testimonial-slider-showcase' ),
				'carousel1' => esc_html__( 'Slider', 'testimonial-slider-showcase' ),
			];
		}

		public function scLayoutMetaFields() {
			$layout_option = [
				'layout_type'                   => [
					'type'    => 'radio-image',
					'label'   => esc_html__( 'Layout type', 'testimonial-slider-showcase' ),
					'id'      => 'rtts-layout-type',
					'options' => apply_filters(
						'rtts_layout_type',
						[
							[
								'name'  => esc_html__( 'Grid Layout', 'testimonial-slider-showcase' ),
								'value' => 'grid',
								'img'   => TSSPro()->assetsUrl . 'images/grid.png',
							],
							[
								'name'  => esc_html__( 'Slider Layout', 'testimonial-slider-showcase' ),
								'value' => 'slider',
								'img'   => TSSPro()->assetsUrl . 'images/slider.png',
							],
						]
					),
				],
				'tss_layout'                    => [
					'type'        => 'radio-image',
					'label'       => esc_html__( 'Layout style', 'testimonial-slider-showcase' ),
					'description' => esc_html__( 'Click to the Layout name to see live demo', 'testimonial-slider-showcase' ),
					'id'          => 'rtts-style',
					'options'     => [],
				],
				'tss_desktop_column'            => [
					'type'    => 'select',
					'label'   => esc_html__( 'Desktop column', 'testimonial-slider-showcase' ),
					'class'   => 'rt-select2',
					'default' => 3,
					'options' => $this->scColumns(),
				],
				'tss_tab_column'                => [
					'type'    => 'select',
					'label'   => esc_html__( 'Tab column', 'testimonial-slider-showcase' ),
					'class'   => 'rt-select2',
					'default' => 2,
					'options' => $this->scColumns(),
				],
				'tss_mobile_column'             => [
					'type'    => 'select',
					'label'   => esc_html__( 'Mobile column', 'testimonial-slider-showcase' ),
					'class'   => 'rt-select2',
					'default' => 1,
					'options' => $this->scColumns(),
				],
				'tss_carousel_speed'            => [
					'label'       => esc_html__( 'Speed', 'testimonial-slider-showcase' ),
					'holderClass' => 'tss-hidden tss-carousel-item',
					'type'        => 'number',
					'default'     => 2000,
					'description' => esc_html__( 'Auto play Speed in milliseconds', 'testimonial-slider-showcase' ),
				],
				'tss_carousel_options'          => [
					'label'       => esc_html__( 'Carousel Options', 'testimonial-slider-showcase' ),
					'holderClass' => 'tss-hidden tss-carousel-item',
					'type'        => 'checkbox',
					'multiple'    => true,
					'alignment'   => 'vertical',
					'options'     => $this->owlProperty(),
					'default'     => [ 'autoplay', 'arrows', 'dots', 'responsive', 'infinite' ],
				],
				'tss_carousel_autoplay_timeout' => [
					'label'       => esc_html__( 'Autoplay timeout', 'testimonial-slider-showcase' ),
					'holderClass' => 'tss-hidden tss-carousel-auto-play-timeout',
					'type'        => 'number',
					'default'     => 5000,
					'description' => esc_html__( 'Autoplay interval timeout', 'testimonial-slider-showcase' ),
				],
				'tss_pagination'                => [
					'type'        => 'switch',
					'label'       => esc_html__( 'Pagination', 'testimonial-slider-showcase' ),
					'holderClass' => 'pagination',
					'optionLabel' => esc_html__( 'Enable', 'testimonial-slider-showcase' ),
					'option'      => 1,
				],
				'tss_posts_per_page'            => [
					'type'        => 'number',
					'label'       => esc_html__( 'Display per page', 'testimonial-slider-showcase' ),
					'holderClass' => 'tss-pagination-item tss-hidden',
					'default'     => 5,
					'description' => esc_html__( 'If value of Limit setting is not blank (empty), this value should be smaller than Limit value.', 'testimonial-slider-showcase' ),
				],
				'tss_load_more_button_text'     => [
					'type'        => 'text',
					'label'       => esc_html__( 'Load more button text', 'testimonial-slider-showcase' ),
					'holderClass' => ' tss-load-more-item tss-hidden',
					'default'     => esc_html__( 'Load more', 'testimonial-slider-showcase' ),
				],
				'tss_image_size'                => [
					'type'    => 'select',
					'label'   => esc_html__( 'Image Size', 'testimonial-slider-showcase' ),
					'class'   => 'rt-select2',
					'options' => TSSPro()->get_image_sizes(),
				],
				'tss_custom_image_size'         => [
					'type'        => 'image_size',
					'label'       => esc_html__( 'Custom Image Size', 'testimonial-slider-showcase' ),
					'holderClass' => 'tss-hidden',
					'description' => sprintf(
						'<span style="margin-top: 20px; display: block; color: #9A2A2A; font-weight: 400;">%s</span>.',
						esc_html__( 'Please note that, if you enter image size larger than the actual image iteself, the image sizes will fallback to the default thumbnail dimension (150x150 px)', 'testimonial-slider-showcase' )
					),
				],
				'tss_image_type'                => [
					'type'      => 'radio',
					'label'     => esc_html__( 'Image Type', 'testimonial-slider-showcase' ),
					'alignment' => 'vertical',
					'default'   => 'normal',
					'options'   => $this->get_image_types(),
				],
				'tss_testimonial_limit'         => [
					'type'        => 'number',
					'is_pro'      => true,
					'label'       => esc_html__( 'Testimonial limit', 'testimonial-slider-showcase' ),
					'description' => esc_html__( 'Testimonial limit only integer number is allowed, Leave it blank for full text.', 'testimonial-slider-showcase' ),
				],
				'tss_margin'                    => [
					'type'        => 'radio',
					'label'       => esc_html__( 'Margin', 'testimonial-slider-showcase' ),
					'alignment'   => 'vertical',
					'description' => esc_html__( 'Select the margin for layout', 'testimonial-slider-showcase' ),
					'default'     => 'default',
					'options'     => $this->scMarginOpt(),
				],
				'tss_grid_style'                => [
					'type'        => 'radio',
					'label'       => esc_html__( 'Grid style', 'testimonial-slider-showcase' ),
					'alignment'   => 'vertical',
					'is_pro'      => true,
					'description' => esc_html__( 'Select grid style for layout', 'testimonial-slider-showcase' ),
					'default'     => 'even',
					'options'     => $this->scGridStyle(),
				],
				'tss_detail_page_link'          => [
					'type'        => 'switch',
					'is_pro'      => true,
					'label'       => esc_html__( 'Detail page link', 'testimonial-slider-showcase' ),
					'optionLabel' => esc_html__( 'Enable', 'testimonial-slider-showcase' ),
					'option'      => 1,
				],
				'default_preview_image'         => [
					'is_pro'      => true,
					'type'        => 'image',
					'label'       => esc_html__( 'Default preview  image', 'testimonial-slider-showcase' ),
					'description' => esc_html__( 'Add an image for default preview', 'testimonial-slider-showcase' ),
				],
			];

			return apply_filters( 'rtts_layout_option', $layout_option );
		}

		public function scFilterMetaFields() {
			return [
				'tss_post__in'          => [
					'label'       => esc_html__( 'Include only', 'testimonial-slider-showcase' ),
					'type'        => 'text',
					'description' => esc_html__( 'List of post IDs to show (comma-separated values, for example: 1,2,3)', 'testimonial-slider-showcase' ),
				],
				'tss_post__not_in'      => [
					'label'       => esc_html__( 'Exclude', 'testimonial-slider-showcase' ),
					'type'        => 'text',
					'description' => esc_html__( 'List of post IDs to show (comma-separated values, for example: 1,2,3)', 'testimonial-slider-showcase' ),
				],
				'tss_limit'             => [
					'label'       => esc_html__( 'Limit', 'testimonial-slider-showcase' ),
					'type'        => 'number',
					'description' => esc_html__( 'The number of posts to show. Set empty to show all found posts.', 'testimonial-slider-showcase' ),
				],
				'tss_categories'        => [
					'label'       => esc_html__( 'Categories', 'testimonial-slider-showcase' ),
					'type'        => 'select',
					'class'       => 'rt-select2',
					'multiple'    => true,
					'is_pro'      => true,
					'description' => esc_html__( 'Select the category you want to filter, Leave it blank for All category', 'testimonial-slider-showcase' ),
					'options'     => TSSPro()->getAllTssCategoryList(),
				],
				'tss_tags'              => [
					'label'       => esc_html__( 'Tags', 'testimonial-slider-showcase' ),
					'type'        => 'select',
					'class'       => 'rt-select2',
					'multiple'    => true,
					'is_pro'      => true,
					'description' => esc_html__( 'Select the category you want to filter, Leave it blank for All category', 'testimonial-slider-showcase' ),
					'options'     => TSSPro()->getAllTssTagList(),
				],
				'tss_taxonomy_relation' => [
					'label'       => esc_html__( 'Taxonomy relation', 'testimonial-slider-showcase' ),
					'type'        => 'select',
					'is_pro'      => true,
					'class'       => 'rt-select2',
					'description' => esc_html__( 'Select this option if you select more than one taxonomy like category and tag, or category , tag and tools', 'testimonial-slider-showcase' ),
					'options'     => $this->scTaxonomyRelation(),
				],
				'tss_order_by'          => [
					'label'   => esc_html__( 'Order By', 'testimonial-slider-showcase' ),
					'type'    => 'select',
					'class'   => 'rt-select2',
					'default' => 'date',
					'options' => $this->scOrderBy(),
				],
				'tss_order'             => [
					'label'     => esc_html__( 'Order', 'testimonial-slider-showcase' ),
					'type'      => 'radio',
					'options'   => $this->scOrder(),
					'default'   => 'DESC',
					'alignment' => 'vertical',
				],
			];
		}

		public function singleTestimonialFields() {
			return [
				'tss_designation'  => [
					'label' => esc_html__( 'Designation', 'testimonial-slider-showcase' ),
					'type'  => 'text',
					'class' => 'full-width',
				],
				'tss_company'      => [
					'label' => esc_html__( 'Company', 'testimonial-slider-showcase' ),
					'type'  => 'text',
					'class' => 'full-width',
				],
				'tss_location'     => [
					'label' => esc_html__( 'Location', 'testimonial-slider-showcase' ),
					'type'  => 'text',
					'class' => 'full-width',
				],
				'tss_rating'       => [
					'label' => esc_html__( 'Rating', 'testimonial-slider-showcase' ),
					'type'  => 'rating',
				],
				'tss_video'        => [
					'label'       => esc_html__( 'Video URL', 'testimonial-slider-showcase' ),
					'type'        => 'video',
					'is_pro'      => true,
					'class'       => 'full-width',
					'description' => esc_html__( 'Only Youtube and Vimeo url allowed', 'testimonial-slider-showcase' ),
				],
				'tss_social_media' => [
					'label'       => esc_html__( 'Social media', 'testimonial-slider-showcase' ),
					'type'        => 'socialMedia',
					'is_pro'      => true,
					'description' => 'Drag from available list to active list to add social link.<br> Please add your social page URL like (https://www.facebook.com/radiustheme/ or https://twitter.com/radiustheme).',
					'options'     => $this->getSocialMediaList(),
				],
			];
		}

		public function scStyleFields() {
			$sc_style = [
				'tss_parent_class'                   => [
					'type'        => 'text',
					'label'       => esc_html__( 'Parent class', 'testimonial-slider-showcase' ),
					'class'       => 'medium-text',
					'description' => esc_html__( 'Parent class for adding custom css', 'testimonial-slider-showcase' ),
				],
				'tss_color'                          => [
					'type'    => 'multiple_options',
					'label'   => esc_html__( 'Color schema', 'testimonial-slider-showcase' ),
					'options' => [
						'primary' => [
							'type'  => 'color',
							'label' => esc_html__( 'Primary', 'testimonial-slider-showcase' ),
						],
					],
				],
				'tss_button_style'                   => [
					'type'    => 'multiple_options',
					'label'   => 'Button color',
					'options' => [
						'bg'         => [
							'type'  => 'color',
							'label' => esc_html__( 'Background', 'testimonial-slider-showcase' ),
						],
						'hover_bg'   => [
							'type'  => 'color',
							'label' => esc_html__( 'Hover background', 'testimonial-slider-showcase' ),
						],
						'active_bg'  => [
							'type'  => 'color',
							'label' => esc_html__( 'Active background', 'testimonial-slider-showcase' ),
						],
						'text'       => [
							'type'  => 'color',
							'label' => esc_html__( 'Text', 'testimonial-slider-showcase' ),
						],
						'hover_text' => [
							'type'  => 'color',
							'label' => esc_html__( 'Hover text', 'testimonial-slider-showcase' ),
						],
						'border'     => [
							'type'  => 'color',
							'label' => esc_html__( 'Border', 'testimonial-slider-showcase' ),
						],
					],
				],
				'tss_iso_counter_tooltip_bg_color'   => [
					'type'        => 'colorpicker',
					'holderClass' => 'tss-isotope-item tss-hidden',
					'label'       => esc_html__( 'Isotope counter tooltip background color', 'testimonial-slider-showcase' ),
				],
				'tss_iso_counter_tooltip_text_color' => [
					'type'        => 'colorpicker',
					'holderClass' => 'tss-isotope-item tss-hidden',
					'label'       => esc_html__( 'Isotope counter tooltip text color', 'testimonial-slider-showcase' ),
				],
				'tss_gutter'                         => [
					'is_pro'      => true,
					'type'        => 'number',
					'label'       => esc_html__( 'Gutter / Padding', 'testimonial-slider-showcase' ),
					'description' => 'Unit will be pixel, No need to give any unit. Only integer value will be valid.<br> Leave it blank for default',
				],
				'tss_image_border'                   => [
					'type'    => 'multiple_options',
					'label'   => esc_html__( 'Image Border Style', 'testimonial-slider-showcase' ),
					'is_pro'  => true,
					'options' => [
						'width' => [
							'type'        => 'number',
							'label'       => esc_html__( 'Border width at pixel', 'testimonial-slider-showcase' ),
							'description' => esc_html__( 'Only number is allowed', 'testimonial-slider-showcase' ),
						],
						'color' => [
							'type'  => 'color',
							'label' => esc_html__( 'Border Color', 'testimonial-slider-showcase' ),
						],
						'style' => [
							'type'    => 'select',
							'label'   => esc_html__( 'Border style', 'testimonial-slider-showcase' ),
							'class'   => 'rt-select2',
							'options' => [
								'solid'  => esc_html__( 'Solid', 'testimonial-slider-showcase' ),
								'dotted' => esc_html__( 'Dotted', 'testimonial-slider-showcase' ),
								'dashed' => esc_html__( 'Dashed', 'testimonial-slider-showcase' ),
								'double' => esc_html__( 'Double', 'testimonial-slider-showcase' ),
							],
						],
					],
				],
				'tss_author_name_style'              => [
					'type'  => 'style',
					'label' => esc_html__( 'Author name', 'testimonial-slider-showcase' ),
				],
				'tss_author_bio_style'               => [
					'type'  => 'style',
					'label' => esc_html__( 'Author bio', 'testimonial-slider-showcase' ),
				],
				'tss_rating_style'                   => [
					'type'  => 'style',
					'label' => esc_html__( 'Rating', 'testimonial-slider-showcase' ),
				],
				'tss_social_style'                   => [
					'type'   => 'style',
					'is_pro' => true,
					'label'  => esc_html__( 'Social', 'testimonial-slider-showcase' ),
				],
				'tss_testimonial_style'              => [
					'type'    => 'multiple_options',
					'label'   => esc_html__( 'Testimonial Style', 'testimonial-slider-showcase' ),
					'options' => [
						'color' => [
							'type'  => 'color',
							'label' => esc_html__( 'Color', 'testimonial-slider-showcase' ),
						],
					],
				],
			];
			return apply_filters( 'rtts_layout_style', $sc_style );
		}

		public function scTaxonomyRelation() {
			return [
				'OR'  => esc_html__( 'OR Relation', 'testimonial-slider-showcase' ),
				'AND' => esc_html__( 'AND Relation', 'testimonial-slider-showcase' ),
			];
		}

		public function scTextStyle() {
			return [
				'normal'  => esc_html__( 'Normal', 'testimonial-slider-showcase' ),
				'italic'  => esc_html__( 'Italic', 'testimonial-slider-showcase' ),
				'oblique' => esc_html__( 'Oblique', 'testimonial-slider-showcase' ),
			];
		}

		public function get_image_types() {
			return [
				'normal' => esc_html__( 'Normal', 'testimonial-slider-showcase' ),
				'circle' => esc_html__( 'Circle', 'testimonial-slider-showcase' ),
			];
		}

		public function scColumns() {
			return [
				1 => esc_html__( '1 Column', 'testimonial-slider-showcase' ),
				2 => esc_html__( '2 Columns', 'testimonial-slider-showcase' ),
				3 => esc_html__( '3 Columns', 'testimonial-slider-showcase' ),
				4 => esc_html__( '4 Columns', 'testimonial-slider-showcase' ),
				5 => esc_html__( '5 Columns', 'testimonial-slider-showcase' ),
				6 => esc_html__( '6 Columns', 'testimonial-slider-showcase' ),
			];
		}

		public function scOrderBy() {
			$oder_by = [
				'menu_order' => esc_html__( 'Menu Order', 'testimonial-slider-showcase' ),
				'title'      => esc_html__( 'Name', 'testimonial-slider-showcase' ),
				'ID'         => esc_html__( 'ID', 'testimonial-slider-showcase' ),
				'date'       => esc_html__( 'Date', 'testimonial-slider-showcase' ),
			];

			if ( function_exists( 'rttsp' ) ) {
				$oder_by['rand'] = esc_html__( 'Random', 'testimonial-slider-showcase' );
			}

			return $oder_by;
		}

		public function scOrder() {
			return [
				'ASC'  => esc_html__( 'Ascending', 'testimonial-slider-showcase' ),
				'DESC' => esc_html__( 'Descending', 'testimonial-slider-showcase' ),
			];
		}

		public function scGridStyle() {
			return [
				'even'    => esc_html__( 'Even', 'testimonial-slider-showcase' ),
				'masonry' => esc_html__( 'Masonry', 'testimonial-slider-showcase' ),
			];
		}

		public function imageCropType() {
			return [
				'soft' => esc_html__( 'Soft Crop', 'testimonial-slider-showcase' ),
				'hard' => esc_html__( 'Hard Crop', 'testimonial-slider-showcase' ),
			];
		}

		public function scFontSize() {
			$num = [];
			for ( $i = 10; $i <= 30; $i ++ ) {
				$num[ $i ] = $i . 'px';
			}

			return $num;
		}

		public function scMarginOpt() {
			return [
				'default' => esc_html__( 'Bootstrap default', 'testimonial-slider-showcase' ),
				'no'      => esc_html__( 'No Margin', 'testimonial-slider-showcase' ),
			];
		}

		public function getSocialMediaList() {
			return apply_filters(
				'tss_social_link',
				[
					'facebook'  => esc_html__( 'Facebook', 'testimonial-slider-showcase' ),
					'twitter'   => esc_html__( 'Twitter', 'testimonial-slider-showcase' ),
					'instagram' => esc_html__( 'Instagram', 'testimonial-slider-showcase' ),
					'linkedin'  => esc_html__( 'Linkedin', 'testimonial-slider-showcase' ),
					'pinterest' => esc_html__( 'Pinterest', 'testimonial-slider-showcase' ),
					'email'     => esc_html__( 'Email', 'testimonial-slider-showcase' ),
					'skype'     => esc_html__( 'Skype', 'testimonial-slider-showcase' ),
					'whatsapp'     => esc_html__( 'Whatsapp', 'testimonial-slider-showcase' ),
					'telegram'     => esc_html__( 'Telegram', 'testimonial-slider-showcase' ),
				]
			);
		}


		public function scAlignment() {
			return [
				'left'    => esc_html__( 'Left', 'testimonial-slider-showcase' ),
				'right'   => esc_html__( 'Right', 'testimonial-slider-showcase' ),
				'center'  => esc_html__( 'Center', 'testimonial-slider-showcase' ),
				'justify' => esc_html__( 'Justify', 'testimonial-slider-showcase' ),
			];
		}

		public function scTextWeight() {
			return [
				'normal'  => esc_html__( 'Normal', 'testimonial-slider-showcase' ),
				'bold'    => esc_html__( 'Bold', 'testimonial-slider-showcase' ),
				'bolder'  => esc_html__( 'Bolder', 'testimonial-slider-showcase' ),
				'lighter' => esc_html__( 'Lighter', 'testimonial-slider-showcase' ),
				'inherit' => esc_html__( 'Inherit', 'testimonial-slider-showcase' ),
				'initial' => esc_html__( 'Initial', 'testimonial-slider-showcase' ),
				'unset'   => esc_html__( 'Unset', 'testimonial-slider-showcase' ),
				100       => esc_html__( '100', 'testimonial-slider-showcase' ),
				200       => esc_html__( '200', 'testimonial-slider-showcase' ),
				300       => esc_html__( '300', 'testimonial-slider-showcase' ),
				400       => esc_html__( '400', 'testimonial-slider-showcase' ),
				500       => esc_html__( '500', 'testimonial-slider-showcase' ),
				600       => esc_html__( '600', 'testimonial-slider-showcase' ),
				700       => esc_html__( '700', 'testimonial-slider-showcase' ),
				800       => esc_html__( '800', 'testimonial-slider-showcase' ),
				900       => esc_html__( '900', 'testimonial-slider-showcase' ),
			];
		}

		public function alignment() {
			return [
				'left'    => esc_html__( 'Left', 'testimonial-slider-showcase' ),
				'right'   => esc_html__( 'Right', 'testimonial-slider-showcase' ),
				'center'  => esc_html__( 'Center', 'testimonial-slider-showcase' ),
				'justify' => esc_html__( 'Justify', 'testimonial-slider-showcase' ),
			];
		}

		public function tlpOverlayBg() {
			return [
				'0.1' => esc_html__( '10 %', 'testimonial-slider-showcase' ),
				'0.2' => esc_html__( '20 %', 'testimonial-slider-showcase' ),
				'0.3' => esc_html__( '30 %', 'testimonial-slider-showcase' ),
				'0.4' => esc_html__( '40 %', 'testimonial-slider-showcase' ),
				'0.5' => esc_html__( '50 %', 'testimonial-slider-showcase' ),
				'0.6' => esc_html__( '60 %', 'testimonial-slider-showcase' ),
				'0.7' => esc_html__( '70 %', 'testimonial-slider-showcase' ),
				'0.8' => esc_html__( '80 %', 'testimonial-slider-showcase' ),
				'0.9' => esc_html__( '90 %', 'testimonial-slider-showcase' ),
			];
		}

		public function owlProperty() {
			return [
				'loop'               => esc_html__( 'Loop', 'testimonial-slider-showcase' ),
				'autoplay'           => esc_html__( 'Auto Play', 'testimonial-slider-showcase' ),
				'autoplayHoverPause' => esc_html__( 'Pause on mouse hover', 'testimonial-slider-showcase' ),
				'nav'                => esc_html__( 'Nav Button', 'testimonial-slider-showcase' ),
				'dots'               => esc_html__( 'Pagination', 'testimonial-slider-showcase' ),
				'auto_height'        => esc_html__( 'Auto Height', 'testimonial-slider-showcase' ),
				'lazy_load'          => esc_html__( 'Lazy Load', 'testimonial-slider-showcase' ),
				'rtl'                => esc_html__( 'Right to left (RTL)', 'testimonial-slider-showcase' ),
			];
		}

		public function sc_fieldSelection_fields() {
			if ( function_exists( 'rttsp' ) ) {
				$fields      = $this->singleTestimonialFields();
				$fieldsArray = [];
				foreach ( $fields as $index => $field ) {
					$fieldsArray[ str_replace( 'tss_', '', $index ) ] = ( $index == 'tss_video' ? esc_html__(
						'Video',
						'testimonial-slider-showcase'
					) : $field['label'] );
				}

				$newFields = [
					'author'      => esc_html__( 'Author', 'testimonial-slider-showcase' ),
					'author_img'  => esc_html__( 'Author Image', 'testimonial-slider-showcase' ),
					'testimonial' => esc_html__( 'Testimonial', 'testimonial-slider-showcase' ),
					'read_more'   => esc_html__( 'Read More', 'testimonial-slider-showcase' ),
				];

				return array_merge( $newFields, $fieldsArray, [ 'social_share' => esc_html__( 'Social Share', 'testimonial-slider-showcase' ) ] );
			} else {
				return $newFields = [
					'author'      => esc_html__( 'Author', 'testimonial-slider-showcase' ),
					'author_img'  => esc_html__( 'Author Image', 'testimonial-slider-showcase' ),
					'testimonial' => esc_html__( 'Short Description', 'testimonial-slider-showcase' ),
					'designation' => esc_html__( 'Designation', 'testimonial-slider-showcase' ),
					'company'     => esc_html__( 'Company', 'testimonial-slider-showcase' ),
					'location'    => esc_html__( 'Location', 'testimonial-slider-showcase' ),
					'rating'      => esc_html__( 'Rating', 'testimonial-slider-showcase' ),
				];
			}
		}

		public function single_page_field() {
			$fields = $this->sc_fieldSelection_fields();

			return $fields;
		}
	}
endif;
