<html lang="en">
	<head>

		<meta charset="utf-8">
		<title>Caution!</title>
		<link rel="icon" href="user/plugins/YOURLS-Compliance/assets/caution.png" type="image/png" />

    		<!-- Bootstrap core CSS -- USE LOCAL CACHE
   		<link href="https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/spacelab/bootstrap.min.css" rel="stylesheet" integrity="sha384-L/tgI3wSsbb3f/nW9V6Yqlaw3Gj7mpE56LWrhew/c8MIhAYWZ/FNirA64AVkB5pI" crossorigin="anonymous"> -->
   
		<!-- Bootstrap core CSS -- LOCAL CACHE -->
		<link href="user/plugins/YOURLS-Compliance/assets/bootstrap.min.css" rel="stylesheet" integrity="sha384-L/tgI3wSsbb3f/nW9V6Yqlaw3Gj7mpE56LWrhew/c8MIhAYWZ/FNirA64AVkB5pI" crossorigin="anonymous">

		<!-- Add extra support of older browsers -->
		<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->

	</head>
	<body>
		<div style="padding:10px 50px; text-align: center;" class="well well-lg">
		    <div style="display: inline-block; text-align: left">

				<h2 class="text-danger"><img src="user/plugins/YOURLS-Compliance/assets/caution.png" width="30" height="30"/> Caution: This link has been flagged <img src="user/plugins/YOURLS-Compliance/assets/caution.png" width="30" height="30"/></h2>
				<p>You have requested short URL <strong><a href="%base%/%keyword%">%base%/%keyword%</a></strong></p>
				<p>This link been flagged as <span class="text-danger">potentially harmful</span> by our community with the following explanation:</p>

				<blockquote>
					<p>%reason%</p>
				</blockquote>

				<p>This short URL points to:</p>
				<ul>
					<li>Page title: <strong>%title%</strong></li>
					<li>Long URL: <strong><a href="%base%/%keyword%">%url%</a></strong></li>
				</ul>

				<p><strong><a href="https://staysafeonline.org/stay-safe-online/keep-a-clean-machine/spam-and-phishing" target="_blank">Click here</a></strong> for some usefull informaiton on phishing, and keeping yourself safe online.
				</ br>
				<p>If you understand the risk, and still want to visit this link, you may do so by <strong><a href="%url%">clicking here</a></strong>.</p>
				<p><a href="%base%">Click here</a> to go visit our home page.</p>

				<p>Thank you.</p>
    		</div>
		</div>
	</body>
</html>
