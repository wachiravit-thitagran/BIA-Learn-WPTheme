<?php
/**
 * Course catalog header: title, count, course search and browse-by-category
 * chips. Sits above the Tutor LMS archive grid (see tutor/archive-course.php)
 * and also adapts to course-category / course-tag term archives.
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

$bia_total = 0;
$bia_counts = wp_count_posts( 'courses' );
if ( $bia_counts && isset( $bia_counts->publish ) ) {
	$bia_total = (int) $bia_counts->publish;
}

$bia_is_term = is_tax( 'course-category' ) || is_tax( 'course-tag' );
$bia_term    = $bia_is_term ? get_queried_object() : null;
$bia_active  = ( $bia_term && isset( $bia_term->term_id ) ) ? (int) $bia_term->term_id : 0;

$bia_title = $bia_term ? $bia_term->name : __( 'คอร์สเรียนทั้งหมด', 'bia-learn' );
$bia_desc  = ( $bia_term && ! empty( $bia_term->description ) )
	? $bia_term->description
	: ( $bia_total ? sprintf( __( 'เลือกเรียนได้จาก %s คอร์ส ตามจังหวะของคุณ', 'bia-learn' ), number_format_i18n( $bia_total ) ) : '' );

$bia_courses_url = function_exists( 'bia_learn_courses_url' ) ? bia_learn_courses_url() : home_url( '/courses/' );

$bia_cats = get_terms(
	array(
		'taxonomy'   => 'course-category',
		'hide_empty' => true,
		'number'     => 12,
		'orderby'    => 'count',
		'order'      => 'DESC',
	)
);
?>
<section class="bg-paper-50 pt-10 sm:pt-14">
	<div class="container-bia">
		<p class="eyebrow"><?php esc_html_e( 'คลังคอร์ส', 'bia-learn' ); ?></p>

		<div class="mt-3 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
			<div class="max-w-2xl">
				<h1 class="section-title"><?php echo esc_html( $bia_title ); ?></h1>
				<?php if ( $bia_desc ) : ?>
					<p class="lead mt-2"><?php echo esc_html( wp_strip_all_tags( $bia_desc ) ); ?></p>
				<?php endif; ?>
			</div>

			<form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>"
				class="flex w-full max-w-sm items-center gap-2 rounded-full border border-paper-200 bg-white p-1.5 shadow-soft focus-within:ring-2 focus-within:ring-crimson/30">
				<input type="hidden" name="post_type" value="courses" />
				<span class="grid h-9 w-9 shrink-0 place-items-center text-ink-light"><?php echo bia_learn_icon( 'search', 'h-5 w-5' ); // phpcs:ignore ?></span>
				<label for="bia-catalog-search" class="sr-only"><?php esc_html_e( 'ค้นหาคอร์ส', 'bia-learn' ); ?></label>
				<input id="bia-catalog-search" type="search" name="s"
					placeholder="<?php esc_attr_e( 'ค้นหาคอร์ส…', 'bia-learn' ); ?>"
					class="min-w-0 flex-1 border-0 bg-transparent text-ink placeholder:text-ink-light focus:ring-0" />
				<button type="submit" class="btn-primary shrink-0 rounded-full"><?php esc_html_e( 'ค้นหา', 'bia-learn' ); ?></button>
			</form>
		</div>

		<?php if ( ! is_wp_error( $bia_cats ) && ! empty( $bia_cats ) ) : ?>
			<nav class="mt-6 flex flex-wrap gap-2" aria-label="<?php esc_attr_e( 'หมวดหมู่คอร์ส', 'bia-learn' ); ?>">
				<a href="<?php echo esc_url( $bia_courses_url ); ?>"
					class="pill <?php echo $bia_active ? 'bg-paper-100 text-ink-light hover:bg-crimson-50 hover:text-crimson' : 'pill-crimson'; ?>">
					<?php esc_html_e( 'ทั้งหมด', 'bia-learn' ); ?>
				</a>
				<?php foreach ( $bia_cats as $bia_cat ) : ?>
					<a href="<?php echo esc_url( get_term_link( $bia_cat ) ); ?>"
						class="pill <?php echo ( $bia_active === (int) $bia_cat->term_id ) ? 'pill-crimson' : 'bg-paper-100 text-ink-light hover:bg-crimson-50 hover:text-crimson'; ?>">
						<?php echo esc_html( $bia_cat->name ); ?>
					</a>
				<?php endforeach; ?>
			</nav>
		<?php endif; ?>
	</div>
</section>
