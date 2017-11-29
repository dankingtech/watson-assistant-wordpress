<?php
namespace WatsonConv;

register_deactivation_hook(WATSON_CONV_FILE, array('WatsonConv\Settings', 'unregister'));
add_action('admin_menu', array('WatsonConv\Settings', 'init_page'));
add_action('admin_init', array('WatsonConv\Settings', 'init_settings'));
add_action('admin_enqueue_scripts', array('WatsonConv\Settings', 'init_scripts'));
add_action('after_plugin_row_'.WATSON_CONV_BASENAME, array('WatsonConv\Settings', 'render_notice'), 10, 3);
add_filter('plugin_action_links_'.WATSON_CONV_BASENAME, array('WatsonConv\Settings', 'add_settings_link'));

add_action('plugins_loaded', array('WatsonConv\Settings', 'migrate_old_credentials'));

class Settings {
    const SLUG = 'watsonconv';

    public static function init_page() {
        add_options_page('Watson Conversation', 'Watson', 'manage_options',
            self::SLUG, array(__CLASS__, 'render_page'));
    }

    public static function init_settings() {
        self::init_workspace_settings();
        self::init_rate_limit_settings();
        self::init_client_rate_limit_settings();
        self::init_twilio_cred_settings();
        self::init_call_ui_settings();
        self::init_behaviour_settings();
        self::init_appearance_settings();
    }

    public static function unregister() {
        unregister_setting(self::SLUG, 'watsonconv_id');
        unregister_setting(self::SLUG, 'watsonconv_username');
        unregister_setting(self::SLUG, 'watsonconv_password');
        unregister_setting(self::SLUG, 'watsonconv_delay');
    }

    public static function init_scripts($hook_suffix) {
        if ($hook_suffix == 'settings_page_'.self::SLUG) {
            wp_enqueue_style('watsonconv-settings', WATSON_CONV_URL.'css/settings.css');
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script('settings-script', WATSON_CONV_URL.'includes/settings.js',
                array('wp-color-picker'), false, true );

            Frontend::load_styles(false);
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
                        <?php esc_html_e('Please fill in your Watson Conversation Workspace Credentials.', self::SLUG) ?>
                    </a>
                </div>
            </td></tr>
        <?php
        }
    }

    public static function add_settings_link($links) {
            $settings_link = '<a href="options-general.php?page='.self::SLUG.'">'
                . esc_html__('Settings', self::SLUG) . '</a>';
            return array($settings_link) + $links;
    }

    private static function render_preview() {
    ?>
        <div id='watson-fab' class='drop-shadow animated-shadow' style='display: block; margin: 20px auto; cursor: default;'>
          <span id='watson-fab-icon' class='dashicons dashicons-format-chat'></span>
        </div>
        <div id='watson-box' class='drop-shadow animated' style='display: block; margin: 10px auto;'>
            <div id='watson-header' class='watson-font' style='cursor: default;'>
                <span class='dashicons dashicons-arrow-down-alt2 popup-control'></span>
                <div class='overflow-hidden' ><?php echo get_option('watsonconv_title', '') ?></div>
            </div>
            <div id='message-container'>
                <div id='messages' class='watson-font'>
                    <div class='message watson-message'>
                        This is a message from the chatbot.
                    </div>
                    <div class='message user-message'>
                        This is a message from the user.
                    </div>
                    <div class='message watson-message'>
                        This message is a slightly longer message than the previous one from the chatbot.
                    </div>
                    <div class='message user-message'>
                        This message is a slightly longer message than the previous one from the user.
                    </div>
                </div>
            </div>
            <div class='message-form watson-font'>
                <input
                    class='message-input watson-font'
                    type='text'
                    placeholder='Type a message'
                    disabled='true'
                />
            </div>
        </div>
    <?php
    }

    public static function render_page() {
    ?>
      <div class="wrap" style="max-width: 95em">
          <h2><?php esc_html_e('Watson Conversation Settings', self::SLUG); ?></h2>

          <?php
            if (isset($_GET['tab'])) {
                $active_tab = $_GET[ 'tab' ];
            } else {
                $active_tab = 'workspace';
            } // end if

            $option_group = self::SLUG . '_' . $active_tab;

            ?>

          <h2 class="nav-tab-wrapper">
            <a href="?page=watsonconv&tab=workspace" class="nav-tab <?php echo $active_tab == 'workspace' ? 'nav-tab-active' : ''; ?>">Workspace Credentials</a>
            <a href="?page=watsonconv&tab=voice_call" class="nav-tab <?php echo $active_tab == 'voice_call' ? 'nav-tab-active' : ''; ?>">Voice Calling</a>
            <a href="?page=watsonconv&tab=usage_management" class="nav-tab <?php echo $active_tab == 'usage_management' ? 'nav-tab-active' : ''; ?>">Usage Management</a>
            <a href="?page=watsonconv&tab=behaviour" class="nav-tab <?php echo $active_tab == 'behaviour' ? 'nav-tab-active' : ''; ?>">Behaviour</a>
            <a href="?page=watsonconv&tab=appearance" class="nav-tab <?php echo $active_tab == 'appearance' ? 'nav-tab-active' : ''; ?>">Appearance</a>
          </h2>

          <form action="options.php" method="POST">
            <?php settings_fields($option_group); ?>
            <?php do_settings_sections($option_group); ?>
            <?php 
                if ($active_tab == 'appearance') {
                    echo "<h1 style='text-align: center'>Preview</h3>";
                    self::render_preview();
                } 
            ?>
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

    // ------------- Sanitization Functions -------------

    public static function sanitize_array($val) {
        return empty($val) ? -1 : $val;
    }

    public static function sanitize_show_on($val) {
        return ($val == 'all_except') ? 'all_except' : 'only';
    }

    // ------------ Workspace Credentials ---------------

    // If an installation of this plugin has a credentials format from the versions before 0.3.0,
    // migrate them to the new format.
    public static function migrate_old_credentials() {
        $credentials = get_option('watsonconv_credentials');

        if (!isset($credentials['workspace_url']) && isset($credentials['url']) && isset($credentials['id'])) {
            $credentials['workspace_url'] = 
                rtrim($credentials['url'], '/').'/workspaces/'.$credentials['id'].'/message/';
        }

        unset($credentials['url']);
        update_option('watsonconv_credentials', $credentials);
    }

    public static function init_workspace_settings() {
        $option_group = self::SLUG . '_workspace';

        add_settings_section('watsonconv_workspace', 'Workspace Credentials',
            array(__CLASS__, 'workspace_description'), $option_group);

        add_settings_field('watsonconv_username', 'Username', array(__CLASS__, 'render_username'),
            $option_group, 'watsonconv_workspace');
        add_settings_field('watsonconv_password', 'Password', array(__CLASS__, 'render_password'),
            $option_group, 'watsonconv_workspace');
        add_settings_field('watsonconv_workspace_url', 'Workspace URL', array(__CLASS__, 'render_url'),
            $option_group, 'watsonconv_workspace');

        register_setting($option_group, 'watsonconv_credentials', array(__CLASS__, 'validate_credentials'));
    }

    public static function validate_credentials($credentials) {
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
            return get_option('watsonconv_credentials');
        }

        $auth_token = 'Basic ' . base64_encode(
            $credentials['username'].':'.
            $credentials['password']);

        $response = wp_remote_post(
            $credentials['workspace_url'].'?version='.API::API_VERSION,
            array(
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

        if ($response_code == 401) {
            add_settings_error('watsonconv_credentials', 'invalid-credentials', 
                wp_remote_retrieve_response_message($response).': Please ensure you entered a valid username/password and URL');
            return get_option('watsonconv_credentials');
        } else if ($response_code == 404 || $response_code == 400) {
            add_settings_error('watsonconv_credentials', 'invalid-id', 
                'Please ensure you entered a valid workspace URL.');
            return get_option('watsonconv_credentials');
        } else if ($response_code != 200) {
            add_settings_error('watsonconv_credentials', 'invalid-url',
                'Please ensure you entered a valid workspace URL.');
            return get_option('watsonconv_credentials');
        }

        return $credentials;
    }

    public static function workspace_description($args) {
    ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>">
            <?php esc_html_e('Here, you can specify the Workspace ID for your Watson
                Conversation Workspace in addition to the required credentials.', self::SLUG) ?> <br />
            <?php esc_html_e('Note: These are not the same as your Bluemix Login Credentials.', self::SLUG) ?>
            <a href='https://cocl.us/watson-credentials-help' target="_blank">
                <?php esc_html_e('Click here for details.', self::SLUG) ?>
            </a> <br /><br />
            <?php esc_html_e('If the URL specified in your Service Credentials page is different 
                from the default, you will need to change it below.', self::SLUG) ?>
        </p>
    <?php
    }

    public static function render_username() {
        $credentials = get_option('watsonconv_credentials', array('username' => ''));
    ?>
        <input name="watsonconv_credentials[username]" id="watsonconv_username" type="text"
            value="<?php echo $credentials['username'] ?>"
            placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
            style="width: 24em"/>
    <?php
    }

    public static function render_password() {
        $credentials = get_option('watsonconv_credentials', array('password' => ''));
    ?>
        <input name="watsonconv_credentials[password]" id="watsonconv_password" type="password"
            value="<?php echo $credentials['password'] ?>"
            style="width: 8em" />
    <?php
    }

    public static function render_url() {
        $credentials = get_option('watsonconv_credentials', array('workpsace_url' => ''));
    ?>
        <input name="watsonconv_credentials[workspace_url]" id="watsonconv_workspace_url" type="text"
            value="<?php echo $credentials['workspace_url']; ?>"
            placeholder='https://gateway.watsonplatform.net/conversation/api/v1/workspaces/xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx/message/'
            style="width: 60em" />
    <?php
    }

    // ---------------- Rate Limiting -------------------

    public static function init_rate_limit_settings() {
        $option_group = self::SLUG . '_usage_management';

        add_settings_section('watsonconv_rate_limit', 'Total Usage Management',
            array(__CLASS__, 'rate_limit_description'), $option_group);

        add_settings_field('watsonconv_use_limit', 'Limit Total API Requests',
            array(__CLASS__, 'render_use_limit'), $option_group, 'watsonconv_rate_limit');
        add_settings_field('watsonconv_limit', 'Maximum Number of Total Requests',
            array(__CLASS__, 'render_limit'), $option_group, 'watsonconv_rate_limit');

        register_setting($option_group, 'watsonconv_use_limit');
        register_setting($option_group, 'watsonconv_interval');
        register_setting($option_group, 'watsonconv_limit');
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
                    Conversation, then the amount you have to pay is directly related to the
                    number of API requests made. The number of API requests is equal to the
                    number of messages sent by users of your chat bot, in addition to the chatbot's initial greeting.
                ", self::SLUG) ?>
            </p>
            <p>
                <?php esc_html_e("
                    For example, the Standard plan charges $0.0025 per API
                    call. That means if visitors to your site send a total of 1000 messages in
                    a month, you will be charged ($0.0025 per API call) x (1000 calls) =
                    $2.50. If you want to limit the costs incurred by this chatbot, you can
                    put a limit on the total number of API requests for a specific period of
                    time here.
                ", self::SLUG) ?>
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

    // ---------- Rate Limiting Per Client --------------

    public static function init_client_rate_limit_settings() {
        $option_group = self::SLUG . '_usage_management';

        add_settings_section('watsonconv_client_rate_limit', 'Usage Per Client',
            array(__CLASS__, 'client_rate_limit_description'), $option_group);

        add_settings_field('watsonconv_use_client_limit', 'Limit API Requests Per Client',
            array(__CLASS__, 'render_use_client_limit'), $option_group, 'watsonconv_client_rate_limit');
        add_settings_field('watsonconv_client_limit', 'Maximum Number of Requests Per Client',
            array(__CLASS__, 'render_client_limit'), $option_group, 'watsonconv_client_rate_limit');

        register_setting($option_group, 'watsonconv_use_client_limit');
        register_setting($option_group, 'watsonconv_client_interval');
        register_setting($option_group, 'watsonconv_client_limit');
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
            value="<?php echo empty($client_limit) ? 0 : $client_limit?>"
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
    
    // ------------ Twilio Credentials ---------------

    public static function init_twilio_cred_settings() {
        $option_group = self::SLUG . '_voice_call';

        add_settings_section('watsonconv_twilio_cred', 'Twilio Credentials',
            array(__CLASS__, 'twilio_cred_description'), $option_group);

        add_settings_field('watsonconv_twilo_sid', 'Account SID', array(__CLASS__, 'render_twilio_sid'),
            $option_group, 'watsonconv_twilio_cred');
        add_settings_field('watsonconv_twilio_auth', 'Auth Token', array(__CLASS__, 'render_twilio_auth'),
            $option_group, 'watsonconv_twilio_cred');
        add_settings_field('watsonconv_call_id', 'Caller ID (Verified Number with Twilio)',
            array(__CLASS__, 'render_call_id'), $option_group, 'watsonconv_twilio_cred');
        add_settings_field('watsonconv_call_recipient', 'Phone Number to Receive Calls from Users',
            array(__CLASS__, 'render_call_recipient'), $option_group, 'watsonconv_twilio_cred');
        add_settings_field('watsonconv_twilio_domain', 'Domain Name of this Website (Probably doesn\'t need changing)',
            array(__CLASS__, 'render_domain_name'), $option_group, 'watsonconv_twilio_cred');

        register_setting($option_group, 'watsonconv_twilio', array(__CLASS__, 'validate_twilio'));
        register_setting($option_group, 'watsonconv_call_id', array(__CLASS__, 'validate_phone'));
        register_setting($option_group, 'watsonconv_call_recipient', array(__CLASS__, 'validate_phone'));
    }

    public static function validate_twilio($new_config) {
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

        return $new_config;
    }

    public static function validate_phone($number) {
        if (!preg_match('/^\+?[1-9]\d{1,14}$/', $number)) {
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
        <p id="<?php echo esc_attr( $args['id'] ); ?>">
            <?php esc_html_e('Here, you can set up the beta voice calling feature, so that if the user
                wants to speak to a real person, they can call you directly from their browser using VOIP.') ?> <br><br>
            <a href="http://cocl.us/try-twilio"><?php esc_html_e('Start by creating your free trial Twilio account here.')?></a><br>
            <?php esc_html_e(' You can get your Account SID and Auth Token from your Twilio Dashboard.') ?> <br>
            <?php esc_html_e('For the caller ID, you can use a number that you\'ve either obtained from or') ?>
            <a href="https://www.twilio.com/console/phone-numbers/verified"><?php esc_html_e('verified with') ?></a>
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
        $config = get_option('watsonconv_twilio', array('sid' => ''));
    ?>
        <input name="watsonconv_twilio[sid]" id="watsonconv_twilio_sid" type="text"
            value="<?php echo $config['sid'] ?>"
            placeholder="xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
            style="width: 24em" />
    <?php
    }

    public static function render_twilio_auth() {
        $config = get_option('watsonconv_twilio', array('auth_token' => ''));
    ?>
        <input name="watsonconv_twilio[auth_token]" id="watsonconv_twilio_auth" type="password"
            value="<?php echo $config['auth_token'] ?>"
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
    
    public static function render_call_recipient() {
    ?>
        <input name="watsonconv_call_recipient" id="watsonconv_call_recipient" type="text"
            value="<?php echo get_option('watsonconv_call_recipient') ?>"
            placeholder="+15555555555"
            style="width: 24em" />
    <?php
    }
    
    public static function render_domain_name() {
        $config = get_option('watsonconv_twilio', array('domain_name' => get_site_url()));
    ?>
        <input name="watsonconv_twilio[domain_name]" id="watsonconv_twilio_domain" type="text"
            value="<?php echo $config['domain_name'] ?>"
            placeholder="<?php echo get_site_url() ?>"
            style="width: 24em" />
    <?php
    }
    
    // ------------ Voice Call UI Text ---------------

    public static function init_call_ui_settings() {
        $option_group = self::SLUG . '_voice_call';

        add_settings_section('watsonconv_call_ui', 'Voice Call UI Text',
            array(__CLASS__, 'twilio_call_ui_description'), $option_group);

        add_settings_field('watsonconv_call_tooltip', 'This message will display when the user hovers over the phone button.', 
            array(__CLASS__, 'render_call_tooltip'), $option_group, 'watsonconv_call_ui');
        add_settings_field('watsonconv_call_button', 'This is the text for the button to call using Twilio.',
            array(__CLASS__, 'render_call_button'), $option_group, 'watsonconv_call_ui');
        add_settings_field('watsonconv_calling_text', 'This text is displayed when calling.',
            array(__CLASS__, 'render_calling_text'), $option_group, 'watsonconv_call_ui');

        register_setting($option_group, 'watsonconv_call_tooltip');
        register_setting($option_group, 'watsonconv_call_button');
        register_setting($option_group, 'watsonconv_calling_text');
    }

    public static function twilio_call_ui_description($args) {
    ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>">
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
        $option_group = self::SLUG . '_behaviour';

        add_settings_section('watsonconv_behaviour', 'Behaviour',
            array(__CLASS__, 'behaviour_description'), $option_group);

        add_settings_field('watsonconv_delay', esc_html__('Delay Before Pop-Up', self::SLUG),
            array(__CLASS__, 'render_delay'), $option_group, 'watsonconv_behaviour');

        add_settings_field('watsonconv_show_on', esc_html__('Show Chat Box On', self::SLUG),
            array(__CLASS__, 'render_show_on'), $option_group, 'watsonconv_behaviour');
        add_settings_field('watsonconv_home_page', esc_html__('Front Page', self::SLUG),
            array(__CLASS__, 'render_home_page'), $option_group, 'watsonconv_behaviour');
        add_settings_field('watsonconv_pages', esc_html__('Pages', self::SLUG),
            array(__CLASS__, 'render_pages'), $option_group, 'watsonconv_behaviour');
        add_settings_field('watsonconv_posts', esc_html__('Posts', self::SLUG),
            array(__CLASS__, 'render_posts'), $option_group, 'watsonconv_behaviour');
        add_settings_field('watsonconv_categories', esc_html__('Categories', self::SLUG),
            array(__CLASS__, 'render_categories'), $option_group, 'watsonconv_behaviour');

        register_setting($option_group, 'watsonconv_delay');

        register_setting($option_group, 'watsonconv_show_on', array(__CLASS__, 'sanitize_show_on'));
        register_setting($option_group, 'watsonconv_home_page');
        register_setting($option_group, 'watsonconv_pages', array(__CLASS__, 'sanitize_array'));
        register_setting($option_group, 'watsonconv_posts', array(__CLASS__, 'sanitize_array'));
        register_setting($option_group, 'watsonconv_categories', array(__CLASS__, 'sanitize_array'));
    }

    public static function behaviour_description($args) {
    ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>">
            <?php esc_html_e('This section allows you to customize
                how you want the chat box to behave.', self::SLUG) ?>
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
            'all_except',
            array(
                array(
                    'label' => esc_html__('All Pages Except the Following', self::SLUG),
                    'value' => 'all_except'
                ), array(
                    'label' => esc_html__('Only the Following Pages', self::SLUG),
                    'value' => 'only'
                )
            )
        );
    }

    public static function render_home_page() {
    ?>
        <input
            type="checkbox" id="watsonconv_home_page"
            name="watsonconv_home_page" value="true"
            <?php checked('true', get_option('watsonconv_home_page', 'false')) ?>
        />
        <label for="watsonconv_home_page">
            Front Page
        </label>
    <?php
    }

    public static function render_pages() {
    ?>
        <fieldset style="border: 1px solid black; padding: 1em">
            <legend>Select Pages:</legend>
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
        <fieldset style="border: 1px solid black; padding: 1em">
            <legend>Select Posts:</legend>
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
        <fieldset style="border: 1px solid black; padding: 1em">
            <legend>Select Categories:</legend>
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
        $option_group = self::SLUG . '_appearance';

        add_settings_section('watsonconv_appearance', 'Appearance',
            array(__CLASS__, 'appearance_description'), $option_group);

        add_settings_field('watsonconv_minimized', 'Chat Box Minimized by Default',
            array(__CLASS__, 'render_minimized'), $option_group, 'watsonconv_appearance');
        add_settings_field('watsonconv_full_screen', 'Full Screen',
            array(__CLASS__, 'render_full_screen'), $option_group, 'watsonconv_appearance');
        add_settings_field('watsonconv_position', 'Position',
            array(__CLASS__, 'render_position'), $option_group, 'watsonconv_appearance');
        add_settings_field('watsonconv_title', 'Chat Box Title',
            array(__CLASS__, 'render_title'), $option_group, 'watsonconv_appearance');
        add_settings_field('watsonconv_font_size', 'Font Size',
            array(__CLASS__, 'render_font_size'), $option_group, 'watsonconv_appearance');
        add_settings_field('watsonconv_color', 'Color',
            array(__CLASS__, 'render_color'), $option_group, 'watsonconv_appearance');
        add_settings_field('watsonconv_size', 'Window Size',
            array(__CLASS__, 'render_size'), $option_group, 'watsonconv_appearance');

        register_setting($option_group, 'watsonconv_minimized');
        register_setting($option_group, 'watsonconv_full_screen');
        register_setting($option_group, 'watsonconv_position');
        register_setting($option_group, 'watsonconv_title');
        register_setting($option_group, 'watsonconv_font_size');
        register_setting($option_group, 'watsonconv_color');
        register_setting($option_group, 'watsonconv_size');
    }

    public static function appearance_description($args) {
    ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>">
            <?php esc_html_e('This section allows you to specify how you want
                the chat box to appear to your site visitor.', self::SLUG) ?>
        </p>
    <?php
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
    
    public static function render_full_screen() {
        self::render_radio_buttons(
            'watsonconv_full_screen',
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

    public static function render_title() {
    ?>
        <input name="watsonconv_title" id="watsonconv_title"
            type="text" style="width: 16em"
            value="<?php echo get_option('watsonconv_title', '') ?>" />
    <?php
    }

    public static function render_font_size() {
    ?>
        <input name="watsonconv_font_size" id="watsonconv_font_size"
            type="number" min=9 step=0.5 style="width: 4em"
            value="<?php echo get_option('watsonconv_font_size', 11) ?>" />
        pt
    <?php
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
}
