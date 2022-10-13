<?php

/*
	Plugin Name: Anomify
	Plugin URI: http://wordpress.org/plugins/anomify
	Description: This plugin sends metrics from your WordPress site to anomify.ai for anomaly detection
	Version: 0.3.2
	Author: Anomify AI
	Author URI: https://anomify.ai/
*/

if (true == defined('__DIR__')) {
	define('ANOMIFY_PLUGIN_DIR', __DIR__);
} else {
	define('ANOMIFY_PLUGIN_DIR', dirname(__FILE__));
}

require_once 'Anomify.php';

register_uninstall_hook(__FILE__, array('Anomify','uninstall'));

Anomify::init();
