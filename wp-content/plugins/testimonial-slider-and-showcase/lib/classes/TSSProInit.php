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

if ( ! class_exists( 'TSSProInit' ) ) :
	/**
	 * Init Class.
	 */
	class TSSProInit {
		/**
		 * Class constructor
		 */
		public function __construct() {
			add_action( 'init', [ __CLASS__, 'init' ], 1 );
			add_action( 'admin_menu', [ $this, 'tss_menu_register' ], 99 );
			add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
			register_activation_hook( TSS_PLUGIN_ACTIVE_FILE_NAME, [ $this, 'activate' ] );
			register_deactivation_hook( TSS_PLUGIN_ACTIVE_FILE_NAME, [ $this, 'deactivate' ] );
			add_action( 'plugins_loaded', [ $this, 'plugin_loaded' ] );
			add_action( 'wp_ajax_tssSettingsAction', [ $this, 'tssSettingsUpdate' ] );
			add_filter( 'plugin_action_links_' . plugin_basename( TSS_PLUGIN_ACTIVE_FILE_NAME ), [ $this, 'rt_plugin_active_link_marketing' ] );
			add_action( 'admin_init', [ $this, 'my_plugin_redirect' ] );
			add_filter( 'wp_insert_post_data', [ $this, 'sanitize_title' ], 99, 1 );
		}

		/**
		 * Activation
		 *
		 * @return void
		 */
		public function activate() {
			$this->flush_rewrite();
			$this->fixLayout();
			add_option( 'rtts_activation_redirect', true );
		}

		/**
		 * Redirect
		 *
		 * @return void
		 */
		public function my_plugin_redirect() {
			if ( get_option( 'rtts_activation_redirect', false ) ) {
				delete_option( 'rtts_activation_redirect' );
				wp_safe_redirect( admin_url( 'edit.php?post_type=testimonial&page=tss_get_help' ) );
			}
		}

		/**
		 * Rewrite
		 *
		 * @return void
		 */
		public function flush_rewrite() {
			flush_rewrite_rules();
		}

		/**
		 * Deactivation
		 *
		 * @return void
		 */
		public function deactivate() {
			$this->flush_rewrite();
		}

		/**
		 * Layout fixer
		 *
		 * @return void
		 */
		public function fixLayout() {
			$installed_version = get_option( TSSPro()->options['installed_version'] );

			if ( $installed_version && version_compare( $installed_version, '2.0.0', '<' ) ) {
				if ( ! function_exists( 'rttsp' ) ) {
					$this->scLayoutFixer();
				}
			} else {
				// pro version with free install.
				if ( ! $installed_version ) {
					if ( ! function_exists( 'rttsp' ) ) {
						$this->scLayoutFixer();
					}
				}
			}
		}

		/**
		 * Layout fixer
		 *
		 * @return void
		 */
		private function scLayoutFixer() {
			$allSC = get_posts(
				[
					'post_type'      => TSSPro()->shortCodePT,
					'posts_per_page' => -1,
				]
			);

			if ( is_array( $allSC ) && ! empty( $allSC ) ) {
				foreach ( $allSC as $sc ) {
					$layout = get_post_meta( $sc->ID, 'tss_layout', true );

					if ( 'carousel1' === $layout ) {
						update_post_meta( $sc->ID, 'tss_layout', 'carousel3' );
					}

					if ( 'layout1' === $layout || 'carousel1' === $layout ) {
						$tss_author_name_style = [ 'color' => '#3a3a3a' ];
						update_post_meta( $sc->ID, 'tss_author_name_style', $tss_author_name_style );

						$tss_author_bio_style = [ 'color' => '#8cc63e' ];
						update_post_meta( $sc->ID, 'tss_author_bio_style', $tss_author_bio_style );
					}
				}
			}
		}

		/**
		 * Init
		 *
		 * @return void
		 */
		public static function init() {
			$testimonial_args = [
				'label'               => esc_html__( 'Testimonial', 'testimonial-slider-showcase' ),
				'labels'              => [
					'name'               => esc_html__( 'Testimonials', 'testimonial-slider-showcase' ),
					'all_items'          => esc_html__( 'All Testimonials', 'testimonial-slider-showcase' ),
					'singular_name'      => esc_html__( 'Testimonial', 'testimonial-slider-showcase' ),
					'menu_name'          => esc_html__( 'Testimonial', 'testimonial-slider-showcase' ),
					'name_admin_bar'     => esc_html__( 'Testimonial', 'testimonial-slider-showcase' ),
					'add_new'            => esc_html__( 'Add Testimonial', 'testimonial-slider-showcase' ),
					'add_new_item'       => esc_html__( 'Add Testimonial', 'testimonial-slider-showcase' ),
					'edit_item'          => esc_html__( 'Edit Testimonial', 'testimonial-slider-showcase' ),
					'new_item'           => esc_html__( 'New Testimonial', 'testimonial-slider-showcase' ),
					'view_item'          => esc_html__( 'View Testimonial', 'testimonial-slider-showcase' ),
					'search_items'       => esc_html__( 'Search Testimonial', 'testimonial-slider-showcase' ),
					'not_found'          => esc_html__( 'No Testimonials found', 'testimonial-slider-showcase' ),
					'not_found_in_trash' => esc_html__( 'No Testimonials in the trash', 'testimonial-slider-showcase' ),
				],
				'supports'            => [ 'title', 'editor', 'thumbnail', 'revisions', 'page-attributes' ],
				'hierarchical'        => false,
				'public'              => true,
				'rewrite'             => [ 'slug' => TSSPro()->post_type_slug ],
				'show_ui'             => true,
				'show_in_menu'        => true,
				'menu_icon'           => esc_url( TSSPro()->assetsUrl ) . 'images/icon-16x16.png',
				'show_in_admin_bar'   => true,
				'show_in_nav_menus'   => false,
				'can_export'          => true,
				'has_archive'         => false,
				'exclude_from_search' => false,
				'publicly_queryable'  => true,
				'capability_type'     => 'page',
			];
			register_post_type( TSSPro()->post_type, $testimonial_args );

			$sc_args = [
				'label'               => esc_html__( 'ShortCode', 'testimonial-slider-showcase' ),
				'description'         => esc_html__( 'Testimonial ShortCode generator', 'testimonial-slider-showcase' ),
				'labels'              => [
					'all_items'          => esc_html__( 'ShortCode', 'testimonial-slider-showcase' ),
					'menu_name'          => esc_html__( 'ShortCode', 'testimonial-slider-showcase' ),
					'singular_name'      => esc_html__( 'ShortCode', 'testimonial-slider-showcase' ),
					'edit_item'          => esc_html__( 'Edit ShortCode', 'testimonial-slider-showcase' ),
					'new_item'           => esc_html__( 'New ShortCode', 'testimonial-slider-showcase' ),
					'view_item'          => esc_html__( 'View ShortCode', 'testimonial-slider-showcase' ),
					'search_items'       => esc_html__( 'ShortCode Locations', 'testimonial-slider-showcase' ),
					'not_found'          => esc_html__( 'No ShortCode found.', 'testimonial-slider-showcase' ),
					'not_found_in_trash' => esc_html__( 'No ShortCode found in trash.', 'testimonial-slider-showcase' ),
				],
				'supports'            => [ 'title' ],
				'public'              => false,
				'rewrite'             => false,
				'show_ui'             => true,
				'show_in_menu'        => 'edit.php?post_type=' . esc_attr( TSSPro()->post_type ),
				'show_in_admin_bar'   => true,
				'show_in_nav_menus'   => false,
				'can_export'          => true,
				'has_archive'         => false,
				'exclude_from_search' => false,
				'publicly_queryable'  => false,
				'capability_type'     => 'page',
			];
			register_post_type( TSSPro()->shortCodePT, $sc_args );

			TSSPro()->doFlush();

			// register scripts.
			$scripts = [];
			$styles  = [];

			$swiperKey = class_exists( 'Rtcl' ) ? 'rtt-swiper' : 'swiper';
			$default_swiper_path = TSSPro()->assetsUrl . 'vendor/swiper/swiper.min.js';

			if ( 'swiper' === $swiperKey ) {
				if ( defined( 'ELEMENTOR_ASSETS_PATH' ) ) {
					$is_swiper8_enable = get_option( 'elementor_experiment-e_swiper_latest' );

					if ( $is_swiper8_enable == 'active' ) {
						$el_swiper_path = 'lib/swiper/v8/swiper.min.js';
					} else {
						$el_swiper_path = 'lib/swiper/swiper.min.js';
					}

					$elementor_swiper_path = ELEMENTOR_ASSETS_PATH . $el_swiper_path;

					if ( file_exists( $elementor_swiper_path ) ) {
						$default_swiper_path = ELEMENTOR_ASSETS_URL . $el_swiper_path;
					}
				}
			}

			$scripts[ $swiperKey ]         = [
				'src'    => $default_swiper_path,
				'deps'   => [ 'jquery' ],
				'footer' => false,
			];
			$scripts['tss-image-load']     = [
				'src'    => TSSPro()->assetsUrl . 'vendor/isotope/imagesloaded.pkgd.min.js',
				'deps'   => [ 'jquery' ],
				'footer' => false,
			];
			$scripts['tss-isotope']        = [
				'src'    => TSSPro()->assetsUrl . 'vendor/isotope/isotope.pkgd.min.js',
				'deps'   => [ 'jquery' ],
				'footer' => false,
			];
			$scripts['tss-actual-height']  = [
				'src'    => TSSPro()->assetsUrl . 'vendor/actual-height/jquery.actual.min.js',
				'deps'   => [ 'jquery' ],
				'footer' => false,
			];
			$scripts['tss-admin-taxonomy'] = [
				'src'    => TSSPro()->assetsUrl . 'js/admin-taxonomy.js',
				'deps'   => [ 'jquery' ],
				'footer' => true,
			];

			if ( function_exists( 'rttsp' ) ) {
				$scripts['rt-tss-sortable'] = [
					'src'    => TSSPro()->assetsUrl . 'js/rt-sortable.js',
					'deps'   => [ 'jquery' ],
					'footer' => true,
				];
			}

			$scripts['tss-recaptcha'] = [
				'src'    => 'https://www.google.com/recaptcha/api.js',
				'deps'   => '',
				'footer' => true,
			];
			$scripts['tss-validator'] = [
				'src'    => TSSPro()->assetsUrl . 'js/jquery.validate.min.js',
				'deps'   => [ 'jquery' ],
				'footer' => true,
			];
			$scripts['tss-submit']    = [
				'src'    => TSSPro()->assetsUrl . 'js/tss-submit.js',
				'deps'   => [ 'jquery' ],
				'footer' => true,
			];
			$scripts['tss']           = [
				'src'    => TSSPro()->assetsUrl . 'js/wptestimonial.js',
				'deps'   => [ 'jquery' ],
				'footer' => true,
			];

			$styles['tss-fontello'] = TSSPro()->assetsUrl . 'vendor/fontello/css/tss-font.min.css';
			$styles['swiper']       = TSSPro()->assetsUrl . 'vendor/swiper/swiper.min.css';
			$styles['tss']          = TSSPro()->assetsUrl . 'css/wptestimonial.css';

			if ( is_admin() ) {
				$scripts['tss-select2']       = [
					'src'    => TSSPro()->assetsUrl . 'vendor/select2/select2.min.js',
					'deps'   => [ 'jquery' ],
					'footer' => false,
				];
				$scripts['tss-admin-preview'] = [
					'src'    => TSSPro()->assetsUrl . 'js/admin-preview.js',
					'deps'   => [ 'jquery' ],
					'footer' => false,
				];
				$scripts['tss-admin']         = [
					'src'    => TSSPro()->assetsUrl . 'js/settings.js',
					'deps'   => [ 'jquery' ],
					'footer' => true,
				];
				$scripts['tss-admin-sc']      = [
					'src'    => TSSPro()->assetsUrl . 'js/admin-sc.js',
					'deps'   => [ 'jquery' ],
					'footer' => true,
				];
				$styles['tss-select2']        = TSSPro()->assetsUrl . 'vendor/select2/select2.min.css';
				$styles['tss-admin']          = TSSPro()->assetsUrl . 'css/settings.css';
			}

			$version = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? time() : TSS_VERSION;

			foreach ( $scripts as $handel => $script ) {
				wp_register_script( $handel, $script['src'], $script['deps'], $version, $script['footer'] );
			}

			foreach ( $styles as $k => $v ) {
				wp_register_style( $k, $v, false, $version );
			}

		}

		/**
		 * Settings update
		 *
		 * @return void
		 */
		public function tssSettingsUpdate() {
			$error = true;
			$msg   = false;

			if ( TSSPro()->verifyNonce() ) {
				unset( $_REQUEST['action'] );
				unset( $_REQUEST['_wp_http_referer'] );
				unset( $_REQUEST['tss_nonce'] );

				$mates = TSSPro()->tssAllSettingsFields();

				foreach ( $mates as $key => $field ) {
					$rValue       = ! empty( $_REQUEST[ $key ] ) ? $_REQUEST[ $key ] : null;
					$value        = TSSPro()->sanitize( $field, $rValue );
					$data[ $key ] = $value;
				}

				$settings = get_option( TSSPro()->options['settings'] );

				if ( ! empty( $settings['slug'] ) && isset( $_REQUEST['slug'] ) && $settings['slug'] !== $_REQUEST['slug'] ) {
					update_option( TSSPro()->options['flash'], true );
				}

				update_option( TSSPro()->options['settings'], $data );

				$error = false;
				$msg   = esc_html__( 'Settings successfully updated', 'testimonial-slider-showcase' );
			} else {
				$msg = esc_html__( 'Security Error !!', 'testimonial-slider-showcase' );
			}
			$response = [
				'error' => $error,
				'msg'   => $msg,
			];

			wp_send_json( $response );
			die();
		}

		/**
		 * Register Menu
		 *
		 * @return void
		 */
		public function tss_menu_register() {
			add_submenu_page(
				'edit.php?post_type=' . TSSPro()->post_type,
				esc_html__( 'Testimonial Settings', 'testimonial-slider-showcase' ),
				esc_html__( 'Settings', 'testimonial-slider-showcase' ),
				'administrator',
				'tss_settings',
				[ $this, 'settings_page_view' ]
			);

			add_submenu_page(
				'edit.php?post_type=' . TSSPro()->post_type,
				esc_html__( 'Get Help', 'testimonial-slider-showcase' ),
				esc_html__( 'Get Help', 'testimonial-slider-showcase' ),
				'administrator',
				'tss_get_help',
				[ $this, 'get_help' ]
			);
		}

		/**
		 * Admin enqueue
		 *
		 * @return void
		 */
		public function admin_enqueue_scripts() {
			global $pagenow, $typenow;

			if ( ! in_array( $pagenow, [ 'post.php', 'post-new.php', 'edit.php' ] ) ) {
				// return;
			}

			if ( $typenow != TSSPro()->post_type ) {
				return;
			}

			// scripts.
			wp_enqueue_script(
				[
					'jquery',
					'jquery-ui-core',
					'jquery-ui-sortable',
					'ace_code_highlighter_js',
					'ace_mode_js',
					'tss-select2',
					'tss-admin',
				]
			);

			// styles.
			wp_enqueue_style(
				[
					'tss-select2',
					'tss-admin',
				]
			);

			wp_localize_script(
				'tss-admin',
				'tss',
				[
					'nonce'   => esc_attr( wp_create_nonce( TSSPro()->nonceText() ) ),
					'nonceId' => esc_attr( TSSPro()->nonceId() ),
					'ajaxurl' => esc_url( admin_url( 'admin-ajax.php' ) ),
				]
			);
		}

		/**
		 * Settings page
		 *
		 * @return void
		 */
		public function settings_page_view() {
			TSSPro()->render_view( 'settings' );
		}

		/**
		 * Help page
		 *
		 * @return void
		 */
		public function get_help() {
			TSSPro()->render_view( 'help' );
		}

		/**
		 * Load the plugin text domain for translation.
		 *
		 * @since 0.1.0
		 */
		public function plugin_loaded() {
			load_plugin_textdomain( 'testimonial-slider-showcase', false, TSS_LANGUAGE_PATH );

			if ( ! get_option( TSSPro()->options['settings'] ) ) {
				update_option( TSSPro()->options['settings'], TSSPro()->preSettings );
			}
		}

		/**
		 * Marketing
		 *
		 * @param array $links Links.
		 * @return array
		 */
		public function rt_plugin_active_link_marketing( $links ) {
			$links[] = '<a target="_blank" href="' . esc_url( TSSPro()->demo_home_page_link() ) . '">Demo</a>';
			$links[] = '<a target="_blank" href="' . esc_url( TSSPro()->documentation_link() ) . '">Documentation</a>';
			if ( ! function_exists( 'rttsp' ) ) {
				$links[] = '<a target="_blank" style="color: #39b54a;font-weight: 700;" href="' . esc_url( TSSPro()->pro_version_link() ) . '">Get Pro</a>';
			}

			return $links;
		}

		public function sanitize_title( $data ) {
			if ( $data['post_type'] === TSSPro()->post_type && isset( $_POST['post_title'] ) ) {
				$data['post_title'] = wp_kses( $data['post_title'], TSSPro()->allowedHtml() );
			}

			return $data;
		}
	}
endif;
