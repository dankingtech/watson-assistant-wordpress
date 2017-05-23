<?php
namespace WatsonConv;

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

    public static function route_request($request) {
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
    }
}
