<?php
/*
Plugin Name: Compliance
Plugin URI: https://github.com/joshp23/YOURLS-Compliance
Description: Provides a way to flag short urls for abuse, and warn users of potential risk.
Version: 1.7.0
Author: Josh Panter
Author URI: https://unfettered.net
*/
// No direct call
if( !defined( 'YOURLS_ABSPATH' ) ) die();
/*
 *
 * ADMIN PAGE FUNCTIONS
 *
 *
*/
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
	// Replace all %stuff% in opt.0 with variable $stuff
	$opt_0_view = preg_replace_callback( '/%([^%]+)?%/', function( $match ) use( $vars ) { return $vars[ $match[1] ]; }, $opt_0_view );

	echo $opt_0_view;

	compliance_flag_list_mgr(); // sys
}

// Display page 0.01 - maybe insert some JS and CSS files to head
yourls_add_action( 'html_head', 'compliance_head' );
function compliance_head($context) {
	if ( $context[0] == 'plugin_page_compliance' ) {
		$home = YOURLS_SITE;
		echo "<link rel=\"stylesheet\" href=\"".$home."/css/infos.css?v=".YOURLS_VERSION."\" type=\"text/css\" media=\"screen\" />\n";
		echo "<script src=\"".$home."/js/infos.js?v=".YOURLS_VERSION."\" type=\"text/javascript\"></script>\n";
	}
}
// Display page 0.1 - listing the flags !IF NO NUKES!
function flag_list() {
	// should we bother with this data, has the "nuke" option been set?"
	$compliance_nuke = yourls_get_option( 'compliance_nuke' );
	if ($compliance_nuke !== "true") {
		// no nuke, draw flaglist page ~ this picks up where opt.0.php leaves off.
		$opt_1_view = file_get_contents( dirname( __FILE__ ) . '/inc/opt.1.php', NULL, NULL, 200);
		echo $opt_1_view;
		// populate table rows with flag data if there is any
		global $ydb;
		$table = YOURLS_DB_PREFIX . 'flagged';
		$sql = "SELECT * FROM `$table` ORDER BY timestamp DESC";
		$flagged_list = $ydb->fetchObjects($sql);
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
}

// Display page 0.2 - adding a flag
function flag_add() {
	
	if (!empty($_POST) && isset($_POST['alias']) && isset($_POST['reason']) && isset($_POST['contact'])) {
		$alias = $_POST['alias'];
		if (yourls_keyword_is_taken( $alias ) == true) {
			global $ydb;
			$table = YOURLS_DB_PREFIX . "flagged";
			$reason = $_POST['reason'];
			$contact = $_POST['contact'];
			$binds = array( 'alias' => $alias,
							'reason' => $reason,
							'contact' => $contact);
							
			$sql = "REPLACE INTO `$table` (keyword, reason, addr) VALUES (:alias, :reason, :contact)";
			$insert = $ydb->fetchAffected($sql, $binds);

		} else {
		echo '<h3 style="color:red">ERROR: No such URL in our database. Please try again.</h3>';
		}
	}

	flag_list();
}

// Display page 0.3 - removing a flag
function remove_flag() {

	if( isset($_GET['key']) ) {
		global $ydb;
		$table = YOURLS_DB_PREFIX . 'flagged';
		$key = $_GET['key'];
		$binds = array( 'key' => $key);
						
		$sql = "DELETE FROM `$table` WHERE keyword=:key";
		$delete = $ydb->fetchAffected($sql, $binds);
	}
	flag_list();
}

// Mark flagged links on admin page - TODO, add unflag option & link to flaglist
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

/*
 *
 *	Form submissions
 *
 *
*/
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
/*
 *
 *	Flag Checking
 *
 *
*/
// Hook on basic redirect
yourls_add_action( 'redirect_shorturl', 'check_safe_redirection' );

// Flag check 0 ~ skelleton and flow
function check_safe_redirection( $args ) {

        $url = $args[0]; // Target URL to scan
        $keyword = $args[1]; // Keyword for this request
       
	$result = check_flagpage($url, $keyword);  

	if($result !== false) {

		// A hit was found, and we're not nuking. Check for custom intercept
		$compliance_cust_toggle = yourls_get_option( 'compliance_cust_toggle' );
		$compliance_intercept = yourls_get_option( 'compliance_intercept' );
		if (($compliance_cust_toggle == "true") && ($compliance_intercept !== '')) {
			// How to pass keyword and url to redirect?
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
	$table = YOURLS_DB_PREFIX . 'flagged';
	$sql = "SELECT * FROM $table WHERE `keyword` = :keyword";
	$binds = array('keyword' => $keyword);
	$flagged = $ydb->fetchOne($sql, $binds);
	if( $flagged ) {
		// A hit was found. Check for nuke
		$compliance_nuke = yourls_get_option( 'compliance_nuke' );
		if ($compliance_nuke == "true") {
			// Delete link & die
			yourls_delete_link_by_keyword( $keyword );
			yourls_die('This short URL has been flagged by our community and deleted from our records.', 'Domain blacklisted', '403');
		}
		$flagged = (array)$flagged;
		$result['reason'] = $flagged['reason'];
	}

	return $result;
}

// Flag check 0.2 ~ interstitial warning TEMPLATE
function display_flagpage($keyword, $reason) {

	$title 	= yourls_get_keyword_title( $keyword );
	$url		= yourls_get_keyword_longurl( $keyword );
	$base		= YOURLS_SITE;
	$img		= yourls_plugin_url( dirname( __FILE__ ).'/assets/caution.png' );
	$css 		= yourls_plugin_url( dirname( __FILE__ ).'/assets/bootstrap.min.css' );
	
	if((yourls_is_active_plugin('snapshot/plugin.php')) !== false) { 
		$preview = compliance_snapshot_preview($keyword, $url);
		$img_li	 = '<li>Preview:</li>';
	} else {
		$preview = null;
		$img_li  = null;
	}
	

	$vars = array();
		$vars['keyword'] 	= $keyword;
		$vars['reason'] 	= $reason;
		$vars['title'] 	= $title;
		$vars['url'] 		= $url;
		$vars['base'] 		= $base;
		$vars['img'] 		= $img;
		$vars['css'] 		= $css;
		$vars['preview'] 	= $preview;
		$vars['img_li'] 	= $img_li;

	$intercept = file_get_contents( dirname( __FILE__ ) . '/assets/intercept.php' );
	// Replace all %stuff% in intercept.php with variable $stuff
	$intercept = preg_replace_callback( '/%([^%]+)?%/', function( $match ) use( $vars ) { return $vars[ $match[1] ]; }, $intercept );

	echo $intercept;

	die();
}

// Flag check 0.4 ~ image preview
function compliance_snapshot_preview($keyword, $url) {

	$base 	= YOURLS_SITE;
	$id 	= 'snapshot';
	$fn 	= snapshot_request($keyword, $url);
	
	if($fn == 'alt') {

		$id = 'snapshot-alt';
		$fn = array(
			'sorry.png',
			'420'
		);
	}
	
	$now = round(time()/60);
	$key = md5($now . $id);

	$preview = '<div id="live_p"><img style="display:block;margin:auto;" border=1 src="'.$base.'/srv/?id='.$id.'&key='.$key.'&fn='.$fn[0].'" width="800" /></div>';
	
	return $preview;
}
/*
 *
 *	Database
 *
 *
*/

// temporary update DB script
if (!defined( 'COMPLIANCE_DB_UPDATE' ))
	define( 'COMPLIANCE_DB_UPDATE', false );
if (COMPLIANCE_DB_UPDATE)
	yourls_add_action( 'plugins_loaded', 'compliance_update_DB' );
function compliance_update_DB () {
	global $ydb;
	$table = 'flagged';
	if ( YOURLS_DB_PREFIX ) {
		try {
			$sql = "DESCRIBE `".YOURLS_DB_PREFIX . $table."`";
			$fix = $ydb->fetchAffected($sql);
		} catch (PDOException $e) {
			$sql = "RENAME TABLE `".$table."` TO  `".YOURLS_DB_PREFIX.$table."`";
			$fix = $ydb->fetchAffected($sql);
		}
		
		$table = YOURLS_DB_PREFIX . $table;
	}
	
	try {
	    	$sql = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES
	    		WHERE TABLE_NAME = `".$table."`
	    		AND ENGINE = 'INNODB' LIMIT 1";
	    	$fix = $ydb->fetchAffected($sql);
    	} catch (PDOException $e) {
		$sql = "ALTER TABLE `".$table."` ENGINE = INNODB;";
		$fix = $ydb->fetchAffected($sql);
	}
}
// Create tables for this plugin when activatedydb
yourls_add_action( 'activated_compliance/plugin.php', 'compliance_activated' );
function compliance_activated() {

	$init = yourls_get_option('compliance_init');
	if ($init === false) {
		global $ydb;
		// Create the init value
		yourls_add_option('compliance_init', time());
		// Create the flag table
		$table = YOURLS_DB_PREFIX . 'flagged';
		$table_flagged  = "CREATE TABLE IF NOT EXISTS ".$table." (";
		$table_flagged .= "keyword varchar(200) NOT NULL, ";
		$table_flagged .= "clicks int(10) NOT NULL default 0, ";
		$table_flagged .= "timestamp timestamp NOT NULL default CURRENT_TIMESTAMP, ";
		$table_flagged .= "reason text, ";
		$table_flagged .= "addr varchar(200) default NULL, ";
		$table_flagged .= "PRIMARY KEY (keyword) ";
		$table_flagged .= ") ENGINE=InnoDB DEFAULT CHARSET=latin1;";

		$tables = $ydb->fetchAffected($table_flagged);

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
	$compliance_table_drop = yourls_get_option('compliance_table_drop');
	if ( $compliance_table_drop !== 'false' ) {
		$init = yourls_get_option('compliance_init');
		if ($init !== false) {
			yourls_delete_option('compliance_init');
			global $ydb;
			$table = YOURLS_DB_PREFIX . 'flagged';
			$sql = "DROP TABLE IF EXISTS `$table`";
			$ydb->fetchAffected($sql);
		}
	}
}

// Flush Tables
function compliance_db_flush() {
	$init_1 = yourls_get_option('compliance_init');
	if ($init_1 !== false) {
		global $ydb;
		$table = YOURLS_DB_PREFIX . 'flagged';
		$sql = "TRUNCATE TABLE `$table`";
		$ydb->fetchAffected($sql);

		yourls_update_option('compliance_init', time());
		$init_2 = yourls_get_option('compliance_init');
		if ($init_2 === false || $init_1 == $init_2) {
			die("Unable to properly reset the database. Contact your sys admin");
		}
	}
}
/*
 *
 *	FS IO
 *
 *
*/
yourls_add_action( 'delete_link', 'delete_flagged_link_by_keyword' );
function delete_flagged_link_by_keyword( $args ) {
	global $ydb;

	$keyword = $args[0]; // Keyword to delete

	// Delete the flag data, no need for it anymore
	$ftable = YOURLS_DB_PREFIX . "flagged";
	$binds = array( 'keyword' => $keyword);
	$sql = "DELETE FROM $ftable WHERE `keyword` = :keyword";
	$ydb->fetchAffected($sql, $binds);

	// Uncomment to delete log-entries for deleted URL
	$ltable = YOURLS_DB_TABLE_LOG;
	$binds = array( 'keyword' => $keyword);
	$sql = "DELETE FROM $ltable WHERE `shorturl` = :keyword";
	$ydb->fetchAffected($sql, $binds);
}
?>
