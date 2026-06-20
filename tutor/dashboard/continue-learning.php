<?php
/**
 * Template for the Continue Learning dashboard tab.
 * 
 * @package BIA_Learn
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$user_id = get_current_user_id();
$enrolled_courses = tutor_utils()->get_enrolled_courses_by_user( $user_id );

?>
<div class="tutor-dashboard-content-inner">
	<div class="tutor-dashboard-inline-links mb-6">
		<h3 class="font-sans text-xl font-bold text-ink m-0"><?php esc_html_e( 'เรียนต่อ (Continue Learning)', 'bia-learn' ); ?></h3>
	</div>

	<?php if ( $enrolled_courses && $enrolled_courses->have_posts() ) : ?>
		<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
			<?php
			while ( $enrolled_courses->have_posts() ) {
				$enrolled_courses->the_post();
				$course_id = get_the_ID();
				
				$progress = tutor_utils()->get_course_completed_percent( $course_id, 0, true );
				$action   = BIA_Learn_Tutor_UX::get_next_best_action( $course_id, $user_id );
				
				// Tracker exact last lesson URL
				$resume_url = BIA_Learn_Tutor_UX::get_last_viewed_lesson_url( $course_id, $user_id );
				if ( ! $resume_url ) {
					$resume_url = $action ? $action['url'] : get_permalink( $course_id );
				}
				?>
				<div class="card p-5 flex flex-col justify-between h-full bg-white border border-paper-200 hover:border-primary-300 transition shadow-sm rounded-xl">
					<div>
						<h4 class="font-sans text-lg font-bold text-ink m-0 mb-3 line-clamp-2">
							<a href="<?php echo esc_url( get_permalink( $course_id ) ); ?>" class="hover:text-primary-600 no-underline text-inherit">
								<?php the_title(); ?>
							</a>
						</h4>
						
						<!-- Progress Bar -->
						<div class="mb-4">
							<div class="flex justify-between items-center text-xs font-medium text-ink-light mb-1.5">
								<span><?php esc_html_e( 'ความคืบหน้า', 'bia-learn' ); ?></span>
								<span class="text-primary-600"><?php echo esc_html( $progress['completed_percent'] ); ?>%</span>
							</div>
							<div class="w-full bg-paper-100 rounded-full h-2 overflow-hidden">
								<div class="bg-primary-500 h-2 rounded-full transition-all duration-500" style="width: <?php echo esc_attr( $progress['completed_percent'] ); ?>%;"></div>
							</div>
						</div>

						<!-- Next Action Hint -->
						<?php if ( $action ) : ?>
							<p class="text-sm text-ink-light m-0 mb-4 bg-paper-50 p-3 rounded-lg border border-paper-100">
								<span class="font-semibold text-ink"><?php esc_html_e( 'ถัดไป:', 'bia-learn' ); ?></span> <?php echo esc_html( $action['title'] ); ?>
							</p>
						<?php endif; ?>
					</div>
					
					<div class="mt-auto pt-2">
						<a href="<?php echo esc_url( $resume_url ); ?>" class="bia-tutor-btn w-full justify-center">
							<?php esc_html_e( 'เริ่มเรียนต่อเลย', 'bia-learn' ); ?>
						</a>
					</div>
				</div>
				<?php
			}
			wp_reset_postdata();
			?>
		</div>
	<?php else : ?>
		<div class="tutor-dashboard-content-inner text-center py-12 bg-paper-50 rounded-xl border border-dashed border-paper-200">
			<i class="tutor-icon-mortarboard-o text-4xl text-paper-300 mb-4 block"></i>
			<p class="text-ink-light"><?php esc_html_e( 'คุณยังไม่ได้ลงทะเบียนเรียนคอร์สใดเลย', 'bia-learn' ); ?></p>
			<a href="<?php echo esc_url( home_url( '/courses' ) ); ?>" class="bia-tutor-btn mt-4 inline-flex">
				<?php esc_html_e( 'สำรวจคอร์สเรียน', 'bia-learn' ); ?>
			</a>
		</div>
	<?php endif; ?>
</div>
