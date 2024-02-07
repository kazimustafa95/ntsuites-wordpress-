<?php

/**
 * @package Unlimited Elements
 * @author UniteCMS http://unitecms.net
 * @copyright Copyright (c) 2016 UniteCMS
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

defined('UNLIMITED_ELEMENTS_INC') or die('Restricted access');

class UniteCreatorGutenbergIntegrate{

	private static $initialized = false;
	private static $instance = null;

	/**
	 * Create a new instance.
	 */
	private function __construct(){
		//
	}

	/**
	 * Get the class instance.
	 *
	 * @return self
	 */
	public static function getInstance(){

		if(self::$instance === null)
			self::$instance = new self();

		return self::$instance;
	}

	/**
	 * Initialize the integration.
	 *
	 * @return void
	 */
	public function init(){
				
		$shouldInitialize = $this->shouldInitialize();

		if($shouldInitialize === false)
			return;

		$this->registerHooks();

		self::$initialized = true;
	}

	/**
	 * Determine if the integration should be initialized.
	 *
	 * @return bool
	 */
	private function shouldInitialize(){

		if(self::$initialized === true)
			return false;

		if(GlobalsUnlimitedElements::$enableGutenbergSupport === false)
			return false;

		if(function_exists('register_block_type') === false)
			return false;

		return true;
	}

	/**
	 * Register the integration hooks.
	 *
	 * @return void
	 */
	private function registerHooks(){

		UniteProviderFunctionsUC::addFilter('block_categories_all', array($this, 'registerCategories'));
		UniteProviderFunctionsUC::addAction('init', array($this, 'registerBlocks'));
		UniteProviderFunctionsUC::addAction('enqueue_block_editor_assets', array($this, 'enqueueAssets'));
	}

	/**
	 * Register the Gutenberg categories.
	 *
	 * @param array $categories
	 *
	 * @return array
	 */
	public function registerCategories($categories){

		$categories[] = array(
			'slug' => GlobalsUnlimitedElements::PLUGIN_NAME,
			'title' => __('Elementor Widgets', 'unlimited-elements-for-elementor'),
		);

		return $categories;
	}

	/**
	 * Register the Gutenberg blocks.
	 *
	 * @return void
	 */
	public function registerBlocks(){

		$blocks = $this->getBlocks();

		foreach($blocks as $block){
			register_block_type($block['name'], $block);
		}
	}

	/**
	 * Render the Gutenberg block on the frontend.
	 *
	 * @param array $attributes
	 *
	 * @return string
	 */
	public function renderBlock($attributes){

		$data = array(
			'id' => $attributes['_id'],
			'settings' => json_decode($attributes['data'], true),
			'selectors' => true,
		);

		$addonsManager = new UniteCreatorAddons();
		$addonData = $addonsManager->getAddonOutputData($data);

		foreach($addonData['includes'] as $include){
			$type = UniteFunctionsUC::getVal($include, "type");
			$url = UniteFunctionsUC::getVal($include, "url");
			$handle = UniteFunctionsUC::getVal($include, "handle");

			if($type === "css")
				HelperUC::addStyleAbsoluteUrl($url, $handle);
			else
				HelperUC::addScriptAbsoluteUrl($url, $handle);
		}

		return $addonData['html'];
	}

	/**
	 * Enqueue the Gutenberg assets.
	 *
	 * @return void
	 */
	public function enqueueAssets(){
		
		UniteCreatorAdmin::setView('testaddonnew');
		UniteCreatorAdmin::onAddScripts();

		$scriptHandle = 'uc_gutenberg_integrate';
		$scriptUrl = GlobalsUC::$url_provider . 'assets/gutenberg_integrate.js';
		$scriptDeps = array('jquery', 'wp-block-editor', 'wp-blocks', 'wp-components', 'wp-element');

		HelperUC::addScriptAbsoluteUrl($scriptUrl, $scriptHandle, false, $scriptDeps);

		wp_localize_script($scriptHandle, 'g_gutenbergBlocks', $this->getBlocks());
		wp_add_inline_script($scriptHandle, HelperHtmlUC::getGlobalJsOutput(), 'before');
	}

	/**
	 * Get the Gutenberg blocks.
	 *
	 * @return array
	 */
	private function getBlocks(){

		$addonsOrder = '';
		$addonsParams = array('filter_active' => 'active');
		$addonsType = GlobalsUC::ADDON_TYPE_ELEMENTOR;
		$addonsManager = new UniteCreatorAddons();
		$addons = $addonsManager->getArrAddons($addonsOrder, $addonsParams, $addonsType);
		$blocks = array();

		foreach($addons as $addon){
			$blocks[] = array(
				'name' => GlobalsUnlimitedElements::PLUGIN_NAME . '/' . sanitize_title($addon->getTitle()),
				'title' => $addon->getTitle(),
				'description' => $addon->getDescription(),
				'category' => GlobalsUnlimitedElements::PLUGIN_NAME,
				'render_callback' => array($this, 'renderBlock'),
				'attributes' => array(
					'_id' => array(
						'type' => 'string',
						'default' => $addon->getID(),
					),
					'data' => array(
						'type' => 'string',
						'default' => '',
					),
				),
			);
		}

		return $blocks;
	}

}
