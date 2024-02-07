<?php
/**
 * Front end submission class.
 *
 * @package RT_TSS
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

if ( ! class_exists( 'TSSFrontEndSubmission' ) ) :
	/**
	 * Front end submission class.
	 */
	class TSSFrontEndSubmission {
		/**
		 * Class constructor
		 */
		public function __construct() {
			add_shortcode( 'tss-testimonial-submit', [ $this, 'testimonial_submission' ] );
			add_action( 'wp_ajax_tss_submit_action', [ $this, 'tss_submit_action' ] );
			add_action( 'wp_ajax_nopriv_tss_submit_action', [ $this, 'tss_submit_action' ] );
		}

		/**
		 * Admin notification
		 *
		 * @return string
		 */
		private function sendAdminNotification() {
			$settings = get_option( TSSPro()->options['settings'] );
			if ( ! isset( $settings['notification_disable'] ) || ( isset( $settings['notification_disable'] ) && empty( $settings['notification_disable'] ) ) ) {
				$site_name    = get_bloginfo( 'name' );
				$siteUrl      = home_url();
				$date_format  = get_option( 'date_format' );
				$time_format  = get_option( 'time_format' );
				$current_time = current_time( 'timestamp' );
				$placeholders = [
					'{site_name}' => esc_html( $site_name ),
					'{site_link}' => sprintf( '<a href="%s">%s</a>', esc_url( $siteUrl ), esc_html( $site_name ) ),
					'{siteUrl}'  => sprintf( '<a href="%s">%s</a>', esc_url( $siteUrl ), esc_url( $siteUrl ) ),
					'{today}'     => date_i18n( $date_format, $current_time ),
					'{now}'       => date_i18n( $date_format . ' ' . $time_format, $current_time ),
				];

				$headers  = '';
				$name     = get_option( 'blogname' );
				$email    = isset( $settings['notification_email'] ) && sanitize_email( $settings['notification_email'] ) ? esc_html( $settings['notification_email'] ) : get_option( 'admin_email' );
				$headers .= "From: {$name} <{$email}>\r\n";
				$headers .= "Reply-To: {$email}\r\n";
				$to       = $email;
				$subject  = ( isset( $settings['notification_email_subject'] ) && $settings['notification_email_subject'] ? esc_html( $settings['notification_email_subject'] ) : esc_html__( '[{site_name}] New Testimonial received', 'testimonial-slider-showcase' ) );
				$subject  = strtr( $subject, $placeholders );

				$body = wp_kses( __( 'Dear Administrator,<br /><br />You have received a new Testimonial on the website {site_name}.<br /><br />Please do not respond to this message. It is automatically generated and is for information purposes only.', 'testimonial-slider-showcase' ), [ 'br' => [] ] );
				$body = strtr( $body, $placeholders );

				add_filter( 'wp_mail_content_type', [ $this, 'set_html_mail_content_type' ] );
				$success = wp_mail( $to, $subject, $body, $headers );
				remove_filter( 'wp_mail_content_type', [ $this, 'set_html_mail_content_type' ] );

				return $success;
			}
		}

		/**
		 * Content type
		 *
		 * @return string
		 */
		public function set_html_mail_content_type() {
			return 'text/html';
		}

		/**
		 * Submit action
		 *
		 * @return void
		 */
		public function tss_submit_action() {
			$error       = true;
			$msg         = $data = null;
			$required    = [];
			$name        = isset( $_REQUEST['tss_name'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tss_name'] ) ) : '';
			$testimonial = isset( $_REQUEST['tss_testimonial'] ) ? wp_kses_post( wp_unslash( $_REQUEST['tss_testimonial'] ) ) : '';
			$recaptcha   = isset( $_REQUEST['g-recaptcha-response'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['g-recaptcha-response'] ) ) : '';

			if ( empty( $name ) ) {
				$required['tss_name'] = esc_html__( 'Name field is required', 'testimonial-slider-showcase' );
			}

			if ( empty( $testimonial ) ) {
				$required['tss_testimonial'] = esc_html__( 'Testimonial field is required', 'testimonial-slider-showcase' );
			}

			$settings        = get_option( TSSPro()->options['settings'] );
			$activeFields    = ( ! empty( $settings['form_fields'] ) ? array_map( 'sanitize_text_field', $settings['form_fields'] ) : [] );
			$enableRecaptcha = in_array( 'tss_recaptcha', $activeFields, true );

			if ( empty( $recaptcha ) && $enableRecaptcha ) {
				$required['tss_recaptcha'] = esc_html__( 'reCAPTCHA field is required', 'testimonial-slider-showcase' );
			}

			if ( empty( $required ) ) {
				if ( TSSPro()->verifyNonce() ) {
					$r = true;

					if ( ! TSSPro()->verifyRecaptcha() && $enableRecaptcha ) {
						$r   = false;
						$msg = esc_html__( 'reCAPTCHA verification error', 'testimonial-slider-showcase' );
					}

					if ( $r ) {
						$metaInput = $this->sanitizeRequest( $_REQUEST );
						unset( $metaInput['tss_name'] );
						unset( $metaInput['tss_testimonial'] );

						$post_arr = [
							'post_title'   => $name,
							'post_content' => $testimonial,
							'post_type'    => TSSPro()->post_type,
							'post_author'  => get_current_user_id(),
						];
						$id       = wp_insert_post( $post_arr );

						if ( $id ) {
							$this->sendAdminNotification();

							if ( isset( $_FILES['feature_image'] ) ) {
								if ( ! function_exists( 'wp_handle_upload' ) ) {
									require_once ABSPATH . 'wp-admin/includes/file.php';
								}

								$status = wp_handle_upload(
									$_FILES['feature_image'],
									[
										'test_form' => false,
									]
								);

								if ( $status && ! isset( $status['error'] ) ) {
									$filename      = $status['file'];
									$filetype      = wp_check_filetype( basename( $filename ), null );
									$wp_upload_dir = wp_upload_dir();

									$attachment = [
										'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ),
										'post_mime_type' => $filetype['type'],
										'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
										'post_content'   => '',
										'post_status'    => 'inherit',
									];

									// Insert the attachment.
									$attach_id = wp_insert_attachment( $attachment, $filename, $id );

									if ( ! is_wp_error( $attach_id ) ) {
										wp_update_attachment_metadata( $attach_id, wp_generate_attachment_metadata( $attach_id, $filename ) );
										set_post_thumbnail( $id, $attach_id );
									}
								} else {
									$msg = $status['error'];
								}
							}

							foreach ( $metaInput as $mKey => $mValue ) {
								update_post_meta( $id, $mKey, $mValue );
							}

							$error = false;
							$msg   = esc_html__( 'Testimonial successfully posted.', 'testimonial-slider-showcase' );
						} else {
							$msg = esc_html__( 'Wp Error!!!.', 'testimonial-slider-showcase' );
						}
					}
				} else {
					$msg = esc_html__( 'Session error', 'testimonial-slider-showcase' );
				}
			}

			wp_send_json(
				[
					'error'    => $error,
					'msg'      => $msg,
					'data'     => $data,
					'required' => $required,
				]
			);
		}

		/**
		 * Submission Shortcode
		 *
		 * @return string
		 */
		public function testimonial_submission() {
			$h  = null;
			$h .= "<div class='tss-wrapper tss-submit-wrapper'>";
			$h .= "<form id='tss-submit-form' method='post'>";
			$h .= TSSPro()->rtFieldGenerator( TSSPro()->tssFrontEndSubmitFields() );
			$h .= "<div class='field-holder submit-holder'><input type='submit' class='tss-submit-button' value='" . esc_html__(
				'Submit',
				'testimonial-slider-showcase'
			) . "'></div>";
			$h .= '</form>';
			$h .= "<div id='tss-submit-response'></div>";
			$h .= '</div>';

			add_action( 'wp_footer', [ $this, 'submission_scripts' ], 10 );

			return $h;
		}

		/**
		 * Submission scripts
		 *
		 * @return void
		 */
		public function submission_scripts() {
			wp_enqueue_style( 'dashicons' );
			wp_enqueue_script(
				[
					'jquery',
					'tss-validator',
					'tss-recaptcha',
					'tss-submit',
				]
			);
			$settings = get_option( TSSPro()->options['settings'] );

			$activeFields = ( ! empty( $settings['form_fields'] ) ? array_map( 'sanitize_text_field', $settings['form_fields'] ) : [] );

			wp_localize_script(
				'tss-submit',
				'tss',
				[
					'ajaxurl'   => esc_url( admin_url( 'admin-ajax.php' ) ),
					'nonce'     => esc_html( wp_create_nonce( TSSPro()->nonceText() ) ),
					'nonceId'   => esc_html( TSSPro()->nonceId() ),
					'error'     => [
						'tss_name'        => esc_html__( 'Name field is required.', 'testimonial-slider-showcase' ),
						'tss_testimonial' => esc_html__( 'Testimonial field is required.', 'testimonial-slider-showcase' ),
						'tss_recaptcha'   => esc_html__( 'reCAPTCHA field is required.', 'testimonial-slider-showcase' ),
					],
					'recaptcha' => [
						'enable'   => in_array( 'tss_recaptcha', $activeFields, true ),
						'errorMSG' => esc_html__( 'Testimonial field is required.', 'testimonial-slider-showcase' ),
					],
				]
			);
		}

		/**
		 * Sanitize
		 *
		 * @param array $request Request.
		 * @return array
		 */
		private function sanitizeRequest( $request ) {
			unset( $request['action'] );
			unset( $request['tss_nonce'] );
			unset( $request['tss_recaptcha'] );

			$submitFields  = TSSPro()->tssFrontEndSubmitFields();
			$sanitizeValue = [];

			foreach ( $submitFields as $key => $field ) {
				if ( ! empty( $request[ $key ] ) ) {
					$value = null;
					if ( 'textarea' === $field['type'] ) {
						$allowed = [
							'a'      => [
								'href'  => [],
								'title' => [],
							],
							'br'     => [],
							'em'     => [],
							'strong' => [],
						];
						$value   = wp_kses( $request[ $key ], $allowed );
					} elseif ( 'rating' === $field['type'] ) {
						$value = absint( $request[ $key ] );
					} elseif ( 'email' === $field['type'] ) {
						$value = sanitize_email( $request[ $key ] );
					} elseif ( 'socialMedia' === $field['type'] ) {
						$sFields = $request[ $key ];
						$value   = [];
						foreach ( $sFields as $sKey => $sValue ) {
							if ( ! empty( $sValue ) ) {
								$value[ $sKey ] = sanitize_text_field( $sValue );
							}
						}
					} else {
						$value = sanitize_text_field( $request[ $key ] );
					}
					$sanitizeValue[ $key ] = $value;
				}
			}

			return $sanitizeValue;
		}
	}

endif;
