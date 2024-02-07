<?php
/******************************************************************************************
 * Copyright (C) Smackcoders. - All Rights Reserved under Smackcoders Proprietary License
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/
global $wpdb;

$wpdb->hide_errors();

require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

$collate = '';
if ( $wpdb->has_cap( 'collation' ) ) {
	if ( ! empty( $wpdb->charset ) ) {
		$collate .= "DEFAULT CHARACTER SET $wpdb->charset";
	}
	if ( ! empty( $wpdb->collate ) ) {
		$collate .= " COLLATE $wpdb->collate";
	}
}

$wpdb->query("alter table ultimate_cfimporter_pro_scheduled_import modify column `cron_status` varchar(15) DEFAULT 'pending'");
