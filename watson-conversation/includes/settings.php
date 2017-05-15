<?php
namespace WatsonConv;

class Settings {
    public static function init_page() {
        add_options_page('Watson Conversation', 'Watson', 'manage_options',
            'watsonconv', array(__CLASS__, 'page_render'));
    }

    public static function init_settings() {
        self::init_workspace_settings();
        self::init_advanced_settings();
    }

    public static function unregister() {
        unregister_setting('watsonconv', 'watsonconv_id');
        unregister_setting('watsonconv', 'watsonconv_username');
        unregister_setting('watsonconv', 'watsonconv_password');
        unregister_setting('watsonconv', 'watsonconv_auth_method');
    }

    public static function render_notice($plugin_file, $plugin_data, $status) {
        if (empty(get_option('watsonconv_id')) ||
            empty(get_option('watsonconv_username')) ||
            empty(get_option('watsonconv_password'))) {
        ?>
            <tr class="active icon-settings"><td colspan=3>
                <div class="update-message notice inline notice-warning notice-alt"
                     style="padding:0.5em; padding-left:1em; margin:0">
                    <span style='color:orange; margin-right:0.3em'
                          class='dashicons dashicons-admin-settings'></span>
                    <a href="options-general.php?page=watsonconv">
                        <?php esc_html_e('Please fill in your Watson Conversation Workspace Credentials.') ?>
                    </a>
                </div>
            </td></tr>
        <?php
        }
    }

    public static function page_render() {
    ?>
      <div class="wrap">
          <h2><?php _e('Watson Conversation Settings', 'textdomain'); ?></h2>
          <form action="options.php" method="POST">
            <?php settings_fields('watsonconv'); ?>
            <?php do_settings_sections('watsonconv'); ?>
            <?php submit_button(); ?>
          </form>
      </div>
    <?php
    }

    public static function init_workspace_settings() {
        add_settings_section('watsonconv_workspace', 'Workspace Credentials',
            array(__CLASS__, 'description_workspace'), 'watsonconv');

        add_settings_field('watsonconv_id', 'Workspace ID', array(__CLASS__, 'id_render'),
            'watsonconv', 'watsonconv_workspace');
        add_settings_field('watsonconv_username', 'Username', array(__CLASS__, 'username_render'),
            'watsonconv', 'watsonconv_workspace');
        add_settings_field('watsonconv_password', 'Password', array(__CLASS__, 'password_render'),
            'watsonconv', 'watsonconv_workspace');

        register_setting('watsonconv', 'watsonconv_id');
        register_setting('watsonconv', 'watsonconv_username');
        register_setting('watsonconv', 'watsonconv_password');
    }

    public static function description_workspace($args) {
    ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>">
            <?php esc_html_e('Here, you can specify the Workspace ID for your Watson
                Conversation Workspace in addition to the required credentials.', 'watsonconv') ?> <br />
            <?php esc_html_e('Note: These are not the same as your Bluemix Login Credentials.', 'watsonconv') ?>
            <a href='https://www.ibm.com/watson/developercloud/doc/common/getting-started-credentials.html' target="_blank">
                Click here for details.
            </a>
        </p>
    <?php
    }

    public static function id_render() {
    ?>
        <input name="watsonconv_id" id="watsonconv_id" type="text"
        value="<?php echo get_option('watsonconv_id') ?>" />
    <?php
    }

    public static function username_render() {
    ?>
        <input name="watsonconv_username" id="watsonconv_username" type="text"
        value="<?php echo get_option('watsonconv_username') ?>" />
    <?php
    }

    public static function password_render() {
    ?>
        <input name="watsonconv_password" id="watsonconv_password" type="password"
        value="<?php echo get_option('watsonconv_password') ?>" />
    <?php
    }

    public static function init_advanced_settings() {
        add_settings_section('watsonconv_advanced', 'Advanced Settings',
            array(__CLASS__, 'description_advanced'), 'watsonconv');

        add_settings_field('watsonconv_auth', 'Authentication Method',
            array(__CLASS__, 'auth_method_render'), 'watsonconv', 'watsonconv_advanced');

        register_setting('watsonconv', 'watsonconv_auth_method');
    }

    public static function description_advanced() {
    ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>">
            <?php esc_html_e('These settings allow advanced users to
                change more specific settings.', 'watsonconv') ?> <br />
        </p>
    <?php
    }

    public static function auth_method_render() {
    ?>
        <input name="watsonconv_auth_method" id="watsonconv_auth_method" type="radio" value="basic"
            <?php checked('basic', get_option('watsonconv_auth_method')) ?> >
            HTTP Basic Authentication (Relaying requests through server)
        <br />
        <input name="watsonconv_auth_method" id="watsonconv_auth_method" type="radio" value="token"
            <?php checked('token', get_option('watsonconv_auth_method')) ?> >
            Authentication Token (Direct requests to Watson)
    <?php
    }

}
