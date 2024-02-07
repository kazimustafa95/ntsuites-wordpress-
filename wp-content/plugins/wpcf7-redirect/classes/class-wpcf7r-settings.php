<?php

/**
 * Class WPCF7r_Settings file.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Contact form 7 Redirect Settings panel
 */
class WPCF7r_Settings {

	/**
	 *
	 *
	 * @var [type]
	 */
	public $product_url = WPCF7_PRO_REDIRECT_PLUGIN_PAGE_URL;

	/**
	 * Page slug.
	 *
	 * @var string
	 */
	private $page_slug;

	/**
	 * Fields array.
	 *
	 * @var [type]
	 */
	public $fields;

	public function __construct() {
		$this->page_slug = 'wpc7_redirect';

		add_action( 'admin_menu', array( $this, 'create_plugin_settings_page' ) );
		add_action( 'admin_init', array( $this, 'wpcf7r_register_options' ) );
		add_filter( 'plugin_row_meta', array( $this, 'register_plugin_links' ), 10, 2 );
	}

	/**
	 * Register plugin options
	 */
	public function wpcf7r_register_options() {
		$this->fields = array();

		$this->add_settings_section();

		foreach ( $this->fields as $field ) {
			$args = array();
			add_settings_field( $field['uid'], $field['label'], array( $this, 'field_callback' ), $this->page_slug, $field['section'], $field );
			// $args['sanitize_callback'] = array($this, 'validate_serial_key');
			register_setting( $this->page_slug, $field['uid'], $args );
		}
	}

	public function add_settings_section() {
		add_settings_section( 'settings_section', __( 'Global Settings', 'wpcf7-redirect' ), array( $this, 'section_callback' ), $this->page_slug );

		$this->fields = array_merge(
			$this->fields,
			array(
				array(
					'uid'          => 'wpcf_debug',
					'label'        => 'Debug',
					'section'      => 'settings_section',
					'type'         => 'checkbox',
					'options'      => false,
					'placeholder'  => '',
					'helper'       => '',
					'supplemental' => __( 'This will open the actions post type and display debug feature.', 'wpcf7-redirect' ),
					'default'      => '',
				),
			)
		);
	}

	/**
	 * A function for displaying a field on the admin settings page
	 */
	public function field_callback( $arguments ) {
		$value = get_option( $arguments['uid'] ); // Get the current value, if there is one
		if ( ! $value ) { // If no value exists
			$value = $arguments['default']; // Set to our default
		}
		// Check which type of field we want
		switch ( $arguments['type'] ) {
			case 'text': // If it is a text field
			case 'password':
				printf( '<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" class="widefat" />', $arguments['uid'], $arguments['type'], $arguments['placeholder'], $value );
				break;
			case 'checkbox': // If it is a text field
				$checked = checked( $value, '1', false );
				printf( '<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" class="widefat" %5$s/>', $arguments['uid'], $arguments['type'], $arguments['placeholder'], '1', $checked );
				break;
		}

		$helper       = $arguments['helper'];
		$supplimental = $arguments['supplemental'];

		// If there is help text
		if ( $helper ) {
			printf( '<span class="helper"> %s</span>', $helper ); // Show it
		}

		// If there is supplemental text
		if ( $supplimental ) {
			printf( '<p class="description">%s</p>', $supplimental ); // Show it
		}
	}

	/**
	 * Main call for creating the settings page
	 */
	public function create_plugin_settings_page() {
		// Add the menu item and page
		$page_title = 'Redirection settings';
		$capability = 'manage_options';
		$callback   = array( $this, 'plugin_settings_page_content' );

		add_submenu_page(
			'wpcf7',
			$page_title,
			$page_title,
			$capability,
			$this->page_slug,
			$callback
		);
	}

	/**
	 * The setting page template HTML
	 */
	public function plugin_settings_page_content() {        ?>
		<section class="padbox">
			<div class="wrap wrap-wpcf7redirect">
				<h2>
					<span>
						<?php _e( 'Redirection For Contact Form 7', 'wpcf7-redirect' ); ?>
					</span>
				</h2>
				<div class="postbox">
					<div class="padbox">
						<div class="info wpcf7r-info">
							<?php
							_e(
								"<h2 class='about-title'>About Plugin & Features</h2>
                                        Contact Form 7 is the most popular contact form plugin for WordPress andfor good reasons!
										It is flexible and can easily help you create anything from simple forms to complex form structures.

										Redirection for Contact Form 7, with Conditional Actions management, extends the basic contact form 7 functionality and allows you to add submission actions to your forms"
							);
							?>
						</div>
						<form method="POST" action="options.php" name="wpcfr7_settings">
							<?php
							do_action( 'before_settings_fields' );
							settings_fields( $this->page_slug );
							do_settings_sections( $this->page_slug );
							submit_button();
							?>
						</form>
						<?php if ( is_wpcf7r_debug() ) : ?>
							<input type="button" name="migrate_again" value="<?php _e( 'Migrate Again from Old Settings', 'wpcf7-redirect' ); ?>" class="migrate_again button button-secondary" />
							<input type="button" name="reset_all" value="<?php _e( 'Reset all Settings - BE CAREFUL! this will delete all Redirection for Contact Form 7 data.', 'wpcf7-redirect' ); ?>" class="cf7-redirect-reset button button-secondary" />

						<?php endif; ?>
					</div>
				</div>
			</div>
		</section>
		<?php
	}

	/**
	 * Create a section on the admin settings page
	 */
	public function section_callback( $arguments ) {
		switch ( $arguments['id'] ) {
			case 'serial_section':
				echo sprintf( "In order to gain access to plugin updates, please enter your license key below. If you don't have a licence key, please <a href='%s' target='_blank'>click Here</a>.", $this->product_url );
				break;
		}
	}

	/**
	 * Add a link to the options page to the plugin description block.
	 */
	function register_plugin_links( $links, $file ) {
		if ( WPCF7_PRO_REDIRECT_BASE_NAME === $file ) {
			$links[] = WPCF7r_Utils::get_settings_link();
		}
		return $links;
	}
}
