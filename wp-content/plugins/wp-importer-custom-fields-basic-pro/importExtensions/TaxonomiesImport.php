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

class TaxonomiesImport {
	private static $taxonomies_instance = null;

	public static function getInstance() {

		if (TaxonomiesImport::$taxonomies_instance == null) {
			TaxonomiesImport::$taxonomies_instance = new TaxonomiesImport;
			return TaxonomiesImport::$taxonomies_instance;
		}
		return TaxonomiesImport::$taxonomies_instance;
	}

	public function taxonomies_import_function ($data_array, $mode, $importType , $unmatched_row, $check , $hash_key , $line_number ,$header_array ,$value_array,$gmode,$templatekey) {
		global $wpdb,$core_instance;
		$returnArr = array();
		$mode_of_affect = 'Inserted';
		$helpers_instance = ImportHelpers::getInstance();
		$core_instance = CoreFieldsImport::getInstance();
		$media_instance = MediaHandling::getInstance();
		$log_table_name = $wpdb->prefix ."cfimport_detail_log";

		$unikey_name = 'hash_key';
		$unikey_value = $hash_key;

		if($gmode == 'CLI'){ //Exchange the hashkey value with template key
			$unikey_name = 'templatekey';
			$unikey_value = ($templatekey != null) ? $templatekey : '';
		}

		$updated_row_counts = $helpers_instance->update_count($unikey_value,$unikey_name);
		$created_count = $updated_row_counts['created'];
		$updated_count = $updated_row_counts['updated'];
		$skipped_count = $updated_row_counts['skipped'];		
		$terms_table = $wpdb->prefix ."term_taxonomy";
		$taxonomy = $importType;		
		$term_children_options = get_option("$taxonomy" . "_children");
		$_name = isset($data_array['name']) ? $data_array['name'] : '';
		$_slug = isset($data_array['slug']) ? $data_array['slug'] : '';
		$_desc = isset($data_array['description']) ? $data_array['description'] : '';
		$_image = isset($data_array['image']) ? $data_array['image'] : '';
		$_parent = isset($data_array['parent']) ? $data_array['parent'] : '';
		$_display_type = isset($data_array['display_type']) ? $data_array['display_type'] : '';
		$termID = '';
		
		$get_category_list = array();
		if (strpos($_name, ',') !== false) {
			$get_category_list = explode(',', $_name);
		} elseif (strpos($_name, '>') !== false) {
			$get_category_list = explode('>', $_name);
		} else {
			$get_category_list[] = trim($_name);
		}

		$parent_term_id = 0;
		if (count($get_category_list) == 1) {
			$_name = trim($get_category_list[0]);
			if($_parent){
				$get_parent = term_exists("$_parent", "$taxonomy");
				$parent_term_id = $get_parent['term_id'];
			}
			else{
				$termid_value = $wpdb->get_results("SELECT term_id FROM {$wpdb->prefix}terms WHERE slug = '$_slug'");
				if(!empty($termid_value[0]->term_id)){
					$termid_val = $termid_value[0]->term_id;
					$term_parent_value = $wpdb->get_results("SELECT parent FROM {$wpdb->prefix}term_taxonomy WHERE term_id = '$termid_val'");
					$parent_term_id = $term_parent_value[0]->parent;
				}
			}
		} else {
			$count = count($get_category_list);
			$_name = trim($get_category_list[$count - 1]);
			$checkParent = trim($get_category_list[$count - 2]);
			$parent_term = term_exists("$checkParent", "$taxonomy");
			$parent_term_id = $parent_term['term_id'];
		}

		if($check == 'termid'){
			$termID = $data_array['TERMID'];
		}
		if($check == 'slug'){
			$get_termid = get_term_by( "slug" ,"$_slug" , "$taxonomy");
			if(isset($get_termid->term_id)){
				$termID = $get_termid->term_id;
			}
		}	
		if($_display_type){
			$_display_type = $_display_type;
		}else{
			$term_id_value = $wpdb->get_results("SELECT term_id FROM {$wpdb->prefix}terms WHERE slug = '$_slug'");
			if(!empty($term_id_value[0]->term_id)){
				$term_id_val = $term_id_value[0]->term_id;
				$term_display_type_value = $wpdb->get_results("SELECT display_type FROM {$wpdb->prefix}termmeta WHERE term_id = '$term_id_val'");
				$_display_type = $term_display_type_value[0]->display_type;
			}
		}
		if($mode == 'Insert'){
			if(!empty($termID)){

				$core_instance->detailed_log[$line_number]['Message'] = "Skipped, Due to duplicate Term found!.";
				$wpdb->get_results("UPDATE $log_table_name SET skipped = $skipped_count WHERE $unikey_name  = '$unikey_value'");
				return array('MODE' => $mode, 'ERROR_MSG' => 'The term already exists!');
			}else{
				$taxoID = wp_insert_term("$_name", "$taxonomy", array('description' => $_desc, 'slug' => $_slug));
				if(is_wp_error($taxoID)){
					$core_instance->detailed_log[$line_number]['Message'] = "Can't insert this " . $taxonomy . ". " . $taxoID->get_error_message();
					$wpdb->get_results("UPDATE $log_table_name SET skipped = $skipped_count WHERE $unikey_name  = '$unikey_value'");
				}else{

					$termID = $taxoID['term_id'];
					$wpdb->get_results("UPDATE {$wpdb->prefix}term_taxonomy SET `parent` = $parent_term_id WHERE `term_id` =$termID ");
					$returnArr = array('ID' => $termID, 'MODE' => $mode_of_affect);

					if(isset($_display_type)){
						add_term_meta($termID , 'display_type' , $_display_type);
					}

					if(isset($parent_term_id)){
						$wpdb->get_results("UPDATE $terms_table SET `parent` = $parent_term_id WHERE `term_id` = $termID ");
					}	
					$returnArr = array('ID' => $termID, 'MODE' => $mode_of_affect);

					if($importType = 'wpsc_product_category'){
						$img_name = '';
						$market = '';
						if(isset($data_array['category_image'])){
							$udir = wp_upload_dir();
							$imgurl = $data_array['category_image'];
							$img_name = basename($imgurl);
							$uploadpath = $udir['basedir'] . "/wpsc/category_images/";
						}
						if(isset($data_array['target_market'])){
							$custom_market = explode(',', $data_array['target_market']);
							foreach ($custom_market as $key =>$value) {
								$market[$value - 1] = $value;
							}					
						}
						$data_array['address_calculate'] = isset($data_array['address_calculate']) ? $data_array['address_calculate'] :'';
						$data_array['category_image_width'] = isset($data_array['category_image_width']) ? $data_array['category_image_width'] :'';
						$data_array['category_image_height'] =isset($data_array['category_image_height']) ? $data_array['category_image_height'] :'';
						$data_array['catelog_view'] = isset($data_array['catelog_view']) ? $data_array['catelog_view'] :'';
						
						$meta_data = array('uses_billing_address' => $data_array['address_calculate'],'image' => $img_name,'image_width' => $data_array['category_image_width'],'image_height' => $data_array['category_image_height'],'display_type'=>$data_array['catelog_view'],'target_market'=>serialize($market));
						foreach($meta_data as $mk => $mv){
							
						}
					}
					$core_instance->detailed_log[$line_number]['Message'] = 'Inserted ' . $taxonomy . ' ID: ' . $termID;
					$wpdb->get_results("UPDATE $log_table_name SET created = $created_count WHERE $unikey_name  = '$unikey_value'");
				}
			}
			if($unmatched_row == 'true'){
				global $wpdb;
				$post_entries_table = $wpdb->prefix ."post_entries_table";
				$file_table_name = $wpdb->prefix."smackcf_file_events";
				$get_id  = $wpdb->get_results( "SELECT file_name  FROM $file_table_name WHERE `hash_key` = '$hash_key'");	
				$file_name = $get_id[0]->file_name;
				
				$wpdb->get_results("INSERT INTO $post_entries_table (`ID`,`type`, `file_name`,`status`) VALUES ( '{$termID}','{$type}', '{$file_name}','Inserted')");
			}
		} else {
			//if($mode == 'Import-Update') {
				if(!empty($termID)){

					wp_update_term($termID, "$taxonomy", array('name' => $_name, 'slug' => $_slug, 'description' => $_desc));
					$wpdb->get_results("UPDATE {$wpdb->prefix}term_taxonomy SET `description` = '$_desc' WHERE `term_id` = '$termID'");
					$wpdb->get_results("UPDATE {$wpdb->prefix}term_taxonomy SET `parent` = $parent_term_id WHERE `term_id` =$termID ");
					//start of added for adding thumbnail
					if(isset($_image)){
						$_image = trim($_image);						
						$img = $media_instance->image_meta_table_entry('', $termID, 'thumbnail_id', $_image, $hash_key, 'term', 'term',$templatekey);
						update_term_meta($termID , 'thumbnail_id' , $img); 
					}
					if(isset($_display_type)){
						update_term_meta($termID , 'display_type' , $_display_type);
					}	

					if(isset($parent_term_id)){
						$wpdb->get_results("UPDATE $terms_table SET `parent` = $parent_term_id WHERE `term_id` = $termID ");
					}

					//end of added for adding thumbnail
					//start wpsc_product_category meta fields
					if($importType = 'wpsc_product_category'){
						if(isset($data_array['category_image'])){
							$udir = wp_upload_dir();
							$imgurl = $data_array['category_image'];
							$img_name = basename($imgurl);
							$uploadpath = $udir['basedir'] . "/wpsc/category_images/";
						}
						if(isset($data_array['target_market'])){
							$custom_market = explode(',', $data_array['target_market']);
							foreach ($custom_market as $key =>$value) {
								$market[$value - 1] = $value;
							}
						}
						$data_array['address_calculate'] = isset($data_array['address_calculate'])?$data_array['address_calculate']:'';
						$data_array['category_image_width'] = isset($data_array['category_image_width'])?$data_array['category_image_width']:'';
						$data_array['category_image_height'] = isset($data_array['category_image_height'])?$data_array['category_image_height']:'';
						$data_array['catelog_view'] = isset($data_array['catelog_view'])?$data_array['catelog_view']:'';
						$market = isset($market)?$market:''; 
						$img_name = isset($img_name)?$img_name:'';
						$meta_data = array('uses_billing_address' => $data_array['address_calculate'],'image' => $img_name,'image_width' => $data_array['category_image_width'],'image_height' => $data_array['category_image_height'],'display_type'=>$data_array['catelog_view'],'target_market'=>serialize($market));
						foreach($meta_data as $mk => $mv){
						}
					}
					//end wpsc_product_category meta fields
					$mode_of_affect = 'Updated';		
					$returnArr = array('ID' => $termID, 'MODE' => $mode_of_affect);	
					if($unmatched_row == 'true'){
						global $wpdb;
						$post_entries_table = $wpdb->prefix ."post_entries_table";
						$file_table_name = $wpdb->prefix."smackcf_file_events";
						$get_id  = $wpdb->get_results( "SELECT file_name  FROM $file_table_name WHERE `hash_key` = '$hash_key'");	
						$file_name = $get_id[0]->file_name;
						$wpdb->get_results("INSERT INTO $post_entries_table (`ID`,`type`, `file_name`,`status`) VALUES ( '{$termID}','{$type}', '{$file_name}','Updated')");
					}

					$core_instance->detailed_log[$line_number]['Message'] = 'Updated ' . $taxonomy . ' ID: ' . $termID;
					$wpdb->get_results("UPDATE $log_table_name SET updated = $updated_count WHERE $unikey_name  = '$unikey_value'");

				}else{
					if($mode == 'Import-Update'){
					 	$taxoID = wp_insert_term("$_name", "$taxonomy", array('description' => $_desc, 'slug' => $_slug));

					 	$termID = $taxoID['term_id'];

				        if(isset($parent_term_id)){
					    	$wpdb->get_results("UPDATE {$wpdb->prefix}term_taxonomy SET `parent` = $parent_term_id WHERE `term_id` =$termID ");
					    }
					 	$returnArr = array('ID' => $termID, 'MODE' => $mode_of_affect);

						if($unmatched_row == 'true'){
							global $wpdb;
							$post_entries_table = $wpdb->prefix ."post_entries_table";
							$file_table_name = $wpdb->prefix."smackcf_file_events";
							$get_id  = $wpdb->get_results( "SELECT file_name  FROM $file_table_name WHERE `hash_key` = '$hash_key'");	
							$file_name = $get_id[0]->file_name;
							$wpdb->get_results("INSERT INTO $post_entries_table (`ID`,`type`, `file_name`,`status`) VALUES ( '{$termID}','{$type}', '{$file_name}','Inserted')");
						}
					 	$core_instance->detailed_log[$line_number]['Message'] = 'Inserted ' . $taxonomy . ' ID: ' . $termID;
						$wpdb->get_results("UPDATE $log_table_name SET created = $created_count WHERE $unikey_name  = '$unikey_value'");

					}
					else{
						if(isset($parent_term_id)){
							$update = $wpdb->get_results("UPDATE $terms_table SET `parent` = $parent_term_id WHERE `term_id` =$termID ");
							
							$mode_of_affect = 'Updated';
							$returnArr = array('ID' => $termID, 'MODE' => $mode_of_affect);

						    $core_instance->detailed_log[$line_number]['Message'] = 'Updated ' . $taxonomy . ' ID: ' . $termID;
						    $wpdb->get_results("UPDATE $log_table_name SET created = $created_count WHERE $unikey_name  = '$unikey_value'");
						}else{
						    $core_instance->detailed_log[$line_number]['Message'] = "Skipped." ;
						    $wpdb->get_results("UPDATE $log_table_name SET created = $skipped_count WHERE $unikey_name  = '$unikey_value'");
						    return array('MODE' => $mode);
						}
						
					}
				}
			//} 
		}

		if(!is_wp_error($termID)) {
			update_option("$taxonomy" . "_children", $term_children_options);
			delete_option($taxonomy . "_children");
		}
		return $returnArr;
	}
}
