<?php
/**
 * Template for displaying My Certificates in Tutor Dashboard.
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;
?>

	<div class="tutor-mb-32 flex justify-between items-end flex-wrap gap-4 border-b border-paper-200 pb-4">
		<div>
			<h3 class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-12">
				<?php esc_html_e( 'เกียรติบัตรของฉัน', 'bia-learn' ); ?>
			</h3>
			<p class="tutor-fs-6 tutor-color-muted">
				<?php esc_html_e( 'รายการเกียรติบัตรทั้งหมดที่คุณได้รับ', 'bia-learn' ); ?>
			</p>
		</div>
		
		<?php 
		$user_id = get_current_user_id();
		$profile_url = tutor_utils()->profile_url( $user_id );
		$enc_url = urlencode( $profile_url );
		$fb_share = 'https://www.facebook.com/sharer/sharer.php?u=' . $enc_url;
		$line_share = 'https://social-plugins.line.me/lineit/share?url=' . $enc_url;
		$linkedin_share = 'https://www.linkedin.com/sharing/share-offsite/?url=' . $enc_url;
		?>
		<div class="flex items-center gap-2">
			<span class="text-sm font-medium text-ink-light mr-1"><?php esc_html_e( 'แชร์โปรไฟล์:', 'bia-learn' ); ?></span>
			<a href="<?php echo esc_url( $fb_share ); ?>" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center w-9 h-9 rounded-full bg-[#1877F2] text-white hover:bg-[#0c63d4] transition-colors shadow-sm" title="Facebook">
				<i class="ti ti-brand-facebook text-lg"></i>
			</a>
			<a href="<?php echo esc_url( $line_share ); ?>" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center w-9 h-9 rounded-full bg-[#00C300] text-white hover:bg-[#00a300] transition-colors shadow-sm" title="LINE">
				<i class="ti ti-brand-line text-lg"></i>
			</a>
			<a href="<?php echo esc_url( $linkedin_share ); ?>" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center w-9 h-9 rounded-full bg-[#0a66c2] text-white hover:bg-[#004182] transition-colors shadow-sm" title="LinkedIn">
				<i class="ti ti-brand-linkedin text-lg"></i>
			</a>
		</div>
	</div>

	<?php echo do_shortcode( '[certpsu_my_certificates]' ); ?>
</div>
