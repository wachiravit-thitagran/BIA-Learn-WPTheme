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
	 * Per-request memo of whether the analytics tracker table exists.
	 * Public so templates that already ran the check can share the result
	 * (and so tests can reset it).
	 *
	 * @var bool|null
	 */
	public static $analytics_table_exists = null;

	/**
	 * Initialize all hooks.
	 */
	public static function init() {
		// 1. Smart Continue Button Redirect
		add_filter( 'tutor_course_continue_url', array( __CLASS__, 'smart_continue_url' ), 10, 2 );

		// Keep the cached curriculum status fresh (see get_course_curriculum_status).
		add_action( 'tutor_mark_lesson_complete_after', array( __CLASS__, 'flush_curriculum_cache_for_lesson' ), 10, 2 );
		add_action( 'tutor_quiz/attempt_ended', array( __CLASS__, 'flush_curriculum_cache_for_attempt' ), 10, 3 );

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

		// Check if table exists — memoized per request (this used to run a
		// SHOW TABLES query for every card on archive pages).
		if ( null === self::$analytics_table_exists ) {
			self::$analytics_table_exists = $wpdb->get_var(
				$wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) )
			) === $table_name;
		}
		if ( ! self::$analytics_table_exists ) {
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
	 * Status of a single curriculum content item.
	 *
	 * Quizzes/assignments are judged by their attempt/submission state (see
	 * get_quiz_assignment_status); everything else by lesson completion.
	 *
	 * @param object $content Curriculum content post.
	 * @param int    $user_id Learner.
	 * @return string 'completed' | 'quiz_pending' | 'quiz_failed' | 'unattempted'
	 */
	public static function get_content_status( $content, $user_id ) {
		$type = $content->post_type;
		if ( 'tutor_quiz' === $type || 'tutor_assignments' === $type ) {
			return self::get_quiz_assignment_status( $content->ID, $user_id, $type );
		}
		return tutor_utils()->is_completed_lesson( $content->ID, $user_id ) ? 'completed' : 'unattempted';
	}

	/**
	 * Get progress percentage for a specific topic/section.
	 *
	 * Passed quizzes/assignments now count as completed (they used to be judged
	 * by is_completed_lesson(), so a section containing a quiz could never
	 * reach 100%). The returned 'statuses' map (content_id => status) lets
	 * templates render per-item icons without re-querying.
	 *
	 * @param int        $topic_id Topic ID.
	 * @param int        $user_id  Learner.
	 * @param mixed|null $contents Optional pre-fetched result of
	 *                             get_course_contents_by_topic() to avoid a
	 *                             duplicate query.
	 */
	public static function get_section_progress( $topic_id, $user_id, $contents = null ) {
		if ( ! function_exists( 'tutor_utils' ) ) {
			return array( 'completed' => 0, 'total' => 0, 'percent' => 0, 'statuses' => array() );
		}

		if ( null === $contents ) {
			$contents = tutor_utils()->get_course_contents_by_topic( $topic_id, -1 );
		}
		$lesson_posts = is_object( $contents ) && isset( $contents->posts ) ? $contents->posts : ( is_array( $contents ) ? $contents : array() );

		$statuses  = array();
		$completed = 0;
		foreach ( $lesson_posts as $content ) {
			$status = self::get_content_status( $content, $user_id );
			$statuses[ (int) $content->ID ] = $status;
			if ( 'completed' === $status ) {
				$completed++;
			}
		}

		$total = count( $lesson_posts );

		return array(
			'completed' => $completed,
			'total'     => $total,
			'percent'   => $total > 0 ? round( ( $completed / $total ) * 100 ) : 0,
			'statuses'  => $statuses,
		);
	}

	private static function get_quiz_assignment_status( $content_id, $user_id, $type ) {
		global $wpdb;

		if ( 'tutor_quiz' === $type ) {
			// Only count submitted attempts (exclude in-progress `attempt_started`),
			// matching Tutor's own get_course_completed_percent() criteria — otherwise
			// a quiz the learner merely opened would be misread as failed.
			$attempts = $wpdb->get_results( $wpdb->prepare( "
				SELECT result, earned_marks, total_marks
				FROM {$wpdb->prefix}tutor_quiz_attempts
				WHERE user_id = %d AND quiz_id = %d AND attempt_status != 'attempt_started'
			", $user_id, $content_id ) );

			if ( empty( $attempts ) ) {
				return 'unattempted';
			}

			// Priority across attempts: pass > pending (awaiting review) > fail.
			$passing_grade = (float) tutor_utils()->get_quiz_option( $content_id, 'passing_grade', 0 );
			$has_pending   = false;

			foreach ( $attempts as $attempt ) {
				if ( 'pass' === $attempt->result ) {
					return 'completed';
				}
				if ( empty( $attempt->result ) ) {
					// Legacy attempts predate the `result` column: derive pass/fail from marks.
					$total   = (float) $attempt->total_marks;
					$percent = $total > 0 ? ( (float) $attempt->earned_marks * 100 ) / $total : 0;
					if ( $percent >= $passing_grade ) {
						return 'completed';
					}
				} elseif ( 'fail' !== $attempt->result ) {
					$has_pending = true; // e.g. 'pending' — submitted, awaiting manual review.
				}
			}

			return $has_pending ? 'quiz_pending' : 'quiz_failed';
		}

		if ( 'tutor_assignments' === $type ) {
			$submissions = tutor_utils()->is_assignment_submitted( $content_id, $user_id );
			if ( empty( $submissions ) ) {
				return 'unattempted';
			}

			$pass_mark   = tutor_utils()->get_assignment_option( $content_id, 'pass_mark' );
			$has_pending = false;

			foreach ( $submissions as $submission ) {
				$mark = get_comment_meta( $submission->comment_ID, 'assignment_mark', true );
				if ( ! is_numeric( $mark ) ) {
					$has_pending = true; // Submitted but not evaluated yet.
					continue;
				}
				if ( (int) $mark >= $pass_mark ) {
					return 'completed';
				}
			}

			return $has_pending ? 'quiz_pending' : 'quiz_failed';
		}

		return 'unattempted';
	}

	/**
	 * Get status of every lesson and quiz in the course for segmented progress bar.
	 *
	 * Returns an array of items with their status: 'completed' (green),
	 * 'quiz_pending' (yellow — submitted, awaiting review), 'quiz_failed'
	 * (red — submitted but not passed), 'unattempted' (gray).
	 */
	public static function get_course_curriculum_status( $course_id, $user_id ) {
		if ( ! function_exists( 'tutor_utils' ) ) {
			return array();
		}

		// Archive pages render many cards per request — cache per learner+course.
		// Without a persistent object cache this is a per-request memo; with one
		// (Redis etc.) the short TTL + the flush hooks in init() keep it fresh.
		$cache_key = 'bia_curr_status_' . (int) $course_id . '_' . (int) $user_id;
		$cached    = wp_cache_get( $cache_key, 'bia-learn' );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		$status_list = array();
		$topics      = tutor_utils()->get_topics( $course_id );
		$topic_posts = ( $topics && ! empty( $topics->posts ) ) ? $topics->posts : array();

		// Iterate the raw posts array — the_post()/wp_reset_postdata() would
		// clobber the global $post of enclosing custom loops (front page,
		// dashboard grids), pointing later template tags at the wrong post.
		foreach ( $topic_posts as $topic ) {
			$contents = tutor_utils()->get_course_contents_by_topic( $topic->ID, -1 );
			$items    = is_object( $contents ) && isset( $contents->posts ) ? $contents->posts : ( is_array( $contents ) ? $contents : array() );

			foreach ( $items as $content ) {
				$status_list[] = array(
					'title'  => get_the_title( $content->ID ),
					'status' => self::get_content_status( $content, $user_id ),
				);
			}
		}

		wp_cache_set( $cache_key, $status_list, 'bia-learn', 5 * MINUTE_IN_SECONDS );

		return $status_list;
	}

	/**
	 * Drop the cached curriculum status for one learner+course.
	 */
	public static function flush_curriculum_cache( $course_id, $user_id ) {
		wp_cache_delete( 'bia_curr_status_' . (int) $course_id . '_' . (int) $user_id, 'bia-learn' );
	}

	/**
	 * Cache invalidation: lesson completed (tutor_mark_lesson_complete_after).
	 */
	public static function flush_curriculum_cache_for_lesson( $lesson_id, $user_id = 0 ) {
		$user_id   = $user_id ? (int) $user_id : get_current_user_id();
		$course_id = (int) tutor_utils()->get_course_id_by( 'lesson', (int) $lesson_id );
		if ( $course_id ) {
			self::flush_curriculum_cache( $course_id, $user_id );
		}
	}

	/**
	 * Cache invalidation: quiz attempt submitted (tutor_quiz/attempt_ended).
	 */
	public static function flush_curriculum_cache_for_attempt( $attempt_id, $course_id = 0, $user_id = 0 ) {
		if ( $course_id ) {
			self::flush_curriculum_cache( (int) $course_id, $user_id ? (int) $user_id : get_current_user_id() );
		}
	}

	public static function render_segmented_progress_bar( $course_id, $user_id = 0, $percent = null ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		$segments = self::get_course_curriculum_status( $course_id, $user_id );
		$total    = count( $segments );
		if ( 0 === $total ) {
			return; // No lessons, don't show progress
		}

		if ( null === $percent ) {
			// Use Tutor LMS actual percentage calculation to ensure absolute
			// consistency. Callers that already computed it (course cards) pass
			// it in to avoid running the same queries twice per card.
			$course_progress = tutor_utils()->get_course_completed_percent( $course_id, $user_id, true );
			$percent = is_array( $course_progress ) && isset( $course_progress['completed_percent'] ) ? (int) $course_progress['completed_percent'] : 0;
		}
		$percent = (int) $percent;

		?>
		<div class="mb-4">
			<div class="flex items-center justify-between text-xs text-ink-light mb-1.5">
				<span><?php esc_html_e( 'ความคืบหน้า', 'bia-learn' ); ?></span>
				<span class="font-semibold text-crimson"><?php echo esc_html( $percent ); ?>%</span>
			</div>
			
			<div class="flex gap-0.5 w-full h-1.5">
				<?php
				$status_meta = array(
					'completed'    => array( 'bg-success', __( 'ผ่านแล้ว', 'bia-learn' ) ),
					'quiz_pending' => array( 'bg-warning', __( 'ส่งแล้ว รอตรวจ', 'bia-learn' ) ),
					'quiz_failed'  => array( 'bg-danger', __( 'ยังไม่ผ่าน', 'bia-learn' ) ),
					'unattempted'  => array( 'bg-paper-100', __( 'ยังไม่ได้เริ่ม', 'bia-learn' ) ),
				);
				foreach ( $segments as $segment ) :
					list( $bg_class, $status_label ) = isset( $status_meta[ $segment['status'] ] )
						? $status_meta[ $segment['status'] ]
						: $status_meta['unattempted'];
					$tooltip = $segment['title'] . ' — ' . $status_label;
				?>
					<div class="flex-1 rounded-full <?php echo esc_attr( $bg_class ); ?> transition-colors duration-500" title="<?php echo esc_attr( $tooltip ); ?>"></div>
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
					'title' => __( 'เรียนต่อจากที่ค้างไว้', 'bia-learn' ),
					'icon'  => 'play-line',
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
