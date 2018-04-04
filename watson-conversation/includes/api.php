<?php
namespace WatsonConv;

register_activation_hook(WATSON_CONV_FILE, array('WatsonConv\API', 'init_rate_limit'));
register_deactivation_hook(WATSON_CONV_FILE, array('WatsonConv\API', 'uninit_rate_limit'));

add_action('watson_save_to_disk', array('WatsonConv\API', 'record_api_usage'));
add_action('watson_reset_total_usage', array('WatsonConv\API', 'reset_total_usage'));
add_action('watson_reset_client_usage', array('WatsonConv\API', 'reset_client_usage'));
add_action('rest_api_init', array('WatsonConv\API', 'register_routes'));
add_action('update_option_watsonconv_interval', array('WatsonConv\API', 'init_rate_limit'));
add_action('update_option_watsonconv_client_interval', array('WatsonConv\API', 'init_rate_limit'));
add_filter('cron_schedules', array('WatsonConv\API', 'add_cron_schedules'));

class API {
    const API_VERSION = '2017-04-21';

    public static function register_routes() {
        if (get_option('watsonconv_credentials')) {
            register_rest_route('watsonconv/v1', '/message',
                array(
                    'methods' => 'post',
                    'callback' => array(__CLASS__, 'route_request')
                )
            );
        }

        $twilio_config = get_option('watsonconv_twilio');

        if (!empty($twilio_config['sid']) && 
            !empty($twilio_config['auth_token']) && 
            get_option('watsonconv_twiml_sid') &&
            get_option('watsonconv_call_id') &&
            get_option('watsonconv_call_recipient'))
        {
            register_rest_route('watsonconv/v1', '/twilio-token',
                array(
                    'methods' => 'get',
                    'callback' => array(__CLASS__, 'twilio_get_token')
                )
            );

            register_rest_route('watsonconv/v1', '/twilio-call',
                array(
                    'methods' => 'post',
                    'callback' => array(__CLASS__, 'twilio_call')
                )
            );
        }
    }

    public static function twilio_get_token(\WP_REST_Request $request) {
        $twilio_config = get_option('watsonconv_twilio');
        
        $TWILIO_ACCOUNT_SID = $twilio_config['sid'];
        $TWILIO_AUTH_TOKEN = $twilio_config['auth_token'];
        $TWILIO_TWIML_APP_SID = get_option('watsonconv_twiml_sid');

        $capability = new \Twilio\Jwt\ClientToken($TWILIO_ACCOUNT_SID, $TWILIO_AUTH_TOKEN);
        $capability->allowClientOutgoing($TWILIO_TWIML_APP_SID);
        $token = $capability->generateToken();
        return array(
            'identity' => $identity,
            'token' => $token,
        );
    }

    public static function twilio_call(\WP_REST_Request $request) {
        $response = new \Twilio\Twiml;
        
        $number = get_option('watsonconv_call_recipient');
        $dial = $response->dial(array('callerId' => get_option('watsonconv_call_id')));
        
        // wrap the phone number or client name in the appropriate TwiML verb
        // by checking if the number given has only digits and format symbols
        if (preg_match("/^[\d\+\-\(\) ]+$/", $number)) {
            $dial->number($number);
        } else {
            $dial->client($number);
        }

        echo header('Content-Type: text/xml');
        echo $response;
        die();
    }

    public static function route_request(\WP_REST_Request $request) {
        $ip_addr = self::get_client_ip();
        $body = $request->get_json_params();

        $total_requests = get_option('watsonconv_total_requests', 0) +
            get_transient('watsonconv_total_requests') ?: 0;
        $client_requests = get_option("watsonconv_requests_$ip_addr", 0) +
            get_transient("watsonconv_requests_$ip_addr") ?: 0;

        if ((get_option('watsonconv_use_limit', 'no') == 'no' ||
                $total_requests < get_option('watsonconv_limit', 10000)) &&
            (get_option('watsonconv_use_client_limit', 'no') == 'no' ||
                $client_requests < get_option('watsonconv_client_limit', 100)))
        {
            set_transient(
                'watsonconv_total_requests',
                (get_transient('watsonconv_total_requests') ?: 0) + 1,
                DAY_IN_SECONDS
            );
            set_transient(
                "watsonconv_requests_$ip_addr",
                (get_transient("watsonconv_requests_$ip_addr") ?: 0) + 1,
                DAY_IN_SECONDS
            );

            $client_list = get_transient('watsonconv_client_list', array());
            $client_list[$ip_addr] = true;
            set_transient('watsonconv_client_list', $client_list, DAY_IN_SECONDS);

            $credentials = get_option('watsonconv_credentials');

            if (empty($credentials)) {
                return new \WP_Error(
                    'config_error',
                    'Service not configured.',
                    503
                );
            }

            $auth_token = 'Basic ' . base64_encode(
                $credentials['username'].':'.
                $credentials['password']);

            $send_body = apply_filters(
                'watsonconv_user_message',
                array( 
                    'input' => empty($body['input']) ? new \stdClass() : $body['input'], 
                    'context' => empty($body['context']) ? new \stdClass() : $body['context']
                )
            );
            
            do_action('watsonconv_message_pre_send', $send_body);

            $response = wp_remote_post(
                $credentials['workspace_url'].'?version='.self::API_VERSION,
                array(
                    'timeout' => 20,
                    'headers' => array(
                        'Authorization' => $auth_token,
                        'Content-Type' => 'application/json'
                    ),
                    'body' => json_encode($send_body)
                )
            );

            do_action('watsonconv_message_received', $response);

            $response_body = json_decode(wp_remote_retrieve_body($response), true);
            $response_code = wp_remote_retrieve_response_code($response);
            
            do_action('watsonconv_message_parsed', $response_body);

            $response_body = apply_filters('watsonconv_bot_message', $response_body);

            if ($response_code !== 200) {
                return new \WP_Error(
                    'watson_error',
                    $response_body,
                    empty($response_code) ? array() : array('status' => $response_code)
                );
            } else {
                return $response_body;
            }
        } else {
            return array('output' => array('text' => "Sorry, I can't talk right now. Try again later."));
        }
    }

    public static function reset_total_usage() {
        delete_option('watsonconv_total_requests');
    }

    public static function reset_client_usage() {
        foreach (get_transient('watsonconv_client_list', array()) as $client_id => $val) {
            delete_option("watsonconv_requests_$client_id");
        };

        delete_option('watsonconv_client_list');
    }

    public static function record_api_usage() {
        update_option(
            'watsonconv_total_requests',
            get_option('watsonconv_total_requests', 0) +
                get_transient('watsonconv_total_requests') ?: 0
        );

        delete_transient('watsonconv_total_requests');

        foreach (get_transient('watsonconv_client_list', array()) as $client_id => $val) {
            update_option(
                "watsonconv_requests_$client_id",
                get_option("watsonconv_requests_$client_id", 0) +
                    get_transient("watsonconv_requests_$client_id") ?: 0
            );

            delete_transient("watsonconv_requests_$client_id");
        };

        update_option(
            'watsonconv_client_list',
            get_option('watsonconv_client_list', array()) +
                get_transient('watsonconv_client_list') ?: array()
        );

        delete_transient('watsonconv_client_list');
    }

    public static function init_rate_limit() {
        self::uninit_rate_limit();
        wp_schedule_event(time(), 'minutely', 'watson_save_to_disk');
        wp_schedule_event(time(), get_option('watsonconv_interval', 'monthly'), 'watson_reset_total_usage');
        wp_schedule_event(time(), get_option('watsonconv_client_interval', 'monthly'), 'watson_reset_client_usage');
    }

    public static function uninit_rate_limit() {
        wp_clear_scheduled_hook('watson_save_to_disk');
        wp_clear_scheduled_hook('watson_reset_total_usage');
        wp_clear_scheduled_hook('watson_reset_client_usage');
    }

    public static function add_cron_schedules($schedules) {
      $schedules['monthly'] = array('interval' => MONTH_IN_SECONDS, 'display' => 'Once every month');
      $schedules['weekly'] = array('interval' => WEEK_IN_SECONDS, 'display' => 'Once every week');
      $schedules['minutely'] = array('interval' => MINUTE_IN_SECONDS, 'display' => 'Once every minute');
      return $schedules;
    }

    public static function get_client_ip() {
        $ip_addr = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ip_addr = $_SERVER['HTTP_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ip_addr = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_X_FORWARDED']))
            $ip_addr = $_SERVER['HTTP_X_FORWARDED'];
        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ip_addr = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_FORWARDED']))
            $ip_addr = $_SERVER['HTTP_FORWARDED'];
        else if(isset($_SERVER['REMOTE_ADDR']))
            $ip_addr = $_SERVER['REMOTE_ADDR'];

        return $ip_addr;
    }
}
