<?php
/**
 * Featured instructors row (Tutor LMS instructors).
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'tutor_utils' ) ) {
	return;
}

$instructors = bia_learn_get_instructors( 4 );

if ( empty( $instructors ) ) {
	return;
}
?>
<section class="section bg-paper-100/60">
	<div class="container-bia">
		<div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
			<?php
			bia_learn_section_heading(
				array(
					'eyebrow' => __( 'ผู้สอน', 'bia-learn' ),
					'title'   => __( 'เรียนกับผู้สอนและวิทยากร', 'bia-learn' ),
					'lead'    => __( 'ผู้เชี่ยวชาญและครูบาอาจารย์ที่พร้อมถ่ายทอดความรู้และประสบการณ์', 'bia-learn' ),
					'align'   => 'left',
				)
			);
			?>
			<a href="<?php echo esc_url( bia_learn_page_url( 'instructors' ) ); ?>" class="btn-outline shrink-0">
				<?php esc_html_e( 'ดูทั้งหมด', 'bia-learn' ); ?>
				<?php echo bia_learn_icon( 'arrow', 'h-4 w-4' ); // phpcs:ignore ?>
			</a>
		</div>

		<div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
			<?php
			foreach ( $instructors as $user ) :
				get_template_part( 'template-parts/cards/instructor-card', null, array( 'user' => $user ) );
			endforeach;
			?>
		</div>
	</div>
</section>
