<?php
/*
Plugin Name: Watson Conversation
Description: This plugin allows you to easily add chatbots powered by IBM Watson Conversation to your website.
Author: IBM DBG
Version: 0.1.0
Text Domain: watsonconv
*/

define('WATSON_CONV_FILE', __FILE__);
define('WATSON_CONV_PATH', plugin_dir_path(__FILE__));
define('WATSON_CONV_URL', plugin_dir_url(__FILE__));
define('WATSON_CONV_BASENAME', plugin_dir_url(__FILE__));

require_once(WATSON_CONV_PATH.'includes/settings.php');
require_once(WATSON_CONV_PATH.'includes/frontend.php');
require_once(WATSON_CONV_PATH.'includes/api.php');
