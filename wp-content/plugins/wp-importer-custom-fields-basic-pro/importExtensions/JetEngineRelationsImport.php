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

class JetEngineRELImport {

	private static $instance = null;
	
    public static function getInstance() {		
		if (JetEngineRELImport::$instance == null) {
			JetEngineRELImport::$instance = new JetEngineRELImport;
		}
		return JetEngineRELImport::$instance;
	}

	function set_jet_engine_rel_values($header_array ,$value_array , $map, $post_id , $type , $mode, $hash_key, $line_number,$gmode,$templatekey){
		$post_values = [];
		$helpers_instance = ImportHelpers::getInstance();
        $post_values = $helpers_instance->get_header_values($map , $header_array , $value_array);
        $get_plugins_list = get_plugins();
		$get_jetengine_plugin_version = $get_plugins_list['jet-engine/jet-engine.php']['Version'];
		if($get_jetengine_plugin_version >= '2.11.4'){
            $this->jet_engine_rel_import_function_new($post_values,$type, $post_id, $mode, $hash_key, $line_number,$gmode,$templatekey);
        }
        else{
            $this->jet_engine_rel_import_function($post_values,$type, $post_id, $mode, $hash_key,$gmode,$templatekey);
        }		
	}
	
	public function jet_engine_rel_import_function($data_array, $type, $pID ,$mode, $hash_key,$gmode,$templatekey) 
	{
        global $wpdb;
        $meta_key_exp = explode('|',$data_array['jet_relation_metakey']);
        $meta_values_exp = explode('|',$data_array['jet_related_post']);
        $count = count($meta_key_exp);
        foreach($meta_key_exp as $metkey){
            if($mode != 'Insert'){
                $query = "SELECT meta_value FROM {$wpdb->prefix}postmeta WHERE meta_key ='{$metkey}' and post_id = '{$pID}' ";
                $get_metavalue = $wpdb->get_results($query);
                foreach($get_metavalue as $metval){
                    $metaid = $metval->meta_value;
                    delete_post_meta($metaid,$metkey);
                }
                delete_post_meta($pID,$metkey);
            }
        }
        for($i=0 ;$i<$count ; $i++){
            $meta_keys = $meta_key_exp[$i];
            $meta_values = $meta_values_exp[$i];
            $metaval = explode(',',$meta_values);
            $val_count = count($metaval);
          
            foreach($metaval as $metakeyval => $metavalues){
               $rmeta_keys = rtrim($meta_keys,' ');
               $metavalues = trim($metavalues);

                if(is_numeric($metavalues)){
                    if($count > 1){
                        add_post_meta($pID,$rmeta_keys,$metavalues);
                        add_post_meta($metavalues,$meta_keys,$pID);
                    }
                    else{
                        update_post_meta($pID,$rmeta_keys,$metavalues);
                        update_post_meta($metavalues,$meta_keys,$pID);
                    }
                }
                else{
                    $query = "SELECT id FROM {$wpdb->prefix}posts WHERE post_title ='{$metavalues}' and post_status = 'publish' ORDER BY ID DESC";
                    $get_id = $wpdb->get_results($query);
                    
                    $getids =$get_id[0];
                    $meta_valueid = $getids->id;
                
                    add_post_meta($pID,$rmeta_keys,$meta_valueid);
                    add_post_meta($meta_valueid,$meta_keys,$pID);
                }
            }
        }
    }
    
    public function jet_engine_rel_import_function_new($data_array, $type, $pID ,$mode, $hash_key, $line_number,$gmode,$templatekey) {
        global $wpdb;
		$extension_object = new ExtensionHandler;
		$import_type = $extension_object->import_post_types($type );        
        $relation_fields = [];       
        $relation_meta = [];

        foreach($data_array as $data_key => $data_value){
            if(strpos($data_key, 'jet_related_post') !== false){      
               if(!empty($data_value)){
                    $get_relation_id = explode(' :: ', $data_key);
                    $relation_id = $get_relation_id[1];
                    $get_related_objects = $this->get_relation_objects($relation_id, $import_type, $pID, 'args', $mode);

                    $parent_object_id = $get_related_objects['parent_object'];
                    $child_object_id = $get_related_objects['child_object'];
                    $relation_type = $get_related_objects['relation_type'];
                    $connection_type = $get_related_objects['connected_type'];
                    $connection_object = $get_related_objects['connected_object'];
                    $get_relate_db_table = $get_related_objects['get_relation_db_table'];

                    $relation_post_meta = [];
                    $get_related_posts_data = explode('|', $data_value);
                    $get_related_posts = array_map('trim', $get_related_posts_data);
                    $get_related_posts = array_unique($get_related_posts);
                
                    foreach($get_related_posts as $related_posts){
                        $related_posts = trim($related_posts);
                        if(is_numeric($related_posts)){
                            //$get_related_post_id = $related_posts;

                            if($connection_type == 'posts'){
                                $get_related_post_id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}posts WHERE ID = $related_posts AND post_status = 'publish' AND post_type = '$connection_object'");
                            }
                            elseif($connection_type == 'users'){
                                $get_related_post_id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}users WHERE ID = $related_posts");
                            }
                            elseif($connection_type == 'terms'){
                                $get_related_post_id = $wpdb->get_var("SELECT term_id FROM {$wpdb->prefix}terms WHERE term_id = $related_posts");
                            }
                            elseif($connection_type == 'cct'){                                
                                $table_names = 'jet_cct_'.$connection_object;
                                $get_related_post_id = $wpdb->get_var("SELECT _ID FROM {$wpdb->prefix}$table_names WHERE _ID = $related_posts");                                
                            }
                        }
                        else{
                            if($connection_type == 'posts'){
                                $get_related_post_id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}posts WHERE post_title = '$related_posts' AND post_status = 'publish' AND post_type = '$connection_object' ORDER BY ID DESC");
                            }
                            elseif($connection_type == 'users'){
                                $get_related_post_id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}users WHERE user_login = '$related_posts'");
                            }
                            elseif($connection_type == 'terms'){
                                $get_related_post_id = $wpdb->get_var("SELECT term_id FROM {$wpdb->prefix}terms WHERE name = '$related_posts'");
                            }
                            elseif($connection_type == 'cct'){
                                $table_names = 'jet_cct_'.$connection_object;                                
                                $get_related_post_id = $wpdb->get_var("SELECT _ID FROM {$wpdb->prefix}$table_names WHERE _ID = $related_posts");
                            }
                        }

                        if(!empty($get_related_post_id)){
                            if(!empty($parent_object_id)){
                                if($relation_type == 'one') {
                                    $this->delete_rel($get_related_post_id,'child',$relation_id,$get_relate_db_table);
                                }
                                $child_object_id1 = $get_related_post_id;
                                $parent_object_id1 = $parent_object_id;
                            }
                            else{
                                if($relation_type == 'one') {
                                    $this->delete_rel($get_related_post_id,'parent',$relation_id,$get_relate_db_table);
                                }
                                $parent_object_id1 = $get_related_post_id;
                                $child_object_id1 = $child_object_id;
                            }
                            $current_time = current_time('Y-m-d H:i:s');
                        
                            $jet_rel_default_table = $wpdb->prefix . 'jet_rel_default';
                            $jet_rel_default_db_table = $wpdb->prefix . 'jet_rel_'.$relation_id;                            

                            $wpdb->insert($jet_rel_default_table, array('created' => $current_time, 'rel_id' => $relation_id, 'parent_rel' => 0, 'parent_object_id' => $parent_object_id1, 'child_object_id' => $child_object_id1));
                            if($get_relate_db_table == 1){
                                $get_id = $wpdb->get_results("SELECT _ID FROM $jet_rel_default_db_table WHERE rel_id = '$relation_id' AND parent_object_id = '$parent_object_id1' AND child_object_id = '$child_object_id1'");                                
                                $get_default_id = !empty($get_id) ? $get_id[0]->_ID : "";
                                if(empty($get_default_id)){  
                                    $wpdb->insert($jet_rel_default_db_table, array('created' => $current_time, 'rel_id' => $relation_id, 'parent_rel' => 0, 'parent_object_id' => $parent_object_id1, 'child_object_id' => $child_object_id1));
                                }  
                            }

                            $last_id = $wpdb->insert_id; 
                            $relation_post_meta[] = $last_id;

                            if($relation_type == 'one'){
                                break;
                            }
                        }
                    }

                    $relation_meta[$relation_id] = $relation_post_meta;
                }
            }
            else{
                $relation_fields[$data_key] = $data_value;
            }
        }

		$current_time = current_time('Y-m-d H:i:s');
        if(!empty($relation_fields)){
            foreach($relation_fields as $relation_field_key => $relation_field_value){
                $get_relation_name = explode(' :: ', $relation_field_key);
                $get_relation_field_values = explode('|', $relation_field_value);           
                $field_name = $get_relation_name[0]; 

                $get_field_relation_id = $get_relation_name[1];
                $get_related_metafields_objects = $this->get_relation_objects($get_field_relation_id, $import_type, $pID, 'metafields', $mode);
                $relation_meta_fields = $get_related_metafields_objects['meta_fields'];
                $get_relation_db_table = $get_related_metafields_objects['get_relation_db_table'];

                if($get_relation_db_table == 1){
                    $jet_rel_table = $wpdb->prefix . 'jet_rel_' . $get_field_relation_id;
                    $jet_rel_default_meta_table = $wpdb->prefix . 'jet_rel_' . $get_field_relation_id . '_meta';
                }
                else{
                    $jet_rel_table = $wpdb->prefix . 'jet_rel_default';
                    $jet_rel_default_meta_table = $wpdb->prefix . 'jet_rel_default_meta';
                }
                if(array_key_exists('type',$get_related_metafields_objects))
                  $relation_type = $get_related_metafields_objects['type'];
                  if(!empty($relation_meta)) {                    
                    $get_inserted_rel_details = array_key_exists($get_field_relation_id,$relation_meta) ? $relation_meta[$get_field_relation_id] : [];                
        
                    foreach($get_inserted_rel_details as $inserted_key => $inserted_values){
                        $relation_field_values = isset($get_relation_field_values[$inserted_key]) ? $get_relation_field_values[$inserted_key] : '';                    
                        if(!empty($relation_field_values)){
                            $inserted_parent_id = $wpdb->get_var("SELECT parent_object_id FROM $jet_rel_table WHERE _ID = $inserted_values");
                            $inserted_child_id = $wpdb->get_var("SELECT child_object_id FROM $jet_rel_table WHERE _ID = $inserted_values");

                            if( strpos($relation_field_values, ',') !== false) {
                                $relation_field_values_exp = explode(',', $relation_field_values);
                                $result_relation_field_values = array_map('trim', $relation_field_values_exp);
                        
                                $serialize_relation_field_values = serialize($result_relation_field_values);
                                $wpdb->insert($jet_rel_default_meta_table, array('created' => $current_time, 'rel_id' => $get_field_relation_id, 'parent_object_id' => $inserted_parent_id, 'child_object_id' => $inserted_child_id, 'meta_key' => $field_name, 'meta_value' => $serialize_relation_field_values));
                            }
                            else{
                                if($relation_meta_fields[$field_name] == 'date'){
                                    $dateformat = 'Y-m-d';
                                    $relation_field_metavalues = $helpers_instance->validate_datefield($relation_field_values, $field_name, $dateformat, $line_number);
                                }
                                elseif($relation_meta_fields[$field_name] == 'datetime-local'){
                                    $dateformat = 'Y-m-dTH:i';
                                    $relation_field_metavalues = $helpers_instance->validate_datefield($relation_field_values, $field_name, $dateformat, $line_number);
                                }
                                elseif(strpos($relation_meta_fields[$field_name][0], 'select') !== false){                                
                                    if($relation_type[$field_name.'type'][0] == 1){                                                                        
                                        $relation_field_values_exp = explode(',', $relation_field_values);                                    
                                        $result_relation_field_values = array_map('trim', $relation_field_values_exp);
                                
                                        $relation_field_metavalues = serialize($result_relation_field_values);                                    
                                    }
                                    else{
                                        $relation_field_metavalues = $relation_field_values;
                                    }
                                    
                                }
                                elseif(strpos($relation_meta_fields[$field_name][0], 'media') !== false){                                                                
                                    $exp_image = explode(',', $relation_meta_fields[$field_name][0]);
                                    if(array_key_exists(1,$exp_image))
                                    $image_format = $exp_image[1];
                                    else
                                    $image_format = "id";                                
                                    $relation_field_metavalues = $this->get_related_image_id($relation_field_values, $image_format, $pID);                                
                                    if(is_array($relation_field_metavalues) && array_key_exists('0',$relation_field_metavalues)){
                                        $relation_field_metavalues = serialize($relation_field_metavalues[0]);//Media type is in both
                                    }     

                                    
                                }
                                else{
                                    $relation_field_metavalues = $relation_field_values;
                                }
                                $wpdb->insert($jet_rel_default_meta_table, array('created' => $current_time, 'rel_id' => $get_field_relation_id, 'parent_object_id' => $inserted_parent_id, 'child_object_id' => $inserted_child_id, 'meta_key' => $field_name, 'meta_value' => $relation_field_metavalues));
                            }
                        }
                    }
                }
            }
        }
    }

    public function get_relation_objects($relation_id, $import_type, $pID, $fetch, $mode){
        global $wpdb;

        //to get parent and child object for the given relation
        if($fetch == 'args'){
            //to get parent and child object for the given relation
            $get_relation_objects = $wpdb->get_var("SELECT args FROM {$wpdb->prefix}jet_post_types WHERE id = $relation_id");
            $get_relation_objects = maybe_unserialize($get_relation_objects);

            $get_rel_parent_value = $get_relation_objects['parent_object'];
            $get_rel_child_value = $get_relation_objects['child_object'];
            $get_relation_types = $get_relation_objects['type'];
            $get_relation_db_table_name = $get_relation_objects['db_table'];
        
            $get_rel_parent = explode('::', $get_rel_parent_value);
            $relation_parent_object = $get_rel_parent[1];

            $get_rel_child = explode('::', $get_rel_child_value);
            $relation_child_object = $get_rel_child[1];

            $exp_relation_types = explode('_to_', $get_relation_types);
            
            if($import_type == 'user'){
                $import_type = 'users';
            }

            if($import_type == $relation_parent_object){
                $parent_object_id = $pID;
                $child_object_id = '';
                $get_relation_type = $exp_relation_types[1]; 
                $connected_type =  $get_rel_child[0];
                $connected_object = $relation_child_object;
                if($mode != 'Insert'){
                    $this->delete_rel($pID,'parent',$relation_id,$get_relation_db_table_name);
                }
            }
            elseif($import_type == $relation_child_object){
                $parent_object_id = '';
                $child_object_id = $pID;
                $get_relation_type = $exp_relation_types[0];
                $connected_type =  $get_rel_parent[0];
                $connected_object = $relation_parent_object;
                if($get_relation_type == 'one') {
                    $this->delete_rel($pID,'parent',$relation_id,$get_relation_db_table_name);
                }
                if($mode != 'Insert'){
                    $this->delete_rel($pID,'child',$relation_id,$get_relation_db_table_name);
                }
            }

            if($connected_type == 'mix'){
                $connected_type = 'users';
            }

            $response['parent_object'] = $parent_object_id;
            $response['child_object'] = $child_object_id;
            $response['relation_type'] = $get_relation_type;
            $response['connected_type'] = $connected_type;
            $response['connected_object'] = $connected_object;
            $response['get_relation_db_table'] = $get_relation_db_table_name;
            return $response;
        }
        elseif($fetch == 'metafields'){
            //get meta fields for the given relation
            $get_relation_metafields = $wpdb->get_var("SELECT meta_fields FROM {$wpdb->prefix}jet_post_types WHERE id = $relation_id");
            $get_relation_metafields = maybe_unserialize($get_relation_metafields);

            //form an array consisting of meta field name as key and meta type as value
            $jetengine_relation_metafields_array = [];
            $jetengine_relation_type_array = [];
            foreach($get_relation_metafields as $get_relation_metafield_values){
                if($get_relation_metafield_values['type'] == 'media' && isset($get_relation_metafield_values['value_format'])){
                    $jetengine_relation_metafields_array[$get_relation_metafield_values['name']][] = $get_relation_metafield_values['type'] .','. $get_relation_metafield_values['value_format'];
                }
                elseif($get_relation_metafield_values['type'] == 'select'){
                    $jetengine_relation_metafields_array[$get_relation_metafield_values['name']][] = $get_relation_metafield_values['type'];
                    if(array_key_exists('is_multiple',$get_relation_metafield_values)){
                        $jetengine_relation_type_array[$get_relation_metafield_values['name'].'type'] []= $get_relation_metafield_values['is_multiple'];
                        $response['type'] = $jetengine_relation_type_array;
                    }
                }
                else{
                    $jetengine_relation_metafields_array[$get_relation_metafield_values['name']][] = $get_relation_metafield_values['type'];
                }
            }
 
            $response['meta_fields'] = $jetengine_relation_metafields_array;
            $get_relation_objects = $wpdb->get_var("SELECT args FROM {$wpdb->prefix}jet_post_types WHERE id = $relation_id");
            $get_relation_objects = maybe_unserialize($get_relation_objects);
         
            $response['get_relation_db_table'] = $get_relation_objects['db_table'];
            return $response;
        }
    }

    public function get_related_image_id($image, $image_format, $pID){
        global $wpdb;
        $media_instance = MediaHandling::getInstance();
        $media_id = $media_instance->media_handling( $image, $pID);

        if($image_format == 'url'){
            $get_media_fields = $wpdb->get_results("select meta_value from {$wpdb->prefix}postmeta where post_id = $media_id and meta_key ='_wp_attached_file'");
            $dir = wp_upload_dir();			
           
            if(!empty($get_media_fields[0]->meta_value)){
                $media_id = $dir ['baseurl'] . '/' .$get_media_fields[0]->meta_value;
            }        
            else{
                $media_id='';
            }
        }
        elseif($image_format == 'both'){
            $media_ids1['id'] = $media_id;
            $get_media_fields = $wpdb->get_results("select meta_value from {$wpdb->prefix}postmeta where post_id = $media_id and meta_key ='_wp_attached_file'");
            $dir = wp_upload_dir();			
            if(!empty($get_media_fields[0]->meta_value)){
                $media_ids2['url'] = $dir ['baseurl'] . '/' .$get_media_fields[0]->meta_value;
            }        
            else{
                $media_ids2['url']='';
            }
            $mediavalue= array_merge($media_ids1,$media_ids2);
            $media_id = array($mediavalue);

        }
        else{
            $media_id = $media_id;
        }
        return $media_id;
    }

    public function delete_rel($id,$type,$relation_id,$table){              
        global $wpdb;

        if($table == 1){
            $jet_rel_table = $wpdb->prefix . 'jet_rel_'.$relation_id;
            $jet_rel_meta_table = $wpdb->prefix . 'jet_rel_'.$relation_id. '_meta';
        }
        else{
            $jet_rel_table = $wpdb->prefix . 'jet_rel_default';
            $jet_rel_meta_table = $wpdb->prefix . 'jet_rel_default_meta';
        }
    
        if($type == 'parent'){
            $obj = "parent_object_id";
        }
        else {
            $obj = "child_object_id";
        }        

        $get_id = $wpdb->get_results("SELECT _ID FROM $jet_rel_table WHERE rel_id = '$relation_id' AND $obj = '$id'",ARRAY_A);        
        if(!empty($get_id)){  
            foreach($get_id as $rid){
                $wpdb->delete($jet_rel_table, array('_ID' => $rid["_ID"]));
            }                
        }

        $get_meta_id = $wpdb->get_results("SELECT _ID FROM $jet_rel_meta_table WHERE rel_id = '$relation_id' AND $obj = '$id'",ARRAY_A);        
        if(!empty($get_meta_id)){  
            foreach($get_meta_id as $rmetaid){
                $wpdb->delete($jet_rel_meta_table,array('_ID' => $rmetaid["_ID"]));
            }                
        }
    }
}