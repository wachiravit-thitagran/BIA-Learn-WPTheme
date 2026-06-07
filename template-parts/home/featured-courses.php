<?php
/**
 * Featured / latest courses grid.
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

$post_type    = function_exists( 'tutor_utils' ) ? 'courses' : 'post';
$per_page     = 6;
$featured_key = apply_filters( 'bia_learn_featured_meta_key', '_tutor_course_featured' );

// Pass 1 — featured courses first (newest featured). Using meta_key directly
// would INNER JOIN and *exclude* every non-featured course, so we gather IDs
// in two passes instead and merge, keeping order.
$ids = get_posts(
	array(
		'post_type'      => $post_type,
		'posts_per_page' => $per_page,
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'meta_query'     => array(
			array(
				'key'     => $featured_key,
				'value'   => array( '1', 'yes', 'on', 'true' ),
				'compare' => 'IN',
			),
		),
	)
);

// Pass 2 — fill the remaining slots with the latest courses, excluding any
// already picked. Runs even when no course is flagged featured.
if ( count( $ids ) < $per_page ) {
	$fill = get_posts(
		array(
			'post_type'           => $post_type,
			'posts_per_page'      => $per_page - count( $ids ),
			'fields'              => 'ids',
			'no_found_rows'       => true,
			'ignore_sticky_posts' => true,
			'orderby'             => 'date',
			'order'               => 'DESC',
			'post__not_in'        => $ids ? $ids : array( 0 ),
		)
	);
	$ids = array_merge( $ids, $fill );
}

if ( empty( $ids ) ) {
	return;
}

$courses = new WP_Query(
	array(
		'post_type'           => $post_type,
		'post__in'            => $ids,
		'orderby'             => 'post__in',
		'posts_per_page'      => $per_page,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	)
);

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
