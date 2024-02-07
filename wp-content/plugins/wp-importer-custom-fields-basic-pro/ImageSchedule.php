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

class ImageSchedule {
    private static $instance=null;
    public static $media_instance = null;
    public static $corefields_instance = null;

    public static function getInstance() {
        if ( null == self::$instance ) {
            self::$instance = new self;
            self::$media_instance = MediaHandling::getInstance();
            self::$corefields_instance = CoreFieldsImport::getInstance();

        }
        return self::$instance;
        }
    
    public function __construct() {
        $this->plugin = Plugin::getInstance();	
    }
    public function image_schedule($schedule_array,$unikey,$module,$clikey)
    {
        global $wpdb;
        $core_instance = CoreFieldsImport::getInstance();	
       
        $hash_key =  $unikey == 'hash_key' ? $schedule_array : $clikey;   
        $templatekey = $unikey == 'templatekey' ? $schedule_array : "";
        $get_result = $wpdb->get_results("SELECT post_id FROM {$wpdb->prefix}ultimate_cf_importer_shortcode_manager as a join {$wpdb->prefix}posts as b on a.post_id = b.ID WHERE a.status = 'pending' AND b.ID != 'trash' ORDER BY a.post_id ASC LIMIT 10", ARRAY_A);
        if(empty($get_result)){
            $schedule_argument = array($schedule_array,$unikey,$module,$clikey);
            wp_clear_scheduled_hook('smackcf_image_schedule_hook', $schedule_argument);
        }
        else{    
            $records = array_column($get_result, 'post_id');  
            foreach ($records as $title => $id) { 
                $get_shortcode = $wpdb->get_var("SELECT image_shortcode FROM {$wpdb->prefix}ultimate_cf_importer_shortcode_manager WHERE post_id = $id AND $unikey = '$schedule_array' AND status = 'pending' ");  
                //$get_image_meta = $wpdb->get_var("SELECT image_meta FROM {$wpdb->prefix}ultimate_cf_importer_shortcode_manager WHERE post_id = $id AND hash_key = '$schedule_array' AND status = 'pending' ");      
                $get_import_type = $wpdb->get_var("SELECT import_type FROM {$wpdb->prefix}ultimate_cf_importer_shortcode_manager WHERE post_id = $id AND $unikey = '$schedule_array' AND status = 'pending' ");
                if(empty($get_import_type)){
                    $get_import_type = 'post';
                }
                $get_image_meta = '';
                if($get_shortcode == 'Featured'){
                    $get_original_image = $wpdb->get_var("SELECT original_image FROM {$wpdb->prefix}ultimate_cf_importer_shortcode_manager WHERE post_id = $id AND status = 'pending' AND image_shortcode = 'Featured' ");
                    $post_values['featured_image'] = $get_original_image;									
                    $attach_id = self::$media_instance->media_handling($get_original_image,$id,$post_values,$module,'Featured',$hash_key,$templatekey);			
                    if($attach_id){
                        set_post_thumbnail( $id, $attach_id );
                        //Handle duplicate or existing image updates
                        $this->update_status_shortcode_table($id, $get_shortcode,$get_original_image, 'completed'); 
                    }
                    else {
                        $this->update_status_shortcode_table($id, $get_shortcode,$get_original_image, 'failed');
                    }
                }
                elseif($get_shortcode == 'Inline'){
                    $get_original_image = $wpdb->get_var("SELECT original_image FROM {$wpdb->prefix}ultimate_cf_importer_shortcode_manager WHERE image_shortcode LIKE '%$get_shortcode' AND post_id = $id AND status = 'pending' ");
                    $post_values['inline'] = $get_original_image;
                    $image_type = 'Inline';

                    $attach_id = self::$media_instance->media_handling( $get_original_image , $id ,$post_values,'','inline',$hash_key,$templatekey);                    
                    $core_instance = CoreFieldsImport::getInstance();
                    $core_instance->image_handling($id,$hash_key,$module,$templatekey);
                    if($attach_id){  
                        $this->update_status_shortcode_table($id, $get_shortcode,$get_original_image, 'completed'); 
                    }
                    else{
                        $this->update_status_shortcode_table($id, $get_shortcode,$get_original_image, 'failed');
                    }

                }
                elseif( strpos($get_shortcode, 'yoast_opengraph_image__') !== false) {
                    $image_type = 'yoast_opengraph';
                    $this->images_import_function($id, $get_shortcode, $hash_key, $templatekey, $image_type, $get_import_type);             
                }
                elseif( strpos($get_shortcode, 'yoast_twitter_image__') !== false) {
                    $image_type = 'yoast_twitter';
                    $this->images_import_function($id, $get_shortcode, $hash_key, $templatekey, $image_type, $get_import_type);             
                }
                elseif( strpos($get_shortcode, 'wpmember_image__') !== false) {
                    $image_type = 'wpmember';
                    $this->images_import_function($id, $get_shortcode, $hash_key, $templatekey, $image_type, $get_import_type);             
                }  
                elseif( strpos($get_shortcode, 'term_image__') !== false) {
                    $image_type = 'term';
                    $this->images_import_function($id, $get_shortcode, $hash_key, $templatekey, $image_type, $get_import_type);             
                }  
                elseif( strpos($get_shortcode, 'cmb2_image__') !== false) {
                    $image_type = 'cmb2';
                    $this->images_import_function($id, $get_shortcode, $hash_key, $templatekey, $image_type, $get_import_type);             
                }  
                elseif( strpos($get_shortcode, 'cfs_image__') !== false) {
                    $image_type = 'cfs';
                    $this->images_import_function($id, $get_shortcode, $hash_key, $templatekey, $image_type, $get_import_type);
                }
                elseif( strpos($get_shortcode, 'metabox_image__') !== false) {
                    $image_type = 'metabox';
                    $this->images_import_function($id, $get_shortcode, $hash_key, $templatekey, $image_type, $get_import_type);
                }
                elseif( strpos($get_shortcode, 'metabox_clone_image__') !== false) {
                    $image_type = 'metabox_clone';
                    $this->images_import_function($id, $get_shortcode, $hash_key, $templatekey, $image_type, $get_import_type);
                }
                elseif( strpos($get_shortcode, 'metabox_image__clone_image__') !== false) {
                    $image_type = 'metabox_image_clone';
                    $this->images_import_function($id, $get_shortcode, $hash_key, $templatekey, $image_type, $get_import_type);
                }
                elseif( strpos($get_shortcode, 'wordpress_custom_image__') !== false) {
                    $this->acf_gallery_image_update($id, $get_shortcode, '', 'wordpress_custom_image__', $get_import_type,$hash_key, $templatekey);
                }
                elseif( strpos($get_shortcode, 'acf_image__') !== false) {
                    $image_type = 'acf';
                    $this->acf_image_update($id,$image_type, $get_shortcode, $get_image_meta, 'acf_image__', $get_import_type,$hash_key, $templatekey);
                }
                elseif( strpos($get_shortcode, 'acf_group_image__') !== false) {
                    $image_type = 'acf_group';
                    $this->acf_image_update($id,$image_type, $get_shortcode, $get_image_meta, 'acf_group_image__', $get_import_type,$hash_key, $templatekey);
                }
                elseif( strpos($get_shortcode, 'acf_repeater_image__') !== false) {
                    $image_type = 'acf_repeater';
                    $this->acf_image_update($id,$image_type ,$get_shortcode, $get_image_meta, 'acf_repeater_image__', $get_import_type,$hash_key, $templatekey);
                }
                elseif( strpos($get_shortcode, 'acf_flexible_image__') !== false) {
                    $image_type = 'acf_flexible';
                    $this->acf_image_update($id,$image_type ,$get_shortcode, $get_image_meta, 'acf_flexible_image__', $get_import_type,$hash_key, $templatekey);
                }
                elseif( strpos($get_shortcode, 'acf_group_repeater_image__') !== false) {
                    $image_type = 'acf_group_repeater';
                    $this->acf_image_update($id,$image_type, $get_shortcode, $get_image_meta, 'acf_group_repeater_image__', $get_import_type,$hash_key, $templatekey);
                }
                elseif( strpos($get_shortcode, 'acf_repeater_group_image__') !== false) {
                    $image_type = 'acf_repeater_group';
                    $this->acf_image_update($id,$image_type, $get_shortcode, $get_image_meta, 'acf_repeater_group_image__', $get_import_type,$hash_key, $templatekey);
                }
                elseif( strpos($get_shortcode, 'acf_gallery_image__') !== false) {
                    $this->acf_gallery_image_update($id, $get_shortcode, $get_image_meta, 'acf_gallery_image__', $get_import_type,$hash_key, $templatekey);
                }
                elseif( strpos($get_shortcode, 'acf_group_gallery_image__') !== false) {
                    $this->acf_gallery_image_update($id, $get_shortcode, $get_image_meta, 'acf_group_gallery_image__', $get_import_type,$hash_key, $templatekey);
                }
                elseif( strpos($get_shortcode, 'acf_repeater_gallery_image__') !== false) {
                    $this->acf_gallery_image_update($id, $get_shortcode, $get_image_meta, 'acf_repeater_gallery_image__', $get_import_type,$hash_key, $templatekey);
                }
                elseif( strpos($get_shortcode, 'acf_group_repeater_gallery_image__') !== false) {
                    $this->acf_gallery_image_update($id, $get_shortcode, $get_image_meta, 'acf_group_repeater_gallery_image__', $get_import_type,$hash_key, $templatekey);
                }
                elseif( strpos($get_shortcode, 'acf_repeater_group_gallery_image__') !== false) {
                    $this->acf_gallery_image_update($id, $get_shortcode, $get_image_meta, 'acf_repeater_group_gallery_image__', $get_import_type,$hash_key, $templatekey);
                }
                elseif( strpos($get_shortcode, 'acf_flexible_gallery_image__') !== false) {
                    $this->acf_gallery_image_update($id, $get_shortcode, $get_image_meta, 'acf_flexible_gallery_image__', $get_import_type,$hash_key, $templatekey);
                }
                elseif( strpos($get_shortcode, 'pods_image__') !== false ){
                    $get_original_image = $wpdb->get_var("SELECT original_image FROM {$wpdb->prefix}ultimate_cf_importer_shortcode_manager WHERE post_id = $id AND status = 'pending' ");
                    $get_image_fieldname = explode('__', $get_shortcode); 
                    $image_type = 'pods';                    
					$attach_id = self::$media_instance->media_handling( $get_original_image, $id, array(),'','pods',$hash_key,$templatekey);
					
                    if($attach_id){
                        //update_post_meta($id, $get_image_fieldname[1], $attach_id);
                        $this->update_db_values($id, $get_image_fieldname[1], $attach_id, $get_import_type);

                        if(!empty($get_image_meta)){
                            $image_meta = unserialize($get_image_meta);
                            self::$media_instance->acfimageMetaImports($attach_id, $image_meta, 'pods');
                        }

                        $this->update_status_shortcode_table($id, $get_shortcode, $get_original_image, 'completed');
                    }
                    else{
                        $this->update_status_shortcode_table($id, $get_shortcode,$get_original_image, 'failed');
                       
                        //update_post_meta($id, $get_image_fieldname[1], '');
                        $this->update_db_values($id, $get_image_fieldname[1], '', $get_import_type);
                    }
                }

                elseif( strpos($get_shortcode, 'product_image__') !== false ){
                    $get_gallery_images = $wpdb->get_results("SELECT original_image FROM {$wpdb->prefix}ultimate_cf_importer_shortcode_manager WHERE image_shortcode LIKE 'product_image__%' AND post_id = $id AND status = 'pending' ");
                    $get_image_fieldname = explode('__', $get_shortcode); 
                    $image_gallery = '';
                    $gallery_ids = [];
                    $image_type = 'product';
        
                    foreach($get_gallery_images as $gallery_key => $gallery_image){
                        $gallery_image_url = $gallery_image->original_image;
                   
                        $image_metas = [];
                        if(!empty($get_image_meta)){
                            $image_metas = unserialize($get_image_meta);
                            if(!empty($image_metas['product_file_name'][$gallery_key])){
                                $image_metas = $image_metas['product_file_name'][$gallery_key];
                            }
                        }
                        
                        $attach_id = self::$media_instance->media_handling( $gallery_image_url, $id, array(), null,'product-gallery', $hash_key,$templatekey);
                        if($attach_id){ 
                         
                            $image_gallery .= $attach_id . ',';
                            $gallery_ids[] = $attach_id;
                            $this->update_status_shortcode_table($id, $get_shortcode, $gallery_image_url,'completed');
                        }
                        else{
                            $this->update_status_shortcode_table($id, $get_shortcode, $gallery_image_url,'failed');
                        
                            //update_post_meta($id, $get_image_fieldname[1], '');
                            $this->update_db_values($id, $get_image_fieldname[1], '', $get_import_type);
                        }
                    } 
                       

                    if(!empty($image_gallery)){
                        $productImageGallery = substr($image_gallery, 0, -1);
                        //update_post_meta($id, '_'.$get_image_fieldname[1], $productImageGallery);
                        $this->update_db_values($id, '_'.$get_image_fieldname[1], $productImageGallery, $get_import_type);

                        if(!empty($get_image_meta)){
                            $image_meta = unserialize($get_image_meta);
                            //self::$media_instance->acfgalleryMetaImports($gallery_ids,$image_meta, 'product');	
                            self::$media_instance->acfImageMetaImports($gallery_ids,$image_meta, 'product');	
                        }                        
                       
                    }
                }

                elseif( strpos($get_shortcode, 'types_image__') !== false ){
                    $get_original_image = $wpdb->get_results("SELECT original_image FROM {$wpdb->prefix}ultimate_cf_importer_shortcode_manager WHERE image_shortcode LIKE 'types_image__%' AND post_id = $id AND status = 'pending' ",ARRAY_A);
                    $get_image_fieldname = explode('__', $get_shortcode); 
                    $gallery_ids = [];
                    $image_type = 'types';
                    foreach($get_original_image as $gallery_image){
                        
                        $attach_id = self::$media_instance->media_handling( $gallery_image['original_image'], $id,array(), null,'types', $hash_key,$templatekey);

                        if($attach_id){
                            $gallery_ids[] = $attach_id;
                            $this->update_status_shortcode_table($id, $get_shortcode,$gallery_image['original_image'], 'completed');
                        }
                        else{
                            $this->update_status_shortcode_table($id, $get_shortcode, $gallery_image['original_image'],'failed');
                        }
                    }

                    if($gallery_ids){
                        // delete dummy imagemeta
                        delete_post_meta($id, $get_image_fieldname[1]);
                        foreach($gallery_ids as $gallery_id){
                            $get_guid = $wpdb->get_var("SELECT guid FROM {$wpdb->prefix}posts WHERE ID = $gallery_id");
                            add_post_meta($id, $get_image_fieldname[1], $get_guid);
                        }
                    }

                    if(!empty($get_image_meta)){
                        $image_meta = unserialize($get_image_meta);
                        self::$media_instance->acfimageMetaImports($gallery_ids, $image_meta, 'types');
                    }
                    
                }   
                elseif(strpos($get_shortcode, 'jetengine_media_') !== false ){
                    $this->jetengine_image_update($id, $get_shortcode, $get_image_meta, 'jetengine_media_',$hash_key,$templatekey);
                }
                elseif(strpos($get_shortcode,'jetengine_repeater_media_')!== false){
                    $this->jetengine_image_update($id, $get_shortcode, $get_image_meta, 'jetengine_repeater_media_',$hash_key,$templatekey);
                } 
                elseif(strpos($get_shortcode, 'jetengine_gallery_') !== false ){
                    $this->jetengine_image_update($id, $get_shortcode, $get_image_meta, 'jetengine_gallery_',$hash_key,$templatekey);
                } 
                elseif(strpos($get_shortcode,'jetengine_repeater_gallery_')!== false){
                    $this->jetengine_image_update($id, $get_shortcode, $get_image_meta, 'jetengine_repeater_gallery_',$hash_key,$templatekey);
                }    
                elseif(strpos($get_shortcode,'jetenginecpt_media_') !==false){
                    $this->jetenginecpt_image_update($id, $get_shortcode, $get_image_meta, 'jetenginecpt_media_',$hash_key,$templatekey);
                }
                elseif(strpos($get_shortcode,'jetenginecpt_gallery_') !==false){
                    $this->jetenginecpt_image_update($id, $get_shortcode, $get_image_meta, 'jetenginecpt_gallery_',$hash_key,$templatekey);
                }   
                elseif(strpos($get_shortcode,'jetenginecpt_repeater_media_') !==false){
                    $this->jetenginecpt_image_update($id, $get_shortcode, $get_image_meta, 'jetenginecpt_repeater_media_',$hash_key,$templatekey);
                }
                elseif(strpos($get_shortcode,'jetenginecpt_repeater_gallery_') !==false){
                    $this->jetenginecpt_image_update($id, $get_shortcode, $get_image_meta, 'jetenginecpt_repeater_gallery_',$hash_key,$templatekey);
                }
                elseif(strpos($get_shortcode,'jetenginecct_media_') !==false){
                    $this->jetenginecct_image_update($id, $get_shortcode, $get_image_meta, 'jetenginecct_media_',$hash_key,$templatekey);
                }
                elseif(strpos($get_shortcode,'jetenginecct_gallery_') !==false){
                    $this->jetenginecct_image_update($id, $get_shortcode, $get_image_meta, 'jetenginecct_gallery_',$hash_key,$templatekey);
                }   
                elseif(strpos($get_shortcode,'jetenginecct_repeater_media_') !==false){
                    $this->jetenginecct_image_update($id, $get_shortcode, $get_image_meta, 'jetenginecct_repeater_media_',$hash_key,$templatekey);
                }
                elseif(strpos($get_shortcode,'jetenginecct_repeater_gallery_') !==false){
                    $this->jetenginecct_image_update($id, $get_shortcode, $get_image_meta, 'jetenginecct_repeater_gallery_',$hash_key,$templatekey);
                }
                elseif(strpos($get_shortcode,'jetenginetaxonomies_media_') !==false){
                    $this->jetenginetaxonomies_image_update($id, $get_shortcode, $get_image_meta, 'jetenginetaxonomies_media_',$hash_key,$templatekey);
                }
                elseif(strpos($get_shortcode,'jetenginetaxonomies_gallery_') !==false){
                    $this->jetenginetaxonomies_image_update($id, $get_shortcode, $get_image_meta, 'jetenginetaxonomies_gallery_',$hash_key,$templatekey);
                }   
                elseif(strpos($get_shortcode,'jetenginetaxonomies_repeater_media_') !==false){
                    $this->jetenginetaxonomies_image_update($id, $get_shortcode, $get_image_meta, 'jetenginetaxonomies_repeater_media_',$hash_key,$templatekey);
                }
                elseif(strpos($get_shortcode,'jetenginetaxonomies_repeater_gallery_') !==false){
                    $this->jetenginetaxonomies_image_update($id, $get_shortcode, $get_image_meta, 'jetenginetaxonomies_repeater_gallery_',$hash_key,$templatekey);
                }
            }
        }
    }
   
    public function update_status_shortcode_table($id, $get_shortcode, $get_origin_image, $status){
        global $wpdb;
        $wpdb->update( $wpdb->prefix . 'ultimate_cf_importer_shortcode_manager' , 
            array( 
                'status' => $status,
            ) , 
            array( 'post_id' => $id ,
                'image_shortcode' => $get_shortcode,
                'original_image' => $get_origin_image,
            ) 
        );
    }

    public function jetengine_image_update($id, $get_shortcode, $get_image_meta, $image_shortcode,$hash_key,$templatekey){
        
        $get_image_fieldname = explode('__', $get_shortcode); 
        $shortcode = end($get_image_fieldname);
        global $wpdb;
        $get_original_image = $wpdb->get_results("SELECT original_image FROM {$wpdb->prefix}ultimate_cf_importer_shortcode_manager WHERE image_shortcode LIKE '%$shortcode' AND post_id = $id AND status = 'pending' ",ARRAY_A);
        $image_meta = json_decode($get_image_meta);
        $header_array = $image_meta->headerarray;
        $value_array = $image_meta->valuearray;
        if($image_shortcode == 'jetengine_media_'){
            $image_type = 'jetengine_media';
            $rep = explode('__',$get_shortcode);
            $type =$rep[1];
            $listTaxonomy = get_taxonomies();
            foreach($get_original_image as $gallery_image){
                $get_origin_image = $gallery_image['original_image'];
                $attach_id = self::$media_instance->media_handling($gallery_image['original_image'],$id,'','',$image_type,$hash_key,$templatekey,'','',$header_array,$value_array);			
                if($attach_id){ 
                    $this->update_status_shortcode_table_jet($id, $get_shortcode,$get_origin_image, 'completed');
                    if($type == 'Users'){
                        update_user_meta($id, $shortcode, $attach_id);
                    }
                    elseif(in_array($type,$listTaxonomy)){
                        update_term_meta($id, $shortcode, $attach_id);
                    }
                    else{
                        update_post_meta($id, $shortcode, $attach_id);
                    }
                }
                else{
                    $this->update_status_shortcode_table_jet($id, $get_shortcode,$get_origin_image, 'failed');
                }
            }
        }
        elseif($image_shortcode == 'jetengine_repeater_media_'){
            $image_type = 'jetengine_repeater_media';
            $rep = explode('__',$get_shortcode);
            $type =$rep[1];
            $listTaxonomy = get_taxonomies();
            $get_origin_image = $gallery_image['original_image'];
            foreach($get_original_image as $gallery_image){
                $get_origin_image = $gallery_image['original_image'];
                $attach_id = self::$media_instance->media_handling($gallery_image['original_image'],$id,'','',$image_type,$hash_key,$templatekey,'','',$header_array,$value_array);			
                if($attach_id){
                    $this->update_status_shortcode_table_jet($id, $get_shortcode,$get_origin_image, 'Completed');
                    if($type == 'Users'){
                        $g_value = array();
                        $get_value = get_user_meta($id,$rep[3]);
                        foreach($get_value as $gkey => $gval){
                            foreach($gval as $gk => $gv){
                                if($rep[2] == $gk){
                                    $g_value[$gk][$shortcode] = $attach_id;        
                                }
                                else{
                                    $g_value[$gk][$shortcode] = $gv[$shortcode];        
                                }
                            }
                            
                        }
                        update_user_meta($id, $rep[3], $g_value);  
                    }
                    elseif(in_array($type,$listTaxonomy)){
                        $g_value = array();
                        $get_value = get_term_meta($id,$rep[3]);
                        foreach($get_value as $gkey => $gval){
                            foreach($gval as $gk => $gv){
                                if($rep[2] == $gk){
                                    $g_value[$gk][$shortcode] = $attach_id;        
                                }
                                else{
                                    $g_value[$gk][$shortcode] = $gv[$shortcode];        
                                }
                            }
                            
                        }
                        update_term_meta($id, $rep[3], $g_value);  
                    }
                    else{
                        $g_value = array();
                        $get_value = get_post_meta($id,$rep[3]);
                        foreach($get_value as $gkey => $gval){
                            foreach($gval as $gk => $gv){
                                if($rep[2] == $gk){
                                    $g_value[$gk][$shortcode] = $attach_id;        
                                }
                                else{
                                    $g_value[$gk][$shortcode] = $gv[$shortcode];        
                                }
                            }
                            
                        }
                        update_post_meta($id, $rep[3], $g_value);  
                    }
                   
                }
                else{
                    $this->update_status_shortcode_table_jet($id, $get_shortcode,$get_origin_image, 'failed');
                }
            }

        }
        elseif($image_shortcode == 'jetengine_repeater_gallery_'){
            $image_type = 'jetengine_repeater_gallery';
            $rep = explode('__',$get_shortcode);
            $type =$rep[1];
            $gallery_ids = '';
            $listTaxonomy = get_taxonomies();
            $get_origin_image = $gallery_image['original_image'];
            foreach($get_original_image as $gallery_image){
                $get_origin_image = $gallery_image['original_image'];
                $attach_id = self::$media_instance->media_handling($gallery_image['original_image'],$id,'','',$image_type,$hash_key,$templatekey,'','',$header_array,$value_array);			
                if($attach_id){
                    $gallery_ids .= $attach_id.','; 
                    $this->update_status_shortcode_table_jet($id, $get_shortcode,$get_origin_image, 'Completed');
                }
                else{
                    $this->update_status_shortcode_table_jet($id, $get_shortcode,$get_origin_image, 'failed');
                }
                
            }
            if(!empty($gallery_ids)){
                $g_value =array();
                if($type == 'Users'){
                    $gallery_ids =trim($gallery_ids,',');
                    $get_value = get_user_meta($id,$rep[3]);
                    foreach($get_value as $gkey => $gval){
                        foreach($gval as $gk => $gv){
                            if($rep[2] == $gk){
                                $g_value[$gk][$shortcode] = $gallery_ids;        
                            }
                            else{
                                $g_value[$gk][$shortcode] = $gv[$shortcode];        
                            }
                        }
                        
                    }
                    update_user_meta($id,$rep[3], $g_value);
                }
                elseif(in_array($type,$listTaxonomy)){
                    $gallery_ids =trim($gallery_ids,',');
                    $get_value = get_term_meta($id,$rep[3]);
                    foreach($get_value as $gkey => $gval){
                        foreach($gval as $gk => $gv){
                            if($rep[2] == $gk){
                                $g_value[$gk][$shortcode] = $gallery_ids;        
                            }
                            else{
                                $g_value[$gk][$shortcode] = $gv[$shortcode];        
                            }
                        }
                        
                    }
                    update_term_meta($id,$rep[3], $g_value);
                }
                else{
                    $gallery_ids =trim($gallery_ids,',');
                    $get_value = get_post_meta($id,$rep[3]);
                    foreach($get_value as $gkey => $gval){
                        foreach($gval as $gk => $gv){
                            if($rep[2] == $gk){
                                $g_value[$gk][$shortcode] = $gallery_ids;        
                            }
                            else{
                                $g_value[$gk][$shortcode] = $gv[$shortcode];        
                            }
                        }
                        
                    }
                    update_post_meta($id,$rep[3], $g_value);
                }
            }

        }
        elseif($image_shortcode == 'jetengine_gallery_'){
            $gallery_ids = '';
            $image_type = 'jetengine_gallery';
            $rep = explode('__',$get_shortcode);
            $type =$rep[1];
            $listTaxonomy = get_taxonomies();
            foreach($get_original_image as $gallery_image){
                $get_origin_image = $gallery_image['original_image'];
                $attach_id = self::$media_instance->media_handling($gallery_image['original_image'],$id,'','',$image_type,$hash_key,$templatekey,'','',$header_array,$value_array);			
                if($attach_id){
                    $gallery_ids .= $attach_id.','; 
                    $this->update_status_shortcode_table_jet($id, $get_shortcode,$get_origin_image, 'Completed');
                }
                else{
                    $this->update_status_shortcode_table_jet($id, $get_shortcode,$get_origin_image, 'failed');
                }
            }
            if(!empty($gallery_ids)){
                $gallery_ids =trim($gallery_ids,',');
                if($type == 'Users'){
                    update_user_meta($id,$shortcode, $gallery_ids);
                }
                elseif(in_array($type,$listTaxonomy)){
                    update_term_meta($id,$shortcode, $gallery_ids);
                }
                else{
                    update_post_meta($id,$shortcode, $gallery_ids);
                }
            }
        }

    }

    public function jetenginecpt_image_update($id, $get_shortcode, $get_image_meta, $image_shortcode,$hash_key,$templatekey){
        $get_image_fieldname = explode('__', $get_shortcode); 
        $shortcode = end($get_image_fieldname);
        global $wpdb;
        $get_original_image = $wpdb->get_results("SELECT original_image FROM {$wpdb->prefix}ultimate_cf_importer_shortcode_manager WHERE image_shortcode LIKE '%$shortcode' AND post_id = $id AND status = 'pending' ",ARRAY_A);
        $image_meta = json_decode($get_image_meta);
        $header_array = $image_meta->headerarray;
        $value_array = $image_meta->valuearray;
    
        if($image_shortcode == 'jetenginecpt_media_'){
            $image_type = 'jetenginecpt_media';
            foreach($get_original_image as $gallery_image){
                $get_origin_image = $gallery_image['original_image'];
                $attach_id = self::$media_instance->media_handling($gallery_image['original_image'],$id,'','',$image_type,$hash_key,$templatekey,'','',$header_array,$value_array);			
                if($attach_id){
                    update_post_meta($id, $shortcode, $attach_id);
                    $this->update_status_shortcode_table_jet($id, $get_shortcode,$get_origin_image, 'completed');
                }
                else{
                    $this->update_status_shortcode_table_jet($id, $get_shortcode,$get_origin_image, 'failed');
                }
            }
        }
        elseif($image_shortcode == 'jetenginecpt_repeater_media_'){
            $image_type = 'jetenginecpt_repeater_media';
            $rep = explode('__',$get_shortcode);
            $get_origin_image = $gallery_image['original_image'];
            foreach($get_original_image as $gallery_image){
                $get_origin_image = $gallery_image['original_image'];
                $attach_id = self::$media_instance->media_handling($gallery_image['original_image'],$id,'','',$image_type,$hash_key,$templatekey,'','',$header_array,$value_array);			
                if($attach_id){
                    $this->update_status_shortcode_table_jet($id, $get_shortcode,$get_origin_image, 'Completed');
                    $g_value = array();
                    $get_value = get_post_meta($id,$rep[2]);
                    foreach($get_value as $gkey => $gval){
                        foreach($gval as $gk => $gv){
                            if($rep[1] == $gk){
                                $g_value[$gk][$shortcode] = $attach_id;        
                            }
                            else{
                                $g_value[$gk][$shortcode] = $gv[$shortcode];        
                            }
                        }
                        
                    }
                    update_post_meta($id, $rep[2], $g_value);  
                   
                }
                else{
                    $this->update_status_shortcode_table_jet($id, $get_shortcode,$get_origin_image, 'failed');
                }
            }

        }
        elseif($image_shortcode == 'jetenginecpt_repeater_gallery_'){
            $image_type = 'jetenginecpt_repeater_gallery';
            $rep = explode('__',$get_shortcode);
            $gallery_ids = '';
            $get_origin_image = $gallery_image['original_image'];
            foreach($get_original_image as $gallery_image){
                $get_origin_image = $gallery_image['original_image'];
                $attach_id = self::$media_instance->media_handling($gallery_image['original_image'],$id,'','',$image_type,$hash_key,$templatekey,'','',$header_array,$value_array);			
                if($attach_id){
                    $gallery_ids .= $attach_id.','; 
                    $this->update_status_shortcode_table_jet($id, $get_shortcode,$get_origin_image, 'Completed');
                }
                else{
                    $this->update_status_shortcode_table_jet($id, $get_shortcode,$get_origin_image, 'failed');
                }
                
            }
            if(!empty($gallery_ids)){
                $g_value =array();
               
                $gallery_ids =trim($gallery_ids,',');
                $get_value = get_post_meta($id,$rep[2]);
                foreach($get_value as $gkey => $gval){
                    foreach($gval as $gk => $gv){
                        if($rep[1] == $gk){
                            $g_value[$gk][$shortcode] = $gallery_ids;        
                        }
                        else{
                            $g_value[$gk][$shortcode] = $gv[$shortcode];        
                        }
                    }
                    
                }
                update_post_meta($id,$rep[2], $g_value);
               
            }

        }
        elseif($image_shortcode == 'jetenginecpt_gallery_'){
            $gallery_ids = '';
            $image_type = 'jetenginecpt_gallery';
            foreach($get_original_image as $gallery_image){
                $get_origin_image = $gallery_image['original_image'];
                $attach_id = self::$media_instance->media_handling($gallery_image['original_image'],$id,'','',$image_type,$hash_key,$templatekey,'','',$header_array,$value_array);			
                if($attach_id){
                    $gallery_ids .= $attach_id.','; 
                    $this->update_status_shortcode_table_jet($id, $get_shortcode,$get_origin_image, 'Completed');
                }
                else{
                    $this->update_status_shortcode_table_jet($id, $get_shortcode,$get_origin_image, 'failed');
                }
            }
            if(!empty($gallery_ids)){
                $gallery_ids =trim($gallery_ids,',');
                update_post_meta($id,$shortcode, $gallery_ids);
                
            }
        }
    }

    public function acf_image_update($id, $image_type,$get_shortcode, $get_image_meta, $image_shortcode, $get_import_type,$hash_key, $templatekey){        
        global $wpdb;        
        $get_image_fieldname = explode('__', $get_shortcode); 
        if($image_shortcode == 'acf_group_repeater_image__' || $image_shortcode == 'acf_repeater_group_image__' ){
            $shortcode = $get_image_fieldname[1];
            $get_original_image = $wpdb->get_var("SELECT original_image FROM {$wpdb->prefix}ultimate_cf_importer_shortcode_manager WHERE image_shortcode LIKE '%$shortcode' AND post_id = $id ");
        }
        else{           
            $get_original_image = $wpdb->get_var("SELECT original_image FROM {$wpdb->prefix}ultimate_cf_importer_shortcode_manager WHERE image_shortcode LIKE '$get_shortcode' AND post_id = $id ");
        }

        $acf_key = $wpdb->get_var("SELECT hash_key FROM {$wpdb->prefix}ultimate_cf_importer_shortcode_manager WHERE post_id = $id  ");         
        $attach_id = self::$media_instance->media_handling( $get_original_image, $id,array(),'','',$hash_key, $templatekey);       
        if($attach_id){
           // update_post_meta($id, $get_image_fieldname[1], $attach_id);
            $this->update_db_values($id, $get_image_fieldname[1], $attach_id, $get_import_type);

            if(!empty($get_image_meta)){
                $image_meta = unserialize($get_image_meta);
                self::$media_instance->acfimageMetaImports($attach_id, $image_meta, 'acf');
            }
            $this->update_status_shortcode_table($id, $get_shortcode,$get_original_image, 'completed');
        }
        else{
            $this->update_status_shortcode_table($id, $get_shortcode, $get_original_image,'failed');
           
            //update_post_meta($id, $get_image_fieldname[1], '');
            $this->update_db_values($id, $get_image_fieldname[1], '', $get_import_type);
        }   
    }

    public function acf_gallery_image_update($id, $get_shortcode, $get_image_meta, $image_shortcode, $get_import_type,$hash_key, $templatekey){
        global $wpdb;
        $get_image_fieldname = explode('__', $get_shortcode);
        if($image_shortcode == 'acf_repeater_gallery_image__' || $image_shortcode == 'acf_group_repeater_gallery_image__' || $image_shortcode == 'acf_repeater_group_gallery_image__' ){
            $shortcode = $get_image_fieldname[1];
            $get_gallery_images = $wpdb->get_results("SELECT original_image FROM {$wpdb->prefix}ultimate_cf_importer_shortcode_manager WHERE image_shortcode LIKE '%$shortcode' AND post_id = $id ", ARRAY_A);  
        }
        else{
            $get_gallery_images = $wpdb->get_results("SELECT original_image FROM {$wpdb->prefix}ultimate_cf_importer_shortcode_manager WHERE image_shortcode LIKE '$image_shortcode%' AND post_id = $id ", ARRAY_A);
        }
       
        $acf_key = $wpdb->get_var("SELECT hash_key FROM {$wpdb->prefix}ultimate_cf_importer_shortcode_manager WHERE post_id = $id  ");
        $image_type = chop($image_shortcode,'_image__');
        $image_media_table = $wpdb->prefix . 'ultimate_csv_importer_media_report';
        
        $gallery_ids = [];
        $get_existing_gallery_ids = get_post_meta($id, $get_image_fieldname[1]);
        if(!empty($get_existing_gallery_ids[0]) && is_array($get_existing_gallery_ids[0])){
            $gallery_ids = $get_existing_gallery_ids[0];
        }
    
        foreach($get_gallery_images as $gallery_image){
            $attach_id = self::$media_instance->media_handling( $gallery_image['original_image'], $id,array(),'','',$hash_key, $templatekey);

            if($attach_id){ 
                $gallery_ids[] = $attach_id;
                $this->update_status_shortcode_table($id, $get_shortcode, $gallery_image,'completed');
            }
            else{
                $this->update_status_shortcode_table($id, $get_shortcode,$gallery_image, 'failed');
            }
        } 
      
        if(!empty($gallery_ids)){
            if( strpos($get_shortcode, 'wordpress_custom_image__') !== false) {
               // update_post_meta($id, 'image_gallery_ids', $imgs);
                $this->update_db_values($id, 'image_gallery_ids', $imgs, $get_import_type);
            }
            else{
                //update_post_meta($id, $get_image_fieldname[1], $gallery_ids);
                $this->update_db_values($id, $get_image_fieldname[1], $gallery_ids, $get_import_type);
            }
            if(!empty($get_image_meta)){
                $image_meta = unserialize($get_image_meta);
                self::$media_instance->acfgalleryMetaImports($gallery_ids,$image_meta, 'acf');	
            }            
        }    
    }

    public function jetenginecct_image_update($id, $get_shortcode, $get_image_meta, $image_shortcode,$hash_key,$templatekey){
        $get_image_fieldname = explode('__', $get_shortcode); 
        $shortcode = end($get_image_fieldname);
        global $wpdb;
        $get_original_image = $wpdb->get_results("SELECT original_image FROM {$wpdb->prefix}ultimate_cf_importer_shortcode_manager WHERE image_shortcode LIKE '%$shortcode' AND post_id = $id AND status = 'pending' ",ARRAY_A);
        $image_meta = json_decode($get_image_meta);
        $header_array = $image_meta->headerarray;
        $value_array = $image_meta->valuearray;
    
        if($image_shortcode == 'jetenginecct_media_'){
            $image_type = 'jetenginecct_media';
            $rep = explode('__',$get_shortcode);
            $type= $rep[1];
            $table_name = 'jet_cct_'.$type;
            foreach($get_original_image as $gallery_image){
                $get_origin_image = $gallery_image['original_image'];
                $attach_id = self::$media_instance->media_handling($gallery_image['original_image'],$id,'','',$image_type,$hash_key,$templatekey,'','',$header_array,$value_array);			
                if($attach_id){
                    $this->update_status_shortcode_table_jet($id, $get_shortcode,$get_origin_image, 'completed');
                    $sql = $wpdb->prepare(
                        "UPDATE {$wpdb->prefix}$table_name SET $shortcode = '$attach_id' WHERE _ID = %d;",
                        $id
                        );
                    $wpdb->query( $sql );
                    // update_post_meta($id, $shortcode, $attach_id);
                  
                }
                else{
                    $this->update_status_shortcode_table_jet($id, $get_shortcode,$get_origin_image, 'failed');
                }
            }
        }
        elseif($image_shortcode == 'jetenginecct_repeater_media_'){
            $image_type = 'jetenginecct_repeater_media';
            $rep = explode('__',$get_shortcode);
            $table_name = 'jet_cct_'.$type;
            $type= $rep[1];
            $get_origin_image = $gallery_image['original_image'];
            foreach($get_original_image as $gallery_image){
                $get_origin_image = $gallery_image['original_image'];
                $attach_id = self::$media_instance->media_handling($gallery_image['original_image'],$id,'','',$image_type,$hash_key,$templatekey,'','',$header_array,$value_array);			
                if($attach_id){
                    $this->update_status_shortcode_table_jet($id, $get_shortcode,$get_origin_image, 'Completed');
                    $g_value = array(); 
                    $sql = $wpdb->prepare(
                        "SELECT $rep[3] FROM {$wpdb->prefix}$table_name WHERE _ID = %d;",
                        $id
                        );
                        $get_value = $wpdb->query( $sql );
                    foreach($get_value as $gkey => $gval){
                        foreach($gval as $gk => $gv){
                            if($rep[2] == $gk){
                                $g_value[$gk][$shortcode] = $attach_id;        
                            }
                            else{
                                $g_value[$gk][$shortcode] = $gv[$shortcode];        
                            }
                        }
                        
                    }
                    $sql = $wpdb->prepare(
                        "UPDATE {$wpdb->prefix}$table_name SET $rep[3] = '$g_value' WHERE _ID = %d;",
                        $id
                        );
                    $wpdb->query( $sql );
                }
                else{
                    $this->update_status_shortcode_table_jet($id, $get_shortcode,$get_origin_image, 'failed');
                }
            }

        }
        elseif($image_shortcode == 'jetenginecct_repeater_gallery_'){
            $image_type = 'jetenginecct_repeater_gallery';
            $rep = explode('__',$get_shortcode);
            $gallery_ids = '';
            $table_name = 'jet_cct_'.$type;
            $rep = explode('__',$get_shortcode);
            $type= $rep[1];
            $get_origin_image = $gallery_image['original_image'];
            foreach($get_original_image as $gallery_image){
                $get_origin_image = $gallery_image['original_image'];
                $attach_id = self::$media_instance->media_handling($gallery_image['original_image'],$id,'','',$image_type,$hash_key,$templatekey,'','',$header_array,$value_array);			
                if($attach_id){
                    $gallery_ids .= $attach_id.','; 
                    $this->update_status_shortcode_table_jet($id, $get_shortcode,$get_origin_image, 'Completed');
                }
                else{
                    $this->update_status_shortcode_table_jet($id, $get_shortcode,$get_origin_image, 'failed');
                }
                
            }
            if(!empty($gallery_ids)){
                $g_value =array();
                $gallery_ids =trim($gallery_ids,',');
                $sql = $wpdb->prepare(
                    "SELECT $rep[3] FROM {$wpdb->prefix}$table_name WHERE _ID = %d;",
                    $id
                    );
                    $get_value = $wpdb->query( $sql );
                foreach($get_value as $gkey => $gval){
                    foreach($gval as $gk => $gv){
                        if($rep[2] == $gk){
                            $g_value[$gk][$shortcode] = $gallery_ids;        
                        }
                        else{
                            $g_value[$gk][$shortcode] = $gv[$shortcode];        
                        }
                    }
                    
                }
                $sql = $wpdb->prepare(
                    "UPDATE {$wpdb->prefix}$table_name SET $rep[3] = '$g_value' WHERE _ID = %d;",
                    $id
                    );
                $wpdb->query( $sql );
            }

        }
        elseif($image_shortcode == 'jetenginecct_gallery_'){
            $gallery_ids = '';
            $image_type = 'jetenginecct_gallery';
            $table_name = 'jet_cct_'.$type;
            $rep = explode('__',$get_shortcode);
            $type= $rep[1];
            foreach($get_original_image as $gallery_image){
                $get_origin_image = $gallery_image['original_image'];
                $attach_id = self::$media_instance->media_handling($gallery_image['original_image'],$id,'','',$image_type,$hash_key,$templatekey,'','',$header_array,$value_array);			
                if($attach_id){
                    $gallery_ids .= $attach_id.','; 
                    $this->update_status_shortcode_table_jet($id, $get_shortcode,$get_origin_image, 'Completed');
                }
                else{
                    $this->update_status_shortcode_table_jet($id, $get_shortcode,$get_origin_image, 'failed');
                }
            }
            if(!empty($gallery_ids)){
                $gallery_ids =trim($gallery_ids,',');
                $sql = $wpdb->prepare(
                    "UPDATE {$wpdb->prefix}$table_name SET $shortcode = '$gallery_ids' WHERE _ID = %d;",
                    $id
                    );
                $wpdb->query( $sql );
            }
        }
    }

    public function jetenginetaxonomies_image_update($id, $get_shortcode, $get_image_meta, $image_shortcode,$hash_key,$templatekey){
        $get_image_fieldname = explode('__', $get_shortcode); 
        $shortcode = end($get_image_fieldname);
        global $wpdb;
        $get_original_image = $wpdb->get_results("SELECT original_image FROM {$wpdb->prefix}ultimate_cf_importer_shortcode_manager WHERE image_shortcode LIKE '%$shortcode' AND post_id = $id AND status = 'pending' ",ARRAY_A);
        $image_meta = json_decode($get_image_meta);
        $header_array = $image_meta->headerarray;
        $value_array = $image_meta->valuearray;
    
        if($image_shortcode == 'jetenginetaxonomies_media_'){
            $image_type = 'jetenginetaxonomies_media';
            foreach($get_original_image as $gallery_image){
                $get_origin_image = $gallery_image['original_image'];
                $attach_id = self::$media_instance->media_handling($gallery_image['original_image'],$id,'','',$image_type,$hash_key,$templatekey,'','',$header_array,$value_array);			
                if($attach_id){
                    update_term_meta($id, $shortcode, $attach_id);
                    $this->update_status_shortcode_table_jet($id, $get_shortcode,$get_origin_image, 'completed');
                }
                else{
                    $this->update_status_shortcode_table_jet($id, $get_shortcode,$get_origin_image, 'failed');
                }
            }
        }
        elseif($image_shortcode == 'jetenginetaxonomies_repeater_media_'){
            $image_type = 'jetenginetaxonomies_repeater_media';
            $rep = explode('__',$get_shortcode);
            $get_origin_image = $gallery_image['original_image'];
            foreach($get_original_image as $gallery_image){
                $get_origin_image = $gallery_image['original_image'];
                $attach_id = self::$media_instance->media_handling($gallery_image['original_image'],$id,'','',$image_type,$hash_key,$templatekey,'','',$header_array,$value_array);			
                if($attach_id){
                    $this->update_status_shortcode_table_jet($id, $get_shortcode,$get_origin_image, 'Completed');
                    $g_value = array();
                    $get_value = get_term_meta($id,$rep[2]);  
                    foreach($get_value as $gkey => $gval){
                        foreach($gval as $gk => $gv){
                            if($rep[1] == $gk){
                                $g_value[$gk][$shortcode] = $attach_id;        
                            }
                            else{
                                $g_value[$gk][$shortcode] = $gv[$shortcode];        
                            }
                        }
                        
                    }
                    update_term_meta($id, $rep[2], $g_value);  
                   
                }
                else{
                    $this->update_status_shortcode_table_jet($id, $get_shortcode,$get_origin_image, 'failed');
                }
            }

        }
        elseif($image_shortcode == 'jetenginetaxonomies_repeater_gallery_'){
            $image_type = 'jetenginetaxonomies_repeater_gallery';
            $rep = explode('__',$get_shortcode);
            $gallery_ids = '';
            $get_origin_image = $gallery_image['original_image'];
            foreach($get_original_image as $gallery_image){
                $get_origin_image = $gallery_image['original_image'];
                $attach_id = self::$media_instance->media_handling($gallery_image['original_image'],$id,'','',$image_type,$hash_key,$templatekey,'','',$header_array,$value_array);			
                if($attach_id){
                    $gallery_ids .= $attach_id.','; 
                    $this->update_status_shortcode_table_jet($id, $get_shortcode,$get_origin_image, 'Completed');
                }
                else{
                    $this->update_status_shortcode_table_jet($id, $get_shortcode,$get_origin_image, 'failed');
                }
                
            }
            if(!empty($gallery_ids)){
                $g_value =array();
                $gallery_ids =trim($gallery_ids,',');
                $get_value = get_term_meta($id,$rep[2]);
                foreach($get_value as $gkey => $gval){
                    foreach($gval as $gk => $gv){
                        if($rep[1] == $gk){
                            $g_value[$gk][$shortcode] = $gallery_ids;        
                        }
                        else{
                            $g_value[$gk][$shortcode] = $gv[$shortcode];        
                        }
                    }
                    
                }
                update_term_meta($id,$rep[2], $g_value);
               
            }

        }
        elseif($image_shortcode == 'jetenginetaxonomies_gallery_'){
            $gallery_ids = '';
            $image_type = 'jetenginetaxonomies_gallery';
            foreach($get_original_image as $gallery_image){
                $get_origin_image = $gallery_image['original_image'];
                $attach_id = self::$media_instance->media_handling($gallery_image['original_image'],$id,'','',$image_type,$hash_key,$templatekey,'','',$header_array,$value_array);			
                if($attach_id){
                    $gallery_ids .= $attach_id.','; 
                    $this->update_status_shortcode_table_jet($id, $get_shortcode,$get_origin_image, 'Completed');
                }
                else{
                    $this->update_status_shortcode_table_jet($id, $get_shortcode,$get_origin_image, 'failed');
                }
            }
            if(!empty($gallery_ids)){
                $gallery_ids =trim($gallery_ids,',');
                update_term_meta($id,$shortcode, $gallery_ids);
                
            }
        }
    }

    public function update_status_shortcode_table_jet($id, $get_shortcode,$get_origin_image, $status){
        global $wpdb;
        $wpdb->update( $wpdb->prefix . 'ultimate_cf_importer_shortcode_manager' , 
            array( 
                'status' => $status,
            ) , 
            array( 'post_id' => $id ,
                'image_shortcode' => $get_shortcode,
                'original_image' => $get_origin_image,
            ) 
        );
    }
    
    public function update_db_values($post_id, $meta_key, $meta_value, $import_type){
        global $wpdb;
        if($import_type == 'post'){
            update_post_meta($post_id, $meta_key, $meta_value);
        }
        elseif($import_type == 'term'){
            update_term_meta($post_id, $meta_key, $meta_value);
        }
        elseif($import_type == 'user'){
            update_user_meta($post_id, $meta_key, $meta_value);
        }
    }

    public function images_import_function($id, $get_shortcode, $hash_key, $templatekey, $image_type, $get_import_type){
        global $wpdb;
        $get_original_image = $wpdb->get_var("SELECT original_image FROM {$wpdb->prefix}ultimate_cf_importer_shortcode_manager WHERE post_id = $id AND status = 'pending' ");
        $get_image_fieldname = explode('__', $get_shortcode); 
        $attach_id = self::$media_instance->media_handling( $get_original_image, $id, array(),'',$image_type,$hash_key, $templatekey);
    
        if($attach_id){
            if($image_type == 'wpmember'){
                update_user_meta($id, $get_image_fieldname[1], $attach_id);
            }
            elseif($image_type == 'cfs'){
                //update_post_meta($id, $get_image_fieldname[1], $attach_id);
                $this->update_db_values($id, $get_image_fieldname[1], $attach_id, $get_import_type);
            }
            elseif($image_type == 'term'){
                update_term_meta($id, $get_image_fieldname[1], $attach_id);
            }

            $this->update_status_shortcode_table($id, $get_shortcode,$get_original_image, 'completed');
        }
        else{
            $this->update_status_shortcode_table($id, $get_shortcode, $get_original_image, 'failed');
        }
    }
}