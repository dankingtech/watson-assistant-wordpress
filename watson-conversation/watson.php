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

define('API_VERSION', '2017-04-21');
define('BASE_URL', 'https://gateway.watsonplatform.net/conversation/api/v1');

require_once(WATSON_CONV_PATH.'includes/settings.php');

// ----- Settings --------

add_action('admin_menu', 'WatsonConv\Settings::init_page');
add_action('admin_init', 'WatsonConv\Settings::init_settings');
register_deactivation_hook(WATSON_CONV_FILE, 'WatsonConv\Settings::unregister');

$path = plugin_basename( __FILE__ );

add_action("after_plugin_row_{$path}", 'WatsonConv\Settings::render_notice', 10, 3);

// ----- Frontend --------

add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('chat-style', plugin_dir_url( __FILE__ ).'styles.css');
});

add_action('wp_footer', function () {
    if (!empty(get_option('watsonconv_id')) &&
        !empty(get_option('watsonconv_username')) &&
        !empty(get_option('watsonconv_password'))) {
    ?>
        <div id="chat-box"></div>
    <?php
        wp_enqueue_script('chat-app', plugin_dir_url( __FILE__ ).'app.js');
        wp_localize_script('chat-app', 'delay', (int)get_option('watsonconv_delay', 0));
    }
});

// ----- Server-side Proxy API --------

add_action('rest_api_init', function () {
    register_rest_route('watsonconv/v1', '/message',
        array(
            'methods' => 'post',
            'callback' => function (WP_REST_Request $request) {
                $body = $request->get_json_params();
                $auth_token = 'Basic ' . base64_encode(
                    get_option('watsonconv_username').':'.
                    get_option('watsonconv_password'));
                $watsonconv_id = get_option('watsonconv_id');

                $response = wp_remote_post(
                    BASE_URL."/workspaces/$watsonconv_id/message?version=".API_VERSION,
                    array(
                        'headers' => array(
                            'Authorization' => $auth_token,
                            'Content-Type' => 'application/json'
                        ), 'body' => json_encode(array(
                            'input' => $body['input'],
                            'context' => empty($body['context']) ? new stdClass() : $body['context']
                        ))
                    )
                );

                return json_decode(wp_remote_retrieve_body($response));
            }
        )
    );
});
