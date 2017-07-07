<?php
namespace WatsonConv;

add_action('wp_enqueue_scripts', array('WatsonConv\Frontend', 'load_styles'));
add_action('wp_footer', array('WatsonConv\Frontend', 'render_chat_box'));

class Frontend {
    public static function load_styles() {
        wp_enqueue_style('watsonconv-chatbox', WATSON_CONV_URL.'css/chatbox.css', array('dashicons'));

        $font_size = get_option('watsonconv_font_size', 11);
        $color = get_option('watsonconv_color', '#23282d');
        $messages_height = get_option('watsonconv_size', 200);

        $position = explode('_', get_option('watsonconv_position', 'bottom_right'));

        $text_color = self::luminance($color) > 0.5 ? 'black' : 'white';

        wp_add_inline_style('watsonconv-chatbox', '
            #watson-fab-float
            {
                '.$position[0].': 5vmin;
                '.$position[1].': 5vmin;
            }
            #watson-fab
            {
                background-color: '.$color.';
                color: '.$text_color.';
            }
            #watson-float
            {
                '.$position[0].': 5vmin;
                '.$position[1].': 5vmin;
            }
            #watson-box
            {
                width: '.(0.825*$messages_height + 4.2*$font_size).'pt;
            }
            #watson-box .watson-font
            {
                font-size: '.$font_size.'pt;
            }
            #watson-box #watson-header, #message-container #messages .watson-message
            {
                background-color: '.$color.';
                color: '.$text_color.';
            }
            #message-container
            {
                height: '.$messages_height.'pt
            }
        ');
    }

    private static function luminance($hex) {
        $rgb = array_map(function($val) {
            $val /= 255;

            if ($val <= 0.03928) {
                return $val / 12.92;
            } else {
                return pow(($val + 0.055) / 1.055, 2.4);
            }
        }, sscanf($hex, "#%02x%02x%02x"));

        return 0.2126 * $rgb[0] + 0.7152 * $rgb[1] + 0.0722 * $rgb[2];
    }

    public static function render_chat_box() {
        $ip_addr = API::get_client_ip();

        $page_selected =
            (is_home() && get_option('watsonconv_home_page', 'false') == 'true') ||
            is_page(get_option('watsonconv_pages', -1)) ||
            is_single(get_option('watsonconv_posts', -1)) ||
            in_category(get_option('watsonconv_categories', -1));

        $total_requests = get_option('watsonconv_total_requests', 0) +
            get_transient('watsonconv_total_requests') ?: 0;
        $client_requests = get_option("watsonconv_requests_$ip_addr", 0) +
            get_transient("watsonconv_requests_$ip_addr") ?: 0;

        if ($page_selected == (get_option('watsonconv_show_on', 'only') == 'only') &&
            (get_option('watsonconv_use_limit', 'no') == 'no' ||
                $total_requests < get_option('watsonconv_limit', 10000)) &&
            (get_option('watsonconv_use_client_limit', 'no') == 'no' ||
                $client_requests < get_option('watsonconv_client_limit', 100)) &&
            !empty(get_option('watsonconv_credentials'))) {
        ?>
            <div id="chat-box"></div>
        <?php
            $settings = array(
                'delay' => (int) get_option('watsonconv_delay', 0),
                'minimized' => get_option('watsonconv_minimized', 'no') == 'yes',
                'position' => explode('_', get_option('watsonconv_position', 'bottom_right')),
                'title' => get_option('watsonconv_title', '')
            );

            wp_enqueue_script('chat-app', WATSON_CONV_URL.'app.js');
            wp_localize_script('chat-app', 'settings', $settings);
        }
    }
}
