<?php
namespace WatsonConv;

// Logging related functionality
class Logger {
	// Description of logger related table
	public static $schema_description = array(
        "debug_log" => array(
            "id" => array("property" => "_db_id", "type" => "integer"),
            "p_message" => array("property" => "message", "type" => "string"),
            "p_details" => array("property" => "details", "type" => "string"),
            "p_event" => array("property" => "event", "type" => "string"),
            "s_created" => array("property" => "_db_timestamp", "type" => "timestamp")
        )
	);

	// Initializing logger functionality
	public static function init() {
		// Adding error log table to Storage class database description
		Storage::$schema_description["debug_log"] = self::$schema_description["debug_log"];
		// Getting debug option
		$logger_initialized = get_option("watsonconv_logger_initialized", "no");
		// If option is nonexistent, creating table
		if($logger_initialized == "no") {
			// Creating table
			self::create_log_table();
			// Adding option to Wordpress options list
			update_option("watsonconv_logger_initialized", "yes", "yes");
		}
		// Hook to register REST routes
		add_action('rest_api_init', array('WatsonConv\Logger', 'register_rest_routes'));
	}

	// Registering REST routes
	public static function register_rest_routes() {
		$plugin_rest_namespace = "watsonconv/v1";
		$logs_rest_route = "/logs/";
		// Registering route for getting logs
		register_rest_route($plugin_rest_namespace, $logs_rest_route, array(
            	'methods' => "GET",
                'callback' => array('\WatsonConv\Logger', 'get_logs')
        ));
	}

	public static function log_message($message, $details = NULL, $event = NULL) {
		// Checking if event id is passed. If not, assigning it manually.
		if($event == NULL) {
			$event = uniqid();
		}
		// Constructing array of values for log
		$values = array(
			"message" => $message,
			"details" => $details,
			"event" => $event
		);
		Storage::insert("debug_log", $values);
		self::delete_excess_log_messages();
	}

	// Creation of a table for log messages
	private static function create_log_table() {
		// Getting required variables (wpdb object, database prefix, collation)
		global $wpdb;
		$prefix = $wpdb->prefix;
		$collation = $wpdb->get_charset_collate();
		// Getting full table name with all the prefixes
		$full_table_name = Storage::get_full_table_name("debug_log");

		$table_fields = array(
            'id integer(64) UNSIGNED NOT NULL AUTO_INCREMENT',
            'p_message text NOT NULL',
            'p_details text',
            'p_event varchar(256)',
            's_created timestamp DEFAULT CURRENT_TIMESTAMP',
            'PRIMARY KEY  (id)'
        );
        // Constructing CREATE TABLE expression
        $fields_expression = "\n\t" . implode(",\n\t", $table_fields) . "\n";
        $full_expression = "CREATE TABLE {$full_table_name}({$fields_expression}){$collation};";

		// File with dbDelta function
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($full_expression);
		// Updating list of tables in Storage
		Storage::init();
	}

	// Handling WPDB errors
	public static function handle_wpdb_error($message) {
		// Global wpdb object
		global $wpdb;
		// Getting unique event id
		$event_id = uniqid();
		// Getting WP error
		$wpdb_error = $wpdb->last_error;
		// WPDB query that caused an error
		$wpdb_query = $wpdb->last_query;
		// Stack trace
		$stack_trace = debug_backtrace();
		// Function call
		$function_call = json_encode($stack_trace[1], JSON_PRETTY_PRINT);

		// Writing event details to log
		self::log_message("WPDB failure", $message, $event_id);
		self::log_message("Error", $wpdb_error, $event_id);
		self::log_message("Query", $wpdb_query, $event_id);
		self::log_message("Function call", $function_call, $event_id);
	}

	// Error message with stack trace
	public static function error_with_args($message, $details) {
		// Getting unique event id
		$event_id = uniqid();
		// Stack trace
		$stack_trace = debug_backtrace();
		// Function call
		$function_call = json_encode($stack_trace[1], JSON_PRETTY_PRINT);

		// Writing event details to log
		self::log_message($message, $details, $event_id);
		self::log_message("Function call", $function_call, $event_id);
	}

	// Log WP_Error
	public static function log_wp_error($wp_error) {
		// Error code
		$code = $wp_error->get_error_code();
		// Error message
		$message = $wp_error->get_error_message();
		// Error data
		$data = json_encode($wp_error->get_error_data());
		// Full error message
		$full_error_message = "WP Error: {$code}. {$message}";
		self::log_message($full_error_message, $data);
	}

	// Delete excess log entries
	public static function delete_excess_log_messages() {
		// Log upper limit
		$log_limit = 1000;
		// Amount of already stored messages
		$recorded_amount = Storage::count_rows("debug_log");
		// Amount of excess messages
		$excess_amount = $recorded_amount - $log_limit;

		// If there are no excess messages, returning 0
		if($excess_amount <= 0) {
			return 0;
		}

		// Deleting excess messages
		$result = Storage::delete("debug_log", NULL, $excess_amount);
		return $result;
	}

	// Get logs JSON
	public static function get_logs(\WP_REST_Request $request) {
		// Current timestamp
		$timestamp = time();
		// Timestamp of link creation
		$fetch_timestamp = (integer)get_option("watsonconv_log_fetch_ts", 0);
		// Validating timestamp
		$timestamp_valid = ( ($timestamp - $fetch_timestamp) < 60);
		// Erasing timestamp
		update_option("watsonconv_log_fetch_ts", 0);

		// Getting nonce for log fetching action from query
		$fetch_nonce = $request['fetch_nonce'];
		// Getting log fetch event id from database
		$log_fetch_event_id = get_option("watsonconv_log_fetch_event", 0);
		// Constructing action name and verifying it
		$action = "log_fetch_{$log_fetch_event_id}";
		$fetch_nonce_valid = wp_verify_nonce( $fetch_nonce, $action );
		// Erasing log fetch event id from database
		update_option("watsonconv_log_fetch_event", "erased");

		// Check if current user is permitted to control plugins
        if(!current_user_can('edit_plugins') || !$fetch_nonce_valid || !$timestamp_valid) {
            return new \WP_REST_Response('Not authorized for log fetching', 403);
        }

		$log = \WatsonConv\Storage::select("debug_log");
        $log_entries_number = count($log);
        // Making last messages appear first
        $log = array_reverse($log);

        $result = new \WP_REST_Response($log);
        $result->header("Content-Disposition", "attachment; filename=\"log.json\"");
        return $result;
	}
}

Logger::init();
