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

class ACFImageMetaExtension extends ExtensionHandler{
	private static $instance = null;

    public static function getInstance() {
		
		if (ACFImageMetaExtension::$instance == null) {
			ACFImageMetaExtension::$instance = new ACFImageMetaExtension;
		}
		return ACFImageMetaExtension::$instance;
	}
	
	/**
	* Provides ACF Image Meta mapping fields for specific post type
	* @param string $data - selected import type
	* @return array - mapping fields
	*/
    public function processExtension($data) {
    
        $response = [];
        $import_type = $data;
        $acf_meta_value = '';
        global $wpdb;
        $get_acf_groups = $wpdb->get_results( $wpdb->prepare("SELECT ID, post_content FROM {$wpdb->prefix}posts WHERE post_status != %s AND post_type = %s",'trash', 'acf-field-group'));
		foreach ( $get_acf_groups as $item => $group_rules ) {
			$rule = maybe_unserialize($group_rules->post_content);
			if(!empty($rule)) {
				if ($import_type != 'Users') {
					foreach($rule['location'] as $key => $value) {
                        $importtype=$this->import_post_types($import_type);
						if($value[0]['operator'] == '==' && $value[0]['value'] == $importtype || $value[0]['param'] == 'comment'){	
                           $acf_meta_value = $this->acfmetafields();
						}
						elseif($value[0]['operator'] == '==' && $value[0]['value'] == 'all' && $value[0]['param'] == 'taxonomy' && in_array($importtype , get_taxonomies())){
                             $acf_meta_value = $this->acfmetafields();
						}
					}
				} else { 
					foreach($rule['location'] as $key => $value) {
						if( $value[0]['operator'] == '==' && $value[0]['param'] == 'user_role'){
                        $acf_meta_value = $this->acfmetafields();
                        
						}
					}
				}
			}
		}

		$response['acf_image_meta_fields'] = $acf_meta_value ;
		return $response;
    }

    public function acfmetafields(){
        global $wpdb;
        $get_acf_fields=$wpdb->get_results("SELECT post_content FROM {$wpdb->prefix}posts WHERE post_type='acf-field' AND post_status='publish'");
           
        $array=json_decode(json_encode($get_acf_fields),true);
        $acf_image_meta_Fields = [];
        $acf_gallery_meta_Fields = [];
        foreach($array as $acf_fields=>$acf_field_values){
            $get_acf_fields=unserialize($acf_field_values['post_content']);
            $field_type =$get_acf_fields['type'];
            if($field_type == 'image' ){
               $acf_image_meta_Fields = array(
                  'Caption' => 'acf_caption',
                   'Alt text' => 'acf_alt_text',
                'Description' => 'acf_description',
                   'File Name' => 'acf_file_name',
                   'Title' => 'acf_title',
             );
            }
            if($field_type == 'gallery' ){
                $acf_gallery_meta_Fields = array(
                    'Gallery Caption' => 'acf_gallery_caption',
                    'Gallery Alt text' => 'acf_gallery_alt_text',
                    'Gallery Description' => 'acf_gallery_description',
                    'Gallery File Name' => 'acf_gallery_file_name',
                    'Gallery Title' => 'acf_gallery_title',
                );
            }
            else if($field_type == 'group' || $field_type == 'flexible_content' || $field_type == 'repeater'){
               
                global $wpdb;
                $get_acf_pro_fields=$wpdb->get_results("SELECT post_content FROM {$wpdb->prefix}posts WHERE post_type='acf-field' AND post_status='publish'");
               
                $arrays=json_decode(json_encode($get_acf_pro_fields),true);
                foreach($arrays as $acf_field=>$acf_field_value){
                    $get_acf_pro_fields=unserialize($acf_field_value['post_content']);
                    $field_types =$get_acf_pro_fields['type'];
                    if($field_types == 'image' ){
                        $acf_image_meta_Fields = array(
                            'Caption' => 'acf_caption',
                            'Alt text' => 'acf_alt_text',
                            'Description' => 'acf_description',
                            'File Name' => 'acf_file_name',
                            'Title' => 'acf_title',
                        );
                    }
                    elseif($field_types == 'gallery'){
                        $acf_gallery_meta_Fields = array(
                            'Gallery Caption' => 'acf_gallery_caption',
                            'Gallery Alt text' => 'acf_gallery_alt_text',
                            'Gallery Description' => 'acf_gallery_description',
                            'Gallery File Name' => 'acf_gallery_file_name',
                            'Gallery Title' => 'acf_gallery_title',
                        );
                    }
                }
            }
        } 
        $acf_meta_fields=array_merge($acf_image_meta_Fields,$acf_gallery_meta_Fields);
        $acf_meta_value = $this->convert_static_fields_to_array( $acf_meta_fields);
        return $acf_meta_value;
    }

	/**
	* ACF Image Meta mapping fields extension supported import types
	* @param string $import_type - selected import type
	* @return boolean
	*/
    public function extensionSupportedImportType($import_type){
     
        if(is_plugin_active('advanced-custom-fields/acf.php') || is_plugin_active('advanced-custom-fields-pro/acf.php')){
            global $wpdb;
            $get_acf_fields=$wpdb->get_results("SELECT post_content FROM {$wpdb->prefix}posts WHERE post_type='acf-field' AND post_status='publish'");
            $array=json_decode(json_encode($get_acf_fields),true);
      
            foreach($array as $acf_fields=>$acf_field_values){
                $get_acf_fields=unserialize($acf_field_values['post_content']);
                //$field_type =$get_acf_fields['type'];
                $field_type = isset($get_acf_fields['type']) ? $get_acf_fields['type'] : '';

                if($field_type == 'image' || $field_type == 'gallery'){
                  
                    if($import_type == 'nav_menu_item'){
                        return false;
                    }
                
                    $import_type = $this->import_name_as($import_type);
                    if($import_type =='Posts' || $import_type =='Pages' || $import_type =='CustomPosts' || $import_type =='event' || $import_type =='location' || $import_type == 'event-recurring' || $import_type =='Users' || $import_type =='Taxonomies' || $import_type =='Tags' || $import_type =='Categories' || $import_type == 'CustomerReviews') {	
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
                else if($field_type == 'group' || $field_type == 'flexible_content' || $field_type == 'repeater'){
                   
                    $get_acf_pro_fields=$wpdb->get_results("SELECT post_content FROM {$wpdb->prefix}posts WHERE post_type='acf-field' AND post_status='publish'");
                   
                    $arrays=json_decode(json_encode($get_acf_pro_fields),true);
                    foreach($arrays as $acf_field=>$acf_field_value){
                        $get_acf_pro_fields=unserialize($acf_field_value['post_content']);
                        $field_types =$get_acf_pro_fields['type'];
                        if($field_types == 'image' || $field_types == 'gallery'){
                            if($import_type == 'nav_menu_item'){
                                 return false;
                           }
                       
                           $import_type = $this->import_name_as($import_type);
                           if($import_type =='Posts' || $import_type =='Pages' || $import_type =='CustomPosts' || $import_type =='event' || $import_type =='location' || $import_type == 'event-recurring' || $import_type =='Users' || $import_type =='Taxonomies' || $import_type =='Tags' || $import_type =='Categories' || $import_type == 'CustomerReviews') {	
                                //return true;
                                return false;
                           }
                           if($import_type == 'ticket'){
                               if(is_plugin_active('events-manager/events-manager.php')){
                                    return false;
                               }else{
                                    //return true;
                                    return false;
                               }
                           }
                           else{
                             return false;
                           }
                        }
                    }
                }
                else{
                    return false;
                }
            }      
        }
    }
}