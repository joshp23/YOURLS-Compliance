<?php
// Compliance plugin for Yourls - URL Shortener ~ Complaint report page
// Copyright (c) 2016, Josh Panter <joshu@unfettered.net>
global $ydb;
?>
<html lang="en">
  <head>
  	
    <meta charset="utf-8">
    <title>Compliance Report</title>
	  
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
	$alias = "";
	$report = "";
	$contact = "";
	if(isset($_POST['alias'])) $alias=mysql_escape_string($_POST['alias']);
	if(isset($_POST['report'])) $report=mysql_escape_string($_POST['report']);
	if(isset($_POST['contact'])) $contact=mysql_escape_string($_POST['contact']);

	if (!empty($alias)) {
	    $table = "flagged";
	    $insert = $ydb->query("REPLACE INTO `$table` (keyword, reason, addr) VALUES ('$alias', '$report', '$contact')");
	    echo "
		<div class='alert alert-dismissible alert-success'>
			<strong>Success</strong> <b>http://$_SERVER[HTTP_HOST]/$alias</b> has been flagged. <a href='https://$_SERVER[HTTP_HOST]'>Click here</a> to return to the main page.
		</div>";
			}
	?>

	<form class="form-horizontal" method="post" action="">
	<fieldset>

		<div class="form-group">
		<label for="alias" class="col-lg-2 control-label">Short URL</label>
			<div class="col-lg-10">
				<input type="text" class="form-control" id="alias" name="alias" placeholder="If you need to report the url http://example.com/THIS ... Just enter THIS here." style="background-repeat: repeat; background-image: none; background-position: 0% 0%;" value="<?php echo $alias ?>">
			</div>
		</div>

		<div class="form-group">
		<label for="report" class="col-lg-2 control-label">Reason for report</label>
			<div class="col-lg-10">
				<textarea class="form-control" rows="3" id="report" name="report"><?php echo $report ?></textarea>
			<span class="help-block">Please give us some details about this problem.</span>
			</div>
		</div>

		<div class="form-group">
		<label for="contact" class="col-lg-2 control-label">Email</label>
			<div class="col-lg-10">
				<input type="text" class="form-control" id="contact" name="contact" placeholder="Email (kept private)" style="background-repeat: repeat; background-image: none; background-position: 0% 0%;" value="<?php echo $contact ?>">
			</div>
		</div>

		<div class="form-group">
			<div class="col-lg-10 col-lg-offset-2">
				<button type="reset" class="btn btn-default">Cancel</button>
				<button type="submit" class="btn btn-primary">Submit</button>
			</div>
		</div>
	</fieldset>
	</form>

	<p><i>Please be advised: <b>Your report will be visible to future visitors</b> of the shortlink (Reason for report) but your contact information, which we request in case additional information is required, will remain confidential.</i></p>
	
	<p><a href="/">Click here</a> to go visit our home page.</p>
	<p>Thank You.</p>

	</div>
	</body>
</html>
