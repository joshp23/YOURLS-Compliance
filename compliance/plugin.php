<?php
/*
Plugin Name: Compliance
Plugin URI: https://github.com/joshp23/YOURLS-Compliance
Description: Provides a way to flag short urls for abuse, and warn users of potential risk.
Version: 1.0
Author: Josh Panter
Author URI: https://unfettered.net
*/
// Compliance plugin for Yourls - URL Shortener
// Copyright (c) 2016, Josh Panter <joshu@unfettered.net>

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
// This section will delete all tables when plugin is deactivated - comment out to save flag data
yourls_add_action('deactivated_compliance/plugin.php', 'compliance_deactivate');

function compliance_deactivate() {
	global $ydb;
	
	$init = yourls_get_option('compliance_init');
	if ($init !== false) {
		yourls_delete_option('compliance_init');
		$ydb->query("DROP TABLE IF EXISTS flagged");
	}
}

// Add forms to work with flagged links
yourls_add_action( 'plugins_loaded', 'compliance_add_pages' );

function compliance_add_pages() {
        yourls_register_plugin_page( 'compliance', 'Compliance', 'compliance_do_page' );
}

// Display page 0 - skelleton and flow
function compliance_do_page() {

	// Check if the behavior form was submitted
	if( isset( $_POST['compliance_nuke'] ) ) {
		// Check nonce
		yourls_verify_nonce( 'compliance' );
		
		// Process behavior form - update option in database
		yourls_update_option( 'compliance_nuke', $_POST['compliance_nuke'] );
		if(isset($_POST['compliance_cust_toggle'])) yourls_update_option( 'compliance_cust_toggle', $_POST['compliance_cust_toggle'] );
		if(isset($_POST['compliance_intercept'])) yourls_update_option( 'compliance_intercept', $_POST['compliance_intercept'] );
		//if(isset($_POST['compliance_flush_db'])) yourls_update_option( 'compliance_flush_db', $_POST['compliance_flush_db'] );
	}
	// Get values from database
	$compliance_nuke = yourls_get_option( 'compliance_nuke' );

	if ($compliance_nuke == "false" || $compliance_nuke == null) {
		$nuke_chk = null;
		$vis_del = 'inline';
		}
	if ($compliance_nuke == "true") {
		$nuke_chk = 'checked';
		$vis_del = 'none';
		}

	$compliance_cust_toggle = yourls_get_option( 'compliance_cust_toggle' );

	if ($compliance_cust_toggle == "false" || $compliance_cust_toggle == null) {
		$url_chk = null;
		$vis_url = 'none';
		}
	if ($compliance_cust_toggle == "true") {
		$url_chk = 'checked';
		$vis_url = 'inline';
		}
	$compliance_intercept = yourls_get_option( 'compliance_intercept' );
	// Create nonce
	$nonce = yourls_create_nonce( 'compliance' );

	echo <<<HTML

		<h2>Handling Compliance Behavior</h2>
		<p>The default behavior in Compliance is to preserve flagged links, opting to intercept all flagged redirects, and notify the users with an informational warning page, thus giving them the choice of action. This prevents abusive arbitrary deleting or disabling of short URLS by the public.</p>
		<p>This behavior can be overridden here, functionally allowing any user with access to the abuse page to delete any Short URL. <b>Use with caution</b>. This will make it so that if a flagged link is visited after being flagged, instead of interception the Short URL is merely deleted from the database.</p>
	
		<form method="post">
			<div class="checkbox">
			  <label>
			    <input type="hidden" name="compliance_nuke" value="false" />
			    <input name="compliance_nuke" type="checkbox" value="true" $nuke_chk > Delete all flagged links?
			  </label>
			</div>

			<input type="hidden" name="nonce" value="$nonce" />

			<div style="display:$vis_del;">
				<p>Compliance provides a well formed and functional Notice, or warning page written in bootstrap. You can opt to use your own, however.</p>
				<div class="checkbox">
				  <label>
				    <input type="hidden" name="compliance_cust_toggle" value="false" />
				    <input name="compliance_cust_toggle" type="checkbox" value="true" $url_chk >Use Custom Intercept URL?
				  </label>
				</div>
				<div style="display:$vis_url;">
					<p>Setting the above option without setting this will result in an endles refresh.</p>
					<p><label for="compliance_intercept">Enter intercept URL here</label> <input type="text" size=40 id="compliance_intercept" name="compliance_intercept" value="$compliance_intercept" /></p>
				</div>
			</div>

			<p><input type="submit" value="Submit" /></p>
		</form>
		<div style="display:$vis_del;">
 			<h2>Flagged URL List</h2>
		</div>
HTML;

        if( isset( $_GET['action'] ) && $_GET['action'] == 'unflag' ) {
                remove_flag();
	} else if( isset( $_POST['action'] ) && $_POST['action'] == 'flag' ) {
                flag_add();
        } else {
                flag_list();
        }
}
// Display page 0.1 - listing the flags
function flag_list() {
	$compliance_nuke = yourls_get_option( 'compliance_nuke' );
	if ($compliance_nuke == "false" || $compliance_nuke == null) {

		global $ydb;
		echo "<p>When flagging a url from here, make sure that you only put in the alias, the part after the slash. So if you are flagging https://example.com/<b>THIS</b> -> only add <b>THIS</b>.</p>";
		echo "<form method=\"post\">\n";
		echo "<table id=\"main_table\" class=\"tblSorter\" border=\"1\" cellpadding=\"5\" style=\"border-collapse: collapse\">\n";
		echo "<thead><tr><th>Flagged Short URL</th><th>Reason</th><th>Email</th><th>Date</th><th>Clicks</th><th>&nbsp;</th></tr></thead>\n";
		echo "<tbody>\n";
		echo "<tr><td><input type=\"text\" name=\"alias\" size=12></td>";
		echo "<td><input type=\"text\" name=\"reason\" size=40></td>";
		echo "<td><input type=\"text\" name=\"reportemail\" size=30></td>";
		echo "<td><size=30>n/a</td>";
		echo "<td><size=10>n/a</td>";
		echo "<td colspan=3 align=right><input type=submit name=\"submit\" value=\"Flag this!\"><input type=\"hidden\" name=\"action\" value=\"flag\"></td></tr>";

		$table = 'flagged';
		$flagged_list = $ydb->get_results("SELECT * FROM `$table` ORDER BY timestamp DESC");
		$found_rows = false;
		if($flagged_list) {
			$found_rows = true;
			foreach( $flagged_list as $flag ) {
				$alias = $flag->keyword;
				$timestamp = strtotime($flag->timestamp);
				$reason = $flag->reason;
				$reportemail = $flag->addr;
				$clicks = $flag->clicks;
				$date = date( 'M d, Y H:i', $timestamp+( YOURLS_HOURS_OFFSET * 3600) );
				echo "<tr><td>$alias</td>";
				echo "<td>$reason</td>";
				echo "<td>$reportemail</td>";
				echo "<td>$date</td>";
				echo "<td>$clicks</td>";
				echo "<td><a href=\"".$_SERVER['PHP_SELF']."?page=compliance&action=unflag&key=$alias\"><img src=\"/images/delete.png\" title=\"UnFlag\" align=right border=0></a></td></tr>\n";
			}
		}
		echo "</tbody>\n";
		echo "</table>\n";
		echo "</form>\n";
	}
}
// Display page 0.2 - adding a flag
function flag_add() {
	global $ydb;

	$alias = "";
	$reason = "";
	$reportemail = "";
	if(isset($_POST['alias'])) $alias=mysql_escape_string($_POST['alias']);
	if(isset($_POST['reason'])) $reason=mysql_escape_string($_POST['reason']);
	if(isset($_POST['reportemail'])) $reportemail=mysql_escape_string($_POST['reportemail']);

	if (!empty($alias)) {
	    $table = "flagged";
	    $insert = $ydb->query("REPLACE INTO `$table` (keyword, reason, addr) VALUES ('$alias', '$reason', '$reportemail')");
	}

	flag_list();
}
// Display page 0.3 - unflagging a shorturl
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

// Flag check hook on basic redirect
yourls_add_action( 'redirect_shorturl', 'check_safe_redirection' );

// Flag check 0 ~ skelleton and flow
function check_safe_redirection( $args ) {
	global $ydb;

        $url = $args[0]; // Target URL to scan
        $keyword = $args[1]; // Keyword for this request
       
	$result = check_flagpage($url, $keyword);  

	if($result !== false) {

		// A hit was found, and we're not nuking. Check for custom intercept
		$compliance_cust_toggle = yourls_get_option( 'compliance_cust_toggle' );
		if ($compliance_cust_toggle == "true") {
			// pass keyword and url to redirect?
			$compliance_intercept = yourls_get_option( 'compliance_intercept' );
			yourls_redirect( $compliance_intercept, 302 );
			die ();
		}
		// Or go to default flag intercept
		display_flagpage($keyword, $result['reason']);
	}	
}
// Flag check 0.1 ~ checking
function check_flagpage($url, $keyword='') {
	global $ydb;

	$result = false;

	// Safety check: Was the url flagged?
	$table = 'flagged';
	$flagged = $ydb->get_row("SELECT * FROM `$table` WHERE `keyword` = '$keyword'");
	if( $flagged ) {

		// A hit was found. Check for nuke
		$compliance_nuke = yourls_get_option( 'compliance_nuke' );
		if ($compliance_nuke == "true") {
			// Delete link & die
			yourls_delete_link_by_keyword( $keyword );
			yourls_die( 'This short URL has been flagged by our community, and deleted from our records.', 'Domain blacklisted', '403' );
			}
		$flagged = (array)$flagged;
		$result['reason'] = $flagged['reason'];
	}

	return $result;
}
// Flag check 0.2 ~ interstitial warning
function display_flagpage($keyword, $reason) {

        $title = yourls_get_keyword_title( $keyword );
        $url   = yourls_get_keyword_longurl( $keyword );
        $base  = YOURLS_SITE;
	$img   = yourls_plugin_url( dirname( __FILE__ ).'/assets/caution.png' );
	$css   = yourls_plugin_url( dirname( __FILE__ ).'/assets/bootstrap.min.css' );

	$vars = array();
		$vars['keyword'] = $keyword;
		$vars['reason'] = $reason;
		$vars['title'] = $title;
		$vars['url'] = $url;
		$vars['base'] = $base;
		$vars['img'] = $img;
		$vars['css'] = $css;

	$notice = file_get_contents( dirname( __FILE__ ) . '/notice.php' );
	// Replace all %stuff% in the notice with variable $stuff
	$notice = preg_replace_callback( '/%([^%]+)?%/', function( $match ) use( $vars ) { return $vars[ $match[1] ]; }, $notice );

	echo $notice;

	die();
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
