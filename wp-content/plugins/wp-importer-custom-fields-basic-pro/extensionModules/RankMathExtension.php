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

class RankMathExtension extends ExtensionHandler{
	private static $instance = null;

    public static function getInstance() {
		
		if (RankMathExtension::$instance == null) {
			RankMathExtension::$instance = new RankMathExtension;
		}
		return RankMathExtension::$instance;
    }

	/**
	* Provides Rank Math fields for specific post type
	* @param string $data - selected import type
	* @return array - mapping fields
	*/
    public function processExtension($data) {	
		$response = [];
		if(is_plugin_active('seo-by-rank-math/rank-math.php') ){
			$rankmathFields = array(
				'Focus Keyword' => 'rank_math_focus_keyword',
				'This post is Pillar Content' => 'rank_math_pillar_content',
				//'Robots Meta' => 'rank_math_robots',
				'Robots Meta Index' => 'index',
				'Robots Meta Nofollow' => 'nofollow',
				'Robots Meta No Image Index' => 'noimageindex',
				'Robots Meta No Index' => 'noindex',
				'Robots Meta No Archive' => 'noarchive',
				'Robots Meta No Snippet' => 'nosnippet',
				'Advanced Robots Meta' => 'rank_math_advanced_robots',
				'Canonical URL' => 'rank_math_canonical_url',
				'Redirection Type'  => 'redirection_type', 
				'Destination URL' => 'destination_url',
				'Headline' => 'headline',
				'Schema Description' => 'schema_description',
				'Article Type'=>'article_type',
				'General Title'=>'rank_math_title',
				'Permalink'=>'_wp_old_slug',
				'General Description'=>'rank_math_description',
				'Facebook Title' =>'rank_math_facebook_title',
				'Facebook Description' =>'rank_math_facebook_description',
				'Facebook Image' => 'rank_math_facebook_image',
				'Add icon overlay to thumbnail'=>'rank_math_facebook_enable_image_overlay',
				'Icon overlay'=>'rank_math_facebook_image_overlay',
				
				'Use Data from Facebook Tab'=>'rank_math_twitter_use_facebook',
				'Twitter title' => 'rank_math_twitter_title',
				'Twitter Description' => 'rank_math_twitter_description',
				'Card Type'=>'rank_math_twitter_card_type',
				// 'App Card'=>'app_card',
				'App Description'=>'rank_math_twitter_app_description',
				'iPhone App Name'=>'rank_math_twitter_app_iphone_name',
				'iPhone App ID'=>'rank_math_twitter_app_iphone_id',
				'iPhone App URL'=>'rank_math_twitter_app_iphone_url',
				'iPad App Name'=>'rank_math_twitter_app_ipad_name',
				'iPad App ID'=>'rank_math_twitter_app_ipad_id',
				'iPad App URL'=>'rank_math_twitter_app_ipad_url',
				'Google Play App Name'=>'rank_math_twitter_app_googleplay_name',
				'Google Play App ID'=>'rank_math_twitter_app_googleplay_id',
				'Google Play App URL'=>'rank_math_twitter_app_googleplay_url',
				'App Country'=>'rank_math_twitter_app_country',
				'Player URL'=>'rank_math_twitter_player_url',
				'Player Size'=>'rank_math_twitter_player_size',
				'Stream URL'=>'rank_math_twitter_player_stream',
				'Stream Content Type'=>'rank_math_twitter_player_stream_ctype'			
			);
		
			if(in_array($data , get_taxonomies())){
				unset($rankmathFields['Cornerstone Content']);
			}
		}
		if(is_plugin_active('seo-by-rank-math-pro/rank-math-pro.php')){
			$rankmathProFields=array(
				'cssSelector'=>'cssSelector',
				'Image Type'=>'image_type',
				'Image Url'=>'image_url',
				'Author Type'=>'author_type',
				'Author Name'=>'author_name',
				'Speakable Type'=>'speakable_type',
				'Enable Speakable'=>'enable_speakable',
				'DateModified'=>'date_modified',
				'DatePublished'=>'date_published',
				'Advanced Editor'=>'advanced_editor',
				'Advanced Editor Group Values'=>'advanced_editor_group_values'
			);

			foreach($rankmathProFields as $key => $value){
				$rankmathFields[$key] = $value;
			}
		}

		if(is_plugin_active('seo-by-rank-math-pro/rank-math-pro.php')){
			$rank_math_value = $this->convert_static_fields_to_array($rankmathFields);
			$response['rank_math_pro_fields'] = $rank_math_value ;
			return $response;
		}
		else{
			$rank_math_value = $this->convert_static_fields_to_array($rankmathFields);
			$response['rank_math_fields'] = $rank_math_value ;
			return $response;
		}
    }

	/**
	* Rank Math extension supported import types
	* @param string $import_type - selected import type
	* @return boolean
	*/
    public function extensionSupportedImportType($import_type ){
		if(is_plugin_active('seo-by-rank-math/rank-math.php') || is_plugin_active('seo-by-rank-math-pro/rank-math-pro.php')){
			if($import_type == 'nav_menu_item'){
				return false;
			}
			$import_type = $this->import_name_as($import_type);
			if($import_type == 'Posts' || $import_type == 'Pages' || $import_type == 'CustomPosts' || $import_type == 'event' || $import_type == 'event-recurring' || $import_type == 'location' || $import_type == 'Taxonomies' || $import_type == 'Tags' || $import_type == 'Categories' ) {	
				return true;
			}
			if($import_type == 'ticket'){
				if(is_plugin_active('events-manager/events-manager.php')){
					return false;
				}else{
					return true;
				}
			}
			else{
				return false;
			}
		}
	}
}