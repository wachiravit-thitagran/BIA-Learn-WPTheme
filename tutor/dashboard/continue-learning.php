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

// Get enrolled courses (standard order)
$enrolled_courses = tutor_utils()->get_enrolled_courses_by_user( $user_id );
$sorted_courses   = array();

if ( $enrolled_courses && $enrolled_courses->have_posts() ) {
	$posts = $enrolled_courses->posts;
	
	global $wpdb;
	$table_name = $wpdb->prefix . 'tutorlms_analytics_events';
	
	// Fetch last access time for each course if tracker table exists
	$has_tracker = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name;
	
	foreach ( $posts as $post ) {
		$course_id = $post->ID;
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
		$sorted_courses[] = $post;
	}
	
	// Sort by bia_last_access descending
	usort( $sorted_courses, function( $a, $b ) {
		return $b->bia_last_access <=> $a->bia_last_access;
	});
	
	// Replace WP_Query posts with sorted posts
	$enrolled_courses->posts = $sorted_courses;
}

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
				
				// Tracker exact last lesson URL
				$resume_url = BIA_Learn_Tutor_UX::get_last_viewed_lesson_url( $course_id, $user_id );
				if ( ! $resume_url ) {
					$resume_url = get_permalink( $course_id );
				}
				?>
				<div class="card p-5 flex flex-col justify-between h-full bg-white border border-paper-200 hover:border-primary-300 transition shadow-sm rounded-xl">
					<div>
						<h4 class="font-sans text-lg font-bold text-ink m-0 mb-3 line-clamp-2">
							<a href="<?php echo esc_url( get_permalink( $course_id ) ); ?>" class="hover:text-primary-600 no-underline text-inherit">
								<?php the_title(); ?>
							</a>
						</h4>
						
						<!-- Segmented Progress Bar -->
						<?php BIA_Learn_Tutor_UX::render_segmented_progress_bar( $course_id, $user_id ); ?>
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
