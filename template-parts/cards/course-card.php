<?php
/**
 * Course card. Designed for the Tutor LMS `courses` post type but degrades
 * gracefully to a regular post when Tutor data isn't available.
 *
 * Expects to run inside the loop (uses the current global $post).
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

$course_id = get_the_ID();
$tutils    = bia_learn_tutor_utils();
$has_tutor = (bool) $tutils;

// --- Gather Tutor metadata (guarded) -------------------------------------
$price_html   = '';
$is_free      = true;
$lesson_count = 0;
$duration     = '';
$rating       = null;
$level        = '';
$enrolled     = 0;

if ( $has_tutor ) {
	if ( method_exists( $tutils, 'is_course_purchasable' ) ) {
		$is_free = ! $tutils->is_course_purchasable( $course_id );
	}

	if ( method_exists( $tutils, 'get_lesson_count_by_course' ) ) {
		$lesson_count = (int) $tutils->get_lesson_count_by_course( $course_id );
	}

	if ( method_exists( $tutils, 'count_enrolled_users_by_course' ) ) {
		$enrolled = (int) $tutils->count_enrolled_users_by_course( $course_id );
	}

	$level        = get_post_meta( $course_id, '_tutor_course_level', true );

	$dur = method_exists( $tutils, 'get_course_duration_context' ) ? $tutils->get_course_duration_context( $course_id ) : '';
	if ( $dur ) {
		$duration = trim( wp_strip_all_tags( $dur ) );
	}

	$rating_obj = method_exists( $tutils, 'get_course_rating' ) ? $tutils->get_course_rating( $course_id ) : null;
	if ( $rating_obj && isset( $rating_obj->rating_avg, $rating_obj->rating_count ) && $rating_obj->rating_count > 0 ) {
		$rating = $rating_obj;
	}

	if ( ! $is_free && method_exists( $tutils, 'get_course_price' ) ) {
		ob_start();
		echo wp_kses_post( $tutils->get_course_price( $course_id ) );
		$price_html = trim( ob_get_clean() );
	}
}

// Theme-level enrichments (instructor falls back to author even without Tutor;
// progress is non-null only for an enrolled, logged-in learner).
$instructor = bia_learn_course_instructor( $course_id );
$progress   = bia_learn_course_progress( $course_id );
$cat_terms  = get_the_terms( $course_id, 'course-category' );
$category   = ( $cat_terms && ! is_wp_error( $cat_terms ) ) ? $cat_terms[0] : null;

$level_labels = array(
	'beginner'     => __( 'ระดับต้น', 'bia-learn' ),
	'intermediate' => __( 'ระดับกลาง', 'bia-learn' ),
	'expert'       => __( 'ระดับสูง', 'bia-learn' ),
	'all_levels'   => __( 'ทุกระดับ', 'bia-learn' ),
);
?>
<article <?php post_class( 'card card-hover group flex flex-col' ); ?>>
	<a href="<?php the_permalink(); ?>" class="relative block aspect-[16/10] overflow-hidden bg-paper-100">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php
			the_post_thumbnail(
				'bia-card',
				array(
					'class'   => 'h-full w-full object-cover transition duration-500 ease-out-expo group-hover:scale-105',
					'loading' => 'lazy',
					'alt'     => the_title_attribute( array( 'echo' => false ) ),
				)
			);
			?>
		<?php else : ?>
			<span class="flex h-full w-full items-center justify-center bg-crimson-wash text-paper-50">
				<?php echo bia_learn_icon( 'book', 'h-12 w-12 opacity-60' ); // phpcs:ignore ?>
			</span>
		<?php endif; ?>

		<span class="absolute left-4 top-4 <?php echo $is_free ? 'badge-gold' : 'badge'; ?> bg-white/90 backdrop-blur">
			<?php echo $is_free ? esc_html__( 'เรียนฟรี', 'bia-learn' ) : wp_kses_post( $price_html ?: __( 'มีค่าใช้จ่าย', 'bia-learn' ) ); ?>
		</span>
		<?php if ( $category ) : ?>
			<span class="absolute right-4 top-4 badge bg-white/90 backdrop-blur"><?php echo esc_html( $category->name ); ?></span>
		<?php endif; ?>
	</a>

	<div class="flex flex-1 flex-col gap-3 p-6">
		<!-- meta row -->
		<div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-ink-light">
			<?php if ( $lesson_count ) : ?>
				<span class="inline-flex items-center gap-1.5"><?php echo bia_learn_icon( 'book', 'h-4 w-4 text-crimson' ); // phpcs:ignore ?><?php printf( esc_html__( '%d บทเรียน', 'bia-learn' ), $lesson_count ); ?></span>
			<?php endif; ?>
			<?php if ( $duration ) : ?>
				<span class="inline-flex items-center gap-1.5"><?php echo bia_learn_icon( 'clock', 'h-4 w-4 text-crimson' ); // phpcs:ignore ?><?php echo esc_html( $duration ); ?></span>
			<?php endif; ?>
		</div>

		<h3 class="font-serif text-xl font-bold leading-snug text-ink transition group-hover:text-crimson">
			<a href="<?php the_permalink(); ?>" class="line-clamp-2"><?php the_title(); ?></a>
		</h3>

		<p class="line-clamp-2 text-sm leading-relaxed text-ink-light"><?php echo esc_html( get_the_excerpt() ); ?></p>

		<?php if ( $instructor ) : ?>
			<div class="flex items-center gap-2 text-xs text-ink-light">
				<?php echo get_avatar( $instructor->ID, 24, '', esc_attr( $instructor->display_name ), array( 'class' => 'h-6 w-6 rounded-full' ) ); ?>
				<span class="line-clamp-1"><?php echo esc_html( $instructor->display_name ); ?></span>
			</div>
		<?php endif; ?>

		<?php if ( null !== $progress ) : ?>
			<!-- enrolled learner: completion + continue -->
			<div class="mt-auto border-t border-paper-100 pt-4">
				<div class="flex items-center justify-between text-xs text-ink-light">
					<span><?php esc_html_e( 'ความคืบหน้า', 'bia-learn' ); ?></span>
					<span class="font-semibold text-crimson"><?php echo esc_html( $progress ); ?>%</span>
				</div>
				<div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-paper-100">
					<span class="block h-full rounded-full bg-crimson transition-all duration-500" style="width:<?php echo (int) $progress; ?>%"></span>
				</div>
				<span class="mt-3 inline-flex items-center gap-1.5 text-sm font-semibold text-crimson">
					<?php esc_html_e( 'เรียนต่อ', 'bia-learn' ); ?>
					<?php echo bia_learn_icon( 'arrow', 'h-4 w-4 transition group-hover:translate-x-1' ); // phpcs:ignore ?>
				</span>
			</div>
		<?php else : ?>
			<!-- footer row -->
			<div class="mt-auto flex items-center justify-between border-t border-paper-100 pt-4">
			<?php if ( $level && isset( $level_labels[ $level ] ) ) : ?>
				<span class="badge-muted"><?php echo esc_html( $level_labels[ $level ] ); ?></span>
			<?php elseif ( $enrolled ) : ?>
				<span class="inline-flex items-center gap-1.5 text-xs text-ink-light"><?php echo bia_learn_icon( 'users', 'h-4 w-4 text-crimson' ); // phpcs:ignore ?><?php printf( esc_html__( '%s ผู้เรียน', 'bia-learn' ), esc_html( number_format_i18n( $enrolled ) ) ); ?></span>
			<?php else : ?>
				<span></span>
			<?php endif; ?>

			<?php if ( $rating ) : ?>
				<span class="inline-flex items-center gap-1 text-xs font-semibold text-ink">
					<?php echo bia_learn_icon( 'star', 'h-4 w-4 text-gold' ); // phpcs:ignore ?>
					<?php echo esc_html( number_format( (float) $rating->rating_avg, 1 ) ); ?>
				</span>
			<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</article>
