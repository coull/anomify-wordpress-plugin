<?php

require_once 'Wp/Admin.php';
require_once 'Wp/Hook.php';

class Anomify_Wp
{

	const PLUGIN_DIR = 'anomify';

	public static function init ()
	{

		if ((true == function_exists('is_admin')) && (true == is_admin())) {
			// Admin settings go here
			add_action('admin_menu', array('Anomify_Wp_Admin', 'addOptionsPage'));
		}

		if (false == Anomify_Config::getInstance()->getEnabled()) {
			return;
		}

		// Log unhandled exceptions
		set_exception_handler(array('Anomify_Wp_Hook', 'exception_handler'));

		add_action('shutdown', array('Anomify', 'export'), null, 0);

		self::_addHookAction('anomify_increment_plugin_metric', null, 2);

		// https://developer.wordpress.org/reference/hooks/wp/
		self::_addHookAction('wp', null, 1);

		// https://developer.wordpress.org/reference/hooks/post_updated/
		self::_addHookAction('post_updated', null, 2);

		// https://developer.wordpress.org/reference/hooks/deleted_post/
		self::_addHookAction('deleted_post', null, 2);

		// https://developer.wordpress.org/reference/hooks/comment_post/
		self::_addHookAction('comment_post', null, 3);

		// https://developer.wordpress.org/reference/hooks/transition_post_status/
		self::_addHookAction('transition_post_status', null, 3);

		// https://developer.wordpress.org/reference/hooks/field_no_prefix_save_pre/
		self::_addHookAction('content_save_pre', null, 1);

		// https://developer.wordpress.org/reference/hooks/wp_login/
		self::_addHookAction('wp_login', null, 2);

		// https://developer.wordpress.org/reference/hooks/wp_login_failed/
		self::_addHookAction('wp_login_failed', null, 2);

		if (true == Anomify_Config::getInstance()->get3pPluginIntegrationEnabled('wp-statistics')) {

			/*
			* WP Statistics plugin integration hooks
			*/

			// WP Statistics New Visitor
			self::_addHookFilter('wp_statistics_visitor_information', null, 1);

			// WP Statistics Returning Visitor
			self::_addHookAction('wp_statistics_update_visitor_hits', null, 2);

			// WP Statistics Exclusion
			self::_addHookAction('wp_statistics_save_exclusion', null, 2);

		}

		if (true == Anomify_Config::getInstance()->get3pPluginIntegrationEnabled('woocommerce')) {

			/*
			* WooCommerce plugin integration hooks
			*/

			self::_addHookAction('woocommerce_add_to_cart', null, 6);
			self::_addHookAction('woocommerce_cart_emptied', null, 1);
			self::_addHookAction('woocommerce_cart_updated', null, 0);

			self::_addHookAction('woocommerce_payment_complete', null, 1);
			self::_addHookAction('woocommerce_checkout_order_created', null, 1);
			self::_addHookAction('woocommerce_cancelled_order', null, 1);

			// Looks like woocommerce_created_customer is better
			// self::_addHookAction('woocommerce_new_customer', null, 2);
			self::_addHookAction('woocommerce_created_customer', null, 3);
			self::_addHookAction('woocommerce_delete_customer', null, 1);

			self::_addHookAction('woocommerce_customer_reset_password', null, 1);

		}

	}

	private static function _addHookAction ($sHook, $iPriority=null, $iArgs=null)
	{
		add_action($sHook, array('Anomify_Wp_Hook', $sHook), $iPriority, $iArgs);
	}

	private static function _addHookFilter ($sHook, $iPriority=null, $iArgs=null)
	{
		add_filter($sHook, array('Anomify_Wp_Hook', $sHook), $iPriority, $iArgs);
	}

	public static function uninstall ()
	{
		delete_option(Anomify_Config::OPTION_DB_KEY);
	}

}
