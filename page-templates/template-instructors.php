<?php
/**
 * Template Name: ผู้สอนและวิทยากร (Instructors)
 *
 * Lists Tutor LMS instructors. Falls back to a notice when Tutor is inactive.
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	bia_learn_page_hero(
		array(
			'eyebrow'  => __( 'ทีมผู้สอน', 'bia-learn' ),
			'title'    => get_the_title(),
			'subtitle' => __( 'พบกับผู้สอนและวิทยากรผู้เชี่ยวชาญที่พร้อมถ่ายทอดความรู้และประสบการณ์', 'bia-learn' ),
		)
	);
endwhile;
?>

<main id="main" class="section">
	<div class="container-bia">
		<?php
		if ( function_exists( 'tutor_utils' ) ) :
			$instructors = bia_learn_get_instructors( -1 );

			if ( ! empty( $instructors ) ) :
				?>
				<div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
					<?php
					foreach ( $instructors as $user ) :
						get_template_part( 'template-parts/cards/instructor-card', null, array( 'user' => $user ) );
					endforeach;
					?>
				</div>
				<?php
			else :
				get_template_part( 'template-parts/content-none' );
			endif;

		else :
			?>
			<div class="mx-auto max-w-md rounded-2xl border border-paper-200 bg-white p-8 text-center">
				<p class="text-ink-light"><?php esc_html_e( 'ต้องเปิดใช้งานปลั๊กอิน Tutor LMS เพื่อแสดงรายชื่อผู้สอน', 'bia-learn' ); ?></p>
			</div>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
