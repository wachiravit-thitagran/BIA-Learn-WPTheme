<?php
/**
 * Self-update from GitHub Releases via the Plugin Update Checker library.
 *
 * Releases are produced automatically by .github/workflows/release.yml — one
 * per push to `main`. Each release attaches a ready-to-install `bia-learn.zip`
 * built in CI, and this checker downloads that asset (rather than GitHub's
 * source zip) so the installed folder name and compiled assets are always
 * correct. The repo is public, so no access token is required.
 *
 * Library: Plugin Update Checker 5.7 by Yahnis Elsts (MIT) — vendored under
 * inc/lib/plugin-update-checker/.
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

/**
 * Initialise the GitHub update checker once the library is present.
 */
function bia_learn_init_updater() {
	$lib    = BIA_LEARN_DIR . '/inc/lib/plugin-update-checker';
	$loader = $lib . '/plugin-update-checker.php';

	// Require the loader AND a key vendored dependency (Parsedown, used by PUC to
	// render GitHub release notes). If the library is incomplete — e.g. vendor/
	// got stripped from a distribution — skip updates rather than risk a fatal
	// during the update check. Defence in depth against the missing-Parsedown bug.
	if ( ! is_readable( $loader ) || ! is_readable( $lib . '/vendor/Parsedown.php' ) ) {
		return;
	}
	require_once $loader;

	$factory = '\\YahnisElsts\\PluginUpdateChecker\\v5\\PucFactory';
	if ( ! class_exists( $factory ) ) {
		return;
	}

	$checker = $factory::buildUpdateChecker(
		'https://github.com/wachiravit-thitagran/BIA-Learn-WPTheme/',
		BIA_LEARN_DIR . '/style.css', // Theme mode: PUC reads the Version header here.
		'bia-learn'
	);

	// Prefer the CI-built release asset (correct theme folder + compiled assets).
	// Falls back to the release source zip if the asset is ever missing.
	$api = $checker->getVcsApi();
	if ( $api && method_exists( $api, 'enableReleaseAssets' ) ) {
		$api->enableReleaseAssets( '/bia-learn\.zip$/' );
	}
}
add_action( 'after_setup_theme', 'bia_learn_init_updater' );
