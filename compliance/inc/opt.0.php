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

				<p>The default behavior in Compliance is to preserve flagged links, opting to intercept all flagged redirects, and notify the users with an informational warning page, thus giving them the choice of action. This prevents abusive arbitrary deleting or disabling of short URLS by the public.</p>

				<h3>Override Defaut: Flag & Intercept</h3>

				<p>This functionally allows any user with access to the abuse page to delete any Short URL. <b>Use with caution</b>. This will make it so that if a flagged link is visited after being flagged, instead of interception the Short URL is merely deleted from the database.</p>

				<form method="post">
					<div class="checkbox">
					  <label>
						<input name="compliance_nuke" type="hidden" value="false" />
						<input name="compliance_nuke" type="checkbox" value="true" %nuke_chk% > Delete flagged links? (instead of interecept)
					  </label>
					</div>

					<h3>Override Defaut: Admin Interface Expose Flags</h3>

					<p>Compliance can check links on the fly and tag flagged links in your admin interface regardless of weather they are going to be nuked on the next redirect or not. If you are serving a large amount of short URL's and you notice big hangs when you open your admin interface you may want to disable this feature.</p>

					<div class="checkbox">
					  <label>
						<input name="compliance_expose_flags" type="hidden" value="false" />
						<input name="compliance_expose_flags" type="checkbox" value="true" %exp_chk% > Expose flags on Admin Interface?
					  </label>
					</div>

					<div style="display:%vis_del%;">

						<h3>Override Default: Intercept Page</h3>

						<p>Compliance provides a well formed and functional intercept page written in bootstrap for flagged redirects. You can opt here to use your own, however.</p>

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

				<p>By default Compliance will drop its databse table when the plugin is disabled. You can override this setting here.</p>
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
