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

// Cap the grid — enrolled courses are unbounded and each card costs queries.
$bia_max_cards = 6;

// Get enrolled courses (standard order)
$enrolled_courses = tutor_utils()->get_enrolled_courses_by_user( $user_id );
$sorted_courses   = array();

if ( $enrolled_courses && ! empty( $enrolled_courses->posts ) ) {
	$posts = $enrolled_courses->posts;

	global $wpdb;
	$table_name = $wpdb->prefix . 'tutorlms_analytics_events';

	// Fetch last access time for each course if tracker table exists.
	$has_tracker = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) ) ) === $table_name;
	// Share the result so get_last_viewed_lesson_url() doesn't re-check per card.
	BIA_Learn_Tutor_UX::$analytics_table_exists = $has_tracker;

	foreach ( $posts as $post ) {
		$course_id = $post->ID;

		// Completed courses belong in history/certificates, not the resume queue.
		$course_progress = bia_learn_course_progress( $course_id, $user_id );
		if ( null !== $course_progress && $course_progress >= 100 ) {
			continue;
		}

		$last_access = 0;
		if ( $has_tracker ) {
			$last_access = $wpdb->get_var( $wpdb->prepare( "
				SELECT MAX(created_at)
				FROM {$table_name}
				WHERE course_id = %d AND user_id = %d
			", $course_id, $user_id ) );
			$last_access = $last_access ? strtotime( $last_access ) : 0;
		}

		// If no access found, fallback to post modification date or 0
		if ( ! $last_access ) {
			$last_access = strtotime( $post->post_modified );
		}

		$post->bia_last_access = $last_access;
		$sorted_courses[]      = $post;
	}

	// Sort by bia_last_access descending
	usort( $sorted_courses, function( $a, $b ) {
		return $b->bia_last_access <=> $a->bia_last_access;
	});
}

$bia_total_courses = count( $sorted_courses );
$sorted_courses    = array_slice( $sorted_courses, 0, $bia_max_cards );

?>
<div class="tutor-dashboard-content-inner">
	<div class="tutor-dashboard-inline-links mb-6">
		<h3 class="font-sans text-xl font-bold text-ink m-0"><?php esc_html_e( 'เรียนต่อจากที่ค้างไว้', 'bia-learn' ); ?></h3>
		<p class="mt-2 text-sm text-ink-light"><?php esc_html_e( 'คอร์สที่คุณเปิดล่าสุดจะอยู่ด้านบน เพื่อกลับเข้าเรียนต่อได้ทันที', 'bia-learn' ); ?></p>
	</div>

	<?php if ( ! empty( $sorted_courses ) ) : ?>
		<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
			<?php
			// Plain foreach on post objects — the_post()/wp_reset_postdata()
			// on a secondary query corrupts the main query's global $post.
			foreach ( $sorted_courses as $course_post ) {
				$course_id = $course_post->ID;

				// Tracker exact last lesson URL
				$resume_url = BIA_Learn_Tutor_UX::get_last_viewed_lesson_url( $course_id, $user_id );
				if ( ! $resume_url ) {
					$resume_url = tutor_utils()->get_course_first_lesson( $course_id );
					// If the course has no lessons at all, fallback to course page
					if ( ! $resume_url ) {
						$resume_url = get_permalink( $course_id );
					}
				}
				?>
				<article class="card card-hover flex h-full flex-col bg-white">
					<a href="<?php echo esc_url( get_permalink( $course_id ) ); ?>" class="block aspect-[16/10] overflow-hidden bg-paper-100">
						<?php if ( has_post_thumbnail( $course_id ) ) : ?>
							<?php echo get_the_post_thumbnail( $course_id, 'bia-card', array( 'class' => 'h-full w-full object-cover' ) ); ?>
						<?php else : ?>
							<span class="flex h-full w-full items-center justify-center bg-crimson-wash text-paper-50">
								<?php echo bia_learn_icon( 'book', 'h-10 w-10 opacity-60' ); // phpcs:ignore ?>
							</span>
						<?php endif; ?>
					</a>

					<div class="flex flex-1 flex-col p-5">
						<h4 class="line-clamp-2 font-sans text-lg font-bold text-ink m-0 mb-4">
							<a href="<?php echo esc_url( get_permalink( $course_id ) ); ?>" class="hover:text-crimson no-underline text-inherit">
								<?php echo esc_html( get_the_title( $course_id ) ); ?>
							</a>
						</h4>

						<?php BIA_Learn_Tutor_UX::render_segmented_progress_bar( $course_id, $user_id ); ?>

						<div class="mt-auto pt-4">
							<a href="<?php echo esc_url( $resume_url ); ?>" class="bia-tutor-btn w-full justify-center">
								<?php echo bia_learn_icon( 'play', 'h-4 w-4' ); // phpcs:ignore ?>
								<?php esc_html_e( 'เรียนต่อ', 'bia-learn' ); ?>
							</a>
						</div>
					</div>
				</article>
				<?php
			}
			?>
		</div>

		<?php if ( $bia_total_courses > $bia_max_cards ) : ?>
			<p class="mt-6 text-sm text-ink-light">
				<?php printf( esc_html__( 'แสดง %1$d จาก %2$d คอร์สที่เรียนล่าสุด — ดูทั้งหมดได้ที่เมนู "คอร์สที่ลงทะเบียน"', 'bia-learn' ), (int) $bia_max_cards, (int) $bia_total_courses ); ?>
			</p>
		<?php endif; ?>
	<?php else : ?>
		<div class="tutor-dashboard-content-inner text-center py-12 bg-paper-50 rounded-xl border border-dashed border-paper-200">
			<span class="icon-chip icon-chip-crimson mx-auto mb-4"><?php echo bia_learn_icon( 'book', 'h-5 w-5' ); // phpcs:ignore ?></span>
			<p class="text-ink-light"><?php esc_html_e( 'คุณยังไม่ได้ลงทะเบียนเรียนคอร์สใดเลย', 'bia-learn' ); ?></p>
			<a href="<?php echo esc_url( bia_learn_courses_url() ); ?>" class="bia-tutor-btn mt-4 inline-flex">
				<?php esc_html_e( 'สำรวจคอร์สเรียน', 'bia-learn' ); ?>
			</a>
		</div>
	<?php endif; ?>
</div>
