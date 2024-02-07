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

/**
 * Class ScheduleImport
 * @package Smackcoders\CFCSV
 */

class ScheduleImport {

	protected static $instance=null;
	protected static $smackcsv_instance = null;
	protected static $save_mapping_instance = null;
	protected static $core = null;
	private static $validatefile = [];

	public static  function getInstance() {
		if (ScheduleImport::$instance == null) {
			ScheduleImport::$instance = new ScheduleImport;
			ScheduleImport::$validatefile = new ValidateFile;
			ScheduleImport::$smackcsv_instance = SmackCSV::getInstance();
			ScheduleImport::$save_mapping_instance = SaveMapping::getInstance();
			return self::$instance;
		}
		return self::$instance;
	}
	public function schedule_import($data){
	
		global $wpdb,$core_instance;
		$hash_key  = $data['eventkey'];
		$check = $data['duplicate_headers'];
		$last_run = $data['lastrun'];
		$next_run = $data['nexrun'];
		$data_id = $data['id'];
		$frequency = $data['frequency'];
		$helpers_instance = ImportHelpers::getInstance();
		$core_instance = CoreFieldsImport::getInstance();
		$file_manager_instance = FileManager::getInstance();
		$log_manager_instance = LogManager::getInstance();
		$response = [];
		$file_table_name = $wpdb->prefix ."smackcf_file_events";
		$template_table_name = "ultimate_cfimporter_pro_mappingtemplate";
		$log_table_name = $wpdb->prefix ."cfimport_detail_log";
		ScheduleImport::$smackcsv_instance = SmackCSV::getInstance();
		$upload_dir = ScheduleImport::$smackcsv_instance->create_upload_dir();
		$background_values = $wpdb->get_results("SELECT mapping , module  FROM $template_table_name WHERE `eventKey` = '$hash_key' ");
		$gmode = 'Schedule';
		foreach($background_values as $values){
			$mapped_fields_values = $values->mapping;	
			$selected_type = $values->module;
		}

		if ( ! wp_next_scheduled( 'smackcf_image_schedule_hook', array($hash_key,'hash_key',$selected_type,'')) ) {
			wp_schedule_event( time(), 'smack_image_every_second', 'smackcf_image_schedule_hook', array($hash_key,'hash_key',$selected_type,'') );	
		}

		$get_id  = $wpdb->get_results( "SELECT id , mode ,file_name , total_rows FROM $file_table_name WHERE `hash_key` = '$hash_key'");
		$get_mode = $get_id[0]->mode;

		//$total_rows = $get_id[0]->total_rows;
		$get_rows  = $wpdb->get_results( "SELECT total_rows FROM $file_table_name WHERE `hash_key` = '$hash_key' order by id desc");
		$total_rows = $get_rows[0]->total_rows;
		//$total_rows = $wpdb->get_var( "SELECT total_rows FROM $file_table_name WHERE `hash_key` = '$hash_key' order by id desc limit 1");
		
		$file_name = $get_id[0]->file_name;
		$file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
		if(empty($file_extension)){
			$file_extension = 'xml';
		}
		$file_size = filesize($upload_dir.$hash_key.'/'.$hash_key);
		$filesize = $helpers_instance->formatSizeUnits($file_size);
	
		$update_based_on = get_option('custom_fields_importer_update_using');
		if(empty($update_based_on)){
			$update_based_on = 'normal';
		}
		$wpdb->insert( $log_table_name , array('file_name' => $file_name , 'hash_key' => $hash_key , 'total_records' => $total_rows, 'filesize' => $filesize  ) );
		$map = unserialize($mapped_fields_values);
		$check_page_number = get_option('smack_cf_page_number_'. $data_id);
		$page_number = $check_page_number + 1;

		if($file_extension == 'csv' || $file_extension == 'txt'){
			ini_set("auto_detect_line_endings", true	);
			$info = [];
			if (($h = fopen($upload_dir.$hash_key.'/'.$hash_key, "r")) !== FALSE) 
			{
				$inputFile = $upload_dir.$hash_key.'/'.$hash_key;
				// $lines = file($inputFile); 
				// //$totRecords = count($lines);
				// $file_table_name = $wpdb->prefix . "smackcsv_file_events";
				// //$totRecords = count($lines);
                // $recordcount =  $wpdb->get_results("SELECT total_rows FROM $file_table_name WHERE hash_key = '$hash_key' ", ARRAY_A);
				// $count =count($recordcount);
				// $count -- ;
				// $tot=json_decode(json_encode($recordcount),true);
				// $totRecords = $tot[$count]['total_rows'];

				$totRecords = $total_rows;


				//comment these 2 lines after setting limit
				// $line_number = 0;
				// $addHeader = true;

				$header_array = [];
				$value_array = [];
				
                $delimiters = array( ',','\t',';','|',':','&nbsp');
				$file_path = $upload_dir . $hash_key . '/' . $hash_key;
				$delimiter = ScheduleImport::$validatefile->getFileDelimiter($file_path, 5);
				$array_index = array_search($delimiter,$delimiters);
				if($array_index == 5){
					$delimiters[$array_index] = ' ';
				}

				
				//uncomment after setting limit
				$get_limit = get_option('smack_cf_record_limit_'.$data_id);
				if(!empty($get_limit)){
					$record_limit = $get_limit;
					//added get total pages count for deleting csv records - settings option
					$total_pages = ceil($total_rows/$record_limit);
				}
				else{
					$record_limit = $totRecords;
					//added get total pages count for deleting csv records - settings option
					$total_pages = ceil($total_rows/$record_limit);
				}
		
				$line_number = (($record_limit * $page_number) - $record_limit) + 1;
				$limit = ($record_limit * $page_number);
				if($page_number == 1)
				{
					$addHeader = true;
				}
				$info = [];
				$i = 0;
			
				// time calculation for next 50 seconds
				$datetime_now = new \DateTime($last_run);
				$datetime_now->modify('+50 seconds');
				$datetime_after_50_sec = $datetime_now->format('Y-m-d H:i:s');	
				
				while (($data = fgetcsv($h, 0, $delimiters[$array_index])) !== FALSE) 
				{	
					ignore_user_abort(1);
					//set_time_limit(0);	
					$schedule_tableName = 'ultimate_cfimporter_pro_scheduled_import';
					$getScheduling_data = $wpdb->get_results("select * from $schedule_tableName");
					if(empty($getScheduling_data))
					{
						return true;
					}	
					// Read the data from a single line
					$trimmed_array = array_map('trim', $data);
					array_push($info , $trimmed_array);
					if ($i == 0) {
						$header_array = $info[$i];
						$i++;
						continue;
					}
					//$unmatched_rows = '';
					$unmatched_row_value = get_option('sm_uci_pro_settings');
					$unmatched_rows = isset($unmatched_row_value['unmatchedrow']) ? $unmatched_row_value['unmatchedrow'] : '';
					
					if ($i >= $line_number && $i <= $limit) {
						$value_array = $info[$i];
						$get_arr = ScheduleImport::$save_mapping_instance->main_import_process($map , $header_array ,$value_array , $selected_type , $get_mode, $i ,$unmatched_rows , $check , $hash_key, $update_based_on, $gmode);
						$post_id = $get_arr['id'];	
						$core_instance->detailed_log = $get_arr['detail_log'];
						//$helpers_instance->get_post_ids($post_id ,$hash_key);
						$log_table_name = $wpdb->prefix ."cfimport_detail_log";
						$remaining_records = $total_rows - $i;
						$wpdb->get_results("UPDATE $log_table_name SET processing_records = $i , remaining_records = $remaining_records , status = 'Processing' WHERE hash_key = '$hash_key'");
						if ($i == $total_rows) {
							$wpdb->get_results("UPDATE $log_table_name SET status = 'Completed' WHERE hash_key = '$hash_key'");
						}

						if (count($core_instance->detailed_log) > 5) {
							$log_manager_instance->get_event_log($hash_key, $file_name, $file_extension, $get_mode, $total_rows, $selected_type, $core_instance->detailed_log, $addHeader);
							$addHeader = false;
							$core_instance->detailed_log = [];
						}
					}
					if ($i > $limit) {
					    break;
				    }
			
					$get_timezone = $wpdb->get_var("SELECT time_zone FROM $schedule_tableName WHERE id = $data_id ");
					$date = new \DateTime('now', new \DateTimeZone($get_timezone));
					$current_timestamp_now=$date->format('Y-m-d H:i:s');
			
					// if($current_timestamp_now >= $datetime_after_50_sec){
					// 	$get_limit = get_option('smack_cf_record_limit_'.$data_id);
					// 	if(empty($get_limit)){
					// 		$smack_record_limit = $total_rows - $remaining_records;
					// 		update_option('smack_cf_record_limit_'.$data_id,$smack_record_limit);
					// 	}
					// 	break;
					// }

					$get_limit = get_option('smack_cf_record_limit_'.$data_id);
					if(empty($get_limit)){
						if($current_timestamp_now >= $datetime_after_50_sec){
							$smack_record_limit = $total_rows - $remaining_records;
							update_option('smack_cf_record_limit_'.$data_id,$smack_record_limit);
							break;
						}
					}

					$i++;
				
					if($i > $totRecords) {	
						
						if($frequency != 0){
							//uncomment
							//$page_number = 0;
							//$wpdb->query( "update  ultimate_cfimporter_pro_scheduled_import set start_limit = 0, lastrun = '$last_run',nexrun = '$next_run', cron_status = 'waiting for next schedule' where id = $data_id" );
						
							$page_number = 0;
							$existing_next_run = $wpdb->get_var("SELECT nexrun FROM ultimate_cfimporter_pro_scheduled_import WHERE id = $data_id ");
							$last_run = $existing_next_run;
	
							$frequency_times = array(
								1 => '+1 day',
								2 => '+1 week',
								3 => '+1 month',
								4 => '+1 hour',
								5 => '+30 minutes',
								6 => '+15 minutes',
								7 => '+10 minutes',
								8 => '+5 minutes',
								9 => '+120 minutes',
								10 => '+240 minutes'
							);
	
							$existing_next_runs = new \DateTime($existing_next_run);
							$existing_next_runs->modify($frequency_times[$frequency]);
							$next_run = $existing_next_runs->format('Y-m-d H:i:s');	
						
							$wpdb->query( "UPDATE ultimate_cfimporter_pro_scheduled_import SET start_limit = 0, lastrun = '$last_run',nexrun = '$next_run', cron_status = 'waiting for next schedule' WHERE id = $data_id" );

						}
						else{
							//uncomment
							$page_number = 0;
							delete_option('smack_cf_record_limit_'.$id);
							$wpdb->query( "update  ultimate_cfimporter_pro_scheduled_import set start_limit = 0, lastrun = '$last_run',nexrun = '$next_run', cron_status = 'completed' where id = $data_id" );
							wp_clear_scheduled_hook('smack_cf_cron_schedule_function_'. $data_id);
						}
						break;
					}
				
				}
				fclose($h);
			}
		}
		// if($file_extension == 'xml'){
		// 	$path = $upload_dir . $hash_key . '/' . $hash_key;
		// 	$line_number = 0;
		// 	$header_array = [];
		// 	$value_array = [];
		// 	$addHeader = true;

		// 	for($line_number = 0; $line_number < $total_rows ; $line_number++){
		// 		$xml_class = new XmlHandler();
		// 		$parse_xml = $xml_class->parse_xmls($hash_key,$line_number);
		// 		$i = 0;
		// 		foreach($parse_xml as $xml_key => $xml_value){
		// 			if(is_array($xml_value)){
		// 				foreach ($xml_value as $e_key => $e_value){
		// 					$header_array['header'][$i] = $e_value['name'];
		// 					$value_array['value'][$i] = $e_value['value'];
		// 					$i++;
		// 				}
		// 			}
		// 		}
		// 		$xml = simplexml_load_file($path);
		// 		foreach($xml->children() as $child){   
		// 			$tag = $child->getName();     
		// 		}
		// 		$total_xml_count = $xml_class->get_xml_count($path , $tag);
		// 		if($total_xml_count == 0 || $total_xml_count == 1){
		// 			$sub_child = $xml_class->get_child($child,$path);
		// 			$tag = $sub_child['child_name'];
		// 			$total_xml_count = $sub_child['total_count'];
		// 		}

		// 		$doc = new \DOMDocument();
		// 		$doc->load($path);
		// 		foreach ($map as $field => $value) {
		// 			foreach ($value as $head => $val) {
		// 				if (preg_match('/{/',$val) && preg_match('/}/',$val)){
		// 					preg_match_all('/{(.*?)}/', $val, $matches);
		// 					$line_numbers = $line_number+1;	
		// 					$val = preg_replace("{"."(".$tag."[+[0-9]+])"."}", $tag."[".$line_numbers."]", $val);
		// 					for($i = 0 ; $i < count($matches[1]) ; $i++){		
		// 						$matches[1][$i] = preg_replace("(".$tag."[+[0-9]+])", $tag."[".$line_numbers."]", $matches[1][$i]);
		// 						$value = $this->parse_element($doc, $matches[1][$i], $line_number);	
		// 						$search = '{'.$matches[1][$i].'}';
		// 						$val = str_replace($search, $value, $val);
		// 					}
		// 					$mapping[$field][$head] = $val;	
		// 				} 
		// 				else{
		// 					$mapping[$field][$head] = $val;
		// 				}

		// 			}
		// 		}
		// 		$get_arr = ScheduleImport::$save_mapping_instance->main_import_process($mapping , $header_array ,$value_array , $selected_type , $get_mode, $line_number , $check , $hash_key, $update_based_on, $gmode);
		// 		$post_id = $get_arr['id'];	
		// 		$core_instance->detailed_log = $get_arr['detail_log'];
		// 		$helpers_instance->get_post_ids($post_id ,$hash_key);
		// 		$line_numbers = $line_number + 1;
		// 		$remaining_records = $total_rows - $line_numbers;
		// 		$wpdb->get_results("UPDATE $log_table_name SET processing_records = $line_number + 1 , remaining_records = $remaining_records , status = 'Processing' WHERE hash_key = '$hash_key'");

		// 		if($line_number == $total_rows - 1){
		// 			$wpdb->get_results("UPDATE $log_table_name SET status = 'Completed' WHERE hash_key = '$hash_key'");
		// 		}

		// 		if (count($core_instance->detailed_log) > 5) {
		// 			$log_manager_instance->get_event_log($hash_key , $file_name , $file_extension, $get_mode , $total_rows , $selected_type , $core_instance->detailed_log, $addHeader);
		// 			$addHeader = false;
		// 			$core_instance->detailed_log = [];
		// 		}

		// 		if($line_number == $total_rows - 1) {	
		// 			$wpdb->query( "update ultimate_cfimporter_pro_scheduled_import set start_limit = 0, lastrun = '$last_run', nexrun = '$next_run', cron_status = 'completed' where id = $data_id" );
		// 		}
		// 	}
		// }


		if($file_extension == 'xml'){
			$path = $upload_dir . $hash_key . '/' . $hash_key;
			
			$get_limit = get_option('smack_cf_xml_record_limit_'.$data_id);
			$total_rows = json_decode($total_rows);
			$background_values = $wpdb->get_results("SELECT mapping , module  FROM $template_table_name WHERE `eventKey` = '$hash_key' ");
			foreach ($background_values as $values) {
				$mapped_fields_values = $values->mapping;
				$selected_type = $values->module;
			}
			$map = unserialize($mapped_fields_values);
			$upload_dir = ScheduleImport::$smackcsv_instance->create_upload_dir();
			$path = $upload_dir . $hash_key . '/' . $hash_key;    

			$xml = simplexml_load_file($path);
			$xml_arr = json_decode( json_encode($xml) , 1);
			if ( count($xml_arr) == count($xml_arr, COUNT_RECURSIVE)) 
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
			if(!empty($get_limit)){
				$record_limit = $get_limit;
			}
			else{
				$record_limit = $total_rows;
			}
			
			$lined_number = (($record_limit * $page_number) - $record_limit);
			$limit = ($record_limit * $page_number) - 1;
			$header_array = [];
			$value_array = [];
			$i = 0;
			$info = [];
			if($page_number == 1)
			{
				$addHeader = true;
			}

			// time calculation for next 50 seconds
			$datetime_now = new \DateTime($last_run);
			$datetime_now->modify('+50 seconds');
			$datetime_after_50_sec = $datetime_now->format('Y-m-d H:i:s');	

			for($line_number = 0; $line_number < $total_rows ; $line_number++){
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
					

					
					// foreach($xml->children() as $child){   
					// 	$tag = $child->getName();     
					// }
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
					$h=0;
					// $total_xml_count = $xml_class->get_xml_count($path , $tag);
					// if($total_xml_count == 0 ){
					// 	$sub_child = $xml_class->get_child($child,$path);
					// 	$tag = $sub_child['child_name'];
					// 	$total_xml_count = $sub_child['total_count'];
					// }

					// $doc = new \DOMDocument();
					// $doc->load($path);
					// foreach ($map as $field => $value) {
					// 	foreach ($value as $head => $val) {
					// 		if (preg_match('/{/',$val) && preg_match('/}/',$val)){
					// 			preg_match_all('/{(.*?)}/', $val, $matches);
					// 			$line_numbers = $i+1;	
					// 			$val = preg_replace("{"."(".$tag."[+[0-9]+])"."}", $tag."[".$line_numbers."]", $val);
					// 			for($k = 0 ; $k < count($matches[1]) ; $k++){		
					// 				$matches[1][$k] = preg_replace("(".$tag."[+[0-9]+])", $tag."[".$line_numbers."]", $matches[1][$k]);
					// 				$value = $this->parse_element($doc, $matches[1][$k], $i);	
					// 				$search = '{'.$matches[1][$k].'}';
					// 				$val = str_replace($search, $value, $val);
					// 			}
					// 			$mapping[$field][$head] = $val;	
					// 		} 
					// 		else{
					// 			$mapping[$field][$head] = $val;
					// 		}

					// 	}
					// }

					// array_push($info, $value_array['value']);
					$unmatched_rows = '';
					$unmatched_row_value = get_option('sm_uci_pro_settings');
					$unmatched_rows = isset($unmatched_row_value['unmatchedrow']) ? $unmatched_row_value['unmatchedrow'] : '';
					// $get_arr = ScheduleImport::$save_mapping_instance->main_import_process($mapping , $header_array['header'] ,$value_array['value'] , $selected_type , $get_mode, $i ,$unmatched_rows , $check , $hash_key, $update_based_on, $gmode);
					// $post_id = $get_arr['id'];	
					// $core_instance->detailed_log = $get_arr['detail_log'];
					if($arraytype == 'parent'){
						foreach($childs as $tag){
							$mapping=array();
					// $total_xml_count = $this->get_xml_count($path , $tag);
							$total_xml_count = $xml_class->get_xml_count($path , $tag);
							foreach($xml->children() as $child){  
								$child_names =  $child->getName();  
							}
							if($total_xml_count == 0){
								$sub_child = $xml_class->get_child($child,$path);
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
								$get_arr = ScheduleImport::$save_mapping_instance->main_import_process($mapping, $header_array[$h]['header'], $value_array[$h]['value'], $selected_type, $get_mode, $i, $unmatched_rows, $check, $hash_key, $update_based_on, $gmode);
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
							}
						}
						array_push($info, $value_array[$h]['value']);
						if(!empty($mapping)){
							$get_arr = ScheduleImport::$save_mapping_instance->main_import_process($mapping, $header_array[$h]['header'], $value_array[$h]['value'], $selected_type, $get_mode, $i, $unmatched_rows, $check, $hash_key, $update_based_on, $gmode);
							
							$post_id = $get_arr['id'];
							$core_instance->detailed_log = $get_arr['detail_log'];
						}
					}
					$helpers_instance->get_post_ids($post_id ,$hash_key);
					$line_numbers = $i + 1;
					$remaining_records = $total_rows - $line_numbers;
					$wpdb->get_results("UPDATE $log_table_name SET processing_records = $i + 1 , remaining_records = $remaining_records , status = 'Processing' WHERE hash_key = '$hash_key'");

					if($i == $total_rows - 1){
						$wpdb->get_results("UPDATE $log_table_name SET status = 'Completed' WHERE hash_key = '$hash_key'");
					}

					if (count($core_instance->detailed_log) > 5) {
						$log_manager_instance->get_event_log($hash_key , $file_name , $file_extension, $get_mode , $total_rows , $selected_type , $core_instance->detailed_log, $addHeader);
						$addHeader = false;
						$core_instance->detailed_log = [];
					}

					if($i == $total_rows - 1) {	
						$wpdb->query( "update ultimate_cfimporter_pro_scheduled_import set start_limit = 0, lastrun = '$last_run', nexrun = '$next_run', cron_status = 'completed' where id = $data_id" );
					}
				}

				if ($i > $limit) {
					break;
				}

				$get_timezone = $wpdb->get_var("SELECT time_zone FROM ultimate_cfimporter_pro_scheduled_import WHERE id = $data_id ");
				$date = new \DateTime('now', new \DateTimeZone($get_timezone));
				$current_timestamp_now=$date->format('Y-m-d H:i:s');
		
				if($current_timestamp_now >= $datetime_after_50_sec){
					$get_limit = get_option('smack_cf_xml_record_limit_'.$data_id);
					if(empty($get_limit)){
						$smack_record_limit = $total_rows - $remaining_records;
						update_option('smack_cf_xml_record_limit_'.$data_id,$smack_record_limit);
					}
					break;
				}

				$i++;

				if($i >= $total_rows) {	
					if($frequency != 0){
						//uncomment
						//$page_number = 0;
						//$wpdb->query( "UPDATE ultimate_cfimporter_pro_scheduled_import set start_limit = 0, lastrun = '$last_run',nexrun = '$next_run', cron_status = 'waiting for next schedule' where id = $data_id" );
					
						$page_number = 0;
						$existing_next_run = $wpdb->get_var("SELECT nexrun FROM ultimate_cfimporter_pro_scheduled_import WHERE id = $data_id ");
						$last_run = $existing_next_run;

						$frequency_times = array(
							1 => '+1 day',
							2 => '+1 week',
							3 => '+1 month',
							4 => '+1 hour',
							5 => '+30 minutes',
							6 => '+15 minutes',
							7 => '+10 minutes',
							8 => '+5 minutes',
							9 => '+120 minutes',
							10 => '+240 minutes'
						);

						$existing_next_runs = new \DateTime($existing_next_run);
						$existing_next_runs->modify($frequency_times[$frequency]);
						$next_run = $existing_next_runs->format('Y-m-d H:i:s');	
					
						$wpdb->query( "UPDATE ultimate_cfimporter_pro_scheduled_import SET start_limit = 0, lastrun = '$last_run',nexrun = '$next_run', cron_status = 'waiting for next schedule' WHERE id = $data_id" );
					}
					else{
						//uncomment
						$page_number = 0;
						delete_option('smack_cf_xml_record_limit_'.$id);
						$wpdb->query( "UPDATE ultimate_cfimporter_pro_scheduled_import set start_limit = 0, lastrun = '$last_run',nexrun = '$next_run', cron_status = 'completed' where id = $data_id" );
						wp_clear_scheduled_hook('smack_cf_cron_schedule_function_'. $data_id);
					}
				}	
			}
		}
		//added code for deleting records from csv - settings options
		// if (($unmatched_rows == 'true') && ($page_number >= $total_pages)){
			if ($unmatched_rows == 'true'){
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
				if($import_type_value == 'post' || $import_type_value == 'product' || $import_type_value == 'page' || $import_name_as == 'CustomPosts'){
					
					$get_total_row_count =  $wpdb->get_col("SELECT DISTINCT ID FROM {$wpdb->prefix}posts WHERE post_type = '{$import_type_value}' AND post_status != 'trash' ");
					$unmatched_id=array_diff($get_total_row_count,$test);
					foreach($unmatched_id as $keys => $values){
						$wpdb->get_results("DELETE FROM {$wpdb->prefix}posts WHERE `ID` = '$values' ");
					}
				}
				$wpdb->get_results("DELETE FROM {$wpdb->prefix}post_entries_table");
				
			}
		//uncomment
		update_option('smack_cf_page_number_'. $data_id, $page_number);

		if (count($core_instance->detailed_log) > 0) {
			$log_manager_instance->get_event_log($hash_key , $file_name , $file_extension, $get_mode , $total_rows , $selected_type , $core_instance->detailed_log, $addHeader);
		}

		$file_manager_instance->manage_records($hash_key ,$selected_type , $file_name , $total_rows);
		$upload = wp_upload_dir();
		$upload_base_url = $upload['baseurl'];
		$upload_url = $upload_base_url . '/smack_uci_uploads/imports/';
		$log_path = $upload_dir.$hash_key.'/'.$hash_key.'.html';

		if(file_exists($log_path)){
			$log_link_path = $upload_url. $hash_key .'/'.$hash_key.'.html';
			$response['success'] = true;
			$response['log_link'] = $log_link_path;
		}else{
			$response['success'] = false;
		}
	}

	public function parse_element($xml,$query){
		$query = strip_tags($query);
		$xpath = new \DOMXPath($xml);
		$entries = $xpath->query($query);
		$content = $entries->item(0)->textContent;
		return $content;
	}
}
