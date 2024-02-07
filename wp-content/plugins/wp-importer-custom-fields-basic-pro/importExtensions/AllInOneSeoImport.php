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

class AllInOneSeoImport {
    private static $all_seo_instance = null;

    public static function getInstance() {
		
		if (AllInOneSeoImport::$all_seo_instance == null) {
			AllInOneSeoImport::$all_seo_instance = new AllInOneSeoImport;
			return AllInOneSeoImport::$all_seo_instance;
		}
		return AllInOneSeoImport::$all_seo_instance;
	}
	
    function set_all_seo_values($header_array ,$value_array , $map, $post_id , $type,$mode){
		$post_values = [];
		$helpers_instance = ImportHelpers::getInstance();	
		$post_values = $helpers_instance->get_header_values($map , $header_array , $value_array);
		$this->all_seo_import_function($post_values,$type, $post_id,$mode);
        
    }

    function all_seo_import_function($data_array, $importas,$pID,$mode) {
		$createdFields = array();
		foreach($data_array as $dkey => $dvalue) {
			$createdFields[] = $dkey;
		}
		if ($mode == 'Insert')
        {
            if (isset($data_array['keywords']))
            {
                $custom_array['_aioseop_keywords'] = $data_array['keywords'];
            }
            if (isset($data_array['description']))
            {
                $custom_array['_aioseop_description'] = $data_array['description'];
            }
            if (isset($data_array['title']))
            {
                $custom_array['_aioseop_title'] = $data_array['title'];
            }
            if (isset($data_array['noindex']))
            {
                $custom_array['_aioseop_noindex'] = $data_array['noindex'];
            }
            if (isset($data_array['nofollow']))
            {
                $custom_array['_aioseop_nofollow'] = $data_array['nofollow'];
            }
            if (isset($data_array['custom_link']))
            {
                $custom_array['_aioseop_custom_link'] = $data_array['custom_link'];
            }
            if (isset($data_array['noodp']))
            {
                $custom_array['_aioseop_noodp'] = $data_array['noodp'];
            }
            if (isset($data_array['noydir']))
            {
                $custom_array['_aioseop_noydir'] = $data_array['noydir'];
            }
            if (isset($data_array['titleatr']))
            {
                $custom_array['_aioseop_titleatr'] = $data_array['titleatr'];
            }
            if (isset($data_array['menulabel']))
            {
                $custom_array['_aioseop_menulabel'] = $data_array['menulabel'];
            }
            if (isset($data_array['disable']))
            {
                $custom_array['_aioseop_disable'] = $data_array['disable'];
                if ($data_array['disable'] == 'off')
                {
                    unset($custom_array['_aioseop_disable']);
                }
            }
            if (isset($data_array['disable_analytics']))
            {
                $custom_array['_aioseop_disable_analytics'] = $data_array['disable_analytics'];
                if ($data_array['disable_analytics'] == 'off')
                {
                    unset($custom_array['_aioseop_disable_analytics']);
                }
            }
            if (isset($data_array['og_title']))
            {
                $custom_array['_aioseo_og_title'] = $data_array['og_title'];
            }
            if (isset($data_array['og_description']))
            {
                $custom_array['_aioseo_og_description'] = $data_array['og_description'];
            }
            if (isset($data_array['og_article_section']))
            {
                $custom_array['_aioseo_og_article_section'] = $data_array['og_article_section'];
            }
            if (isset($data_array['og_article_tags']))
            {
                $custom_array['_aioseo_og_article_tags'] = $data_array['og_article_tags'];
            }
            if (isset($data_array['twitter_title']))
            {
                $custom_array['_aioseo_twitter_title'] = $data_array['twitter_title'];
            }
            if (isset($data_array['twitter_description']))
            {
                $custom_array['_aioseo_twitter_description'] = $data_array['twitter_description'];
            }

            if (!empty($custom_array))
            {
                foreach ($custom_array as $custom_key => $custom_value)
                {
                    update_post_meta($pID, $custom_key, $custom_value);
                }
            }
            global $wpdb;
            $aioseo_table_name = $wpdb->prefix . "aioseo_posts";
            $og_title = $data_array['og_title'];
            $og_description = $data_array['og_description'];
            $canonical_url = $data_array['custom_link'];
            $og_image_type = $data_array['og_image_type'];
            $og_video = $data_array['og_video'];
            $og_object_type = $data_array['og_object_type'];
            $og_article_section = isset($data_array['og_article_section'])?$data_array['og_article_section']:'';
            $value['label'] = isset($data_array['og_article_tags'])?$data_array['og_article_tags']:'';
            $name['value'] = isset($data_array['og_article_tags'])?$data_array['og_article_tags']:'';
            $obj_merged = (object)array_merge((array)$value, (array)$name);
            $article_tags = wp_json_encode($obj_merged);
            $og_article_tags = '';
            $og_article_tags .= '[' . $article_tags . ']';
            $twitter_use_og = isset($data_array['twitter_use_og'])?$data_array['twitter_use_og']:'';
            $twitter_card = $data_array['twitter_card'];
            $twitter_image_type = $data_array['twitter_image_type'];
            $twitter_title = $data_array['twitter_title'];
            $twitter_description = $data_array['twitter_description'];
            $robots_default = $data_array['robots_default'];
            $noindex = $data_array['noindex'];
			if($noindex == 'on'){
				$robots_noindex = 1;
			}else{
				$robots_noindex = 0;
			}
            $robots_noarchive = $data_array['robots_noarchive'];
            $robots_nosnippet = $data_array['robots_nosnippet'];
            $nofollow = $data_array['nofollow'];
			if($nofollow == 'on'){
				$robots_nofollow = 1;
			}
			else{
				$robots_nofollow = 0;
			}
            $robots_noimageindex = $data_array['robots_noimageindex'];
            $robots_noodp = $data_array['noodp'];
            $robots_notranslate = $data_array['robots_notranslate'];
            $robots_max_snippet = $data_array['robots_max_snippet'];
            $robots_max_videopreview = $data_array['robots_max_videopreview'];
            $robots_max_imagepreview = $data_array['robots_max_imagepreview'];
            $title = $data_array['aioseo_title'];
            $description = $data_array['aioseo_description'];
            $keyphrases_val['keyphrase'] = $data_array['keyphrases'];
            $keyphras['focus'] = (object)(array)$keyphrases_val;
            $keyphrass = wp_json_encode($keyphras);
            $keyphrases = $keyphrass;
            if ($twitter_image_type == 'custom')
            {
                $twitter_image_custom_fields = isset($data_array['twitter_image_custom_fields'])?$data_array['twitter_image_custom_fields']:'';
            }
            if ($og_image_type == 'custom')
            {
                $og_image_custom_fields = isset($data_array['og_image_custom_fields'])?$data_array['og_image_custom_fields']:'';
            }
            $og_image_custom_fields = isset($og_image_custom_fields)?$og_image_custom_fields:'';
            $twitter_image_custom_fields = isset($twitter_image_custom_fields)?$twitter_image_custom_fields:'';
            $wpdb->get_results("INSERT INTO $aioseo_table_name
				(post_id,og_title ,og_description,canonical_url,og_image_type,og_video,og_object_type,og_article_section,
				twitter_use_og,twitter_card,twitter_image_type,twitter_title,twitter_description,robots_default,
				robots_noindex,robots_noarchive,robots_nosnippet,robots_nofollow,robots_noimageindex,
				robots_noodp,robots_notranslate,robots_max_snippet,robots_max_videopreview,robots_max_imagepreview,og_article_tags
				,title,keyphrases,description,og_image_custom_fields,twitter_image_custom_fields)
				values('$pID','$og_title','$og_description','$canonical_url','$og_image_type','$og_video','$og_object_type','$og_article_section',
					'$twitter_use_og','$twitter_card','$twitter_image_type','$twitter_title','$twitter_description',
					'$robots_default','$robots_noindex','$robots_noarchive','$robots_nosnippet','$robots_nofollow',
					'$robots_noimageindex','$robots_noodp','$robots_notranslate','$robots_max_snippet',
					'$robots_max_videopreview','$robots_max_imagepreview','$og_article_tags','$title','$keyphrases','$description'
					,'$og_image_custom_fields','$twitter_image_custom_fields')");
        }

        if ($mode == 'Update')
        {
            global $wpdb;
            if (isset($data_array['og_title']))
            {
                $custom_value['og_title'] = $data_array['og_title'];
            }
            if (isset($data_array['og_description']))
            {
                $custom_value['og_description'] = $data_array['og_description'];
            }
            if (isset($data_array['custom_link']))
            {
                $custom_value['canonical_url'] = $data_array['custom_link'];
            }
            if (isset($data_array['og_image_type']))
            {
                $custom_value['og_image_type'] = $data_array['og_image_type'];
            }
            if (isset($data_array['og_video']))
            {
                $custom_value['og_video'] = $data_array['og_video'];
            }
            if (isset($data_array['og_object_type']))
            {
                $custom_value['og_object_type'] = $data_array['og_object_type'];
            }
            if (isset($data_array['og_article_section']))
            {
                $custom_value['og_article_section'] = $data_array['og_article_section'];
            }
            if (isset($data_array['og_article_tags']))
            {
                $value['label'] = $data_array['og_article_tags'];
                $name['value'] = $data_array['og_article_tags'];
                $obj_merged = (object)array_merge((array)$value, (array)$name);
                $article_tags = wp_json_encode($obj_merged);
                $og_article_tag .= '[' . $article_tags . ']';
                $custom_value['og_article_tags'] = $og_article_tag;
            }
            if (isset($data_array['twitter_use_og']))
            {
                $custom_value['twitter_use_og'] = $data_array['twitter_use_og'];
            }
            if (isset($data_array['twitter_card']))
            {
                $custom_value['twitter_card'] = $data_array['twitter_card'];
            }
            if (isset($data_array['twitter_image_type']))
            {
                $custom_value['twitter_image_type'] = $data_array['twitter_image_type'];
            }
            if (isset($data_array['twitter_title']))
            {
                $custom_value['twitter_title'] = $data_array['twitter_title'];
            }
            if (isset($data_array['twitter_description']))
            {
                $custom_value['twitter_description'] = $data_array['twitter_description'];
            }
            if (isset($data_array['robots_default']))
            {
                $custom_value['robots_default'] = $data_array['robots_default'];
            }
            if (isset($data_array['noindex']))
            {
                $custom_value['robots_noindex'] = $data_array['noindex'];
            }
            if (isset($data_array['robots_noarchive']))
            {
                $custom_value['robots_noarchive'] = $data_array['robots_noarchive'];
            }
            if (isset($data_array['robots_nosnippet']))
            {
                $custom_value['robots_nosnippet'] = $data_array['robots_nosnippet'];
            }
            if (isset($data_array['nofollow']))
            {
                $custom_value['robots_nofollow'] = $data_array['nofollow'];
            }
            if (isset($data_array['robots_noimageindex']))
            {
                $custom_value['robots_noimageindex'] = $data_array['robots_noimageindex'];
            }
            if (isset($data_array['noodp']))
            {
                $custom_value['robots_noodp'] = $data_array['noodp'];
            }
            if (isset($data_array['robots_notranslate']))
            {
                $custom_value['robots_notranslate'] = $data_array['robots_notranslate'];
            }
            if (isset($data_array['robots_max_snippet']))
            {
                $custom_value['robots_max_snippet'] = $data_array['robots_max_snippet'];
            }
            if (isset($data_array['robots_max_videopreview']))
            {
                $custom_value['robots_max_videopreview'] = $data_array['robots_max_videopreview'];
            }
            if (isset($data_array['robots_max_imagepreview']))
            {
                $custom_value['robots_max_imagepreview'] = $data_array['robots_max_imagepreview'];
            }
            if (isset($data_array['aioseo_title']))
            {
                $custom_value['title'] = $data_array['aioseo_title'];
            }
            if (isset($data_array['aioseo_description']))
            {
                $custom_value['description'] = $data_array['aioseo_description'];
            }
            if (isset($data_array['keyphrases']))
            {
                $keyphrases_val['keyphrase'] = $data_array['keyphrases'];
                $keyphras['focus'] = (object)(array)$keyphrases_val;
                $keyphrass = wp_json_encode($keyphras);
                $custom_value['keyphrases'] = $keyphrass;
            }
            if (isset($data_array['twitter_image_custom_fields']))
            {
                if ($custom_value['twitter_image_type'] == 'custom')
                {
                    $custom_value['twitter_image_custom_fields'] = $data_array['twitter_image_custom_fields'];
                }
            }
            if (isset($data_array['og_image_custom_fields']))
            {
                if ($custom_value['og_image_type'] == 'custom')
                {
                    $custom_value['og_image_custom_fields'] = $data_array['og_image_custom_fields'];
                }
            }
            if (!empty($custom_value))
            {
                foreach ($custom_value as $custom_key => $custom_val)
                {
                    $sql = $wpdb->prepare("UPDATE {$wpdb->prefix}aioseo_posts SET $custom_key = '$custom_val' WHERE post_id = %d;", $pID);
                    $wpdb->query($sql);

                }
            }
        }
		return $createdFields;
	}
    
}