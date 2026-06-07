<?php
/**
 * Enqueue compiled styles, scripts and webfonts.
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

/**
 * Cache-busting version: file mtime in dev, theme version in production.
 *
 * @param string $relative Path relative to the theme root.
 * @return string
 */
function bia_learn_asset_version( $relative ) {
	$path = BIA_LEARN_DIR . '/' . ltrim( $relative, '/' );
	return file_exists( $path ) ? (string) filemtime( $path ) : BIA_LEARN_VERSION;
}

/**
 * Front-end assets.
 */
function bia_learn_enqueue_assets() {
	// Compiled Tailwind stylesheet. Self-hosted webfonts (Sarabun + Noto Serif
	// Thai) are declared with @font-face inside this file — see src/css/main.css.
	wp_enqueue_style(
		'bia-learn-style',
		BIA_LEARN_URI . '/assets/css/main.css',
		array(),
		bia_learn_asset_version( 'assets/css/main.css' )
	);

	// Bundled Alpine.js + interactions.
	wp_enqueue_script(
		'bia-learn-main',
		BIA_LEARN_URI . '/assets/js/main.js',
		array(),
		bia_learn_asset_version( 'assets/js/main.js' ),
		true
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'bia_learn_enqueue_assets' );

/**
 * Preload the primary Thai webfonts (body + display) to reduce layout shift /
 * flash of unstyled text. Other weights/subsets load on demand via @font-face.
 *
 * @param array $preload_resources Existing preload entries.
 * @return array
 */
function bia_learn_preload_fonts( $preload_resources ) {
	foreach ( array( 'sarabun-400-thai', 'notoserifthai-700-thai' ) as $slug ) {
		$preload_resources[] = array(
			'href'        => BIA_LEARN_URI . '/assets/fonts/' . $slug . '.woff2',
			'as'          => 'font',
			'type'        => 'font/woff2',
			'crossorigin' => 'anonymous',
		);
	}
	return $preload_resources;
}
add_filter( 'wp_preload_resources', 'bia_learn_preload_fonts' );

/**
 * Mark the bundled script as a module (Alpine ships as ESM via esbuild IIFE).
 * We keep it classic by default; filter retained for future use.
 */

/**
 * Editor styles so the block editor roughly matches the front end.
 */
function bia_learn_editor_assets() {
	add_editor_style( 'assets/css/main.css' );
}
add_action( 'after_setup_theme', 'bia_learn_editor_assets' );
