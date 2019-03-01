<?php
// Email Notificator
namespace WatsonConv;

class Email_Notificator {
    public function __construct() {
        add_action("init", array(__CLASS__, "run"));
    }

    public static function run() {
        $enabled = get_option('watsonconv_notification_enabled', '') === 'yes';
        if ($enabled) {
            $prev_ts = intval(get_option('watsonconv_notification_summary_prev_ts', 0));
            $dt = time() - $prev_ts;

            $interval = intval(get_option('watsonconv_notification_summary_interval', 0));

            if ($interval > 0 && $dt > $interval) {
                self::send_summary_notification();
                self::reset_summary_prev_ts();
            }
        }
    }

    public static function reset_summary_prev_ts() {
        update_option('watsonconv_notification_summary_prev_ts', time());
    }

    /**
     * @param bool $force_send
     * @return bool
     */
    public static function send_summary_notification($force_send=false, $emails=NULL) {
        $res = false;

        if(empty($emails)) {
            $emails = get_option('watsonconv_notification_email_to', '');
        }

        $emails_array = explode(",", $emails);
        $errors_array = array();
        foreach($emails_array as $email) {
            $email = trim($email);

            $prev_ts = intval(get_option('watsonconv_notification_summary_prev_ts', 0));
            $topic = 'Watson Assistant plug-in for WordPress: ChatBot Usage Summary';
            $count = self::get_session_count_since_last_time($prev_ts);
            if ($count > 0 || $force_send) {
                $message = 'ChatBot served ' . $count . ' session(s) since ' . date('r', $prev_ts);
                $res = wp_mail($email, $topic, $message);

                if(!$res) {
                    array_push($errors_array, $GLOBALS['phpmailer']->ErrorInfo);
                }
            }
        }

        if(count($errors_array) > 0) {
            return $errors_array;
        }
        else {
            return true;
        }
    }

    /**
     * @param integer $since_ts - unix timestamp
     * @return integer
     */
    private static function get_session_count_since_last_time($since_ts) {
        global $wpdb;
        $tname = \WatsonConv\Storage::get_full_table_name('sessions');
        $count = intval($wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM '.$tname.' WHERE s_created > FROM_UNIXTIME(%d)', $since_ts)));
        return $count;
    }
}

new Email_Notificator();
