<?php
namespace WatsonConv\Settings;

class Setup {
    const SLUG = Main::SLUG.'_setup';

    public static function init_page() {
        add_submenu_page(Main::SLUG, 'Watson Assistant Setup', 'Set Up Chatbot', 
            'manage_options', self::SLUG, array(__CLASS__, 'render_page'));
    }

    public static function init_settings() {
        self::init_basic_cred_settings();
        self::init_iam_cred_settings();

        register_setting(self::SLUG, 'watsonconv_enabled');
        register_setting(self::SLUG, 'watsonconv_credentials', array(__CLASS__, 'validate_credentials'));
    }

    public static function render_page() {
    ?>
        <div class="wrap" style="max-width: 95em">
            <h2><?php esc_html_e('Set Up Your Chatbot', self::SLUG); ?></h2>

            <?php 
                Main::render_isv_banner(); 
                settings_errors(); 
            ?>

            <h2 class="nav-tab-wrapper">
                <a onClick="switch_tab('build')" class="nav-tab nav-tab-active build_tab">1. Building a chatbot</a>
                <a onClick="switch_tab('workspace')" class="nav-tab workspace_tab">2. Plugin Setup</a>
            </h2>

            <form action="options.php" method="POST">
                <div class="tab-page build_page"><?php self::render_build(); ?></div>

                <?php settings_fields(self::SLUG); ?> 

                <div class="tab-page workspace_page" style="display: none">
                    <?php self::main_setup_description(); ?>
                    <div id="basic_cred">
                        <?php do_settings_sections(self::SLUG.'_basic_cred') ?>
                    </div>
                    <div id="iam_cred" style="display: none;">
                        <?php do_settings_sections(self::SLUG.'_iam_cred') ?>
                    </div>
                    <?php submit_button(); ?>

                    <p  class="update-message notice inline notice-warning notice-alt"
                        style="padding-top: 0.5em; padding-bottom: 0.5em">
                        <b>Note:</b> If you have a server-side caching plugin installed such as
                        WP Super Cache, you may need to clear your cache after changing settings or
                        deactivating the plugin. Otherwise, your action may not take effect.
                    <p>
                </div>
            </form>
        </div>
    <?php
    }

    public static function render_intro() {
    ?>
        <p>
            Watson Assistant, formerly known as Watson Conversation, provides a clear and user-friendly
            interface to build virtual assistants to speak with your users. With the use of this plugin, 
            you can add these virtual assistants, or <b>chatbots</b>, to your website with minimal
            technical knowledge or work.
        </p>
        <p>
            This diagram shows the overall architecture of a complete solution:
            <img 
                src="https://console.bluemix.net/docs/api/content/services/conversation/images/conversation_arch_overview.png?lang=en-US" 
                alt="Flow diagram of the service" 
                class="style-scope doc-content"
                style="width:100%; border: 1px solid grey"
            >
        </p>
        <p>
            When you use this plugin, the <strong>Back-end system</strong> is Wordpress, while the 
            <strong>Application</strong> and <strong>Interface</strong> are both included in this
            plugin. Therefore, all you need to worry about is bulding your chatbot in your Watson
            Assistant workspace and this plugin will take care of the rest.
        </p>
        <button type="button" class="button button-primary" onClick="switch_tab('build')">Next</button>
    <?php
    }

    public static function render_build() {
    ?>
        <p>
            Watson Assistant, formerly known as Watson Conversation, provides a clear and user-friendly
            interface to build virtual assistants to speak with your users. With the use of this plugin, 
            you can add these virtual assistants, or <b>chatbots</b>, to your website with minimal
            technical knowledge or work.
        </p>
        <p>
            Before you can use Watson Assistant on your website, you'll have to build your chatbot using
            our user-friendly interface.
        </p>
        <p>
            <a href="https://cocl.us/bluemix-registration" rel="nofollow" target="_blank">
                Sign up here</a> 
            for a free IBM Cloud Lite account to get started. If you have an account but have not started
            with Watson Assistant yet,
            <a href="https://console.bluemix.net/registration?target=/catalog/services/conversation">click here</a> 
            to get started. Once you launch the Watson Assistant tool, you will be shown how to proceed
            to create your chatbot. You may find the following resources helpful.
        </p>
        <ul>
            <p><li>
                This video provides an overview of the Watson Assistant tool:
            </li></p>
            <iframe width="560" height="315" src="https://www.youtube.com/embed/sSfTcxDrmSI" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
            <p><li>
                You can also learn how to set up your Watson Assistant chatbot with 
                <a href="https://cocl.us/build-a-chatbot" rel="nofollow" target="_blank">this quick free course</a>.
                </li></p>
            <p><li>
                See 
                <a href="https://cocl.us/watson-conversation-help" rel="nofollow" target="_blank">
                    the Watson Assistant documentation</a>
                for more information.
            </li></p>
        </ul>
        <p>
            Once you've created your workspace and built your chatbot using the outlined resources,
            you're ready to connect it to your website!
        </p>

        <button type="button" class="button button-primary" onClick="switch_tab('workspace')">Next</button>
    <?php
    }

    public static function main_setup_description() {
        $credentials = get_option('watsonconv_credentials');
        $cred_type = empty($credentials['type']) ? 'basic' : $credentials['type'];
    ?>
        <p>
            This is where you  get to finally connect the Watson Assistant chatbot you built to your
            website. To do this, you need to get the URL and credentials of your Watson Assistant
            workspace. To find these values, navigate to the workspace where you built your chatbot. Then click
            on the Deploy tab in the navigation bar on the left to reach one of these two pages.
        </p>
        <p>
            If your page has a Username and Password, you may proceed to enter them in the fields below.
            If you have an API key instead of a Username and Password, please click on the second
            image before proceeding.
        </p>
        <table width="100%"><tr>
            <td class="responsive" style="padding: 10px; text-align: center;">
                <label for="watsonconv_credentials_basic">
                    <input 
                        type="radio" 
                        id="watsonconv_credentials_basic" 
                        name="watsonconv_credentials[type]" 
                        value="basic"
                        <?php checked($cred_type, 'basic'); ?>
                    >
                    <strong>Username/Password</strong>
                    <div>
                        <img src="<?php echo WATSON_CONV_URL ?>/img/credentials.jpg">
                    </div>
                </label>
            </td>
            <td class="responsive" style="padding: 10px; text-align: center;">
                <label for="watsonconv_credentials_iam">
                    <input 
                        type="radio" 
                        id="watsonconv_credentials_iam" 
                        name="watsonconv_credentials[type]" 
                        value="iam"
                        <?php checked($cred_type, 'iam'); ?>
                    >
                    <strong>API Key</strong>
                    <div>
                        <img src="<?php echo WATSON_CONV_URL ?>/img/credentials_iam.jpg">
                    </div>
                </label>
            </td>
        </tr></table>
        <p>
            Enter these values in their corresponding fields below. Once you click 
            "Save Changes", the plugin will verify if the credentials are valid and notify 
            you of whether or not the configuration was successful. 
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

            if (!isset($credentials['auth_header']) && isset($credentials['username']) && isset($credentials['password'])) {
                $credentials['auth_header'] = 'Basic ' . base64_encode(
                    $credentials['username'].':'.
                    $credentials['password']
                );

                update_option('watsonconv_credentials', $credentials);
            }
        } catch (\Exception $e) {}
    }

    public static function init_basic_cred_settings() {
        $settings_page = self::SLUG . '_basic_cred';

        add_settings_section('watsonconv_basic_cred', 'Workspace Credentials',
            array(__CLASS__, 'workspace_description'), $settings_page);

        add_settings_field('watsonconv_enabled', '', array(__CLASS__, 'render_enabled'),
            $settings_page, 'watsonconv_basic_cred', array('id' => 'basic_enabled'));
        add_settings_field('watsonconv_username', 'Username', array(__CLASS__, 'render_username'),
            $settings_page, 'watsonconv_basic_cred');
        add_settings_field('watsonconv_password', 'Password', array(__CLASS__, 'render_password'),
            $settings_page, 'watsonconv_basic_cred');
        add_settings_field('watsonconv_workspace_url', 'Workspace URL', array(__CLASS__, 'render_url'),
            $settings_page, 'watsonconv_basic_cred', array('id' => 'basic_workspace_url'));
    }

    public static function init_iam_cred_settings() {
        $settings_page = self::SLUG . '_iam_cred';

        add_settings_section('watsonconv_iam_cred', 'Workspace Credentials',
            array(__CLASS__, 'workspace_iam_description'), $settings_page);

        add_settings_field('watsonconv_enabled', '', array(__CLASS__, 'render_enabled'),
            $settings_page, 'watsonconv_iam_cred', array('id' => 'iam_enabled'));
        add_settings_field('watsonconv_api_key', 'API Key', array(__CLASS__, 'render_api_key'),
            $settings_page, 'watsonconv_iam_cred');
        add_settings_field('watsonconv_workspace_url', 'Workspace URL', array(__CLASS__, 'render_url'),
            $settings_page, 'watsonconv_iam_cred', array('id' => 'iam_workspace_url'));
    }

    private static function get_debug_info($response) {
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

        return '<a id="error_expand">Click here for debug information.</a>
            <pre id="error_response" style="display: none;">'.$response_string.'</pre>';
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

        if ($credentials['type'] == 'iam') {
            if (empty($credentials['api_key'])) {
                add_settings_error('watsonconv_credentials', 'invalid-api-key', 'Please enter an API key.');
                $empty = true;
            }
        } else {
            if (empty($credentials['username'])) {
                add_settings_error('watsonconv_credentials', 'invalid-username', 'Please enter a username.');
                $empty = true;
            }
            if (empty($credentials['password'])) {
                add_settings_error('watsonconv_credentials', 'invalid-password', 'Please enter a password.');
                $empty = true;
            }
        }

        if (isset($empty)) {
            return $old_credentials;
        }

        if ($credentials == $old_credentials) {
            return $credentials;
        }

        if ($credentials['type'] == 'iam') {
            $token_response = wp_remote_post(
                'https://iam.bluemix.net/identity/token',
                array(
                    'timeout' => 20,
                    'headers' => array(
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/x-www-form-urlencoded'
                    ), 'body' => array(
                        'grant_type' => 'urn:ibm:params:oauth:grant-type:apikey',
                        'apikey' => $credentials['api_key']
                    )
                )
            );

            $token_response_code = wp_remote_retrieve_response_code($token_response);
            $token_code_string = empty($response_code) ? '' : ' ('.$response_code.')';
            $token_debug_info = self::get_debug_info($token_response);

            if (is_wp_error($token_response)) {
                add_settings_error('watsonconv_credentials', 'token-error', 
                    'Unable to connect to Watson IAM server'.$token_code_string.'. ' . $token_debug_info);
                return get_option('watsonconv_credentials');
            } else if ($token_response_code == 400) {
                add_settings_error('watsonconv_credentials', 'invalid-api-key', 
                    'Please ensure you entered a valid API key'.$token_code_string.'. ' . $token_debug_info);
                return get_option('watsonconv_credentials');
            } else if ($token_response_code != 200) {
                add_settings_error('watsonconv_credentials', 'token-error',
                    'Unable to retrieve IAM token'.$token_code_string.'. ' . $token_debug_info);
                return get_option('watsonconv_credentials');
            }

            $token_body = json_decode(wp_remote_retrieve_body($token_response), true);

            if (empty($token_body['access_token'])) {
                add_settings_error('watsonconv_credentials', 'token-error',
                    'Unable to retrieve IAM token'.$token_code_string.'. ' . $debug_info);
                return get_option('watsonconv_credentials');
            }

            update_option('watsonconv_iam_expiry', 
                empty($token_body['expires_in']) ? 3000 : ($token_body['expires_in'] - 600));

            $token_type = empty($token_body['token_type']) ? 'Bearer' : $token_body['token_type'];
            $auth_header = $token_type.' '.$token_body['access_token'];
        } else {
            $auth_header = 'Basic ' . base64_encode(
                $credentials['username'].':'.
                $credentials['password']
            );
        }

        $response = wp_remote_post(
            $credentials['workspace_url'].'?version='.\WatsonConv\API::API_VERSION,
            array(
                'timeout' => 20,
                'headers' => array(
                    'Authorization' => $auth_header,
                    'Content-Type' => 'application/json'
                ), 'body' => json_encode(array(
                    'input' => new \stdClass, 
                    'context' => new \stdClass()
                ))
            )
        );

        $response_code = wp_remote_retrieve_response_code($response);
        $response_code_string = empty($response_code) ? '' : ' ('.$response_code.')';

        $debug_info = self::get_debug_info($response);

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

        $credentials['auth_header'] = $auth_header;
        
        wp_clear_scheduled_hook('watson_get_iam_token');

        if ($credentials['type'] == 'iam') {
            wp_schedule_event(time(), 'watson_token_interval', 'watson_get_iam_token');
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
            <?php esc_html_e('Specify the Workspace URL, username and password for your Watson
                Assistant Workspace below.', self::SLUG) ?> <br />
        </p>
    <?php
    }

    public static function workspace_iam_description($args) {
    ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>">
            <?php esc_html_e('Specify the Workspace URL and API key for your Watson
                Assistant Workspace below.', self::SLUG) ?> <br />
        </p>
    <?php
    }

    public static function render_enabled($args) {
        $credentials = get_option('watsonconv_credentials');
        $enabled = (isset($credentials['enabled']) ? $credentials['enabled'] : 'true') == 'true';
    ?>
        <fieldset>
            <input
                type="checkbox" id=<?php echo $args['id']; ?>
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
        $credentials = get_option('watsonconv_credentials');
    ?>
        <input name="watsonconv_credentials[username]" class="watsonconv_credentials"
            id="watsonconv_username" type="text"
            value="<?php echo empty($credentials['username']) ? '' : $credentials['username'] ?>"
            placeholder="e.g. xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
            style="max-width: 24em; width: 100%;"/>
    <?php
    }

    public static function render_password() {
        $credentials = get_option('watsonconv_credentials');
    ?>
        <input name="watsonconv_credentials[password]" class="watsonconv_credentials"
            id="watsonconv_password" type="password"
            value="<?php echo empty($credentials['password']) ? '' : $credentials['password'] ?>"
            style="max-width: 8em; width: 100%;" />
    <?php
    }

    public static function render_url($args) {
        $credentials = get_option('watsonconv_credentials');
    ?>
        <input name="watsonconv_credentials[workspace_url]" class="watsonconv_credentials"
            id=<?php echo $args['id']; ?> type="text"
            value="<?php echo empty($credentials['workspace_url']) ? '' : $credentials['workspace_url']; ?>"
            placeholder='e.g. https://gateway.watsonplatform.net/conversation/api/v1/workspaces/xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx/message/'
            style="max-width: 60em; width: 100%;" />
    <?php
    }

    public static function render_api_key() {
        $credentials = get_option('watsonconv_credentials');
    ?>
        <input name="watsonconv_credentials[api_key]" class="watsonconv_credentials"
            id="watsonconv_api_key" type="text"
            value="<?php echo empty($credentials['api_key']) ? '' : $credentials['api_key']; ?>"
            placeholder="e.g. XxXxXxXxXxXxXxXxXxXxXxXxXxXxXxXxXxXxXxXxXxXx"
            style="max-width: 30em; width: 100%;"/>
    <?php
    }
}
