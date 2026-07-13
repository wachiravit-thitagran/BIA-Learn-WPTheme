<?php
/**
 * Template for displaying course topics/sections with Progress Bars.
 * Overrides Tutor LMS core template.
 * 
 * @package BIA_Learn
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$topics      = tutor_utils()->get_topics();
$course_id   = get_the_ID();
$user_id     = get_current_user_id();
$is_enrolled = tutor_utils()->is_enrolled( $course_id, $user_id );

// Overall course progress
$course_progress   = tutor_utils()->get_course_completed_percent( $course_id, $user_id, true );
$completed_percent = is_array( $course_progress ) && isset( $course_progress['completed_percent'] ) ? (int) $course_progress['completed_percent'] : 0;

if ( $topics->have_posts() ) {
	?>
	<div class="tutor-course-topics-wrap mb-8 pb-8">
		
		<?php if ( $is_enrolled ) : ?>
		<!-- Overall Progress Header -->
		<div class="tutor-course-overall-progress mb-6 bg-white p-5 rounded-2xl border border-paper-100 shadow-sm">
			<div class="flex justify-between items-center mb-3">
				<h4 class="font-sans text-lg font-bold text-ink m-0">
					<?php echo esc_html( $completed_percent ); ?>% <?php esc_html_e( 'Completed', 'bia-learn' ); ?>
				</h4>
				<button class="text-ink-light hover:text-primary-600 transition" onclick="window.location.reload();" title="<?php esc_attr_e( 'Refresh', 'bia-learn' ); ?>">
					<i class="ti ti-refresh text-xl"></i>
				</button>
			</div>
			<div class="w-full bg-paper-200 rounded-full h-2">
				<div class="h-2 rounded-full bg-success transition-all duration-500" style="width: <?php echo esc_attr( $completed_percent ); ?>%;"></div>
			</div>
		</div>
		<?php else: ?>
		<h3 class="tutor-segment-title font-sans text-lg font-bold text-ink mb-4"><?php esc_html_e( 'เนื้อหาคอร์สเรียน', 'bia-learn' ); ?></h3>
		<?php endif; ?>

		<div class="tutor-accordion space-y-2">
			<?php
			$topic_index = 0;
			while ( $topics->have_posts() ) {
				$topics->the_post();
				$topic_id = get_the_ID();
				$contents = tutor_utils()->get_course_contents_by_topic( $topic_id, -1 );
				$lesson_posts = is_object( $contents ) && isset( $contents->posts ) ? $contents->posts : ( is_array( $contents ) ? $contents : array() );

				// Calculate Section Progress
				$progress = BIA_Learn_Tutor_UX::get_section_progress( $topic_id, $user_id );
				$is_section_completed = ( $progress['percent'] === 100 && $progress['total'] > 0 );
				$topic_index++;
				$is_first = ( $topic_index === 1 ); // open the first one by default if we were using JS, but Tutor handles it via its own classes

				?>
				<div class="tutor-accordion-item bg-white border border-paper-100 rounded-xl overflow-hidden shadow-sm transition-all">
					<div class="tutor-accordion-item-header flex justify-between items-center p-4 bg-white cursor-pointer hover:bg-paper-50 transition group">
						<div class="flex items-center gap-3 flex-1">
							<!-- Status Icon -->
							<div class="flex-shrink-0">
								<?php if ( $is_enrolled && $is_section_completed ) : ?>
									<div class="w-6 h-6 rounded-full bg-success text-white flex items-center justify-center">
										<i class="ti ti-check text-sm"></i>
									</div>
								<?php else : ?>
									<div class="w-6 h-6 rounded-full border-2 border-paper-300 group-hover:border-primary-400 transition-colors"></div>
								<?php endif; ?>
							</div>
							<h4 class="tutor-accordion-item-title font-sans text-base font-medium text-ink m-0"><?php the_title(); ?></h4>
						</div>
						<div class="flex items-center gap-2 text-ink-light">
							<!-- Info Icon (optional, could show section duration) -->
							<?php if ( ! $is_section_completed ) : ?>
								<i class="ti ti-info-circle text-lg opacity-0 group-hover:opacity-100 transition-opacity"></i>
							<?php endif; ?>
							<i class="ti ti-chevron-down tutor-accordion-indicator text-lg transition-transform duration-300"></i>
						</div>
					</div>

					<div class="tutor-accordion-item-body p-2 border-t border-paper-100 bg-white">
						<ul class="tutor-course-topic-contents m-0 p-0 list-none space-y-1">
							<?php
							foreach ( $lesson_posts as $content ) {
								$type = $content->post_type;
								$is_completed = tutor_utils()->is_completed_lesson( $content->ID, $user_id );
								
								// Determine specific icon and subtext
								if ( 'tutor_quiz' === $type ) {
									$subtext = __( 'Quiz', 'bia-learn' );
									$content_icon = 'ti ti-help-circle';
								} elseif ( 'tutor_assignments' === $type ) {
									$subtext = __( 'Assignment', 'bia-learn' );
									$content_icon = 'ti ti-clipboard-text';
								} else {
									// It's a lesson. Check if it has a video.
									$video = tutor_utils()->get_video_info( $content->ID );
									if ( $video && isset( $video->source_video_id ) && ! empty( $video->source_video_id ) ) {
										// Has video
										$duration = get_post_meta( $content->ID, '_video_runtime', true );
										if ( $duration ) {
											// format duration from H:i:s
											$time_parts = explode(':', $duration);
											$mins = 0;
											if ( count($time_parts) === 3 ) {
												$mins = ((int)$time_parts[0] * 60) + (int)$time_parts[1];
											} elseif ( count($time_parts) === 2 ) {
												$mins = (int)$time_parts[0];
											}
											$subtext = $mins > 0 ? sprintf( __( 'Video - %d mins', 'bia-learn' ), $mins ) : __( 'Video', 'bia-learn' );
										} else {
											$subtext = __( 'Video', 'bia-learn' );
										}
										$content_icon = 'ti ti-player-play-filled';
									} else {
										$subtext = __( 'Reading', 'bia-learn' );
										$content_icon = 'ti ti-file-description';
									}
								}

								// Check if active (assuming this is rendered on the single lesson page as sidebar, we check get_the_ID())
								// But this is course single page, so no lesson is "active" yet.
								$is_active = false;
								
								$active_class = $is_active ? 'bg-paper-100 border-l-4 border-primary-500 rounded-r-lg' : 'hover:bg-paper-50 rounded-lg border-l-4 border-transparent';
								?>
								<li class="tutor-course-lesson <?php echo esc_attr( $active_class ); ?> transition-all">
									<a href="<?php echo esc_url( get_permalink( $content->ID ) ); ?>" class="flex items-start gap-3 py-3 px-3 no-underline w-full">
										<div class="flex-shrink-0 mt-0.5">
											<?php if ( $is_enrolled ) : ?>
												<?php if ( $is_completed ) : ?>
													<div class="w-5 h-5 rounded-full bg-success text-white flex items-center justify-center shadow-sm">
														<i class="ti ti-check text-xs"></i>
													</div>
												<?php else : ?>
													<?php if ( 'tutor_quiz' === $type || 'tutor_assignments' === $type ) : ?>
														<div class="w-5 h-5 rounded-full bg-warning text-white flex items-center justify-center shadow-sm">
															<i class="ti ti-exclamation-mark text-xs"></i>
														</div>
													<?php else: ?>
														<div class="w-5 h-5 rounded-full border-2 border-primary-300"></div>
													<?php endif; ?>
												<?php endif; ?>
											<?php else: ?>
												<i class="ti ti-lock text-paper-400"></i>
											<?php endif; ?>
										</div>
										<div class="flex-1 min-w-0">
											<div class="text-sm font-semibold <?php echo $is_active ? 'text-primary-700' : 'text-ink'; ?> truncate">
												<?php echo esc_html( $content->post_title ); ?>
											</div>
											<div class="text-xs text-ink-light mt-0.5 flex items-center gap-1">
												<i class="<?php echo esc_attr( $content_icon ); ?> text-sm opacity-70"></i>
												<?php echo esc_html( $subtext ); ?>
											</div>
										</div>
									</a>
								</li>
								<?php
							}
							?>
						</ul>
					</div>
				</div>
				<?php
			}
			wp_reset_postdata();
			?>
		</div>
	</div>
	<?php
}
