<?php
/*
Plugin Name: Watson Conversation
Description: This plugin allows the admin to easily add chatbots to their website powered by IBM Watson Conversation technology.
Author: IBM DBG
Version: 0.1.0
*/

define('WATSON_CONV_FILE', __FILE__);
define('WATSON_CONV_PATH', plugin_dir_path(__FILE__));

require_once(WATSON_CONV_PATH.'includes/settings.php');

add_action('admin_menu', 'WatsonConv\Settings::init_page');
add_action('admin_init', 'WatsonConv\Settings::init_settings');
register_deactivation_hook(__FILE__, 'WatsonConv\Settings::unregister');
