<?php
namespace WatsonConv;

register_deactivation_hook(WATSON_CONV_FILE, array('WatsonConv\Settings', 'unregister'));
add_action('admin_menu', array('WatsonConv\Settings', 'init_page'));
add_action('admin_init', array('WatsonConv\Settings', 'init_settings'));
add_action('admin_enqueue_scripts', array('WatsonConv\Settings', 'init_scripts'));
add_action('after_plugin_row_'.WATSON_CONV_BASENAME, array('WatsonConv\Settings', 'render_notice'), 10, 3);
add_filter('plugin_action_links_'.WATSON_CONV_BASENAME, array('WatsonConv\Settings', 'add_links'));

add_action('plugins_loaded', array('WatsonConv\Settings', 'migrate_old_credentials'));
add_action('plugins_loaded', array('WatsonConv\Settings', 'migrate_old_show_on'));
add_action('plugins_loaded', array('WatsonConv\Settings', 'migrate_old_full_screen'));
add_action('upgrader_process_complete', array('WatsonConv\Settings', 'clear_css_cache'));

class Settings {
    const SLUG = 'watsonconv';

    public static function init_page() {
        add_options_page('Watson Assistant', 'Watson', 'manage_options',
            self::SLUG, array(__CLASS__, 'render_page'));
    }

    public static function init_settings() {
        self::init_main_setup_intro();
        self::init_workspace_settings();
        self::init_rate_limit_settings();
        self::init_client_rate_limit_settings();
        self::init_voice_call_intro();
        self::init_twilio_cred_settings();
        self::init_call_ui_settings();
        self::init_behaviour_settings();
        self::init_appearance_settings();
        self::init_context_var_settings();
    }

    public static function unregister() {
        unregister_setting(self::SLUG, 'watsonconv_id');
        unregister_setting(self::SLUG, 'watsonconv_username');
        unregister_setting(self::SLUG, 'watsonconv_password');
        unregister_setting(self::SLUG, 'watsonconv_delay');
    }

    public static function init_scripts($hook_suffix) {
        if ($hook_suffix == 'settings_page_'.self::SLUG) {
            wp_enqueue_style(
                'watsonconv-settings', 
                WATSON_CONV_URL.'css/settings.css', 
                array('wp-color-picker')
            );

            wp_enqueue_script(
                'settings-script', 
                WATSON_CONV_URL.'includes/settings.js',
                array('wp-color-picker', 'jquery-ui-tooltip')
            );

            Frontend::enqueue_styles(false);
        }
    }

    public static function render_notice($plugin_file, $plugin_data, $status) {
        $credentials = get_option('watsonconv_credentials');

        if (empty($credentials)) {
        ?>
            <tr class="active icon-settings"><td colspan=3>
                <div class="update-message notice inline notice-warning notice-alt"
                     style="padding:0.5em; padding-left:1em; margin:0">
                    <span style='color:orange; margin-right:0.3em'
                          class='dashicons dashicons-admin-settings'></span>
                    <a href="options-general.php?page=<?php echo self::SLUG ?>">
                        <?php esc_html_e('Please fill in your Watson Assistant Workspace Credentials.', self::SLUG) ?>
                    </a>
                </div>
            </td></tr>
        <?php
        }
    }

    public static function add_links($links) {
            $settings_link = '<a href="options-general.php?page='.self::SLUG.'">'
                . esc_html__('Settings', self::SLUG) . '</a>';

            $learn_link = '<a href="https://cocl.us/build-a-chatbot" target="_blank">'
                . esc_html__('Learn', self::SLUG) . '</a>';

            return array($learn_link, $settings_link) + $links;
    }

    public static function render_page() {
    ?>
        <div class="wrap" style="max-width: 95em">
            <h2><?php esc_html_e('Watson Assistant Settings', self::SLUG); ?></h2>
            
            <div class="notice notice-info is-dismissible">
                <p><?php esc_html_e('
                    Want to make money building chatbots for clients? Become an IBM Partner, registration is quick and free!
                    Get one year of Watson Assistant and 100,000 API calls, 10 workspaces or chatbots, 200 intents and 200 entities as your free starting bonus.'
                , self::SLUG); ?></p>
                <a
                    class='button button-primary' 
                    style='margin-bottom: 0.5em' 
                    href='https://cocl.us/CB0103EN_WATR_WPP' 
                    target="_blank"
                >
                    Become a Partner
                </a>
            </div>

            <h2 class="nav-tab-wrapper">
                <a onClick="switch_tab('intro')" class="nav-tab nav-tab-active intro_tab">Introduction</a>
                <a onClick="switch_tab('workspace')" class="nav-tab workspace_tab">Main Setup</a>
                <a onClick="switch_tab('advanced')" class="nav-tab advanced_tab">Advanced</a>
                <a onClick="switch_tab('voice_call')" class="nav-tab voice_call_tab">Voice Calling</a>
                <a onClick="switch_tab('usage_management')" class="nav-tab usage_management_tab">Usage Management</a>
                <a onClick="switch_tab('behaviour')" class="nav-tab behaviour_tab">Behaviour</a>
                <a onClick="switch_tab('appearance')" class="nav-tab appearance_tab">Appearance</a>
                <a onClick="switch_tab('context_var')" class="nav-tab context_var_tab">Context Variables</a>
            </h2>

            <form action="options.php" method="POST">
                <div class="tab-page intro_page"><?php self::render_intro(); ?></div>
                <div class="tab-page advanced_page" style="display: none"><?php self::render_advanced(); ?></div>
                <?php
                    settings_fields(self::SLUG); 

                    ?> 
                        <div class="tab-page workspace_page" style="display: none">
                            <?php do_settings_sections('watsonconv_workspace') ?>
                        </div>
                        <div class="tab-page voice_call_page" style="display: none">
                            <?php do_settings_sections('watsonconv_voice_call') ?>
                        </div>
                        <div class="tab-page usage_management_page" style="display: none">
                            <?php do_settings_sections('watsonconv_usage_management') ?>
                        </div>
                        <div class="tab-page behaviour_page" style="display: none">
                            <?php do_settings_sections('watsonconv_behaviour') ?>
                        </div>
                        <div class="tab-page appearance_page" style="display: none">
                            <?php do_settings_sections('watsonconv_appearance') ?>
                        </div>
                        <div class="tab-page context_var_page" style="display: none">
                            <?php self::context_var_description() ?>
                            <hr>
                            <table width='100%'>
                                <tr>
                                    <td class="responsive">
                                        <h2>Enter Context Variable Labels Here</h2>
                                        <p>
                                            Enter your desired labels in the text boxes. Next to the 
                                            text boxes, you can see the corresponding values of the 
                                            fields which you have set in your Wordpress profile, as
                                            an example of the information that will be provided to the 
                                            chatbot.
                                        </p>
                                        <table class='form-table'>
                                            <?php do_settings_fields('watsonconv_context_var', 'watsonconv_context_var') ?>
                                        </table>
                                    </td>
                                    <td id='context-var-image' class="responsive">
                                        <img 
                                            class="drop-shadow" 
                                            style="width: 40em" 
                                            src="<?php echo WATSON_CONV_URL ?>/img/context_var.jpg"
                                        >
                                    </td>
                                </tr>
                            </table>
                        </div>
                    <?php
                ?>

                <input type="hidden" value="" name="watsonconv_css_cache" />

                <?php submit_button(); ?>
                <p class="update-message notice inline notice-warning notice-alt"
                style="padding-top: 0.5em; padding-bottom: 0.5em">
                    <b>Note:</b> If you have a server-side caching plugin installed such as
                    WP Super Cache, you may need to clear your cache after changing settings or
                    deactivating the plugin. Otherwise, your action may not take effect.
                <p>
            </form>
        </div>
    <?php
    }

    public static function render_intro() {
    ?>
        <p>
            Watson Assistant, formerly known as Watson Conversation, is a chatbot service. This is
            one of many AI services offered by IBM to help integrate cognitive computing into your 
            applications. With the use of this plugin, you can easily add chatbots to your website 
            created using the Watson Assistant service. The instructions below will help you get started:

            <h4>Building Your Chatbot</h4>
            <ol>
                <li><p>
                    Learn how to set up your Watson Assistant chatbot with 
                    <a href="https://cocl.us/build-a-chatbot" rel="nofollow" target="_blank">this quick free course</a>.
                </p></li>
                <li><p>
                    <a href="https://cocl.us/bluemix-registration" rel="nofollow" target="_blank">
                        Sign up for a free IBM Cloud Lite account.</a>
                </p></li>
                <li><p>
                    You can see 
                    <a href="https://cocl.us/watson-conversation-help" rel="nofollow" target="_blank">
                        the Watson Assistant documentation</a>
                    for more information.
                </p></li>
            </ol>
            <p>
                Once you've created your workspace using the course or the link above, 
                you must connect it to your Wordpress site.
            </p>
            <h4>Configuring the Plugin</h4>
            <ol>
                <li><p>
                    From the Deploy tab of your workspace, you must obtain your username and password
                    credentials in addition to the Workspace URL of your new workspace.
                </p></li>
                <li><p>
                    Enter these  on the "Main Setup" tab of this settings page. Once you click 
                    "Save Changes", the plugin will verify if the credentials are valid and notify 
                    you of whether or not the configuration was successful. 
                </p></li>
                <li><p>
                    (Optional) By default, the chatbot shows up on all pages of your website.
                    In the Behaviour tab, you can choose which pages to show the chat bot on.
                    You can also show the chat box inline within posts and pages using the shortcode
                    <b>[watson-chat-box]</b>.
                </p></li>
            </ol>
        </p>
    <?php
    }

    public static function render_advanced() {
    ?>
    <div class="wrap" style="max-width: 60em; ">
        <p><?php esc_html_e('This page contains information on advanced features supported by this plugin.
            If you have not yet created your chatbot, you should see the Introduction tab first.'); ?></p>
        <h2> <?php esc_html_e('Preset Response Options'); ?></h2>
        <p><?php esc_html_e('Using this feature, you can create predefined message buttons that users can use to
            quickly and easily respond to messages from your chatbot as shown here.'); ?></p>
        <img class="drop-shadow" style="height: 24em" src="<?php echo WATSON_CONV_URL ?>/img/options_instructions/result.png">
        <p><?php esc_html_e('The following instructions will guide you through the process of using this feature.') ?></p>

        <h4><?php esc_html_e('1. Open your chatbot workspace in Watson Assistant and go to the Dialog tab.') ?>
        <h4><?php esc_html_e('2. Select the node you want to create predefined messages for.') ?></h4>
        <img class="drop-shadow" style="width: 60em" src="<?php echo WATSON_CONV_URL ?>/img/options_instructions/2_full_page_highlighted.jpg">
        <h4><?php esc_html_e('3. Click the 3 dots at the top-right of this section to get the following dropdown.
            Click the "Open JSON Editor" button.') ?></h4>
        <img class="drop-shadow" style="width: 44em" src="<?php echo WATSON_CONV_URL ?>/img/options_instructions/4_json_dropdown.png">
        <h4><?php esc_html_e('A box should open up containing text resembling the picture below.') ?></h4>
        <img class="drop-shadow" style="width: 44em" src="<?php echo WATSON_CONV_URL ?>/img/options_instructions/5_json_initial.png">
        <h4><?php esc_html_e('4. Find the line containing "text". In this case it\'s line 3. You will
             notice this line has an opening curly bracket at the end.') ?></h4>
        <img class="drop-shadow" style="width: 44em" src="<?php echo WATSON_CONV_URL ?>/img/options_instructions/6_json_text_open.png">
        <h4><?php esc_html_e('5. Look below the word "text" to find the matching closing bracket.') ?></h4>
        <img class="drop-shadow" style="width: 44em" src="<?php echo WATSON_CONV_URL ?>/img/options_instructions/7_json_text_close.png">
        <h4><?php esc_html_e('6. Add the following text after this closing bracket. The empty line 
            under "options" is where you\'ll put your predefined messages.') ?></h4>
        <img class="drop-shadow" style="width: 44em" src="<?php echo WATSON_CONV_URL ?>/img/options_instructions/8_json_options_added.png">
        <h4><?php esc_html_e('7. Write your message options in the space below "options", with one message per line.
            Surround each message with double quotes and put a comma at the end of each line except for the last,
            as shown in the picture below.  ') ?></h4>
        <img class="drop-shadow" style="width: 44em" src="<?php echo WATSON_CONV_URL ?>/img/options_instructions/9_json_options_filled.png">
        <h4><?php esc_html_e('If done correctly, the chatbox on your Wordpress site should now show
            these response options as buttons like in the picture at the top of this page.') ?></h4>
    </div>
    <?php
    }

    // ----------------- Main Setup ---------------------

    public static function init_main_setup_intro() {
        $settings_page = self::SLUG . '_workspace';
        
        add_settings_section('watsonconv_main_setup_intro', '',
            array(__CLASS__, 'main_setup_description'), $settings_page);
    }

    public static function main_setup_description() {
    ?>
        <p>
            This page contains all the configuration you need to get your chatbot working.<br>
            Before you get these credentials, you need to set up a chatbot on your 
            <a href="https://cocl.us/bluemix-registration" rel="nofollow" target="_blank">free IBM Cloud account</a>.
            See the Introduction tab for details.
        </p>
    <?php
    } 

    // ------------ Workspace Credentials ---------------

    // If an installation of this plugin has a credentials format from the versions before 0.3.0,
    // migrate them to the new format.
    public static function migrate_old_credentials() {
        try {
            $credentials = get_option('watsonconv_credentials');

            if (!isset($credentials['workspace_url']) && isset($credentials['url']) && isset($credentials['id'])) {
                $credentials['workspace_url'] = 
                    rtrim($credentials['url'], '/').'/workspaces/'.$credentials['id'].'/message/';

                unset($credentials['url']);
                update_option('watsonconv_credentials', $credentials);
            }
        } catch (\Exception $e) {}
    }

    public static function init_workspace_settings() {
        $settings_page = self::SLUG . '_workspace';

        add_settings_section('watsonconv_workspace', 'Workspace Credentials',
            array(__CLASS__, 'workspace_description'), $settings_page);


        add_settings_field('watsonconv_enabled', '', array(__CLASS__, 'render_enabled'),
            $settings_page, 'watsonconv_workspace');
        add_settings_field('watsonconv_username', 'Username', array(__CLASS__, 'render_username'),
            $settings_page, 'watsonconv_workspace');
        add_settings_field('watsonconv_password', 'Password', array(__CLASS__, 'render_password'),
            $settings_page, 'watsonconv_workspace');
        add_settings_field('watsonconv_workspace_url', 'Workspace URL', array(__CLASS__, 'render_url'),
            $settings_page, 'watsonconv_workspace');

        register_setting(self::SLUG, 'watsonconv_enabled');
        register_setting(self::SLUG, 'watsonconv_credentials', array(__CLASS__, 'validate_credentials'));
    }

    public static function validate_credentials($credentials) {
        $old_credentials = get_option('watsonconv_credentials');

        if (!isset($credentials['enabled'])) {
            $old_credentials['enabled'] = 'false';
            return $old_credentials;
        }

        if (empty($credentials['workspace_url'])) {
            add_settings_error('watsonconv_credentials', 'invalid-id', 'Please enter a Workspace URL.');
            $empty = true;
        }
        if (empty($credentials['username'])) {
            add_settings_error('watsonconv_credentials', 'invalid-username', 'Please enter a username.');
            $empty = true;
        }
        if (empty($credentials['password'])) {
            add_settings_error('watsonconv_credentials', 'invalid-password', 'Please enter a password.');
            $empty = true;
        }

        if (isset($empty)) {
            return $old_credentials;
        }

        if ($credentials == $old_credentials) {
            return $credentials;
        }

        $auth_token = 'Basic ' . base64_encode(
            $credentials['username'].':'.
            $credentials['password']);

        $response = wp_remote_post(
            $credentials['workspace_url'].'?version='.API::API_VERSION,
            array(
                'timeout' => 20,
                'headers' => array(
                    'Authorization' => $auth_token,
                    'Content-Type' => 'application/json'
                ), 'body' => json_encode(array(
                    'input' => new \stdClass, 
                    'context' => new \stdClass()
                ))
            )
        );

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);

        $json_data = @json_decode($response_body);

        if (empty($response_body)) {
            $response_string = var_export($response, true);
        } else if (!is_null($json_data) && json_last_error() === JSON_ERROR_NONE) {
            $response_string = json_encode($json_data, defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : 128);
        } else if (is_array($response_body)) {
            $response_string = json_encode($response_body, defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : 128);
        } else if (is_string($response_body)) {
            $response_string = $response_body;
        } else {
            $response_string = var_export($response_body, true);
        }

        $response_string = str_replace('\\/', '/', $response_string);
        $response_code_string = empty($response_code) ? '' : ' ('.$response_code.')';

        $debug_info = '<a id="error_expand">Click here for debug information.</a>
            <pre id="error_response" style="display: none;">'.$response_string.'</pre>';

        if (is_wp_error($response)) {
            add_settings_error('watsonconv_credentials', 'invalid-credentials', 
                'Unable to connect to Watson server'.$response_code_string.'. ' . $debug_info);
            return get_option('watsonconv_credentials');
        } else if ($response_code == 401) {
            add_settings_error('watsonconv_credentials', 'invalid-credentials', 
                'Please ensure you entered a valid username/password and URL'.$response_code_string.'. ' . $debug_info);
            return get_option('watsonconv_credentials');
        } else if ($response_code == 404 || $response_code == 400) {
            add_settings_error('watsonconv_credentials', 'invalid-id', 
                'Please ensure you entered a valid workspace URL'.$response_code_string.'. ' . $debug_info);
            return get_option('watsonconv_credentials');
        } else if ($response_code != 200) {
            add_settings_error('watsonconv_credentials', 'invalid-url',
                'Please ensure you entered a valid workspace URL'.$response_code_string.'. ' . $debug_info);
            return get_option('watsonconv_credentials');
        }

        add_settings_error(
            'watsonconv_credentials', 
            'valid-credentials', 
            'Your chatbot has been successfully connected to your Wordpress site. <a href="'
                .get_site_url().'">Browse your website</a> to see it in action.', 
            'updated'
        );

        return $credentials;
    }

    public static function workspace_description($args) {
    ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>">
            <?php esc_html_e('Here, you can specify the Workspace ID for your Watson
                Assistant Workspace in addition to the required credentials.', self::SLUG) ?> <br />
            <?php esc_html_e('Note: These are not the same as your IBM Cloud Login Credentials.', self::SLUG) ?>
        </p>
    <?php
    }

    public static function render_enabled() {
        $credentials = get_option('watsonconv_credentials');
        $enabled = (isset($credentials['enabled']) ? $credentials['enabled'] : 'true') == 'true';
    ?>
        <fieldset>
            <input
                type="checkbox" id="watsonconv_enabled"
                name="watsonconv_credentials[enabled]"
                value="true"
                <?php echo $enabled ? 'checked' : '' ?>
            />
            <label for="watsonconv_enabled">
                Enable Chatbot
            </label>
        </fieldset>
    <?php
    }

    public static function render_username() {
        $credentials = get_option('watsonconv_credentials', array('username' => ''));
    ?>
        <input name="watsonconv_credentials[username]" class="watsonconv_credentials"
            id="watsonconv_username" type="text"
            value="<?php echo $credentials['username'] ?>"
            placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
            style="width: 24em"/>
    <?php
    }

    public static function render_password() {
        $credentials = get_option('watsonconv_credentials', array('password' => ''));
    ?>
        <input name="watsonconv_credentials[password]" class="watsonconv_credentials"
            id="watsonconv_password" type="password"
            value="<?php echo $credentials['password'] ?>"
            style="width: 8em" />
    <?php
    }

    public static function render_url() {
        $credentials = get_option('watsonconv_credentials', array('workpsace_url' => ''));
    ?>
        <input name="watsonconv_credentials[workspace_url]" class="watsonconv_credentials"
            id="watsonconv_workspace_url" type="text"
            value="<?php echo $credentials['workspace_url']; ?>"
            placeholder='https://gateway.watsonplatform.net/conversation/api/v1/workspaces/xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx/message/'
            style="width: 60em" />
    <?php
    }

    // ---------------- Rate Limiting -------------------

    public static function init_rate_limit_settings() {
        $settings_page = self::SLUG . '_usage_management';

        add_settings_section('watsonconv_rate_limit', 'Total Usage Management',
            array(__CLASS__, 'rate_limit_description'), $settings_page);

        $overage_title = sprintf(
            '<span href="#" title="%s">%s</span>', 
            esc_html__(
                'This is the message that will be given to users who are talking with your chatbot
                when the Maximum Number of Total Requests is exceeded. The chat box will disappear
                when the user navigates to a different page.'
                , self::SLUG
            ),
            esc_html__('Overage Message', self::SLUG)
        );

        add_settings_field('watsonconv_use_limit', 'Limit Total API Requests',
            array(__CLASS__, 'render_use_limit'), $settings_page, 'watsonconv_rate_limit');
        add_settings_field('watsonconv_limit', 'Maximum Number of Total Requests',
            array(__CLASS__, 'render_limit'), $settings_page, 'watsonconv_rate_limit');
        add_settings_field('watsonconv_limit_message', $overage_title,
            array(__CLASS__, 'render_limit_message'), $settings_page, 'watsonconv_rate_limit');

        register_setting(self::SLUG, 'watsonconv_use_limit');
        register_setting(self::SLUG, 'watsonconv_interval');
        register_setting(self::SLUG, 'watsonconv_limit');
        register_setting(self::SLUG, 'watsonconv_limit_message');
    }

    public static function rate_limit_description($args) {
    ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>">
            <p>
                <?php esc_html_e('
                    This section allows you to prevent overusage of your credentials by
                    limiting use of the chat bot.
                ', self::SLUG) ?>
            </p>
            <p>
                <?php esc_html_e("
                    If you have a paid plan for Watson
                    Assistant, then the amount you have to pay is directly related to the
                    number of API requests made. The number of API requests is equal to the
                    number of messages sent by users of your chat bot, in addition to the chatbot's initial greeting.
                ", self::SLUG) ?>
            </p>
            <p>
                <?php 
                    esc_html_e("
                        For example, the Standard plan charges $0.0025 per API call (one API call includes
                        one message sent by a user and its response from the chatbot). That means if 
                        visitors to your site send a total of 1000 messages in a month, you will be 
                        charged ($0.0025 per API call) x (1000 calls) = $2.50. If you want to limit the 
                        costs incurred by this chatbot, you can put a limit on the total number of API 
                        requests for a specific period of time here. However, it is recommended to regularly
                        check your API usage for Watson Assistant in your
                    ", self::SLUG);
                    printf(
                        ' <a href="https://console.bluemix.net/dashboard/apps" target="_blank">%s</a> ', 
                        esc_html__('IBM Cloud Console', self::SLUG)
                    );
                    esc_html_e("
                        as that is the most accurate measure.
                    ", self::SLUG);
                ?>
            </p>
        </p>
    <?php
    }

    public static function render_use_limit() {
        self::render_radio_buttons(
            'watsonconv_use_limit',
            'no',
            array(
                array(
                    'label' => esc_html__('Yes', self::SLUG),
                    'value' => 'yes'
                ), array(
                    'label' => esc_html__('No', self::SLUG),
                    'value' => 'no'
                )
            )
        );
    }

    public static function render_limit() {
        $limit = get_option('watsonconv_limit');
    ?>
        <input name="watsonconv_limit" id="watsonconv_limit" type="number"
            value="<?php echo empty($limit) ? 0 : $limit?>"
            style="width: 8em" />
        <select name="watsonconv_interval" id="watsonconv_interval">
            <option value="monthly" <?php selected(get_option('watsonconv_interval', 'monthly'), 'monthly')?>>
                Per Month
            </option>
            <option value="weekly" <?php selected(get_option('watsonconv_interval', 'monthly'), 'weekly')?>>
                Per Week
            </option>
            <option value="daily" <?php selected(get_option('watsonconv_interval', 'monthly'), 'daily')?>>
                Per Day
            </option>
            <option value="hourly" <?php selected(get_option('watsonconv_interval', 'monthly'), 'hourly')?>>
                Per Hour
            </option>
        </select>
    <?php
    }
    
    public static function render_limit_message() {
    ?>
        <input name="watsonconv_limit_message" id="watsonconv_limit_message" type="text"
            value="<?php echo get_option('watsonconv_limit_message', "Sorry, I can't talk right now. Try again later.") ?>"
            style="width: 40em" />
    <?php
    }

    // ---------- Rate Limiting Per Client --------------

    public static function init_client_rate_limit_settings() {
        $settings_page = self::SLUG . '_usage_management';

        add_settings_section('watsonconv_client_rate_limit', 'Usage Per Client',
            array(__CLASS__, 'client_rate_limit_description'), $settings_page);

        $overage_title = sprintf(
            '<span href="#" title="%s">%s</span>', 
            esc_html__(
                'This is the message that will be given to users who exceed the Maximum Number of
                Requests Per Client. The chat box will disappear when the user navigates to a 
                different page.'
                , self::SLUG
            ),
            esc_html__('Overage Message', self::SLUG)
        );

        add_settings_field('watsonconv_use_client_limit', 'Limit API Requests Per Client',
            array(__CLASS__, 'render_use_client_limit'), $settings_page, 'watsonconv_client_rate_limit');
        add_settings_field('watsonconv_client_limit', 'Maximum Number of Requests Per Client',
            array(__CLASS__, 'render_client_limit'), $settings_page, 'watsonconv_client_rate_limit');
        add_settings_field('watsonconv_client_limit_message', $overage_title,
            array(__CLASS__, 'render_client_limit_message'), $settings_page, 'watsonconv_client_rate_limit');

        register_setting(self::SLUG, 'watsonconv_use_client_limit');
        register_setting(self::SLUG, 'watsonconv_client_interval');
        register_setting(self::SLUG, 'watsonconv_client_limit');
        register_setting(self::SLUG, 'watsonconv_client_limit_message');
    }

    public static function render_use_client_limit() {
        self::render_radio_buttons(
            'watsonconv_use_client_limit',
            'no',
            array(
                array(
                    'label' => esc_html__('Yes', self::SLUG),
                    'value' => 'yes'
                ), array(
                    'label' => esc_html__('No', self::SLUG),
                    'value' => 'no'
                )
            )
        );
    }

    public static function client_rate_limit_description($args) {
    ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>">
            <?php esc_html_e('
                These settings allow you to control how many messages can be sent by each
                visitor to your site, rather than in total. This can help protect against
                a few visitors from using up too many messages and, therefore, preventing
                the rest of the visitors from having access to the chatbot.
            ', self::SLUG) ?>
            </a>
        </p>
    <?php
    }

    public static function render_client_limit() {
        $client_limit = get_option('watsonconv_client_limit');
    ?>
        <input name="watsonconv_client_limit" id="watsonconv_client_limit" type="number"
            value="<?php echo empty($client_limit) ? 0 : $client_limit ?>"
            style="width: 8em" />
        <select name="watsonconv_client_interval" id="watsonconv_client_interval">
            <option value="monthly" <?php selected(get_option('watsonconv_client_interval', 'monthly'), 'monthly')?>>
                Per Month
            </option>
            <option value="weekly" <?php selected(get_option('watsonconv_client_interval', 'monthly'), 'weekly')?>>
                Per Week
            </option>
            <option value="daily" <?php selected(get_option('watsonconv_client_interval', 'monthly'), 'daily')?>>
                Per Day
            </option>
            <option value="hourly" <?php selected(get_option('watsonconv_client_interval', 'monthly'), 'hourly')?>>
                Per Hour
            </option>
        </select>
    <?php
    }
    
    public static function render_client_limit_message() {
    ?>
        <input name="watsonconv_client_limit_message" id="watsonconv_client_limit_message" type="text"
            value="<?php echo get_option('watsonconv_client_limit_message', "Sorry, I can't talk right now. Try again later.") ?>"
            style="width: 40em" />
    <?php
    }

    // ------------- Voice Calling -------------------

    public static function init_voice_call_intro() {
        $settings_page = self::SLUG . '_voice_call';

        add_settings_section('watsonconv_voice_call_intro', 'What is Voice Calling?',
            array(__CLASS__, 'voice_call_description'), $settings_page);
        
        add_settings_field('watsonconv_call_recipient', 'Phone Number to Receive Calls from Users',
            array(__CLASS__, 'render_call_recipient'), $settings_page, 'watsonconv_voice_call_intro');
        add_settings_field('watsonconv_use_twilio', 'Use Voice Calling?',
            array(__CLASS__, 'render_use_twilio'), $settings_page, 'watsonconv_voice_call_intro');

        register_setting(self::SLUG, 'watsonconv_call_recipient', array(__CLASS__, 'validate_phone'));
        register_setting(self::SLUG, 'watsonconv_use_twilio');

    }

    public static function voice_call_description($args) {
    ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>">
            <?php esc_html_e('The Voice Calling feature essentially allows users to get in 
                touch with a real person on your team if they get tired of speaking with a chatbot.') ?> <br><br>
            <?php esc_html_e('If you input your phone number below, the user will have the option to call you.
                They can either do this by simply dialing
                your number on their phone, or you can enable the VOIP feature which allows the user to call
                you directly from their browser through their internet connection, with no toll. This is powered
                by a service called ') ?>
            <a href="http://cocl.us/what-is-twilio" target="_blank">Twilio</a>.
        </p>
    <?php
    }
    
    public static function render_call_recipient() {
    ?>
        <input name="watsonconv_call_recipient" id="watsonconv_call_recipient" type="text"
            value="<?php echo get_option('watsonconv_call_recipient') ?>"
            placeholder="+15555555555"
            style="width: 24em" />
    <?php
    }

    public static function render_use_twilio() {
        self::render_radio_buttons(
            'watsonconv_use_twilio',
            'no',
            array(
                array(
                    'label' => esc_html__('Yes', self::SLUG),
                    'value' => 'yes'
                ), array(
                    'label' => esc_html__('No', self::SLUG),
                    'value' => 'no'
                )
            )
        );
    }
    
    // ------------ Twilio Credentials ---------------

    public static function init_twilio_cred_settings() {
        $settings_page = self::SLUG . '_voice_call';

        add_settings_section('watsonconv_twilio_cred', '<span class="twilio_settings">Twilio Credentials</span>',
            array(__CLASS__, 'twilio_cred_description'), $settings_page);

        add_settings_field('watsonconv_twilo_sid', 'Account SID', array(__CLASS__, 'render_twilio_sid'),
            $settings_page, 'watsonconv_twilio_cred');
        add_settings_field('watsonconv_twilio_auth', 'Auth Token', array(__CLASS__, 'render_twilio_auth'),
            $settings_page, 'watsonconv_twilio_cred');
        add_settings_field('watsonconv_call_id', 'Caller ID (Verified Number with Twilio)',
            array(__CLASS__, 'render_call_id'), $settings_page, 'watsonconv_twilio_cred');
        add_settings_field('watsonconv_twilio_domain', 'Domain Name of this Website (Probably doesn\'t need changing)',
            array(__CLASS__, 'render_domain_name'), $settings_page, 'watsonconv_twilio_cred');

        register_setting(self::SLUG, 'watsonconv_twilio', array(__CLASS__, 'validate_twilio'));
        register_setting(self::SLUG, 'watsonconv_call_id', array(__CLASS__, 'validate_phone'));
    }

    public static function validate_twilio($new_config) {
        if (!empty($new_config['sid']) || !empty($new_config['auth_token'])) {
            $old_config = get_option('watsonconv_twilio');

            try {
                $client = new \Twilio\Rest\Client($new_config['sid'], $new_config['auth_token']);
                
                try {
                    $app = $client
                        ->applications(get_option('watsonconv_twiml_sid'))
                        ->fetch();
                } catch (\Twilio\Exceptions\RestException $e) {
                    $app = false;
                    $params = array('FriendlyName' => 'Chatbot for ' . $new_config['domain_name']);

                    foreach($client->account->applications->read($params) as $_app) {
                        $app = $_app;
                    }

                    if (!$app) {
                        $params = array('FriendlyName' => 'Chatbot for ' . $old_config['domain_name']);
        
                        foreach($client->account->applications->read($params) as $_app) {
                            $app = $_app;
                        }

                        if (!$app) {
                            $app = $client->applications->create('Chatbot for ' . $new_config['domain_name']);
                        }
                    }
                }

                $app->update(
                    array(
                        'voiceUrl' => $new_config['domain_name'] . '?rest_route=/watsonconv/v1/twilio-call',
                        'FriendlyName' => 'Chatbot for ' . $new_config['domain_name']
                    )
                );

                update_option('watsonconv_twiml_sid', $app->sid);
            } catch (\Exception $e) {
                add_settings_error(
                    'watsonconv_twilio', 
                    'twilio-invalid', 
                    $e->getMessage() . ' (' . $e->getCode() . ')'
                );
                
                return array(
                    'sid' => '',
                    'auth_token' => '',
                    'domain_name' => $old_config['domain_name']
                );
            }
        }

        return $new_config;
    }

    public static function validate_phone($number) {
        if (!empty($number) && !preg_match('/^\+?[1-9]\d{1,14}$/', $number)) {
            add_settings_error(
                'watsonconv_twilio', 
                'invalid-phone-number', 
                'Please use valid E.164 format for phone numbers (e.g. +15555555555).'
            );

            return '';
        }

        return $number;
    }

    public static function twilio_cred_description($args) {
    ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>" class="twilio_settings">
            <a href="http://cocl.us/try-twilio" target="_blank">
                <?php esc_html_e('Start by creating your free trial Twilio account here.')?>
            </a><br>
            <?php esc_html_e(' You can get your Account SID and Auth Token from your Twilio Dashboard.') ?> <br>
            <?php esc_html_e('For the caller ID, you can use a number that you\'ve either obtained from or') ?>
            <a href="https://www.twilio.com/console/phone-numbers/verified" target="_blank">
                <?php esc_html_e('verified with') ?>
            </a>
            <?php esc_html_e('Twilio.') ?> <br>
            <?php esc_html_e('Then just specify the phone number you want to answer the user\'s calls on 
                and you\'re good to go.') ?> <br>
            <?php esc_html_e('The Domain Name below is simply the domain name that Twilio will use 
                to reach your website. For most websites the default will work fine.', self::SLUG) ?> <br><br>
            <?php esc_html_e('Note: Phone numbers must be entered in E.164 format (e.g. +15555555555).') ?>
        </p>
    <?php
    }

    public static function render_twilio_sid() {
        $config = get_option('watsonconv_twilio');
        $sid = (empty($config) || empty($config['sid'])) ? '' : $config['sid'];
    ?>
        <input name="watsonconv_twilio[sid]" id="watsonconv_twilio_sid" type="text"
            value="<?php echo $sid ?>"
            placeholder="xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
            style="width: 24em" />
    <?php
    }

    public static function render_twilio_auth() {
        $config = get_option('watsonconv_twilio');
        $token = (empty($config) || empty($config['auth_token'])) ? '' : $config['auth_token'];
    ?>
        <input name="watsonconv_twilio[auth_token]" id="watsonconv_twilio_auth" type="password"
            value="<?php echo $token ?>"
            style="width: 24em"/>
    <?php
    }
    
    public static function render_call_id() {
    ?>
        <input name="watsonconv_call_id" id="watsonconv_call_id" type="text"
            value="<?php echo get_option('watsonconv_call_id') ?>"
            placeholder="+15555555555"
            style="width: 24em" />
    <?php
    }
    
    public static function render_domain_name() {
        $config = get_option('watsonconv_twilio');
        $domain_name = (empty($config) || empty($config['domain_name']))
            ? get_site_url() : $config['domain_name'];
    ?>
        <input name="watsonconv_twilio[domain_name]" id="watsonconv_twilio_domain" type="text"
            value="<?php echo $domain_name ?>"
            placeholder="<?php echo get_site_url() ?>"
            style="width: 24em" />
    <?php
    }
    
    // ------------ Voice Call UI Text ---------------

    public static function init_call_ui_settings() {
        $settings_page = self::SLUG . '_voice_call';

        add_settings_section('watsonconv_call_ui', '<span class="twilio_settings">Voice Call UI Text</span>',
            array(__CLASS__, 'twilio_call_ui_description'), $settings_page);

        add_settings_field('watsonconv_call_tooltip', 'This message will display when the user hovers over the phone button.', 
            array(__CLASS__, 'render_call_tooltip'), $settings_page, 'watsonconv_call_ui');
        add_settings_field('watsonconv_call_button', 'This is the text for the button to call using Twilio.',
            array(__CLASS__, 'render_call_button'), $settings_page, 'watsonconv_call_ui');
        add_settings_field('watsonconv_calling_text', 'This text is displayed when calling.',
            array(__CLASS__, 'render_calling_text'), $settings_page, 'watsonconv_call_ui');

        register_setting(self::SLUG, 'watsonconv_call_tooltip');
        register_setting(self::SLUG, 'watsonconv_call_button');
        register_setting(self::SLUG, 'watsonconv_calling_text');
    }

    public static function twilio_call_ui_description($args) {
    ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>" class="twilio_settings">
            <?php esc_html_e('Here, you can customize the text to be used in the voice calling 
                user interface.', self::SLUG) ?>
        </p>
    <?php
    }

    public static function render_call_tooltip() {
    ?>
        <input name="watsonconv_call_tooltip" id="watsonconv_call_tooltip" type="text"
            value="<?php echo get_option('watsonconv_call_tooltip') ?: 'Talk to a Live Agent' ?>"
            style="width: 24em" />
    <?php
    }

    public static function render_call_button() {
    ?>
        <input name="watsonconv_call_button" id="watsonconv_call_button" type="text"
            value="<?php echo get_option('watsonconv_call_button') ?: 'Start Toll-Free Call Here' ?>"
            style="width: 24em"/>
    <?php
    }
    
    public static function render_calling_text() {
    ?>
        <input name="watsonconv_calling_text" id="watsonconv_calling_text" type="text"
            value="<?php echo get_option('watsonconv_calling_text') ?: 'Calling Agent...' ?>"
            style="width: 24em"/>
    <?php
    }

    // ------------- Behaviour Settings ----------------

    public static function init_behaviour_settings() {
        $settings_page = self::SLUG . '_behaviour';

        add_settings_section('watsonconv_behaviour', '',
            array(__CLASS__, 'behaviour_description'), $settings_page);

        $delay_title = sprintf(
            '<span href="#" title="%s">%s</span>', 
            esc_html__(
                'When you use this setting, the chat box will wait for the chosen number of seconds
                before being displayed to the user.'
                , self::SLUG
            ),
            esc_html__('Delay Before Pop-Up', self::SLUG)
        );

        $show_on_title = sprintf(
            '<span href="#" title="%s">%s</span>', 
            esc_html__(
                'By default, the chat box pop-up will display on every page of your website.
                If you choose "Only Certain Pages", you can control which pages you want users
                to see your chat box on.', 
                self::SLUG
            ),
            esc_html__('Show Chat Box On:', self::SLUG)
        );

        $front_page_title = sprintf(
            '<span href="#" title="%s">%s</span>',
            esc_html__(
                'This is usually the first page users see when they visit your website.
                By default, this is a list of the latest posts on your website. However, this 
                can also be set to a static page in the Reading section of your Settings.', 
                self::SLUG
            ),
            esc_html__('Front Page', self::SLUG)
        );

        $pages_title = sprintf(
            '<span href="#" title="%s">%s</span>', 
            esc_html__(
                'Simply check the boxes next to the pages you want the floating chat box to display on.
                If you want the chat box to display on every page in this list, you can click the
                check box at the top next to "Select all Pages".', 
                self::SLUG
            ),
            esc_html__('Pages', self::SLUG)
        );

        $posts_title = sprintf(
            '<span href="#" title="%s">%s</span>', 
            esc_html__(
                'Simply check the boxes next to the posts you want the floating chat box to display on.
                If you want the chat box to display on every post in this list, you can click the
                check box at the top next to "Select all Posts".', 
                self::SLUG
            ),
            esc_html__('Posts', self::SLUG)
        );

        $cats_title = sprintf(
            '<span href="#" title="%s">%s</span>', 
            esc_html__(
                'Here, you can select which categories of posts you want to display the chat box on.
                The chat box will display on every post in the selected categories.', 
                self::SLUG
            ),
            esc_html__('Categories', self::SLUG)
        );

        add_settings_field('watsonconv_delay', $delay_title,
            array(__CLASS__, 'render_delay'), $settings_page, 'watsonconv_behaviour');

        add_settings_field('watsonconv_show_on', $show_on_title,
            array(__CLASS__, 'render_show_on'), $settings_page, 'watsonconv_behaviour');
        add_settings_field('watsonconv_home_page', $front_page_title,
            array(__CLASS__, 'render_home_page'), $settings_page, 'watsonconv_behaviour');
        add_settings_field('watsonconv_pages', $pages_title,
            array(__CLASS__, 'render_pages'), $settings_page, 'watsonconv_behaviour');
        add_settings_field('watsonconv_posts', $posts_title,
            array(__CLASS__, 'render_posts'), $settings_page, 'watsonconv_behaviour');
        add_settings_field('watsonconv_categories', $cats_title,
            array(__CLASS__, 'render_categories'), $settings_page, 'watsonconv_behaviour');

        register_setting(self::SLUG, 'watsonconv_delay');

        register_setting(self::SLUG, 'watsonconv_show_on');
        register_setting(self::SLUG, 'watsonconv_home_page');
        register_setting(self::SLUG, 'watsonconv_pages', array(__CLASS__, 'sanitize_array'));
        register_setting(self::SLUG, 'watsonconv_posts', array(__CLASS__, 'sanitize_array'));
        register_setting(self::SLUG, 'watsonconv_categories', array(__CLASS__, 'sanitize_array'));
    }
    
    public static function sanitize_array($val) {
        return empty($val) ? array(-1) : $val;
    }

    public static function behaviour_description($args) {
    ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>">
            <?php esc_html_e('This section allows you to customize
                how you want the chat box to behave. These settings display the chatbox as a
                floating box on the specified pages. If you want to show the chat box inline within 
                posts and pages, you can use the shortcode', self::SLUG) ?> <b>[watson-chat-box]</b>.
        </p>
    <?php
    }

    public static function render_delay() {
        $delay = get_option('watsonconv_delay');
    ?>
        <input name="watsonconv_delay" id="watsonconv_delay" type="number"
            value="<?php echo empty($delay) ? 0 : $delay?>"
            style="width: 4em" />
        seconds
    <?php
    }

    public static function render_show_on() {
        self::render_radio_buttons(
            'watsonconv_show_on',
            'all',
            array(
                array(
                    'label' => esc_html__('All Pages', self::SLUG),
                    'value' => 'all'
                ), array(
                    'label' => esc_html__('Only Certain Pages', self::SLUG),
                    'value' => 'only'
                )
            )
        );

    ?>
        <span class="show_on_only">
            <br>
            Please select which pages you want to display the chat box on from the options below:
        </span>
    <?php
    }

    public static function migrate_old_show_on() {
        try {
            $show_on = get_option('watsonconv_show_on');
            $home_page = get_option('watsonconv_home_page', 'false') == true;
            $pages = get_option('watsonconv_pages', array(-1));
            $posts = get_option('watsonconv_posts', array(-1));
            $cats = get_option('watsonconv_categories', array(-1));

            if ($show_on == 'all_except') {
                if (!$home_page && $pages == array(-1) && $posts == array(-1) && $cats == array(-1)) {
                    update_option('watsonconv_show_on', 'all');
                } else {
                    update_option('watsonconv_show_on', 'only');
                    update_option('watsonconv_home_page', $home_page ? 'false' : 'true');

                    update_option('watsonconv_pages', array_diff(
                            array_map(function($page) {return $page->ID;}, get_pages()),
                            $pages
                    ));

                    update_option('watsonconv_posts', array_diff(
                            array_map(function($post) {return $post->ID;}, get_posts()),
                            $posts
                    ));

                    update_option('watsonconv_categories', array_diff(
                            array_map(function($cat) {return $cat->cat_ID;}, get_categories(array('hide_empty' => 0))),
                            $cats
                    ));
                }
            }
        } catch (\Exception $e) {}
    }

    public static function render_home_page() {
    ?>
        <fieldset class="show_on_only">
            <input
                type="checkbox" id="watsonconv_home_page"
                name="watsonconv_home_page" value="true"
                <?php checked('true', get_option('watsonconv_home_page', 'false')) ?>
            />
            <label for="watsonconv_home_page">
                Front Page
            </label>
        </fieldset>
    <?php
    }

    public static function render_pages() {
    ?>
        <fieldset class="show_on_only" style="border: 1px solid black; padding: 1em">
            <legend>
                <input id="select_all_pages" type="checkbox"/>
                <label for="select_all_pages">Select All Pages</label>
            </legend>
            <?php
                $pages = get_pages(array(
                    'sort_column' => 'post_date',
                    'sort_order' => 'desc'
                ));
                $checked_pages = get_option('watsonconv_pages');

                foreach ($pages as $page) {
                ?>
                    <input
                        type="checkbox" id="pages_<?php echo $page->ID ?>"
                        name="watsonconv_pages[]" value="<?php echo $page->ID ?>"
                        <?php if (in_array($page->ID, (array)$checked_pages)): ?>
                            checked
                        <?php endif; ?>
                    />
                    <label for="pages_<?php echo $page->ID; ?>">
                        <?php echo $page->post_title ?>
                    </label>
                    <span style="float: right">
                        <?php echo $page->post_date ?>
                    </span>
                    <br>
                <?php
                }
            ?>
        </fieldset
    <?php
    }

    public static function render_posts() {
    ?>
        <fieldset class="show_on_only" style="border: 1px solid black; padding: 1em">
            <legend>
                <input id="select_all_posts" type="checkbox"/>
                <label for="select_all_posts">Select All Posts</label>
            </legend>
            <?php
                $posts = get_posts(array('order_by' => 'date'));
                $checked_posts = get_option('watsonconv_posts');

                foreach ($posts as $post) {
                ?>
                    <input
                        type="checkbox" id="posts_<?php echo $post->ID ?>"
                        name="watsonconv_posts[]" value="<?php echo $post->ID ?>"
                        <?php if (in_array($post->ID, (array)$checked_posts)): ?>
                            checked
                        <?php endif; ?>
                    />
                    <label for="posts_<?php echo $post->ID; ?>">
                        <?php echo $post->post_title ?>
                    </label>
                    <span style="float: right">
                        <?php echo $post->post_date ?>
                    </span>
                    <br>
                <?php
                }
            ?>
        </fieldset
    <?php
    }

    public static function render_categories() {
    ?>
        <fieldset class="show_on_only" style="border: 1px solid black; padding: 1em">
            <legend>
                <input id="select_all_cats" type="checkbox"/>
                <label for="select_all_cats">Select All Categories</label>
            </legend>
            <?php
                $cats = get_categories(array('hide_empty' => 0));
                $checked_cats = get_option('watsonconv_categories');

                foreach ($cats as $cat) {
                ?>
                    <input
                        type="checkbox" id="cats_<?php echo $cat->cat_ID ?>"
                        name="watsonconv_categories[]" value="<?php echo $cat->cat_ID ?>"
                        <?php if (in_array($cat->cat_ID, (array)$checked_cats)): ?>
                            checked
                        <?php endif; ?>
                    />
                    <label for="cats_<?php echo $cat->cat_ID ?>">
                        <?php echo $cat->cat_name ?>
                    </label>
                    <span style="float: right; margin-left: 4em">
                        <?php echo $cat->category_description ?>
                    </span>
                    <br>
                <?php
                }
            ?>
        </fieldset
    <?php
    }

    // ------------- Appearance Settings ----------------

    public static function init_appearance_settings() {
        $settings_page = self::SLUG . '_appearance';

        add_settings_section('watsonconv_appearance_chatbox', 'Chat Box',
            array(__CLASS__, 'appearance_chatbox_description'), $settings_page);
        add_settings_section('watsonconv_appearance_button', 'Chat Button',
            array(__CLASS__, 'appearance_fab_description'), $settings_page);

        // ---- Chat Box Appearance Section ------

        $minimized_title = sprintf(
            '<span href="#" title="%s">%s</span>', 
            esc_html__(
                'This setting only affects how the chat box appears to the user the first time they
                see it in a single browser session. On every page after the first one, the minimized
                state will be controlled by the user. If you want to force the chat box to be minimized
                on a specific page, you can add "chat_min=yes" to the end of the URL (without the quotes).'
                , self::SLUG
            ),
            esc_html__('Chat Box Minimized by Default', self::SLUG)
        );

        $full_screen_title = sprintf(
            '<span href="#" title="%s">%s</span>', 
            esc_html__(
                'Choosing "Always" causes the chat box to always display in full-screen mode. 
                On small devices, it can get hard to use the default draggable floating chat box.
                By checking the "Only Small Devices" option, you can keep the floating chat box
                for laptops and desktop computers while showing it in full screen mode for mobile
                devices with a smaller width. The "Never" option causes the floating chat box to
                always be used, though this can be difficult to use on small devices. Advanced users
                can also write their own custom CSS media query by choosing the last option.'
                , self::SLUG
            ),
            esc_html__('Full Screen', self::SLUG)
        );

        $position_title = sprintf(
            '<span href="#" title="%s">%s</span>', 
            esc_html__(
                'This setting determines which corner of the screen the floating chat box will appear
                in when the user first sees it. If the chat box isn\'t in full screen mode, the user
                can then drag it to a different position if they please.'
                , self::SLUG
            ),
            esc_html__('Position', self::SLUG)
        );

        $send_btn_title = sprintf(
            '<span href="#" title="%s">%s</span>', 
            esc_html__(
                'Users can send messages from the text box by pressing the "Enter" key on desktop, 
                or "Submit"/"Go" on mobile device keyboards. If you set this setting to "Yes", then 
                there will also be a button next to the text box to give the user another option for
                sending messages.'
                , self::SLUG
            ),
            esc_html__('Show Send Message Button', self::SLUG)
        );

        // Weird, I know
        $title_title = sprintf(
            '<span href="#" title="%s">%s</span>', 
            esc_html__(
                'This title appears at the top of the chat box, above the messages.'
                , self::SLUG
            ),
            esc_html__('Chat Box Title', self::SLUG)
        );

        $clear_text_title = sprintf(
            '<span href="#" title="%s">%s</span>', 
            esc_html__(
                'This is the tooltip for the button the user can click to clear the conversation 
                history and start over.'
                , self::SLUG
            ),
            esc_html__('"Clear Messages" Tooltip', self::SLUG)
        );

        $message_prompt_title = sprintf(
            '<span href="#" title="%s">%s</span>', 
            esc_html__(
                'This is the text that appears in the message text box to prompt the user to type a message.'
                , self::SLUG
            ),
            esc_html__('"Type Message" Prompt', self::SLUG)
        );

        $font_size_title = sprintf(
            '<span href="#" title="%s">%s</span>', 
            esc_html__(
                'This changes the font size of the title and messages in the chat box.'
                , self::SLUG
            ),
            esc_html__('Font Size', self::SLUG)
        );

        $font_size_fs_title = sprintf(
            '<span href="#" title="%s">%s</span>', 
            esc_html__(
                'This changes the font size when the chat box is displaying in full screen mode.'
                , self::SLUG
            ),
            esc_html__('Font Size in Full Screen', self::SLUG)
        );

        $color_title = sprintf(
            '<span href="#" title="%s">%s</span>', 
            esc_html__(
                'This changes the color of the chatbox header, and the background color of messages
                received by the user from the chatbot. If your version of Wordpress does not support
                the color picker, you will have to manually enter the color in hexadecimal format 
                prefixed with #. For example, white would be written as #ffffff or #FFFFFF, and black
                would be written as #000000.'
                , self::SLUG
            ),
            esc_html__('Color', self::SLUG)
        );

        $size_title = sprintf(
            '<span href="#" title="%s">%s</span>', 
            esc_html__(
                'This changes the size of the floating chat box window, allowing more space for the
                messages.'
                , self::SLUG
            ),
            esc_html__('Window Size', self::SLUG)
        );
        

        add_settings_field('watsonconv_minimized', $minimized_title,
            array(__CLASS__, 'render_minimized'), $settings_page, "watsonconv_appearance_chatbox");
        add_settings_field('watsonconv_full_screen', $full_screen_title,
            array(__CLASS__, 'render_full_screen'), $settings_page, 'watsonconv_appearance_chatbox');
        add_settings_field('watsonconv_position', $position_title,
            array(__CLASS__, 'render_position'), $settings_page, 'watsonconv_appearance_chatbox');
        add_settings_field('watsonconv_send_btn', $send_btn_title,
                array(__CLASS__, 'render_send_btn'), $settings_page, 'watsonconv_appearance_chatbox');
        add_settings_field('watsonconv_title', $title_title,
            array(__CLASS__, 'render_title'), $settings_page, 'watsonconv_appearance_chatbox');
        add_settings_field('watsonconv_clear_text', $clear_text_title,
            array(__CLASS__, 'render_clear_text'), $settings_page, 'watsonconv_appearance_chatbox');
        add_settings_field('watsonconv_message_prompt', $message_prompt_title,
            array(__CLASS__, 'render_message_prompt'), $settings_page, 'watsonconv_appearance_chatbox');
        add_settings_field('watsonconv_font_size', $font_size_title,
            array(__CLASS__, 'render_font_size'), $settings_page, 'watsonconv_appearance_chatbox');
        add_settings_field('watsonconv_font_size_fs', $font_size_fs_title,
            array(__CLASS__, 'render_font_size_fs'), $settings_page, 'watsonconv_appearance_chatbox');
        add_settings_field('watsonconv_color', $color_title,
            array(__CLASS__, 'render_color'), $settings_page, 'watsonconv_appearance_chatbox');
        add_settings_field('watsonconv_size', $size_title,
            array(__CLASS__, 'render_size'), $settings_page, 'watsonconv_appearance_chatbox');
        add_settings_field('watsonconv_chatbox_preview', esc_html__('Preview'),
            array(__CLASS__, 'render_chatbox_preview'), $settings_page, 'watsonconv_appearance_chatbox');

        // ---- FAB Appearance Section ------

        $fab_icon_pos_title = sprintf(
            '<span href="#" title="%s">%s</span>', 
            esc_html__(
                'If you want the chat button to have an icon and a text label, then you can specify whether
                you want the icon to be on the left of the text or the right. If there is no text,
                the position doesn\'t matter. Alternatively, you can hide the icon and just use text.'
                , self::SLUG
            ),
            esc_html__('Icon Position', self::SLUG)
        );

        $fab_text_title = sprintf(
            '<span href="#" title="%s">%s</span>', 
            esc_html__(
                'This is the label for the chat button that users click to open the chat box. This
                can be left blank if you like.'
                , self::SLUG
            ),
            esc_html__('Text Label', self::SLUG)
        );
        
        add_settings_field('watsonconv_fab_icon_pos', $fab_icon_pos_title,
            array(__CLASS__, 'render_fab_icon_pos'), $settings_page, 'watsonconv_appearance_button');
        add_settings_field('watsonconv_fab_text', $fab_text_title,
            array(__CLASS__, 'render_fab_text'), $settings_page, 'watsonconv_appearance_button');
        add_settings_field('watsonconv_fab_icon_size', esc_html__('Icon Size'),
            array(__CLASS__, 'render_fab_icon_size'), $settings_page, 'watsonconv_appearance_button');
        add_settings_field('watsonconv_fab_text_size', esc_html__('Text Size'),
            array(__CLASS__, 'render_fab_text_size'), $settings_page, 'watsonconv_appearance_button');
        add_settings_field('watsonconv_fab_preview', esc_html__('Preview'),
            array(__CLASS__, 'render_fab_preview'), $settings_page, 'watsonconv_appearance_button');

        register_setting(self::SLUG, 'watsonconv_minimized');
        register_setting(self::SLUG, 'watsonconv_full_screen', array(__CLASS__, 'parse_full_screen_settings'));
        register_setting(self::SLUG, 'watsonconv_position');
        register_setting(self::SLUG, 'watsonconv_send_btn');
        register_setting(self::SLUG, 'watsonconv_title');
        register_setting(self::SLUG, 'watsonconv_clear_text');
        register_setting(self::SLUG, 'watsonconv_message_prompt');
        register_setting(self::SLUG, 'watsonconv_font_size');
        register_setting(self::SLUG, 'watsonconv_font_size_fs');
        register_setting(self::SLUG, 'watsonconv_color', array(__CLASS__,  'validate_color'));
        register_setting(self::SLUG, 'watsonconv_size');

        register_setting(self::SLUG, 'watsonconv_fab_icon_pos');
        register_setting(self::SLUG, 'watsonconv_fab_text');
        register_setting(self::SLUG, 'watsonconv_fab_icon_size');
        register_setting(self::SLUG, 'watsonconv_fab_text_size');

        register_setting(self::SLUG, 'watsonconv_css_cache');
    }

    public static function appearance_chatbox_description($args) {
    ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>">
            <?php esc_html_e('This section allows you to specify how you want
                the chat box to appear to your site visitor.', self::SLUG) ?>
        </p>
    <?php
    }

    public static function clear_css_cache() {
        try {
            $current_plugin_path_name = plugin_basename( __FILE__ );

            if ($options['action'] == 'update' && $options['type'] == 'plugin' ) {
                foreach($options['plugins'] as $each_plugin){
                    if ($each_plugin == $current_plugin_path_name) {
                        delete_option('watsonconv_css_cache');
                    }
                }
            }
        } catch (\Exception $e) {}
    }

    public static function render_minimized() {
        self::render_radio_buttons(
            'watsonconv_minimized',
            'no',
            array(
                array(
                    'label' => esc_html__('Yes', self::SLUG),
                    'value' => 'yes'
                ), array(
                    'label' => esc_html__('No', self::SLUG),
                    'value' => 'no'
                )
            )
        );
    }

    public static function migrate_old_full_screen() {
        try {
            if (get_option('watsonconv_full_screen') == 'yes') {
                update_option(
                    'watsonconv_full_screen', 
                    array(
                        'mode' => 'all',
                        'max_width' => '640px',
                        'query' => '%s'
                    )
                );
            } else if (get_option('watsonconv_full_screen') == 'no') {
                update_option(
                    'watsonconv_full_screen', 
                    array(
                        'mode' => 'mobile',
                        'max_width' => '640px',
                        'query' => '@media screen and (max-width:640px) { %s }'
                    )
                );
            }
        } catch (\Exception $e) {}
    }

    public static function parse_full_screen_settings($settings) {
        if ($settings['mode'] == 'all') {
            $settings['query'] = '%s';
        } else if ($settings['mode'] == 'mobile') {
            $settings['query'] = '@media screen and (max-width:'.$settings['max_width'].') { %s }';
        } else if ($settings['mode'] == 'custom') {
            $settings['query'] = $settings['query'] . ' { %s }';
        } else {
            $settings['query'] = '';
        }

        return $settings;
    }
    
    public static function render_full_screen() {
        $settings = get_option('watsonconv_full_screen');

        $mode = isset($settings['mode']) ? $settings['mode'] : 'mobile';
        $max_width = isset($settings['max_width']) ? $settings['max_width'] : '640px';
        $query = isset($settings['query']) ? 
            substr($settings['query'], 0, -7) : 
            '@media screen and (max-width:640px)';

        ?>
            <input
                name="watsonconv_full_screen[mode]"
                id="watsonconv_full_screen_all"
                type="radio"
                value="all"
                <?php checked('all', $mode) ?>
            >
            <label for="watsonconv_full_screen_all">
                Always
            </label><br />

            <input
                name="watsonconv_full_screen[mode]"
                id="watsonconv_full_screen_mobile"
                type="radio"
                value="mobile"
                <?php checked('mobile', $mode) ?>
            >
            <label for="watsonconv_full_screen_mobile">
                Only Small Devices
            </label><br />
            <div id="watsonconv_full_screen_max_width">
                Maximum Width: 
                <input
                    name="watsonconv_full_screen[max_width]"
                    type="text"
                    value="<?php echo $max_width ?>"
                    style="width: 6em"
                >
            </div>

            <input
                name="watsonconv_full_screen[mode]"
                id="watsonconv_full_screen_never"
                type="radio"
                value="never"
                <?php checked('never', $mode) ?>
            >
            <label for="watsonconv_full_screen_never">
                Never (Not recommended)
            </label><br />
            
            <input
                name="watsonconv_full_screen[mode]"
                id="watsonconv_full_screen_custom"
                type="radio"
                value="custom"
                <?php checked('custom', $mode) ?>
            >
            <label for="watsonconv_full_screen_custom">
                Custom CSS query (Advanced)
            </label><br />
            <div id="watsonconv_full_screen_query">
                Query: 
                <input
                    name="watsonconv_full_screen[query]"
                    type="text"
                    value="<?php echo $query ?>"
                    style="width: 40em"
                >
            </div>
        <?php
    }

    public static function render_position() {
        $top_left_box =
            "<div class='preview-window'>
                <div class='preview-box' style='top: 1em; left: 1em'></div>
            </div>";

        $top_right_box =
            "<div class='preview-window'>
                <div class='preview-box' style='top: 1em; right: 1em'></div>
            </div>";

        $bottom_left_box =
            "<div class='preview-window'>
                <div class='preview-box' style='bottom: 1em; left: 1em'></div>
            </div>";

        $bottom_right_box =
            "<div class='preview-window'>
                <div class='preview-box' style='bottom: 1em; right: 1em'></div>
            </div>";

        self::render_radio_buttons(
            'watsonconv_position',
            'bottom_right',
            array(
                array(
                    'label' => esc_html__('Top-Left', self::SLUG) . $top_left_box,
                    'value' => 'top_left'
                ), array(
                    'label' => esc_html__('Top-Right', self::SLUG) . $top_right_box,
                    'value' => 'top_right'
                ), array(
                    'label' => esc_html__('Bottom-Left', self::SLUG) . $bottom_left_box,
                    'value' => 'bottom_left'
                ), array(
                    'label' => esc_html__('Bottom-Right', self::SLUG) . $bottom_right_box,
                    'value' => 'bottom_right'
                )
            ),
            'display: inline-block'
        );
    }

    public static function render_send_btn() {
        self::render_radio_buttons(
            'watsonconv_send_btn',
            'no',
            array(
                array(
                    'label' => esc_html__('Yes', self::SLUG),
                    'value' => 'yes'
                ), array(
                    'label' => esc_html__('No', self::SLUG),
                    'value' => 'no'
                )
            )
        );
    }

    public static function render_title() {
    ?>
        <input name="watsonconv_title" id="watsonconv_title"
            type="text" style="width: 16em"
            value="<?php echo get_option('watsonconv_title', '') ?>" />
    <?php
    }

    public static function render_clear_text() {
    ?>
        <input name="watsonconv_clear_text" id="watsonconv_clear_text"
            type="text" style="width: 16em"
            value="<?php echo get_option('watsonconv_clear_text', 'Clear Messages') ?>" />
    <?php
    }

    public static function render_message_prompt() {
    ?>
        <input name="watsonconv_message_prompt" id="watsonconv_message_prompt"
            type="text" style="width: 16em"
            value="<?php echo get_option('watsonconv_message_prompt', 'Type a Message') ?>" />
    <?php
    }

    public static function render_font_size() {
    ?>
        <input name="watsonconv_font_size" id="watsonconv_font_size"
            type="number" min=4 step=0.5 style="width: 4em"
            value="<?php echo get_option('watsonconv_font_size', 11) ?>" />
        pt
    <?php
    }

    public static function render_font_size_fs() {
    ?>
        <input name="watsonconv_font_size_fs" id="watsonconv_font_size_fs"
            type="number" min=4 step=0.5 style="width: 4em"
            value="<?php echo get_option('watsonconv_font_size_fs', 14) ?>" />
        pt
    <?php
    }

    public static function validate_color($val) {
        if (strlen($val) < 7 || count(sscanf($val, "#%02x%02x%02x")) !== 3) {
            add_settings_error('watsonconv_color', 'invalid-format', 
                'The color entered must be in 6-digit hexadecimal format prefixed with #. For example, 
                white would be written as #ffffff or #FFFFFF, and black would be written as #000000.');

            return get_option('watsonconv_color', '#23282d');
        }

        return $val;
    }

    public static function render_color() {
    ?>
        <input name="watsonconv_color" id="watsonconv_color"
            type="text" style="width: 6em"
            value="<?php echo get_option('watsonconv_color', '#23282d')?>" />
    <?php
    }

    public static function render_size() {
        self::render_radio_buttons(
            'watsonconv_size',
            200,
            array(
                array(
                    'label' => esc_html__('Small', self::SLUG),
                    'value' => 160
                ), array(
                    'label' => esc_html__('Medium', self::SLUG),
                    'value' => 200
                ), array(
                    'label' => esc_html__('Large', self::SLUG),
                    'value' => 240
                )
            )
        );
    }

    public static function render_chatbox_preview() {
        ?>
            <div id='watson-box' class='drop-shadow animated' style='display: block;'>
                <div id='watson-header' class='watson-font' style='cursor: default;'>
                    <span class='dashicons dashicons-arrow-down-alt2 header-button'></span>
                    <span class='dashicons dashicons-trash header-button'></span>
                    <span class='dashicons dashicons-phone header-button'></span>
                    <div id='watson-title' class='overflow-hidden' ><?php echo get_option('watsonconv_title', '') ?></div>
                </div>
                <div id='message-container'>
                    <div id='messages' class='watson-font'>
                        <div>
                            <div class='message watson-message'>
                                This is a message from the chatbot.
                            </div>
                        </div>
                        <div>
                            <div class='message user-message'>
                                This is a message from the user.
                            </div>
                        </div>
                        <div>
                            <div class='message watson-message'>
                                This message is a slightly longer message than the previous one from the chatbot.
                            </div>
                        </div>
                        <div>
                            <div class='message user-message'>
                                This message is a slightly longer message than the previous one from the user.
                            </div>
                        </div>
                    </div>
                </div>
                <div class='message-form watson-font'>
                    <input
                        id='watson-message-input'
                        class='message-input watson-font'
                        type='text'
                        placeholder='<?php echo get_option('watsonconv_message_prompt', 'Type a Message') ?>'
                        disabled='true'
                    />
                    <div id='message-send'>
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" 
                                viewBox="0 0 48 48" 
                                fill="white"
                            >
                                <path d="M4.02 42L46 24 4.02 6 4 20l30 4-30 4z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        <?php
        }

    public static function appearance_fab_description($args) {
    ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>">
            <?php esc_html_e('This section allows you to customize the appearance of the button
                the user clicks to access the chat box.', self::SLUG) ?>
        </p>
    <?php
    }

    public static function render_fab_icon_pos() {
        self::render_radio_buttons(
            'watsonconv_fab_icon_pos',
            'left',
            array(
                array(
                    'label' => esc_html__('Left of Text', self::SLUG),
                    'value' => 'left'
                ), array(
                    'label' => esc_html__('Right of Text', self::SLUG),
                    'value' => 'right'
                ), array(
                    'label' => esc_html__('Hide Icon', self::SLUG),
                    'value' => 'hide'
                )
            )
        );
    }

    public static function render_fab_text() {
    ?>
        <input name="watsonconv_fab_text" id="watsonconv_fab_text"
            type="text" style="width: 16em"
            value="<?php echo get_option('watsonconv_fab_text', '') ?>" />
    <?php
    }

    public static function render_fab_icon_size() {
    ?>
        <input name="watsonconv_fab_icon_size" id="watsonconv_fab_icon_size"
            type="number" min=4 step=0.5 style="width: 4em"
            value="<?php echo get_option('watsonconv_fab_icon_size', 28) ?>" />
        pt
    <?php
    }

    public static function render_fab_text_size() {
    ?>
        <input name="watsonconv_fab_text_size" id="watsonconv_fab_text_size"
            type="number" min=4 step=0.5 style="width: 4em"
            value="<?php echo get_option('watsonconv_fab_text_size', 15) ?>" />
        pt
    <?php
    }

    public static function render_fab_preview() {
    ?>
        <div id='watson-fab' class='drop-shadow animated-shadow' style='cursor: default;'>
            <span id='watson-fab-icon' class='fab-icon-left dashicons dashicons-format-chat' style='padding: 0;'></span>
            <span id='watson-fab-text' style='display: none; padding: 0;'>
                <?php echo get_option('watsonconv_fab_text') ?>
            </span>
            <span id='watson-fab-icon' class='fab-icon-right dashicons dashicons-format-chat' style='display: none; padding: 0;'></span>
        </div>
    <?php
    }

    private static function render_radio_buttons($option_name, $default_value, $options, $div_style = '') {
        foreach ($options as $option) {
        ?>
            <div style="<?php echo $div_style ?>" >
                <input
                    name=<?php echo $option_name ?>
                    id="<?php echo $option_name.'_'.$option['value'] ?>"
                    type="radio"
                    value="<?php echo $option['value'] ?>"
                    <?php checked($option['value'], get_option($option_name, $default_value)) ?>
                >
                <label for="<?php echo $option_name.'_'.$option['value'] ?>">
                    <?php echo $option['label'] ?>
                </label><br />
            </div>
        <?php
        }
    }
    
    // ---------- Context Variable Settings -------------
    
    private static function init_context_var_settings() {
        $settings_page = self::SLUG . '_context_var';

        add_settings_section('watsonconv_context_var', '',
            array(__CLASS__, 'context_var_description'), $settings_page);

        $first_name_title = sprintf(
            '<span href="#" title="%s">%s</span>', 
            esc_html__(
                'The first name of the user.'
                , self::SLUG
            ),
            esc_html__('First Name', self::SLUG)
        );

        $last_name_title = sprintf(
            '<span href="#" title="%s">%s</span>', 
            esc_html__(
                'The last name of the user.'
                , self::SLUG
            ),
            esc_html__('Last Name', self::SLUG)
        );

        $nickname_title = sprintf(
            '<span href="#" title="%s">%s</span>', 
            esc_html__(
                "The user's nickname."
                , self::SLUG
            ),
            esc_html__('Nickname', self::SLUG)
        );

        $email_title = sprintf(
            '<span href="#" title="%s">%s</span>', 
            esc_html__(
                "The user's email address."
                , self::SLUG
            ),
            esc_html__('Email Address', self::SLUG)
        );

        $login_title = sprintf(
            '<span href="#" title="%s">%s</span>', 
            esc_html__(
                "The user's login username."
                , self::SLUG
            ),
            esc_html__('Username', self::SLUG)
        );
        
        add_settings_field('watsonconv_fname_var', $first_name_title,
            array(__CLASS__, 'render_fname_var'), $settings_page, 'watsonconv_context_var');
        add_settings_field('watsonconv_lname_var', $last_name_title,
            array(__CLASS__, 'render_lname_var'), $settings_page, 'watsonconv_context_var');
        add_settings_field('watsonconv_nname_var', $nickname_title,
            array(__CLASS__, 'render_nname_var'), $settings_page, 'watsonconv_context_var');
        add_settings_field('watsonconv_email_var', $email_title,
            array(__CLASS__, 'render_email_var'), $settings_page, 'watsonconv_context_var');
        add_settings_field('watsonconv_login_var', $login_title,
            array(__CLASS__, 'render_login_var'), $settings_page, 'watsonconv_context_var');

        register_setting(self::SLUG, 'watsonconv_fname_var', array(__CLASS__, 'validate_context_var'));
        register_setting(self::SLUG, 'watsonconv_lname_var', array(__CLASS__, 'validate_context_var'));
        register_setting(self::SLUG, 'watsonconv_nname_var', array(__CLASS__, 'validate_context_var'));
        register_setting(self::SLUG, 'watsonconv_email_var', array(__CLASS__, 'validate_context_var'));
        register_setting(self::SLUG, 'watsonconv_login_var', array(__CLASS__, 'validate_context_var'));
    }

    public static function context_var_description() {
    ?>
        <p>
            Would you like to use a user's name or email in your chatbot's dialog? 
            This page allows you to send user account information (such as first name, last name) to your
            Watson Assistant chatbot as a "context variable". You can use this to customize
            your dialog to say different things depending on the value of the context variable. 
            To do this, follow these instructions:
        </p>
        <ol>
            <li>Give labels to the values you want to use by filling out the fields below 
                (e.g. 'fname' for First Name).</li>
            <li>Navigate to you Watson Assistant workspace (the place where you create your chatbot's dialog).</li>
            <li>Now you can type <strong>$fname</strong> in your chatbot dialog and this 
                will be replaced with the user's first name.</li> 
            <li>Sometimes a user may not specify their first name and so this context 
                variable won't be sent. Because of this, you should check if the
                chatbot recognizes the context variable first like in the example below.</li>
        </ol>
    <?php
    } 

    public static function render_fname_var() {
    ?>
        <input name="watsonconv_fname_var" id="watsonconv_fname_var"
            type="text" style="width: 16em"
            placeholder="e.g. fname"
            value="<?php echo get_option('watsonconv_fname_var', '') ?>" 
        />
        <span class='dashicons dashicons-arrow-right-alt'></span>
        "<?php echo get_user_meta(get_current_user_id(), 'first_name', true); ?>"
    <?php
    }

    public static function render_lname_var() {
        ?>
            <input name="watsonconv_lname_var" id="watsonconv_lname_var"
                type="text" style="width: 16em"
                placeholder="e.g. lname"
                value="<?php echo get_option('watsonconv_lname_var', '') ?>" 
            />
            <span class='dashicons dashicons-arrow-right-alt'></span>
            "<?php echo get_user_meta(get_current_user_id(), 'last_name', true); ?>"
        <?php
    }

    public static function render_nname_var() {
        ?>
            <input name="watsonconv_nname_var" id="watsonconv_nname_var"
                type="text" style="width: 16em"
                placeholder="e.g. nickname"
                value="<?php echo get_option('watsonconv_nname_var', '') ?>" 
            />
            <span class='dashicons dashicons-arrow-right-alt'></span>
            "<?php echo get_user_meta(get_current_user_id(), 'nickname', true); ?>"
        <?php
    }

    public static function render_email_var() {
        ?>
            <input name="watsonconv_email_var" id="watsonconv_email_var"
                type="text" style="width: 16em"
                placeholder="e.g. email"
                value="<?php echo get_option('watsonconv_email_var', '') ?>" 
            />
            <span class='dashicons dashicons-arrow-right-alt'></span>
            "<?php echo wp_get_current_user()->get('user_email'); ?>"
        <?php
    }

    public static function render_login_var() {
        ?>
            <input name="watsonconv_login_var" id="watsonconv_login_var"
                type="text" style="width: 16em"
                placeholder="e.g. username"
                value="<?php echo get_option('watsonconv_login_var', '') ?>" 
            />
            <span class='dashicons dashicons-arrow-right-alt'></span>
            "<?php echo wp_get_current_user()->get('user_login'); ?>"
        <?php
    }

    public static function validate_context_var($str) 
    {
        if (preg_match('/^[a-zA-Z0-9_]*$/',$str)) {
            return $str;
        } else {
            add_settings_error('watsonconv', 'invalid-var-name', 
                'A context variable name can only contain upper and lowercase alphabetic characters,
                numeric characters (0-9), and underscores.');
            return '';
        }
    }
}
