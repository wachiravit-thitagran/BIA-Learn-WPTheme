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

$bia_user         = wp_get_current_user();
$bia_display_name = $bia_user->first_name ? $bia_user->first_name : $bia_user->display_name;

$bia_enrolled_total  = 0;
$bia_in_progress     = 0;
$bia_completed_total = 0;

if ( function_exists( 'tutor_utils' ) ) {
	$bia_enrolled_query = tutor_utils()->get_enrolled_courses_by_user( get_current_user_id() );

	if ( $bia_enrolled_query && ! empty( $bia_enrolled_query->posts ) ) {
		$bia_enrolled_total = count( $bia_enrolled_query->posts );

		foreach ( $bia_enrolled_query->posts as $bia_course_post ) {
			$bia_progress = bia_learn_course_progress( $bia_course_post->ID );

			if ( null === $bia_progress ) {
				continue;
			}

			if ( $bia_progress >= 100 ) {
				++$bia_completed_total;
			} elseif ( $bia_progress > 0 ) {
				++$bia_in_progress;
			}
		}
	}
}

while ( have_posts() ) :
	the_post();
	?>

	<section class="section-tight" style="padding-bottom:0">
		<div class="container-bia max-w-6xl">
			<div class="dashboard-hero">
				<div class="relative z-10 flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
					<div>
						<p class="text-sm font-semibold text-white/70"><?php esc_html_e( 'พื้นที่การเรียนรู้ของคุณ', 'bia-learn' ); ?></p>
						<h1 class="dashboard-hero__title mt-1">
							<?php
							printf(
								esc_html__( 'สวัสดี %s', 'bia-learn' ),
								esc_html( $bia_display_name )
							);
							?>
						</h1>
						<p class="dashboard-hero__subtitle"><?php esc_html_e( 'เรียนต่อจากที่ค้างไว้ ติดตามความคืบหน้า และจัดการคอร์สของคุณได้จากที่นี่', 'bia-learn' ); ?></p>
					</div>
					<a href="<?php echo esc_url( bia_learn_courses_url() ); ?>" class="btn-gold shrink-0">
						<?php esc_html_e( 'สำรวจคอร์สเพิ่มเติม', 'bia-learn' ); ?>
						<?php echo bia_learn_icon( 'arrow', 'h-4 w-4' ); // phpcs:ignore ?>
					</a>
				</div>
			</div>
		</div>
	</section>

	<?php if ( $bia_enrolled_total > 0 ) : ?>
		<section class="section-tight" style="padding-bottom:0">
			<div class="container-bia max-w-6xl">
				<div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
					<div class="stat-card flex items-center gap-4">
						<span class="icon-chip icon-chip-crimson"><?php echo bia_learn_icon( 'book', 'h-5 w-5' ); // phpcs:ignore ?></span>
						<div>
							<div class="stat-card__num"><?php echo esc_html( number_format_i18n( $bia_enrolled_total ) ); ?></div>
							<div class="stat-card__label"><?php esc_html_e( 'คอร์สที่ลงทะเบียน', 'bia-learn' ); ?></div>
						</div>
					</div>
					<div class="stat-card flex items-center gap-4">
						<span class="icon-chip icon-chip-warning"><?php echo bia_learn_icon( 'play', 'h-5 w-5' ); // phpcs:ignore ?></span>
						<div>
							<div class="stat-card__num"><?php echo esc_html( number_format_i18n( $bia_in_progress ) ); ?></div>
							<div class="stat-card__label"><?php esc_html_e( 'กำลังเรียน', 'bia-learn' ); ?></div>
						</div>
					</div>
					<div class="stat-card flex items-center gap-4">
						<span class="icon-chip icon-chip-success"><?php echo bia_learn_icon( 'cert', 'h-5 w-5' ); // phpcs:ignore ?></span>
						<div>
							<div class="stat-card__num"><?php echo esc_html( number_format_i18n( $bia_completed_total ) ); ?></div>
							<div class="stat-card__label"><?php esc_html_e( 'เรียนจบแล้ว', 'bia-learn' ); ?></div>
						</div>
					</div>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<section class="section-tight pt-8">
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
