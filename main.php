<?php
/*
Plugin Name: Awesome Twitter Feeds
Plugin URI: https://wordpress.org/plugins/awesome-twitter-feeds/
Description: ATFeeds is a free WordPress plugin that allows you to display custom Twitter feeds using shortcode or widgets, it is also fully compatible with Visual Composer. You can customize the appearance of the feeds as you like!. ATFeeds uses the Twitter API.
Version: 1.0
Author: Alberto de Vera Sevilla
Author URI: https://profiles.wordpress.org/titodevera/
Text Domain: awesome-twitter-feeds
Domain Path: /lang
License: GPL3

  Awesome Twitter Feeds version 1.0, Copyright (C) 2016 Alberto de Vera Sevilla

  Awesome Twitter Feeds is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  Awesome Twitter Feeds is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with Awesome Twitter Feeds.  If not, see <http://www.gnu.org/licenses/>.
*/

namespace Awesome_Twitter_Feeds;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

define('ATF_CURRENT_VERSION','1.0');
define('ATF_PATH',plugin_basename( dirname( __FILE__ ) ));
define('ATF_DIR',dirname(__FILE__));
define('ATF_TEMPLATES_DIR',dirname(__FILE__).'/templates');
define('ATF_CLASSES_DIR',dirname(__FILE__).'/classes');

include_once ABSPATH . 'wp-admin/includes/plugin.php';
require_once ATF_DIR . '/inc/avs-metaboxes-master/init.php';
require_once ATF_DIR . "/inc/twitteroauth-master/autoload.php";
require_once ATF_CLASSES_DIR . '/class-atf-widget.php';
$widget_instance = new ATF_Widget;
require_once ATF_CLASSES_DIR . '/class-atf-plugin.php';
new ATF_Plugin($widget_instance);
if(is_admin()){
    require_once ATF_CLASSES_DIR . '/class-atf-admin.php';
    new ATF_Admin();
}
