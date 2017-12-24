<?php
namespace WatsonConv;

add_action('wp_enqueue_scripts', array('WatsonConv\Frontend', 'render_chat_box'));
add_action('wp_footer', array('WatsonConv\Frontend', 'render_div'));

class Frontend {
    public static function enqueue_styles($full_screen = null) {
        wp_enqueue_style('watsonconv-chatbox', WATSON_CONV_URL.'css/chatbox.css', array('dashicons'));

        $font_size = get_option('watsonconv_font_size', 11);
        $color_rgb = sscanf(get_option('watsonconv_color', '#23282d'), "#%02x%02x%02x");
        $messages_height = get_option('watsonconv_size', 200);
        $position = explode('_', get_option('watsonconv_position', 'bottom_right'));
        $text_color = self::luminance($color_rgb) > 0.5 ? 'black' : 'white';

        if (is_null($full_screen)) {
            $full_screen = get_option('watsonconv_full_screen', 'no') == 'yes';
        }

        wp_add_inline_style('watsonconv-chatbox', '
            :root {
                --chatbot-color: ' . vsprintf('rgb(%d, %d, %d)', $color_rgb) . ';
                --chatbot-text-color: '.$text_color.';
            }
        
            #watson-fab-float
            {
                '.$position[0].': 5vmin;
                '.$position[1].': 5vmin;
            }

            #watson-box .watson-font
            {
                font-size: '.$font_size.'pt;
            }

            #watson-float
            {
                '.$position[0].': 5vmin;
                '.$position[1].': 5vmin;
            }
            #watson-box
            {
                width: '.(0.825*$messages_height + 4.2*$font_size).'pt;
                height: auto;
            }
            #message-container
            {
                height: '.$messages_height.'pt
            }
            
            @media (max-width:768px)  {
                #watson-box .watson-font
                {
                    font-size: 16px;
                }
            }' . 
            sprintf(
                $full_screen ? '%s' : '@media (max-width:768px) { %s }', 
                '#watson-box
                {
                  width: 100%;
                  height: 100%;
                }
              
                #watson-float
                {
                  top: 0;
                  right: 0;
                  bottom: 0;
                  left: 0;
                }

                #message-container
                {
                    height: auto;
                }
              
                #watson-box .message-form
                {
                  width: 100vw;
                }'
            )
        );
    }

    private static function luminance($srgb) {
        $lin_rgb = array_map(function($val) {
            $val /= 255;

            if ($val <= 0.03928) {
                return $val / 12.92;
            } else {
                return pow(($val + 0.055) / 1.055, 2.4);
            }
        }, $srgb);

        return 0.2126 * $lin_rgb[0] + 0.7152 * $lin_rgb[1] + 0.0722 * $lin_rgb[2];
    }

    public static function render_chat_box() {
        $ip_addr = API::get_client_ip();

        $page_selected =
            get_option('watsonconv_show_on', 'all') == 'all' ||
            (is_front_page() && get_option('watsonconv_home_page', 'false') == 'true') ||
            is_page(get_option('watsonconv_pages', array(-1))) ||
            is_single(get_option('watsonconv_posts', array(-1))) ||
            in_category(get_option('watsonconv_categories', array(-1)));

        $total_requests = get_option('watsonconv_total_requests', 0) +
            get_transient('watsonconv_total_requests') ?: 0;
        $client_requests = get_option("watsonconv_requests_$ip_addr", 0) +
            get_transient("watsonconv_requests_$ip_addr") ?: 0;

        $credentials = get_option('watsonconv_credentials');

        if ($page_selected &&
            (get_option('watsonconv_use_limit', 'no') == 'no' ||
                $total_requests < get_option('watsonconv_limit', 10000)) &&
            (get_option('watsonconv_use_client_limit', 'no') == 'no' ||
                $client_requests < get_option('watsonconv_client_limit', 100)) &&
            !empty($credentials)) {

            Frontend::enqueue_styles();

            $twilio_config = get_option('watsonconv_twilio');

            $call_configured = boolval(
                !empty($twilio_config['sid']) && 
                !empty($twilio_config['auth_token']) && 
                get_option('watsonconv_twiml_sid') &&
                get_option('watsonconv_call_id') &&
                get_option('watsonconv_call_recipient')
            );

            $settings = array(
                'delay' => (int) get_option('watsonconv_delay', 0),
                'minimized' => get_option('watsonconv_minimized', 'no'),
                'position' => explode('_', get_option('watsonconv_position', 'bottom_right')),
                'title' => get_option('watsonconv_title', ''),
                'full_screen' => get_option('watsonconv_full_screen', 'no'),
                'call_config' => array(
                    'use_twilio' => get_option('watsonconv_use_twilio', 'no'),
                    'configured' => $call_configured,
                    'recipient' => get_option('watsonconv_call_recipient'),
                    'call_tooltip' => get_option('watsonconv_call_tooltip'),
                    'call_button' => get_option('watsonconv_call_button'),
                    'calling_text' => get_option('watsonconv_calling_text')
                )
            );

            if ($settings['call_config']['use_twilio'] == 'yes' && $call_configured) {
                wp_enqueue_script('twilio-js', 'https://media.twiliocdn.com/sdk/js/client/v1.4/twilio.min.js');
            }

            wp_enqueue_script('chat-app', WATSON_CONV_URL.'app.js', array(), false, true);
            wp_localize_script('chat-app', 'settings', $settings);
        }
    }

    public static function render_div() {
        ?>
            <div id="watsonconv-chat-box"></div>
        <?php
    }
}
