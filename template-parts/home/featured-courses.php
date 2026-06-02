<?php
/**
 * Featured / latest courses grid.
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

$post_type = function_exists( 'tutor_utils' ) ? 'courses' : 'post';

$courses = new WP_Query(
	array(
		'post_type'           => $post_type,
		'posts_per_page'      => 6,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
		'meta_key'            => '_tutor_course_featured', // featured first when set.
		'orderby'             => array( 'meta_value' => 'DESC', 'date' => 'DESC' ),
	)
);

// If the featured meta query returned nothing, fall back to latest.
if ( ! $courses->have_posts() ) {
	$courses = new WP_Query(
		array(
			'post_type'           => $post_type,
			'posts_per_page'      => 6,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);
}

if ( ! $courses->have_posts() ) {
	return;
}
?>
<section class="section">
	<div class="container-bia">
		<div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
			<?php
			bia_learn_section_heading(
				array(
					'eyebrow' => __( 'คอร์สเรียน', 'bia-learn' ),
					'title'   => __( 'คอร์สแนะนำสำหรับคุณ', 'bia-learn' ),
					'lead'    => __( 'เลือกเรียนตามความสนใจ เริ่มต้นได้ทันทีและเรียนรู้ในจังหวะของคุณเอง', 'bia-learn' ),
					'align'   => 'left',
				)
			);
			?>
			<a href="<?php echo esc_url( bia_learn_courses_url() ); ?>" class="btn-outline shrink-0">
				<?php esc_html_e( 'ดูคอร์สทั้งหมด', 'bia-learn' ); ?>
				<?php echo bia_learn_icon( 'arrow', 'h-4 w-4' ); // phpcs:ignore ?>
			</a>
		</div>

		<div class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
			<?php
			while ( $courses->have_posts() ) :
				$courses->the_post();
				if ( 'courses' === $post_type ) {
					get_template_part( 'template-parts/cards/course-card' );
				} else {
					get_template_part( 'template-parts/cards/post-card' );
				}
			endwhile;
			wp_reset_postdata();
			?>
		</div>
	</div>
</section>
