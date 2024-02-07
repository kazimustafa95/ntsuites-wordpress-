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

class BBPressExtension extends ExtensionHandler{
	private static $instance = null;

    public static function getInstance() {		
        if (BBPressExtension::$instance == null) {
            BBPressExtension::$instance = new BBPressExtension;
        }
        return BBPressExtension::$instance;
    }


    public function processExtension($data){        
        $import_type = $data;
        $response = [];
        //$import_type = $this->import_type_as($import_type);
        if(is_plugin_active('bbpress/bbpress.php')){   
            if($import_type == 'forum'){
                $bbpress_meta_fields = array(
                            'Type' => '_bbp_forum_type',
                            'Status' => '_bbp_status',
                            'Visibility' => 'Visibility',
                            'post_parent' => 'post_parent',

                );

            }

            if($import_type == 'topic'){            
                $bbpress_meta_fields = array(
                            'Forum_name' => 'forum_name',
                            'Status' => 'status',
                            'Author' => 'author',
                            'Author_ip' => '_bbp_author_ip',
                            'Type' =>'type',
                        );
            }

            if($import_type == 'reply'){            
                $bbpress_meta_fields = array(
                    'Forum_name' => 'forum_name',
                    'Topic_name' => 'topic_name',
                    'Status' => 'status',
                    'Author' => 'author',
                    'Author_ip' => '_bbp_author_ip',
                        );
            }
        }

        $bbpress_meta_field_key = $this->convert_static_fields_to_array($bbpress_meta_fields);
        
        if($data == 'forum'){
            $response['forum_attributes_fields'] = $bbpress_meta_field_key; 
        }
        if($data == 'topic'){
            $response['topic_attributes_fields'] = $bbpress_meta_field_key; 
        }  
        if($data == 'reply'){
            $response['reply_attributes_fields'] = $bbpress_meta_field_key; 
        }   
		return $response;
			
    }

    public function extensionSupportedImportType($import_type ){
        if(is_plugin_active('bbpress/bbpress.php')){
           // $import_type = $this->import_name_as($import_type);
            if($import_type == 'forum' || $import_type == 'topic' || $import_type == 'reply') { 
                return true;
            }else{
                return false;
            }
        }
	}
}
