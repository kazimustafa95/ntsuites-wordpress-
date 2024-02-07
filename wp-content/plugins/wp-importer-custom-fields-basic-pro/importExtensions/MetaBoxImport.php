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

class MetaBoxImport {
    private static $metabox_instance = null, $media_instance;

    public static function getInstance() {
		
		if (MetaBoxImport::$metabox_instance == null) {
			MetaBoxImport::$metabox_instance = new MetaBoxImport;
			MetaBoxImport::$media_instance = new MediaHandling();
			return MetaBoxImport::$metabox_instance;
		}
		return MetaBoxImport::$metabox_instance;
    }
	
    function set_metabox_values($header_array ,$value_array , $map, $post_id , $type,$line_number,$hash_key,$gmode,$templatekey){
		
		$post_values = [];
		$helpers_instance = ImportHelpers::getInstance();	
		$post_values = $helpers_instance->get_header_values($map , $header_array , $value_array);
		
		$this->metabox_import_function($post_values, $post_id , $header_array , $value_array, $type,$line_number,$hash_key,$gmode,$templatekey);
    }

    public function metabox_import_function ($data_array, $pID, $header_array, $value_array, $type,$line_number,$hash_key,$gmode,$templatekey) {
		
		global $wpdb;
		$helpers_instance = ImportHelpers::getInstance();
		$media_instance = MediaHandling::getInstance();
		$extension_object = new MetaBoxExtension;
		$import_as = $extension_object->import_post_types($type );
		$listTaxonomy = get_taxonomies();

		if($import_as == 'user')		{
			$get_metabox_fields = \rwmb_get_object_fields( $import_as,$import_as); 
			$get_import_type = 'user';
		}
		elseif(in_array($import_as, $listTaxonomy)){
			$get_metabox_fields = \rwmb_get_object_fields( $import_as,'term'); 
			$get_import_type = 'term';
		}
		else{
			$get_metabox_fields = \rwmb_get_object_fields($import_as); 
			$get_import_type = $import_as;
		}	

				
		foreach($data_array as $data_key => $data_value){
			$field_type = $get_metabox_fields[$data_key]['type'];
			$clonable = $get_metabox_fields[$data_key]['clone'];			
			$storage_type = isset($get_metabox_fields[$data_key]['storage']) ? 	$get_metabox_fields[$data_key]['storage'] : "";
			$timestamp = isset($get_metabox_fields[$data_key]['timestamp']) ? $get_metabox_fields[$data_key]['timestamp'] : "";
			$check_for_multiple = isset($get_metabox_fields[$data_key]['multiple']) ? $get_metabox_fields[$data_key]['multiple'] : '';
			$get_fieldset_options = isset($get_metabox_fields[$data_key]['options']) ? $get_metabox_fields[$data_key]['options'] : '';			

			if($storage_type != ""){				
				$this->importFieldsCustomTable($data_key,$data_value,$get_metabox_fields,$pID,$hash_key,$line_number,$type,$get_import_type,$gmode,$templatekey);
			}

			else {
		
			if($clonable){
				$max_item = $get_metabox_fields[$data_key]['max_clone'];
				$this->metabox_clone_import($data_value,$pID,$type,$data_key,$field_type,$check_for_multiple,$timestamp,$max_item,$get_fieldset_options,$line_number, $hash_key, $get_import_type,$gmode,$templatekey);
			}
			else{
				if($clonable){
					$max_item = $get_metabox_fields[$data_key]['max_clone'];
					$this->metabox_clone_import($data_value,$pID,$type,$data_key,$field_type,$check_for_multiple,$timestamp,$max_item,$get_fieldset_options,$line_number, $hash_key, $get_import_type,$gmode,$templatekey);
				}
				else {

					if($field_type == 'text_list' || $field_type == 'select' || $field_type == 'select_advanced'){
						$get_text_list_fields = explode(',', $data_value);
						foreach($get_text_list_fields as $text_list_fields){
							if($check_for_multiple){
								add_post_meta($pID, $data_key, $text_list_fields);
							}
							else{
								update_post_meta($pID, $data_key, $text_list_fields);
							}
						}
					}
					elseif($field_type == 'checkbox_list'){
						$get_checkbox_list_fields = explode(',', $data_value);
						foreach($get_checkbox_list_fields as $checkbox_list_fields){
							add_post_meta($pID, $data_key, $checkbox_list_fields);
						}
					}
					elseif($field_type == 'fieldset_text'){
						$get_fieldset_text_fields = explode(',', $data_value);

						$temp = 0;
						$fieldset_array = [];
						foreach($get_fieldset_options as $fieldset_key => $fieldset_options){
							$fieldset_array[$fieldset_key] = $get_fieldset_text_fields[$temp];
							$temp++;
						}
				
						update_post_meta($pID, $data_key, $fieldset_array);
					}
					elseif($field_type == 'image' || $field_type == 'file' || $field_type == 'file_advanced' || $field_type == 'file_upload' || $field_type == 'image_advanced'){
						$get_uploads_fields = explode(',', $data_value);
						$get_fields_count = count($get_uploads_fields);
						foreach($get_uploads_fields as $uploads_fields){							
							
							if($field_type == 'image' || $field_type == 'image_advanced'){
								$attachmentId = MetaBoxImport::$media_instance->image_meta_table_entry('', $pID, $data_key, $uploads_fields, $hash_key, 'metabox', $get_import_type, $templatekey, $gmode);
							}
							else{
								$attachmentId = MetaBoxImport::$media_instance->media_handling($uploads_fields, $pID);
							}

							if($get_fields_count > 1){
								add_post_meta($pID, $data_key, $attachmentId);	
							}
							else{
								update_post_meta($pID, $data_key, $attachmentId);	
							}
						}	
					}
					elseif($field_type == 'file_input'){
						$attachmentId = MetaBoxImport::$media_instance->media_handling($data_value, $pID);
						$get_file_url = $wpdb->get_var("SELECT guid FROM {$wpdb->prefix}posts WHERE post_type = 'attachment' AND ID = $attachmentId");
						update_post_meta($pID, $data_key, $get_file_url);
					}
					elseif($field_type == 'password'){
						$data_value = wp_hash_password($data_value);
						update_post_meta($pID, $data_key, $data_value);
					}
					elseif($field_type == 'post' || $field_type == 'user' || $field_type == 'taxonomy'){
						if(is_numeric($data_value)){
							update_post_meta($pID, $data_key, $data_value);
						}
						else{
							if($field_type == 'post'){
								$get_post_id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}posts WHERE post_title = '$data_value' AND post_status != 'trash' ");
							}
							elseif($field_type == 'user'){
								$get_post_id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}users WHERE user_login = '$data_value' ");
							}
							elseif($field_type == 'taxonomy'){
								$get_post_id = $wpdb->get_var("SELECT term_id FROM {$wpdb->prefix}terms WHERE name = '$data_value' ");
							}
							update_post_meta($pID, $data_key, $get_post_id);
						}	
					}
					$listTaxonomy = get_taxonomies();
					if($data_array){
						if($type == 'Users'){
							foreach($data_array as $data_key => $data_value){
								update_user_meta($pID,$data_key,$data_value,$uploads_fields );
							}
						}
						
						elseif(in_array($type, $listTaxonomy)){
							foreach($data_array as $data_key => $data_value){
								update_term_meta($pID,$data_key,$data_value,$uploads_fields );
							}
						}
						else{
							foreach($data_array as $data_key => $data_value){
								update_post_meta($pID,$data_key,$data_value, $uploads_fields );
							}
						}
					}
					else{
						update_post_meta($pID, $data_key, $data_value,$get_file_url );
					}
				}
			}
		}
	}
	}
	public function metabox_clone_import ($data_array, $pID,$type,$data_key,$field_type,$is_multiple,$timestamp,$max_item,$options,$line_number, $hash_key, $get_import_type,$gmode,$templatekey, $customtable = null) {
		global $wpdb;		
		$helpers_instance = ImportHelpers::getInstance();
		$media_instance = MediaHandling::getInstance();
		$extension_object = new MetaBoxExtension;
		$import_as = $extension_object->import_post_types($type );
		$listTaxonomy = get_taxonomies();

		$value_array = explode('|',$data_array);
		$count = 0;
		foreach($value_array as $fvalue){			
			switch($field_type){
				case 'date':
					case 'datetime':
						{							
							$dateformat = $field_type == 'date' ? "Y-m-d" : "Y-m-d H:i:s";
							$date_arr = array();
							if($timestamp) {								
								$date = $helpers_instance->validate_datefield($fvalue,$data_key,$dateformat,$line_number);				
								if(!empty($date)){																		
									$field_arr[] = strtotime($date);
								}
							}
							else {
								$date = $helpers_instance->validate_datefield($fvalue,$data_key,$dateformat,$line_number);				
								if(!empty($date))
									$field_arr[] = $date;									
							}
							break;
						}
					case 'checkbox_list':
					case 'autocomplete':
					case 'text_list':
						{                                
							$field_arr[] = explode(',',$fvalue); 
							break;
						}
					case 'checkbox':
						{                           
							if($fvalue)
							$field_arr[] = $fvalue;                                							
							break;
						}
					case 'fieldset_text':
						{							
							if(!empty($options)){								
							$fieldset_keys = array_keys($options);							
							$fieldset_values = explode(',',$fvalue);							
							$fieldset_arr = array_combine($fieldset_keys,$fieldset_values);
							$field_arr[] = $fieldset_arr;	
							}						
							break;
						}	
					//case 'image':
					//case 'file':
					case 'file_advanced':
					case 'file_upload':					
					case 'video':
						{							
							$media_fd = explode(',',$fvalue);
							$media_arr = array();
							foreach($media_fd as $data){
								if(is_numeric($data)){
									$media_arr[] = $data;
								}
								else {
									$attachmentId = $media_instance->media_handling($data, $pID);
									if($attachmentId)
										$media_arr[] = $attachmentId;
								}
							}							
								$field_arr[] = $media_arr;							
							break;
						}
					case 'image_upload':
					case 'image_advanced':
						{							
							$media_fd = explode(',',$fvalue);
							$media_arr = array();
							foreach($media_fd as $data){
								if(is_numeric($data)){
									$media_arr[] = $data;
								}
								else {									
									$attachmentId = MetaBoxImport::$media_instance->image_meta_table_entry('', $pID, $data_key, $data, $hash_key, 'metabox_image_clone', $get_import_type, $templatekey, $gmode);
									if($attachmentId)
										$media_arr[] = $attachmentId;
								}
							}							
								$field_arr[] = $media_arr;							
							break;
						}
					case 'single_image': {
						if(is_numeric($fvalue)){
							$field_arr[] = $fvalue;	
						}
						else {
							//$attachmentId = $media_instance->media_handling($fvalue, $pID);
							$attachmentId = MetaBoxImport::$media_instance->image_meta_table_entry('', $pID, $data_key, $fvalue, $hash_key, 'metabox_clone', $get_import_type, $templatekey, $gmode);
							if($attachmentId)
							$field_arr[] = $attachmentId;
						}
						break;
					}
					case 'file_input': {
						if(is_numeric($fvalue)){
							$url = $wpdb->get_var("select guid from {$wpdb->prefix}posts where id = $fvalue");
							if(!empty($url)){
								$field_arr[] = $url;
							}
						}
						else {
							$field_arr[] = $fvalue;							
						}
						break;
					}
					case 'post':
						{
							$post_field_data = array();
							if($is_multiple){
								$post_fd = explode(',',$fvalue);
								foreach($post_fd as $value){
									if(is_numeric($value)){
										$id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}posts WHERE id = $value AND post_status != 'trash' ");
										if($id)
											$post_field_data[] = $id;
									}
									else {
										$id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}posts WHERE post_title = '$value' AND post_status != 'trash' ");
										if($id)
											$post_field_data[] = $id;
									}
								}
								$field_arr[] = $post_field_data;

							}
							else {								
							if(is_numeric($fvalue)){
								$id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}posts WHERE id = $fvalue AND post_status != 'trash' ");
								if($id) // Check it exists or not
								$field_arr[] = $fvalue;
							}
							else {
								$id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}posts WHERE post_title = '$fvalue' AND post_status != 'trash' ");
								if($id)
								$field_arr[] = $id;
							}
						}						
							break;
						}
					case 'user':
						{
							$user_field_data = array();
							if($is_multiple){
								$user_fd = explode(',',$fvalue);
								foreach($user_fd as $value){
									if(is_numeric($value)){
										$id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}users WHERE id = $value");
										if($id)
											$user_field_data[] = $id;
									}
									else {
										$id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}users WHERE user_login = '$value' ");
										if($id)
											$user_field_data[] = $id;
									}
								}
								$field_arr[] = $user_field_data;
							}
							else{
							if(is_numeric($fvalue)){
								$id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}users WHERE id = $fvalue");
								if($id) // Check it exists or not
								$field_arr[] = $fvalue;
							}
							else {
								$id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}users WHERE user_login = '$fvalue' ");
								if($id)
								$field_arr[] = $id;
							}
						}
							break;
						}
					case 'taxonomy':
						{
							$term_field_data = array();
							if($is_multiple){
								$term_fd = explode(',',$fvalue);								
								foreach($term_fd as $value){
									if(is_numeric($value)){
										$id = $wpdb->get_var("SELECT term_id FROM {$wpdb->prefix}terms WHERE term_id = $value");
										if($id)
											$term_field_data[] = $id;
									}
									else {
										$id = $wpdb->get_var("SELECT term_id FROM {$wpdb->prefix}terms WHERE name = '$value' ");
										if($id)
											$term_field_data[] = $id;
									}
								}
								$field_arr[] = $term_field_data;
							}
							else {
							if(is_numeric($fvalue)){
								$id = $wpdb->get_var("SELECT term_id FROM {$wpdb->prefix}terms WHERE term_id = $fvalue");
								if($id) // Check it exists or not
								$field_arr[] = $fvalue;
							}
							else {
								$id = $wpdb->get_var("SELECT term_id FROM {$wpdb->prefix}terms WHERE name = '$fvalue' ");
								$field_arr[] = $id;
							}
						}
							break;
						}			
					default:
					{					
						if($is_multiple){
							$field_arr[] = explode(',',$fvalue);
						}
						else {												
							$field_arr[] = $fvalue;												
						}
						break;
					}
			}
			$count++;
			if($max_item == $count){
				break;
			}
			else
				continue;
		}
		
		if($customtable){
			if(is_array($field_arr))
				$field_arr = serialize($field_arr);
			$id = $wpdb->get_var("select * from $customtable where ID = $pID");
				if($id){									
					$wpdb->update($customtable,
					array($data_key => $field_arr),
					array("ID" => $pID));
				}													
				else {
				$wpdb->insert($customtable,
				array("ID" => $pID,
				"$data_key" => $field_arr),
				array('%d','%s'));
				}
		}
		else {
		if($import_as == 'user')
			update_user_meta($pID,$data_key,$field_arr);
		elseif(in_array($import_as, $listTaxonomy)){
			update_term_meta($pID,$data_key,$field_arr);
		}
		else
			update_post_meta($pID, $data_key, $field_arr);						
	}				
	}

	public function importFieldsCustomTable($data_key,$data_value,$fieldData,$pID,$hash_key,$line_number,$type,$get_import_type,$gmode,$templatekey){
		$storage_type = $fieldData[$data_key]['storage'];
		$customtable = $storage_type->table;
		$field_type = $fieldData[$data_key]['type'];
		$is_multiple = isset($fieldData[$data_key]['multiple']) ? $fieldData[$data_key]['multiple'] : 0;
		$timestamp = isset($fieldData[$data_key]['timestamp']) ? $fieldData[$data_key]['timestamp'] : 0;
		$options = isset($fieldData[$data_key]['options']) ? $fieldData[$data_key]['options'] : "";					
		$field_arr = "";

		$clonable = $fieldData[$data_key]['clone'];
		global $wpdb;
		$helpers_instance = ImportHelpers::getInstance();
		$media_instance = MediaHandling::getInstance();

		if($clonable){
			$max_item = $fieldData[$data_key]['max_clone'];
			$this->metabox_clone_import($data_value,$pID,$type,$data_key,$field_type,$is_multiple,$timestamp,$max_item,$options,$line_number, $hash_key, $get_import_type,$gmode,$templatekey, $customtable);
		}

		else {
		switch($field_type){
			case 'date':
			case 'datetime':
					{							
						$dateformat = $field_type == 'date' ? "Y-m-d" : "Y-m-d H:i:s";						
						$date_arr = array();
						if($timestamp) {								
							$date = $helpers_instance->validate_datefield($data_value,$data_key,$dateformat,$line_number);				
							if(!empty($date)){																		
								$field_arr = strtotime($date);
							}
						}
						else {
							$date = $helpers_instance->validate_datefield($data_value,$data_key,$dateformat,$line_number);				
							if(!empty($date))
								$field_arr = $date;									
						}
						break;
					}
			case 'checkbox_list':
			case 'autocomplete':
			case 'text_list':
					{                                
						$field_arr = explode(',',$data_value); 
						break;
					}
				case 'checkbox':
					{                           
						if($data_value)
						$field_arr = $data_value;      							
						break;
					}
				case 'fieldset_text':
					{								
						if(!empty($options)){								
						$fieldset_keys = array_keys($options);							
						$fieldset_values = explode(',',$data_value);							
						$fieldset_arr = array_combine($fieldset_keys,$fieldset_values);
						$field_arr = $fieldset_arr;	
						}						
						break;
					}	
				//case 'image':
				//case 'file':
				case 'file_advanced':
				case 'file_upload':										
				case 'video':
					{							
						$media_fd = explode(',',$data_value);
						$media_arr = array();
						foreach($media_fd as $data){
							if(is_numeric($data)){
								$media_arr[] = $data;
							}
							else {
								$attachmentId = $media_instance->media_handling($data, $pID);
								if($attachmentId)
									$media_arr[] = $data;
							}
						}														
							$field_arr = $media_arr;							
						break;
					}
				case 'image_upload':
				case 'image_advanced':
					{							
						$media_fd = explode(',',$data_value);
						$media_arr = array();
						foreach($media_fd as $data){
							if(is_numeric($data)){
								$media_arr[] = $data;
							}
							else {
								$attachmentId = $media_instance->media_handling($data, $pID);								
								if($attachmentId)
									$media_arr[] = $attachmentId;
							}
						}							
							$field_arr = $media_arr;							
						break;
					}
				case 'single_image': {
					if(is_numeric($data_value)){
						$field_arr = $data_value;	
					}
					else {
						$attachmentId = $media_instance->media_handling($data_value, $pID);						
						if($attachmentId)
						$field_arr = $attachmentId;
					}
					break;
				}
				case 'file_input': {
					if(is_numeric($data_value)){
						$url = $wpdb->get_var("select guid from {$wpdb->prefix}posts where id = $data_value");
						if(!empty($url)){
							$field_arr = $url;
						}
					}
					else {
						$field_arr = $data_value;							
					}
					break;
				}				
				case 'post':
						{
							$post_field_data = array();
							if($is_multiple){
								$post_fd = explode(',',$data_value);
								foreach($post_fd as $value){
									if(is_numeric($value)){
										$id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}posts WHERE id = $value AND post_status != 'trash' ");
										if($id)
											$post_field_data[] = $id;
									}
									else {
										$id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}posts WHERE post_title = '$value' AND post_status != 'trash' ");
										if($id)
											$post_field_data[] = $id;
									}
								}
								$field_arr = $post_field_data;

							}
							else {								
							if(is_numeric($data_value)){
								$id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}posts WHERE id = $data_value AND post_status != 'trash' ");
								if($id) // Check it exists or not
								$field_arr = $data_value;
							}
							else {
								$id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}posts WHERE post_title = '$data_value' AND post_status != 'trash' ");
								if($id)
								$field_arr = $id;
							}
						}						
							break;
						}
					case 'user':
						{
							$user_field_data = array();
							if($is_multiple){
								$user_fd = explode(',',$data_value);
								foreach($user_fd as $value){
									if(is_numeric($value)){
										$id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}users WHERE id = $value");
										if($id)
											$user_field_data[] = $id;
									}
									else {
										$id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}users WHERE user_login = '$value' ");
										if($id)
											$user_field_data[] = $id;
									}
								}
								$field_arr = $user_field_data;
							}
							else{
							if(is_numeric($data_value)){
								$id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}users WHERE id = $data_value");
								if($id) // Check it exists or not
								$field_arr = $id;
							}
							else {
								$id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}users WHERE user_login = '$data_value' ");
								if($id)
								$field_arr = $id;
							}
						}
							break;
						}
					case 'taxonomy':
						{
							$term_field_data = array();
							if($is_multiple){
								$term_fd = explode(',',$data_value);								
								foreach($term_fd as $value){
									if(is_numeric($value)){
										$id = $wpdb->get_var("SELECT term_id FROM {$wpdb->prefix}terms WHERE term_id = $value");
										if($id)
											$term_field_data[] = $id;
									}
									else {
										$id = $wpdb->get_var("SELECT term_id FROM {$wpdb->prefix}terms WHERE name = '$value' ");
										if($id)
											$term_field_data[] = $id;
									}
								}
								$field_arr = $term_field_data;
							}
							else {
							if(is_numeric($data_value)){
								$id = $wpdb->get_var("SELECT term_id FROM {$wpdb->prefix}terms WHERE term_id = $data_value");
								if($id) // Check it exists or not
								$field_arr = $id;
							}
							else {
								$id = $wpdb->get_var("SELECT term_id FROM {$wpdb->prefix}terms WHERE name = '$data_value' ");
								$field_arr = $id;
							}
						}
							break;
						}	
			default : 
			{
				if($is_multiple){
					$field_arr = explode(',',$data_value);
				}
				else {												
					$field_arr = $data_value;												
				}
				break;
			}
		}

		if(is_array($field_arr))
			$field_arr = serialize($field_arr);					
				
				$id = $wpdb->get_var("select * from $customtable where ID = $pID");
				if($id){									
					$wpdb->update($customtable,
					array($data_key => $field_arr),
					array("ID" => $pID));
				}													
				else {
				$wpdb->insert($customtable,
				array("ID" => $pID,
				"$data_key" => $field_arr),
				array('%d','%s'));
				}
			}
									
	}
}