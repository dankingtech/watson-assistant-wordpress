<?php

// Installation and update related code
namespace WatsonConv;

// Direct call prevention
defined( 'ABSPATH' ) || exit;

class Install {
	// Versions and callbacks to upgrade functions
	private static $update_callbacks = array(
		'0.0.0' => array(
			'watsonconv_000_initialize'
		),
		'0.8.4' => array(
			'watsonconv_084_create_database'
		)
	);

	public static function init() {
		// Checking if we are in admin area. If not, exiting
		if(!is_admin()) {
			return false;
		}

		// Getting plugin version and last applied update
		$plugin_version = self::get_plugin_version();
		$last_update = self::get_last_update();
		// Updates we need to apply list
		$updates_to_apply = array();

		// Looking if we've found last applied update and current version
		$last_applied_update_found = false;
		// If there's no information about updates, apply them all
		if($last_update == "none") {
			$last_applied_update_found = true;
		}
		$current_version_found = false;
		// Iterating through versions
		foreach(self::$update_callbacks as $version => $update) {
			// Iterating through update callbacks of each version
			foreach($update as $update_callback) {
				// If last applied update is already found, adding current to
				// the list
				if($last_applied_update_found) {
					array_push($updates_to_apply, $update_callback);
				}
				// Checking if current update is the last applied one
				else if($update_callback == $last_update) {
					$last_applied_update_found = true;
				}
			}

			// If we've reached current plugin version, don't look for next one
			if($version == $plugin_version) {
				break;
			}
		}

		// If there are no updates to apply, exit
		if(count($updates_to_apply) == 0) {
			return false;
		}

		// Iterating through updates and applying them
		foreach($updates_to_apply as $update_to_apply) {
			// If this update is not scheduled yet, scheduling it
			if(! Background_Task_Runner::task_already_exists("apply_update", $update_to_apply) ) {
				Background_Task_Runner::new_task("apply_update", $update_to_apply);
			}
		}
	}

	// Getting last applied update
	public static function get_last_update() {
		return get_option("watsonconv_last_applied_update", "none");
	}

	// Getting plugin version
	public static function get_plugin_version() {
		return get_file_data(WATSON_CONV_FILE, array("Version" => "Version"));
	}

	// Applying update
	public static function apply_update($update_callback) {
		// Including file with update functions
		require_once(WATSON_CONV_PATH.'includes/update_functions.php');
		// Calling update function
		UpdateFunctions::$update_callback();
		// Updating "last applied update" value in database
		update_option("watsonconv_last_applied_update", $update_callback);
	}
}

Install::init();
