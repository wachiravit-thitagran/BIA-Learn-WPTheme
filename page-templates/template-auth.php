<?php
/**
 * Template Name: เข้าสู่ระบบ / สมัครเรียน (Auth)
 *
 * Branded sign-in / sign-up page. Uses WordPress' native login flow and, when
 * Tutor LMS is active, its student registration form — so submissions work
 * without any extra plumbing.
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

// Where to send the learner after authenticating: the Tutor dashboard if
// available, otherwise the homepage.
$bia_dashboard = home_url( '/' );
if ( function_exists( 'tutor_utils' ) ) {
	$dash = tutor_utils()->get_tutor_dashboard_page_permalink();
	if ( $dash ) {
		$bia_dashboard = $dash;
	}
}

$bia_has_tutor   = function_exists( 'bia_learn_has_tutor_lms' ) ? bia_learn_has_tutor_lms() : function_exists( 'tutor' );
$bia_can_register = $bia_has_tutor || (bool) get_option( 'users_can_register' );

// Default tab: registration for new learners, unless ?tab=login or sign-up
// is disabled.
$bia_default_tab = ( isset( $_GET['tab'] ) && 'login' === $_GET['tab'] ) || ! $bia_can_register ? 'login' : 'register'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

// Where to send the learner after login: an explicit ?redirect_to (e.g. when
// sent here from a gated lesson) wins, otherwise the dashboard.
$bia_redirect = isset( $_GET['redirect_to'] ) ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) : $bia_dashboard; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

// Optional auth shortcode from a login/registration plugin, set at Appearance →
// Customize → ตั้งค่า BIA Learn → หน้าเข้าสู่ระบบ. When present it replaces the
// theme's built-in login/register forms — so a plugin can be dropped in without
// editing the parent theme, and the value (a theme mod in the database) survives
// theme updates. Developers can override it via the bia_learn_auth_shortcode filter.
$bia_auth_shortcode = trim( (string) apply_filters( 'bia_learn_auth_shortcode', get_theme_mod( 'bia_learn_auth_shortcode', '' ) ) );

get_header();
?>

<main id="main" class="relative overflow-hidden">
	<section class="container-bia grid min-h-[70vh] items-stretch gap-0 py-12 lg:grid-cols-2 lg:py-16">

		<!-- Brand panel -->
		<div class="dashboard-hero hidden flex-col justify-between rounded-l-3xl rounded-r-none lg:flex">
			<div>
				<span class="inline-flex items-center gap-2 rounded-lg bg-white px-2 py-1 shadow-soft">
					<img src="<?php echo esc_url( BIA_LEARN_URI . '/assets/images/biaxpsu-logo.png' ); ?>" alt="<?php echo esc_attr( 'BIA × PSU — ' . get_bloginfo( 'name' ) ); ?>" width="273" height="142" class="h-10 w-auto" />
				</span>
				<h1 class="dashboard-hero__title mt-8 max-w-sm leading-snug">
					<?php esc_html_e( 'เรียนรู้ธรรมะ ภาวนา และปัญญา จากสวนโมกข์สู่โลกดิจิทัล', 'bia-learn' ); ?>
				</h1>
			</div>
			<ul class="mt-10 space-y-3 text-sm text-white/90">
				<?php
				$bia_benefits = array(
					__( 'คอร์สเรียนออนไลน์ฟรี เปิดให้ทุกคน', 'bia-learn' ),
					__( 'เรียนได้ทุกที่ทุกเวลา ตามจังหวะของคุณ', 'bia-learn' ),
					__( 'ทำบทเรียนครบ รับเกียรติบัตรยืนยันการเรียนรู้', 'bia-learn' ),
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

		<!-- Form panel -->
		<div class="flex flex-col justify-center rounded-3xl border border-paper-200 bg-white p-6 shadow-card sm:p-10 lg:rounded-l-none">

			<?php if ( '' !== $bia_auth_shortcode ) : ?>

				<div class="mx-auto w-full max-w-md">
					<?php
					/** Fires inside the auth form panel, before the Customizer auth shortcode. */
					do_action( 'bia_learn_before_auth_shortcode' );

					echo do_shortcode( $bia_auth_shortcode ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- shortcode output from a trusted plugin.

					/** Fires inside the auth form panel, after the Customizer auth shortcode. */
					do_action( 'bia_learn_after_auth_shortcode' );
					?>
				</div>

			<?php elseif ( is_user_logged_in() ) : ?>

				<?php $bia_user = wp_get_current_user(); ?>
				<div class="mx-auto w-full max-w-md text-center">
					<span class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-success-light text-success"><?php echo bia_learn_icon( 'check', 'h-7 w-7' ); // phpcs:ignore ?></span>
					<h2 class="mt-5 font-sans text-2xl font-bold text-ink"><?php printf( esc_html__( 'เข้าสู่ระบบแล้ว สวัสดี %s', 'bia-learn' ), esc_html( $bia_user->display_name ) ); ?></h2>
					<p class="mt-2 text-ink-light"><?php esc_html_e( 'พร้อมเรียนรู้ต่อหรือยัง?', 'bia-learn' ); ?></p>
					<div class="mt-6 flex flex-col items-center gap-3 sm:flex-row sm:justify-center">
						<a href="<?php echo esc_url( $bia_dashboard ); ?>" class="btn-primary btn-lg"><?php esc_html_e( 'ไปที่แดชบอร์ดของฉัน', 'bia-learn' ); ?><?php echo bia_learn_icon( 'arrow', 'h-5 w-5' ); // phpcs:ignore ?></a>
						<a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>" class="btn-ghost"><?php esc_html_e( 'ออกจากระบบ', 'bia-learn' ); ?></a>
					</div>
				</div>

			<?php else : ?>

				<div class="mx-auto w-full max-w-md" x-data="{ tab: '<?php echo esc_js( $bia_default_tab ); ?>' }">

					<!-- Tabs -->
					<div class="grid grid-cols-2 gap-1 rounded-xl bg-paper-100 p-1">
						<?php if ( $bia_can_register ) : ?>
							<button type="button" @click="tab = 'register'"
								class="rounded-lg px-4 py-2 text-sm font-semibold transition"
								:class="tab === 'register' ? 'bg-white text-crimson shadow-soft' : 'text-ink-light hover:text-ink'">
								<?php esc_html_e( 'สมัครเรียน', 'bia-learn' ); ?>
							</button>
						<?php endif; ?>
						<button type="button" @click="tab = 'login'"
							class="rounded-lg px-4 py-2 text-sm font-semibold transition <?php echo $bia_can_register ? '' : 'col-span-2'; ?>"
							:class="tab === 'login' ? 'bg-white text-crimson shadow-soft' : 'text-ink-light hover:text-ink'">
							<?php esc_html_e( 'เข้าสู่ระบบ', 'bia-learn' ); ?>
						</button>
					</div>

					<!-- Login -->
					<div x-show="tab === 'login'" x-cloak class="mt-7">
						<h2 class="font-sans text-2xl font-bold text-ink"><?php esc_html_e( 'เข้าสู่ระบบ', 'bia-learn' ); ?></h2>
						<p class="mt-1 text-sm text-ink-light"><?php esc_html_e( 'เข้าสู่ระบบเพื่อเรียนต่อและจัดการคอร์สของคุณ', 'bia-learn' ); ?></p>

						<form method="post" action="<?php echo esc_url( wp_login_url( $bia_redirect ) ); ?>" class="mt-6 space-y-4">
							<div>
								<label for="bia-log" class="field-label"><?php esc_html_e( 'ชื่อผู้ใช้ หรืออีเมล', 'bia-learn' ); ?></label>
								<input id="bia-log" type="text" name="log" autocomplete="username" required class="field" />
							</div>
							<div>
								<label for="bia-pwd" class="field-label"><?php esc_html_e( 'รหัสผ่าน', 'bia-learn' ); ?></label>
								<input id="bia-pwd" type="password" name="pwd" autocomplete="current-password" required class="field" />
							</div>
							<div class="flex items-center justify-between text-sm">
								<label class="inline-flex items-center gap-2 text-ink-light">
									<input type="checkbox" name="rememberme" value="forever" class="rounded border-paper-300 text-crimson focus:ring-crimson" />
									<?php esc_html_e( 'จดจำฉัน', 'bia-learn' ); ?>
								</label>
								<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" class="font-semibold text-crimson hover:underline"><?php esc_html_e( 'ลืมรหัสผ่าน?', 'bia-learn' ); ?></a>
							</div>
							<input type="hidden" name="redirect_to" value="<?php echo esc_url( $bia_redirect ); ?>" />
							<button type="submit" class="btn-primary w-full"><?php esc_html_e( 'เข้าสู่ระบบ', 'bia-learn' ); ?></button>
						</form>

						<?php if ( $bia_can_register ) : ?>
							<p class="mt-6 text-center text-sm text-ink-light">
								<?php esc_html_e( 'ยังไม่มีบัญชี?', 'bia-learn' ); ?>
								<button type="button" @click="tab = 'register'" class="font-semibold text-crimson hover:underline"><?php esc_html_e( 'สมัครเรียนฟรี', 'bia-learn' ); ?></button>
							</p>
						<?php endif; ?>
					</div>

					<!-- Register -->
					<?php if ( $bia_can_register ) : ?>
						<div x-show="tab === 'register'" x-cloak class="mt-7">
							<h2 class="font-sans text-2xl font-bold text-ink"><?php esc_html_e( 'สมัครเรียนฟรี', 'bia-learn' ); ?></h2>
							<p class="mt-1 text-sm text-ink-light"><?php esc_html_e( 'สร้างบัญชีเพื่อเข้าถึงคอร์สและบทเรียนทั้งหมด', 'bia-learn' ); ?></p>

							<div class="bia-auth-register mt-6">
								<?php
								if ( $bia_has_tutor ) {
									// Tutor LMS student registration form (handles submission + validation).
									echo do_shortcode( '[tutor_student_registration_form]' );
								} else {
									// Native WordPress registration.
									?>
									<form method="post" action="<?php echo esc_url( wp_registration_url() ); ?>" class="space-y-4">
										<div>
											<label for="bia-user_login" class="field-label"><?php esc_html_e( 'ชื่อผู้ใช้', 'bia-learn' ); ?></label>
											<input id="bia-user_login" type="text" name="user_login" autocomplete="username" required class="field" />
										</div>
										<div>
											<label for="bia-user_email" class="field-label"><?php esc_html_e( 'อีเมล', 'bia-learn' ); ?></label>
											<input id="bia-user_email" type="email" name="user_email" autocomplete="email" required class="field" />
										</div>
										<p class="text-xs text-ink-light"><?php esc_html_e( 'ระบบจะส่งลิงก์ตั้งรหัสผ่านไปยังอีเมลของคุณ', 'bia-learn' ); ?></p>
										<button type="submit" class="btn-primary w-full"><?php esc_html_e( 'สมัครเรียน', 'bia-learn' ); ?></button>
									</form>
									<?php
								}
								?>
							</div>

							<p class="mt-6 text-center text-sm text-ink-light">
								<?php esc_html_e( 'มีบัญชีอยู่แล้ว?', 'bia-learn' ); ?>
								<button type="button" @click="tab = 'login'" class="font-semibold text-crimson hover:underline"><?php esc_html_e( 'เข้าสู่ระบบ', 'bia-learn' ); ?></button>
							</p>
						</div>
					<?php endif; ?>

				</div>

			<?php endif; ?>
		</div>
	</section>
</main>

<?php
get_footer();
