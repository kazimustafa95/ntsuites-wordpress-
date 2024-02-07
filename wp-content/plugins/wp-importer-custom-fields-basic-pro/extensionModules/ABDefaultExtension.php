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

class DefaultExtension extends ExtensionHandler{
	private static $instance = null;

	public static function getInstance() {

		if (DefaultExtension::$instance == null) {
			DefaultExtension::$instance = new DefaultExtension;
		}
		return DefaultExtension::$instance;
	}

	/**
	* Provides default mapping fields for specific post type or taxonomies
	* @param string $data - selected import type
	* @return array - mapping fields
	*/
	public function processExtension($data){
		$mode = isset($_POST['Mode']) ? sanitize_text_field($_POST['Mode']) : "";
		$import_types = $data;
		$import_type = $this->import_name_as($import_types);
		$response = [];
		if ( $import_type != 'Users' && $import_type != 'Taxonomies' && $import_type != 'Categories' && $import_type != 'Comments' && $import_type != 'Images' && $import_type != 'ngg_pictures' && $import_types != 'nav_menu_item' && $import_types != 'widgets'&& $import_types != 'lp_order') {
			$wordpressfields = array(
                                    'Title' => 'post_title',
                                       'ID' => 'ID',
                                       'Content' => 'post_content',
                                       'Short Description' => 'post_excerpt',
                                       'Publish Date' => 'post_date',
                                       'Slug' => 'post_name',
                                       'Author' => 'post_author',
                                       'Status' => 'post_status',
                                       'Featured Image' => 'featured_image'    
                                       );
			if ($import_type === 'Posts') { 
				$wordpressfields['Format'] = 'post_format';
				$wordpressfields['Comment Status'] = 'comment_status';
				$wordpressfields['Ping Status'] = 'ping_status';
				$wordpressfields['Track Options'] = 'pinged';
			}
			if ($import_type === 'CustomPosts') { 
				$wordpressfields['Format'] = 'post_format';
				$wordpressfields['Comment Status'] = 'comment_status';
				$wordpressfields['Ping Status'] = 'ping_status';
				$wordpressfields['Track Options'] = 'pinged';
				$wordpressfields['Parent'] = 'post_parent';
				$wordpressfields['Order'] = 'menu_order';
			}
			if ($import_type === 'Pages') {
				$wordpressfields['Parent'] = 'post_parent';
				$wordpressfields['Order'] = 'menu_order';
				$wordpressfields['Page Template'] = 'wp_page_template';
				$wordpressfields['Comment Status'] = 'comment_status';
				$wordpressfields['Ping Status'] = 'ping_status';
			}

			if($mode == 'Insert'){
				unset($wordpressfields['ID']);
			}
			if($import_types == 'lp_lesson'){
				unset($wordpressfields['Format']);
				unset($wordpressfields['Featured Image']);
				unset($wordpressfields['Short Description']);
				unset($wordpressfields['Author']);
				unset($wordpressfields['Parent']);
				unset($wordpressfields['Order']);
			}
	
			if($import_types == 'lp_quiz' || $import_types == 'lp_question'){
				unset($wordpressfields['Format']);
				unset($wordpressfields['Featured Image']);
				unset($wordpressfields['Short Description']);
				unset($wordpressfields['Author']);
				unset($wordpressfields['Comment Status']);
				unset($wordpressfields['Ping Status']);
				unset($wordpressfields['Track Options']);
				unset($wordpressfields['Parent']);
				unset($wordpressfields['Order']);
			}
		} 
	
		if($import_types == 'lp_order'){
			$wordpressfields = array(
				'Order Status' => 'order_status',
				'Order Date' => 'order_date',
				'Order ID' => 'ORDER_ID'
			);
			if($mode == 'Insert'){
				unset($wordpressfields['Order ID']);
			}
		}
		if($import_types == 'nav_menu_item'){
			$wordpressfields = array(
				'Menu Title' => 'menu_title',
				'Menu Type' => '_menu_item_type',
				'Menu Items' => '_menu_item_object',
				'Menu Item Ids' => '_menu_item_object_id',
				'Menu Custom Url' => '_menu_item_url',
				'Menu Auto Add' => 'menu_auto_add'
			); 

			$get_navigation_locations = get_nav_menu_locations();
			foreach($get_navigation_locations as $nav_key => $nav_values){
				$wordpressfields[$nav_key] = $nav_key;
			}
		}

		if($import_types == 'widgets') {
			$wordpressfields = array(
				'Recent Posts'   => 'widget_recent-posts',
				'Pages'          => 'widget_pages',
				'Recent Comments'=> 'widget_recent-comments',
				'Archieves' => 'widget_archives',
				'Categories'     => 'widget_categories'
			);
		}

		if($import_type == 'Users'){
			$wordpressfields = array(
					'User Login' => 'user_login',
					'User Pass' => 'user_pass',
					'First Name' => 'first_name',
					'Last Name' => 'last_name',
					'Nick Name' => 'nickname',
					'User Email' => 'user_email',
					'User URL' => 'user_url',
					'User Nicename' => 'user_nicename',
					'User Registered' => 'user_registered',
					'Display Name' => 'display_name',
					'User Role' => 'role',
					'Biographical Info' => 'biographical_info',
					'Disable Visual Editor' => 'disable_visual_editor',
					'Syntax Highlighting' => 'syntax_highlighting',
					'Admin Color Scheme' => 'admin_color',
					'Enable Keyboard Shortcuts' => 'enable_keyboard_shortcuts',
					'Show Toolbar' => 'show_toolbar',
					'Language' => 'language',
					'ID' => 'ID'
					);
			if($mode == 'Insert'){
				unset($wordpressfields['ID']);
			}
		}
		
		if($import_type === 'Comments') {
			$wordpressfields = array(
					'Comment Post Id' => 'comment_post_ID',
					'Comment Author' => 'comment_author',
					'Comment Author Email' => 'comment_author_email',
					'Comment Author URL' => 'comment_author_url',
					'Comment Content' => 'comment_content',
					'Comment Rating' => 'comment_rating',
					'Comment Author IP' => 'comment_author_IP',
					'Comment Date' => 'comment_date',
					'Comment Approved' => 'comment_approved',
					'Comment Parent' => 'comment_parent', 
					'user_id'=>'user_id',
					);
		}
		if($import_type === 'Images') {
			$wordpressfields = array(
					'Caption' => 'caption',
					'Alt text' => 'alt_text',
					'Description' => 'description',
					'ID' => 'ID',
					'File Name' => 'file_name',
					'Title' => 'title',
					//'Featured Image' => 'featured_image');
					//changed
					'Image URL' => 'image_url');
		}
		if($import_type === 'ngg_pictures') {
			$wordpressfields = array(
				     'ID' =>'id',
				    'Filename' => 'filename',
					'Alt text' => 'alt_text',
					'Description' => 'description',
					'Featured Image' => 'featured_image',
					'Nextgen Gallery' => 'nextgen_gallery',
					'Manage Tags' =>'manage_tags');
		}
		if($import_type === 'Taxonomies') {
			$wordpressfields = array(
					'Taxonomy Name' => 'name',
					'Taxonomy Slug' => 'slug',
					'Taxonomy Description' => 'description',
					'Term ID' => 'TERMID',
					);
			if($mode == 'Insert'){
				unset($wordpressfields['Term ID']);
			}
		}
		if($import_type === 'Categories') {
			$wordpressfields = array(
					'Category Name' => 'name',
					'Category Slug' => 'slug',
					'Category Description' => 'description',
					'Term ID' => 'TERMID',
					'Parent' => 'parent',
					);
			if($mode == 'Insert'){
				unset($wordpressfields['Term ID']);
			}	
		}
		if($import_type === 'Tags') {
			$wordpressfields = array(
					'Tag Name' => 'name',
					'Tag Slug' => 'slug',
					'Tag Description' => 'description',
					'Term ID' => 'TERMID',
					);
			if($mode == 'Insert'){
				unset($wordpressfields['Term ID']);
			}	
		}
		if($import_type === 'CustomerReviews') {
			if(is_plugin_active('wp-customer-reviews/wp-customer-reviews-3.php') || is_plugin_active('wp-customer-reviews/wp-customer-reviews.php')){
				$wordpressfields = array(
					'Review Date Time' => 'date_time',
					'Reviewer Name' => 'review_name',
					'Reviewer Email' => 'review_email',
					'Reviewer IP' => 'review_ip',
					'Review Format' => 'review_format',
					'Review Title' => 'review_title',
					'Review Text' => 'review_text',
					'Review Response' => 'review_admin_response',
					'Review Status' => 'status',
					'Review Rating' => 'review_rating',
					'Review URL' => 'review_website',
					'Review to Post/Page Id' => 'review_post',
					'Review ID' => 'review_id',
					);
				if($mode == 'Insert'){
					unset($wordpressfields['Review ID']);
				}
			}
		}
		if(is_plugin_active('jet-engine/jet-engine.php')){
			global $wpdb;
			$get_slug_name = $wpdb->get_results("SELECT slug FROM {$wpdb->prefix}jet_post_types WHERE status = 'content-type'");
			
			foreach($get_slug_name as $key=>$get_slug){
				$value=$get_slug->slug;
				if($import_type == $value){
					$wordpressfields=array(
						'_ID'=>'_ID',
						'Status'=>'cct_status',			
					);
					if($mode == 'Insert'){
						unset($wordpressfields['_ID']);
					}
				}
			}
		}
		
		$wordpress_value = $this->convert_static_fields_to_array($wordpressfields);
		$response['core_fields'] = $wordpress_value ;
		return $response;
	} 

	/**
	* Core Fields extension supported import types
	* @param string $import_type - selected import type
	* @return boolean
	*/
	public function extensionSupportedImportType($import_type){
		return true;
	}

}
