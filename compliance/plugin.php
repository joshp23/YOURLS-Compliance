<?php
/*
Plugin Name: Compliance
Plugin URI: https://github.com/joshp23/YOURLS-Compliance
Description: Provides a way to flag short urls for abuse, and warn users of potential risk.
Version: 1.0
Author: Josh Panter
Author URI: https://unfettered.net
*/
// No direct call
if( !defined( 'YOURLS_ABSPATH' ) ) die();

// Database, Sys Admin, and Flag Check Functions
require ('inc/db.php');
require ('inc/sys.php');
require ('inc/chk.php');

//// ADMIN PAGE FUNCTIONS

// Register admin forms
yourls_add_action( 'plugins_loaded', 'compliance_add_pages' );
function compliance_add_pages() {
        yourls_register_plugin_page( 'compliance', 'Compliance', 'compliance_do_page' );
}

// Display page 0 - skelleton and flow
function compliance_do_page() {

	// CHECK if the BEHAVIOR form was submitted
	compliance_update_op_behavior();
	
	// Retreive BEHAVIOR settings & Set Values
	$compliance_nuke = yourls_get_option( 'compliance_nuke' );
	if ($compliance_nuke !== "true") {
		$nuke_chk = null;
		$vis_del = 'inline';
		} else {
		$nuke_chk = 'checked';
		$vis_del = 'none';
		}

	$compliance_expose_flags = yourls_get_option( 'compliance_expose_flags' ); 
	if ($compliance_expose_flags !== "false") {
		$exp_chk = 'checked';
		} else {
		$exp_chk = null;
		}

	$compliance_cust_toggle = yourls_get_option( 'compliance_cust_toggle' );
	if ($compliance_cust_toggle !== "true") {
		$url_chk = null;
		$vis_url = 'none';
		} else {
		$url_chk = 'checked';
		$vis_url = 'inline';
		}
		
	$compliance_intercept = yourls_get_option( 'compliance_intercept' );

	// CHECK if the DATABASE form was submitted
	compliance_update_op_db();

	$compliance_table_drop = yourls_get_option( 'compliance_table_drop' );

	if ($compliance_table_drop == "false") {
		$drop_chk = null;
		}
	if ($compliance_table_drop == "true" || $compliance_table_drop == null) {
		$drop_chk = 'checked';
		}

	// CHECK if the DATABASE FLUSH form was submitted
	compliance_flush_flags();

	// Create nonce
	$nonce = yourls_create_nonce( 'compliance' );

	// Main interface html
	$vars = array();
		$vars['vis_del'] = $vis_del;
		$vars['vis_url'] = $vis_url;
		$vars['nuke_chk'] = $nuke_chk;
		$vars['exp_chk'] = $exp_chk;
		$vars['url_chk'] = $url_chk;
		$vars['drop_chk'] = $drop_chk;
		$vars['compliance_intercept'] = $compliance_intercept;
		$vars['nonce'] = $nonce;

	$opt_0_view = file_get_contents( dirname( __FILE__ ) . '/inc/opt.0.php', NULL, NULL, 200);
	// Replace all %stuff% in the notice with variable $stuff
	$opt_0_view = preg_replace_callback( '/%([^%]+)?%/', function( $match ) use( $vars ) { return $vars[ $match[1] ]; }, $opt_0_view );

	echo $opt_0_view;

	compliance_flag_list_mgr(); // sys
}

// Display page 0.1 - listing the flags !IF NO NUKES!
function flag_list() {
	// should we bother with this data, has the "nuke" option been set?"
	$compliance_nuke = yourls_get_option( 'compliance_nuke' );
	if ($compliance_nuke !== "true") {
		// no nuke, draw flaglist page ~ this picks up where opt.0.php leaves off.
		global $ydb;

		$opt_1_view = file_get_contents( dirname( __FILE__ ) . '/inc/opt.1.php', NULL, NULL, 200);
		echo $opt_1_view;
		
		// populate table rows with flag data if there is any
		$table = 'flagged';
		$flagged_list = $ydb->get_results("SELECT * FROM `$table` ORDER BY timestamp DESC");
		$found_rows = false;
		if($flagged_list) {
			$found_rows = true;
			foreach( $flagged_list as $flag ) {
				$alias = $flag->keyword;
				$timestamp = strtotime($flag->timestamp);
				$reason = $flag->reason;
				$contact = $flag->addr;
				$clicks = $flag->clicks;
				$date = date( 'M d, Y H:i', $timestamp);
				$unflag = ''. $_SERVER['PHP_SELF'] .'?page=compliance&action=unflag&key='. $alias .'';
				// print if there is any data
				echo <<<HTML
					<tr>
						<td>$alias</td>
						<td>$reason</td>
						<td>$contact</td>
						<td>$date</td>
						<td>$clicks</td>
						<td><a href="$unflag">Unflag <img src="/images/delete.png" title="UnFlag" border=0></a></td>
					</tr>
HTML;
			}
		}
		echo "</tbody>\n";
		echo "</table>\n";
		echo "</form>\n";
	}
	// close flaglist div and the rest of the settings page
				echo "</div>\n";
			echo "</div>\n";
			echo "</div>\n";
		echo "</div>\n";
}

// Display page 0.2 - adding a flag
function flag_add() {
	global $ydb;
	
	if (!empty($_POST) && isset($_POST['alias']) && isset($_POST['reason']) && isset($_POST['contact'])) {

		$table = "flagged";
		$alias = $_POST['alias'];
		$reason = $_POST['reason'];
		$contact = $_POST['contact'];

		if (yourls_keyword_is_taken( $alias ) == true) {

			$insert = $ydb->query("REPLACE INTO `$table` (keyword, reason, addr) VALUES ('$alias', '$reason', '$contact')");
		} else {
		echo '<h3 style="color:red">ERROR: No such URL in our database. Please try again.</h3>';
		}
	}

	flag_list();
}

// Display page 0.3 - removing a flag
function remove_flag() {
	global $ydb;

	if( isset($_GET['key']) ) {
		$table = 'flagged';
		// @@@FIXME@@@ needs securing against SQL injection !
		$key = $_GET['key'];
        	$delete = $ydb->query("DELETE FROM `$table` WHERE keyword='$key'");
	}
	// @@@FIXME@@@ This should probably be rewritten to do a redirect to avoid confusion between GET/POST forms
	flag_list();
}
?>
