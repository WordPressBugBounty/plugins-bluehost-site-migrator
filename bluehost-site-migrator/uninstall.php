<?php
/**
 * Uninstall routine for Bluehost Site Migrator.
 *
 * Removes every artifact the plugin (current or legacy) may have left behind.
 *
 * @package BluehostSiteMigrator
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

require_once plugin_dir_path( __FILE__ ) . 'includes/cleanup.php';

bh_site_migrator_remove_stray_db_dumps();
bh_site_migrator_remove_migration_uploads();
bh_site_migrator_remove_legacy_data();

delete_option( 'bh_site_migrator_cleaned' );
delete_transient( 'bh_site_migrator_diag' );
delete_transient( 'bh_site_migrator_checked' );
delete_transient( 'bh_site_migrator_activated' );
delete_transient( 'bh_site_migrator_cleanup_retry' );
