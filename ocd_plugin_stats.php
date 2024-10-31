<?php
/*
Plugin Name: OCD Plugin Stats
Plugin URI: http://www.makerblock.com
Description: An easy way to monitor your plugin download statistics
Version: 0.31
Date: 01-24-2014
Author: MakerBlock
Author URI: http://www.makerblock.com
**************************************************************
	Copyright 2012  MakerBlock  

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
//	Create Defaults, Upon Activation
	register_activation_hook( __FILE__, array('mbk_ocd_plugin_stats', 'install') );
//	Upon Deactivation, Uninstall Everything
	register_deactivation_hook( __FILE__, array('mbk_ocd_plugin_stats', 'uninstall') );
//	Hook into the `wp_dashboard_setup` to register the widget
	add_action('wp_dashboard_setup', array('mbk_ocd_plugin_stats', 'widget_action_hook'));
//	Plugin, as a class
	class mbk_ocd_plugin_stats
		{
		//	Create Defaults, Upon Activation
			static function uninstall() 
				{  }
		//	Create Defaults, Upon Activation
			static function install() 
				{  }
		//	Create Dashboard Widget
				//	Dashboard Widget API - http://codex.wordpress.org/Dashboard_Widgets_API 
				//	An In-Depth Look at the Dashboard Widgets API - http://theme.it/an-in-depth-look-at-the-dashboard-widgets-api/
			static function widget_content()
				{ 
				
				// Cache the results so this doesn't need to process every time.
				// It takes a long time when you have more than a couple of plugins!
				if(!isset($_GET['cache'])) {
					$content = get_site_transient('ocd_plugins_table');
					if(!is_wp_error($content) && !empty($content)) { echo $content; return; }
				}
				
				$plugins = get_option('dashboard_widget_options');
					$plugins = explode(",", $plugins['mbk_ocd_plugin_stats_widget']['items']);
				$content = "
				<style type='text/css'>
					#mbk_ocd_plugin_table { font-size:1.1em; }
					#mbk_ocd_plugin_table td { padding: .5em; }
					div.star-holder {
						position: relative;
						height: 17px;
						width: 92px;
						background: url('http://wordpress.org/extend/plugins-plugins/bb-ratings/stars.png?19') repeat-x bottom left!important;
					}
					.star-rating {
						background: url('http://wordpress.org/extend/plugins-plugins/bb-ratings/stars.png?19') repeat-x top left!important;
						height: 17px;
						float: left;
						text-indent: 100%;
						overflow: hidden;
						white-space: nowrap;
					}
				</style>
				<div class='table table_content'>
				<span class='description alignright'>Last updated: ".date_i18n(get_option('date_format').' \a\t g:ia' , current_time( 'timestamp' ))." <a href='".add_query_arg(array('cache' => 0))."'>Refresh now</a></span>
				<table id='mbk_ocd_plugin_table' class='wp-list-table widefat'>
					<thead><tr><th>Plugin</th><th>Today</th><th>Yesterday</th><th>Last Week</th><th>All Time</th><th>Rating</th></tr></thead>
					<tbody>
					";
				echo $content;
				$yesterdaytotal = $todaytotal = $lastweektotal = $alltimetotal = $ratingtotal = 0;
				for ($i=0;$i<count($plugins);$i++)
					{
					$slug = rtrim(trim($plugins[$i]));
					if(empty($slug)) { continue; }
					$url = 'http://wordpress.org/extend/plugins/'. $slug .'/stats/';
					$src = wp_remote_get($url);
					if(is_wp_error($src) || $src['response']['code'] === 404) {
						$errors[] = sprintf('The "%s" plugin does not exist.', $slug);
						continue;
					} else {
						$src = $src['body'];	
					}
					
					$dom = new DOMDocument();
					$dom->preserveWhiteSpace = false; 
					$dom->formatOutput = true; 
					@$dom->loadHTML($src);
					$title 		= $dom->getElementsByTagName('h2') ->item(1) ->nodeValue;
					$today 		= $dom->getElementsByTagName('td') ->item(0) ->nodeValue;
					$yesterday	= $dom->getElementsByTagName('td') ->item(1) ->nodeValue;
					$lastweek	= $dom->getElementsByTagName('td') ->item(2) ->nodeValue;
					$alltime	= $dom->getElementsByTagName('td') ->item(3) ->nodeValue;
					
					
					preg_match('/itemprop\=\"ratingValue\"\scontent="(.*?)".*?ratingCount".*?content="(.*?)"/ism', $src, $matches);
					$rating = isset($matches[1]) ? floatval($matches[1]) : null;
					$starstitle	= isset($matches[1]) ? number_format($rating, 1).' out of 5 stars' : 'N/A';
					$starsstats = isset($matches[2]) ? '<small>('.$matches[2].' reviews)</small>' : '';
					
					preg_match('/(<div\sclass="star\-rating".*?<\/div>)/ism', $src, $matches);
					$stargraphic = isset($matches[1]) ? '<div class="star-holder" title="'.$starstitle.'">'.$matches[1].'</div>' : '';
					
					$stars = $stargraphic.$starsstats;
					
					$row = "<tr><td><a href='$url'>$title</a></td><td>$today</td><td>$yesterday</td><td>$lastweek</td><td>$alltime</td><td>$stars</td></tr>";
					echo $row; flush();
					$content .= $row;
					$todaytotal = $todaytotal + self::get_number($today);
					$yesterdaytotal = $yesterdaytotal + self::get_number($yesterday);
					$lastweektotal = $lastweektotal + self::get_number($lastweek);
					$alltimetotal = $alltimetotal + self::get_number($alltime);
					$ratingtotal = $ratingtotal + $rating;
					}
				$footer = "
					</tbody>
					<tfoot>
						<tr><th scope='row'>Totals</th><th>".self::get_number($todaytotal, true)."</th><th>".self::get_number($yesterdaytotal, true)."</th><th>".self::get_number($lastweektotal, true)."</th><th>".self::get_number($alltimetotal, true)."</th><th>".round($ratingtotal/count($plugins), 2)." out of 5 stars</tr></tfoot></table></div>";
				echo $footer;
				$content .= $footer;
				
				// Update the stats every 12 hours
				set_site_transient('ocd_plugins_table', $content, 60*60*12);
				
				// If plugins were non-existant, show the list here.
				if(!empty($errors)) {
					echo '<div class="widefat" style=" width: 96%; margin-bottom:1em; background-color: #ffebe8; border-color: #c00; padding: 0 2%;"><ul class="ul-square"><li>'.implode('</li><li>', $errors).'</li></ul></div>';
				}
				
			}
			static function get_number($number, $format = false) {
				$number = floatval(preg_replace('/[^0-9]/ism', '', trim(rtrim($number))));
				if($format) { 
					$number = number_format($number);
				}
				return $number;
			}
		//	Create configuration link for dashboard
			static function mbk_ocd_plugin_stats_configuration()
				{
				$widget_id = 'mbk_ocd_plugin_stats_widget'; 		// This must be the same ID we set in wp_add_dashboard_widget
				$form_id = 'mbk_ocd_plugin_stats_widget_control'; 	// Set this to whatever you want
				// Checks whether there are already dashboard widget options in the database
					if ( !$widget_options = get_option( 'dashboard_widget_options' ) )
						{ $widget_options = array(); } 				// If not, we create a new array 
				// Check whether we have information for this form
					if ( !isset($widget_options[$widget_id]) )
						{ $widget_options[$widget_id] = array(); } // If not, we create a new array
				// Check whether our form was just submitted
					if ( 'POST' == $_SERVER['REQUEST_METHOD'] && isset($_POST[$form_id]) ) 
						{
						$plugins = $_POST[$form_id]['items'];							// Get the value. In this case ['items'] is from the input field with the name of '.$form_id.'[items]
						$widget_options[$widget_id]['items'] = $plugins; 				// Set the plugins of items
						update_option( 'dashboard_widget_options', $widget_options );	// Update our dashboard widget options so we can access later
						}
				// Check if we have set the plugins of posts previously. If we didn't, then we just set it as empty. This value is used when we create the input field
					$plugins = isset( $widget_options[$widget_id]['items'] ) ? $widget_options[$widget_id]['items'] : '';
				// Create our form fields. Pay very close attention to the name part of the input field.
					echo '<p><label for="mbk_ocd_plugin_stats_names">' . __('Plugin slugs, separated by ",":') . '</label>';
					echo "<input id='mbk_ocd_plugin_stats_names' name='".$form_id."[items]' type='text' value='$plugins'/ size='53'></p>";
				}
		//	Create function to use in the action hook
			static function widget_action_hook()
				{ wp_add_dashboard_widget('mbk_ocd_plugin_stats_widget', 'OCD Plugin Stats', array('mbk_ocd_plugin_stats', 'widget_content'), array('mbk_ocd_plugin_stats', 'mbk_ocd_plugin_stats_configuration')); }
		}
?>