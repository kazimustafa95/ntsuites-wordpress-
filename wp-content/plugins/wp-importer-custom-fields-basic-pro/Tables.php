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

class Tables {

	private static $instance = null;
	private static $smack_csv_instance = null;

	public static function getInstance() {

			if (Tables::$instance == null) {
			Tables::$instance = new Tables;
			Tables::$smack_csv_instance = SmackCSV::getInstance();
			Tables::$instance->create_tables();
			return Tables::$instance;
		}
		return Tables::$instance;
	}

	public function create_tables(){
		global $wpdb;
		$file_table_name = $wpdb->prefix ."smackcf_file_events";
		
		$wpdb->query("CREATE TABLE IF NOT EXISTS $file_table_name (
			`id` int(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			`file_name` VARCHAR(255) NOT NULL,
			`status` VARCHAR(255) NOT NULL,
			`mode` VARCHAR(255) NOT NULL,
			`hash_key` VARCHAR(255) NOT NULL,
			`templatekey` VARCHAR(32),
			`total_rows` longtext,
			`lock` BOOLEAN DEFAULT false,
			`progress` INT(6)) ENGINE=InnoDB" 
				);

		$image_table =  $wpdb->prefix ."ultimate_cf_importer_media";
		$wpdb->query("CREATE TABLE IF NOT EXISTS $image_table (
			`post_id` INT(6),
			`attach_id` INT(6) NOT NULL,
			`image_url` VARCHAR(255) NOT NULL,
			`hash_key` VARCHAR(255) NOT NULL,
			`templatekey` VARCHAR(32),
			`status` VARCHAR(255) DEFAULT 'pending',
			`module` VARCHAR(255) DEFAULT NULL,
			`image_type` VARCHAR(255) DEFAULT NULL
				) ENGINE=InnoDB"
				);

		$shortcode_table_name =  $wpdb->prefix ."ultimate_cf_importer_shortcode_manager";
		$wpdb->query("CREATE TABLE IF NOT EXISTS $shortcode_table_name (
			`post_id` INT(6),
			`image_shortcode` VARCHAR(255) NOT NULL,
			`original_image` VARCHAR(255) NOT NULL,
			`import_type` VARCHAR(10),
			`hash_key` VARCHAR(255) NOT NULL,
			`templatekey` VARCHAR(32),
			`image_meta` TEXT DEFAULT NULL,
			`status` VARCHAR(255) DEFAULT 'pending'
				) ENGINE=InnoDB"
				);

		$schedule_import_table = "ultimate_cfimporter_pro_scheduled_import";
		$wpdb->query( "CREATE TABLE IF NOT EXISTS $schedule_import_table (
			`id` int(10) NOT NULL AUTO_INCREMENT,
			`templateid` int(10) NOT NULL,
			`importid` int(10) NOT NULL,
			`createdtime` datetime NOT NULL,
			`updatedtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			`isrun` int(1) DEFAULT '0',
			`scheduledtimetorun` varchar(10) NOT NULL,
			`scheduleddate` date NOT NULL,
			`module` varchar(100) NOT NULL,
			`file_type` varchar(10) NOT NULL,
			`response` blob,
			`version` varchar(10) DEFAULT NULL,
			`event_key` varchar(100) DEFAULT NULL,
			`importbymethod` varchar(60) DEFAULT NULL,
			`import_limit` int(11) DEFAULT '1',
			`import_row_ids` blob default NULL,
			`frequency` int(5) DEFAULT '0',
			`start_limit` int(11) DEFAULT '0',
			`end_limit` int(11) DEFAULT '0',
			`lastrun` datetime DEFAULT '0000-00-00 00:00:00',
			`nexrun` datetime DEFAULT '0000-00-00 00:00:00',
			`scheduled_by_user` varchar(10) DEFAULT '1',
			`cron_status` varchar(30) DEFAULT 'pending',
			`import_mode` varchar(100) NOT NULL,
			`duplicate_headers` blob DEFAULT NULL,
			`time_zone` varchar(100) DEFAULT NULL,
			`hook_name` varchar(100) DEFAULT NULL,
			PRIMARY KEY (`id`)) ENGINE=InnoDB"
				);  

		$ftp_schedule_table = "ultimate_cfimporter_pro_ftp_schedules";
		$wpdb->query( "
				CREATE TABLE IF NOT EXISTS $ftp_schedule_table (
					`id` int(10) NOT NULL AUTO_INCREMENT,
					`schedule_id` int(10) NOT NULL,
					`hostname` varchar(110) DEFAULT NULL,
					`username` varchar(110) DEFAULT NULL,
					`password` varchar(110) DEFAULT NULL,
					`initial_path` varchar(225) DEFAULT NULL,
					`filename` varchar(110) DEFAULT NULL,
					`port_no` int(5) DEFAULT NULL,
					`hosttype` varchar(110) DEFAULT NULL,
					PRIMARY KEY (`id`)
					) ENGINE=InnoDB");

		$external_url_schedule = "ultimate_cfimporter_pro_external_file_schedules";
		$wpdb->query( "CREATE TABLE IF NOT EXISTS $external_url_schedule (
			`id` int(10) NOT NULL AUTO_INCREMENT,
			`schedule_id` int(10) NOT NULL,
			`file_url` varchar(255) DEFAULT NULL,
			`filename` varchar(255) DEFAULT NULL,
			PRIMARY KEY (`id`)
				) ENGINE=InnoDB");

		$template_table_name = "ultimate_cfimporter_pro_mappingtemplate";
		$wpdb->query( "CREATE TABLE IF NOT EXISTS $template_table_name (
			`id` int(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			`templatename` varchar(250) NOT NULL,
			`mapping` blob NOT NULL,
			`createdtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			`deleted` int(1) DEFAULT '0',
			`templateused` int(10) DEFAULT '0',
			`mapping_type` varchar(30),
			`module` varchar(50) DEFAULT NULL,
			`csvname` varchar(250) DEFAULT NULL,
			`eventKey` varchar(60) DEFAULT NULL				
				) ENGINE = InnoDB "
				);  

		$log_table_name = $wpdb->prefix ."cfimport_detail_log";
		$wpdb->query("CREATE TABLE IF NOT EXISTS $log_table_name (
			`id` int(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			`file_name` VARCHAR(255) NOT NULL,
			`templatekey` VARCHAR(32),
			`status` VARCHAR(255) NOT NULL,
			`hash_key` VARCHAR(255) NOT NULL,
			`total_records` INT(6),
			`processing_records` INT(6) default 0,
			`remaining_records` INT(6) default 0,
			`filesize` VARCHAR(255) NOT NULL,
			`created` bigint(20) NOT NULL default 0,
			`updated` bigint(20) NOT NULL default 0,
			`skipped` bigint(20) NOT NULL default 0
				) ENGINE=InnoDB" 
				);

		$import_records_table = "ultimate_cfimporter_pro_smackuci_events";
		$wpdb->query("CREATE TABLE IF NOT EXISTS $import_records_table (
			`id` bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			`revision` bigint(20) NOT NULL default 0,
			`name` varchar(255),
			`original_file_name` varchar(255),
			`friendly_name` varchar(255),
			`import_type` varchar(32),
			`filetype` text,
			`filepath` text,
			`eventKey` varchar(32),
			`templatekey` VARCHAR(32),
			`registered_on` datetime NOT NULL default '0000-00-00 00:00:00',
			`parent_node` varchar(255),
			`processing` tinyint(1) NOT NULL default 0,
			`executing` tinyint(1) NOT NULL default 0,
			`triggered` tinyint(1) NOT NULL default 0,
			`event_started_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			`count` bigint(20) NOT NULL default 0,
			`processed` bigint(20) NOT NULL default 0,
			`created` bigint(20) NOT NULL default 0,
			`updated` bigint(20) NOT NULL default 0,
			`skipped` bigint(20) NOT NULL default 0,
			`deleted` bigint(20) NOT NULL default 0,
			`is_terminated` tinyint(1) NOT NULL default 0,
			`terminated_on` datetime NOT NULL default '0000-00-00 00:00:00',
			`last_activity` datetime NOT NULL default '0000-00-00 00:00:00',
			`siteid` int(11) NOT NULL DEFAULT 1,
			`month` varchar(60) DEFAULT NULL,
			`year` varchar(60) DEFAULT NULL,
			`deletelog` BOOLEAN DEFAULT false
				) ENGINE=InnoDB"
				);

		$custom_fields_table = "ultimate_cfimporter_pro_smack_field_types";
		$wpdb->query("CREATE TABLE IF NOT EXISTS $custom_fields_table (
			`id` int(10) NOT NULL AUTO_INCREMENT,
			`choices` varchar(160) NOT NULL,
			`fieldType` varchar(100) NOT NULL,
			`groupType` varchar(100) NOT NULL,
			PRIMARY KEY (`id`)
				) ENGINE=InnoDB"
				);

		$acf_fields_table = "ultimate_cfimporter_pro_acf_fields";
		$wpdb->query("CREATE TABLE IF NOT EXISTS $acf_fields_table (
			`id` int(10) NOT NULL AUTO_INCREMENT,
			`groupId` varchar(100) NOT NULL,
			`fieldId` varchar(100) NOT NULL,
			`fieldLabel` varchar(100) NOT NULL,
			`fieldName` varchar(100) NOT NULL,
			`fieldType` varchar(60) NOT NULL,
			`fdOption` varchar(100) DEFAULT NULL,
			PRIMARY KEY (`id`)
				) ENGINE=InnoDB"
				);

		$post_entries_table = $wpdb->prefix ."post_entries_table";
		$table = $wpdb->query("CREATE TABLE IF NOT EXISTS $post_entries_table (
					`ID` INT(6),
					`file_name` varchar(255) DEFAULT NULL,
					`type` varchar(255) DEFAULT NULL,
					`revision` INT(6),
					`status` varchar(255) DEFAULT NULL
					) ENGINE=InnoDB"
					);

		$clitemplate = $wpdb->prefix. "cli_cf_template";
		$clitemplate = $wpdb->query("CREATE TABLE IF NOT EXISTS $clitemplate (
			`ID` INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			`template_name` varchar(255) DEFAULT NULL,
			`file_name` varchar(255) DEFAULT NULL,
			`type` varchar(255) DEFAULT NULL,
			`templatekey` varchar(32),	
			`month` varchar(60) DEFAULT NULL,
			`year` varchar(60) DEFAULT NULL					
			) ENGINE=InnoDB"
			);					


		$result = $wpdb->query("SHOW COLUMNS FROM `{$wpdb->prefix}cfimport_detail_log` LIKE 'running'");
		if($result == 0){
			$wpdb->query("ALTER TABLE `{$wpdb->prefix}cfimport_detail_log` ADD COLUMN running boolean not null default 1");
		}

		$result = $wpdb->query("SHOW COLUMNS FROM `{$wpdb->prefix}ultimate_cf_importer_shortcode_manager` LIKE 'import_type'");
		if($result == 0){
			$wpdb->query("ALTER TABLE `{$wpdb->prefix}ultimate_cf_importer_shortcode_manager` ADD COLUMN import_type varchar(10)");
		}

		$result_4 = $wpdb->query("SHOW COLUMNS FROM `{$wpdb->prefix}cfimport_detail_log` LIKE 'templatekey'");
		if($result_4 == 0){
			$wpdb->query("ALTER TABLE `{$wpdb->prefix}cfimport_detail_log` ADD COLUMN templatekey varchar(32)");
		}

		$result_5 = $wpdb->query("SHOW COLUMNS FROM `{$wpdb->prefix}ultimate_cf_importer_shortcode_manager` LIKE 'templatekey'");
		if($result_5 == 0){
			$wpdb->query("ALTER TABLE `{$wpdb->prefix}ultimate_cf_importer_shortcode_manager` ADD COLUMN templatekey varchar(32)");
		}

		$shortcode_result = $wpdb->query("SHOW COLUMNS FROM `{$wpdb->prefix}ultimate_cf_importer_shortcode_manager` LIKE 'image_meta'");
		if($shortcode_result == 0){
			$wpdb->query("ALTER table `{$wpdb->prefix}ultimate_cf_importer_shortcode_manager` ADD COLUMN image_meta TEXT DEFAULT NULL;");
		}

		$result = $wpdb->query("SHOW COLUMNS FROM `{$wpdb->prefix}ultimate_cf_importer_media` LIKE 'templatekey'");
		if($result == 0){
			$wpdb->query("ALTER TABLE `{$wpdb->prefix}ultimate_cf_importer_media` ADD COLUMN templatekey varchar(32)");
		}

		$result = $wpdb->query("SHOW COLUMNS FROM `{$wpdb->prefix}smackcf_file_events` LIKE 'templatekey'");
		if($result == 0){
			$wpdb->query("ALTER TABLE `{$wpdb->prefix}smackcf_file_events` ADD COLUMN templatekey varchar(32)");
		}
		
		$schedule_result = $wpdb->query("SHOW COLUMNS FROM `ultimate_cfimporter_pro_scheduled_import` LIKE 'hook_name'");
		if($schedule_result == 0){
			$wpdb->query("ALTER table `ultimate_cfimporter_pro_scheduled_import` ADD COLUMN hook_name varchar(100) DEFAULT NULL;");
		}

		/** Added deletelog column for log manager deletion available from ultimate csv pro version 6.4  */
		$logmanager_result = $wpdb->query("SHOW COLUMNS FROM ultimate_cfimporter_pro_smackuci_events LIKE 'deletelog'");
		if($logmanager_result == 0){
			$wpdb->query("ALTER table ultimate_cfimporter_pro_smackuci_events ADD COLUMN deletelog BOOLEAN DEFAULT false;");
		}

		$logmanager_result = $wpdb->query("SHOW COLUMNS FROM ultimate_cfimporter_pro_smackuci_events LIKE 'templatekey'");
		if($logmanager_result == 0){
			$wpdb->query("ALTER table ultimate_cfimporter_pro_smackuci_events ADD COLUMN templatekey varchar(32)");
		}
	}
}
