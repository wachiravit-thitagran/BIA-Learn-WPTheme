<?php
/**
 * Tutor LMS Student UX Enhancements (Phase 1)
 *
 * Provides Core Learning UX features like Smart Continue, Section Progress,
 * Next Best Action, and the Continue Learning Dashboard.
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

class BIA_Learn_Tutor_UX {

	/**
	 * Initialize all hooks.
	 */
	public static function init() {
		// 1. Smart Continue Button Redirect
		add_filter( 'tutor_course_continue_url', array( __CLASS__, 'smart_continue_url' ), 10, 2 );

		// 2. Next Best Action UI Injection
		add_action( 'tutor_course/single/before/content', array( __CLASS__, 'inject_next_best_action' ), 10, 1 );

		// 3. Continue Learning Dashboard Tab
		add_filter( 'tutor_dashboard/nav_items', array( __CLASS__, 'register_dashboard_tab' ), 10, 1 );

		// 4. Estimated Time Remaining Badge
		add_action( 'tutor_course/single/before/inner-wrap', array( __CLASS__, 'render_estimated_time' ), 10, 1 );
	}

	/**
	 * Get estimated time remaining (in minutes) for a course based on native Tutor LMS duration.
	 */
	public static function get_estimated_time_remaining( $course_id, $user_id ) {
		if ( ! function_exists( 'tutor_utils' ) ) {
			return 0;
		}

		// Get total course duration set by instructor (hours, minutes, seconds)
		$duration = get_post_meta( $course_id, '_tutor_course_duration', true );
		if ( empty( $duration ) || ! is_array( $duration ) ) {
			return 0; // If instructor didn't set a duration, we can't reliably estimate.
		}

		$hours   = isset( $duration['hours'] ) ? (int) $duration['hours'] : 0;
		$minutes = isset( $duration['minutes'] ) ? (int) $duration['minutes'] : 0;
		$total_minutes = ( $hours * 60 ) + $minutes;

		if ( $total_minutes === 0 ) {
			return 0;
		}

		// Get current completion percentage
		$progress = tutor_utils()->get_course_completed_percent( $course_id, $user_id, true );
		$percent_completed = isset( $progress['completed_percent'] ) ? (float) $progress['completed_percent'] : 0;

		if ( $percent_completed >= 100 ) {
			return 0;
		}

		// Calculate remaining minutes based on the percentage left
		$percent_left = 100 - $percent_completed;
		$remaining_minutes = round( $total_minutes * ( $percent_left / 100 ) );

		return (int) $remaining_minutes;
	}

	/**
	 * Render the estimated time badge on the course page.
	 */
	public static function render_estimated_time( $course_id = 0 ) {
		if ( ! $course_id ) {
			$course_id = get_the_ID();
		}
		$user_id = get_current_user_id();
		if ( ! $user_id ) return;
		
		if ( ! tutor_utils()->is_enrolled( $course_id, $user_id ) ) return;

		$minutes = self::get_estimated_time_remaining( $course_id, $user_id );
		if ( $minutes <= 0 ) return;

		$hours = floor( $minutes / 60 );
		$mins  = $minutes % 60;
		
		$time_str = '';
		if ( $hours > 0 ) {
			$time_str .= sprintf( __( '%d ชม. ', 'bia-learn' ), $hours );
		}
		if ( $mins > 0 || $hours === 0 ) {
			$time_str .= sprintf( __( '%d นาที', 'bia-learn' ), $mins );
		}
		?>
		<div class="mt-4 flex items-center gap-2 text-sm text-ink-light bg-paper-50 px-3 py-2 rounded-lg border border-paper-100 inline-flex w-auto">
			<?php echo bia_learn_icon( 'clock', 'h-5 w-5 text-primary-500' ); ?>
			<span><?php printf( esc_html__( 'ใช้เวลาเรียนต่อประมาณ %s', 'bia-learn' ), $time_str ); ?></span>
		</div>
		<?php
	}

	/**
	 * Get the exact last lesson URL for the user based on analytics tracker.
	 * Returns null if no record is found or tracker table doesn't exist.
	 */
	public static function get_last_viewed_lesson_url( $course_id, $user_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'tutorlms_analytics_events';
		
		// Check if table exists
		if ( $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") !== $table_name ) {
			return null;
		}

		$lesson_id = (int) $wpdb->get_var( $wpdb->prepare( "
			SELECT lesson_id 
			FROM {$table_name} 
			WHERE course_id = %d 
			  AND user_id = %d 
			  AND lesson_id > 0
			  AND (
			      event_type = 'video_watch_heartbeat' 
			      OR (event_type = 'page_exit' AND CAST(event_value AS UNSIGNED) >= 15)
			  )
			ORDER BY created_at DESC 
			LIMIT 1
		", $course_id, $user_id ) );

		if ( $lesson_id > 0 ) {
			return get_permalink( $lesson_id );
		}

		return null;
	}

	/**
	 * Determine the "Next Best Action" (e.g., specific uncompleted lesson or pending quiz).
	 */
	public static function get_next_best_action( $course_id, $user_id ) {
		if ( ! function_exists( 'tutor_utils' ) ) {
			return null;
		}

		// Check if course is completed
		$is_completed = tutor_utils()->is_completed_course( $course_id, $user_id );
		if ( $is_completed ) {
			return array(
				'type'  => 'certificate',
				'title' => __( 'Course Completed!', 'bia-learn' ),
				'desc'  => __( 'Download your certificate or leave a review.', 'bia-learn' ),
				'url'   => get_permalink( $course_id ),
			);
		}

		$topics = tutor_utils()->get_topics( $course_id );
		if ( ! $topics || ! $topics->have_posts() ) {
			return null;
		}

		while ( $topics->have_posts() ) {
			$topics->the_post();
			$topic_id = get_the_ID();
			$contents = tutor_utils()->get_course_contents_by_topic( $topic_id, -1 );
			
			foreach ( $contents as $content ) {
				$is_completed_item = tutor_utils()->is_completed_lesson( $content->ID, $user_id );
				
				if ( ! $is_completed_item ) {
					// Found the first incomplete item
					$type = $content->post_type === 'tutor_quiz' ? 'quiz' : 'lesson';
					
					return array(
						'type'  => $type,
						'title' => $content->post_title,
						'desc'  => $type === 'quiz' ? __( 'You have a pending quiz.', 'bia-learn' ) : __( 'Continue where you left off.', 'bia-learn' ),
						'url'   => get_permalink( $content->ID ),
					);
				}
			}
		}

		wp_reset_postdata();

		return null;
	}

	/**
	 * Get progress percentage for a specific topic/section.
	 */
	public static function get_section_progress( $topic_id, $user_id ) {
		if ( ! function_exists( 'tutor_utils' ) ) {
			return array( 'completed' => 0, 'total' => 0, 'percent' => 0 );
		}

		$contents = tutor_utils()->get_course_contents_by_topic( $topic_id, -1 );
		$total = count( $contents );
		$completed = 0;

		foreach ( $contents as $content ) {
			if ( tutor_utils()->is_completed_lesson( $content->ID, $user_id ) ) {
				$completed++;
			}
		}

		$percent = $total > 0 ? round( ( $completed / $total ) * 100 ) : 0;

		return array(
			'completed' => $completed,
			'total'     => $total,
			'percent'   => $percent,
		);
	}

	/**
	 * Intercept the Continue button URL and redirect to the exact last viewed lesson.
	 */
	public static function smart_continue_url( $url, $course_id ) {
		$user_id = get_current_user_id();
		if ( ! $user_id ) return $url;

		// Try to get exact last viewed lesson from tracker
		$exact_url = self::get_last_viewed_lesson_url( (int) $course_id, $user_id );
		if ( $exact_url ) {
			return $exact_url;
		}

		// Fallback to Next Best Action if no tracker data
		$action = self::get_next_best_action( (int) $course_id, $user_id );
		if ( $action && ! empty( $action['url'] ) ) {
			return $action['url'];
		}

		return $url;
	}

	/**
	 * Inject the "Next Best Action" card at the top of the course.
	 */
	public static function inject_next_best_action( $course_id = 0 ) {
		if ( ! $course_id ) {
			$course_id = get_the_ID();
		}
		$user_id = get_current_user_id();
		if ( ! $user_id ) return;

		// Only show if enrolled
		if ( ! tutor_utils()->is_enrolled( $course_id, $user_id ) ) return;

		$action = self::get_next_best_action( (int) $course_id, $user_id );
		if ( ! $action ) return;

		// Use theme classes (card, etc.)
		?>
		<div class="card p-5 mb-6 border-l-4 border-l-blue-500 bg-blue-50 flex flex-col md:flex-row justify-between items-center gap-4">
			<div>
				<h4 class="font-sans text-base font-bold text-blue-900 m-0 flex items-center gap-2">
					<?php echo bia_learn_icon( 'target', 'h-5 w-5 text-blue-900' ); ?>
					<?php esc_html_e( 'Next Best Action', 'bia-learn' ); ?>
				</h4>
				<p class="text-sm text-blue-800 m-0 mt-1">
					<span class="font-semibold"><?php echo esc_html( $action['desc'] ); ?></span>: <?php echo esc_html( $action['title'] ); ?>
				</p>
			</div>
			<div>
				<a href="<?php echo esc_url( $action['url'] ); ?>" class="bia-tutor-btn">
					<?php echo $action['type'] === 'quiz' ? esc_html__( 'Take Quiz', 'bia-learn' ) : esc_html__( 'Resume Learning', 'bia-learn' ); ?>
				</a>
			</div>
		</div>
		<?php
	}

	/**
	 * Register the Continue Learning tab in Student Dashboard.
	 */
	public static function register_dashboard_tab( $tabs ) {
		$new_tabs = array();
		
		foreach ( $tabs as $key => $tab ) {
			$new_tabs[$key] = $tab;
			if ( $key === 'dashboard' || $key === 'index' ) {
				$new_tabs['continue-learning'] = array(
					'title' => __( 'เรียนต่อ (Continue Learning)', 'bia-learn' ),
					'icon'  => 'ti ti-player-play',
				);
			}
		}
		return $new_tabs;
	}
}

// Initialize
if ( function_exists( 'tutor_utils' ) ) {
	BIA_Learn_Tutor_UX::init();
}
