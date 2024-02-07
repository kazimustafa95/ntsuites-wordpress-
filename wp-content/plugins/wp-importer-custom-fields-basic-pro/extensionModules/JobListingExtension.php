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
class JobListingExtension extends ExtensionHandler{
    private static $instance = null;
    
    public static function getInstance() {
        if (JobListingExtension::$instance == null) {
            JobListingExtension::$instance = new JobListingExtension;
        }
        return JobListingExtension::$instance;
    }
     /**
	* Provides Job Manager mapping fields for specific post type
	* @param string $data - selected import type
	* @return array - mapping fields
	*/
    public function processExtension($data) {
        $response = [];
        $jobFields = array(
			'Application email/URL' => 'application',
			'Company Website' => 'company_website',
			'Company Twitter' => 'company_twitter',
			'Position Filled' => 'filled',
			'Listing Expiry Date' => 'job_expires',
            'Location' => 'job_location',
            'Company Name' => 'company_name',
            'Company Tagline' => 'company_tagline',
            'Company Video' => 'company_video',
            'Featured Listing' => 'featured',
        );
		$job_listing_value = $this->convert_static_fields_to_array($jobFields);
		$response['job_listing_fields'] = $job_listing_value ;
		return $response;
    }

	/**
	* Job Manager extension supported import types
	* @param string $import_type - selected import type
	* @return boolean
	*/
    public function extensionSupportedImportType($import_type){
		if(is_plugin_active('wp-job-manager/wp-job-manager.php')){
			if($import_type == 'job_listing') {
				return true;
			}
			else{
				return false;
			}
		}
	}
}