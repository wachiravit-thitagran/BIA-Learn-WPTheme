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
				<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 10v4h3v7h4v-7h3l1 -4h-4v-2a1 1 0 0 1 1 -1h3v-4h-3a5 5 0 0 0 -5 5v2h-3" /></svg>
			</a>
			<a href="<?php echo esc_url( $line_share ); ?>" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center w-9 h-9 rounded-full bg-[#00C300] text-white hover:bg-[#00a300] transition-colors shadow-sm" title="LINE">
				<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M21 10.5c0 -3.6 -4 -6.5 -9 -6.5s-9 2.9 -9 6.5c0 3.2 3.2 5.9 7.4 6.4c.5 .1 1.2 .2 1 .8c-.1 .5 -.4 1.7 -.5 2.1c-.2 .7 .2 1 1 .5c.8 -.5 4.3 -2.6 6.3 -4.5c1.8 -1.7 2.8 -3.2 2.8 -4.8z" /><path d="M12 9l0 3" /><path d="M15 9l0 3" /><path d="M8 9l0 3" /><path d="M13 11l-2 0" /></svg>
			</a>
			<a href="<?php echo esc_url( $linkedin_share ); ?>" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center w-9 h-9 rounded-full bg-[#0a66c2] text-white hover:bg-[#004182] transition-colors shadow-sm" title="LinkedIn">
				<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z" /><path d="M8 11l0 5" /><path d="M8 8l0 .01" /><path d="M12 16l0 -5" /><path d="M16 16v-3a2 2 0 0 0 -4 0" /></svg>
			</a>
		</div>
	</div>

	<?php echo do_shortcode( '[certpsu_my_certificates]' ); ?>
