<?php
/*
Compliance plugin for Yourls - URL Shortener ~ database related functions
Copyright (c) 2016, Josh Panter <joshu@unfettered.net>
*/
// No direct call
if( !defined( 'YOURLS_ABSPATH' ) ) die();

// Create tables for this plugin when activated
yourls_add_action( 'activated_compliance/plugin.php', 'compliance_activated' );
function compliance_activated() {

	global $ydb;

	$init = yourls_get_option('compliance_init');
	if ($init === false) {
		// Create the init value
		yourls_add_option('compliance_init', time());
		// Create the flag table
		$table_flagged  = "CREATE TABLE IF NOT EXISTS flagged (";
		$table_flagged .= "keyword varchar(200) NOT NULL, ";
		$table_flagged .= "clicks int(10) NOT NULL default 0, ";
		$table_flagged .= "timestamp timestamp NOT NULL default CURRENT_TIMESTAMP, ";
		$table_flagged .= "reason text, ";
		$table_flagged .= "addr varchar(200) default NULL, ";
		$table_flagged .= "PRIMARY KEY (keyword) ";
		$table_flagged .= ") ENGINE=MyISAM DEFAULT CHARSET=latin1;";
		$tables = $ydb->query($table_flagged);

		yourls_update_option('compliance_init', time());
		$init = yourls_get_option('compliance_init');
		if ($init === false) {
			die("Unable to properly enable Compliance due an apparent problem with the database.");
		}
	}
}
// Delete table when plugin is deactivated - comment out to save flag data
yourls_add_action('deactivated_compliance/plugin.php', 'compliance_deactivate');
function compliance_deactivate() {
	global $ydb;
	
	$init = yourls_get_option('compliance_init');
	if ($init !== false) {
		yourls_delete_option('compliance_init');
		$ydb->query("DROP TABLE IF EXISTS flagged");
	}
}

function compliance_db_flush() {
	global $ydb;

	$table = 'flagged';
	$init_1 = yourls_get_option('compliance_init');

	if ($init_1 !== false) {
		$ydb->query("TRUNCATE TABLE `$table`");
		yourls_update_option('compliance_init', time());
		$init_2 = yourls_get_option('compliance_init');
		if ($init_2 === false || $init_1 == $init_2) {
			die("Unable to properly reset the database. Contact your sys admin");
		}
	}
}
?>
