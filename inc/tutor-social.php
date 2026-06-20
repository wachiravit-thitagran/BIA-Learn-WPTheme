<?php
/**
 * Social Proof & Community Engine for Tutor LMS
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

class BIA_Learn_Tutor_Social {

	/**
	 * Initialize hooks.
	 */
	public static function init() {
		// 1. Enrolled Students Avatars on Single Course
		add_action( 'tutor_course/single/enrolled/after/lead_info', array( __CLASS__, 'render_enrolled_avatars' ), 5 );
		add_action( 'tutor_course/single/before/enroll_btn', array( __CLASS__, 'render_enrolled_avatars' ), 5 );

		// 2. Activity Feed Shortcode
		add_shortcode( 'bia_tutor_activity_feed', array( __CLASS__, 'activity_feed_shortcode' ) );

		// 3. Featured Review Highlight
		add_action( 'tutor_course/single/before/reviews', array( __CLASS__, 'render_featured_review' ), 10 );
	}

	/**
	 * Get recent activities across the platform.
	 */
	public static function get_recent_activities( $limit = 5 ) {
		global $wpdb;
		
		// Get recent enrollments or completions from comments
		$query = $wpdb->prepare( "
			SELECT comment_post_ID as course_id, comment_type as type, comment_date as date, comment_author as author
			FROM {$wpdb->comments}
			WHERE comment_type IN ('tutor_course_completed', 'tutor_course_enrolled')
			ORDER BY comment_date DESC
			LIMIT %d
		", $limit );

		$results = $wpdb->get_results( $query );
		$activities = array();

		foreach ( $results as $row ) {
			$course_title = get_the_title( $row->course_id );
			$time_diff = human_time_diff( strtotime( $row->date ), current_time('timestamp') );
			
			// Mask the author name (e.g., "Somchai" -> "Som****")
			$author_name = trim( $row->author );
			if ( empty( $author_name ) ) {
				$author_name = __( 'ผู้เรียนท่านหนึ่ง', 'bia-learn' );
			} else {
				$len = mb_strlen( $author_name );
				if ( $len > 3 ) {
					$author_name = mb_substr( $author_name, 0, 3 ) . '***';
				} else {
					$author_name = mb_substr( $author_name, 0, 1 ) . '***';
				}
			}

			$action_text = ( $row->type === 'tutor_course_completed' ) ? __( 'เพิ่งเรียนจบ', 'bia-learn' ) : __( 'เพิ่งลงทะเบียน', 'bia-learn' );
			
			$activities[] = array(
				'message' => sprintf( __( 'คุณ %s %s', 'bia-learn' ), $author_name, $action_text ),
				'course'  => $course_title,
				'time'    => sprintf( __( '%s ที่ผ่านมา', 'bia-learn' ), $time_diff )
			);
		}

		return $activities;
	}

	/**
	 * Render the Activity Feed shortcode.
	 */
	public static function activity_feed_shortcode( $atts ) {
		$activities = self::get_recent_activities();
		if ( empty( $activities ) ) return '';

		ob_start();
		?>
		<div class="bg-blue-50 border border-blue-100 p-4 rounded-xl mb-6 relative overflow-hidden flex items-center gap-4">
			<div class="text-blue-500 animate-pulse">
				<?php echo bia_learn_icon( 'megaphone', 'h-6 w-6' ); ?>
			</div>
			<div class="flex-1 overflow-hidden relative h-6">
				<div class="bia-activity-ticker absolute top-0 left-0 w-full transition-transform duration-500">
					<?php foreach ( $activities as $index => $act ) : ?>
						<div class="h-6 flex items-center text-sm text-ink-light whitespace-nowrap">
							<strong><?php echo esc_html( $act['message'] ); ?></strong>&nbsp;
							<span class="text-blue-600 font-medium truncate max-w-[200px] sm:max-w-xs">"<?php echo esc_html( $act['course'] ); ?>"</span>&nbsp;
							<span class="text-xs text-paper-500 ml-2">&bull; <?php echo esc_html( $act['time'] ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
			
			<script>
				// Simple JS Ticker
				document.addEventListener('DOMContentLoaded', () => {
					const ticker = document.querySelector('.bia-activity-ticker');
					if (!ticker) return;
					const items = ticker.children.length;
					let current = 0;
					
					if (items > 1) {
						setInterval(() => {
							current = (current + 1) % items;
							ticker.style.transform = `translateY(-${current * 24}px)`;
						}, 3000);
					}
				});
			</script>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render enrolled student avatars as social proof.
	 */
	public static function render_enrolled_avatars( $course_id = 0 ) {
		if ( ! $course_id ) {
			$course_id = get_the_ID();
		}

		// Get enrolled users for this course
		$enrolled_users = tutor_utils()->get_enrolled_users_by_course( $course_id );
		
		if ( ! $enrolled_users || ! is_array( $enrolled_users ) || count( $enrolled_users ) < 3 ) {
			return; // Only show if we have a decent amount of students
		}

		$total_enrolled = count( $enrolled_users );
		$display_users = array_slice( $enrolled_users, 0, 5 ); // Show max 5 avatars

		?>
		<div class="mt-4 mb-6 flex items-center gap-3">
			<div class="flex -space-x-3 rtl:space-x-reverse">
				<?php foreach ( $display_users as $user_id ) : ?>
					<?php 
					$avatar_url = get_avatar_url( $user_id, array( 'size' => 40 ) ); 
					// Generate a random pastel background color based on User ID for variety if avatar is default
					$bg_colors = ['bg-red-100', 'bg-blue-100', 'bg-green-100', 'bg-yellow-100', 'bg-purple-100'];
					$bg = $bg_colors[ $user_id % 5 ];
					?>
					<img class="w-8 h-8 rounded-full border-2 border-white <?php echo esc_attr( $bg ); ?> object-cover shadow-sm" src="<?php echo esc_url( $avatar_url ); ?>" alt="Student Avatar">
				<?php endforeach; ?>
			</div>
			<div class="text-sm text-ink-light">
				<?php printf( wp_kses_post( __( 'เรียนร่วมกับเพื่อนอีก <strong>%d</strong> คน', 'bia-learn' ) ), $total_enrolled ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render the featured review before the reviews list.
	 */
	public static function render_featured_review( $course_id = 0 ) {
		if ( ! $course_id ) {
			$course_id = get_the_ID();
		}

		$review = self::get_featured_review( $course_id );
		if ( ! $review ) return;

		// Only feature a review if it has substantial text (e.g. > 50 chars)
		if ( strlen( $review->comment_content ) < 50 ) return;

		?>
		<div class="mb-8 mt-6">
			<h3 class="tutor-fs-5 tutor-fw-medium tutor-color-black mb-4 flex items-center">
				<span class="text-yellow-500 mr-2 flex items-center">
					<?php echo bia_learn_icon( 'star', 'h-5 w-5' ); ?>
				</span>
				<?php esc_html_e( 'รีวิวแนะนำ', 'bia-learn' ); ?>
			</h3>
			<div class="bg-gradient-to-br from-paper-50 to-white border border-paper-200 p-6 rounded-2xl shadow-sm relative">
				<div class="absolute top-4 right-6 text-6xl text-paper-200 font-serif opacity-50 leading-none">"</div>
				<div class="flex items-center gap-4 mb-4">
					<div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 font-bold text-lg">
						<?php echo esc_html( mb_substr( $review->comment_author, 0, 1 ) ); ?>
					</div>
					<div>
						<h4 class="font-bold text-ink m-0"><?php echo esc_html( $review->comment_author ); ?></h4>
						<div class="flex text-yellow-400 gap-0.5">
							<?php for ( $i = 0; $i < 5; $i++ ) { echo bia_learn_icon( 'star', 'h-4 w-4' ); } ?>
						</div>
					</div>
				</div>
				<p class="text-ink-light leading-relaxed italic m-0 relative z-10">
					"<?php echo esc_html( $review->comment_content ); ?>"
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Get the best featured review for a course.
	 */
	public static function get_featured_review( $course_id ) {
		global $wpdb;
		// Fetch 5-star reviews, longest text first
		$query = $wpdb->prepare( "
			SELECT c.comment_ID, c.comment_content, c.comment_author, r.rating
			FROM {$wpdb->comments} c
			INNER JOIN {$wpdb->prefix}tutor_reviews r ON c.comment_ID = r.comment_id
			WHERE c.comment_post_ID = %d
			  AND c.comment_approved = '1'
			  AND r.rating = 5
			ORDER BY LENGTH(c.comment_content) DESC
			LIMIT 1
		", $course_id );

		return $wpdb->get_row( $query );
	}
}

// Initialize Social Engine
if ( function_exists( 'tutor_utils' ) ) {
	BIA_Learn_Tutor_Social::init();
}
