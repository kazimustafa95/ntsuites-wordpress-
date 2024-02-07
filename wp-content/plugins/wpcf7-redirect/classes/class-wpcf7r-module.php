<?php
/**
 * Class WPCF7R_Module - parent class for all wpcf7r Modules.
 *
 * @package wpcf7r
 *
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * WPCF7R_Module class.
 */
class WPCF7R_Module {

	/**
	 * Hold the active modules
	 *
	 * @var [type]
	 */
	private static $registered_modules;

	/**
	 * Hold the module name
	 *
	 * @var [string]
	 */
	private $name;

	/**
	 * Hold the module title
	 *
	 * @var [string]
	 */
	private $title;

	/**
	 * General init function for all child modules.
	 *
	 * @param [array] $module the module array.
	 *
	 * @return void
	 */
	public function init( $module ) {
		$this->name  = $module['name'];
		$this->title = $module['title'];

		// Register global modules actions.
		if ( method_exists( get_class( $this ), 'add_panel' ) ) {
			add_action( 'wpcf7_editor_panels', array( $this, 'add_panel' ) );
		}
		// Enqueue required admin scripts or styles.
		if ( method_exists( get_class( $this ), 'enqueue_admin_scripts' ) ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		}
	}

	/**
	 * Add a new module
	 *
	 * @param [string] $name - the module name.
	 * @param [string] $title - the module title.
	 * @param [string] $handler - the name of the module main class.
	 * @return void
	 */
	public static function register_module( $name, $title, $handler ) {
		self::$registered_modules[ $name ] = array(
			'name'  => $name,
			'title' => $title,
			'class' => $handler,
		);
	}

	/**
	 * Initialize all registered modules
	 *
	 * @return void
	 */
	public static function init_modules() {
		if ( self::get_active_modules() ) {
			foreach ( self::get_active_modules() as $module ) {
				$class_name    = $module['class'];
				$module_object = new $class_name();

				// Create an instance of the loaded module.
				$module_object->init( $module );
			}
		}
	}

	/**
	 * Return the registered modules array
	 *
	 * @return [array] - the registered modules array.
	 */
	public static function get_active_modules() {
		return self::$registered_modules;
	}
}
