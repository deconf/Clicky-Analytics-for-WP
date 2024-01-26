=== Clicky Analytics ===
Contributors: deconf
Donate link: https://deconf.com/donate/
Tags: clicky, analytics, statistics, stats
Requires at least: 2.8
Tested up to: 6.4
Requires PHP: 5.2.4
Stable tag: 2.2.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

This plugin will display Clicky Web Analytics data and statistics inside your WordPress Administration Dashboard.

== Description ==
Using a widget, [Clicky Analytics Plugin](https://deconf.com/clicky-analytics-dashboard-wordpress/) displays detailed info and stats about: online users, number of visits, number of actions, bounce rates, organic searches, time average directly on your Admin Dashboard.

This plugin automatically inserts <a href="https://clicky.com/66508224" target="_blank">Clicky Web Analytics</a> cookieless tracking code in each page of your website.


#### Clicky Admin Dashboard features:

- you can access your website's basic statistics in a widget on your Administration Dashboard
- cache feature, this improves loading speeds
- access level settings
- option to display top 30 pages, referrers and searches (sortable by columns)
- option to display Clicky Analytics statistics on frontend, at the end of each article
- has multilingual support, a POT file is available for translations.

#### Clicky Tracking features:

- cookieless tracking
- enable/disable Clicky Web Analytics tracking code
- user names tracking feature
- e-mails tracking feature
- video actions tracking for Youtube
- video actions tracking for HTML5
- asynchronously load of Clicky Web Analytics tracking code

#### User privacy oriented features (GDPR and other):

- IP address anonymization
- global opt-out feature
- tools to comply with GDPR requests from your visitors
- cookie-free/cookieless tracking

#### Clicky Custom Dashboard:

- all clicky stats are available in a custom dashboard, under your blog's administration panel.

Some features like video analytics and custom data tracking will require a <a href="https://clicky.com/66508224" target="_blank">Clicky Analytics Pro</a> account.
 
= Further reading =

* [Search Engine Insights](https://wordpress.org/plugins/search-engine-insights/) - The perfect tool for viewing Google Search Console stats in your WordPress dashboard.
* [Analytics Insights](https://wordpress.org/plugins/analytics-insights/) - Connects Google Analytics with your WordPress site.

== Installation ==

1. Upload the full directory into your wp-content/plugins directory
2. Activate the plugin at the plugin administration page
3. Open the plugin configuration page, which is located under Settings -> Clicky Analytics and enter your Site Key and Site ID
4. Save your configuration
5. Enjoy your Clicky reports and stats!

A step by step tutorial is available here: [Clicky Analytics video tutorial](https://deconf.com/clicky-analytics-dashboard-wordpress/)

== Frequently Asked Questions == 

= Where can I find my Clicky Site Key and Site ID? =

Follow this step by step video tutorial: [Clicky Analytics](https://deconf.com/clicky-analytics-dashboard-wordpress/)

= Some settings are missing from your video tutorial ... =

We are constantly improving our plugin, sometimes the video tutorial may be a little outdated.

== Screenshots ==

1. Clicky Analytics stats
2. Clicky Analytics Location stats
3. Clicky Analytics Top Pages, Top Referrers and Top Searches stats
4. Clicky Analytics Settings

== License ==

This plugin it's released under the GPLv2, you can use it free of charge on your personal or commercial website.

== Changelog ==

= 2.2.1 (2024.01.26) =
* Bug Fixes:
	* prevent multiple Clicky Analytics reports calls at first widget render	
* Enhancements:
	* clear expired cache daily using WP Cron

= 2.1 (2023.12.20) =
* Bug Fixes:
	* multiple CSS and URI fixes

= 2.0.4 (2023.12.02) =
* Bug Fixes:
	* daterange picker bug fix, store the last selected date interval label instead of the actual date range
	
= 2.0.3 (2023.09.30) =
* Bug Fixes:
	* fixes PHP 8.x count() fatal errors 

= 2.0.2 (2023.09.06) =
* Enhancements:	
	* Clicky Analytics UI improvements on area chart
	* Clicky Analytics API call optimization	
* Bug Fixes:
	* fixes a bug preventing other roles to view the clicky stats on admin dashboard 
	
= 2.0.1 (2023.08.08) =
* Bug Fixes:
	* multiple fixes for Location, Pages and Referrers reports
	
= 2.0 (2023.08.07) =
* Enhancements:	
	* new UI design and lots of UX improvements
	* complete code refactoring
	
= 1.8 =
* Enhancements:	
	* add option to disable Clicky Analytics widget on admin dashboard
	
= 1.7
* Bug Fixes:
	* fix invalid links
	* use sprintf for plugin i18n
* Security:	
	* switching all links and calls to HTTPS
		
= 1.6.5 =
* Bug Fixes:
	* code formatting
* Enhancements:	
	* settings page cleanup
	
= 1.6.4 =
* Enhancements: 
	* major tracking code upgrade, uses the latest Clicky tracking code
	
= 1.6.3 =
* Bug Fixes:
	* removed some white spaces, causing footer issues
	
= 1.6.2 =
* Bug Fixes:
	* remove the "X of Y support threads in the last..." text

= 1.6.1 =
* Bug Fixes:
	* multiple text domain fixes for i18n

= 1.6 =
* Enhancements: 
	* major tracking code upgrade, uses the latest Clicky tracking code (with navigator.sendBeacon)

= 1.5.2 =
* Bug Fixes:
	* Fixes a PHP notice when video tracking isn't enabled

= 1.5.1 =
* Bug Fixes:
	* YouTube video tracking support missing
	
= 1.5 =
* Bug Fixes:
	* some small code fixes
* Enhancements: 
	* ability to track Outbound Link Patterns
	* support for HTML5 media tracking
	
= 1.4.8 =
* Bug Fixes:
	* fixes some PHP notices
	
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
- hidding error messages on frontend stats

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
