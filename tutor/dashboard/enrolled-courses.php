<?php
/**
 * Template for displaying Enrolled Courses with dynamic empty state.
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
	<div class="tutor-mb-32">
		<h3 class="font-sans text-xl font-bold text-ink m-0">
			<?php esc_html_e( 'คอร์สเรียนของฉัน', 'bia-learn' ); ?>
		</h3>
	</div>

	<?php if ( $enrolled_courses && $enrolled_courses->have_posts() ) : ?>
		<div class="tutor-dashboard-course-cards grid grid-cols-1 md:grid-cols-2 gap-6">
			<?php
			while ( $enrolled_courses->have_posts() ) {
				$enrolled_courses->the_post();
				get_template_part( 'template-parts/cards/course-card' );
			}
			wp_reset_postdata();
			?>
		</div>
	<?php else : ?>
		<div class="tutor-dashboard-content-inner text-center py-16 bg-white rounded-2xl border border-paper-200 shadow-sm flex flex-col items-center justify-center">
			<div class="icon-chip icon-chip-crimson mb-6 h-20 w-20">
				<?php echo bia_learn_icon( 'book', 'h-9 w-9' ); // phpcs:ignore ?>
			</div>
			<h4 class="font-sans text-2xl font-bold text-ink m-0 mb-3"><?php esc_html_e( 'พร้อมจะเริ่มเรียนรู้หรือยัง?', 'bia-learn' ); ?></h4>
			<p class="text-ink-light max-w-md mx-auto mb-8 leading-relaxed">
				<?php esc_html_e( 'ดูเหมือนว่าคุณจะยังไม่ได้ลงทะเบียนเรียนคอร์สใดเลยบนแพลตฟอร์มของเรา ลองสำรวจคอร์สเรียนฟรีและน่าสนใจมากมายที่เราเตรียมไว้ให้คุณสิ!', 'bia-learn' ); ?>
			</p>
			<a href="<?php echo esc_url( home_url( '/courses' ) ); ?>" class="bia-tutor-btn px-8 py-3 text-lg shadow-md hover:shadow-lg transition-transform hover:-translate-y-0.5">
				<?php esc_html_e( 'เริ่มค้นหาคอร์สที่ใช่สำหรับคุณ', 'bia-learn' ); ?>
			</a>
		</div>
	<?php endif; ?>
</div>
