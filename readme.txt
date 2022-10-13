=== Anomify AI - Anomaly Detection and Alerting ===

Contributors: simon.holliday
Tags: anomaly,anomalies,detection,metrics,analysis,analytics,performance
Requires at least: 5.0
Tested up to: 6.0.1
Requires PHP: 7.0
Stable tag: 0.3.2
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

The Anomify plugin sends selected performance metrics about your WordPress site to the Anomify.ai service for anomaly detection and alerting.

After ingesting data we learn normal patterns, detect any unusual behaviour, alert on anomalies in real time, and constantly feed back to improve the model.

You will need to obtain an API key and data URL from [Anomify AI](https://anomify.ai/) before the plugin can be enabled. Anomify has a free Developer account which allows up to 1000 metrics.

== Description ==

## Intro

Anomify AI is a UK based anomaly detection company for real time time-series data. Using machine learning and multi-stage analysis we enable organisations to react quickly to the changing health of their data at scale.

We provide a cost effective anomaly detection solution, which can be deployed flexibly via the cloud or on-premise. 

Anomify employs a semi-supervised system model that allows domain experts to directly train the system and continually improve its performance and usefulness.

### Integration with other WordPress plugins

Anomify can integrate with certain other plugins to send metrics describing their performance to Anomify which wll automatically detect anomalies.

The Anomify Wordpress plugin can detect anomalies on metrics generated from certain other plugins. 

To enable a third-party plugin integration, first install and activate the plugin, then enable it from the Anomify plugin settings page. All plugin integrations are disabled by default.

Metrics generated from plugins are automatically prefixed with "plugin.{plugin_name}." e.g. "plugin.woocommerce.".

These integrations are works in progress. If you find them useful, [please let us know](https://wordpress.org/support/plugin/anomify/) as it will help us to prioritise further development.

## WP Statistics

[Plugin page](https://wordpress.org/plugins/wp-statistics/)

Metrics that Anomify will collect:

* visitor.new - number of new visitors today
* visitor.returning - number of return visitors today
* visitor.country.$country_code - number of visitors from country with ISO code $country_code e.g. "ca"
* visitor.agent.$agent - number of visitors using user agent $agent e.g. "chrome"
* visitor.device.$device - number of visitors using device type $device e.g. "desktop"
* visitor.platform.$platform - number of visitors using platform $platform e.g. "windows"
* exclusion.$exclusion_reason - number of requests which are excluded from visit counts due to $exclusion_reason e.g. "robot"

Metrics from this plugin are prefixed with "plugin.wp-statistics", e.g. "plugin.wp-statistics.visitor.new"

## WooCommerce

[Plugin page](https://wordpress.org/plugins/woocommerce/)

Metrics that Anomify will collect:

* cart.added
* cart.emptied
* cart.updated
* customer.created
* customer.deleted
* customer.password.reset
* order.cancelled
* order.created
* payment.complete

Metrics from this plugin are prefixed with "plugin.woocommerce", e.g. "plugin.woocommerce.payment.complete"

## For plugin developers

If you want to send metrics from your own plugin, install the Anomify plugin, and use the following syntax in your own code:

	do_action('anomify_increment_plugin_metric', 'my.metric.name');

Or to increment the metric by a value other than 1, e.g. 5, add an optional third parameter:

	do_action('anomify_increment_plugin_metric', 'my.metric.name', 5);

The name of your plugin will be automatically prepended to the metric name so you don't need to include it in the name that you pass.

= Features =

* Always on
* Analysing your data 24/7
* Real-time alerts
* Custom algorithms
* Root cause analysis
* API access
* Patented tech

== Installation ==

You will need to obtain an API key and data URL from [Anomify AI](https://anomify.ai/) before the plugin can be enabled. Anomify has a free Developer account which allows up to 1000 metrics.

== Upgrade Notice ==

= 0.2.1 =

Now works without requiring the cURL library.

== Changelog ==

= 0.3.2 =

* Added metrics for successful and failed logins

= 0.3.1 =

* Integration with WooCommerce plugin
* Fix for WP Statistics integration

= 0.3.0 =

* Addition of 'platform' from WP Statistics

= 0.2.9 =

* Integration with WP Statistics plugin

= 0.2.8 =

* Bug fix for updating config
* Delete config from DB when plugin is deleted

= 0.2.7 =

* Sanitize text inputs

= 0.2.6 =

* Use WordPress HTTP_API for all requests

= 0.2.5 =

* Add ability for other plugins to send metrics

= 0.2.4 =

* Add exception handler to send metric for unhandled exceptions

= 0.2.3 =

* Remove debug info from config page
* Fix to params passed into _addHookAction()

= 0.2.2 =

* Add form validation and user feedback
* Add API key and URL live validation
* Catch exceptions on HTTP POST errors

= 0.2.1 =

* Always use file_get_contents() for POST, even when cURL is available
* Add User-agent HTTP header

= 0.2.0 =

* Store config JSON in the database rather than filesystem

= 0.1.1 =

* Initial release
