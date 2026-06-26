<?php
/**
 * Override Tutor LMS Registration Form to force SSO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="tutor-registration-wrap text-center py-16 bg-white rounded-2xl border border-paper-200 shadow-sm max-w-lg mx-auto">
	<div class="w-16 h-16 mx-auto bg-blue-50 text-blue-600 rounded-full flex items-center justify-center text-2xl mb-4">
		🔒
	</div>
	<h3 class="font-sans text-xl font-bold text-ink mb-2">
		<?php esc_html_e( 'เข้าสู่ระบบเพื่อเรียนรู้', 'bia-learn' ); ?>
	</h3>
	<p class="text-ink-light mb-8 max-w-sm mx-auto">
		<?php esc_html_e( 'ระบบสงวนสิทธิ์สำหรับผู้ใช้งานที่ยืนยันตัวตนผ่าน Single Sign-On (SSO) แล้วเท่านั้น ไม่สามารถสมัครสมาชิกด้วยอีเมลปกติได้', 'bia-learn' ); ?>
	</p>
	<a href="<?php echo esc_url( wp_login_url( tutor_utils()->get_current_url() ) ); ?>" class="btn-primary px-8 py-3">
		<?php esc_html_e( 'เข้าสู่ระบบด้วยบัญชีกลาง (SSO)', 'bia-learn' ); ?>
	</a>
</div>
