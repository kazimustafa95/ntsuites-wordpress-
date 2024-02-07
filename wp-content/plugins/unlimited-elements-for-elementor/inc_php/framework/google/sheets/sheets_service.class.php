<?php

/**
 * @link https://developers.google.com/sheets/api/reference/rest
 */
class UEGoogleAPISheetsService extends UEGoogleAPIClient{

	/**
	 * Get the spreadsheet.
	 *
	 * @param string $spreadsheetId
	 * @param array $params
	 *
	 * @return UEGoogleAPISpreadsheet
	 */
	public function getSpreadsheet($spreadsheetId, $params = array()){

		$response = $this->get("/$spreadsheetId", $params);
		$response = UEGoogleAPISpreadsheet::transform($response);

		return $response;
	}

	/**
	 * Get the spreadsheet values.
	 *
	 * @param string $spreadsheetId
	 * @param string $range
	 * @param array $params
	 *
	 * @return UEGoogleAPISheetValues
	 */
	public function getSpreadsheetValues($spreadsheetId, $range, $params = array()){

		$range = urlencode($range);

		$response = $this->get("/$spreadsheetId/values/$range", $params);
		$response = UEGoogleAPISheetValues::transform($response);

		return $response;
	}

	/**
	 * Get the base URL for the API.
	 *
	 * @return string
	 */
	protected function getBaseUrl(){

		return "https://sheets.googleapis.com/v4/spreadsheets";
	}

}
