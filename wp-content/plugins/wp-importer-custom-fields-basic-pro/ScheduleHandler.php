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

// schedule code
add_filter( 'cron_schedules', 'Smackcoders\\CFCSV\\cron_schedule_times' );
add_action('init', 'Smackcoders\\CFCSV\\set_schedule');

global $wpdb;
$check_for_scheduling = $wpdb->get_results("SELECT * FROM ultimate_cfimporter_pro_scheduled_import WHERE isrun = 0 AND cron_status != 'completed'", ARRAY_A);
if($check_for_scheduling){
    foreach($check_for_scheduling as $check_schedule){
        add_action($check_schedule['hook_name'], 'Smackcoders\\CFCSV\\start_cf_schedule_function');
    }
}

function cron_schedule_times( $schedules ) {
    if(!isset($schedules["smack_every_five_minutes"])){
        $schedules["smack_every_five_minutes"] = array(
            // 'interval'  => 5*60,
            'interval'  => 60,
            'display'   => __( 'Smack Every Five Minutes' )
        );
    }
    if(!isset($schedules["smack_every_ten_minutes"])){
        $schedules["smack_every_ten_minutes"] = array(
            //'interval'  => 10*60,
            'interval'  => 60,
            'display'   => __( 'Smack Every Ten Minutes' )
        );
    }
    if(!isset($schedules["smack_every_fifteen_minutes"])){
        $schedules["smack_every_fifteen_minutes"] = array(
            //'interval'  => 15*60,
            'interval'  => 60,
            'display'   => __( 'Smack Every Fifteen Minutes' )
        );
    }
    if(!isset($schedules["smack_every_thirty_minutes"])){
        $schedules["smack_every_thirty_minutes"] = array(
            //'interval'  => 30*60,
            'interval'  => 60,
            'display'   => __( 'Smack Every Thirty Minutes' )
        );
    }
    if(!isset($schedules["smack_every_one_hour"])){
        $schedules["smack_every_one_hour"] = array(
            //'interval'  => 3600,
            'interval'  => 60,
            'display'   => __( 'Smack Every One hour' )
        );
    }
    if(!isset($schedules["smack_every_two_hrs"])){
        $schedules["smack_every_two_hrs"] = array(
            //'interval'  => 120*60,
            'interval'  => 60,
            'display'   => __( 'Smack Every Two hours' )
        );
    }
    if(!isset($schedules["smack_every_four_hrs"])){
        $schedules["smack_every_four_hrs"] = array(
            //'interval'  => 240*60,
            'interval'  => 60,
            'display'   => __( 'Smack Every Four hours' )
        );
    }
    if(!isset($schedules["smack_daily"])){
        $schedules["smack_daily"] = array(
            //'interval'  => 86400,
            'interval'  => 60,
            'display'   => __( 'Smack Daily' )
        );
    }
    if(!isset($schedules["smack_weekly"])){
        $schedules["smack_weekly"] = array(
            //'interval'  => 604800,
            'interval'  => 60,
            'display'   => __( 'Smack Weekly' )
        );
    }
    if(!isset($schedules["smack_monthly"])){
        $schedules["smack_monthly"] = array(
            //'interval'  => 2592000,
            'interval'  => 60,
            'display'   => __( 'Smack Monthly' )
        );
    }
    if(!isset($schedules["smack_one_time"])){
        $schedules["smack_one_time"] = array(
            //'interval'  => 3,
            'interval'  => 60,
            'display'   => __( 'Smack One Time' )
        );
    }
    if(!isset($schedules["smack_inline_every_second"])){
        $schedules["smack_inline_every_second"] = array(
            //'interval'  => 2,
            'interval'  => 60,
            'display'   => __( 'Smack Inline Image Every Second' )
        );
    }
    if(!isset($schedules["smack_image_every_second"])){
        $schedules["smack_image_every_second"] = array(
            //'interval'  => 2,
            'interval'  => 60,
            'display'   => __( 'Smack Featured Image Every Second' )
        );
    }
    return $schedules;
}

function set_schedule(){
    global $wpdb;
    $timeZone = $wpdb->get_results("SELECT * FROM ultimate_cfimporter_pro_scheduled_import WHERE isrun = 0 AND cron_status != 'completed' ");
    if(!empty($timeZone)){
        $date = new \DateTime('now', new \DateTimeZone($timeZone[0]->time_zone));
        $current_timestamp=$date->format('Y-m-d H:i:s');
        $scheduleList = $wpdb->get_results("SELECT * FROM ultimate_cfimporter_pro_scheduled_import WHERE isrun = 0 AND nexrun <= '$current_timestamp' AND cron_status != 'completed'", ARRAY_A);
    }
 
    if (!empty($scheduleList)) {
        // Schedule an action if it's not already scheduled
        foreach($scheduleList as $schedule_list){
            $schedule_hook_name = $schedule_list['hook_name'];
            if ( ! wp_next_scheduled( $schedule_hook_name ) ) {
                $get_schedule_frequency = $wpdb->get_results("SELECT frequency FROM ultimate_cfimporter_pro_scheduled_import WHERE isrun = 0 AND cron_status != 'completed' ");
                if(!empty($get_schedule_frequency)){
                    foreach($get_schedule_frequency as $schedule_frequency){
                        $frequency = $schedule_frequency->frequency;
            
                        $frequency_timing_array = array(
                            '0' => 'smack_one_time',
                            '1' => 'smack_daily',
                            '2' => 'smack_weekly',
                            '3' => 'smack_monthly',
                            '4' => 'smack_every_one_hour',
                            '5' => 'smack_every_thirty_minutes',
                            '6' => 'smack_every_fifteen_minutes',        
                            '7' => 'smack_every_ten_minutes',
                            '8' => 'smack_every_five_minutes',
                            '9' => 'smack_every_two_hrs',
                            '10' => 'smack_every_four_hrs',
                        );
                        wp_schedule_event( time(), $frequency_timing_array[$frequency], $schedule_hook_name );
                    }
                }
            }
        }
    }
}