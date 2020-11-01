<?php
/*
Plugin Name: Compliance | Report Page
Plugin URI: https://github.com/joshp23/YOURLS-Compliance
Description: Provides a way to flag short urls for abuse, and warn users of potential risk.
Version: 1.7.1
Author: Josh Panter
Author URI: https://unfettered.net
*/
// Make sure we're in YOURLS context
if( !defined( 'YOURLS_ABSPATH' ) ) {
?>
<html>
	<head>
		<meta http-equiv="refresh" content="3;url=../">
	</head>
	<body>
		<h2 style="position: absolute; top: 50%; left: 50%; transform: translateX(-50%) translateY(-50%);">You are trying to access an offlimits path. If you are not redirected automatically, please return to our <a href='/'>home</a> page. Thank you.</h2>
	</body>
</html>

<?php 
	die();
}
// Autofill abuse form - integration with other plugins
if( isset($_GET['action']) && $_GET['action'] == "autofill" ) {
	if( isset($_GET['alias'])) $alias = $_GET['alias'];
	if( isset($_GET['reason'])) $reason = $_GET['reason'];
	if( isset($_GET['contact'])) $contact = $_GET['contact'];
}
?>
<!--- In path, resume --->
<html lang="en">
  <head>
  	
    <meta charset="utf-8">
    <title>Compliance Report</title>
    <link rel="icon" href="assets/img/0eq2.fav.ico" />
    <!-- Bootstrap core CSS -- USE LOCAL CACHE
   <link href="https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/spacelab/bootstrap.min.css" rel="stylesheet" integrity="sha384-L/tgI3wSsbb3f/nW9V6Yqlaw3Gj7mpE56LWrhew/c8MIhAYWZ/FNirA64AVkB5pI" crossorigin="anonymous"> -->

    <!-- Bootstrap core CSS -- LOCAL CACHE -->
   <link href="user/plugins/compliance/assets/bootstrap.min.css" rel="stylesheet" integrity="sha384-L/tgI3wSsbb3f/nW9V6Yqlaw3Gj7mpE56LWrhew/c8MIhAYWZ/FNirA64AVkB5pI" crossorigin="anonymous">

    <!-- Add extra support of older browsers -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

  </head>

	<body>
	<div style='padding: 10px 50px''>

	<h2>Compliance Report</h2>
	<p>If you feel that any short URL's from this serivce have been posted to support abusive purposes such as spam, phishing, or the spread of malware, please report the URL as such using the form below.</p>
	<p><b>The offending URL will be flagged automatically, and users will be warned of a potential threat.</b></p>

	<?php
	// has the form been submitted?
	if (!empty($_POST)) {
		// is the botbox clear?
		if ($_POST['botbox'] == '1') {
			// Error... 
			$result='<div class="alert alert-danger">You chekced the box. I told you not to check the box. Try again, or <a href="/">click here</a> to return to the home page.</div>';
		// all clear...
		} else {
			// Check if alias has been entered
			if (!$_POST['alias']) {
				// no alias?
				$errAlias = 'You neglected to enter a short url for us to flag.';
			} else { 
				// or set vars and continue
				$errAlias = null;
				$alias = $_POST['alias'];
					}

			//Check if message has been entered...
			if (!$_POST['reason']) {
				$errReason = '<p class="text-danger">Please tell us why we should flag this url.</p>';
			} else { 
				$errReason = null;
				$reason = $_POST['reason'];
					}

			// Check if email has been entered and is valid...
			if (!$_POST['contact'] || !filter_var($_POST['contact'], FILTER_VALIDATE_EMAIL)) {
				$errContact = 'This form will not submit without a valid email address.';
			} else { 
				$errContact = null;
				$contact = $_POST['contact'];
					}

			// If there are no errors, submit the report
			if (!$errAlias && !$errReason && !$errContact) {

				if (yourls_keyword_is_taken( $alias ) == true) {
					global $ydb;
					$table = YOURLS_DB_PREFIX .'flagged';
					if (version_compare(YOURLS_VERSION, '1.7.3') >= 0) {
						$binds = array( 'alias' => $alias,
										'reason' => $reason,
										'contact' => $contact);
								
						$sql = "REPLACE INTO `$table` (keyword, reason, addr) VALUES (:alias, :reason, :contact)";
						$insert = $ydb->fetchAffected($sql, $binds);
					} else {
						$insert = $ydb->query("REPLACE INTO `$table` (keyword, reason, addr) VALUES ('$alias', '$reason', '$contact')");
					}
					$result = "
					<div class='alert alert-dismissible alert-success'>
						<strong>Success</strong> <b>http://$_SERVER[HTTP_HOST]/$alias</b> has been flagged. <a href='https://$_SERVER[HTTP_HOST]'>Click here</a> to return to the main page.
					</div>";
				} else {
				$result = "
					<div class='alert alert-dismissible alert-danger'>
						<strong>ERROR: <b>http://$_SERVER[HTTP_HOST]/$alias</b> No such URL in our database. Please try again, or <a href='https://$_SERVER[HTTP_HOST]'>click here</a> to return to the main page.</strong>
					</div>";
				}
				
			}
		}
	}
        ?>
	<form class="form-horizontal" method="post" action="">
	<fieldset>

		<div class="form-group<?php echo (isset($errAlias) ? (' has-error') : null); ?>" >
		<label for="alias" class="col-lg-2 control-label">Short URL</label>
			<div class="col-lg-10">
				<input type="text" class="form-control" id="alias" name="alias" placeholder="<?php echo (isset($errAlias) ? $errAlias : 'If you need to report the url  http://example.com/THIS  Just enter THIS here'); ?>" style="background-repeat: repeat; background-image: none; background-position: 0% 0%;" value="<?php echo (isset($alias) ? $alias : null); ?>">
			</div>
		</div>

		<div class="form-group<?php echo (isset($errReason) ? (' has-error') : null); ?>">
		<label for="report" class="col-lg-2 control-label">Reason for report</label>
			<div class="col-lg-10">
				<textarea class="form-control" rows="3" id="reason" name="reason"><?php echo (isset($reason) ? $reason : null); ?></textarea>
			<?php echo (isset($errReason) ? ("<p class='text-danger'> $errReason</p>") : '<span class="help-block">Please give us some details about this problem</span>');?>
			</div>
		</div>

		<div class="form-group<?php echo (isset($errContact) ? (' has-error') : null); ?>">
		<label for="contact" class="col-lg-2 control-label">Email</label>
			<div class="col-lg-10">
				<input type="text" class="form-control" id="contact" name="contact" placeholder="<?php echo (isset($errContact) ? $errContact : 'Email (kept private)');?>" style="background-repeat: repeat; background-image: none; background-position: 0% 0%;" value="<?php echo (isset($contact) ? $contact : null); ?>">
			</div>
		</div>

		<div class="form-group">
			<div class="col-lg-10 col-lg-offset-2">
				<div class="checkbox">
				  <label>
					<input type="hidden" name="botbox" value="0" />
					<input name="botbox" type="checkbox" value="1"> Leave this box unchecked.
				  </label>
				</div>
				<button type="reset" class="btn btn-default">Cancel</button>
				<button type="submit" class="btn btn-primary">Submit</button>
			</div>
		</div>
	</fieldset>
	</form>
	<?php echo (isset($result) ? $result : '<p><i>Please be advised: <b>Your report will be visible to future visitors</b> of the shortlink (Reason for report) but your contact information, which we request in case additional information is required, will remain confidential.</i></p><p><a href="/">Click here</a> to return to our home page.</p><p>Thank You.</p>'); ?>

	</div>
	<?php if((yourls_is_active_plugin('httpBL/plugin.php') && yourls_get_option('httpBL_honeypot'))) print httpbl_link() . "\n"; ?>
	</body>
</html>
