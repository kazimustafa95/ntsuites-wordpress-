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

class ACFProImport {
	private static $acf_pro_instance = null , $media_instance;

	public static function getInstance() {
		if (ACFProImport::$acf_pro_instance == null) {
			ACFProImport::$acf_pro_instance = new ACFProImport;
			ACFProImport::$media_instance = new MediaHandling;			
			return ACFProImport::$acf_pro_instance;
		}
		return ACFProImport::$acf_pro_instance;
	}

	public function set_acf_pro_values($header_array ,$value_array , $map,$maps, $post_id , $type,$mode,$line_number,$hash_key,$gmode,$templatekey){	
		$acf_instance = ACFImport::getInstance();
		$helpers_instance = ImportHelpers::getInstance();
		$post_values =$helpers_instance->get_meta_values($maps , $header_array , $value_array);
		
		foreach($map as $key => $value){
			$csv_value= trim($map[$key]);
			if(!empty($csv_value) || $csv_value == 0){
				$pattern = "/({([a-z A-Z 0-9 | , _ -]+)(.*?)(}))/";
				if(preg_match_all($pattern, $csv_value, $matches, PREG_PATTERN_ORDER)){	
					$csv_element = $csv_value;
					foreach($matches[2] as $value){
						$get_key = array_search($value , $header_array);
						if(isset($value_array[$get_key])){
							$csv_value_element = $value_array[$get_key];	
							$value = '{'.$value.'}';
							$csv_element = str_replace($value, $csv_value_element, $csv_element);
						}
					}

					$math = 'MATH';
						if (strpos($csv_element, $math) !== false) {		
							$equation = str_replace('MATH', '', $csv_element);
							$csv_element = $helpers_instance->evalmath($equation);
						}
					$wp_element= trim($key);
					if((!empty($csv_element) || $csv_element == '0') && !empty($wp_element)){	
						if(is_plugin_active('advanced-custom-fields-pro/acf.php')){
							$this->acfpro_import_function($wp_element , $csv_element ,$type, $post_id,$mode,$post_values,$line_number,$hash_key,$gmode,$templatekey);

						} else {
							if(is_plugin_active('advanced-custom-fields-pro/acf.php')){
								$acf_pluginPath = WP_PLUGIN_DIR . '/advanced-custom-fields/pro';
								if(is_dir($acf_pluginPath)) {
									$this->acfpro_import_function($wp_element , $csv_element ,$type, $post_id,$mode,$post_values,$line_number,$hash_key,$gmode,$templatekey);
								}
								else
									$acf_instance->acf_import_function($wp_element , $csv_element ,$type, $post_id,$mode,$post_values,$line_number,$hash_key,$gmode,$templatekey);
							}
						}
					}		
				}

				elseif(!in_array($csv_value , $header_array)){
					$wp_element= trim($key);
					if(is_plugin_active('advanced-custom-fields-pro/acf.php')){
						$this->acfpro_import_function($wp_element , $csv_value ,$type, $post_id,$mode,$post_values,$line_number,$hash_key,$gmode,$templatekey);

					} else {
						if(is_plugin_active('advanced-custom-fields/acf.php')){
							$acf_pluginPath = WP_PLUGIN_DIR . '/advanced-custom-fields/pro';
							if(is_dir($acf_pluginPath)) {
								$this->acfpro_import_function($wp_element , $csv_value ,$type, $post_id,$mode,$post_values,$line_number,$hash_key,$gmode,$templatekey);
							}
							else
								$acf_instance->acf_import_function($wp_element , $csv_value ,$type, $post_id,$mode,$post_values,$line_number,$hash_key,$gmode,$templatekey);
						}
					}
				}
				else{
					$get_key= array_search($csv_value , $header_array);
					if(isset($value_array[$get_key])){
						$csv_element = $value_array[$get_key];	
						$wp_element= trim($key);
						if($mode == 'Insert'){
							if((!empty($csv_element) || $csv_element == '0') && !empty($wp_element)){
								if(is_plugin_active('advanced-custom-fields-pro/acf.php')){
									$this->acfpro_import_function($wp_element , $csv_element ,$type, $post_id,$mode,$post_values,$line_number,$hash_key,$gmode,$templatekey);
	
								} else {
									if(is_plugin_active('advanced-custom-fields/acf.php')){
										$acf_pluginPath = WP_PLUGIN_DIR . '/advanced-custom-fields/pro';
										if(is_dir($acf_pluginPath)) {
											$this->acfpro_import_function($wp_element , $csv_element ,$type, $post_id,$mode,$post_values,$line_number,$hash_key,$gmode,$templatekey);
										}
										else
											$acf_instance->acf_import_function($wp_element , $csv_element ,$type, $post_id,$mode,$post_values,$line_number,$hash_key,$gmode,$templatekey);
									}
								}
							}
						}
						else{
							if(!empty($csv_element) || !empty($wp_element)){
								if(is_plugin_active('advanced-custom-fields-pro/acf.php')){
									$this->acfpro_import_function($wp_element , $csv_element ,$type, $post_id,$mode,$post_values,$line_number,$hash_key,$gmode,$templatekey);
	
								} else {
									if(is_plugin_active('advanced-custom-fields/acf.php')){
										$acf_pluginPath = WP_PLUGIN_DIR . '/advanced-custom-fields/pro';
										if(is_dir($acf_pluginPath)) {
											$this->acfpro_import_function($wp_element , $csv_element ,$type, $post_id,$mode,$post_values,$line_number,$hash_key,$gmode,$templatekey);
										}
										else
											$acf_instance->acf_import_function($wp_element , $csv_element ,$type, $post_id,$mode,$post_values,$line_number,$hash_key,$gmode,$templatekey);
									}
								}
							}
							

						}			
					
					}
				}
			}
		}	
	} 

	public function set_acf_rf_values($header_array ,$value_array , $map, $maps, $post_id , $type,$mode,$line_number,$hash_key,$gmode,$templatekey){
		$post_values = [];
		$helpers_instance = ImportHelpers::getInstance();
		$post_values = $helpers_instance->get_header_values($map , $header_array , $value_array);				
		$img_meta = $helpers_instance->get_meta_values($maps , $header_array , $value_array);
		if(is_plugin_active('advanced-custom-fields/acf.php')){
			$acf_pluginPath = WP_PLUGIN_DIR . '/advanced-custom-fields/pro';
			if(is_dir($acf_pluginPath)) {
				$this->acfpro_repeater_import_fuction($post_values,$type, $post_id,$img_meta,$mode,$line_number,$hash_key,$gmode,$templatekey);
			}
		}
		if(is_plugin_active('advanced-custom-fields-pro/acf.php')){
			$this->acfpro_repeater_import_fuction($post_values,$type, $post_id,$img_meta,$mode,$line_number,$hash_key,$gmode,$templatekey);
		} else if(is_plugin_active('acf-repeater/acf-repeater.php')){

		}
	}

    public function set_acf_fc_values($header_array ,$value_array , $map, $maps, $post_id , $type,$mode,$hash_key,$line_number,$gmode,$templatekey){
		$post_values = [];
		$helpers_instance = ImportHelpers::getInstance();

		$post_values = $helpers_instance->get_header_values($map , $header_array , $value_array);		
		$img_meta = $helpers_instance->get_meta_values($maps , $header_array , $value_array);
		if(is_plugin_active('advanced-custom-fields/acf.php')){
			$acf_pluginPath = WP_PLUGIN_DIR . '/advanced-custom-fields/pro';
			if(is_dir($acf_pluginPath)) {
				$this->acfpro_flexible_import_fuction($post_values,$type, $post_id,$img_meta,$mode,$hash_key,$line_number,$gmode,$templatekey);
			}
		}
		if(is_plugin_active('advanced-custom-fields-pro/acf.php')){
			$this->acfpro_flexible_import_fuction($post_values,$type, $post_id,$img_meta,$mode,$hash_key,$line_number,$gmode,$templatekey);
		} else if(is_plugin_active('acf-repeater/acf-repeater.php')){

		}
	}

	public function set_acf_gf_values($header_array ,$value_array , $map,$maps, $post_id , $type,$mode,$line_number,$hash_key,$gmode,$templatekey){
		$post_values = [];
		$helpers_instance = ImportHelpers::getInstance();
		$post_values = $helpers_instance->get_header_values($map , $header_array , $value_array);
		$img_meta = $helpers_instance->get_meta_values($maps , $header_array , $value_array);
		if((is_plugin_active('advanced-custom-fields/acf.php')) || (is_plugin_active('advanced-custom-fields-pro/acf.php'))){ 
			$this->acfpro_group_import_fuction($post_values,$type, $post_id,$img_meta,$mode,$line_number,$hash_key,$gmode,$templatekey);
		} 
	}

	function acfpro_import_function($acf_wpname_element ,$acf_csv_element , $importAs , $post_id,$mode,$imgmeta,$line_number,$hash_key,$gmode,$templatekey){
		global $wpdb;
		$plugin = 'acf';
		$acf_wp_name = $acf_wpname_element;
		$acf_csv_name = $acf_csv_element;
		$map_acf_csv_element = "";
		$helpers_instance = ImportHelpers::getInstance();
		
		//get import type
		$listTaxonomy = get_taxonomies();
		if (in_array($importAs, $listTaxonomy)) {
			$get_import_type = 'term';
		}elseif ($importAs == 'Users') {
			$get_import_type = 'user';
		}elseif ($importAs == 'Comments') {
			$get_import_type = 'comment';
		} else {	
			$get_import_type = 'post';
		}

		$get_acf_fields = $wpdb->get_results($wpdb->prepare("select post_content, post_name from {$wpdb->prefix}posts where post_type = %s and post_excerpt = %s", 'acf-field', $acf_wp_name ), ARRAY_A);
		foreach($get_acf_fields as $keys => $value_type){
			$get_type_field = unserialize($value_type['post_content']);	
			$field_type = $get_type_field['type'];
			$key = $get_acf_fields[0]['post_name'];
			$return_format = isset($get_type_field['return_format']) ? $get_type_field :'';
			if($field_type == 'text' || $field_type == 'textarea' || $field_type == 'number' || $field_type == 'email' || $field_type == 'url' || $field_type == 'password' || $field_type == 'range' || $field_type == 'radio' || $field_type == 'true_false' || $field_type == 'time_picker' || $field_type == 'color_picker' || $field_type == 'button_group' || $field_type == 'oembed' || $field_type == 'wysiwyg'){
				$map_acf_wp_element = $acf_wp_name;
				$map_acf_csv_element = $acf_csv_name;	
			}
			if($field_type == 'date_time_picker'){
				$dt_var = trim($acf_csv_name);
				$dateformat = "Y-m-d H:i:s";
				$date_time_of = $helpers_instance->validate_datefield($dt_var,$acf_wp_name,$dateformat,$line_number);				
				if($mode == 'Insert'){
					if($dt_var == 0 || $dt_var == '')
					$map_acf_csv_element = $dt_var;	
					else{
					
							$map_acf_csv_element = $date_time_of;
					}
				}
				else{
					if($dt_var == 0 || $dt_var == '')
					$map_acf_csv_element = $dt_var;	
					else{
						$map_acf_csv_element = $date_time_of;
					}
				}
				$map_acf_wp_element = $acf_wp_name;					
			}
			if($field_type == 'user'){	
				$maps_acf_csv_name = $acf_csv_name;	
				$map_acf_wp_element = $acf_wp_name;
				$explo_acf_csv_name = explode(',',trim($acf_csv_name));		
				foreach($explo_acf_csv_name as $user){
					if(!is_numeric($explo_acf_csv_name)){
						$userid = $wpdb->get_col($wpdb->prepare("select ID from {$wpdb->prefix}users where user_login = %s",$user));			
						foreach($userid as $users){
							$map_acf_csv_element[] = $users;		
						}
					}
				}
				if(is_numeric($user)){
					$map_acf_csv_element = $user;
				}
			}
			if ($field_type == 'google_map') {

				$location = trim($acf_csv_name);
				list($add, $lat,$lng) = explode('|', $location);
				$area = rtrim($add, ",");
				$map = array(
					'address' => $area,
					'lat'     =>  $lat,
					'lng'     => $lng
				);
				$map_acf_csv_element = $map;
				$map_acf_wp_element = $acf_wp_name;
			}
			if($field_type == 'date_picker'){
				$var = trim($acf_csv_name);
				$dateformat = 'Ymd';
				$date = str_replace('/', '-', "$var");
				$date_of = $helpers_instance->validate_datefield($var,$acf_wp_name,$dateformat,$line_number);				
				if($mode == 'Insert'){
					if($var == 0 || $var == '')
						$map_acf_csv_element = $var;	
					else{
						$map_acf_csv_element = $date_of;
					}
				}
				else{
					if($var == 0 || $var == '')
					$map_acf_csv_element = $var;	
					else{
						$map_acf_csv_element = $date_of;
					}
				}
				$map_acf_wp_element = $acf_wp_name;				
			}
			if($field_type == 'select'){
				if($get_type_field['multiple'] == 0){
					$map_acf_csv_element = $acf_csv_name;
				}else{
					$map_acf_csv_element = array();
					$explo_acf_csv_name = explode(',',trim($acf_csv_name));
					$maps_acf_csv_name = array();
					foreach($explo_acf_csv_name as $explo_csv_value){
						$map_acf_csv_element[] = trim($explo_csv_value);
					}	
				}
				$map_acf_wp_element = $acf_wp_name;
			}
			if($field_type == 'post_object' || $field_type == 'page_link'){
				if($get_type_field['multiple'] == 0){
					$maps_acf_csv_name = $acf_csv_name;
				}else{
					$explo_acf_csv_name = explode(',',trim($acf_csv_name));
					$maps_acf_csv_name = array();
					foreach($explo_acf_csv_name as $explo_csv_value){
						$maps_acf_csv_name[] = trim($explo_csv_value);
					}	
				}
				$map_acf_csv_elements = $maps_acf_csv_name;
				if($get_type_field['multiple'] == 0){
					if (!is_numeric($map_acf_csv_elements ) ){
						$map_acf_csv_elements = $wpdb->_real_escape($map_acf_csv_elements);
					
						$id = $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_title = '{$map_acf_csv_elements}' AND post_status = 'publish' order by ID DESC", ARRAY_A);
						$map_acf_csv_element = isset($id[0]['ID']) ? $id[0]['ID'] : '';
					}
					else{
						$map_acf_csv_element = $maps_acf_csv_name;	
					}
				}
				else{
					foreach($map_acf_csv_elements as $csv_element){
						if (!is_numeric($csv_element ) ){
						$id = $wpdb->get_col($wpdb->prepare("select ID from {$wpdb->prefix}posts where post_title = %s",$csv_element));
						$map_acf_csv_element[] = isset($id[0]) ? $id[0] : '';
					}
					else{
						$map_acf_csv_element = $maps_acf_csv_name;
					}
				}
				}	
				$map_acf_wp_element = $acf_wp_name;
			}
			if($field_type == 'relationship' || $field_type == 'taxonomy'){
				$relations = array();
				$check_is_valid_term = null;
				$get_relations = $acf_csv_name;
				if(!empty($get_relations)){
					$exploded_relations = explode(',', $get_relations);
					foreach ($exploded_relations as $relVal) {
						$relationTerm = trim($relVal);
						//$relTerm[] = $relationTerm;
						if ($field_type == 'taxonomy') {
							$taxonomy_name =  $get_type_field['taxonomy'];
							$check_is_valid_term = $helpers_instance->get_requested_term_details($post_id, array($relationTerm),$taxonomy_name);
							$relations[] = $check_is_valid_term;
						} else {
							$reldata = strlen($relationTerm);
							$checkrelid = intval($relationTerm);
							$verifiedRelLen = strlen($checkrelid);
							if ($reldata == $verifiedRelLen) {
								$relations[] = $relationTerm;
							} else {
								$relVal = $wpdb->_real_escape($relVal);
								$relation_id = $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_title = '$relVal' AND post_status = 'publish'", ARRAY_A);
								if (!empty($relation_id)) {
									$relations[] = $relation_id[0]['ID'];
								}
							}
						}
					}
				}
				$map_acf_csv_element = $relations;
				$map_acf_wp_element = $acf_wp_name;
			}		

			if($field_type == 'checkbox'){
				$explode_acf_csv = explode(',',trim($acf_csv_name));	
				$explode_acf_csv_name = [];
				foreach($explode_acf_csv as $explode_acf_csv_value){
					$explode_acf_csv_name[] = trim($explode_acf_csv_value);
				}	
				$map_acf_csv_element = $explode_acf_csv_name;
				$map_acf_wp_element = $acf_wp_name;
			}
			if($field_type == 'link'){
				$serial_acf_csv = explode(',', $acf_csv_name);
				$serial_acf_csv_name = [];
				foreach($serial_acf_csv as $serial_acf_csv_value){
					$serial_acf_csv_name[] = trim($serial_acf_csv_value);
				}	
				$serial_acf_csv_names['title'] = isset($serial_acf_csv_name[0]) ? $serial_acf_csv_name[0] : '';
				$serial_acf_csv_names['url'] = isset($serial_acf_csv_name[1]) ? $serial_acf_csv_name[1] : '';
				if(isset($serial_acf_csv_name[2]) && $serial_acf_csv_name[2] == 1){
					$serial_acf_csv_names['target'] = '_blank';
				}else{
					$serial_acf_csv_names['target'] = '';
				}
				$map_acf_csv_element = $serial_acf_csv_names;
				$map_acf_wp_element = $acf_wp_name;
			}
			if ($field_type == 'message') {
				$get_type_field['message'] = $acf_csv_name;
			}
			elseif ($field_type == 'image') {
				if ($return_format == 'url' || $return_format == 'array') {
					$ext = pathinfo($acf_csv_name, PATHINFO_EXTENSION);
					if($ext== 'jpg' || $ext == 'jpeg' || $ext == 'png' || $ext = 'gif') {
						$img_id = $wpdb->get_col($wpdb->prepare("select ID from {$wpdb->prefix}posts where guid = %s AND post_type='attachment'",$acf_csv_name));
						if(!empty($img_id)) {
							$map_acf_csv_element=$img_id[0];							
						}
						else {							
							$map_acf_csv_element = ACFProImport::$media_instance->image_meta_table_entry('', $post_id, $acf_wpname_element, $acf_csv_name, $hash_key, 'acf', $get_import_type,$templatekey,$gmode);							
						}
					}
					else {						
						$map_acf_csv_element = ACFProImport::$media_instance->image_meta_table_entry('', $post_id, $acf_wpname_element, $acf_csv_name, $hash_key, 'acf', $get_import_type,$templatekey,$gmode);						
					}
				}
				else {					
					$map_acf_csv_element = ACFProImport::$media_instance->image_meta_table_entry('', $post_id, $acf_wpname_element, $acf_csv_name, $hash_key, 'acf', $get_import_type,$templatekey,$gmode);					
				}
				$map_acf_wp_element = $acf_wp_name;
			}

			elseif ($field_type == 'file') {
				if ($return_format == 'url' || $return_format == 'array') {
					$ext = pathinfo($acf_csv_name, PATHINFO_EXTENSION);
					if($ext=='pdf' || $ext=='mp3' || $ext == $ext ){
						$pdf_id = $wpdb->get_col($wpdb->prepare("select ID from {$wpdb->prefix}posts where guid = %s AND post_type='attachment'",$acf_csv_name));
						if(!empty($pdf_id)) {
							$map_acf_csv_element=$pdf_id[0];
						}
						else {
							$map_acf_csv_element = ACFProImport::$media_instance->media_handling($acf_csv_name, $post_id, $acf_wpname_element);
						}
					}
					else {
						$map_acf_csv_element = ACFProImport::$media_instance->media_handling($acf_csv_name, $post_id, $acf_wpname_element);
					}
				}
				else {
					$map_acf_csv_element = ACFProImport::$media_instance->media_handling($acf_csv_name, $post_id, $acf_wpname_element);
				}
				$map_acf_wp_element = $acf_wp_name;
			}
			elseif($field_type == 'clone'){
				$acf_clone_values = explode(',',$acf_csv_name);
				foreach($acf_clone_values as $clone_value_key=>$clone_values){
					$clone_value = explode('->',$clone_values);
					if(!empty($clone_value)) {
					$cloen_val_key = $clone_value[0];
					$clone_val = array_key_exists(1,$clone_value) ? $clone_value[1] : "";
					if(is_serialized($clone_val)){
						$clone_val = unserialize($clone_val);
					}
					$get_acf_values = $wpdb->get_results( $wpdb->prepare("SELECT  post_name FROM {$wpdb->prefix}posts WHERE post_excerpt='$cloen_val_key' and post_status != 'trash' AND post_type = %s", 'acf-field'));
					if(!empty($get_acf_values)){
					$get_acf_value = $get_acf_values[0]->post_name;
					}
					else {
						$get_acf_value = "";
					}
					update_post_meta($post_id, $cloen_val_key, $clone_val);
					update_post_meta($post_id, '_' . $cloen_val_key, $get_acf_value);
				}
				}
				update_post_meta($post_id, $acf_wp_name , '');
				update_post_meta($post_id, '_' . $acf_wp_name, $key);
			}
			elseif ($field_type == 'gallery') {
				$gallery_ids =array();
				$exploded_gallery_items = explode(',', $acf_csv_name);
				foreach($exploded_gallery_items as $gallery) {
					$gallery = trim($gallery);
					if(preg_match_all('/\b(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i', $gallery,$matched_gallerylist,PREG_PATTERN_ORDER)){
						$ext = pathinfo($gallery, PATHINFO_EXTENSION);
						if($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png' || $ext == 'gif') {
							$img_id = $wpdb->get_col($wpdb->prepare("select ID from {$wpdb->prefix}posts where guid = %s AND post_type='attachment'",$gallery));
							if(!empty($img_id)) {
								$gallery_ids[] = $img_id[0];								
							}else{								
								$get_gallery_id = ACFProImport::$media_instance->image_meta_table_entry('', $post_id, $acf_wpname_element, $gallery, $hash_key, 'acf_gallery', $get_import_type,$templatekey,$gmode);	
								if($get_gallery_id != '') {
									$gallery_ids[] = $get_gallery_id;									
								}
							}
						}else {							
							$get_gallery_id = ACFProImport::$media_instance->image_meta_table_entry('', $post_id, $acf_wpname_element, $gallery, $hash_key, 'acf_gallery', $get_import_type,$templatekey,$gmode);	
							if($get_gallery_id != '') {
								$gallery_ids[] = $get_gallery_id;				
							}
						}
					} else {
						$galleryLen = strlen($gallery);
						$checkgalleryid = intval($gallery);
						$verifiedGalleryLen = strlen($checkgalleryid);
						$gallery_val=explode('.',$gallery);
						$img_id = $wpdb->get_col($wpdb->prepare("select ID from {$wpdb->prefix}posts where post_title = %s AND post_type='attachment'",$gallery_val[0]));						
						if(!empty($img_id)) {
							$gallery_ids[] = $img_id[0];							
							
						}
					}
				}
				$map_acf_csv_element = $gallery_ids;
			}
			$map_acf_wp_element = $acf_wp_name;

		}

		if ($importAs == 'Users') {
			update_user_meta($post_id, $map_acf_wp_element, $map_acf_csv_element);
			update_user_meta($post_id, '_' . $map_acf_wp_element, $key);
		} else {
			update_post_meta($post_id, $map_acf_wp_element, $map_acf_csv_element);
			update_post_meta($post_id, '_' . $map_acf_wp_element, $key);
		}

		$listTaxonomy = get_taxonomies();
		if (in_array($importAs, $listTaxonomy)) {
			if($term_meta = 'yes'){
				if(is_array($map_acf_csv_element)){
					$map=$map_acf_csv_element[0];
				}
				else{
					$map = $map_acf_csv_element;
				}
				update_term_meta($post_id, $map_acf_wp_element, $map);
				update_term_meta($post_id, '_' . $map_acf_wp_element, $key);
			}else{
				$option_name = $importAs . "_" . $post_id . "_" . $map_acf_wp_element;
				$option_value = $map_acf_csv_element;
				if (is_array($option_value)) {
					$option_value = serialize($option_value);
				}

				update_option("$option_name", "$option_value");
			}
		}
	}

	function acfpro_group_import_fuction($data_array, $importAs, $pID,$maps,$mode,$line_number,$hash_key,$gmode,$templatekey) { 
		global $wpdb;
		$helpers_instance = ImportHelpers::getInstance();
		$filekey ="";
		$plugin = 'acf';
		$group_image_import_method = null;
		$createdFields = $grp_parent_fields = $group_fields = $group_flexible_content_import_method = array();

		//get import type
		$listTaxonomy = get_taxonomies();
		if (in_array($importAs, $listTaxonomy)) {
			$get_import_type = 'term';
		}elseif ($importAs == 'Users') {
			$get_import_type = 'user';
		}elseif ($importAs == 'Comments') {
			$get_import_type = 'comment';
		} else {	
			$get_import_type = 'post';
		}

		foreach($data_array as $data_keys => $data_values){
			if (strpos($data_keys, 'field_') !== false){
			}
			else{
				unset($data_array[$data_keys]);
			}
		}
		foreach($data_array as $grpKey => $grpVal) {
			$i = 0;
			// Prepare the meta array by field type			
			$get_field_info  = $wpdb->get_results( $wpdb->prepare( "select ID, post_content, post_name, post_parent from {$wpdb->prefix}posts where post_name = %s", $grpKey ) );
			$field_info = unserialize( $get_field_info[0]->post_content );
			$group_fields[$grpKey] = $get_field_info[0]->post_name;

			if(isset($field_info['type']) && $field_info['type'] == 'flexible_content') {
				$group_flexible_content_import_method[ $get_field_info[0]->post_name ] = $field_info['layouts'][0]['name'];
			} elseif(isset($field_info['type']) && ($field_info['type'] == 'image' || $field_info['type'] == 'file')) {
				if($field_info['type'] == 'image') {
					$group_image_import_method[ $get_field_info[0]->post_name ] = $field_info['return_format'];
				} else {
					$group_file_import_method[ $get_field_info[0]->post_name ] = $field_info['return_format'];
				}
			} else {
				$group_sub_field_type[ $get_field_info[0]->post_name ] = $field_info['type'];
			}

			$group_field_rows = explode('|', $grpVal);

			$j = 0;
			foreach($group_field_rows as $index => $value) {
				$group_field_values = explode('->', $value);
				$checkCount = count($group_field_values);
				foreach($group_field_values as $key => $val) {
					if($checkCount > 1){
						$grp_field_meta_key = $this->getMetaKeyOfGroupField( $pID, $grpKey, $index, $key );
					}
					else{
						$grp_field_meta_key = $this->getMetaKeyOfGroupField( $pID, $grpKey, $i, $j );
					}

					if($grp_field_meta_key[0] == '_')
						$grp_field_meta_key = substr($grp_field_meta_key, 1);
					$grp_field_parent_key = explode( '_' . $grpKey, $grp_field_meta_key );
					$grp_field_parent_key = substr( $grp_field_parent_key[0], 0, - 2 );
					if (substr($grp_field_parent_key, -1) == "_") {
						$grp_field_parent_key = substr($grp_field_parent_key, 0, -1);
					}
					$super_parent = explode('_'.$index.'_',$grp_field_parent_key);
					$grp_parent_fields[$super_parent[0]] = count($group_field_rows);
					if($checkCount > 1)
						$grp_parent_fields[$grp_field_parent_key] = $key + 1;
					else
						$grp_parent_fields[$grp_field_parent_key] = $i + 1;
					$j++;

					$group_sub_field_type[$group_fields[$grpKey]] = isset($group_sub_field_type[$group_fields[$grpKey]]) ? $group_sub_field_type[$group_fields[$grpKey]] :'';
					$group_type = $group_sub_field_type[$group_fields[$grpKey]];
					if($group_type == 'user' || $group_type == 'page_link' || $group_type == 'post_object' || $group_type == 'select') {
						if($field_info['multiple'] == 0){
							$acf_group_field_value = trim($val);
							if(is_string($acf_group_field_value)){
								$acf_group_field_value = $wpdb->_real_escape($acf_group_field_value);
							    $query = "SELECT id FROM {$wpdb->prefix}posts WHERE post_title ='{$acf_group_field_value}' AND post_status='publish'";
								$name = $wpdb->get_results($query);
								if (!empty($name)) {
									$acf_group_field_value=$name[0]->id;
								}
							}
							elseif (is_numeric($acf_group_field_value)) {
								$acf_group_field_value=$acf_group_field_value;
							}
						}elseif(!$field_info['multiple'] == 0){
							$acf_group_value_exp = explode(',',trim($val));
							$acf_group_field_value = array();
							foreach($acf_group_value_exp as $acf_grp_value){
								$acf_grp_value = $wpdb->_real_escape($acf_grp_value);
								$query = "SELECT id FROM {$wpdb->prefix}posts WHERE post_title ='{$acf_grp_value}' AND post_status!='trash'";
								$multiple_id = $wpdb->get_results($query);
								foreach($multiple_id as $mul_id){
									$acf_group_field_value[]=trim($mul_id->id);
								}
							}
						}
						$acf_grp_field_info[$grp_field_meta_key] = $acf_group_field_value;	
						$acf_grp_field_info['_'.$grp_field_meta_key]=$group_fields[$grpKey];
					}

					if($group_type == 'text' || $group_type == 'textarea' || $group_type == 'email' || $group_type == 'number' || $group_type == 'url' || $group_type == 'password' || $group_type == 'range' || $group_type == 'radio' || $group_type == 'true_false' || $group_type == 'time_picker' || $group_type == 'color_picker' || $group_type == 'button_group' || $group_type == 'oembed' || $group_type == 'wysiwyg'){
						$acf_grp_field_info[$grp_field_meta_key] = trim($val);
						$acf_grp_field_info['_'.$grp_field_meta_key]=$group_fields[$grpKey];	
					}
					if($group_type == 'date_time_picker'){
						$dt_group_var = trim($val);
						$dateformat = "Y-m-d H:i:s";
						$fieldnm = substr($grp_field_meta_key,strrpos($grp_field_meta_key,'_')+1);						
						if($dt_group_var== 0 || $dt_group_var== ''){
							$acf_grp_field_info[$grp_field_meta_key] = $dt_group_var;
							$acf_grp_field_info['_'.$grp_field_meta_key]=$group_fields[$grpKey];
						}	
						else{
							$date_time_group_of = $helpers_instance->validate_datefield($dt_group_var,$fieldnm,$dateformat,$line_number);
							$acf_grp_field_info[$grp_field_meta_key] = $date_time_group_of;
							$acf_grp_field_info['_'.$grp_field_meta_key]=$group_fields[$grpKey];
						}										
					}
					if ($group_type == 'google_map') {
						$location[] = trim($val);
					    foreach($location as $loc){
						$locc=implode('|', $location);
					}
			
					list($add, $lat,$lng) = explode('|', $locc);
					$area = rtrim($add, ",");
						$map = array(
							'address' => $location,
							'lat'     => $lat,
							'lng'     => $lng
						);
						$acf_grp_field_info[$grp_field_meta_key] = $map;
						$acf_grp_field_info['_'.$grp_field_meta_key]=$group_fields[$grpKey];
					}
					if($group_type == 'date_picker'){
						$var_group = trim($val);
						$dateformat = "Ymd";
						$fieldnm = substr($grp_field_meta_key,strrpos($grp_field_meta_key,'_')+1);
						$date_group = str_replace('/', '-', "$var_group");						
						if($mode == 'Insert'){
							if($var_group == 0 || $var_group == ''){
								$acf_grp_field_info[$grp_field_meta_key]  = $var_group;
								$acf_grp_field_info['_'.$grp_field_meta_key]=$group_fields[$grpKey];
							}
							else{
								$date_group_of = $helpers_instance->validate_datefield($var_group,$fieldnm,$dateformat,$line_number);
								$acf_grp_field_info[$grp_field_meta_key]  = $date_group_of;
								$acf_grp_field_info['_'.$grp_field_meta_key]=$group_fields[$grpKey];

							}	
						}
						else{
							if($var_group == 0 || $var_group == ''){
								$acf_grp_field_info[$grp_field_meta_key]  = $var_group;
							$acf_grp_field_info['_'.$grp_field_meta_key]=$group_fields[$grpKey];
							}	
							else{
								$date_group_of = $helpers_instance->validate_datefield($var_group,$fieldnm,$dateformat,$line_number);
								$acf_grp_field_info[$grp_field_meta_key]  = $date_group_of;
								$acf_grp_field_info['_'.$grp_field_meta_key]=$group_fields[$grpKey];
							}
						}						
					}
					if($group_type == 'link'){
						$serial_acf_csv_group = explode(',',$val);
						$serial_acf_csv_group_name = [];
						foreach($serial_acf_csv_group as $serial_acf_csv_group_value){
							$serial_acf_csv_group_name[] = trim($serial_acf_csv_group_value);
						}	
						$serial_acf_csv_group_names['title'] = $serial_acf_csv_group_name[0];
						$serial_acf_csv_group_names['url'] = $serial_acf_csv_group_name[1];
						if($serial_acf_csv_group_name[2] == 1){
							$serial_acf_csv_group_names['target'] = '_blank';
						}else{
							$serial_acf_csv_group_names['target'] = '';
						}
						$acf_grp_field_info[$grp_field_meta_key] = $serial_acf_csv_group_names;
						$acf_grp_field_info['_'.$grp_field_meta_key]=$group_fields[$grpKey];
					}

					if($group_type == 'checkbox'){
						$explo_acf_val = explode(',',trim($val));	
						$explo_acf_val_name = [];
						foreach($explo_acf_val as $explode_acf_csv_value){
							$explo_acf_val_name[] = trim($explode_acf_csv_value);
						}

						$acf_grp_field_info[$grp_field_meta_key] = $explo_acf_val_name;
						$acf_grp_field_info['_'.$grp_field_meta_key]=$group_fields[$grpKey];

					}
					if($group_type == 'gallery'){
						$gallery_ids = array();
						if ( is_array( $gallery_ids ) ) {
							unset( $gallery_ids );
							$gallery_ids = array();
						}
						$exploded_gallery_items = explode( ',', $val );
						foreach ( $exploded_gallery_items as $gallery ) {
							$gallery = trim( $gallery );
							if ( preg_match_all( '/\b(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i', $gallery ) ) {								
								$get_gallery_id = ACFProImport::$media_instance->image_meta_table_entry('', $pID, $grp_field_meta_key, $gallery, $hash_key, 'acf_group_gallery', $get_import_type,$templatekey,$gmode);
								if ( $get_gallery_id != '' ) {
									$gallery_ids[] = $get_gallery_id;								
								}
							} else {
								$galleryLen         = strlen( $gallery );
								$checkgalleryid     = intval( $gallery );
								$verifiedGalleryLen = strlen( $checkgalleryid );
								if ( $galleryLen == $verifiedGalleryLen ) {
									$gallery_ids[] = $gallery;
									//ACFProImport::$media_instance->acfgalleryMetaImports($gallery_ids,$maps,$plugin);
								}
							}
						}
						$acf_grp_field_info[$grp_field_meta_key] = $gallery_ids;
						$acf_grp_field_info['_'.$grp_field_meta_key] = $group_fields[$grpKey];
					} 

					$group_image_import_method[$group_fields[$grpKey]] = isset($group_image_import_method[$group_fields[$grpKey]]) ? $group_image_import_method[$group_fields[$grpKey]] :'';
					$group_file_import_method[$group_fields[$grpKey]]  = isset($group_file_import_method[$group_fields[$grpKey]]) ? $group_file_import_method[$group_fields[$grpKey]] :'';

					if($group_image_import_method[$group_fields[$grpKey]] == 'url' || $group_image_import_method[$group_fields[$grpKey]] == 'array' ) {
						$image_link = trim($val);
						if(preg_match_all('/\b(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i', $image_link)){
							//$acf_grp_field_info[$grp_field_meta_key] = ACFProImport::$media_instance->media_handling($image_link, $pID);
							$acf_grp_field_info[$grp_field_meta_key] = ACFProImport::$media_instance->image_meta_table_entry('', $pID, $grp_field_meta_key, $image_link, $hash_key, 'acf_group', $get_import_type,$templatekey,$gmode);
							$img_id[]=$acf_grp_field_info[$grp_field_meta_key];
							//ACFProImport::$media_instance->acfimageMetaImports($img_id,$maps,$plugin);
							$acf_grp_field_info['_'.$grp_field_meta_key] = $group_fields[$grpKey];
						} else {
							$acf_grp_field_info[$grp_field_meta_key] = $image_link;
							$img_id[]=$acf_grp_field_info[$grp_field_meta_key];
							//ACFProImport::$media_instance->acfimageMetaImports($img_id,$maps,$plugin);
							$acf_grp_field_info['_'.$grp_field_meta_key] = $group_fields[$grpKey];
						}
					}
					if($group_file_import_method[$group_fields[$grpKey]] == 'url' || $group_file_import_method[$group_fields[$grpKey]] == 'array' ) {
						$image_link = trim($val);
						if(preg_match_all('/\b(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i', $image_link)){
							$ext = pathinfo($image_link, PATHINFO_EXTENSION);
							if($ext== 'pdf' || $ext == 'mp3' || $ext == $ext) {
								$fil_id = $wpdb->get_col($wpdb->prepare("select ID from {$wpdb->prefix}posts where guid = %s AND post_type='attachment'",$image_link));
								if(!empty($fil_id)) {
									$acf_grp_field_info[$grp_field_meta_key]=$fil_id[0];
								}else {									
									$acf_grp_field_info[$grp_field_meta_key] = ACFProImport::$media_instance->image_meta_table_entry('', $pID, $grp_field_meta_key, $image_link, $hash_key, 'acf_group', $get_import_type,$templatekey,$gmode);
								}
								$acf_grp_field_info['_'.$grp_field_meta_key] = $group_fields[$grpKey];
							} else {								
								$acf_grp_field_info[$grp_field_meta_key] = ACFProImport::$media_instance->image_meta_table_entry('', $pID, $grp_field_meta_key, $image_link, $hash_key, 'acf_group', $get_import_type,$templatekey,$gmode);
								$acf_grp_field_info['_'.$grp_field_meta_key] = $group_fields[$grpKey];
							}
						} else {
							$acf_grp_field_info[$grp_field_meta_key] = $image_link;
							$acf_grp_field_info['_'.$grp_field_meta_key] = $group_fields[$grpKey];
						}
					}  
					if ($group_type == 'message') {
						$field_info['message'] = $val;
					}
					elseif($group_type == 'relationship' || $group_type == 'taxonomy') {
						$exploded_relations = $relations = array();
						$exploded_relations = explode(',', $val);
						foreach($exploded_relations as $relVal) {
							$relationTerm = trim( $relVal );
							//$relTerm[] = $relationTerm;
							if ( $group_type == 'taxonomy' ) {
								$taxonomy_name =  $field_info['taxonomy'];
								$check_is_valid_term = $helpers_instance->get_requested_term_details( $pID, array($relationTerm), $taxonomy_name );
								$relations[]         = $check_is_valid_term;
							} else {
								if(is_numeric($relVal)){
									$relations[] = $relationTerm;
								}
								else{
									$relationTerm=$wpdb->_real_escape($relationTerm);
									$query = "SELECT id FROM {$wpdb->prefix}posts WHERE post_title ='{$relationTerm}' AND post_status!='trash'";
								    $multiple_id = $wpdb->get_results($query);
								    foreach($multiple_id as $mul_id){
										$relations[]=trim($mul_id->id);
									}
								}
								
							}
						}
						$acf_grp_field_info[$grp_field_meta_key] = $relations;
						$acf_grp_field_info['_'.$grp_field_meta_key] = $group_fields[$grpKey];
					} 
				}
				$i++;
			}

			if(!empty($acf_grp_field_info)) {
				foreach($acf_grp_field_info as $fName => $fVal) {
					$listTaxonomy = get_taxonomies();
					if (in_array($importAs, $listTaxonomy)) {
						if($term_meta = 'yes'){
							update_term_meta($pID, $fName, $fVal);
						}else{
							$option_name = $importAs . "_" . $pID . "_" . $fName;
							$option_value = $fVal;
							if (is_array($option_value)) {
								$option_value = serialize($option_value);
							}
							update_option("$option_name", "$option_value");
						}
					}
					else{
						if($importAs == 'Users'){
							update_user_meta($pID, $fName, $fVal);
						}else{
							update_post_meta($pID, $fName, $fVal);
						}
					}

				}
			}

			$createdFields[] = $grpKey;
			$grp_fname = $grpKey;
			$grp_fID   = $group_fields[$grpKey];
			// Flexible Content
			$flexible_content = array();
			$listTaxonomy = get_taxonomies();
			if ( array_key_exists( $grp_fID, $group_flexible_content_import_method ) && $group_flexible_content_import_method[ $grp_fID ] != null ) {
				$flexible_content[] = $group_flexible_content_import_method[ $grp_fID ];
				if($importAs == 'Users'){
					update_user_meta($pID, $grp_fname, $flexible_content);
				}
				elseif(in_array($importAs , $listTaxonomy)){
					update_term_meta($pID, $grp_fname, $flexible_content);
				}else{
					update_post_meta($pID, $grp_fname, $flexible_content);
				}	
			}
		}
		foreach($grp_parent_fields as $pKey => $pVal) {
			$listTaxonomy = get_taxonomies();
			if (in_array($importAs, $listTaxonomy)) {
				if($term_meta = 'yes'){
					update_term_meta($pID, $pKey, $pVal);
				}else{
					$option_name = $importAs . "_" . $pID . "_" . $pKey;
					$option_value = $pVal;
					if (is_array($option_value)) {
						$option_value = serialize($option_value);
					}
					update_option("$option_name", "$option_value");
				}
			}
			else{
				if($importAs == 'Users'){
					update_user_meta($pID, $pKey, $pVal);
				}else{
					update_post_meta($pID, $pKey, $pVal);
				}
			}		
		}
	}


	function getMetaKeyOfGroupField($pID, $field_name, $meta_key = '') {
		global $wpdb;
		//changed		
		$get_field_details  = $wpdb->get_results( $wpdb->prepare( "select ID, post_content, post_name, post_parent, post_excerpt from {$wpdb->prefix}posts where post_name = %s", $field_name ) );

		$get_group_parent_field = $wpdb->get_results( $wpdb->prepare( "select post_content, post_name, post_excerpt, post_parent from {$wpdb->prefix}posts where ID = %d", $get_field_details[0]->post_parent ));
		$field_info = unserialize( $get_group_parent_field[0]->post_content );

		$get_group_super_parent_field = $wpdb->get_results( $wpdb->prepare( "select post_content, post_name, post_excerpt, post_parent from {$wpdb->prefix}posts where ID = %d", $get_group_parent_field[0]->post_parent ));
				
		if(isset($get_group_super_parent_field[0]->post_content)){
			$parent_field_info = unserialize($get_group_super_parent_field[0]->post_content );
		}
		if(empty($parents) && $field_name == $get_field_details[0]->post_name) {	
		
			$get_field_excerpt = $wpdb->get_var( $wpdb->prepare( "SELECT post_excerpt from {$wpdb->prefix}posts where post_name = %s", $field_name ) );		
			$meta_key =  $get_field_excerpt;
		}

		if($get_group_parent_field[0]->post_parent != 0 && isset($field_info['type']) && $field_info['type'] == 'group'  ) {  			
			$meta_key =  $get_group_parent_field[0]->post_excerpt . '_' . $meta_key;	
			update_post_meta($pID, $get_group_parent_field[0]->post_excerpt , 1);
			update_post_meta($pID, '_'.$get_group_parent_field[0]->post_excerpt , $get_group_parent_field[0]->post_name );
		} 
		if(isset($get_group_super_parent_field[0]->post_parent)){
			if($get_group_super_parent_field[0]->post_parent != 0 && isset($parent_field_info['type']) && $parent_field_info['type'] == 'group'){
				$meta_key =  $get_group_super_parent_field[0]->post_excerpt . '_' . $meta_key;
				update_post_meta($pID, $get_group_super_parent_field[0]->post_excerpt , 1);
				update_post_meta($pID, '_'.$get_group_super_parent_field[0]->post_excerpt , $get_group_super_parent_field[0]->post_name );
			}
	}
		return $meta_key;
	}

	function acfpro_repeater_import_fuction($data_array, $importAs, $pID,$maps,$mode,$line_number,$hash_key,$gmode,$templatekey) {
		global $wpdb;
		$plugin = 'acf';		
		//get import type
		$listTaxonomy = get_taxonomies();
		if (in_array($importAs, $listTaxonomy)) {
			$get_import_type = 'term';
		}elseif ($importAs == 'Users') {
			$get_import_type = 'user';
		}elseif ($importAs == 'Comments') {
			$get_import_type = 'comment';
		} else {	
			$get_import_type = 'post';
		}

		foreach($data_array as $data_keys => $data_values){
			if (strpos($data_keys, 'field_') !== false){
			}
			else{
				unset($data_array[$data_keys]);
			}
		}
		$helpers_instance = ImportHelpers::getInstance();
        $plugin ='acf';
		$createdFields = $rep_parent_fields = $repeater_fields = $repeater_sub_fields = $repeater_flexible_content_import_method = array();
		$flexible_array = [];
		$parent_key_values = [];
		$child_key_values = [];
		$acf_rep_field_info = [];		
		foreach($data_array as $repKey => $repVal) {
			$i = 0;
			
			// Prepare the meta array by field type			
			$get_field_info  = $wpdb->get_results( $wpdb->prepare( "select ID, post_content, post_name, post_parent from {$wpdb->prefix}posts where post_name = %s", $repKey ) );			
			$field_info = unserialize( $get_field_info[0]->post_content );

			$parent=$get_field_info[0]->post_parent;
		
			$get_fieldparent  = $wpdb->get_results( $wpdb->prepare( "select ID, post_content, post_name, post_parent from {$wpdb->prefix}posts where ID = %d", $parent ) );
			$parent_content=unserialize( $get_fieldparent[0]->post_content );
			if($parent_content['type'] == 'group'){
				if($parent != 0){	
				   $get_field_info  = $wpdb->get_results( $wpdb->prepare( "select ID, post_content, post_name, post_parent from {$wpdb->prefix}posts where ID = %d", $parent ) );
				   $field_info = unserialize( $get_field_info[0]->post_content );
				   $acf_rep_field_info =$this ->acf_pro_grp_repeater_import($pID,$repKey,$repVal,$maps,$hash_key,$i,$line_number, $get_import_type,$gmode,$templatekey);
			    }
			}
			
			$repeater_fields[$repKey] = $get_field_info[0]->post_name;
			if(isset($field_info['type']) && $field_info['type'] == 'flexible_content') {
				$repeater_flexible_content_import_method[ $get_field_info[0]->post_name ] = $field_info['layouts'][0]['name'];
				$flexible_array[$repKey] = $get_field_info[0]->ID;
			} elseif(isset($field_info['type']) && ($field_info['type'] == 'image' || $field_info['type'] == 'file')) {
				if($field_info['type'] == 'image') {
					$repeater_image_import_method[ $get_field_info[0]->post_name ] = $field_info['return_format'];
				} else {
					$repeater_file_import_method[ $get_field_info[0]->post_name ] = $field_info['return_format'];
				}
			} else {
				$repeater_sub_field_type[ $get_field_info[0]->post_name ] = $field_info['type'];
			}

			// Parse values if have any multiple values
			$repeater_field_rows = explode('->', $repVal);
		
			
			foreach($repeater_field_rows as $index => $value) {
				$j = 0;
				if(!empty($value)){
					$repeater_field_values = explode('|', $value);
					$checkCount = count($repeater_field_values);
					foreach($repeater_field_values as $key => $val) {
						if(!empty($repeater_field_values)){
							if($checkCount > 1){
								$rep_field_meta_key = $this->getMetaKeyOfRepeaterField( $pID, $repKey, $index, $key );
							}else{
								$rep_field_meta_key = $this->getMetaKeyOfRepeaterField( $pID, $repKey, $i, $j );
								
							}
							if($rep_field_meta_key[0] == '_')
								$rep_field_meta_key = substr($rep_field_meta_key, 1);
							//$rep_field_parent_key = explode( '_' . $repKey, $rep_field_meta_key );
							$get_field_details  = $wpdb->get_results( $wpdb->prepare( "select  post_excerpt from {$wpdb->prefix}posts where post_name = %s", $repKey ) );
							$rep_field_parent_key = explode( '_' . $get_field_details[0]->post_excerpt, $rep_field_meta_key );
							$rep_field_parent_key = substr( $rep_field_parent_key[0], 0, - 2 );
							if (substr($rep_field_parent_key, -1) == "_") {
								$rep_field_parent_key = substr($rep_field_parent_key, 0, -1);
							}
							$super_parent = explode('_'.$index.'_',$rep_field_parent_key);
							$parent_key_values[] = count($repeater_field_rows);
							$rep_parent_fields[$super_parent[0]] = max($parent_key_values);

							if($checkCount > 1){
								$child_key_values[] = $key + 1;
								//$rep_parent_fields[$rep_field_parent_key] = max($child_key_values); 
								//$rep_parent_fields[$rep_field_parent_key] = $checkCount; 
								
								if(isset($parent_keys)){
									if($parent_keys == $rep_field_parent_key){
										
										$rep_parent_fields[$rep_field_parent_key] =$parent_count;
										
									}
								}
								else{
									$rep_parent_fields[$rep_field_parent_key] = $checkCount;
									$parent_keys=$rep_field_parent_key;
									$parent_count=$checkCount;
									
								}
								
							}else{
								$child_key_values[] = $i + 1;
								//$child_key_values1[] = $j + 1;
								//$rep_parent_fields[$rep_field_parent_key] = max($child_key_values);
								if(isset($parent_keys)){
									if($parent_keys == $rep_field_parent_key){
										
										$rep_parent_fields[$rep_field_parent_key] = $parent_count;
									}
								}
								else{
									$rep_parent_fields[$rep_field_parent_key] = $checkCount;
								}
							
								//$rep_parent_fields[$rep_field_parent_key] = max($child_key_values1);
							}
						}
						$j++;
						
						$repeater_sub_field_type[$repeater_fields[$repKey]] = isset($repeater_sub_field_type[$repeater_fields[$repKey]]) ? $repeater_sub_field_type[$repeater_fields[$repKey]] :'';
						$rep_type = $repeater_sub_field_type[$repeater_fields[$repKey]];
						if($rep_type == 'user' || $rep_type == 'page_link' || $rep_type == 'post_object') {
							if($field_info['multiple'] == 0){
								$acf_rep_field_value = trim($val);
								if(is_string($acf_rep_field_value)){
									$acf_rep_field_value = $wpdb->_real_escape($acf_rep_field_value);
									$query = "SELECT id FROM {$wpdb->prefix}posts WHERE post_title ='{$acf_rep_field_value}' AND post_status='publish'";
									$name = $wpdb->get_results($query);
									if (!empty($name)) {
										$acf_rep_field_value=$name[0]->id;
									}
								}
								elseif (is_numeric($acf_rep_field_value)) {
									$acf_rep_field_value=$acf_rep_field_value;
								}
							}elseif(!$field_info['multiple'] == 0){								
								$acf_rep_value_exp = explode(',',trim($val));
								$acf_rep_field_value = array();
								foreach($acf_rep_value_exp as $acf_reps_value){
									$acf_rep_value = $wpdb->_real_escape($acf_reps_value);									
									$query = "SELECT id FROM {$wpdb->prefix}posts WHERE post_title ='{$acf_reps_value}' AND post_status!='trash'";
									$multiple_id = $wpdb->get_results($query);
									foreach($multiple_id as $mul_id){
										$acf_rep_field_value[]=trim($mul_id->id);
									}
								}
							}
							$acf_rep_field_info[$rep_field_meta_key] = $acf_rep_field_value;	
							$acf_rep_field_info['_'.$rep_field_meta_key]=$repeater_fields[$repKey];
						}
						if($rep_type == 'select'){
							if($field_info['multiple'] == 0){
								$acf_rep_field_value = trim($val);
							}
							else {
								$acf_rep_field_value = explode(',',trim($val));								
							}
							$acf_rep_field_info[$rep_field_meta_key] = $acf_rep_field_value;	
							$acf_rep_field_info['_'.$rep_field_meta_key]=$repeater_fields[$repKey];
						}
						if($rep_type == 'text' || $rep_type == 'textarea' || $rep_type == 'email' || $rep_type == 'number' || $rep_type == 'url' || $rep_type == 'password' || $rep_type == 'range' || $rep_type == 'radio' || $rep_type == 'true_false' || $rep_type == 'time_picker' || $rep_type == 'color_picker' || $rep_type == 'button_group' || $rep_type == 'oembed' || $rep_type == 'wysiwyg'){

							$acf_rep_field_info[$rep_field_meta_key] = trim($val);
							$acf_rep_field_info['_'.$rep_field_meta_key]=$repeater_fields[$repKey];	
						}
						if($rep_type == 'date_time_picker'){
							$dt_rep_var = trim($val);
								$dateformat = "Y-m-d H:i:s";
								$fieldnm = substr($rep_field_meta_key,strrpos($rep_field_meta_key,'_')+1);							
								if($dt_rep_var== 0 || $dt_rep_var== ''){
									$acf_rep_field_info[$rep_field_meta_key] =$dt_rep_var ;
									$acf_rep_field_info['_'.$rep_field_meta_key]=$repeater_fields[$repKey];
								}	
								else{
									$date_time_rep_of = $helpers_instance->validate_datefield($dt_rep_var,$fieldnm,$dateformat,$line_number);
									$acf_rep_field_info[$rep_field_meta_key] = $date_time_rep_of;
									$acf_rep_field_info['_'.$rep_field_meta_key]=$repeater_fields[$repKey];
								}						
						}
						if ($rep_type == 'google_map') {

							$location[] = trim($val);
							foreach($location as $loc){
								$locc=implode('|', $location);
							}
							list($add, $lat,$lng) = explode('|', $locc);
							$area = rtrim($add, ",");
							$map = array(
								'address' => $area,
								'lat'     =>  $lat,
								'lng'     => $lng
							);
							$acf_rep_field_info[$rep_field_meta_key] = $map;
							$acf_rep_field_info['_'.$rep_field_meta_key] = $repeater_fields[$repKey];
						}
						if($rep_type == 'date_picker'){
							$dateformat = "Ymd";
								$var_rep = trim($val);
								$fieldnm = substr($rep_field_meta_key,strrpos($rep_field_meta_key,'_')+1);
								$date_rep = str_replace('/', '-', "$var_rep");							
								if($mode == 'Insert'){
									if($var_rep == 0 || $var_rep == ''){
										$acf_rep_field_info[$rep_field_meta_key] = $var_rep;
										$acf_rep_field_info['_'.$rep_field_meta_key]=$repeater_fields[$repKey];
									}
									else{
										$date_rep_of = $helpers_instance->validate_datefield($var_rep,$fieldnm,$dateformat,$line_number);
										$acf_rep_field_info[$rep_field_meta_key] = $date_rep_of;
										$acf_rep_field_info['_'.$rep_field_meta_key]=$repeater_fields[$repKey];

									}	
								}
								else{
									if($var_rep == 0 || $var_rep == ''){
									$acf_rep_field_info[$rep_field_meta_key] =$var_rep ;
									$acf_rep_field_info['_'.$rep_field_meta_key]=$repeater_fields[$repKey];
									}	
									else{
										$date_rep_of = $helpers_instance->validate_datefield($var_rep,$fieldnm,$dateformat,$line_number);
										$acf_rep_field_info[$rep_field_meta_key] = $date_rep_of;
										$acf_rep_field_info['_'.$rep_field_meta_key]=$repeater_fields[$repKey];
									}
								}						
						}
						if($rep_type == 'checkbox'){

							$explode_val = explode(',',trim($val));	
							$explode_val_name = [];
							foreach($explode_val as $explode_acf_csv_value){
								$explode_val_name[] = trim($explode_acf_csv_value);
							}

							$acf_rep_field_info[$rep_field_meta_key] = $explode_val_name;
							$acf_rep_field_info['_'.$rep_field_meta_key]=$repeater_fields[$repKey];
						}
						if($rep_type == 'gallery'){
							$gallery_ids = array();
							if ( is_array( $gallery_ids ) ) {
								unset( $gallery_ids );
								$gallery_ids = array();
							}
							$exploded_gallery_items = explode( ',', $val );
							foreach ( $exploded_gallery_items as $gallery ) {
								$gallery = trim( $gallery );
								if ( preg_match_all( '/\b(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i', $gallery ) ) {
									//$get_gallery_id = ACFProImport::$media_instance->media_handling( $gallery, $pID);	
									$get_gallery_id = ACFProImport::$media_instance->image_meta_table_entry('', $pID, $rep_field_meta_key, $gallery, $hash_key, 'acf_repeater_gallery', $get_import_type,$templatekey,$gmode);
									if ( $get_gallery_id != '' ) {
										$gallery_ids[] = $get_gallery_id;
										//ACFProImport::$media_instance->acfgalleryMetaImports($gallery_ids,$maps,$plugin);
									}
								} else {
									$galleryLen         = strlen( $gallery );
									$checkgalleryid     = intval( $gallery );
									$verifiedGalleryLen = strlen( $checkgalleryid );
									if ( $galleryLen == $verifiedGalleryLen ) {
										$gallery_ids[] = $gallery;
										//ACFProImport::$media_instance->acfgalleryMetaImports($gallery_ids,$maps,$plugin);
									}
								}
							}
							$acf_rep_field_info[$rep_field_meta_key] = $gallery_ids;
							$acf_rep_field_info['_'.$rep_field_meta_key] = $repeater_fields[$repKey];
						}
						if($rep_type == 'link'){
							$explode_acf_val = explode(',',$val);
							$serial_acf_val = [];
							foreach($explode_acf_val as $explode_acf_value){
								$serial_acf_val[] = trim($explode_acf_value);
							}	

							$serial_acf_value['title'] = $serial_acf_val[0];
							$serial_acf_value['url'] = $serial_acf_val[1];
							if($serial_acf_val[2] == 1){
								$serial_acf_value['target'] = '_blank';
							}else{
								$serial_acf_value['target'] = '';
							}

							$acf_rep_field_info[$rep_field_meta_key] = $serial_acf_value;
							$acf_rep_field_info['_'.$rep_field_meta_key]=$repeater_fields[$repKey];
						}

						//Push meta information into WordPress

						elseif($rep_type == 'relationship' || $rep_type == 'taxonomy') {
							$exploded_relations = $relations = array();
							$exploded_relations = explode(',', $val);
							foreach($exploded_relations as $relVal) {
								$relationTerm = trim( $relVal );
								if ( $rep_type == 'taxonomy' ) {
									$taxonomy_name =  $field_info['taxonomy'];
									$check_is_valid_term = $helpers_instance->get_requested_term_details( $pID, array($relationTerm), $taxonomy_name );
									$relations[]         = $check_is_valid_term;
								} else {
									if(is_numeric($relVal)){
										$relations[] = $relationTerm;
									}
									else{
										$relationTerm=$wpdb->_real_escape($relationTerm);
										$query = "SELECT id FROM {$wpdb->prefix}posts WHERE post_title ='{$relationTerm}' AND post_status!='trash'";
										$multiple_id = $wpdb->get_results($query);
										foreach($multiple_id as $mul_id){
											$relations[]=trim($mul_id->id);
										}
									}
									
								}
							}
							$acf_rep_field_info[$rep_field_meta_key] = $relations;
							$acf_rep_field_info['_'.$rep_field_meta_key] = $repeater_fields[$repKey];
						} 


						elseif($rep_type == 'group'){

							$get_subfield_info  = $wpdb->get_results( $wpdb->prepare( "select ID, post_content, post_name, post_parent from {$wpdb->prefix}posts where post_excerpt = %s", $repKey ) );
							$subfield_info = unserialize( $get_subfield_info[0]->post_content );
							$rep_sub_fields[$repKey] = $get_subfield_info[0]->post_name;
							
							if(isset($subfield_info['type']) && $subfield_info['type'] == 'flexible_content') {
								$repeater_sub_flexible_content_import_method[ $get_subfield_info[0]->post_name ] = $subfield_info['layouts'][0]['name'];
								$flexible_sub_array[$repKey] = $get_subfield_info[0]->ID;
							} elseif(isset($subfield_info['type']) && ($subfield_info['type'] == 'image' || $subfield_info['type'] == 'file')) {
								if($subfield_info['type'] == 'image') {
									$repeater_sub_image_import_method[ $get_subfield_info[0]->post_name ] = $subfield_info['return_format'];
									$imgkey = $get_subfield_info[0]->post_name ;
								} else {
									$repeater_sub_file_import_method[ $get_subfield_info[0]->post_name ] = $subfield_info['return_format'];
								}
							} else {
								$repeater_sub_sub_field_type[ $get_subfield_info[0]->post_name ] = $subfield_info['type'];
							}
							
							// Parse values if have any multiple values
							$repeater_sub_field_rows = explode('|', $repVal);
							$count=count($repeater_sub_field_rows);
							$j = 0;
							foreach($repeater_sub_field_rows as $index => $value) {
								$rep_sub_field_values = explode('->', $value);
								$checkCount = count($rep_sub_field_values);
								foreach($rep_sub_field_values as $key => $val) {
									if($checkCount > 1){
										$rep_subfield_meta_key = $this->getMetaKeyOfRepeaterGroupField( $pID, $repKey, $index, $key);	
										//$rep_subfield_meta_key = $this->getMetaKeyOfRepeaterGroupField( $pID, $grpKey, $index, $key ,$count,$importAs);
									}
									else{
										$rep_subfield_meta_key = $this->getMetaKeyOfRepeaterGroupField( $pID, $repKey, $index, $key );
										//$rep_subfield_meta_key = $this->getMetaKeyOfRepeaterGroupField( $pID, $grpKey, $i, $j,$count,$importAs );
									}
									if($rep_subfield_meta_key[0] == '_' && $rep_subfield_meta_key[1] != '_')
										$rep_subfield_meta_key = substr($rep_subfield_meta_key, 1);
									$reps_subfield_parent_key = explode( '_' . $repKey, $rep_subfield_meta_key );
									
									$rep_subfield_parent_key =  $reps_subfield_parent_key[0];
									
									if (substr($rep_subfield_parent_key, -1) == "_") {
										$rep_subfield_parent_key = substr($rep_subfield_parent_key, 0, -1);
									}
									
									$super_sub_parent = explode('_'.$index.'_',$rep_subfield_parent_key);
								
									$rep_sub_parent_fields[$super_sub_parent[0]] = count($rep_sub_field_values);
									if($checkCount > 1)
										$rep_sub_parent_fields[$rep_field_parent_key] = $key + 1;
									else
										$rep_parent_fields[$rep_field_parent_key] = $i + 1;
									$j++;
									$reptype = $repeater_sub_sub_field_type[$rep_sub_fields[$repKey]];
								

									if($reptype == 'user' || $reptype == 'page_link' || $reptype == 'post_object' || $reptype == 'select') {
										if($subfield_info['multiple'] == 0){
											$acf_rep_field_value = trim($val);
											if(is_string($acf_rep_field_value)){
												$acf_rep_field_value = $wpdb->_real_escape($acf_rep_field_value);
												$query = "SELECT id FROM {$wpdb->prefix}posts WHERE post_title ='{$acf_rep_field_value}' AND post_status='publish'";
												$name = $wpdb->get_results($query);
												if (!empty($name)) {
													$acf_rep_field_value=$name[0]->id;
												}
											}
											elseif (is_numeric($acf_rep_field_value)) {
												$acf_rep_field_value=$acf_rep_field_value;
											}
										}else{
											$acf_rep_value_exp = explode(',',trim($val));
											$acf_rep_field_value = array();
											foreach($acf_rep_value_exp as $acf_reps_value){
												$acf_reps_value = $wpdb->_real_escape($acf_reps_value);
												$query = "SELECT id FROM {$wpdb->prefix}posts WHERE post_title ='{$acf_reps_value}' AND post_status!='trash'";
												$multiple_id = $wpdb->get_results($query);
												if(!$multiple_id){
													$acf_rep_field_value=$acf_rep_value_exp;
												}
												else{
													foreach($multiple_id as $mul_id){
														$acf_rep_field_value[]=trim($mul_id->id);
													}
												}
												
											}
										}
										$acf_rep_field_info[$rep_subfield_meta_key] = $acf_rep_field_value;	
										$acf_rep_field_info['_'.$rep_subfield_meta_key]=$rep_sub_fields[$repKey];
									}
									if($reptype == 'text' || $reptype == 'textarea' || $reptype == 'email' || $reptype == 'number' || $reptype == 'url' || $reptype == 'password' || $reptype == 'range' || $reptype == 'radio' || $reptype == 'true_false' || $reptype == 'time_picker' || $reptype == 'color_picker' || $reptype == 'button_group' || $reptype == 'oembed' || $reptype == 'wysiwyg'){
				
										$acf_rep_field_info[$rep_subfield_meta_key] = trim($val);
										$acf_rep_field_info['_'.$rep_subfield_meta_key]=$rep_sub_fields[$repKey];	
									}
									if($reptype == 'date_time_picker'){
										$dt_rep_var = trim($val);
										$dateformat = "Y-m-d H:i:s";
										$fieldnm = substr($rep_subfield_meta_key,strrpos($rep_subfield_meta_key,'_')+1);							
										if($dt_rep_var== 0 || $dt_rep_var== ''){	
											$acf_rep_field_info[$rep_subfield_meta_key] =$dt_rep_var ;
											$acf_rep_field_info['_'.$rep_subfield_meta_key]=$repeater_sub_fields[$repKey];
										}	
										else{
											$date_time_rep_of = $helpers_instance->validate_datefield($dt_rep_var,$fieldnm,$dateformat,$line_number);
											$acf_rep_field_info[$rep_subfield_meta_key] = $date_time_rep_of;
											$acf_rep_field_info['_'.$rep_subfield_meta_key]=$rep_sub_fields[$repKey];
										}									
									}
									if ($reptype == 'google_map') {
				
										$location[] = trim($val);
										foreach($location as $loc){
											$locc=implode('|', $location);
										}
										list($add, $lat,$lng) = explode('|', $locc);
										$area = rtrim($add, ",");
										$map = array(
											'address' => $area,
											'lat'     =>  $lat,
											'lng'     => $lng
										);
										$acf_rep_field_info[$rep_subfield_meta_key] = $map;
										$acf_rep_field_info['_'.$rep_subfield_meta_key] = $rep_sub_fields[$repKey];
									}
									if($reptype == 'date_picker'){
										$dateformat = 'Ymd';
										$fieldnm = substr($rep_subfield_meta_key,strrpos($rep_subfield_meta_key,'_')+1);
										$var_rep = trim($val);
										$date_rep = str_replace('/', '-', "$var_rep");					
										if($mode == 'Insert'){
											if($var_rep == 0 || $var_rep == ''){
												$acf_rep_field_info[$rep_subfield_meta_key] = $var_rep;
												$acf_rep_field_info['_'.$rep_subfield_meta_key]=$rep_sub_fields[$repKey];
											}
											else{
												$date_rep_of = $helpers_instance->validate_datefield($var_rep,$fieldnm,$dateformat,$line_number);
												$acf_rep_field_info[$rep_subfield_meta_key] = $date_rep_of;
												$acf_rep_field_info['_'.$rep_subfield_meta_key]=$rep_sub_fields[$repKey];

											}	
										}
										else{
											if($var_rep == 0 || $var_rep == ''){
											$acf_rep_field_info[$rep_subfield_meta_key] =$var_rep ;
											$acf_rep_field_info['_'.$rep_subfield_meta_key]=$rep_sub_fields[$repKey];
											}	
											else{
												$date_rep_of = $helpers_instance->validate_datefield($var_rep,$fieldnm,$dateformat,$line_number);
												$acf_rep_field_info[$rep_subfield_meta_key] = $date_rep_of;
												$acf_rep_field_info['_'.$rep_subfield_meta_key]=$rep_sub_fields[$repKey];
											}
										}									
									}
									if($reptype == 'checkbox'){
				
										$explode_val = explode(',',trim($val));	
										$explode_val_name = [];
										foreach($explode_val as $explode_acf_csv_value){
											$explode_val_name[] = trim($explode_acf_csv_value);
										}
				
										$acf_rep_field_info[$rep_subfield_meta_key] = $explode_val_name;
										$acf_rep_field_info['_'.$rep_subfield_meta_key]=$rep_sub_fields[$repKey];
									}
									if($reptype == 'gallery'){
										$gallery_ids = array();
										if ( is_array( $gallery_ids ) ) {
											unset( $gallery_ids );
											$gallery_ids = array();
										}
										$exploded_gallery_items = explode( ',', $val );
										foreach ( $exploded_gallery_items as $gallery ) {
											$gallery = trim( $gallery );
											if ( preg_match_all( '/\b(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i', $gallery ) ) {
												$get_gallery_id = ACFProImport::$media_instance->media_handling( $gallery, $pID);	
												if ( $get_gallery_id != '' ) {
													$gallery_ids[] = $get_gallery_id;
													//ACFProImport::$media_instance->acfgalleryMetaImports($gallery_ids,$maps,$plugin);
												}
												
				
											} else {
												$galleryLen         = strlen( $gallery );
												$checkgalleryid     = intval( $gallery );
												$verifiedGalleryLen = strlen( $checkgalleryid );
												if ( $galleryLen == $verifiedGalleryLen ) {
													$gallery_ids[] = $gallery;
													//ACFProImport::$media_instance->acfgalleryMetaImports($gallery_ids,$maps,$plugin);
												}
											}
										}
										$acf_rep_field_info[$rep_subfield_meta_key] = $gallery_ids;
										$acf_rep_field_info['_'.$rep_subfield_meta_key] = $rep_sub_fields[$repKey];
									}
									if($reptype == 'link'){
										$explode_acf_val = explode(',',$val);
										$serial_acf_val = [];
										foreach($explode_acf_val as $explode_acf_value){
											$serial_acf_val[] = trim($explode_acf_value);
										}	
				
										$serial_acf_value['title'] = $serial_acf_val[0];
										$serial_acf_value['url'] = $serial_acf_val[1];
										if($serial_acf_val[2] == 1){
											$serial_acf_value['target'] = '_blank';
										}else{
											$serial_acf_value['target'] = '';
										}
				
										$acf_rep_field_info[$rep_subfield_meta_key] = $serial_acf_value;
										$acf_rep_field_info['_'.$rep_subfield_meta_key]=$rep_sub_fields[$repKey];
									}
				
									//Push meta information into WordPress
				
									elseif($reptype == 'relationship' || $reptype == 'taxonomy') {
										$exploded_relations = $relations = array();
										$exploded_relations = explode(',', $val);
										foreach($exploded_relations as $relVal) {
											$relationTerm = trim( $relVal );
											if ( $reptype == 'taxonomy' ) {
												$taxonomy_name =  $subfield_info['taxonomy'];
												$check_is_valid_term = $helpers_instance->get_requested_term_details( $pID, array($relationTerm), $taxonomy_name );
												$relations[]         = $check_is_valid_term;
											} else {
												if(is_numeric($relVal)){
													$relations[] = $relationTerm;
												}
												else{
													$relationTerm=$wpdb->_real_escape($relationTerm);
													$query = "SELECT id FROM {$wpdb->prefix}posts WHERE post_title ='{$relationTerm}' AND post_status!='trash'";
													$multiple_id = $wpdb->get_results($query);
													foreach($multiple_id as $mul_id){
														$relations[]=trim($mul_id->id);
													}
												}
												
											}
										}
										$acf_rep_field_info[$rep_subfield_meta_key] = $relations;
										$acf_rep_field_info['_'.$rep_subfield_meta_key] = $rep_sub_fields[$repKey];
									} 
									if($reptype == 'image' && ($repeater_sub_image_import_method[$imgkey] == 'url' || $repeater_sub_image_import_method[$imgkey] == 'array' )) {
										$image_link = trim($val);
										if(preg_match_all('/\b(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i', $image_link)){
											$acf_rep_field_info[$rep_subfield_meta_key] = ACFProImport::$media_instance->media_handling($image_link, $pID);
											$img_id[]=$acf_rep_field_info[$rep_subfield_meta_key];
											//ACFProImport::$media_instance->acfimageMetaImports($img_id,$maps,$plugin);
											$acf_rep_field_info['_'.$rep_subfield_meta_key] =$rep_sub_fields[$repKey];
										} else {
											$acf_rep_field_info[$rep_subfield_meta_key] = $image_link;
											$img_id[]=$img_id[]=$acf_rep_field_info[$rep_subfield_meta_key];
											//ACFProImport::$media_instance->acfimageMetaImports($img_id,$maps,$plugin);
											$acf_rep_field_info['_'.$rep_subfield_meta_key] = $rep_sub_fields[$repKey];
										}
									}
									if(!empty($filekey) && $repeater_sub_file_import_method[$filekey] == 'url' || $repeater_sub_file_import_method[$filekey] == 'array' ) {
										$image_link = trim($val);
										if(preg_match_all('/\b(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i', $image_link)){
											$ext = pathinfo($image_link, PATHINFO_EXTENSION);
											if($ext== 'pdf' || $ext == 'mp3' || $ext == $ext) {
												$fil_id = $wpdb->get_col($wpdb->prepare("select ID from {$wpdb->prefix}posts where guid = %s AND post_type='attachment'",$image_link));
												if(!empty($fil_id)) {
													$acf_rep_field_info[$rep_subfield_meta_key]=$fil_id[0];
												}else {
													$acf_rep_field_info[$rep_subfield_meta_key] = ACFProImport::$media_instance->media_handling( $image_link, $pID );
												}
												$acf_rep_field_info['_'.$rep_subfield_meta_key] = $rep_sub_fields[$repKey];
											} else {
												$acf_rep_field_info[$rep_subfield_meta_key] = ACFProImport::$media_instance->media_handling( $image_link, $pID );
												$acf_rep_field_info['_'.$rep_subfield_meta_key] = $rep_sub_fields[$repKey];
											}
										} else {
											$acf_rep_field_info[$rep_subfield_meta_key] = $image_link;
											$acf_rep_field_info['_'.$rep_subfield_meta_key] = $rep_sub_fields[$repKey];
										}
									}

									
								}
							}

						}

						$repeater_image_import_method[$repeater_fields[$repKey]] = isset($repeater_image_import_method[$repeater_fields[$repKey]]) ? $repeater_image_import_method[$repeater_fields[$repKey]] :'';
						$repeater_file_import_method[$repeater_fields[$repKey]] = isset($repeater_file_import_method[$repeater_fields[$repKey]]) ? $repeater_file_import_method[$repeater_fields[$repKey]] :'';
						
						if($repeater_image_import_method[$repeater_fields[$repKey]] == 'url' || $repeater_image_import_method[$repeater_fields[$repKey]] == 'array' ) {
							$image_link = trim($val);
							if(preg_match_all('/\b(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i', $image_link)){
								//$acf_rep_field_info[$rep_field_meta_key] = ACFProImport::$media_instance->media_handling($image_link, $pID);
								$acf_rep_field_info[$rep_field_meta_key] = ACFProImport::$media_instance->image_meta_table_entry('', $pID, $rep_field_meta_key, $image_link, $hash_key, 'acf_repeater', $get_import_type,$templatekey,$gmode);
								$img_id[]=$img_id[]=$acf_rep_field_info[$rep_field_meta_key];
								//ACFProImport::$media_instance->acfimageMetaImports($img_id,$maps,$plugin);
								$acf_rep_field_info['_'.$rep_field_meta_key] = $repeater_fields[$repKey];
							} else {
								$acf_rep_field_info[$rep_field_meta_key] = $image_link;
								$img_id[]=$img_id[]=$acf_rep_field_info[$rep_field_meta_key];
								//ACFProImport::$media_instance->acfimageMetaImports($img_id,$maps,$plugin);
								$acf_rep_field_info['_'.$rep_field_meta_key] = $repeater_fields[$repKey];
							}
						}
						if($repeater_file_import_method[$repeater_fields[$repKey]] == 'url' || $repeater_file_import_method[$repeater_fields[$repKey]] == 'array' ) {
							$image_link = trim($val);
							if(preg_match_all('/\b(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i', $image_link)){
								$ext = pathinfo($image_link, PATHINFO_EXTENSION);
								if($ext== 'pdf' || $ext == 'mp3' || $ext == $ext) {
									$fil_id = $wpdb->get_col($wpdb->prepare("select ID from {$wpdb->prefix}posts where guid = %s AND post_type='attachment'",$image_link));
									if(!empty($fil_id)) {
										$acf_rep_field_info[$rep_field_meta_key]=$fil_id[0];
									}else {
										//$acf_rep_field_info[$rep_field_meta_key] = ACFProImport::$media_instance->media_handling( $image_link, $pID );
										$acf_rep_field_info[$rep_field_meta_key] = ACFProImport::$media_instance->image_meta_table_entry('', $pID, $rep_field_meta_key, $image_link, $hash_key, 'acf_repeater', $get_import_type,$templatekey,$gmode);
									}
									$acf_rep_field_info['_'.$rep_field_meta_key] = $repeater_fields[$repKey];
								} else {
									//$acf_rep_field_info[$rep_field_meta_key] = ACFProImport::$media_instance->media_handling( $image_link, $pID );
									$acf_rep_field_info[$rep_field_meta_key] = ACFProImport::$media_instance->image_meta_table_entry('', $pID, $rep_field_meta_key, $image_link, $hash_key, 'acf_repeater', $get_import_type,$templatekey,$gmode);
									$acf_rep_field_info['_'.$rep_field_meta_key] = $repeater_fields[$repKey];
								}
							} 

							else {
								//added code for repeater file field with filename
								if( preg_match_all( '/\b[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i', $image_link, $matchedlist, PREG_PATTERN_ORDER ) ){
									$fil_id = $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE post_type = 'attachment' AND guid LIKE '%$image_link'", ARRAY_A);
									if(!empty($fil_id)) {
										$acf_rep_field_info[$rep_field_meta_key]=$fil_id[0]['ID'];
										$acf_rep_field_info['_'.$rep_field_meta_key] = $repeater_fields[$repKey];
									}
									else{
										$acf_rep_field_info[$rep_field_meta_key] = $image_link;
										$acf_rep_field_info['_'.$rep_field_meta_key] = $repeater_fields[$repKey];
									}
								}
								else{
									$acf_rep_field_info[$rep_field_meta_key] = $image_link;
									$acf_rep_field_info['_'.$rep_field_meta_key] = $repeater_fields[$repKey];
								}
							}
						}  
						if ($rep_type == 'message') {
							$field_info['message'] = $val;
						}
					}
					$i++;
				}
			}
			if(!empty($acf_rep_field_info)) {
				foreach($acf_rep_field_info as $fName => $fVal) {

					$listTaxonomy = get_taxonomies();
					if (in_array($importAs, $listTaxonomy)) {
						if($term_meta = 'yes'){
							update_term_meta($pID, $fName, $fVal);
						}else{
							$option_name = $importAs . "_" . $pID . "_" . $fName;
							$option_value = $fVal;
							if (is_array($option_value)) {
								$option_value = serialize($option_value);
							}
							update_option("$option_name", "$option_value");
						}
					}
					else{
						if($importAs == 'Users'){
							update_user_meta($pID, $fName, $fVal);
						}else{
							update_post_meta($pID, $fName, $fVal);
						}
					}

				}
			}

			$createdFields[] = $repKey;
			$rep_fname = $repKey;
			$rep_fID   = $repeater_fields[$repKey];
			// Flexible Content
			$flexible_content = array();
			if ( array_key_exists( $rep_fID, $repeater_flexible_content_import_method ) && $repeater_flexible_content_import_method[ $rep_fID ] != null ) {
				$flexible_content[] = $repeater_flexible_content_import_method[ $rep_fID ];
				$listTaxonomy = get_taxonomies();

				if($importAs == 'Users'){
					update_user_meta($pID, $rep_fname, $flexible_content);
				}
				elseif(in_array($importAs, $listTaxonomy)){
					if($term_meta = 'yes'){	
						update_term_meta($pID, $rep_fname, $flexible_content);
					}
				}
				else{
					update_post_meta($pID, $rep_fname, $flexible_content);
				}	
			}
		}


		$countof_flexi_child_names = isset($acf_rep_field_info) ? array_count_values($acf_rep_field_info) : [];
		$flexi_inner_parent_child_names = [];

		foreach($rep_parent_fields as $pKey => $pVal) {

			//$get_cust  = $wpdb->get_results( $wpdb->prepare( "select ID, post_content, post_name, post_parent from {$wpdb->prefix}posts where post_excerpt = %s", $pKey),ARRAY_A);
			$get_cust  = $wpdb->get_results( $wpdb->prepare( "select ID, post_content, post_name, post_parent from {$wpdb->prefix}posts where post_name = %s", $pKey),ARRAY_A);
			foreach ($get_cust as $get_val ) {
				$custvalue = $get_val['post_content'];
				$post_content = unserialize($custvalue);
				$field_type = $post_content['type'];
				$custkey='_'.$pKey; 
			}
			$listTaxonomy = get_taxonomies();
			if (in_array($importAs, $listTaxonomy)) {
				if($term_meta = 'yes'){
					update_term_meta($pID, $pKey, $pVal);
				}else{
					$option_name = $importAs . "_" . $pID . "_" . $pKey;
					$option_value = $pVal;
					if (is_array($option_value)) {
						$option_value = serialize($option_value);
					}
					update_option("$option_name", "$option_value");
				}
			}
			elseif(isset($field_type) && $field_type == 'flexible_content'){

				$flexible_parent_id = $flexible_array[$pKey];
				if(!empty($flexible_parent_id)){
					$get_flexi_child_name = $wpdb->get_results("SELECT ID, post_name, post_content, post_excerpt FROM {$wpdb->prefix}posts WHERE post_parent = $flexible_parent_id", ARRAY_A);
					$flexi_child_array = [];

					$temp = 0;
					foreach($get_flexi_child_name as $flexi_values){	

						if(array_key_exists($flexi_values['post_name'] , $countof_flexi_child_names)){
							array_push($flexi_child_array, $countof_flexi_child_names[$flexi_values['post_name']]);
						}

						$flexi_post_content = unserialize($flexi_values['post_content']);
						if($flexi_post_content['type'] == 'flexible_content'){

							$flexible_parent_name = $wpdb->get_var("SELECT post_excerpt FROM {$wpdb->prefix}posts WHERE ID = $flexible_parent_id ");
							$flexible_layout_names = explode('->' , $data_array[$flexible_parent_name]);
							$flexible_parent_layout_name = $flexible_layout_names[0];

							$flexi_post_id = $flexi_values['ID'];

							$get_inner_flexi_child_name = $wpdb->get_results("SELECT post_name, post_excerpt, post_content FROM {$wpdb->prefix}posts WHERE post_parent = $flexi_post_id", ARRAY_A);
							foreach($get_inner_flexi_child_name as $inner_flexi_values){
								if(array_key_exists($inner_flexi_values['post_name'] , $countof_flexi_child_names)){
									array_push($flexi_child_array, $countof_flexi_child_names[$inner_flexi_values['post_name']]);
								}
							}

							$flexible_child_name = $wpdb->get_var("SELECT post_excerpt FROM {$wpdb->prefix}posts WHERE ID = $flexi_post_id ");	
							if(strpos($flexible_layout_names[1], '|') !== false){
								$flexible_inner_layout_names = explode('|', $flexible_layout_names[1]);
								$flexi_inner_parent_child_names[$flexible_parent_name .'->'. $flexible_child_name] = $flexible_parent_layout_name .'->'.$flexible_inner_layout_names[$temp];	
								$temp++;
							}
							else{
								$flexible_child_layout_name = $flexible_layout_names[1];
								$flexi_inner_parent_child_names[$flexible_parent_name .'->'. $flexible_child_name] = $flexible_parent_layout_name .'->'.$flexible_child_layout_name;	
							}
						}
					}
				}	
				$final_flexi_count = max($flexi_child_array);

				//$flexible_group = explode('|',$data_array[$pKey]);
				$flexi_group_value = $data_array[$pKey];
				foreach ($repeater_field_rows as $repKey => $repVal) {
					//foreach($flexible_group as $flexi_group_value){	
					if(strpos($flexi_group_value, '->') !== false){
						$flexible_inner_group = explode('->', $flexi_group_value);

						$flexible_inner_group_values = $flexible_inner_group[0];
						if($final_flexi_count > 1){
							$flexible_inner_group_values = array_fill(0, $final_flexi_count, $flexible_inner_group_values);
						}
						$flex_value[$repKey] = $flexible_inner_group_values;

						$is_inner_flexible = true;
					}
					else{
						if($final_flexi_count > 1){
							$flexi_group_value = array_fill(0, $final_flexi_count, $flexi_group_value);
						}
						$flex_value[$repKey] = $flexi_group_value;	
					}

					if($is_inner_flexible){	
						foreach($flexi_inner_parent_child_names as $flexi_inner_names_keys => $flexi_inner_names_values){
							$flexi_inner_names_key = explode('->', $flexi_inner_names_keys);
							$flexi_inner_names_value = explode('->', $flexi_inner_names_values);

							if((strpos($pKey, $flexi_inner_names_key[0]) !== false) && (strpos($pKey, $flexi_inner_names_key[1]) !== false)){
								$flexible_inner_groups_values = $flexi_inner_names_value[1];
								if($final_flexi_count > 1){
									$flexible_inner_groups_values = array_fill(0, $final_flexi_count, $flexible_inner_groups_values);
								}
								$flex_value[$repKey] = $flexible_inner_groups_values;
							}
						}
					}		
					//}		
				}		
				if($importAs == 'Users'){
					update_user_meta($pID, $pKey, $flex_value);
				}elseif(in_array($importAs, $listTaxonomy)){
					if($term_meta = 'yes'){
						update_term_meta($pID, $pKey, $flex_value);
					}
				}else{
					if(is_array($flex_value[0])){
						$flex_values = $flex_value[0];
						update_post_meta($pID, $pKey, $flex_values);
					}
					else{
						update_post_meta($pID, $pKey, $flex_value);
					}
				}	
			}
			else{
				if($importAs == 'Users'){
					update_user_meta($pID, $pKey, $pVal);
				}else{
					update_post_meta($pID, $pKey, $pVal);
				}
			}	
		}	
		//}
	}
	function acf_pro_grp_repeater_import($pID,$repKey,$repVal,$maps,$hash_key,$i,$line_number, $get_import_type,$gmode,$templatekey){		
		$helpers_instance = ImportHelpers::getInstance();
		global $wpdb;
	
		//changed
		// $get_subfield_info  = $wpdb->get_results( $wpdb->prepare( "select ID, post_content, post_name, post_parent from {$wpdb->prefix}posts where post_excerpt = %s", $repKey ) );
		$get_subfield_info  = $wpdb->get_results( $wpdb->prepare( "select ID, post_content, post_name, post_parent from {$wpdb->prefix}posts where post_name = %s", $repKey ) );
		
		$subfield_info = unserialize( $get_subfield_info[0]->post_content );
		$rep_sub_fields[$repKey] = $get_subfield_info[0]->post_name;
		
		if(isset($subfield_info['type']) && $subfield_info['type'] == 'flexible_content') {
			$repeater_sub_flexible_content_import_method[ $get_subfield_info[0]->post_name ] = $subfield_info['layouts'][0]['name'];
			$flexible_sub_array[$repKey] = $get_subfield_info[0]->ID;
		} elseif(isset($subfield_info['type']) && ($subfield_info['type'] == 'image' || $subfield_info['type'] == 'file')) {
			if($subfield_info['type'] == 'image') {
				$repeater_sub_image_import_method[ $get_subfield_info[0]->post_name ] = $subfield_info['return_format'];
				$imgkey = $get_subfield_info[0]->post_name ;
				$repeater_sub_sub_field_type[$rep_sub_fields[$repKey]] =$subfield_info['type'];
				//$group_sub_sub_field_type[ $get_subfield_info[0]->post_name ] = $subfield_info['type'];
			} else {
				$repeater_sub_file_import_method[ $get_subfield_info[0]->post_name ] = $subfield_info['return_format'];
				$repeater_sub_sub_field_type[$rep_sub_fields[$repKey]] =$subfield_info['type'];
			}
		} else {
			$repeater_sub_sub_field_type[ $get_subfield_info[0]->post_name ] = $subfield_info['type'];
		}
		
		// Parse values if have any multiple values
		$repeater_sub_field_rows = explode('|', $repVal);
		$count=count($repeater_sub_field_rows);
		$j = 0;		
		foreach($repeater_sub_field_rows as $index => $value) {
			$rep_sub_field_values = explode('->', $value);
			$checkCount = count($rep_sub_field_values);			
			foreach($rep_sub_field_values as $key => $val) {				
				if($checkCount > 1){
					$rep_subfield_meta_key = $this->getMetaKeyOfRepeaterGroupField( $pID, $repKey, $index, $key);	
					//$rep_subfield_meta_key = $this->getMetaKeyOfRepeaterGroupField( $pID, $grpKey, $index, $key ,$count,$importAs);
				}
				else{
					//changed
					// $rep_subfield_meta_key = $this->getMetaKeyOfRepeaterGroupField( $pID, $repKey, $index, $key );
					$rep_subfield_meta_key = $this->getMetaKeyOfRepeaterGroupField( $pID, $repKey, $i, $j );

					//$rep_subfield_meta_key = $this->getMetaKeyOfRepeaterGroupField( $pID, $grpKey, $i, $j,$count,$importAs );
				}

				if($rep_subfield_meta_key[0] == '_' && $rep_subfield_meta_key[1] != '_')
					$rep_subfield_meta_key = substr($rep_subfield_meta_key, 1);
				$reps_subfield_parent_key = explode( '_' . $repKey, $rep_subfield_meta_key );
				
				  $rep_subfield_parent_key =  $reps_subfield_parent_key[0];
				
				if (substr($rep_subfield_parent_key, -1) == "_") {
					$rep_subfield_parent_key = substr($rep_subfield_parent_key, 0, -1);
				}
				
				$super_sub_parent = explode('_'.$index.'_',$rep_subfield_parent_key);
				
				//changed
				//  $rep_sub_parent_fields[$super_sub_parent[0]] = count($rep_sub_field_rows);
				$rep_sub_parent_fields[$super_sub_parent[0]] = count($rep_sub_field_values);
				
				if($checkCount > 1)
					$rep_sub_parent_fields[$rep_field_parent_key] = $key + 1;
				else
					//changed
					// $rep_parent_fields[$rep_field_parent_key] = $i + 1;
					$rep_sub_parent_fields[$rep_field_parent_key] = $i + 1;
				$j++;

				$reptype = $repeater_sub_sub_field_type[$rep_sub_fields[$repKey]];				
				if($reptype == 'user' || $reptype == 'page_link' || $reptype == 'post_object' || $reptype == 'select') {
					if($subfield_info['multiple'] == 0){
						$acf_rep_field_value = trim($val);
						if(is_string($acf_rep_field_value)){
							$acf_rep_field_value = $wpdb->_real_escape($acf_rep_field_value);
							$query = "SELECT id FROM {$wpdb->prefix}posts WHERE post_title ='{$acf_rep_field_value}' AND post_status='publish'";
							$name = $wpdb->get_results($query);
							if (!empty($name)) {
								$acf_rep_field_value=$name[0]->id;
							}
						}
						elseif (is_numeric($acf_rep_field_value)) {
							$acf_rep_field_value=$acf_rep_field_value;
						}
					}else{
						$acf_rep_value_exp = explode(',',trim($val));
						$acf_rep_field_value = array();
						foreach($acf_rep_value_exp as $acf_reps_value){
							$acf_reps_value = $wpdb->_real_escape($acf_reps_value);
							$query = "SELECT id FROM {$wpdb->prefix}posts WHERE post_title ='{$acf_reps_value}' AND post_status!='trash'";
							$multiple_id = $wpdb->get_results($query);
							if(!$multiple_id){
								$acf_rep_field_value=$acf_rep_value_exp;
							}
							else{
								foreach($multiple_id as $mul_id){
									$acf_rep_field_value[]=trim($mul_id->id);
								}
							}	
						}
					}
					$acf_rep_field_info[$rep_subfield_meta_key] = $acf_rep_field_value;	
					$acf_rep_field_info['_'.$rep_subfield_meta_key]=$rep_sub_fields[$repKey];
				}
				if($reptype == 'text' || $reptype == 'textarea' || $reptype == 'email' || $reptype == 'number' || $reptype == 'url' || $reptype == 'password' || $reptype == 'range' || $reptype == 'radio' || $reptype == 'true_false' || $reptype == 'time_picker' || $reptype == 'color_picker' || $reptype == 'button_group' || $reptype == 'oembed' || $reptype == 'wysiwyg'){

					$acf_rep_field_info[$rep_subfield_meta_key] = trim($val);
					$acf_rep_field_info['_'.$rep_subfield_meta_key]=$rep_sub_fields[$repKey];	
				}
				if($reptype == 'date_time_picker'){							
					$dt_rep_var = trim($val);
					$dateformat = "Y-m-d H:i:s";
					
					$fieldnm = substr($rep_subfield_meta_key,strrpos($rep_subfield_meta_key,'_')+1);
					if($dt_rep_var== 0 || $dt_rep_var== ''){
						$acf_rep_field_info[$rep_subfield_meta_key] =$dt_rep_var ;
						$acf_rep_field_info['_'.$rep_subfield_meta_key]=$rep_sub_fields[$repKey];
					}	
					else{
						$date_time_rep_of = $helpers_instance->validate_datefield($dt_rep_var,$fieldnm,$dateformat,$line_number);
						$acf_rep_field_info[$rep_subfield_meta_key] = $date_time_rep_of;
						$acf_rep_field_info['_'.$rep_subfield_meta_key]=$rep_sub_fields[$repKey];
					}	
				}
				if ($reptype == 'google_map') {

					$location[] = trim($val);
					foreach($location as $loc){
						$locc=implode('|', $location);
					}
					list($add, $lat,$lng) = explode('|', $locc);
					$area = rtrim($add, ",");
					$map = array(
						'address' => $area,
						'lat'     =>  $lat,
						'lng'     => $lng
					);
					$acf_rep_field_info[$rep_subfield_meta_key] = $map;
					$acf_rep_field_info['_'.$rep_subfield_meta_key] = $rep_sub_fields[$repKey];
				}
				if($reptype == 'date_picker'){
					$dateformat = 'Ymd';
					$fieldnm = substr($rep_subfield_meta_key,strrpos($rep_subfield_meta_key,'_')+1);
					$var_rep = trim($val);
					$date_rep = str_replace('/', '-', "$var_rep");					
					if($mode == 'Insert'){
						if($var_rep == 0 || $var_rep == ''){
							$acf_rep_field_info[$rep_subfield_meta_key] = $var_rep;
							$acf_rep_field_info['_'.$rep_subfield_meta_key]=$rep_sub_fields[$repKey];
						}
						else{
							$date_rep_of = $helpers_instance->validate_datefield($var_rep,$fieldnm,$dateformat,$line_number);
							$acf_rep_field_info[$rep_subfield_meta_key] = $date_rep_of;
							  $acf_rep_field_info['_'.$rep_subfield_meta_key]=$rep_sub_fields[$repKey];

						}	
					}
					else{
						if($var_rep == 0 || $var_rep == ''){
						$acf_rep_field_info[$rep_subfield_meta_key] =$var_rep ;
						$acf_rep_field_info['_'.$rep_subfield_meta_key]=$rep_sub_fields[$repKey];
						}	
						else{
							$date_rep_of = $helpers_instance->validate_datefield($var_rep,$fieldnm,$dateformat,$line_number);
							$acf_rep_field_info[$rep_subfield_meta_key] = $date_rep_of;
							$acf_rep_field_info['_'.$rep_subfield_meta_key]=$rep_sub_fields[$repKey];
						}
					}					
				}
				if($reptype == 'checkbox'){

					$explode_val = explode(',',trim($val));	
					$explode_val_name = [];
					foreach($explode_val as $explode_acf_csv_value){
						$explode_val_name[] = trim($explode_acf_csv_value);
					}

					$acf_rep_field_info[$rep_subfield_meta_key] = $explode_val_name;
					$acf_rep_field_info['_'.$rep_subfield_meta_key]=$rep_sub_fields[$repKey];
				}
				if($reptype == 'gallery'){
					$gallery_ids = array();
					if ( is_array( $gallery_ids ) ) {
						unset( $gallery_ids );
						$gallery_ids = array();
					}
					$exploded_gallery_items = explode( ',', $val );
					foreach ( $exploded_gallery_items as $gallery ) {
						$gallery = trim( $gallery );
						if ( preg_match_all( '/\b(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i', $gallery ) ) {
							//$get_gallery_id = ACFProImport::$media_instance->media_handling( $gallery, $pID);	
							$get_gallery_id = ACFProImport::$media_instance->image_meta_table_entry('', $pID, $rep_subfield_meta_key, $gallery, $hash_key, 'acf_repeater_group_gallery', $get_import_type,$templatekey,$gmode);
							if ( $get_gallery_id != '' ) {
								$gallery_ids[] = $get_gallery_id;
								//ACFProImport::$media_instance->acfgalleryMetaImports($gallery_ids,$maps,$plugin);
							}
							

						} else {
							$galleryLen         = strlen( $gallery );
							$checkgalleryid     = intval( $gallery );
							$verifiedGalleryLen = strlen( $checkgalleryid );
							if ( $galleryLen == $verifiedGalleryLen ) {
								$gallery_ids[] = $gallery;
								//ACFProImport::$media_instance->acfgalleryMetaImports($gallery_ids,$maps,$plugin);
							}
						}
					}
					$acf_rep_field_info[$rep_subfield_meta_key] = $gallery_ids;
					$acf_rep_field_info['_'.$rep_subfield_meta_key] = $rep_sub_fields[$repKey];
				}
				if($reptype == 'link'){
					$explode_acf_val = explode(',',$val);
					$serial_acf_val = [];
					foreach($explode_acf_val as $explode_acf_value){
						$serial_acf_val[] = trim($explode_acf_value);
					}	

					$serial_acf_value['title'] = $serial_acf_val[0];
					$serial_acf_value['url'] = $serial_acf_val[1];
					if($serial_acf_val[2] == 1){
						$serial_acf_value['target'] = '_blank';
					}else{
						$serial_acf_value['target'] = '';
					}

					$acf_rep_field_info[$rep_subfield_meta_key] = $serial_acf_value;
					$acf_rep_field_info['_'.$rep_subfield_meta_key]=$rep_sub_fields[$repKey];
				}

				//Push meta information into WordPress

				elseif($reptype == 'relationship' || $reptype == 'taxonomy') {
					$exploded_relations = $relations = array();
					$exploded_relations = explode(',', $val);
					foreach($exploded_relations as $relVal) {
						$relationTerm = trim( $relVal );
						//$relTerm[] = $relationTerm;
						if ( $reptype == 'taxonomy' ) {
							$taxonomy_name =  $subfield_info['taxonomy'];
							//$taxonomy_name       = substr( $repKey, 4 );
							$check_is_valid_term = $helpers_instance->get_requested_term_details( $pID, array($relationTerm), $taxonomy_name );
							$relations[]         = $check_is_valid_term;
						} else {
							if(is_numeric($relVal)){
								$relations[] = $relationTerm;
							}
							else{
								$relationTerm=$wpdb->_real_escape($relationTerm);
								$query = "SELECT id FROM {$wpdb->prefix}posts WHERE post_title ='{$relationTerm}' AND post_status!='trash'";
								$multiple_id = $wpdb->get_results($query);
								foreach($multiple_id as $mul_id){
									$relations[]=trim($mul_id->id);
								}
							}
							
						}
					}
					$acf_rep_field_info[$rep_subfield_meta_key] = $relations;
					$acf_rep_field_info['_'.$rep_subfield_meta_key] = $rep_sub_fields[$repKey];
				} 

				if($reptype == 'image' && ($repeater_sub_image_import_method[$imgkey] == 'url' || $repeater_sub_image_import_method[$imgkey] == 'array' )) {
					$image_link = trim($val);
					if(preg_match_all('/\b(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i', $image_link)){
						//$acf_rep_field_info[$rep_subfield_meta_key] = ACFProImport::$media_instance->media_handling($image_link, $pID);
						$acf_rep_field_info[$rep_subfield_meta_key] = ACFProImport::$media_instance->image_meta_table_entry('', $pID, $rep_subfield_meta_key, $image_link, $hash_key, 'acf_repeater_group', $get_import_type,$templatekey,$gmode);
						$img_id[]=$acf_rep_field_info[$rep_subfield_meta_key];
						//ACFProImport::$media_instance->acfimageMetaImports($img_id,$maps,$plugin);
						$acf_rep_field_info['_'.$rep_subfield_meta_key] =$rep_sub_fields[$repKey];
					} else {
						$acf_rep_field_info[$rep_subfield_meta_key] = $image_link;
						$img_id[]=$img_id[]=$acf_rep_field_info[$rep_subfield_meta_key];
						//ACFProImport::$media_instance->acfimageMetaImports($img_id,$maps,$plugin);
						$acf_rep_field_info['_'.$rep_subfield_meta_key] = $rep_sub_fields[$repKey];
					}
				}
				if(!empty($filekey) && $repeater_sub_file_import_method[$filekey] == 'url' || $repeater_sub_file_import_method[$filekey] == 'array' ) {
					$image_link = trim($val);
					if(preg_match_all('/\b(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i', $image_link)){
						$ext = pathinfo($image_link, PATHINFO_EXTENSION);
						if($ext== 'pdf' || $ext == 'mp3' || $ext == $ext) {
							$fil_id = $wpdb->get_col($wpdb->prepare("select ID from {$wpdb->prefix}posts where guid = %s AND post_type='attachment'",$image_link));
							if(!empty($fil_id)) {
								$acf_rep_field_info[$rep_subfield_meta_key]=$fil_id[0];
							}else {
								//$acf_rep_field_info[$rep_subfield_meta_key] = ACFProImport::$media_instance->media_handling( $image_link, $pID );
								$acf_rep_field_info[$rep_subfield_meta_key] = ACFProImport::$media_instance->image_meta_table_entry('', $pID, $rep_subfield_meta_key, $image_link, $hash_key, 'acf_repeater_group', $get_import_type,$templatekey,$gmode);
							}
							$acf_rep_field_info['_'.$rep_subfield_meta_key] = $rep_sub_fields[$repKey];
						} else {
							//$acf_rep_field_info[$rep_subfield_meta_key] = ACFProImport::$media_instance->media_handling( $image_link, $pID );
							$acf_rep_field_info[$rep_subfield_meta_key] = ACFProImport::$media_instance->image_meta_table_entry('', $pID, $rep_subfield_meta_key, $image_link, $hash_key, 'acf_repeater_group', $get_import_type,$templatekey,$gmode);
							$acf_rep_field_info['_'.$rep_subfield_meta_key] = $rep_sub_fields[$repKey];
						}
					} else {
						$acf_rep_field_info[$rep_subfield_meta_key] = $image_link;
						$acf_rep_field_info['_'.$rep_subfield_meta_key] = $rep_sub_fields[$repKey];
					}
				}	
			}
		}
		return $acf_rep_field_info;
	}

    function getMetaKeyOfRepeaterField($pID, $field_name, $key = 0, $fKey = 0, $parents = array(), $meta_key = '') {
		global $wpdb;
		//changed
		// $get_field_details  = $wpdb->get_results( $wpdb->prepare( "select ID, post_content, post_name, post_parent, post_excerpt from {$wpdb->prefix}posts where post_excerpt = %s", $field_name ) );
		$get_field_details  = $wpdb->get_results( $wpdb->prepare( "select ID, post_content, post_name, post_parent, post_excerpt from {$wpdb->prefix}posts where post_name = %s ORDER by ID DESC", $field_name ) );
		//changed
		// if(empty($parents) && $field_name == $get_field_details[0]->post_excerpt) {
		if(empty($parents) && $field_name == $get_field_details[0]->post_name) {
			//added
			// $parents[] = $fKey . '_' . $field_name . '_';
			// $meta_key .= $fKey . '_' . $field_name . '_';
			$get_field_excerpt = $wpdb->get_var( $wpdb->prepare( "SELECT post_excerpt from {$wpdb->prefix}posts where post_name = %s", $field_name ) );
			$parents[] = $fKey . '_' . $get_field_excerpt . '_';
			$meta_key .= $fKey . '_' . $get_field_excerpt . '_';
		}
		$get_repeater_parent_field = $wpdb->get_results( $wpdb->prepare( "select post_content, post_name, post_excerpt, post_parent from {$wpdb->prefix}posts where ID = %d", $get_field_details[0]->post_parent ) );
	
		$field_info           = unserialize( $get_repeater_parent_field[0]->post_content );
		if((isset($field_info['type']) && $get_repeater_parent_field[0]->post_parent != 0) && ($field_info['type'] == 'repeater' || $field_info['type'] == 'group' || $field_info['type'] == 'flexible_content' )) {
			//$fieldkey=$get_field_details[0]->post_name;
			$parents[] = $key . '_' . $get_repeater_parent_field[0]->post_excerpt . '_';	
			$meta_key .= $key . '_' . $get_repeater_parent_field[0]->post_excerpt . '_' . $meta_key;
			//changed
			// $meta_key = $this->getMetaKeyOfRepeaterField($pID, $get_repeater_parent_field[0]->post_excerpt, 0, 0, $parents, $meta_key);
			$meta_key = $this->getMetaKeyOfRepeaterField($pID, $get_repeater_parent_field[0]->post_name, 0, 0, $parents, $meta_key);
			update_post_meta($pID, $get_repeater_parent_field[0]->post_excerpt , 1);
			update_post_meta($pID, '_'.$get_repeater_parent_field[0]->post_excerpt , $get_repeater_parent_field[0]->post_name );
		} 
		else {
			if(!empty($parents)) {
				$meta_key = '';
				for($i = count($parents); $i >= 0 ; $i--) {
					if(isset($parents[$i])){
						$meta_key .= $parents[$i];
					}	
				}
			}
			$meta_key = substr($meta_key, 2);
			$meta_key = substr($meta_key, 0, -1);
			return $meta_key;
		}
		return $meta_key;
	}
	
	function getMetaKeyOfRepeaterGroupField($pID, $field_name, $key = 0, $fKey = 0, $parents = array(), $meta_key = '') {

		global $wpdb;

		//changed		
		$get_field_details  = $wpdb->get_results( $wpdb->prepare( "select ID, post_content, post_name, post_parent, post_excerpt from {$wpdb->prefix}posts where post_name = %s", $field_name ) );
	
		//changed		
		if(empty($parents) && $field_name == $get_field_details[0]->post_name) {			
			//changed
			$get_field_excerpt = $wpdb->get_var( $wpdb->prepare( "SELECT post_excerpt from {$wpdb->prefix}posts where post_name = %s", $field_name ) );
			$parents[] =  $get_field_excerpt . '_';
			$meta_key .=  $get_field_excerpt . '_';
		}
	
		$get_repeater_parent_field = $wpdb->get_results( $wpdb->prepare( "select post_content, post_name, post_excerpt, post_parent from {$wpdb->prefix}posts where ID = %d", $get_field_details[0]->post_parent ) );
	
		$field_info           = unserialize( $get_repeater_parent_field[0]->post_content );
		if((isset($field_info['type']) && $get_repeater_parent_field[0]->post_parent != 0) && ($field_info['type'] == 'repeater' || $field_info['type'] == 'group' || $field_info['type'] == 'flexible_content' )) {					
			//changed			
			$parents[] = $fKey . '_' . $get_repeater_parent_field[0]->post_excerpt . '_';	
			$meta_key .= $fKey . '_' . $get_repeater_parent_field[0]->post_excerpt . '_' . $meta_key;

			//changed
			// $meta_key = $this->getMetaKeyOfRepeaterGroupField($pID, $get_repeater_parent_field[0]->post_excerpt, 0, 0, $parents, $meta_key);
			$meta_key = $this->getMetaKeyOfRepeaterGroupField($pID, $get_repeater_parent_field[0]->post_name, 0, 0, $parents, $meta_key);			
		} 
		
		else {
			if(!empty($parents)) {
				$meta_key = '';
				for($i = count($parents); $i >= 0 ; $i--) {
					if(isset($parents[$i])){
						$meta_key .= $parents[$i];
					}	
				}
			}
			$meta_key = substr($meta_key, 2);
			$meta_key = substr($meta_key, 0, -1);
			return $meta_key;
		}
		return $meta_key;		
	} 

	function acfpro_flexible_import_fuctions($data_array, $importAs, $pID,$maps,$mode,$line_number,$gmode,$templatekey) {
		global $wpdb;
		$helpers_instance = ImportHelpers::getInstance();
		
		//get import type
		$listTaxonomy = get_taxonomies();
		if (in_array($importAs, $listTaxonomy)) {
			$get_import_type = 'term';
		}elseif ($importAs == 'Users') {
			$get_import_type = 'user';
		}elseif ($importAs == 'Comments') {
			$get_import_type = 'comment';
		} else {	
			$get_import_type = 'post';
		}

		$createdFields = $rep_parent_fields = $repeater_fields = $repeater_flexible_content_import_method = array();
		$flexible_array = [];
		$parent_key_values = [];
		$child_key_values = [];
		$plugin = 'acf';
		foreach($data_array as $repKey => $repVal) {
			$i = 0;
			// Prepare the meta array by field type
			//$get_field_info  = $wpdb->get_results( $wpdb->prepare( "select ID, post_content, post_name, post_parent from {$wpdb->prefix}posts where post_excerpt = %s", $repKey ) );
			$get_field_info  = $wpdb->get_results( $wpdb->prepare( "select ID, post_content, post_name, post_parent from {$wpdb->prefix}posts where post_name = %s", $repKey ) );
			$parentid =  $get_field_info[0]->post_parent ;
			$field_info = unserialize( $get_field_info[0]->post_content );
			$field_layout[] = isset($field_info['parent_layout']) ? $field_info['parent_layout'] :'';
			$fieldtype =  $wpdb->get_results( $wpdb->prepare( "select ID, post_content, post_name, post_parent from {$wpdb->prefix}posts where ID = $parentid"  ) );
			$fieldtype_info =unserialize( $fieldtype[0]->post_content );
			$repeater_fields[$repKey] = $get_field_info[0]->post_name;
			if(isset($field_info['type']) && $field_info['type'] == 'flexible_content') {
				$repeater_flexible_content_import_method[ $get_field_info[0]->post_name ] = $field_info['layouts'][$field_layout[0]]['name'];
				$flexible_array[$repKey] = $get_field_info[0]->ID;
			} elseif(isset($field_info['type']) && ($field_info['type'] == 'image' || $field_info['type'] == 'file')) {
				if($field_info['type'] == 'image') {
					$repeater_image_import_method[ $get_field_info[0]->post_name ] = $field_info['return_format'];
				} else {
					$repeater_file_import_method[ $get_field_info[0]->post_name ] = $field_info['return_format'];
				}
			} else {
				$repeater_sub_field_type[ $get_field_info[0]->post_name ] = $field_info['type'];
			}
			$repeater_field_rows = explode(',', $repVal);
			
			$j = 0;
			foreach($repeater_field_rows as $index => $value) {
				$repeater_field_values = explode('->', $value);
				$checkCount = count($repeater_field_values);
				foreach($repeater_field_values as $key => $val) {
					if($checkCount > 1){
						   if(isset($fieldtype_info['layouts'])){
							$rep_field_meta_key = $this->getMetaKeyOfFlexibleField( $pID, $repKey, $index, $key );                   
						   }
						   else{
							$rep_field_meta_key = $this->getMetaKeyOfRepeaterField( $pID, $repKey, $index, $key );  
						   }
						
							
					}else{
						    if(isset($fieldtype_info['layouts'])){
								$rep_field_meta_key = $this->getMetaKeyOfFlexibleField( $pID, $repKey, $i, $j );
							}
							else{
								$rep_field_meta_key = $this->getMetaKeyOfRepeaterField( $pID, $repKey, $i, $j );	
							}
													
					}
					if($rep_field_meta_key[0] == '_')
						$rep_field_meta_key = substr($rep_field_meta_key, 1);
					$rep_field_parent_key = explode( '_' . $repKey, $rep_field_meta_key );
					$rep_field_parent_key = substr( $rep_field_parent_key[0], 0, - 2 );
					if (substr($rep_field_parent_key, -1) == "_") {
						$rep_field_parent_key = substr($rep_field_parent_key, 0, -1);
					}
					$super_parent = explode('_'.$index.'_',$rep_field_parent_key);
					$parent_key_values[] = count($repeater_field_rows);
					$rep_parent_fields[$super_parent[0]] = max($parent_key_values);
	
					if($checkCount > 1){
						$child_key_values[] = $key + 1;
						$rep_parent_fields[$rep_field_parent_key] = max($child_key_values); 
					}else{
						$child_key_values[] = $i + 1;
						$rep_parent_fields[$rep_field_parent_key] = max($child_key_values);
					}
					$j++;
					$repeater_sub_field_type[$repeater_fields[$repKey]] = isset($repeater_sub_field_type[$repeater_fields[$repKey]]) ? $repeater_sub_field_type[$repeater_fields[$repKey]] :'';
					$rep_type = $repeater_sub_field_type[$repeater_fields[$repKey]];
	
					if($rep_type == 'user' || $rep_type == 'page_link' || $rep_type == 'post_object' || $rep_type == 'select') {
						if($field_info['multiple'] == 0){
							$acf_rep_field_value = trim($val);
							if(is_string($acf_rep_field_value)){
								$acf_rep_field_value=$wpdb->_real_escape($acf_rep_field_value);
								$query = "SELECT id FROM {$wpdb->prefix}posts WHERE post_title ='{$acf_rep_field_value}' AND post_status='publish'";
								$name = $wpdb->get_results($query);
								if (!empty($name)) {
									$acf_rep_field_value=$name[0]->id;
								}
							}
							elseif (is_numeric($acf_rep_field_value)) {
								$acf_rep_field_value=$acf_rep_field_value;
							}
						}elseif(!$field_info['multiple'] == 0){
							$acf_rep_value_exp = explode(',',trim($val));
							$acf_rep_field_value = array();
							foreach($acf_rep_value_exp as $acf_reps_value){
								$acf_reps_value=$wpdb->_real_escape($acf_reps_value);
								$query = "SELECT id FROM {$wpdb->prefix}posts WHERE post_title ='{$acf_reps_value}' AND post_status!='trash'";
								$multiple_id = $wpdb->get_results($query);
								foreach($multiple_id as $mul_id){
									$acf_rep_field_value[]=trim($mul_id->id);
								}
							}
						}
						$acf_rep_field_info[$rep_field_meta_key] = $acf_rep_field_value;	
						$acf_rep_field_info['_'.$rep_field_meta_key]=$repeater_fields[$repKey];
					}
					if($rep_type == 'text' || $rep_type == 'textarea' || $rep_type == 'email' || $rep_type == 'number' || $rep_type == 'url' || $rep_type == 'password' || $rep_type == 'range' || $rep_type == 'radio' || $rep_type == 'true_false' || $rep_type == 'time_picker' || $rep_type == 'color_picker' || $rep_type == 'button_group' || $rep_type == 'oembed' || $rep_type == 'wysiwyg'){
	
						$acf_rep_field_info[$rep_field_meta_key] = trim($val);
						$acf_rep_field_info['_'.$rep_field_meta_key]=$repeater_fields[$repKey];
					}
					if($rep_type == 'date_time_picker'){
						$dt_rep_var = trim($val);
						$dateformat = "Y-m-d H:i:s";
						$fieldnm =  substr($rep_field_meta_key,strrpos($rep_field_meta_key,'_')+1);								
						if($dt_rep_var== 0 || $dt_rep_var== ''){
							$acf_rep_field_info[$rep_field_meta_key] =$dt_rep_var ;
							$acf_rep_field_info['_'.$rep_field_meta_key]=$repeater_fields[$repKey];
						}	
						else{
							$date_time_rep_of = $helpers_instance->validate_datefield($dt_rep_var,$fieldnm,$dateformat,$line_number);
							$acf_rep_field_info[$rep_field_meta_key] = $date_time_rep_of;
							$acf_rep_field_info['_'.$rep_field_meta_key]=$repeater_fields[$repKey];
						}						
					}
					if ($rep_type == 'google_map') {
	
						$location[] = trim($val);
						foreach($location as $loc){
							$locc=implode('|', $location);
						}
						list($add, $lat,$lng) = explode('|', $locc);
						$area = rtrim($add, ",");
						$map = array(
							'address' => $area,
							'lat'     =>  $lat,
							'lng'     => $lng
						);
						$acf_rep_field_info[$rep_field_meta_key] = $map;
						$acf_rep_field_info['_'.$rep_field_meta_key] = $repeater_fields[$repKey];
					}
					if($rep_type == 'date_picker'){
						$dateformat = "Ymd";
						$var_rep = trim($val);
						$fieldnm = substr($rep_field_meta_key,strrpos($rep_field_meta_key,'_')+1);
						$date_rep = str_replace('/', '-', "$var_rep");
								
						if($var_rep == 0 || $var_rep == ''){
							$acf_rep_field_info[$rep_field_meta_key] = $var_rep;
							$acf_rep_field_info['_'.$rep_field_meta_key]=$repeater_fields[$repKey];
						}
						else{
							$date_rep_of = $helpers_instance->validate_datefield($var_rep,$fieldnm,$dateformat,$line_number);
							$acf_rep_field_info[$rep_field_meta_key] = $date_rep_of;
							$acf_rep_field_info['_'.$rep_field_meta_key]=$repeater_fields[$repKey];
						}	
					}
					if($rep_type == 'checkbox'){
	
						$explode_val = explode(',',trim($val));	
						$explode_val_name = [];
						foreach($explode_val as $explode_acf_csv_value){
							$explode_val_name[] = trim($explode_acf_csv_value);
						}
	
						$acf_rep_field_info[$rep_field_meta_key] = $explode_val_name;
						$acf_rep_field_info['_'.$rep_field_meta_key]=$repeater_fields[$repKey];
					}
					if($rep_type == 'gallery'){
						$gallery_ids = array();
						if ( is_array( $gallery_ids ) ) {
							unset( $gallery_ids );
							$gallery_ids = array();
						}
						$exploded_gallery_items = explode( ',', $val );
						foreach ( $exploded_gallery_items as $gallery ) {
							$gallery = trim( $gallery );
							if ( preg_match_all( '/\b(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i', $gallery ) ) {
								//$get_gallery_id = ACFProImport::$media_instance->media_handling( $gallery, $pID);	
								$get_gallery_id = ACFProImport::$media_instance->image_meta_table_entry('', $pID, $rep_field_meta_key, $gallery, $hash_key, 'acf_flexible_gallery', $get_import_type,$templatekey,$gmode);
								if ( $get_gallery_id != '' ) {
									$gallery_ids[] = $get_gallery_id;
									//ACFProImport::$media_instance->acfgalleryMetaImports($gallery_ids,$maps,$plugin);
								}
							} else {
								$galleryLen         = strlen( $gallery );
								$checkgalleryid     = intval( $gallery );
								$verifiedGalleryLen = strlen( $checkgalleryid );
								if ( $galleryLen == $verifiedGalleryLen ) {
									$gallery_ids[] = $gallery;
									//ACFProImport::$media_instance->acfgalleryMetaImports($gallery_ids,$maps,$plugin);
								}
							}
						}
						$acf_rep_field_info[$rep_field_meta_key] = $gallery_ids;
						$acf_rep_field_info['_'.$rep_field_meta_key] = $repeater_fields[$repKey];
					}
					if($rep_type == 'link'){
						$explode_acf_val = explode(',',$val);
						$serial_acf_val = [];
						foreach($explode_acf_val as $explode_acf_value){
							$serial_acf_val[] = trim($explode_acf_value);
						}	
	
						$serial_acf_value['title'] = $serial_acf_val[0];
						$serial_acf_value['url'] = $serial_acf_val[1];
						if($serial_acf_val[2] == 1){
							$serial_acf_value['target'] = '_blank';
						}else{
							$serial_acf_value['target'] = '';
						}
	
						$acf_rep_field_info[$rep_field_meta_key] = $serial_acf_value;
						$acf_rep_field_info['_'.$rep_field_meta_key]=$repeater_fields[$repKey];
					}
	
					//Push meta information into WordPress
	
					elseif($rep_type == 'relationship' || $rep_type == 'taxonomy') {
						$exploded_relations = $relations = array();
						$exploded_relations = explode(',', $val);
						foreach($exploded_relations as $relVal) {
							$relationTerm = trim( $relVal );
							if ( $rep_type == 'taxonomy' ) {
								$taxonomy_name =  $field_info['taxonomy'];
								$check_is_valid_term = $helpers_instance->get_requested_term_details( $pID, array($relationTerm), $taxonomy_name );
								$relations[]         = $check_is_valid_term;
							} else {
								if(is_numeric($relVal)){
									$relations[] = $relationTerm;
								}
								else{
									$relationTerm=$wpdb->_real_escape($relationTerm);
									$query = "SELECT id FROM {$wpdb->prefix}posts WHERE post_title ='{$relationTerm}' AND post_status!='trash'";
								    $multiple_id = $wpdb->get_results($query);
								    foreach($multiple_id as $mul_id){
										$relations[]=trim($mul_id->id);
									}
								}
								
							}
						}
						$acf_rep_field_info[$rep_field_meta_key] = $relations;
						$acf_rep_field_info['_'.$rep_field_meta_key] = $repeater_fields[$repKey];
					} 
	
					$repeater_image_import_method[$repeater_fields[$repKey]] = isset($repeater_image_import_method[$repeater_fields[$repKey]]) ? $repeater_image_import_method[$repeater_fields[$repKey]] :'';
					$repeater_file_import_method[$repeater_fields[$repKey]] = isset($repeater_file_import_method[$repeater_fields[$repKey]]) ? $repeater_file_import_method[$repeater_fields[$repKey]] :'';

					if($repeater_image_import_method[$repeater_fields[$repKey]] == 'url' || $repeater_image_import_method[$repeater_fields[$repKey]] == 'array' ) {
						$image_link = trim($val);
						if(preg_match_all('/\b(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i', $image_link)){
							//$acf_rep_field_info[$rep_field_meta_key] = ACFProImport::$media_instance->media_handling($image_link, $pID);
							$acf_rep_field_info[$rep_field_meta_key] = ACFProImport::$media_instance->image_meta_table_entry('', $pID, $rep_field_meta_key, $image_link, $hash_key, 'acf_flexible', $get_import_type,$templatekey,$gmode);
							$img_id[]=$img_id[]=$acf_rep_field_info[$rep_field_meta_key];
							//ACFProImport::$media_instance->acfimageMetaImports($img_id,$maps,$plugin);
							$acf_rep_field_info['_'.$rep_field_meta_key] = $repeater_fields[$repKey];
						} else {
							$acf_rep_field_info[$rep_field_meta_key] = $image_link;
							$img_id[]=$img_id[]=$acf_rep_field_info[$rep_field_meta_key];
							//ACFProImport::$media_instance->acfimageMetaImports($img_id,$maps,$plugin);
							$acf_rep_field_info['_'.$rep_field_meta_key] = $repeater_fields[$repKey];
						}
					}
					if($repeater_file_import_method[$repeater_fields[$repKey]] == 'url' || $repeater_file_import_method[$repeater_fields[$repKey]] == 'array' ) {
						$image_link = trim($val);
						if(preg_match_all('/\b(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i', $image_link)){
							$ext = pathinfo($image_link, PATHINFO_EXTENSION);
							if($ext== 'pdf' || $ext == 'mp3' || $ext == $ext) {
								$fil_id = $wpdb->get_col($wpdb->prepare("select ID from {$wpdb->prefix}posts where guid = %s AND post_type='attachment'",$image_link));
								if(!empty($fil_id)) {
									$acf_rep_field_info[$rep_field_meta_key]=$fil_id[0];
								}else {
									//$acf_rep_field_info[$rep_field_meta_key] = ACFProImport::$media_instance->media_handling( $image_link, $pID );
									$acf_rep_field_info[$rep_field_meta_key] = ACFProImport::$media_instance->image_meta_table_entry('', $pID, $rep_field_meta_key, $image_link, $hash_key, 'acf_flexible', $get_import_type,$templatekey,$gmode);
								}
								$acf_rep_field_info['_'.$rep_field_meta_key] = $repeater_fields[$repKey];
							} else {
								//$acf_rep_field_info[$rep_field_meta_key] = ACFProImport::$media_instance->media_handling( $image_link, $pID );
								$acf_rep_field_info[$rep_field_meta_key] = ACFProImport::$media_instance->image_meta_table_entry('', $pID, $rep_field_meta_key, $image_link, $hash_key, 'acf_flexible', $get_import_type,$templatekey,$gmode);
								$acf_rep_field_info['_'.$rep_field_meta_key] = $repeater_fields[$repKey];
							}
						} else {
							$acf_rep_field_info[$rep_field_meta_key] = $image_link;
							$acf_rep_field_info['_'.$rep_field_meta_key] = $repeater_fields[$repKey];
						}
					}  
					if ($rep_type == 'message') {
						$field_info['message'] = $val;
					}
				}
				$i++;
			}
			if(!empty($acf_rep_field_info)) {
				foreach($acf_rep_field_info as $fName => $fVal) {
	
					$listTaxonomy = get_taxonomies();
					if (in_array($importAs, $listTaxonomy)) {
						if($term_meta = 'yes'){
							update_term_meta($pID, $fName, $fVal);
						}else{
							$option_name = $importAs . "_" . $pID . "_" . $fName;
							$option_value = $fVal;
							if (is_array($option_value)) {
								$option_value = serialize($option_value);
							}
							update_option("$option_name", "$option_value");
						}
					}
					else{
						if($importAs == 'Users'){
							update_user_meta($pID, $fName, $fVal);
						}else{
							update_post_meta($pID, $fName, $fVal);
						}
					}
	
				}
			}
	
			$createdFields[] = $repKey;
			$rep_fname = $repKey;
			$rep_fID   = $repeater_fields[$repKey];
			// Flexible Content
			$flexible_content = array();
			if ( array_key_exists( $rep_fID, $repeater_flexible_content_import_method ) && $repeater_flexible_content_import_method[ $rep_fID ] != null ) {
				$flexible_content[] = $repeater_flexible_content_import_method[ $rep_fID ];
				$listTaxonomy = get_taxonomies();
	
				if($importAs == 'Users'){
					update_user_meta($pID, $rep_fname, $flexible_content);
				}
				elseif(in_array($importAs, $listTaxonomy)){
					if($term_meta = 'yes'){	
						update_term_meta($pID, $rep_fname, $flexible_content);
					}
				}
				else{
					update_post_meta($pID, $rep_fname, $flexible_content);
				}	
			}
		}
		$acf_rep_field_info_check = array_replace($acf_rep_field_info,array_fill_keys(array_keys($acf_rep_field_info, null),''));
		$countof_flexi_child_names = array_count_values($acf_rep_field_info_check);
		$flexi_inner_parent_child_names = [];
	
		foreach($rep_parent_fields as $pKey => $pVal) {
			$field_type = '';
			//$get_cust  = $wpdb->get_results( $wpdb->prepare( "select ID, post_content, post_name, post_parent from {$wpdb->prefix}posts where post_excerpt = %s", $pKey),ARRAY_A);
			$get_cust  = $wpdb->get_results( $wpdb->prepare( "select ID, post_content, post_name, post_parent from {$wpdb->prefix}posts where post_name = %s", $pKey),ARRAY_A);
			foreach ($get_cust as $get_val ) {
				$custvalue = $get_val['post_content'];
				$post_content = unserialize($custvalue);
				$field_layout =$post_content['layouts'];
				$field_type = $post_content['type'];
				$custkey='_'.$pKey; 
			}
			$listTaxonomy = get_taxonomies();
			if (in_array($importAs, $listTaxonomy)) {
				if($term_meta = 'yes'){
					update_term_meta($pID, $pKey, $pVal);
				}else{
					$option_name = $importAs . "_" . $pID . "_" . $pKey;
					$option_value = $pVal;
					if (is_array($option_value)) {
						$option_value = serialize($option_value);
					}
					update_option("$option_name", "$option_value");
				}
			}
			elseif($field_type == 'flexible_content'){
	
				$flexible_parent_id = $flexible_array[$pKey];
				if(!empty($flexible_parent_id)){
					$get_flexi_child_name = $wpdb->get_results("SELECT ID, post_name, post_content, post_excerpt FROM {$wpdb->prefix}posts WHERE post_parent = $flexible_parent_id", ARRAY_A);
					$flexi_child_array = [];
	
					$temp = 0;
					foreach($get_flexi_child_name as $flexi_values){	
	
						if(array_key_exists($flexi_values['post_name'] , $countof_flexi_child_names)){
							array_push($flexi_child_array, $countof_flexi_child_names[$flexi_values['post_name']]);
						}
	
						$flexi_post_content = unserialize($flexi_values['post_content']);
						if($flexi_post_content['type'] == 'flexible_content'){
	
							$flexible_parent_name = $wpdb->get_var("SELECT post_excerpt FROM {$wpdb->prefix}posts WHERE ID = $flexible_parent_id ");
							$flexible_layout_names = explode('->' , $data_array[$flexible_parent_name]);
							$flexible_parent_layout_name = $flexible_layout_names[0];
	
							$flexi_post_id = $flexi_values['ID'];
	
							$get_inner_flexi_child_name = $wpdb->get_results("SELECT post_name, post_excerpt, post_content FROM {$wpdb->prefix}posts WHERE post_parent = $flexi_post_id", ARRAY_A);	
							foreach($get_inner_flexi_child_name as $inner_flexi_values){
								if(array_key_exists($inner_flexi_values['post_name'] , $countof_flexi_child_names)){
									array_push($flexi_child_array, $countof_flexi_child_names[$inner_flexi_values['post_name']]);
								}
							}
	
							$flexible_child_name = $wpdb->get_var("SELECT post_excerpt FROM {$wpdb->prefix}posts WHERE ID = $flexi_post_id ");	
							if(strpos($flexible_layout_names[1], '|') !== false){
								$flexible_inner_layout_names = explode('|', $flexible_layout_names[1]);
								$flexi_inner_parent_child_names[$flexible_parent_name .'->'. $flexible_child_name] = $flexible_parent_layout_name .'->'.$flexible_inner_layout_names[$temp];	
								$temp++;
							}
							else{
								$flexible_child_layout_name = $flexible_layout_names[1];
								$flexi_inner_parent_child_names[$flexible_parent_name .'->'. $flexible_child_name] = $flexible_parent_layout_name .'->'.$flexible_child_layout_name;	
							}
						}
					}
				}	
			if(isset($field_layout)){
				$final_flexi_count = max($flexi_child_array);
				$flexible_group = explode(',',$data_array[$pKey]);
				
				$flexi_group_value=explode(',',$data_array[$pKey]);
				foreach ($repeater_field_rows as $repKey => $repVal) {
					foreach($flexible_group as $flexi_group_key => $flexi_group_value){	
					if(strpos($flexi_group_value, '->') !== false){
						$flexible_inner_group = explode('->', $flexi_group_value);
	
						$flexible_inner_group_values = $flexible_inner_group[0];
						if($final_flexi_count > 1){
							$flexible_inner_group_values = array_fill(0, $final_flexi_count, $flexible_inner_group_values);
						}
						$flex_value[$repKey] = $flexible_inner_group_values;
	
						$is_inner_flexible = true;
					}
					else{
						if($final_flexi_count > 1){
							$flexi_group_value = array_fill(0, $final_flexi_count, $flexi_group_value);
						}
						$flex_value[ $flexi_group_key] = $flexi_group_value;
					}
					if(isset($is_inner_flexible)){	
						foreach($flexi_inner_parent_child_names as $flexi_inner_names_keys => $flexi_inner_names_values){
							$flexi_inner_names_key = explode('->', $flexi_inner_names_keys);
							$flexi_inner_names_value = explode('->', $flexi_inner_names_values);
	
							if((strpos($pKey, $flexi_inner_names_key[0]) !== false) && (strpos($pKey, $flexi_inner_names_key[1]) !== false)){
								$flexible_inner_groups_values = $flexi_inner_names_value[1];
								if($final_flexi_count > 1){
									$flexible_inner_groups_values = array_fill(0, $final_flexi_count, $flexible_inner_groups_values);
								}
								$flex_value[$repKey] = $flexible_inner_groups_values;
							}
						}
					}		
					}		
				}
			}	
			else{
				$final_flexi_count = max($flexi_child_array);

				//$flexible_group = explode('|',$data_array[$pKey]);
				$flexi_group_value = $data_array[$pKey];
				foreach ($repeater_field_rows as $repKey => $repVal) {
					//foreach($flexible_group as $flexi_group_value){	
					if(strpos($flexi_group_value, '->') !== false){
						$flexible_inner_group = explode('->', $flexi_group_value);

						$flexible_inner_group_values = $flexible_inner_group[0];
						if($final_flexi_count > 1){
							$flexible_inner_group_values = array_fill(0, $final_flexi_count, $flexible_inner_group_values);
						}
						$flex_value[$repKey] = $flexible_inner_group_values;

						$is_inner_flexible = true;
					}
					else{
						if($final_flexi_count > 1){
							$flexi_group_value = array_fill(0, $final_flexi_count, $flexi_group_value);
						}
						$flex_value[$repKey] = $flexi_group_value;	
					}

					if($is_inner_flexible){	
						foreach($flexi_inner_parent_child_names as $flexi_inner_names_keys => $flexi_inner_names_values){
							$flexi_inner_names_key = explode('->', $flexi_inner_names_keys);
							$flexi_inner_names_value = explode('->', $flexi_inner_names_values);

							if((strpos($pKey, $flexi_inner_names_key[0]) !== false) && (strpos($pKey, $flexi_inner_names_key[1]) !== false)){
								$flexible_inner_groups_values = $flexi_inner_names_value[1];
								if($final_flexi_count > 1){
									$flexible_inner_groups_values = array_fill(0, $final_flexi_count, $flexible_inner_groups_values);
								}
								$flex_value[$repKey] = $flexible_inner_groups_values;
							}
						}
					}		
					//}		
				}
			}	
				if($importAs == 'Users'){
					update_user_meta($pID, $pKey, $flex_value);
				}elseif(in_array($importAs, $listTaxonomy)){
					if($term_meta = 'yes'){
						update_term_meta($pID, $pKey, $flex_value);
					}
				}else{
					if(is_array($flex_value[0])){
						$flex_values = $flex_value[0];
						update_post_meta($pID, $pKey, $flex_values);
					}
					else{
						update_post_meta($pID, $pKey, $flex_value);
					}
				}	
			}
			else{
				if($importAs == 'Users'){
					update_user_meta($pID, $pKey, $pVal);
				}else{
					update_post_meta($pID, $pKey, $pVal);
				}
			}	
		}	
		//}
	}
	//added new function for flexible content
	function acfpro_flexible_import_fuction($data_array, $importAs, $pID, $maps, $mode, $hash_key,$line_number,$gmode,$templatekey) {
		global $wpdb;
		$helpers_instance = ImportHelpers::getInstance();
		
		//get import type
		$listTaxonomy = get_taxonomies();
		if (in_array($importAs, $listTaxonomy)) {
			$get_import_type = 'term';
		}elseif ($importAs == 'Users') {
			$get_import_type = 'user';
		}elseif ($importAs == 'Comments') {
			$get_import_type = 'comment';
		} else {	
			$get_import_type = 'post';
		}

		foreach($data_array as $data_keys => $data_values){
			if (strpos($data_keys, 'field_') !== false){
			}
			else{
				unset($data_array[$data_keys]);
			}
		}
		$flexible_layout_rows =array();
		foreach($data_array as $repKey => $repVal) {
			$get_field_info  = $wpdb->get_results( $wpdb->prepare( "select ID, post_content, post_name, post_parent from {$wpdb->prefix}posts where post_name = %s", $repKey ) );
			$parentid =  $get_field_info[0]->post_parent ;
			$field_info = unserialize( $get_field_info[0]->post_content );
			$flexi_field_type = $field_info['type'];
			if($flexi_field_type == 'flexible_content'){
				$flexible_layout_rows[] = explode(',', $repVal);

				foreach($field_info['layouts'] as $field_layout_key => $field_layout_value){
					$flexible_layouts[$field_layout_key] = $field_layout_value['name'];
				}
			}
		}

		foreach($data_array as $repKey => $repVal) {
			$get_field_info  = $wpdb->get_results( $wpdb->prepare( "select ID, post_content, post_name, post_parent from {$wpdb->prefix}posts where post_name = %s", $repKey ) );
			$parentid =  $get_field_info[0]->post_parent ;
			$field_info = unserialize( $get_field_info[0]->post_content );
			
			$repeater_fields[$repKey] = $get_field_info[0]->post_name;
			if(isset($field_info['type']) && $field_info['type'] == 'flexible_content') {
				$repeater_flexible_content_import_method[ $get_field_info[0]->post_name ] = $field_info['layouts'][0]['name'];
				$flexible_array[$repKey] = $get_field_info[0]->ID;


				

			} elseif(isset($field_info['type']) && ($field_info['type'] == 'image' || $field_info['type'] == 'file')) {
				if($field_info['type'] == 'image') {
					$repeater_image_import_method[ $get_field_info[0]->post_name ] = $field_info['return_format'];
				} else {
					$repeater_file_import_method[ $get_field_info[0]->post_name ] = $field_info['return_format'];
				}
			} else {
				$repeater_sub_field_type[ $get_field_info[0]->post_name ] = $field_info['type'];
			}

			$flexi_field_type = $field_info['type'];
		
			if($flexi_field_type == 'flexible_content'){
				$flexible_layout_rowss = explode(',', $repVal);

				$get_flexi_excerpt = $wpdb->get_var("SELECT post_excerpt FROM {$wpdb->prefix}posts WHERE post_name = '$repKey'");
				$acf_rep_field_info[$get_flexi_excerpt] = $flexible_layout_rowss;
				$acf_rep_field_info['_'.$get_flexi_excerpt] = $repeater_fields[$repKey];
			}

			else{
				
				$repeater_field_rows = explode('->', $repVal);
				foreach($repeater_field_rows as $index => $value) {
					//if(strpos($value, '->') !== FALSE){
						$repeater_field_values = explode('|', $value);
						$checkCount = count($repeater_field_values);

						$parent_layout_id = $field_info['parent_layout'];
						$parent_layout_name = $flexible_layouts[$parent_layout_id];

						$parent_layout_arr = [];
						foreach($flexible_layout_rows as $flexible_layout_rowvalue){
							foreach($flexible_layout_rowvalue as $layout_key => $layout_rows){
								if($parent_layout_name == $layout_rows){
									$parent_layout_arr[] = $layout_key;
								}
							}
						}
						
						$combine_flexible_fields_layoutorder = $this->array_combine_function($parent_layout_arr, $repeater_field_values);
						// foreach($repeater_field_values as $key => $val) {
						foreach($combine_flexible_fields_layoutorder as $key => $val) {
							$rep_field_meta_key = $this->getMetaKeyOfFlexibleFields( $pID, $repKey, $key );
							$rep_type = isset($repeater_sub_field_type[$repeater_fields[$repKey]]) ? $repeater_sub_field_type[$repeater_fields[$repKey]] :'';

							if($rep_type == 'user' || $rep_type == 'page_link' || $rep_type == 'post_object' || $rep_type == 'select') {
								if($field_info['multiple'] == 0){
									$acf_rep_field_value = trim($val);
									$acf_rep_field_value = $wpdb->_real_escape($acf_rep_field_value);
									if(is_string($acf_rep_field_value)){
										$query = "SELECT id FROM {$wpdb->prefix}posts WHERE post_title ='{$acf_rep_field_value}' AND post_status='publish'";
										$name = $wpdb->get_results($query);
										if (!empty($name)) {
											$acf_rep_field_value=$name[0]->id;
										}
									}
									elseif (is_numeric($acf_rep_field_value)) {
										$acf_rep_field_value=$acf_rep_field_value;
									}
								}elseif(!$field_info['multiple'] == 0){
									$acf_rep_value_exp = explode(',',trim($val));
									$acf_rep_field_value = array();
									foreach($acf_rep_value_exp as $acf_reps_value){
										$acf_reps_value = $wpdb->_real_escape($acf_reps_value);
										$query = "SELECT id FROM {$wpdb->prefix}posts WHERE post_title ='{$acf_reps_value}' AND post_status!='trash'";
										$multiple_id = $wpdb->get_results($query);
										foreach($multiple_id as $mul_id){
											$acf_rep_field_value[]=trim($mul_id->id);
										}
									}
								}
								$acf_rep_field_info[$rep_field_meta_key] = $acf_rep_field_value;	
								$acf_rep_field_info['_'.$rep_field_meta_key]=$repeater_fields[$repKey];
							}
							if($rep_type == 'text' || $rep_type == 'textarea' || $rep_type == 'email' || $rep_type == 'number' || $rep_type == 'url' || $rep_type == 'password' || $rep_type == 'range' || $rep_type == 'radio' || $rep_type == 'true_false' || $rep_type == 'time_picker' || $rep_type == 'color_picker' || $rep_type == 'button_group' || $rep_type == 'oembed' || $rep_type == 'wysiwyg'){
			
								$acf_rep_field_info[$rep_field_meta_key] = trim($val);
								$acf_rep_field_info['_'.$rep_field_meta_key]=$repeater_fields[$repKey];
							}
							if($rep_type == 'date_time_picker'){
								$dt_rep_var = trim($val);
								$dateformat = "Y-m-d H:i:s";
								$fieldnm =  substr($rep_field_meta_key,strrpos($rep_field_meta_key,'_')+1);								
								if($dt_rep_var== 0 || $dt_rep_var== ''){
									$acf_rep_field_info[$rep_field_meta_key] =$dt_rep_var ;
									$acf_rep_field_info['_'.$rep_field_meta_key]=$repeater_fields[$repKey];
								}	
								else{
									$date_time_rep_of = $helpers_instance->validate_datefield($dt_rep_var,$fieldnm,$dateformat,$line_number);
									$acf_rep_field_info[$rep_field_meta_key] = $date_time_rep_of;
									$acf_rep_field_info['_'.$rep_field_meta_key]=$repeater_fields[$repKey];
								}					
							}
							if ($rep_type == 'google_map') {
			
								$location[] = trim($val);
								foreach($location as $loc){
									$locc=implode('|', $location);
								}
								list($add, $lat,$lng) = explode('|', $locc);
								$area = rtrim($add, ",");
								$map = array(
									'address' => $area,
									'lat'     =>  $lat,
									'lng'     => $lng
								);
								$acf_rep_field_info[$rep_field_meta_key] = $map;
								$acf_rep_field_info['_'.$rep_field_meta_key] = $repeater_fields[$repKey];
							}
							if($rep_type == 'date_picker'){
								$dateformat = "Ymd";
								$var_rep = trim($val);
								$fieldnm = substr($rep_field_meta_key,strrpos($rep_field_meta_key,'_')+1);
								$date_rep = str_replace('/', '-', "$var_rep");
								
									if($var_rep == 0 || $var_rep == ''){
										$acf_rep_field_info[$rep_field_meta_key] = $var_rep;
										$acf_rep_field_info['_'.$rep_field_meta_key]=$repeater_fields[$repKey];
									}
									else{
										$date_rep_of = $helpers_instance->validate_datefield($var_rep,$fieldnm,$dateformat,$line_number);
										$acf_rep_field_info[$rep_field_meta_key] = $date_rep_of;
										$acf_rep_field_info['_'.$rep_field_meta_key]=$repeater_fields[$repKey];
									}									
							}
							if($rep_type == 'checkbox'){
			
								$explode_val = explode(',',trim($val));	
								$explode_val_name = [];
								foreach($explode_val as $explode_acf_csv_value){
									$explode_val_name[] = trim($explode_acf_csv_value);
								}
			
								$acf_rep_field_info[$rep_field_meta_key] = $explode_val_name;
								$acf_rep_field_info['_'.$rep_field_meta_key]=$repeater_fields[$repKey];
							}
							if($rep_type == 'gallery'){
								$gallery_ids = array();
								if ( is_array( $gallery_ids ) ) {
									unset( $gallery_ids );
									$gallery_ids = array();
								}
								$exploded_gallery_items = explode( ',', $val );
								foreach ( $exploded_gallery_items as $gallery ) {
									$gallery = trim( $gallery );
									if ( preg_match_all( '/\b(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i', $gallery ) ) {
										//$get_gallery_id = ACFProImport::$media_instance->media_handling( $gallery, $pID);	
										$get_gallery_id = ACFProImport::$media_instance->image_meta_table_entry('', $pID, $rep_field_meta_key, $gallery, $hash_key, 'acf_flexible_gallery', $get_import_type,$templatekey,$gmode);
										if ( $get_gallery_id != '' ) {
											$gallery_ids[] = $get_gallery_id;
											//ACFProImport::$media_instance->acfgalleryMetaImports($gallery_ids,$maps,$plugin);
										}
									} else {
										$galleryLen         = strlen( $gallery );
										$checkgalleryid     = intval( $gallery );
										$verifiedGalleryLen = strlen( $checkgalleryid );
										if ( $galleryLen == $verifiedGalleryLen ) {
											$gallery_ids[] = $gallery;
											//ACFProImport::$media_instance->acfgalleryMetaImports($gallery_ids,$maps,$plugin);
										}
									}
								}
								$acf_rep_field_info[$rep_field_meta_key] = $gallery_ids;
								$acf_rep_field_info['_'.$rep_field_meta_key] = $repeater_fields[$repKey];
							}
							if($rep_type == 'link'){
								$explode_acf_val = explode(',',$val);
								$serial_acf_val = [];
								foreach($explode_acf_val as $explode_acf_value){
									$serial_acf_val[] = trim($explode_acf_value);
								}	
			
								$serial_acf_value['title'] = $serial_acf_val[0];
								$serial_acf_value['url'] = $serial_acf_val[1];
								if($serial_acf_val[2] == 1){
									$serial_acf_value['target'] = '_blank';
								}else{
									$serial_acf_value['target'] = '';
								}
			
								$acf_rep_field_info[$rep_field_meta_key] = $serial_acf_value;
								$acf_rep_field_info['_'.$rep_field_meta_key]=$repeater_fields[$repKey];
							}
			
							//Push meta information into WordPress
							elseif($rep_type == 'relationship' || $rep_type == 'taxonomy') {
								$exploded_relations = $relations = array();
								$exploded_relations = explode(',', $val);
								foreach($exploded_relations as $relVal) {
									$relationTerm = trim( $relVal );
									//$relTerm[] = $relationTerm;
									if ( $rep_type == 'taxonomy' ) {
										$taxonomy_name =  $field_info['taxonomy'];
										$check_is_valid_term = $helpers_instance->get_requested_term_details( $pID, array($relationTerm), $taxonomy_name );
										$relations[]         = $check_is_valid_term;
									} else {
										if(is_numeric($relVal)){
											$relations[] = $relationTerm;
										}
										else{
											$relationTerm=$wpdb->_real_escape($relationTerm);
											$query = "SELECT id FROM {$wpdb->prefix}posts WHERE post_title ='{$relationTerm}' AND post_status!='trash'";
											$multiple_id = $wpdb->get_results($query);
											foreach($multiple_id as $mul_id){
												$relations[]=trim($mul_id->id);
											}
										}
									}
								}
								$acf_rep_field_info[$rep_field_meta_key] = $relations;
								$acf_rep_field_info['_'.$rep_field_meta_key] = $repeater_fields[$repKey];
							} 
			
							$repeater_image_import_method[$repeater_fields[$repKey]] = isset($repeater_image_import_method[$repeater_fields[$repKey]]) ? $repeater_image_import_method[$repeater_fields[$repKey]] :'';
							if($repeater_image_import_method[$repeater_fields[$repKey]] == 'url' || $repeater_image_import_method[$repeater_fields[$repKey]] == 'array' ) {
								$image_link = trim($val);
								if(preg_match_all('/\b(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i', $image_link)){
									//$acf_rep_field_info[$rep_field_meta_key] = ACFProImport::$media_instance->media_handling($image_link, $pID);
									$acf_rep_field_info[$rep_field_meta_key] = ACFProImport::$media_instance->image_meta_table_entry('', $pID, $rep_field_meta_key, $image_link, $hash_key, 'acf_flexible', $get_import_type,$templatekey,$gmode);
									$img_id[]=$img_id[]=$acf_rep_field_info[$rep_field_meta_key];
									//ACFProImport::$media_instance->acfimageMetaImports($img_id,$maps,$plugin);
									$acf_rep_field_info['_'.$rep_field_meta_key] = $repeater_fields[$repKey];
								} else {
									$acf_rep_field_info[$rep_field_meta_key] = $image_link;
									$img_id[]=$img_id[]=$acf_rep_field_info[$rep_field_meta_key];
									//ACFProImport::$media_instance->acfimageMetaImports($img_id,$maps,$plugin);
									$acf_rep_field_info['_'.$rep_field_meta_key] = $repeater_fields[$repKey];
								}
							}
		
							$repeater_file_import_method[$repeater_fields[$repKey]] = isset($repeater_file_import_method[$repeater_fields[$repKey]]) ? $repeater_file_import_method[$repeater_fields[$repKey]] :'';
							if($repeater_file_import_method[$repeater_fields[$repKey]] == 'url' || $repeater_file_import_method[$repeater_fields[$repKey]] == 'array' ) {
								$image_link = trim($val);
								if(preg_match_all('/\b(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i', $image_link)){
									$ext = pathinfo($image_link, PATHINFO_EXTENSION);
									if($ext== 'pdf' || $ext == 'mp3' || $ext == $ext) {
										$fil_id = $wpdb->get_col($wpdb->prepare("select ID from {$wpdb->prefix}posts where guid = %s AND post_type='attachment'",$image_link));
										if(!empty($fil_id)) {
											$acf_rep_field_info[$rep_field_meta_key]=$fil_id[0];
										}else {
											//$acf_rep_field_info[$rep_field_meta_key] = ACFProImport::$media_instance->media_handling( $image_link, $pID );
											$acf_rep_field_info[$rep_field_meta_key] = ACFProImport::$media_instance->image_meta_table_entry('', $pID, $rep_field_meta_key, $image_link, $hash_key, 'acf_flexible', $get_import_type,$templatekey,$gmode);
										}
										$acf_rep_field_info['_'.$rep_field_meta_key] = $repeater_fields[$repKey];
									} else {
										//$acf_rep_field_info[$rep_field_meta_key] = ACFProImport::$media_instance->media_handling( $image_link, $pID );
										$acf_rep_field_info[$rep_field_meta_key] = ACFProImport::$media_instance->image_meta_table_entry('', $pID, $rep_field_meta_key, $image_link, $hash_key, 'acf_flexible', $get_import_type,$templatekey,$gmode);
										$acf_rep_field_info['_'.$rep_field_meta_key] = $repeater_fields[$repKey];
									}
								} else {
									$acf_rep_field_info[$rep_field_meta_key] = $image_link;
									$acf_rep_field_info['_'.$rep_field_meta_key] = $repeater_fields[$repKey];
								}
							}  
							// if ($rep_type == 'message') {
							// 	$field_info['message'] = $val;
							// }
						}
					//}
				}
			}
			
			
			// $repeater_field_rows = explode(',', $repVal);
		
			// foreach($repeater_field_rows as $index => $value) {
			// 	if(strpos($value, '->') !== FALSE){
			// 		$repeater_field_values = explode('->', $value);
			// 		$checkCount = count($repeater_field_values);

			// 		foreach($repeater_field_values as $key => $val) {
			// 			$rep_field_meta_key = $this->getMetaKeyOfFlexibleFields( $pID, $repKey, $index, $key );
			// 			$rep_type = isset($repeater_sub_field_type[$repeater_fields[$repKey]]) ? $repeater_sub_field_type[$repeater_fields[$repKey]] :'';

			// 			if($rep_type == 'user' || $rep_type == 'page_link' || $rep_type == 'post_object' || $rep_type == 'select') {
			// 				if($field_info['multiple'] == 0){
			// 					$acf_rep_field_value = trim($val);
			// 					$acf_rep_field_value = $wpdb->_real_escape($acf_rep_field_value);
			// 					if(is_string($acf_rep_field_value)){
			// 						$query = "SELECT id FROM {$wpdb->prefix}posts WHERE post_title ='{$acf_rep_field_value}' AND post_status='publish'";
			// 						$name = $wpdb->get_results($query);
			// 						if (!empty($name)) {
			// 							$acf_rep_field_value=$name[0]->id;
			// 						}
			// 					}
			// 					elseif (is_numeric($acf_rep_field_value)) {
			// 						$acf_rep_field_value=$acf_rep_field_value;
			// 					}
			// 				}elseif(!$field_info['multiple'] == 0){
			// 					$acf_rep_value_exp = explode(',',trim($val));
			// 					$acf_rep_field_value = array();
			// 					foreach($acf_rep_value_exp as $acf_reps_value){
			// 						$acf_reps_value = $wpdb->_real_escape($acf_reps_value);
			// 						$query = "SELECT id FROM {$wpdb->prefix}posts WHERE post_title ='{$acf_reps_value}' AND post_status!='trash'";
			// 						$multiple_id = $wpdb->get_results($query);
			// 						foreach($multiple_id as $mul_id){
			// 							$acf_rep_field_value[]=trim($mul_id->id);
			// 						}
			// 					}
			// 				}
			// 				$acf_rep_field_info[$rep_field_meta_key] = $acf_rep_field_value;	
			// 				$acf_rep_field_info['_'.$rep_field_meta_key]=$repeater_fields[$repKey];
			// 			}
			// 			if($rep_type == 'text' || $rep_type == 'textarea' || $rep_type == 'email' || $rep_type == 'number' || $rep_type == 'url' || $rep_type == 'password' || $rep_type == 'range' || $rep_type == 'radio' || $rep_type == 'true_false' || $rep_type == 'time_picker' || $rep_type == 'color_picker' || $rep_type == 'button_group' || $rep_type == 'oembed' || $rep_type == 'wysiwyg'){
		
			// 				$acf_rep_field_info[$rep_field_meta_key] = trim($val);
			// 				$acf_rep_field_info['_'.$rep_field_meta_key]=$repeater_fields[$repKey];
			// 			}
			// 			if($rep_type == 'date_time_picker'){
			// 				$dt_rep_var = trim($val);
			// 				$date_time_rep_of = date("Y-m-d H:i:s", strtotime($dt_rep_var) );
			// 				if($dt_rep_var== 0 || $dt_rep_var== ''){
			// 					$acf_rep_field_info[$rep_field_meta_key] =$dt_rep_var ;
			// 					$acf_rep_field_info['_'.$rep_field_meta_key]=$repeater_fields[$repKey];
			// 				}	
			// 				else{
			// 					$acf_rep_field_info[$rep_field_meta_key] = $date_time_rep_of;
			// 					$acf_rep_field_info['_'.$rep_field_meta_key]=$repeater_fields[$repKey];
			// 				}
			// 			}
			// 			if ($rep_type == 'google_map') {
		
			// 				$location[] = trim($val);
			// 				foreach($location as $loc){
			// 					$locc=implode('|', $location);
			// 				}
			// 				list($add, $lat,$lng) = explode('|', $locc);
			// 				$area = rtrim($add, ",");
			// 				$map = array(
			// 					'address' => $area,
			// 					'lat'     =>  $lat,
			// 					'lng'     => $lng
			// 				);
			// 				$acf_rep_field_info[$rep_field_meta_key] = $map;
			// 				$acf_rep_field_info['_'.$rep_field_meta_key] = $repeater_fields[$repKey];
			// 			}
			// 			if($rep_type == 'date_picker'){
		
			// 				$var_rep = trim($val);
			// 				$date_rep = str_replace('/', '-', "$var_rep");
			// 				$date_rep_of = date('Ymd', strtotime($date_rep));
			// 					if($var_rep == 0 || $var_rep == ''){
			// 						$acf_rep_field_info[$rep_field_meta_key] = $var_rep;
			// 						$acf_rep_field_info['_'.$rep_field_meta_key]=$repeater_fields[$repKey];
			// 					}
			// 					else{
			// 						$acf_rep_field_info[$rep_field_meta_key] = $date_rep_of;
			// 						  $acf_rep_field_info['_'.$rep_field_meta_key]=$repeater_fields[$repKey];
	
			// 					}	
						
			// 			}
			// 			if($rep_type == 'checkbox'){
		
			// 				$explode_val = explode(',',trim($val));	
			// 				$explode_val_name = [];
			// 				foreach($explode_val as $explode_acf_csv_value){
			// 					$explode_val_name[] = trim($explode_acf_csv_value);
			// 				}
		
			// 				$acf_rep_field_info[$rep_field_meta_key] = $explode_val_name;
			// 				$acf_rep_field_info['_'.$rep_field_meta_key]=$repeater_fields[$repKey];
			// 			}
			// 			if($rep_type == 'gallery'){
			// 				$gallery_ids = array();
			// 				if ( is_array( $gallery_ids ) ) {
			// 					unset( $gallery_ids );
			// 					$gallery_ids = array();
			// 				}
			// 				$exploded_gallery_items = explode( ',', $val );	
			// 				foreach ( $exploded_gallery_items as $gallery ) {
			// 					$gallery = trim( $gallery );
			// 					if ( preg_match_all( '/\b(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i', $gallery ) ) {
			// 						// $get_gallery_id = ACFProImport::$media_instance->media_handling( $gallery, $pID);	
			// 						// if ( $get_gallery_id != '' ) {
			// 						// 	$gallery_ids[] = $get_gallery_id;
			// 						// 	ACFProImport::$media_instance->acfgalleryMetaImports($gallery_ids,$maps,$plugin);
			// 						// }
	
			// 						//image_change
			// 						$get_gallery_id = ACFProImport::$media_instance->image_meta_table_entry($maps, $pID, $rep_field_meta_key, $gallery, $hash_key, 'acf_flexible_gallery');	
			// 						if($get_gallery_id != '') {
			// 							$gallery_ids[] = $get_gallery_id;
			// 						}
	
			// 					} else {
			// 						$galleryLen         = strlen( $gallery );
			// 						$checkgalleryid     = intval( $gallery );
			// 						$verifiedGalleryLen = strlen( $checkgalleryid );
			// 						if ( $galleryLen == $verifiedGalleryLen ) {
			// 							$gallery_ids[] = $gallery;
			// 							ACFProImport::$media_instance->acfgalleryMetaImports($gallery_ids,$maps,$plugin);
			// 						}
			// 					}
			// 				}
	
			// 				//update_image_meta
			// 				$option_entry = 'smack_schedule_image_exists_acf_flexible_gallery_'.$pID;
			// 				$this->acf_imagemeta_update($gallery_ids, $maps, $plugin, $option_entry, 'gallery');
	
			// 				$acf_rep_field_info[$rep_field_meta_key] = $gallery_ids;
			// 				$acf_rep_field_info['_'.$rep_field_meta_key] = $repeater_fields[$repKey];
			// 			}
			// 			if($rep_type == 'link'){
			// 				$explode_acf_val = explode(',',$val);
			// 				$serial_acf_val = [];
			// 				foreach($explode_acf_val as $explode_acf_value){
			// 					$serial_acf_val[] = trim($explode_acf_value);
			// 				}	
		
			// 				$serial_acf_value['url'] = $serial_acf_val[0];
			// 				$serial_acf_value['title'] = $serial_acf_val[1];
			// 				if($serial_acf_val[2] == 1){
			// 					$serial_acf_value['target'] = '_blank';
			// 				}else{
			// 					$serial_acf_value['target'] = '';
			// 				}
		
			// 				$acf_rep_field_info[$rep_field_meta_key] = $serial_acf_value;
			// 				$acf_rep_field_info['_'.$rep_field_meta_key]=$repeater_fields[$repKey];
			// 			}
		
			// 			//Push meta information into WordPress
		
			// 			elseif($rep_type == 'relationship' || $rep_type == 'taxonomy') {
			// 				$exploded_relations = $relations = array();
			// 				$exploded_relations = explode(',', $val);
			// 				foreach($exploded_relations as $relVal) {
			// 					$relationTerm = trim( $relVal );
			// 					$relTerm[] = $relationTerm;
			// 					if ( $rep_type == 'taxonomy' ) {
			// 						$taxonomy_name =  $field_info['taxonomy'];
			// 						$check_is_valid_term = $helpers_instance->get_requested_term_details( $pID, $relTerm, $taxonomy_name );
			// 						$relations[]         = $check_is_valid_term;
			// 					} else {
			// 						if(is_numeric($relVal)){
			// 							$relations[] = $relationTerm;
			// 						}
			// 						else{
			// 							$relationTerm=$wpdb->_real_escape($relationTerm);
			// 							$query = "SELECT id FROM {$wpdb->prefix}posts WHERE post_title ='{$relationTerm}' AND post_status!='trash'";
			// 							$multiple_id = $wpdb->get_results($query);
			// 							foreach($multiple_id as $mul_id){
			// 								$relations[]=trim($mul_id->id);
			// 							}
			// 						}
									
			// 					}
			// 				}
			// 				$acf_rep_field_info[$rep_field_meta_key] = $relations;
			// 				$acf_rep_field_info['_'.$rep_field_meta_key] = $repeater_fields[$repKey];
			// 			} 
		
			// 			$repeater_image_import_method[$repeater_fields[$repKey]] = isset($repeater_image_import_method[$repeater_fields[$repKey]]) ? $repeater_image_import_method[$repeater_fields[$repKey]] :'';
			// 			if($repeater_image_import_method[$repeater_fields[$repKey]] == 'url' || $repeater_image_import_method[$repeater_fields[$repKey]] == 'array' ) {
			// 				$image_link = trim($val);
			// 				if(preg_match_all('/\b(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i', $image_link)){
			// 					// $acf_rep_field_info[$rep_field_meta_key] = ACFProImport::$media_instance->media_handling($image_link, $pID);
			// 					// $img_id[]=$acf_rep_field_info[$rep_field_meta_key];
			// 					// ACFProImport::$media_instance->acfimageMetaImports($img_id,$maps,$plugin);
								
			// 					$acf_rep_field_info[$rep_field_meta_key] = ACFProImport::$media_instance->image_meta_table_entry($maps, $pID, $rep_field_meta_key, $image_link, $hash_key, 'acf_flexible');
			// 					$img_id[]=$acf_rep_field_info[$rep_field_meta_key];
								
								
			// 					$acf_rep_field_info['_'.$rep_field_meta_key] = $repeater_fields[$repKey];
			// 				} else {
			// 					$acf_rep_field_info[$rep_field_meta_key] = $image_link;
			// 					$img_id[]=$acf_rep_field_info[$rep_field_meta_key];
			// 					ACFProImport::$media_instance->acfimageMetaImports($img_id,$maps,$plugin);
			// 					$acf_rep_field_info['_'.$rep_field_meta_key] = $repeater_fields[$repKey];
			// 				}
			// 			}
	
			// 			$repeater_file_import_method[$repeater_fields[$repKey]] = isset($repeater_file_import_method[$repeater_fields[$repKey]]) ? $repeater_file_import_method[$repeater_fields[$repKey]] :'';
			// 			if($repeater_file_import_method[$repeater_fields[$repKey]] == 'url' || $repeater_file_import_method[$repeater_fields[$repKey]] == 'array' ) {
			// 				$image_link = trim($val);
			// 				if(preg_match_all('/\b(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i', $image_link)){
			// 					$ext = pathinfo($image_link, PATHINFO_EXTENSION);
			// 					if($ext== 'pdf' || $ext == 'mp3' || $ext == $ext) {
			// 						$fil_id = $wpdb->get_col($wpdb->prepare("select ID from {$wpdb->prefix}posts where guid = %s AND post_type='attachment'",$image_link));
			// 						if(!empty($fil_id)) {
			// 							$acf_rep_field_info[$rep_field_meta_key]=$fil_id[0];
			// 						}else {
			// 							$acf_rep_field_info[$rep_field_meta_key] = ACFProImport::$media_instance->media_handling( $image_link, $pID );
			// 						}
			// 						$acf_rep_field_info['_'.$rep_field_meta_key] = $repeater_fields[$repKey];
			// 					} else {
			// 						$acf_rep_field_info[$rep_field_meta_key] = ACFProImport::$media_instance->media_handling( $image_link, $pID );
			// 						$acf_rep_field_info['_'.$rep_field_meta_key] = $repeater_fields[$repKey];
			// 					}
			// 				} else {
			// 					$acf_rep_field_info[$rep_field_meta_key] = $image_link;
			// 					$acf_rep_field_info['_'.$rep_field_meta_key] = $repeater_fields[$repKey];
			// 				}
			// 			}  
			// 			// if ($rep_type == 'message') {
			// 			// 	$field_info['message'] = $val;
			// 			// }
			// 		}
			// 	}
			// 	else{
					
			// 		$get_flexi_excerpt = $wpdb->get_var("SELECT post_excerpt FROM {$wpdb->prefix}posts WHERE post_name = '$repKey'");
			// 		$get_flexi_count = count($repeater_field_rows);
			// 		$acf_rep_field_info[$get_flexi_excerpt] = array_fill(0, $get_flexi_count, $value);
			// 		$acf_rep_field_info['_'.$get_flexi_excerpt] = $repeater_fields[$repKey];
			// 	}
			// }

			if(!empty($acf_rep_field_info)) {
				foreach($acf_rep_field_info as $fName => $fVal) {
	
					$listTaxonomy = get_taxonomies();
					if (in_array($importAs, $listTaxonomy)) {
						if($term_meta = 'yes'){
							update_term_meta($pID, $fName, $fVal);
						}else{
							$option_name = $importAs . "_" . $pID . "_" . $fName;
							$option_value = $fVal;
							if (is_array($option_value)) {
								$option_value = serialize($option_value);
							}
							update_option("$option_name", "$option_value");
						}
					}
					else{
						if($importAs == 'Users'){
							update_user_meta($pID, $fName, $fVal);
						}else{
							update_post_meta($pID, $fName, $fVal);
						}
					}
				}
			}
		}
	}
	//added new function get flexible fields
	function getMetaKeyOfFlexibleFields($pID, $field_name, $fKey) {
		global $wpdb;
		$get_field_details = $wpdb->get_results( $wpdb->prepare( "select ID, post_content, post_name, post_parent, post_excerpt from {$wpdb->prefix}posts where post_name = %s", $field_name ) );
		$get_flexible_parent_field = $wpdb->get_results( $wpdb->prepare( "select post_content, post_name, post_excerpt, post_parent from {$wpdb->prefix}posts where ID = %d", $get_field_details[0]->post_parent ) );
		
		$get_flexible_parent_name = $get_flexible_parent_field[0]->post_excerpt;
		$meta_key = $get_flexible_parent_name .'_'.  $fKey .'_'. $get_field_details[0]->post_excerpt;
	
		return $meta_key;
	}

	function array_combine_function($arr1, $arr2) {
		$count = min(count($arr1), count($arr2));
		return array_combine(array_slice($arr1, 0, $count), array_slice($arr2, 0, $count));
	}
	
	function getMetaKeyOfFlexibleField($pID, $field_name, $key , $fKey , $parents = array(), $meta_key = '') {
		global $wpdb;
		//changed
		// $get_field_details  = $wpdb->get_results( $wpdb->prepare( "select ID, post_content, post_name, post_parent, post_excerpt from {$wpdb->prefix}posts where post_excerpt = %s", $field_name ) );
		$get_field_details  = $wpdb->get_results( $wpdb->prepare( "select ID, post_content, post_name, post_parent, post_excerpt from {$wpdb->prefix}posts where post_name = %s", $field_name ) );
		
		$field_info1           = unserialize( $get_field_details[0]->post_content );
		$i =0;
		$layout = isset($field_info1['parent_layout']) ? $field_info1['parent_layout'] :'';
	
		$get_repeater_parent_field = $wpdb->get_results( $wpdb->prepare( "select post_content, post_name, post_excerpt, post_parent from {$wpdb->prefix}posts where ID = %d", $get_field_details[0]->post_parent ) );
	
		$field_info           = unserialize( $get_repeater_parent_field[0]->post_content );
		$layouts= isset($field_info['layouts']) ? $field_info['layouts'] :'';
	
		if(!empty($layouts)){
			$keys = array_keys($layouts);//get the main keys
			foreach($keys as $layout_key =>$val){
				if($layout == $val){
					$fKey =$layout_key;
					$key= $layout_key;
					if(empty($parents) && $field_name == $get_field_details[0]->post_name) {
						// $parents[] = $fKey . '_' . $field_name . '_';
						// $meta_key .= $fKey . '_' . $field_name . '_';

						//changed
						$get_field_excerpt = $wpdb->get_var( $wpdb->prepare( "SELECT post_excerpt from {$wpdb->prefix}posts where post_name = %s", $field_name ) );
						$parents[] = $fKey . '_' . $get_field_excerpt . '_';
						$meta_key .= $fKey . '_' . $get_field_excerpt . '_';
					}
				}
			}
		}
		if((isset($field_info['type']) && $get_repeater_parent_field[0]->post_parent != 0) && ($field_info['type'] == 'repeater' || $field_info['type'] == 'group' || $field_info['type'] == 'flexible_content' )) {

			$parents[] = $key . '_' . $get_repeater_parent_field[0]->post_excerpt . '_';	
			$meta_key .= $key . '_' . $get_repeater_parent_field[0]->post_excerpt . '_' . $meta_key;
			$meta_key = $this->getMetaKeyOfRepeaterField($pID, $get_repeater_parent_field[0]->post_name, $key, $fKey, $parents, $meta_key);
			//$meta_key = $this->getMetaKeyOfFlexibleField($pID, $get_repeater_parent_field[0]->post_name, $key, $fKey, $parents, $meta_key);
		
			update_post_meta($pID, $get_repeater_parent_field[0]->post_excerpt , 1);
			update_post_meta($pID, '_'.$get_repeater_parent_field[0]->post_excerpt , $get_repeater_parent_field[0]->post_name );
		} else {
			if(!empty($parents)) {
				$meta_key = '';
				for($i = count($parents); $i >= 0 ; $i--) {
					if(isset($parents[$i])){
						$meta_key .= $parents[$i];
					}	
				}
			}
			$meta_key = substr($meta_key, 2);
			$meta_key = substr($meta_key, 0, -1);
			return $meta_key;
		}
		return $meta_key;
	}

}
