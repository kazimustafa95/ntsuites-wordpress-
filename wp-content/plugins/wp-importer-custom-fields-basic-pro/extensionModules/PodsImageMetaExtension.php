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

class PodsImageMetaExtension extends ExtensionHandler{
	private static $instance = null;

    public static function getInstance() {
		
		if (PodsImageMetaExtension::$instance == null) {
			PodsImageMetaExtension::$instance = new PodsImageMetaExtension;
		}
		return PodsImageMetaExtension::$instance;
	}
	
	/**
	* Provides Podss Image Meta mapping fields for specific post type
	* @param string $data - selected import type
	* @return array - mapping fields
	*/
    public function processExtension($data) {
        $response = [];
        global $wpdb;
        $import_type = $data;
		$import_type = $this->import_type_as($import_type);
		$response = [];
        $podsFields = array();
        $pods_image_meta_Fields = null;
		$import_type = $this->import_post_types($import_type);
		$post_id = $wpdb->get_results($wpdb->prepare("select ID from {$wpdb->prefix}posts where post_name= %s and post_type = %s", $import_type, '_pods_pod'));
		if(empty($post_id) && $import_type == 'comments'){
            $post_id = $wpdb->get_results($wpdb->prepare("select ID from {$wpdb->prefix}posts where post_name= %s and post_type = %s", 'comment', '_pods_pod'));
		}
        if(empty($post_id) && $import_type == 'Images'){
            $post_id = $wpdb->get_results($wpdb->prepare("select ID from {$wpdb->prefix}posts where post_name= %s and post_type = %s", 'media', '_pods_pod'));
		}
		if(!empty($post_id)) {
			$lastId = $post_id[0]->ID;
			$get_pods_fields = $wpdb->get_results( $wpdb->prepare( "SELECT post_title, post_name FROM {$wpdb->prefix}posts where post_parent = %d AND post_type = %s", $lastId, '_pods_field' ) );
            if ( ! empty( $get_pods_fields ) ) :
                $pods_image_meta_Fields = array(
			        'Caption' => 'pods_caption',
					'Alt text' => 'pods_alt_text',
					'Description' => 'pods_description',
					'File Name' => 'pods_file_name',
					'Title' => 'pods_title',
                );
			endif;
		}
        
		$pods_image_meta_value = $this->convert_static_fields_to_array($pods_image_meta_Fields);
		$response['pods_image_meta_fields'] = $pods_image_meta_value ;
		return $response;
		
    }

	/**
	* Pods Image Meta Fields extension supported import types
	* @param string $import_type - selected import type
	* @return boolean
	*/
    public function extensionSupportedImportType($import_type){
        if(is_plugin_active('pods/init.php')){
            global $wpdb;
            $post_id = $wpdb->get_results($wpdb->prepare("select ID from {$wpdb->prefix}posts where post_type = %s", '_pods_pod'));
            if(!empty($post_id)) {
                $lastId  = $post_id[0]->ID;
                $get_pods_fields = $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_name FROM {$wpdb->prefix}posts where post_parent = %d AND post_type = %s", $lastId, '_pods_field' ) );
                if ( ! empty( $get_pods_fields ) ) {
                    foreach ( $get_pods_fields as $pods_field ) {
            
                        $get_pods_types = $wpdb->get_results( $wpdb->prepare( "SELECT  meta_value FROM {$wpdb->prefix}postmeta where post_id = %d AND meta_key = %s", $pods_field->ID, 'type' ) );
                        $array=json_decode(json_encode($get_pods_types),true);
                        foreach($array as $arrkey =>$arrval){
                            if($arrval['meta_value'] == 'file'){
                                if($import_type == 'nav_menu_item'){
                                    return false;
                                }
                                $import_type = $this->import_name_as($import_type);
                        
                                if($import_type == 'Posts' || $import_type == 'Pages' || $import_type == 'CustomPosts' || $import_type == 'Users' || $import_type == 'Taxonomies' || $import_type == 'Categories' || $import_type == 'Tags' || $import_type == 'Comments' || $import_type == 'Images') {	
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
                }
            }
            
        }

    }
}
