<?php
namespace WatsonConv\Settings;

class Setup {
    const SLUG = Main::SLUG.'_setup';

    public static function init_page() {
        add_submenu_page(Main::SLUG, 'Watson Assistant Setup', 'Set Up Chatbot', 
            'manage_options', self::SLUG, array(__CLASS__, 'render_page'));
    }

    public static function init_settings() {
        self::init_main_setup_intro();
        self::init_workspace_settings();
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
                <a onClick="switch_tab('intro')" class="nav-tab nav-tab-active intro_tab">Introduction</a>
                <a onClick="switch_tab('workspace')" class="nav-tab workspace_tab">Main Setup</a>
            </h2>

            <form action="options.php" method="POST">
                <div class="tab-page intro_page"><?php self::render_intro(); ?></div>

                <?php settings_fields(self::SLUG); ?> 

                <div class="tab-page workspace_page" style="display: none">
                    <?php do_settings_sections(self::SLUG.'_workspace') ?>
                    <?php submit_button(); ?>
                </div>

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
            $credentials['workspace_url'].'?version='.\WatsonConv\API::API_VERSION,
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
}
