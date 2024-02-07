<?php
/**
 * Elementor Grid Widget Class.
 *
 * @package RT_TSS
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Elementor Grid Widget Class.
 */
class TSSElementorGridWidget extends \Elementor\Widget_Base {
	/**
	 * Controls
	 *
	 * @var array
	 */
	private $rtControls = [];

	/**
	 * Widget name
	 *
	 * @return string
	 */
	public function get_name() {
		return 'rt-testimonial-grid';
	}

	/**
	 * Widget title
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'Testimonial Grid Layout', 'testimonial-slider-showcase' );
	}

	/**
	 * Widget icon
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-gallery-grid tss-element';
	}

	/**
	 * Widget scripts
	 *
	 * @return array
	 */
	public function get_script_depends() {
		if ( \Elementor\Plugin::$instance->preview->is_preview_mode() || \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			return [ 'tss-image-load', 'tss-isotope', 'tss' ];
		}

		return [];
	}

	/**
	 * Widget styles
	 *
	 * @return array
	 */
	public function get_style_depends() {
		return [ 'tss', 'tss-fontello', 'dashicons' ];
	}

	/**
	 * Widget category
	 *
	 * @return array
	 */
	public function get_categories() {
		return [ 'tss-elementor-widgets' ];
	}

	/**
	 * Register controls.
	 *
	 * @return void
	 */
	protected function register_controls() {
		$this->controlTabs();

		if ( empty( $this->rtControls ) ) {
			return;
		}

		TSSPro()->tssAddElementorControls( $this->rtControls, $this );
	}

	/**
	 * Register controls.
	 *
	 * @return void
	 */
	private function controlTabs() {
		$tabs = [
			TSSElementorGridContent::class,
			TSSElementorGridStyle::class,
		];

		$this->rtControls = TSSPro()->tssInitTabs( $tabs, $this->rtControls );
	}

	/**
	 * Render.
	 *
	 * @return void
	 */
	protected function render() {
		$data = $this->get_settings_for_display();
		$html = new TSSElementorRender();

		TSSPro()->printHtml( $html->testimonialRender( $data ) );
	}
}
