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
	// Google Fonts — Noto Serif Thai (display) + Sarabun (body).
	wp_enqueue_style(
		'bia-learn-fonts',
		'https://fonts.googleapis.com/css2?family=Sarabun:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&family=Noto+Serif+Thai:wght@400;500;600;700;900&display=swap',
		array(),
		null
	);

	// Compiled Tailwind stylesheet.
	wp_enqueue_style(
		'bia-learn-style',
		BIA_LEARN_URI . '/assets/css/main.css',
		array( 'bia-learn-fonts' ),
		bia_learn_asset_version( 'assets/css/main.css' )
	);

	// The theme's style.css header (kept for tooling / child themes).
	wp_enqueue_style(
		'bia-learn-theme',
		get_stylesheet_uri(),
		array( 'bia-learn-style' ),
		BIA_LEARN_VERSION
	);

	// Bundled Alpine.js + interactions.
	wp_enqueue_script(
		'bia-learn-main',
		BIA_LEARN_URI . '/assets/js/main.js',
		array(),
		bia_learn_asset_version( 'assets/js/main.js' ),
		true
	);

	// Expose a few values to JS.
	wp_localize_script(
		'bia-learn-main',
		'biaLearn',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'restUrl' => esc_url_raw( rest_url() ),
			'nonce'   => wp_create_nonce( 'wp_rest' ),
		)
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'bia_learn_enqueue_assets' );

/**
 * Preconnect to the font CDN for faster first paint.
 *
 * @param array  $urls           URLs to print.
 * @param string $relation_type  Relation type.
 * @return array
 */
function bia_learn_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type ) {
		$urls[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => 'anonymous',
		);
		$urls[] = 'https://fonts.googleapis.com';
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'bia_learn_resource_hints', 10, 2 );

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
