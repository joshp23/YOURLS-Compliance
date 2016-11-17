# Compliance for YOURLS
An anti-abuse plugin written for and tested with YOURLS 1.7.1

By Josh Panter [Unfettered](https://unfettered.net). See it in action at [0=2](https://0eq2.com/) - An Unfettered URL Shortening Service

Compliance is designed to address link complaints from 3rd parties. Compliance implements a flag-list which can be updated via the admin interface or via an optional public report-page. This allows any visitor to complain about an abusive URL. By default the URL is flagged and future visitors are warned and given the option to avoid the link.

You can set the options to nuke any flagged URL, to use a custom intercept page for flagged URL visits, and to keep or drop the tables on plugin deactivation in the admin section of YOURLS. You will also find an option to flush your flag-list table and start fresh.

The default flagged redirect interecept and abuse report pages are both written with Bootstrap.

## REQUIREMENTS

1. A working [YOURLS](https://github.com/YOURLS/YOURLS) installation
2. YOURLS mysql user should have CREATE TABLE grants on YOURLS database. See NOTE.

### INSTALLATION

1. Place the compliance folder in YOURLS/user/plugins
2. Activate Compliance for Yourls in the Admin interface - sql tables should be made automatically
3. Set options in the Compliance options page. The default options are just fine. Clicking submit on various forms will enter the default values into the sql tables, but null values all fall back to default actions as well.
4. Link abuse.php to the pages/ directory. From YOURLS base directory:

	```bash
  $ ln -s ./user/plugins/compliance/abuse.php ./pages/abuse.php
  ```
 
#### NOTE: The sql table may need to be added manually using compliance.sql located in compliance/assets/. 

### WON'T DO: 
1. Flagged links need to be sanitized against known blacklists (ex: GSB, etc.) and deleted. This is not a part of the current release, as other plugins, such as [Phishtank-2.0](https://github.com/joshp23/YOURLS-Phishtank-2.0), can check blacklists before link submissionn and recheck them on redirects. It is highly reccomended that one of the existing blacklist plugins be used.
2. Project Honeypot (or some otehr "gate keeper") implimented in order to prevent acces from known abusers. This needs to be it's own entity.

### TODO:
1. Make flagged URLS colour-coded in main admin interface. 
2. Add option to flag url in traditional Admin Interface Actions section
3. Check for validity of url on flag submission (in abuse.php & admin page) & send error on false submission

### CREDITS
Scripts used for inspiration and/or copypasta:

1. [YOURLS Abuse Desk](https://github.com/florianoverkamp/yourls-abusedesk)
2. [YOURLS Interstitial plugin](https://github.com/joelgratcyk/yourls-interstitial-plugin)
3. [YOURLS Preview URL](https://github.com/YOURLS/YOURLS/wiki/Plugin-=-Preview-URL)

===========================

    Copyright (C) 2016 Josh Panter

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
