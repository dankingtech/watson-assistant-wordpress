<?php
namespace WatsonConv;

register_activation_hook(__FILE__, array('WatsonConv\API', 'init_rate_limit'));
register_deactivation_hook(__FILE__, array('WatsonConv\API', 'uninit_rate_limit'));
add_action('watson_save_to_disk', array('WatsonConv\API', 'record_api_usage'));
add_action('watson_reset_api_usage', array('WatsonConv\API', 'reset_api_usage'));
add_action('rest_api_init', array('WatsonConv\API', 'register_proxy'));
add_action('update_option_watsonconv_interval', array('WatsonConv\API', 'init_rate_limit'));
add_filter('cron_schedules', array('WatsonConv\API', 'add_cron_schedules'));

class API {
    const API_VERSION = '2017-04-21';
    const BASE_URL = 'https://gateway.watsonplatform.net/conversation/api/v1';

    public static function register_proxy() {
        register_rest_route('watsonconv/v1', '/message',
            array(
                'methods' => 'post',
                'callback' => array(__CLASS__, 'route_request')
            )
        );
    }

    public static function route_request(\WP_REST_Request $request) {
        if (get_option('watsonconv_use_limit', false) == false ||
            get_option('watsonconv_total_requests', 0) < get_option('watsonconv_limit'))
        {
            set_transient(
                'watsonconv_total_requests',
                (get_transient('watsonconv_total_requests') ?: 0) + 1,
                3600
            );

            $body = $request->get_json_params();
            $auth_token = 'Basic ' . base64_encode(
                get_option('watsonconv_username').':'.
                get_option('watsonconv_password'));
            $watsonconv_id = get_option('watsonconv_id');

            $response = wp_remote_post(
                self::BASE_URL."/workspaces/$watsonconv_id/message?version=".self::API_VERSION,
                array(
                    'headers' => array(
                        'Authorization' => $auth_token,
                        'Content-Type' => 'application/json'
                    ), 'body' => json_encode(array(
                        'input' => $body['input'],
                        'context' => empty($body['context']) ? new \stdClass() : $body['context']
                    ))
                )
            );

            return json_decode(wp_remote_retrieve_body($response));
        } else {
            return array('output' => array('text' => "Sorry, I can't talk right now. Try again later."));
        }
    }

    public static function reset_api_usage() {
        delete_option('watsonconv_total_requests');
    }

    public static function record_api_usage() {
        update_option(
            'watsonconv_total_requests',
            get_option('watsonconv_total_requests', 0) +
                get_transient('watsonconv_total_requests') ?: 0
        );

        delete_transient('watsonconv_total_requests');
    }

    public static function init_rate_limit() {
        self::uninit_rate_limit();
        wp_schedule_event(time(), 'minutely', 'watson_save_to_disk');
        wp_schedule_event(time(), get_option('watsonconv_interval', 'monthly'), 'watson_reset_api_usage');
    }

    public static function uninit_rate_limit() {
        wp_clear_scheduled_hook('watson_save_to_disk');
        wp_clear_scheduled_hook('watson_reset_api_usage');
    }

    public static function add_cron_schedules($schedules) {
      $schedules['monthly'] = array('interval' => MONTH_IN_SECONDS, 'display' => 'Once every month');
      $schedules['weekly'] = array('interval' => WEEK_IN_SECONDS, 'display' => 'Once every week');
      $schedules['minutely'] = array('interval' => MINUTE_IN_SECONDS, 'display' => 'Once every minute');
      return $schedules;
    }
}
