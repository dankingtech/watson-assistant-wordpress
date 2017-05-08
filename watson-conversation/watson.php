<?php
/*
Plugin Name: Watson Conversation
Description: This plugin allows you to easily add chatbots powered by IBM Watson Conversation to your website.
Author: IBM DBG
Version: 0.1.0
*/

define('WATSON_CONV_FILE', __FILE__);
define('WATSON_CONV_PATH', plugin_dir_path(__FILE__));

require_once(WATSON_CONV_PATH.'includes/settings.php');

add_action('admin_menu', 'WatsonConv\Settings::init_page');
add_action('admin_init', 'WatsonConv\Settings::init_settings');
register_deactivation_hook(WATSON_CONV_FILE, 'WatsonConv\Settings::unregister');

$path = plugin_basename( __FILE__ );

add_action("after_plugin_row_{$path}", 'WatsonConv\Settings::render_notice', 10, 3);
