<?php

class Anomify_Wp_Admin
{

	public static function addOptionsPage ()
	{
		add_options_page('Anomify Options', 'Anomify', 'manage_options', 'anomify', array(__CLASS__, 'renderOptionsPage'));
		add_filter('plugin_action_links', array(__CLASS__, 'renderPluginActions'), 10, 2);
	}

	public static function getAdminUrl ()
	{
		return admin_url('options-general.php?page=' . Anomify_Wp::PLUGIN_DIR);
	}

	public static function renderPluginActions ($aLinks, $sFile)
	{

		if (false !== strpos($sFile, Anomify_Wp::PLUGIN_DIR)) {
			array_push($aLinks, sprintf('<a href="%s">Configure</a>', self::getAdminUrl()));
		}

		return $aLinks;

	}

	public static function renderOptionsPage ()
	{

		if (false == empty($_POST)) {
			Anomify_Config::getInstance()->update($_POST);
		}

		include 'includes/admin_options.php';

	}

}
