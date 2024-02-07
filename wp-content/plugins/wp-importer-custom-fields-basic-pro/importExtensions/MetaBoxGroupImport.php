<?php
/******************************************************************************************
 * Copyright (C) Smackcoders. - All Rights Reserved under Smackcoders Proprietary License
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/

namespace Smackcoders\CFCSV;

if (!defined('ABSPATH')) exit; // Exit if accessed directly
class MetaBoxGroupImport
{
    private static $metabox_group_instance = null, $media_instance;

    public static function getInstance()
    {

        if (MetaBoxGroupImport::$metabox_group_instance == null)
        {
            MetaBoxGroupImport::$metabox_group_instance = new MetaBoxGroupImport;
            MetaBoxGroupImport::$media_instance = new MediaHandling();
            return MetaBoxGroupImport::$metabox_group_instance;
        }
        return MetaBoxGroupImport::$metabox_group_instance;
    }
    function set_metabox_group_values($header_array, $value_array, $map, $post_id, $type, $mode,$line_number)
	{        		
        $post_values = [];
        $helpers_instance = ImportHelpers::getInstance();
        $post_values = $helpers_instance->get_header_values($map, $header_array, $value_array);

        $this->metabox_group_import_function($post_values, $post_id, $header_array, $value_array, $type, $mode,$line_number);
    }

    public function metabox_group_import_function($data_array, $pID, $header_array, $value_array, $type, $mode,$line_number)
    {					
        global $wpdb;
		$helpers_instance = ImportHelpers::getInstance();
		$media_instance = MediaHandling::getInstance();
		$extension_object = new MetaBoxGroupExtension;
		$import_as = $extension_object->import_post_types($type );	
		$listTaxonomy = get_taxonomies();
		$grpfields = array();				

		if($import_as == 'user')		{
			$get_metabox_fields = \rwmb_get_object_fields( $import_as,$import_as); 
		}
		elseif(in_array($import_as, $listTaxonomy)){
			$get_metabox_fields = \rwmb_get_object_fields( $import_as,'term'); 
		}
		else{
			$get_metabox_fields = \rwmb_get_object_fields($import_as); 						
		}
		
		$clonable = $max_item = []; 		
		
        foreach($get_metabox_fields as $grpkey => $fvalue ){
            if($fvalue['type'] == 'group') {
                $grpfields[$grpkey] = $fvalue['fields']; 
				$clonable[$grpkey] = $fvalue['clone'];
				if($clonable[$grpkey])
					$max_item[$grpkey] = $fvalue['max_clone'] ? $fvalue['max_clone'] : 10;
            }
        }   

		foreach($grpfields as $grpkey => $field_data) {				//$grpfields is parent grp
			$grpvalues = array();					
			if($clonable[$grpkey]){									
					$get_grpfield_arr = $this->groupImportProcess($field_data,$data_array,$grpvalues,$grpkey,$line_number,$pID,0,$max_item[$grpkey],true);															
			}
			else
			$get_grpfield_arr = $this->groupImportProcess($field_data,$data_array,$grpvalues,$grpkey,$line_number,$pID,0,$max_item[$grpkey],false);															
						
			if(empty($get_grpfield_arr))
				$grpfield_arr[$grpkey] = "";
			else
				$grpfield_arr = $get_grpfield_arr;				
		}												

		if(!empty($grpfield_arr) )
			{ 							
				$customtable_flag = 0;
				$meta_box_registry = \rwmb_get_registry( 'meta_box' );				
				$args = [				
					'object_type' => 'post',
					'post_types' => [$import_as],
				];
				$metabox_groups = $meta_box_registry->get_by( $args );
				
				foreach($metabox_groups as $metagroupid => $gmeta)
				{
					$eachgroup = "";
					$eachgroup = $gmeta->meta_box;
					if(array_key_exists('storage_type',$eachgroup) && $eachgroup['storage_type'] == 'custom_table'
					&& array_key_exists('table',$eachgroup) ){
						$custom_meta_table = $eachgroup['table'];
						$customtable_flag = 1;
						$metagrp_fields = $eachgroup['fields'];							
						
						foreach($metagrp_fields as $key => $meta_fieldData){
							$metafield_key =$meta_fieldData['id'];								
							foreach($grpfield_arr as $grpkey => $field_arr){					
							if($grpkey == $metafield_key){																
								$field_arr = serialize($field_arr);
								
								
								$id = $wpdb->get_var("select * from $custom_meta_table where ID = $pID");
								if($id){									
									$wpdb->update($custom_meta_table,
									array($grpkey => $field_arr),
									array("ID" => $pID));
								}													
								else {
								$wpdb->insert($custom_meta_table,
								array("ID" => $pID,
								"$grpkey" => $field_arr),
								array('%d','%s'));
								}
							}						
						}
						}
					}					
				}				
				if(!$customtable_flag){	
					foreach($grpfield_arr as $grpkey => $field_arr)	{
				if($import_as == 'user')
					update_user_meta($pID,$grpkey,$field_arr);
				elseif(in_array($import_as, $listTaxonomy)){
					update_term_meta($pID,$grpkey,$field_arr);
				}
				else
					update_post_meta($pID, $grpkey, $field_arr);
			}
		}
		
        }		
	}


	public function groupImportProcess($grpfields,$data_array,$group_fdvalues,$grpkey,$line_number,$pID,$clonecount,$grpclone,$is_clone){
        static $finaldata;
		static $group_fdvalues;
		$new_arr = array();
		$grp_field_values = [];	

		if(!$is_clone){
			foreach($grpfields as $row => $rowvalues){ // Number of fields in group $row				 
				$field_key = $rowvalues['id'];
				
				if($rowvalues['type'] == 'group') {					
					$group_fdvalues[$grpkey][$field_key] = $this->process_grpfields($data_array,$rowvalues,$line_number,$pID);					
				}
				
				if(array_key_exists($field_key,$data_array) && !empty($data_array[$field_key])){																						
                    $grp_field_values = explode('|',$data_array[$field_key]);					
					$group_fdvalues[$grpkey][$field_key] = $this->process_grpfields($grp_field_values,$rowvalues,$line_number,$pID);					
            }
            }				
			return $group_fdvalues;		
		}

		else {
				
            foreach($grpfields as $row => $rowvalues){ // Number of fields in group $row				 
				$field_key = $rowvalues['id'];
				
				if($rowvalues['type'] == 'group') {					
					$group_fdvalues[$grpkey][$clonecount][$field_key] = $this->process_grpfields($data_array,$rowvalues,$line_number,$pID,$clonecount);					
				}
				
				if(array_key_exists($field_key,$data_array) && !empty($data_array[$field_key])){																						
                    $grp_field_values = explode('|',$data_array[$field_key]);
					if(!count($grp_field_values) || $clonecount == count($grp_field_values)){						
						return 	$group_fdvalues;				
					}
					$group_fdvalues[$grpkey][$clonecount][$field_key] = $this->process_grpfields($grp_field_values,$rowvalues,$line_number,$pID,$clonecount);					
            }
            }	
			$clonecount++;
			
			if($clonecount == $grpclone){
				return $group_fdvalues;
			}
			else {
				$dummy = $this->groupImportProcess($grpfields,$data_array,$group_fdvalues,$grpkey,$line_number,$pID,$clonecount,$grpclone,$is_clone);
			}						
			return $group_fdvalues;	
		}		
           }	
	
public function process_grpfields($grp_field_values,$rowvalues,$line_number,$pID)	 {	
	$helpers_instance = ImportHelpers::getInstance();
	$media_instance = MediaHandling::getInstance();
	global $wpdb;
	$field_type = $rowvalues['type'];
    $field_key = $rowvalues['id'];	
	
	$is_multiple = array_key_exists('multiple',$rowvalues) ? $rowvalues['multiple'] : 0;
	$field_clone = $rowvalues['clone'];

	if($field_clone && $field_type != 'group'){
		$field_arr = $this->process_clonefields($grp_field_values,$rowvalues,$line_number,$pID);		
		
		return $field_arr;
	}

	foreach($grp_field_values as $ckey => $cvalue){						
		switch($field_type) {
			case 'date':
			case 'datetime':
				{
					$timestamp = $rowvalues['timestamp'];
					$dateformat = $field_type == 'date' ? "Y-m-d" : "Y-m-d H:i:s";
					if($timestamp) {
						$date_arr = array();
						$date = $helpers_instance->validate_datefield($cvalue,$field_key,$dateformat,$line_number);				
						if(!empty($date)){
							$date_arr['timestamp'] = strtotime($date);
							$date_arr['formatted'] = $date;
							$field_arr = $date_arr;
						}
					}
					else {
						$date = $helpers_instance->validate_datefield($cvalue,$field_key,$dateformat,$line_number);				
						if(!empty($date))
							$field_arr = $date;									
					}
					break;
				}
			case 'checkbox_list':
			case 'autocomplete':
			case 'text_list':
				{                                
					$field_arr = explode(',',$cvalue); 
					break;
				}
			case 'checkbox':
				{                           
					if($cvalue)
						$field_arr = $cvalue;                                							
					break;
				}
			case 'fieldset_text':
				{
					$fieldset_options = $rowvalues['options'];
					$fieldset_keys = array_keys($fieldset_options);
					$fieldset_values = explode(',',$cvalue);
					$fieldset_arr = array_combine($fieldset_keys,$fieldset_values);
					$field_arr = $fieldset_arr;							
					break;
				}	
			//case 'image':
			case 'image_advanced':
			//case 'file':
			case 'file_advanced':
			case 'file_upload':					
			case 'image_upload':					
			case 'video':
				{							
					$media_fd = explode(',',$cvalue);
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
				if(is_numeric($cvalue)){
					$field_arr = $cvalue;	
				}
				else {
					$attachmentId = $media_instance->media_handling($cvalue, $pID);
					if($attachmentId)
					$field_arr = $attachmentId;
				}
				break;
			}
			case 'file_input': {
				if(is_numeric($cvalue)){
					$url = $wpdb->get_var("select guid from {$wpdb->prefix}posts where id = $cvalue");
					if(!empty($url)){
						$field_arr = $url;
					}
				}
				else {
					$field_arr = $cvalue;							
				}
				break;
			}
			case 'post':
				{
					$post_field_data = array();
					if($is_multiple){
						$post_fd = explode(',',$cvalue);
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
					if(is_numeric($cvalue)){
						$id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}posts WHERE id = $cvalue AND post_status != 'trash' ");
						if($id) // Check it exists or not
						$field_arr = $cvalue;
					}
					else {
						$id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}posts WHERE post_title = '$cvalue' AND post_status != 'trash' ");
						$field_arr = $id;
					}
				}
					break;
				}
			case 'user':
				{
					$user_field_data = array();
					if($is_multiple){
						$user_fd = explode(',',$cvalue);
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
					if(is_numeric($cvalue)){
						$id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}users WHERE id = $cvalue");
						if($id) // Check it exists or not
						$field_arr = $cvalue;
					}
					else {
						$id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}users WHERE user_login = '$cvalue' ");
						$field_arr = $id;
					}
				}
					break;
				}
			case 'taxonomy':
				{
					$term_field_data = array();
					if($is_multiple){
						$term_fd = explode(',',$cvalue);								
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
					if(is_numeric($cvalue)){
						$id = $wpdb->get_var("SELECT term_id FROM {$wpdb->prefix}terms WHERE term_id	 = $cvalue");
						if($id) // Check it exists or not
						$field_arr = $cvalue;
					}
					else {
						$id = $wpdb->get_var("SELECT term_id FROM {$wpdb->prefix}terms WHERE name = '$cvalue' ");
						$field_arr = $id;
					}
				}
					break;
				}

			case 'group':
				{					
					$subfields = $rowvalues['fields'];
					$sub_grpkey = $rowvalues['id'];
					$sub_clone = $rowvalues['clone'];
					$flag = 0;
					if(array_key_exists('max_clone',$rowvalues) && $rowvalues['max_clone']){
						$max_item = $rowvalues[$max_item];
					} 
					else{
						$max_item = 10;
					}
					if($sub_clone){						
						
						
						for($i = 0;$i < $max_item;$i++ ){
							foreach($subfields as $subvalue){
								$sub_id = $subvalue['id'];	
								if(array_key_exists($sub_id,$grp_field_values)){
									$subgrp_field_values = explode('|',$grp_field_values[$sub_id]);	
									$length = count($subgrp_field_values);
									
									if($length == $i){
										$flag = 1;
										break;
										
									}
									$subfield_arr[$i][$sub_id] = $this->process_grpfields($subgrp_field_values,$subvalue,$line_number,$pID);
								}
							}
							if($flag){
								break;
							}
						}						
					}

					else {

					foreach($subfields as $subvalue){
						$sub_id = $subvalue['id'];							
						if(array_key_exists($sub_id,$grp_field_values)){							
							$subgrp_field_values = explode('|',$grp_field_values[$sub_id]);														
							$subfield_arr[$sub_id] = $this->process_grpfields($subgrp_field_values,$subvalue,$line_number,$pID);							
						}
					}	
				}						
					return $subfield_arr;										
				}
			
			default:
			{
				//Basic controls like text,textarea,radio,select and so on.                        
				if($is_multiple){						
					$field_arr = explode(',',$cvalue);
				}
				else{
					
					$field_arr = $cvalue;                        
				}
				break;
			}

		}		
	}
	return $field_arr;
}

public function process_clonefields($grp_field_values,$rowvalues,$line_number,$pID)	 {	
	$helpers_instance = ImportHelpers::getInstance();
	$media_instance = MediaHandling::getInstance();
	global $wpdb;
	$field_type = $rowvalues['type'];
    $field_key = $rowvalues['id'];		
	$is_multiple = array_key_exists('multiple',$rowvalues) ? $rowvalues['multiple'] : 0;	
	$max_item = $rowvalues['max_clone'] ? $rowvalues['max_clone'] : 10;
	$field_arr = array();

	foreach($grp_field_values as $ckey => $cvalue){			
		$cvalue = explode('->',$cvalue);		
		$length = count($cvalue);
		for($i = 0; $i < $length; $i++){
			if($max_item == $i){
				return $field_arr;
			}
		switch($field_type) {
			case 'date':
			case 'datetime':
				{
					$timestamp = $rowvalues['timestamp'];
					$dateformat = $field_type == 'date' ? "Y-m-d" : "Y-m-d H:i:s";
					if($timestamp) {
						$date_arr = array();
						$date = $helpers_instance->validate_datefield($cvalue[$i],$field_key,$dateformat,$line_number);				
						if(!empty($date)){
							$date_arr['timestamp'] = strtotime($date);
							$date_arr['formatted'] = $date;
							$field_arr[$i] = $date_arr;
						}
						else {
							$field_arr[$i] = "";
						}
					}
					else {
						$date = $helpers_instance->validate_datefield($cvalue[$i],$field_key,$dateformat,$line_number);				
						if(!empty($date))
							$field_arr[$i] = $date;
						else
							$field_arr[$i] = "";
					}
					break;
				}
			case 'checkbox_list':
			case 'autocomplete':
			case 'text_list':
				{                                
					$field_arr[$i] = explode(',',$cvalue[$i]); 
					break;
				}
			case 'checkbox':
				{                           					
						$field_arr[$i] = $cvalue[$i];                                							
					break;
				}
			case 'fieldset_text':
				{
					$fieldset_options = $rowvalues['options'];
					$fieldset_keys = array_keys($fieldset_options);
					$fieldset_values = explode(',',$cvalue[$i]);
					$fieldset_arr = array_combine($fieldset_keys,$fieldset_values);
					$field_arr[$i] = $fieldset_arr;							
					break;
				}	
			//case 'image':
			case 'image_advanced':
			//case 'file':
			case 'file_advanced':
			case 'file_upload':					
			case 'image_upload':					
			case 'video':
				{							
					$media_fd = explode(',',$cvalue[$i]);
					$media_arr = array();
					foreach($media_fd as $data){
						if(is_numeric($data)){
							$media_arr[] = $data;
						}
						else {
							$attachmentId = $media_instance->media_handling($data, $pID);
							if($attachmentId)
								$media_arr[] = $attachmentId;
							else
								$media_arr[] = "";
						}
					}							
						$field_arr[$i] = $media_arr;							
					break;
				}
			case 'single_image': {
				if(is_numeric($cvalue[$i])){
					$field_arr[$i] = $cvalue[$i];	
				}
				else {
					$attachmentId = $media_instance->media_handling($cvalue[$i], $pID);
					if($attachmentId)
					$field_arr[$i] = $attachmentId;
					else
					$field_arr[$i] = "";
				}
				break;
			}
			case 'file_input': {
				if(is_numeric($cvalue[$i])){
					$urldata = $cvalue[$i];
					$url = $wpdb->get_var("select guid from {$wpdb->prefix}posts where id = $urldata");
					if(!empty($url)){
						$field_arr[$i] = $url;
					}
					else
						$field_arr[$i] = "";
				}
				else {
					$field_arr[$i] = $cvalue[$i];							
				}
				break;
			}
			case 'post':
				{
					$post_field_data = array();
					if($is_multiple){
						$post_fd = explode(',',$cvalue[$i]);
						foreach($post_fd as $value){
							if(is_numeric($value)){
								$id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}posts WHERE id = $value AND post_status != 'trash' ");
								if($id)
									$post_field_data[] = $id;
								else
									$post_field_data[] = "";
							}
							else {
								$id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}posts WHERE post_title = '$value' AND post_status != 'trash' ");
								if($id)
									$post_field_data[] = $id;
								else
									$post_field_data[] = "";
							}
						}
						$field_arr[$i] = $post_field_data;
					}
					else {
					if(is_numeric($cvalue[$i])){
						$id_data = $cvalue[$i];
						$id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}posts WHERE id = $id_data AND post_status != 'trash' ");
						if($id) // Check it exists or not
						$field_arr[$i] = $id;
						else
						$field_arr[$i] = "";
					}
					else {
						$title_data = $cvalue[$i];
						$id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}posts WHERE post_title = '$title_data' AND post_status != 'trash' ");
						$field_arr[$i] = $id;
					}
				}
					break;
				}
			case 'user':
				{
					$user_field_data = array();
					if($is_multiple){
						$user_fd = explode(',',$cvalue[$i]);
						foreach($user_fd as $value){
							if(is_numeric($value)){
								$id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}users WHERE id = $value");
								if($id)
									$user_field_data[] = $id;
								else
									$user_field_data[] = "";
							}
							else {
								$id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}users WHERE user_login = '$value' ");
								if($id)
									$user_field_data[] = $id;
								else
									$user_field_data[] = "";
							}
						}
						$field_arr[$i] = $user_field_data;
					}
					else{
					if(is_numeric($cvalue[$i])){
						$user_id = $cvalue[$i];
						$id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}users WHERE id = $user_id");
						if($id) // Check it exists or not
						$field_arr[$i] = $id;
						else
						$field_arr[$i] = "";
					}
					else {
						$username = $cvalue[$i];
						$id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}users WHERE user_login = '$username' ");
						$field_arr[$i] = $id;						
					}
				}
					break;
				}
			case 'taxonomy':
				{
					$term_field_data = array();
					if($is_multiple){
						$term_fd = explode(',',$cvalue[$i]);								
						foreach($term_fd as $value){
							if(is_numeric($value)){
								$id = $wpdb->get_var("SELECT term_id FROM {$wpdb->prefix}terms WHERE term_id = $value");
								if($id)
									$term_field_data[] = $id;
								else
									$term_field_data[]= "";
							}
							else {
								$id = $wpdb->get_var("SELECT term_id FROM {$wpdb->prefix}terms WHERE name = '$value' ");
								if($id)
									$term_field_data[] = $id;
								else
									$term_field_data[] = "";
							}
						}
						$field_arr[$i] = $term_field_data;
					}
					else {
					if(is_numeric($cvalue[$i])){
						$term_id = $cvalue[$id];
						$id = $wpdb->get_var("SELECT term_id FROM {$wpdb->prefix}terms WHERE term_id = $term_id");
						if($id) // Check it exists or not
						$field_arr[$i] = $id;
						else
						$field_arr[$i] = "";
					}
					else {
						$term = $cvalue[$i];
						$id = $wpdb->get_var("SELECT term_id FROM {$wpdb->prefix}terms WHERE name = '$term' ");
						$field_arr[$i] = $id;
					}
				}
					break;
				}			
			
			default:
			{
				//Basic controls like text,textarea,radio,select and so on.                        
				if($is_multiple){						
					$field_arr[$i] = explode(',',$cvalue[$i]);
				}
				else{					
					$field_arr[$i] = $cvalue[$i];                        
				}
				break;
			}

		}
		}		
	}
	return $field_arr;
}

}

