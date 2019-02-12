<?php
namespace WatsonConv;
// Update functions for Watson Assistant Plugin

class UpdateFunctions {
	// Initialize update functionality options
	public static function watsonconv_000_initialize() {
		add_option('watsonconv_last_applied_update', 'none');
	}

	// Build database for 0.8.4
	public static function watsonconv_084_create_database() {
		// Global Wordpress database object
		global $wpdb;
		// Wordpress collation
		$collate = '';
		if ( $wpdb->has_cap('collation') ) {
			$collate = $wpdb->get_charset_collate();
		}
		// Wordpress prefix
		$prefix = $wpdb->prefix;
		// Plugin storage prefix
		$plugin_prefix = Storage::$storage_prefix;
		// Full prefix (wordpress + plugin)
		$full_prefix = $prefix . $plugin_prefix;
		// Array with table names and fields
		$tables_array = array(
			'sessions' => array(
				'id binary(16) NOT NULL',
				's_created timestamp DEFAULT CURRENT_TIMESTAMP',
				'PRIMARY KEY  (id)'
			),
			'requests' => array(
				'id integer(64) UNSIGNED NOT NULL AUTO_INCREMENT',
				'a_session_id binary(16) NOT NULL',
				's_created timestamp DEFAULT CURRENT_TIMESTAMP',
				'o_user_input_id integer(64)',
				'o_input_context_id integer(64)',
				'o_output_context_id integer(64)',
				'p_debug_output json',
				'p_user_defined json',
				'PRIMARY KEY  (id)'
			),
			'user_inputs' => array(
				'id integer(64) UNSIGNED NOT NULL AUTO_INCREMENT',
				'p_message_type enum("text")',
				'p_text text NOT NULL',
				'p_debug boolean',
				'p_restart boolean',
				'p_alternate_intents boolean',
				'p_return_context boolean',
				'PRIMARY KEY  (id)'
			),
			'watson_outputs' => array(
				'id integer(64) UNSIGNED NOT NULL AUTO_INCREMENT',
				'a_request_id integer(64) UNSIGNED NOT NULL',
				'p_response_type enum("text","pause","image","option","connect_to_agent","suggestion") NOT NULL',
				'p_text varchar(2048)',
				'p_time integer(16)',
				'p_typing boolean',
				'p_source varchar(2048)',
				'p_title varchar(2048)',
				'p_description varchar(2048)',
				'p_preference enum("dropdown", "button")',
				'p_options json',
				'p_message_to_human_agent varchar(2048)',
				'p_topic varchar(2048)',
				'p_suggestions json',
				'PRIMARY KEY  (id)'
			),
			'contexts' => array(
				'id integer(64) UNSIGNED NOT NULL AUTO_INCREMENT',
				'p_global json',
				'p_skills json',
				'PRIMARY KEY  (id)'
			),
			'intents' => array(
				'id integer(64) UNSIGNED NOT NULL AUTO_INCREMENT',
				'p_intent varchar(512) NOT NULL',
				'p_confidence double(64, 30) NOT NULL',
				'PRIMARY KEY  (id)'
			),
			'entities' => array(
				'id integer(64) UNSIGNED NOT NULL AUTO_INCREMENT',
				'p_entity varchar(512) NOT NULL',
				'p_location json NOT NULL',
				'p_value varchar(1024) NOT NULL',
				'p_confidence double(64, 30)',
				'p_metadata json',
				'p_groups json',
				'PRIMARY KEY  (id)'
			),
			'input_intents' => array(
				'id integer(64) UNSIGNED NOT NULL AUTO_INCREMENT',
				'a_request_id integer(64) UNSIGNED NOT NULL',
				'o_intent_id integer(64) UNSIGNED NOT NULL',
				'PRIMARY KEY  (id)'
			),
			'output_intents' => array(
				'id integer(64) UNSIGNED NOT NULL AUTO_INCREMENT',
				'a_request_id integer(64) UNSIGNED NOT NULL',
				'o_intent_id integer(64) UNSIGNED NOT NULL',
				'PRIMARY KEY  (id)'
			),
			'input_entities' => array(
				'id integer(64) UNSIGNED NOT NULL AUTO_INCREMENT',
				'a_request_id integer(64) UNSIGNED NOT NULL',
				'o_entity_id integer(64) UNSIGNED NOT NULL',
				'PRIMARY KEY  (id)'
			),
			'output_entities' => array(
				'id integer(64) UNSIGNED NOT NULL AUTO_INCREMENT',
				'a_request_id integer(64) UNSIGNED NOT NULL',
				'o_entity_id integer(64) UNSIGNED NOT NULL',
				'PRIMARY KEY  (id)'
			),
			'actions' => array(
				'id integer(64) UNSIGNED NOT NULL AUTO_INCREMENT',
				'a_request_id integer(64) UNSIGNED NOT NULL',
				'p_name varchar(512) NOT NULL',
				'p_result_variable varchar(512) NOT NULL',
				'p_type enum("client", "server", "web-action", "cloud-function")',
				'p_parameters json',
				'p_credentials varchar(5120)',
				'PRIMARY KEY  (id)'
			)
		);
		// Empty array for sql expressions
		$sql_expressions = array();
		// Generating sql expressions
		foreach($tables_array as $table_name => $table_fields) {
			// Table name
			$table_start = "CREATE TABLE {$full_prefix}{$table_name} (\n";
			// All the fields together
			$fields = implode(",\n\t", $table_fields);
			// Table end with collation
			$table_end = "\n) {$collate};";
			// Full expression for table creation
			$sql_expression = $table_start . $fields . $table_end;
			// Adding it to the array pf sql expressions
			array_push($sql_expressions, $sql_expression);
		}

		// Wordpress file with dbDelta
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		// Iterating through all the generated expressions and calling db
		foreach($sql_expressions as $sql_expression) {
			dbDelta($sql_expression);
		}
	}
}
