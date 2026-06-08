<?php
/**
 * Theme setup: supports, navigation menus, image sizes, content width.
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register theme feature support and navigation menus.
 */
function bia_learn_setup() {
	// Make the theme available for translation. Translations live in /languages.
	load_theme_textdomain( 'bia-learn', BIA_LEARN_DIR . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );

	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
			'navigation-widgets',
		)
	);

	add_theme_support(
		'custom-logo',
		array(
			'height'      => 96,
			'width'       => 280,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	// Let WordPress manage the document <title>.
	add_theme_support(
		'post-formats',
		array( 'aside', 'gallery', 'link', 'image', 'quote', 'video', 'audio' )
	);

	register_nav_menus(
		array(
			'primary'      => __( 'เมนูหลัก (Primary)', 'bia-learn' ),
			'footer'       => __( 'เมนูส่วนท้าย (Footer)', 'bia-learn' ),
			'footer_legal' => __( 'เมนูนโยบาย/กฎหมาย (Footer legal)', 'bia-learn' ),
			'social'       => __( 'โซเชียลมีเดีย (Social)', 'bia-learn' ),
		)
	);

	// Custom image sizes used by course / post cards.
	add_image_size( 'bia-card', 720, 460, true );
	add_image_size( 'bia-card-wide', 1080, 600, true );
	add_image_size( 'bia-hero', 1600, 900, true );
}
add_action( 'after_setup_theme', 'bia_learn_setup' );

/**
 * Set the content width used by oEmbeds and wide images.
 */
function bia_learn_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'bia_learn_content_width', 768 );
}
add_action( 'after_setup_theme', 'bia_learn_content_width', 0 );

/**
 * Friendlier label for our custom image sizes in the media UI.
 *
 * @param array $sizes Existing selectable sizes.
 * @return array
 */
function bia_learn_image_size_names( $sizes ) {
	return array_merge(
		$sizes,
		array(
			'bia-card' => __( 'การ์ด BIA', 'bia-learn' ),
			'bia-hero' => __( 'ภาพปก BIA', 'bia-learn' ),
		)
	);
}
add_filter( 'image_size_names_choose', 'bia_learn_image_size_names' );

/**
 * Add the text-domain to the body class list so Tailwind component scoping is
 * predictable, plus a flag when Tutor LMS is active.
 *
 * @param array $classes Body classes.
 * @return array
 */
function bia_learn_body_classes( $classes ) {
	$classes[] = 'bia-learn';
	if ( bia_learn_has_tutor_lms() ) {
		$classes[] = 'has-tutor-lms';
	}
	return $classes;
}
add_filter( 'body_class', 'bia_learn_body_classes' );

/**
 * Add the `nav-link` class to primary-menu anchors so the animated underline
 * styling applies without needing a custom walker.
 *
 * @param array    $atts  Anchor attributes.
 * @param WP_Post  $item  Menu item.
 * @param stdClass $args  wp_nav_menu args.
 * @return array
 */
function bia_learn_nav_link_atts( $atts, $item, $args ) {
	if ( isset( $args->theme_location ) && 'primary' === $args->theme_location ) {
		$current             = in_array( 'current-menu-item', (array) $item->classes, true ) ? ' current-menu-item' : '';
		$atts['class']       = 'nav-link' . $current;
		$atts['aria-current'] = $current ? 'page' : false;
	}
	return $atts;
}
add_filter( 'nav_menu_link_attributes', 'bia_learn_nav_link_atts', 10, 3 );

/**
 * Default menu shown when no `primary` menu has been assigned yet — links to
 * the core pages so the header is never empty on a fresh install.
 */
function bia_learn_default_menu() {
	// "เกี่ยวกับเรา" and "ติดต่อ" intentionally live in the footer, not here.
	$items = array(
		home_url( '/' )                     => __( 'หน้าแรก', 'bia-learn' ),
		bia_learn_courses_url()             => __( 'คอร์สเรียน', 'bia-learn' ),
		bia_learn_page_url( 'instructors' ) => __( 'ผู้สอน', 'bia-learn' ),
		bia_learn_news_url()                => __( 'ข่าวสาร', 'bia-learn' ),
	);
	echo '<ul class="flex items-center gap-7">';
	foreach ( $items as $url => $label ) {
		echo '<li><a class="nav-link" href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></li>';
	}
	echo '</ul>';
}

/**
 * Resolve a supporting page's URL by slug, falling back to a pretty path.
 *
 * Uses the page's real permalink when it exists, so links keep working under
 * any permalink structure — including the "/index.php/…" prefix WordPress
 * adds when mod_rewrite is unavailable. Avoids hard-coded "/about/" links
 * that 404 when the actual page lives at "/index.php/about/".
 *
 * @param string $slug     Page slug (path).
 * @param string $fallback Optional fallback path if the page doesn't exist.
 * @return string
 */
function bia_learn_page_url( $slug, $fallback = '' ) {
	$page = get_page_by_path( $slug );
	if ( $page ) {
		return get_permalink( $page );
	}
	return home_url( '/' . ltrim( $fallback ? $fallback : $slug, '/' ) . '/' );
}

/**
 * Resolve the courses archive URL (Tutor LMS course archive, else /courses/).
 *
 * @return string
 */
function bia_learn_courses_url() {
	if ( bia_learn_has_tutor_lms() ) {
		$archive = get_post_type_archive_link( 'courses' );
		if ( $archive ) {
			return $archive;
		}
	}
	return home_url( '/courses/' );
}

/**
 * Resolve the news / blog index URL: the assigned Posts page when set,
 * otherwise a sensible /news/ fallback. Avoids hard-coding the slug.
 *
 * @return string
 */
function bia_learn_news_url() {
	$posts_page = (int) get_option( 'page_for_posts' );
	if ( $posts_page ) {
		return (string) get_permalink( $posts_page );
	}
	return home_url( '/news/' );
}

/**
 * Trim the default excerpt and use a softer ellipsis.
 */
add_filter(
	'excerpt_length',
	function () {
		return 28;
	}
);
add_filter(
	'excerpt_more',
	function () {
		return '&hellip;';
	}
);
