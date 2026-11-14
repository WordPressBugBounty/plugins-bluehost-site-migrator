<?php
/**
 * Bluehost Site Migrator
 *
 * @package           BluehostSiteMigrator
 * @author            Bluehost
 * @copyright         Copyright 2026 by Bluehost - All rights reserved.
 * @license           GPL2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Bluehost Site Migrator
 * Plugin URI:        https://wordpress.org/plugins/bluehost-site-migrator
 * Description:       Shows you the best way to move your WordPress site to Bluehost.
 * Version:           2.0.0
 * Requires PHP:      5.6
 * Requires at least: 4.7
 * Author:            Bluehost
 * Author URI:        https://www.bluehost.com/
 * Text Domain:       bluehost-site-migrator
 * Domain Path:       /languages
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

defined( 'ABSPATH' ) || exit;

define( 'BH_SITE_MIGRATOR_VERSION', '2.0.0' );
define( 'BH_SITE_MIGRATOR_FILE', __FILE__ );
define( 'BH_SITE_MIGRATOR_DIR', plugin_dir_path( __FILE__ ) );
define( 'BH_SITE_MIGRATOR_URL', plugin_dir_url( __FILE__ ) );

require_once BH_SITE_MIGRATOR_DIR . 'includes/cleanup.php';
require_once BH_SITE_MIGRATOR_DIR . 'includes/self-deactivate.php';
require_once BH_SITE_MIGRATOR_DIR . 'includes/checks.php';
require_once BH_SITE_MIGRATOR_DIR . 'includes/diagnostics.php';
require_once BH_SITE_MIGRATOR_DIR . 'includes/admin-page.php';

// Register the admin menu (same page slug as earlier versions, so the menu entry is unchanged).
add_action( 'admin_menu', 'bh_site_migrator_register_menu' );
add_action( 'admin_enqueue_scripts', 'bh_site_migrator_menu_icon_style' );
add_action( 'wp_ajax_bh_site_migrator_checked', 'bh_site_migrator_ajax_mark_checked' );

/*
 * Remove any sensitive artifacts left behind by versions 1.0.14 and earlier, which could leave
 * a database export in a web-accessible location after a migration. This release contains no
 * migration or packaging code; the cleanup runs so existing installs self-heal on update, on
 * activation, and on deactivation. See includes/cleanup.php.
 */
add_action( 'plugins_loaded', 'bh_site_migrator_cleanup_legacy_artifacts', 10, 0 );
add_action( 'admin_notices', 'bh_site_migrator_stray_db_dump_notice' );
add_action( 'network_admin_notices', 'bh_site_migrator_stray_db_dump_notice' );
register_activation_hook( __FILE__, 'bh_site_migrator_force_cleanup' );
register_activation_hook( __FILE__, 'bh_site_migrator_mark_activated' );
register_deactivation_hook( __FILE__, 'bh_site_migrator_force_cleanup' );

// A site with the Bluehost plugin already has the migration tool. Once the cleanup is done, turn off.
add_action( 'admin_init', 'bh_site_migrator_maybe_deactivate' );
