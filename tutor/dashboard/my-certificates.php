<?php
/**
 * Template for displaying My Certificates in Tutor Dashboard.
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;
?>

	<div class="tutor-mb-32 flex justify-between items-end flex-wrap gap-4">
		<div>
			<h3 class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-12">
				<?php esc_html_e( 'เกียรติบัตรของฉัน', 'bia-learn' ); ?>
			</h3>
			<p class="tutor-fs-6 tutor-color-muted">
				<?php esc_html_e( 'รายการเกียรติบัตรทั้งหมดที่คุณได้รับจากการเรียนรู้บนแพลตฟอร์ม', 'bia-learn' ); ?>
			</p>
		</div>
		
		<?php 
		$user_id = get_current_user_id();
		$profile_url = tutor_utils()->profile_url( $user_id );
		$linkedin_share_url = 'https://www.linkedin.com/sharing/share-offsite/?url=' . urlencode( $profile_url );
		?>
		<a href="<?php echo esc_url( $linkedin_share_url ); ?>" target="_blank" rel="noopener noreferrer" class="bia-tutor-btn flex items-center gap-2 bg-[#0a66c2] hover:bg-[#004182] text-white border-none px-4 py-2 rounded-md shadow-sm transition-colors">
			<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
				<path d="M0 1.146C0 .513.526 0 1.175 0h13.65C15.474 0 16 .513 16 1.146v13.708c0 .633-.526 1.146-1.175 1.146H1.175C.526 16 0 15.487 0 14.854V1.146zm4.943 12.248V6.169H2.542v7.225h2.401zm-1.2-8.212c.837 0 1.358-.554 1.358-1.248-.015-.709-.52-1.248-1.342-1.248-.822 0-1.359.54-1.359 1.248 0 .694.521 1.248 1.327 1.248h.016zm4.908 8.212V9.359c0-.216.016-.432.08-.586.173-.431.568-.878 1.232-.878.869 0 1.216.662 1.216 1.634v3.865h2.401V9.25c0-2.22-1.184-3.252-2.764-3.252-1.274 0-1.845.7-2.165 1.193v.025h-.016a5.54 5.54 0 0 1 .016-.025V6.169h-2.4c.03.678 0 7.225 0 7.225h2.4z"/>
			</svg>
			<span><?php esc_html_e( 'แชร์โปรไฟล์สาธารณะลง LinkedIn', 'bia-learn' ); ?></span>
		</a>
	</div>

	<div class="bg-paper-50 p-4 rounded-xl border border-paper-200 mb-8 flex items-start gap-3">
		<div class="text-2xl mt-1">💡</div>
		<div class="text-sm text-ink-light leading-relaxed">
			<strong><?php esc_html_e( 'เคล็ดลับ:', 'bia-learn' ); ?></strong> 
			<?php esc_html_e( 'คุณสามารถแชร์โปรไฟล์ของคุณไปที่ LinkedIn เพื่อโชว์ใบประกาศนียบัตรและคอร์สที่คุณเรียนจบทั้งหมดให้โลกเห็นได้ทันที หรือดาวน์โหลดใบประกาศนียบัตรเป็นรูปภาพไปโพสต์เองก็ได้เช่นกัน', 'bia-learn' ); ?>
		</div>
	</div>

	<?php echo do_shortcode( '[certpsu_my_certificates]' ); ?>
</div>
