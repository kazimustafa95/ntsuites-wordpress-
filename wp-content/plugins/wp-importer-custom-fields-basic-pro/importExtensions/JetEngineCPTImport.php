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

class JetEngineCPTImport {

	private static $instance = null;
	
    public static function getInstance() {		
		if (JetEngineCPTImport::$instance == null) {
			JetEngineCPTImport::$instance = new JetEngineCPTImport;
		}
		return JetEngineCPTImport::$instance;
	}

	function set_jet_engine_cpt_values($header_array ,$value_array , $map, $post_id , $type , $mode, $hash_key,$line_number,$gmode,$templatekey){
		$post_values = [];
		$helpers_instance = ImportHelpers::getInstance();
		$post_values = $helpers_instance->get_header_values($map , $header_array , $value_array);
		
		$extension_object = new ExtensionHandler;
		$import_as = $extension_object->import_post_types($type );
	
		$this->jet_engine_cpt_import_function($post_values,$import_as, $post_id, $mode, $hash_key,$line_number,$header_array,$value_array,$gmode,$templatekey);
	}

	function set_jet_engine_cpt_rf_values($header_array ,$value_array , $map, $post_id , $type , $mode, $hash_key,$line_number,$gmode,$templatekey){
		$post_values = [];
		$helpers_instance = ImportHelpers::getInstance();
		$post_values = $helpers_instance->get_header_values($map , $header_array , $value_array);
		
		$extension_object = new ExtensionHandler;
		$import_as = $extension_object->import_post_types($type );
	
		$this->jet_engine_cpt_rf_import_function($post_values,$import_as, $post_id, $mode, $hash_key,$line_number,$header_array,$value_array,$gmode,$templatekey);
	}
	
	public function jet_engine_cpt_import_function($data_array, $type, $pID ,$mode, $hash_key,$line_number,$header_array,$value_array,$gmode,$templatekey) 
	{
		global $wpdb;
		$media_instance = MediaHandling::getInstance();
		$helpers_instance = ImportHelpers::getInstance();
		$jet_data = $this->JetEngineCPTFields($type);
		$get_gallery_id = $gallery_ids = '';

		if($type == 'WooCommerce Product'){
			$type = 'product';
		}

		$listTaxonomy = get_taxonomies();
		if (in_array($type, $listTaxonomy)) {
			$get_import_type = 'term';
		}elseif ($type == 'Users' || $type == 'user') {
			$get_import_type = 'user';
		}elseif ($type == 'Comments') {
			$get_import_type = 'comment';
		} else {	
			$get_import_type = 'post';
		}

		foreach ($data_array as $dkey => $dvalue) {
			if(array_key_exists($dkey,$jet_data['JECPT'])){
				if($jet_data['JECPT'][$dkey]['type'] == 'gallery' || $jet_data['JECPT'][$dkey]['type'] == 'media'){
					$gallery_ids = $media_ids = '';
					$exploded_gallery_items = explode( ',', $dvalue );
					$galleryvalue=array();
					foreach ( $exploded_gallery_items as $gallery ) {
						$gallery = trim( $gallery );
						if ( preg_match_all( '/\b(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i', $gallery ) ) {
							$field_name = $jet_data['JECPT'][$dkey]['name'];
							$field_type = $jet_data['JECPT'][$dkey]['type'];
							$plugin = 'jetenginecpt_'.$field_type;
							$get_gallery_id = $media_instance->image_meta_table_entry($data_array, $pID, $field_name,$gallery, $hash_key, $plugin, $get_import_type, $templatekey, $gmode, $header_array, $value_array);							
							$media_id = $get_gallery_id;
							if ( $get_gallery_id != '' ) {
								if($jet_data['JECPT'][$dkey]['type'] == 'media'){
									$media_ids .= $media_id. ',';
								}
								elseif($jet_data['JECPT'][$dkey]['value_format'] == 'url'){
									$get_gallery_fields = $wpdb->get_results("select meta_value from {$wpdb->prefix}postmeta where post_id=$get_gallery_id and meta_key ='_wp_attached_file'");
									$dir = wp_upload_dir();
									$gallery_ids .= $dir ['baseurl'] . '/' .$get_gallery_fields[0]->meta_value. ',';
								}
								elseif($jet_data['JECPT'][$dkey]['value_format'] == 'both'){
									$gallery_id1 ['id']= $get_gallery_id;
									
									$get_gallery_fields = $wpdb->get_results("select meta_value from {$wpdb->prefix}postmeta where post_id=$get_gallery_id and meta_key ='_wp_attached_file'");
									$dir = wp_upload_dir();
									$gallery_id2['url']= $dir ['baseurl'] . '/' .$get_gallery_fields[0]->meta_value;
									
									$galleryvalue[] = array_merge($gallery_id1,$gallery_id2);
									
									$gallery_ids=$galleryvalue;
								}
								else{
									$gallery_ids .= $get_gallery_id.',';
								}
							}
						} else {
							$galleryLen         = strlen( $gallery );
							$checkgalleryid     = intval( $gallery );
							$verifiedGalleryLen = strlen( $checkgalleryid );
							if ( $galleryLen == $verifiedGalleryLen ) {
								if($jet_data['JECPT'][$dkey]['type'] == 'media'){
									$media_ids .= $gallery. ',';
								}
								else{
									$gallery_ids .= $gallery. ',';
								}

							}
						}
					}
					if(is_array($gallery_ids)){
						$gallery_id  = $gallery_ids;
					}
					if (!is_array($gallery_ids)) {
						$gallery_id = rtrim($gallery_ids,',');
					}
					if(isset($media_ids)){
						$media_id1= rtrim($media_ids,',');
					}
					if($jet_data['JECPT'][$dkey]['value_format'] == 'url'){
						$get_media_fields = $wpdb->get_results("select meta_value from {$wpdb->prefix}postmeta where post_id=$media_id and meta_key ='_wp_attached_file'");
						$dir = wp_upload_dir();			
						//$media_id = $dir ['baseurl'] . '/' .$get_media_fields[0]->meta_value;
						
						if(!empty($get_media_fields[0]->meta_value)){
                            $media_id = $dir ['baseurl'] . '/' .$get_media_fields[0]->meta_value;
                        }        
                        else{
                            $media_id='';
                        }
					}
					elseif($jet_data['JECPT'][$dkey]['value_format'] == 'both'){
						$media_ids1['id']=$media_id;
						$get_media_fields = $wpdb->get_results("select meta_value from {$wpdb->prefix}postmeta where post_id=$media_id and meta_key ='_wp_attached_file'");
						$dir = wp_upload_dir();			
						if(!empty($get_media_fields[0]->meta_value)){
							$media_ids2['url'] = $dir ['baseurl'] . '/' .$get_media_fields[0]->meta_value;
						}        
						else{
							$media_ids2['url']='';
						}
						$mediavalue= array_merge($media_ids1,$media_ids2);
						$media_id=array($mediavalue);

					}
					
					else{
						$media_id=$media_id;
					}
					if($jet_data['JECPT'][$dkey]['type'] == 'media'){
						$darray[$jet_data['JECPT'][$dkey]['name']] = $media_id;
					}
					else{
						$darray[$jet_data['JECPT'][$dkey]['name']] = $gallery_id;
					}
				}
				elseif($jet_data['JECPT'][$dkey]['type'] == 'datetime-local'){
					$dateformat = 'Y-m-d\TH:m';					
					if(!empty($dvalue)){
						$dt_var = trim($dvalue);
						$datetime = str_replace('/', '-', "$dt_var");

						if($jet_data['JECPT'][$dkey]['is_timestamp']){
							if(is_numeric($datetime)){
								$date_time_of = $datetime;
							}
							else{
								$date_time_of = strtotime($datetime);
							}
						}else{
							$date_time_of = $helpers_instance->validate_datefield($dt_var,$dkey,$dateformat,$line_number);
						}

						$darray[$jet_data['JECPT'][$dkey]['name']] = $date_time_of;
					}
					else{
						$darray[$jet_data['JECPT'][$dkey]['name']] = '';
					}
				}
				elseif($jet_data['JECPT'][$dkey]['type'] == 'date'){
					$dateformat = 'Y-m-d';
					if(!empty($dvalue)){
						$var = trim($dvalue);
						$date = str_replace('/', '-', "$var");
						if($jet_data['JECPT'][$dkey]['is_timestamp']){
							if(is_numeric($date)){
								$date_of = $date;
							}
							else{
								$date_of = strtotime($date);
							}
						}else{
							$date_of = $helpers_instance->validate_datefield($var,$dkey,$dateformat,$line_number);
						}
						$darray[$jet_data['JECPT'][$dkey]['name']] = $date_of;
					}
					else{
						$darray[$jet_data['JECPT'][$dkey]['name']] = '';
					}
				}
				elseif($jet_data['JECPT'][$dkey]['type'] == 'time'){
					$var = trim($dvalue);
					$time = date('H:i', strtotime($var));
					$darray[$jet_data['JECPT'][$dkey]['name']] = $time;
				}
				elseif($jet_data['JECPT'][$dkey]['type'] == 'checkbox'){
					$dvalue = trim($dvalue);
					if($jet_data['JECPT'][$dkey]['is_array'] == 1){
						$dvalexp = explode(',' , $dvalue);
						$darray[$jet_data['JECPT'][$dkey]['name']] = $dvalexp;

					}
					else{
						$options = $jet_data['JECPT'][$dkey]['options'];
						$arr = [];
						$opt = [];
						$dvalexp = explode(',' , $dvalue);
						foreach($options as $option_key => $option_val){
							//$opt[$option_key]	= $option_val['key'];
							$arr[$option_val['key']] = 'false';
						}
						foreach($dvalexp as $dvalkey => $dvalueval){
							$dvalueval = trim($dvalueval);
							$keys = array_keys($arr);
							foreach($keys as $keys1){
								if($dvalueval == $keys1){
									$arr[$keys1] = 'true';
								}
							}

							//added new checkbox values
							if(!in_array($dvalueval, $keys)){
								$get_meta_fields = $wpdb->get_results("SELECT id, meta_fields FROM {$wpdb->prefix}jet_post_types WHERE slug = '$type' AND status IN ('publish','built-in')");
								if(isset($get_meta_fields[0])){
									$unserialized_meta = maybe_unserialize($get_meta_fields[0]->meta_fields);
									$jet_engine_id = $get_meta_fields[0]->id;
								
									if(!empty($unserialized_meta)){
										foreach($unserialized_meta as $jet_keys => $jet_values){
											$count_jetvalues = 0;
											if($jet_values['type'] == 'checkbox' && $jet_values['name'] == $dkey){
												
												$count_jetvalues = count($jet_values['options']);
											
												$unserialized_meta[$jet_keys]['options'][$count_jetvalues]['key'] = $dvalueval;
												$unserialized_meta[$jet_keys]['options'][$count_jetvalues]['value'] = $dvalueval;
												$unserialized_meta[$jet_keys]['options'][$count_jetvalues]['id'] = $jet_values['options'][$count_jetvalues - 1]['id'] + 1;
												
											}
										}
									
										$serialized_meta = serialize($unserialized_meta);
										$wpdb->update( $wpdb->prefix . 'jet_post_types' , 
											array( 
												'meta_fields' => $serialized_meta,
											) , 
											array( 
												'id' => $jet_engine_id
											) 
										);

										$arr[$dvalueval] = 'true';
									}
								}		
							}
						}
						$darray[$jet_data['JECPT'][$dkey]['name']] = $arr;
					}
				}
				elseif($jet_data['JECPT'][$dkey]['type'] == 'select'){
					$dselect = [];
					if($jet_data['JECPT'][$dkey]['is_multiple'] == 0){
						$darray[$jet_data['JECPT'][$dkey]['name']] = $dvalue;	
					}
					else{
						$exp = explode(',',$dvalue);
						foreach($exp as $exp_values){
							$dselect[] = trim($exp_values);
						}
						// $dselect = $exp;
						$darray[$jet_data['JECPT'][$dkey]['name']] = $dselect;
					}
				}
				elseif($jet_data['JECPT'][$dkey]['type'] == 'posts'){
				
					if($jet_data['JECPT'][$dkey]['is_multiple']){
						$jet_posts_exp = explode(',',trim($dvalue));
						$jet_posts_value = array();
						foreach($jet_posts_exp as $jet_posts_value){
							$jet_posts_value = trim($jet_posts_value);

							if(is_numeric($jet_posts_value)){
								$jet_posts_field_value[] = $jet_posts_value;
							}
							else{
								$query = "SELECT id FROM {$wpdb->prefix}posts WHERE post_title ='{$jet_posts_value}' AND post_status = 'publish' ORDER BY ID DESC";
								$multiple_id = $wpdb->get_results($query);
								$multiple_id[0] = isset($multiple_id[0]) ? $multiple_id[0] : '';
								$multiple_ids = $multiple_id[0];
								if(!$multiple_id){
									$jet_posts_field_value[] = $jet_posts_value;
								}
								else{
									$jet_posts_field_value[] = trim($multiple_ids->id);
								}
							}
						}
		
						$darray[$jet_data['JECPT'][$dkey]['name']] = $jet_posts_field_value;
					}
					else{
						$jet_posts = trim($dvalue);
		
						//$jet_posts = $wpdb->_real_escape($jet_posts);
						if(is_numeric($jet_posts)){
							$jet_posts_field_value_1 = $jet_posts;	
						}
						else{
							$query = "SELECT id FROM {$wpdb->prefix}posts WHERE post_title ='{$jet_posts}' AND post_status='publish'";
							$name = $wpdb->get_results($query);
							if (!empty($name)) {
								$jet_posts_field_value_1 = $name[0]->id;
							}
						}
						$darray[$jet_data['JECPT'][$dkey]['name']] = $jet_posts_field_value_1;
					}
				}
				else{
					if($jet_data['JECPT'][$dkey]['type'] != 'repeater'){
						$darray[$jet_data['JECPT'][$dkey]['name']] = $dvalue;
					}
				}
			}
		}

		//update_post_meta($post_id, $map_acf_wp_element, $map_acf_csv_element)
		if($darray){
			foreach($darray as $mkey => $mval){
				update_post_meta($pID, $mkey, $mval);
			}
		}
	}

	public function jet_engine_cpt_rf_import_function($data_array, $type, $pID ,$mode, $hash_key,$line_number,$header_array,$value_array,$gmode,$templatekey) 
	{
		global $wpdb;
		$media_instance = MediaHandling::getInstance();
		$helpers_instance = ImportHelpers::getInstance();
		//$jet_rf_data = $this->JetEngineCPTRFFields($type);

		if($type == 'WooCommerce Product'){
			$type = 'product';
		}

		$listTaxonomy = get_taxonomies();
		if (in_array($type, $listTaxonomy)) {
			$get_import_type = 'term';
		}elseif ($type == 'Users' || $type == 'user') {
			$get_import_type = 'user';
		}elseif ($type == 'Comments') {
			$get_import_type = 'comment';
		} else {	
			$get_import_type = 'post';
		}

		$get_rf_data = $this->JetEngineCPTRFFields($type);
		$jet_rf_data = $get_rf_data['rf_cpt_fields'];
		$jet_cpt_rf_parent = $get_rf_data['rf_cpt_parent'];

		$jet_cpt_all_array = [];
		foreach($data_array as $data_key => $data_value){
			$jet_cpt_all_array[$jet_cpt_rf_parent[$data_key]][$data_key] = $data_value;
		}

		foreach($jet_cpt_all_array as $jet_cpt_all_array_key => $jet_cpt_all_array_value){
			$darray = [];
			foreach ($jet_cpt_all_array_value as $dkey => $dvalue) {
				$dvalue =trim($dvalue);
				$dvaluexp = explode( '|', $dvalue);
				
				foreach($dvaluexp  as $dvalueexpkey => $dvalues){
			
					$item = 'item-'.$dvalueexpkey;
					$gallery_ids = '';
					$media_ids = '';
					if(array_key_exists($dkey,$jet_rf_data['JECPTRF'])){
						if($jet_rf_data['JECPTRF'][$dkey]['type'] == 'gallery' || $jet_rf_data['JECPTRF'][$dkey]['type'] == 'media'){
							$exploded_gallery_items = explode( ',', $dvalues );
							$galleryvalue=array();
							foreach ( $exploded_gallery_items as $gallery ) {
								$gallery = trim( $gallery );
								if ( preg_match_all( '/\b(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i', $gallery ) ) {
																		
									$field_name = $item.'__'.$jet_cpt_all_array_key.'__'.$jet_rf_data['JECPTRF'][$dkey]['name'];
									$field_type = $jet_rf_data['JECPTRF'][$dkey]['type'];
									$plugin = 'jetenginecpt_repeater_'.$field_type;
									$get_gallery_id = $media_instance->image_meta_table_entry($data_array, $pID, $field_name,$gallery, $hash_key, $plugin, $get_import_type, $templatekey, $gmode, $header_array, $value_array);							
									$media_id = $get_gallery_id;
									if ( $get_gallery_id != '' ) {
										if($jet_rf_data['JECPTRF'][$dkey]['type'] == 'media'){
											$media_ids .= $media_id. ',';
										}
										elseif($jet_rf_data['JECPTRF'][$dkey]['value_format'] == 'url'){
							
											$get_gallery_fields = $wpdb->get_results("select meta_value from {$wpdb->prefix}postmeta where post_id=$get_gallery_id and meta_key ='_wp_attached_file'");
											$dir = wp_upload_dir();
											$gallery_ids .= $dir ['baseurl'] . '/' .$get_gallery_fields[0]->meta_value. ',';
										}
										elseif($jet_rf_data['JECPTRF'][$dkey]['value_format'] == 'both'){
											
											$gallery_id1 ['id']= $get_gallery_id;
											
											$get_gallery_fields = $wpdb->get_results("select meta_value from {$wpdb->prefix}postmeta where post_id=$get_gallery_id and meta_key ='_wp_attached_file'");
											$dir = wp_upload_dir();
											$gallery_id2['url']= $dir ['baseurl'] . '/' .$get_gallery_fields[0]->meta_value;
											
											$galleryvalue[] = array_merge($gallery_id1,$gallery_id2);
											
											$gallery_ids=$galleryvalue;
										}
										else{
											$gallery_ids .= $get_gallery_id.',';
										}
									}
								} else {
									$galleryLen         = strlen( $gallery );
									$checkgalleryid     = intval( $gallery );
									$verifiedGalleryLen = strlen( $checkgalleryid );
									if ( $galleryLen == $verifiedGalleryLen ) {
										if($jet_rf_data['JECPTRF'][$dkey]['type'] == 'media'){
											$media_ids .= $gallery. ',';
										}
										else{
											$gallery_ids .= $gallery. ',';
										}

									}
								}
							}
							// if(isset($gallery_ids)){
							// 	$gallery_id = rtrim($gallery_ids,',');
							// }
							if(is_array($gallery_ids)){
								$gallery_id  = $gallery_ids;
							}
							if (!is_array($gallery_ids)) {
								$gallery_id = rtrim($gallery_ids,',');
							}
							if(isset($media_ids)){
								$media_id = rtrim($media_ids,',');
							}
							if($jet_rf_data['JECPTRF'][$dkey]['value_format'] == 'url'){
	
								$get_media_fields = $wpdb->get_results("select meta_value from {$wpdb->prefix}postmeta where post_id=$media_id and meta_key ='_wp_attached_file'");
								$dir = wp_upload_dir();			
								//$media_id = $dir ['baseurl'] . '/' .$get_media_fields[0]->meta_value;	

								if(!empty($get_media_fields[0]->meta_value)){
									$media_id = $dir ['baseurl'] . '/' .$get_media_fields[0]->meta_value;
								}        
								else{
									$media_id='';
								}
							}
							elseif($jet_rf_data['JECPTRF'][$dkey]['value_format'] == 'both'){
								$media_ids1['id']=$media_id;
								$get_media_fields = $wpdb->get_results("select meta_value from {$wpdb->prefix}postmeta where post_id=$media_id and meta_key ='_wp_attached_file'");
								$dir = wp_upload_dir();			
								if(!empty($get_media_fields[0]->meta_value)){
									$media_ids2['url'] = $dir ['baseurl'] . '/' .$get_media_fields[0]->meta_value;
								}        
								else{
									$media_ids2['url']='';
								}
								$mediavalue= array_merge($media_ids1,$media_ids2);
								$media_id=array($mediavalue);

							}
							else{
								$media_id=$media_id;
							}
							if($jet_rf_data['JECPTRF'][$dkey]['type'] == 'media'){
								$darray[$item][$jet_rf_data['JECPTRF'][$dkey]['name']] = $media_id;
							}
							else{
								$darray[$item][$jet_rf_data['JECPTRF'][$dkey]['name']] = $gallery_id;
							}
						}
						elseif($jet_rf_data['JECPTRF'][$dkey]['type'] == 'datetime-local'){
							$dateformat = 'Y-m-d\TH:m';
							if(!empty($dvalues)) {
							$dt_var = trim($dvalues);
							$datetime = str_replace('/', '-', "$dt_var");
						
							if($jet_rf_data['JECPTRF'][$dkey]['is_timestamp']){
								$date_time_of =  strtotime($datetime);
							}else{
								$date_time_of = $helpers_instance->validate_datefield($dt_var,$dkey,$dateformat,$line_number);
							}

							$darray[$item][$jet_rf_data['JECPTRF'][$dkey]['name']] = $date_time_of;
						}
						else{
							$darray[$item][$jet_rf_data['JECPTRF'][$dkey]['name']] = '';
						}

						}
						elseif($jet_rf_data['JECPTRF'][$dkey]['type'] == 'date'){
							if(!empty($dvalues)) {
								$dateformat = 'Y-m-d';
							$var = trim($dvalues);
							$date = str_replace('/', '-', "$var");
							
							if($jet_rf_data['JECPTRF'][$dkey]['is_timestamp']){
								$date_of =  strtotime($date);
							}else{
								$date_of = $helpers_instance->validate_datefield($var,$dkey,$dateformat,$line_number);
							}
							$darray[$item][$jet_rf_data['JECPTRF'][$dkey]['name']] = $date_of;
						}
						else {
							$darray[$item][$jet_rf_data['JECPTRF'][$dkey]['name']] = '';
						}
						}
						elseif($jet_rf_data['JECPTRF'][$dkey]['type'] == 'time'){
							$var = trim($dvalues);
							$time = date('H:i', strtotime($var));
							$darray[$item][$jet_rf_data['JECPTRF'][$dkey]['name']] = $time;
						}
						elseif($jet_rf_data['JECPTRF'][$dkey]['type'] == 'checkbox'){
							if($jet_rf_data['JECPTRF'][$dkey]['is_array'] == 1){
								$dvalexp = explode(',' , $dvalues);
								$darray[$item][$jet_rf_data['JECPTRF'][$dkey]['name']] = $dvalexp;
							}
							else{
								$options = $jet_rf_data['JECPTRF'][$dkey]['options'];
								$arr = [];
								$opt = [];
								$dvalexp = explode(',' , $dvalues);
								foreach($options as $option_key => $option_val){
									//$opt[$option_key]	= $option_val['key'];
									$arr[$option_val['key']] = 'false';
								}
								foreach($dvalexp as $dvalkey => $dvalueval){
									$dvalueval = trim($dvalueval);
									$keys = array_keys($arr);
									foreach($keys as $keys1){
										if($dvalueval == $keys1){
											$arr[$keys1] = 'true';
										}
									}
								}
								$darray[$item][$jet_rf_data['JECPTRF'][$dkey]['name']] = $arr;
							}
						}
						elseif($jet_rf_data['JECPTRF'][$dkey]['type'] == 'select'){
							$dselect = [];
							if($jet_rf_data['JECPTRF'][$dkey]['is_multiple'] == 0){
								$darray[$item][$jet_rf_data['JECPTRF'][$dkey]['name']] = $dvalues;	
							}
							else{
								$exp =explode(',',$dvalues);
								foreach($exp as $exp_values){
									$dselect[] = trim($exp_values);
								}
								//$dselect = $exp;
								$darray[$item][$jet_rf_data['JECPTRF'][$dkey]['name']] = $dselect;
							}
						}
						elseif($jet_rf_data['JECPTRF'][$dkey]['type'] == 'posts'){
							if($jet_rf_data['JECPTRF'][$dkey]['is_multiple'] == 0){
								$jet_posts = trim($dvalues);
								//$jet_posts = $wpdb->_real_escape($jet_posts);
								if(is_numeric($jet_posts)){
									$jet_posts_field_value=$jet_posts;
								}
								else{
									$query = "SELECT id FROM {$wpdb->prefix}posts WHERE post_title ='{$jet_posts}' AND post_status = 'publish'";
									$name = $wpdb->get_results($query);
									if (!empty($name)) {
										$jet_posts_field_value=$name[0]->id;
									}
								}
							}
							else{
								$jet_posts_field_value = [];
								$jet_posts_exp = explode(',',trim($dvalues));
								$jet_posts_value = array();
								foreach($jet_posts_exp as $jet_posts_value){
									$jet_posts_value = trim($jet_posts_value);
									if(is_numeric($jet_posts_value)){
										$jet_posts_field_value[] = $jet_posts_value;
									}
									else{
										$query = "SELECT id FROM {$wpdb->prefix}posts WHERE post_title ='{$jet_posts_value}' AND post_status = 'publish' ORDER BY ID DESC";
										$multiple_id = $wpdb->get_results($query);
										$multiple_ids = isset($multiple_id[0])? $multiple_id[0] : '' ;
										if(!$multiple_id){
											$jet_posts_field_value[] = $jet_posts_value;
										}
										else{
											$jet_posts_field_value[] = trim($multiple_ids->id);
										}
									}
								}
							}
							$darray[$item][$jet_rf_data['JECPTRF'][$dkey]['name']] = $jet_posts_field_value;
						}
						else{
							$dvalues = trim($dvalues);
							$darray[$item][$jet_rf_data['JECPTRF'][$dkey]['name']] = $dvalues;
						}
						//$repfield = $jet_rf_data['JECPT'];
						// foreach($repfield as $rep_fkey => $rep_fvalue){
						// 	update_post_meta($pID, $rep_fvalue['name'], $darray);
						// }
						
						update_post_meta($pID, $jet_cpt_all_array_key, $darray);
					}	
				}
			}
		}
	}

	public function JetEngineCPTFields($type){
		global $wpdb;	
		$jet_field = array();
		// $get_meta_fields = $wpdb->get_results($wpdb->prepare("select id, meta_fields from {$wpdb->prefix}jet_post_types where slug = %s and status != %s", $type, 'trash'));
		// $unserialized_meta = maybe_unserialize($get_meta_fields[0]->meta_fields);
		//$get_meta_fields = $wpdb->get_results( $wpdb->prepare("SELECT id, meta_fields FROM {$wpdb->prefix}jet_post_types WHERE status != 'trash' AND slug = '$type'"),ARRAY_A);
		//$unserialized_meta = maybe_unserialize($get_meta_fields[0]['meta_fields']);

		$get_meta_fields = $wpdb->get_results("SELECT id, meta_fields FROM {$wpdb->prefix}jet_post_types WHERE slug = '$type' AND status IN ('publish','built-in')");
		if(isset($get_meta_fields[0])){
			$unserialized_meta = maybe_unserialize($get_meta_fields[0]->meta_fields);
		}
		else{
			$unserialized_meta = '';
		}

		$customFields = [];
		if(is_array($unserialized_meta)){
			foreach($unserialized_meta as $jet_key => $jet_value){
				$customFields["JECPT"][ $jet_value['name']]['label'] = $jet_value['title'];
				$customFields["JECPT"][ $jet_value['name']]['name']  = $jet_value['name'];
				$customFields["JECPT"][ $jet_value['name']]['type']  = $jet_value['type'];
				$customFields["JECPT"][ $jet_value['name']]['options'] = isset($jet_value['options']) ? $jet_value['options'] : '';
				$customFields["JECPT"][ $jet_value['name']]['is_multiple'] = isset($jet_value['is_multiple']) ? $jet_value['is_multiple'] : '';
				$customFields["JECPT"][ $jet_value['name']]['is_array'] = isset($jet_value['is_array']) ? $jet_value['is_array'] : '';
				$customFields["JECPT"][ $jet_value['name']]['value_format'] = isset($jet_value['value_format']) ? $jet_value['value_format'] : '';
				
				if($jet_value['type'] == 'date' || $jet_value['type'] == 'datetime-local'){
					$customFields["JECPT"][ $jet_value['name']]['is_timestamp'] = isset($jet_value['is_timestamp']) ? $jet_value['is_timestamp'] : '';
				}
				$jet_field[] = $jet_value['name'];
			}
		}
		return $customFields;	
	}
	
	public function JetEngineCPTRFFields($type){
		global $wpdb;	
		$jet_rf_field = array();
		$jet_rf_array = array();

		// $get_meta_fields = $wpdb->get_results($wpdb->prepare("select id, meta_fields from {$wpdb->prefix}jet_post_types where slug = %s and status != %s", $type, 'trash'));
		// $unserialized_meta = maybe_unserialize($get_meta_fields[0]->meta_fields);
		// $get_meta_fields = $wpdb->get_results( $wpdb->prepare("SELECT id, meta_fields FROM {$wpdb->prefix}jet_post_types WHERE status != 'trash' AND slug = '$type'"),ARRAY_A);
		// $unserialized_meta = maybe_unserialize($get_meta_fields[0]['meta_fields']);
		
		$get_meta_fields = $wpdb->get_results("SELECT id, meta_fields FROM {$wpdb->prefix}jet_post_types WHERE slug = '$type' AND status IN ('publish','built-in')");
		if(isset($get_meta_fields[0])){
			$unserialized_meta = maybe_unserialize($get_meta_fields[0]->meta_fields);
		}
		else{
			$unserialized_meta = '';
		}

		$customFields = [];
		if(is_array($unserialized_meta)){
			foreach($unserialized_meta as $jet_key => $jet_value){
				if($jet_value['type'] == 'repeater'){
					$customFields["JECPT"][ $jet_value['name']]['name']  = $jet_value['name'];
					$fields=$jet_value['repeater-fields'];
					foreach($fields as $rep_fieldkey => $rep_fieldvalue){
						$customFields["JECPTRF"][ $rep_fieldvalue['name']]['label'] = $rep_fieldvalue['title'];
						$customFields["JECPTRF"][ $rep_fieldvalue['name']]['name']  = $rep_fieldvalue['name'];
						$customFields["JECPTRF"][ $rep_fieldvalue['name']]['type']  = $rep_fieldvalue['type'];
						$customFields["JECPTRF"][ $rep_fieldvalue['name']]['options']  = isset($rep_fieldvalue['options']) ? $rep_fieldvalue['options'] : '';
						$customFields["JECPTRF"][ $rep_fieldvalue['name']]['is_multiple']  = isset($rep_fieldvalue['is_multiple']) ? $rep_fieldvalue['is_multiple'] : '';
						$customFields["JECPTRF"][ $rep_fieldvalue['name']]['is_array']  = isset($rep_fieldvalue['is_array']) ? $rep_fieldvalue['is_array'] : '';
						$customFields["JECPTRF"][ $rep_fieldvalue['name']]['value_format'] = isset($rep_fieldvalue['value_format']) ? $rep_fieldvalue['value_format'] : '';
						
						if($rep_fieldvalue['type'] == 'date' || $rep_fieldvalue['type'] == 'datetime-local'){
							$customFields["JECPTRF"][ $rep_fieldvalue['name']]['is_timestamp'] = isset($rep_fieldvalue['is_timestamp']) ? $rep_fieldvalue['is_timestamp'] : '';
						}
						$jet_rf_field[] = $rep_fieldvalue['name'];

						$jet_rf_array[$rep_fieldvalue['name']] = $jet_value['name'];
					}
				}
			}
		}
		//return $customFields;	

		$resultant_arr['rf_cpt_fields'] = $customFields;
		$resultant_arr['rf_cpt_parent'] = $jet_rf_array;
		return $resultant_arr;
	}
}