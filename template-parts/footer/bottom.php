<?php
/**
 * Footer bottom bar: copyright + legal menu.
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

$copyright = bia_learn_option( 'bia_footer_copyright' );
if ( ! $copyright ) {
	/* translators: 1: year, 2: site name */
	$copyright = sprintf( __( '© %1$s %2$s สงวนลิขสิทธิ์', 'bia-learn' ), gmdate( 'Y' ), get_bloginfo( 'name' ) );
}
?>
<div class="border-t border-white/10">
	<div class="container-bia py-6 text-xs text-paper-400">

		<!-- Institutional credit -->
		<div class="space-y-1 text-center leading-relaxed [&_a]:text-paper-300 [&_a:hover]:text-gold-light">
			<p>
				<?php esc_html_e( 'เสริมสร้างปัญญาบนแพลตฟอร์มการเรียนรู้พุทธธรรม', 'bia-learn' ); ?>
				<a href="https://www.psu.ac.th/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'มหาวิทยาลัยสงขลานครินทร์', 'bia-learn' ); ?></a>
				<?php esc_html_e( 'ร่วมกับ', 'bia-learn' ); ?>
				<a href="https://www.bia.or.th/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'หอจดหมายเหตุพุทธทาส อินทปัญโญ (สวนโมกข์กรุงเทพ)', 'bia-learn' ); ?></a>
			</p>
			<p>
				<?php esc_html_e( 'อีเมล:', 'bia-learn' ); ?>
				<a href="mailto:bia@psu.ac.th">bia@psu.ac.th</a>
				<span class="px-1 text-paper-500">&middot;</span>
				<?php esc_html_e( 'Line ID:', 'bia-learn' ); ?>
				<a href="https://line.me/ti/p/@biaxpsu" target="_blank" rel="noopener noreferrer">@BIAxPSU</a>
			</p>
		</div>

		<!-- Copyright + legal -->
		<div class="mt-5 flex flex-col items-center justify-between gap-3 border-t border-white/5 pt-5 md:flex-row">
			<p><?php echo wp_kses_post( $copyright ); ?></p>

			<?php
			if ( has_nav_menu( 'footer_legal' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'footer_legal',
						'container'      => false,
						'menu_class'     => 'flex flex-wrap items-center gap-x-5 gap-y-1 [&_a]:text-paper-400 [&_a:hover]:text-gold-light',
						'depth'          => 1,
						'fallback_cb'    => false,
					)
				);
			} else {
				echo '<p class="text-paper-500">' . esc_html__( 'พัฒนาเพื่อการเรียนรู้และเผยแผ่ธรรม', 'bia-learn' ) . '</p>';
			}
			?>
		</div>
	</div>
</div>
