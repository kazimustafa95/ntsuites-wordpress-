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

class JetEngineRelationsExtension extends ExtensionHandler{
    private static $instance = null;
	
    public static function getInstance() {		
		if (JetEngineRelationsExtension::$instance == null) {
			JetEngineRelationsExtension::$instance = new JetEngineRelationsExtension;
		}
		return JetEngineRelationsExtension::$instance;
	}
	
	/**
	* Provides default mapping fields for Jet Engine Pro plugin
	* @param string $data - selected import type
	* @return array - mapping fields
	*/
	public function processExtension($data){
		$import_type = $data;
		$response = [];
		$get_plugins_list = get_plugins();
		$get_jetengine_plugin_version = $get_plugins_list['jet-engine/jet-engine.php']['Version'];
		if($get_jetengine_plugin_version >= (int)'2.11.4'){
			$jet_engine_fields = $this->JetEngineRelationsFields_New($import_type);
		}
		else{
			$jet_engine_fields = $this->JetEngineRelationsFields($import_type);
		}

		$response['jetengine_rel_fields'] = $jet_engine_fields;		
		
		return $response;	
	}

	/**
	* Retrieves Jet Engine Relations mapping fields
	* @param string $import_type - selected import type
	* @return array - mapping fields
	*/
	public function JetEngineRelationsFields($import_type) {	
		
		$import_type = $this->import_post_types($import_type);
        global $wpdb;	
      
		$get_import_types = $wpdb->get_results("SELECT option_value FROM {$wpdb->prefix}options WHERE option_name = 'jet_engine_relations' ");
	   if(isset($get_import_types[0]->option_value)){
			$unserialized_meta = maybe_unserialize($get_import_types[0]->option_value);
			if(isset($unserialized_meta)){
				$arraykeys = array_keys($unserialized_meta);
			}
			
			foreach($arraykeys as $val){
				$values = explode('-',$val);
				$v = $values[1];
			}
	
			for($i=1 ; $i<=$v ; $i++){
				$unserialized_meta['item-'.$i] = isset($unserialized_meta['item-'.$i]) ? $unserialized_meta['item-'.$i] : '';
				$fields = $unserialized_meta['item-'.$i];
				
				if(!empty($fields)){
					$post_type_1 = $fields['post_type_1'];
					$post_type_2 = $fields['post_type_2'];
				
					if($import_type == $post_type_1 || $import_type == $post_type_2){
						$customFields['JE']['relation_meta_key']['label'] = 'Jet Relation Metakey';
						$customFields['JE']['relation_meta_key']['name'] = 'jet_relation_metakey';
						$customFields['JE']['relation_meta_key']['slug'] = 'jet_relation_metakey';
						$customFields['JE']['related_post']['label'] = 'Jet Related Post';
						$customFields['JE']['related_post']['name'] = 'jet_related_post';
						$customFields['JE']['related_post']['slug'] = 'jet_related_post';
					}
				}
			}
		}
		 
		if(isset($customFields)){
			$jet_value = $this->convert_fields_to_array($customFields);
		}
		else{
			$jet_value = '';
		}
		return $jet_value;		
	}

	/**
	* Retrieves Jet Engine Relations mapping fields for jetengine version greater than 2.11.4
	* @param string $import_type - selected import type
	* @return array - mapping fields
	*/
	public function JetEngineRelationsFields_New($import_type) {
		
		global $wpdb;	
		$get_rel_fields = $wpdb->get_results("SELECT id,labels, args, meta_fields FROM {$wpdb->prefix}jet_post_types WHERE status = 'relation' ", ARRAY_A);
	
		if(!empty($get_rel_fields)){
		
			$temp = 0;
			foreach($get_rel_fields as $get_rel_values){
				
				$jet_relation_names = maybe_unserialize($get_rel_values['labels']);
				$jet_relation_name = maybe_unserialize($jet_relation_names['name']);
				$jet_relation_id = $get_rel_values['id'];

				$get_rel_fields_args = maybe_unserialize($get_rel_values['args']);
				$get_rel_parent_value = $get_rel_fields_args['parent_object'];
				$get_rel_child_value = $get_rel_fields_args['child_object'];
			
				$get_rel_parent1 = explode('::', $get_rel_parent_value);
				$get_rel_parent = $get_rel_parent1[1];

				$get_rel_child1 = explode('::', $get_rel_child_value);
				$get_rel_child = $get_rel_child1[1];
	
				$import_type = $this->import_post_types($import_type);
				if($import_type == 'user'){
					$import_type = 'users';
				}

				if($import_type == $get_rel_parent || $import_type == $get_rel_child){
					
					$get_rel_metafields = maybe_unserialize($get_rel_values['meta_fields']);
					foreach($get_rel_metafields as $get_rel_metavalue){
						$customFields['JE'][$temp]['label'] = $get_rel_metavalue['title'];						
						$customFields['JE'][$temp]['name'] = $get_rel_metavalue['name'] . ' :: '.$jet_relation_id;
						$temp++;
					}

					$customFields['JE'][$temp]['label'] = 'Jet Related Post';					
					$customFields['JE'][$temp]['name'] = 'jet_related_post' . ' :: '.$jet_relation_id;
					$temp++;
				}
			}	
		}

		if(isset($customFields)){
			$jet_value = $this->convert_fields_to_array($customFields);
		}
		else{
			$jet_value = '';
		}		
		return $jet_value;		
	}

	/**
	* Jet Engine extension supported import types
	* @param string $import_type - selected import type
	* @return boolean
	*/
	public function extensionSupportedImportType($import_type){
		if(is_plugin_active('jet-engine/jet-engine.php')){
			if($import_type == 'nav_menu_item'){
				return false;
			}
			$import_type = $this->import_name_as($import_type);
			if($import_type =='Posts' || $import_type =='Pages' || $import_type =='CustomPosts' || $import_type =='event' || $import_type =='location' || $import_type == 'event-recurring' || $import_type =='Users' || $import_type =='Taxonomies' || $import_type =='Tags' || $import_type =='Categories' || $import_type == 'CustomerReviews' || $import_type ='Comments') {		
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
	}
	
	function import_post_types($import_type, $importAs = null) {	
		$import_type = trim($import_type);
		$module = array('Posts' => 'post', 'Pages' => 'page', 'Users' => 'user', 'Comments' => 'comments', 'Taxonomies' => $importAs, 'CustomPosts' => $importAs);
		foreach (get_taxonomies() as $key => $taxonomy) {
			$module[$taxonomy] = $taxonomy;
		}
		if(array_key_exists($import_type, $module)) {
			return $module[$import_type];
		}
		else {
			return $import_type;
		}
	}
}