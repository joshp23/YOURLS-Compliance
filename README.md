# Compliance for YOURLS
An anti-abuse plugin written for and tested with YOURLS 1.7.1

By Josh Panter [Unfettered](https://unfettered.net). See it in action at [0=2](https://0eq2.com/) - An Unfettered URL Shortening Service

Compliance is designed to address link complaints from 3rd parties. Compliance implements a flag-list which can be updated via the admin interface or an optional public report-page. This allows any visitor to complain about an abusive URL. The URL is flagged and future visitors are warned and given the option to avoid the link.

Notification page and abuse report pages are both written with Bootstrap.

## REQUIREMENTS

1. A working [YOURLS](https://github.com/YOURLS/YOURLS) installation
2. YOURLS mysql user should have CREATE TABLE grants on YOURLS database. See NOTE.

### INSTALLATION

1. Place the compliance folder in YOURLS/user/plugins
2. Copy compliance/config.php.dist to compliance/config.php and edit to suit
3. Activate Compliance for Yourls in the Admin interface - sql tables should be made automatically
4. Link abuse.php to the pages/ directory. From YOURLS base directory:

	```bash
  $ ln -s ./user/plugins/compliance/abuse.php ./pages/abuse.php
  ```

#### NOTE: The sql table may need to be added manually using compliance.sql located in compliance/assets/. 

#### CAUTION: When disabling the plugin, tables will be dropped, currently the plugin.php file can be edited to rpevent this.

### WON'T DO: 
1. Flagged links need to be sanitized against known blacklists (ex: GSB, etc.) and deleted. This is not a part of the current release, as other plugins can check blacklists before link submission. It is highly reccomended that one of these be used. One job for one program... no monoliths.
2. Project Honeypot can be implimented in order to prevent known abusers access. This also needs to be it's own entity.

### TODO:
1. Add option to keep tables on deactivation in config file.
2. Cause flagged URLS to be colour-coded in main admin interface.
3. Move to uniform pdo

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
