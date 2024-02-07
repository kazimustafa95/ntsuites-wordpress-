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

 // Define WP CLI command.
if (class_exists('\WP_CLI')) {    
    \WP_CLI::add_command( 'ultimate-cfimport', 'Smackcoders\CFCSV\Smackuci_Cli' );
}

/**
 * Class Smackuci_Cli
 */
class Smackuci_Cli{
    private static $smackcsv_instance = null;    
    
    public static function getInstance()
	{
        if (Smackuci_Cli::$instance == null) {
            Smackuci_Cli::$instance = new Smackuci_Cli;
            Smackuci_Cli::$smackcsv_instance = SmackCSV::getInstance();
            return Smackuci_Cli::$instance;
        }
        return Smackuci_Cli::$instance;        
    }

    /**          
     * @param $args
     * @param $assoc_args
     */
    function run( $args, $assoc_args ) {    
        //Get id from cli    
        list( $idlist ) = $args;        
        $idlist = explode(',', $idlist );

        $helpers_instance = ImportHelpers::getInstance();
        $core_instance = CoreFieldsImport::getInstance();
        $smackcsv_instance = SmackCSV::getInstance();
        
        $file_manager_instance = FileManager::getInstance();
        $log_manager_instance = LogManager::getInstance();
        global $wpdb;
        $gmode = 'CLI';
        $file_table_name = $wpdb->prefix . "smackcf_file_events";                
        $template_table_name = "ultimate_cfimporter_pro_mappingtemplate";        
        $upload_dir = $smackcsv_instance->create_upload_dir($gmode);                        
            
        foreach( $idlist as $id) {
            try {                    
                $background_values = $wpdb->get_results("SELECT mapping , templatename, eventKey, module  FROM $template_table_name WHERE `id` = '$id' ");
                foreach ($background_values as $values) {
                    $mapped_fields_values = $values->mapping;
                    $selected_type = $values->module;
                    $template = $values->templatename;
                    $hash_key = $values->eventKey;
                }
                $file_path = $upload_dir.$hash_key.'/'.$hash_key;                              
                $get_id = $wpdb->get_results("SELECT id , mode , file_name, total_rows FROM $file_table_name WHERE `hash_key` = '$hash_key'");
                $get_mode = $get_id[0]->mode;
                $total_rows = $get_id[0]->total_rows;
                $file_name = $get_id[0]->file_name;
                
                $line_number = 1;
                $progress = NULL;
                $addHeader = true;
                $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);                
                //unique key for each Template 
                $templatekey = $smackcsv_instance->convert_string2hash_key($template);                                                
                $map = unserialize($mapped_fields_values);
                //Template Directory
                $uploadpath = $upload_dir.$hash_key.'/'.$templatekey;
                if (!is_dir($uploadpath)) {
                    wp_mkdir_p($uploadpath);
                }
                  
		        if(empty($file_extension)){
                    $file_extension = 'xml';                    
                }
                
                $schedule_array = array($templatekey,'templatekey',$selected_type,$hash_key);
		
                if ( ! wp_next_scheduled( 'smackcf_image_schedule_hook', $schedule_array) ) {
		        	wp_schedule_event( time(), 'smack_image_every_second', 'smackcf_image_schedule_hook', $schedule_array );	
		        }
                $start = time();
                $progress = \WP_CLI\Utils\make_progress_bar( 'Importing ' . $selected_type, $total_rows );                
                if($file_extension == 'csv'){
                    $validatefile_instance = ValidateFile::getInstance();
                    ini_set("auto_detect_line_endings", true);
			if (($h = fopen($upload_dir.$hash_key.'/'.$hash_key, "r")) !== FALSE) 
			{
				$delimiters = array( ',','\t',';','|',':','&nbsp');
				$file_path = $upload_dir . $hash_key . '/' . $hash_key;
				$delimiter = $validatefile_instance->getFileDelimiter($file_path, 5);
				$array_index = array_search($delimiter,$delimiters);
				$file_iteration = get_option('sm_cf_import_iteration_limit');
				if($array_index == 5){
					$delimiters[$array_index] = ' ';
				}
				
				$line_number = 0;				
				$info = [];
                $all_value_array = array();
				$i = 0;
				while(($data = fgetcsv($h, 0, $delimiters[$array_index]))!== FALSE) {
					$trimmed_array = array_map('trim', $data);
					array_push($info , $trimmed_array);

					if ($i == 0) {
						$header_array = $info[$i];
                    }
                    else {                        
                            $values = $info[$i];
                            array_push($all_value_array , $values);		
                        }
                        $i++;
                    }												
					}	                    
                    $importids = $this->importcsv($all_value_array,$map,$header_array,$selected_type,$get_mode,$hash_key,$templatekey,$gmode,$progress,$file_name,$total_rows);	
					
            }
                elseif($file_extension == 'xml'){
                    $total_rows = $this->set_rowcount($map,$total_rows,$file_path);                                                            
                    $importids = $this->importxml($map,$selected_type,$get_mode,$hash_key,$templatekey,$gmode,$progress,$file_name,$file_path,$total_rows);
                }
                elseif($file_extension == 'json') 
                    $importids = $this->importjson();                
                else 
                    $importids = $this->importcsv($all_value_array,$map,$header_array,$selected_type,$get_mode,$hash_key,$templatekey,$gmode,$progress,$file_name,$total_rows);
             
                $end = time();
                //Log file creation after import .html  
                if(!empty($importids)){
                    $core_instance->detailed_log = $importids['detail_log'];                    
                    $log_manager_instance->get_event_log($hash_key, $file_name, $file_extension, $get_mode, $total_rows, $selected_type, $core_instance->detailed_log, $addHeader,$templatekey);                   
                    $this->dashboardrecords($template,$file_name,$templatekey,$selected_type);
                    \WP_CLI::success( sprintf(__('Import completed. [ time: %s ]'), human_time_diff($start, $end)));
                    $this->showrecords($templatekey);
                }
            	
            } catch (Exception $e) {
               \WP_CLI::error($e->getTraceAsString());
            }
        }
    }

    /**
     * revision
     * 
     */

     /**
      * csv import    
      */
      function importcsv($all_value_array,$map,$header_array,$selected_type,$get_mode,$hash_key,$templatekey,$gmode,$progress,$file_name,$total_rows){
        global $wpdb;
        $save_mapping_instance = SaveMapping::getInstance();                
                        
        foreach($all_value_array as $line_number => $value_array){
            if(!empty($value_array)){     
                $this->maintainlog($templatekey,$hash_key,$file_name,$total_rows,$line_number,'Processing');                   
                $get_arr = $save_mapping_instance->main_import_process($map, $header_array, $value_array, $selected_type, $get_mode, $line_number,'', 'post_title', $hash_key,'', $gmode,$templatekey);                                                                                                                                          
                if ($progress) {
                    $progress->tick();
                }
                //Update Log
                if(!empty($get_arr)){     
                $this->maintainlog($templatekey,$hash_key,$file_name,$total_rows,$line_number,'Processing',$get_arr['id']);
                }                
            }      
                    
        }
        return $get_arr;
      }

      /**
       * Log entry for import
       */
      function maintainlog($templatekey,$hash_key,$file_name,$total_rows,$line_number,$status,$post_id = null){                  
        global $wpdb;
        $helpers_instance = ImportHelpers::getInstance();                      
        $log_table_name = $wpdb->prefix . "cfimport_detail_log";                
        $remain_records = $total_rows - $line_number;
        if($line_number == $total_rows) {
            $status = 'Completed';            
        }
        if($post_id) {
           
        //Log file creation after import .txt                                                                       
        $helpers_instance->get_post_ids($post_id, $hash_key, $templatekey);                                           
        }
        $logid = $wpdb->get_results("select id from $log_table_name WHERE templatekey = '$templatekey'");
        if(!empty($logid)){              
            $wpdb->update($log_table_name, 
                array( 
                    'status' => $status,
                    'processing_records' => $line_number + 1, // Because counts(line number) are start from 0
                    'remaining_records' => $remain_records                                               
                ) , 
                array('templatekey' => $templatekey
                ) 
            );                    
        }
        else {
            $smackcsv_instance = SmackCSV::getInstance();
            $upload_dir = $smackcsv_instance->create_upload_dir('CLI');
            $file_path = $upload_dir.$hash_key.'/'.$hash_key;
            $file_size = filesize($file_path);
		    $filesize = $helpers_instance->formatSizeUnits($file_size);
            $wpdb->insert($log_table_name, array('file_name' => $file_name, 'hash_key' => $hash_key, 'templatekey' => $templatekey, 'total_records' => $total_rows, 'filesize' => $filesize, 'processing_records' => 1, 'remaining_records' => $remain_records, 'status' => $status));
        }                            
      }

      /**
       * set total rows for xml
       */
      function set_rowcount($map,$total_rows,$path){       
            $total_rows = json_decode($total_rows);	            
			$xml = simplexml_load_file($path);
            $xml_arr = json_decode( json_encode($xml) , 1);            

            //Find the arraytype
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
            
            //Set child node            
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
            
            //Set row count
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
		    return $totalrows;		
      }

      /**
       * xml import
       */
      function importxml($map,$selected_type,$get_mode,$hash_key,$templatekey,$gmode,$progress,$file_name,$path,$total_rows){ 
        $save_mapping_instance = SaveMapping::getInstance();
        $info = [];              
        for ($i = 0; $i < $total_rows; $i++) {               
                $xml_class = new XmlHandler();
                $parse_xml = $xml_class->parse_xmls($hash_key,$i,$gmode,$path);
                 $j = 0;
                 $header_array = [];
                 $value_array = [];
                 $head = array();
                 $value = array();
                 $count = array();

                 //SET header and value for each node
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

                 //Find array type
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

                 //SET child
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
                 
                 //SET mapping fields
                 $h=0;
                 if($arraytype == 'parent'){
                    
                    $mapping=array();
                     foreach($childs as $tag){
                                          
                         $total_xml_count = $save_mapping_instance->get_xml_count($path , $tag);
                         foreach($xml->children() as $child){  
                             $child_names =  $child->getName();  
                         }
                         if($total_xml_count == 0){
                             $sub_child = $save_mapping_instance->get_child($child,$path);
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
                                             $value = $save_mapping_instance->parse_element($doc, $matches[1][$k], $i);	
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
                         //array_push($info, $value_array[$h]['value']);
                        
                         /*if(!empty($mapping)){
                            $this->maintainlog($templatekey,$hash_key,$file_name,$total_rows,$i + 1,'Processing');
                             $get_arr = $save_mapping_instance->main_import_process($mapping, $header_array[$h]['header'], $value_array[$h]['value'], $selected_type, $get_mode, $i,'', 'post_title', $hash_key,'', $gmode,$templatekey);
                            if ($progress) {
                                $progress->tick();
                            }
                            //Update Log
                            if(!empty($get_arr)){     
                            $this->maintainlog($templatekey,$hash_key,$file_name,$total_rows,$i + 1,'Processing',$get_arr['id']);
                            }  */
                             //$post_id = $get_arr['id'];
                             //$core_instance->detailed_log = $get_arr['detail_log'];
                        // }
                         $h++;
                     }
                     
                     if(!empty($mapping)){
                        $this->maintainlog($templatekey,$hash_key,$file_name,$total_rows,$i + 1,'Processing');
                         $get_arr = $save_mapping_instance->main_import_process($mapping, $header_array[0]['header'], $value_array[0]['value'], $selected_type, $get_mode, $i,'', 'post_title', $hash_key,'', $gmode,$templatekey);
                      /*   if ($progress) {
                            $progress->tick();
                        }
                        //Update Log
                        if(!empty($get_arr)){     
                        $this->maintainlog($templatekey,$hash_key,$file_name,$total_rows,$i + 1,'Processing',$get_arr['id']);
                        }  */
                         //$post_id = $get_arr['id'];
                         //$core_instance->detailed_log = $get_arr['detail_log'];
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
                     //array_push($info, $value_array[$h]['value']);
                   /*  if(!empty($mapping)){
                         $get_arr = $this->main_import_process($mapping, $header_array[$h]['header'], $value_array[$h]['value'], $selected_type, $get_mode, $i,'','post_title', $hash_key, $update_based_on, $gmode,$templatekey);
                         $post_id = $get_arr['id'];
                         $core_instance->detailed_log = $get_arr['detail_log'];
                     }*/
                     if(!empty($mapping)){
                        $this->maintainlog($templatekey,$hash_key,$file_name,$total_rows,$i + 1,'Processing');
                         $get_arr = $save_mapping_instance->main_import_process($mapping, $header_array[0]['header'], $value_array[0]['value'], $selected_type, $get_mode, $i,'', 'post_title', $hash_key,'', $gmode);                                                 
                     }
                 }
                 if ($progress) {
                    $progress->tick();
                }
                //Update Log
                if(!empty($get_arr)){     
                $this->maintainlog($templatekey,$hash_key,$file_name,$total_rows,$i + 1,'Processing',$get_arr['id']);
                }                                          
            // $i++;
         }
         return $get_arr;
      }

       /**
        * json import
        */
        function importjson(){

        }

        /**
         * show records
         */
        function showrecords($templatekey){
            global $wpdb;
            $items = $wpdb->get_results("select created,updated,skipped,processing_records,total_records from {$wpdb->prefix}cfimport_detail_log where templatekey = '$templatekey'",ARRAY_A);                                                            
                    if(!empty($items)){
                        foreach($items as $values){
                            $records[] = [
                                'Created' => $values['created'],
                                'Updated' => $values['updated'],
                                'Skipped' => $values['skipped'],
                                //'Deleted' => $values['deleted'],
                                'Processed' => $values['processing_records'],
                                'Records' => $values['total_records']
                            ];
                        }
                    \WP_CLI\Utils\format_items( 'table', $records, array( 
                        'Created',
                        'Updated',
                        'Skipped',                        
                        'Processed',
                        'Records'
                    ));
                    }
        }        

        /**
	 * Saves event logs in database.
	 * @param  string $hash_key - File hash key
     * @param  string $selected_type - Post type
	 * @param  string $file_name - File name
	 * @param  string $total_rows - Total rows in file
	 */
    public function dashboardrecords($template,$file_name,$templatekey,$selected_type){		
        global $wpdb;
        $cli_template = $wpdb->prefix ."cli_cf_template";
        $get_details = $wpdb->get_results("SELECT file_name FROM $cli_template where templatekey = '$templatekey'",ARRAY_A);        
        
        //Check record exists or not
        if(empty($get_details)){         
            $extension_object = new ExtensionHandler;
            $import_type = $extension_object->import_name_as($selected_type);   
            $wpdb->insert($cli_template, array('template_name' => $template,'file_name' => $file_name, 'templatekey' => $templatekey,'type' => $import_type,'month' => date('M'),'Year' => date('Y') ));            
        }                     
    }

    /**
     * ## EXAMPLES
     *
     *     wp ultimate-import list          
     *
     * @subcommand list
     * @param $args
     * @param $assoc_args
     */
    function list( $args, $assoc_args ) {        
        try {
            global $wpdb;     
            $file_table_name = $wpdb->prefix . "smackcf_file_events";       
            $templatelist = $wpdb->get_results("SELECT map.id,map.templatename,map.module,uci.file_name,uci.mode FROM ultimate_cfimporter_pro_mappingtemplate as map left join $file_table_name as uci on map.eventkey = uci.hash_key",ARRAY_A);                                    
            
            if(!empty($templatelist)) {
                foreach($templatelist as $data){
                    $items[] = [
                        'ID' => $data['id'],                        
                        'Template Name' => $data['templatename'],                        
                        'File Name' => $data['file_name'],     
                        'Module' => $data['module'],
                        'Mode' => $data['mode']
                    ];
                }                       
                \WP_CLI\Utils\format_items( 'table', $items, array( 'ID',
                'Template Name',                
                'File Name',
                'Module',
                'Mode'));
            }
            else {
                \WP_CLI::log( "Templates Not Found.Only Mapped Templates are used here." );
            }
        } catch (Exception $e) {
            \WP_CLI::error($e->getMessage());
        }
    }   
}
