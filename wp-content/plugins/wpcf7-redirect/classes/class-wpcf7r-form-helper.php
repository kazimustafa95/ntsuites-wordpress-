<?php
/**
 * The main class that manages the plugin.
 *
 * @package wpcf7r
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class WPCF7r_Form_Helper - Adds contact form scripts and actions
 */
class WPCF7r_Form_Helper {

	/**
	 * The plugin url.
	 *
	 * @var string
	 */
	public $plugin_url = '';

	/**
	 * The plugin assets js lib url.
	 *
	 * @var string
	 */
	public $assets_js_lib = '';

	/**
	 * The plugin assets js url.
	 *
	 * @var string
	 */
	public $assets_js_url = '';

	/**
	 * The plugin assets css url.
	 *
	 * @var string
	 */
	public $assets_css_url = '';

	/**
	 * The plugin build js url.
	 *
	 * @var string
	 */
	public $build_js_url = '';

	/**
	 * The plugin build css url.
	 *
	 * @var string
	 */
	public $build_css_url = '';

	/**
	 * The plugin extensions.
	 *
	 * @var string
	 */
	public $extensions = '';

	/**
	 * Class Constructor
	 */
	public function __construct() {

		$this->plugin_url     = WPCF7_PRO_REDIRECT_BASE_URL;
		$this->assets_js_lib  = WPCF7_PRO_REDIRECT_BASE_URL . 'assets/lib/';
		$this->assets_js_url  = WPCF7_PRO_REDIRECT_BASE_URL . 'assets/js/';
		$this->assets_css_url = WPCF7_PRO_REDIRECT_BASE_URL . 'assets/css/';
		$this->build_js_url   = WPCF7_PRO_REDIRECT_BASE_URL . 'build/js/';
		$this->build_css_url  = WPCF7_PRO_REDIRECT_BASE_URL . 'build/css/';
		$this->extensions     = wpcf7_get_extensions();

		$this->add_actions();
	}

	/**
	 * Add Actions
	 */
	private function add_actions() {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'wpcf7_editor_panels', array( $this, 'add_panel' ) );
		add_action( 'wpcf7_after_save', array( $this, 'store_meta' ) );
		// add contact form scripts.
		add_action( 'wp_enqueue_scripts', array( $this, 'front_end_scripts' ) );
		// add contact form scripts for admin panel.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_backend' ) );
	}

	/**
	 * Only load scripts when contact form instance is created
	 */
	public function front_end_scripts() {

		wp_register_style( 'wpcf7-redirect-script-frontend', $this->build_css_url . 'wpcf7-redirect-frontend.min.css', array(), '1.1' );
		wp_enqueue_style( 'wpcf7-redirect-script-frontend' );

		wp_register_script( 'wpcf7-redirect-script', $this->build_js_url . 'wpcf7r-fe.js', array( 'jquery' ), '1.1', true );
		wp_enqueue_script( 'wpcf7-redirect-script' );
		wp_localize_script( 'wpcf7-redirect-script', 'wpcf7r', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );

		// Load active extensions scripts and styles.
		$installed_extensions = wpcf7r_get_available_actions();

		foreach ( $installed_extensions as $installed_extension ) {
			if ( method_exists( $installed_extension['handler'], 'enqueue_frontend_scripts' ) ) {
				call_user_func( array( $installed_extension['handler'], 'enqueue_frontend_scripts' ) );
			}
		}

		// Add support for other plugins.
		do_action( 'wpcf7_redirect_enqueue_frontend', $this );
	}

	/**
	 * Check if the current page is the plugin settings page
	 */
	public function is_wpcf7_settings_page() {
		return isset( $_GET['page'] ) && 'wpc7_redirect' === $_GET['page'];
	}

	/**
	 * Check if the current admin post type is a lead post type.
	 *
	 * @return boolean
	 */
	public function is_wpcf7_lead_page() {
		return 'wpcf7r_leads' === get_post_type();
	}

	/**
	 * Check if the current page is the contact form edit screen
	 */
	public function is_wpcf7_edit() {
		return wpcf7r_is_wpcf7_edit();
	}

	/**
	 * Load plugin textdomain.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'wpcf7-redirect', false, basename( __DIR__ ) . '/lang' );
	}

	/**
	 * Enqueue theme styles and scripts - back-end
	 */
	public function enqueue_backend() {

		if ( $this->is_wpcf7_edit() || $this->is_wpcf7_settings_page() || $this->is_wpcf7_lead_page() ) {

			wp_enqueue_style( 'admin-build', $this->build_css_url . 'wpcf7-redirect-backend.css', array(), WPCF7_PRO_REDIRECT_PLUGIN_VERSION );

			wp_enqueue_script( 'admin-build-js', $this->build_js_url . 'wpcf7-redirect-backend-script.js', array(), WPCF7_PRO_REDIRECT_PLUGIN_VERSION, true );
			wp_enqueue_script(
				array(
					'jquery-ui-core',
					'jquery-ui-sortable',
					'wp-color-picker',
				)
			);

			// Load active extensions scripts and styles.
			$installed_extensions = wpcf7r_get_available_actions();

			foreach ( $installed_extensions as $installed_extension ) {
				if ( method_exists( $installed_extension['handler'], 'enqueue_backend_scripts' ) ) {
					call_user_func( array( $installed_extension['handler'], 'enqueue_backend_scripts' ) );
				}
			}

			// add support for other plugins.
			do_action( 'wpcf_7_redirect_admin_scripts', $this );
		}
	}

	/**
	 * Store form data.
	 *
	 * @param [object] $cf7  -contact form object.
	 */
	public function store_meta( $cf7 ) {

		$form = get_cf7r_form( $cf7->id() );
		$form->store_meta( $cf7 );
	}

	/**
	 * Adds a tab to the editor on the form edit page
	 *
	 * @param array $panels An array of panels. Each panel has a callback function.
	 */
	public function add_panel( $panels ) {

		// Disable plugin functionality for old contact form 7 installations.

		if ( wpcf7_get_cf7_ver() > 4.8 ) {

			$panels['redirect-panel'] = array(
				'title'    => __( 'Actions', 'wpcf7-redirect' ),
				'callback' => array( $this, 'create_panel_inputs' ),
			);

		}

		return $panels;
	}

	/**
	 * Get the default fields
	 */
	public static function get_plugin_default_fields() {

		return array(
			array(
				'name' => 'redirect_type',
				'type' => 'text',
			),
		);
	}

	/**
	 * Create the panel inputs.
	 *
	 * @param object $cf7 - Contact form 7 post object.
	 */
	public function create_panel_inputs( $cf7 ) {

		$form = get_cf7r_form( $cf7->id() );

		$form->init();
	}

	/**
	 * Returns an html for displaying a link to the form.
	 *
	 * @param [int] $form_id - the if of the contact form 7 post.
	 * @return [string] - a link to the form edit screen.
	 */
	public static function get_cf7_link_html( $form_id ) {
		$form_post  = get_post( $form_id );
		$form_title = get_the_title( $form_id );
		$link       = get_edit_post_link( $form_id );

		if ( $form_post ) {
			return sprintf( "<a href='%s' target='_blank'>%s</a>", $link, $form_title );
		}

		return __( 'This form no longer exists', 'wpcf7-redirect' );
	}

}
