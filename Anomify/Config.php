<?php

class Anomify_Config
{

	const FORM_FIELD_API_KEY	= 'anomify_api_key';
	const FORM_FIELD_DATA_URL	= 'anomify_data_url';
	const FORM_FIELD_ENABLED	= 'anomify_enabled';

	const OPTION_DB_KEY			= 'anomify_options';
	const USE_DB_FOR_OPTIONS	= true; // Otherwise use filesystem

	private static $_oInstance;
	private $_aFormError = array();

	private $_bEnabled = false;
	private $_sApiKey = '';
	private $_sDataUrl = '';

	private static $_a3pPluginIntegrationAvailable = array (
		'wp-statistics'	=> 'WP Statistics',
		'woocommerce'	=> 'WooCommerce'
	);

	private $_a3pPluginIntegrationEnabled = array (
		'wp-statistics'	=> false,
		'woocommerce'	=> false
	);

	private $_bIsValid;

	public static function getInstance ()
	{

		if (null == self::$_oInstance) {
			self::$_oInstance = self::_load();
		}

		return self::$_oInstance;

	}

	private static function _load ()
	{

		if (true == self::USE_DB_FOR_OPTIONS) {
			return self::_load_db();
		}

		return self::_load_fs();

	}

	private static function _load_db ()
	{

		if (false === ($oConfig = get_option(self::OPTION_DB_KEY))) {
			$sClass = __CLASS__;
			return new $sClass;
		}

		return $oConfig;

	}

	private static function _load_fs ()
	{

		$sFilePath = self::_getFilePath();
		$sClass = __CLASS__;

		if (false == file_exists($sFilePath)) {
			return new $sClass;
		}

		if (false == is_readable($sFilePath)) {
			throw new Exception('Config file exists but is not readable: ' . $sFilePath);
		}

		$sConfig = file_get_contents($sFilePath);

		if (true == empty($sConfig)) {
			return new $sClass;
		}

		if (false == ($oConfig = unserialize($sConfig))) {
			throw new Exception('Config file could not be parsed: ' . $sFilePath);
		}

		return $oConfig;

	}

	private static function _getFilePath ()
	{
		return Anomify::getDataDir() . DIRECTORY_SEPARATOR . 'config.srl';
	}

	private function _addFormError ($sKey, $sError)
	{
		$this->_aFormError[$sKey] = $sError;
		return $this;
	}

	public function getFormErrors ()
	{
		return $this->_aFormError;
	}

	public function getNumFormErrors ()
	{
		return count($this->_aFormError);
	}

	public function update ($aData)
	{

		$iUpdated = 0;
		$this->setIsValid(false);

		if (true == array_key_exists(self::FORM_FIELD_ENABLED, $aData)) {
			$this->setEnabled(Anomify_Utils::stringToBool($aData[self::FORM_FIELD_ENABLED]));
			++$iUpdated;
		}

		if (true == array_key_exists(self::FORM_FIELD_DATA_URL, $aData)) {

			$sDataUrl = sanitize_text_field($aData[self::FORM_FIELD_DATA_URL]);

			// Basic URL validation

			if (true == empty($sDataUrl)) {

				$this->_addFormError(self::FORM_FIELD_DATA_URL, 'Data URL must be specfied');
				$this->setDataUrl(null);

			} else if (false == ($aDataUrl = parse_url($sDataUrl)) || false == array_key_exists('host', $aDataUrl) || false == array_key_exists('path', $aDataUrl)) {

				$this->_addFormError(self::FORM_FIELD_DATA_URL, '"' . $sDataUrl . '" is not a valid URL');

			} else {

				$this->setDataUrl($sDataUrl);
				++$iUpdated;

			}

		}

		if (true == array_key_exists(self::FORM_FIELD_API_KEY, $aData)) {

			$sApiKey = sanitize_text_field($aData[self::FORM_FIELD_API_KEY]);

			if (true == empty($sApiKey)) {

				$this->_addFormError(self::FORM_FIELD_API_KEY, 'API key must be specfied');
				$this->setApiKey(null);

			} else {

				$this->setApiKey($sApiKey);
				++$iUpdated;

			}

		}

		if (false == empty($this->getApiKey()) && false == empty($this->getDataUrl())) {

			// If the key and data URL are both set, validate them.

			if (false == Anomify::validateApiKey($this)) {
				// If they're not valid, don't save the config
				$this->_addFormError(self::FORM_FIELD_API_KEY, 'The API key provided is not valid for the data URL provided.');
				return false;
			}

		} else {

			// Disable if either API key or URL is not set
			$this->setEnabled(false);

		}

		// Check for third-party plugin integrations

		$aValid3pPluginKey = array_keys(self::$_a3pPluginIntegrationAvailable);

		foreach ($aData as $sKey=>$sVal) {

			if (true == preg_match('#^3p-plugin-(.+)-enabled$#', $sKey, $aCapture)) {

				$sPluginKey = $aCapture[1];

				if (false == in_array($sPluginKey, $aValid3pPluginKey)) {

					$this->_addFormError($sKey, 'Third-party plugin key not recognized: ' . $sKey);

				} else {

					$this->_a3pPluginIntegrationEnabled[$sPluginKey] = Anomify_Utils::stringToBool($sVal);
					++$iUpdated;

				}

			}

		}

		if (0 < $iUpdated && 0 === $this->getNumFormErrors()) {
			$this->setIsValid(true);
			$this->save();
		}

		return true;

	}

	public function delete ()
	{

		if (false == self::USE_DB_FOR_OPTIONS) {
			return false;
		}

		if (true === delete_option(self::OPTION_DB_KEY)) {
			return true;
		}

		return false;

	}

	public function save ()
	{

		if (true == self::USE_DB_FOR_OPTIONS) {
			return $this->_save_db();
		}

		return $this->_save_fs();

	}

	private function _save_db ()
	{

		if (true === update_option(self::OPTION_DB_KEY, $this)) {
			return true;
		}

		if (true === add_option(self::OPTION_DB_KEY, $this)) {
			return true;
		}

		return false;

	}

	private function _save_fs ()
	{
		$sFilePath = self::_getFilePath();

		if (false == (file_put_contents($sFilePath, serialize($this)))) {
			throw new Exception('Config file could not be written: ' . $sFilePath);
		}

		return true;

	}

	public function setIsValid ($bIsValid)
	{
		$this->_bIsValid = (bool) $bIsValid;
		return $this;
	}

	public function getIsValid ()
	{
		return $this->_bIsValid;
	}

	public function setEnabled ($bEnabled)
	{
		$this->_bEnabled = (bool) $bEnabled;
		return $this;
	}

	public function getEnabled ()
	{
		return $this->_bEnabled;
	}

	public function get3pPluginIntegrationsAvailable ()
	{
		return self::$_a3pPluginIntegrationAvailable;
	}

	public function get3pPluginIntegrationEnabled ($sPlugin)
	{

		if (true == array_key_exists($sPlugin, $this->_a3pPluginIntegrationEnabled)) {
			return $this->_a3pPluginIntegrationEnabled[$sPlugin];
		}

		return false;

	}

	public function setApiKey ($sApiKey = null)
	{
		$this->_sApiKey = (null == $sApiKey) ? null : (string) $sApiKey;
		return $this;
	}

	public function getApiKey ()
	{
		return $this->_sApiKey;
	}

	public function setDataUrl ($sDataUrl = null)
	{
		$this->_sDataUrl = (null == $sDataUrl) ? null : (string) $sDataUrl;
		return $this;
	}

	public function getDataUrl ()
	{
		return $this->_sDataUrl;
	}

}
