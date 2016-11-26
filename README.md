# Compliance for YOURLS
An anti-abuse plugin written for and tested with YOURLS 1.7.1

By Josh Panter [Unfettered](https://unfettered.net). See it in action at [0=2](https://0eq2.com/) - An Unfettered URL Shortening Service

Compliance is designed to address link complaints from 3rd parties. Compliance implements a flag-list which can be updated via the admin interface or via an optional public report-page. This allows any visitor to complain about a potentially abusive URL which is then marked in the admin section, and future visitors are given the option to either continue through or avoid the link at their discretion.

Compliance is highly customizable. You can set the following options in the admin section of YOURLS:

1. Nuke any flagged URL on its next redirect (Default: preserve the links)
2. Use a custom intercept page for flagged URL redirects (Default: use the Compliance Bootstrap page)
3. Expose flags on Admin interface or not (Default: Expose the flags)
3. Keep or drop the Compliance sql table on plugin deactivation (Default: Drop)
4. Flush your flag-list table and start fresh.

The default interecept and abuse report pages are both written with Bootstrap.

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
2. Project Honeypot's http:BL implimented in order to prevent acces from known abusers. This very necessary feature ~~needs to be~~ has already been turned into it's own entity, [here](https://github.com/joshp23/YOURLS-httpBL).

### TODO:
1. Add options to flag/unflag url + visit flaglist in traditional Admin Interface section

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
