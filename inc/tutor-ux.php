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

		// 3. Continue Learning Dashboard Tab
		add_filter( 'tutor_dashboard/nav_items', array( __CLASS__, 'register_dashboard_tab' ), 10, 1 );

		// 4. Change Start Learning button text
		add_filter( 'gettext', array( __CLASS__, 'translate_start_learning' ), 20, 3 );

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
	 * Get progress percentage for a specific topic/section.
	 */
	public static function get_section_progress( $topic_id, $user_id ) {
		if ( ! function_exists( 'tutor_utils' ) ) {
			return array( 'completed' => 0, 'total' => 0, 'percent' => 0 );
		}

		$contents = tutor_utils()->get_course_contents_by_topic( $topic_id, -1 );
		$lesson_posts = is_object( $contents ) && isset( $contents->posts ) ? $contents->posts : ( is_array( $contents ) ? $contents : array() );
		$total = count( $lesson_posts );
		$completed = 0;

		foreach ( $lesson_posts as $content ) {
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

	private static function get_quiz_assignment_status( $content_id, $user_id, $type ) {
		global $wpdb;
		
		if ( 'tutor_quiz' === $type ) {
			if ( ! tutor_utils()->has_attempted_quiz( $user_id, $content_id ) ) {
				return 'unattempted';
			}
			
			$passed = $wpdb->get_var( $wpdb->prepare( "
				SELECT attempt_id
				FROM {$wpdb->prefix}tutor_quiz_attempts
				WHERE user_id = %d AND quiz_id = %d AND result = 'pass'
				LIMIT 1
			", $user_id, $content_id ) );
			
			return $passed ? 'completed' : 'quiz_pending';
		}
		
		if ( 'tutor_assignments' === $type ) {
			$submissions = tutor_utils()->is_assignment_submitted( $content_id, $user_id );
			if ( empty( $submissions ) ) {
				return 'unattempted';
			}
			
			$pass_mark = tutor_utils()->get_assignment_option( $content_id, 'pass_mark' );
			foreach ( $submissions as $submission ) {
				$mark = get_comment_meta( $submission->comment_ID, 'assignment_mark', true );
				if ( is_numeric( $mark ) && (int) $mark >= $pass_mark ) {
					return 'completed';
				}
			}
			return 'quiz_pending'; // Attempted but not passed/evaluated
		}
		
		return 'unattempted';
	}

	/**
	 * Get status of every lesson and quiz in the course for segmented progress bar.
	 *
	 * Returns an array of items with their status: 'completed' (green), 'quiz_pending' (yellow), 'unattempted' (gray).
	 */
	public static function get_course_curriculum_status( $course_id, $user_id ) {
		if ( ! function_exists( 'tutor_utils' ) ) {
			return array();
		}

		$status_list = array();
		$topics = tutor_utils()->get_topics( $course_id );
		
		if ( ! $topics || ! $topics->have_posts() ) {
			return $status_list;
		}

		while ( $topics->have_posts() ) {
			$topics->the_post();
			$topic_id = get_the_ID();
			$contents = tutor_utils()->get_course_contents_by_topic( $topic_id, -1 );
			$items    = is_object( $contents ) && isset( $contents->posts ) ? $contents->posts : ( is_array( $contents ) ? $contents : array() );
			
			foreach ( $items as $content ) {
				$type = $content->post_type; // 'tutor_quiz', 'tutor_assignments', 'lesson'
				
				if ( 'tutor_quiz' === $type || 'tutor_assignments' === $type ) {
					$status_list[] = self::get_quiz_assignment_status( $content->ID, $user_id, $type );
				} else {
					$status_list[] = tutor_utils()->is_completed_lesson( $content->ID, $user_id ) ? 'completed' : 'unattempted';
				}
			}
		}

		wp_reset_postdata();

		return $status_list;
	}

	/**
	 * Render the segmented progress bar HTML.
	 */
	public static function render_segmented_progress_bar( $course_id, $user_id = 0 ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}
		
		$segments = self::get_course_curriculum_status( $course_id, $user_id );
		$total    = count( $segments );
		if ( 0 === $total ) {
			return; // No lessons, don't show progress
		}

		// Use Tutor LMS actual percentage calculation to ensure absolute consistency
		$course_progress = tutor_utils()->get_course_completed_percent( $course_id, $user_id, true );
		$percent = is_array( $course_progress ) && isset( $course_progress['completed_percent'] ) ? (int) $course_progress['completed_percent'] : 0;

		?>
		<div class="mb-4">
			<div class="flex justify-between items-center text-xs font-medium text-ink-light mb-1.5">
				<span><?php esc_html_e( 'ความคืบหน้า', 'bia-learn' ); ?></span>
				<span class="text-primary-600 font-bold"><?php echo esc_html( $percent ); ?>%</span>
			</div>
			
			<div class="flex gap-1 w-full h-2.5">
				<?php foreach ( $segments as $status ) : 
					$bg_class = 'bg-paper-200'; // Default gray (unattempted)
					if ( 'completed' === $status ) {
						$bg_class = 'bg-success'; // Green
					} elseif ( 'quiz_pending' === $status ) {
						$bg_class = 'bg-warning'; // Yellow
					}
				?>
					<div class="flex-1 rounded-full <?php echo esc_attr( $bg_class ); ?> transition-colors duration-500 shadow-sm"></div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
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

		// Fallback to the first incomplete lesson
		$fallback_url = tutor_utils()->get_course_first_lesson( $course_id );
		if ( $fallback_url ) {
			return $fallback_url;
		}

		// Fallback to course page if no lessons exist
		return $url;
	}



	/**
	 * Change "Start Learning" to "Continue Learning" (เรียนต่อ)
	 */
	public static function translate_start_learning( $translated_text, $text, $domain ) {
		if ( 'tutor' === $domain ) {
			if ( 'Start Learning' === $text || 'Start learning!' === $text || 'Continue to lesson' === $text ) {
				return __( 'เรียนต่อ', 'bia-learn' );
			}
		}
		return $translated_text;
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
