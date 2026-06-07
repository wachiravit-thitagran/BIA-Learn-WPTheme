<?php
/**
 * BIA Learn theme bootstrap.
 *
 * Loads the modular includes that configure the theme. Each concern lives in
 * its own file under /inc to keep things small and focused.
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'BIA_LEARN_VERSION' ) ) {
	define( 'BIA_LEARN_VERSION', '1.0.0' );
}
define( 'BIA_LEARN_DIR', get_template_directory() );
define( 'BIA_LEARN_URI', get_template_directory_uri() );

/**
 * Whether Tutor LMS is available.
 *
 * @return bool
 */
function bia_learn_has_tutor_lms() {
	return function_exists( 'tutor_utils' ) || function_exists( 'tutor' ) || class_exists( 'TUTOR\\Tutor' );
}

/**
 * Return Tutor's utility object when available.
 *
 * @return object|null
 */
function bia_learn_tutor_utils() {
	return function_exists( 'tutor_utils' ) ? tutor_utils() : null;
}

/**
 * Check whether the Tutor utility object supports a method.
 *
 * @param string $method Method name.
 * @return bool
 */
function bia_learn_tutor_utils_supports( $method ) {
	$utils = bia_learn_tutor_utils();

	return $utils && is_string( $method ) && method_exists( $utils, $method );
}

/**
 * Resolve the Tutor dashboard URL with a safe fallback.
 *
 * @param string $fallback Fallback URL.
 * @return string
 */
function bia_learn_tutor_dashboard_url( $fallback = '' ) {
	if ( bia_learn_tutor_utils_supports( 'tutor_dashboard_url' ) ) {
		return (string) bia_learn_tutor_utils()->tutor_dashboard_url();
	}

	return $fallback ? $fallback : wp_login_url();
}

/**
 * Require an include file from the /inc directory.
 *
 * @param string $relative Path relative to /inc, without extension.
 */
function bia_learn_require( $relative ) {
	require_once BIA_LEARN_DIR . '/inc/' . $relative . '.php';
}

bia_learn_require( 'setup' );          // Theme supports, menus, image sizes.
bia_learn_require( 'enqueue' );        // Styles, scripts, fonts.
bia_learn_require( 'template-tags' );  // Reusable presentation helpers.
bia_learn_require( 'widgets' );        // Sidebar + footer widget areas.
bia_learn_require( 'customizer' );     // Brand / contact / social settings.
bia_learn_require( 'structured-data' ); // JSON-LD (Organization, breadcrumb, article).
bia_learn_require( 'updater' );        // Self-update from GitHub Releases.

// Tutor LMS glue only loads when the plugin is active.
if ( bia_learn_has_tutor_lms() ) {
	bia_learn_require( 'tutor' );
}
