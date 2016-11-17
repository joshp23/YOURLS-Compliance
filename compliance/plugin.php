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

	echo <<<HTML

		<link rel="stylesheet" href="/css/infos.css?v=1.7.2" type="text/css" media="screen" />
		<script src="/js/infos.js?v=1.7.2" type="text/javascript"></script>

		<div id="wrap">

			<div class="sub_wrap">
			<div id="tabs">
		
				<div class="wrap_unfloat">
					<ul id="headers" class="toggle_display stat_tab">
						<li class="selected"><a href="#stat_tab_behavior"><h2>Behavior</h2></a></li>
						<li style="display:$vis_del;"><a href="#stat_tab_flag_list"><h2>Flag List</h2></a></li>
						<li><a href="#stat_tab_db"><h2>Database Settings</h2></a></li>
					</ul>
				</div>

				<div id="stat_tab_behavior" class="tab">

					<h2>Handling Compliance Behavior</h2>

					<p>The default behavior in Compliance is to preserve flagged links, opting to intercept all flagged redirects, and notify the users with an informational warning page, thus giving them the choice of action. This prevents abusive arbitrary deleting or disabling of short URLS by the public.</p>

					<h3>Override Defaut: Flag & Intercept</h3>

					<p>This functionally allows any user with access to the abuse page to delete any Short URL. <b>Use with caution</b>. This will make it so that if a flagged link is visited after being flagged, instead of interception the Short URL is merely deleted from the database.</p>
	
					<form method="post">
						<div class="checkbox">
						  <label>
							<input name="compliance_nuke" type="hidden" value="false" />
							<input name="compliance_nuke" type="checkbox" value="true" $nuke_chk > Delete flagged links? (instead of interecept)
						  </label>
						</div>

						<div style="display:$vis_del;">

					<h3>Override Default: Intercept Page</h3>

							<p>Compliance provides a well formed and functional Notice, or warning page written in bootstrap. You can opt to use your own, however.</p>

							<div class="checkbox">
							  <label>
								<input name="compliance_cust_toggle" type="hidden" value="false" />
								<input name="compliance_cust_toggle" type="checkbox" value="true" $url_chk >Use Custom Intercept URL?
							  </label>
							</div>
							<div style="display:$vis_url;">

								<p>Setting the above option without setting this will result in an endles refresh.</p>

								<p><label for="compliance_intercept">Enter intercept URL here</label> <input type="text" size=40 id="compliance_intercept" name="compliance_intercept" value="$compliance_intercept" /></p>
							</div>

						</div>
						<input type="hidden" name="nonce" value="$nonce" />
						<p><input type="submit" value="Submit" /></p>
					</form>
				</div>

				<div id="stat_tab_db" class="tab">

					<h2>Database Settings</h2>

					<h3>Table management on plugin disable</h3>

					<p>By default Compliance will drop its databse table when the plugin is disabled. You can override this setting here.</p>
					<form method="post">
						<div class="checkbox">
						  <label>
							<input name="compliance_table_drop" type="hidden" value="false" />
							<input name="compliance_table_drop" type="checkbox" value="true" $drop_chk > Drop Compliance table on disable?
						  </label>
						</div>
						<input type="hidden" name="nonce" value="$nonce" />
						<p><input type="submit" value="Submit" /></p>
					</form>

					<h3>Flush Flaglist Data</h3>

					<p>This will give you a fresh and clean table.</p>

					<form method="post">
						<div class="checkbox">
						  <label>
							<input name="compliance_table_flush" type="hidden" value="no" />
							<input name="compliance_table_flush" type="checkbox" value="yes"> Are you sure you want to flush the table?
						  </label>
						</div>
						<input type="hidden" name="nonce" value="$nonce" />
						<p><input type="submit" value="FLUSH!" /></p>
					</form>
					<p>Don't forget to return here after submitting to check for errors!</p>
				</div>

				<div  id="stat_tab_flag_list" class="tab">

		 			<h2>Flagged URL List</h2>
HTML;

    compliance_flag_list_mgr(); // sys
}

// Display page 0.1 - listing the flags !IF NO NUKES!
function flag_list() {
	// should we bother with this data, has the "nuke" option been set?"
	$compliance_nuke = yourls_get_option( 'compliance_nuke' );
	if ($compliance_nuke == "false" || $compliance_nuke == null) {
		// no nuke, draw flaglist page ~ this picks up where the html in Display page 0 leaves off.
		global $ydb;

		echo <<<HTML

		<p>When flagging a url from here, make sure that you only put in the alias, the part after the slash. So if you are flagging https://example.com/<b>THIS</b> -> only add <b>THIS</b>.</p>
		<p>Don't forget to return here after submitting to check for messages!</p>
		
		<form method="post">
			<table id="main_table" class="tblSorter" border="1" cellpadding="5" style="border-collapse: collapse">
				<thead>
					<tr>
						<th>Flagged Alias</th>
						<th>Reason</th>
						<th>Email</th>
						<th>Time and Date of Complaint</th>
						<th>Clicks</th>
						<th>&nbsp;</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><input type="text" name="alias" size=8></td>
						<td><input type="text" name="reason" size=30></td>
						<td><input type="text" name="contact" size=20></td>
						<td><input type="text" name="date" size=12 disabled></td>
						<td><input type="text" name="date" size=3 disabled></td>
						<td colspan=3 align=right>
							<input type=submit name="submit" value="Flag this!">
							<input type="hidden" name="action" value="flag">
						</td>
					</tr>

HTML;
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
