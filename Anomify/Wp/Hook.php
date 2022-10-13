<?php

class Anomify_Wp_Hook
{

	// @see https://adambrown.info/p/wp_hooks/hook/actions

	public static function post_updated (int $iPostId, WP_Post $oPost)
	{

		$sPostType = get_post_type($oPost);

		$sMetric = sprintf("content.%s.updated",
			$sPostType
		);

		Anomify::incrementMetric($sMetric);

	}

	public static function deleted_post (int $iPostId, WP_Post $oPost)
	{

		$sPostType = get_post_type($oPost);

		$sMetric = sprintf("content.%s.deleted",
			$sPostType
		);

		Anomify::incrementMetric($sMetric);

	}

	public static function transition_post_status (string $sNewStatus, string $sOldStatus, WP_Post $oPost)
	{

		// @see https://codex.wordpress.org/Post_Status_Transitions
		// Expected status types auto-draft, draft, future, inherit, new, pending, private, publish, trash

		$sPostType = get_post_type($oPost);

		$sMetric = sprintf("content.%s.status.transition.%s",
			$sPostType,
			$sNewStatus
		);

		Anomify::incrementMetric($sMetric);

	}

	public static function comment_post (int $iCommentId, $mCommentApproved, $aCommentData=null)
	{

		switch ($mCommentApproved) {

			case 1:
			case '1':
				$sApprovedStatus = 'approved';
				break;

			case 0:
			case '0':
				$sApprovedStatus = 'not-approved';
				break;

			case 'spam':
				$sApprovedStatus = 'spam';
				break;

			default:
				$sApprovedStatus = 'unknown-status';

		}

		Anomify::incrementMetric(sprintf("content.comment.added.%s", $sApprovedStatus));

	}

	public static function wp (WP $oWp)
	{
		Anomify::incrementMetric('wp');
	}

	public static function content_save_pre ($mContent)
	{

		if (true == is_string($mContent)) {

			if (true == preg_match('#(?:&lt;|<)\s*/?\s*(script|style)\W#i', $mContent, $aCapture)) {
				Anomify::incrementMetric(sprintf("content.filter.tag.%s", $aCapture[1]));
			}

		}

		return $mContent;

	}

	public static function wp_login ($sUsername, $oUser)
	{
		Anomify::incrementMetric('login.success');
	}

	public static function wp_login_failed ($sUsername, $oError)
	{
		Anomify::incrementMetric('login.fail');
	}

	public static function anomify_increment_plugin_metric ($sMetric, $iCount=1)
	{
		Anomify::incrementPluginMetric($sMetric, $iCount);
	}

	public static function exception_handler ()
	{
		Anomify::incrementMetric('php.exception');
	}

	/*
	* Integration with WP Statistics plugin
	* https://wordpress.org/plugins/wp-statistics/
	*/

	private static function _wp_statistics_sanitize_visitor_property ($sString)
	{

		// Replace non-valid metric characters
		$sString = preg_replace('#[^a-z0-9_-]+#', '_', strtolower($sString));

		// Reduce duplicate _ chars
		$sString = preg_replace('#_{2,}#', '_', $sString);

		$sString = trim($sString, '_');

		if (true == empty($sString) || '000' == $sString) {
			// "000" occurs for an unknown country
			return 'unknown';
		}

		return $sString;

	}

	private static function _wp_statistics_visitor_information ($oVisitor)
	{

		if (null == $oVisitor) {
			return;
		}

		if (true == isset($oVisitor->location)) {
			Anomify::incrementMetric(sprintf('plugin.wp-statistics.visitor.country.%s', self::_wp_statistics_sanitize_visitor_property($oVisitor->location)));
		}

		if (true == isset($oVisitor->agent)) {
			Anomify::incrementMetric(sprintf('plugin.wp-statistics.visitor.agent.%s', self::_wp_statistics_sanitize_visitor_property($oVisitor->agent)));
		}

		if (true == isset($oVisitor->device)) {
			Anomify::incrementMetric(sprintf('plugin.wp-statistics.visitor.device.%s', self::_wp_statistics_sanitize_visitor_property($oVisitor->device)));
		}

		if (true == isset($oVisitor->platform)) {
			Anomify::incrementMetric(sprintf('plugin.wp-statistics.visitor.platform.%s', self::_wp_statistics_sanitize_visitor_property($oVisitor->platform)));
		}

	}

	// WP Statistics New Visitor
	public static function wp_statistics_visitor_information ($oVisitor)
	{

		Anomify::incrementMetric('plugin.wp-statistics.visitor.new');
		self::_wp_statistics_visitor_information($oVisitor);

		return $oVisitor;

	}

	// WP Statistics Returning Visitor
	public static function wp_statistics_update_visitor_hits ($iVisitorId, $oVisitor)
	{
		Anomify::incrementMetric('plugin.wp-statistics.visitor.returning');
		self::_wp_statistics_visitor_information($oVisitor);
	}

	// WP Statistics Exclusion
	public static function wp_statistics_save_exclusion ($aExclusion, $iDbInsertId)
	{
		Anomify::incrementMetric(sprintf('plugin.wp-statistics.exclusion.%s', self::_wp_statistics_sanitize_visitor_property($aExclusion['exclusion_reason'])));
	}

	/*
	* Integration with WooCommerce Plugin
	* https://wordpress.org/plugins/woocommerce/
	*/

	public static function woocommerce_add_to_cart ($sCartItemKey, $iProductId, $iQuantity, $iVariationId, $aVariation, $aCartItemData)
	{
		Anomify::incrementMetric('plugin.woocommerce.cart.added', $iQuantity);
	}

	public static function woocommerce_cart_emptied ()
	{
		Anomify::incrementMetric('plugin.woocommerce.cart.emptied');
	}

	public static function woocommerce_cart_updated ()
	{
		Anomify::incrementMetric('plugin.woocommerce.cart.updated');
	}

	public static function woocommerce_payment_complete ()
	{
		Anomify::incrementMetric('plugin.woocommerce.payment.complete');
	}

	public static function woocommerce_checkout_order_created ($mOrder)
	{
		Anomify::incrementMetric('plugin.woocommerce.order.created');
	}

	public static function woocommerce_cancelled_order ()
	{
		Anomify::incrementMetric('plugin.woocommerce.order.cancelled');
	}

	public static function woocommerce_created_customer ($iCustomerId, $mCustomerData, $mPasswordGenerated)
	{
		Anomify::incrementMetric('plugin.woocommerce.customer.created');
	}

	public static function woocommerce_new_customer ($iCustomerId, $mCustomerData)
	{
		Anomify::incrementMetric('plugin.woocommerce.customer.new');
	}

	public static function woocommerce_delete_customer ($iCustomerId)
	{
		Anomify::incrementMetric('plugin.woocommerce.customer.deleted');
	}

	public static function woocommerce_customer_reset_password ($mUser)
	{
		Anomify::incrementMetric('plugin.woocommerce.customer.password.reset');
	}

}
