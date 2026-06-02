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

// Tutor LMS glue only loads when the plugin is active.
if ( function_exists( 'tutor' ) || class_exists( 'TUTOR\\Tutor' ) ) {
	bia_learn_require( 'tutor' );
}
