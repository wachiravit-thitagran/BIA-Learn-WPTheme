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

$topics = tutor_utils()->get_topics();
$course_id = get_the_ID();
$user_id = get_current_user_id();
$is_enrolled = tutor_utils()->is_enrolled( $course_id, $user_id );

if ( $topics->have_posts() ) {
	?>
	<div class="tutor-course-topics-wrap mb-8 pb-8">
		<h3 class="tutor-segment-title font-sans text-lg font-bold text-ink"><?php esc_html_e( 'เนื้อหาคอร์สเรียน', 'bia-learn' ); ?></h3>
		<div class="tutor-accordion tutor-mt-24 mt-4 space-y-4">
			<?php
			while ( $topics->have_posts() ) {
				$topics->the_post();
				$topic_id = get_the_ID();
				$contents = tutor_utils()->get_course_contents_by_topic( $topic_id, -1 );
				$lesson_posts = is_object( $contents ) && isset( $contents->posts ) ? $contents->posts : ( is_array( $contents ) ? $contents : array() );

				
				// Calculate Section Progress
				$progress = BIA_Learn_Tutor_UX::get_section_progress( $topic_id, $user_id );
				?>
				<div class="tutor-accordion-item bg-white border border-paper-100 rounded-lg overflow-hidden shadow-sm">
					<div class="tutor-accordion-item-header flex justify-between items-center p-4 bg-paper-50 cursor-pointer transition hover:bg-paper-100">
						<div class="flex-1">
							<h4 class="tutor-accordion-item-title font-sans text-base font-bold text-ink m-0"><?php the_title(); ?></h4>
							<?php if ( $is_enrolled && $progress['total'] > 0 ) : ?>
								<div class="tutor-ux-section-progress mt-2">
									<div class="text-xs text-ink-light mb-1 font-medium">
										<?php echo esc_html( $progress['percent'] ); ?>% &mdash; <?php printf( esc_html__( 'เรียนจบแล้ว %1$s/%2$s บท', 'bia-learn' ), $progress['completed'], $progress['total'] ); ?>
									</div>
									<div class="w-full max-w-xs bg-paper-200 rounded-full h-1.5">
										<div class="h-1.5 rounded-full transition-all duration-500 <?php echo $progress['percent'] === 100 ? 'bg-green-500' : 'bg-primary-500'; ?>" style="width: <?php echo esc_attr( $progress['percent'] ); ?>%;"></div>
									</div>
								</div>
							<?php endif; ?>
						</div>
					</div>

					<div class="tutor-accordion-item-body p-4 border-t border-paper-100 bg-white">
						<ul class="tutor-course-topic-contents m-0 p-0 list-none space-y-2">
							<?php
							foreach ( $lesson_posts as $content ) {
								$icon = $content->post_type === 'tutor_quiz' ? 'tutor-icon-quiz' : 'tutor-icon-document-text';
								$is_completed = tutor_utils()->is_completed_lesson( $content->ID, $user_id );
								$status_text = $is_completed ? __( 'สำเร็จ', 'bia-learn' ) : __( 'รอเรียน', 'bia-learn' );
								$status_color = $is_completed ? 'text-green-600 bg-green-50' : 'text-ink-light bg-paper-50';
								?>
								<li class="tutor-course-lesson flex justify-between items-center py-2 px-3 rounded hover:bg-paper-50 transition">
									<div class="flex items-center gap-3">
										<i class="<?php echo esc_attr( $icon ); ?> text-ink-light"></i>
										<a href="<?php echo esc_url( get_permalink( $content->ID ) ); ?>" class="text-sm font-medium text-ink hover:text-primary-600 no-underline">
											<?php echo esc_html( $content->post_title ); ?>
										</a>
									</div>
									<?php if ( $is_enrolled ) : ?>
										<span class="text-2xs font-semibold px-2 py-0.5 rounded-full <?php echo esc_attr( $status_color ); ?>">
											<?php echo esc_html( $status_text ); ?>
										</span>
									<?php endif; ?>
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
