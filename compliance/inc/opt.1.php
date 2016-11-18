<?php
// Compliance plugin for Yourls - URL Shortener ~ Options display 0 html
// Copyright (c) 2016, Josh Panter <joshu@unfettered.net>

// No direct call
if( !defined( 'YOURLS_ABSPATH' ) ) die();
?>
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
