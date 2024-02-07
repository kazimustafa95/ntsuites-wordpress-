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

class AllInOneSeoExtension extends ExtensionHandler{
	private static $instance = null;

    public static function getInstance() {	
		if (AllInOneSeoExtension::$instance == null) {
			AllInOneSeoExtension::$instance = new AllInOneSeoExtension;
		}
		return AllInOneSeoExtension::$instance;
    }

	/**
	* Provides All In One Seo mapping fields for specific post type
	* @param string $data - selected import type
	* @return array - mapping fields
	*/
    public function processExtension($data) {
        $response = [];
        $all_in_one_seo_Fields = array(
			// 'Keywords' => 'keywords',
			// 'Description' => 'description',
			// 'Title' => 'title',
			'NOINDEX' => 'noindex',
			'NOFOLLOW' => 'nofollow',
			'Canonical URL' => 'custom_link',
			// 'Title Atr' => 'titleatr',
			// 'Menu Label' => 'menulabel',
			'Disable' => 'disable',
			'Disable Analytics' => 'disable_analytics',
			'NOODP' => 'noodp',
			'NOYDIR' => 'noydir',
			'Aioseo Title'=>'aioseo_title',
			'Aioseo description'=>'aioseo_description',
			'Facebook Title'=>'og_title',
			'Facebook Description'=>'og_description',
			'Facebook Image Source'=>'og_image_type',
			'Video URL'=>'og_video',
			'Object Type'=>'og_object_type',
			'Article Section'=>'og_article_section',
			'Article Tags'=>'og_article_tags',
			'Use Data from Facebook Tab'=>'twitter_use_og',
			'Twitter Card Type'=>'twitter_card',
			'Twitter Image Source'=>'twitter_image_type',
			'Twitter Title'=>'twitter_title',
			'Twitter Description'=>'twitter_description',
			'Robots Settings'=>'robots_default',
			'Robots No Archive'=>'robots_noarchive',
			'Robots No Snippet'=>'robots_nosnippet',
			'Robots No Image Index'=>'robots_noimageindex',
			'Robots No Translate'=>'robots_notranslate',
			'Robots Max Snippet'=>'robots_max_snippet',
			'Robots Max Video Preview'=>'robots_max_videopreview',
			'Robots Max Image Preview'=>'robots_max_imagepreview',
			'Keyphrases'=>'keyphrases'
        );
		$all_in_one_seo_value = $this->convert_static_fields_to_array($all_in_one_seo_Fields);
		$response['all_in_one_seo_fields'] = $all_in_one_seo_value ;
		return $response;	
    }

	/**
	* All In One Seo extension supported import types
	* @param string $import_type - selected import type
	* @return boolean
	*/
    public function extensionSupportedImportType($import_type){
		if(is_plugin_active('all-in-one-seo-pack/all_in_one_seo_pack.php')|| is_plugin_active('all-in-one-seo-pack-pro/all_in_one_seo_pack.php')){
			if($import_type == 'nav_menu_item'){
				return false;
			}

			$import_type = $this->import_name_as($import_type);
			if($import_type == 'Posts' || $import_type == 'Pages' || $import_type == 'CustomPosts' ) {	
				return true;
			}
			else{
				return false;
			}
		}
	}
}