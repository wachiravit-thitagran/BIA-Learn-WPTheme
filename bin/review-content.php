<?php
/**
 * Preview content corrections: wp eval-file bin/review-content.php
 * Apply only in local/staging: wp eval-file bin/review-content.php apply
 *
 * Production writes are deliberately disabled. Save the JSON preview and a
 * database backup before applying to a non-production copy.
 */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit;
}
require_once __DIR__ . '/review-content-lib.php';
$apply = in_array( 'apply', $args ?? array(), true );
if ( $apply && 'production' === wp_get_environment_type() ) {
	WP_CLI::error( 'Production changes are disabled; use a local or staging copy.' );
}
$page = 1;
do {
	$posts = get_posts( array( 'post_type' => array( 'post', 'page', 'courses', 'lesson' ), 'post_status' => array( 'publish', 'draft' ), 'posts_per_page' => 100, 'paged' => $page, 'orderby' => 'ID', 'order' => 'ASC' ) );
	foreach ( $posts as $post ) {
		$fields = bia_review_content_changes( $post );
		$meta   = array();
		if ( 'courses' === $post->post_type ) {
			foreach ( array( '_tutor_course_benefits', '_tutor_course_requirements', '_tutor_course_target_audience', '_tutor_course_material_includes' ) as $key ) {
				if ( bia_review_is_placeholder( get_post_meta( $post->ID, $key, true ) ) ) {
					$meta[ $key ] = '';
				}
			}
		}
		if ( ! $fields && ! $meta ) {
			continue;
		}
		$before = array();
		foreach ( $fields as $key => $value ) {
			$before[ $key ] = $post->$key;
		}
		foreach ( $meta as $key => $value ) {
			$before[ $key ] = get_post_meta( $post->ID, $key, true );
		}
		WP_CLI::line( wp_json_encode( array( 'post_id' => $post->ID, 'before' => $before, 'after' => $fields + $meta, 'mode' => $apply ? 'apply' : 'preview' ), JSON_UNESCAPED_UNICODE ) );
		if ( $apply ) {
			$result = $fields ? wp_update_post( wp_slash( array_merge( array( 'ID' => $post->ID ), $fields ) ), true ) : $post->ID;
			if ( is_wp_error( $result ) ) {
				WP_CLI::error( $result->get_error_message() );
			}
			foreach ( $meta as $key => $value ) {
				update_post_meta( $post->ID, $key, $value );
				if ( $value !== get_post_meta( $post->ID, $key, true ) ) {
					WP_CLI::error( 'Content metadata could not be saved.' );
				}
			}
		}
	}
	++$page;
} while ( count( $posts ) === 100 );
