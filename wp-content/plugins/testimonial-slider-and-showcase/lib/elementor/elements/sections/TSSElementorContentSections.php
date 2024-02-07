<?php
/**
 * Content Section Class.
 *
 * @package RT_TSS
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

if ( ! class_exists( 'TSSElementorContentSections' ) ) :
	/**
	 * Grid Widget Elementor Content Tab Controls Class
	 */
	class TSSElementorContentSections {

		/**
		 * Accumulates tab fields.
		 *
		 * @access private
		 * @static
		 *
		 * @var array
		 */
		private $fields = [];

		/**
		 * Tab name.
		 *
		 * @access private
		 * @static
		 *
		 * @var array
		 */
		private $tab = \Elementor\Controls_Manager::TAB_CONTENT;

		/**
		 * Layout Section - Grid
		 *
		 * @return array
		 */
		public function gridLayout() {
			$this->fields[] = [
				'mode'  => 'section_start',
				'id'    => 'tss_el_layout_section',
				'label' => esc_html__( 'Layout', 'testimonial-slider-showcase' ),
				'tab'   => $this->tab,
			];

			$this->fields[] = [
				'type'            => \Elementor\Controls_Manager::RAW_HTML,
				'id'              => 'tss_el_layout_note',
				'raw'             => sprintf(
					'<h3 class="tss-elementor-group-heading">%s</h3>',
					esc_html__( 'Select Layout', 'testimonial-slider-showcase' )
				),
				'content_classes' => 'elementor-panel-heading-title',
			];

			$this->fields[] = [
				'type'    => 'tss-image-selector',
				'id'      => 'tss_el_layout_type',
				'options' => TSSPro()->tssGridLayouts(),
				'default' => 'layout1',
			];

			$this->fields[] = [
				'type'            => \Elementor\Controls_Manager::RAW_HTML,
				'id'              => 'tss_el_layout_col_note',
				'raw'             => sprintf(
					'<h3 class="tss-elementor-group-heading">%s</h3>',
					esc_html__( 'Responsive Columns', 'testimonial-slider-showcase' )
				),
				'content_classes' => 'elementor-panel-heading-title',
				'separator'       => 'before',
			];

			$this->fields[] = [
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'id'          => 'tss_el_cols',
				'mode'        => 'responsive',
				'label'       => esc_html__( 'Number of Columns', 'testimonial-slider-showcase' ),
				'description' => esc_html__( 'Please select the number of columns to show per row.', 'testimonial-slider-showcase' ),
				'options'     => TSSPro()->scColumns(),
				'default'     => 2,
				'required'    => true,
				'device_args' => [
					\Elementor\Controls_Stack::RESPONSIVE_TABLET => [
						'required' => false,
						'default'  => 2,
					],
					\Elementor\Controls_Stack::RESPONSIVE_MOBILE => [
						'required' => false,
						'default'  => 1,
					],
				],
			];

			$this->fields = array_merge( apply_filters( 'rttss_elementor_before_grid_layout_end', $this->fields ) );

			$this->fields[] = [
				'mode' => 'section_end',
			];

			return $this->fields;
		}

		/**
		 * Layout Section - Slider
		 *
		 * @return array
		 */
		public function sliderLayout() {
			$this->fields[] = [
				'mode'  => 'section_start',
				'id'    => 'tss_el_layout_section',
				'label' => esc_html__( 'Layout', 'testimonial-slider-showcase' ),
				'tab'   => $this->tab,
			];

			$this->fields[] = [
				'type'            => \Elementor\Controls_Manager::RAW_HTML,
				'id'              => 'tss_el_layout_note',
				'raw'             => sprintf(
					'<h3 class="tss-elementor-group-heading">%s</h3>',
					esc_html__( 'Select Layout', 'testimonial-slider-showcase' )
				),
				'content_classes' => 'elementor-panel-heading-title',
			];

			$this->fields[] = [
				'type'    => 'tss-image-selector',
				'id'      => 'tss_el_layout_type',
				'options' => TSSPro()->tssSliderLayout(),
				'default' => 'carousel1',
			];

			$this->fields[] = [
				'type'            => \Elementor\Controls_Manager::RAW_HTML,
				'id'              => 'tss_el_layout_col_note',
				'raw'             => sprintf(
					'<h3 class="tss-elementor-group-heading">%s</h3>',
					esc_html__( 'Responsive Columns', 'testimonial-slider-showcase' )
				),
				'content_classes' => 'elementor-panel-heading-title',
				'separator'       => 'before',
			];

			$this->fields[] = [
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'id'          => 'tss_el_cols',
				'mode'        => 'responsive',
				'label'       => esc_html__( 'Number of Columns', 'testimonial-slider-showcase' ),
				'description' => esc_html__( 'Please select the number of columns to show per row.', 'testimonial-slider-showcase' ),
				'options'     => TSSPro()->scColumns(),
				'default'     => 2,
				'required'    => true,
				'device_args' => [
					\Elementor\Controls_Stack::RESPONSIVE_TABLET => [
						'required' => false,
						'default'  => 2,
					],
					\Elementor\Controls_Stack::RESPONSIVE_MOBILE => [
						'required' => false,
						'default'  => 1,
					],
				],
			];

			$this->fields = array_merge( apply_filters( 'rttss_elementor_before_slider_layout_end', $this->fields ) );

			$this->fields[] = [
				'mode' => 'section_end',
			];

			return $this->fields;
		}


		/**
		 * Layout Section - Isotope
		 *
		 * @return array
		 */
		public function isotopeLayout() {
			$this->fields[] = [
				'mode'  => 'section_start',
				'id'    => 'tss_el_layout_section',
				'label' => esc_html__( 'Layout', 'testimonial-slider-showcase' ),
				'tab'   => $this->tab,
			];

			$this->fields = array_merge( apply_filters( 'rttss_elementor_isotope_layouts', $this->fields ) );

			$this->fields[] = [
				'mode' => 'section_end',
			];

			return $this->fields;
		}

		/**
		 * Isotope Section
		 *
		 * @return array
		 */
		public function isotope() {
			$this->fields[] = [
				'mode'  => 'section_start',
				'id'    => 'tss_el_isotope_settings_section',
				'label' => esc_html__( 'Isotope Settings', 'testimonial-slider-showcase' ),
				'tab'   => $this->tab,
			];

			$this->fields = array_merge( apply_filters( 'rttss_elementor_isotope_settings', $this->fields ) );

			$this->fields[] = [
				'mode' => 'section_end',
			];

			return $this->fields;
		}

		/**
		 * Pagination Section for Isotope.
		 *
		 * @return array
		 */
		public function paginationIso() {
			$this->fields[] = [
				'mode'  => 'section_start',
				'id'    => 'tss_el_pagination_section',
				'label' => esc_html__( 'Pagination', 'testimonial-slider-showcase' ),
				'tab'   => $this->tab,
			];

			$this->fields[] = [
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'id'           => 'tss_el_pagination',
				'label'        => esc_html__( 'Show Pagination?', 'testimonial-slider-showcase' ),
				'return_value' => 'yes',
				'description'  => esc_html__( 'Please enable the switch to display pagination.', 'testimonial-slider-showcase' ),
			];

			$this->fields[] = [
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'id'          => 'tss_el_pagination_per_page',
				'label'       => esc_html__( 'Number of Testimonials Per Page', 'testimonial-slider-showcase' ),
				'default'     => 5,
				'description' => esc_html__( 'Please enter the number of posts per page to show.', 'testimonial-slider-showcase' ),
				'separator'   => 'before',
				'condition'   => [ 'tss_el_pagination' => [ 'yes' ] ],
			];

			$this->fields = array_merge( apply_filters( 'rttss_elementor_before_pagination_end_iso', $this->fields ) );

			$this->fields[] = [
				'mode' => 'section_end',
			];

			return $this->fields;
		}

		/**
		 * Pagination Section
		 *
		 * @return array
		 */
		public function pagination() {
			$this->fields[] = [
				'mode'  => 'section_start',
				'id'    => 'tss_el_pagination_section',
				'label' => esc_html__( 'Pagination', 'testimonial-slider-showcase' ),
				'tab'   => $this->tab,
			];

			$this->fields[] = [
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'id'           => 'tss_el_pagination',
				'label'        => esc_html__( 'Show Pagination?', 'testimonial-slider-showcase' ),
				'return_value' => 'yes',
				'description'  => esc_html__( 'Please enable the switch to display pagination.', 'testimonial-slider-showcase' ),
			];

			$this->fields[] = [
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'id'          => 'tss_el_pagination_per_page',
				'label'       => esc_html__( 'Number of Testimonials Per Page', 'testimonial-slider-showcase' ),
				'default'     => 5,
				'description' => esc_html__( 'Please enter the number of posts per page to show.', 'testimonial-slider-showcase' ),
				'separator'   => 'before',
				'condition'   => [ 'tss_el_pagination' => [ 'yes' ] ],
			];

			$this->fields = array_merge( apply_filters( 'rttss_elementor_before_pagination_end', $this->fields ) );

			$this->fields[] = [
				'mode' => 'section_end',
			];

			return $this->fields;
		}

		/**
		 * Image Section
		 *
		 * @return array
		 */
		public function image() {
			$this->fields[] = [
				'mode'  => 'section_start',
				'id'    => 'tss_el_image_section',
				'label' => esc_html__( 'Image', 'testimonial-slider-showcase' ),
				'tab'   => $this->tab,
			];

			$this->fields[] = [
				'type'            => \Elementor\Controls_Manager::SELECT2,
				'id'              => 'tss_el_image',
				'label'           => esc_html__( 'Select Image Size', 'testimonial-slider-showcase' ),
				'options'         => TSSPro()->get_image_sizes(),
				'default'         => 'thumbnail',
				'label_block'     => true,
				'content_classes' => 'elementor-descriptor',
			];

			$this->fields = array_merge( apply_filters( 'rttss_elementor_before_image_end', $this->fields ) );

			$this->fields[] = [
				'mode' => 'section_end',
			];

			return $this->fields;
		}

		/**
		 * Filtering Section
		 *
		 * @return array
		 */
		public function filtering() {
			$this->fields[] = [
				'mode'  => 'section_start',
				'id'    => 'tss_el_filtering_section',
				'label' => esc_html__( 'Query', 'testimonial-slider-showcase' ),
				'tab'   => $this->tab,
			];

			$this->fields[] = [
				'type'            => \Elementor\Controls_Manager::RAW_HTML,
				'id'              => 'tss_el_filtering_note',
				'raw'             => sprintf(
					'<h3 class="tss-elementor-group-heading">%s</h3>',
					esc_html__( 'Filtering', 'testimonial-slider-showcase' )
				),
				'content_classes' => 'elementor-panel-heading-title',
			];

			$this->fields[] = [
				'mode' => 'tabs_start',
				'id'   => 'tss_el_filtering_tab',
			];

			$this->fields[] = [
				'mode'  => 'tab_start',
				'id'    => 'tss_el_filtering_include_tab',
				'label' => esc_html__( 'Include', 'testimonial-slider-showcase' ),
			];

			$this->fields[] = [
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'id'          => 'tss_el_include_posts',
				'label'       => esc_html__( 'Include Testimonials', 'testimonial-slider-showcase' ),
				'options'     => TSSPro()->tssAllTestimonialPosts(),
				'description' => esc_html__( 'Please select the testimonials to show. Leave it blank to include all posts.', 'testimonial-slider-showcase' ),
				'multiple'    => true,
				'label_block' => true,
			];

			$this->fields[] = [
				'mode' => 'tab_end',
			];

			$this->fields[] = [
				'mode'  => 'tab_start',
				'id'    => 'tss_el_filtering_exclude_tab',
				'label' => esc_html__( 'Exclude', 'testimonial-slider-showcase' ),
			];

			$this->fields[] = [
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'id'          => 'tss_el_exclude_posts',
				'label'       => esc_html__( 'Exclude Testimonials', 'testimonial-slider-showcase' ),
				'options'     => TSSPro()->tssAllTestimonialPosts(),
				'description' => esc_html__( 'Please select the testimonials to exclude. Leave it blank to exclude none.', 'testimonial-slider-showcase' ),
				'multiple'    => true,
				'label_block' => true,
			];

			$this->fields[] = [
				'mode' => 'tab_end',
			];

			$this->fields[] = [
				'mode' => 'tabs_end',
			];

			$this->fields[] = [
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'id'          => 'tss_el_posts_limit',
				'label'       => esc_html__( 'Posts Limit', 'testimonial-slider-showcase' ),
				'description' => esc_html__( 'The number of testimonials to show. Set empty to show all testimonials.', 'testimonial-slider-showcase' ),
				'default'     => 8,
			];

			$this->fields = array_merge( apply_filters( 'rttss_elementor_before_sorting_control', $this->fields ) );

			$this->fields[] = [
				'type'            => \Elementor\Controls_Manager::RAW_HTML,
				'id'              => 'tss_el_sorting_note',
				'raw'             => sprintf(
					'<h3 class="tss-elementor-group-heading">%s</h3>',
					esc_html__( 'Sorting', 'testimonial-slider-showcase' )
				),
				'content_classes' => 'elementor-panel-heading-title',
				'separator'       => 'before',
			];

			$this->fields[] = [
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'id'          => 'tss_el_posts_order_by',
				'label'       => esc_html__( 'Order By', 'testimonial-slider-showcase' ),
				'description' => esc_html__( 'Please choose to reorder to testimonials.', 'testimonial-slider-showcase' ),
				'options'     => TSSPro()->tssPostsOrderBy(),
				'default'     => 'date',
			];

			$this->fields[] = [
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'id'          => 'tss_el_posts_order',
				'label'       => esc_html__( 'Order', 'testimonial-slider-showcase' ),
				'description' => esc_html__( 'Please choose to reorder to testimonials.', 'testimonial-slider-showcase' ),
				'options'     => TSSPro()->tssPostsOrder(),
				'default'     => 'DESC',
			];

			$this->fields[] = [
				'mode' => 'section_end',
			];

			return $this->fields;
		}

		/**
		 * Content Visibility Section
		 *
		 * @return array
		 */
		public function contentVisibility() {
			$this->fields[] = [
				'mode'  => 'section_start',
				'id'    => 'tss_el_field_display_section',
				'label' => esc_html__( 'Content Visibility', 'testimonial-slider-showcase' ),
				'tab'   => $this->tab,
			];

			$this->fields[] = [
				'type'        => \Elementor\Controls_Manager::SWITCHER,
				'id'          => 'tss_el_author',
				'label'       => esc_html__( 'Show Author Name', 'testimonial-slider-showcase' ),
				'label_on'    => esc_html__( 'On', 'testimonial-slider-showcase' ),
				'label_off'   => esc_html__( 'Off', 'testimonial-slider-showcase' ),
				'default'     => 'yes',
				'description' => esc_html__( 'Switch on to display author name.', 'testimonial-slider-showcase' ),
			];

			$this->fields[] = [
				'type'        => \Elementor\Controls_Manager::SWITCHER,
				'id'          => 'tss_el_author_img',
				'label'       => esc_html__( 'Show Author Image', 'testimonial-slider-showcase' ),
				'label_on'    => esc_html__( 'On', 'testimonial-slider-showcase' ),
				'label_off'   => esc_html__( 'Off', 'testimonial-slider-showcase' ),
				'default'     => 'yes',
				'separator'   => 'before',
				'description' => esc_html__( 'Switch on to display author image.', 'testimonial-slider-showcase' ),
			];

			$this->fields[] = [
				'type'        => \Elementor\Controls_Manager::SWITCHER,
				'id'          => 'tss_el_testimonial',
				'label'       => esc_html__( 'Show Testimonial Text', 'testimonial-slider-showcase' ),
				'label_on'    => esc_html__( 'On', 'testimonial-slider-showcase' ),
				'label_off'   => esc_html__( 'Off', 'testimonial-slider-showcase' ),
				'default'     => 'yes',
				'separator'   => 'before',
				'description' => esc_html__( 'Switch on to display testimonial text.', 'testimonial-slider-showcase' ),
			];

			$this->fields[] = [
				'type'        => \Elementor\Controls_Manager::SWITCHER,
				'id'          => 'tss_el_designation',
				'label'       => esc_html__( 'Show Designation', 'testimonial-slider-showcase' ),
				'label_on'    => esc_html__( 'On', 'testimonial-slider-showcase' ),
				'label_off'   => esc_html__( 'Off', 'testimonial-slider-showcase' ),
				'default'     => 'yes',
				'separator'   => 'before',
				'description' => esc_html__( 'Switch on to display author designation.', 'testimonial-slider-showcase' ),
			];

			$this->fields[] = [
				'type'        => \Elementor\Controls_Manager::SWITCHER,
				'id'          => 'tss_el_company',
				'label'       => esc_html__( 'Show Company Name', 'testimonial-slider-showcase' ),
				'label_on'    => esc_html__( 'On', 'testimonial-slider-showcase' ),
				'label_off'   => esc_html__( 'Off', 'testimonial-slider-showcase' ),
				'default'     => 'yes',
				'separator'   => 'before',
				'description' => esc_html__( 'Switch on to display company name.', 'testimonial-slider-showcase' ),
			];

			$this->fields[] = [
				'type'        => \Elementor\Controls_Manager::SWITCHER,
				'id'          => 'tss_el_location',
				'label'       => esc_html__( 'Show Location Name', 'testimonial-slider-showcase' ),
				'label_on'    => esc_html__( 'On', 'testimonial-slider-showcase' ),
				'label_off'   => esc_html__( 'Off', 'testimonial-slider-showcase' ),
				'default'     => 'yes',
				'separator'   => 'before',
				'description' => esc_html__( 'Switch on to display location name.', 'testimonial-slider-showcase' ),
			];

			$this->fields[] = [
				'type'        => \Elementor\Controls_Manager::SWITCHER,
				'id'          => 'tss_el_rating',
				'label'       => esc_html__( 'Show Rating', 'testimonial-slider-showcase' ),
				'label_on'    => esc_html__( 'On', 'testimonial-slider-showcase' ),
				'label_off'   => esc_html__( 'Off', 'testimonial-slider-showcase' ),
				'default'     => 'yes',
				'separator'   => 'before',
				'description' => esc_html__( 'Switch on to display author rating.', 'testimonial-slider-showcase' ),
			];

			$this->fields = array_merge( apply_filters( 'rttss_elementor_before_visibility_end', $this->fields ) );

			$this->fields[] = [
				'mode' => 'section_end',
			];

			return $this->fields;
		}

		/**
		 * Text Limit Section
		 *
		 * @return array
		 */
		public function textLimit() {
			$this->fields[] = [
				'mode'  => 'section_start',
				'id'    => 'tss_el_text_limit_section',
				'label' => esc_html__( 'Content Limit', 'testimonial-slider-showcase' ),
				'tab'   => $this->tab,
			];

			$this->fields[] = [
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'id'           => 'tss_el_text_limit',
				'label'        => esc_html__( 'Limit Testimonial Content?', 'testimonial-slider-showcase' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'description'  => esc_html__( 'Switch on to limit testimonial content.', 'testimonial-slider-showcase' ),
			];

			$this->fields[] = [
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'id'          => 'tss_el_testimonial_text_limit',
				'label'       => esc_html__( 'Content Limit', 'testimonial-slider-showcase' ),
				'description' => esc_html__( 'Limits the testimonial text (letter limit). Leave it blank for full text.', 'testimonial-slider-showcase' ),
				'separator'   => 'before',
				'condition'   => [ 'tss_el_text_limit' => [ 'yes' ] ],
			];

			$this->fields = array_merge( apply_filters( 'rttss_elementor_before_content_limit_end', $this->fields ) );

			$this->fields[] = [
				'mode' => 'section_end',
			];

			return $this->fields;
		}

		/**
		 * Slider Section
		 *
		 * @return array
		 */
		public function slider() {
			$this->fields[] = [
				'mode'  => 'section_start',
				'id'    => 'tss_el_slider_section',
				'label' => esc_html__( 'Slider Settings', 'testimonial-slider-showcase' ),
				'tab'   => $this->tab,
			];

			$this->fields[] = [
				'type'            => \Elementor\Controls_Manager::RAW_HTML,
				'id'              => 'tss_el_slider_control_note',
				'raw'             => sprintf(
					'<h3 class="tss-elementor-group-heading">%s</h3>',
					esc_html__( 'Controls', 'testimonial-slider-showcase' )
				),
				'content_classes' => 'elementor-panel-heading-title',
			];

			$this->fields[] = [
				'type'        => \Elementor\Controls_Manager::SWITCHER,
				'id'          => 'tss_el_slider_loop',
				'label'       => esc_html__( 'Infinite Loop', 'testimonial-slider-showcase' ),
				'label_on'    => esc_html__( 'On', 'testimonial-slider-showcase' ),
				'label_off'   => esc_html__( 'Off', 'testimonial-slider-showcase' ),
				'description' => esc_html__( 'Switch on to enable slider infinite loop.', 'testimonial-slider-showcase' ),
				'condition'   => [ 'tss_el_layout_type!' => [ 'carousel11', 'carousel12' ] ],
			];

			$this->fields[] = [
				'type'        => \Elementor\Controls_Manager::SWITCHER,
				'id'          => 'tss_el_slider_nav',
				'label'       => esc_html__( 'Navigation Arrows', 'testimonial-slider-showcase' ),
				'label_on'    => esc_html__( 'On', 'testimonial-slider-showcase' ),
				'label_off'   => esc_html__( 'Off', 'testimonial-slider-showcase' ),
				'description' => esc_html__( 'Switch on to enable slider navigation arrows.', 'testimonial-slider-showcase' ),
				'default'     => 'yes',
			];

			$this->fields[] = [
				'type'        => \Elementor\Controls_Manager::SWITCHER,
				'id'          => 'tss_el_slider_pagi',
				'label'       => esc_html__( 'Dot Pagination', 'testimonial-slider-showcase' ),
				'label_on'    => esc_html__( 'On', 'testimonial-slider-showcase' ),
				'label_off'   => esc_html__( 'Off', 'testimonial-slider-showcase' ),
				'default'     => 'yes',
				'description' => esc_html__( 'Switch on to enable slider dot pagination.', 'testimonial-slider-showcase' ),
				'condition'   => [ 'tss_el_layout_type!' => [ 'carousel11', 'carousel12' ] ],
			];

			$this->fields[] = [
				'type'        => \Elementor\Controls_Manager::SWITCHER,
				'id'          => 'tss_el_slider_auto_height',
				'label'       => esc_html__( 'Auto Height', 'testimonial-slider-showcase' ),
				'label_on'    => esc_html__( 'On', 'testimonial-slider-showcase' ),
				'label_off'   => esc_html__( 'Off', 'testimonial-slider-showcase' ),
				'description' => esc_html__( 'Switch on to enable slider dynamic height.', 'testimonial-slider-showcase' ),
			];

			$this->fields[] = [
				'type'        => \Elementor\Controls_Manager::SWITCHER,
				'id'          => 'tss_el_slider_lazy_load',
				'label'       => esc_html__( 'Image Lazy Load', 'testimonial-slider-showcase' ),
				'label_on'    => esc_html__( 'On', 'testimonial-slider-showcase' ),
				'label_off'   => esc_html__( 'Off', 'testimonial-slider-showcase' ),
				'description' => esc_html__( 'Switch on to enable slider image lazy load.', 'testimonial-slider-showcase' ),
			];

			$this->fields[] = [
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'id'          => 'tss_el_slide_speed',
				'label'       => esc_html__( 'Slide Speed (in ms)', 'testimonial-slider-showcase' ),
				'description' => esc_html__( 'Please enter the duration of transition between slides (in ms).', 'testimonial-slider-showcase' ),
				'default'     => 2000,
			];

			$this->fields[] = [
				'type'            => \Elementor\Controls_Manager::RAW_HTML,
				'id'              => 'tss_el_slider_autoplay_note',
				'raw'             => sprintf(
					'<h3 class="tss-elementor-group-heading">%s</h3>',
					esc_html__( 'Autoplay', 'testimonial-slider-showcase' )
				),
				'content_classes' => 'elementor-panel-heading-title',
			];

			$this->fields[] = [
				'type'        => \Elementor\Controls_Manager::SWITCHER,
				'id'          => 'tss_el_slide_autoplay',
				'label'       => esc_html__( 'Enable Autoplay?', 'testimonial-slider-showcase' ),
				'label_on'    => esc_html__( 'On', 'testimonial-slider-showcase' ),
				'label_off'   => esc_html__( 'Off', 'testimonial-slider-showcase' ),
				'default'     => 'yes',
				'description' => esc_html__( 'Switch on to enable slider autoplay.', 'testimonial-slider-showcase' ),
			];

			$this->fields[] = [
				'type'        => \Elementor\Controls_Manager::SWITCHER,
				'id'          => 'tss_el_pause_hover',
				'label'       => esc_html__( 'Pause on Mouse Hover?', 'testimonial-slider-showcase' ),
				'label_on'    => esc_html__( 'On', 'testimonial-slider-showcase' ),
				'label_off'   => esc_html__( 'Off', 'testimonial-slider-showcase' ),
				'description' => esc_html__( 'Switch on to enable slider autoplay pause on mouse hover.', 'testimonial-slider-showcase' ),
				'default'     => 'yes',
				'condition'   => [ 'tss_el_slide_autoplay' => 'yes' ],
			];

			$this->fields[] = [
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'id'          => 'tss_el_autoplay_timeout',
				'label'       => esc_html__( 'Autoplay Delay', 'testimonial-slider-showcase' ),
				'options'     => TSSPro()->tssSliderAutoplayDelay(),
				'default'     => '5000',
				'description' => esc_html__( 'Please select autoplay interval delay', 'testimonial-slider-showcase' ),
				'condition'   => [ 'tss_el_slide_autoplay' => 'yes' ],
			];

			$this->fields[] = [
				'mode' => 'section_end',
			];

			return $this->fields;
		}
	}
endif;
