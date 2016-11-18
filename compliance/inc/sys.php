<?php
// Compliance plugin for Yourls - URL Shortener ~ Various sys admin functions (basically, the ugly code goes here)
// Copyright (c) 2016, Josh Panter <joshu@unfettered.net>

// No direct call
if( !defined( 'YOURLS_ABSPATH' ) ) die();

// Options updater: Behavior
function compliance_update_op_behavior() {
	if(isset( $_POST['compliance_nuke'])) {
		// Check nonce
		yourls_verify_nonce( 'compliance' );

		yourls_update_option( 'compliance_nuke', $_POST['compliance_nuke'] );
	
		if(isset($_POST['compliance_cust_toggle'])) yourls_update_option( 'compliance_cust_toggle', $_POST['compliance_cust_toggle'] );
		if(isset($_POST['compliance_intercept'])) yourls_update_option( 'compliance_intercept', $_POST['compliance_intercept'] );
		if(isset($_POST['compliance_expose_flags'])) yourls_update_option( 'compliance_expose_flags', $_POST['compliance_expose_flags'] );
	}
}

// Options updater: DB
function compliance_update_op_db() {
	if( isset( $_POST['compliance_table_drop'] ) ) {
		// Check nonce
		yourls_verify_nonce( 'compliance' );
		// Process DATABASE form - update option in database
		yourls_update_option( 'compliance_table_drop', $_POST['compliance_table_drop'] );
		}
}

// Special OPS: Flush FLags
function compliance_flush_flags() {
	if( isset( $_POST['compliance_table_flush'] ) ) {
		if( $_POST['compliance_table_flush'] == 'yes' ) {
		// Check nonce
		yourls_verify_nonce( 'compliance' );
		compliance_db_flush();
		echo 'Database reset, all falgs dropped. Have a nice day!';
		}
	}
}

// CHECK if FLAG/UNFLAG form was submitted, handle flaglist
function compliance_flag_list_mgr() {
 if( isset( $_GET['action'] ) && $_GET['action'] == 'unflag' ) {
            remove_flag();
		} else if( isset( $_POST['action'] ) && $_POST['action'] == 'flag' ) {
            flag_add();
    	} else {
            flag_list();
	}
}

// Mark flagged links on admin page - todo, add unflag option & link to flaglist
yourls_add_filter( 'table_add_row', 'show_flagged_tablerow' );
function show_flagged_tablerow($row, $keyword, $url, $title, $ip, $clicks, $timestamp) {

	// Check if this is wanted
	$compliance_expose_flags = yourls_get_option( 'compliance_expose_flags' ); 
	if($compliance_expose_flags !== "false") {

		// If the row is malware, make the URL show in red;
		$WEBPATH=substr(dirname(__FILE__), strlen(YOURLS_ABSPATH));
		$flagset = check_flagpage($url, $keyword);
		if($flagset !== false) {
			$old_key = '/td class="keyword"/';
			$new_key = 'td class="keyword" style="border-left: 6px solid red;"';
			$newrow = preg_replace($old_key, $new_key, $row);
			return $newrow;
		} else {
		$newrow = $row;
		}
		return $newrow;
	} else {
	return $row;
	}
}

// House keeping: Clean up flags on link delete
yourls_add_action( 'delete_link', 'delete_flagged_link_by_keyword' );

function delete_flagged_link_by_keyword( $args ) {
	global $ydb;

    $keyword = $args[0]; // Keyword to delete

	// Delete the flag data, no need for it anymore
	$ftable = "flagged";
	$ydb->query("DELETE FROM `$ftable` WHERE `keyword` = '$keyword';");

	// Uncomment to delete log-entries for deleted URL
	$ltable = YOURLS_DB_TABLE_LOG;
	$ydb->query("DELETE FROM `$ltable` WHERE `shorturl` = '$keyword';");
}
?>
