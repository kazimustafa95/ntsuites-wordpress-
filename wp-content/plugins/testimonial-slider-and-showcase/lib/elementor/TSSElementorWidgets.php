<?php
/**
 * Elementor Widgets class.
 *
 * @package RT_TSS
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

if ( ! class_exists( 'TSSElementorWidgets' ) ) :
	/**
	 * Elementor Widgets class.
	 */
	class TSSElementorWidgets {
		/**
		 * Widgets
		 *
		 * @var object
		 */
		private static $widgets;

		/**
		 * Register
		 *
		 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
		 * @return void
		 */
		public static function register( $widgets_manager ) {
			self::includes();

			self::$widgets = apply_filters(
				'rttss_elementor_widgets',
				[
					TSSElementorGridWidget::class,
					TSSElementorSliderWidget::class,
				]
			);

			if ( empty( self::$widgets ) ) {
				return;
			}

			// Registering the widgets.
			self::registerWidgets( $widgets_manager );
		}

		/**
		 * Register widgets
		 *
		 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
		 * @return void
		 */
		private static function registerWidgets( $widgets_manager ) {
			foreach ( self::$widgets as $widget ) {
				$widgets_manager->register( new $widget() );
			}
		}

		/**
		 * Includes
		 *
		 * @return void
		 */
		private static function includes() {
			$directories = [
				'/elementor/elements/',
				'/elementor/elements/tabs/',
				'/elementor/elements/renders/',
				'/elementor/elements/sections/',
			];

			foreach ( $directories as $include ) {
				TSSPro()->loadClass( TSSPro()->incPath . $include );
			}
		}

		/**
		 * Register widget controls.
		 *
		 * Adds different control fields into the widget settings.
		 *
		 * @param array  $fields Control fields to add.
		 * @param object $obj Object in which controls are adding.
		 * @return void
		 *
		 * @access public
		 */
		public function tssAddElementorControls( $fields, $obj ) {
			foreach ( $fields as $field ) {
				if ( isset( $field['mode'] ) && 'section_start' === $field['mode'] ) {
					$id = $field['id'];
					unset( $field['id'] );
					unset( $field['mode'] );
					$obj->start_controls_section( $id, $field );
				} elseif ( isset( $field['mode'] ) && 'section_end' === $field['mode'] ) {
					$obj->end_controls_section();
				} elseif ( isset( $field['mode'] ) && 'tabs_start' === $field['mode'] ) {
					$id = $field['id'];
					unset( $field['id'] );
					unset( $field['mode'] );
					$obj->start_controls_tabs( $id );
				} elseif ( isset( $field['mode'] ) && 'tabs_end' === $field['mode'] ) {
					$obj->end_controls_tabs();
				} elseif ( isset( $field['mode'] ) && 'tab_start' === $field['mode'] ) {
					$id = $field['id'];
					unset( $field['id'] );
					unset( $field['mode'] );
					$obj->start_controls_tab( $id, $field );
				} elseif ( isset( $field['mode'] ) && 'tab_end' === $field['mode'] ) {
					$obj->end_controls_tab();
				} elseif ( isset( $field['mode'] ) && 'group' === $field['mode'] ) {
					$type          = $field['type'];
					$field['name'] = $field['id'];
					unset( $field['mode'] );
					unset( $field['type'] );
					unset( $field['id'] );
					$obj->add_group_control( $type, $field );
				} elseif ( isset( $field['mode'] ) && 'responsive' === $field['mode'] ) {
					$id = $field['id'];
					unset( $field['id'] );
					unset( $field['mode'] );
					$obj->add_responsive_control( $id, $field );
				} else {
					$id = $field['id'];
					unset( $field['id'] );
					$obj->add_control( $id, $field );
				}
			}
		}

		/**
		 * Register widget tabs with controls.
		 *
		 * @param array $tabs Tabs to register.
		 * @param array $controls Controls to register.
		 * @return array
		 *
		 * @access public
		 */
		public function tssInitTabs( $tabs, $controls ) {
			foreach ( $tabs as $tab ) {
				if ( method_exists( $tab, 'register' ) ) {
					$controls = array_merge( $controls, $tab::register() );
				}
			}

			return $controls;
		}

		/**
		 * Elementor Promotional section controls.
		 *
		 * @param array $fields Elementor Controls.
		 * @return array
		 *
		 * @access public
		 */
		public function tssContentPromotion( $fields ) {
			if ( ! function_exists( 'rttsp' ) ) {
				$fields[] = [
					'mode'  => 'section_start',
					'id'    => 'tss_el_pro_alert',
					'label' => sprintf(
						'<span style="color: #f54">%s</span>',
						esc_html__( 'Go Premium for More Features', 'testimonial-slider-showcase' )
					),
					'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
				];

				$fields[] = [
					'type' => \Elementor\Controls_Manager::RAW_HTML,
					'id'   => 'tss_el_get_pro',
					'raw'  => '<div class="elementor-nerd-box"><div class="elementor-nerd-box-title" style="margin-top: 0; margin-bottom: 20px;">Unlock more possibilities</div><div class="elementor-nerd-box-message"><span class="pro-feature" style="font-size: 13px;"> Get the <a href="' . esc_url( TSSPro()->pro_version_link() ) . '" target="_blank" style="color: #f54">Pro version</a> for more stunning layouts and customization options.</span></div><a class="elementor-nerd-box-link elementor-button elementor-button-default elementor-button-go-pro" href="' . esc_url( TSSPro()->pro_version_link() ) . '" target="_blank">Get Pro</a></div>',
				];

				$fields[] = [
					'mode' => 'section_end',
				];
			}

			return $fields;
		}
	}

endif;
