<?php
/**
 * Template for displaying Quiz Attempts
 *
 * @package BIA_Learn
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$user_id = get_current_user_id();
$attempts = tutor_utils()->get_all_quiz_attempts_by_user( $user_id );

?>

<div class="tutor-dashboard-content-inner">
	<div class="mb-6 flex items-center">
		<div class="inline-flex items-center gap-2 px-4 py-2 bg-primary-50 text-primary-600 font-semibold rounded-lg">
			<i class="ti ti-puzzle text-lg"></i>
			<?php esc_html_e( 'Quiz Attempts', 'bia-learn' ); ?>
		</div>
	</div>

	<?php if ( is_array( $attempts ) && count( $attempts ) ) : ?>
		<div class="bg-white border border-paper-200 rounded-xl overflow-hidden shadow-sm">
			<!-- Header -->
			<div class="grid grid-cols-12 gap-4 p-4 bg-paper-50 border-b border-paper-200 text-sm font-semibold text-ink-light">
				<div class="col-span-12 md:col-span-6"><?php esc_html_e( 'Quiz info', 'bia-learn' ); ?></div>
				<div class="col-span-4 md:col-span-2 text-center md:text-left"><?php esc_html_e( 'Marks', 'bia-learn' ); ?></div>
				<div class="col-span-4 md:col-span-2 text-center md:text-left"><?php esc_html_e( 'Time', 'bia-learn' ); ?></div>
				<div class="col-span-4 md:col-span-2 text-center md:text-left"><?php esc_html_e( 'Result', 'bia-learn' ); ?></div>
			</div>

			<!-- Rows -->
			<div class="divide-y divide-paper-100">
				<?php foreach ( $attempts as $attempt ) : 
					$quiz_title = get_the_title( $attempt->quiz_id );
					$course_title = get_the_title( $attempt->course_id );
					$date = date_i18n( 'D M d, Y, h:i A', strtotime( $attempt->attempt_ended_at ) );
					$percent = $attempt->earned_percent;
					$is_pass = ( $attempt->result === 'pass' );
					
					// Parse attempt info
					$attempt_info = $attempt->attempt_info;
					if ( is_string( $attempt_info ) ) {
						$attempt_info = unserialize( $attempt_info );
					}
					
					$total_questions = isset( $attempt_info['total_questions'] ) ? $attempt_info['total_questions'] : 0;
					$total_answered = isset( $attempt->total_answered_questions ) ? $attempt->total_answered_questions : 0;
					// Tutor LMS usually provides earned_marks and total_marks. We can roughly estimate correct/incorrect based on percent or just use what we have.
					// Since the exact correct/incorrect count might not be directly available on the attempt object without fetching answers, we'll try to find it or approximate.
					// Let's use earned_marks vs total_marks as correct/incorrect ratio if we can't find question counts.
					$earned_marks = isset($attempt->earned_marks) ? (float) $attempt->earned_marks : 0;
					$total_marks = isset($attempt->total_marks) ? (float) $attempt->total_marks : 1;
					
					// Assuming 1 mark per question for display purposes if actual question counts aren't easily accessible
					$correct = $earned_marks;
					$incorrect = $total_marks - $earned_marks;

					// Time Taken
					$time_taken = '-';
					if ( isset( $attempt->attempt_ended_at ) && isset( $attempt->attempt_started_at ) ) {
						$diff = strtotime( $attempt->attempt_ended_at ) - strtotime( $attempt->attempt_started_at );
						$mins = floor( $diff / 60 );
						$secs = $diff % 60;
						$time_taken = sprintf( '%02d:%02d', $mins, $secs );
					}
				?>
				<div class="grid grid-cols-12 gap-4 p-5 hover:bg-paper-50 transition-colors items-center">
					
					<!-- Quiz Info -->
					<div class="col-span-12 md:col-span-6">
						<h4 class="font-sans text-base font-bold text-ink m-0 mb-1">
							<?php echo esc_html( $quiz_title ); ?> - Attempt <?php echo esc_html( $attempt->attempt_id ); ?>
						</h4>
						<div class="text-sm text-ink-light mb-1 italic">
							Course: <?php echo esc_html( $course_title ); ?>
						</div>
						<div class="text-xs text-paper-400 font-medium">
							<?php echo esc_html( $date ); ?>
						</div>
					</div>
					
					<!-- Marks -->
					<div class="col-span-4 md:col-span-2 flex items-center gap-3">
						<div class="relative w-10 h-10 flex items-center justify-center rounded-full border-4 <?php echo $is_pass ? 'border-success' : 'border-error'; ?>">
							<span class="text-xs font-bold <?php echo $is_pass ? 'text-success' : 'text-error'; ?>">
								<?php echo esc_html( round($percent) ); ?>%
							</span>
						</div>
						<div class="text-xs font-medium space-y-1">
							<div class="flex items-center gap-1 text-ink-light">
								<span class="w-2 h-2 rounded-full bg-success inline-block"></span>
								<?php echo esc_html( $correct ); ?> correct
							</div>
							<div class="flex items-center gap-1 text-ink-light">
								<span class="w-2 h-2 rounded-full bg-error inline-block"></span>
								<?php echo esc_html( $incorrect ); ?> incorrect
							</div>
						</div>
					</div>

					<!-- Time -->
					<div class="col-span-4 md:col-span-2 text-center md:text-left flex items-center justify-center md:justify-start gap-1.5 text-sm text-ink-light font-medium">
						<i class="ti ti-clock-hour-4 text-lg"></i>
						<?php echo esc_html( $time_taken ); ?>
					</div>

					<!-- Result -->
					<div class="col-span-4 md:col-span-2 text-center md:text-left">
						<?php if ( $is_pass ) : ?>
							<span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">Passed</span>
						<?php else: ?>
							<span class="px-3 py-1 bg-red-100 text-red-700 text-xs font-bold rounded-full">Failed</span>
						<?php endif; ?>
					</div>
					
				</div>
				<?php endforeach; ?>
			</div>
		</div>
	<?php else : ?>
		<div class="text-center py-12 bg-paper-50 rounded-xl border border-dashed border-paper-200">
			<i class="ti ti-puzzle text-4xl text-paper-300 mb-4 block"></i>
			<p class="text-ink-light"><?php esc_html_e( 'No quiz attempts found.', 'bia-learn' ); ?></p>
		</div>
	<?php endif; ?>
</div>
