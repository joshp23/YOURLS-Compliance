<?php
// Compliance plugin for Yourls - URL Shortener ~ Flag Checking and Interception functions
// Copyright (c) 2016, Josh Panter <joshu@unfettered.net>

// No direct call
if( !defined( 'YOURLS_ABSPATH' ) ) die();

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

?>
