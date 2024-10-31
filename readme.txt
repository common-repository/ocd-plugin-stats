=== OCD Plugin Stats ===
Contributors: MakerBlock
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=EYYL4BEFYRJF2
Tags: plugin, download, stat, stats, statistics
Requires at least: 3.3
Tested up to: 3.8.1
Stable tag: 0.31

An easy way to monitor your plugin download statistics

== Description ==

OCD Plugin Stats is a simple plugin that creates a little dashboard widget that displays the WordPress.org plugin download statistics for plugins of your choice.

If you like this plugin, <a href="http://wordpress.org/extend/plugins/ocd-plugin-stats/">please give it a 5-star rating over here --></a>

This short and simple plugin won't leave any trace in your database after it has been uninstalled.  It doesn't create extra tables, content types, or taxonomies.

And, you can always <a href="http://makerblock.com/">visit my website</a> (It's mostly about awesome open source robots, if you're into that kinda thing).

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload `ocd_plugin_stats.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Navigate to the dashboard located at `http://YOURSITE.com/wp-admin/`
1. Hover the mouse over the "OCD Plugin Stats" widget and you will see the "Configure" link show up (`http://YOURSITE.com/wp-admin/?edit=mbk_ocd_plugin_stats_widget#mbk_ocd_plugin_stats_widget`)
1. Enter your plugin slugs separated by commas and click "Submit" to save your settings

== Frequently Asked Questions ==

= Wait!  I have more questions! = 

Please <a href="http://makerblock.com/2012/01/simple-series-wordpress-plugin/">visit my website</a> or <a href="http://makerblock.com/contact/">send me an e-mail!</a>

== Screenshots ==

1. What the widget will look like in your dashboard.

== Changelog ==

= 0.3 =
* Added plugin ratings
* Added plugin stat totals to table
* Added stats caching for 12 hours, as well as a refresh link
* Added `rtrim` to the plugin name
* Improved table layout
* Fixed `$dom->loadHTML()` PHP notices
* Added error notices when a plugin doesn't exist
* Update contributed by <a href="http://profiles.wordpress.org/katzwebdesign/">Katz Web Services, Inc.</a>

= 0.1 =
* Initial release on 2/29/2012!

== To Do List ==
* Include multi-language support.