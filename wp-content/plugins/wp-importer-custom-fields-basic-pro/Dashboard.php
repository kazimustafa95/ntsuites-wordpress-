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

class Dashboard {
	private static $dashboard_instance = null;
	private static $extension_instance = null;

	private function __construct(){
		add_action('wp_ajax_LineChart',array($this,'fetch_LineChart_data'));
		add_action('wp_ajax_PieChart',array($this,'fetch_PieChart_data'));
		add_action('wp_ajax_BarChart',array($this,'fetch_BarStackedChart_data'));
		add_action('wp_ajax_checkExtensions',array($this,'check_extensions'));
		add_action('wp_ajax_listuploads',array($this,'list_upload_function'));
		add_action('wp_ajax_locklist',array($this,'lock_list_function'));
	}

	public static function getInstance() {
		if (Dashboard::$dashboard_instance == null) {
			Dashboard::$dashboard_instance = new Dashboard;
			Dashboard::$extension_instance = new ExtensionHandler;
			return Dashboard::$dashboard_instance;
		}
		return Dashboard::$dashboard_instance;
	}

	public function fetch_LineChart_data() {
		check_ajax_referer('smack-importer-custom-fields-basic-pro', 'securekey');
		global $wpdb;
		$available_types = array();
		$data_arr = array();
		$cli_template = $wpdb->prefix ."cli_cf_template";
		$log_table_name = $wpdb->prefix. "cfimport_detail_log";
		foreach(Dashboard::$extension_instance->get_import_post_types() as $name => $type) {
			$available_types[$name] = $type;
		}
		foreach (get_taxonomies() as $item => $taxonomy_name) {
			$available_types[$item] = $taxonomy_name;
		}
		$available_types = array_flip($available_types);
		$myarr = array();
		$today = date("Y-m-d H:i:s");
		$j = 0;
		for($i = 11; $i >= 0; $i--) {
			$month[$j] = date("M", strtotime( $today." -$i months"));
			$year[$j]  = date("Y", strtotime( $today." -$i months"));
			$j++;
		}
		$get_list_of_imported_types = $wpdb->get_results("SELECT distinct( import_type ) FROM ultimate_cfimporter_pro_smackuci_events", ARRAY_A);
		$get_list_of_imported_types = array_column($get_list_of_imported_types, 'import_type');
		$cli_list_of_imported_types = $wpdb->get_results("select distinct(type) from $cli_template", ARRAY_A);
		$cli_list_of_imported_types = array_column($cli_list_of_imported_types,'type');
		if(empty($get_list_of_imported_types) && !empty($cli_list_of_imported_types)){
			$get_list_of_imported_types = $cli_list_of_imported_types;
		}	
		else {
			foreach($cli_list_of_imported_types as $import_type){
				$type = in_array($import_type,$get_list_of_imported_types,true);				
				if(!$type){
					array_push($get_list_of_imported_types,$import_type);
				}
			}
		}

		foreach($get_list_of_imported_types as $import_type) {
			$data = array();
			for($i = 0; $i <= 11; $i++) {
				//$count = 0;
				$get_chart_data = $wpdb->get_results("SELECT sum(created) as '$import_type' from ultimate_cfimporter_pro_smackuci_events where import_type = '$import_type' and month = '$month[$i]' and year = '$year[$i]' ");
				if($import_type != 'event-recurring'){
				$get_cli_data = $wpdb->get_results("select sum(log.created) as '$import_type' from $log_table_name as log inner join $cli_template as cli on cli.templatekey = log.templatekey
				where cli.type = '$import_type' and cli.month = '$month[$i]' and cli.year = '$year[$i]' group by cli.type");				
				}

				$created_chart = !empty($get_chart_data) ? $get_chart_data[0]->$import_type : 0;
				$created_clichart = !empty($get_cli_data) ? $get_cli_data[0]->$import_type : 0;
				$data[] = 	$created_chart + $created_clichart;				
			}				
	
			if(array_key_exists($import_type,$available_types)){
				$import_type_data[] = $available_types[$import_type];
			} else {
				$import_type_data[] = $import_type;
			}
			array_push($data_arr , $data);	
				
		}
		$myarr['success'] = true;
		$myarr['label'] = isset($import_type_data) ? $import_type_data : '';
		$myarr['data'] = $data_arr;
		echo wp_json_encode($myarr);
		wp_die();
	}

	public function fetch_PieChart_data() {
		global $wpdb;
		$available_types = array();
		foreach(Dashboard::$extension_instance->get_import_post_types() as $name => $type) {
			$available_types[$name] = $type;
		}
		foreach (get_taxonomies() as $item => $taxonomy_name) {
			$available_types[$item] = $taxonomy_name;
		}
		$available_types = array_flip($available_types);
		$returnArray = array();
		$today = date("Y-m-d H:i:s");
		$j = 0;
		for($i = 11; $i >= 0; $i--) {
			$month[$j] = date("M", strtotime( $today." -$i months"));
			$year[$j]  = date("Y", strtotime( $today." -$i months"));
			$j++;
		}
		$get_list_of_imported_types = $wpdb->get_results("SELECT distinct( import_type ) FROM ultimate_cfimporter_pro_smackuci_events", ARRAY_A);
		$get_list_of_imported_types = array_column($get_list_of_imported_types, 'import_type');

		$count = 1;
		foreach($get_list_of_imported_types as $import_type) {
			$get_chart_data = $wpdb->get_results( "SELECT sum(created) AS $import_type FROM ultimate_cfimporter_pro_smackuci_events WHERE import_type = '$import_type' ");

			if(array_key_exists($import_type,$available_types)){
				$import_type_data = $available_types[$import_type];
			} else {
				$import_type_data = $import_type;
			}
			if($get_chart_data[0]->$import_type) {
				$data = $get_chart_data[0]->$import_type;
				$returnArray['success'] = true;
				$returnArray[ $count ][ $import_type_data ] = $data;
			} else {
				$returnArray['success'] = true;
				$returnArray[ $count ][ $import_type_data ] = 0;
			}
			$count++;
		}
		echo wp_json_encode($returnArray);
		wp_die();
	}

	public function fetch_BarStackedChart_data() {
		check_ajax_referer('smack-importer-custom-fields-basic-pro', 'securekey');
		global $wpdb;
		$cli_template = $wpdb->prefix ."cli_cf_template";
		$log_table_name = $wpdb->prefix. "cfimport_detail_log";
		$available_types = array();
		foreach(Dashboard::$extension_instance->get_import_post_types() as $name => $type) {
			$available_types[$name] = $type;
		}
		foreach (get_taxonomies() as $item => $taxonomy_name) {
			$available_types[$item] = $taxonomy_name;
		}
		$available_types = array_flip($available_types);
		$returnArray = array();
		$today = date("Y-m-d H:i:s");
		$j = 0;
		for($i = 11; $i >= 0; $i--) {
			$month[$j] = date("M", strtotime( $today." -$i months"));
			$year[$j]  = date("Y", strtotime( $today." -$i months"));
			$j++;
		}
		$get_list_of_imported_types = $wpdb->get_results("SELECT distinct( import_type ) FROM ultimate_cfimporter_pro_smackuci_events", ARRAY_A);
		$cli_list_of_imported_types = $wpdb->get_results("select distinct(type) from $cli_template", ARRAY_A);
		$cli_list_of_imported_types = array_column($cli_list_of_imported_types,'type');
		$get_list_of_imported_types = array_column($get_list_of_imported_types, 'import_type');

		if(empty($get_list_of_imported_types) && !empty($cli_list_of_imported_types)){
			$get_list_of_imported_types = $cli_list_of_imported_types;
		}	
		else {
			foreach($cli_list_of_imported_types as $import_type){
				$type = in_array($import_type,$get_list_of_imported_types,true);				
				if(!$type){
					array_push($get_list_of_imported_types,$import_type);
				}
			}
		}	


		$count = 1;
		foreach($get_list_of_imported_types as $import_type) {
			$get_chart_data = $wpdb->get_results("select sum(created) as created, sum(updated) as updated, sum(skipped) as skipped from ultimate_cfimporter_pro_smackuci_events where import_type = '$import_type' ");						
			$get_cli_data = $wpdb->get_results("select sum(log.created) as created,sum(log.updated) as updated,sum(log.skipped) as skipped from $log_table_name as log inner join $cli_template as cli on cli.templatekey = log.templatekey where cli.type = '$import_type' group by cli.type");						
			
            if(array_key_exists($import_type,$available_types)){
				$import_type_data = $available_types[$import_type];
			} else {
				$import_type_data = $import_type;
			}
			$returnArray['success'] = true;

			$created_chart = !empty($get_chart_data) ? $get_chart_data[0]->created : 0;
			$created_clichart = !empty($get_cli_data) ? $get_cli_data[0]->created : 0;
			$returnArray[ $import_type_data ]['created'] = $created_chart + $created_clichart;

			$updated_chart = !empty($get_chart_data) ? $get_chart_data[0]->updated : 0;
			$updated_clichart = !empty($get_cli_data) ? $get_cli_data[0]->updated : 0;
			$returnArray[ $import_type_data ]['updated'] = $updated_chart + $updated_clichart;

			$skipped_chart = !empty($get_chart_data) ? $get_chart_data[0]->skipped : 0;
			$skipped_clichart = !empty($get_cli_data) ? $get_cli_data[0]->skipped : 0;
			$returnArray[ $import_type_data ]['skipped'] = $skipped_chart + $skipped_clichart;
		
			$count++;
		}
		echo wp_json_encode($returnArray);
		wp_die();
	}

	public function list_upload_function(){
		global $wpdb;
		$response =[];		
		$file_table_name = $wpdb->prefix ."smackcf_file_events";
		$last_data = $wpdb->get_results("SELECT * FROM $file_table_name order by id desc limit 10",ARRAY_A); 

		if(!$last_data){
			$response['success'] = false;
			$response['message'] = 'not exists';
			echo wp_json_encode($response);
		}else{
			$response['success'] = true;
			$response['message'] = $last_data;
			echo wp_json_encode($response);
		}

		wp_die();
	}

	public function get_config_bytes($val) {
		$val = trim($val);
		$last = strtolower($val[strlen($val) - 1]);
		switch ($last) {
		case 'g':
			$val *= 1024;
		case 'm':
			$val *= 1024;
		case 'k':
			$val *= 1024;
		}
		return $val;
	}

	public function lock_list_function(){
		$response=[];
		global $wpdb;
		$file_table_name = $wpdb->prefix ."smackcf_file_events";
		$lock_data = $wpdb->get_results("SELECT * FROM $file_table_name WHERE `lock`=true");
		if(!$lock_data){
			$response['success'] = false;
			$response['message'] = 'No lock exists';
			echo wp_json_encode($response);
		}else{
			$response['success'] = true;
			$response['mesage'] = $lock_data;
			echo wp_json_encode($response);
		}
		wp_die();
	}
}
