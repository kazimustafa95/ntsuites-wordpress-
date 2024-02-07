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

class DragandDropExtension {
	private static $drag_drop_instance = null,$validatefile;

	private function __construct(){
		add_action('wp_ajax_displayCSV',array($this,'display_csv_values'));		
	}

	public static function getInstance() {

		if (DragandDropExtension::$drag_drop_instance == null) {
			DragandDropExtension::$drag_drop_instance = new DragandDropExtension;
			DragandDropExtension::$validatefile = ValidateFile::getInstance();
			return DragandDropExtension::$drag_drop_instance;
		}
		return DragandDropExtension::$drag_drop_instance;
	}

	public function display_csv_values(){
		check_ajax_referer('smack-importer-custom-fields-basic-pro', 'securekey');
		global $wpdb;
		$hashkey = sanitize_key($_POST['HashKey']);
		$templatename = sanitize_text_field($_POST['templatename']);
		$get_row = intval($_POST['row']);
		$file_table_name = $wpdb->prefix ."smackcf_file_events";
		$template_table_name = "ultimate_cfimporter_pro_mappingtemplate";
		$row = $get_row - 1;
		if(empty($hashkey)){	
			$get_detail   = $wpdb->get_results( "SELECT eventKey FROM $template_table_name WHERE templatename = '$templatename' " );
			$hashkey = $get_detail[0]->eventKey;
		}
		$smackcsv_instance = SmackCSV::getInstance();
		$upload_dir = $smackcsv_instance->create_upload_dir();		
		ini_set("auto_detect_line_endings", true);
		$info = [];
		if (($h = fopen($upload_dir.$hashkey.'/'.$hashkey, "r")) !== FALSE) 
		{
			$line_number = 0;
			$Headers = [];
			$Values = [];
			$response = [];
			// Convert each line into the local $data variable	
			$delimiters = array( ',','\t',';','|',':','&nbsp');
			$file_path = $upload_dir . $hashkey . '/' . $hashkey;
			$delimiter = DragandDropExtension::$validatefile->getFileDelimiter($file_path, 5);
			$array_index = array_search($delimiter,$delimiters);
			if($array_index == 5){
				$delimiters[$array_index] = ' ';
			}
			while (($data = fgetcsv($h, 0, $delimiters[$array_index])) !== FALSE) 
			{		
				// Read the data from a single line
				$trimmed_array = array_map('trim', $data);
				array_push($info , $trimmed_array);
				if($line_number == 0){
					$Headers = $info[$line_number];
				}else{
					$values = $info[$line_number];
					array_push($Values , $values);		
				}
				$line_number ++;		
			}	
			// Close the file
			fclose($h);
		}

		$get_total_row = $wpdb->get_results("SELECT total_rows FROM $file_table_name WHERE hash_key = '$hashkey' ");
		$total_row = $get_total_row[0]->total_rows;
		$response['success'] = true;
		$response['total_rows'] = $total_row;
		$response['Headers'] = $Headers;
		$response['Values'] = $Values[$row];   
		echo wp_json_encode($response);
		wp_die();
	}

	/**
	 * @param $xml
	 * @param $query
	 * @param $row
	 * @return string
	 */
	public function parse_element($xml,$value,$row,$parent_name,$child_name){
		$xpath = new \DOMXPath($xml);
		$query = '/'.$parent_name.'/'.$child_name.'['.$row.']/'.$value;
		$entries = $xpath->query($query);   
		$content = $entries->item(0)->textContent;
		return $content;
	}	
}
