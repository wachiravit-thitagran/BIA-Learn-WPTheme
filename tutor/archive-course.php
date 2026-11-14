<?php
/**
 * BIA Learn override for the Tutor LMS course archive shell.
 *
 * Keeps Tutor's filtering/pagination flow but adds a theme-specific archive
 * shell so the page feels native before deeper template overrides land.
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

$tutils = bia_learn_tutor_utils();

if ( $tutils && method_exists( $tutils, 'tutor_custom_header' ) ) {
	$tutils->tutor_custom_header();
} else {
	get_header();
}

// Themed catalog header (title, count, search, category chips) above the grid.
// Rendered before any Course_Filter query_posts() so archive conditionals and
// the queried term resolve correctly.
get_template_part( 'template-parts/courses/catalog-header' );

$get = array();
if ( isset( $_GET['course_filter'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$get = map_deep(
		array_intersect_key( wp_unslash( $_GET ), array_flip( array( 'course_filter', 'course_order', 'keyword', 'current_page', 'tutor-course-filter-category', 'tutor-course-filter-tag', 'tutor-course-filter-level', 'tutor-course-filter-price' ) ) ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filters are allowlisted and sanitized.
		'sanitize_text_field'
	); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filters.
}

if ( isset( $get['course_filter'] ) && class_exists( '\TUTOR\Course_Filter' ) ) {
	$filter = ( new \Tutor\Course_Filter( false ) )->load_listing( $get, true );
	query_posts( $filter ); // phpcs:ignore WordPress.WP.DiscouragedFunctions.query_posts_query_posts
}

$archive_args = array_merge(
	$get,
	array(
		'course_filter'     => $tutils && method_exists( $tutils, 'get_option' ) ? (bool) $tutils->get_option( 'course_archive_filter', false ) : false,
		'supported_filters' => $tutils && method_exists( $tutils, 'get_option' ) ? (array) $tutils->get_option( 'supported_course_filters', array() ) : array(),
		'loop_content_only' => false,
	)
);

echo '<div class="bia-tutor-archive-shell">';
tutor_load_template( 'archive-course-init', $archive_args );
echo '</div>';

if ( $tutils && method_exists( $tutils, 'tutor_custom_footer' ) ) {
	$tutils->tutor_custom_footer();
} else {
	get_footer();
}