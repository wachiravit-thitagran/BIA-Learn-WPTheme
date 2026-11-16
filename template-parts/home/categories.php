<?php
/**
 * Learning categories grid (Tutor "course-category" taxonomy when present).
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

$taxonomy = taxonomy_exists( 'course-category' ) ? 'course-category' : 'category';
$terms = get_terms(
	array(
		'taxonomy'   => $taxonomy,
		'hide_empty' => true,
		'number'     => 8,
		'orderby'    => 'count',
		'order'      => 'DESC',
	)
);

if ( is_wp_error( $terms ) || empty( $terms ) ) {
	return;
}
?>
<section class="section bg-paper-50">
	<div class="container-bia">
		<?php
		bia_learn_section_heading(
			array(
				'eyebrow' => __( 'หมวดหมู่', 'bia-learn' ),
				'title'   => __( 'เรียนรู้ตามหัวข้อที่สนใจ', 'bia-learn' ),
				'lead'    => __( 'สำรวจหมวดหมู่การเรียนรู้ที่หลากหลาย ตั้งแต่ธรรมะ การภาวนา ไปจนถึงศิลปะและวัฒนธรรม', 'bia-learn' ),
			)
		);
		?>

		<nav aria-label="<?php esc_attr_e( 'หมวดหมู่คอร์สเรียน', 'bia-learn' ); ?>" class="mt-10 grid grid-cols-1 gap-x-10 sm:grid-cols-2 lg:grid-cols-4">
			<?php foreach ( $terms as $term ) : ?>
				<a href="<?php echo esc_url( get_term_link( $term ) ); ?>" class="group flex min-h-24 items-center justify-between gap-4 border-b border-paper-200 py-5 transition-colors hover:border-crimson focus-visible:rounded-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-crimson">
					<span class="min-w-0">
						<span class="block text-lg font-semibold leading-snug text-ink transition-colors group-hover:text-crimson"><?php echo esc_html( $term->name ); ?></span>
						<span class="mt-1 block text-sm text-ink-light"><?php printf( esc_html( _n( '%d คอร์ส', '%d คอร์ส', $term->count, 'bia-learn' ) ), (int) $term->count ); ?></span>
					</span>
					<span class="shrink-0 text-ink-light transition-colors group-hover:text-crimson" aria-hidden="true"><?php echo bia_learn_icon( 'arrow', 'h-4 w-4' ); // phpcs:ignore ?></span>
				</a>
			<?php endforeach; ?>
		</nav>
	</div>
</section>
