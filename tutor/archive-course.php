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

$get = array();
if ( isset( $_GET['course_filter'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$get = array_map(
		'sanitize_text_field',
		wp_unslash( (array) $_GET ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	);
}

if ( isset( $get['course_filter'] ) && class_exists( '\\Tutor\\Course_Filter' ) ) {
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