<?php
/**
 * Local checks that decide where to send the user.
 *
 * These run on the current site only, with no external requests.
 *
 * @package BluehostSiteMigrator
 */

defined( 'ABSPATH' ) || exit;

/**
 * Run the checks.
 *
 * @return string[] Check id => 'pass' | 'warn' | 'block'.
 */
function bh_site_migrator_qualification_checks() {
	// WordPress in its own subdirectory: the WordPress address has a different path from the site address.
	$site_path = untrailingslashit( (string) wp_parse_url( site_url(), PHP_URL_PATH ) );
	$home_path = untrailingslashit( (string) wp_parse_url( home_url(), PHP_URL_PATH ) );

	return array(
		// The self-service migration moves single sites only. Any result but 'pass' sends the user to the support team.
		'multisite' => is_multisite() ? 'block' : 'pass',
		'layout'    => ( $site_path !== $home_path ) ? 'warn' : 'pass',
		'https'     => is_ssl() ? 'pass' : 'warn',
		// The versions that the plugin header asks for. WordPress 5.2 and later also check them at install and update.
		'versions'  => ( version_compare( PHP_VERSION, '5.6', '>=' ) && version_compare( get_bloginfo( 'version' ), '4.7', '>=' ) ) ? 'pass' : 'warn',
	);
}

/**
 * The developer-forced status, if BH_SITE_MIGRATOR_FORCE_STATUS is defined to a valid value.
 *
 * Define it as 'pass', 'warn', or 'block' (for example in wp-config.php) to preview each result.
 *
 * @return string|false 'pass' | 'warn' | 'block', or false when not forced.
 */
function bh_site_migrator_forced_status() {
	if ( defined( 'BH_SITE_MIGRATOR_FORCE_STATUS' )
		&& in_array( BH_SITE_MIGRATOR_FORCE_STATUS, array( 'pass', 'warn', 'block' ), true ) ) {
		return BH_SITE_MIGRATOR_FORCE_STATUS;
	}
	return false;
}

/**
 * Reduce the check results to one verdict.
 *
 * @param string[] $checks Results from bh_site_migrator_qualification_checks().
 * @return string 'block' | 'warn' | 'pass'
 */
function bh_site_migrator_overall_status( $checks ) {
	$forced = bh_site_migrator_forced_status();
	if ( $forced ) {
		return $forced;
	}
	if ( in_array( 'block', $checks, true ) ) {
		return 'block';
	}
	if ( in_array( 'warn', $checks, true ) ) {
		return 'warn';
	}
	return 'pass';
}
