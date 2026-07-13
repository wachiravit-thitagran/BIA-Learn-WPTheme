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
$enrolled_count   = 0;
$active_count     = 0;
$completed_count  = 0;

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
		
		// Calculate stats
		$progress = tutor_utils()->get_course_completed_percent( $course_id, 0, true );
		$percent = is_array( $progress ) && isset( $progress['completed_percent'] ) ? (int) $progress['completed_percent'] : 0;
		$enrolled_count++;
		if ( $percent > 0 && $percent < 100 ) {
			$active_count++;
		} elseif ( $percent === 100 ) {
			$completed_count++;
		}
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
	
	<!-- Top Stats Section -->
	<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
		<!-- Enrolled Courses -->
		<div class="bg-[#1C2333] border border-gray-700 rounded-xl p-5 flex flex-col shadow-sm text-white">
			<div class="flex justify-between items-start mb-2">
				<span class="text-gray-400 text-sm font-medium"><?php esc_html_e( 'Enrolled Courses', 'bia-learn' ); ?></span>
				<i class="ti ti-book text-blue-400 text-xl"></i>
			</div>
			<div class="text-3xl font-bold text-blue-400"><?php echo esc_html( $enrolled_count ); ?></div>
		</div>
		<!-- Active -->
		<div class="bg-[#1C2333] border border-gray-700 rounded-xl p-5 flex flex-col shadow-sm text-white">
			<div class="flex justify-between items-start mb-2">
				<span class="text-gray-400 text-sm font-medium"><?php esc_html_e( 'Active', 'bia-learn' ); ?></span>
				<i class="ti ti-player-play text-cyan-400 text-xl"></i>
			</div>
			<div class="text-3xl font-bold text-cyan-400"><?php echo esc_html( $active_count ); ?></div>
		</div>
		<!-- Completed -->
		<div class="bg-[#1C2333] border border-gray-700 rounded-xl p-5 flex flex-col shadow-sm text-white">
			<div class="flex justify-between items-start mb-2">
				<span class="text-gray-400 text-sm font-medium"><?php esc_html_e( 'Completed', 'bia-learn' ); ?></span>
				<i class="ti ti-circle-check text-green-500 text-xl"></i>
			</div>
			<div class="text-3xl font-bold text-green-500"><?php echo esc_html( $completed_count ); ?></div>
		</div>
	</div>

	<div class="flex justify-between items-end mb-6">
		<h3 class="font-sans text-xl font-bold text-ink m-0"><?php esc_html_e( 'Continue Learning', 'bia-learn' ); ?></h3>
	</div>

	<?php if ( $enrolled_courses && $enrolled_courses->have_posts() ) : ?>
		<div class="space-y-4">
			<?php
			while ( $enrolled_courses->have_posts() ) {
				$enrolled_courses->the_post();
				$course_id = get_the_ID();
				
				$progress = tutor_utils()->get_course_completed_percent( $course_id, 0, true );
				$percent = is_array( $progress ) && isset( $progress['completed_percent'] ) ? (int) $progress['completed_percent'] : 0;
				$completed_count_lessons = is_array( $progress ) && isset( $progress['completed_count'] ) ? (int) $progress['completed_count'] : 0;
				$total_count_lessons = is_array( $progress ) && isset( $progress['total_count'] ) ? (int) $progress['total_count'] : 0;
				
				// Tracker exact last lesson URL
				$resume_url = BIA_Learn_Tutor_UX::get_last_viewed_lesson_url( $course_id, $user_id );
				if ( ! $resume_url ) {
					$resume_url = tutor_utils()->get_course_first_lesson( $course_id );
					// If the course has no lessons at all, fallback to course page
					if ( ! $resume_url ) {
						$resume_url = get_permalink( $course_id );
					}
				}
				
				$thumbnail = get_tutor_course_thumbnail_src( 'medium', $course_id );
				$course_categories = get_tutor_course_categories( $course_id );
				$category_name = ! empty( $course_categories ) ? $course_categories[0]->name : '';
				?>
				<div class="flex flex-col md:flex-row gap-5 p-4 bg-[#232A3B] border border-gray-700 rounded-2xl hover:border-gray-500 transition shadow-sm items-center">
					<!-- Thumbnail -->
					<div class="w-full md:w-64 h-36 flex-shrink-0 rounded-xl overflow-hidden bg-gray-800">
						<?php if ( $thumbnail ) : ?>
							<img src="<?php echo esc_url( $thumbnail ); ?>" alt="<?php the_title_attribute(); ?>" class="w-full h-full object-cover" />
						<?php else : ?>
							<div class="w-full h-full flex items-center justify-center text-gray-500">
								<i class="ti ti-photo text-3xl"></i>
							</div>
						<?php endif; ?>
					</div>
					
					<!-- Info -->
					<div class="flex-1 min-w-0 flex flex-col justify-center">
						<?php if ( $category_name ) : ?>
							<div class="text-xs text-gray-400 mb-1"><?php echo esc_html( $category_name ); ?></div>
						<?php endif; ?>
						
						<h4 class="font-sans text-lg font-bold text-white m-0 mb-4 line-clamp-2">
							<a href="<?php echo esc_url( get_permalink( $course_id ) ); ?>" class="hover:text-blue-400 no-underline text-inherit transition-colors">
								<?php the_title(); ?>
							</a>
						</h4>
						
						<!-- Progress -->
						<div class="mb-1 text-xs text-gray-400 flex gap-2 items-center">
							<span><?php printf( esc_html__( '%1$d of %2$d lessons', 'bia-learn' ), $completed_count_lessons, $total_count_lessons ); ?></span>
							<span>&bull;</span>
							<span><?php echo esc_html( $percent ); ?>% <?php esc_html_e( 'Complete', 'bia-learn' ); ?></span>
						</div>
						<div class="w-full max-w-md bg-gray-700 rounded-full h-2">
							<div class="h-2 rounded-full bg-green-500 transition-all duration-500" style="width: <?php echo esc_attr( $percent ); ?>%;"></div>
						</div>
					</div>
					
					<!-- Action -->
					<div class="w-full md:w-auto mt-4 md:mt-0 flex-shrink-0 md:pl-4">
						<a href="<?php echo esc_url( $resume_url ); ?>" class="w-full md:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors">
							<i class="ti ti-player-play-filled text-sm"></i>
							<?php esc_html_e( 'Resume', 'bia-learn' ); ?>
						</a>
					</div>
				</div>
				<?php
			}
			wp_reset_postdata();
			?>
		</div>
	<?php else : ?>
		<div class="text-center py-12 bg-paper-50 rounded-xl border border-dashed border-paper-200">
			<i class="ti ti-book text-4xl text-paper-300 mb-4 block"></i>
			<p class="text-ink-light"><?php esc_html_e( 'คุณยังไม่ได้ลงทะเบียนเรียนคอร์สใดเลย', 'bia-learn' ); ?></p>
			<a href="<?php echo esc_url( home_url( '/courses' ) ); ?>" class="bia-tutor-btn mt-4 inline-flex">
				<?php esc_html_e( 'สำรวจคอร์สเรียน', 'bia-learn' ); ?>
			</a>
		</div>
	<?php endif; ?>
</div>
