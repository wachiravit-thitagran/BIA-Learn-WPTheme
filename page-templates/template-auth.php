<?php
/**
 * Template Name: เข้าสู่ระบบ / สมัครเรียน (Auth)
 *
 * Branded Authorizenter-first authentication page.
 *
 * Learners and ordinary users always authenticate through configured external
 * identity providers. WordPress username/password authentication is deliberately
 * absent from this surface; the administrator-only emergency password route is
 * wp-login.php?external=wordpress and is enforced by Authorizenter Core.
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

// Where to send the learner after authenticating: Tutor dashboard when
// available, otherwise the homepage.
$bia_dashboard = home_url( '/' );
if ( function_exists( 'tutor_utils' ) ) {
	$dash = tutor_utils()->get_tutor_dashboard_page_permalink();
	if ( $dash ) {
		$bia_dashboard = $dash;
	}
}

// Preserve the requested destination from gated Tutor pages.
$bia_redirect = isset( $_GET['redirect_to'] )
	? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) )
	: $bia_dashboard; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

$bia_is_register = isset( $_GET['tab'] ) && 'register' === sanitize_key( wp_unslash( $_GET['tab'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

// Authorizenter UI is the preferred and authoritative front-end. A custom
// shortcode remains as a deployment fallback only when its UI plugin is absent.
$bia_auth_shortcode = '';
if ( shortcode_exists( 'authorizenter_login' ) ) {
	$bia_auth_shortcode = sprintf(
		'[authorizenter_login context="default" return_to="%s"]',
		esc_attr( $bia_redirect )
	);
} else {
	$bia_auth_shortcode = trim(
		(string) apply_filters(
			'bia_learn_auth_shortcode',
			get_theme_mod( 'bia_learn_auth_shortcode', '' )
		)
	);
}

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<main id="main" class="relative overflow-hidden">
		<?php
		$bia_content      = get_the_content();
		$bia_is_elementor = isset( $_GET['elementor-preview'] ) || ( class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->preview->is_preview_mode() ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( trim( $bia_content ) || $bia_is_elementor ) :
			?>
			<div class="container-bia mt-8">
				<div class="prose-bia mx-auto"><?php the_content(); ?></div>
			</div>
		<?php endif; ?>

		<section class="container-bia grid min-h-[70vh] items-stretch gap-0 py-12 lg:grid-cols-2 lg:py-16">
			<!-- Brand panel -->
			<div class="dashboard-hero hidden flex-col justify-between rounded-l-3xl rounded-r-none lg:flex">
				<div>
					<span class="inline-flex items-center gap-2 rounded-lg bg-white px-2 py-1 shadow-soft">
						<img src="<?php echo esc_url( BIA_LEARN_URI . '/assets/images/biaxpsu-logo.png' ); ?>" alt="<?php echo esc_attr( 'BIA × PSU — ' . get_bloginfo( 'name' ) ); ?>" width="273" height="142" class="h-10 w-auto" />
					</span>
					<h1 class="dashboard-hero__title mt-8 max-w-sm leading-snug">
						<?php echo wp_kses_post( __( 'เรียนรู้ พุทธธรรม<br>ในยุค ดิจิทัล', 'bia-learn' ) ); ?>
					</h1>
				</div>

				<ul class="mt-10 space-y-3 text-sm text-white/90">
					<?php
					$bia_benefits = array(
						__( 'เข้าสู่ระบบด้วยบัญชีที่คุณใช้อยู่แล้ว', 'bia-learn' ),
						__( 'ไม่ต้องสร้างหรือจดจำรหัสผ่านใหม่', 'bia-learn' ),
						__( 'เรียนต่อและติดตามความคืบหน้าได้ในบัญชีเดียว', 'bia-learn' ),
					);
					foreach ( $bia_benefits as $bia_benefit ) :
						?>
						<li class="flex items-center gap-3">
							<span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-white/15 text-gold-light"><?php echo bia_learn_icon( 'check', 'h-4 w-4' ); // phpcs:ignore ?></span>
							<?php echo esc_html( $bia_benefit ); ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

			<!-- Auth panel -->
			<div class="flex flex-col justify-center rounded-3xl border border-paper-200 bg-white p-6 shadow-card sm:p-10 lg:rounded-l-none">
				<?php if ( is_user_logged_in() ) : ?>
					<?php $bia_user = wp_get_current_user(); ?>
					<div class="mx-auto w-full max-w-md text-center">
						<span class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-success-light text-success"><?php echo bia_learn_icon( 'check', 'h-7 w-7' ); // phpcs:ignore ?></span>
						<h2 class="mt-5 font-sans text-2xl font-bold text-ink">
							<?php printf( esc_html__( 'เข้าสู่ระบบแล้ว สวัสดี %s', 'bia-learn' ), esc_html( $bia_user->display_name ) ); ?>
						</h2>
						<p class="mt-2 text-ink-light"><?php esc_html_e( 'พร้อมเรียนรู้ต่อหรือยัง?', 'bia-learn' ); ?></p>
						<div class="mt-6 flex flex-col items-center gap-3 sm:flex-row sm:justify-center">
							<a href="<?php echo esc_url( $bia_dashboard ); ?>" class="btn-primary btn-lg">
								<?php esc_html_e( 'ไปที่แดชบอร์ดของฉัน', 'bia-learn' ); ?>
								<?php echo bia_learn_icon( 'arrow', 'h-5 w-5' ); // phpcs:ignore ?>
							</a>
							<a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>" class="btn-ghost"><?php esc_html_e( 'ออกจากระบบ', 'bia-learn' ); ?></a>
						</div>
					</div>
				<?php else : ?>
					<div class="mx-auto w-full max-w-md">
						<p class="eyebrow"><?php esc_html_e( 'บัญชี BIA Learn', 'bia-learn' ); ?></p>
						<h2 class="mt-3 font-sans text-2xl font-bold text-ink">
							<?php echo esc_html( $bia_is_register ? __( 'สมัครใช้งานด้วยบัญชีที่คุณมีอยู่แล้ว', 'bia-learn' ) : __( 'เข้าสู่ระบบเพื่อเรียนต่อ', 'bia-learn' ) ); ?>
						</h2>
						<p class="mt-2 text-sm leading-relaxed text-ink-light">
							<?php
							echo esc_html(
								$bia_is_register
									? __( 'เลือกผู้ให้บริการบัญชีด้านล่าง ระบบจะสร้างบัญชีผู้เรียนให้โดยอัตโนมัติตามนโยบายที่กำหนด', 'bia-learn' )
									: __( 'เลือกช่องทางเข้าสู่ระบบด้านล่าง ระบบจะพาคุณกลับไปยังหน้าที่ต้องการหลังยืนยันตัวตนสำเร็จ', 'bia-learn' )
							);
							?>
						</p>

						<div class="bia-auth-social mt-7">
							<?php if ( '' !== $bia_auth_shortcode ) : ?>
								<?php
								do_action( 'bia_learn_before_auth_shortcode' );
								echo do_shortcode( $bia_auth_shortcode ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								do_action( 'bia_learn_after_auth_shortcode' );
								?>
							<?php else : ?>
								<div class="rounded-2xl border border-warning/30 bg-warning-light p-5 text-sm text-warning-dark">
									<p class="font-semibold"><?php esc_html_e( 'ยังไม่พบช่องทางเข้าสู่ระบบ', 'bia-learn' ); ?></p>
									<p class="mt-1"><?php esc_html_e( 'กรุณาติดต่อผู้ดูแลระบบเพื่อตรวจสอบ Authorizenter และผู้ให้บริการยืนยันตัวตน', 'bia-learn' ); ?></p>
								</div>
							<?php endif; ?>
						</div>

						<p class="mt-6 text-xs leading-relaxed text-ink-light">
							<?php esc_html_e( 'เพื่อความปลอดภัย ผู้ใช้งานทั่วไปไม่สามารถเข้าสู่ระบบด้วยรหัสผ่าน WordPress จากหน้านี้ได้', 'bia-learn' ); ?>
						</p>
					</div>
				<?php endif; ?>
			</div>
		</section>
	</main>
	<?php
endwhile;

get_footer();
