<?php
namespace WatsonConv;

class Settings {
    public static function init_page() {
        add_options_page('Watson Conversation', 'Watson', 'manage_options', 'watsonconv', array(__CLASS__, 'page_render'));
    }

    public static function init_settings() {
        add_settings_section('watsonconv_setting_section', 'Watson Conversation Workspace', array(__CLASS__, 'section_render'), 'watsonconv');

        add_settings_field('watsonconv_id', 'Workspace ID', array(__CLASS__, 'id_render'), 'watsonconv', 'watsonconv_setting_section');
        add_settings_field('watsonconv_username', 'Username', array(__CLASS__, 'username_render'), 'watsonconv', 'watsonconv_setting_section');
        add_settings_field('watsonconv_password', 'Password', array(__CLASS__, 'password_render'), 'watsonconv', 'watsonconv_setting_section');

        register_setting('watsonconv', 'watsonconv_id');
        register_setting('watsonconv', 'watsonconv_username');
        register_setting('watsonconv', 'watsonconv_password');
    }

    public static function unregister() {
        unregister_setting('watsonconv', 'watsonconv_id');
        unregister_setting('watsonconv', 'watsonconv_username');
        unregister_setting('watsonconv', 'watsonconv_password');
    }

    public static function page_render() {
    ?>
      <div class="wrap">
          <h2><?php _e('Watson Settings', 'textdomain'); ?></h2>
          <form action="options.php" method="POST">
            <?php settings_fields('watsonconv'); ?>
            <?php do_settings_sections('watsonconv'); ?>
            <?php submit_button(); ?>
          </form>
      </div>
    <?php
    }

    public static function section_render() {
    ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>">
            <?php esc_html_e('Here, you can specify the Workspace ID for your Watson
                Conversation Workspace in addition to the required credentials.', 'watsonconv') ?> <br />
            <?php esc_html_e('Note: these are not the same as your Bluemix Login Credentials.', 'watsonconv' ); ?>
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
}
