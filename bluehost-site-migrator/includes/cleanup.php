<?php
/**
 * Cleanup of artifacts left behind by Bluehost Site Migrator 1.0.14 and earlier.
 *
 * Older versions packaged the site for migration. That could leave two kinds of sensitive
 * artifacts on disk: a raw `database.sql` export in the web root (the issue this release
 * addresses), and migration `.zip` archives, which contain a database dump, in the uploads
 * directory. This file removes both, along with the options, transient, and scheduled event the
 * old version created. It intentionally does NOT drop the `nfd_tasks` / `nfd_task_results`
 * tables, which belong to the shared wp-module-tasks package and may be in use by other plugins.
 *
 * @package BluehostSiteMigrator
 */

defined( 'ABSPATH' ) || exit;

/**
 * Remove all legacy artifacts. Safe to call repeatedly.
 *
 * The web-root dump check is cheap and runs every time so an install self-heals. The heavier
 * cleanup runs until it succeeds. After that, a front-end request only reads an autoloaded option,
 * which costs no query, and an admin request also checks that the archive directory has not come
 * back. If the directory cannot be removed, the cleanup is retried at most once an hour, so a site
 * with unusual file permissions is not slowed down.
 *
 * @param bool $force Run the full cleanup now, ignoring the "done" flag and the retry delay.
 * @return void
 */
function bh_site_migrator_cleanup_legacy_artifacts( $force = false ) {
	bh_site_migrator_remove_stray_db_dumps();

	if ( true !== $force ) {
		/*
		 * Done. A request from an old version that was still running can write the directory again,
		 * so an admin request looks one more time. A front-end request trusts the flag: it must not
		 * ask for the uploads directory this early on every page load.
		 */
		if ( 'yes' === get_option( 'bh_site_migrator_cleaned' ) && ( ! is_admin() || '' === bh_site_migrator_migration_uploads_path() ) ) {
			return;
		}
		if ( get_transient( 'bh_site_migrator_cleanup_retry' ) ) {
			return;
		}
	}

	bh_site_migrator_remove_legacy_data();

	if ( bh_site_migrator_remove_migration_uploads() ) {
		update_option( 'bh_site_migrator_cleaned', 'yes', true );
		delete_transient( 'bh_site_migrator_cleanup_retry' );
	} else {
		// The archives are still on disk. Do not record the cleanup as done; try again later.
		delete_option( 'bh_site_migrator_cleaned' );
		set_transient( 'bh_site_migrator_cleanup_retry', 1, HOUR_IN_SECONDS );
	}
}

/**
 * Run the full cleanup on activation and deactivation, whatever the stored state.
 *
 * @return void
 */
function bh_site_migrator_force_cleanup() {
	bh_site_migrator_cleanup_legacy_artifacts( true );
}

/**
 * Delete any raw database export left in a web-accessible location.
 *
 * Older versions ran `mysqldump --result-file=database.sql` with a relative path, so the file went
 * to the working directory of the request and was never removed, exposing it for unauthenticated
 * download. That directory is the WordPress directory for cron requests, `wp-admin` for admin
 * requests, and the directory of the front controller (`index.php`) for REST requests. The last
 * one is a different directory when WordPress is installed in its own subdirectory. Those are the
 * only places that code could write, and no WordPress core or standard plugin places a file there,
 * so a `database.sql` found at any of them is removed.
 *
 * @return void
 */
function bh_site_migrator_remove_stray_db_dumps() {
	foreach ( bh_site_migrator_stray_db_dump_paths() as $candidate ) {
		wp_delete_file( $candidate );
	}
}

/**
 * List the database exports from older versions that are on disk now.
 *
 * @return string[] Absolute paths. Empty when there is nothing to remove.
 */
function bh_site_migrator_stray_db_dump_paths() {
	$candidates = array(
		ABSPATH . 'database.sql',
		ABSPATH . 'wp-admin/database.sql',
	);

	$home_dir = bh_site_migrator_home_dir( ABSPATH, get_option( 'home' ), get_option( 'siteurl' ) );
	if ( '' !== $home_dir ) {
		$candidates[] = $home_dir . 'database.sql';
	}

	clearstatcache();
	return array_values( array_filter( $candidates, 'is_file' ) );
}

/**
 * Find the migration uploads directory from older versions, if it is on disk now.
 *
 * @return string The path, or '' when it is not there.
 */
function bh_site_migrator_migration_uploads_path() {
	$uploads = wp_get_upload_dir();
	if ( empty( $uploads['basedir'] ) ) {
		return '';
	}

	$dir = trailingslashit( $uploads['basedir'] ) . 'bluehost-site-migrator';
	clearstatcache( true, $dir );
	return ( is_link( $dir ) || is_dir( $dir ) ) ? $dir : '';
}

/**
 * Tell an administrator when a database export, or the directory of migration archives, could not be removed.
 *
 * The removal fails without an error when the file permissions do not allow it, for example when
 * the file was written by a different system user. The file is then still open to download, so
 * the administrator must delete it by hand.
 *
 * @return void
 */
function bh_site_migrator_stray_db_dump_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$remaining = bh_site_migrator_stray_db_dump_paths();
	if ( ! empty( $remaining ) ) :
		?>
		<div class="notice notice-error">
			<p>
				<strong><?php esc_html_e( 'Bluehost Site Migrator: action needed.', 'bluehost-site-migrator' ); ?></strong>
				<?php esc_html_e( 'An earlier version of this plugin left a copy of your database in a place where anyone can download it. The plugin could not delete it because of the file permissions on your server. Delete this file now, or ask your hosting provider to delete it:', 'bluehost-site-migrator' ); ?>
			</p>
			<?php foreach ( $remaining as $path ) : ?>
				<p><code><?php echo esc_html( $path ); ?></code></p>
			<?php endforeach; ?>
		</div>
		<?php
	endif;

	/*
	 * The cleanup ran earlier in this request (on plugins_loaded), or it failed in the last hour.
	 * If the directory is still on disk, the server refused the delete.
	 */
	$directory = bh_site_migrator_migration_uploads_path();
	if ( '' !== $directory ) :
		?>
		<div class="notice notice-error">
			<p>
				<strong><?php esc_html_e( 'Bluehost Site Migrator: action needed.', 'bluehost-site-migrator' ); ?></strong>
				<?php esc_html_e( 'An earlier version of this plugin left a folder for migration archives on your site. An archive can contain a copy of your database. The plugin could not delete the folder, most likely because of the file permissions on your server. Delete this folder now, or ask your hosting provider to delete it:', 'bluehost-site-migrator' ); ?>
			</p>
			<p><code><?php echo esc_html( $directory ); ?></code></p>
		</div>
		<?php
	endif;
}

/**
 * Find the directory of the front controller when WordPress is installed in its own subdirectory.
 *
 * This is the same calculation that get_home_path() does in core, without the need to load an
 * admin include on every request. Example: with the site address `https://example.com`, the
 * WordPress address `https://example.com/wp`, and WordPress in `/var/www/html/wp/`, the result is
 * `/var/www/html/`.
 *
 * @param string $abspath  The WordPress directory (ABSPATH).
 * @param string $home     The site address (the `home` option).
 * @param string $site_url The WordPress address (the `siteurl` option).
 * @return string The directory with a trailing slash, or '' when it is the WordPress directory or
 *                cannot be worked out.
 */
function bh_site_migrator_home_dir( $abspath, $home, $site_url ) {
	// The two addresses can differ in the scheme and in a leading "www.". Neither changes the directory.
	$home     = untrailingslashit( (string) preg_replace( '#^https?://(www\.)?#i', '', (string) $home ) );
	$site_url = untrailingslashit( (string) preg_replace( '#^https?://(www\.)?#i', '', (string) $site_url ) );

	if ( '' === $home || 0 !== stripos( $site_url, $home . '/' ) ) {
		return ''; // Same address, or the WordPress address is not below the site address.
	}

	$relative = substr( $site_url, strlen( $home ) ); // For example "/wp".
	$abspath  = untrailingslashit( str_replace( '\\', '/', (string) $abspath ) );
	$length   = strlen( $relative );

	if ( strlen( $abspath ) <= $length || 0 !== strcasecmp( substr( $abspath, -$length ), $relative ) ) {
		return ''; // The directory layout does not match the addresses. Do not guess.
	}

	return trailingslashit( substr( $abspath, 0, -$length ) );
}

/**
 * Delete the plugin's migration uploads directory (which held database-bearing .zip archives).
 *
 * This uses direct file access on purpose. WP_Filesystem() can select an FTP or SSH method that
 * needs credentials, which are not available during a normal page load, and the archives would
 * then stay on disk. Earlier versions wrote these files with direct PHP file functions, so direct
 * access is what can remove them.
 *
 * @return bool True when the directory is absent afterwards, false when it could not be removed.
 */
function bh_site_migrator_remove_migration_uploads() {
	$uploads = wp_get_upload_dir();
	if ( empty( $uploads['basedir'] ) ) {
		return true;
	}

	$dir = trailingslashit( $uploads['basedir'] ) . 'bluehost-site-migrator';
	if ( is_link( $dir ) ) {
		// Never follow a symbolic link out of the uploads directory. Remove the link itself.
		wp_delete_file( $dir );
		clearstatcache();
		return ! is_link( $dir );
	}
	if ( ! is_dir( $dir ) ) {
		return true;
	}

	if ( ! class_exists( 'WP_Filesystem_Base' ) ) {
		require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
	}
	if ( ! class_exists( 'WP_Filesystem_Direct' ) ) {
		require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
	}

	$filesystem = new WP_Filesystem_Direct( false );

	/*
	 * Walk the tree here instead of using the recursive delete of the filesystem class, which
	 * follows a symbolic link to a directory and would delete the contents of the target. This
	 * iterator does not follow links, and a link is removed as a file.
	 */
	try {
		$items = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ),
			RecursiveIteratorIterator::CHILD_FIRST
		);
		foreach ( $items as $item ) {
			if ( $item->isDir() && ! $item->isLink() ) {
				$filesystem->rmdir( $item->getPathname() );
			} else {
				wp_delete_file( $item->getPathname() );
			}
		}
	} catch ( Exception $e ) {
		return false; // Unreadable directory. Nothing was recorded as done, so this runs again.
	}

	$filesystem->rmdir( $dir );

	clearstatcache();
	return ! is_dir( $dir );
}

/**
 * Delete the options, transient, and scheduled event created by older versions.
 *
 * @return void
 */
function bh_site_migrator_remove_legacy_data() {
	$options = array(
		'bluehost_site_migrator',          // Main options blob (held queued_packaging_tasks, isComplete, etc.).
		'bh_site_migration_id',
		'bh_site_migration_token',
		'bh_site_migration_geo_data',
		'bh_site_migration_country_code',
		'bh_site_migration_region_urls',
	);
	foreach ( $options as $option ) {
		delete_option( $option );
	}

	delete_transient( 'bluehost_site_migrator_can_migrate' );
	wp_clear_scheduled_hook( 'bluehost_site_migrator_purge_migration_packages' );
}
