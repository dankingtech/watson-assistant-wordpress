<?php
/*
Plugin Name: Watson Conversation
Description: This plugin allows you to easily add chatbots powered by IBM Watson Conversation to your website.
Author: IBM DBG
Version: 0.1.0
Text Domain: watsonconv
*/

define('WATSON_CONV_PATH', plugin_dir_path(__FILE__));
define('WATSON_CONV_URL', plugin_dir_url(__FILE__));

require_once(WATSON_CONV_PATH.'includes/settings.php');
require_once(WATSON_CONV_PATH.'includes/frontend.php');
require_once(WATSON_CONV_PATH.'includes/api.php');

// ----- Settings --------

add_action('admin_menu', array('WatsonConv\Settings', 'init_page'));
add_action('admin_init', array('WatsonConv\Settings', 'init_settings'));
add_action('admin_enqueue_scripts', array('WatsonConv\Settings', 'init_scripts'));
register_deactivation_hook(__FILE__, array('WatsonConv\Settings', 'unregister'));

$path = plugin_basename(__FILE__);

add_action("after_plugin_row_{$path}", array('WatsonConv\Settings', 'render_notice'), 10, 3);
add_filter("plugin_action_links_{$path}", array('WatsonConv\Settings', 'add_settings_link'));

// ----- Frontend --------

add_action('wp_enqueue_scripts', array('WatsonConv\Frontend', 'load_styles'));
add_action('wp_footer', array('WatsonConv\Frontend', 'render_chat_box'));

// ----- Server-side Proxy API --------

register_activation_hook(__FILE__, array('WatsonConv\Api', 'init_rate_limit'));
register_deactivation_hook(__FILE__, array('WatsonConv\Api', 'uninit_rate_limit'));
add_action('watson_save_to_disk', array('WatsonConv\API', 'record_api_usage'));
add_action('watson_reset_api_usage', array('WatsonConv\API', 'reset_api_usage'));
add_action('rest_api_init', array('WatsonConv\API', 'register_proxy'));
add_action('update_option_watsonconv_interval', array('WatsonConv\API', 'init_rate_limit'));
add_filter('cron_schedules', array('WatsonConv\API', 'add_cron_schedules'));
