<?php
/**
 * Override Tutor LMS Login Form to force SSO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="tutor-login-form-wrap text-center py-8">
	<div class="w-16 h-16 mx-auto bg-blue-50 text-blue-600 rounded-full flex items-center justify-center text-2xl mb-4">
		🔒
	</div>
	<p class="text-ink-light mb-6">
		<?php esc_html_e( 'ระบบสงวนสิทธิ์สำหรับผู้ใช้งานที่ยืนยันตัวตนผ่าน Single Sign-On (SSO) แล้วเท่านั้น กรุณาเข้าสู่ระบบเพื่อดำเนินการต่อ', 'bia-learn' ); ?>
	</p>
	<a href="<?php echo esc_url( wp_login_url( tutor_utils()->get_current_url() ) ); ?>" class="btn-primary w-full inline-block py-3">
		<?php esc_html_e( 'เข้าสู่ระบบด้วยบัญชีกลาง (SSO)', 'bia-learn' ); ?>
	</a>
</div>
