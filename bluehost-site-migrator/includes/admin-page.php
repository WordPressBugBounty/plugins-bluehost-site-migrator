<?php
/**
 * Admin menu and page for Bluehost Site Migrator.
 *
 * One button. It runs the local checks (see includes/checks.php), then points the user either to
 * the Bluehost migration tool or to the migration support team. The plugin performs no migration
 * and makes no external requests.
 *
 * @package BluehostSiteMigrator
 */

defined( 'ABSPATH' ) || exit;

/**
 * The admin page slug. Unchanged from earlier versions so the menu entry stays in place.
 */
const BH_SITE_MIGRATOR_PAGE_SLUG = 'bluehost-site-migrator';

/**
 * Outbound destinations. Centralized so they are easy to update.
 */
// The help article and the paid service go through outbound.click, the Newfold Digital link service
// (newfold-labs/cf-worker-shorturl). This is on purpose: the destinations can change without a plugin release.
const BH_SITE_MIGRATOR_HELP_URL         = 'https://outbound.click/5P74WH918JPN08B59G60YSZ03W';
const BH_SITE_MIGRATOR_SUPPORT_FORM_URL = 'https://migrate.bluehost.com/support-form';
const BH_SITE_MIGRATOR_PAID_SERVICE_URL = 'https://outbound.click/0N6GZN5WZPQC1DKMQ3VBE12DFD';
const BH_SITE_MIGRATOR_TERMS_URL        = 'https://www.bluehost.com/terms/user-agreement';
const BH_SITE_MIGRATOR_PRIVACY_URL      = 'https://www.bluehost.com/privacy';

/**
 * Register the top-level admin menu page.
 *
 * @return void
 */
function bh_site_migrator_register_menu() {
	$icon = 'data:image/svg+xml;base64,PHN2ZyBpZD0iTGF5ZXJfMSIgZGF0YS1uYW1lPSJMYXllciAxIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA1OC4wMyA1OC4xMyI+PGRlZnM+PHN0eWxlPi5jbHMtMXtmaWxsOiNmZmY7fTwvc3R5bGU+PC9kZWZzPjx0aXRsZT5iaC13aGl0ZTwvdGl0bGU+PGcgaWQ9Il9Hcm91cF8iIGRhdGEtbmFtZT0iJmx0O0dyb3VwJmd0OyI+PGcgaWQ9Il9Hcm91cF8yIiBkYXRhLW5hbWU9IiZsdDtHcm91cCZndDsiPjxnIGlkPSJfR3JvdXBfMyIgZGF0YS1uYW1lPSImbHQ7R3JvdXAmZ3Q7Ij48cmVjdCBpZD0iX1BhdGhfIiBkYXRhLW5hbWU9IiZsdDtQYXRoJmd0OyIgY2xhc3M9ImNscy0xIiB3aWR0aD0iMTYuMiIgaGVpZ2h0PSIxNi4yMSIvPjxyZWN0IGlkPSJfUGF0aF8yIiBkYXRhLW5hbWU9IiZsdDtQYXRoJmd0OyIgY2xhc3M9ImNscy0xIiB4PSIyMC45MSIgd2lkdGg9IjE2LjIxIiBoZWlnaHQ9IjE2LjIxIi8+PHJlY3QgaWQ9Il9QYXRoXzMiIGRhdGEtbmFtZT0iJmx0O1BhdGgmZ3Q7IiBjbGFzcz0iY2xzLTEiIHg9IjQxLjgyIiB3aWR0aD0iMTYuMjEiIGhlaWdodD0iMTYuMjEiLz48cmVjdCBpZD0iX1BhdGhfNCIgZGF0YS1uYW1lPSImbHQ7UGF0aCZndDsiIGNsYXNzPSJjbHMtMSIgeT0iMjAuOTYiIHdpZHRoPSIxNi4yIiBoZWlnaHQ9IjE2LjIxIi8+PHJlY3QgaWQ9Il9QYXRoXzUiIGRhdGEtbmFtZT0iJmx0O1BhdGgmZ3Q7IiBjbGFzcz0iY2xzLTEiIHg9IjIwLjkxIiB5PSIyMC45NiIgd2lkdGg9IjE2LjIxIiBoZWlnaHQ9IjE2LjIxIi8+PHJlY3QgaWQ9Il9QYXRoXzYiIGRhdGEtbmFtZT0iJmx0O1BhdGgmZ3Q7IiBjbGFzcz0iY2xzLTEiIHg9IjQxLjgyIiB5PSIyMC45NiIgd2lkdGg9IjE2LjIxIiBoZWlnaHQ9IjE2LjIxIi8+PHJlY3QgaWQ9Il9QYXRoXzciIGRhdGEtbmFtZT0iJmx0O1BhdGgmZ3Q7IiBjbGFzcz0iY2xzLTEiIHk9IjQxLjkyIiB3aWR0aD0iMTYuMiIgaGVpZ2h0PSIxNi4yMSIvPjxyZWN0IGlkPSJfUGF0aF84IiBkYXRhLW5hbWU9IiZsdDtQYXRoJmd0OyIgY2xhc3M9ImNscy0xIiB4PSIyMC45MSIgeT0iNDEuOTIiIHdpZHRoPSIxNi4yMSIgaGVpZ2h0PSIxNi4yMSIvPjxyZWN0IGlkPSJfUGF0aF85IiBkYXRhLW5hbWU9IiZsdDtQYXRoJmd0OyIgY2xhc3M9ImNscy0xIiB4PSI0MS44MiIgeT0iNDEuOTIiIHdpZHRoPSIxNi4yMSIgaGVpZ2h0PSIxNi4yMSIvPjwvZz48L2c+PC9nPjwvc3ZnPg==';

	$hook = add_menu_page(
		__( 'Bluehost Site Migrator', 'bluehost-site-migrator' ),
		__( 'Site Migrator', 'bluehost-site-migrator' ),
		'manage_options',
		BH_SITE_MIGRATOR_PAGE_SLUG,
		'bh_site_migrator_render_page',
		$icon
	);

	add_action( 'admin_print_styles-' . $hook, 'bh_site_migrator_enqueue_styles' );
}

/**
 * Line up the menu icon with the menu label on a small screen.
 *
 * WordPress gives a menu icon that is an SVG image a shorter box than the label on screens up to
 * 782 pixels wide, so the icon sits too high. This runs on every admin page, because the menu does.
 *
 * @return void
 */
function bh_site_migrator_menu_icon_style() {
	wp_add_inline_style(
		'wp-admin',
		'@media screen and (max-width:782px){#adminmenu #toplevel_page_' . BH_SITE_MIGRATOR_PAGE_SLUG . ' div.wp-menu-image.svg{height:37px}}'
	);
}

/**
 * Whether someone has run the check on this site in the last 48 hours.
 *
 * The page then goes straight to the result. The result itself is worked out on each page load.
 *
 * @return bool
 */
function bh_site_migrator_was_checked() {
	return (bool) get_transient( 'bh_site_migrator_checked' );
}

/**
 * Record that the check has run. The page script calls this when the progress line finishes.
 *
 * @return void
 */
function bh_site_migrator_ajax_mark_checked() {
	check_ajax_referer( 'bh_site_migrator_checked' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( null, 403 );
	}
	set_transient( 'bh_site_migrator_checked', 1, 2 * DAY_IN_SECONDS );
	wp_send_json_success();
}

/**
 * Enqueue the page stylesheet and script (only on this plugin's page).
 *
 * The script loads in the head on purpose. It marks the document before the page is painted, so
 * the result does not show for a moment before the button replaces it.
 *
 * @return void
 */
function bh_site_migrator_enqueue_styles() {
	wp_enqueue_style(
		'bluehost-site-migrator',
		BH_SITE_MIGRATOR_URL . 'assets/css/admin.css',
		array(),
		BH_SITE_MIGRATOR_VERSION
	);
	wp_enqueue_script(
		'bluehost-site-migrator',
		BH_SITE_MIGRATOR_URL . 'assets/js/admin.js',
		array(),
		BH_SITE_MIGRATOR_VERSION,
		false
	);

	// The page has its own footer (WordPress hides its footer on a small screen), so the WordPress lines go.
	add_filter( 'admin_footer_text', '__return_empty_string', 20 );
	add_filter( 'update_footer', '__return_empty_string', 20 );
}

/**
 * Render the page.
 *
 * The result is in the page from the start. With scripts on, it stays hidden until the user
 * presses the button and the progress line finishes. With scripts off, it shows at once. It also
 * shows at once when someone has run the check on this site in the last 48 hours.
 *
 * @return void
 */
function bh_site_migrator_render_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( ! empty( $GLOBALS['bh_site_migrator_deactivated'] ) ) {
		// The plugin turned itself off during this request. The admin notice says so.
		echo '<div class="wrap"><h1>' . esc_html__( 'Bluehost Site Migrator', 'bluehost-site-migrator' ) . '</h1></div>';
		return;
	}

	$overall = bh_site_migrator_overall_status( bh_site_migrator_qualification_checks() );
	$seen    = bh_site_migrator_was_checked();

	/*
	 * Only a site that passes every check gets the steps for the automated transfer. A site that
	 * fails one (a multisite network, WordPress in its own subdirectory, no HTTPS, old software)
	 * goes to the support team, because the automated transfer may not work for it.
	 */
	$assisted = ( 'pass' !== $overall );
	?>
	<div class="wrap bhsm-wrap">
		<h1 class="screen-reader-text"><?php esc_html_e( 'Bluehost Site Migrator', 'bluehost-site-migrator' ); ?></h1>
		<hr class="wp-header-end">

		<div class="bhsm-panel<?php echo $seen ? ' bhsm-seen' : ''; ?>" id="bhsm-panel" data-nonce="<?php echo esc_attr( wp_create_nonce( 'bh_site_migrator_checked' ) ); ?>">
			<?php if ( ! $seen ) : ?>
				<div class="bhsm-start">
					<h2 class="bhsm-title"><?php esc_html_e( 'Move this site to Bluehost', 'bluehost-site-migrator' ); ?></h2>
					<p class="bhsm-lead"><?php esc_html_e( 'We will take a quick look at this site and show you the best way to move it.', 'bluehost-site-migrator' ); ?></p>
					<?php // A box of fixed height holds the button, then the mark and its text, so nothing below it moves. ?>
					<div class="bhsm-stage">
					<p class="bhsm-action"><button type="button" class="bhsm-button" id="bhsm-start"><?php esc_html_e( 'Check my site', 'bluehost-site-migrator' ); ?></button></p>
					<?php
					/*
					 * Each line names a check that runs before this page loads (all four are in
					 * includes/checks.php), so the script only paces the text: a second or two for each
					 * line, one at a time.
					 */
					?>
					<div class="bhsm-progress">
						<img src="<?php echo esc_url( BH_SITE_MIGRATOR_URL . 'assets/images/loading.svg' ); ?>" alt="" width="56" height="56" draggable="false">
						<ul aria-live="polite">
							<li><?php esc_html_e( 'Checking site configuration', 'bluehost-site-migrator' ); ?></li>
							<li><?php esc_html_e( 'Checking the WordPress install path', 'bluehost-site-migrator' ); ?></li>
							<li><?php esc_html_e( 'Inspecting SSL/TLS', 'bluehost-site-migrator' ); ?></li>
							<li><?php esc_html_e( 'Checking software versions', 'bluehost-site-migrator' ); ?></li>
						</ul>
					</div>
					</div>
				</div>
			<?php endif; ?>

			<div class="bhsm-result" id="bhsm-result" tabindex="-1">
				<?php if ( $assisted ) : ?>
					<h2 class="bhsm-title"><?php esc_html_e( 'Let’s work on this one together', 'bluehost-site-migrator' ); ?></h2>
					<p class="bhsm-lead"><?php esc_html_e( 'Your site may be too complex for an automated transfer. Please fill out our support form, and our team will look into your specific case to see if we can still assist with the migration.', 'bluehost-site-migrator' ); ?></p>
					<p><a class="bhsm-button bhsm-help-link" href="<?php echo esc_url( bh_site_migrator_support_url( false ) ); ?>" data-with-details="<?php echo esc_url( bh_site_migrator_support_url( true ) ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Submit help request form', 'bluehost-site-migrator' ); ?></a></p>
					<?php bh_site_migrator_render_details_optin(); ?>
					<p class="bhsm-second">
						<?php
						printf(
							/* translators: %s: a link with the text "full-service Site Migration". */
							esc_html__( 'You can also opt for our %s, handled by our expert Migrations team for a fee.', 'bluehost-site-migrator' ),
							'<a href="' . esc_url( BH_SITE_MIGRATOR_PAID_SERVICE_URL ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'full-service Site Migration', 'bluehost-site-migrator' ) . '</a>'
						);
						?>
					</p>
				<?php else : ?>
					<h2 class="bhsm-title"><?php esc_html_e( 'You’re ready to start', 'bluehost-site-migrator' ); ?></h2>
					<p class="bhsm-lead"><?php esc_html_e( 'The transfer starts from your Bluehost account. Log in, go to Websites, click Add Website, then choose Transfer WordPress Website.', 'bluehost-site-migrator' ); ?></p>
					<p><a class="bhsm-button" href="<?php echo esc_url( BH_SITE_MIGRATOR_HELP_URL ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Show me the steps', 'bluehost-site-migrator' ); ?></a></p>
				<?php endif; ?>
			</div>

			<?php // Two pictures. The first one flips over, like a card, to show the second one with the result. ?>
			<div class="bhsm-art" aria-hidden="true">
				<?php if ( ! $seen ) : ?>
					<img class="bhsm-art__start" src="<?php echo esc_url( BH_SITE_MIGRATOR_URL . 'assets/images/start.svg' ); ?>" alt="" width="200" height="200" draggable="false">
				<?php endif; ?>
				<img class="bhsm-art__result" src="<?php echo esc_url( BH_SITE_MIGRATOR_URL . 'assets/images/' . ( $assisted ? 'assisted' : 'ready' ) . '.svg' ); ?>" alt="" width="200" height="200" draggable="false">
			</div>

			<p class="bhsm-footer">
				<a href="<?php echo esc_url( BH_SITE_MIGRATOR_TERMS_URL ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Terms of Service', 'bluehost-site-migrator' ); ?></a>
				|
				<a href="<?php echo esc_url( BH_SITE_MIGRATOR_PRIVACY_URL ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Privacy Notice', 'bluehost-site-migrator' ); ?></a>
			</p>
		</div>
	</div>
	<?php
}

/**
 * Render the checkbox that adds the site details to the help request.
 *
 * It is not checked by default, and it needs the page script, so it is hidden when scripts are off.
 *
 * @return void
 */
function bh_site_migrator_render_details_optin() {
	?>
	<p class="bhsm-optin">
		<label>
			<input type="checkbox" id="bhsm-details">
			<?php esc_html_e( 'Include technical details about this site (software versions, database size, media size).', 'bluehost-site-migrator' ); ?>
		</label>
	</p>
	<?php
}

/**
 * Build the address of the migration support form, which opens in a new tab.
 *
 * The form is filled in with the login address of this site. The site details (base64url-encoded
 * JSON, see includes/diagnostics.php) are added only when the user asks for that.
 *
 * @param bool $with_details Add the site details.
 * @return string
 */
function bh_site_migrator_support_url( $with_details ) {
	/*
	 * Not double encoding. add_query_arg() adds each value as it is; it does not URL-encode values
	 * (its documentation says the caller must). rawurlencode() here is the only encoding, and the
	 * login address needs it because it can have its own query string. Checked on WordPress 4.7 and
	 * 7.2: the result has `src_url=https%3A%2F%2F...`, with `%3A`, not `%253A`.
	 */
	$args = array( 'src_url' => rawurlencode( wp_login_url() ) );

	if ( $with_details ) {
		// base64url keeps the JSON intact in a query string. It is transport encoding, not obfuscation.
		$args['diag'] = rtrim( strtr( base64_encode( wp_json_encode( bh_site_migrator_diagnostics() ) ), '+/', '-_' ), '=' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
	}

	return add_query_arg( $args, BH_SITE_MIGRATOR_SUPPORT_FORM_URL );
}
