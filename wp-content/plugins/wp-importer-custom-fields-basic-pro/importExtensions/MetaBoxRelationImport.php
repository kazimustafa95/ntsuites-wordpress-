<?php
/******************************************************************************************
 * Copyright (C) Smackcoders. - All Rights Reserved under Smackcoders Proprietary License
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/

namespace Smackcoders\CFCSV;

if (!defined('ABSPATH')) exit; // Exit if accessed directly
class MetaBoxRelationsImport
{
    private static $metabox_relations_instance = null, $media_instance;

    public static function getInstance()
    {

        if (MetaBoxRelationsImport::$metabox_relations_instance == null)
        {
            MetaBoxRelationsImport::$metabox_relations_instance = new MetaBoxRelationsImport;
            MetaBoxRelationsImport::$media_instance = new MediaHandling();
            return MetaBoxRelationsImport::$metabox_relations_instance;
        }
        return MetaBoxRelationsImport::$metabox_relations_instance;
    }
    function set_metabox_relations_values($header_array, $value_array, $map, $post_id, $type, $mode)
    {

        $post_values = [];
        $helpers_instance = ImportHelpers::getInstance();
        $post_values = $helpers_instance->get_header_values($map, $header_array, $value_array);

        $this->metabox_relations_import_function($post_values, $post_id, $header_array, $value_array, $type, $mode);
    }

    public function metabox_relations_import_function($data_array, $pID, $header_array, $value_array, $type, $mode)
    {

        global $wpdb;
        $helpers_instance = ImportHelpers::getInstance();
        $media_instance = MediaHandling::getInstance();
        $extension_object = new ExtensionHandler;
        $import_as = $extension_object->import_post_types($type);
        $taxonomies = get_taxonomies();
        if ($import_as == 'user')
        {
            $get_metabox_fields = \rwmb_get_object_fields($import_as, 'user');
        }
        else if (array_key_exists($import_as, $taxonomies))
        {
            $get_metabox_fields = \rwmb_get_object_fields($import_as, 'term');
        }
        else
        {
            $get_metabox_fields = \rwmb_get_object_fields($import_as);
        }

        foreach ($get_metabox_fields as $meta_key => $meta_value)
        {
            $metabox_relation_table = $wpdb->prefix . "mb_relationships";
            if ($meta_value['relationship'] == 1)
            {
                if ($meta_value['type'] == 'user')
                {
                    $post_type = 'user';
                }
                else if ($meta_value['type'] == 'post')
                {
                    $post_type = $meta_value['post_type'][0];
                }
                else
                {
                    $post_type = $meta_value['taxonomy'][0];
                }
                if (strpos($meta_key, '_to') !== false)
                {
                    $types = 'from';
                }
                else
                {
                    $types = 'to';
                }
                if ($mode == 'Update')
                {
                    if ($types == 'from')
                    {
                        $wpdb->delete($metabox_relation_table, array(
                            'from' => $pID
                        ));
                    }
                    else
                    {
                        $wpdb->delete($metabox_relation_table, array(
                            'to' => $pID
                        ));
                    }
                }
                $meta_title_name = explode('_', $meta_key);
                $meta_title_name = $meta_title_name[0];
                $datavalue = explode(',', $data_array[$meta_key]);
                $i = 1;
                foreach ($datavalue as $d_val)
                {
                    if ($post_type == 'user')
                    {
                        $relate_id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}users WHERE display_name ='$d_val'");
                    }
                    else if (array_key_exists($post_type, $taxonomies))
                    {
                        $relate_id = $wpdb->get_var("SELECT t.term_id FROM {$wpdb->prefix}terms as t join {$wpdb->prefix}term_taxonomy as tt WHERE t.name='$d_val' AND tt.taxonomy='$post_type'");
                    }
                    else
                    {
                        $relate_id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}posts WHERE post_title='$d_val' AND post_type='$post_type'");
                    }
                    if($relate_id) {
                    if ($types == 'from')
                    {
                        $wpdb->insert($metabox_relation_table, array(
                            'from' => $pID,
                            'to' => $relate_id,
                            'type' => $meta_title_name,
                            'order_from' => $i,
                            'order_to' => 0
                        ));
                    }
                    else
                    {
                        $wpdb->insert($metabox_relation_table, array(
                            'from' => $relate_id,
                            'to' => $pID,
                            'type' => $meta_title_name,
                            'order_from' => 0,
                            'order_to' => $i
                        ));
                    }
                }
                    $i++;
                }
            }
        }

    }

}

