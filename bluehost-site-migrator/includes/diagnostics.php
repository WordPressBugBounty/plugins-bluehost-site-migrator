<?php
/**
 * On-demand migration diagnostics.
 *
 * Gathers non-sensitive facts about the site that help triage and automate a migration: the sizes
 * that drive scheduling (database, uploads), a few compatibility versions, and the cURL/TLS backend
 * that explains outbound-connection failures. It is gathered only when a support request is offered,
 * and cached for an hour so the uploads scan does not repeat on every page load. No credentials or
 * user data are collected.
 *
 * @package BluehostSiteMigrator
 */

defined( 'ABSPATH' ) || exit;

/**
 * The most time, in seconds, the uploads scan may take.
 */
const BH_SITE_MIGRATOR_SCAN_SECONDS = 5;

/**
 * Gather the migration diagnostics (cached for one hour).
 *
 * @return array
 */
function bh_site_migrator_diagnostics() {
	$cached = get_transient( 'bh_site_migrator_diag' );
	if ( is_array( $cached ) ) {
		return $cached;
	}

	global $wpdb;

	// Database size and approximate row count (one cheap information_schema query, result cached below).
	$db_bytes = 0;
	$db_rows  = 0;
	// Some hosts deny access to information_schema. Do not print a database error on the page.
	$suppress = $wpdb->suppress_errors( true );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$row = $wpdb->get_row(
		$wpdb->prepare(
			'SELECT SUM(data_length + index_length) AS bytes, SUM(table_rows) AS rows_count
			 FROM information_schema.tables WHERE table_schema = %s',
			DB_NAME
		)
	);
	$wpdb->suppress_errors( $suppress );
	if ( $row ) {
		$db_bytes = (int) $row->bytes;
		$db_rows  = (int) $row->rows_count;
	}

	/*
	 * Uploads directory size and file count (the dominant factor in migration time). The scan has a
	 * time budget so a very large media library cannot hold up, or time out, the admin page. When the
	 * budget runs out the totals are a lower bound and `uploads_partial` says so.
	 */
	$uploads_bytes   = 0;
	$uploads_files   = 0;
	$uploads_partial = false;
	$uploads         = wp_get_upload_dir();
	if ( ! empty( $uploads['basedir'] ) && is_dir( $uploads['basedir'] ) ) {
		$deadline = microtime( true ) + BH_SITE_MIGRATOR_SCAN_SECONDS;
		try {
			$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator( $uploads['basedir'], FilesystemIterator::SKIP_DOTS )
			);
			foreach ( $iterator as $file ) {
				// Skip links: a broken one throws on getSize(), and a link can point outside uploads.
				if ( ! $file->isLink() && $file->isFile() ) {
					$uploads_bytes += $file->getSize();
					++$uploads_files;
				}
				if ( microtime( true ) > $deadline ) {
					$uploads_partial = true;
					break;
				}
			}
		} catch ( Exception $e ) {
			// An unreadable directory or a file that vanished mid-scan: keep what was counted.
			$uploads_partial = true;
		}
	}

	// cURL and its TLS backend: triage for why a source cannot reach the migration service.
	$curl = '';
	if ( function_exists( 'curl_version' ) ) {
		$info = curl_version();
		if ( is_array( $info ) && isset( $info['version'] ) ) {
			$curl = $info['version'];
			if ( isset( $info['ssl_version'] ) ) {
				$curl .= ' / ' . $info['ssl_version'];
			}
		}
	}

	$web_server = '';
	if ( isset( $_SERVER['SERVER_SOFTWARE'] ) ) {
		$web_server = sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) );
	}

	$data = array(
		'wp_version'      => get_bloginfo( 'version' ),
		'php_version'     => PHP_VERSION,
		'db_server'       => method_exists( $wpdb, 'db_server_info' ) ? $wpdb->db_server_info() : '',
		'web_server'      => $web_server,
		'curl'            => $curl,
		'multisite'       => is_multisite(),
		'https'           => is_ssl(),
		'woocommerce'     => class_exists( 'WooCommerce' ),
		'active_plugins'  => count( (array) get_option( 'active_plugins', array() ) ),
		'db_bytes'        => $db_bytes,
		'db_rows'         => $db_rows,
		'uploads_bytes'   => $uploads_bytes,
		'uploads_files'   => $uploads_files,
		'uploads_partial' => $uploads_partial,
	);

	set_transient( 'bh_site_migrator_diag', $data, HOUR_IN_SECONDS );
	return $data;
}
