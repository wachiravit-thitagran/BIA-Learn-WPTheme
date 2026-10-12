<?php
/**
 * Enrolled course progress — BIA passed-only presentation.
 * Tutor LMS continues to own course completion and enrollment data.
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

$course_id = get_the_ID();
$user_id   = get_current_user_id();

if ( class_exists( 'BIA_Learn_Tutor_UX' ) ) {
	BIA_Learn_Tutor_UX::render_segmented_progress_bar( $course_id, $user_id );
}
