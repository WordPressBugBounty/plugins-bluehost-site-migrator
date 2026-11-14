<?php
/**
 * Self-deactivation on a site that already runs the Bluehost plugin.
 *
 * The Bluehost plugin has the migration tool, so this plugin has nothing to tell that site. It
 * removes the files from older versions first (see includes/cleanup.php), then turns itself off.
 *
 * @package BluehostSiteMigrator
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether the Bluehost plugin is active on this site.
 *
 * @return bool
 */
function bh_site_migrator_is_bluehost_site() {
	if ( defined( 'BLUEHOST_PLUGIN_VERSION' ) || defined( 'BLUEHOST_PLUGIN_FILE' ) ) {
		return true;
	}
	foreach ( (array) get_option( 'active_plugins', array() ) as $plugin ) {
		// Match the Bluehost plugin, not this migrator.
		if ( 0 === strpos( (string) $plugin, 'bluehost-wordpress-plugin/' )
			|| false !== strpos( (string) $plugin, 'wp-plugin-bluehost' ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Deactivate this plugin when the Bluehost plugin is active and the cleanup is complete.
 *
 * It runs only on an admin page that an administrator is looking at. A deactivated plugin is not
 * loaded again, so that page is the only chance to say what happened.
 *
 * @return void
 */
function bh_site_migrator_maybe_deactivate() {
	if ( ! bh_site_migrator_is_bluehost_site() ) {
		return;
	}
	if ( wp_doing_ajax() || ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	/*
	 * Stay active while a file or the archive directory from an older version is still on disk.
	 * The cleanup retries on later page loads, and the notice about a file that could not be
	 * deleted needs this plugin to be active.
	 */
	if ( 'yes' !== get_option( 'bh_site_migrator_cleaned' ) || bh_site_migrator_stray_db_dump_paths() || '' !== bh_site_migrator_migration_uploads_path() ) {
		return;
	}

	if ( ! function_exists( 'deactivate_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	/*
	 * Never deactivate a network-active plugin. The cleanup works one site at a time, so one clean
	 * site with the Bluehost plugin must not stop the cleanup for the other sites of the network.
	 */
	$plugin = plugin_basename( BH_SITE_MIGRATOR_FILE );
	if ( is_multisite() && is_plugin_active_for_network( $plugin ) ) {
		return;
	}

	// The last argument keeps this to the current site, whatever screen the administrator is on.
	deactivate_plugins( $plugin, false, false );
	if ( is_plugin_active( $plugin ) ) {
		return;
	}

	/*
	 * Do not let the Plugins screen say "Plugin activated" for a plugin that is now inactive. Do
	 * this only when this plugin is the one that was activated a moment ago. When an administrator
	 * activated a different plugin, for example the Bluehost plugin, that message is correct.
	 */
	if ( get_transient( 'bh_site_migrator_activated' ) ) {
		delete_transient( 'bh_site_migrator_activated' );
		unset( $_GET['activate'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	$GLOBALS['bh_site_migrator_deactivated'] = true;
	add_action( 'admin_notices', 'bh_site_migrator_deactivated_notice' );
}

/**
 * Record, for one minute, that this plugin was activated. See bh_site_migrator_maybe_deactivate().
 *
 * @return void
 */
function bh_site_migrator_mark_activated() {
	set_transient( 'bh_site_migrator_activated', 1, MINUTE_IN_SECONDS );
}

/**
 * Tell the administrator that the plugin turned itself off.
 *
 * @return void
 */
function bh_site_migrator_deactivated_notice() {
	?>
	<div class="notice notice-info">
		<p><?php esc_html_e( 'Bluehost Site Migrator has been deactivated. The Bluehost plugin on this site already has the migration tool, under Tools → Import.', 'bluehost-site-migrator' ); ?></p>
	</div>
	<?php
}
