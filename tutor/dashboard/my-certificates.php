<?php
/**
 * Certificate dashboard tab, styled consistently with the other BIA tabs.
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="tutor-dashboard-content-inner">
	<div class="tutor-dashboard-inline-links mb-6">
		<h3 class="font-sans text-xl font-bold text-ink m-0"><?php esc_html_e( 'เกียรติบัตรของฉัน', 'bia-learn' ); ?></h3>
		<p class="mt-2 text-sm text-ink-light"><?php esc_html_e( 'รายการเกียรติบัตรทั้งหมดที่คุณได้รับ', 'bia-learn' ); ?></p>
	</div>
	<?php echo do_shortcode( '[certpsu_my_certificates]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</div>
