<?php
/**
 * Gamification Engine for Tutor LMS (Streaks & Goals)
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

class BIA_Learn_Tutor_Gamification {

	/**
	 * Initialize hooks.
	 */
	public static function init() {
		// Display streak on the dashboard header
		add_action( 'tutor_dashboard/before_header', array( __CLASS__, 'render_dashboard_streak' ), 10 );
		
		// Display streak in the single-course header for enrolled learners.
		// (Tutor 4.0 removed 'tutor_course/single/enrolled/after/lead_info';
		// the '/progress_bar' variant is what fires now — see
		// templates/single/common/header.php in the plugin.)
		add_action( 'tutor_course/single/enrolled/after/lead_info/progress_bar', array( __CLASS__, 'render_sidebar_streak' ), 10 );
	}

	/**
	 * Calculate the current learning streak for a user.
	 *
	 * @param int $user_id
	 * @return array { current_streak: int, active_today: bool, longest_streak: int }
	 */
	public static function get_user_streak( $user_id ) {
		global $wpdb;

		// Active means the user COMPLETED a lesson on that day. Tutor LMS 4.x
		// records completions as usermeta `_tutor_completed_lesson_id_{id}`
		// whose value is tutor_time() — a site-local unix timestamp. (It never
		// writes lesson-completion comments; the old comments query here
		// matched nothing, so streaks were permanently zero.)
		$query = $wpdb->prepare( "
			SELECT DISTINCT DATE(FROM_UNIXTIME(meta_value)) as active_date
			FROM {$wpdb->usermeta}
			WHERE user_id = %d
			  AND meta_key LIKE %s
			  AND meta_value REGEXP '^[0-9]+$'
			ORDER BY active_date DESC
		", $user_id, $wpdb->esc_like( '_tutor_completed_lesson_id_' ) . '%' );

		$results = $wpdb->get_col( $query );

		if ( empty( $results ) ) {
			return array( 'current_streak' => 0, 'active_today' => false, 'longest_streak' => 0 );
		}

		$today = current_time( 'Y-m-d' );
		$yesterday = gmdate( 'Y-m-d', strtotime( $today . ' -1 day' ) );
		
		$active_today = in_array( $today, $results, true );
		$current_streak = 0;
		$longest_streak = 0;
		$temp_streak = 0;
		
		$expected_date = $active_today ? $today : $yesterday;

		// Calculate current streak
		foreach ( $results as $date ) {
			if ( $date === $expected_date ) {
				$current_streak++;
				$expected_date = gmdate( 'Y-m-d', strtotime( $date . ' -1 day' ) );
			} else {
				break; // Streak broken
			}
		}

		// Calculate longest streak
		$prev_date = null;
		foreach ( $results as $date ) {
			if ( $prev_date === null ) {
				$temp_streak = 1;
			} else {
				$expected_prev = gmdate( 'Y-m-d', strtotime( $prev_date . ' -1 day' ) );
				if ( $date === $expected_prev ) {
					$temp_streak++;
				} else {
					$temp_streak = 1;
				}
			}
			$longest_streak = max( $longest_streak, $temp_streak );
			$prev_date = $date;
		}

		return array(
			'current_streak' => $current_streak,
			'active_today'   => $active_today,
			'longest_streak' => max( $longest_streak, $current_streak ),
		);
	}

	/**
	 * Render the streak widget for the dashboard.
	 */
	public static function render_dashboard_streak() {
		$user_id = get_current_user_id();
		if ( ! $user_id ) return;

		$streak = self::get_user_streak( $user_id );
		if ( $streak['current_streak'] === 0 && ! $streak['active_today'] ) {
			// Don't show if they have 0 streak and aren't active today to avoid demotivation
			return;
		}
		
		$icon_color = $streak['active_today'] ? 'text-orange-500' : 'text-paper-400';
		$bg_color   = $streak['active_today'] ? 'bg-orange-50 border-orange-200' : 'bg-paper-50 border-paper-200';
		$text_color = $streak['active_today'] ? 'text-orange-700' : 'text-ink-light';
		?>
		<div class="mb-6 card p-4 flex items-center justify-between border <?php echo esc_attr( $bg_color ); ?> rounded-xl">
			<div class="flex items-center gap-4">
				<div class="text-4xl <?php echo esc_attr( $icon_color ); ?>">
					<?php echo bia_learn_icon( 'flame', 'h-10 w-10' ); ?>
				</div>
				<div>
					<h4 class="font-sans text-lg font-bold <?php echo esc_attr( $text_color ); ?> m-0 mb-1">
						<?php printf( esc_html__( '%d Day Streak!', 'bia-learn' ), $streak['current_streak'] ); ?>
					</h4>
					<p class="text-sm text-ink-light m-0">
						<?php if ( $streak['active_today'] ) : ?>
							<?php esc_html_e( 'You\'re on fire today! Keep learning tomorrow to maintain your streak.', 'bia-learn' ); ?>
						<?php else : ?>
							<?php esc_html_e( 'Complete a lesson today to keep your streak alive!', 'bia-learn' ); ?>
						<?php endif; ?>
					</p>
				</div>
			</div>
			<div class="text-right hidden sm:block">
				<p class="text-xs text-ink-light m-0 font-medium uppercase tracking-wider"><?php esc_html_e( 'Longest Streak', 'bia-learn' ); ?></p>
				<p class="text-xl font-bold text-ink m-0"><?php echo esc_html( $streak['longest_streak'] ); ?></p>
			</div>
		</div>
		<?php
	}

	/**
	 * Render the streak widget for the course sidebar.
	 */
	public static function render_sidebar_streak() {
		$user_id = get_current_user_id();
		if ( ! $user_id ) return;

		$streak = self::get_user_streak( $user_id );
		if ( $streak['current_streak'] === 0 && ! $streak['active_today'] ) {
			return;
		}
		
		$icon_color = $streak['active_today'] ? 'text-orange-500' : 'text-paper-400';
		?>
		<div class="mt-6 card p-4 flex items-center justify-between bg-white border border-paper-200 rounded-xl shadow-sm">
			<div class="flex items-center gap-3">
				<div class="text-2xl <?php echo esc_attr( $icon_color ); ?>">
					<?php echo bia_learn_icon( 'flame', 'h-8 w-8' ); ?>
				</div>
				<div>
					<h4 class="font-sans text-sm font-bold text-ink m-0">
						<?php printf( esc_html__( '%d Day Streak!', 'bia-learn' ), $streak['current_streak'] ); ?>
					</h4>
					<p class="text-xs text-ink-light m-0 mt-0.5">
						<?php echo $streak['active_today'] ? esc_html__( 'Streak secured today!', 'bia-learn' ) : esc_html__( 'Learn today to keep it!', 'bia-learn' ); ?>
					</p>
				</div>
			</div>
		</div>
		<?php
	}
}

// Initialize Gamification Engine
if ( function_exists( 'tutor_utils' ) ) {
	BIA_Learn_Tutor_Gamification::init();
}
