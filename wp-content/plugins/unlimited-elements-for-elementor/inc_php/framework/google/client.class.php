<?php

abstract class UEGoogleAPIClient{

	const METHOD_GET = "GET";

	private $apiKey;
	private $cacheTime = 0; // in seconds

	/**
	 * Create a new client instance.
	 *
	 * @param string $apiKey
	 *
	 * @return void
	 */
	public function __construct($apiKey){

		$this->apiKey = $apiKey;
	}

	/**
	 * Set the cache time.
	 *
	 * @param int $seconds
	 *
	 * @return void
	 */
	public function setCacheTime($seconds){

		$this->cacheTime = $seconds;
	}

	/**
	 * Get the base URL for the API.
	 *
	 * @return string
	 */
	abstract protected function getBaseUrl();

	/**
	 * Make a GET request to the API.
	 *
	 * @param $endpoint
	 * @param $params
	 *
	 * @return array
	 */
	protected function get($endpoint, $params = array()){

		return $this->request(self::METHOD_GET, $endpoint, $params);
	}

	/**
	 * Make a request to the API.
	 *
	 * @param string $method
	 * @param string $endpoint
	 * @param array $params
	 *
	 * @return array
	 */
	private function request($method, $endpoint, $params = array()){

		$params["key"] = $this->apiKey;

		$query = ($method === self::METHOD_GET && $params) ? '?' . http_build_query($params) : '';
		$body = ($method !== self::METHOD_GET && $params) ? json_encode($params) : null;

		$url = $this->getBaseUrl() . $endpoint . $query;

		$cacheKey = $this->getCacheKey($url);
		$cacheTime = ($method === self::METHOD_GET) ? $this->cacheTime : 0;

		$response = UniteProviderFunctionsUC::rememberTransient($cacheKey, $cacheTime, function() use ($method, $url, $body){

			$headers = array(
				'Accept: application/json',
				'Content-Type: application/json',
			);

			$curl = curl_init();

			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

			$error = curl_error($curl);
			$response = curl_exec($curl);
			$response = json_decode($response, true);

			curl_close($curl);

			if($error)
				throw new Exception($error);

			if(isset($response["error"])){
				$error = $response["error"];
				$message = $error["message"];
				$status = isset($error["status"]) ? $error["status"] : $error["code"];

				throw new Exception("$message ($status)");
			}elseif(isset($response["error_message"])){
				$message = $response["error_message"];
				$status = isset($response["status"]) ? $response["status"] : $response["code"];

				throw new Exception("$message ($status)");
			}

			return $response;
		});

		return $response;
	}

	/**
	 * Get the cache key for the URL.
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	private function getCacheKey($url){

		$key = "google:" . md5($url);

		return $key;
	}

}
