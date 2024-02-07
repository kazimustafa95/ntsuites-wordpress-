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

class RankMathImport {
    private static $rankmath_instance = null;

    public static function getInstance() {
		
			if (RankMathImport::$rankmath_instance == null) {
				RankMathImport::$rankmath_instance = new RankMathImport;
				return RankMathImport::$rankmath_instance;
			}
			return RankMathImport::$rankmath_instance;
		}
		
    function set_rankmath_values($header_array ,$value_array , $map, $post_id , $type){	
			$post_values = [];
			$helpers_instance = ImportHelpers::getInstance();
			$post_values = $helpers_instance->get_header_values($map , $header_array , $value_array);

			$this->rankmath_import_function($post_values,$type, $post_id, $header_array , $value_array);
    }

    function rankmath_import_function($data_array, $importas, $pID, $header_array , $value_array) {
		global $wpdb;
			$createdFields = $rankmathData = array();
			$media_instance = MediaHandling::getInstance();
			foreach ($data_array as $dkey => $dvalue) {
				$createdFields[] = $dkey;
			}
			
			if (isset($data_array['rank_math_focus_keyword'])) {
				$custom_array['rank_math_focus_keyword']= $data_array['rank_math_focus_keyword']; 
			}
			if (isset($data_array['rank_math_pillar_content'])) {
				$custom_array['rank_math_pillar_content']= $data_array['rank_math_pillar_content'];
			}
			// if (isset($data_array['rank_math_robots'])) {
			// 	$robots= $data_array['rank_math_robots'];
			// 	$custom_array['rank_math_robots']=array($robots);
			// }
			//added - rank math robots meta are provided as separate fields in mapping - combining them together for db entry
			$rank_math_robots_meta = [];
			if (isset($data_array['index']) && $data_array['index'] == 1){
				$rank_math_robots_meta[] = 'index';
			}
			if (isset($data_array['nofollow']) && $data_array['nofollow'] == 1){
				$rank_math_robots_meta[] = 'nofollow';
			}
			if (isset($data_array['noimageindex']) && $data_array['noimageindex'] == 1){
				$rank_math_robots_meta[] = 'noimageindex';
			}
			if (isset($data_array['noindex']) && $data_array['noindex'] == 1){
				$rank_math_robots_meta[] = 'noindex';
			}
			if (isset($data_array['noarchive']) && $data_array['noarchive'] == 1){
				$rank_math_robots_meta[] = 'noarchive';
			}
			if (isset($data_array['nosnippet']) && $data_array['nosnippet'] == 1){
				$rank_math_robots_meta[] = 'nosnippet';
			}
			$custom_array['rank_math_robots'] = $rank_math_robots_meta;
			if (isset($data_array['rank_math_advanced_robots'])) {
				$rank_math_advanced_robots = $data_array['rank_math_advanced_robots'];
				$rank_math_advanced=explode(',',$rank_math_advanced_robots);
				$max_snippet=$rank_math_advanced[0];
				$max_video_preview=$rank_math_advanced[1];
				$max_image_preview=$rank_math_advanced[2];

				$custom_array['rank_math_advanced_robots'] = [
					'max-snippet'       => $max_snippet,
					'max-video-preview' => $max_video_preview,
					'max-image-preview' => $max_image_preview,
				];
			}
			if (isset($data_array['rank_math_canonical_url'])) {
				$custom_array['rank_math_canonical_url']= $data_array['rank_math_canonical_url'];
			}
			if (isset($data_array['redirection_type'])) {
				$redirection_type= $data_array['redirection_type'];
				$redirection_table_name=$wpdb->prefix ."rank_math_redirections";
				$destination_url= $data_array['destination_url'];
				
					$sources=[	
						'ignore'=> '',
						'pattern'    => 'index.php'.'/'.date('y/d/m').'/'.$data_array['rank_math_focus_keyword'],	
						'comparison' => 'exact',
					];
					$from_url='index.php'.'/'.date('y/d/m').'/'.$data_array['rank_math_focus_keyword'];					
					$sourcess=array($sources);
					$source=serialize($sourcess);
					$wpdb->insert("{$wpdb->prefix}rank_math_redirections", array('sources' => $source,'url_to' => $destination_url,'header_code' => $redirection_type,'created'=>date('Y-m-d H:i:s'),'updated'=>date('Y-m-d H:i:s'))); 
					$get_id=$wpdb->get_results("select id from {$wpdb->prefix}rank_math_redirections where url_to = '$destination_url'" ,ARRAY_A);
					$redirection_id=$get_id[0]['id'];
										
					$wpdb->insert("{$wpdb->prefix}rank_math_redirections_cache", array('from_url' => $from_url,'redirection_id' => $redirection_id,'object_id' => $pID,'is_redirected'=>'1')); 
			}
			if (isset($data_array['headline'])) {
				$headline= $data_array['headline'];
			}
			if (isset($data_array['schema_description'])) {
				$schema_description = $data_array['schema_description'];	
			}
			if (isset($data_array['article_type'])) {
				$article_type= $data_array['article_type'];	
			}
			if(isset($data_array['image_type'])){
				$image_type=$data_array['image_type'];
			}
			if(isset($data_array['image_url'])){
				$image_url=$data_array['image_url'];
			}
			if(isset($data_array['author_type'])){
				$author_type=$data_array['author_type'];
			}
			if(isset($data_array['author_name'])){
				$author_name=$data_array['author_name'];
			}
			if(isset($data_array['enable_speakable'])){
				$enable_speakable=$data_array['enable_speakable'];
			}
			if(isset($data_array['speakable_type'])){
				$speakable_type=$data_array['speakable_type'];
			}
			if(isset($data_array['date_modified'])){
				$date_modified=$data_array['date_modified'];
			}
			if(isset($data_array['date_published'])){
				$date_published=$data_array['date_published'];
			}
			if(isset($data_array['cssSelector'])){
				$cssSelector=$data_array['cssSelector'];
			}
			if(isset($data_array['advanced_editor'])){
				$advanced_editor_value=$data_array['advanced_editor'];
				$values=explode('|',$advanced_editor_value);
				foreach($values as $value){
					$advanced_value=explode('->',$value);
					$editor_values=$advanced_value[0];
					if($editor_values == 'image'){
						$editor_value=$advanced_value[1];
						$image_values=explode(';',$editor_value);
						$image_details=array();
						foreach($image_values as $img_values){
							$img_value=explode(':',$img_values);
							$image_key=$img_value[0];
							$image_value=$img_value[1];
							$image_details[$image_key]=$image_value;
						}
					}
					else if($editor_values == 'author'){
						$editor_value=$advanced_value[1];
						$author_values=explode(';',$editor_value);
						$author_details=array();
						foreach($author_values as $auth_values){
							$auth_value=explode(':',$auth_values);
							$author_key=$auth_value[0];
							$author_value=$auth_value[1];
							$author_details[$author_key]=$author_value;
						}
					}
					else if($editor_values == 'speakable'){
						$editor_value=$advanced_value[1];
						$speakable_values=explode(';',$editor_value);
						$speakable_details=array();
						foreach($speakable_values as $speak_values){
							$speak_value=explode(':',$speak_values);
							$speakable_key=$speak_value[0];
							$speakable_value=$speak_value[1];
							$speakable_details[$speakable_key]=$speakable_value;
						}
					}
					else{
						$editor_value=$advanced_value[1];
						$new_property=explode(';',$editor_value);
						$property_details=array();
						foreach($new_property as $property){
							$new_property_value=explode(':',$property);
							$property_key=$new_property_value[0];
							$property_value=$new_property_value[1];
							$property_details[$property_key]=$property_value;
						}
					}
				}
			}
			if(isset($data_array['advanced_editor_group_values'])){
				$advanced_editor_group_values=$data_array['advanced_editor_group_values'];
				$values=explode('|',$advanced_editor_group_values);
				foreach($values as $value){
					$advanced_group_value=explode('->',$value);
					$group_editor_values=$advanced_group_value[0];
					if($group_editor_values == 'image'){
						$editor_group_value=$advanced_group_value[1];
						$editor_values=explode(',',$editor_group_value);
						foreach($editor_values as $editor_value){
							$image_group_values=explode(';',$editor_value);
							$image_detail=array();
							foreach($image_group_values as $img_group_values){
								$img_group_value=explode(':',$img_group_values);
								$image_group_key=$img_group_value[0];
								$image_group_value=$img_group_value[1];
								$image_detail[$image_group_key]=$image_group_value;								
							}
							$image_group_details[] = $image_detail;
						}
					}
					else if($group_editor_values == 'author'){
						$editor_group_value=$advanced_group_value[1];
						$editor_values=explode(',',$editor_group_value);
						foreach($editor_values as $editor_value){
							$author_group_values=explode(';',$editor_value);
							$author_detail=array();
							foreach($author_group_values as $auth_group_values){
								$auth_group_value=explode(':',$auth_group_values);
								$author_group_key=$auth_group_value[0];
								$author_group_value=$auth_group_value[1];
								$author_detail[$author_group_key]=$author_group_value;
							}
							$author_group_details[] = $author_detail;
						}	
					 }
					else if($group_editor_values == 'speakable'){
						$editor_group_value=$advanced_group_value[1];
						$editor_values=explode(',',$editor_group_value);
						foreach($editor_values as $editor_value){
							$speakable_group_values=explode(';',$editor_value);
							$speakable_detail=array();
							foreach($speakable_group_values as $speak_group_values){
								$speak_group_value=explode(':',$speak_group_values);
								$speakable_group_key=$speak_group_value[0];
								$speakable_group_value=$speak_group_value[1];
								$speakable_detail[$speakable_group_key]=$speakable_group_value;
							}
							$speakable_group_details[] = $speakable_detail;
						}
					}
					else{
						$editor_group_value=$advanced_group_value[1];
						$editor_values=explode(',',$editor_group_value);
						foreach($editor_values as $editor_value){
							$new_property_group=explode(';',$editor_value);
							$property_detail=array();
							foreach($new_property_group as $property_group){
								$new_property_group_value=explode(':',$property_group);
								$property_group_key=$new_property_group_value[0];
								$property_group_value=$new_property_group_value[1];
								$property_detail[$property_group_key]=$property_group_value;
							}
							$property_group_details[] = $property_detail;
						}
					}
				}
			}
			if (isset($data_array['cssSelector'])) {
				$selector=explode(',',$cssSelector);
			}
			if(is_plugin_active('seo-by-rank-math-pro/rank-math-pro.php')){
				$key=array("headline","description","@type","enableSpeakable","datePublished","dateModified");
					$rank_math_schema=array($headline,$schema_description,$article_type,$enable_speakable,$date_published,$date_modified);
					$rank_math_schem=array_combine($key,$rank_math_schema);	
					$rank_math=array_merge($rank_math_schem,$property_details,$property_group_details);
					//image details
					$image_key=array("@type","url");
					$schema_values=array($image_type,$image_url);
					$schema_value=array_combine($image_key,$schema_values);
					$schema['image']=array_merge($schema_value,$image_details,$image_group_details);
					//author details
					$author_key=array("@type","name");
					$author_values=array($author_type,$author_name);
					$author_value=array_combine($author_key,$author_values);
					$author['author']=array_merge($author_value,$author_details,$author_group_details);
					//speakable details
					$speakable_key=array("@type","cssSelector");
					$speakable_values=array($speakable_type,$selector);
					$speakable_value=array_combine($speakable_key,$speakable_values);
					$speakable['speakable']=array_merge($speakable_value,$speakable_details,$speakable_group_details);
					$schems['metadata']=[						
						'title'     => 'Article' ,
						'type'      => 'template',
						'shortcode' => uniqid( 's-' ),
						'isPrimary' => true,
						'enableSpeakable'=>$enable_speakable,
					
					];
					$array_rank_math=array_merge($schems,$schema,$rank_math,$speakable,$author);						
					update_post_meta($pID, 'rank_math_schema_BlogPosting', $array_rank_math);
			}
			else{
				$key=array("headline","description","@type");
				$rank_math_schema=array($headline,$schema_description,$article_type);
				$rank_math=array_combine($key,$rank_math_schema);			
				$schems['metadata']=[	
				
					'title'     => 'Article' ,
					'type'      => 'template',
					'shortcode' => uniqid( 's-' ),
					'isPrimary' => true,
				
				];
				$author['author']=[
					'@type'=>'Person',
					'name'=>'%name%',
				];

				$date_variables = [
					'datePublished' => '%date(Y-m-dTH:i:sP)%',
					'dateModified'  => '%modified(Y-m-dTH:i:sP)%',
				];
				$schema['image'] = [
					'@type' => 'ImageObject',
					'url'   => '%post_thumbnail%',
				];
				$custom_array['rank_math_schema_BlogPosting']=array_merge($schems,$schema,$rank_math,$author,$date_variables);
			}
			if (isset($data_array['rank_math_title'])) {
				$custom_array['rank_math_title']= $data_array['rank_math_title'];
			}
			if (isset($data_array['_wp_old_slug'])) {
				$custom_array['_wp_old_slug']= $data_array['_wp_old_slug'];
			}
			if (isset($data_array['rank_math_description'])) {
				$custom_array['rank_math_description']= $data_array['rank_math_description'];
			}
			if (isset($data_array['rank_math_facebook_title'])) {
				$custom_array['rank_math_facebook_title']= $data_array['rank_math_facebook_title'];
			}
			if (isset($data_array['rank_math_facebook_description'])) {
				$custom_array['rank_math_facebook_description']= $data_array['rank_math_facebook_description'];
			}
			if (isset($data_array['rank_math_facebook_image'])) {
				$custom_array['rank_math_facebook_image']= $data_array['rank_math_facebook_image'];
			}
			if (isset($data_array['rank_math_facebook_enable_image_overlay'])) {
				$custom_array['rank_math_facebook_enable_image_overlay']= $data_array['rank_math_facebook_enable_image_overlay'];
			}
			if (isset($data_array['rank_math_facebook_image_overlay'])) {
				$custom_array['rank_math_facebook_image_overlay']= $data_array['rank_math_facebook_image_overlay'];
			}
			if (isset($data_array['rank_math_twitter_use_facebook'])) {
				$custom_array['rank_math_twitter_use_facebook']= $data_array['rank_math_twitter_use_facebook'];
			}
			if (isset($data_array['rank_math_twitter_title'])) {
				$custom_array['rank_math_twitter_title']= $data_array['rank_math_twitter_title'];
			}
			if (isset($data_array['rank_math_twitter_description'])) {
				$custom_array['rank_math_twitter_description'] = $data_array['rank_math_twitter_description'];
			}
			if (isset($data_array['rank_math_twitter_card_type'])) {
				$custom_array['rank_math_twitter_card_type']= $data_array['rank_math_twitter_card_type'];
			}
			if(isset($data_array['rank_math_twitter_app_description'])) {
				$custom_array['rank_math_twitter_app_description']= $data_array['rank_math_twitter_app_description'];
			}
			if(isset($data_array['rank_math_twitter_app_iphone_name'])) {
				$custom_array['rank_math_twitter_app_iphone_name']= $data_array['rank_math_twitter_app_iphone_name'];
			}
			if(isset($data_array['rank_math_twitter_app_iphone_id'])) {
				$custom_array['rank_math_twitter_app_iphone_id']= $data_array['rank_math_twitter_app_iphone_id'];
			}
			if(isset($data_array['rank_math_twitter_app_iphone_url'])) {
				$custom_array['rank_math_twitter_app_iphone_url']= $data_array['rank_math_twitter_app_iphone_url'];
			}
			if(isset($data_array['rank_math_twitter_app_ipad_name'])) {
				$custom_array['rank_math_twitter_app_ipad_name']= $data_array['rank_math_twitter_app_ipad_name'];
			}
			if(isset($data_array['rank_math_twitter_app_ipad_id'])) {
				$custom_array['rank_math_twitter_app_ipad_id']= $data_array['rank_math_twitter_app_ipad_id'];
			}
			if(isset($data_array['rank_math_twitter_app_ipad_url'])) {
				$custom_array['rank_math_twitter_app_ipad_url']= $data_array['rank_math_twitter_app_ipad_url'];
			}
			if(isset($data_array['rank_math_twitter_app_googleplay_name'])) {
				$custom_array['rank_math_twitter_app_googleplay_name']= $data_array['rank_math_twitter_app_googleplay_name'];
			}
			if(isset($data_array['rank_math_twitter_app_googleplay_id'])) {
				$custom_array['rank_math_twitter_app_googleplay_id']= $data_array['rank_math_twitter_app_googleplay_id'];
			}
			if(isset($data_array['rank_math_twitter_app_googleplay_url'])) {
				$custom_array['rank_math_twitter_app_googleplay_url']= $data_array['rank_math_twitter_app_googleplay_url'];
			}
			if(isset($data_array['rank_math_twitter_app_country'])) {
				$custom_array['rank_math_twitter_app_country']= $data_array['rank_math_twitter_app_country'];
			}
			if(isset($data_array['rank_math_twitter_player_url'])) {
				$custom_array['rank_math_twitter_player_url']= $data_array['rank_math_twitter_player_url'];
			}
			if(isset($data_array['rank_math_twitter_player_size'])) {
				$custom_array['rank_math_twitter_player_size']= $data_array['rank_math_twitter_player_size'];
			}
			if(isset($data_array['rank_math_twitter_player_stream'])) {
				$custom_array['rank_math_twitter_player_stream']= $data_array['rank_math_twitter_player_stream'];
			}
			if(isset($data_array['rank_math_twitter_player_stream_ctype'])) {
				$custom_array['rank_math_twitter_player_stream_ctype']= $data_array['rank_math_twitter_player_stream_ctype'];
			}
			if (!empty ($custom_array)) {
				foreach ($custom_array as $custom_key => $custom_value) {
					update_post_meta($pID, $custom_key, $custom_value);
				}
			}
		
		}

}