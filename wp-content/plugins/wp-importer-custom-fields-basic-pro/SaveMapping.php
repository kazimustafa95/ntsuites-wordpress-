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

$import_extensions = glob( __DIR__ . '/importExtensions/*.php');

foreach ($import_extensions as $import_extension_value) {
	require_once($import_extension_value);
}

class SaveMapping{
	private static $instance=null,$validatefile;
	private static $smackcsv_instance = null,$media_instance;
	private static $core = null,$nextgen_instance,$mapping_instance;
	private static $imageschedule_instance = null;

	private function __construct(){
		add_action('wp_ajax_saveMappedFields',array($this,'check_templatename_exists'));
		add_action('wp_ajax_StartImport' , array($this,'background_starts_function'));
		add_action('wp_ajax_GetProgress',array($this,'import_detail_function'));
		add_action('wp_ajax_ImportState',array($this,'import_state_function'));
		add_action('wp_ajax_ImportStop',array($this,'import_stop_function'));
		add_action('wp_ajax_checkmain_mode',array($this,'checkmain_mode'));
		add_action('wp_ajax_disable_main_mode',array($this,'disable_main_mode'));
		add_action('wp_ajax_bulk_file_import',array($this,'bulk_file_import_function'));
		add_action('wp_ajax_bulk_import',array($this,'bulk_import'));
		add_action('wp_ajax_PauseImport',array($this,'pause_import'));
		add_action('wp_ajax_ResumeImport',array($this,'resume_import'));
		add_action('wp_ajax_send_error_status', array($this, 'send_error_status'));		
		add_action( 'smackcf_image_schedule_hook', array($this, 'smackcf_image_schedule_function'),10,4 );
	}

	public static function getInstance() {
		if (SaveMapping::$instance == null) {
			SaveMapping::$instance = new SaveMapping;
			SaveMapping::$media_instance = MediaHandling::getInstance();
			SaveMapping::$validatefile = new ValidateFile;
			SaveMapping::$smackcsv_instance = SmackCSV::getInstance();
			SaveMapping::$mapping_instance = MappingExtension::getInstance();
			SaveMapping::$nextgen_instance = new NextGenGalleryImport;
			SaveMapping::$imageschedule_instance = ImageSchedule::getInstance();
			return SaveMapping::$instance;
		}
		return SaveMapping::$instance;
	}

	public static function disable_main_mode(){
		$disable_option = sanitize_text_field($_POST['option']);
		delete_option($disable_option);
		$result['success'] = true;
		echo wp_json_encode($result);
		wp_die();
	}

	public static function checkmain_mode(){
		check_ajax_referer('smack-importer-custom-fields-basic-pro', 'securekey');
		$ucisettings = get_option('sm_uci_pro_settings');
		if(isset($ucisettings['enable_main_mode']) && $ucisettings['enable_main_mode'] == 'true') {
			$result['success'] = true;
		}
		else{
			$result['success'] = false;
		}
		echo wp_json_encode($result);
		wp_die();
	}

	public function check_templatename_exists(){
		check_ajax_referer('smack-importer-custom-fields-basic-pro', 'securekey');
		$use_template = sanitize_text_field($_POST['UseTemplateState']);
		$template_name = sanitize_text_field($_POST['TemplateName']);	
		$response = [];

		if($use_template === 'true'){	
			$response['success'] = $this->save_temp_fields();

		}else{
			global $wpdb;
			$template_table_name = "ultimate_cfimporter_pro_mappingtemplate";
			$get_template_names = $wpdb->get_results( "SELECT templatename FROM $template_table_name" );	
			if(!empty($get_template_names)){

				foreach($get_template_names as $temp_names){
					$inserted_temp_names[] = $temp_names->templatename;
				}
				if(in_array($template_name , $inserted_temp_names) && $template_name != ''){
					$response['success'] = false;
					$response['message'] = 'Template Name Already Exists';
				}else{
					$response['success'] = $this->save_fields_function();
				}

			}else{	
				$response['success'] = $this->save_fields_function();
			}
		}
		echo wp_json_encode($response); 	
		wp_die();

	}

	public function save_temp_fields(){

		$type          = sanitize_text_field($_POST['Types']);
		$map_fields    = sanitize_text_field($_POST['MappedFields']);	
		$template_name = sanitize_text_field($_POST['TemplateName']);
		$new_template_name = sanitize_text_field($_POST['NewTemplate']);
		$mapping_type = sanitize_text_field($_POST['MappingType']);
		$hash_key = sanitize_key($_POST['HashKey']);
		$response = [];

		global $wpdb;
		$template_table_name = "ultimate_cfimporter_pro_mappingtemplate";
		$get_detail = null;
		$get_detail   = $wpdb->get_results( "SELECT id FROM $template_table_name WHERE templatename = '$template_name' " );
		$get_id = $get_detail[0]->id;

		$mapped_fields = json_decode(stripslashes($map_fields),true);

		$mapping_fields = serialize( $mapped_fields );
		//added for saving serialized value with apostrophe
		$mapping_fields = $wpdb->_real_escape($mapping_fields);
		$mapping_fields = str_replace("\\","",$mapping_fields);
		$map = unserialize($mapping_fields);
		if(array_key_exists('GF', $map) || array_key_exists('RF', $map) || array_key_exists('FC', $map)){
			foreach($map as $key => $value){
				$newmapdata = [];
				switch($key) {
					case 'GF':
					case 'RF':
					case 'FC':
						foreach($value as $name => $fvalue) {							
							$fkey = $wpdb->get_var("select post_name from {$wpdb->prefix}posts where post_excerpt = '$fvalue'");
							if(!empty($fkey))
							$newmapdata[$fkey] = $fvalue;
						}
						break;
					default:
						$newmapdata = $value;
				}				
				$map[$key] = $newmapdata;
			}
		}
		$mapping_fields = serialize($map);	
		$time = date('Y-m-d h:i:s');

		if(!empty($new_template_name)){
			$wpdb->get_results("UPDATE $template_table_name SET templatename = '$new_template_name' , mapping ='$mapping_fields' , createdtime = '$time' , module = '$type' , eventKey = '$hash_key' , mapping_type = '$mapping_type' WHERE id = $get_id ");	
		}else{	
			//$wpdb->get_results("UPDATE $template_table_name SET eventKey = '$hash_key' , mapping_type = '$mapping_type' WHERE id = $get_id ");	
			//changed
			$wpdb->get_results("UPDATE $template_table_name SET mapping ='$mapping_fields', eventKey = '$hash_key', mapping_type = '$mapping_type' WHERE id = $get_id ");

		}

		return true;

	}

	public function save_fields_function() {
		global $wpdb;
		$hash_key      = sanitize_key($_POST['HashKey']);
		$type          = sanitize_text_field($_POST['Types']);
		$map_fields    = sanitize_text_field($_POST['MappedFields']);	
		$template_name = sanitize_text_field($_POST['TemplateName']);
		$mapping_type = sanitize_text_field($_POST['MappingType']);
		$template_table_name = "ultimate_cfimporter_pro_mappingtemplate";
		$file_table_name = $wpdb->prefix . "smackcf_file_events";
		$mapped_fields = json_decode(stripslashes($map_fields),true);
		$mapping_fields = serialize( $mapped_fields );
		$map_field_value=unserialize($mapping_fields);
		foreach ($map_field_value as $key => $value) {
			
			foreach($value as $map_key=>$map_value){
				if (is_int($map_key)) {
					
					unset($value[$map_key]);
					
				}else{
					$map[$key][$map_key]=$map_value;
				}

				
			}	
		}
		$mapping_fields = serialize($map);
		$time = date('Y-m-d h:i:s');
		$get_detail   = $wpdb->get_results( "SELECT file_name FROM $file_table_name WHERE `hash_key` = '$hash_key'" );
		$get_file_name = $get_detail[0]->file_name;
		$get_hash = $wpdb->get_results( "SELECT eventKey FROM $template_table_name" );

		if(!empty($get_hash)){
			foreach($get_hash as $hash_values){
				$inserted_hash_values[] = $hash_values->eventKey;
			}
			if(in_array($hash_key , $inserted_hash_values)){
				$wpdb->get_results("UPDATE $template_table_name SET templatename = '$template_name' , mapping ='$mapping_fields' , createdtime = '$time' , module = '$type' , mapping_type = '$mapping_type' WHERE eventKey = '$hash_key'");	
			}
			else{
				$wpdb->get_results( "INSERT INTO $template_table_name(templatename ,mapping ,createdtime ,module,csvname ,eventKey , mapping_type)values('$template_name','$mapping_fields' , '$time' , '$type' , '$get_file_name', '$hash_key', '$mapping_type')" );	
			}
		}else{
			$wpdb->get_results( "INSERT INTO $template_table_name(templatename ,mapping ,createdtime ,module,csvname ,eventKey , mapping_type)values('$template_name','$mapping_fields' , '$time' , '$type' , '$get_file_name', '$hash_key' , '$mapping_type' )" );

		}
		return true;
	}

	public function import_detail_function(){
		check_ajax_referer('smack-importer-custom-fields-basic-pro', 'securekey');
		$hash_key = sanitize_key($_POST['HashKey']);
		$response = [];

		global $wpdb;
		$log_table_name = $wpdb->prefix . "cfimport_detail_log";
		$file_table_name = $wpdb->prefix ."smackcf_file_events";

		$file_records = $wpdb->get_results("SELECT mode FROM $file_table_name WHERE hash_key = '$hash_key' ",ARRAY_A);
		$mode = $file_records[0]['mode'];

		if($mode == 'Insert'){
			$method = 'Import';
		}
		if($mode == 'Update'){
			$method = 'Update';
		}
		if($mode == 'Import-Update'){
			$method = 'Import-Update';
		}

		$total_records = $wpdb->get_results("SELECT file_name , total_records , processing_records ,status ,remaining_records , filesize FROM $log_table_name WHERE hash_key = '$hash_key' ",ARRAY_A);

		$response['success'] = true;
		$response['file_name']= $total_records[0]['file_name'];
		$response['total_records']= $total_records[0]['total_records'];
		$response['processing_records']= $total_records[0]['processing_records'];
		$response['remaining_records']= $total_records[0]['remaining_records'];
		$response['status']= $total_records[0]['status'];
		$response['filesize'] = $total_records[0]['filesize'];
		$response['method'] = $method;

		if($total_records[0]['status'] == 'Completed'){
			$response['progress'] = false;
		}else{
			$response['progress'] = true;
		}
		$response['Info'] = [];

		echo wp_json_encode($response); 
		wp_die();
	}

	public function import_state_function(){
		check_ajax_referer('smack-importer-custom-fields-basic-pro', 'securekey');
		$response = [];
		$hash_key = sanitize_key($_POST['HashKey']);
		$upload = wp_upload_dir();
		$upload_base_url = $upload['baseurl'];
		$upload_url = $upload_base_url . '/smack_uci_uploads/imports/';
		$upload_dir = SaveMapping::$smackcsv_instance->create_upload_dir();
		$log_path = $upload_dir.$hash_key.'/'.$hash_key.'.html';
		if(file_exists($log_path)){
			$log_link_path = $upload_url. $hash_key .'/'.$hash_key.'.html';
		}

		$import_txt_path = $upload_dir.'import_state.txt';
		chmod($import_txt_path , 0777);
		$import_state_arr = array();

		if(sanitize_text_field($_POST['State']) == 'true'){
			//first check then set on
			$open_file = fopen( $import_txt_path , "w");
			$import_state_arr = array('import_state' => 'on','import_stop' => 'on');
			$state_arr = serialize($import_state_arr);
			fwrite($open_file , $state_arr);
			fclose($open_file);

			$response['import_state'] = false;	
		}
		if(sanitize_text_field($_POST['State']) == 'false'){
			//first check then set off	
			$open_file = fopen( $import_txt_path , "w");
			$import_state_arr = array('import_state' => 'off','import_stop' => 'on');
			$state_arr = serialize($import_state_arr);
			fwrite($open_file , $state_arr);
			fclose($open_file);

			if ($log_link_path != null){
				$response['show_log'] = true;	
			}
			else{
				$response['show_log'] = false;
			}

			$response['import_state'] = true;
			$response['log_link'] = $log_link_path;			
		}	
		echo wp_json_encode($response);
		wp_die();
	}

	public function import_stop_function(){
		check_ajax_referer('smack-importer-custom-fields-basic-pro', 'securekey');
		$upload_dir = SaveMapping::$smackcsv_instance->create_upload_dir();
		/* Gets string 'false' when page is refreshed */

		if(sanitize_text_field($_POST['Stop']) == 'false'){
			$import_txt_path = $upload_dir.'import_state.txt';
			chmod($import_txt_path , 0777);
			$import_state_arr = array();

			$open_file = fopen( $import_txt_path , "w");
			$import_state_arr = array('import_state' => 'on','import_stop' => 'off');
			$state_arr = serialize($import_state_arr);
			fwrite($open_file , $state_arr);
			fclose($open_file);
		}
		wp_die();
	}

	public function parse_element($xml,$query){
		$query = strip_tags($query);
		$xpath = new \DOMXPath($xml);
		$entries = $xpath->query($query);
		$content = $entries->item(0)->textContent;
		return $content;
	}
	
	public function background_starts_function(){		
		check_ajax_referer('smack-importer-custom-fields-basic-pro', 'securekey');
		global $wpdb;
		$hash_key  = sanitize_key($_POST['HashKey']);
		$check = sanitize_text_field($_POST['Check']);
		$rollback_option = sanitize_text_field($_POST['RollBack']);
		$unmatched_row_value = get_option('sm_uci_pro_settings');
		$unmatched_row = $unmatched_row_value['unmatchedrow'];
		//first check then set true	
		$upload_dir = SaveMapping::$smackcsv_instance->create_upload_dir();
		$import_txt_path = $upload_dir.'import_state.txt';
		chmod($import_txt_path , 0777);
		$import_state_arr = array();

		$open_file = fopen( $import_txt_path , "w");
		$import_state_arr = array('import_state' => 'on','import_stop' => 'on');
		$state_arr = serialize($import_state_arr);
		fwrite($open_file , $state_arr);
		fclose($open_file);

		$helpers_instance = ImportHelpers::getInstance();
		$core_instance = CoreFieldsImport::getInstance();
		$import_config_instance = ImportConfiguration::getInstance();
		$file_manager_instance = FileManager::getInstance();
		$log_manager_instance = LogManager::getInstance();
		global $core_instance;

		$file_table_name = $wpdb->prefix ."smackcf_file_events";
		$template_table_name = "ultimate_cfimporter_pro_mappingtemplate";
		$log_table_name = $wpdb->prefix ."cfimport_detail_log";
		$response = [];

		$background_values = $wpdb->get_results("SELECT mapping , module  FROM $template_table_name WHERE `eventKey` = '$hash_key' ");
		foreach($background_values as $values){
			$mapped_fields_values = $values->mapping;	
			$selected_type = $values->module;
		}
	
		if($rollback_option === 'true'){
			$tables = $import_config_instance->get_rollback_tables($selected_type);
			$import_config_instance->set_backup_restore($hash_key, 'backup', $tables);	
		}

		$get_id = $wpdb->get_results( "SELECT id , mode ,file_name , total_rows FROM $file_table_name WHERE `hash_key` = '$hash_key'");

		$get_mode = $get_id[0]->mode;
		$total_rows = $get_id[0]->total_rows;
		$file_name = $get_id[0]->file_name;
		$file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
		if(empty($file_extension)){
			$file_extension = 'xml';
		}
		$file_size = filesize($upload_dir.$hash_key.'/'.$hash_key);
		$filesize = $helpers_instance->formatSizeUnits($file_size);

		$remain_records = $total_rows - 1;
		$wpdb->insert( $log_table_name , array('file_name' => $file_name , 'hash_key' => $hash_key , 'total_records' => $total_rows , 'filesize' => $filesize , 'processing_records' => 1 , 'remaining_records' => $remain_records, 'status' => 'Processing' ) );

		$map = unserialize($mapped_fields_values);
		$gmode = 'Normal';
		if($file_extension == 'csv' || $file_extension == 'txt'){

			ini_set("auto_detect_line_endings", true	);
			$info = [];
			if (($h = fopen($upload_dir.$hash_key.'/'.$hash_key, "r")) !== FALSE) 
			{
				// Convert each line into the local $data variable
				$line_number = 0;
				$header_array = [];
				$value_array = [];
				$addHeader = true;
				$delimiters = array( ',','\t',';','|',':','&nbsp');
				$file_path = $upload_dir . $hash_key . '/' . $hash_key;
				$delimiter = SaveMapping::$validatefile->getFileDelimiter($file_path, 5);
				$array_index = array_search($delimiter,$delimiters);
				if($array_index == 5){
					$delimiters[$array_index] = ' ';
				}
				while(($data = fgetcsv($h, 0, $delimiters[$array_index]))!== FALSE) 
				{		
					// Read the data from a single line
					$trimmed_array = array_map('trim', $data);
					array_push($info , $trimmed_array);
					if($line_number == 0){
						$header_array = $info[$line_number];

					}else{
						$value_array = $info[$line_number];
						$get_arr = $this->main_import_process($map , $header_array ,$value_array , $selected_type , $get_mode, $line_number , $unmatched_row, $check , $hash_key, '', $gmode);
						$post_id = $get_arr['id'];	
						$core_instance->detailed_log = $get_arr['detail_log'];
						$helpers_instance->get_post_ids($post_id ,$hash_key);
						$remaining_records = $total_rows - $line_number;
						$wpdb->get_results("UPDATE $log_table_name SET processing_records = $line_number , remaining_records = $remaining_records , status = 'Processing' WHERE hash_key = '$hash_key'");

						if($line_number == $total_rows){
							$wpdb->get_results("UPDATE $log_table_name SET status = 'Completed' WHERE hash_key = '$hash_key'");
						}

						if (count($core_instance->detailed_log) > 5) {
							$log_manager_instance->get_event_log($hash_key , $file_name , $file_extension, $get_mode , $total_rows , $selected_type , $core_instance->detailed_log, $addHeader);
							$addHeader = false;
							$core_instance->detailed_log = [];
						}

					}

					// get the pause or resume state
					$open_txt = fopen($import_txt_path , "r");
					$read_text_ser = fread($open_txt , filesize($import_txt_path));  
					$read_state = unserialize($read_text_ser);    
					fclose($open_txt);

					if($read_state['import_stop'] == 'off'){
						return;
					}

					while($read_state['import_state'] == 'off'){	
						$open_txts = fopen($import_txt_path , "r");
						$read_text_sers = fread($open_txts , filesize($import_txt_path));  
						$read_states = unserialize($read_text_sers);    
						fclose($open_txts);

						if($read_states['import_state'] == 'on'){
							break;
						}

						if($read_states['import_stop'] == 'off'){
							return;
						}
					}
					$line_number++;			
				}
				fclose($h);
			}
		}
		if($file_extension == 'xml'){
			$path = $upload_dir . $hash_key . '/' . $hash_key;
			$line_number = 0;
			$header_array = [];
			$value_array = [];
			$addHeader = true;
			
			for ($line_number = 0; $line_number < $total_rows; $line_number++) {
				$xml_class = new XmlHandler();
				$parse_xml = $xml_class->parse_xmls($hash_key,$line_number);
				$i = 0;
				foreach($parse_xml as $xml_key => $xml_value){
					if(is_array($xml_value)){
						foreach ($xml_value as $e_key => $e_value){
							$header_array['header'][$i] = $e_value['name'];
							$value_array['value'][$i] = $e_value['value'];
							$i++;
						}
					}
				}
				$xml = simplexml_load_file($path);
				$xml_arr = json_decode( json_encode($xml) , 1);
					if (count($xml_arr) == count($xml_arr, COUNT_RECURSIVE)) 
					{
						$item = $xml->addchild('item');
						foreach($xml_arr as $key => $value){
							$xml->item->addchild($key,$value);
							unset($xml->$key);
						}
						$arraytype = "not parent";
						$xmls['item'] =$xml_arr;
					}
					else
					{
						$arraytype = "parent";
					}
					$childs=array();
					$s=0;
					foreach($xml->children() as $child => $val){   
						// $tag = $child->getName(); 
						$tag = (array)$val;
						if(empty($tag)){   
							if (!in_array($child, $childs,true))
							{
								$childs[$s++] = $child;
	
							}    
						} 
						else{
							if(array_key_exists("@attributes",$tag)){
								if (!in_array($child, $childs,true))
								{
									$childs[$s++] = $child;
								}   
							}
							else{
								foreach($tag as $k => $v){
									$checks =(string)$tag[$k];
									if(is_numeric($k)){
										if(empty($checks)){
											if (!in_array($child, $childs,true))
											{
												$childs[$s++] = $child;
											}   	
										}
									}
									else{
										if(!empty($checks)){
											if (!in_array($child, $childs,true))
											{
												$childs[$s++] = $child;
											}   	
										}
									}
								}
							}
						}
					}
					$tag =current($childs);					
					if($arraytype == "parent"){
						$total_xml_count = $this->get_xml_count($path , $tag);
					}
					else{
						$total_xml_count = 1;
					}
				foreach($xml->children() as $child){   
					$child_names = $child->getName();     
				}				
				if($total_xml_count == 0){
					$sub_child = $this->get_child($child,$path);
					$tag = $sub_child['child_name'];
					$total_xml_count = $sub_child['total_count'];
				}

				$doc = new \DOMDocument();
				$doc->load($path);
				foreach ($map as $field => $value) {
					foreach ($value as $head => $val) {
						if (preg_match('/{/',$val) && preg_match('/}/',$val)){
							preg_match_all('/{(.*?)}/', $val, $matches);
							$line_numbers = $line_number+1;	
							$val = preg_replace("{"."(".$tag."[+[0-9]+])"."}", $tag."[".$line_numbers."]", $val);
							for($i = 0 ; $i < count($matches[1]) ; $i++){		
								$matches[1][$i] = preg_replace("(".$tag."[+[0-9]+])", $tag."[".$line_numbers."]", $matches[1][$i]);
								$value = $this->parse_element($doc, $matches[1][$i], $line_number);	
								$search = '{'.$matches[1][$i].'}';
								$val = str_replace($search, $value, $val);
							}
							$mapping[$field][$head] = $val;	
						} 
						else{
							$mapping[$field][$head] = $val;
						}
					}
				}
				$get_arr = $this->main_import_process($mapping, $header_array['header'], $value_array['value'], $selected_type, $get_mode, $line_number, $unmatched_row, $check, $hash_key, '', $gmode);
				$post_id = $get_arr['id'];
				$core_instance->detailed_log = $get_arr['detail_log'];
				$helpers_instance->get_post_ids($post_id, $hash_key);
				$line_numbers = $line_number + 1;
				$remaining_records = $total_rows - $line_numbers;
				$wpdb->get_results("UPDATE $log_table_name SET processing_records = $line_number + 1 , remaining_records = $remaining_records, status = 'Processing' WHERE hash_key = '$hash_key'");

				if ($line_number == $total_rows - 1) {
					$wpdb->get_results("UPDATE $log_table_name SET status = 'Completed' WHERE hash_key = '$hash_key'");
				}
			
					$log_manager_instance->get_event_log($hash_key, $file_name, $file_extension, $get_mode, $total_rows, $selected_type, $core_instance->detailed_log, $line_number);
					$addHeader = false;
					$core_instance->detailed_log = [];			

				$open_txt = fopen($import_txt_path, "r");
				$read_text_ser = fread($open_txt, filesize($import_txt_path));
				$read_state = unserialize($read_text_ser);
				fclose($open_txt);

				if ($read_state['import_stop'] == 'off') {
					return;
				}

				while ($read_state['import_state'] == 'off') {
					$open_txts = fopen($import_txt_path, "r");
					$read_text_sers = fread($open_txts, filesize($import_txt_path));
					$read_states = unserialize($read_text_sers);
					fclose($open_txts);

					if ($read_states['import_state'] == 'on') {
						break;
					}

					if ($read_states['import_stop'] == 'off') {
						return;
					}
				}
			}
		}

		if (count($core_instance->detailed_log) > 0) {
			$log_manager_instance->get_event_log($hash_key , $file_name , $file_extension, $get_mode , $total_rows , $selected_type , $core_instance->detailed_log, $addHeader);
		}

		$file_manager_instance->manage_records($hash_key ,$selected_type , $file_name , $total_rows);	
		$upload = wp_upload_dir();
		$upload_base_url = $upload['baseurl'];
		$upload_url = $upload_base_url . '/smack_uci_uploads/imports/';
		$log_link_path = $upload_url. $hash_key .'/'.$hash_key.'.html';
		$response['success'] = true;
		$response['log_link'] = $log_link_path;
		if($rollback_option === 'true'){
			$response['rollback'] = true;
		}
		unlink($import_txt_path);
		echo wp_json_encode($response);
		wp_die();
	}

	public	function bulk_file_import_function()
	{
		global $wpdb;
		$helpers_instance = ImportHelpers::getInstance();
		$hash_key = sanitize_key($_POST['HashKey']);
		$highspeed= sanitize_text_field($_POST['highspeed']);
		$piecebypiece= sanitize_text_field($_POST['PieceByPiece']);		
		$splitchunks= sanitize_text_field($_POST['SplitChunks']);
		if($highspeed=='true'){
			$fileiteration='30';
			update_option('sm_cf_import_iteration_limit', $fileiteration);
		}
		if($piecebypiece=='true'){
			$fileiteration= sanitize_text_field($_POST['FileIteration']);
			update_option('sm_cf_import_iteration_limit', $fileiteration);

		}
		//Added
		$fileiteration = 5;
		update_option('sm_cf_import_iteration_limit', $fileiteration);
		$file_table_name = $wpdb->prefix ."smackcf_file_events";
		$get_id = $wpdb->get_results( "SELECT id , mode ,file_name , total_rows FROM $file_table_name WHERE `hash_key` = '$hash_key'");
		// $total_rows = $get_id[0]->total_rows;
		$file_name = $get_id[0]->file_name;
		$file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
		if($file_extension == 'xml'){
			
			$template_table_name = "ultimate_cfimporter_pro_mappingtemplate";
			$total_rows = json_decode($get_id[0]->total_rows);
			$background_values = $wpdb->get_results("SELECT mapping , module  FROM $template_table_name WHERE `eventKey` = '$hash_key' ");
			foreach ($background_values as $values) {
				$mapped_fields_values = $values->mapping;
				$selected_type = $values->module;
			}
			$map = unserialize($mapped_fields_values);
			$upload_dir = SaveMapping::$smackcsv_instance->create_upload_dir();
			$path = $upload_dir . $hash_key . '/' . $hash_key;    

			$xml = simplexml_load_file($path);
			$xml_arr = json_decode( json_encode($xml) , 1);
			if (count($xml_arr) == count($xml_arr, COUNT_RECURSIVE)) 
			{
				$item = $xml->addchild('item');
				foreach($xml_arr as $key => $value){
					$xml->item->addchild($key,$value);
					unset($xml->$key);
				}
				$arraytype = "not parent";
				$xmls['item'] =$xml_arr;
			}
			else
			{
				$arraytype = "parent";
			
			}
			$i=0;
			$childs=array();
			foreach($xml->children() as $child => $val){   
				// $child_name =  $child->getName();  
				$values =(array)$val;
				if(empty($values)){
					if (!in_array($child, $childs,true))
					{
						$childs[$i++] = $child;
		
					}
				}
				else{
					if(array_key_exists("@attributes",$values)){
						if (!in_array($child, $childs,true))
						{
							$childs[$i++] = $child;
						}   
					}
					else{
						foreach($values as $k => $v){
							$checks =(string)$values[$k];
							if(is_numeric($k)){
								if(empty($checks)){
									if (!in_array($child, $childs,true))
									{
										$childs[$i++] = $child;
									}   	
								}
							}
							else{
								if(!empty($checks)){
									if (!in_array($child, $childs,true))
									{
										$childs[$i++] = $child;
									}   	
								}	
							}
						}
					}
				}
			}
			$h=0;
			if($arraytype == 'parent'){
				foreach($childs as $child_name){
					foreach ($map as $field => $value) {
						foreach ($value as $head => $val) {
							$str = str_replace(array( '(','[',']', ')' ), '', $val);
							$ex = explode('/',$str);
							$last = substr($ex[2],-1);
							
							if(is_numeric($last)){
								$substr = substr($ex[2], 0, -1);
							}
							else{
								$substr = $ex[2];
							}
							if($substr == $child_name){
								$count='count'.$h;
								$totalrows = $total_rows->$count;
							}
						}
					}
				$h++;
				}
			}
			else{
				$count='count'.$h;
				$totalrows = $total_rows->$count;
			}
			$total_rows = $totalrows;
		}else{
			$total_rows = $get_id[0]->total_rows;
		}
		$upload_dir = SaveMapping::$smackcsv_instance->create_upload_dir();
		$file_size = filesize($upload_dir.$hash_key.'/'.$hash_key);
		$filesize = $helpers_instance->formatSizeUnits($file_size);
		$response['total_rows'] = $total_rows;
		$response['file_extension'] = $file_extension;
		$response['file_name']= $file_name;
		$response['filesize'] = $filesize;
		$response['file_iteration'] = (int)$fileiteration;
		echo wp_json_encode($response);
		wp_die();
	}

	public function send_error_status(){		
		check_ajax_referer('smack-importer-custom-fields-basic-pro', 'securekey');
		$hash_key = sanitize_key($_POST['hash_key']);
		global $wpdb;
		$template_table_name = "ultimate_cfimporter_pro_mappingtemplate";        
		$wpdb->get_results("DELETE FROM {$wpdb->prefix}ultimate_cf_importer_shortcode_manager WHERE hash_key = '$hash_key'");
		$module = $wpdb->get_var("SELECT module FROM $template_table_name WHERE eventKey = '$hash_key'");
		$schedule_argument = array($hash_key,'hash_key',$module,'');
		wp_clear_scheduled_hook('smackcf_image_schedule_hook', $schedule_argument);

		$log_table_name = $wpdb->prefix . "cfimport_detail_log";
        $get_processed_records = $wpdb->get_var("SELECT processing_records FROM $log_table_name WHERE hash_key = '$hash_key' order by id desc limit 1");
		$get_total_records = $wpdb->get_var("SELECT total_records FROM $log_table_name WHERE hash_key = '$hash_key' order by id desc limit 1");

        $response['success'] = true;
		$response['processed_records'] = (int)$get_processed_records;
		$response['total_records'] = (int)$get_total_records;
		
		echo wp_json_encode($response);
		wp_die();
	}

public function delete_image_schedule()
{
	global $wpdb;	
	$wpdb->get_results("DELETE FROM {$wpdb->prefix}ultimate_cf_importer_shortcode_manager WHERE status != 'pending' ");

	$check_for_pending_images = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ultimate_cf_importer_shortcode_manager WHERE status = 'pending' ");
	if(empty($check_for_pending_images)){
		$check_for_loading_images = $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}posts WHERE guid LIKE '%loading-image%' ");
		if(!empty($check_for_loading_images)){
			$delete_post_id = $check_for_loading_images[0]->ID;
			$wpdb->get_results("DELETE FROM {$wpdb->prefix}posts WHERE ID = $delete_post_id ");
			$wpdb->get_results("DELETE FROM {$wpdb->prefix}postmeta WHERE post_id = $delete_post_id ");

		}
	}
}


public function smackcf_image_schedule_function($schedule_array,$unikey,$module,$clikey){
	global $wpdb;		
	SaveMapping::$imageschedule_instance->image_schedule($schedule_array,$unikey,$module,$clikey);	
	$image = $wpdb->get_results("select post_id from {$wpdb->prefix}ultimate_cf_importer_shortcode_manager where hash_key = '{$schedule_array}' and status = 'pending'");

	if (empty($image)) {
		$this->delete_image_schedule();
	}		
}

	public function bulk_import(){					
		global $wpdb,$core_instance;
		$upload_dir = SaveMapping::$smackcsv_instance->create_upload_dir();
		$hash_key  = sanitize_key($_POST['HashKey']);
		$check = sanitize_text_field($_POST['Check']);
		$page_number = intval($_POST['PageNumber']);
		$rollback_option = sanitize_text_field($_POST['RollBack']);
		$unmatched_row_value = get_option('sm_uci_pro_settings');
		
		if(isset($unmatched_row_value['unmatchedrow'])){
			$unmatched_row = $unmatched_row_value['unmatchedrow'];
		}
		$update_based_on = sanitize_text_field($_POST['UpdateUsing']);
		$helpers_instance = ImportHelpers::getInstance();
		$core_instance = CoreFieldsImport::getInstance();
		$import_config_instance = ImportConfiguration::getInstance();
		$file_manager_instance = FileManager::getInstance();
		$log_manager_instance = LogManager::getInstance();
		$file_table_name = $wpdb->prefix ."smackcf_file_events";
		$template_table_name = "ultimate_cfimporter_pro_mappingtemplate";
		$log_table_name = $wpdb->prefix ."cfimport_detail_log";
		$response = [];	
		$mapped_fields_values = '';
		$selected_type = '';
		$addHeader = false;
		$get_id = $wpdb->get_results( "SELECT id , mode ,file_name , total_rows FROM $file_table_name WHERE `hash_key` = '$hash_key'");
		$get_mode = $get_id[0]->mode;
		$file_name = $get_id[0]->file_name;
		$file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
		if(empty($file_extension)){
			$file_extension = 'xml';
		}
		// $total_rows = $get_id[0]->total_rows;
		if($file_extension == 'xml'){
			$total_rows = json_decode($get_id[0]->total_rows);
			$background_values = $wpdb->get_results("SELECT mapping , module  FROM $template_table_name WHERE `eventKey` = '$hash_key' ");
			foreach ($background_values as $values) {
				$mapped_fields_values = $values->mapping;
				$selected_type = $values->module;
			}
			$map = unserialize($mapped_fields_values);
			$upload_dir = SaveMapping::$smackcsv_instance->create_upload_dir();
			$path = $upload_dir . $hash_key . '/' . $hash_key;    

			$xml = simplexml_load_file($path);
			$xml_arr = json_decode( json_encode($xml) , 1);
			if (count($xml_arr) == count($xml_arr, COUNT_RECURSIVE)) 
			{
				$item = $xml->addchild('item');
				foreach($xml_arr as $key => $value){
					$xml->item->addchild($key,$value);
					unset($xml->$key);
				}
				$arraytype = "not parent";
				$xmls['item'] =$xml_arr;
			}
			else
			{
				$arraytype = "parent";
			
			}
			$i=0;
			$childs=array();
			foreach($xml->children() as $child => $val){   
				// $child_name =  $child->getName();  
				$values =(array)$val;
				if(empty($values)){
					if (!in_array($child, $childs,true))
					{
						$childs[$i++] = $child;
		
					}
				}
				else{
					if(array_key_exists("@attributes",$values)){
						if (!in_array($child, $childs,true))
						{
							$childs[$i++] = $child;
						}   
					}
					else{
						foreach($values as $k => $v){
							$checks =(string)$values[$k];
							if(is_numeric($k)){
								if(empty($checks)){
									if (!in_array($child, $childs,true))
									{
										$childs[$i++] = $child;
									}   	
								}
							}
							else{
								if(!empty($checks)){
									if (!in_array($child, $childs,true))
									{
										$childs[$i++] = $child;
									}   	
								}	
							}
						}
					}
				}
			}
			$h=0;
			if($arraytype == "parent"){
				foreach($childs as $child_name){
					foreach ($map as $field => $value) {
						foreach ($value as $head => $val) {
							$str = str_replace(array( '(','[',']', ')' ), '', $val);
							$ex = explode('/',$str);
							
							$last = substr($ex[2],-1);
							if(is_numeric($last)){
								$substr = substr($ex[2], 0, -1);
							}
							else{
								$substr = $ex[2];
							}
							if($substr == $child_name){
								$count='count'.$h;
								$totalrows = $total_rows->$count;
							}
						}
					}
				$h++;
				}
			}
			else{
				$count='count'.$h;
				$totalrows = $total_rows->$count;
			}
			$total_rows = $totalrows;
		}
		else{
			$total_rows = $get_id[0]->total_rows;
		}

		$file_iteration = '5';
		$total_pages = ceil($total_rows/$file_iteration);
		
		$gmode = 'Normal';
		$upload_dir = SaveMapping::$smackcsv_instance->create_upload_dir();
		$file_size = filesize($upload_dir.$hash_key.'/'.$hash_key);
		$filesize = $helpers_instance->formatSizeUnits($file_size);
		update_option('sm_cf_import_page_number', $page_number);
		$remain_records = $total_rows - 1;
		$wpdb->insert( $log_table_name , array('file_name' => $file_name , 'hash_key' => $hash_key , 'total_records' => $total_rows , 'filesize' => $filesize , 'processing_records' => 1 , 'remaining_records' => $remain_records , 'status' => 'Processing' ) );		
		$background_values = $wpdb->get_results("SELECT mapping , module  FROM $template_table_name WHERE `eventKey` = '$hash_key' ");	
		foreach($background_values as $values){
			$mapped_fields_values = $values->mapping;	
			$selected_type = $values->module;
		}
		$map = unserialize($mapped_fields_values);	
		$map = $this->remove_existingfields($map,$selected_type);	
		if ( ! wp_next_scheduled( 'smackcf_image_schedule_hook', array($hash_key,'hash_key',$selected_type,'')) ) {
			wp_schedule_event( time(), 'smack_image_every_second', 'smackcf_image_schedule_hook', array($hash_key,'hash_key',$selected_type,'') );	
		}
		if($rollback_option == 'true'){
			$tables = $import_config_instance->get_rollback_tables($selected_type);
			$import_config_instance->set_backup_restore($hash_key, 'backup', $tables);	
		}
		if($file_extension == 'csv' || $file_extension == 'txt'){
			ini_set("auto_detect_line_endings", true);
			if (($h = fopen($upload_dir.$hash_key.'/'.$hash_key, "r")) !== FALSE) 
			{
				$delimiters = array( ',','\t',';','|',':','&nbsp');
				$file_path = $upload_dir . $hash_key . '/' . $hash_key;
				$delimiter = SaveMapping::$validatefile->getFileDelimiter($file_path, 5);
				$array_index = array_search($delimiter,$delimiters);
				$file_iteration = get_option('sm_cf_import_iteration_limit');
				if($array_index == 5){
					$delimiters[$array_index] = ' ';
				}
				// $line_number = ((50 * $page_number) - 50) + 1;
				// $limit = (50 * $page_number);
				$line_number = (($file_iteration * $page_number) - $file_iteration) + 1;
				$limit = ($file_iteration * $page_number);
				if($page_number == 1){
					$addHeader = true;
				}
				$info = [];
				$i = 0;
				while(($data = fgetcsv($h, 0, $delimiters[$array_index]))!== FALSE) {
					$trimmed_array = array_map('trim', $data);
					array_push($info , $trimmed_array);

					if ($i == 0) {
						$header_array = $info[$i];
						$i++;
						continue;
					}					

					if ($i >= $line_number && $i <= $limit) {
						$value_array = $info[$i];
						$unmatched_row = isset($unmatched_row)?$unmatched_row:'';
						$get_arr = $this->main_import_process($map , $header_array ,$value_array , $selected_type , $get_mode, $i , $unmatched_row, $check , $hash_key, $update_based_on, $gmode);
						$post_id = $get_arr['id'];	
						$core_instance->detailed_log = $get_arr['detail_log'];

						$helpers_instance->get_post_ids($post_id ,$hash_key);

						$remaining_records = $total_rows - $i;
						$wpdb->get_results("UPDATE $log_table_name SET processing_records = $i , remaining_records = $remaining_records , status = 'Processing' WHERE hash_key = '$hash_key'");

						if($i == $total_rows){
							$wpdb->get_results("UPDATE $log_table_name SET status = 'Completed' WHERE hash_key = '$hash_key'");
						}
						
							$log_manager_instance->get_event_log($hash_key , $file_name , $file_extension, $get_mode , $total_rows , $selected_type , $core_instance->detailed_log, $addHeader);
							$addHeader = false;
							$core_instance->detailed_log = [];						
					}

					if ($i > $limit) {
						break;
					}

					$i++;
				}
				$running = $wpdb->get_row("SELECT running FROM $log_table_name WHERE hash_key = '$hash_key' ");
				$file_manager_instance->manage_records($hash_key ,$selected_type , $file_name , $total_rows);
				$check_pause = $running->running;
					if($check_pause == 0){

					$response['success'] = false;
					$response['pause_message'] = 'Record Paused';
					echo wp_json_encode($response);
					wp_die();
				}
				fclose($h);
			}
		}

		if ($file_extension == 'xml') {
			$path = $upload_dir . $hash_key . '/' . $hash_key;
			$lined_number = ((3 * $page_number) - 3);
			$limit = (3 * $page_number) - 1;
			$header_array = [];
			$value_array = [];
			$i = 0;
			$info = [];
			$addHeader = true;
			
			for ($line_number = 0; $line_number < $total_rows; $line_number++) {
				if ( $i >= $lined_number && $i <= $limit) {
					$xml_class = new XmlHandler();
					$parse_xml = $xml_class->parse_xmls($hash_key,$i);
					$j = 0;
					$header_array = [];
					$value_array = [];
					$head = array();
					$value = array();
					$count = array();
					foreach($parse_xml as $xml_key => $xml_value){
						if(is_array($xml_value)){
							foreach ($xml_value as $e_key => $e_value){
								$head['header'][$j] = $e_value['name'];
								$value['value'][$j] = $e_value['value'];
								$j++;
							}
							array_push($header_array,$head);
							array_push($value_array,$value);
						}
						else{
							if(strpos($xml_key, 'count') !== false){
								array_push($count,$xml_value);
							}
						}

					}
					$xml = simplexml_load_file($path);
					$xml_arr = json_decode( json_encode($xml) , 1);
					if (count($xml_arr) == count($xml_arr, COUNT_RECURSIVE)) 
					{
						$item = $xml->addchild('item');
						foreach($xml_arr as $key => $value){
							$xml->item->addchild($key,$value);
							unset($xml->$key);
						}
						$arraytype = "not parent";
						$xmls['item'] =$xml_arr;
					}
					else
					{
						$arraytype = "parent";
					}
					$childs=array();
					$s=0;
					foreach($xml->children() as $child => $val){   
						// $tag = $child->getName(); 
						$tag = (array)$val;
						if(empty($tag)){   
							if (!in_array($child, $childs,true))
							{
								$childs[$s++] = $child;
	
							}    
						} 
						else{
							if(array_key_exists("@attributes",$tag)){
								if (!in_array($child, $childs,true))
								{
									$childs[$s++] = $child;
								}   
							}
							else{
								foreach($tag as $k => $v){
									$checks =(string)$tag[$k];
									if(is_numeric($k)){
										if(empty($checks)){
											if (!in_array($child, $childs,true))
											{
												$childs[$s++] = $child;
											}   	
										}
									}
									else{
										if(!empty($checks)){
											if (!in_array($child, $childs,true))
											{
												$childs[$s++] = $child;
											}   	
										}
									}
								}
							}
						}
					}
					// $tag =current($childs);
					$h=0;
					if($arraytype == 'parent'){
						foreach($childs as $tag){
							$mapping=array();
							$total_xml_count = $this->get_xml_count($path , $tag);
							foreach($xml->children() as $child){  
								$child_names =  $child->getName();  
							}
							if($total_xml_count == 0){
								$sub_child = $this->get_child($child,$path);
								$tag = $sub_child['child_name'];
								$total_xml_count = $sub_child['total_count'];
							}
							$doc = new \DOMDocument();
							$doc->load($path);
							foreach ($map as $field => $value) {
								foreach ($value as $head => $val) {
									$str = str_replace(array( '(','[',']', ')' ), '', $val);
									$ex = explode('/',$str);
									
									$last = substr($ex[2],-1);
									if(is_numeric($last)){
										$substr = substr($ex[2], 0, -1);
									}
									else{
										$substr = $ex[2];
									}
									if($substr == $tag){
										if (preg_match('/{/',$val) && preg_match('/}/',$val)){
											preg_match_all('/{(.*?)}/', $val, $matches);
											$line_numbers = $i+1;	
											$val = preg_replace("{"."(".$tag."[+[0-9]+])"."}", $tag."[".$line_numbers."]", $val);
											for($k = 0 ; $k < count($matches[1]) ; $k++){		
												$matches[1][$k] = preg_replace("(".$tag."[+[0-9]+])", $tag."[".$line_numbers."]", $matches[1][$k]);
												$value = $this->parse_element($doc, $matches[1][$k], $i);	
												$search = '{'.$matches[1][$k].'}';
												$val = str_replace($search, $value, $val);
											}
											$mapping[$field][$head] = $val;	
										} 
										else{
											$mapping[$field][$head] = $val;
										}
									}
								}
							}
							array_push($info, $value_array[$h]['value']);
							if(!empty($mapping)){
								$get_arr = $this->main_import_process($mapping, $header_array[$h]['header'], $value_array[$h]['value'], $selected_type, $get_mode, $i, $unmatched_row, $check, $hash_key, $update_based_on, $gmode);
								$post_id = $get_arr['id'];
								$core_instance->detailed_log = $get_arr['detail_log'];
							}
							$h++;
						}

					}
					else{
						$total_xml_count = 1;
						$doc = new \DOMDocument();
						$doc->load($path);
						foreach ($map as $field => $value) {
							foreach ($value as $head => $val) {
								if (preg_match('/{/',$val) && preg_match('/}/',$val)){
									preg_match_all('/{(.*?)}/', $val, $matches);
									$line_numbers = $i+1;	
									$val = preg_replace("{"."(".$tag."[+[0-9]+])"."}", $tag."[".$line_numbers."]", $val);
									for($k = 0 ; $k < count($matches[1]) ; $k++){		
										$matches[1][$k] = preg_replace("(".$tag."[+[0-9]+])", $tag."[".$line_numbers."]", $matches[1][$k]);
										$value = $this->parse_element($doc, $matches[1][$k], $i);	
										$search = '{'.$matches[1][$k].'}';
										$val = str_replace($search, $value, $val);
									}
									$mapping[$field][$head] = $val;	
								} 
								else{
									$mapping[$field][$head] = $val;
								}
								array_push($info, $value_array[$h]['value']);
								if(!empty($mapping)){
									$get_arr = $this->main_import_process($mapping, $header_array[$h]['header'], $value_array[$h]['value'], $selected_type, $get_mode, $i, $unmatched_row, $check, $hash_key, $update_based_on, $gmode);
									$post_id = $get_arr['id'];
									$core_instance->detailed_log = $get_arr['detail_log'];
								}
							}

						}
					}
					
					$helpers_instance->get_post_ids($post_id, $hash_key);
					$line_numbers = $i + 1;
					$remaining_records = $total_rows - $line_numbers;
					$wpdb->get_results("UPDATE $log_table_name SET processing_records = $i + 1 , remaining_records = $remaining_records, status = 'Processing' WHERE hash_key = '$hash_key'");

					if ($i == $total_rows - 1) {
						$wpdb->get_results("UPDATE $log_table_name SET status = 'Completed' WHERE hash_key = '$hash_key'");
					}
					
					$log_manager_instance->get_event_log($hash_key, $file_name, $file_extension, $get_mode, $total_rows, $selected_type, $core_instance->detailed_log, $i);
					$addHeader = false;
					$core_instance->detailed_log = [];					
				}
				if ($i > $limit) {
					break;
				}
				$i++;
			}
			$running = $wpdb->get_row("SELECT running FROM $log_table_name WHERE hash_key = '$hash_key' ");
			$file_manager_instance->manage_records($hash_key ,$selected_type , $file_name , $total_rows);
			$check_pause = $running->running;
			if ($check_pause == 0) {
				$response['success'] = false;
				$response['pause_message'] = 'Record Paused';
				echo wp_json_encode($response);
				wp_die();
			}
		}

		if (isset($unmatched_row) && ($unmatched_row == 'true') && ($page_number >= $total_pages)){
			$post_entries_table = $wpdb->prefix ."post_entries";
			$post_entries_value = $wpdb->get_results("select ID from {$wpdb->prefix}post_entries_table " ,ARRAY_A);
		
			foreach($post_entries_value as $product_id){
				$test [] = $product_id['ID'];
			}

		    $unmatched_object = new ExtensionHandler;
			$import_type = $unmatched_object->import_type_as($selected_type);
			$import_type_value = $unmatched_object->import_post_types($import_type);
			$import_name_as = $unmatched_object->import_name_as($import_type);
			if($import_type_value == 'category' || $import_type_value == 'post_tag' || $import_type_value == 'product_cat' || $import_type_value == 'product_tag'){
				
				$get_total_row_count =  $wpdb->get_col("SELECT term_id FROM {$wpdb->prefix}term_taxonomy WHERE taxonomy = '$import_type_value'");
				$unmatched_id=array_diff($get_total_row_count,$test);

				foreach($unmatched_id as $keys => $values){
					$wpdb->get_results("DELETE FROM {$wpdb->prefix}terms WHERE `term_id` = '$values' ");
				}
			}
			if($import_type_value == 'post' || $import_type_value == 'product' || $import_type_value == 'page'  || $import_name_as == 'CustomPosts'){
				
				$get_total_row_count =  $wpdb->get_col("SELECT DISTINCT ID FROM {$wpdb->prefix}posts WHERE post_type = '{$import_type_value}' AND post_status != 'trash' ");
				$unmatched_id=array_diff($get_total_row_count,$test);
			
				foreach($unmatched_id as $keys => $values){
					$wpdb->get_results("DELETE FROM {$wpdb->prefix}posts WHERE `ID` = '$values' ");
				}
			}
			$wpdb->get_results("DELETE FROM {$wpdb->prefix}post_entries_table");
			
		}

		if (isset($core_instance->detailed_log) && count($core_instance->detailed_log) > 0) {
			$log_manager_instance->get_event_log($hash_key , $file_name , $file_extension, $get_mode , $total_rows , $selected_type , $core_instance->detailed_log, $addHeader);
		}
		$file_manager_instance->manage_records($hash_key ,$selected_type , $file_name , $total_rows);			
		$count = count($info);
			
		$upload = wp_upload_dir();
		$upload_base_url = $upload['baseurl'];
		$upload_url = $upload_base_url . '/smack_uci_uploads/imports/';
		$log_link_path = $upload_url. $hash_key .'/'.$hash_key.'.html';
		$response['success'] = true;
		$response['log_link'] = $log_link_path;
		if($rollback_option == 'true'){
			$response['rollback'] = true;
		}	
		echo wp_json_encode($response);
		wp_die();
	
	}

	public function pause_import(){
		global $wpdb;				
		$response = [];
		$hash_key = sanitize_key($_POST['HashKey']);
		$log_table_name = $wpdb->prefix ."cfimport_detail_log";
		$wpdb->get_results("UPDATE $log_table_name SET running = 0  WHERE hash_key = '$hash_key'");					
		$response['pause_state'] = true;	
		echo wp_json_encode($response);
		wp_die();
	}

	public function resume_import(){
		global $wpdb;
		$response = [];
		$hash_key = sanitize_key($_POST['HashKey']);
		$log_table_name = $wpdb->prefix ."cfimport_detail_log";
		$wpdb->get_results("UPDATE $log_table_name SET running = 1  WHERE hash_key = '$hash_key'");					
		$response['resume_state'] = true;	
		$response['page_number'] = get_option('sm_cf_import_page_number') + 1;
		echo wp_json_encode($response);
		wp_die();
	}

	public function get_xml_count($eventFile , $child_name){
		$doc = new \DOMDocument();
		$doc->load($eventFile);
		$nodes=$doc->getElementsByTagName($child_name);
		$total_row_count = $nodes->length;
		return $total_row_count;	
	}

	public function get_child($child,$path){
		foreach($child->children() as $sub_child){
			$sub_child_name = $sub_child->getName();
		}
		$total_xml_count = $this->get_xml_count($path , $sub_child_name);
		if($total_xml_count == 0){
			$this->get_child($sub_child,$path);
		}
		else{
			$result['child_name'] = $sub_child_name;
			$result['total_count'] = $total_xml_count;
			return $result;
		}
	}

	public function main_import_process($map , $header_arrays ,$value_array , $selected_type , $get_mode, $line_number , $unmatched_row, $check , $hash_key, $update_based_on, $gmode, $templatekey = null){

		$header_array = [];
		foreach($header_arrays as $header_values){
			$header_array[] = rtrim($header_values, " ");
		}
		$map['ACFIMAGEMETA'] = isset($map['ACFIMAGEMETA']) ? $map['ACFIMAGEMETA'] : '';
		global $core_instance;
		$return_arr = [];
		$post_id = isset($post_id) ? $post_id :'';
		$core_instance = CoreFieldsImport::getInstance();
		foreach($map as $group_name => $group_value){
			if($group_name == 'CORE'){
				$acf_map = isset($map['ACF']) ? $map['ACF'] : '';
				$types_map = isset($map['TYPES']) ? $map['TYPES'] : '';
				$pods_map = isset($map['PODS']) ? $map['PODS'] : '';
				
				$core_instance = CoreFieldsImport::getInstance();
				$post_id = $core_instance->set_core_values($header_array ,$value_array , $map['CORE'] , $selected_type , $get_mode, $line_number , $unmatched_row, $check , $hash_key, $acf_map, $pods_map ,$types_map, $update_based_on, $gmode,$templatekey);		
			}
			else{				
				$acf_map = isset($map['ACF']) ? $map['ACF'] : '';
				global $wpdb;
				if($update_based_on == 'acf'){
					foreach($acf_map as $custom_key => $custom_value){
						if (strpos($custom_value, '{') !== false && strpos($custom_value, '}') !== false) {
							$custom_value = $custom_key;
						}
						if($custom_key == $check){
							$get_key= array_search($custom_value , $header_array);
						}
						if(isset($value_array[$get_key])){
							$csv_element = $value_array[$get_key];	
						}
						
						$get_result = $wpdb->get_results("SELECT post_id FROM {$wpdb->prefix}postmeta as a join {$wpdb->prefix}posts as b on a.post_id = b.ID WHERE a.meta_key = '$check' AND a.meta_value = '$csv_element' AND b.post_status = 'publish' order by a.post_id DESC ");
					}	
					if(!empty($get_result)){
						$post_id = $get_result[0]->post_id;	
						$core_instance->detailed_log[$line_number]['Message'] = 'Updated ' .$selected_type . ' ID: ' . $post_id  ;
						$core_instance->detailed_log[$line_number]['VERIFY'] = "<b> Click here to verify</b> - <a href='" . get_permalink( $post_id ) . "' target='_blank' title='" . esc_attr( sprintf( __( 'View &#8220;%s&#8221;' ), $post_values['post_title'] ) ) . "'rel='permalink'>Web View</a> | <a href='" . get_edit_post_link( $post_id ) . "'target='_blank' title='" . esc_attr( 'Edit this item' ) . "'>Admin View</a>";	
						$core_instance->detailed_log[$line_number][' Status'] = 'publish';
					}
					else{
						$core_instance->detailed_log[$line_number]['Message'] = 'Skipped,Due to existing field value is not presents.';
					}
				}
			}
		}
        if(!empty($post_id)){
			foreach($map as $group_name => $group_value){
				switch($group_name){
	
				case 'ACF':	
					$acf_image = isset($map['ACFIMAGEMETA']) ? $map['ACFIMAGEMETA'] : '';
					$acf_pro_instance = ACFProImport::getInstance();
					$acf_pro_instance->set_acf_pro_values( $header_array, $value_array, $map['ACF'],$acf_image , $post_id, $selected_type ,$get_mode,$line_number,$hash_key,$gmode,$templatekey);
					break;
	
				case 'RF':
					$acf_image = isset($map['ACFIMAGEMETA']) ? $map['ACFIMAGEMETA'] : '';
					$acf_pro_instance = ACFProImport::getInstance();
					$acf_pro_instance->set_acf_rf_values( $header_array, $value_array, $map['RF'],$acf_image , $post_id, $selected_type,$get_mode,$line_number,$hash_key,$gmode,$templatekey );
					break;
	
				case 'FC':
					$acf_pro_instance = ACFProImport::getInstance();
					$acf_pro_instance->set_acf_fc_values($header_array, $value_array, $map['FC'], $map['ACFIMAGEMETA'], $post_id, $selected_type,$get_mode,$hash_key,$line_number,$gmode,$templatekey);
					break;	
		
				case 'GF':
					$acf_pro_instance = ACFProImport::getInstance();
					$acf_pro_instance->set_acf_gf_values( $header_array, $value_array, $map['GF'],$map['ACFIMAGEMETA'] , $post_id, $selected_type,$get_mode,$line_number,$hash_key,$gmode,$templatekey );
					break;
	
				case 'TYPES':
					$types_image_meta = isset($map['TYPESIMAGEMETA']) ? $map['TYPESIMAGEMETA'] :'';
					$toolset_instance = ToolsetImport::getInstance();
					$toolset_instance->set_toolset_values( $header_array, $value_array, $map['TYPES'], $types_image_meta, $post_id, $selected_type , $get_mode,$line_number,$hash_key,$gmode,$templatekey );
					break;	
				
				case 'JE':
					$jet_engine_instance = JetEngineImport::getInstance();
					$jet_engine_instance->set_jet_engine_values($header_array, $value_array, $map['JE'], $post_id, $selected_type, $get_mode, $hash_key,$line_number,$gmode,$templatekey);
					break;
					
				case 'JERF':
					$jet_engine_instance = JetEngineImport::getInstance();
					$jet_engine_instance->set_jet_engine_rf_values($header_array, $value_array, $map['JERF'], $post_id, $selected_type, $get_mode, $hash_key,$line_number,$gmode,$templatekey);
					break;

				case 'JECPT':
					$jet_engine_cpt_instance = JetEngineCPTImport::getInstance();
					$jet_engine_cpt_instance->set_jet_engine_cpt_values($header_array, $value_array, $map['JECPT'], $post_id, $selected_type, $get_mode, $hash_key,$line_number,$gmode,$templatekey);
					break;

				case 'JECPTRF':
					$jet_engine_cpt_instance = JetEngineCPTImport::getInstance();
					$jet_engine_cpt_instance->set_jet_engine_cpt_rf_values($header_array, $value_array, $map['JECPTRF'], $post_id, $selected_type, $get_mode, $hash_key,$line_number,$gmode,$templatekey);
					break;

				case 'JECCT':
					$jet_engine_cct_instance = JetEngineCCTImport::getInstance();
					$jet_engine_cct_instance->set_jet_engine_cct_values($header_array, $value_array, $map['JECCT'], $post_id, $selected_type, $get_mode, $hash_key,$line_number,$gmode,$templatekey);
					break;

				case 'JECCTRF':
					$jet_engine_cct_instance = JetEngineCCTImport::getInstance();
					$jet_engine_cct_instance->set_jet_engine_cct_rf_values($header_array, $value_array, $map['JECCTRF'], $post_id, $selected_type, $get_mode, $hash_key,$line_number,$gmode,$templatekey);
					break;
			
				case 'JETAX':
					$jet_engine_tax_instance = JetEngineTAXImport::getInstance();
					$jet_engine_tax_instance->set_jet_engine_tax_values($header_array, $value_array, $map['JETAX'], $post_id, $selected_type, $get_mode, $hash_key,$line_number,$gmode,$templatekey);
					break;

				case 'JETAXRF':
					$jet_engine_tax_instance = JetEngineTAXImport::getInstance();
					$jet_engine_tax_instance->set_jet_engine_tax_rf_values($header_array, $value_array, $map['JETAXRF'], $post_id, $selected_type, $get_mode, $hash_key,$line_number,$gmode,$templatekey);
					break;

				case 'JEREL':
					$jet_engine_rel_instance = JetEngineRELImport::getInstance();
					$jet_engine_rel_instance->set_jet_engine_rel_values($header_array, $value_array, $map['JEREL'], $post_id, $selected_type, $get_mode, $hash_key, $line_number,$gmode,$templatekey);
					break;

				case 'PODS':
					$pods_image_meta = isset($map['PODSIMAGEMETA']) ? $map['PODSIMAGEMETA'] :'';
					$pods_instance = PodsImport::getInstance();
					$pods_instance->set_pods_values($header_array ,$value_array , $map['PODS'],$pods_image_meta, $post_id , $selected_type,$hash_key,$gmode,$templatekey);
					break;
	
				case 'AIOSEO':
					$all_seo_instance = AllInOneSeoImport::getInstance();
					$all_seo_instance->set_all_seo_values($header_array ,$value_array , $map['AIOSEO'], $post_id , $selected_type,$get_mode);
					break;
	
				case 'YOASTSEO':
					$yoast_instance = YoastSeoImport::getInstance();
					$yoast_instance->set_yoast_values($header_array ,$value_array , $map['YOASTSEO'], $post_id , $selected_type,$hash_key,$gmode,$templatekey);
					break;
				case 'RANKMATH':
					$rankmath_instance = RankMathImport::getInstance();
					$rankmath_instance->set_rankmath_values($header_array, $value_array, $map['RANKMATH'], $post_id, $selected_type);
					break;
	
				case 'CCTM':
					$cctm_instance = CCTMImport::getInstance();
					$cctm_instance->set_cctm_values($header_array ,$value_array , $map['CCTM'], $post_id , $selected_type);
					break;
	
				case 'CFS':
					$cfs_instance = CFSImport::getInstance();
					$cfs_instance->set_cfs_values($header_array ,$value_array , $map['CFS'], $post_id , $selected_type);
					break;
	
				case 'CMB2':
					$cmb2_instance = CMB2Import::getInstance();
					$cmb2_instance->set_cmb2_values($header_array ,$value_array , $map['CMB2'], $post_id , $selected_type,$hash_key,$gmode,$templatekey);
					break;
	
				case 'WPMEMBERS':
					$wpmembers_instance = WPMembersImport::getInstance();
					$wpmembers_instance->set_wpmembers_values($header_array, $value_array, $map['WPMEMBERS'], $post_id, $selected_type, $hash_key,$gmode,$templatekey);
					break;
					
				case 'TERMS':
					$terms_taxo_instance = TermsandTaxonomiesImport::getInstance();
					$terms_taxo_instance->set_terms_taxo_values($header_array ,$value_array , $map['TERMS'], $post_id , $selected_type , $get_mode , $line_number);
					break;
	
				case 'CORECUSTFIELDS':
					$wordpress_custom_instance = WordpressCustomImport::getInstance();
					$wordpress_custom_instance->set_wordpress_custom_values($header_array ,$value_array , $map['CORECUSTFIELDS'], $post_id , $selected_type,$group_name, $hash_key,$gmode,$templatekey);
					break;
	
				case 'NEXTGEN':
					SaveMapping::$nextgen_instance->nextgenImport($header_array ,$value_array , $map['NEXTGEN'], $post_id , $selected_type);
					break;
	
				case 'COREUSERCUSTFIELDS':
					$wordpress_custom_instance = WordpressCustomImport::getInstance();
					$wordpress_custom_instance->set_wordpress_custom_values($header_array ,$value_array , $map['COREUSERCUSTFIELDS'], $post_id , $selected_type, $group_name, $hash_key,$gmode,$templatekey);
					break;
				case 'LPCOURSE':
					//case 'LPCURRICULUM':
					$learn_merge = [];
					$learn_merge = array_merge($map['LPCOURSE'], $map['LPCURRICULUM']);	
					$learnpress_instance = LearnPressImport::getInstance();
					$learnpress_instance->set_learnpress_values($header_array, $value_array, $learn_merge, $post_id, $selected_type,$get_mode);
					break;
				case 'LPLESSON':
					$learnpress_instance = LearnPressImport::getInstance();
					$learnpress_instance->set_learnpress_values($header_array, $value_array, $map['LPLESSON'], $post_id, $selected_type, $get_mode);
					break;
				case 'LPQUIZ':
					$learnpress_instance = LearnPressImport::getInstance();
					$learnpress_instance->set_learnpress_values($header_array, $value_array, $map['LPQUIZ'], $post_id, $selected_type, $get_mode);
					break;
				case 'LPQUESTION':
					$learnpress_instance = LearnPressImport::getInstance();
					$learnpress_instance->set_learnpress_values($header_array, $value_array, $map['LPQUESTION'], $post_id, $selected_type, $get_mode);
					break;
				case 'LPORDER':
					$learnpress_instance = LearnPressImport::getInstance();
					$learnpress_instance->set_learnpress_values($header_array, $value_array, $map['LPORDER'], $post_id, $selected_type, $get_mode);
					break;
				case 'METABOX':
					$metabox_instance = MetaBoxImport::getInstance();
					$metabox_instance->set_metabox_values($header_array, $value_array, $map['METABOX'], $post_id, $selected_type,$line_number,$hash_key,$gmode,$templatekey);
					break;
				case 'METABOXRELATION':
					$metabox_relations_instance = MetaBoxRelationsImport::getInstance();
					$metabox_relations_instance->set_metabox_relations_values($header_array, $value_array, $map['METABOXRELATION'], $post_id, $selected_type,$get_mode);
					break;
				case 'METABOXGROUP':					
					$metabox_group_instance = MetaBoxGroupImport::getInstance();
					// $metabox_group_instance->set_metabox_group_values($header_array, $value_array, $map['METABOXGROUP'], $post_id, $selected_type,$get_mode);
					$metabox_group_instance->set_metabox_group_values($header_array, $value_array, $map['METABOXGROUP'], $post_id, $selected_type,$get_mode,$line_number);
					break;
				case 'JOB':
					$job_listing_instance = JobListingImport::getInstance();
					$job_listing_instance->set_job_listing_values($header_array, $value_array, $map['JOB'], $post_id, $selected_type);
					break;
				case 'FORUM':
					$bbpress_instance = BBPressImport::getInstance();
					$bbpress_instance->set_bbpress_values($header_array, $value_array, $map['FORUM'], $post_id, $selected_type);
					break;
	
				case 'TOPIC':
					$bbpress_instance = BBPressImport::getInstance();
					$bbpress_instance->set_bbpress_values($header_array, $value_array, $map['TOPIC'], $post_id, $selected_type);
					break;
	
				case 'REPLY':
					$bbpress_instance = BBPressImport::getInstance();
					$bbpress_instance->set_bbpress_values($header_array, $value_array, $map['REPLY'], $post_id, $selected_type);
					break;
	
				}
			}

		}
		
		$return_arr['id'] = $post_id;
		$return_arr['detail_log'] = $core_instance->detailed_log;	
		return $return_arr;	
	}
	public function remove_existingfields($map,$selected_type){
		
		$mapfield_data = SaveMapping::$mapping_instance->mapping_fields($selected_type);	
		$keydata = array_keys($map);			

		foreach($keydata as $widgetname) {
			$newmap = [];
			switch($widgetname) {
				case 'CORE':
					$fieldtype = "core_fields";
					break;
				case 'ACF':
					$fieldtype = "acf_pro_fields";
				break;	
				case 'RF':
					$fieldtype = "acf_repeater_fields";
					break;		
				case 'JE':
					$fieldtype = "jetengine_fields";
					break;
				case 'JERF':
					$fieldtype = "jetengine_rf_fields";
					break;
				case 'JECPT':
					$fieldtype = "jetenginecpt_fields";
					break;
				case 'JECPTRF':
					$fieldtype = "jetenginecpt_rf_fields";
					break;
				case 'JECCT':
					$fieldtype = "jetenginecct_fields";
					break;
				case 'JECCTRF':
					$fieldtype = "jetenginecct_rf_fields";
					break;			
				case 'JETAX':	
					$fieldtype = "jetenginetaxonomy_fields";
					break;
				case 'JETAXRF':					
					$fieldtype = "jetenginetaxonomy_rf_fields";
					break;
				case 'JEREL':
					$fieldtype = "jetengine_rel_fields";
					break;
				case 'PODS':
					$fieldtype = "pods_fields";
					break;				
				case 'ELEMENTOR':
					$fieldtype = "elementor_meta_fields";
					break;
				case 'AIOSEO':
					$fieldtype = "all_in_one_seo_fields";
					break;
				case 'YOASTSEO':
					$fieldtype = "yoast_seo_fields";
					break;					
				case 'RANKMATH':
					$fieldtype = "rank_math_fields";
					break;
				case 'ECOMMETA':					
					$fieldtype = "product_meta_fields";
					break;								
				case 'ATTRMETA':
					$fieldtype = "product_attr_fields";
					break;
				case 'BUNDLEMETA':
					$fieldtype = "product_bundle_meta_fields";
					break;
				case 'REFUNDMETA':
					$fieldtype = "refund_meta_fields";
					break;
				case 'ORDERMETA':
					$fieldtype = "order_meta_fields";
					break;
				case 'COUPONMETA':
					$fieldtype = "coupon_meta_fields";
					break;
				case 'CCTM':
					$fieldtype = "cctm_fields";
					break;
				case 'CFS':
					$fieldtype = "custom_fields_suite_fields";
					break;
				case 'CMB2':
					$fieldtype = "cmb2_fields";
					break;
				case 'BSI':
					$fieldtype = "billing_and_shipping_information";
					break;
				case 'WPMEMBERS':
					$fieldtype = "custom_fields_wp_members";
					break;							
				case 'WPECOMMETA':
					$fieldtype = "wp_ecom_custom_fields";
					break;
				case 'TERMS':
					$fieldtype = "terms_and_taxonomies";
					break;
				case 'WPML':
					$fieldtype = "wpml_fields";
					break;
				case 'CORECUSTFIELDS':
					$fieldtype = "wordpress_custom_fields";
					break;				
				case 'DPF':
					$fieldtype = "directory_pro_fields";
					break;
				case 'EVENTS':
					$fieldtype = "events_manager_fields";
					break;
				case 'NEXTGEN':
					$fieldtype = "nextgen_gallery_fields";
					break;				
				case 'LPCOURSE':
					$fieldtype = "course_settings_fields";
					break;					
				case 'FC':
					$fieldtype = "acf_flexible_fields";
					break;						
				case 'GF':
					$fieldtype = "acf_group_fields";
					break;	
				case 'TYPES':
					$fieldtype = "types_fields";
					break;
				case 'LPLESSON':
					$fieldtype = "lesson_settings_fields";
					break;
				case 'LPCURRICULUM':
					$fieldtype = "curriculum_settings_fields";
				case 'LPQUIZ':
					$fieldtype = "quiz_settings_fields";
					break;	
				case 'LPQUESTION':
					$fieldtype = "question_settings_fields";
					break;	
				case 'LPORDER':
					$fieldtype = "order_settings_fields";
					break;	
				case 'FORUM':
					$fieldtype = "forum_attributes_fields";
					break;
				case 'TOPIC':
					$fieldtype = "topic_attributes_fields";
					break;	
				case 'REPLY':
					$fieldtype = "reply_attributes_fields";
					break;
				case 'POLYLANG':
					$fieldtype = "Polylang_settings_fields";
					break;
				case 'METABOX':
					$fieldtype = "metabox_fields";
					break;
				case 'METABOXRELATION':
					$fieldtype = "metabox_relations_fields";
					break;	
				case 'METABOXGROUP':
					$fieldtype = "metabox_group_fields";
					break;
				case 'JOB':
					$fieldtype = "job_listing_fields";
					break;					
				default:
					$fieldtype = "core_fields";
					break;
			}			
			
			//Get all current fields of mapping widgets
			foreach($mapfield_data as $extensions){				
				if(array_key_exists($fieldtype,$extensions)){
					foreach($extensions[$fieldtype] as $fielddata){						
							$newmap[] = $fielddata['name'];						
					}
				}				
			}
			
			//Remove unwanted/deleted fields
			foreach($map[$widgetname] as $key => $value){
				if(!empty($newmap) && !in_array($key,$newmap)){
					unset($map[$widgetname][$key]);
				}
			}		
		}	
		return $map;

	}
}
