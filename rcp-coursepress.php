<?php
/**
 * Main plugin file
 *
 * @package svbk-rcp-coursepress
 */

/*
Plugin Name: Restrict Content Pro - Coursepress
Description: Buy CoursePress Courses via Restrict Content Pro
Author: Silverback Studio
Version: 1.0
Author URI: http://www.silverbackstudio.it/
Text Domain: svbk-rcp-coursepress
*/

use Svbk\WP\Plugins\RCP\CoursePress\RCP_CoursePress;

define( 'SVBK_RCP_COURSEPRESS_PLUGIN_FILE', __FILE__ );

/**
 * Loads textdomain and main initializes main class
 *
 * @return void
 */
function svbk_rcp_coursepress_init() {
	load_plugin_textdomain( 'svbk-rcp-coursepress', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	if ( ! class_exists( 'Svbk\WP\Plugins\RCP\CoursePress\RCP_CoursePress' ) ) {
		require_once 'src/RCP_CoursePress.php';
	}

	$svbk_rcp_coursepress = new RCP_CoursePress();

	add_action( 'rcp_add_subscription_form', array( $svbk_rcp_coursepress, 'admin_subscription_form' ) );
	add_action( 'rcp_edit_subscription_form', array( $svbk_rcp_coursepress, 'admin_subscription_form' ) );

	add_action( 'rcp_add_subscription', array( $svbk_rcp_coursepress, 'admin_subscription_form_save' ), 10, 2 );
	add_action( 'rcp_pre_edit_subscription_level', array( $svbk_rcp_coursepress, 'admin_subscription_form_save' ), 10, 2 );

	add_filter( 'coursepress_enroll_student', array( $svbk_rcp_coursepress, 'allow_enroll' ) , 10, 3 );
	add_action( 'rcp_member_post_set_subscription_id', array( $svbk_rcp_coursepress, 'enroll' ), 10, 3 );

	// // Add additional fields to Course Setup Step 6 if paid is checked
	// add_filter(
	// 'coursepress_course_setup_step_6_paid',
	// array( $svbk_rcp_coursepress, 'product_settings'),
	// 10, 2
	// );
	// // Respond to Course Update/Create
	// add_filter(
	// 'coursepress_course_update_meta',
	// array( $svbk_rcp_coursepress, 'settings_save' ),
	// 10, 2
	// );
	// add_filter( 'rcp_metabox_excluded_post_types', array( $svbk_rcp_coursepress, 'exclude_post_type') );
	// add_filter( 'coursepress_payment_supported', '__return_true' );
}

add_action( 'plugins_loaded', 'svbk_rcp_coursepress_init' );



