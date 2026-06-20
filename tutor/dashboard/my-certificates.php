<?php
/**
 * Template for displaying My Certificates in Tutor Dashboard.
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="tutor-dashboard-content-inner">
	<div class="tutor-mb-32">
		<h3 class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-12">
			<?php esc_html_e( 'เกียรติบัตรของฉัน', 'bia-learn' ); ?>
		</h3>
		<p class="tutor-fs-6 tutor-color-muted">
			<?php esc_html_e( 'รายการเกียรติบัตรทั้งหมดที่คุณได้รับจากการเรียนรู้บนแพลตฟอร์ม', 'bia-learn' ); ?>
		</p>
	</div>

	<?php echo do_shortcode( '[certpsu_my_certificates]' ); ?>
</div>
