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
                <a onClick="switch_tab('build')" class="nav-tab nav-tab-active build_tab">1. Building a chatbot</a>
                <a onClick="switch_tab('workspace')" class="nav-tab workspace_tab">2. Plugin Setup</a>
            </h2>

            <form action="options.php" method="POST">
                <div class="tab-page build_page"><?php self::render_build(); ?></div>

                <?php settings_fields(self::SLUG); ?> 

                <div class="tab-page workspace_page" style="display: none">
                    <?php do_settings_sections(self::SLUG.'_workspace') ?>
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

    // ----------------- Main Setup ---------------------

    public static function init_main_setup_intro() {
        $settings_page = self::SLUG . '_workspace';
        
        add_settings_section('watsonconv_main_setup_intro', '',
            array(__CLASS__, 'main_setup_description'), $settings_page);
    }

    public static function main_setup_description() {
    ?>
        <p>
            This is where you  get to finally connect the Watson Assistant chatbot you built to your
            website. To do this, you need to get the Username, Password and Workspace URL of your
            Watson Assistant workspace.
        </p>
        <p>
            To find these values, navigate to the workspace where you built your chatbot. Then click
            on the Deploy tab in the navigation bar on the left, as shown in this photo.
        </p>
        <img 
            style="max-width: 100%; border: 1px solid rgb(29, 40, 51)" 
            src="<?php echo WATSON_CONV_URL ?>/img/credentials.jpg"
        >
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
            <?php esc_html_e('Specify the Workspace URL, username and password for your Watson
                Assistant Workspace below.', self::SLUG) ?> <br />
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
