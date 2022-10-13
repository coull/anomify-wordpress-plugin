<?php

require_once 'Anomify/Config.php';
require_once 'Anomify/Exception.php';
require_once 'Anomify/Utils.php';
require_once 'Anomify/Wp.php';

class Anomify
{

	const LOG_JSON_TO_FILE = false;

	// If true, we will wait for response, check status code, and error on non 2XX
	// If false, we will fire and forget
	const WP_HTTP_API_BLOCKING = false;

	const METRIC_PREFIX = 'wordpress';
	const DATA_SUBDIR = 'data';

	/**
	 * content.post.view.x.
	 * content.post.status.transition
	 * .content.page.status.transition.publish
	*/

	private static $_aMetric = array();

	public static function init ()
	{
		Anomify::incrementMetric('plugin.' . Anomify_Wp::PLUGIN_DIR . '.init');
		Anomify_Wp::init();
	}

	public static function uninstall ()
	{
		Anomify_Wp::uninstall();
	}

	public static function getDataDir ()
	{
		return ANOMIFY_PLUGIN_DIR . DIRECTORY_SEPARATOR . self::DATA_SUBDIR;
	}

	public static function incrementPluginMetric ($sMetric, $iCount=1)
	{
		self::incrementMetric(self::_normalizePluginMetricName($sMetric), $iCount);
	}

	public static function incrementMetric ($sMetric, $iCount=1)
	{

		// WordPress plugin validation requires this one of their sanitize functions here
		$sHost = sanitize_text_field($_SERVER['HTTP_HOST']);

		$sMetric = $sHost . '.' . $sMetric;

		if (true == array_key_exists($sMetric, self::$_aMetric)) {
			++self::$_aMetric[$sMetric]	;
		} else {
			self::$_aMetric[$sMetric] = 1;
		}

	}

	private static function _getLogFilePath ()
	{
		return self::getDataDir() . DIRECTORY_SEPARATOR . Anomify_Wp::PLUGIN_DIR . '.log';
	}

	private static function _log ($sString)
	{

		if (false == (file_put_contents(self::_getLogFilePath(), $sString . "\n", FILE_APPEND))) {
			return false;
		}

		return true;

	}

	public static function validateApiKey (Anomify_Config $oConfig)
	{

		$oRequest = new StdClass;

		$oRequest->key = $oConfig->getApiKey();
		$oRequest->validate_key = true;

		$sJson = json_encode($oRequest);

		$sResponse = self::_exportWpHttpApi($sJson, $oConfig->getDataUrl(), true);

		if (true == empty($sResponse)) {
			return false;
		}

		if (false == ($oResponse = @json_decode($sResponse))) {
			return false;
		}

		if (false == isset($oResponse->status)) {
			return false;
		}

		if ('key valid' !== $oResponse->status) {
			return false;
		}

		return true;

	}

	public static function export ()
	{

		$sApiKey = Anomify_Config::getInstance()->getApiKey();
		$sDataUrl = Anomify_Config::getInstance()->getDataUrl();

		$iTimestamp = time();

		$aoMetric = array();

		foreach (self::$_aMetric as $sMetric => $iCount) {

			$oMetric = new StdClass;

			if (false == empty(self::METRIC_PREFIX)) {
				$oMetric->metric = self::METRIC_PREFIX . '.' . $sMetric;
			} else {
				$oMetric->metric = $sMetric;
			}

			$oMetric->timestamp = $iTimestamp;
			$oMetric->value = $iCount;

			array_push($aoMetric, $oMetric);

		}

		$oRequest = new StdClass;
		$oRequest->key = $sApiKey;
		$oRequest->metrics = $aoMetric;

		$sJson = json_encode($oRequest);

		if (true == self::LOG_JSON_TO_FILE) {
			self::_log($sJson);
		}

		if (true == empty($sApiKey) || true == empty($sDataUrl)) {
			return;
		}

		return self::_exportWpHttpApi($sJson, $sDataUrl);

	}

	private static function _exportWpHttpApi ($sJson, $sDataUrl, $bWpHttpApiBlocking=null)
	{

		$bWpHttpApiBlocking = (null !== $bWpHttpApiBlocking) ? (bool) $bWpHttpApiBlocking : self::WP_HTTP_API_BLOCKING;
		$aOpt = array (

			// @see https://developer.wordpress.org/plugins/http-api/

			'method'  			=> 'POST',
			'timeout' 			=> 1,
			'redirection'		=> 0,
			'httpversion'		=> '1.1',
			'blocking'			=> $bWpHttpApiBlocking,
			'headers'  			=> array('Content-type: application/json', 'Accept: */*', 'User-Agent: Anomify WordPress Plugin'),
			'body'				=> $sJson,
			'cookies'			=> array()

		);

		try {

			$mResponse = wp_remote_post($sDataUrl, $aOpt);

			if (false === $mResponse) {
				throw new Anomify_Exception('Request failed :(');
			}

			if (true == is_wp_error($mResponse)) {
				throw new Anomify_Exception('Request failed with WP error: ' . $mResponse->get_error_message());
			}

			if (false == $bWpHttpApiBlocking) {

				$sResponse = '';

			} else {

				$iStatusCode = (int) wp_remote_retrieve_response_code($mResponse);

				if (200 > $iStatusCode || 300 <= $iStatusCode) {
					throw new Anomify_Exception('Request returned non 2XX status code: ' . $iStatusCode);
				}

				$sResponse = wp_remote_retrieve_body($mResponse);

			}


		} catch (Anomify_Exception $oE) {

			return false;

		}

		return $sResponse;

	}

	/**
	 * Prepend a plugin metric name with the plugin's name, which is derived
	 * from the calling file path
	 */
	private static function _normalizePluginMetricName ($sMetric=null)
	{

		$aaBacktrace = debug_backtrace();

		if (false == is_array($aaBacktrace) || true == empty($aaBacktrace)) {
			return;
		}

		$sPlugin = null;

		foreach ($aaBacktrace as $aBacktrace) {

			// Find first plugin reference which is not *this* plugin

			$aFileInfo = pathinfo($aBacktrace['file']);

			if (false == preg_match('#^' . WP_PLUGIN_DIR . '/\s*([^\s/]+)#', $aFileInfo['dirname'], $aCapture)) {
				continue;
			}

			if (true == empty($aCapture) || (2 > count($aCapture)) || true == empty($aCapture[1])) {
				continue;
			}

			if (Anomify_Wp::PLUGIN_DIR == trim($aCapture[1])) {
				continue;
			}

			$sPlugin = trim($aCapture[1]);

			break;

		}

		if (true == empty($sPlugin)) {
			return;
		}

		$sMetric = trim($sMetric, " \n\r\t\v\0.");
		$sMetric = preg_replace('#\.{2,}#', '.', $sMetric);
		$sMetric = 'plugin.' . $sPlugin . '.' . $sMetric;

		return $sMetric;

	}

}
