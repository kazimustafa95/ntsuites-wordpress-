<?php
/******************************************************************************************
 * Copyright (C) Smackcoders. - All Rights Reserved under Smackcoders Proprietary License
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/

namespace Smackcoders\CFCSV;

if (!defined('ABSPATH')) exit; // Exit if accessed directly
class MetaBoxGroupExtension extends ExtensionHandler
{
    private static $instance = null;

    public static function getInstance()
    {
        if (MetaBoxGroupExtension::$instance == null)
        {
            MetaBoxGroupExtension::$instance = new MetaBoxGroupExtension;
        }
        return MetaBoxGroupExtension::$instance;
    }

    /**
     * Provides Metabox mapping fields for specific post type
     * @param string $data - selected import type
     * @return array - mapping fields
     */
    public function processExtension($data)
    {
        global $wpdb;
        $response = [];
        $import_type = $this->import_post_types($data);
        $metabox_fields = [];
        $taxonomies = get_taxonomies();
        if ($import_type == 'user')
        {
            $get_metabox_fields = \rwmb_get_object_fields($import_type, 'user');
        }
        else if (array_key_exists($import_type, $taxonomies))
        {
            $get_metabox_fields = \rwmb_get_object_fields($import_type, 'term');
        }
        else
        {           
            $get_metabox_fields = \rwmb_get_object_fields($import_type);
        }        
        
        if (!empty($get_metabox_fields))
        {
            foreach($get_metabox_fields as $metakey => $metavalue){
                if(isset($metavalue['type']) && $metavalue['type'] == 'group'){
                    //get the group field data
                    $customFields = $this->get_groupfields($metavalue['fields']);                   

                }
                $mb_value = isset($customFields) ? $this->convert_static_fields_to_array($customFields) : "";
            }            
        }
        else
        {
            $mb_value = '';
        }

        $response['metabox_group_fields'] = $mb_value;         
        return $response;
    }

    public function get_groupfields($fields){
        static $grpfields;
        foreach($fields as $key => $fieldData){
            if($fieldData['type'] == 'group'){
                $grpfields = $this->get_groupfields($fieldData['fields']);
            }
            else {
                $grpfields[$fieldData['id']] = $fieldData['name'];
            }
        }        
        
        return $grpfields;
    }

    /**
     * Metabox extension supported import types
     * @param string $import_type - selected import type
     * @return boolean
     */
    public function extensionSupportedImportType($import_type)
    {        
        if (is_plugin_active('meta-box-aio/meta-box-aio.php'))
        {
            if ($import_type == 'nav_menu_item')
            {
                return false;
            }
            $import_type = $this->import_name_as($import_type);
            if($import_type == 'Posts' || $import_type == 'Pages' || $import_type == 'CustomPosts' || $import_type == 'event' || $import_type == 'event-recurring' || $import_type == 'Users' || $import_type == 'Taxonomies'  || $import_type =='Tags' || $import_type =='Categories') 
            {
                return true;
            }
            else
            {
                return false;
            }
        }
    }

    function import_post_types($import_type, $importAs = null)
    {
        $import_type = trim($import_type);

        $module = array(
            'Posts' => 'post',
            'Pages' => 'page',
            'Users' => 'user',
            'Comments' => 'comments',
            'Taxonomies' => $importAs,
            'CustomPosts' => $importAs
        );
        foreach (get_taxonomies() as $key => $taxonomy)
        {
            $module[$taxonomy] = $taxonomy;
        }
        if (array_key_exists($import_type, $module))
        {
            return $module[$import_type];
        }
        else
        {
            return $import_type;
        }
    }

    public function convert_static_fields_to_array($static_value){
        if (is_array($static_value) || is_object($static_value)){
            foreach($static_value as $key=>$values){
                $static_fields_getting[] = array('label' => $values,                                                
                                                'name' => $key			
                );
            }
        }
        $static_fields_getting=isset($static_fields_getting)?$static_fields_getting:'';
        return $static_fields_getting;
    }
    
}

