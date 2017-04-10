=== Clicky Analytics ===
Contributors: deconf
Donate link: https://deconf.com/donate/
Tags: clicky,analytics,dashboard,tracking,tracking code,statistics,clicky analytics,clicky code,wpmu,multisite
Requires at least: 2.8
Tested up to: 4.7.3
Stable tag: 1.4.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin will display Clicky Web Analytics data and statistics inside your WordPress Blog.

== Description ==
Using a widget, [Clicky Analytics Plugin](https://deconf.com/clicky-analytics-dashboard-wordpress/) displays detailed info and statistics about: online users, number of visits, number of actions, bounce rates, organic searches, time average directly on your Admin Dashboard.

This plugin automatically inserts <a href="http://clicky.com/66508224" target="_blank">Clicky Web Analytics</a> tracking code in each page of your website.

Authorized users can also view Clicky statistics like visitors and top searches, on frontend, at the end of each article.

Clicky Admin Dashboard features:

- you can access your website's basic statistics in a widget on your Administration Dashboard
- cache feature, this improves loading speeds
- access level settings
- option to display top 30 pages, referrers and searches (sortable by columns)
- option to display Clicky Analytics statistics on frontend, at the end of each article
- has multilingual support, a POT file is available for translations.

Clicky Tracking features:

- enable/disable Clicky Web Analytics tracking code
- user names tracking feature
- e-mails tracking feature
- video actions tracking for Youtube
- video actions tracking for HTML5
- asynchronously load of Clicky Web Analytics tracking code

Clicky Custom Dashboard

- all clicky stats are available in a custom dashboard, under your blog's administration panel.


Some features like video analytics and custom data tracking will require a <a href="http://clicky.com/66508224" target="_blank">Clicky Analytics Pro</a> account.
 
More Reading:

* [Clicky Analytics Plugin](https://deconf.com/clicky-analytics-dashboard-wordpress/) homepage
* Other [WordPress Plugins](https://deconf.com/wordpress/) by same author
* [Clicky Analytics Plugin](https://deconf.com/ask/) translations and support

== Installation ==

1. Upload the full directory into your wp-content/plugins directory
2. Activate the plugin at the plugin administration page
3. Open the plugin configuration page, which is located under Settings -> Clicky Analytics and enter your Site Key and Site ID
4. Save your configuration

A step by step tutorial is available here: [Clicky Analytics video tutorial](https://deconf.com/clicky-analytics-dashboard-wordpress/)

== Frequently Asked Questions == 

= Where can I find my Clicky Site Key and Site ID? =

Follow this step by step video tutorial: [Clicky Analytics](https://deconf.com/clicky-analytics-dashboard-wordpress/)

= Some settings are missing from your video tutorial ... =

We are constantly improving our plugin, sometimes the video tutorial may be a little outdated.

= More Questions? =

A dedicated section for Wordpress Plugins is available here: [Wordpress Plugins Support](https://deconf.com/ask/)

== Screenshots ==

1. Clicky Analytics
2. Clicky Analytics Top Pages, Top Referrers and Top Searches
3. Clicky Analytics Settings
4. Clicky Analytics statistics per page on Frontend
5. Clicky Analytics Dashboard

== License ==

This plugin it's released under the GPLv2, you can use it free of charge on your personal or commercial website.

== Changelog ==

= 1.4.7 =
* Bug Fixes:
	* switch to get_sites() while maintaining compatibility with older WP installs
	* use the new library loader for Google Charts 

= 1.4.6 =
* Bug Fixes:
	* replaces get_currentuserinfo() which was deprecated since WordPress 4.5, props [Stanko Metodiev](https://profiles.wordpress.org/metodiew/)
* Enhancements: 
	* translation.wordpress.org ready 

= v1.4.5 =
- Bug Fix: white screen on custom dashboard

= v1.4.4 =
- Bug Fix: updated error text
- Bug Fix: plugin options cleared during deactivation/activation 
- Enhancement: adding noscript tracking capability

= v1.4.3 =
- Bug Fix: display mixed content when using https
- Bug Fix: some frontend stats were not generated properly
- Bug Fix: custom dashboard fix

= v1.4.2 =
- bugfix: notices and warnings in main dashboard
- language file updates
- hardening security

= v1.4.1 =
- allow specified access level to view the custom dashboard
- custom dashboard is now able to display all clicky stats

= v1.4 =
- data validation and sanitization
- switching from cURL to wp_remote_get
- additional dedicated dashboard for Clicky stats
- css tweaks 
- code optimizations
- display stats in frontend even if there are no organic searches
- added install/uninstall functions
- updated translations
- default options update

= v1.3.5 =
- all clicky analytics requests are now made using cURL 

= v1.3.4 =
- hidding error messages on frontend 

= v1.3.3 =
- updates on clicky tracking code and some action hooks

= v1.3.2 =
- notices and warnings fixes
- less error prone 

= v1.3.1 =
- minor fixes and updates

= v1.3 =
- added additional error messages
- fixed some minor issues

= v1.2.1 =
- fixed article view crash when no stats are available

= v1.2 =
- switched to cURL, to increase compatibility with some webhostings
- removed trailing commas on charts, for IE8 compatibility

= v1.1.1 =

- table title fix for top pages
- language file updated

= v1.1 =
- switch to internal jQuery library
- added video actions tracking for Youtube
- added video actions tracking for HTML5

= v1.0 = 
- first release