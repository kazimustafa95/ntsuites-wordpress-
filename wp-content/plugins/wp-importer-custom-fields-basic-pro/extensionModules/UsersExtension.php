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

class UsersExtension extends ExtensionHandler{
		private static $instance = null;

    public static function getInstance() {
		
				if (UsersExtension::$instance == null) {
                    UsersExtension::$instance = new UsersExtension;
				}
				return UsersExtension::$instance;
    }

    /**
	* Provides Users fields for specific post type
	* @param string $data - selected import type
	* @return array - mapping fields
	*/
    public function processExtension($data){ 
        $response = [];
    
        if(is_plugin_active('wp-members/wp-members.php')){
            $wp_members_fields = $this->custom_fields_by_wp_members();
            $response['custom_fields_wp_members'] = $wp_members_fields;
               
        }
        if(is_plugin_active('members/members.php')){
            $members_fields = $this->custom_fields_by_members();
            $response['custom_fields_members'] =  $members_fields;
                
        } 
        if(is_plugin_active('ultimate-member/ultimate-member.php')){
            $members_fields = $this->custom_fields_by_ultimate_member();
            $response['custom_ultimate_members'] =  $members_fields;   
        } 
		return $response;	
    }

    public function custom_fields_by_wp_members () {
        $WPMemberFields = array();   
        $get_WPMembers_fields = get_option('wpmembers_fields');
        $search_array = array('Choose a Username', 'First Name', 'Last Name', 'Email', 'Confirm Email', 'Website', 'Biographical Info', 'Password', 'Confirm Password', 'Terms of Service');

		if (is_array($get_WPMembers_fields) && !empty($get_WPMembers_fields)) {
           
			foreach ($get_WPMembers_fields as $get_fields) {
                    foreach($search_array as $search_values){         
                        if(is_array($get_fields)){   
                            if(in_array($search_values , $get_fields)){
                                unset($get_fields);
                            }
                        }
                    }
                if(!empty($get_fields[2])){
                    $WPMemberFields['WPMEMBERS'][$get_fields[2]]['label'] = $get_fields[1];
                    $WPMemberFields['WPMEMBERS'][$get_fields[2]]['name'] = $get_fields[2];
                }
            }
        }
        
        $wp_mem_fields = $this->convert_fields_to_array($WPMemberFields);
        return $wp_mem_fields;
    }

    public function custom_fields_by_members () {
		$MemberFields = array();
		$MemberFields['MULTIROLE']['multi_user_role']['label'] = 'Multi User Role';
        $MemberFields['MULTIROLE']['multi_user_role']['name'] = 'multi_user_role';
        $mem_fields = $this->convert_fields_to_array($MemberFields);
		return $mem_fields;
    }
    
    public function custom_fields_by_ultimate_member () {
		$WPUltimateMember = array();
		$get_WPUltimateMember = get_option('um_fields');
		if(is_array($get_WPUltimateMember) && !empty($get_WPUltimateMember)) {
			foreach($get_WPUltimateMember as $get_fields) {
				$WPUltimateMember['ULTIMATEMEMBER'][$get_fields['metakey']]['label'] = $get_fields['label'];
				$WPUltimateMember['ULTIMATEMEMBER'][$get_fields['metakey']]['name'] = $get_fields['metakey'];
			}
        }
        $ultimate_member_fields = $this->convert_fields_to_array($WPUltimateMember);
		return $ultimate_member_fields;
	}
    
    /**
	* Users extension supported import types
	* @param string $import_type - selected import type
	* @return boolean
	*/
    public function extensionSupportedImportType($import_type ){
		if($import_type == 'Users'){
            return true;
        }
	}
        
 }