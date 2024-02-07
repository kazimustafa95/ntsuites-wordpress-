<?php

/**
 * @package Unlimited Elements
 * @author unlimited-elements.com
 * @copyright (C) 2021 Unlimited Elements, All Rights Reserved.
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('UNLIMITED_ELEMENTS_INC') or die('Restricted access');

class UniteCreatorAPIIntegrations{

	const FORMAT_DATETIME = "d.m.Y H:i";
	const FORMAT_MYSQL_DATETIME = "Y-m-d H:i:s";

	const TYPE_GOOGLE_EVENTS = "google_events";
	const TYPE_GOOGLE_REVIEWS = "google_reviews";
	const TYPE_GOOGLE_SHEETS = "google_sheets";
	const TYPE_YOUTUBE_PLAYLIST = "youtube_playlist";

	const GOOGLE_EVENTS_FIELD_CALENDAR_ID = "google_events:calendar_id";
	const GOOGLE_EVENTS_FIELD_RANGE = "google_events:range";
	const GOOGLE_EVENTS_FIELD_ORDER = "google_events:order";
	const GOOGLE_EVENTS_FIELD_LIMIT = "google_events:limit";
	const GOOGLE_EVENTS_FIELD_CACHE_TIME = "google_events:cache_time";
	const GOOGLE_EVENTS_DEFAULT_LIMIT = 250;
	const GOOGLE_EVENTS_DEFAULT_CACHE_TIME = 10;
	const GOOGLE_EVENTS_RANGE_UPCOMING = "upcoming";
	const GOOGLE_EVENTS_RANGE_TODAY = "today";
	const GOOGLE_EVENTS_RANGE_TOMORROW = "tomorrow";
	const GOOGLE_EVENTS_RANGE_WEEK = "week";
	const GOOGLE_EVENTS_RANGE_MONTH = "month";
	const GOOGLE_EVENTS_ORDER_DATE_ASC = "date:asc";
	const GOOGLE_EVENTS_ORDER_DATE_DESC = "date:desc";

	const GOOGLE_REVIEWS_FIELD_PLACE_ID = "google_reviews:place_id";
	const GOOGLE_REVIEWS_FIELD_CACHE_TIME = "google_reviews:cache_time";
	const GOOGLE_REVIEWS_DEFAULT_CACHE_TIME = 10;

	const GOOGLE_SHEETS_FIELD_ID = "google_sheets:id";
	const GOOGLE_SHEETS_FIELD_SHEET_ID = "google_sheets:sheet_id";
	const GOOGLE_SHEETS_FIELD_CACHE_TIME = "google_sheets:cache_time";
	const GOOGLE_SHEETS_DEFAULT_CACHE_TIME = 10;

	const YOUTUBE_PLAYLIST_FIELD_ID = "youtube_playlist:id";
	const YOUTUBE_PLAYLIST_FIELD_ORDER = "youtube_playlist:order";
	const YOUTUBE_PLAYLIST_FIELD_LIMIT = "youtube_playlist:limit";
	const YOUTUBE_PLAYLIST_FIELD_CACHE_TIME = "youtube_playlist:cache_time";
	const YOUTUBE_PLAYLIST_DEFAULT_LIMIT = 5;
	const YOUTUBE_PLAYLIST_DEFAULT_CACHE_TIME = 10;
	const YOUTUBE_PLAYLIST_ORDER_DEFAULT = "default";
	const YOUTUBE_PLAYLIST_ORDER_DATE_ADDED_ASC = "date_added:asc";
	const YOUTUBE_PLAYLIST_ORDER_DATE_ADDED_DESC = "date_added:desc";
	const YOUTUBE_PLAYLIST_ORDER_DATE_ADDED_RANDOM = "date_added:random";
	const YOUTUBE_PLAYLIST_ORDER_DATE_PUBLISHED_ASC = "date_published:asc";
	const YOUTUBE_PLAYLIST_ORDER_DATE_PUBLISHED_DESC = "date_published:desc";
	const YOUTUBE_PLAYLIST_ORDER_DATE_PUBLISHED_RANDOM = "date_published:random";

	const ORDER_FIELD = "__order_field";
	const ORDER_DIRECTION_ASC = "asc";
	const ORDER_DIRECTION_DESC = "desc";
	const ORDER_DIRECTION_RANDOM = "random";

	private static $instance = null;

	private $params = array();

	/**
	 * create a new instance
	 */
	private function __construct(){

		$this->init();
	}

	/**
	 * get the class instance
	 */
	public static function getInstance(){

		if(self::$instance === null)
			self::$instance = new self();

		return self::$instance;
	}

	/**
	 * get the api types
	 */
	public function getTypes(){

		$types = array();
		$types[self::TYPE_GOOGLE_EVENTS] = "Google Events";
		$types[self::TYPE_GOOGLE_REVIEWS] = "Google Reviews";
		$types[self::TYPE_GOOGLE_SHEETS] = "Google Sheets";
		$types[self::TYPE_YOUTUBE_PLAYLIST] = "Youtube Playlist";

		return $types;
	}

	/**
	 * get the api data
	 */
	public function getData($type, $params){

		$this->params = $params;

		$data = array();

		switch($type){
			case self::TYPE_GOOGLE_EVENTS:
				$data = $this->getGoogleEventsData();
			break;
			case self::TYPE_GOOGLE_REVIEWS:
				$data = $this->getGoogleReviewsData();
			break;
			case self::TYPE_GOOGLE_SHEETS:
				$data = $this->getGoogleSheetsData();
			break;
			case self::TYPE_YOUTUBE_PLAYLIST:
				$data = $this->getYoutubePlaylistData();
			break;
		}

		return $data;
	}

	/**
	 * init the api integrations
	 */
	private function init(){

		$this->include();
	}

	/**
	 * include integration files
	 */
	private function include(){

		$objServices = new UniteServicesUC();
		$objServices->includeGoogleAPI();
	}

	/**
	 * get the param value
	 */
	private function getParam($key, $fallback = null){

		$value = empty($this->params[$key]) ? $fallback : $this->params[$key];

		return $value;
	}

	/**
	 * get the param value, otherwise throw an exception
	 */
	private function getRequiredParam($key, $label = null){

		$value = $this->getParam($key);

		if(empty($value))
			throw new Exception(($label ?: $key) . " is required.");

		return $value;
	}

	/**
	 * get the cache time param
	 */
	private function getCacheTimeParam($key, $default = 10){

		$time = $this->getParam($key, $default);
		$time = intval($time);
		$time = max($time, 1); // minimum is 1 minute
		$time *= 60; // convert to seconds

		return $time;
	}

	/**
	 * get google api key
	 */
	private function getGoogleApiKey(){

		$key = $this->getRequiredParam("google_api_key", "Google API key");

		return $key;
	}

	/**
	 * get google events data
	 */
	private function getGoogleEventsData(){

		$data = array();

		$calendarId = $this->getRequiredParam(self::GOOGLE_EVENTS_FIELD_CALENDAR_ID, "Calendar ID");
		$eventsRange = $this->getParam(self::GOOGLE_EVENTS_FIELD_RANGE);
		$eventsRange = $this->getGoogleEventsDatesRange($eventsRange);
		$eventsOrder = $this->getParam(self::GOOGLE_EVENTS_FIELD_ORDER);
		$eventsLimit = $this->getParam(self::GOOGLE_EVENTS_FIELD_LIMIT, self::GOOGLE_EVENTS_DEFAULT_LIMIT);
		$eventsLimit = intval($eventsLimit);
		$cacheTime = $this->getCacheTimeParam(self::GOOGLE_EVENTS_FIELD_CACHE_TIME, self::GOOGLE_EVENTS_DEFAULT_CACHE_TIME);

		$orderFieldMap = array(
			self::GOOGLE_EVENTS_ORDER_DATE_ASC => "date",
			self::GOOGLE_EVENTS_ORDER_DATE_DESC => "date",
		);

		$orderDirectionMap = array(
			self::GOOGLE_EVENTS_ORDER_DATE_ASC => self::ORDER_DIRECTION_ASC,
			self::GOOGLE_EVENTS_ORDER_DATE_DESC => self::ORDER_DIRECTION_DESC,
		);

		$orderField = isset($orderFieldMap[$eventsOrder]) ? $orderFieldMap[$eventsOrder] : null;
		$orderDirection = isset($orderDirectionMap[$eventsOrder]) ? $orderDirectionMap[$eventsOrder] : null;

		$calendarService = new UEGoogleAPICalendarService($this->getGoogleApiKey());
		$calendarService->setCacheTime($cacheTime);

		$eventsParams = array(
			"singleEvents" => "true",
			"orderBy" => "startTime",
			"maxResults" => $eventsLimit,
		);

		if(isset($eventsRange["start"]) === true)
			$eventsParams["timeMin"] = $eventsRange["start"];

		if(isset($eventsRange["end"]) === true)
			$eventsParams["timeMax"] = $eventsRange["end"];

		$events = $calendarService->getEvents($calendarId, $eventsParams);

		foreach($events as $event){
			$orderValue = ($orderField === "date")
				? $event->getStartDate(self::FORMAT_MYSQL_DATETIME)
				: null;

			$data[] = array(
				"id" => $event->getId(),
				"start_date" => $event->getStartDate(self::FORMAT_DATETIME),
				"end_date" => $event->getEndDate(self::FORMAT_DATETIME),
				"title" => $event->getTitle(),
				"description" => $event->getDescription(true),
				"location" => $event->getLocation(),
				"link" => $event->getUrl(),
				self::ORDER_FIELD => $orderValue,
			);
		}

		$data = $this->sortData($data, $orderDirection);

		return $data;
	}

	/**
	 * get google events dates range
	 */
	private function getGoogleEventsDatesRange($key){

		$currentTime = current_time("timestamp");
		$startTime = null;
		$endTime = null;

		switch($key){
			case self::GOOGLE_EVENTS_RANGE_UPCOMING:
				$startTime = strtotime("now", $currentTime);
			break;
			case self::GOOGLE_EVENTS_RANGE_TODAY:
				$startTime = strtotime("today", $currentTime);
				$endTime = strtotime("tomorrow", $startTime);
			break;
			case self::GOOGLE_EVENTS_RANGE_TOMORROW:
				$startTime = strtotime("tomorrow", $currentTime);
				$endTime = strtotime("tomorrow", $startTime);
			break;
			case self::GOOGLE_EVENTS_RANGE_WEEK:
				$startTime = strtotime("this week midnight", $currentTime);
				$endTime = strtotime("next week midnight", $currentTime);
			break;
			case self::GOOGLE_EVENTS_RANGE_MONTH:
				$startTime = strtotime("first day of this month midnight", $currentTime);
				$endTime = strtotime("first day of next month midnight", $currentTime);
			break;
		}

		$range = array(
			"start" => $startTime ? date("c", $startTime) : null,
			"end" => $endTime ? date("c", $endTime) : null,
		);

		return $range;
	}

	/**
	 * get google reviews data
	 */
	private function getGoogleReviewsData(){

		$data = array();

		$placeId = $this->getRequiredParam(self::GOOGLE_REVIEWS_FIELD_PLACE_ID, "Place ID");
		$cacheTime = $this->getCacheTimeParam(self::GOOGLE_REVIEWS_FIELD_CACHE_TIME, self::GOOGLE_REVIEWS_DEFAULT_CACHE_TIME);

		$placesService = new UEGoogleAPIPlacesService($this->getGoogleApiKey());
		$placesService->setCacheTime($cacheTime);

		$place = $placesService->getDetails($placeId, array(
			"fields" => "reviews",
			"reviews_sort" => "newest",
		));

		foreach($place->getReviews() as $review){
			$data[] = array(
				"id" => $review->getId(),
				"date" => $review->getDate(self::FORMAT_DATETIME),
				"text" => $review->getText(true),
				"rating" => $review->getRating(),
				"author_name" => $review->getAuthorName(),
				"author_photo" => $review->getAuthorPhotoUrl(),
			);
		}

		return $data;
	}

	/**
	 * get google sheets data
	 */
	private function getGoogleSheetsData(){

		$data = array();

		$spreadsheetId = $this->getRequiredParam(self::GOOGLE_SHEETS_FIELD_ID, "Spreadsheet ID");
		$sheetId = $this->getParam(self::GOOGLE_SHEETS_FIELD_SHEET_ID, 0);
		$sheetId = intval($sheetId);
		$cacheTime = $this->getCacheTimeParam(self::GOOGLE_SHEETS_FIELD_CACHE_TIME, self::GOOGLE_SHEETS_DEFAULT_CACHE_TIME);

		$sheetsService = new UEGoogleAPISheetsService($this->getGoogleApiKey());
		$sheetsService->setCacheTime($cacheTime);

		// get sheet title for the range
		$spreadsheet = $sheetsService->getSpreadsheet($spreadsheetId);
		$range = null;

		foreach($spreadsheet->getSheets() as $sheet){
			if($sheet->getId() === $sheetId){
				$range = $sheet->getTitle();

				break;
			}
		}

		// get spreadsheet values
		$spreadsheet = $sheetsService->getSpreadsheetValues($spreadsheetId, $range);
		$values = $spreadsheet->getValues();

		$headers = array_shift($values); // extract first row as headers

		foreach($values as $rowIndex => $row){
			$attributes = array("id" => $rowIndex + 1);

			foreach($headers as $columnIndex => $header){
				if(empty($row[$columnIndex]))
					continue 2; // continue both loops

				$attributes[$header] = $row[$columnIndex];
			}

			$data[] = $attributes;
		}

		return $data;
	}

	/**
	 * get youtube playlist data
	 */
	private function getYoutubePlaylistData(){

		$data = array();

		$playlistId = $this->getRequiredParam(self::YOUTUBE_PLAYLIST_FIELD_ID, "Playlist ID");
		$itemsOrder = $this->getParam(self::YOUTUBE_PLAYLIST_FIELD_ORDER);
		$itemsLimit = $this->getParam(self::YOUTUBE_PLAYLIST_FIELD_LIMIT, self::YOUTUBE_PLAYLIST_DEFAULT_LIMIT);
		$itemsLimit = intval($itemsLimit);
		$cacheTime = $this->getCacheTimeParam(self::YOUTUBE_PLAYLIST_FIELD_CACHE_TIME, self::YOUTUBE_PLAYLIST_DEFAULT_CACHE_TIME);

		$orderFieldMap = array(
			self::YOUTUBE_PLAYLIST_ORDER_DATE_ADDED_ASC => "date",
			self::YOUTUBE_PLAYLIST_ORDER_DATE_ADDED_DESC => "date",
			self::YOUTUBE_PLAYLIST_ORDER_DATE_ADDED_RANDOM => "date",
			self::YOUTUBE_PLAYLIST_ORDER_DATE_PUBLISHED_ASC => "video_date",
			self::YOUTUBE_PLAYLIST_ORDER_DATE_PUBLISHED_DESC => "video_date",
			self::YOUTUBE_PLAYLIST_ORDER_DATE_PUBLISHED_RANDOM => "video_date",
		);

		$orderDirectionMap = array(
			self::YOUTUBE_PLAYLIST_ORDER_DATE_ADDED_ASC => self::ORDER_DIRECTION_ASC,
			self::YOUTUBE_PLAYLIST_ORDER_DATE_ADDED_DESC => self::ORDER_DIRECTION_DESC,
			self::YOUTUBE_PLAYLIST_ORDER_DATE_ADDED_RANDOM => self::ORDER_DIRECTION_RANDOM,
			self::YOUTUBE_PLAYLIST_ORDER_DATE_PUBLISHED_ASC => self::ORDER_DIRECTION_ASC,
			self::YOUTUBE_PLAYLIST_ORDER_DATE_PUBLISHED_DESC => self::ORDER_DIRECTION_DESC,
			self::YOUTUBE_PLAYLIST_ORDER_DATE_PUBLISHED_RANDOM => self::ORDER_DIRECTION_RANDOM,
		);

		$orderField = isset($orderFieldMap[$itemsOrder]) ? $orderFieldMap[$itemsOrder] : null;
		$orderDirection = isset($orderDirectionMap[$itemsOrder]) ? $orderDirectionMap[$itemsOrder] : null;

		$youtubeService = new UEGoogleAPIYouTubeService($this->getGoogleApiKey());
		$youtubeService->setCacheTime($cacheTime);

		$items = $youtubeService->getPlaylistItems($playlistId, array("maxResults" => $itemsLimit));

		foreach($items as $item){
			$orderValue = ($orderField === "date")
				? $item->getDate(self::FORMAT_MYSQL_DATETIME)
				: $item->getVideoDate(self::FORMAT_MYSQL_DATETIME);

			$data[] = array(
				"id" => $item->getId(),
				"date" => $item->getDate(self::FORMAT_DATETIME),
				"title" => $item->getTitle(),
				"description" => $item->getDescription(true),
				"image" => $item->getImageUrl(UEGoogleAPIPlaylistItem::IMAGE_SIZE_MAX),
				"video_id" => $item->getVideoId(),
				"video_date" => $item->getVideoDate(self::FORMAT_DATETIME),
				"video_link" => $item->getVideoUrl(),
				self::ORDER_FIELD => $orderValue,
			);
		}

		$data = $this->sortData($data, $orderDirection);

		return $data;
	}

	/**
	 * sort the data
	 */
	private function sortData($data, $direction){

		$field = self::ORDER_FIELD;

		usort($data, function($a, $b) use ($field, $direction){

			if(isset($a[$field]) === false || isset($b[$field]) === false)
				return 0;

			if($a[$field] == $b[$field])
				return 0;

			switch($direction){
				case self::ORDER_DIRECTION_RANDOM:
					$results = array(rand(-1, 1), rand(-1, 1));
				break;
				case self::ORDER_DIRECTION_DESC:
					$results = array(1, -1);
				break;
				default: // asc
					$results = array(-1, 1);
				break;
			}

			if(is_numeric($a[$field]) && is_numeric($b[$field]))
				return ($a[$field] < $b[$field]) ? $results[0] : $results[1];

			return (strcmp($a[$field], $b[$field]) <= 0) ? $results[0] : $results[1];
		});

		foreach($data as &$values){
			unset($values[$field]);
		}

		return $data;
	}

}
