<?php
/**
 * Override of Tutor LMS `templates/login.php`.
 *
 * Tutor loads this template whenever a visitor reaches gated content — a lesson,
 * a quiz, an assignment — without being able to see it. Its own version does two
 * things this site cannot use:
 *
 *   1. With `enable_tutor_native_login` switched off it calls
 *      `header( 'Location: …' )` on line 19. By then the theme has already sent
 *      the document head, so PHP raises "Cannot modify header information —
 *      headers already sent" and prints the warning where the lesson should be.
 *      The visitor sees a wall of red text instead of a way to sign in.
 *   2. With native login on it renders a username/password form, which is dead
 *      on this site: password sign-in is disabled, so every submission fails.
 *
 * Rendering the branded SSO panel in place solves both. Nothing is redirected, so
 * there are no headers to modify, and the visitor gets the only sign-in method
 * that actually works — on the page they asked for, with `return_to` pointing
 * back at it so they land where they meant to go.
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

$bia_return_to = function_exists( 'tutor_utils' ) ? (string) tutor_utils()->get_current_url() : '';
$bia_providers = array( 'google', 'oidc', 'facebook', 'line', 'oauth2' );
?>
<div class="bia-tutor-login tutor-login-form-wrap max-w-lg mx-auto text-center bg-white rounded-2xl border border-paper-200 shadow-sm py-12 px-6 my-8">
	<div class="w-16 h-16 mx-auto bg-red-50 text-brand rounded-full flex items-center justify-center text-2xl mb-4">
		🔒
	</div>

	<h2 class="font-sans text-xl font-bold text-ink mb-2">
		<?php esc_html_e( 'เข้าสู่ระบบเพื่อเรียนบทเรียนนี้', 'bia-learn' ); ?>
	</h2>

	<p class="text-ink-light mb-8 max-w-sm mx-auto">
		<?php esc_html_e( 'เนื้อหานี้สงวนไว้สำหรับผู้เรียนที่ยืนยันตัวตนแล้ว เข้าสู่ระบบด้วยบัญชีกลาง (SSO) แล้วระบบจะพากลับมาที่หน้านี้', 'bia-learn' ); ?>
	</p>

	<div class="bia-auth-social flex flex-col gap-3 max-w-xs mx-auto">
		<?php
		foreach ( $bia_providers as $bia_provider ) {
			echo do_shortcode(
				sprintf(
					'[authorizenter_button context="default" provider="%1$s" return_to="%2$s"]',
					esc_attr( $bia_provider ),
					esc_url( $bia_return_to )
				)
			);
		}
		?>
	</div>

	<?php
	// Fallback: if the SSO buttons cannot render (plugin inactive, no provider
	// enabled) the visitor still needs a way out, so link the branded auth page.
	if ( function_exists( 'bia_learn_auth_url' ) ) :
		?>
		<p class="mt-8 text-sm">
			<a class="text-brand underline" href="<?php echo esc_url( bia_learn_auth_url( 'login', $bia_return_to ) ); ?>">
				<?php esc_html_e( 'ไปที่หน้าเข้าสู่ระบบ', 'bia-learn' ); ?>
			</a>
		</p>
	<?php endif; ?>
</div>
