<?php
/**
 * Public search and conservative HTTP defaults.
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

/** Add browser protections without replacing a server's stronger policy. */
function bia_learn_public_headers( $headers ) {
	$defaults = array(
		'X-Content-Type-Options'  => 'nosniff',
		'X-Frame-Options'         => 'SAMEORIGIN',
		'Referrer-Policy'         => 'strict-origin-when-cross-origin',
		'Content-Security-Policy' => "object-src 'none'; base-uri 'self'; frame-ancestors 'self'",
	);
	$existing = array_change_key_case( $headers );
	foreach ( $defaults as $name => $value ) {
		if ( ! array_key_exists( strtolower( $name ), $existing ) ) {
			$headers[ $name ] = $value;
		}
	}
	return $headers;
}
add_filter( 'wp_headers', 'bia_learn_public_headers' );

/** Keep anonymous visitors from enumerating users through Core REST routes. */
function bia_learn_private_user_endpoints( $endpoints ) {
	if ( ! is_user_logged_in() ) {
		foreach ( array_keys( $endpoints ) as $route ) {
			if ( '/wp/v2/users' === $route || 0 === strpos( $route, '/wp/v2/users/' ) ) {
				unset( $endpoints[ $route ] );
			}
		}
	}
	return $endpoints;
}
add_filter( 'rest_endpoints', 'bia_learn_private_user_endpoints' );

/** Disable public author archives, including Core's numeric-author redirect. */
function bia_learn_redirect_author_archive() {
	if ( is_author() && ! is_user_logged_in() ) {
		wp_safe_redirect( home_url( '/' ), 302 );
		exit;
	}
}
add_action( 'template_redirect', 'bia_learn_redirect_author_archive', 0 );
remove_action( 'wp_head', 'wp_generator' );

/** Limit frontend search to public learning/editorial content. */
function bia_learn_public_search( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
		return;
	}
	$term = $query->get( 's' );
	if ( ! is_string( $term ) || '' === trim( $term ) ) {
		$query->set( 'post__in', array( 0 ) );
		return;
	}
	$allowed = array( 'post', 'page', 'courses' );
	$types   = $query->get( 'post_type' );
	$types   = $types && 'any' !== $types ? array_values( array_intersect( (array) $types, $allowed ) ) : $allowed;
	if ( ! $types ) {
		$query->set( 'post__in', array( 0 ) );
		return;
	}
	$query->set( 'post_type', $types );
	$query->set( 'post_status', 'publish' );
	$system_pages = get_posts(
		array(
			'post_type'      => 'page',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'     => '_wp_page_template',
					'value'   => array( 'page-templates/template-auth.php', 'page-templates/template-dashboard.php', 'page-templates/template-statistics.php' ),
					'compare' => 'IN',
				),
			),
		)
	);
	$query->set( 'post__not_in', array_values( array_unique( array_merge( array_filter( array_map( 'intval', (array) $query->get( 'post__not_in' ) ) ), $system_pages ) ) ) );
}
add_action( 'pre_get_posts', 'bia_learn_public_search' );

/** Also exclude plugin-created workflow pages, including custom page slugs. */
function bia_learn_search_workflow_pages( $search, $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
		return $search;
	}
	global $wpdb;
	foreach ( array( '[authorizenter_', '[biapsu_', '[tutor_dashboard', '[certpsu_my_certificates' ) as $shortcode ) {
		$search .= $wpdb->prepare( " AND ({$wpdb->posts}.post_type <> 'page' OR {$wpdb->posts}.post_content NOT LIKE %s)", '%' . $wpdb->esc_like( $shortcode ) . '%' );
	}
	return $search;
}
add_filter( 'posts_search', 'bia_learn_search_workflow_pages', 10, 2 );

/** Preserve embedded certificate lists; canonicalize dedicated alias pages. */
function bia_learn_certificate_alias_target( $content, $target, $current ) {
	if ( '[certpsu_my_certificates]' !== trim( $content ) || ! $target || rtrim( $target, '/' ) === rtrim( $current, '/' ) ) {
		return '';
	}
	return $target;
}

/** Keep one learner-facing certificate destination without deleting pages. */
function bia_learn_redirect_certificate_alias() {
	if ( ! is_page() || ! bia_learn_tutor_utils_supports( 'tutor_dashboard_url' ) || ! in_array( $_SERVER['REQUEST_METHOD'] ?? 'GET', array( 'GET', 'HEAD' ), true ) ) {
		return;
	}
	$page    = get_queried_object();
	$options = get_option( 'tutor_option', array() );
	if ( ! $page || (int) $page->ID === (int) ( $options['tutor_dashboard_page_id'] ?? 0 ) ) {
		return;
	}
	$target = bia_learn_certificate_alias_target( $page->post_content, bia_learn_tutor_utils()->tutor_dashboard_url( 'my-certificates' ), get_permalink( $page->ID ) );
	if ( $target ) {
		wp_safe_redirect( $target, 302 );
		exit;
	}
}
add_action( 'template_redirect', 'bia_learn_redirect_certificate_alias', 1 );
