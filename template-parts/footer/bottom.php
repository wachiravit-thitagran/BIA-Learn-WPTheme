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
	<div class="container-bia flex flex-col items-center justify-between gap-3 py-6 text-xs text-paper-400 md:flex-row">
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
