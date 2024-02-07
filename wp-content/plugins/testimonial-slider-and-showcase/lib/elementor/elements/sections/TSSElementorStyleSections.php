<?php
/**
 * Style Section Class.
 *
 * @package RT_TSS
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

if ( ! class_exists( 'TSSElementorStyleSections' ) ) :
	/**
	 * Style Section Class.
	 */
	class TSSElementorStyleSections {
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
		private $tab = \Elementor\Controls_Manager::TAB_STYLE;

		/**
		 * Colors Section
		 *
		 * @param array $condition Condition.
		 * @return array
		 */
		public function colors( $condition = null ) {
			$this->fields[] = [
				'mode'  => 'section_start',
				'id'    => 'tss_el_color_section',
				'label' => esc_html__( 'Colors', 'testimonial-slider-showcase' ),
				'tab'   => $this->tab,
			];

			$this->fields[] = [
				'type'            => \Elementor\Controls_Manager::RAW_HTML,
				'id'              => 'tss_el_color_note',
				'raw'             => sprintf(
					'<h3 class="tss-elementor-group-heading">%s</h3>',
					esc_html__( 'Color Scheme', 'testimonial-slider-showcase' )
				),
				'content_classes' => 'elementor-panel-heading-title',
			];

			$this->fields[] = [
				'type'      => \Elementor\Controls_Manager::COLOR,
				'id'        => 'tss_el_primary_color',
				'label'     => esc_html__( 'Quote Icon', 'testimonial-slider-showcase' ),
				'selectors' => [
					'{{WRAPPER}} .tss-wrapper .tss-isotope1 .profile-img-wrapper:after, {{WRAPPER}} .tss-wrapper .tss-layout9 .profile-img-wrapper:after, {{WRAPPER}} .tss-wrapper .tss-isotope4 .profile-img-wrapper:after, {{WRAPPER}} .tss-wrapper .tss-carousel9 .profile-img-wrapper:after' => 'background: {{VALUE}}',
					'{{WRAPPER}} .tss-wrapper .item-content-wrapper:before, {{WRAPPER}} .tss-wrapper .item-content-wrapper:before, {{WRAPPER}} .tss-wrapper .single-item-wrapper:before' => 'color: {{VALUE}}',
				],
			];

			$this->fields[] = [
				'type'      => \Elementor\Controls_Manager::COLOR,
				'id'        => 'tss_el_author_name_color',
				'label'     => esc_html__( 'Author Name', 'testimonial-slider-showcase' ),
				'selectors' => [
					'{{WRAPPER}} .tss-wrapper .single-item-wrapper h3.author-name, {{WRAPPER}} .tss-wrapper .single-item-wrapper h3.author-name a' => 'color: {{VALUE}}',
				],
			];

			$this->fields[] = [
				'type'      => \Elementor\Controls_Manager::COLOR,
				'id'        => 'tss_el_author_bio_color',
				'label'     => esc_html__( 'Author Bio', 'testimonial-slider-showcase' ),
				'selectors' => [
					'{{WRAPPER}} .tss-wrapper .single-item-wrapper h4.author-bio' => 'color: {{VALUE}}',
				],
			];

			$this->fields[] = [
				'type'      => \Elementor\Controls_Manager::COLOR,
				'id'        => 'tss_el_rating_color',
				'label'     => esc_html__( 'Rating', 'testimonial-slider-showcase' ),
				'selectors' => [
					'{{WRAPPER}} .tss-wrapper .single-item-wrapper .rating-wrapper span.dashicons' => 'color: {{VALUE}}',
				],
			];

			$this->fields[] = [
				'type'      => \Elementor\Controls_Manager::COLOR,
				'id'        => 'tss_el_testimonial_text_color',
				'label'     => esc_html__( 'Testimonial Text', 'testimonial-slider-showcase' ),
				'selectors' => [
					'{{WRAPPER}} .tss-wrapper .single-item-wrapper .item-content' => 'color: {{VALUE}}',
				],
			];

			$this->fields = array_merge( apply_filters( 'rttss_elementor_style_color_scheme', $this->fields ) );

			$this->buttonColors( $condition );

			$this->fields = array_merge( apply_filters( 'rttss_elementor_before_colors_end', $this->fields ) );

			$this->fields[] = [
				'mode' => 'section_end',
			];

			return $this->fields;
		}

		/**
		 * Button Colors.
		 *
		 * @param array $condition Condition.
		 * @return array
		 */
		public function buttonColors( $condition = null ) {
			$this->fields[] = [
				'type'            => \Elementor\Controls_Manager::RAW_HTML,
				'id'              => 'tss_el_button_note',
				'raw'             => sprintf(
					'<h3 class="tss-elementor-group-heading">%s</h3>',
					esc_html__( 'Button Colors', 'testimonial-slider-showcase' )
				),
				'separator'       => 'before',
				'content_classes' => 'elementor-panel-heading-title',
				'conditions'      => $this->buttonCondition( $condition ),
			];

			$this->fields[] = [
				'mode'       => 'tabs_start',
				'id'         => 'tss_el_button_colors',
				'conditions' => $this->buttonCondition( $condition ),
			];

			$this->fields[] = [
				'mode'       => 'tab_start',
				'id'         => 'tss_el_button_colors_normal',
				'label'      => esc_html__( 'Normal', 'testimonial-slider-showcase' ),
				'conditions' => $this->buttonCondition( $condition ),
			];

			$this->fields[] = [
				'type'       => \Elementor\Controls_Manager::COLOR,
				'id'         => 'tss_el_button_bg_color',
				'label'      => esc_html__( 'Background Color', 'testimonial-slider-showcase' ),
				'selectors'  => [
					'{{WRAPPER}} .tss-wrapper .tss-utility button, {{WRAPPER}} .tss-wrapper .tss-carousel-main .swiper-arrow, {{WRAPPER}} .tss-wrapper .tss-carousel-main .swiper-pagination-bullet, {{WRAPPER}} .tss-wrapper .tss-carousel .swiper-arrow, {{WRAPPER}} .tss-wrapper .tss-carousel .swiper-pagination-bullet, {{WRAPPER}} .tss-wrapper .tss-utility .rt-button, {{WRAPPER}} .tss-wrapper .tss-pagination ul.pagination-list li a, {{WRAPPER}} .tss-wrapper .tss-isotope-button-wrapper .rt-iso-button' => 'background-color: {{VALUE}}',
				],
				'conditions' => $this->buttonCondition( $condition ),

			];

			$this->fields[] = [
				'type'       => \Elementor\Controls_Manager::COLOR,
				'id'         => 'tss_el_button_text_color',
				'label'      => esc_html__( 'Text Color', 'testimonial-slider-showcase' ),
				'selectors'  => [
					'{{WRAPPER}} .tss-wrapper .tss-pagination ul.pagination-list li a, {{WRAPPER}} .tss-wrapper .tss-utility .rt-button, {{WRAPPER}} .tss-carousel-main .swiper-arrow > i, {{WRAPPER}} .tss-carousel .swiper-arrow > i, {{WRAPPER}} .tss-wrapper .tss-utility .rt-button, {{WRAPPER}} .tss-wrapper .tss-isotope-button-wrapper .rt-iso-button' => 'color: {{VALUE}}',
				],
				'conditions' => $this->buttonCondition( $condition ),
			];

			$this->fields[] = [
				'type'       => \Elementor\Group_Control_Border::get_type(),
				'id'         => 'tss_el_button_border',
				'mode'       => 'group',
				'label'      => esc_html__( 'Border', 'testimonial-slider-showcase' ),
				'selector'   => '{{WRAPPER}} .tss-wrapper .tss-pagination ul.pagination-list li.active span, {{WRAPPER}} .tss-wrapper .tss-pagination ul.pagination-list li a, {{WRAPPER}} .tss-wrapper .tss-carousel-main .swiper-arrow, {{WRAPPER}} .tss-wrapper .tss-carousel .swiper-arrow, {{WRAPPER}} .tss-wrapper .tss-utility .rt-button, {{WRAPPER}} .tss-wrapper .tss-utility .rt-button, {{WRAPPER}} .tss-wrapper .tss-isotope-button-wrapper .rt-iso-button',
				'conditions' => $this->buttonCondition( $condition ),
			];

			$this->fields[] = [
				'mode'       => 'tab_end',
				'conditions' => $this->buttonCondition( $condition ),
			];

			$this->fields[] = [
				'mode'       => 'tab_start',
				'id'         => 'tss_el_button_colors_hover',
				'label'      => esc_html__( 'Hover', 'testimonial-slider-showcase' ),
				'conditions' => $this->buttonCondition( $condition ),
			];

			$this->fields[] = [
				'type'       => \Elementor\Controls_Manager::COLOR,
				'id'         => 'tss_el_button_bg_color_hover',
				'label'      => esc_html__( 'Background Color', 'testimonial-slider-showcase' ),
				'selectors'  => [
					'{{WRAPPER}} .tss-wrapper .tss-carousel-main .swiper-arrow:hover, {{WRAPPER}} .tss-wrapper .tss-carousel-main .swiper-pagination-bullet:hover, {{WRAPPER}} .tss-wrapper .tss-carousel .swiper-arrow:hover, {{WRAPPER}} .tss-wrapper .tss-carousel .swiper-pagination-bullet:hover, {{WRAPPER}} .tss-wrapper .tss-utility button:hover, {{WRAPPER}} .tss-wrapper .tss-utility .rt-button:hover, {{WRAPPER}} .tss-wrapper .tss-pagination ul.pagination-list li a:hover, {{WRAPPER}} .tss-wrapper .tss-isotope-button-wrapper .rt-iso-button:hover' => 'background-color: {{VALUE}}',
				],
				'conditions' => $this->buttonCondition( $condition ),
			];

			$this->fields[] = [
				'type'       => \Elementor\Controls_Manager::COLOR,
				'id'         => 'tss_el_button_text_color_hover',
				'label'      => esc_html__( 'Text Color', 'testimonial-slider-showcase' ),
				'selectors'  => [
					'{{WRAPPER}} .tss-wrapper .tss-pagination ul.pagination-list li a:hover, {{WRAPPER}} .tss-wrapper .tss-isotope-button-wrapper .rt-iso-button:hover, {{WRAPPER}} .tss-wrapper .tss-utility .rt-button:hover, {{WRAPPER}} .tss-carousel-main .swiper-arrow:hover > i, {{WRAPPER}} .tss-carousel .swiper-arrow:hover > i' => 'color: {{VALUE}}',
				],
				'conditions' => $this->buttonCondition( $condition ),
			];

			$this->fields[] = [
				'type'       => \Elementor\Controls_Manager::COLOR,
				'id'         => 'tss_el_button_border_color_hover',
				'label'      => esc_html__( 'Border Color', 'testimonial-slider-showcase' ),
				'selectors'  => [
					'{{WRAPPER}} .tss-wrapper .tss-pagination ul.pagination-list li a:hover, {{WRAPPER}} .tss-wrapper .tss-carousel-main .swiper-arrow:hover, {{WRAPPER}} .tss-wrapper .tss-carousel .swiper-arrow:hover, {{WRAPPER}} .tss-wrapper .tss-utility .rt-button:hover, {{WRAPPER}} .tss-wrapper .tss-utility .rt-button:hover, {{WRAPPER}} .tss-wrapper .tss-isotope-button-wrapper .rt-iso-button:hover' => 'border-color: {{VALUE}}',
				],
				'conditions' => $this->buttonCondition( $condition ),
			];

			$this->fields[] = [
				'mode'       => 'tab_end',
				'conditions' => $this->buttonCondition( $condition ),
			];

			$this->fields[] = [
				'mode'       => 'tab_start',
				'id'         => 'tss_el_button_colors_active',
				'label'      => esc_html__( 'Active', 'testimonial-slider-showcase' ),
				'conditions' => $this->buttonCondition( $condition ),
			];

			$this->fields[] = [
				'type'       => \Elementor\Controls_Manager::COLOR,
				'id'         => 'tss_el_button_bg_color_active',
				'label'      => esc_html__( 'Background Color', 'testimonial-slider-showcase' ),
				'selectors'  => [
					'{{WRAPPER}} .tss-wrapper .tss-carousel-main .swiper-pagination-bullet-active, {{WRAPPER}} .tss-wrapper .tss-carousel .swiper-pagination-bullet-active, {{WRAPPER}} .tss-wrapper .tss-pagination ul.pagination-list li.active span, {{WRAPPER}} .tss-wrapper .tss-isotope-button-wrapper .rt-iso-button.selected' => 'background-color: {{VALUE}}',
				],
				'conditions' => $this->buttonCondition( $condition ),
			];

			$this->fields[] = [
				'type'       => \Elementor\Controls_Manager::COLOR,
				'id'         => 'tss_el_button_text_color_active',
				'label'      => esc_html__( 'Text Color', 'testimonial-slider-showcase' ),
				'selectors'  => [
					'{{WRAPPER}} .tss-wrapper .slick-dots li.slick-active button, {{WRAPPER}} .tss-wrapper .tss-pagination ul.pagination-list li.active span, {{WRAPPER}} .tss-wrapper .tss-isotope-button-wrapper .rt-iso-button.selected' => 'color: {{VALUE}}',
				],
				'conditions' => $this->buttonCondition( $condition ),
			];

			$this->fields[] = [
				'type'       => \Elementor\Controls_Manager::COLOR,
				'id'         => 'tss_el_button_border_color_active',
				'label'      => esc_html__( 'Border Color', 'testimonial-slider-showcase' ),
				'selectors'  => [
					'{{WRAPPER}} .tss-wrapper .tss-carousel-main .swiper-pagination-bullet-active, {{WRAPPER}} .tss-wrapper .tss-carousel .swiper-pagination-bullet-active, {{WRAPPER}} .tss-wrapper .tss-pagination ul.pagination-list li.active span, {{WRAPPER}} .tss-wrapper .tss-isotope-button-wrapper .rt-iso-button.selected' => 'border-color: {{VALUE}}',
				],
				'conditions' => $this->buttonCondition( $condition ),
			];

			$this->fields[] = [
				'mode'       => 'tab_end',
				'conditions' => $this->buttonCondition( $condition ),
			];

			$this->fields[] = [
				'mode'       => 'tabs_end',
				'conditions' => $this->buttonCondition( $condition ),
			];

			return $this->fields;
		}

		/**
		 * Typography Section
		 *
		 * @return array
		 */
		public function typography() {
			$this->fields[] = [
				'mode'  => 'section_start',
				'id'    => 'tss_el_typography_section',
				'label' => esc_html__( 'Typography', 'testimonial-slider-showcase' ),
				'tab'   => $this->tab,
			];

			$this->fields[] = [
				'type'            => \Elementor\Controls_Manager::RAW_HTML,
				'id'              => 'tss_el_author_name_note',
				'raw'             => sprintf(
					'<h3 class="tss-elementor-group-heading">%s</h3>',
					esc_html__( 'Author Name', 'testimonial-slider-showcase' )
				),
				'content_classes' => 'elementor-panel-heading-title',
			];

			$this->fields[] = [
				'mode'     => 'group',
				'id'       => 'tss_el_author_name_typography',
				'type'     => \Elementor\Group_Control_Typography::get_type(),
				'selector' => '{{WRAPPER}} .tss-wrapper .single-item-wrapper h3.author-name, {{WRAPPER}} .tss-wrapper .single-item-wrapper h3.author-name a',
			];

			$this->fields[] = [
				'mode'      => 'responsive',
				'id'        => 'tss_el_author_name_alignment',
				'type'      => \Elementor\Controls_Manager::CHOOSE,
				'label'     => esc_html__( 'Alignment', 'testimonial-slider-showcase' ),
				'options'   => [
					'left'   => [
						'title' => esc_html__( 'Left', 'testimonial-slider-showcase' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'testimonial-slider-showcase' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right'  => [
						'title' => esc_html__( 'Right', 'testimonial-slider-showcase' ),
						'icon'  => 'eicon-text-align-right',
					],
				],
				'default'   => 'center',
				'selectors' => [
					'{{WRAPPER}} .tss-wrapper .single-item-wrapper h3.author-name, {{WRAPPER}} .tss-wrapper .single-item-wrapper h3.author-name a' => 'text-align: {{VALUE}}',
				],
			];

			$this->fields[] = [
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'id'         => 'tss_el_author_name_margin',
				'mode'       => 'responsive',
				'label'      => esc_html__( 'Spacing', 'testimonial-slider-showcase' ),
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .tss-wrapper .single-item-wrapper h3.author-name, {{WRAPPER}} .tss-wrapper .single-item-wrapper h3.author-name a' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			];

			$this->fields[] = [
				'type'            => \Elementor\Controls_Manager::RAW_HTML,
				'id'              => 'tss_el_author_bio_note',
				'raw'             => sprintf(
					'<h3 class="tss-elementor-group-heading">%s</h3>',
					esc_html__( 'Author Bio', 'testimonial-slider-showcase' )
				),
				'separator'       => 'before',
				'content_classes' => 'elementor-panel-heading-title',
			];

			$this->fields[] = [
				'mode'     => 'group',
				'id'       => 'tss_el_author_bio_typography',
				'type'     => \Elementor\Group_Control_Typography::get_type(),
				'selector' => '{{WRAPPER}} .tss-wrapper .single-item-wrapper h4.author-bio',
			];

			$this->fields[] = [
				'mode'      => 'responsive',
				'id'        => 'tss_el_author_bio_alignment',
				'type'      => \Elementor\Controls_Manager::CHOOSE,
				'label'     => esc_html__( 'Alignment', 'testimonial-slider-showcase' ),
				'options'   => [
					'left'   => [
						'title' => esc_html__( 'Left', 'testimonial-slider-showcase' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'testimonial-slider-showcase' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right'  => [
						'title' => esc_html__( 'Right', 'testimonial-slider-showcase' ),
						'icon'  => 'eicon-text-align-right',
					],
				],
				'default'   => 'center',
				'selectors' => [
					'{{WRAPPER}} .tss-wrapper .single-item-wrapper h4.author-bio' => 'text-align: {{VALUE}}',
				],
			];

			$this->fields[] = [
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'id'         => 'tss_el_author_bio_margin',
				'mode'       => 'responsive',
				'label'      => esc_html__( 'Spacing', 'testimonial-slider-showcase' ),
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .tss-wrapper .single-item-wrapper h4.author-bio' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			];

			$this->fields[] = [
				'type'            => \Elementor\Controls_Manager::RAW_HTML,
				'id'              => 'tss_el_rating_note',
				'raw'             => sprintf(
					'<h3 class="tss-elementor-group-heading">%s</h3>',
					esc_html__( 'Rating', 'testimonial-slider-showcase' )
				),
				'separator'       => 'before',
				'content_classes' => 'elementor-panel-heading-title',
			];

			$this->fields[] = [
				'mode'     => 'group',
				'id'       => 'tss_el_rating_typography',
				'type'     => \Elementor\Group_Control_Typography::get_type(),
				'selector' => '{{WRAPPER}} .tss-wrapper .single-item-wrapper .rating-wrapper span.dashicons',
				'exclude'  => [ 'font_family', 'letter_spacing', 'text_transform', 'font_style', 'text_decoration', 'line_height' ],
			];

			$this->fields[] = [
				'mode'      => 'responsive',
				'id'        => 'tss_el_rating_alignment',
				'type'      => \Elementor\Controls_Manager::CHOOSE,
				'label'     => esc_html__( 'Alignment', 'testimonial-slider-showcase' ),
				'options'   => [
					'left'   => [
						'title' => esc_html__( 'Left', 'testimonial-slider-showcase' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'testimonial-slider-showcase' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right'  => [
						'title' => esc_html__( 'Right', 'testimonial-slider-showcase' ),
						'icon'  => 'eicon-text-align-right',
					],
				],
				'default'   => 'center',
				'selectors' => [
					'{{WRAPPER}} .tss-wrapper .single-item-wrapper .rating-wrapper' => 'text-align: {{VALUE}}',
				],
			];

			$this->fields[] = [
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'id'         => 'tss_el_rating_spacing',
				'mode'       => 'responsive',
				'label'      => esc_html__( 'Icon Spacing', 'testimonial-slider-showcase' ),
				'size_units' => [ 'px', '%', 'em' ],
				'range'      => [
					'px' => [ 'max' => 100 ],
					'%'  => [ 'max' => 100 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .tss-wrapper .single-item-wrapper .rating-wrapper span.dashicons' => 'margin-left: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}}  .tss-wrapper .single-item-wrapper .rating-wrapper span.dashicons' => 'margin-right: {{SIZE}}{{UNIT}};',
				],
			];

			$this->fields[] = [
				'type'               => \Elementor\Controls_Manager::DIMENSIONS,
				'id'                 => 'tss_el_rating_margin',
				'mode'               => 'responsive',
				'label'              => esc_html__( 'Spacing', 'testimonial-slider-showcase' ),
				'size_units'         => [ 'px', '%', 'em' ],
				'allowed_dimensions' => [ 'top', 'bottom' ],
				'selectors'          => [
					'{{WRAPPER}} .tss-wrapper .single-item-wrapper .rating-wrapper' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			];

			$this->fields[] = [
				'type'            => \Elementor\Controls_Manager::RAW_HTML,
				'id'              => 'tss_el_testimonial_text_note',
				'raw'             => sprintf(
					'<h3 class="tss-elementor-group-heading">%s</h3>',
					esc_html__( 'Testimonial Text', 'testimonial-slider-showcase' )
				),
				'separator'       => 'before',
				'content_classes' => 'elementor-panel-heading-title',
			];

			$this->fields[] = [
				'mode'     => 'group',
				'id'       => 'tss_el_testimonial_text_typography',
				'type'     => \Elementor\Group_Control_Typography::get_type(),
				'selector' => '{{WRAPPER}} .tss-wrapper .single-item-wrapper .item-content',
			];

			$this->fields[] = [
				'mode'      => 'responsive',
				'id'        => 'tss_el_testimonial_text_alignment',
				'type'      => \Elementor\Controls_Manager::CHOOSE,
				'label'     => esc_html__( 'Alignment', 'testimonial-slider-showcase' ),
				'options'   => [
					'left'   => [
						'title' => esc_html__( 'Left', 'testimonial-slider-showcase' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'testimonial-slider-showcase' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right'  => [
						'title' => esc_html__( 'Right', 'testimonial-slider-showcase' ),
						'icon'  => 'eicon-text-align-right',
					],
				],
				'default'   => 'left',
				'selectors' => [
					'{{WRAPPER}} .tss-wrapper .single-item-wrapper .item-content' => 'text-align: {{VALUE}}',
				],
			];

			$this->fields[] = [
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'id'         => 'tss_el_testimonial_text_margin',
				'mode'       => 'responsive',
				'label'      => esc_html__( 'Spacing', 'testimonial-slider-showcase' ),
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .tss-wrapper .single-item-wrapper .item-content' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			];

			$this->fields = array_merge( apply_filters( 'rttss_elementor_typography_end', $this->fields ) );

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
				'id'    => 'tss_el_image_styles_section',
				'label' => esc_html__( 'Image Style', 'testimonial-slider-showcase' ),
				'tab'   => $this->tab,
			];

			$this->fields[] = [
				'type'     => \Elementor\Group_Control_Border::get_type(),
				'id'       => 'tss_el_image_border',
				'mode'     => 'group',
				'label'    => esc_html__( 'Border', 'testimonial-slider-showcase' ),
				'selector' => '{{WRAPPER}} .tss-wrapper .rt-responsive-img',
			];

			$this->fields = array_merge( apply_filters( 'rttss_elementor_before_image_border', $this->fields ) );

			$this->fields[] = [
				'id'         => 'tss_el_grid_image_border-radius',
				'mode'       => 'responsive',
				'label'      => esc_html__( 'Border Radius', 'testimonial-slider-showcase' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'default'    => [
					'top'      => '50',
					'right'    => '50',
					'bottom'   => '50',
					'left'     => '50',
					'unit'     => '%',
					'isLinked' => true,
				],
				'separator'  => 'before',
				'selectors'  => [
					'{{WRAPPER}} .tss-wrapper .rt-responsive-img, {{WRAPPER}} .tss-wrapper .tss-layout9 .profile-img-wrapper:before, {{WRAPPER}} .tss-wrapper .tss-isotope4 .profile-img-wrapper:before, {{WRAPPER}} .tss-wrapper .tss-carousel9 .profile-img-wrapper:before, {{WRAPPER}} .tss-wrapper .tss-layout9 .profile-img-wrapper:after, {{WRAPPER}} .tss-wrapper .tss-isotope4 .profile-img-wrapper:after, {{WRAPPER}} .tss-wrapper .tss-carousel9 .profile-img-wrapper:after' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			];

			$this->fields[] = [
				'mode' => 'section_end',
			];

			return $this->fields;
		}

		/**
		 * Grid Colors Section
		 *
		 * @return array
		 */
		public function gridColors() {
			$this->colors( 'with-pagination' );

			return $this->fields;
		}

		/**
		 * Common Colors Section.
		 *
		 * @return array
		 */
		public function commonColors() {
			$this->colors();

			return $this->fields;
		}

		/**
		 * Button Controls Condition.
		 *
		 * @param array $condition Condition.
		 * @return array
		 */
		private function buttonCondition( $condition = null ) {
			$conditions = [];

			$conditions[] = [
				'relation' => 'or',
			];

			if ( 'with-pagination' === $condition ) {
				$conditions['terms'][] = [
					'name'     => 'tss_el_pagination',
					'operator' => '==',
					'value'    => 'yes',
				];
			} else {
				$conditions['terms'] = [];
			}

			return $conditions;
		}
	}
endif;
