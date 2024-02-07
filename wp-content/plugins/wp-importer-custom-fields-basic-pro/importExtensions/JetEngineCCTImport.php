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

class JetEngineCCTImport {

	private static $instance = null;

	public static function getInstance() {		
		if (JetEngineCCTImport::$instance == null) {
			JetEngineCCTImport::$instance = new JetEngineCCTImport;
		}
		return JetEngineCCTImport::$instance;
	}

	function set_jet_engine_cct_values($header_array ,$value_array , $map, $post_id , $type , $mode, $hash_key,$line_number,$gmode,$templatekey){
		$post_values = [];
		$helpers_instance = ImportHelpers::getInstance();
		$post_values = $helpers_instance->get_header_values($map , $header_array , $value_array);
		$this->jet_engine_cct_import_function($post_values,$type, $post_id, $mode, $hash_key,$line_number,$header_array,$value_array,$gmode,$templatekey);
	}

	function set_jet_engine_cct_rf_values($header_array ,$value_array , $map, $post_id , $type , $mode, $hash_key,$line_number,$gmode,$templatekey){
		$post_values = [];
		$helpers_instance = ImportHelpers::getInstance();
		$post_values = $helpers_instance->get_header_values($map , $header_array , $value_array);
		$this->jet_engine_cct_rf_import_function($post_values,$type, $post_id, $mode, $hash_key,$line_number,$header_array,$value_array,$gmode,$templatekey);
	}
    
	public function jet_engine_cct_import_function($data_array, $type, $pID ,$mode, $hash_key,$line_number,$header_array,$value_array,$gmode,$templatekey) 
	{
		global $wpdb;
		$media_instance = MediaHandling::getInstance();
		$helpers_instance = ImportHelpers::getInstance();
		$jet_data = $this->JetEngineCCTFields($type);
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
			if(array_key_exists($dkey,$jet_data['JECCT'])){
				
				if($jet_data['JECCT'][$dkey]['type'] == 'text' ||$jet_data['JECCT'][$dkey]['type'] == 'textarea'
				|| $jet_data['JECCT'][$dkey]['type'] == 'colorpicker' || $jet_data['JECCT'][$dkey]['type'] == 'iconpicker'
				|| $jet_data['JECCT'][$dkey]['type'] == 'radio' || $jet_data['JECCT'][$dkey]['type'] == 'number'
				|| $jet_data['JECCT'][$dkey]['type'] == 'wysiwyg' || $jet_data['JECCT'][$dkey]['type'] == 'switcher'){
					
					$darray[$jet_data['JECCT'][$dkey]['name']] = $dvalue;

				}
				elseif($jet_data['JECCT'][$dkey]['type'] == 'date'){					
					$dateformat = 'Y-m-d';
					if(!empty($dvalue)){
						$var = trim($dvalue);
						$date = str_replace('/', '-', "$var");
						if($jet_data['JECCT'][$dkey]['is_timestamp']){
							if(is_numeric($date)){
								$date_of = $date;
							}
							else{
								$date_of = strtotime($date);
							}
						}else{
							$date_of = $helpers_instance->validate_datefield($var,$dkey,$dateformat,$line_number);							
						}
						$darray[$jet_data['JECCT'][$dkey]['name']] = $date_of;
					}
					else{
						$darray[$jet_data['JECCT'][$dkey]['name']] = '';
					}
				}
				elseif($jet_data['JECCT'][$dkey]['type'] == 'gallery' || $jet_data['JECCT'][$dkey]['type'] == 'media'){
					$gallery_ids ='';
					$media_ids = '';
					$media_id='';
					$dselect='';
					$exploded_gallery_items = explode( ',', $dvalue );
                    $galleryvalue=array();
					foreach ( $exploded_gallery_items as $gallery ) {
						$gallery = trim( $gallery );
						if ( preg_match_all( '/\b(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i', $gallery ) ) {
							$field_name = $type.'__'.$jet_data['JECCT'][$dkey]['name'];
							$field_type = $jet_data['JECCT'][$dkey]['type'];
							$plugin = 'jetenginecct_'.$field_type;
							$get_gallery_id = $media_instance->image_meta_table_entry($data_array, $pID, $field_name,$gallery, $hash_key, $plugin, $get_import_type, $templatekey, $gmode, $header_array, $value_array);							
							$media_id = $get_gallery_id;
							if ( $get_gallery_id != '' ) {
								if($jet_data['JECCT'][$dkey]['type'] == 'media'){
									$media_ids .= $media_id. ',';
								}
								elseif($jet_data['JECCT'][$dkey]['value_format'] == 'url'){
									$get_gallery_fields = $wpdb->get_results("select meta_value from {$wpdb->prefix}postmeta where post_id=$get_gallery_id and meta_key ='_wp_attached_file'");
									$dir = wp_upload_dir();
									$gallery_ids .= $dir ['baseurl'] . '/' .$get_gallery_fields[0]->meta_value. ',';
								}
                                elseif($jet_data['JECCT'][$dkey]['value_format'] == 'both'){
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
								if($jet_data['JECCT'][$dkey]['type'] == 'media'){
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
						$media_id1 = rtrim($media_ids,',');
					}
					if($jet_data['JECCT'][$dkey]['value_format'] == 'url'){
						$get_media_fields = $wpdb->get_results("select meta_value from {$wpdb->prefix}postmeta where post_id=$media_id and meta_key ='_wp_attached_file'");
						$dir = wp_upload_dir();			
						if(!empty($get_media_fields[0]->meta_value)){
                            $media_id = $dir ['baseurl'] . '/' .$get_media_fields[0]->meta_value;
                        }        
                        else{
                            $media_id='';
                        }
					}
                    elseif($jet_data['JECCT'][$dkey]['value_format'] == 'both'){
						if ( $media_id != '' ) {
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
					}	
					else{
						
						$media_id=$media_id;
					}
					if($jet_data['JECCT'][$dkey]['type'] == 'media'){
						
						$darray[$jet_data['JECCT'][$dkey]['name']] = $media_id;
					}
					else{	
						$darray[$jet_data['JECCT'][$dkey]['name']] = $gallery_id;
					}
				}
				elseif($jet_data['JECCT'][$dkey]['type'] == 'datetime-local'){
					$dateformat = 'Y-m-d\TH:m';
					if(!empty($dvalue)) {
					$dt_var = trim($dvalue);
					$datetime = str_replace('/', '-', "$dt_var");
					if($jet_data['JECCT'][$dkey]['is_timestamp']){
						if(is_numeric($datetime)){
							$date_time_of = $datetime;
						}
						else{
							$date_time_of = strtotime($datetime);
						}
					}else{
						$date_time_of = $helpers_instance->validate_datefield($dt_var,$dkey,$dateformat,$line_number);
					}
					$darray[$jet_data['JECCT'][$dkey]['name']] = $date_time_of;
				}
				else {
					$darray[$jet_data['JECCT'][$dkey]['name']] = '';
				}

				}
				elseif($jet_data['JECCT'][$dkey]['type'] == 'time'){
					$var = trim($dvalue);
					$time = date('H:i', strtotime($var));
					$darray[$jet_data['JECCT'][$dkey]['name']] = $time;
				}
				elseif($jet_data['JECCT'][$dkey]['type'] == 'checkbox'){

					if($jet_data['JECCT'][$dkey]['is_array'] == 1){
						$dvalexp = explode(',' , $dvalue);
						$darray[$jet_data['JECCT'][$dkey]['name']] = $dvalexp;
					}
					else{
						$options = $jet_data['JECCT'][$dkey]['options'];
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
								$get_meta_fields = $wpdb->get_results("SELECT id, meta_fields from {$wpdb->prefix}jet_post_types where slug = '$type' and status = 'content-type'");
					
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
						$check_val=serialize($arr);
						$darray[$jet_data['JECCT'][$dkey]['name']] = $check_val;
					}
				}
				elseif($jet_data['JECCT'][$dkey]['type'] == 'select'){
					$dselect = [];
					if($jet_data['JECCT'][$dkey]['is_multiple'] == 0){
						$darray[$jet_data['JECCT'][$dkey]['name']] = $dvalue;	
					}
					else{
						$exp = explode(',',$dvalue);
						foreach($exp as $exp_values){
							$dselect[] = trim($exp_values);
						}
						//$dselect = $exp;
						$darray[$jet_data['JECCT'][$dkey]['name']] = $dselect;
					}
				}
				elseif($jet_data['JECCT'][$dkey]['type'] == 'posts'){
		
					if($jet_data['JECCT'][$dkey]['is_multiple'] == 0){
						//$jet_posts = $wpdb->_real_escape($jet_posts);
						if(is_numeric($dvalue)) {
							$jet_post_values=$dvalue;
							$darray[$jet_data['JECCT'][$dkey]['name']] = $jet_post_values;
						}
						else{
							$jet_posts = trim($dvalue);
							$query = "SELECT id FROM {$wpdb->prefix}posts WHERE post_title ='{$jet_posts}' AND post_status='publish'";
							$name = $wpdb->get_results($query);
							if (!empty($name)) {
								$jet_posts_values=$name[0]->id;
							}
							$darray[$jet_data['JECCT'][$dkey]['name']] = $jet_posts_values;
						}
					}
					else{
						$jet_posts_exp = explode(',',trim($dvalue));
						$jet_posts_value = array();
						foreach($jet_posts_exp as $jet_posts_value){
							$jet_posts_value = trim($jet_posts_value);
							
							if(is_numeric($jet_posts_value)){
								$jet_posts_field_value[]=$jet_posts_value;
							}
							else{
								$query = "SELECT id FROM {$wpdb->prefix}posts WHERE post_title ='{$jet_posts_value}' AND post_status='publish' ORDER BY ID DESC";
								$multiple_id = $wpdb->get_results($query);
								$multiple_id[0] = isset($multiple_id[0]) ? $multiple_id[0] : '';
								$multiple_ids =$multiple_id[0];
								if(!$multiple_id){
									$jet_posts_field_value[]=$jet_posts_value;
								}
								else{
									$jet_posts_field_value[]=trim($multiple_ids->id);
								}
							}
							
						}
						$jet_posts_value=serialize($jet_posts_field_value);
						$darray[$jet_data['JECCT'][$dkey]['name']] = $jet_posts_value;    
					}
				}
				else{
					if($jet_data['JECCT'][$dkey]['type'] != 'repeater'){
						$darray[$jet_data['JECCT'][$dkey]['name']] = $dvalue;
					}
				}
				// html                          	
			}
		}
		$tab_name ='jet_cct_'.$type;
		$darray['cct_modified']= date("Y-m-d\TH:i");	
		$table_name = 'jet_cct_'.$type;
	
		if($darray){	
			$dataArray = [];
			$key_name = '';
			$data_values = '';
			
			foreach($darray as $mkey => $mval){
				if(is_array($mval)){
					$darray[$mkey] = serialize($mval);
				}
			}													
			$wpdb->update($wpdb->prefix.'jet_cct_'.$type,
				$darray,
				array( '_ID' => $pID) 
			);			
		}

		$getarg = $wpdb->get_results("SELECT args from {$wpdb->prefix}jet_post_types where slug = '$type' and status = 'content-type'",ARRAY_A);		
		foreach($getarg as $key => $value){				
			$arg_data = $value['args'];				
			break;
		}			
		$arg_data = unserialize($arg_data);
		if(!empty($arg_data) && array_key_exists('has_single',$arg_data) && $arg_data['has_single']){
			$this->set_has_single($arg_data,$darray,$type,$pID);
		}	
	}

	public function jet_engine_cct_rf_import_function($data_array, $type, $pID ,$mode, $hash_key,$line_number,$header_array,$value_array,$gmode,$templatekey) 
	{
		global $wpdb;
		$table_names = 'jet_cct_'.$type;
		$get_results =  $wpdb->get_results("SELECT cct_status FROM {$wpdb->prefix}$table_names WHERE  _ID = $pID ");			
		
		if(empty($get_results)){
			return;
		}
		$media_instance = MediaHandling::getInstance();
		$helpers_instance = ImportHelpers::getInstance();
		//$jet_rf_data = $this->JetEngineCCTRFFields($type);

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

		$get_cct_rf_data = $this->JetEngineCCTRFFields($type);
		$jet_rf_data = $get_cct_rf_data['rf_cct_fields'];
		$jet_cct_rf_parent = $get_cct_rf_data['rf_cct_parent'];

		$jet_cct_all_array = [];
		foreach($data_array as $data_key => $data_value){
			$jet_cct_all_array[$jet_cct_rf_parent[$data_key]][$data_key] = $data_value;
		}

		foreach($jet_cct_all_array as $jet_cct_all_array_key => $jet_cct_all_array_value){
			$darray = [];
			$value = [];
			foreach ($jet_cct_all_array_value as $dkey => $dvalue) {
				$dvalue =trim($dvalue);
				$dvaluexp = explode( '|', $dvalue);
				foreach($dvaluexp  as $dvalueexpkey => $dvalues){
					$array = [];
					$item = 'item-'.$dvalueexpkey;
					$gallery_ids = '';
					$media_ids = '';
					if(array_key_exists($dkey,$jet_rf_data['JECCTRF'])){
						if($jet_rf_data['JECCTRF'][$dkey]['type'] == 'text' ||$jet_rf_data['JECCTRF'][$dkey]['type'] == 'textarea'
							|| $jet_rf_data['JECCTRF'][$dkey]['type'] == 'colorpicker' || $jet_rf_data['JECCTRF'][$dkey]['type'] == 'iconpicker'
							|| $jet_rf_data['JECCTRF'][$dkey]['type'] == 'radio' || $jet_rf_data['JECCTRF'][$dkey]['type'] == 'number'
							|| $jet_rf_data['JECCTRF'][$dkey]['type'] == 'wysiwyg' || $jet_rf_data['JECCTRF'][$dkey]['type'] == 'switcher'){									
								$value[$item][$jet_rf_data['JECCTRF'][$dkey]['name']] = $dvalues;
								$darray=serialize($value);
						}
						elseif($jet_rf_data['JECCTRF'][$dkey]['type'] == 'date'){
							$dateformat = 'Y-m-d';
							if(!empty($dvalues)){
								$var = trim($dvalues);
								$date = str_replace('/', '-', "$var");
								if($jet_rf_data['JECCTRF'][$dkey]['is_timestamp']){
									$date_of =  strtotime($date);
								}else{
									$date_of = $helpers_instance->validate_datefield($var,$dkey,$dateformat,$line_number);
								}
								$value[$item][$jet_rf_data['JECCTRF'][$dkey]['name']] = $date_of;
								$darray=serialize($value);
							}
							else{
								$value[$item][$jet_rf_data['JECCTRF'][$dkey]['name']] = '';
								$darray=serialize($value);
							}
						}
						elseif($jet_rf_data['JECCTRF'][$dkey]['type'] == 'gallery' || $jet_rf_data['JECCTRF'][$dkey]['type'] == 'media'){
								$exploded_gallery_items = explode( ',', $dvalues );
								$galleryvalue=array();
								foreach ( $exploded_gallery_items as $gallery ) {
									$gallery = trim( $gallery );
									if ( preg_match_all( '/\b(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i', $gallery ) ) {
										$field_name = $type.'__'.$item.'__'.$jet_cct_all_array_key.'__'.$jet_rf_data['JECCTRF'][$dkey]['name'];
										$field_type = $jet_rf_data['JECCTRF'][$dkey]['type'];
										$plugin = 'jetenginecct_repeater_'.$field_type;
										$table_name = 'jet_cct_'.$type;
										$get_result =  $wpdb->get_results("SELECT _ID FROM {$wpdb->prefix}$table_name   order by _ID ASC ");			
										foreach($get_result as $vkey=>$get_slug){
											$post_id=$get_slug->_ID;
										}	
										$get_gallery_id = $media_instance->image_meta_table_entry($data_array, $post_id, $field_name,$gallery, $hash_key, $plugin,$get_import_type, $templatekey, $gmode, $header_array, $value_array);							
										$media_id = $get_gallery_id;		
										if ( $get_gallery_id != '' ) {
											if($jet_rf_data['JECCTRF'][$dkey]['type'] == 'media'){
												$media_ids .= $media_id. ',';
											}
											elseif($jet_rf_data['JECCTRF'][$dkey]['value_format'] == 'url'){
												$get_gallery_fields = $wpdb->get_results("select meta_value from {$wpdb->prefix}postmeta where post_id=$get_gallery_id and meta_key ='_wp_attached_file'");
												$dir = wp_upload_dir();
												$gallery_ids .= $dir ['baseurl'] . '/' .$get_gallery_fields[0]->meta_value. ',';
											}
											elseif($jet_rf_data['JECCTRF'][$dkey]['value_format'] == 'both'){
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
											if($jet_rf_data['JECCTRF'][$dkey]['type'] == 'media'){
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
									$media_id = rtrim($media_ids,',');
								}
								if($jet_rf_data['JECCTRF'][$dkey]['value_format'] == 'url'){
									$get_media_fields = $wpdb->get_results("select meta_value from {$wpdb->prefix}postmeta where post_id=$media_id and meta_key ='_wp_attached_file'");
									$dir = wp_upload_dir();		
									if(!empty($get_media_fields[0]->meta_value)){
										$media_id = $dir ['baseurl'] . '/' .$get_media_fields[0]->meta_value;
									}        
									else{
										$media_id='';
									}
								}
								elseif($jet_rf_data['JECCTRF'][$dkey]['value_format'] == 'both'){
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
								if($jet_rf_data['JECCTRF'][$dkey]['type'] == 'media'){
									$value[$item][$jet_rf_data['JECCTRF'][$dkey]['name']] = $media_id;
									$darray=serialize($value);
								}
								else{
									$value[$item][$jet_rf_data['JECCTRF'][$dkey]['name']] = $gallery_id;
									$darray=serialize($value);
								}
						}
						elseif($jet_rf_data['JECCTRF'][$dkey]['type'] == 'datetime-local'){
							$dateformat = 'Y-m-d\TH:m';
							if(!empty($dvalues)) {
							$dt_var = trim($dvalues);
							$dt_var = str_replace('/', '-', "$dt_var");

							if($jet_rf_data['JECCTRF'][$dkey]['is_timestamp']){
								$date_time_of =  strtotime($dt_var) ;
							}
							else{
								$date_time_of = $helpers_instance->validate_datefield($dt_var,$dkey,$dateformat,$line_number);
							}
							$value[$item][$jet_rf_data['JECCTRF'][$dkey]['name']] = $date_time_of;
							$darray=serialize($value);
						}
						else {
							$value[$item][$jet_rf_data['JECCTRF'][$dkey]['name']] = '';
							$darray=serialize($value);
						}
						}
						elseif($jet_rf_data['JECCTRF'][$dkey]['type'] == 'time'){
							$var = trim($dvalues);
							$time = date('H:i', strtotime($var));
							$value[$item][$jet_rf_data['JECCTRF'][$dkey]['name']] = $time;
							$darray=serialize($value);
						}
						elseif($jet_rf_data['JECCTRF'][$dkey]['type'] == 'checkbox'){
							if($jet_rf_data['JECCTRF'][$dkey]['is_array'] == 1){
								$dvalexp = explode(',' , $dvalues);
								$value[$item][$jet_rf_data['JECCTRF'][$dkey]['name']] = $dvalexp;
								$darray=serialize($value);
							}
							else{
								$options = $jet_rf_data['JECCTRF'][$dkey]['options'];
								$arr = [];
								$opt = [];
								$dvalexp = explode(',' , $dvalues);
								foreach($options as $option_key => $option_val){
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
								$value[$item][$jet_rf_data['JECCTRF'][$dkey]['name']] = $arr;
								$darray=serialize($value);
							}
						}
						elseif($jet_rf_data['JECCTRF'][$dkey]['type'] == 'select'){
							$dselect = [];
							if($jet_rf_data['JECCTRF'][$dkey]['is_multiple'] == 0){
								$value[$item][$jet_rf_data['JECCTRF'][$dkey]['name']] = $dvalues;
								$darray=serialize($value);	
							}
							else{
								$exp = explode(',',$dvalues);
								foreach($exp as $exp_values){
									$dselect[] = trim($exp_values);
								}
								//$dselect = $exp;
								$value[$item][$jet_rf_data['JECCTRF'][$dkey]['name']] = $dselect;
								$darray=serialize($value);
							}
						}
						elseif($jet_rf_data['JECCTRF'][$dkey]['type'] == 'posts'){
							if($jet_rf_data['JECCTRF'][$dkey]['is_multiple'] == 0){
								$jet_posts = trim($dvalues);
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
								$value[$item][$jet_rf_data['JECCTRF'][$dkey]['name']] = $jet_posts_field_value_1;
							}
							else{
								$jet_posts_field_value = [];
								$jet_posts_exp = explode(',',trim($dvalues));
								$jet_posts_value = array();
								foreach($jet_posts_exp as $jet_posts_value){
									$jet_posts_value = trim($jet_posts_value);

									if(is_numeric($jet_posts_value)){
										$jet_posts_field_value = $jet_posts_value;
									}
									else{
										$query = "SELECT id FROM {$wpdb->prefix}posts WHERE post_title ='{$jet_posts_value}' AND post_status='publish' ORDER BY ID DESC";
										$multiple_id = $wpdb->get_results($query);
										$multiple_ids =isset($multiple_id[0])? $multiple_id[0] : '' ;
										if(!$multiple_id){
											$jet_posts_field_value[]=$jet_posts_value;
										}
										else{
											$jet_posts_field_value[]=trim($multiple_ids->id);
										}
									}
								}
								$value[$item][$jet_rf_data['JECCTRF'][$dkey]['name']] = $jet_posts_field_value;
							}
							
							$darray=serialize($value);
						}
						else{
							$dvalues = trim($dvalues);
							$value[$item][$jet_rf_data['JECCTRF'][$dkey]['name']] = $dvalues;
							$darray=serialize($value);
						}	
					}
				}
			}
			
			$table_name = 'jet_cct_'.$type;
			$get_result =  $wpdb->get_results("SELECT _ID FROM {$wpdb->prefix}$table_name   order by _ID ASC ");			
			foreach($get_result as $vkey=>$get_slug){
				$post_id=$get_slug->_ID;
			}	

			$sql = $wpdb->prepare(
			"UPDATE {$wpdb->prefix}$table_name SET $jet_cct_all_array_key = '$darray' WHERE _ID = %d;",
			$pID
			);
			$wpdb->query( $sql );		
		}
		
	}

	public function JetEngineCCTFields($type){
		global $wpdb;	
		$jet_field = array();
		$get_meta_fields = $wpdb->get_results("select id, meta_fields from {$wpdb->prefix}jet_post_types where slug = '$type' and status = 'content-type'");
		$unserialized_meta = maybe_unserialize($get_meta_fields[0]->meta_fields);

		foreach($unserialized_meta as $jet_key => $jet_value){
			$customFields["JECCT"][ $jet_value['name']]['label'] = $jet_value['title'];
			$customFields["JECCT"][ $jet_value['name']]['name']  = $jet_value['name'];
			$customFields["JECCT"][ $jet_value['name']]['type']  = $jet_value['type'];
			$customFields["JECCT"][ $jet_value['name']]['options'] = isset($jet_value['options']) ? $jet_value['options'] : '';
			$customFields["JECCT"][ $jet_value['name']]['is_multiple'] = isset($jet_value['is_multiple']) ? $jet_value['is_multiple'] : '';
			$customFields["JECCT"][ $jet_value['name']]['is_array'] = isset($jet_value['is_array']) ? $jet_value['is_array'] : '';
			$customFields["JECCT"][ $jet_value['name']]['value_format'] = isset($jet_value['value_format']) ? $jet_value['value_format'] : '';
			if($jet_value['type'] == 'date' || $jet_value['type'] == 'datetime-local'){
				$customFields["JECCT"][ $jet_value['name']]['is_timestamp'] = isset($jet_value['is_timestamp']) ? $jet_value['is_timestamp'] : '';
			}
			$jet_field[] = $jet_value['name'];
		}
		return $customFields;	
	}

	public function JetEngineCCTRFFields($type){
		global $wpdb;	
		$jet_rf_field = array();
		$jet_cct_rf_array = array();

		// $get_meta_fields = $wpdb->get_results($wpdb->prepare("select id, meta_fields from {$wpdb->prefix}jet_post_types where slug = %s and status = %s", $type, 'content-type'));
		$get_meta_fields = $wpdb->get_results("select id, meta_fields from {$wpdb->prefix}jet_post_types where slug = '$type' and status = 'content-type'");
		$unserialized_meta = maybe_unserialize($get_meta_fields[0]->meta_fields);
		foreach($unserialized_meta as $jet_key => $jet_value){
			if($jet_value['type'] == 'repeater'){
				$customFields["JECCT"][ $jet_value['name']]['name']  = $jet_value['name'];
				$fields=$jet_value['repeater-fields'];
				foreach($fields as $rep_fieldkey => $rep_fieldvalue){
					$customFields["JECCTRF"][ $rep_fieldvalue['name']]['label'] = $rep_fieldvalue['title'];
					$customFields["JECCTRF"][ $rep_fieldvalue['name']]['name']  = $rep_fieldvalue['name'];
					$customFields["JECCTRF"][ $rep_fieldvalue['name']]['type']  = $rep_fieldvalue['type'];
					$customFields["JECCTRF"][ $rep_fieldvalue['name']]['options']  = isset($rep_fieldvalue['options']) ? $rep_fieldvalue['options'] : '';
					$customFields["JECCTRF"][ $rep_fieldvalue['name']]['is_multiple']  = isset($rep_fieldvalue['is_multiple']) ? $rep_fieldvalue['is_multiple'] : '';
					$customFields["JECCTRF"][ $rep_fieldvalue['name']]['is_array']  = isset($rep_fieldvalue['is_array']) ? $rep_fieldvalue['is_array'] : '';
					$customFields["JECCTRF"][ $rep_fieldvalue['name']]['value_format'] = isset($rep_fieldvalue['value_format']) ? $rep_fieldvalue['value_format'] : '';
					if($rep_fieldvalue['type'] == 'date' || $rep_fieldvalue['type'] == 'datetime-local'){
						$customFields["JECCTRF"][ $rep_fieldvalue['name']]['is_timestamp'] = isset($rep_fieldvalue['is_timestamp']) ? $rep_fieldvalue['is_timestamp'] : '';
					}
					$jet_rf_field[] = $rep_fieldvalue['name'];

					$jet_cct_rf_array[$rep_fieldvalue['name']] = $jet_value['name'];
				}
			}
		}
		//return $customFields;	

		$resultant_arr['rf_cct_fields'] = $customFields;
		$resultant_arr['rf_cct_parent'] = $jet_cct_rf_array;
		return $resultant_arr;
	}

	function set_has_single($data,$field_data,$type,$pID){
		$title_field = $content_field = "";
		$relation_title = $relation_content = "";
		if(array_key_exists('related_post_type',$data)){
			$relation_type = $data['related_post_type'];
			if(array_key_exists('related_post_type_title',$data)){
				$title_field = $data['related_post_type_title'];				
			}
			if(array_key_exists('related_post_type_content',$data)){
				$content_field = $data['related_post_type_content'];
			}
		}
		if(array_key_exists($title_field,$field_data)){
			$relation_title = $field_data[$title_field];			
		}
		if(array_key_exists($content_field,$field_data)){
			$relation_content = $field_data[$content_field];
		}		
		$this->create_interrelation($relation_title,$relation_content,$type,$relation_type,$pID);
	}

	function create_interrelation($title,$content,$type,$relation_type,$pID){
		global $wpdb;
		$data_array['post_title'] = $title;
		$data_array['post_content'] = $content;
		$data_array['post_status'] = 'publish';
		$data_array['post_name'] = str_replace(" ","-",$title);
		$data_array['post_date'] = current_time('Y-m-d H:i:s');
		$data_array['post_type'] = $relation_type;
		$post_id = wp_insert_post($data_array);	
		
		if(!empty($post_id)){
			$wpdb->update( $wpdb->prefix . 'jet_cct_'.$type , 
											array( 
												'cct_single_post_id' => $post_id,
											) , 
											array( 
												'_ID' => $pID
											) 
										);
		}
	}
}