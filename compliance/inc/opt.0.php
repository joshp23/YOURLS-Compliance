<?php
// Compliance plugin for Yourls - URL Shortener ~ Options display 0 html
// Copyright (c) 2016, Josh Panter <joshu@unfettered.net>

// No direct call
if( !defined( 'YOURLS_ABSPATH' ) ) die();
?>
	<link rel="stylesheet" href="/css/infos.css?v=1.7.2" type="text/css" media="screen" />
	<script src="/js/infos.js?v=1.7.2" type="text/javascript"></script>

	<div id="wrap">

		<div class="sub_wrap">
		<div id="tabs">

			<div class="wrap_unfloat">
				<ul id="headers" class="toggle_display stat_tab">
					<li class="selected"><a href="#stat_tab_behavior"><h2>Behavior</h2></a></li>
					<li style="display:%vis_del%;"><a href="#stat_tab_flag_list"><h2>Flag List</h2></a></li>
					<li><a href="#stat_tab_db"><h2>Database Settings</h2></a></li>
				</ul>
			</div>

			<div id="stat_tab_behavior" class="tab">

				<h2>Handling Compliance Behavior</h2>

				<h3>Override Defaut: Flag & Intercept</h3>

				<p>To prevent the arbitrary disabling of short URLS by anonymous users, Compliance intercepts all flagged redirects by default, informing visitors and giving them the choice of action. Compliance can simply delete these links from the database instead. <b>Use with caution</b>.</p>

				<form method="post">
					<div class="checkbox">
					  <label>
						<input name="compliance_nuke" type="hidden" value="false" />
						<input name="compliance_nuke" type="checkbox" value="true" %nuke_chk% > Delete flagged redirects?
					  </label>
					</div>

					<h3>Admin Interface Expose Flags</h3>

					<p>If you are serving a large amount of short URL's and you notice hangs when Admin Interface loads, you may want to disable this feature to reduce SQL querries.</p>

					<div class="checkbox">
					  <label>
						<input name="compliance_expose_flags" type="hidden" value="false" />
						<input name="compliance_expose_flags" type="checkbox" value="true" %exp_chk% > Expose flags on Admin Interface?
					  </label>
					</div>

					<div style="display:%vis_del%;">

						<h3>Intercept Page</h3>

						<div class="checkbox">
						  <label>
							<input name="compliance_cust_toggle" type="hidden" value="false" />
							<input name="compliance_cust_toggle" type="checkbox" value="true" %url_chk% >Use Custom Intercept URL?
						  </label>
						</div>
						<div style="display:%vis_url%;">

							<p>Setting the above option without setting this will result in an endles refresh.</p>

							<p><label for="compliance_intercept">Enter intercept URL here</label> <input type="text" size=40 id="compliance_intercept" name="compliance_intercept" value="%compliance_intercept%" /></p>
						</div>

					</div>
					<input type="hidden" name="nonce" value="%nonce%" />
					<p><input type="submit" value="Submit" /></p>
				</form>
			</div>

			<div id="stat_tab_db" class="tab">

				<h2>Database Settings</h2>

				<h3>Table management on plugin disable</h3>

				<p>Compliance automatically drops its databse table when the plugin is disabled. You can override this setting here.</p>
				<form method="post">
					<div class="checkbox">
					  <label>
						<input name="compliance_table_drop" type="hidden" value="false" />
						<input name="compliance_table_drop" type="checkbox" value="true" %drop_chk% > Drop Compliance table on disable?
					  </label>
					</div>
					<input type="hidden" name="nonce" value="%nonce%" />
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
					<input type="hidden" name="nonce" value="%nonce%" />
					<p><input type="submit" value="FLUSH!" /></p>
				</form>
				<p>Don't forget to return here after submitting to check for messages!</p>
			</div>

			<div  id="stat_tab_flag_list" class="tab">

	 			<h2>Flagged URL List</h2>
