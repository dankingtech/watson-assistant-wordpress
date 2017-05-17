<?php
namespace WatsonConv;

class Settings {
    const MENU_SLUG = 'watsonconv';

    public static function init_page() {
        add_options_page('Watson Conversation', 'Watson', 'manage_options',
            MENU_SLUG, array(__CLASS__, 'page_render'));
    }

    public static function init_settings() {
        self::init_workspace_settings();
        self::init_behaviour_settings();
        self::init_advanced_settings();
    }

    public static function unregister() {
        unregister_setting(MENU_SLUG, 'watsonconv_id');
        unregister_setting(MENU_SLUG, 'watsonconv_username');
        unregister_setting(MENU_SLUG, 'watsonconv_password');
        unregister_setting(MENU_SLUG, 'watsonconv_auth_method');
        unregister_setting(MENU_SLUG, 'watsonconv_delay');
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
                    <a href="options-general.php?page=<?php echo MENU_SLUG ?>">
                        <?php esc_html_e('Please fill in your Watson Conversation Workspace Credentials.', MENU_SLUG) ?>
                    </a>
                </div>
            </td></tr>
        <?php
        }
    }

    public static function page_render() {
    ?>
      <div class="wrap">
          <h2><?php esc_html_e('Watson Conversation Settings', MENU_SLUG); ?></h2>
          <form action="options.php" method="POST">
            <?php settings_fields(MENU_SLUG); ?>
            <?php do_settings_sections(MENU_SLUG); ?>
            <?php submit_button(); ?>
          </form>
      </div>
    <?php
    }

    // ------------ Workspace Credentials ---------------

    public static function init_workspace_settings() {
        add_settings_section('watsonconv_workspace', 'Workspace Credentials',
            array(__CLASS__, 'description_workspace'), MENU_SLUG);

        add_settings_field('watsonconv_id', 'Workspace ID', array(__CLASS__, 'id_render'),
            MENU_SLUG, 'watsonconv_workspace');
        add_settings_field('watsonconv_username', 'Username', array(__CLASS__, 'username_render'),
            MENU_SLUG, 'watsonconv_workspace');
        add_settings_field('watsonconv_password', 'Password', array(__CLASS__, 'password_render'),
            MENU_SLUG, 'watsonconv_workspace');

        register_setting(MENU_SLUG, 'watsonconv_id');
        register_setting(MENU_SLUG, 'watsonconv_username');
        register_setting(MENU_SLUG, 'watsonconv_password');
    }

    public static function description_workspace($args) {
    ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>">
            <?php esc_html_e('Here, you can specify the Workspace ID for your Watson
                Conversation Workspace in addition to the required credentials.', MENU_SLUG) ?> <br />
            <?php esc_html_e('Note: These are not the same as your Bluemix Login Credentials.', MENU_SLUG) ?>
            <a href='https://www.ibm.com/watson/developercloud/doc/common/getting-started-credentials.html' target="_blank">
                <?php esc_html_e('Click here for details.', MENU_SLUG) ?>
            </a>
        </p>
    <?php
    }

    public static function id_render() {
    ?>
        <input name="watsonconv_id" id="watsonconv_id" type="text"
            value="<?php echo get_option('watsonconv_id') ?>"
            style="width: 22em" />
    <?php
    }

    public static function username_render() {
    ?>
        <input name="watsonconv_username" id="watsonconv_username" type="text"
            value="<?php echo get_option('watsonconv_username') ?>"
            style="width: 22em"/>
    <?php
    }

    public static function password_render() {
    ?>
        <input name="watsonconv_password" id="watsonconv_password" type="password"
            size=11 value="<?php echo get_option('watsonconv_password') ?>"
            style="width: 8em" />
    <?php
    }

    // ------------- Behaviour Settings ----------------

    public static function init_behaviour_settings() {
        add_settings_section('watsonconv_behaviour', 'Behaviour',
            array(__CLASS__, 'description_behaviour'), MENU_SLUG);

        add_settings_field('watsonconv_delay', esc_html__('Delay Before Pop-Up', MENU_SLUG),
            array(__CLASS__, 'delay_render'), MENU_SLUG, 'watsonconv_behaviour');

        register_setting(MENU_SLUG, 'watsonconv_delay');
    }

    public static function description_behaviour($args) {
    ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>">
            <?php esc_html_e('Here you can customize how you want the chat box to behave.', MENU_SLUG) ?>
        </p>
    <?php
    }

    public static function delay_render() {
    ?>
        <input name="watsonconv_delay" id="watsonconv_delay" type="number"
            value="<?php echo get_option('watsonconv_delay') ?>"
            style="width: 4em" />
        seconds
    <?php
    }

    // -------------- Advanced Settings -----------------

    public static function init_advanced_settings() {
        add_settings_section('watsonconv_advanced', 'Advanced Settings',
            array(__CLASS__, 'description_advanced'), MENU_SLUG);

        add_settings_field('watsonconv_auth_method', 'Authentication Method',
            array(__CLASS__, 'auth_method_render'), MENU_SLUG, 'watsonconv_advanced');

        register_setting(MENU_SLUG, 'watsonconv_auth_method');
    }

    public static function description_advanced($args) {
    ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>">
        </p>
    <?php
    }

    public static function auth_method_render() {
    ?>
        <input name="watsonconv_auth_method" id="watsonconv_auth_method" type="radio" value="basic"
            <?php checked('basic', get_option('watsonconv_auth_method')) ?> >
            <?php esc_html_e('HTTP Basic Authentication (Relaying requests through server)', MENU_SLUG) ?>
        <br />
        <input name="watsonconv_auth_method" id="watsonconv_auth_method" type="radio" value="token"
            <?php checked('token', get_option('watsonconv_auth_method')) ?> >
            <?php esc_html_e('Authentication Token (Direct requests to Watson)', MENU_SLUG) ?>
        <br />
        <p style='margin-top: 1em' >
            <a href="https://www.ibm.com/watson/developercloud/doc/common/getting-started-develop.html" target="_blank">
                <?php esc_html_e('Click here for details', MENU_SLUG) ?>
            </a>
        </p>
    <?php
    }

}
