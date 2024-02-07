<?php
/******************************************************************************************
 * Copyright (C) Smackcoders. - All Rights Reserved under Smackcoders Proprietary License
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/
namespace Smackcoders\CFCSV;

if ( ! defined( 'ABSPATH' ) )
exit; // Exit if accessed directly

/**
 * Class SupportMail
 * @package Smackcoders\CFCSV
 */
class SupportMail {

	protected static $instance = null;

	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
			self::$instance->doHooks();
		}
		return self::$instance;
	}

	/**
	 * SupportMail constructor.
	 */
	public function __construct() {
		$this->plugin = Plugin::getInstance();
	}

	/**
	 * SupportMail hooks.
	 */
	public function doHooks(){
		add_action('wp_ajax_support_mail', array($this,'supportMail'));
		add_action('wp_ajax_send_subscribe_email', array($this,'sendSubscribeEmail'));
	}

	public static function supportMail(){
		check_ajax_referer('smack-importer-custom-fields-basic-pro', 'securekey');
		if($_POST){
			$email = sanitize_email($_POST['email']);
			$orderId = intval($_POST['orderid']);
			$url = get_option('siteurl');
			$site_name = get_option('blogname');
			$headers = "From: " . $site_name . "<$email>" . "\r\n";
			$headers.= 'MIME-Version: 1.0' . "\r\n";
			$headers= array( "Content-type: text/html; charset=UTF-8");
			$to = 'support@smackcoders.com';
			$subject = sanitize_text_field($_POST['query']);
			$message = "Site URL: " . $url . "\r\n<br>";
			$message .= "Email: " . $email . "\r\n<br>";
			$message .= "Order Id: " . $orderId . "\r\n<br>";
			$message .= "Plugin Name: WP Importer Custom Fields PRO " . "\r\n<br>";
			$message .= "Message: "."\r\n" . sanitize_text_field($_POST['message']) . "\r\n<br>";
			$urlparts = parse_url(home_url());
			$domain_name = $urlparts['host'];
			//send email
			if ($domain_name == 'localhost')
			{
				$headers[] = 'From: Wordpress<wordpress@mysite.com>';
				add_filter('wp_mail_content_type', function ($content_type)
				{
					return 'text/html';
				});
				$value = wp_mail($to, $subject, $message, $headers);
			}
			else
			{
				$headers = array(
					'Content-Type: text/html;charset=UTF-8'
				);
				$value = wp_mail($to, $subject, $message, $headers);
			}
			
			if($value){
				$success_message = 'Mail Sent!';
				echo wp_json_encode($success_message);
			} else {
				$error_message = "Please draft a mail to support@smackcoders.com. If you doesn't get any acknowledgement within an hour!";
				echo wp_json_encode($error_message);
			}
			wp_die();
		}
	}

	public static function sendSubscribeEmail(){
		check_ajax_referer('smack-importer-custom-fields-basic-pro', 'securekey');
		if($_POST){
			$email = sanitize_email($_POST['subscribe_email']);
			$url = get_option('siteurl');
			$site_name = get_option('blogname');
			$headers = "From: " . $site_name . "<$email>" . "\r\n";
			$headers.= 'MIME-Version: 1.0' . "\r\n";
			$headers.= "Content-type: text/html; charset=iso-8859-1 \r\n";
			$to = 'support@smackcoders.com';
			$subject = 'Newsletter Subscription';
			$message = "Site URL: " . $url . "\r\n<br>";
			$message .= "Email: " . $email . "\r\n<br>";
			$message .= "Plugin Name:  WP Importer Custom Fields PRO  "."\r\n<br>";
			$message .= "Message: Hi Team, I want to subscribe to your newsletter." . "\r\n<br>";
			$urlparts = parse_url(home_url());
			$domain_name = $urlparts['host'];
            //send email
			if ($domain_name == 'localhost')
			{
				$headers = 'From: Wordpress<wordpress@mysite.com>';
				add_filter('wp_mail_content_type', function ($content_type)
				{
					return 'text/html';
				});
				$value = wp_mail($to, $subject, $message, $headers);
			}
			else
			{
				$headers = array(
					'Content-Type: text/html;charset=UTF-8'
				);
				$value = wp_mail($to, $subject, $message, $headers);
			}
			
			
			if($value) {
				$success_message = 'Mail Sent!';
				echo wp_json_encode($success_message);
			} else {
				$error_message = "Please draft a mail to support@smackcoders.com. If you doesn't get any acknowledgement within an hour!";
				echo wp_json_encode($error_message);
			} 
			wp_die();
		}
	}
}
