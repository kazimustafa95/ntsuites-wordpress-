<?php
/**
 * Init Class.
 *
 * @package RT_TSS
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

if ( ! class_exists( 'TSSPro' ) ) {
	/**
	 * Init Class.
	 */
	class TSSPro {
		/**
		 * Post type
		 *
		 * @var string
		 */
		public $post_type;

		/**
		 * Shortcode post type
		 *
		 * @var string
		 */
		public $shortCodePT;

		/**
		 * Taxonomies
		 *
		 * @var array
		 */
		public $taxonomies;

		/**
		 * Pre settings.
		 *
		 * @var array
		 */
		public $preSettings;
		public  $options;
		public  $incPath;
		public  $proPath;
		public  $functionsPath;
		public  $classesPath;
		public  $widgetsPath;
		public  $viewsPath;
		public  $templatePath;
		public  $proTemplatesPath;
		public  $post_type_slug;
		public  $modelsPath ;
		public  $assetsUrl ;
		public  $objects ;

		/**
		 * Instance
		 *
		 * @var object
		 */
		protected static $_instance;

		/**
		 * Class constructor.
		 */
		public function __construct() {
			$this->options          = [
				'settings'          => 'tss_settings',
				'version'           => TSS_VERSION,
				'installed_version' => 'tss_installed_version',
				'flash'             => 'tss_flash',
			];
			$this->shortCodePT      = 'tss-sc';
			$settings               = get_option( $this->options['settings'] );
			$this->post_type        = 'testimonial';
			$this->post_type_slug   = ! empty( $settings['slug'] ) ? sanitize_title_with_dashes( $settings['slug'] ) : 'testimonial';
			$this->taxonomies       = [
				'category' => $this->post_type . '-category',
				'tag'      => $this->post_type . '-tag',
			];
			$this->incPath          = dirname( __FILE__ );
			$this->proPath          = untrailingslashit( plugin_dir_path( TSS_PLUGIN_ACTIVE_FILE_NAME ) ) . '-pro';
			$this->functionsPath    = $this->incPath . '/functions/';
			$this->classesPath      = $this->incPath . '/classes/';
			$this->widgetsPath      = $this->incPath . '/widgets/';
			$this->viewsPath        = $this->incPath . '/views/';
			$this->templatePath     = $this->incPath . '/templates/';
			$this->proTemplatesPath = $this->proPath . '/templates/';
			$this->modelsPath       = $this->incPath . '/models/';

			$this->assetsUrl = TSS_PLUGIN_URL . '/assets/';
			$this->loadModel( $this->modelsPath );
			$this->loadClass( $this->classesPath );
			$this->preSettings = [
				'slug'               => 'testimonial',
				'field'              => [
					'client_name',
					'project_url',
					'completed_date',
					'tools',
					'categories',
					'tags',
				],
				'form_fields'        => [
					'tss_designation',
					'tss_company',
					'tss_location',
					'tss_rating',
					'tss_video',
					'tss_social_media',
				],
				'notification_email' => get_option( 'admin_email' ),
			];

			do_action( 'rtts_loaded' );
		}

		/**
		 * Load Model class
		 *
		 * @param string $dir Directory.
		 * @return void
		 */
		public function loadModel( $dir ) {
			if ( ! file_exists( $dir ) ) {
				return;
			}
			foreach ( scandir( $dir ) as $item ) {
				if ( preg_match( '/.php$/i', $item ) ) {
					require_once $dir . $item;
				}
			}
		}

		/**
		 * Pro link
		 *
		 * @return string
		 */
		public function pro_version_link() {
			$proUrl = esc_url( 'https://www.radiustheme.com/downloads/wp-testimonial-slider-showcase-pro-wordpress/' );
			return $proUrl;
		}

		/**
		 * Demo link
		 *
		 * @return string
		 */
		public function demo_home_page_link() {
			$proUrl = esc_url( 'https://www.radiustheme.com/demo/plugins/testimonial-slider/' );
			return $proUrl;
		}

		/**
		 * Doc link
		 *
		 * @return string
		 */
		public function documentation_link() {
			$proUrl = esc_url( 'https://www.radiustheme.com/setup-wp-testimonials-slider-showcase-wordpress/' );
			return $proUrl;
		}

		/**
		 * Load class
		 *
		 * @param string $dir Directory.
		 * @return void
		 */
		public function loadClass( $dir ) {
			if ( ! file_exists( $dir ) ) {
				return;
			}

			$classes = [];

			foreach ( scandir( $dir ) as $item ) {
				if ( preg_match( '/.php$/i', $item ) ) {
					require_once $dir . $item;
					$className = str_replace( '.php', '', $item );
					$classes[] = new $className();
				}
			}

			if ( $classes ) {
				foreach ( $classes as $class ) {
					$this->objects[] = $class;
				}
			}
		}

		/**
		 * Load widget
		 *
		 * @param string $dir Directory.
		 * @return void
		 */
		public function loadWidget( $dir ) {
			if ( ! file_exists( $dir ) ) {
				return;
			}
			foreach ( scandir( $dir ) as $item ) {
				if ( preg_match( '/.php$/i', $item ) ) {
					require_once $dir . $item;
					$class = str_replace( '.php', '', $item );

					if ( method_exists( $class, 'register_widget' ) ) {
						$caller = new $class();
						$caller->register_widget();
					} else {
						register_widget( $class );
					}
				}
			}
		}


		/**
		 * Render view.
		 *
		 * @param string  $viewName View name.
		 * @param array   $args View args.
		 * @param boolean $return View return.
		 *
		 * @return string|void
		 */
		public function render_view( $viewName, $args = [], $return = false ) {
			$path     = str_replace( '.', '/', $viewName );
			$viewPath = $this->viewsPath . $path . '.php';

			if ( ! file_exists( $viewPath ) ) {
				return;
			}

			if ( $args ) {
				extract( $args );
			}

			if ( $return ) {
				ob_start();
				include $viewPath;

				return ob_get_clean();
			}

			include $viewPath;
		}

		/**
		 * Render.
		 *
		 * @param string  $viewName View name.
		 * @param array   $args View args.
		 * @param boolean $return View return.
		 *
		 * @return string|void
		 */
		public function render( $viewName, $args = [], $return = true ) {

			$path = str_replace( '.', '/', $viewName );

			if ( $args ) {
				extract( $args );
			}

			$template = [
				"testimonial-slider-showcase/{$path}.php",
			];

			$pro_path = $this->proTemplatesPath . $viewName . '.php';

			if ( locate_template( $template ) ) {
				$template_file = locate_template( $template );
			} elseif ( function_exists( 'rttsp' ) && file_exists( $pro_path ) ) {
				$template_file = $pro_path;
			} else {
				$template_file = $this->templatePath . $path . '.php';
			}

			if ( ! file_exists( $template_file ) ) {
				return;
			}

			if ( $return ) {
				ob_start();
				include $template_file;

				return ob_get_clean();
			} else {

				include $template_file;
			}
		}

		/**
		 * Dynamicaly call any  method from models class
		 * by pluginFramework instance
		 *
		 * @param string $name Name.
		 * @param array  $args Args.
		 * @return void
		 */
		public function __call( $name, $args ) {
			if ( ! is_array( $this->objects ) ) {
				return;
			}

			foreach ( $this->objects as $object ) {
				if ( method_exists( $object, $name ) ) {
					$count = count( $args );
					if ( $count == 0 ) {
						return $object->$name();
					} elseif ( $count == 1 ) {
						return $object->$name( $args[0] );
					} elseif ( $count == 2 ) {
						return $object->$name( $args[0], $args[1] );
					} elseif ( $count == 3 ) {
						return $object->$name( $args[0], $args[1], $args[2] );
					} elseif ( $count == 4 ) {
						return $object->$name( $args[0], $args[1], $args[2], $args[3] );
					} elseif ( $count == 5 ) {
						return $object->$name( $args[0], $args[1], $args[2], $args[3], $args[4] );
					} elseif ( $count == 6 ) {
						return $object->$name( $args[0], $args[1], $args[2], $args[3], $args[4], $args[5] );
					}
				}
			}
		}

		/**
		 * Singleton instance
		 *
		 * @return object
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}
	}

	/**
	 * Main function for external use.
	 *
	 * @return TSSPro
	 */
	function TSSPro() {
		return TSSPro::instance();
	}

	// Init app.
	TSSPro();
}
