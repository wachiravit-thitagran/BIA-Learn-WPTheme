<?php
/**
 * Tutor LMS dashboard enrolled-course card: BIA passed-only progress.
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

$bia_course_id = get_the_ID();
$bia_user_id   = get_current_user_id();
$bia_progress  = BIA_Learn_Tutor_UX::get_course_passed_progress( $bia_course_id, $bia_user_id );

if ( 0 === $bia_progress['total'] ) {
	return;
}
?>
<div class="tutor-progress-card-progress">
	<?php BIA_Learn_Tutor_UX::render_segmented_progress_bar( $bia_course_id, $bia_user_id ); ?>
</div>
