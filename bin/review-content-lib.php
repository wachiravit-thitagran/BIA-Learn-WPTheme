<?php
/** Narrow, reviewable corrections for the November 14 content report. */

/** Return changed post fields; leave unrelated authored content untouched. */
function bia_review_content_changes( $post ) {
	$changes = array();
	$content = $post->post_content;
	if ( 'lesson' === $post->post_type ) {
		$content = preg_replace( '/<p>\s*<em>\s*หมายเหตุสำหรับผู้ดูแล:[^<]*<\/em>\s*<\/p>/u', '', $content );
	}
	if ( 'courses' === $post->post_type ) {
		$content = str_replace( '<section class="bia-course-overview"><h2>เกี่ยวกับคอร์ส</h2>', '<section class="bia-course-overview">', $content );
	}
	if ( $content !== $post->post_content ) {
		$changes['post_content'] = $content;
	}
	if ( 'page' === $post->post_type && 'เข้าสู่ระบบ/สมัครสมาขิก' === $post->post_title ) {
		$changes['post_title'] = 'เข้าสู่ระบบ/สมัครสมาชิก';
	}
	if ( 'post' === $post->post_type && 'hello-world' === ( $post->post_name ?? '' ) && 'Hello world!' === $post->post_title && 'publish' === ( $post->post_status ?? '' ) ) {
		$changes['post_status'] = 'draft';
	}
	return $changes;
}

/** Recognize exact sample values, never partially matching authored sentences. */
function bia_review_is_placeholder( $value ) {
	if ( ! is_string( $value ) ) {
		return false;
	}
	$value = preg_replace( '/[.\x{2026}\s]+$/u', '', trim( $value ) );
	return in_array( $value, array( 'สิ่งที่จะได้เรียนรู้', 'สิ่งที่รวมในคอร์ส', 'ความต้องการ/คำแนะนำ', 'กลุ่มเป้าหมายหลักของคอร์สนี้' ), true );
}
