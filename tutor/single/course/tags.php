<?php
/**
 * Template for displaying course tags
 *
 * @package BIA_Learn
 */

do_action( 'tutor_course/single/before/tags' );

$course_tags = apply_filters( 'tutor_course_single_tags', get_tutor_course_tags(), get_the_ID() );
if ( is_array( $course_tags ) && count( $course_tags ) ) { ?>
	<div class="tutor-course-details-widget">
		<h3 class="tutor-course-details-widget-title">
			<?php esc_html_e( 'แท็ก', 'bia-learn' ); ?>
		</h3>
		<div class="tutor-course-details-widget-tags">
		  <ul class="flex flex-wrap gap-2">
				<?php
				foreach ( $course_tags as $course_tag ) {
					$tag_link = get_term_link( (int) $course_tag->term_id );
					echo "<li><a href='" . esc_url( $tag_link ) . "' class='badge-muted hover:bg-paper-200 transition'>" . esc_html( $course_tag->name ) . "</a></li>";
				}
				?>
		  </ul>
		</div>
	</div>
	<?php
}

do_action( 'tutor_course/single/after/tags' ); ?>
