<?php
/**
 * Template Name: แดชบอร์ดผู้เรียน (Dashboard)
 *
 * Provides a dedicated layout for the Tutor LMS dashboard.
 * If the user is not logged in, they are redirected to the auth page.
 * Automatically injects the Tutor dashboard shortcode if missing.
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

// Enforce login
if ( ! is_user_logged_in() ) {
	wp_safe_redirect( bia_learn_auth_url( 'login' ) );
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	bia_learn_page_hero(
		array(
			'eyebrow'  => __( 'พื้นที่การเรียนรู้', 'bia-learn' ),
			'title'    => get_the_title(),
			'subtitle' => __( 'จัดการการเรียนรู้ ติดตามความคืบหน้า และดูเกียรติบัตรของคุณ', 'bia-learn' ),
		)
	);
	?>

	<section class="section">
		<div class="container-bia max-w-6xl">
			<?php
			$bia_content = get_the_content();
			$bia_is_elementor = isset( $_GET['elementor-preview'] ) || ( class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->preview->is_preview_mode() );
			
			if ( trim( $bia_content ) || $bia_is_elementor ) :
				?>
				<div class="prose-bia mb-10 max-w-none"><?php the_content(); ?></div>
			<?php endif; ?>

			<?php
			// Ensure the dashboard is shown even if the admin forgot the shortcode.
			if ( ! has_shortcode( get_the_content(), 'tutor_dashboard' ) ) {
				echo do_shortcode( '[tutor_dashboard]' );
			}
			?>
		</div>
	</section>

	<?php
endwhile;

get_footer();
