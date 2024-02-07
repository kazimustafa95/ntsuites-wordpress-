<?php

/******************************************************************************************
 * Copyright (C) Smackcoders. - All Rights Reserved under Smackcoders Proprietary License
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/

namespace Smackcoders\CFCSV;

if (!defined('ABSPATH'))
	exit; // Exit if accessed directly

class LearnPressImport
{
	private static $learnpress_instance = null;

	public static function getInstance()
	{
		if (LearnPressImport::$learnpress_instance == null) {
			LearnPressImport::$learnpress_instance = new LearnPressImport;
			return LearnPressImport::$learnpress_instance;
		}
		return LearnPressImport::$learnpress_instance;
    } 
    public function set_learnpress_values($header_array, $value_array, $map, $post_id, $type, $mode){
        $post_values = [];
		$helpers_instance = ImportHelpers::getInstance();
		$post_values = $helpers_instance->get_header_values($map, $header_array, $value_array);
		$this->learnpress_values_import($post_values, $post_id, $type, $mode);
    }
    public function learnpress_values_import($post_values, $post_id, $type, $mode){
        global $wpdb;	
		if($type == 'lp_course'){
            if(isset($post_values['curriculum_name'])){

                $get_curriculum_names = explode('|', $post_values['curriculum_name']);

                if(isset($post_values['curriculum_description'])){
                    $get_curriculum_description = explode('|', $post_values['curriculum_description']);
                }

                if(isset($post_values['lesson_name'])){
                    $get_lesson_names = explode('|', $post_values['lesson_name']);
                }
                if(isset($post_values['lesson_description'])){
                    $get_lesson_description = explode('|', $post_values['lesson_description']);
                }
                if(isset($post_values['quiz_name'])){
                    $get_quiz_names = explode('|', $post_values['quiz_name']);
                }
                if(isset($post_values['quiz_description'])){
                    $get_quiz_description = explode('|', $post_values['quiz_description']);
                }
                $temp = 0;
                $get_existing_curriculums_ids = $wpdb->get_results("SELECT section_id FROM {$wpdb->prefix}learnpress_sections WHERE section_course_id = $post_id ", ARRAY_A); 
                $get_existing_curriculums_id = array_column($get_existing_curriculums_ids, 'section_id');
                $get_existing_curriculums_count = count($get_existing_curriculums_id);
                foreach($get_curriculum_names as $curriculum_name){
                    $curriculums_description = isset($get_curriculum_description[$temp]) ? $get_curriculum_description[$temp] : '';
    
                    if($mode == 'Insert'){
                        $wpdb->insert( 
                            "{$wpdb->prefix}learnpress_sections", 
                            array("section_name" => $curriculum_name, "section_course_id" => $post_id, "section_description" => $curriculums_description),
                            array('%s', '%d', '%s')
                        );
                        $inserted_section_id = $wpdb->insert_id;
                    }
                    if($mode == 'Update'){
                        if($temp < $get_existing_curriculums_count){
                            $curriculumname = trim($curriculum_name);
                            $check_assigned_to_course_id =$wpdb->get_results("SELECT * FROM {$wpdb->prefix}learnpress_sections WHERE section_name ='$curriculumname'");
                            if(empty($check_assigned_to_course_id)){
                                $wpdb->update( 
                                    $wpdb->prefix.'learnpress_sections', 
                                    array('section_name' => $curriculum_name,'section_description' => $curriculums_description),
                                    array( 'section_id' => $get_existing_curriculums_id[$temp] )
                                );
                                $inserted_section_id = $get_existing_curriculums_id[$temp];
                            }
                            else{
                                $inserted_section_id = $check_assigned_to_course_id[0]->section_id;
                            }
                        }
                        else{
                            $curriculumname = trim($curriculum_name);
                            $check_assigned_to_course_id =$wpdb->get_results("SELECT * FROM {$wpdb->prefix}learnpress_sections WHERE section_name ='$curriculumname'");
                            if(empty($check_assigned_to_course_id)){
                                $wpdb->insert( 
                                    "{$wpdb->prefix}learnpress_sections", 
                                    array("section_name" => $curriculum_name, "section_course_id" => $post_id, "section_description" => $curriculums_description),
                                    array('%s', '%d', '%s')
                                );
                                $inserted_section_id = $wpdb->insert_id;
                            }
                            else{
                                $inserted_section_id = $check_assigned_to_course_id[0]->section_id;
                            }
                        }
                    }
                    if(isset($post_values['lesson_name'])){
                        $individual_lesson_names = explode(',', $get_lesson_names[$temp]);
                        
                        if(isset($post_values['lesson_description'])){
                            $indivdual_lesson_description = explode(',', $get_lesson_description[$temp]);
                        }
                       
                        $get_all_lesson_names = $wpdb->get_results("SELECT post_title FROM {$wpdb->prefix}posts WHERE post_type = 'lp_lesson' AND post_status = 'publish' ", ARRAY_A);
                        $all_lesson_names = array_column($get_all_lesson_names, 'post_title');

                        $i = 0;
                        foreach($individual_lesson_names as $lesson_name){
                            $lesson_names = trim($lesson_name);
                            if($mode == 'Insert'){
                                if(in_array($lesson_names, $all_lesson_names)){
                                    $lesson_post_id = $wpdb->get_var("SELECT ID from {$wpdb->prefix}posts WHERE post_title = '$lesson_names' and post_type = 'lp_lesson' ");              
                                    $check_assigned_to_course = $wpdb->get_var("SELECT section_id FROM {$wpdb->prefix}learnpress_section_items WHERE item_id = $lesson_post_id AND item_type = 'lp_lesson' ");
                                    if(empty($check_assigned_to_course)){
                                        LearnPressImport::$learnpress_instance->insert_lesson_details($inserted_section_id, $lesson_post_id, $post_values, $mode);
                                    }

                                }else{
                                    $lessons_description = isset($indivdual_lesson_description[$i]) ? $indivdual_lesson_description[$i] : '';
                                    $lesson_post_id = LearnPressImport::$learnpress_instance->insert_post($lesson_names, $lessons_description, 'lp_lesson');
                                    LearnPressImport::$learnpress_instance->insert_lesson_details($inserted_section_id, $lesson_post_id, $post_values, $mode);
                                }   
                            }
                            if($mode == 'Update'){        
                                if(in_array($lesson_names, $all_lesson_names)){  
                                    $lesson_post_id = $wpdb->get_var("SELECT ID from {$wpdb->prefix}posts WHERE post_title = '$lesson_names' and post_type = 'lp_lesson' ");   
                                    $check_assigned_to_course = $wpdb->get_var("SELECT section_id FROM {$wpdb->prefix}learnpress_section_items WHERE item_id = $lesson_post_id AND item_type = 'lp_lesson' ");
                                    if(empty($check_assigned_to_course)){
                                        LearnPressImport::$learnpress_instance->insert_lesson_details($inserted_section_id, $lesson_post_id, $post_values, $mode);
                                    }
                                }else{
                                    $lessons_description = isset($indivdual_lesson_description[$i]) ? $indivdual_lesson_description[$i] : '';
                                    $lesson_post_id = LearnPressImport::$learnpress_instance->insert_post($lesson_names, $lessons_description, 'lp_lesson');
                                    LearnPressImport::$learnpress_instance->insert_lesson_details($inserted_section_id, $lesson_post_id, $post_values, $mode);
                                }
                            }  
                            $i++;
                        }   
                    }
                    if(isset($post_values['quiz_name'])){
                        $individual_quiz_names = explode(',', $get_quiz_names[$temp]);
                        if(isset($post_values['quiz_description'])){
                            $individual_quiz_description = explode(',', $get_quiz_description[$temp]);
                        }
                        else{
                            $individual_quiz_description = [];
                        }
                        $get_all_quiz_names = $wpdb->get_results("SELECT post_title FROM {$wpdb->prefix}posts WHERE post_type = 'lp_quiz' AND post_status = 'publish' ", ARRAY_A);
                        $all_quiz_names = array_column($get_all_quiz_names, 'post_title');
                        $j = 0;
                        foreach($individual_quiz_names as $quiz_names){
                            if($mode == 'Insert'){
                                if(in_array($quiz_names, $all_quiz_names)){
                                    $quiz_post_id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}posts WHERE post_title = '$quiz_names' and post_type = 'lp_quiz' ");   
                                    $check_assigned_to_course = $wpdb->get_var("SELECT section_id FROM {$wpdb->prefix}learnpress_section_items WHERE item_id = $quiz_post_id AND item_type = 'lp_quiz' ");
                                    if(empty($check_assigned_to_course)){
                                        LearnPressImport::$learnpress_instance->insert_quiz_details($type,$inserted_section_id, $quiz_post_id, $post_values, $mode);
                                    }
                                }else{
                                    $quizs_description = isset($individual_quiz_description[$j]) ? $individual_quiz_description[$j] : '';

                                    $quiz_post_id = LearnPressImport::$learnpress_instance->insert_post($quiz_names, $quizs_description, 'lp_quiz');
                                    LearnPressImport::$learnpress_instance->insert_quiz_details($type,$inserted_section_id, $quiz_post_id, $post_values, $mode);
                                }  
                            }
                            if($mode == 'Update'){
                                $quizs_description = isset($individual_quiz_description[$j]) ? $individual_quiz_description[$j] : '';
                                if(isset($get_existing_quiz_ids[$j])){ 
                                    LearnPressImport::$learnpress_instance->update_post($quiz_names, $quizs_description, 'lp_quiz', $get_existing_quiz_ids[$j]);
                                    LearnPressImport::$learnpress_instance->insert_quiz_details($type,$inserted_section_id, $get_existing_quiz_ids[$j], $post_values, $mode);
                                }
                            }
                            $j++;
                        }
                    }
                    $temp++;
                }   
            }
            if(isset($post_values['_lp_requirements'])){
                $lp_requi = $post_values['_lp_requirements'];
                $lp_require = explode('|',$lp_requi);
                $lp_requirements = $lp_require;
            }
            if(isset($post_values['_lp_target_audiences'])){
                $lp_target = $post_values['_lp_target_audiences'];
                $lp_target = explode('|',$lp_target);
                $lp_target_audiences = $lp_target;
            }
            if(isset($post_values['_lp_key_features'])){
                $lp_key_feature = $post_values['_lp_key_features'];
                $lp_key = explode('|',$lp_key_feature);
                $lp_key_features = $lp_key;
            }
            if(isset($post_values['_lp_faqs'])){
                $lp_faqs = $post_values['_lp_faqs'];
                $lp_faq = explode('|',$lp_faqs);
                $lp_faq_value = array();
                foreach($lp_faq as $lp_faq_values){
                    $lp_faq_value[] = explode(',',$lp_faq_values);

                }
                
            }

            $course_setting_array = [];
            $course_setting_array['_lp_duration'] = isset($post_values['_lp_duration']) ? $post_values['_lp_duration'] : '10 week';
            $course_setting_array['_lp_max_students'] = isset($post_values['_lp_max_students']) ? $post_values['_lp_max_students'] : 1000;
            $course_setting_array['_lp_students'] = isset($post_values['_lp_students']) ? $post_values['_lp_students'] : 0;
            $course_setting_array['_lp_retake_count'] = isset($post_values['_lp_retake_count']) ? $post_values['_lp_retake_count'] : 0;
            $course_setting_array['_lp_featured'] = isset($post_values['_lp_featured']) ? $post_values['_lp_featured'] : 'no';
            $course_setting_array['_lp_external_link_buy_course'] = isset($post_values['_lp_external_link_buy_course']) ? $post_values['_lp_external_link_buy_course'] : '';
            $course_setting_array['_lp_submission'] = isset($post_values['_lp_submission']) ? $post_values['_lp_submission'] : 'yes';
            $course_setting_array['_lp_course_result'] = isset($post_values['_lp_course_result']) ? $post_values['_lp_course_result'] : 'evaluate_lesson';
            $course_setting_array['_lp_passing_condition'] = isset($post_values['_lp_passing_condition']) ? $post_values['_lp_passing_condition'] : 80;
            $course_setting_array['_lp_price'] = isset($post_values['_lp_price']) ? $post_values['_lp_price'] : 0;
            $course_setting_array['_lp_sale_price'] = isset($post_values['_lp_sale_price']) ? $post_values['_lp_sale_price'] : 0;
            $course_setting_array['_lp_required_enroll'] = isset($post_values['_lp_required_enroll']) ? $post_values['_lp_required_enroll'] : 'yes';
            $course_setting_array['_lp_course_author'] = isset($post_values['_lp_course_author']) ? $post_values['_lp_course_author'] : 1;
            $course_setting_array['_lp_block_expire_duration'] = isset($post_values['_lp_block_expire_duration']) ? $post_values['_lp_block_expire_duration'] : '';
            $course_setting_array['_lp_allow_course_repurchase'] = isset($post_values['_lp_allow_course_repurchase']) ? $post_values['_lp_allow_course_repurchase'] : '';
            $course_setting_array['_lp_block_finished'] = isset($post_values['_lp_block_finished']) ? $post_values['_lp_block_finished'] : '';
            $course_setting_array['_lp_course_repurchase_option'] = isset($post_values['_lp_course_repurchase_option']) ? $post_values['_lp_course_repurchase_option'] : '';
            $course_setting_array['_lp_level'] = isset($post_values['_lp_level']) ? $post_values['_lp_level'] : '';
            $course_setting_array['_lp_has_finish'] = isset($post_values['_lp_has_finish']) ? $post_values['_lp_has_finish'] : '';
            $course_setting_array['_lp_featured_review'] = isset($post_values['_lp_featured_review']) ? $post_values['_lp_featured_review'] : '';
            $course_setting_array['_lp_no_required_enroll'] = isset($post_values['_lp_no_required_enroll']) ? $post_values['_lp_no_required_enroll'] : '';
            $course_setting_array['_lp_requirements'] = isset($lp_requirements) ? $lp_requirements : '';
            $course_setting_array['_lp_target_audiences'] = isset($lp_target_audiences) ? $lp_target_audiences : '';
            $course_setting_array['_lp_key_features'] = isset($lp_key_features) ? $lp_key_features : '';
            $course_setting_array['_lp_faqs'] = isset($lp_faq_value) ? $lp_faq_value : '';
            $course_setting_array['_lp_course_status'] = 'publish';
            
            foreach ($course_setting_array as $course_key => $course_value) {
                update_post_meta($post_id, $course_key, $course_value);
            }
		}

        if($type == 'lp_lesson' || $type == 'lp_quiz' || $type == 'lp_question'){
            if(isset($post_values['course_id']) && $post_values['curriculum_name']){
                $course_id = $post_values['course_id'];
                $curriculum_name = $post_values['curriculum_name'];
                $get_section_id = $wpdb->get_var("SELECT section_id FROM {$wpdb->prefix}learnpress_sections WHERE section_course_id = $course_id AND section_name = '$curriculum_name' ");
            }
            else{
                $get_section_id = '';
            }
            if($type == 'lp_lesson'){
                LearnPressImport::$learnpress_instance->insert_lesson_details($get_section_id, $post_id, $post_values, $mode);
            } 
            if($type == 'lp_quiz'){
                LearnPressImport::$learnpress_instance->insert_quiz_details($type,$get_section_id, $post_id, $post_values, $mode);
            }  
            if($type == 'lp_question'){
                LearnPressImport::$learnpress_instance->insert_question_details($type,$get_section_id, $post_id, $post_values, 0, $mode, 'new');
            }
        }
        if($type == 'lp_order'){
            LearnPressImport::$learnpress_instance->insert_order_details($post_id, $post_values, $mode);
        }
	}
	
    public function insert_lesson_details($inserted_section_id, $lesson_post_id, $post_values, $mode){
        global $wpdb;
        if($mode == 'Insert'){
            if(isset($inserted_section_id)){
                LearnPressImport::$learnpress_instance->insert_to_learnpress_section_items($inserted_section_id, $lesson_post_id, 'lp_lesson');
            }
        }
        if($mode == 'Update'){
            $check_for_same_section_id = $wpdb->get_var("SELECT section_id FROM {$wpdb->prefix}learnpress_section_items WHERE section_id = $inserted_section_id and item_id = $lesson_post_id AND item_type = 'lp_lesson' ");
            if(!empty($check_for_same_section_id) && $inserted_section_id == $check_for_same_section_id){
                $get_section_item_id = $wpdb->get_var("SELECT section_item_id FROM {$wpdb->prefix}learnpress_section_items WHERE section_id = $check_for_same_section_id  AND item_type = 'lp_lesson' ");
                
                LearnPressImport::$learnpress_instance->update_to_learnpress_section_items($inserted_section_id, $lesson_post_id, 'lp_lesson', $get_section_item_id );
            }
            else{
                LearnPressImport::$learnpress_instance->insert_to_learnpress_section_items($inserted_section_id, $lesson_post_id, 'lp_lesson');
            }
        }
        if(isset($post_values['_lp_lesson_duration'])){
            update_post_meta($lesson_post_id, '_lp_duration', $post_values['_lp_lesson_duration']);
        }
        if(isset($post_values['_lp_preview'])){
            update_post_meta($lesson_post_id, '_lp_preview', $post_values['_lp_preview']);
        }
    }
    public function insert_quiz_details($type,$inserted_section_id, $quiz_post_id, $post_values, $mode){
        global $wpdb;
             
        if($mode == 'Update'){
            $check_for_same_section_id = $wpdb->get_var("SELECT section_id FROM {$wpdb->prefix}learnpress_section_items WHERE item_id = $quiz_post_id AND item_type = 'lp_quiz' ");
            if(!empty($check_for_same_section_id) && $inserted_section_id == $check_for_same_section_id){
                $get_section_item_id = $wpdb->get_var("SELECT section_item_id FROM {$wpdb->prefix}learnpress_section_items WHERE section_id = $check_for_same_section_id AND item_id = $quiz_post_id AND item_type = 'lp_quiz' ");
                LearnPressImport::$learnpress_instance->update_to_learnpress_section_items($inserted_section_id, $quiz_post_id, 'lp_quiz', $get_section_item_id );
            }
        }
        
        if($mode == 'Insert'){
            if(!empty($inserted_section_id)){
                LearnPressImport::$learnpress_instance->insert_to_learnpress_section_items($inserted_section_id, $quiz_post_id, 'lp_quiz');
            }
        }
                                
        $quiz_meta_array['_lp_duration'] = isset($post_values['_lp_duration']) ? $post_values['_lp_duration'] : '10 minute';
        $quiz_meta_array['_lp_minus_points'] = isset($post_values['_lp_minus_points']) ? $post_values['_lp_minus_points'] : 0;
        $quiz_meta_array['_lp_minus_skip_questions'] = isset($post_values['_lp_minus_skip_questions']) ? $post_values['_lp_minus_skip_questions'] : 'no';
        $quiz_meta_array['_lp_passing_grade'] = isset($post_values['_lp_passing_grade']) ? $post_values['_lp_passing_grade'] : 80;
        $quiz_meta_array['_lp_retake_count'] = isset($post_values['_lp_quiz_retake_count']) ? $post_values['_lp_quiz_retake_count'] : 0;                        
        $quiz_meta_array['_lp_instant_check'] = isset($post_values['_lp_instant_check']) ? $post_values['_lp_instant_check'] : 'no';
        $quiz_meta_array['_lp_negative_marking'] = isset($post_values['_lp_negative_marking']) ? $post_values['_lp_negative_marking'] : 'no';
        $quiz_meta_array['_lp_pagination'] = isset($post_values['_lp_pagination']) ? $post_values['_lp_pagination'] : 1;
        $quiz_meta_array['_lp_show_correct_review'] = isset($post_values['_lp_show_correct_review']) ? $post_values['_lp_show_correct_review'] : 'yes';
        $quiz_meta_array['_lp_review'] = isset($post_values['_lp_review']) ? $post_values['_lp_review'] : 'yes';
        
        foreach ($quiz_meta_array as $quiz_key => $quiz_value) {
            update_post_meta($quiz_post_id, $quiz_key, $quiz_value);
        }

        if(isset($post_values['question_title'])){
            $get_question_titles = explode(',', $post_values['question_title']);
            $get_question_descriptions = explode(',', $post_values['question_description']);
            $get_all_question_titles = $wpdb->get_results("SELECT post_title FROM {$wpdb->prefix}posts WHERE post_type = 'lp_question' AND post_status = 'publish' ", ARRAY_A);
            $all_questions_title = array_column($get_all_question_titles, 'post_title');
            $temp = 0;
            foreach($get_question_titles as $question_titles){
                if($mode == 'Insert'){
                    if(in_array($question_titles, $all_questions_title)){
                        $question_post_id = $wpdb->get_var("SELECT ID from {$wpdb->prefix}posts WHERE post_title = '$question_titles' AND post_type = 'lp_question' AND post_status = 'publish' ");       
                        $post_values['quiz_id'] = $quiz_post_id;
                        $check_assigned_to_quiz = $wpdb->get_var("SELECT quiz_question_id FROM {$wpdb->prefix}learnpress_quiz_questions WHERE question_id = $question_post_id  ");
                        if(empty($check_assigned_to_quiz)){
                            LearnPressImport::$learnpress_instance->insert_question_details($type,$inserted_section_id, $question_post_id, $post_values, $temp, $mode, 'existing');
                        }
                    }else{
                        $question_description = isset($get_question_descriptions[$temp]) ? $get_question_descriptions[$temp] : '';
                        $question_post_id = LearnPressImport::$learnpress_instance->insert_post($question_titles, $question_description, 'lp_question');
                        $post_values['quiz_id'] = $quiz_post_id;
                        LearnPressImport::$learnpress_instance->insert_question_details($type,$inserted_section_id, $question_post_id, $post_values, $temp, $mode, 'existing');
                    }
                }
                if($mode == 'Update'){  
                    if(in_array($question_titles, $all_questions_title)){
                        $question_post_id = $wpdb->get_var("SELECT ID from {$wpdb->prefix}posts WHERE post_title = '$question_titles' AND post_type = 'lp_question' AND post_status = 'publish' ");       
                        $post_values['quiz_id'] = $quiz_post_id;
                        $check_assigned_to_quiz = $wpdb->get_var("SELECT quiz_question_id FROM {$wpdb->prefix}learnpress_quiz_questions WHERE question_id = $question_post_id  ");
                        if(empty($check_assigned_to_quiz)){
                            LearnPressImport::$learnpress_instance->insert_question_details($type,$inserted_section_id, $question_post_id, $post_values, $temp, $mode, 'existing');
                        }
                    }else{
                        $question_description = isset($get_question_descriptions[$temp]) ? $get_question_descriptions[$temp] : '';
                        $question_post_id = LearnPressImport::$learnpress_instance->insert_post($question_titles, $question_description, 'lp_question');
                        $post_values['quiz_id'] = $quiz_post_id;
                        LearnPressImport::$learnpress_instance->insert_question_details($type,$inserted_section_id, $question_post_id, $post_values, $temp, $mode, 'existing');
                    }
                }  
                $temp++;
            }
        }    
    }
    public function insert_question_details($type,$inserted_section_id, $question_post_id, $post_values, $temp, $mode, $condition){
        global $wpdb;

        if(isset($post_values['_lp_mark'])){
            $lp_question_mark = explode(',',$post_values['_lp_mark']);
        }
        if(isset($post_values['_lp_explanation'])){
            $lp_question_explanation = explode(',',$post_values['_lp_explanation']);
        }
        if(isset($post_values['_lp_hint'])){
            $lp_question_hint = explode(',',$post_values['_lp_hint']);
        }
        if(isset($post_values['_lp_type'])){
            $lp_question_type = explode(',',$post_values['_lp_type']);
        }
        $question_meta_array['_lp_mark'] = isset($lp_question_mark[$temp]) ? $lp_question_mark[$temp] : 1;
        $question_meta_array['_lp_explanation'] = isset($lp_question_explanation[$temp]) ? $lp_question_explanation[$temp] : NULL;
        $question_meta_array['_lp_hint'] = isset($lp_question_hint[$temp]) ? $lp_question_hint[$temp] : NULL;
        $question_meta_array['_lp_type'] = isset($lp_question_type[$temp]) ? $lp_question_type[$temp] : 'true_or_false';
        foreach ($question_meta_array as $question_key => $question_value) {
            update_post_meta($question_post_id, $question_key, $question_value);
        }
        if(isset($post_values['quiz_id'])){
            $quiz_id = $post_values['quiz_id'];
            $question_order = 1;
            $get_question_order = $wpdb->get_var("SELECT question_order FROM {$wpdb->prefix}learnpress_quiz_questions WHERE quiz_id = $quiz_id ORDER BY quiz_question_id DESC LIMIT 1");
            if(!empty($get_question_order)){
                $question_order = $get_question_order + 1;
            }
            if($mode == 'Insert'){
                LearnPressImport::$learnpress_instance->insert_to_learnpress_quiz_questions($quiz_id, $question_post_id, $question_order);
            }
            if($mode == 'Update'){
                LearnPressImport::$learnpress_instance->insert_to_learnpress_quiz_questions($quiz_id, $question_post_id, $question_order);
                if($condition == 'new'){
                    if(!empty($inserted_section_id)){
                        $check_for_same_section_id = $wpdb->get_var("SELECT section_id FROM {$wpdb->prefix}learnpress_section_items WHERE item_id = $quiz_id AND item_type = 'lp_quiz' ");
                        if(!empty($check_for_same_section_id) && $inserted_section_id == $check_for_same_section_id){
                            $get_section_item_id = $wpdb->get_var("SELECT section_item_id FROM {$wpdb->prefix}learnpress_section_items WHERE section_id = $check_for_same_section_id AND item_id = $quiz_id AND item_type = 'lp_quiz' "); 
                            LearnPressImport::$learnpress_instance->update_to_learnpress_section_items($inserted_section_id, $quiz_id, 'lp_quiz', $get_section_item_id);
                        }  
                    }
                }
            }
            if(!empty($inserted_section_id)){
                $check_assigned_to_course = $wpdb->get_var("SELECT section_item_id FROM {$wpdb->prefix}learnpress_section_items WHERE section_id = $inserted_section_id AND item_id = $quiz_id AND item_type = 'lp_quiz' ");
                $check_assigned_to_another_course = $wpdb->get_var("SELECT section_id FROM {$wpdb->prefix}learnpress_section_items WHERE item_id = $quiz_id AND item_type = 'lp_quiz' ");
                if(empty($check_assigned_to_course) && empty($check_assigned_to_another_course)){
                    LearnPressImport::$learnpress_instance->insert_to_learnpress_section_items($inserted_section_id, $quiz_id, 'lp_quiz');
                }
            }
        }

        if($type == 'lp_quiz'){
            if(isset($post_values['question_options'])){
                $get_separate_question_options = explode(',', $post_values['question_options']);
                $get_question_titles = explode(',', $post_values['question_title']);
                $i=0;
                $get_separate_options =array();
                foreach($get_separate_question_options as $get_separate_question_option){
                    $key=$get_question_titles[$i];
                    $get_separate_options[$key] = explode('->', $get_separate_question_option); 
                    $i++;
                }  
                foreach($get_separate_options as $option_key=> $option_values){
                   foreach($option_values as $option_value){
                       $post_type_values = $wpdb->get_results("SELECT post_title FROM {$wpdb->prefix}posts WHERE id = $question_post_id ", ARRAY_A);
                        $get_title_options_value = explode('|', $option_value);
                        if($option_key == $post_type_values[0]['post_title']){
                            $wpdb->insert( 
                                "{$wpdb->prefix}learnpress_question_answers", 
                                array("question_id" => $question_post_id, "title" => $get_title_options_value[0], "is_true"=>$get_title_options_value[1]),
                                array('%d', '%s','%s')
                            );
                        } 
                    } 
                }
            }
        }
        else{
            if(isset($post_values['question_options'])){  
                $get_separate_options = explode('->', $post_values['question_options']);
                foreach($get_separate_options as $option_values){
                    $get_title_options = explode('|', $option_values);
                    $wpdb->insert( 
                        "{$wpdb->prefix}learnpress_question_answers", 
                        array("question_id" => $question_post_id, "title" => $get_title_options[0], "is_true"=>$get_title_options[1]),
                        array('%d', '%s','%s')
                    );
                }
            }
        }
    }
    public function insert_order_details($order_id, $post_values, $mode){
        global $wpdb;
        $order_key = strtoupper( uniqid( 'ORDER' ) );
        $order_currency = get_option("_order_currency");
        $order_meta_array = [];
        if(isset($post_values['user_id'])){
            $order_meta_array['_user_id'] = $post_values['user_id'];
        }
        else{
            $order_meta_array['_user_id'] = get_current_user_id();
        }
        $order_meta_array['_order_currency'] = $order_currency;
        $order_meta_array['_order_subtotal'] = isset($post_values['_order_subtotal']) ? $post_values['_order_subtotal'] : '';
        $order_meta_array['_order_total'] = isset($post_values['_order_total']) ? $post_values['_order_total'] : '';
        $order_meta_array['_prices_include_tax'] = 'no';
        $order_meta_array['_payment_method'] = '';
        $order_meta_array['_payment_method_title'] = '';
        $order_meta_array['_order_key'] = $order_key;
        $order_meta_array['_order_version'] = '3.0.0';
        foreach($order_meta_array as $order_key => $order_value){
            update_post_meta($order_id, $order_key, $order_value);
        }
        if(isset($post_values['item_id'])){
            $order_item_ids = explode(',', $post_values['item_id']);
            $order_item_quantity = explode(',', $post_values['item_quantity']);
            $order_item_subtotal = explode(',', $post_values['_item_subtotal']);
            $order_item_total = explode(',', $post_values['_item_total']);
            $temp = 0;
            foreach($order_item_ids as $order_item_id){
                $order_name = $wpdb->get_var("SELECT post_title FROM {$wpdb->prefix}posts WHERE ID = $order_item_id ");
                $wpdb->insert( 
                    "{$wpdb->prefix}learnpress_order_items", 
                    array("order_item_name" => $order_name, "order_id" => $order_id),
                    array('%s', '%d')
                );
                $lp_order_item_id = $wpdb->insert_id;
                $order_item_meta_array = [];
                $order_item_meta_array['_course_id'] = $order_item_id;
                $order_item_meta_array['_quantity'] = $order_item_quantity[$temp];
                $order_item_meta_array['_subtotal'] = $order_item_subtotal[$temp];
                $order_item_meta_array['_total'] = $order_item_total[$temp];
                foreach($order_item_meta_array as $order_item_meta_key => $order_item_meta_value){
                    if(empty($order_item_meta_value)){
                        $order_item_meta_value = 'NULL';
                    }
                    $wpdb->insert( 
                        "{$wpdb->prefix}learnpress_order_itemmeta", 
                        array("learnpress_order_item_id" => $lp_order_item_id, "meta_key" => $order_item_meta_key, "meta_value" => $order_item_meta_value),
                        array('%d', '%s', '%s')
                    );
                }
                $temp++;
            }
        }	
    }

    public function learnpress_orders_import($data_array , $mode , $check,$unikey_value , $unikey_name, $line_number){
        $returnArr = array();	
        global $wpdb;
        $helpers_instance = ImportHelpers::getInstance();
        $core_instance = CoreFieldsImport::getInstance();
        global $core_instance;
        $log_table_name = $wpdb->prefix ."import_detail_log";
        $updated_row_counts = $helpers_instance->update_count($unikey_value,$unikey_name);
        $created_count = $updated_row_counts['created'];
        $updated_count = $updated_row_counts['updated'];
        $skipped_count = $updated_row_counts['skipped'];
        $data_array['post_type'] = 'lp_order';
        if(isset($data_array['order_status'])) {
            $data_array['post_status'] = $data_array['order_status'];
        }
        /* Assign order date */
        if(!isset( $data_array['order_date'] )) {
            $data_array['post_date'] = current_time('Y-m-d H:i:s');
        } else {
            if(strtotime( $data_array['order_date'] )) {
                $data_array['post_date'] = date( 'Y-m-d H:i:s', strtotime( $data_array['order_date'] ) );
            } else {
                $data_array['post_date'] = current_time('Y-m-d H:i:s');
            }
        }
        if ($mode == 'Insert') {	
            $retID = wp_insert_post( $data_array );
            $mode_of_affect = 'Inserted';  
            if(is_wp_error($retID) || $retID == '') {
                $core_instance->detailed_log[$line_number]['Message'] = "Can't insert this LP Order. " . $retID->get_error_message();
                $wpdb->get_results("UPDATE $log_table_name SET skipped = $skipped_count WHERE $unikey_name = '$unikey_value'");
                return array('MODE' => $mode, 'ERROR_MSG' => $retID->get_error_message());
            }
            $core_instance->detailed_log[$line_number]['Message'] = 'Inserted LP Order ID: ' . $retID;
            $wpdb->get_results("UPDATE $log_table_name SET created = $created_count WHERE $unikey_name = '$unikey_value'");

        } 
        else {
            if ($mode == 'Update') {
				if($check == 'ORDER_ID'){
					$orderid = $data_array['ORDER_ID'];
					$post_type = 'lp_order';
					$update_query = "select ID from {$wpdb->prefix}posts where ID = '$orderid' and post_type = '$post_type' order by ID DESC";
					$ID_result = $wpdb->get_results($update_query);
					if (is_array($ID_result) && !empty($ID_result)) {
						$retID = $ID_result[0]->ID;
						$data_array['ID'] = $retID;
						wp_update_post($data_array);
						$mode_of_affect = 'Updated';
						$core_instance->detailed_log[$line_number]['Message'] = 'Updated Order ID: ' . $retID;
						$wpdb->get_results("UPDATE $log_table_name SET updated = $updated_count WHERE $unikey_name = '$unikey_value'");	
					} else{
						//$retID = wp_insert_post( $data_array );
						$mode_of_affect = 'Skipped';
						$core_instance->detailed_log[$line_number]['Message'] = "Skipped " ;
						$wpdb->get_results("UPDATE $log_table_name SET skipped = $skipped_count WHERE $unikey_name = '$unikey_value'");
                        $returnArr['MODE'] = $mode_of_affect;
				        return $returnArr;  
					}
				}else{
                    $mode_of_affect = 'Skipped';
					$core_instance->detailed_log[$line_number]['Message'] = "Skipped " ;
					$wpdb->get_results("UPDATE $log_table_name SET skipped = $skipped_count WHERE $unikey_name = '$unikey_value'");
					$returnArr['MODE'] = $mode_of_affect;
				    return $returnArr;
				}
			} 
		}
        $returnArr['ID'] = $retID;
        $returnArr['MODE'] = $mode_of_affect;
        return $returnArr;
    }
    public function insert_to_learnpress_section_items($inserted_section_id, $post_id, $type){
        global $wpdb;
        $wpdb->insert( 
            "{$wpdb->prefix}learnpress_section_items", 
            array("section_id" => $inserted_section_id, "item_id" => $post_id, "item_type" => "$type"),
            array('%d', '%d', '%s')
        );
    }

    public function update_to_learnpress_section_items($inserted_section_id, $post_id, $type, $get_section_item_id){
        global $wpdb;
        $wpdb->update( 
            $wpdb->prefix.'learnpress_section_items', 
            array('section_id' => $inserted_section_id,'item_id' => $post_id, "item_type" => "$type"),
            array( 'section_item_id' => $get_section_item_id )
        );
    }
    public function insert_to_learnpress_quiz_questions($quiz_id, $question_post_id, $question_order){
        global $wpdb;
        $wpdb->insert( 
            "{$wpdb->prefix}learnpress_quiz_questions", 
            array("quiz_id" => $quiz_id, "question_id" => $question_post_id, "question_order" => $question_order),
            array('%d', '%d', '%d')
        );
    }
    public function insert_post($post_title, $post_content, $post_type){
        $post_array['post_title'] = $post_title;
        $post_array['post_content'] = $post_content;
        $post_array['post_type'] = $post_type;
        $post_array['post_status'] = 'publish';
        $post_id = wp_insert_post($post_array);
        return $post_id;
    }

    public function update_post($post_title, $post_content, $post_type, $id){
        $update_array['post_title'] = $post_title;
        $update_array['post_content'] = $post_content;
        $update_array['post_type'] = $post_type;
        $update_array['post_status'] = 'publish';
        $update_array['ID'] = $id;
        wp_update_post($update_array);
    }
}