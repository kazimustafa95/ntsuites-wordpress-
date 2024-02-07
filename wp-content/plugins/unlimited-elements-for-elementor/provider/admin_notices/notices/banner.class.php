<?php

/**
 * @package Unlimited Elements
 * @author UniteCMS http://unitecms.net
 * @copyright Copyright (c) 2016 UniteCMS
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

defined('UNLIMITED_ELEMENTS_INC') or die('Restricted access');

class UCAdminNoticeBanner extends UCAdminNoticeAbstract{

	/**
	 * get the notice identifier
	 */
	public function getId(){

		return 'birthday-sale-23-banner3';
	}

	/**
	 * get the notice html
	 */
	public function getHtml(){
		
		$linkUrl = 'https://unlimited-elements.com/pricing/';
		$linkTarget = '_blank';
		$imageUrl = GlobalsUC::$urlPluginImages . 'birthday_sale_banner1.png'; // 'https://placehold.co/1280x320';
		
		$builder = $this->createBannerBuilder();
		$builder->dismissible();
		$builder->theme(UCAdminNoticeBannerBuilder::THEME_DARK);
		$builder->link($linkUrl, $linkTarget);
		$builder->image($imageUrl);

		$html = $builder->build();

		return $html;
	}

	/**
	 * initialize the notice
	 */
	protected function init(){
		
		$this->freeOnly();		
		$this->setLocation(self::LOCATION_EVERYWHERE);
		$this->setDuration(720); // 30 days in hours
	}

}
