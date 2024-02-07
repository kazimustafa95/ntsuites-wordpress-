<?php
/**
 * Black Friday notice class.
 *
 * @package RT_TSS
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

if ( ! class_exists( 'TSSAdmin' ) ) :
	/**
	 * Black Friday notice class.
	 */
	class TSSAdmin {
		/**
		 * Class constructor.
		 */
		public function __construct() {
			add_action(
				'admin_init',
				function () {
					$current = time();
					if ( mktime( 0, 0, 0, 11, 18, 2022 ) <= $current && $current <= mktime( 0, 0, 0, 1, 15, 2023 ) ) {
						if ( '1' !== get_option( 'rttss_ny_2022' ) ) {
							if ( ! isset( $GLOBALS['rt_tss_ny_2022_notice'] ) ) {
								$GLOBALS['rt_tss_ny_2022_notice'] = 'rttss_ny_2022';
								self::notice();
							}
						}
					}
				}
			);
		}

		/**
		 * Notice
		 *
		 * @return void
		 */
		public static function notice() {
			add_action(
				'admin_enqueue_scripts',
				function () {
					wp_enqueue_script( 'jquery' );
				}
			);

			add_action(
				'admin_notices',
				function () {
					$plugin_name   = TSS_ITEM_NAME . ' Pro';
					$download_link = TSSPro()->pro_version_link();
					?>
				<div class="notice notice-info is-dismissible" data-rttssdismissable="rttss_ny_2022"
					style="display:grid;grid-template-columns: 100px auto;padding-top: 25px; padding-bottom: 22px;">
					<img alt="<?php echo esc_attr( $plugin_name ); ?>" src="<?php echo esc_url( TSSPro()->assetsUrl ) . 'images/icon-128x128.gif'; ?>" width="74px" height="74px" style="grid-row: 1 / 4; align-self: center;justify-self: center"/>
					<h3 style="margin:0;"><?php echo sprintf( '%s New Year Deal!!', esc_html( $plugin_name ) ); ?></h3>

					<p style="margin:0 0 2px;">
						<?php echo esc_html__( "Don't miss out on our biggest sale of the year! Get yours.", 'testimonial-slider-showcase' ); ?>
						<b><?php echo esc_html( $plugin_name ); ?> plan</b> with <b>UP TO 50% OFF</b>! Limited time offer!!
					</p>

					<p style="margin:0;">
						<a class="button button-primary" href="<?php echo esc_url( $download_link ); ?>" target="_blank">Buy Now</a>
						<a class="button button-dismiss" href="#">Dismiss</a>
					</p>
				</div>
					<?php
				}
			);

			add_action(
				'admin_footer',
				function () {
					?>
				<script type="text/javascript">
					(function ($) {
						$(function () {
							setTimeout(function () {
								$('div[data-rttssdismissable] .notice-dismiss, div[data-rttssdismissable] .button-dismiss')
									.on('click', function (e) {
										e.preventDefault();
										$.post(ajaxurl, {
											'action': 'rttss_dismiss_admin_notice',
											'nonce': <?php echo wp_json_encode( wp_create_nonce( 'rttss-dismissible-notice' ) ); ?>
										});
										$(e.target).closest('.is-dismissible').remove();
									});
							}, 1000);
						});
					})(jQuery);
				</script>
					<?php
				}
			);

			add_action(
				'wp_ajax_rttss_dismiss_admin_notice',
				function () {
					check_ajax_referer( 'rttss-dismissible-notice', 'nonce' );

					update_option( 'rttss_ny_2022', '1' );
					wp_die();
				}
			);
		}
	}

endif;
