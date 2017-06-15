<?php
namespace WatsonConv;

register_deactivation_hook(WATSON_CONV_FILE, array('WatsonConv\Settings', 'unregister'));
add_action('admin_menu', array('WatsonConv\Settings', 'init_page'));
add_action('admin_init', array('WatsonConv\Settings', 'init_settings'));
add_action('admin_enqueue_scripts', array('WatsonConv\Settings', 'init_scripts'));
add_action('after_plugin_row_'.WATSON_CONV_BASENAME, array('WatsonConv\Settings', 'render_notice'), 10, 3);
add_filter('plugin_action_links_'.WATSON_CONV_BASENAME, array('WatsonConv\Settings', 'add_settings_link'));

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

            Frontend::load_styles();
        }
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
            return [$settings_link] + $links;
    }

    public static function render_page() {
    ?>
      <div class="wrap" style="max-width: 95em">
          <h2><?php esc_html_e('Watson Conversation Settings', self::SLUG); ?></h2>
          <form action="options.php" method="POST">
            <?php settings_fields(self::SLUG); ?>
            <?php do_settings_sections(self::SLUG); ?>
            <h1 style='text-align: center'>Preview</h3>
            <div class='popup-box' style='display: block; margin: 10px auto; cursor: default;'>
                <div class='popup-head'>
                    Chat Bot
                    <span class='dashicons dashicons-no-alt popup-control'></span>
                    <span class='dashicons dashicons-arrow-down-alt2 popup-control'></span>
                </div>
                <div class='message-container'>
                    <div class='messages'>
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
                <div class='message-form'>
                    <input
                      class='message-input'
                      type='text'
                      placeholder='Type a message'
                      disabled='true'
                    />
                </div>
            </div>
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

    public static function init_workspace_settings() {
        add_settings_section('watsonconv_workspace', 'Workspace Credentials',
            array(__CLASS__, 'workspace_description'), self::SLUG);

        add_settings_field('watsonconv_id', 'Workspace ID', array(__CLASS__, 'render_id'),
            self::SLUG, 'watsonconv_workspace');
        add_settings_field('watsonconv_username', 'Username', array(__CLASS__, 'render_username'),
            self::SLUG, 'watsonconv_workspace');
        add_settings_field('watsonconv_password', 'Password', array(__CLASS__, 'render_password'),
            self::SLUG, 'watsonconv_workspace');

        register_setting(self::SLUG, 'watsonconv_id');
        register_setting(self::SLUG, 'watsonconv_username');
        register_setting(self::SLUG, 'watsonconv_password');
    }

    public static function workspace_description($args) {
    ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>">
            <?php esc_html_e('Here, you can specify the Workspace ID for your Watson
                Conversation Workspace in addition to the required credentials.', self::SLUG) ?> <br />
            <?php esc_html_e('Note: These are not the same as your Bluemix Login Credentials.', self::SLUG) ?>
            <a href='https://www.ibm.com/watson/developercloud/doc/common/getting-started-credentials.html' target="_blank">
                <?php esc_html_e('Click here for details.', self::SLUG) ?>
            </a>
        </p>
    <?php
    }

    public static function render_id() {
    ?>
        <input name="watsonconv_id" id="watsonconv_id" type="text"
            value="<?php echo get_option('watsonconv_id') ?>"
            style="width: 22em" />
    <?php
    }

    public static function render_username() {
    ?>
        <input name="watsonconv_username" id="watsonconv_username" type="text"
            value="<?php echo get_option('watsonconv_username') ?>"
            style="width: 22em"/>
    <?php
    }

    public static function render_password() {
    ?>
        <input name="watsonconv_password" id="watsonconv_password" type="password"
            size=11 value="<?php echo get_option('watsonconv_password') ?>"
            style="width: 8em" />
    <?php
    }

    // ---------------- Rate Limiting -------------------

    public static function init_rate_limit_settings() {
        add_settings_section('watsonconv_rate_limit', 'Total Usage Management',
            array(__CLASS__, 'rate_limit_description'), self::SLUG);

        add_settings_field('watsonconv_use_limit', 'Limit Total API Requests',
            array(__CLASS__, 'render_use_limit'), self::SLUG, 'watsonconv_rate_limit');
        add_settings_field('watsonconv_limit', 'Maximum Number of Total Requests',
            array(__CLASS__, 'render_limit'), self::SLUG, 'watsonconv_rate_limit');
        add_settings_field('watsonconv_interval', 'Time Interval',
            array(__CLASS__, 'render_interval'), self::SLUG, 'watsonconv_rate_limit');

        register_setting(self::SLUG, 'watsonconv_use_limit');
        register_setting(self::SLUG, 'watsonconv_interval');
        register_setting(self::SLUG, 'watsonconv_limit');
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
    ?>
        <input name="watsonconv_limit" id="watsonconv_limit" type="number"
            value="<?php echo empty(get_option('watsonconv_limit')) ?
                        0 : get_option('watsonconv_limit')?>"
            style="width: 8em" />
    <?php
    }

    public static function render_interval() {
    ?>
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
        add_settings_section('watsonconv_client_rate_limit', 'Usage Per Client',
            array(__CLASS__, 'client_rate_limit_description'), self::SLUG);

        add_settings_field('watsonconv_use_client_limit', 'Limit API Requests Per Client',
            array(__CLASS__, 'render_use_client_limit'), self::SLUG, 'watsonconv_client_rate_limit');
        add_settings_field('watsonconv_client_limit', 'Maximum Number of Requests Per Client',
            array(__CLASS__, 'render_client_limit'), self::SLUG, 'watsonconv_client_rate_limit');
        add_settings_field('watsonconv_client_interval', 'Time Interval',
            array(__CLASS__, 'render_client_interval'), self::SLUG, 'watsonconv_client_rate_limit');

        register_setting(self::SLUG, 'watsonconv_use_client_limit');
        register_setting(self::SLUG, 'watsonconv_client_interval');
        register_setting(self::SLUG, 'watsonconv_client_limit');
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
    ?>
        <input name="watsonconv_client_limit" id="watsonconv_client_limit" type="number"
            value="<?php echo empty(get_option('watsonconv_client_limit')) ?
                        0 : get_option('watsonconv_client_limit')?>"
            style="width: 8em" />
    <?php
    }

    public static function render_client_interval() {
    ?>
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

    // ------------- Behaviour Settings ----------------

    public static function init_behaviour_settings() {
        add_settings_section('watsonconv_behaviour', 'Behaviour',
            array(__CLASS__, 'behaviour_description'), self::SLUG);

        add_settings_field('watsonconv_delay', esc_html__('Delay Before Pop-Up', self::SLUG),
            array(__CLASS__, 'render_delay'), self::SLUG, 'watsonconv_behaviour');

        add_settings_field('watsonconv_show_on', esc_html__('Show Chat Box On', self::SLUG),
            array(__CLASS__, 'render_show_on'), self::SLUG, 'watsonconv_behaviour');
        add_settings_field('watsonconv_home_page', esc_html__('Home Page', self::SLUG),
            array(__CLASS__, 'render_home_page'), self::SLUG, 'watsonconv_behaviour');
        add_settings_field('watsonconv_pages', esc_html__('Pages', self::SLUG),
            array(__CLASS__, 'render_pages'), self::SLUG, 'watsonconv_behaviour');
        add_settings_field('watsonconv_posts', esc_html__('Posts', self::SLUG),
            array(__CLASS__, 'render_posts'), self::SLUG, 'watsonconv_behaviour');
        add_settings_field('watsonconv_categories', esc_html__('Categories', self::SLUG),
            array(__CLASS__, 'render_categories'), self::SLUG, 'watsonconv_behaviour');

        register_setting(self::SLUG, 'watsonconv_delay');

        register_setting(self::SLUG, 'watsonconv_show_on', array(__CLASS__, 'sanitize_show_on'));
        register_setting(self::SLUG, 'watsonconv_home_page');
        register_setting(self::SLUG, 'watsonconv_pages', array(__CLASS__, 'sanitize_array'));
        register_setting(self::SLUG, 'watsonconv_posts', array(__CLASS__, 'sanitize_array'));
        register_setting(self::SLUG, 'watsonconv_categories', array(__CLASS__, 'sanitize_array'));
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
    ?>
        <input name="watsonconv_delay" id="watsonconv_delay" type="number"
            value="<?php echo empty(get_option('watsonconv_delay')) ?
                        0 : get_option('watsonconv_delay')?>"
            style="width: 4em" />
        seconds
    <?php
    }

    public static function render_show_on() {
        self::render_radio_buttons(
            'watsonconv_show_on',
            'only',
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
            Home Page
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
        add_settings_section('watsonconv_appearance', 'Appearance',
            array(__CLASS__, 'appearance_description'), self::SLUG);

        add_settings_field('watsonconv_font_size', 'Font Size',
            array(__CLASS__, 'render_font_size'), self::SLUG, 'watsonconv_appearance');
        add_settings_field('watsonconv_color', 'Color',
            array(__CLASS__, 'render_color'), self::SLUG, 'watsonconv_appearance');
        add_settings_field('watsonconv_position', 'Position',
            array(__CLASS__, 'render_position'), self::SLUG, 'watsonconv_appearance');
        add_settings_field('watsonconv_size', 'Window Size',
            array(__CLASS__, 'render_size'), self::SLUG, 'watsonconv_appearance');
        add_settings_field('watsonconv_minimized', 'Chat Box Minimized by Default',
            array(__CLASS__, 'render_minimized'), self::SLUG, 'watsonconv_appearance');

        register_setting(self::SLUG, 'watsonconv_font_size');
        register_setting(self::SLUG, 'watsonconv_color');
        register_setting(self::SLUG, 'watsonconv_position');
        register_setting(self::SLUG, 'watsonconv_size');
        register_setting(self::SLUG, 'watsonconv_minimized');
    }

    public static function appearance_description($args) {
    ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>">
            <?php esc_html_e('This section allows you to specify how you want
                the chat box to appear to your site visitor.', self::SLUG) ?>
        </p>
    <?php
    }

    public static function render_font_size() {
    ?>
        <input name="watsonconv_font_size" id="watsonconv_font_size"
            type="number" min=9 max=13 step=0.5 style="width: 4em"
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
