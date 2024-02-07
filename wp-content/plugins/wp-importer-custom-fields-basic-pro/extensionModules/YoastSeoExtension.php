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

class YoastSeoExtension extends ExtensionHandler{
	private static $instance = null;

    public static function getInstance() {
		
		if (YoastSeoExtension::$instance == null) {
			YoastSeoExtension::$instance = new YoastSeoExtension;
		}
		return YoastSeoExtension::$instance;
    }

	/**
	* Provides Yoast Seo fields for specific post type
	* @param string $data - selected import type
	* @return array - mapping fields
	*/
    public function processExtension($data) {
		
        $response = [];
        $yoastseoFields = array(
			'SEO Title' => 'title',
			'Meta Description' => 'meta_desc',
			'Meta Robots Index' => 'meta-robots-noindex',
			'Meta Robots Follow' => 'meta-robots-nofollow',
			'Meta Robots Advanced' => 'meta-robots-adv',
			'Breadcrumbs Title'  => 'bctitle',
			// 'Include in Sitemap' => 'sitemap-include',
			// 'Sitemap Priority' => 'sitemap-prio',
			'Canonical URL' => 'canonical',
			'301 Redirect' => 'redirect',
			'Facebook Title' => 'opengraph-title',
			'Facebook Description' => 'opengraph-description',
			'Facebook Image' => 'opengraph-image',
			'Twitter Title' => 'twitter-title',
			'Twitter Description' => 'twitter-description',
			'Twitter Image' => 'twitter-image',
			'Google+ Title' => 'google-plus-title',
			'Google+ Description' => 'google-plus-description',
			'Google+ Image' => 'google-plus-image',
			'Focus Keyword' => 'focus_keyword',
			'Schema Page Type' => 'schema_page_type',
			'Schema Article Type' => 'schema_article_type',
			'Cornerstone Content' => 'cornerstone-content',
			'Keyphrase' => 'focuskeywords',
			'Synonyms' => 'keywordsynonyms'
		);
		
		// if(in_array($data , get_taxonomies())){
		// 	unset($yoastseoFields['Cornerstone Content']);
		// }

		$yoast_seo_value = $this->convert_static_fields_to_array($yoastseoFields);
		$response['yoast_seo_fields'] = $yoast_seo_value ;
		return $response;
    }

	/**
	* Yoast Seo extension supported import types
	* @param string $import_type - selected import type
	* @return boolean
	*/
    public function extensionSupportedImportType($import_type ){
		if(is_plugin_active('wordpress-seo/wp-seo.php') || is_plugin_active('wordpress-seo-premium/wp-seo-premium.php')){
			if($import_type == 'nav_menu_item'){
				return false;
			}

			$import_type = $this->import_name_as($import_type);
			if($import_type == 'Posts' || $import_type == 'Pages' || $import_type == 'CustomPosts' || $import_type == 'Taxonomies' || $import_type == 'Tags' || $import_type == 'Categories' ) {	
				return true;
			}
			else{
				return false;
			}
		}
	}
}