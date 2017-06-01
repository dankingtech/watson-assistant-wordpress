<?php
namespace WatsonConv;

class Settings {
    const SLUG = 'watsonconv';

    public static function init_page() {
        add_options_page('Watson Conversation', 'Watson', 'manage_options',
            self::SLUG, array(__CLASS__, 'page_render'));
    }

    public static function init_settings() {
        self::init_workspace_settings();
        self::init_rate_limit_settings();
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
            wp_enqueue_script('color-picker', WATSON_CONV_URL.'includes/color-picker.js',
                array('wp-color-picker'), false, true );
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

    public static function page_render() {
    ?>
      <div class="wrap">
          <h2><?php esc_html_e('Watson Conversation Settings', self::SLUG); ?></h2>
          <form action="options.php" method="POST">
            <?php settings_fields(self::SLUG); ?>
            <?php do_settings_sections(self::SLUG); ?>
            <?php submit_button(); ?>
          </form>
      </div>
    <?php
    }

    // ------------- Sanitization Functions -------------

    public static function boolean_sanitize($val) {
        return $val == 'yes' ? true : false;
    }

    public static function array_sanitize($val) {
        return empty($val) ? -1 : $val;
    }

    public static function show_on_sanitize($val) {
        return ($val == 'only') ? 'only' : 'all_except';
    }

    // ------------ Workspace Credentials ---------------

    public static function init_workspace_settings() {
        add_settings_section('watsonconv_workspace', 'Workspace Credentials',
            array(__CLASS__, 'description_workspace'), self::SLUG);

        add_settings_field('watsonconv_id', 'Workspace ID', array(__CLASS__, 'id_render'),
            self::SLUG, 'watsonconv_workspace');
        add_settings_field('watsonconv_username', 'Username', array(__CLASS__, 'username_render'),
            self::SLUG, 'watsonconv_workspace');
        add_settings_field('watsonconv_password', 'Password', array(__CLASS__, 'password_render'),
            self::SLUG, 'watsonconv_workspace');

        register_setting(self::SLUG, 'watsonconv_id');
        register_setting(self::SLUG, 'watsonconv_username');
        register_setting(self::SLUG, 'watsonconv_password');
    }

    public static function description_workspace($args) {
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

    // ---------------- Rate Limiting -------------------

    public static function init_rate_limit_settings() {
        add_settings_section('watsonconv_rate_limit', 'API Rate Limiting',
            array(__CLASS__, 'description_rate_limit'), self::SLUG);

        add_settings_field('watsonconv_use_limit', 'Use API Rate Limiting',
            array(__CLASS__, 'use_limit_render'), self::SLUG, 'watsonconv_rate_limit');
        add_settings_field('watsonconv_limit', 'Maximum Number of Requests',
            array(__CLASS__, 'limit_render'), self::SLUG, 'watsonconv_rate_limit');
        add_settings_field('watsonconv_interval', 'Time Interval',
            array(__CLASS__, 'interval_render'), self::SLUG, 'watsonconv_rate_limit');

        register_setting(self::SLUG, 'watsonconv_use_limit', array(__CLASS__, 'boolean_sanitize'));
        register_setting(self::SLUG, 'watsonconv_interval');
        register_setting(self::SLUG, 'watsonconv_limit');
    }

    public static function description_rate_limit($args) {
    ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>">
            <?php esc_html_e('This section allows you to prevent overusage of your
                credentials by limiting use of the chat bot.', self::SLUG) ?>
            </a>
        </p>
    <?php
    }

    public static function use_limit_render() {
    ?>
        <input name="watsonconv_use_limit" id="use_limit_yes" type="radio" value="yes"
            <?php checked(true, get_option('watsonconv_use_limit', false)) ?> >
        <label for="use_limit_yes">
            <?php esc_html_e('Yes', self::SLUG) ?>
        </label><br />

        <input name="watsonconv_use_limit" id="use_limit_no" type="radio" value="no"
            <?php checked(false, get_option('watsonconv_use_limit', false)) ?> >
        <label for="use_limit_no">
            <?php esc_html_e('No', self::SLUG) ?>
        </label>
    <?php
    }

    public static function limit_render() {
    ?>
        <input name="watsonconv_limit" id="watsonconv_limit" type="number"
            value="<?php echo empty(get_option('watsonconv_limit')) ?
                        0 : get_option('watsonconv_limit')?>"
            style="width: 8em" />
    <?php
    }

    public static function interval_render() {
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

    // ------------- Behaviour Settings ----------------

    public static function init_behaviour_settings() {
        add_settings_section('watsonconv_behaviour', 'Behaviour',
            array(__CLASS__, 'description_behaviour'), self::SLUG);

        add_settings_field('watsonconv_delay', esc_html__('Delay Before Pop-Up', self::SLUG),
            array(__CLASS__, 'delay_render'), self::SLUG, 'watsonconv_behaviour');

        add_settings_field('watsonconv_show_on', esc_html__('Show Chat Box On', self::SLUG),
            array(__CLASS__, 'show_on_render'), self::SLUG, 'watsonconv_behaviour');
        add_settings_field('watsonconv_pages', esc_html__('Pages', self::SLUG),
            array(__CLASS__, 'pages_render'), self::SLUG, 'watsonconv_behaviour');
        add_settings_field('watsonconv_posts', esc_html__('Posts', self::SLUG),
            array(__CLASS__, 'posts_render'), self::SLUG, 'watsonconv_behaviour');
        add_settings_field('watsonconv_categories', esc_html__('Categories', self::SLUG),
            array(__CLASS__, 'categories_render'), self::SLUG, 'watsonconv_behaviour');

        register_setting(self::SLUG, 'watsonconv_delay');

        register_setting(self::SLUG, 'watsonconv_show_on', array(__CLASS__, 'show_on_sanitize'));
        register_setting(self::SLUG, 'watsonconv_pages', array(__CLASS__, 'array_sanitize'));
        register_setting(self::SLUG, 'watsonconv_posts', array(__CLASS__, 'array_sanitize'));
        register_setting(self::SLUG, 'watsonconv_categories', array(__CLASS__, 'array_sanitize'));
    }

    public static function description_behaviour($args) {
    ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>">
            <?php esc_html_e('This section allows you to customize
                how you want the chat box to behave.', self::SLUG) ?>
        </p>
    <?php
    }

    public static function delay_render() {
    ?>
        <input name="watsonconv_delay" id="watsonconv_delay" type="number"
            value="<?php echo empty(get_option('watsonconv_delay')) ?
                        0 : get_option('watsonconv_delay')?>"
            style="width: 4em" />
        seconds
    <?php
    }

    public static function show_on_render() {
    ?>
        <input
            name="watsonconv_show_on" id="show_on_only" type="radio" value="all_except"
            <?php checked('all_except', get_option('watsonconv_show_on', 'all_except')) ?>
        >
        <label for="show_on_only">
            <?php esc_html_e('All Pages Except the Following', self::SLUG) ?>
        </label><br />
        <input
            name="watsonconv_show_on" id="show_on_all_except" type="radio" value="only"
            <?php checked('only', get_option('watsonconv_show_on', 'all_except')) ?>
        >
        <label for="show_on_all_except">
            <?php esc_html_e('Only the Following Pages', self::SLUG) ?>
        </label>
    <?php
    }

    public static function pages_render() {
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
                        <?php if (in_array($page->ID, (array)$checked_pages)) {echo 'checked';} ?>
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

    public static function posts_render() {
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
                        <?php if (in_array($post->ID, (array)$checked_posts)) {echo 'checked';} ?>
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

    public static function categories_render() {
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
                        <?php if (in_array($cat->cat_ID, (array)$checked_cats)) {echo 'checked';} ?>
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
            array(__CLASS__, 'description_appearance'), self::SLUG);

        add_settings_field('watsonconv_font_size', 'Font Size',
            array(__CLASS__, 'font_size_render'), self::SLUG, 'watsonconv_appearance');
        add_settings_field('watsonconv_color', 'Color',
            array(__CLASS__, 'color_render'), self::SLUG, 'watsonconv_appearance');
        add_settings_field('watsonconv_position', 'Position',
            array(__CLASS__, 'position_render'), self::SLUG, 'watsonconv_appearance');
        add_settings_field('watsonconv_size', 'Window Size',
            array(__CLASS__, 'size_render'), self::SLUG, 'watsonconv_appearance');
        add_settings_field('watsonconv_minimized', 'Chat Box Minimized by Default',
            array(__CLASS__, 'minimized_render'), self::SLUG, 'watsonconv_appearance');

        register_setting(self::SLUG, 'watsonconv_font_size');
        register_setting(self::SLUG, 'watsonconv_color');
        register_setting(self::SLUG, 'watsonconv_position');
        register_setting(self::SLUG, 'watsonconv_size');
        register_setting(self::SLUG, 'watsonconv_minimized', array(__CLASS__, 'boolean_sanitize'));
    }

    public static function description_appearance($args) {
    ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>">
            <?php esc_html_e('This section allows you to specify how you want
                the chat box to appear to your site visitor.', self::SLUG) ?>
        </p>
    <?php
    }

    public static function font_size_render() {
    ?>
        <input name="watsonconv_font_size" id="watsonconv_font_size"
            type="number" min=9 max=13 step=0.5 style="width: 4em"
            value="<?php echo get_option('watsonconv_font_size', 11) ?>" />
        pt
    <?php
    }

    public static function color_render() {
    ?>
        <input name="watsonconv_color" id="watsonconv_color"
            type="text" style="width: 6em"
            value="<?php echo get_option('watsonconv_color', '#23282d')?>" />
    <?php
    }

    public static function position_render() {
    ?>
        <div style='display: inline-block'>
            <input name="watsonconv_position" id="position_topleft" type="radio" value='top_left'
                <?php checked('top_left', get_option('watsonconv_position', 'bottom_right')) ?>>
            <label for="position_topleft">
                <?php esc_html_e('Top-Left', self::SLUG) ?>
                <div class='preview-window'>
                    <div class='preview-box' style='top: 1em; left: 1em'></div>
                </div>
            </label><br />
        </div>

        <div style='display: inline-block'>
            <input name="watsonconv_position" id="position_topright" type="radio" value='top_right'
                <?php checked('top_right', get_option('watsonconv_position', 'bottom_right')) ?>>
            <label for="position_topright">
                <?php esc_html_e('Top-Right', self::SLUG) ?>
                <div class='preview-window'>
                    <div class='preview-box' style='top: 1em; right: 1em'></div>
                </div>
            </label><br />
        </div><br />

        <div style='display: inline-block'>
            <input name="watsonconv_position" id="position_bottomleft" type="radio" value='bottom_left'
                <?php checked('bottom_left', get_option('watsonconv_position', 'bottom_right')) ?>>
            <label for="position_bottomleft">
                <?php esc_html_e('Bottom-Left', self::SLUG) ?>
                <div class='preview-window'>
                    <div class='preview-box' style='bottom: 1em; left: 1em'></div>
                </div>
            </label><br />
        </div>

        <div style='display: inline-block'>
            <input name="watsonconv_position" id="position_bottomright" type="radio" value='bottom_right'
                <?php checked('bottom_right', get_option('watsonconv_position', 'bottom_right')) ?>>
            <label for="position_bottomright">
                <?php esc_html_e('Bottom-Right', self::SLUG) ?>
                <div class='preview-window'>
                    <div class='preview-box' style='bottom: 1em; right: 1em'></div>
                </div>
            </label><br />
        </div>
    <?php
    }

    public static function size_render() {
    ?>
        <input name="watsonconv_size" id="size_small" type="radio" value=160
            <?php checked(160, get_option('watsonconv_size', 200)) ?> >
        <label for="size_small">
            <?php esc_html_e('Small', self::SLUG) ?>
        </label><br />

        <input name="watsonconv_size" id="size_medium" type="radio" value=200
            <?php checked(200, get_option('watsonconv_size', 200)) ?> >
        <label for="size_medium">
            <?php esc_html_e('Medium', self::SLUG) ?>
        </label><br />

        <input name="watsonconv_size" id="size_large" type="radio" value=240
            <?php checked(240, get_option('watsonconv_size', 200)) ?> >
        <label for="size_large">
            <?php esc_html_e('Large', self::SLUG) ?>
        </label>
    <?php
    }

    public static function minimized_render() {
    ?>
        <input name="watsonconv_minimized" id="minimized_yes" type="radio" value="yes"
            <?php checked(true, get_option('watsonconv_minimized', false)) ?> >
        <label for="minimized_yes">
            <?php esc_html_e('Yes', self::SLUG) ?>
        </label><br />

        <input name="watsonconv_minimized" id="minimized_no" type="radio" value="no"
            <?php checked(false, get_option('watsonconv_minimized', false)) ?> >
        <label for="minimized_no">
            <?php esc_html_e('No', self::SLUG) ?>
        </label>
    <?php
    }
}
