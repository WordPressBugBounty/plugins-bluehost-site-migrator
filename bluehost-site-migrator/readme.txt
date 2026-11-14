=== Bluehost Site Migrator ===
Contributors: bluehost, wpscholar
Tags: migrate, migration, bluehost
Requires at least: 4.7
Tested up to: 7.1
Stable tag: 2.0.0
Requires PHP: 5.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Shows you the best way to move your WordPress site to Bluehost.

== Description ==

Bluehost Site Migrator helps you move your existing WordPress site to Bluehost. It takes a quick look at your site, then points you to Bluehost's built-in migration tool, which copies your content, media, themes, and plugins onto your Bluehost hosting for you. If the self-service migration does not support your site, it points you to Bluehost migration support instead.

This plugin provides guidance only. It does not move data itself, run background tasks, or make external requests. The migration is performed by the tool built into Bluehost hosting.

You will need a Bluehost hosting account to complete a migration. This plugin is intended for moving sites to Bluehost and not to other hosting providers.

== Installation ==

1. Install the plugin by clicking `Add New` on the Plugins screen in your WordPress admin, then searching for "Bluehost Site Migrator".
2. Activate the plugin.
3. Click the "Site Migrator" link in the left-hand menu, then click "Check my site".

== Frequently Asked Questions ==

= Does this plugin move my site by itself? =

No. It checks your site and shows you where to go. The migration is performed by the tool built into Bluehost hosting.

= Do I need a Bluehost account? =

Yes. A Bluehost hosting account is required to complete a migration.

= Why did the plugin deactivate itself? =

If the Bluehost plugin is active, your site already has the migration tool under Tools → Import, so this plugin is not needed. It removes the files left by earlier versions, then turns itself off. It does not do this when it is active for a whole multisite network.

= What information does this plugin share? =

None by default. The plugin makes no external requests. For some sites, the page shows a "Submit help request form" link. When you click it, it opens the Bluehost migration support form (migrate.bluehost.com) in a new browser tab and fills in the login address of your site. If you also check the box to include technical details, the link adds these: WordPress, PHP, database, and web server versions, whether the site is a multisite network, whether it uses HTTPS or WooCommerce, the number of active plugins, and the size of the database and media library. It does not send passwords, user data, or site content. These details are part of the link, so the Bluehost server receives them when the form opens. The form itself is sent only when you submit it. The form is operated by Bluehost: [Terms of Service](https://www.bluehost.com/terms/user-agreement), [Privacy Notice](https://www.bluehost.com/privacy).

== Changelog ==

= 2.0.0 =
* Security: Versions 1.0.14 and earlier could leave a copy of the site database (database.sql, or a migration archive) in a place where it could be downloaded without logging in. This version removes the feature that created those files.
* While the plugin is active, it deletes those files. If it cannot delete one, an administrator sees a notice with the location. If the plugin is installed but not active, activate it once or delete it, so the cleanup can run.
* If you started a migration with an earlier version, change your WordPress passwords, and replace any API keys or email passwords that your plugins store, as a precaution.
* The plugin no longer moves a site. A migration to Bluehost now starts from your Bluehost account, and the plugin shows you where.
* On a site that runs the Bluehost plugin, this plugin deactivates itself after the cleanup.

= 1.0.9 =
* Switch to background tasks for packaging and increase timeouts.

= 1.0.8 =
* Updated dependencies and copy regarding compatible hosting plans.

= 1.0.7 =
* Additional checks for presence of required functions.

= 1.0.6 =
* Fix for geolocation functionality

= 1.0.5 =
* Adds international support

= 1.0.4 =
* Applied minor JavaScript dependency security patches
* Update a URL reference on final migration screen

= 1.0.3 =
* Performance improvement: Image optimization

= 1.0.2 =
* Security Patch: Add empty index.php file to prevent directory browsing on insecure server configurations.
* Maintenance: Update account creation URL.

= 1.0.1 =
* Security Patch: Update Lodash version
* Maintenance: Remove debug code

= 1.0 =
* Initial version

== Upgrade Notice ==

= 2.0.0 =
Security update. Earlier versions could leave a copy of the site database where it could be downloaded without logging in. This version deletes it. All users should update.
